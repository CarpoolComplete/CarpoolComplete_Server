<?php
/**
 * Created by PhpStorm.
 * User: jhpassion0621
 * Date: 3/4/16
 * Time: 9:11 AM
 */

defined('BASEPATH') OR exit('No direct script access allowed');

class Error_404 extends CI_Controller {

    public function index()
    {
        $this->output->set_status_header('404');
        $this->load->view('error_404');
    }
}