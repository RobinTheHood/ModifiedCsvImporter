<?php
namespace RobinTheHood\ModifiedCsvImporter\Classes;

use RobinTheHood\ModifiedCsvImporter\Classes\Task;

define('_VALID_XTC', true);
chdir($_SERVER['DOCUMENT_ROOT']);

require_once 'vendor/autoload.php';
require_once 'includes/application_top.php';

restore_error_handler();
restore_exception_handler();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL ^ E_NOTICE);

$taskId = $_GET['taskId'];
$task = new Task();
$task->pushLog($taskId);
die();
