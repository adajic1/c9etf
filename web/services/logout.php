<?php

// WEBSERVICE auth


header('Content-Encoding: none;');
session_start();
require_once("../../lib/config.php");
require_once("../../lib/webidelib.php");
require_once("../login.php");

session_destroy();

print "Ok.";


?>
