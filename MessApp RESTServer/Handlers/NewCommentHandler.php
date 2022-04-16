<?php

require_once "../Basic/Common.php";
require_once "HTTPServer.php";
require_once "JSONServer.php";
require_once "AbstractHandler.php";
require_once "../Session/Authenticate.php";
require_once "../MessDB/MessDB.php";
require_once "../MessCommentSystem/AddComment.php";


class NewCommentHandler extends Handler
{
    /*
     * Post a new comment to a page.
     */

    // If the requested page doesn't exist, create it
    // Calculate comment parameters, time etc
    // Insert comment to page

    private $messdb;

    function __construct()
    {
    }

    function Handle()
    {
        try
        {
            if(!(isset($_REQUEST["url"]) &&
                isset($_REQUEST["content"]) &&
                isset($_REQUEST["parent"])))
            {
                JSONServer::BadRequest();
            }

            // Using $_REQUEST and not $_POST because of the apache rewrite rule, which renders all POST to GET :(
            $comment_content = $_REQUEST["content"];
            $comment_parent = $_REQUEST["parent"];

            $page_url = $_REQUEST["url"];
            $page_title = $_REQUEST["page_title"];

            if("" == $comment_content)
            {
                // Allow empty comments when debugging, cause it's handy
                if(!(Configuration::DEBUG))
                {
                    JSONServer::BadRequest("Empty comments aren't allowed");
                }
            }

            // Formatting user input for server usage
            if(Configuration::TOP_LEVEL_PARENT_ID == $comment_parent)
            {
                $comment_parent = null;
            }
            if("" == $page_url)
            {
                $page_url = null;
            }
            if("" == $page_title)
            {
                $page_title = null;
            }

            $this->messdb = new MessDB();

            // Get my user from session
            $auth = new Authenticate($this->messdb);
            $user_id = $auth->GetUserID();

            $addcomment = new AddComment($this->messdb, $user_id, $page_url, $page_title, $comment_content, $comment_parent);
            $new_comment_id = $addcomment->AddComment();

            JSONServer::ReturnSuccessResponse(["comment-id" => $new_comment_id,
                                                  "message" => "Comment added successfully"]);
        }
        catch(PDOException $e)
        {
            JSONServer::InternalServerError($exception=$e);
        }
        catch(DatabaseException $e)
        {
            JSONServer::InternalServerError($exception=$e);
        }
        catch(AuthenticationException $e)
        {
            JSONServer::NotLoggedIn();
        }
        catch(CommentSystemInvalidURLException $e)
        {
            // TODO: mark the fact that this page is invalid and you could never read the comments in here.
            // TODO: perhaps do that check (from the client) earlier, when fetching a page's info.
            JSONServer::BadRequest();
        }
        catch(UnexpectedError $e)
        {
            JSONServer::InternalServerError($exception=$e);
        }
        catch(RequestIntegrityException $e)
        {
            JSONServer::BadRequest();
        }
        catch(ValidationException $e)
        {
            JSONServer::BadRequest();
        }
        catch(Exception $e)
        {
            JSONServer::UnknownError($exception=$e);
        }
    }

}