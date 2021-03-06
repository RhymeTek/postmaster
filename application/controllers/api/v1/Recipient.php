<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Recipient extends CI_Controller
{
    function __construct()
    {
        parent::__construct();

        $this->load->helper('api');
        
        $this->load->library('lib_api');
        if (is_null($this->lib_api->check_api_key())) output_error($this->lib_api->get_error_message());

        $this->load->library('lib_recipient');
    }

    /*
    curl -X POST -i http://localhost/postmaster/api/v1/recipient/subscribe/account -d \
    "key=XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX\
    &recipient_id=visitor-ecfa94f6c8ef80696ba6ee7d2434cbf7@suvozit.com\
    &to_name=Shubhajit Saha\
    &to_email=www@suvozit.com\
    &subscribed=2016-02-01 08:17:20"
    */
    public function subscribe($list = NULL)
    {
        $this->load->library('lib_list_unsubscribe');
        $list_unsubscribe = $this->lib_list_unsubscribe->get_by_name($list);

        if (empty($list_unsubscribe))
        {
            output_error(['status' => 404, 'message' => 'list not found']);
        }

        if (is_null($result = $this->lib_recipient->subscribe($list_unsubscribe['list_id'])))
        {
            output_error($this->lib_recipient->get_error_message());
        }

        output($result);
    }

    /*
curl -X POST -i http://localhost/postmaster/api/v1/recipient/unsubscribe/account -d \
"key=XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX\
&recipient_id=visitor-ecfa94f6c8ef80696ba6ee7d2434cbf7@suvozit.com\
&to_name=Shubhajit Saha\
&to_email=www@suvozit.com\
&unsubscribed=2016-02-01 08:17:20"
    */
    public function unsubscribe($list = NULL)
    {
        $this->load->library('lib_list_unsubscribe');
        $list_unsubscribe = $this->lib_list_unsubscribe->get_by_name($list);

        if (empty($list_unsubscribe))
        {
            output_error(['status' => 404, 'message' => 'list not found']);
        }

        if (is_null($result = $this->lib_recipient->unsubscribe($list_unsubscribe['list_id'])))
        {
            output_error($this->lib_recipient->get_error_message());
        }

        output($result);
    }

    /*
curl -X POST -i http://localhost/postmaster/api/v1/recipient/update-metadata/account -d \
"key=XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX\
&recipient_id=visitor-ecfa94f6c8ef80696ba6ee7d2434cbf7@suvozit.com\
&to_name=Shubhajit Saha\
&to_email=www@suvozit.com\
&metadata[username]=suvozit\
&metadata[location]=IN\
&metadata_updated=2016-02-01 08:17:20\
&update_other_lists=1"
    */
    public function update_metadata($list = NULL)
    {
        $this->load->library('lib_list_unsubscribe');
        $list_unsubscribe = $this->lib_list_unsubscribe->get_by_name($list);

        if (empty($list_unsubscribe))
        {
            output_error(['status' => 404, 'message' => 'list not found']);
        }
        
        if (is_null($result = $this->lib_recipient->update_metadata($list_unsubscribe['list_id'])))
        {
            output_error($this->lib_recipient->get_error_message());
        }

        output($result);
    }
}