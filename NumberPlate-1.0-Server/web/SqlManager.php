<?php
include "SqlConfig.php";
include "StatusConfig.php";
include_once "HttpResponseManager.php";

class SqlManager {

    private static $_instance = null;
    private static $_connection = null;
    private static $_httpResponseManager = null;
    
    public static function getInstance() {
	    global $hostName, $sqlUser, $sqlPassword, $sqlDatabase;

        if (null === self::$_instance) {
            self::$_instance = new self();
            self::$_connection = mysqli_connect($hostName, $sqlUser, $sqlPassword, $sqlDatabase);
            self::$_httpResponseManager = HttpResponsemanager::getInstance();
        }

        if(self::$_connection) {
            return self::$_instance;
        
        } else {
            die("error: " . mysqli_connect_error($_connection));

        }
    }

    public static function updateLastNum($storeTableName, $updateNum) {
        global $statusSuccess, $statusFail;

        $sqlRequest = "update $storeTableName set last_num = $updateNum where (num_index >= (select max(num_index) from (select * from $storeTableName) as total));";
        $sqlResult = mysqli_query(self::$_connection, $sqlRequest);

        if ($sqlResult) {
            self::$_httpResponseManager->sendHttpResponse($statusSuccess, "");

        } else {
            self::$_httpResponseManager->sendHttpResponse($statusFail, "");

        }

    }

    public static function insertWaitNum($storeTableName, $newWaitNum) {
        global $statusSuccess, $statusFail;

        $sqlRequest = "select wait_num, num_index from $storeTableName order by num_index desc limit 1";
        $sqlResult = mysqli_query(self::$_connection, $sqlRequest);

        if ($sqlResult) {
            $sqlResultRow = mysqli_fetch_assoc($sqlResult);
            $sqlResultRowMax = $sqlResultRow["num_index"] * 10;

            //個數多於10個會再新增一行
            if ($newWaitNum > $sqlResultRowMax) {
		        $sqlRequest = "insert into $storeTableName (wait_num, done_num) values ($newWaitNum, \"\")";
                $sqlResult = mysqli_query(self::$_connection, $sqlRequest);
            
            } else {
                $newWaitNumStr = "";
                if (empty($sqlResultRow["wait_num"])) {
                    $newWaitNumStr = $newWaitNum;

                } else {
                    $newWaitNumStr = $sqlResultRow["wait_num"] . "*" . $newWaitNum;

                }

                $sqlRequest = "update $storeTableName set wait_num=\"$newWaitNumStr\" where (num_index >= (select max(num_index) from (select * from $storeTableName) as total));";
                $sqlResult = mysqli_query(self::$_connection, $sqlRequest);

            }
	    self::$_httpResponseManager->sendHttpResponse($statusSuccess, "");

        } else {
            self::$_httpResponseManager->sendHttpResponse($statusFail, "");

        }

    }

    public static function getLastDoneNum($storeTableName) {
        global $statusSuccess, $statusFail;

        $sqlRequest = "select done_num from store_test_num_status where (num_index >= (select max(num_index) from store_test_num_status));";
        $sqlResult = mysqli_query(self::$_connection, $sqlRequest);
        $sqlResultRow = mysqli_fetch_assoc($sqlResult);
	    $sqlResultRowSplit = explode("*", $sqlResultRow["done_num"]);

        if ($sqlResult) {
            if (empty($sqlResultRow["done_num"])) {
                self::$_httpResponseManager->sendHttpResponse($statusSuccess, "00");

            } else {
                $sqlResultRowArr = array_map(function($value) {
                    return intval($value);
                }, $sqlResultRowSplit);

                self::$_httpResponseManager->sendHttpResponse($statusSuccess, max($sqlResultRowArr));

            }

        } else {
            self::$_httpResponseManager->sendHttpResponse($statusFail, "");

        }

    }

    public static function compareDoneNum($storeTableName, $yourNum, $numIndex) {
        global $statusSuccess, $statusFail;

        $sqlRequest = "select done_num from store_test_num_status where num_index = $numIndex;";
        $sqlResult = mysqli_query(self::$_connection, $sqlRequest);
        $sqlResultRow = mysqli_fetch_assoc($sqlResult);

        if ($sqlResult) {
            if (empty($sqlResultRow["done_num"])) {
                self::$_httpResponseManager->sendHttpResponse($statusSuccess, "no");

            } else {
                $sqlResultRowSplit = explode("*", $sqlResultRow["done_num"]);
                if (in_array($yourNum, $sqlResultRowSplit)) {
                    self::$_httpResponseManager->sendHttpResponse($statusSuccess, "yes");

                } else {
                    self::$_httpResponseManager->sendHttpResponse($statusSuccess, "no");

                }                

            }

        } else {
            self::$_httpResponseManager->sendHttpResponse($statusFail, "");

        }

    }

}

?>
