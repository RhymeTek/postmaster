<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use Sunra\PhpSimple\HtmlDomParser;

class Lib_message
{
  private $error = array();
  
  function __construct($options = array())
  {
    $this->CI =& get_instance();
    $this->CI->load->model('model_message');
  }
  
  /**
   * Get error message.
   * Can be invoked after any failed operation.
   *
   * @return  string
   */
  function get_error_message()
  {
    return $this->error;
  }

  function get($message_id)
  {
    return $this->CI->model_message->get($message_id);
  }

  function get_list()
  {
    return $this->CI->model_message->get_list();
  }

  function create($subject, $owner, $list_id, $published = '1000-01-01 00:00:00')
  {
    if ($owner == 'transactional') $published = date('Y-m-d H:m:s');

    return $this->CI->model_message->create($subject, $owner, $list_id, $published);
  }

  function modify($message_id, $subject, $owner, $list_id, $published, $body_html_input, $reply_to_name, $reply_to_email)
  {
    // var_dump($message_id, $subject, $owner, $list_id, $published, $body_html_input, $reply_to_name, $reply_to_email); die();

    $published = '1000-01-01 00:00:00';
    if ($owner == 'transactional') $published = date('Y-m-d H:m:s');

    if (is_null($result = $this->_process_html($message_id, $body_html_input)))
    {
      return NULL;
    }

    if (empty($reply_to_name)) $reply_to_name = NULL;
    if (empty($reply_to_email)) $reply_to_email = NULL;

    $this->CI->model_message->update(
      $message_id, $subject, $owner, $list_id, $published, $body_html_input, $result['body_html'], $result['body_text'], $reply_to_name, $reply_to_email
    );
    
    return TRUE;
  }

  function archive($message_id, $owner)
  {
    $this->CI->model_message->archive($message_id, $owner);
    return TRUE;
  }

  function unarchive($message_id, $owner)
  {
    $this->CI->model_message->unarchive($message_id, $owner);
    return TRUE;
  }

  function _process_html($message_id, $body_html_input)
  {
    $body_html = $body_html_input;

    // 1. html to text
    $body_text = html_to_text($body_html);

    $dom = HtmlDomParser::str_get_html($body_html);

    // 2. a.target=_blank
    $anchor_url_list = [];
    foreach($dom->find('a') as $anchor)
    {
      $anchor_url_list[] = $anchor->href;
      $anchor->target = '_blank';
    }

    $body_html = $dom->innertext;

    // 3. GA stats
    // [![Analytics](https://ga-beacon.appspot.com/UA-XXXXX-X/your-repo/page-name)](https://github.com/igrigorik/ga-beacon)
    // @todo: campaign vars
    $ga_beacon = [
      'v' => 1, 't' => 'event', 'ec' => 'email', 'ea' => 'open',
      'tid' => getenv('ga'),
      'cid' => random_string('md5'),
      'mid' => $message_id
    ];
    $ga_beacon_html = '<img alt="GA" width="1px" height="1px" src="'.'https://www.google-analytics.com/collect?'.http_build_query($ga_beacon).'">';

    $body_html = str_replace('</body>', $ga_beacon_html.'</body>', $body_html, $replace_count);
    if (!$replace_count) $body_html .= $ga_beacon_html;

    // 4. minify html
    $this->CI->load->library('composer/lib_html_minifier');
    $body_html = $this->CI->lib_html_minifier->process($body_html);

    // 5. inline css
    $this->CI->load->library('composer/lib_css_to_inline');
    $body_html = $this->CI->lib_css_to_inline->convert($body_html);

    // 6. add <title>{_subject}</title>
    $body_html = str_replace('</head>', '<title>{_subject}</title>'.'</head>', $body_html);

    // 7. restore href (since urls are encoded by dom in css inline)
    //    {_unsubscribe_link} => %7B_unsubscribe_link%7D
    $dom = HtmlDomParser::str_get_html($body_html);
    $count = 0;
    foreach($dom->find('a') as $anchor)
    {
      $anchor->href = $anchor_url_list[ $count ];
      $count += 1;
    }

    $body_html = $dom->innertext;

    return compact('body_html', 'body_text');
  }

  function add_request()
  {
    $this->CI->load->library('lib_message_request');

    $message_id = $this->CI->input->post('message_id');

    $to_name = $this->CI->input->post('to_name');

    if (is_null($to_email = valid_email($this->CI->input->post('to_email'))))
    {
      $this->error = ['status' => 401, 'message' => 'invalid email address in to_email'];
      return NULL;
    }

    $pseudo_vars = $this->CI->input->post('pseudo_vars');
    
    if (is_null($request_id = $this->CI->lib_message_request->add(
      $message_id, 'transactional', $to_name, $to_email, $pseudo_vars)))
    {
      $this->error = $this->CI->lib_message_request->get_error_message();
      return NULL;
    }

    return ['request_id' => $request_id];
  }
}