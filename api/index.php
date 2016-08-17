<?php

// Include the SDK using the Composer autoloader
require 'vendor/autoload.php';
require 'Twilio.php';

// Includes ;
require_once( 'config/database.php' );
require_once( 'controller/base.php' );

$app = new Slim\App();

$app->group('/v1', function() use ($app){
    $app->group('/admins', function() use ($app){
        require_once 'controller/admin.php';
        $app->post('', 'createAdmin');

        $app->group('/{id}', function() use ($app) {
            $app->patch('/update/password', 'updateAdminPassword');
        });
    });

    $app->group('/users', function() use ($app){
        require_once 'controller/user.php';
        $app->post('', 'signup');
        $app->get('/login', 'login');
        $app->get('/forgot/password', 'forgotPassword');

        $app->group('/{id}', function() use ($app) {
            $app->get('', 'getUser');
            $app->get('/invitations', 'getInvitations');
            $app->get('/events', 'getEvents');
            $app->get('/passengers', 'getPassengers');
            $app->post('', 'updateUser');
            $app->patch('/update/password', 'updatePassword');
            $app->patch('/logout', 'logout');
        });
    });

    $app->group('/passengers', function() use ($app){
        require_once 'controller/passenger.php';
        $app->post('', 'createPassenger');

        $app->group('/{id}', function() use ($app) {
            $app->put('', 'updatePassenger');
            $app->delete('', 'deletePassenger');
        });
    });

    $app->group('/adults', function() use ($app){
        require_once 'controller/adult.php';
        $app->post('', 'addAdult');

        $app->group('/{id}', function() use ($app) {
            $app->delete('', 'deleteAdult');
        });
    });

    $app->group('/events', function() use ($app){
        require_once 'controller/event.php';
        $app->post('', 'createEvent');

        $app->group('/{id}', function() use ($app) {
            $app->get('', 'getEvent');
            $app->put('', 'updateEvent');
            $app->delete('', 'deleteEvent');

            require_once 'controller/event_block.php';
            $app->post('/driver', 'addDriver');
            $app->post('/details', 'addDetail');
        });
    });

    $app->group('/invitations', function() use ($app){
        require_once 'controller/invitation.php';
        $app->post('', 'sendInvitations');

        $app->group('/{id}', function() use ($app) {
            $app->patch('', 'updateInvitationStatus');
            $app->delete('', 'deleteDriverInvitation');
        });
    });

    $app->post('/send/code', 'sendVerificationCode');

    $app->any('/docs', 'getAPIDoc');
});

$app->run();

function getAPIDoc($req, $res) {
    $strJson = file_get_contents('docs/swagger.json');

    $newRes = $res->withStatus(200)
        ->withHeader('Content-Type', 'application/json;charset=utf-8')
        ->write($strJson);

    return $newRes;
}

function sendVerificationCode($req, $res) {
    global $db;

    $params = $req->getParams();

    $query = $db->prepare('select * from tblUser where user_phone = :user_phone');
    $query->bindParam(':user_phone', $params['phone_number']);
    if($query->execute()) {
        $users = $query->fetchAll(PDO::FETCH_ASSOC);
        if(count($users) == 0) {
            sendSMS($params['phone_number'], $params['verification_code']);
            $newRes = makeResultResponseWithString($res, 200, 'Verification code sent to your phone');
        } else {
            $newRes = makeResultResponseWithString($res, 409, 'This phone number is already used in Carpool.');
        }
    } else {
        $newRes = makeResultResponseWithString($res, 400, $query->errorInfo()[2]);
    }

    return $newRes;
}
