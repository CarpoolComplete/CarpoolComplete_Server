<?php
/**
 * Created by PhpStorm.
 * User: jhpassion0621
 * Date: 3/30/16
 * Time: 3:28 PM
 */

function createAdmin($req, $res)
{
    global $db;

    $params = $req->getParams();

    $query = $db->prepare('insert into tblAdmin (admin_name, admin_pass)
                            values(:admin_name, HEX(AES_ENCRYPT(:admin_pass, \'' . DB_USER_PASSWORD . '\')))');
    $query->bindParam(':admin_name', $params['admin_name']);
    $query->bindParam(':admin_pass', $params['admin_pass']);
    if($query->execute()) {
        $query = $db->prepare('select * from tblAdmin where admin_id = :admin_id');
        $query->bindParam(':admin_id', $db->lastInsertId());
        if($query->execute()) {
            $admin = $query->fetch(PDO::FETCH_NAMED);
            $newRes = makeResultResponseWithObject($res, 200, $admin);
        } else {
            $newRes = makeResultResponseWithString($res, 400, $query->errorInfo()[2]);
        }
    } else {
        $newRes = makeResultResponseWithString($res, 400, $query->errorInfo()[2]);
    }

    return $newRes;
}

function updateAdminPassword($req, $res, $args = []) {
    global $db;

    $params = $req->getParams();

    $query = $db->prepare('select AES_DECRYPT(UNHEX(admin_pass), \'' . DB_USER_PASSWORD . '\') as admin_pass from tblAdmin where admin_id = :admin_id');
    $query->bindParam(':admin_id', $args['id']);
    if($query->execute()) {
        $result = $query->fetch(PDO::FETCH_NAMED);
        if ($result['admin_pass'] == $params['admin_current_pass']) {
            $query = $db->prepare('update tblAdmin set
                                    admin_pass = HEX(AES_ENCRYPT(:admin_pass, \'' . DB_USER_PASSWORD . '\'))
                                    where admin_id = :admin_id');
            $query->bindParam(':admin_id', $args['id']);
            $query->bindParam(':admin_pass', $params['admin_new_pass']);

            if ($query->execute()) {
                $newRes = makeResultResponseWithString($res, 200, 'Admin password was updated successfully');
            } else {
                $newRes = makeResultResponseWithString($res, 400, $query->errorInfo()[2]);
            }

        } else {
            $newRes = makeResultResponseWithString($res, 400, 'Admin current password is wrong.');
        }
    } else {
        $newRes = makeResultResponseWithString($res, 400, $query->errorInfo()[2]);
    }

    return $newRes;
}