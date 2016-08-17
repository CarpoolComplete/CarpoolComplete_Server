<?php
/**
 * Created by PhpStorm.
 * User: jhpassion0621
 * Date: 3/26/16
 * Time: 3:41 PM
 */

function addAdult($req, $res) {
    global $db;

    if(validateUserAuthentication($req)) {
        $params = $req->getParams();

        $query = $db->prepare('insert into tblUser (user_family_id, 
                                                    user_first_name, 
                                                    user_last_name, 
                                                    user_email,
                                                    user_pass)
                                            values(:user_family_id, 
                                                    :user_first_name, 
                                                    :user_last_name, 
                                                    :user_email,
                                                    :user_pass)');

        $query->bindParam(':user_family_id', $params['adult_family_id']);
        $query->bindParam(':user_first_name', $params['adult_first_name']);
        $query->bindParam(':user_last_name', $params['adult_last_name']);
        $query->bindParam(':user_email', $params['adult_email']);
        $query->bindParam(':user_pass', $params['adult_pass']);

        if ($query->execute()) {

            $query = $db->prepare('select * from tblUser where user_id = :user_id');
            $query->bindParam(':user_id', $db->lastInsertId());
            if($query->execute()) {
                $adult = $query->fetch(PDO::FETCH_NAMED);

                // send email to adult user
                $html = '
                        <h1>Carpool Complete</h1>
                        <hr>
                        <br>
                        <h4>Hi, ' . $params['adult_first_name'] .' ' . $params['adult_last_name'] . '!' . '</h4>
                        <h4>I’m using a great new app called Carpool Complete that helps coordinate our kid’s carpools. Download it now and we can easily coordinate our driving tasks. Click <a href = '.APPSTORE_URL.'>here</a> to download it.</h4>
                        <hr>
                        ';
                sendEmail("Carpool Complete", $html, $params['adult_email']);
                $newRes = makeResultResponseWithObject($res, 200, $adult);
            } else {
                $newRes = makeResultResponseWithString($res, 400, $query->errorInfo()[2]);
            }
        } else {
            if($query->errorInfo()[1] == 1062) {
                $newRes = makeResultResponseWithString($res, 400, 'This email is already used in Carpool');
            } else {
                $newRes = makeResultResponseWithString($res, 400, $query->errorInfo()[2]);
            }
        }
    } else {
        $newRes = makeResultResponseWithString($res, 401, 'Your token has expired. Please login again.');
    }

    return $newRes;
}

function deleteAdult($req, $res, $args = []) {
    global $db;

    if(validateUserAuthentication($req)) {

        $query = $db->prepare('delete from tblUser where user_id = :user_id');
        $query->bindParam(':user_id', $args['id']);

        if ($query->execute()) {
            $newRes = makeResultResponseWithString($res, 200, 'Remove adult successfully');
        } else {
            $newRes = makeResultResponseWithString($res, 400, $query->errorInfo()[2]);
        }
    } else {
        $newRes = makeResultResponseWithString($res, 401, 'Your token has expired. Please login again.');
    }

    return $newRes;
}