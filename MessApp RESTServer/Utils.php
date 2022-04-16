<?php
/*
*  UTILS
*  NOTE: these functions are not in any class, but are part of the HTTPServer namespace, for simple usage like newline();
*/

function newline($num=1)
{
    /*
    * Print a new line
    */

    for($i=0; $i< $num; $i++)
    {
        echo "<br>";
    }
}

function bold($str)
{
    /*
    *  Return string as html bold
    */

    return "<b>" . $str . "</b>";
}

function italic($str)
{
    /*
    *  Return string as html italic
    */

    return "<i>" . $str . "</i>";
}

function head1($str)
{
    /*
    *  Return string as html h1
    */
    return "<h1>" . $str . "</h1><hr>";
}

function head2($str)
{
    /*
    *  Return string as html h2
    */
    return "<h2>" . $str . "</h2>";
}

function head3($str)
{
    /*
    *  Return string as html h3
    */
    return "<h3>" . $str . "</h3>";
}

function pretty_print($obj)
{
    /*
     * Pretty-print an object, usually an array.
     * Use instead of print_r
     */
    echo "<pre>";
    print_r($obj);
    echo "</pre>";
}