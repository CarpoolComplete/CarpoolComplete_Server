<?php

/**
 * Created by PhpStorm.
 * User: jhpassion0621
 * Date: 3/4/16
 * Time: 3:43 PM
 */
class Dashboard extends CI_Controller
{
    public function index()
    {
        if($this->session->userdata(LOGIN_KEY)) {
            redirect('dashboard/user/listing');
        } else {
            $this->load->view('admin/login');
        }
    }

    public function user($action_type) {
        if($this->session->userdata(LOGIN_KEY)) {
            $this->load->model('m_User');
            switch($action_type) {
                case ACTION_TYPE_LISTING:
                    $data['aryUsers'] = $this->m_User->getAllUsers();
                    $this->load->view('dashboard/user/user_listing', $data);
                    break;

                case ACTION_TYPE_GOTO_EDIT:
                    $user_id = $this->input->get('user_id');
                    $data['user'] = $this->m_User->getUser($user_id);
                    $this->load->view('dashboard/user/user_view', $data);
                    break;

                case ACTION_TYPE_ACTION_DELETE:
                    $user_id = $this->input->get('user_id');
                    $this->m_User->deleteUser($user_id);
                    redirect('dashboard/user/listing');
                    break;
            }
        } else {
            $this->load->view('admin/login');
        }
    }

    public function broadcast_message($result = 0) {
        if($this->session->userdata(LOGIN_KEY)) {
            $data['result'] = $result;
            $this->load->view('dashboard/message', $data);
        } else {
            $this->load->view('admin/login');
        }
    }
    
    public function documentation() {
        if($this->session->userdata(LOGIN_KEY)) {
            $this->load->view('dashboard/documentation');
        } else {
            $this->load->view('admin/login');
        }
    }

    public function broadcast_message_logic() {
        $this->form_validation->set_rules('message', 'Message', 'trim|required|max_length[180]');

        if($this->form_validation->run()==false){
            //Field validation failed.  User redirected to login page
            redirect('admin/broadcast_message/-1');
        } else {
            $message = $this->input->post('message');
            $this->sendNotificationToMobiles($message);
            redirect('dashboard/broadcast_message/1');
        }
    }

    function getUserEvents() {

        $user_id = $this->input->get('user_id');
        $range_start = $this->input->get('start');
        $range_end = $this->input->get('end');

        $this->load->model('m_User');
        $events = $this->m_User->getUserEvents($user_id, $range_start, $range_end);
        echo json_encode($events);
    }

    function sendNotificationToMobiles($message) {
        $this->load->model('m_User');
        $users = $this->m_User->getAllUsers();

        $user_tokens = [];

        foreach($users as $user) {
            array_push($user_tokens, $user->user_device_token);
        }

        $fields = array(
            'app_id' => ONESIGNAL_APP_ID,
            'include_player_ids' => $user_tokens,
            'ios_badgeType' => 'SetTo',
            'ios_badgeCount' => (int) 1,
            'contents' => array("en" => $message)
        );

        $fields = json_encode($fields);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://onesignal.com/api/v1/notifications");
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Authorization: Basic '.ONESIGNAL_RESTAPI_KEY));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

        $response = curl_exec($ch);
        curl_close($ch);

        return $response;
    }
}