<?php
/**
 * Created by PhpStorm.
 * User: jhpassion0621
 * Date: 3/29/16
 * Time: 9:16 AM
 */

function sendInvitations($req, $res) {
    global $db;

    if(validateUserAuthentication($req)) {
        $params = $req->getParams();

        $query = $db->prepare('select * from viewEvent where event_id = :event_id');
        $query->bindParam(':event_id', $params['invitation_event_id']);
        if($query->execute()) {
            $event = $query->fetch(PDO::FETCH_NAMED);
        } else {
            $event = [];
        }

        $aryResultDrivers = [];

        $aryInvitations = json_decode($params['invitations'], true);
        foreach($aryInvitations as $invitation) {

            //check this driver is in using app or not
            $query = $db->prepare('select * from tblUser where user_phone = :user_phone');
            $query->bindParam(':user_phone', $invitation['invitation_driver_phone']);
            if ($query->execute()) {
                $user = $query->fetch(PDO::FETCH_NAMED);

                $family_members = [];

                if ($user) {
                    $query = $db->prepare('select * from tblUser where user_family_id = :user_family_id');
                    $query->bindParam(':user_family_id', $user['user_family_id']);
                    if($query->execute()) {
                        $family_members = $query->fetchAll(PDO::FETCH_ASSOC);
                    }
                } else {
                    $user['user_id'] = 0;
                    $user['user_family_id'] = 0;
                    $user['user_first_name'] = $invitation['invitation_driver_first_name'];
                    $user['user_last_name'] = $invitation['invitation_driver_last_name'];
                    $user['user_phone'] = $invitation['invitation_driver_phone'];
                    $user['user_email'] = '';

                    array_push($family_members, $user);
                }

                foreach ($family_members as $family_member) {
                    $query = $db->prepare('insert into tblInvitation (
                                                              invitation_event_id,
                                                              invitation_driver_user_id,
                                                              invitation_driver_family_id,
                                                              invitation_driver_first_name,
                                                              invitation_driver_last_name,
                                                              invitation_driver_email,
                                                              invitation_driver_phone
                                                              ) values
                                                              (
                                                              :invitation_event_id,
                                                              :invitation_driver_user_id,
                                                              :invitation_driver_family_id,
                                                              :invitation_driver_first_name,
                                                              :invitation_driver_last_name,
                                                              :invitation_driver_email,
                                                              :invitation_driver_phone
                                                              )');
                    $query->bindParam(':invitation_event_id', $params['invitation_event_id']);
                    $query->bindParam(':invitation_driver_user_id', $family_member['user_id']);
                    $query->bindParam(':invitation_driver_family_id', $family_member['user_family_id']);
                    $query->bindParam(':invitation_driver_first_name', $family_member['user_first_name']);
                    $query->bindParam(':invitation_driver_last_name', $family_member['user_last_name']);
                    $query->bindParam(':invitation_driver_email', $family_member['user_email']);
                    $query->bindParam(':invitation_driver_phone', $family_member['user_phone']);
                    if($query->execute()) {
                        $query = $db->prepare('select * from viewDriver where driver_invitation_id = :invitation_id');
                        $query->bindParam(':invitation_id', $db->lastInsertId());
                        if($query->execute()) {
                            $driver = $query->fetch(PDO::FETCH_NAMED);
                            array_push($aryResultDrivers, $driver);

                            if($family_member['user_id'] > 0) {
                                if(strlen($family_member['user_device_token']) > 0) {
                                    //send notification to user
                                    $noti_message = $event['event_creator_first_name'] . ' ' . substr($event['event_creator_last_name'], 0, 1) . '. invited you to join the carpool titled, ' . $event['event_title'];
                                    sendNotification($family_member['user_id'], $noti_message, $event['event_id'], CARPOOL_PUSH_SEND_INVITATION);
                                } else if(strlen($family_member['user_phone']) > 0) {
                                    $sms_text = 'Hi '.$family_member['user_first_name'] . ' ' . substr($family_member['user_last_name'], 0, 1) . ', I’m using a great new app called Carpool Complete that helps coordinate our kid’s carpools. Download it now and we can easily coordinate our driving tasks. Click here to download it. ' . APPSTORE_URL;
                                    sendSMS($family_member['user_phone'], $sms_text);
                                }
                            }
                        }
                    }
                }
            }
        }
        $newRes = makeResultResponseWithObject($res, 200, $aryResultDrivers);
    } else {
        $newRes = makeResultResponseWithString($res, 401, 'Your token has expired. Please login again.');
    }

    return $newRes;
}

