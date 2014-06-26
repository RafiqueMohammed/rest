<?php
/**
 * Created by PhpStorm.
 * User: Rafique
 * Date: 6/21/14
 * Time: 2:44 PM
 */

function iDie($app,$str)
{
    $app->halt(503, json_encode(array("status" => "no", "result" => $str)));
}

function validateDate($date,$format='Y-n-d') //m-leading 0,n-non zero
{
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) == $date;
}