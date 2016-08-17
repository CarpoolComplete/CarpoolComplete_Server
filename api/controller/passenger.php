<?php
/**
 * Created by PhpStorm.
 * User: jhpassion0621
 * Date: 3/26/16
 * Time: 3:17 PM
 */

function createPassenger($req, $res) {
    global $db;

    if(validateUserAuthentication($req)) {
        $params = $req->getParams();

        $query = $db->prepare('insert into tblPassenger (passenger_family_id, 
                                                          passenger_first_name, 
                                                          passenger_last_name)
                                                  values(:passenger_family_id, 
                                                          :passenger_first_name, 
                                                          :passenger_last_name)');

        $query->bindParam(':passenger_family_id', $params['passenger_family_id']);
        $query->bindParam(':passenger_first_name', $params['passenger_first_name']);
        $query->bindParam(':passenger_last_name', $params['passenger_last_name']);

        if ($query->execute()) {

            $query = $db->prepare('select * from tblPassenger where passenger_id = :passenger_id');
            $query->bindParam(':passenger_id', $db->lastInsertId());
            if($query->execute()) {
                $passenger = $query->fetch(PDO::FETCH_NAMED);
                $newRes = makeResultResponseWithObject($res, 200, $passenger);
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

function updatePassenger($req, $res, $args = []) {
    global $db;

    if(validateUserAuthentication($req)) {
        $params = $req->getParams();

        $query = $db->prepare('select * from tblPassenger where passenger_id = :passenger_id');
        $query->bindParam(':passenger_id', $args['id']);
        if($query->execute()) {
            $old_passenger = $query->fetch(PDO::FETCH_NAMED);

            // make initial name of old passenger
            $old_initial_name = $old_passenger['passenger_first_name'] . ' ' . substr($old_passenger['passenger_last_name'], 0, 1) . '.';

            $query = $db->prepare('update tblPassenger set passenger_first_name = :passenger_first_name,
                                                        passenger_last_name = :passenger_last_name
                               where passenger_id = :passenger_id');

            $query->bindParam(':passenger_first_name', $params['passenger_first_name']);
            $query->bindParam(':passenger_last_name', $params['passenger_last_name']);
            $query->bindParam(':passenger_id', $args['id']);

            if ($query->execute()) {

                $query = $db->prepare('select * from tblPassenger where passenger_id = :passenger_id');
                $query->bindParam(':passenger_id', $args['id']);
                if($query->execute()) {
                    $passenger = $query->fetch(PDO::FETCH_NAMED);

                    $new_initial_name = $passenger['passenger_first_name'] . ' ' . substr($passenger['passenger_last_name'], 0, 1) . '.';

                    // replace old passenger name to new passenger name on tblEventDetail table
                    $query = $db->prepare('update tblEventDetail set event_detail_passengers = REPLACE(event_detail_passengers, :old_initial_name, :new_initial_name)');
                    $query->bindParam(':old_initial_name', $old_initial_name);
                    $query->bindParam(':new_initial_name', $new_initial_name);
                    if($query->execute()) {
                        $newRes = makeResultResponseWithObject($res, 200, $passenger);
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

function deletePassenger($req, $res, $args = []) {
    global $db;

    if(validateUserAuthentication($req)) {

        $query = $db->prepare('select * from tblPassenger where passenger_id = :passenger_id');
        $query->bindParam(':passenger_id', $args['id']);
        if($query->execute()) {
            $old_passenger = $query->fetch(PDO::FETCH_NAMED);
            // make initial name of old passenger
            $old_initial_name = $old_passenger['passenger_first_name'] . ' ' . substr($old_passenger['passenger_last_name'], 0, 1) . '.';

            // remove passenger name on tblEventDetail
            $query = $db->prepare('update tblEventDetail set event_detail_passengers = REPLACE(event_detail_passengers, :old_initial_name, "")');
            $query->bindParam(':old_initial_name', $old_initial_name);
            if($query->execute()) {
                $query = $db->prepare('delete from tblPassenger where passenger_id = :passenger_id');
                $query->bindParam(':passenger_id', $args['id']);

                if ($query->execute()) {
                    $newRes = makeResultResponseWithString($res, 200, 'Remove passenger successfully');
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