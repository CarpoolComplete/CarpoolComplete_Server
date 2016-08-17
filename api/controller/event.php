<?php
/**
 * Created by PhpStorm.
 * User: jhpassion0621
 * Date: 3/28/16
 * Time: 1:33 PM
 */

function createEvent($req, $res) {
    global $db;

    if(validateUserAuthentication($req)) {
        $params = $req->getParams();

        $query = $db->prepare('insert into tblEvent (event_user_id,
                                                      event_title,
                                                      event_repeat_start_at,
                                                      event_repeat_end_at,
                                                      event_repeat_no_end,
                                                      event_repeat_type,
                                                      event_custom_repeat_dates)
                                              values(:event_user_id,
                                                      :event_title,
                                                      :event_repeat_start_at,
                                                      :event_repeat_end_at,
                                                      :event_repeat_no_end,
                                                      :event_repeat_type,
                                                      :event_custom_repeat_dates)');

        $query->bindParam(':event_user_id', $params['event_user_id']);
        $query->bindParam(':event_title', $params['event_title']);
        $query->bindParam(':event_repeat_start_at', $params['event_repeat_start_at']);
        $query->bindParam(':event_repeat_end_at', $params['event_repeat_end_at']);
        $query->bindParam(':event_repeat_no_end', $params['event_repeat_no_end']);
        $query->bindParam(':event_repeat_type', $params['event_repeat_type']);
        $query->bindParam(':event_custom_repeat_dates', $params['event_custom_repeat_dates']);

        if ($query->execute()) {
            $query = $db->prepare('select * from viewEvent where event_id = :event_id');
            $query->bindParam(':event_id', $db->lastInsertId());
            if($query->execute()) {
                $new_event = $query->fetch(PDO::FETCH_NAMED);

                // send invitation to creator's adult and set status = 1
                $query = $db->prepare('select * from tblUser where user_family_id = :user_family_id and user_id <> :user_id');
                $query->bindParam(':user_family_id', $new_event['event_family_id']);
                $query->bindParam(':user_id', $new_event['event_user_id']);
                if($query->execute()) {
                    $adult = $query->fetch(PDO::FETCH_NAMED);
                    if($adult) {
                        $query = $db->prepare('insert into tblInvitation (
                                                              invitation_event_id,
                                                              invitation_driver_user_id,
                                                              invitation_driver_family_id,
                                                              invitation_driver_first_name,
                                                              invitation_driver_last_name,
                                                              invitation_driver_email,
                                                              invitation_driver_phone,
                                                              invitation_status
                                                              ) values
                                                              (
                                                              :invitation_event_id,
                                                              :invitation_driver_user_id,
                                                              :invitation_driver_family_id,
                                                              :invitation_driver_first_name,
                                                              :invitation_driver_last_name,
                                                              :invitation_driver_email,
                                                              :invitation_driver_phone,
                                                              1
                                                              )');
                        $query->bindParam(':invitation_event_id', $new_event['event_id']);
                        $query->bindParam(':invitation_driver_user_id', $adult['user_id']);
                        $query->bindParam(':invitation_driver_family_id', $adult['user_family_id']);
                        $query->bindParam(':invitation_driver_first_name', $adult['user_first_name']);
                        $query->bindParam(':invitation_driver_last_name', $adult['user_last_name']);
                        $query->bindParam(':invitation_driver_email', $adult['user_email']);
                        $query->bindParam(':invitation_driver_phone', $adult['user_phone']);

                        $query->execute();
                    }

                    $query = $db->prepare('select * from viewDriver where event_id = :event_id');
                    $query->bindParam(':event_id', $new_event['event_id']);

                    if($query->execute()) {
                        $drivers = $query->fetchAll(PDO::FETCH_ASSOC);
                        $new_event['event_drivers'] = $drivers;
                    }
                    
                    $newRes = makeResultResponseWithObject($res, 200, $new_event);

                } else {
                    $newRes = makeResultResponseWithString($res, 400, $query->errorInfo()[2]);
                }
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

function getEvent($req, $res, $args = []) {
    global $db;

    if(validateUserAuthentication($req)) {

        $query = $db->prepare('select * from viewEvent where event_id = :event_id');
        $query->bindParam(':event_id', $args['id']);
        if($query->execute()) {
            $event = $query->fetch(PDO::FETCH_NAMED);

            $query = $db->prepare('select * from viewDriver where event_id = :event_id');
            $query->bindParam(':event_id', $event['event_id']);

            if($query->execute()) {
                $drivers = $query->fetchAll(PDO::FETCH_ASSOC);
                $event['event_drivers'] = $drivers;
            }

            $query = $db->prepare('select * from tblEventDriver where event_driver_event_id = :event_id order by event_driver_date');
            $query->bindParam(':event_id', $event['event_id']);

            if($query->execute()) {
                $block_drivers = $query->fetchAll(PDO::FETCH_ASSOC);
                $event['event_block_drivers'] = $block_drivers;
            }

            $query = $db->prepare('select * from tblEventDetail where event_detail_event_id = :event_id order by event_detail_date');
            $query->bindParam(':event_id', $event['event_id']);

            if($query->execute()) {
                $block_details = $query->fetchAll(PDO::FETCH_ASSOC);
                $event['event_block_details'] = $block_details;
            }

            $newRes = makeResultResponseWithObject($res, 200, $event);

        } else {
            $newRes = makeResultResponseWithString($res, 400, $query->errorInfo()[2]);
        }

    } else {
        $newRes = makeResultResponseWithString($res, 401, 'Your token has expired. Please login again.');
    }

    return $newRes;
}

function updateEvent($req, $res, $args = []) {
    global $db;

    if(validateUserAuthentication($req)) {
        $params = $req->getParams();

        $query = $db->prepare('update tblEvent set event_title = :event_title,
                                                   event_repeat_start_at = :event_repeat_start_at,
                                                   event_repeat_end_at = :event_repeat_end_at,
                                                   event_repeat_no_end = :event_repeat_no_end,
                                                   event_repeat_type = :event_repeat_type,
                                                   event_custom_repeat_dates = :event_custom_repeat_dates,
                                                   event_deleted_dates = :event_deleted_dates
                                where event_id = :event_id');

        $query->bindParam(':event_title', $params['event_title']);
        $query->bindParam(':event_repeat_start_at', $params['event_repeat_start_at']);
        $query->bindParam(':event_repeat_end_at', $params['event_repeat_end_at']);
        $query->bindParam(':event_repeat_no_end', $params['event_repeat_no_end']);
        $query->bindParam(':event_repeat_type', $params['event_repeat_type']);
        $query->bindParam(':event_custom_repeat_dates', $params['event_custom_repeat_dates']);
        $query->bindParam(':event_deleted_dates', $params['event_deleted_dates']);
        $query->bindParam(':event_id', $args['id']);

        if($query->execute()) {

            //remove event block drivers and details on future dates than repeat_end_at
            $query = $db->prepare('delete from tblEventDriver where event_driver_event_id = :event_id and event_driver_created_at > :event_repeat_end_at');
            $query->bindParam(':event_id', $args['id']);
            $query->bindParam(':event_repeat_end_at', $params['event_repeat_end_at']);
            if($query->execute()) {
                $query = $db->prepare('delete from tblEventDetail where event_detail_event_id = :event_id and event_detail_created_at > :event_repeat_end_at');
                $query->bindParam(':event_id', $args['id']);
                $query->bindParam(':event_repeat_end_at', $params['event_repeat_end_at']);
                if($query->execute()) {

                    $query = $db->prepare('select * from viewDriver where event_id = :event_id and driver_user_id <> 0');
                    $query->bindParam(':event_id', $args['id']);
                    if($query->execute()) {
                        $event_drivers = $query->fetchAll(PDO::FETCH_ASSOC);

                        $query = $db->prepare('select * from viewEvent where event_id = :event_id');
                        $query->bindParam(':event_id', $args['id']);
                        if($query->execute()) {
                            $event = $query->fetch(PDO::FETCH_NAMED);

                            $query = $db->prepare('select * from tblUser where user_id = :user_id');
                            $query->bindParam(':user_id', $params['user_id']);
                            if($query->execute()) {
                                $user = $query->fetch(PDO::FETCH_NAMED);

                                $noti_message = $user['user_first_name'] . ' ' . substr($user['user_last_name'], 0, 1) . '. updated his carpool titled, ' . $event['event_title'];
                                if($user['user_id'] != $event['event_user_id']) {
                                    sendNotification($event['event_user_id'], $noti_message, $args['id'], CARPOOL_PUSH_UPDATE_EVENT);
                                }
                                foreach($event_drivers as $event_driver) {
                                    if($user['user_id'] != $event_driver['driver_user_id']) {
                                        sendNotification($event_driver['driver_user_id'], $noti_message, $args['id'], CARPOOL_PUSH_UPDATE_EVENT);
                                    }
                                }
                            }
                        }
                    }

                    $newRes = makeResultResponseWithString($res, 200, 'Event was updated successfully');
                } else {
                    $newRes = makeResultResponseWithString($res, 400, $query->errorInfo()[2]);
                }
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

function deleteEvent($req, $res, $args = []) {
    global $db;

    if(validateUserAuthentication($req)) {
        $params = $req->getParams();

        $query = $db->prepare('select * from viewEvent where event_id = :event_id');
        $query->bindParam(':event_id', $args['id']);
        if($query->execute()) {
            $event = $query->fetch(PDO::FETCH_NAMED);

            $query = $db->prepare('delete from tblEvent where event_id = :event_id');
            $query->bindParam(':event_id', $args['id']);

            if($query->execute()) {
                $query = $db->prepare('select * from viewDriver where event_id = :event_id and driver_user_id <> 0');
                $query->bindParam(':event_id', $args['id']);
                if($query->execute()) {
                    $event_drivers = $query->fetchAll(PDO::FETCH_ASSOC);

                    $query = $db->prepare('select * from tblUser where user_id = :user_id');
                    $query->bindParam(':user_id', $params['user_id']);
                    if($query->execute()) {
                        $user = $query->fetch(PDO::FETCH_NAMED);

                        $noti_message = $user['user_first_name'] . ' ' . substr($user['user_last_name'], 0, 1) . '. deleted his carpool titled, ' . $event['event_title'];
                        if($user['user_id'] != $event['event_user_id']) {
                            sendNotification($event['event_user_id'], $noti_message, $args['id'], CARPOOL_PUSH_REMOVE_EVENT);
                        }
                        foreach($event_drivers as $event_driver) {
                            if($user['user_id'] != $event_driver['driver_user_id']) {
                                sendNotification($event_driver['driver_user_id'], $noti_message, $args['id'], CARPOOL_PUSH_REMOVE_EVENT);
                            }
                        }
                    }
                }
            }

            $query = $db->prepare('delete from tblInvitation where invitation_event_id = :event_id');
            $query->bindParam(':event_id', $args['id']);
            if($query->execute()) {
                $query = $db->prepare('delete from tblEventDriver where event_driver_event_id = :event_id');
                $query->bindParam(':event_id', $args['id']);
                if($query->execute()) {
                    $query = $db->prepare('delete from tblEventDetail where event_detail_event_id = :event_id');
                    $query->bindParam(':event_id', $args['id']);
                    if($query->execute()) {
                        $newRes = makeResultResponseWithString($res, 200, 'Event was deleted successfully');
                    } else {
                        $newRes = makeResultResponseWithString($res, 400, $query->errorInfo()[2]);
                    }
                } else {
                    $newRes = makeResultResponseWithString($res, 400, $query->errorInfo()[2]);
                }
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