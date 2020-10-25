<?php
include 'SqlManager.php';

$storeTableName = $_GET['storeTableName'];
$yourNum = $_GET['yourNum'];
$numIndex = $_GET['numIndex'];

$sqlManager = SqlManager::getInstance();
$sqlManager->compareDoneNum($storeTableName, $yourNum, $numIndex);

?>