<?php

class HttpResponseManager {

    private static $_instance = null;

    public static function getInstance() {
        if (null === self::$_instance) {
            self::$_instance = new self();
        }

        return self::$_instance;

    }

    public static function sendHttpResponse($statusCode, $data) {
        header("Content-Type" . "application/json");

        $rawData = array(
            'status' => $statusCode,
            'data' => $data);
        $response = json_encode($rawData);

        echo $response;
    }
}

?>