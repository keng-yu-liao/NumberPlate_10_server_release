<?php 
include 'SqlManager.php';

$storeTableName = $_GET['storeTableName'];
$updateNum = $_GET['updateNum'];
$numIndex = $_GET['numIndex'];

$sqlManager = SqlManager::getInstance();
$sqlManager->updateWaitNum($storeTableName, $updateNum, $numIndex);

?>