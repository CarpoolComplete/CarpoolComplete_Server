<?php
/**
 * Created by PhpStorm.
 * User: jhpassion0621
 * Date: 3/4/16
 * Time: 8:49 AM
 */

defined('BASEPATH') OR exit('No direct script access allowed');

class Admin extends CI_Controller {

    public function index()
    {
        if($this->session->userdata(LOGIN_KEY)) {
            redirect('dashboard');
        } else {
            $this->load->view('admin/login');
        }
    }

    public function login()
    {
        $this->form_validation->set_rules('username', 'Username', 'trim|required');
        $this->form_validation->set_rules('password', 'Password', 'trim|required|callback_admin_check');

        if($this->form_validation->run()==false){
            //Field validation failed.  User redirected to login page
            $this->load->view('admin/login');
        } else {
            redirect('dashboard');
        }
    }

    public function update_password($result = 0) {
        $data['result'] = $result;
        $this->load->view('admin/update_password', $data);
    }

    public function update_password_logic()
    {
        $this->form_validation->set_rules('old_pass', 'Password', 'trim|required');
        $this->form_validation->set_rules('new_pass', 'Password Confirmation', 'trim|required|min_length[8]');
        $this->form_validation->set_rules('confirm_pass', 'Password Confirmation', 'trim|required|matches[new_pass]|callback_admin_password_update');

        if($this->form_validation->run() == false){
            //Field validation failed.  User redirected to login page
            redirect('admin/update_password/-1');
        } else {
            redirect('admin/update_password/1');
        }
    }

    public function admin_check($password) {
        $username = $this->input->post('username');

        $this->load->model('m_Admin');

        $admin = $this->m_Admin->loginAdmin($username, $password);
        if($admin) {
            $this->session->set_userdata(LOGIN_KEY, true);
            $this->session->set_userdata(ADMIN_ID, $admin->admin_id);
            return true;
        } else {
            return false;
        }
    }

    public function admin_password_update($admin_new_pass) {
        $admin_id = $this->session->userdata(ADMIN_ID);
        $admin_old_pass = $this->input->post('old_pass');

        $this->load->model('m_Admin');
        if($this->m_Admin->updateAdminPassword($admin_id, $admin_old_pass, $admin_new_pass)) {
            return true;
        } else {
            return false;
        }
    }

    public function logout() {
        $this->session->set_userdata(LOGIN_KEY, false);

        $this->load->view('admin/login');
    }
}