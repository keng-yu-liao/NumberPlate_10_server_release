<?php
include 'SqlManager.php';

$accountName = $_GET['accountName'];
$updateStatus = $_GET['updateStatus'];

$sqlManager = SqlManager::getInstance();
$sqlManager->updateStartingStatus($accountName, $updateStatus);

?>