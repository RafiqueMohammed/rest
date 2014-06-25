<?php
require_once("common.php");

\Slim\Slim::registerAutoloader();

$app = new \Slim\Slim();

$app->get('/', function () use ($app) {

    $app->status(401);
    $app->response->body(json_encode(array("status" => "no", "result" => "You are not authenticated")));

});


$app->notFound(function () use ($app) {
    $app->halt(404, json_encode(array("status" => "no", "result" => "No Request Found")));
});


$app->get('/test',function(){
    $ar=array("health"=>array("#FFCCBB","#55CCBB","#2211FF"),"relationship"=>array("#1FFCBB","#F9CCBB","#992BCC"),
        "finance"=>array("#FFCC00","#559922","#929b9C"));
    global $app;
    $app->response->body(json_encode($ar));
});

/****************************** LOGIN *********************/
$app->post(
    '/login',
    function () {
        global $app, $DB;
        $result = array();
        $response = $app->response();
        $response['Content-Type'] = 'application/json';
if(isset($_POST['username'],$_POST['password'])&&!empty($_POST['username'])&&!empty($_POST['password'])){
    $headers = apache_request_headers();
    $captcha_token = $headers['Authorization'];

    $username=trim($_POST['username']);
    $password=trim($_POST['password']);

    $m = new MCrypt();
    $verify_token = $m->encrypt($username . $password);
    $p=new  PCrypt();
$password=$p->encrypt($password);
    if ($captcha_token == $verify_token) {
        $qry = $DB->query("SELECT * FROM `".TAB_MEMBER."` as mem INNER JOIN `".TAB_SUBSCRIPTION."` as sub WHERE mem.`email`='$username' AND mem.`password`='$password' AND sub.`mem_id`=mem.`u_id` ORDER BY sub.s_id DESC LIMIT 1") or iDie($app, $DB->error);

        if ($qry->num_rows == 1) {
            $info=$qry->fetch_assoc();
            $mem_id=$info['mem_id'];
$email=$username;
$expiration=$info['expiration_date'];
$activation=$info['activation_date'];
$isPremium=($info['isPremium']=='premium')? 'true':'false';

$AuthToken = bin2hex(openssl_random_pseudo_bytes(64));

        $DB->query("UPDATE `".TAB_MEMBER."` SET `authorization_token`='$AuthToken' WHERE `u_id`='$mem_id'");

            if($DB->affected_rows>0){
                $app->status(200);
                $result['status'] = "ok";
                $result['email']=$email;
                $result['activation'] =$activation;
                $result['expiration'] = $expiration;
                $result['isPremium']=$isPremium;

                $response["Authorization"] = $AuthToken;
            }else{
                $result['status'] = "no";
                $result['result'] = "ERROR #S1020 occured while updating required data";
                $app->status(200);
            }


        } else {
            $result['status'] = "no";
            $result['result'] = "Invalid email/password. Please try again with valid credentials";
            $app->status(200);
        }
        }else{
        $app->status(401);
        $result['status'] = "no";
        $result['result'] = "Verification Token Mismatch. Please Try Again.";
    }
    }else {
        $app->status(200);
        $result['status'] = "no";
        $result['result'] = "Missing Required Data";
    }
        $response->body(json_encode($result));
        $app->response = $response;
    });
/****************************** REGISTRATION *********************/
$app->post(
    '/registration',
    function () {
        global $app, $DB;
        $result = array();
        $response = $app->response();
        $response['Content-Type'] = 'application/json';

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

                    if ($captcha_token == $verify_token) {

    $qry = $DB->query("SELECT `email` FROM `" . TAB_MEMBER . "` WHERE `email`='$email' OR `mobile`='$mobile' ") or iDie($app, $DB->error);

    if ($qry->num_rows == 1) {
        $result['status'] = "no";
        $result['result'] = "Account already registered with this email id or mobile";
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
        $result['isPremium']="false";

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
                        $result['result'] = "Verification Token Mismatch. Please Try Again ";
                    }


                } else {
                    $app->status(200);
                    $result['status'] = "no";
                    $result['result'] = "Required Field Missing";
                };

        $response->body(json_encode($result));
        $app->response = $response;

    }
);


    /***************** FETCH TODAY's PREDICTON ***********************/

$app->post('/fetch/today',function(){

});


/***************** FETCH SPECIFIC DATE PREDICTON ***********************/

$app->post('/fetch/:date',function($date){

});

    /***************** CHECK AUTH TOKEN ******************************/

$app->post('/CheckAuth',function(){

});


$app->hook('slim.before', function () use ($app) {
    global $DB;
    if ($DB->connect_error) {

        $app->halt(503, json_encode(array("status" => "no", "result" => "#SD500 Database server is down. We will be back soon. Inconvenience are regretted")));
    }


});


$app->run();
