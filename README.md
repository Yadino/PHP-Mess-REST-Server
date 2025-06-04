# PHP-Mess-REST-Server
The REST Server package used by [messapp.net](https://messapp.net). .

This REST server was created as part of the MessApp (messapp.net) project, but it can be used as a standalone REST server.

Please note: 
The line
  require_once "../Basic/Common.php";
mostly refers to a config file and can probably be removed with minimal effort.

Some superfluous code was kept in the project as example for general use, and should be removed when creating a new project.


## Usage
To create a new path, an entry should be added to the REST_HANDLER_TABLE array in RestHandlerTable.php, in the following format:

    ["path" => "THE URI PATH",
        "method" => "HTTP METHOD",
        "path_len" => <how deep is the path, IE how many slashes ('/') are in the path>,
        "min_args" => <Minimum number of arguments to be accepted>,
        "max_args" => <Maximum number of arguments to be accepted>,
        "handler" => "NAME OF THE HANDLING FUNCTION TO BE CALLED"]

For example:

    ["path" => "/",
        "method" => "GET",
        "path_len" => 1,
        "min_args" => 0,
        "max_args" => 0,
        "handler" => "Handle_IndexPage"]

When a request arrives, the first entry that matches will have its handler function called. For convinecnce a few example of such functions are included in RESTServer.php, although it is probably a better idea to put them in a new file and include them. It is up to you what you put in such a function. For a request of a static page, for example, a simple include "page.php" would do. It would also be wise to use the provided HTTP class or something similar to server an HTTP code and mark that the request is complete. Like:

    HTTPServer::return_http_response_code(200);

For more complicated requests I like to call a separate handler file (see the director Handlers), where the parameters can be parsed and exceptions from the core code  can be converted to HTTP error codes and served to the user. This design follows the traditional Model–view–controller pattern.

More thorough examples are present in the code.


### Changes to the HTTP server
To make a REST server useful, you would need to tell the http server to ignore the path and redirect all requests to RESTServer.php.

In Apache you can add these rules to the virtual host file:

    RewriteEngine on
    RewriteRule ^(.*)$ /RESTHandlers/RESTServer.php [NC]

Dont forget to enable the rewrite mod
      
      sudo a2enmod rewrite
