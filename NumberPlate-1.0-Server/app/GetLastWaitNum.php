<?php
include 'SqlManager.php';

$storeTableName = $_GET['storeTableName'];

$sqlManager = SqlManager::getInstance();
$sqlManager->getLastWaitNum($storeTableName);

?>