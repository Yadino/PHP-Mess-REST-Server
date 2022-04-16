<?php

require_once "../Basic/Common.php";
require_once "HTTPServer.php";
require_once "AbstractHandler.php";
require_once "../Session/Authenticate.php";
require_once "../MessDB/MessDB.php";
require_once "../MessCommentSystem/EditComment.php";

class EditCommentHandler extends Handler
{
    private $comment_id;
    private $new_comment_content_str;

    public function __construct($comment_id, $new_comment_content_str)
    {
        $this->comment_id = $comment_id;
        $this->new_comment_content_str = $new_comment_content_str;
    }

    public function Handle()
    {
        try
        {
            $messdb = new MessDB();

            // Get user id from session
            $auth = new Authenticate($messdb);
            $user_id = $auth->GetUserID();

            $editcomment = new EditComment($messdb);
            $editcomment->EditComment($user_id, $this->comment_id, $this->new_comment_content_str);

            HTTPServer::Success("Comment was updated successfully.");
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
        catch(DatabaseException $e)
        {
            HTTPServer::InternalServerError($exception=$e);
        }
        catch(PDOException $e)
        {
            HTTPServer::InternalServerError($exception=$e);
        }
        catch(Exception $e)
        {
            HTTPServer::UnknownError($exception=$e);
        }
    }
}