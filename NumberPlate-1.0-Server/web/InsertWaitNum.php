<?php
include 'SqlManager.php';

$storeTableName = $_GET['storeTableName'];
$insertWaitNum = $_GET['insertWaitNum'];

$sqlManager = SqlManager::getInstance();
$sqlManager->insertWaitNum($storeTableName, $insertWaitNum);

?>