function updateInvitationStatus($req, $res, $args = []) {
    global $db;

    if(validateUserAuthentication($req)) {
        $params = $req->getParams();

        $query = $db->prepare('select * from tblInvitation where invitation_id = :invitation_id');
        $query->bindParam(':invitation_id', $args['id']);
        if($query->execute()) {
            $invitation = $query->fetch(PDO::FETCH_NAMED);

            $query = $db->prepare('update tblInvitation set invitation_status = :invitation_status 
                                                  where invitation_driver_family_id = :invitation_driver_family_id
                                                  and invitation_event_id = :invitation_event_id');
            $query->bindParam(':invitation_status', $params['invitation_status']);
            $query->bindParam(':invitation_driver_family_id', $invitation['invitation_driver_family_id']);
            $query->bindParam(':invitation_event_id', $invitation['invitation_event_id']);
            if($query->execute()) {
                //send push notification to event creator
                $query = $db->prepare('select * from viewInvitation where invitation_id = :invitation_id');
                $query->bindParam(':invitation_id', $args['id']);
                if($query->execute()) {
                    $invitation = $query->fetch(PDO::FETCH_NAMED);

                    if ($params['invitation_status'] == INVITATION_STATUS_ACCEPT) {
                        $noti_message = $noti_message = $invitation['invitation_driver_first_name'] . ' ' . substr($invitation['invitation_driver_last_name'], 0, 1) . '. has accepted your invitation to join the carpool titled, ' . $invitation['invitation_event_title'];
                        sendNotification($invitation['invitation_event_user_id'], $noti_message, $invitation['invitation_event_id'], CARPOOL_PUSH_INVITATION_ACCEPT);
                    } else {
                        // remove this driver to assigned event
                        $query = $db->prepare('delete from tblEventDriver where event_driver_event_id = :event_id and event_driver_driver_id = :driver_id');
                        $query->bindParam(':event_id', $invitation['invitation_event_id']);
                        $query->bindParam(':driver_id', $invitation['invitation_driver_user_id']);
                        $query->execute();

                        $noti_message = $noti_message = $invitation['invitation_driver_first_name'] . ' ' . substr($invitation['invitation_driver_last_name'], 0, 1) . '. has declined your invitation to join the carpool titled, ' . $invitation['invitation_event_title'];
                        sendNotification($invitation['invitation_event_user_id'], $noti_message, $invitation['invitation_event_id'], CARPOOL_PUSH_INVITATION_REJECT);
                    }
                }

                $newRes = makeResultResponseWithString($res, 200, 'Removed invitation successfully');

            } else {
                $newRes = makeResultResponseWithString($res, 400, $query->errorInfo()[2]);
            }
        } else {
            $newRes = makeResultResponseWithString($res, 400, $query->errorInfo()[2]);
        }

    } else {
        $newRes = makeResultResponseWithString($res, 401, 'Your token has expired. Please login again.');
    }

    return $newRes;
}

function deleteDriverInvitation($req, $res, $args = []) {
    global $db;

    if(validateUserAuthentication($req)) {
        $query = $db->prepare('select * from viewInvitation where invitation_id = :invitation_id');
        $query->bindParam(':invitation_id', $args['id']);
        if($query->execute()) {
            $invitation = $query->fetch(PDO::FETCH_NAMED);
            if($invitation['invitation_driver_user_id'] > 0) {
                // remove this driver to assigned event
                $query = $db->prepare('delete from tblEventDriver where event_driver_event_id = :event_id and event_driver_driver_id = :driver_id');
                $query->bindParam(':event_id', $invitation['invitation_event_id']);
                $query->bindParam(':driver_id', $invitation['invitation_driver_user_id']);
                $query->execute();

                //send push notification to driver
                $query = $db->prepare('select * from tblUser where user_id = :user_id');
                $query->bindParam(':user_id', $invitation['invitation_event_user_id']);
                if($query->execute()) {
                    $user = $query->fetch(PDO::FETCH_NAMED);
                    $noti_message = $user['user_first_name'] . ' ' . substr($user['user_last_name'], 0, 1) . '. has removed you from his carpool titled, ' . $invitation['invitation_event_title'];
                    sendNotification($invitation['invitation_driver_user_id'], $noti_message, $invitation['invitation_event_id'], CARPOOL_PUSH_REMOVE_DRIVER);
                }
            }

            $query = $db->prepare('delete from tblInvitation where invitation_id = :invitation_id');
            $query->bindParam(':invitation_id', $args['id']);
            if($query->execute()) {
                $newRes = makeResultResponseWithString($res, 200, 'Removed invitation successfully');
            } else {
                $newRes = makeResultResponseWithString($res, 400, $query->errorInfo()[2]);
            }
        } else {
            $newRes = makeResultResponseWithString($res, 400, $query->errorInfo()[2]);
        }
    } else {
        $newRes = makeResultResponseWithString($res, 401, 'Your token has expired. Please login again.');
    }

    return $newRes;
}