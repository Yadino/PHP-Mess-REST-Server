<?php

/*
 * Despite the misleading name, HTTPServer is just a static class that lets you respond with predefined HTTP headers.
 */

require_once "../Basic/Common.php";
require_once "Utils.php";

class HTTPServer
{
    public static $HTTP_RETURN_CODES = array(
        200 => "OK",
        400 => "Bad request",
        401 => "Unauthorized",
        404 => "Not found",
        500 => "Internal Server Error"
    );


    public static function HttpAddHeaders()
    {
        header('Content-Type: text/html; charset=utf-8');
    }

    public static function JavaScriptHeaders()
    {
        header('Content-Type: application/javascript');
    }

    public static function CSSHeaders()
    {
        header('Content-type: text/css');
    }

    public static function ImageHeaders()
    {
        header("Content-Type: image/png");
    }

    public static function Redirect($new_address)
    {
        /*
         * Redirect.
         * new_address: full address including protocol (aka https://)
         */
        header("Location:".$new_address);
        exit();
    }

    public static function InnerRedirect($new_address)
    {
        /*
         * Redirect within the site's domain
         * new_address: only the location for the rest server (for ex. /user/settings etc)
         */
        self::Redirect("https://".Configuration::MESS_SERVER_DOMAIN.$new_address);
    }

    public static function return_json($http_code, $json_response)
    {
        self::return_http_response_code($http_code);
        header("Content-Type: application/json");
        echo $json_response;
        exit();
    }

    public static function return_text($text_response, $http_code=200)
    {
        self::return_http_response_code($http_code);
        echo $text_response;
        exit();
    }

    public static function return_http_response_code($http_code, $custom_message = null)
    {
        $INTERNAL_SERVER_ERR = 500;

        // Check if requested return code exists
        if(!array_key_exists($http_code, self::$HTTP_RETURN_CODES))
        {
            // Internal error
            $http_code = $INTERNAL_SERVER_ERR;
        }
        http_response_code($http_code);
    }


    public static function Success($message=null)
    {
        self::return_http_response_code(200);
        self::PrintMessage($message);
        exit();
    }

    public static function NotFound($message=null, Exception $exception=null)
    {
        self::return_http_response_code(404);
        echo head1("Not Found");

        self::PrintMessage($message);
        self::PrintException($exception);
        self::PrintRequestInfo();

        exit();
    }

    public static function BadRequest($message=null, Exception $exception=null)
    {
        self::return_http_response_code(400);
        echo head1("Bad Request");

        self::PrintMessage($message);
        self::PrintException($exception);
        self::PrintRequestInfo();
        include("html/facepalm.html");

        exit();
    }

    public static function InternalServerError(Exception $exception=null, $message=null)
    {
        self::return_http_response_code(500);
        echo head1("Internal Server Error");

        self::PrintMessage($message);
        self::PrintException($exception);
        self::PrintRequestInfo();

        exit();
    }

    public static function Unauthorized($message="Unauthorized action")
    {
        self::return_http_response_code(401);
        echo head1("Unauthorized");
        self::PrintMessage($message);
        self::PrintRequestInfo();
        exit();
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

    public static function PrintRequestInfo()
    {
        /*
         *  Function for testing.
         */

        if(!Configuration::DEBUG)
        {
            return;
        }

        $request_method = $_SERVER["REQUEST_METHOD"];
        $request_full_uri = strtolower($_SERVER["REQUEST_URI"]);

        // Separate get parameters (if exist) from the REST body
        $request_uri = explode("?", $request_full_uri)[0];
        $request_rest_args = explode("/", trim($request_uri,"/"));
        $request_len = count($request_rest_args);
        $request_rest_key = $request_rest_args[0];

        echo head3("Request info");
        echo "First item is: ".$request_rest_key;
        echo "<br>URI is: ".$request_uri;
        echo "<br>Objects in the URI: ".$request_len;
        echo "<br>--> ";
        print_r($request_rest_args);
        echo "<br> Request method: ".$request_method;
        echo "<br> Info from request: ";
        print_r($_REQUEST);
        echo "<br><br>";
    }

    public static function PrintException($exception)
    {
        /*
         *  Function for testing.
         */

        if(Configuration::DEBUG)
        {
            if (!is_null($exception))
            {
                // Print all of the exception details, for testing, mostly
                echo bold("CODE: ") . $exception->getCode();
                newline(2);
                echo bold("MESSAGE: ") . $exception->getMessage();
                newline(2);
                echo bold("FILE: ") . $exception->getFile();
                newline(2);
                echo bold("TRACE: ") . $exception->getTraceAsString();
                newline(2);
                echo bold("ON FILE: ") . $exception->getFile();
                newline(2);
                echo bold("ON LINE: ") . $exception->getLine();
            }
        }
    }

    private static function PrintMessage($message)
    {
        if(!is_null($message))
        {
            newline();
            echo bold("Message: ").$message;
            newline();
        }
    }
}