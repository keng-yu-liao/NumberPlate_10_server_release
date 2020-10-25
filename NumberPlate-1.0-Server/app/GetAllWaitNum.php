<?php
include 'SqlManager.php';

$storeTable = $_GET['storeTableName'];

$sqlManager = SqlManager::getInstance();
echo $sqlManager->getAllWaitNum($storeTable);

?>