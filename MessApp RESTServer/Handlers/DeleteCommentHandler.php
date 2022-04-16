<?php

require_once "../Basic/Common.php";
require_once "HTTPServer.php";
require_once "AbstractHandler.php";
require_once "../Session/Authenticate.php";
require_once "../MessDB/MessDB.php";
require_once "../MessCommentSystem/DeleteComment.php";

class DeleteCommentHandler extends Handler
{
    private $comment_id;

    public function __construct($comment_id)
    {
        $this->comment_id = $comment_id;
    }

    public function Handle()
    {
        try
        {
            $messdb = new MessDB();

            $auth = new Authenticate($messdb);
            $user_id = $auth->GetUserID();

            $comment = new DeleteComment($messdb);
            $comment->DeleteComment($this->comment_id, $user_id);

            HTTPServer::Success("Comment deleted.");
        }
        catch(PDOException $e)
        {
            HTTPServer::InternalServerError($exception=$e);
        }
        catch(CommentSystemDeleteCommentException $e)
        {
            HTTPServer::BadRequest("Invalid comment to delete.");
        }
        catch(DatabaseException $e)
        {
            HTTPServer::InternalServerError($exception=$e);
        }
        catch(AuthenticationException $e)
        {
            HTTPServer::NotLoggedIn();
        }
        catch(AccessViolationException $e)
        {
            HTTPServer::Unauthorized();
        }
        catch(CommentSystemCommentDoesntExist $e)
        {
            HTTPServer::BadRequest($exception=$e);
        }
        catch(Exception $e)
        {
            HTTPServer::UnknownError($exception=$e);
        }
    }

}