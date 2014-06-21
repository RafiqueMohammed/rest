<?php
define("DB_HOST","localhost");
define("DB_USER","root");
define("DB_PASS","");
define("DB_NAME","color_prediction");

define("TAB_MEMBER","member_account");
define("TAB_PREDICTION","predictions");
define("TAB_COLOR","color_code");
define("TAB_SUBSCRIPTION","subscription");

$DB=@new mysqli(DB_HOST,DB_USER,DB_PASS,DB_NAME);
