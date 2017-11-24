<?php

ini_set("display_errors", "On");

include("./functions.php");
include("./Controller.php");
include("./WeixinController.php");

$WxObj = new WeixinController;
$a = isset($_GET['a']) ? $_GET['a'] : 'index';
$WxObj->$a();

?>