<?php
include 'SqlManager.php';

$tableName = $_GET['tableName'];
$accountName = $_GET['accountName'];
$sqlManager = SqlManager::getInstance();

$sqlManager->resetStatus($tableName, $accountName);

?>
