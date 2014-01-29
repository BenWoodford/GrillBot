<?php
require_once("config.php");
require_once("GrillBot.php");

$bot = new GrillBot($config);
$bot->start();
?>
