<?php
include 'SqlManager.php';

$accountName = $_GET['accountName'];
$sqlManager = SqlManager::getInstance();
$sqlManager->getStartingStatus($accountName);

?>
