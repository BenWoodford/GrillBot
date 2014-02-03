<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
require_once("config.php");
require_once("GrillBot.php");

$bot = new GrillBot($config);
$bot->start();
?>
