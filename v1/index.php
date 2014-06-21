<?php
require_once("common.php");

\Slim\Slim::registerAutoloader();

$app = new \Slim\Slim();

$app->get('/', function () use ($app) {

    $app->status(401);
    $app->response->body(json_encode(array("status" => "no", "result" => "You are not authenticated")));

});


// GET route
$app->get(
    '/register/:type',
    function ($type) {
        switch ($type) {
            case 'login':
                if (isset($_POST['email'], $_POST['password'])) {
                    echo json_encode(array("status" => "ok", "username" => $_POST['email'], "AuthToken" => sha1($_POST['password'])));
                } else {
                    echo json_encode(array("status" => "no", "result" => "Required Field Missing"));
                };
                break;
            case 'registration':
                ;
                break;
        }

    }
);

$app->notFound(function () use ($app) {
    $app->halt(404, json_encode(array("status" => "no", "result" => "No Request Found")));
});

$app->post(
    '/registration/:type',
    function ($type) {
        global $app, $DB;
        $result = array();
        $response = $app->response();
        $response['Content-Type'] = 'application/json';
        switch ($type) {
            case 'add':


                if (isset($_POST['fullname'], $_POST['city'], $_POST['country'], $_POST['email'], $_POST['password'], $_POST['dob'], $_POST['gender'], $_POST['phone']) &&
                    !empty($_POST['fullname']) && !empty($_POST['email']) && !empty($_POST['password']) && !empty($_POST['dob']) && !empty($_POST['gender'])
                    && !empty($_POST['phone']) && !empty($_POST['country']) && !empty($_POST['city'])
                ) {

                    $fullname = trim($_POST['fullname']);
                    $email = trim($_POST['email']);
                    $password = trim($_POST['password']);
                    $dob = trim($_POST['dob']);
                    $gender = trim($_POST['gender']);
                    $mobile = trim($_POST['phone']);
                    $city = trim($_POST['city']);
                    $country = trim($_POST['country']);

                    $headers = apache_request_headers();
                    $captcha_token = $headers['Authorization'];
                    $m = new MCrypt();
                    $verify_token = $m->encrypt($fullname . $email);

                    if (true || $captcha_token == $verify_token) {

                        $qry = $DB->query("SELECT `fullname` FROM `" . TAB_MEMBER . "` WHERE `fullname`='$fullname' ") or iDie($app, $DB->error);

                        if ($qry->num_rows == 1) {
                            $result['status'] = "no";
                            $result['result'] = "Account already exist";
                            $app->status(200);
                        } else {

                            $p = new PCrypt();
                            $password = $p->encrypt($password);
if(validateDate($dob,'d-m-Y')){
    $dob = date("Y-m-d", strtotime($dob));
}else{
    iDie($app,"Invalid Birthday");
    return;
}

                            $AuthToken = bin2hex(openssl_random_pseudo_bytes(64));

                            $DB->query("INSERT INTO `" . TAB_MEMBER . "`(`authorization_token`, `fullname`, `email`, `password`, `dob`, `gender`,`mobile`, `city`, `country`)
VALUES('$AuthToken','$fullname','$email','$password','$dob','$gender','$mobile','$city','$country')") or iDie($app, $DB->error);

                            if ($DB->affected_rows == 1) {
                                $today=date("Y-m-d");
                                $expiration=date("Y-m-d",strtotime("+7 days",time()));
if(validateDate($today)&&validateDate($expiration)){
    $subscribe=new Subscription($DB,$DB->insert_id);
    $subscribe->setToday($today);
    $subscribe->setExpiration($expiration);


    if ($subscribe->addDefaultSubscription()){
        $app->status(201);
        $result['status'] = "ok";
        $result['email']=$email;
        $result['activation'] =$today;
        $result['expiration'] = $expiration;

        $response["Authorization"] = $AuthToken;
    } else {
        iDie($app,"Error occurred while adding subcription");
    }
}else{
    iDie($app,"Invalid Activation or Expiration Date");
}

                            } else {
                                iDie($app, "Error occurred while creating your account");
                            }

                        }


                    } else {
                        $app->status(200);
                        $result['status'] = "no";
                        $result['result'] = "Captcha Verification Mismatch. Please Try Again ";
                    }


                } else {
                    $app->status(200);
                    $result['status'] = "no";
                    $result['result'] = "Required Field Missing";
                };
                break;
            case 'edit':
                ;
                break;

            default:
                $app->status(400);
                $result['status'] = "no";
                $result['result'] = "Invalid type request";

        }
        $response->body(json_encode($result));
        $app->response = $response;

    }
);


$app->hook('slim.before', function () use ($app) {
    global $DB;
    if ($DB->connect_error) {

        $app->halt(503, json_encode(array("status" => "no", "result" => "Database server is down. We will be back soon. Inconvenience are regretted")));
    }


});


$app->run();
