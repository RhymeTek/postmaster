<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Stats extends CI_Controller
{
  public function index()
  {
    $this->load->library('lib_email_status');
    $local_view_data['stats'] = $this->lib_email_status->stats();

    $this->load->library('lib_auth');
    $view_data['is_logged_in'] = $this->lib_auth->is_logged_in();

    $view_data['main_content'] = $this->load->view('stats', $local_view_data, TRUE);
    $this->load->view('base', $view_data);
  }
}