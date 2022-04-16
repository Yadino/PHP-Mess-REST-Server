<?php

/*
 * Similar to HTTPServer, but sends responses in JSON form. Useful for AJAX requests.
 */

require_once "../Basic/Common.php";
require_once "Utils.php";
require_once "HTTPServer.php";

class JSONServer
{
    public static function ReturnStandardJsonMessage($http_code, $text_status ,$message=null, Exception $exception=null)
    {
        $response = array("status" => $http_code, "text-status" => $text_status,  "message" => $message);
        if(Configuration::DEBUG && $exception != null)
        {
            $response["exception"] = $exception;
        }
        $json = json_encode($response);

        // Use HTTPServer's return_json to send the message and to exit.
        HTTPServer::return_json($http_code, $json);
    }

    public static function ReturnStandardResponse($http_code, $text_status ,$response_params, Exception $exception=null)
    {
        /*
         * Return a rich json response.
         * $response_params : an ARRAY to be encoeded as json and merged with the standard json
         *  of "status", "text-status" etc.
         */
        $response = array("status" => $http_code, "text-status" => $text_status, "response" => $response_params);

        if(Configuration::DEBUG && $exception != null)
        {
            $response["exception"] = $exception;
        }
        $json = json_encode($response);

        // Use HTTPServer's return_json to send the message and to exit.
        HTTPServer::return_json($http_code, $json);
    }

    public static function return_message($message)
    {
        /*
         * Equivalent to HTTPServer::return_text
         */

        self::ReturnStandardJsonMessage(200, "OK", $message);
    }

    public static function ReturnSuccessResponse($response_params)
    {
        self::ReturnStandardResponse(200, "OK", $response_params);
    }

    public static function Success($message=null)
    {
        $http_code = 200;
        $text_status = "OK";
        self::ReturnStandardJsonMessage($http_code, $text_status, $message);
    }

    public static function NotFound($message=null, Exception $exception=null)
    {
        $http_code = 404;
        $text_status = "NOT FOUND";
        self::ReturnStandardJsonMessage($http_code, $text_status, $message, $exception);
    }

    public static function BadRequest($message="Bad request", Exception $exception=null)
    {
        $http_code = 400;
        $text_status = "BAD REQUEST";
        self::ReturnStandardJsonMessage($http_code, $text_status, $message, $exception);
    }

    public static function InternalServerError(Exception $exception=null, $message="Internal Server Error")
    {
        $http_code = 500;
        $text_status = "INTERNAL SERVER ERROR";

        if(Configuration::DEBUG)
        {
            $message = $exception->getMessage();
        }

        self::ReturnStandardJsonMessage($http_code, $text_status, $message, $exception);
    }

    public static function Unauthorized($message="Unauthorized action")
    {
        $http_code = 500;
        $text_status = "UNAUTHORIZED";
        self::ReturnStandardJsonMessage($http_code, $text_status, $message);
    }

    public static function NotLoggedIn()
    {
        self::Unauthorized("Please log in first");
    }

    public static function UnknownError(Exception $exception=null)
    {
        /*
         * Specific case of an Internal Server Error, print unknown string.
         */
        self::InternalServerError($exception, "An unknown error has occurred");
    }
}