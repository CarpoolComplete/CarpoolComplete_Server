<?php
/**
 * Created by PhpStorm.
 * User: jhpassion0621
 * Date: 4/28/16
 * Time: 10:02 AM
 */

define ('EVENT_DETAIL_ALL',          0);
define ('EVENT_DETAIL_FUTURE',       1);
define ('EVENT_DETAIL_THIS_ONLY',    2);

define ('PUSH_KIND_TO_DRIVER',       0);
define ('PUSH_KIND_FROM_DRIVER',     1);
define ('PUSH_KIND_DETAIL',          2);

function addDriver($req, $res, $args) {
    global $db;

    if(validateUserAuthentication($req)) {
        $params = $req->getParams();

        //remove same date driver
        $query = $db->prepare('delete from tblEventDriver where event_driver_event_id = :event_driver_event_id
                                                            and event_driver_driver_type = :event_driver_driver_type
                                                            and event_driver_date = :event_driver_date');
        $query->bindParam(':event_driver_event_id', $args['id']);
        $query->bindParam(':event_driver_driver_type', $params['event_driver_driver_type']);
        $query->bindParam(':event_driver_date', $params['event_driver_date']);
        if($query->execute()) {
            $newRes = insertDriver($args['id'], $params, $res);
        } else {
            $newRes = makeResultResponseWithString($res, 400, $query->errorInfo()[2]);
        }
    } else {
        $newRes = makeResultResponseWithString($res, 401, 'Your token has expired. Please login again.');
    }

    return $newRes;
}

function insertDriver($event_id, $params, $res) {
    global $db;

    $query = $db->prepare('insert into tblEventDriver (event_driver_event_id,
                                                        event_driver_driver_id,
                                                        event_driver_driver_type,
                                                        event_driver_date)
                                              VALUES (:event_driver_event_id,
                                                      :event_driver_driver_id,
                                                      :event_driver_driver_type,
                                                      :event_driver_date)');
    $query->bindParam(':event_driver_event_id', $event_id);
    $query->bindParam(':event_driver_driver_id', $params['event_driver_driver_id']);
    $query->bindParam(':event_driver_driver_type', $params['event_driver_driver_type']);
    $query->bindParam(':event_driver_date', $params['event_driver_date']);
    if($query->execute()) {

        $query = $db->prepare('select * from tblEventDriver where event_driver_event_id = :event_id order by event_driver_date');
        $query->bindParam(':event_id', $event_id);

        if($query->execute()) {
            $block_drivers = $query->fetchAll(PDO::FETCH_ASSOC);
            if($params['event_driver_driver_type']) {
                sendPushToDrivers($event_id, PUSH_KIND_TO_DRIVER, $params);
            } else {
                sendPushToDrivers($event_id, PUSH_KIND_FROM_DRIVER, $params);
            }

            $newRes = makeResultResponseWithObject($res, 200, $block_drivers);
        } else {
            $newRes = makeResultResponseWithString($res, 400, $query->errorInfo()[2]);
        }
    } else {
        $newRes = makeResultResponseWithString($res, 400, $query->errorInfo()[2]);
    }

    return $newRes;
}

function addDetail($req, $res, $args) {
    global $db;

    if(validateUserAuthentication($req)) {
        $params = $req->getParams();

        if($params['event_detail_type'] == EVENT_DETAIL_ALL) {
            //remove all other drivers
            $query = $db->prepare('delete from tblEventDriver where event_driver_event_id = :event_id and event_driver_driver_id <> :event_user_id');
            $query->bindParam(':event_id', $args['id']);
            $query->bindParam(':event_user_id', $params['event_user_id']);
            if($query->execute()) {
                //remove all details
                $query = $db->prepare('delete from tblEventDetail where event_detail_event_id = :event_detail_event_id');
                $query->bindParam(':event_detail_event_id', $args['id']);
                if($query->execute()) {
                    $newRes = insertDetail($args['id'], $params, $res);
                } else {
                    $newRes = makeResultResponseWithString($res, 400, $query->errorInfo()[2]);
                }
            } else {
                $newRes = makeResultResponseWithString($res, 400, $query->errorInfo()[2]);
            }
        } else if($params['event_detail_type'] == EVENT_DETAIL_FUTURE) {
            //remove all future drivers
            $query = $db->prepare('delete from tblEventDriver where event_driver_event_id = :event_id and event_driver_driver_id <> :event_user_id and event_driver_date >= :event_detail_date');
            $query->bindParam(':event_id', $args['id']);
            $query->bindParam(':event_user_id', $params['event_user_id']);
            $query->bindParam(':event_detail_date', $params['event_detail_date']);
            if($query->execute()) {
                $query = $db->prepare('delete from tblEventDetail where event_detail_event_id = :event_detail_event_id and event_detail_date >= :event_detail_date');
                $query->bindParam(':event_detail_event_id', $args['id']);
                $query->bindParam(':event_detail_date', $params['event_detail_date']);
                if($query->execute()) {
                    $newRes = insertDetail($args['id'], $params, $res);
                } else {
                    $newRes = makeResultResponseWithString($res, 400, $query->errorInfo()[2]);
                }
            } else {
                $newRes = makeResultResponseWithString($res, 400, $query->errorInfo()[2]);
            }
        } else {
            //remove all future drivers
            $query = $db->prepare('delete from tblEventDriver where event_driver_event_id = :event_id and event_driver_driver_id <> :event_user_id and event_driver_date = :event_detail_date');
            $query->bindParam(':event_id', $args['id']);
            $query->bindParam(':event_user_id', $params['event_user_id']);
            $query->bindParam(':event_detail_date', $params['event_detail_date']);
            if($query->execute()) {
                //get all events with same date
                $query = $db->prepare('select * from tblEventDetail where event_detail_event_id = :event_detail_event_id
                                                                  and event_detail_date = :event_detail_date');
                $query->bindParam(':event_detail_event_id', $args['id']);
                $query->bindParam(':event_detail_date', $params['event_detail_date']);
                if($query->execute()) {
                    $event_details = $query->fetchAll(PDO::FETCH_ASSOC);
                    foreach($event_details as $event_detail) {
                        if($event_detail['event_detail_type'] == EVENT_DETAIL_ALL
                            || $event_detail['event_detail_type'] == EVENT_DETAIL_FUTURE) {
                            // add one day to created_at
                            $query = $db->prepare('update tblEventDetail set event_detail_date = adddate(event_detail_date, INTERVAL 1 DAY) where event_detail_id = :event_detail_id');
                            $query->bindParam(':event_detail_id', $event_detail['event_detail_id']);
                            $query->execute();
                        } else {
                            // remove that day passenger
                            $query = $db->prepare('delete from tblEventDetail where event_detail_id = :event_detail_id');
                            $query->bindParam(':event_detail_id', $event_detail['event_detail_id']);
                            $query->execute();
                        }
                    }

                    $newRes = insertDetail($args['id'], $params, $res);

                } else {
                    $newRes = makeResultResponseWithString($res, 400, $query->errorInfo()[2]);
                }
            } else {
                $newRes = makeResultResponseWithString($res, 400, $query->errorInfo()[2]);
            }
        }
    } else {
        $newRes = makeResultResponseWithString($res, 401, 'Your token has expired. Please login again.');
    }

    return $newRes;
}

function insertDetail($event_id, $params, $res) {
    global $db;

    $query = $db->prepare('insert into tblEventDetail (event_detail_event_id,
                                                       event_detail_start_at,
                                                       event_detail_end_at,
                                                       event_detail_passengers,
                                                       event_detail_alert_time,
                                                       event_detail_type,
                                                       event_detail_date)
                                              VALUES (:event_detail_event_id,
                                                      :event_detail_start_at,
                                                      :event_detail_end_at,
                                                      :event_detail_passengers,
                                                      :event_detail_alert_time,
                                                      :event_detail_type,
                                                      :event_detail_date)');
    $query->bindParam(':event_detail_event_id', $event_id);
    $query->bindParam(':event_detail_start_at', $params['event_detail_start_at']);
    $query->bindParam(':event_detail_end_at', $params['event_detail_end_at']);
    $query->bindParam(':event_detail_passengers', $params['event_detail_passengers']);
    $query->bindParam(':event_detail_alert_time', $params['event_detail_alert_time']);
    $query->bindParam(':event_detail_type', $params['event_detail_type']);
    $query->bindParam(':event_detail_date', $params['event_detail_date']);
    if($query->execute()) {
        $query = $db->prepare('select * from tblEventDetail where event_detail_event_id = :event_id order by event_detail_date');
        $query->bindParam(':event_id', $event_id);

        if($query->execute()) {
            $block_details = $query->fetchAll(PDO::FETCH_ASSOC);
            sendPushToDrivers($event_id, PUSH_KIND_DETAIL, $params);

            $newRes = makeResultResponseWithObject($res, 200, $block_details);
        } else {
            $newRes = makeResultResponseWithString($res, 400, $query->errorInfo()[2]);
        }
    } else {
        $newRes = makeResultResponseWithString($res, 400, $query->errorInfo()[2]);
    }

    return $newRes;
}

function sendPushToDrivers($event_id, $push_kind, $params) {
    global $db;

    if($push_kind == PUSH_KIND_DETAIL) {
        $date = strtotime($params['event_detail_date']);
    } else {
        $date = strtotime($params['event_driver_date']);
    }

    $weekday = ["Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat"];
    $strDate = $weekday[date('w', $date)] . ', ' . date('M d', $date);

    $query = $db->prepare('select * from viewDriver where event_id = :event_id and driver_user_id <> 0');
    $query->bindParam(':event_id', $event_id);
    if($query->execute()) {
        $event_drivers = $query->fetchAll(PDO::FETCH_ASSOC);

        $query = $db->prepare('select * from viewEvent where event_id = :event_id');
        $query->bindParam(':event_id', $event_id);
        if($query->execute()) {
            $event = $query->fetch(PDO::FETCH_NAMED);

            $query = $db->prepare('select * from tblUser where user_id = :user_id');
            $query->bindParam(':user_id', $params['event_user_id']);
            if($query->execute()) {
                $user = $query->fetch(PDO::FETCH_NAMED);

                if($push_kind == PUSH_KIND_TO_DRIVER) {
                    if($params['event_driver_driver_id'] > 0) {
                        $noti_message = $user['user_first_name'] . ' ' . substr($user['user_last_name'], 0, 1) . '. selected to drive passengers TO ' . $event['event_title'] . ' on ' . $strDate;
                    } else {
                        $noti_message = $event['event_title'] . ' needs a driver TO the event on ' . $strDate;
                    }
                } else if($push_kind == PUSH_KIND_FROM_DRIVER) {
                    if($params['event_driver_driver_id'] > 0) {
                        $noti_message = $user['user_first_name'] . ' ' . substr($user['user_last_name'], 0, 1) . '. selected to drive passengers FROM ' . $event['event_title'] . ' on ' . $strDate;
                    } else {
                        $noti_message = $event['event_title'] . ' needs a driver FROM the event on ' . $strDate;
                    }
                } else {
                    $noti_message = $user['user_first_name'] . ' ' . substr($user['user_last_name'], 0, 1) . '. updated event details in ' . $event['event_title'] . ' on ' . $strDate;
                }

                if($user['user_id'] != $event['event_user_id']) {
                    sendNotification($event['event_user_id'], $noti_message, $event_id, CARPOOL_PUSH_UPDATE_EVENT);
                }
                foreach($event_drivers as $event_driver) {
                    if($user['user_id'] != $event_driver['driver_user_id']
                        && $event_driver['driver_status'] == INVITATION_STATUS_ACCEPT) {
                        sendNotification($event_driver['driver_user_id'], $noti_message, $event_id, CARPOOL_PUSH_UPDATE_EVENT);
                    }
                }
            }
        }
    }
}