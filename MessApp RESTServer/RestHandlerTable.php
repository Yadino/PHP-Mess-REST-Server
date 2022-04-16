<?php

/*
 * The table of all REST paths.
 * Require this page in RESTServer if you want stuff to work.
 */

const REST_HANDLER_TABLE = array(
    ["path" => "/",
        "method" => "GET",
        "path_len" => 1,
        "min_args" => 0,
        "max_args" => 0,
        "handler" => "Handle_IndexPage"],

    ["path" => "/home",
        "method" => "GET",
        "path_len" => 1,
        "min_args" => 0,
        "max_args" => 0,
        "handler" => "Handle_IndexPage"],

    ["path" => "/comment",
        "method" => "GET",
        "path_len" => 1,
        "min_args" => 1,
        "max_args" => 1,
        "handler" => "Handle_Comment"],

    ["path" => "/comment",
        "method" => "PUT",
        "path_len" => 1,
        "min_args" => 1,
        "max_args" => 3,
        "handler" => "Handle_CommentVote"],

    ["path" => "/comment",
        "method" => "POST",
        "path_len" => 1,
        "min_args" => 1,
        "max_args" => 3,
        "handler" => "Handle_CommentEditContent"],

    ["path" => "/comment",
        "method" => "DELETE",
        "path_len" => 1,
        "min_args" => 1,
        "max_args" => 1,
        "handler" => "Handle_CommentDelete"],

    ["path" => "/session/login",
        "method" => "POST",
        "path_len" => 2,
        "min_args" => 0,
        "max_args" => 2,
        "handler" => "Handle_Login"],

    ["path" => "/session/logout",
        "method" => "GET",
        "path_len" => 2,
        "min_args" => 0,
        "max_args" => 0,
        "handler" => "Handle_Logout"],

);
