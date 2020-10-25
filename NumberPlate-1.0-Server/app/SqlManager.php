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

    public static function login($accountName, $accountPassword) {
        global $sqlTableAccount, $statusSuccess, $statusFail;
	mysqli_query(self::$_connection, "set names 'utf8'");

        $sqlRequest = "select account_password, store_name, table_name from $sqlTableAccount where account_name = \"$accountName\";";
        $sqlResult = mysqli_query(self::$_connection, $sqlRequest);
        $sqlAffectedRows = mysqli_affected_rows(self::$_connection);    

        if($sqlAffectedRows == 0) {
            self::$_httpResponseManager->sendHttpResponse($statusFail, "");
        
        } else {
            $sqlResultData = mysqli_fetch_assoc($sqlResult);
            $comResult = strcmp($sqlResultData["account_password"], $accountPassword);

            if (!$comResult) {
                $rawData = array(
                    'storeName' => $sqlResultData["store_name"],
                    'storeTableName' => $sqlResultData["table_name"]
                );

                self::$_httpResponseManager->sendHttpResponse($statusSuccess, json_encode($rawData));

            } else {
                self::$_httpResponseManager->sendHttpResponse($statusFail, "");

            }


        }
    }

    public static function getStartingStatus($accountName) {
        global $sqlTableAccount, $statusSuccess, $statusFail;

        $sqlRequest = "select starting_status from $sqlTableAccount where account_name = \"$accountName\";";
        $sqlResult = mysqli_query(self::$_connection, $sqlRequest);
        $sqlAffectedRows = mysqli_affected_rows(self::$_connection);

        if($sqlAffectedRows == 0) {
            self::$_httpResponseManager->sendHttpResponse($statusFail, "");
        
        } else {
            $sqlObj = mysqli_fetch_object($sqlResult);
            self::$_httpResponseManager->sendHttpResponse($statusSuccess, $sqlObj->starting_status);

        }
    }

    public static function updateStartingStatus($accountName, $updateStatus) {
        global $sqlTableAccount, $statusSuccess, $statusFail;

        $sqlRequest = "update $sqlTableAccount set starting_status = \"$updateStatus\" where account_name = \"$accountName\";";
        $sqlResult = mysqli_query(self::$_connection, $sqlRequest);

        if($sqlResult) {
            self::$_httpResponseManager->sendHttpResponse($statusSuccess, "");

        } else {
            self::$_httpResponseManager->sendHttpResponse($statusFail, "");

        }
    }

    public static function resetTable($tableName) {
        global $statusSuccess, $statusFail;

        $sqlRequest = "truncate table $tableName;";
	$sqlResult = mysqli_query(self::$_connection, $sqlRequest);

        if($sqlResult) {
            self::$_httpResponseManager->sendHttpResponse($statusSuccess, "");

        } else {
            self::$_httpResponseManager->sendHttpResponse($statusFail, "");

        }

    }

    public static function resetStartingStatus($accountName) {
        global $statusSuccess, $statusFail, $statusRemoteUnCall, $sqlTableAccount;

        $sqlRequest = "update $sqlTableAccount set starting_status = \"$statusRemoteUnCall\" where account_name = \"$accountName\";";
        $sqlResult = mysqli_query(self::$_connection, $sqlRequest);

        if($sqlResult) {
            self::$_httpResponseManager->sendHttpResponse($statusSuccess, "");

        } else {
            self::$_httpResponseManager->sendHttpResponse($statusFail, "");

        }

    }

    public static function resetStatus($tableName, $accountName) {
        global $statusSuccess, $statusFail, $statusRemoteUnCall, $sqlTableAccount;

        $sqlRequest = "truncate table $tableName;";
        $sqlRequest .= "update $sqlTableAccount set starting_status = \"$statusRemoteUnCall\" where account_name = \"$accountName\";"; 
        $sqlRequest .= "insert into $tableName (wait_num, done_num, last_num) values (\"\", \"\", 0);";

        if (mysqli_multi_query(self::$_connection, $sqlRequest)) {

            while (mysqli_next_result(self::$_connection));
	    
	    if (mysqli_errno(self::$_connection) == 0) {
		self::$_httpResponseManager->sendHttpResponse($statusSuccess, "");

	    } else {
		self::$_httpResponseManager->sendHttpResponse($statusFail, "");

	    }	    

	} else {
            self::$_httpResponseManager->sendHttpResponse($statusFail, "");

        }
    }



    public static function updateWaitNum($storeTableName, $updateNum, $numIndex) {
        global $statusSuccess, $statusFail;

        $sqlRequest = "select wait_num from $storeTableName where num_index = $numIndex;";
        $sqlResult = mysqli_query(self::$_connection, $sqlRequest);

        if ($sqlResult && mysqli_num_rows($sqlResult) > 0) {
            $sqlResultRow = mysqli_fetch_assoc($sqlResult);
            $newWaitNumStr = (new self)->outputNewWaitNum($sqlResultRow["wait_num"], $updateNum);
            (new self)->updateWaitNumSql($storeTableName, $newWaitNumStr, $updateNum, $numIndex);
        
        } else {
            self::$_httpResponseManager->sendHttpResponse($statusFail, "");

        }
    }

    public function outputNewWaitNum($oriWaitNum, $updateNum) {
        $splitArray = explode("*", $oriWaitNum);
	    $newWaitNum = "";

        foreach ($splitArray as $num) {
            if (strcmp($num, $updateNum) > 0 || strcmp($num, $updateNum) < 0) {
		        if (strcmp($newWaitNum, "") != 0) {
		            $newWaitNum = $newWaitNum . "*" . $num;		    

		        } else {
		            $newWaitNum = $num;

		        }	
            }
        }

        return $newWaitNum;
    }

    public function updateWaitNumSql($storeTableName, $newWaitNum, $updateNum, $numIndex) {
        global $statusSuccess, $statusFail;

        $sqlRequest = "update $storeTableName set wait_num = \"$newWaitNum\" where num_index = $numIndex;";
        
        if (mysqli_query(self::$_connection, $sqlRequest)) {
            (new self)->insertDoneNum($storeTableName, $updateNum, $numIndex);

        } else {
            self::$_httpResponseManager->sendHttpResponse($statusFail, "");

        }

    }

    public function insertDoneNum($storeTableName, $insertNum, $numIndex) {
        global $statusSuccess, $statusFail;

        $sqlRequest = "select done_num from $storeTableName where num_index = $numIndex;";
        $sqlResult = mysqli_query(self::$_connection, $sqlRequest);

        if ($sqlResult && mysqli_num_rows($sqlResult) > 0) {
            $sqlResultRow = mysqli_fetch_assoc($sqlResult);
            $newDoneNumStr = (new self)->outputNewDoneNum($sqlResultRow["done_num"], $insertNum);
	    (new self)->updateDoneNumSql($storeTableName, $newDoneNumStr, $numIndex);
        
        } else {
            self::$_httpResponseManager->sendHttpResponse($statusFail, "");

        }

    }

    public function outputNewDoneNum($oriDoneNum, $updateNum) {
        $newDoneNum = "";

        if (strcmp($oriDoneNum, "") == 0) {
            $newDoneNum = $updateNum . "";

        } else {
            $newDoneNum = $oriDoneNum . "*" . $updateNum;

        }

        return $newDoneNum;

    }

    public function updateDoneNumSql($storeTableName, $newDoneNum, $numIndex) {
        global $statusSuccess, $statusFail;

        $sqlRequest = "update $storeTableName set done_num = \"$newDoneNum\" where num_index = $numIndex;";
	$sqlResult = mysqli_query(self::$_connection, $sqlRequest);

        if ($sqlResult) {
            self::$_httpResponseManager->sendHttpResponse($statusSuccess, "");

        } else {
            self::$_httpResponseManager->sendHttpResponse($statusFail, "");

        }

    }

    public static function getAllWaitNum($storeTableName) {
        global $statusSuccess, $statusFail;

	$sqlRequest = "select wait_num from $storeTableName;";
        $sqlResult = mysqli_query(self::$_connection, $sqlRequest);
        $sqlResultCounts = mysqli_num_rows($sqlResult);
	$allWaitNum = "";

        if ($sqlResult && $sqlResultCounts > 0) {
            while ($sqlResultRow = mysqli_fetch_assoc($sqlResult)) {
		        $sqlResultCounts--;                

                if ($sqlResultCounts == 0) {
                    $allWaitNum = $allWaitNum . $sqlResultRow["wait_num"];		    

                } else {
                    $allWaitNum = $allWaitNum . $sqlResultRow["wait_num"] . "*";
                
                }
            }

            self::$_httpResponseManager->sendHttpResponse($statusSuccess, $allWaitNum);

        } else {
            self::$_httpResponseManager->sendHttpResponse($statusFail, "");

        }

        mysqli_free_result($sqlResult);
    }

    public static function getLastWaitNum($storeTableName) {
        global $statusSuccess, $statusFail;

        $sqlRequest = "select last_num from $storeTableName where (num_index >= (select max(num_index) from $storeTableName));";
        $sqlResult = mysqli_query(self::$_connection, $sqlRequest);
        $sqlResultRow = mysqli_fetch_assoc($sqlResult);

	if ($sqlResult) {	
            self::$_httpResponseManager->sendHttpResponse($statusSuccess, $sqlResultRow["last_num"]);            

        } else {
            self::$_httpResponseManager->sendHttpResponse($statusFail, "");

	}

        mysqli_free_result($sqlResult);
    }

    
}

?>
