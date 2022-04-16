<?php

require_once "../Basic/Common.php";
require_once "AbstractHandler.php";
require_once "../Session/Authenticate.php";
require_once "../Session/Logout.php";

class LogoutHandler extends Handler
{

    public function __construct()
    {
    }

    public function Handle()
    {
        try
        {
            $messdb = new MessDB();
            $auth = new Authenticate($messdb);
            // Throw exception if the user is not logged in.
            $auth->Auth();

            $logout = new Logout($messdb);
            $logout->Logout();
            HTTPServer::return_text("Successfully disconnected.");
        }
        catch(AuthenticationException $e)
        {
            HTTPServer::NotLoggedIn();
        }
        catch(Exception $e)
        {
            HTTPServer::InternalServerError($e,"Error logging out.");
        }
    }

}