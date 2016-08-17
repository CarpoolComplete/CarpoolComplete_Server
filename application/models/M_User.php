<?php
/**
 * Created by PhpStorm.
 * User: jhpassion0621
 * Date: 3/10/16
 * Time: 10:58 AM
 */
class M_User extends CI_Model {

    function getAllUsers()
    {
        $query = $this->db->get('viewUser');
        $aryUsers = $query->result();
        return $aryUsers;
    }

    function getUser($user_id) {
        $query = $this->db->get_where('viewUser',["user_id"=>$user_id]);
        $users = $query->result();
        $user = reset($users);
        return $user;
    }

    function deleteUser($user_id) {

        $user = $this->getUser($user_id);

        //remove invitation
        $sql = 'delete from tblInvitation where invitation_event_id in (select event_id from tblEvent where event_user_id = '.$user_id.') or invitation_driver_user_id = '.$user_id;
        $this->db->query($sql);

        //remove event
        $this->db->where('event_user_id', $user_id);
        $this->db->delete('tblEvent');

        //remove passenger
        $this->db->where('passenger_family_id', $user->user_family_id);
        $this->db->delete('tblPassenger');

        //remove user
        $this->db->where('user_id', $user_id);
        $this->db->delete('tblUser');
    }

    function getUserEvents($user_id, $range_start, $range_end) {

        $user = $this->getUser($user_id);
        
        $sql = 'select * from viewEvent where
                  ((event_repeat_start_at<="'.$range_end.'" and event_repeat_end_at>="'.$range_start.'" and event_repeat_type!=0)
                        or event_repeat_type=0
                        or event_custom_repeat_dates!="") and
                  (event_family_id = "'.$user->user_family_id.'"
                        or event_id in (select invitation_event_id from tblInvitation where invitation_driver_user_id = "'.$user_id.'" and invitation_status = 1))';

        $query = $this->db->query($sql);
        $events = $query->result();

        $ret = [];
        foreach($events as $event) {

            $event_repeat_start_obj = new DateTime($event->event_repeat_start_at);
            $event_repeat_end_obj = new DateTime($event->event_repeat_end_at);

            if(!empty($event->event_deleted_dates)) {
                $deleted_dates = explode(',', $event->event_deleted_dates);
            } else {
                $deleted_dates = [];
            }

            $cal_event_default = [
                'id' => $event->event_id,
                'title' => $event->event_title,
                'event_creator_name' => $event->event_creator_first_name.' '.$event->event_creator_last_name,
                'event_creator_avatar_url' => $event->event_creator_avatar_url,
                'event_repeat_type'=>$event->event_repeat_type,
                'event_repeat_end_at'=>$event->event_repeat_end_at,
                'color' =>$this->getRandomColor($event->event_id)
            ];

            $available_dates = [];


            /* event_repeat_type
                0: No repeat
                1: Everyday
                2: Every Weekday
                3: Weekly
                4: Every Other Week
                5: Every Month
                6: Custom
            */
            switch($event->event_repeat_type) {
                case 0:
                {
                    if(!$this->isDeletedEvent($event_repeat_start_obj, $deleted_dates)) {
                        $available_dates[] = $this->getEventDetail($event->event_id, $event_repeat_start_obj);
                    }
                    break;
                }
                case 1:
                {
                    for($i = $event_repeat_start_obj; $i <= $event_repeat_end_obj; $i->modify('+1 day')) {
                        if(!$this->isDeletedEvent($i, $deleted_dates)) {
                            $available_dates[] = $this->getEventDetail($event->event_id, $i);
                        }
                    }
                    break;
                }
                case 2:
                {
                    for($i = $event_repeat_start_obj; $i <= $event_repeat_end_obj; $i->modify('+1 day')){
                        if(!$this->isWeekend($i) && !$this->isDeletedEvent($i, $deleted_dates)) {
                            $available_dates[] = $this->getEventDetail($event->event_id, $i);
                        }
                    }
                    break;
                }
                case 3:
                {
                    for($i = $event_repeat_start_obj; $i <= $event_repeat_end_obj; $i->modify('+7 day')) {
                        if(!$this->isDeletedEvent($i, $deleted_dates)) {
                            $available_dates[] = $this->getEventDetail($event->event_id, $i);
                        }
                    }
                    break;
                }
                case 4:
                {
                    for($i = $event_repeat_start_obj; $i <= $event_repeat_end_obj; $i->modify('+14 day')) {
                        if(!$this->isDeletedEvent($i, $deleted_dates)) {
                            $available_dates[] = $this->getEventDetail($event->event_id, $i);
                        }
                    }
                    break;
                }
                case 5:
                {
                    for($i = $event_repeat_start_obj; $i <= $event_repeat_end_obj; $i->modify('+1 month')) {
                        if(!$this->isDeletedEvent($i, $deleted_dates)) {
                            $available_dates[] = $this->getEventDetail($event->event_id, $i);
                        }
                    }
                    break;
                }
                case 6:
                {
                    if(!empty($event->event_custom_repeat_dates)) {
                        $custom_dates = explode(',', $event->event_custom_repeat_dates);
                        foreach($custom_dates as $i) {
                            try {
                                $dt = new DateTime($i.' '.$event_repeat_start_obj->format('H:i:s'));
                                if(!$this->isDeletedEvent($dt, $deleted_dates)) {
                                    $available_dates[] = $this->getEventDetail($event->event_id, $dt);
                                }
                            } catch(Exception $ex) {
                            }
                        }
                    }
                    break;
                }

            }

            foreach($available_dates as $one) {

                $cal_event = $cal_event_default;
                $cal_event['start'] = $one->event_detail_start_at;
                $cal_event['end'] = $one->event_detail_end_at;
                $cal_event['event_alert_time'] = $this->getAlertString(abs($one->event_detail_alert_time));
                $cal_event['event_passengers'] = $one->event_detail_passengers;
                $ret[] = $cal_event;
            }

        }

        return $ret;
    }

