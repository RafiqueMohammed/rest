<?php
/**
 * Created by Rafique
 * Date: 6/21/14
 * Time: 3:30 PM
 */

require 'common.php';

$n=new Subscription($DB,1);

$n->setToday("12-05-1998");

$c=$n->addDefaultSubscription();
if(!$c){
    echo $n->getError();

}