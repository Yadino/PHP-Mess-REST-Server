<?php

require_once "../Basic/Common.php";
require_once "HTTPServer.php";
require_once "JSONServer.php";
require_once "AbstractHandler.php";
require_once "../MessDB/MessDB.php";
require_once "../Session/Login.php";

class LoginHandler extends Handler
{

    private $messdb;

    public function __construct()
    {
    }

    public function Handle()
    {
        try
        {
            if(!(isset($_REQUEST["username"]) &&
                isset($_REQUEST["password"])))
            {
                JSONServer::BadRequest();
            }

            $username = $_REQUEST["username"];
            $password = $_REQUEST["password"];

            $this->messdb = new MessDB();

            $login = new Login($this->messdb);
            $login->UserLogin($username, $password);

            JSONServer::Success();
        }
        catch(ValidationException $e)
        {
            JSONServer::BadRequest("Validation failed - input details don't pass validation test.");
        }
        catch(AuthenticationException $e)
        {
            JSONServer::NotFound("Wrong username or password.");
        }
        catch(CookieException $e)
        {
            JSONServer::InternalServerError();
        }
    }
}