    function getEventDetail($event_id, $displayDate) {
        //get passengers
        $sql = 'select * from tblEventDetail where event_detail_event_id = '.$event_id.' order by event_detail_date';
        $query = $this->db->query($sql);
        $event_details = $query->result();

        $event_detail = [];
        foreach($event_details as $tmp_detail) {
            $date = new DateTime($tmp_detail->event_detail_date);
            if($date > $displayDate) {
                break;
            }

            if($tmp_detail->event_detail_type == EVENT_DETAIL_ALL
            || $tmp_detail->event_detail_type == EVENT_DETAIL_FUTURE) {
                $event_detail = $tmp_detail;
            } else if($date == $displayDate) {
                $event_detail = $tmp_detail;
            }
        }

        $event_start_at = new DateTime($displayDate->format('Y-m-d').' '.$event_detail->event_detail_start_at);
        $event_end_at = new DateTime($displayDate->format('Y-m-d').' '.$event_detail->event_detail_end_at);
        $event_detail->event_detail_start_at = $event_start_at->format('Y-m-d H:i:s');
        $event_detail->event_detail_end_at = $event_end_at->format('Y-m-d H:i:s');

        return $event_detail;
    }

    function isWeekend(DateTime $dt) {
        $weekDay = $dt->format('w');
        return ($weekDay == 0 || $weekDay == 6);
    }

    function isDeletedEvent(DateTime $dt, $deleted_dates) {
        return in_array($dt->format('Y-m-d'), $deleted_dates);
    }

    function addDate(DateTime $dt, DateInterval $diff) {
        $new_dt = clone $dt;
        $new_dt->add($diff);
        return $new_dt;
    }

    public function getRandomColor($id) {

        $default_colors = array(
            '#5bc0de','#5F9EA0','#A52A2A','#BDB76B','#556B2F','#8FBC8F','#2F4F4F','#00BFFF','#DAA520','#CD5C5C','#F08080','#ADD8E6','#FFB6C1','#FFA07A','#20B2AA',
            '#3CB371','#C71585','#6B8E23','#DB7093','#CD853F','#FA8072','#8B4513','#2E8B57','#F5DEB3','#9ACD32','#87CEFA','#778899','#B0C4DE','#BA55D3','#4682B4');

        $color_index = $id % 30;
        $color = $default_colors[$color_index];

        return $color;
    }

    function getAlertString($alert_time) {
        if($alert_time >= 3600) {
            $hour = (int)$alert_time / 3600;
            return $hour . ' hours before';
        } else if($alert_time == 0) {
            return '';
        } else {
            $min = (int)$alert_time / 60;
            return $min . ' minutes before';
        }
    }
}