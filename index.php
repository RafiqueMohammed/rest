<?php

/**
 * @author Rafique Mohammed
 * @copyright 2014
 */

//echo json_encode(array("status"=>"ok","error"=>false,"result"=>"Successfully tested.."));

if(isset($_SERVER['HTTP_REFERER'])) {
      echo "Refered :".$_SERVER['HTTP_REFERER'];
   }else{
   	echo "Not Refered :".$_SERVER['HTTP_REFERER'];
   }
?>