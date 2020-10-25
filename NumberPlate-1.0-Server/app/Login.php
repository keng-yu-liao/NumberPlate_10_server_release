<?php
include 'SqlManager.php';

$accountName = $_GET['accountName'];
$accountPassword = $_GET['accountPassword'];

$sqlManager = SqlManager::getInstance();
$sqlManager->login($accountName, $accountPassword);

?>