<?php
/**
 * Created by PhpStorm.
 * User: kevinlee0621
 * Date: 2/4/16
 * Time: 8:29 PM
 */

function signup($req, $res) {
    global $db;

    $params = $req->getParams();
    $files = $req->getUploadedFiles();

    if(isset($files['avatar'])){
        $user_avatar_url = 'Avatar_' . generateRandomString(40) . '.jpeg';
        $files['avatar']->moveTo('../assets/avatar/' . $user_avatar_url);
    } else {
        $user_avatar_url = '';
    }

    $query = $db->prepare('insert into tblUser (user_first_name,
                                                user_last_name,
                                                user_email,
                                                user_pass,
                                                user_phone,
                                                user_avatar_url,
                                                user_device_token,
                                                user_time_zone,
                                                user_device_type) values
                                                (:user_first_name,
                                                :user_last_name,
                                                :user_email,
                                                HEX(AES_ENCRYPT(:user_pass, \'' . DB_USER_PASSWORD . '\')),
                                                :user_phone,
                                                :user_avatar_url,
                                                :user_device_token,
                                                :user_time_zone,
                                                :user_device_type)');

    $query->bindParam(':user_first_name', $params['user_first_name']);
    $query->bindParam(':user_last_name', $params['user_last_name']);
    $query->bindParam(':user_email', $params['user_email']);
    $query->bindParam(':user_pass', $params['user_pass']);
    $query->bindParam(':user_phone', $params['user_phone']);
    $query->bindParam(':user_avatar_url', $user_avatar_url);
    $query->bindParam(':user_device_token', $params['user_device_token']);
    $query->bindParam(':user_time_zone', $params['user_time_zone']);
    $query->bindParam(':user_device_type', $params['user_device_type']);

    if($query->execute()) {

        $query = $db->prepare('select * from tblUser where user_id = :user_id');
        $query->bindParam(':user_id', $db->lastInsertId());
        if($query->execute()) {
            $user = $query->fetch(PDO::FETCH_NAMED);

            $query = $db->prepare('update tblInvitation set invitation_driver_user_id = :invitation_driver_user_id,
                                                        invitation_driver_family_id = :invitation_driver_family_id,
                                                        invitation_driver_first_name = :invitation_driver_first_name,
                                                        invitation_driver_last_name = :invitation_driver_last_name,
                                                        invitation_driver_email = :invitation_driver_email,
                                                        invitation_driver_phone = :invitation_driver_phone
                                                  where invitation_driver_email = :invitation_driver_email
                                                     or invitation_driver_phone = :invitation_driver_phone');
            $query->bindParam(':invitation_driver_user_id', $user['user_id']);
            $query->bindParam(':invitation_driver_family_id', $user['user_id']);
            $query->bindParam(':invitation_driver_first_name', $params['user_first_name']);
            $query->bindParam(':invitation_driver_last_name', $params['user_last_name']);
            $query->bindParam(':invitation_driver_email', $params['user_email']);
            $query->bindParam(':invitation_driver_phone', $params['user_phone']);

            if($query->execute()) {
                $query = $db->prepare('update tblUser set user_family_id = :user_id where user_id = :user_id');
                $query->bindParam(':user_id', $user['user_id']);
                if($query->execute()) {
                    $user['user_family_id'] = $user['user_id'];
                    $newRes = makeResultResponseWithObject($res, 200, getUserInformation($user, false));
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
        if($query->errorInfo()[1] == 1062) {
            $newRes = makeResultResponseWithString($res, 409, 'This email is already used in Carpool');
        } else {
            $newRes = makeResultResponseWithString($res, 400, $query->errorInfo()[2]);
        }
    }

    return $newRes;
}

function login($req, $res) {
    global $db;

    $params = $req->getParams();

    $query = $db->prepare('select * from tblUser where
                            (user_email = :user_email and user_pass = HEX(AES_ENCRYPT(:user_pass, \'' . DB_USER_PASSWORD . '\')))');
    $query->bindParam(':user_email', $params['user_email']);
    $query->bindParam(':user_pass', $params['user_pass']);
    $query->execute();
    $user = $query->fetch(PDO::FETCH_NAMED);
    if($user) {
        $query = $db->prepare('update tblUser set user_device_token = ""
                                            where user_device_token = :user_device_token');
        $query->bindParam(':user_device_token', $params['user_device_token']);
        if($query->execute()) {
            $query = $db->prepare('update tblUser set user_device_token = :user_device_token,
                                                  user_time_zone = :user_time_zone,
                                                  user_device_type = :user_device_type
                               where user_id = :user_id');
            $query->bindParam(':user_device_token', $params['user_device_token']);
            $query->bindParam(':user_time_zone', $params['user_time_zone']);
            $query->bindParam(':user_device_type', $params['user_device_type']);
            $query->bindParam(':user_id', $user['user_id']);
            if($query->execute()) {
                $newRes = makeResultResponseWithObject($res, 200, getUserInformation($user));
            } else {
                $newRes = makeResultResponseWithString($res, 400, $query->errorInfo()[2]);
            }
        } else {
            $newRes = makeResultResponseWithString($res, 400, $query->errorInfo()[2]);
        }
    } else {
        $newRes = makeResultResponseWithString($res, 400, 'Your email or password is invalid');
    }

    return $newRes;
}

function getUserInformation($user, $isLogin = true) {
    $me = [];

    $me['my_access_token'] = createUserAccessToken($user['user_id'], $user['user_email']);
    $me['my_user'] = $user;
    if($isLogin) {
        $me['my_adults'] = getUserAdults($user);
        $me['my_passengers'] = getUserPassengers($user['user_family_id']);
        $me['my_events'] = getUserEvents($user);
    }

    $me['my_invitations'] = getUserInvitations($user['user_id']);

    return $me;
}

function getUserAdults($user) {
    global $db;

    $adults = [];

    $query = $db->prepare('select * from tblUser where user_family_id = :user_family_id and user_id <> :user_id');
    $query->bindParam(':user_family_id', $user['user_family_id']);
    $query->bindParam(':user_id', $user['user_id']);
    if($query->execute()) {
        $adults = $query->fetchAll(PDO::FETCH_ASSOC);
    }

    return $adults;
}

function getPassengers($req, $res, $args) {
    if(validateUserAuthentication($req)) {
        $newRes = makeResultResponseWithObject($res, 200, getUserPassengers($args['id']));
    } else {
        $newRes = makeResultResponseWithString($res, 401, 'Your token has expired. Please login again.');
    }

    return $newRes;
}

function getUserPassengers($user_family_id) {
    global $db;

    $aryPassengers = [];

    $query = $db->prepare('select * from tblPassenger where passenger_family_id = :user_family_id');
    $query->bindParam(':user_family_id', $user_family_id);
    if($query->execute()) {
        $aryPassengers = $query->fetchAll(PDO::FETCH_ASSOC);
    }

    return $aryPassengers;
}

function getInvitations($req, $res, $args) {
    if(validateUserAuthentication($req)) {
        $newRes = makeResultResponseWithObject($res, 200, getUserInvitations($args['id']));
    } else {
        $newRes = makeResultResponseWithString($res, 401, 'Your token has expired. Please login again.');
    }

    return $newRes;
}

function getUserInvitations($user_id) {
    global $db;

    $aryEvents = [];

    $query = $db->prepare('select * from viewEvent where event_id in (select invitation_event_id from tblInvitation where invitation_driver_user_id = :user_id and invitation_status <> -1)
                                                         and DATE(event_repeat_end_at) >= CURDATE()');
    $query->bindParam(':user_id', $user_id);
    if($query->execute()) {
        $events = $query->fetchAll(PDO::FETCH_ASSOC);

        foreach ($events as $event) {
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
                $block_passengers = $query->fetchAll(PDO::FETCH_ASSOC);
                $event['event_block_details'] = $block_passengers;
            }

            array_push($aryEvents, $event);
        }
    }

    return $aryEvents;
}

function getEvents($req, $res, $args) {
    global $db;

    if(validateUserAuthentication($req)) {
        $query = $db->prepare('select * from tblUser where user_id = :user_id');
        $query->bindParam(':user_id', $args['id']);
        if($query->execute()) {
            $user = $query->fetch(PDO::FETCH_NAMED);
            $newRes = makeResultResponseWithObject($res, 200, getUserEvents($user));
        } else {
            $newRes = makeResultResponseWithString($res, 400, $query->errorInfo()[2]);
        }
    } else {
        $newRes = makeResultResponseWithString($res, 401, 'Your token has expired. Please login again.');
    }

    return $newRes;
}

function getUserEvents($user) {
    global $db;

    $aryEvents = [];

    $query = $db->prepare('select * from viewEvent where (event_family_id = :user_family_id
                            or event_id in (select invitation_event_id from tblInvitation where invitation_driver_user_id = :user_id and invitation_status = 1))
                            and DATE(event_repeat_end_at) >= CURDATE()');
    $query->bindParam(':user_id', $user['user_id']);
    $query->bindParam(':user_family_id', $user['user_family_id']);

    if($query->execute()) {
        $events = $query->fetchAll(PDO::FETCH_ASSOC);

        foreach ($events as $event) {
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

            array_push($aryEvents, $event);
        }
    }

    return $aryEvents;

}

function createUserAccessToken($user_id, $user_email) {
    global $db;

    $token_key = base64_encode('CarpoolAccessToken=>Start:'.$user_email.'at'.time().':End');
    $query = $db->prepare('insert into tblToken (token_user_id,
                                                  token_key,
                                                  token_expire_at) values
                                                  (:token_user_id,
                                                  HEX(AES_ENCRYPT(:token_key, \'' . DB_USER_PASSWORD . '\')),
                                                  adddate(now(), INTERVAL 1 MONTH))');
    $query->bindParam(':token_user_id', $user_id);
    $query->bindParam(':token_key', $token_key);

    if($query->execute()) {
        $user_access_token = $token_key;
    } else {
        $user_access_token = $query->errorInfo()[2];
    }

    return $user_access_token;
}

function forgotPassword($req, $res) {
    global $db, $result;

    $params = $req->getParams();

    $query = $db->prepare('select * from tblUser where user_email = :user_email');
    $query->bindParam(':user_email', $params['user_email']);
    if($query->execute()) {
        $users = $query->fetchAll();
        if(count($users) > 0) {
            $user = $users[0];
            if($user['user_id'] == $user['user_family_id']) {   // family creator
                $query = $db->prepare('select AES_DECRYPT(UNHEX(user_pass), \'' . DB_USER_PASSWORD . '\') as user_pass from tblUser where user_id = :user_id');
                $query->bindParam(':user_id', $user['user_id']);
                if($query->execute()) {
                    $result = $query->fetch(PDO::FETCH_NAMED);
                    if($result['user_pass']) {
                        $html = '
                            <h1>Forgot Password</h1>
                            <hr>
                            <br>
                            <h4>Your email : ' . $params['user_email'] . '</h4>
                            <h4>Your Password is ' . $result['user_pass'] . '</h4>
                            <hr>
                            ';
                        sendEmail('Forgot your password', $html, $params['user_email']);

                        $newRes = makeResultResponseWithString($res, 200, 'Sent password to your email');
                    } else {
                        $newRes = makeResultResponseWithString($res, 400, 'Invalid email address');
                    }
                } else {
                    $newRes = makeResultResponseWithString($res, 400, $query->errorInfo()[2]);
                }
            } else {
                $newRes = makeResultResponseWithString($res, 400, 'Your family uses a shared password. The other adult in your household needs to share this password with you.');
            }
        } else {
            $newRes = makeResultResponseWithString($res, 400, 'Invalid email address');
        }
    } else {
        $newRes = makeResultResponseWithString($res, 400, $query->errorInfo()[2]);
    }

    return $newRes;
}

function updateUser($req, $res, $args = []) {
    global $db;

    if(validateUserAuthentication($req)) {
        $params = $req->getParams();
        $files = $req->getUploadedFiles();

        if(isset($files['avatar'])){
            if(isset($params['user_avatar_url'])) {
                unlink('../assets/avatar/'.$params['user_avatar_url']);
            }
            $user_avatar_url = 'Avatar_' . generateRandomString(40) . '.jpeg';
            $files['avatar']->moveTo('../assets/avatar/' . $user_avatar_url);
        } else {
            $user_avatar_url = $params['user_avatar_url'];
        }

        $query = $db->prepare('update tblUser set user_first_name = :user_first_name,
                                                  user_last_name = :user_last_name,
                                                  user_email = :user_email,
                                                  user_phone = :user_phone,
                                                  user_avatar_url = :user_avatar_url
                                where user_id = :user_id');

        $query->bindParam(':user_first_name', $params['user_first_name']);
        $query->bindParam(':user_last_name', $params['user_last_name']);
        $query->bindParam(':user_email', $params['user_email']);
        $query->bindParam(':user_phone', $params['user_phone']);
        $query->bindParam(':user_avatar_url', $user_avatar_url);
        $query->bindParam(':user_id', $args['id']);
        if ($query->execute()) {
            $query = $db->prepare('select * from tblUser where user_id = :user_id');
            $query->bindParam(':user_id', $args['id']);
            if($query->execute()) {
                $user = $query->fetch(PDO::FETCH_NAMED);
                $newRes = makeResultResponseWithObject($res, 200, $user);
            } else {
                $newRes = makeResultResponseWithString($res, 400, $query->errorInfo()[2]);
            }
        } else {
            if($query->errorInfo()[1] == 1062) {
                if($query->errorInfo()[2] == 'user_email_UNIQUE') {
                    $newRes = makeResultResponseWithString($res, 400, 'This email is already used in Carpool');
                } else {
                    $newRes = makeResultResponseWithString($res, 400, 'This phone number is already used in Carpool');
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

function updatePassword($req, $res, $args = []) {
    global $db;

    if(validateUserAuthentication($req)) {
        $params = $req->getParams();

        $query = $db->prepare('select AES_DECRYPT(UNHEX(user_pass), \'' . DB_USER_PASSWORD . '\') as user_pass from tblUser where user_id = :user_id');
        $query->bindParam(':user_id', $args['id']);
        if($query->execute()) {
            $result = $query->fetch(PDO::FETCH_NAMED);
            if ($result['user_pass'] == $params['user_current_pass']) {
                $query = $db->prepare('update tblUser set
                                    user_pass = HEX(AES_ENCRYPT(:user_pass, \'' . DB_USER_PASSWORD . '\'))
                                    where user_id = :user_id');
                $query->bindParam(':user_id', $args['id']);
                $query->bindParam(':user_pass', $params['user_new_pass']);

                if ($query->execute()) {
                    $newRes = makeResultResponseWithString($res, 200, 'Your password was updated successfully');
                } else {
                    $newRes = makeResultResponseWithString($res, 400, $query->errorInfo()[2]);
                }

            } else {
                $newRes = makeResultResponseWithString($res, 400, 'Your current password is wrong.');
            }
        } else {
            $newRes = makeResultResponseWithString($res, 400, $query->errorInfo()[2]);
        }
    } else {
        $newRes = makeResultResponseWithString($res, 401, 'Your token has expired. Please login again.');
    }

    return $newRes;
}

function logout($req, $res, $args = []) {
    global $db;

    $query = $db->prepare('update tblUser set user_device_token = "" where user_id = :user_id');
    $query->bindParam(':user_id', $args['id']);
    if($query->execute()) {
        $newRes = makeResultResponseWithString($res, 200, 'Logged out successfully');
    } else {
        $newRes = makeResultResponseWithString($res, 400, $query->errorInfo()[2]);
    }

    return $newRes;
}

function getUser($req, $res, $args = []) {
    global $db;

    if(validateUserAuthentication($req)) {
        $query = $db->prepare('select * from tblUser where user_id = :user_id');
        $query->bindParam(':user_id', $args['id']);
        if($query->execute()) {
            $user = $query->fetch(PDO::FETCH_NAMED);
            $newRes = makeResultResponseWithObject($res, 200, $user);
        } else {
            $newRes = makeResultResponseWithString($res, 400, $query->errorInfo()[2]);
        }
    } else {
        $newRes = makeResultResponseWithString($res, 401, 'Your token has expired. Please login again.');
    }

    return $newRes;
}

