<?php
include 'SqlManager.php';

$tableName = $_GET['tableName'];
$updateLastNum = $_GET['updateLastNum'];
$sqlManager = SqlManager::getInstance();

$sqlManager->updateLastNum($tableName, $updateLastNum);

?>