<?php

require_once "../Basic/Common.php";
require_once "HTTPServer.php";
require_once "JSONServer.php";
require_once "RestHandlerTable.php";

/*
 * Class MessRESTServer
 * This is the app's REST server.
 * Every request that comes through the apache server will arrive in this page, regardless of it's URI.
 * For every sort of request, the rest server will know how to extract the right parameters before calling the appropriate handler.
 * Every request should have it's own handling function within the REST Server class.
 */

Class MessRESTServer
{
    private $request_method;
    private $request_uri;
    private $request_rest_key;
    private $request_rest_args;
    private $request_len;

    // REST_HANDLER_TABLE from RestHandlerTable.php.
    private static $REST_HANDLER_TABLE = REST_HANDLER_TABLE;

    public function __construct()
    {
        $this->request_method = $_SERVER["REQUEST_METHOD"];
        $this->request_full_uri = strtolower(urldecode($_SERVER["REQUEST_URI"]));

        // Separate get parameters (if exist) from the REST body
        $this->request_uri = explode("?", $this->request_full_uri)[0];
        $this->request_rest_args = explode("/", trim($this->request_uri,"/"));
        $this->request_len = count($this->request_rest_args);
        $this->request_rest_key = $this->request_rest_args[0];

        //$this->Start();
    }

    public function Start()
    {
        /*
         *  Basically this class's main.
         */

        try
        {
            //HTTPServer::HttpAddHeaders();
            $this->SortRequests();
        }
        catch(Exception $e)
        {
            // Catch all missed and unexpected errors
            HTTPServer::UnknownError($exception=$e);
        }

    }

    private function SortRequests()
    {
        /*
         * Smart-sort the rest request, evaluating full path, request method, and number of parameters.
         */

        $flag_wrong_param = false;
        foreach(self::$REST_HANDLER_TABLE as $rest_path)
        {
            $argc = $this->request_len - $rest_path["path_len"];
            // If path DOESN'T equal the given path with or without "/"
            if(!($this->request_uri == $rest_path["path"] || $this->request_uri == $rest_path["path"]."/"))
            {
                // if the request uri DOESN'T start with the right rest path
                if (strpos($this->request_uri, $rest_path["path"]."/") !== 0)
                {
                    // No match, keep looking
                    continue;
                }
            }
            if($this->request_method != $rest_path["method"])
            {
                // No match, keep looking
                continue;
            }
            if(($argc >= $rest_path["min_args"] && $argc <= $rest_path["max_args"]))
            {
                $handler_function = $rest_path["handler"];
                if(method_exists($this, $handler_function))
                {
                    // TODO: calculate the position of parameters on the uri and call the handler with parameters

                    // If there are no args then the array will come out empty
                    $args = array_slice($this->request_rest_args, $rest_path["path_len"]);
//                    $argc = count($args);

                    // Call function from string by name
                    $this->{$handler_function}($args, $argc);
                    return;
                }
                else
                {
                    // Throw error, how come there's a function in the arr that doesnt exist?
                    HTTPServer::return_text("Handler for this key doesn't exist yet", 500);
                    //return;
                }
            }
            else
            {
                // wrong parameters for request
                // raise wrong parameter flag and keep going, maybe it will match a deeper path
                // (for example something matching /boring/soccer can still match /boring/soccer/en with other params)
                $flag_wrong_param = true;
                continue;
            }
        }

        if($flag_wrong_param)
        {
            HTTPServer::NotFound("Wrong parameters.");
        }
        HTTPServer::NotFound();
    }


    /*
     * HANDLER FUNCTIONS
     * Dynamic requests - extracting vars for the handler classes.
     * Static html pages - loading the page.
     */
    private function Handle_Test()//array $args)
    {
        /*
         * Call the test script, obviously for testing purposes.
         */

        include("../../../tmps/test_stuff.php");
        HTTPServer::return_http_response_code(200);
    }

    private function Handle_IndexPage()
    {
//        include("html/index.html");
        include("../Website/Frontpage/Frontpage.php");
        HTTPServer::return_http_response_code(200);
    }

    private function Handle_GetPageComments()
    {
        /*
         * Get a page's comments.
         */

        require_once "Handlers/PageCommentsHandler.php";
        $handler = new PageCommentsHandler();
        $handler->Handle();
    }

    private function Handle_PostPageComment()
    {
        /*
         * Post a new comment to a page.
         */
        require_once "Handlers/NewCommentHandler.php";
        $handler = new NewCommentHandler();
        $handler->Handle();
    }

    private function Handle_GetPageUserInfo()
    {
        /*
         * Get the all of the requesting user's votes on a specific page.
         */

        if(!(isset($_GET["url"])))
        {
            HTTPServer::BadRequest();
        }

        $page_url = $_GET["url"];

        require_once "Handlers/PageInfoHandler.php";
        $handler = new PageInfoHandler($page_url);
        $handler->Handle();
    }

    private function Handle_CommentDelete(Array $args, int $argc)
    {
        $comment_id = $args[0];

        require_once "Handlers/DeleteCommentHandler.php";
        $handler = new DeleteCommentHandler($comment_id);
        $handler->Handle();
    }

    private function Handle_PageVote(Array $args, int $argc)
    {
        // The path is: /page/vote/upvote, /page/vote/downvote, /page/vote/delete
        // NOTE: Using POST instead of PUT because PHP can't read PUT parameters.

        if(!(isset($_REQUEST["url"])))
        {
            HTTPServer::BadRequest();
        }

        $page_url = $_REQUEST["url"];
        $action = $this->request_rest_args[2];
        $page_title = null;
        if(isset($_REQUEST["page_title"]))
        {
            $page_title = $_REQUEST["page_title"];
        }

        require_once "Handlers/PageVoteHandler.php";
        $handler = new PageVoteHandler($page_url, $page_title, $action);
        $handler->Handle();
    }

    private function Handle_CommentVote(Array $args, int $argc)
    {
        /*
         * Update a comment with a comment action, such as an Upvote, Downvote and Report.
         */
        // TODO: Use the same handler for upvote downvote and delete.

        $comment_id = $args[0];

        if(2 == $argc)
        {
            switch ($args[1])
            {
                // path: /comment/[comment_id]/upvote
                case "upvote":
                    $this->Handle_CommentUpvote($comment_id);
                    break;

                // path: /comment/[comment_id]/downvote
                case "downvote":
                    $this->Handle_CommentDownvote($comment_id);
                    break;

                default:
                    HTTPServer::BadRequest();
            }
        }
        elseif(3 == $argc)
        {
            // Delete a vote
            if($args[2] == "delete")
            {
                // path: /comment/[comment_id]/upvote/delete
                //       /comment/[comment_id]/downvote/delete
                if($args[1] == "downvote" || $args[1] == "upvote")
                {
                    $this->Handle_CommentDeleteVote($comment_id);
                }
            }
        }
        else
        {
            HTTPServer::BadRequest();
        }
    }

    private function Handle_CommentUpvote($comment_id)
    {
        // the right path is /comment/[comment_id]/upvote

        require_once "Handlers/UpvoteHandler.php";
        $handler = new UpvoteHandler($comment_id);
        $handler->Handle();
    }

    private function Handle_CommentDownvote($comment_id)
    {
        // the right path is /comment/[comment_id]/downvote

        require_once "Handlers/DownvoteHandler.php";
        $handler = new DownvoteHandler($comment_id);
        $handler->Handle();
    }

    private function Handle_CommentDeleteVote($comment_id)
    {
        // the right path is /comment/[comment_id]/upvote/delete,
        //                   /comment/[comment_id]/downvote/delete

        require_once "Handlers/DeleteVoteHandler.php";
        $handler = new DeleteVoteHandler($comment_id);
        $handler->Handle();
    }

    private function Handle_CommentEditContent(Array $args, int $argc)
    {
        // User request to edit a comment
        // path: /comment/[comment_id]

        $comment_id = $args[0];

        if((1 != $argc) ||  !(isset($_REQUEST["content"])))
        {
            HTTPServer::BadRequest();
        }

        $comment_content = $_REQUEST["content"];

        require_once "Handlers/EditCommentHandler.php";
        $handler = new EditCommentHandler($comment_id, $comment_content);
        $handler->Handle();
    }

    private function Handle_Comment($comment_id)
    {
        // Return either a single comment or a comment tree that is under said comment.
        // TODO: implement
    }

    private function Handle_Signup()
    {
        require_once "Handlers/SignUpHandler.php";
        $handler = new SignUpHandler();
        $handler->Handle();
    }

    private function Handle_Login()
    {
        require_once "Handlers/LoginHandler.php";
        $handler = new LoginHandler();
        $handler->Handle();
    }

    private function Handle_Logout()
    {
        require_once "Handlers/LogoutHandler.php";
        $handler = new LogoutHandler();
        $handler->Handle();
    }


    private function Handle_SessionUserName()
    {
        /*
         * Return the userID of the user in session.
         * If not logged in - return 404.
         */

        require_once "Handlers/UserNameHandler.php";
        $handler = new UserNameHandler();
        $handler->Handle();
    }

    private function Handle_UserAction(array $args, int $argc)
    {
        /*
         * takes care of /user/... POST requests
         */
        $username = $args[0];

        if(2 == $argc)
        {
            // PATH: /user/[username]/follow
            if($args[1] == "follow")
            {
                $this->Handle_UserFollow($username);
                return;
            }
            // PATH: /user/[username]/unfollow
            elseif($args[1] == "unfollow")
            {
                $this->Handle_UserUnfollow($username);
                return;
            }
            else
            {
                HTTPServer::BadRequest();
            }
        }
        else
        {
            HTTPServer::BadRequest();
        }
    }

    private function Handle_UserFollow($username)
    {
        // the right path is /user/[username]/follow

        require_once "Handlers/UserFollowHandler.php";

        $handler = new UserFollowHandler($username);
        $handler->Handle();
    }

    private function Handle_UserUnfollow($username)
    {
        // the right path is /user/[username]/unfollow

        require_once "Handlers/UserUnfollowHandler.php";

        $handler = new UserUnfollowHandler($username);
        $handler->Handle();
    }

    private function Handle_PullNotifications()
    {
        require_once "Handlers/PullNotificationsHandler.php";

        $handler = new PullNotificationsHandler();
        $handler->Handle();
    }

    private function Handle_NotificationNumber()
    {
        require_once "Handlers/NotificationNumberHandler.php";

        $handler = new NotificationNumberHandler();
        $handler->Handle();
    }

    private function Handle_ReadNotifications(Array $args, int $argc)
    {
        /*
         * Mark notification as read.
         */
        // the right path is /notifications/read/[notification]

        $notif_id = $args[0];

        require_once "Handlers/ReadNotificationHandler.php";

        $handler = new ReadNotificationHandler($notif_id);
        $handler->Handle();
    }

    private function Handle_SitePageComments(Array $args, int $argc)
    {
        include("../Website/PageComments/PageComments.php");
    }

    private function Handle_SiteSearch(Array $args, int $argc)
    {
        // The right path: /search?s=[term]

        include("../Website/SearchPage/SearchPage.php");
    }

    private function Handle_Search(Array $args, int $argc)
    {
        // The right path: /search/ajax?s=[term]

        require_once "Handlers/SearchHandler.php";

        $handler = new SearchHandler();
        $handler->Handle();
    }

    private function Handle_SiteHashtag(Array $args, int $argc)
    {
        // The right path: /hashtag/[hashtag]

        $incparam_hashtag_name = $args[0];
        include("../Website/HashtagPage/HashtagPage.php");
    }

    private function Handle_SiteSitePage(Array $args, int $argc)
    {
        $incparam_site_domain = $args[0];
        include("../Website/SitePage/SitePage.php");
    }

    private function Handle_SiteThread(Array $args, int $argc)
    {
        // The right path: /thread/[comment_id]

        $incparam_comment_id = $args[0];

        try
        {
            include("../Website/ThreadPage/ThreadPage.php");
        }
        catch(DatabaseEmptyResultException $e)
        {
            HTTPServer::NotFound("Comment not found");
        }
    }

    private function Handle_SiteLogin(Array $args, int $argc)
    {
        include("../Website/Session/UserLogin.php");
    }

    private function Handle_SiteAbout(Array $args, int $argc)
    {
        include(__DIR__ . "/../Website/StaticPages/About.php");
    }

    private function Handle_SiteContact(Array $args, int $argc)
    {
        include(__DIR__ . "/../Website/StaticPages/Contact.php");
    }

    private function Handle_SiteExtension(Array $args, int $argc)
    {
        include(__DIR__ . "/../Website/StaticPages/DownloadExtension.php");
    }

    private function Handle_SitePrivacyPolicy(Array $args, int $argc)
    {
        include(__DIR__ . "/../Website/StaticPages/PrivacyPolicy.php");
    }

    private function Handle_SiteTermsPage(Array $args, int $argc)
    {
        include(__DIR__ . "/../Website/StaticPages/TermsPage.php");
    }

    private function Handle_SiteUserProfile(Array $args, int $argc)
    {
        // The right path: /user/[username] or /site/user/[username]

        // If it's null possibly use the session user
        if(0 == $argc)
        {
            $incparam_username = null;
        }
        elseif (1 == $argc)
        {
            $incparam_username = $args[0];
        }
        else
        {
            // Not a likely situation but still, if the num of parameters is wrong send err
            HTTPServer::BadRequest();
        }

        require_once "Handlers/UserProfileHandler.php";
        $handler = new UserProfileHandler($incparam_username);
        $handler->Handle();

    }

    private function Handle_SiteUserFollowLists(Array $args, int $argc)
    {
        // The right path: /user/[username]/followers, /user/[username]/following
        $username = $args[0];
        $action = $args[1];

        require_once "Handlers/FollowListHandler.php";
        $handler = new FollowListHandler($action, $username);
        $handler->Handle();
    }

    private function Handle_SiteSettings(Array $args, int $argc)
    {
        // The right path: /site/settings

        // Call the site settings php page.
        include("../Website/ManageSettings/ManageSettingsPage.php");             // Relative path
    }

    private function Handle_SiteSettingsUploadProfilePicture(Array $args, int $argc)
    {
        // The right path: /site/settings/uploadprofilepicture

        require_once "Handlers/UploadProfilePictureHandler.php";
        $handler = new UploadProfilePictureHandler();
        $handler->Handle();
    }

    private function Handle_SiteSettingsUploadCoverPicture(Array $args, int $argc)
    {
        // The right path: /site/settings/uploadcoverpicture

        require_once "Handlers/UploadCoverPictureHandler.php";
        $handler = new UploadCoverPictureHandler();
        $handler->Handle();
    }

    private function Handle_SiteSettingsUpdatePassword(Array $args, int $argc)
    {
        require_once "Handlers/UpdatePasswordHandler.php";
        $handler = new UpdatePasswordHandler();
        $handler->Handle();
    }

    private function Handle_SiteSettingsUpdateDetails(Array $args, int $argc)
    {
        require_once "Handlers/UpdateUserDetailsHandler.php";
        $handler = new UpdateUserDetailsHandler();
        $handler->Handle();
    }

    private function Handle_SiteDefault(Array $args, int $argc)
    {
        // This function returns a file for any request that is not listed, and ignores parameters.

        $uri = $_SERVER["SCRIPT_NAME"];

        // Don't server anything for /site
        if($argc == 0)
        {
            HTTPServer::BadRequest();
        }

        // The file's path in the webserver directory after dropping the rest-api path (/site/)
        $file_path = substr($uri, 6);
        $file_path = __DIR__ . "/../Website/" . $file_path;

        if(file_exists($file_path))
        {
            // For JS files return JS headers
            if(preg_match('/.js$/', $file_path))
            {
                HTTPServer::JavaScriptHeaders();
            }
            // For CSS files return CSS headers
            elseif(preg_match('/.css$/', $file_path))
            {
                HTTPServer::CSSHeaders();
            }
            // Serve image files as images
            elseif(preg_match('/.png$/', $file_path) or
                preg_match('/.jpg$/', $file_path) or
                preg_match('/.jpeg$/', $file_path) or
                preg_match('/.ico$/', $file_path) or
                preg_match('/.gif$/', $file_path))
            {
                HTTPServer::ImageHeaders();
            }
            else
            {
                // Quit with an error if file type is not one of those listed above
                HTTPServer::BadRequest("File type is not allowed");
            }

            include($file_path);
        }
        else
        {
            HTTPServer::NotFound();
        }
    }
}

$rest_server = new MessRESTServer();
$rest_server->Start();