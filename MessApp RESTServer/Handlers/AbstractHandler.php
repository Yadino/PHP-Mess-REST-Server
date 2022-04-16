<?php

require_once "../Basic/Common.php";

abstract class Handler
{

    // A handler will:
    //  Validate user-session
    //  Validate request integrity
    //  Take care of errors and exceptions
    //  Return a response readable by the client

    abstract public function Handle();
}
