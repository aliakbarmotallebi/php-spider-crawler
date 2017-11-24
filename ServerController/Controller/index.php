<?php 
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ini_set('max_execution_time', 1000000000000000000);
set_time_limit(0);

require_once('ControllerComponent.php');
$ControllerComponent= new ControllerComponent();
$ControllerComponent->Request();

?>
