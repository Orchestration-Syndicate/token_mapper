<?php

/*
 * This is the server side of the ClimbUI application. It is a simple PHP script that
 * extends from the Server class defined in the Service layer
 */

namespace Tokenmapper;

// Needed for apache2 servers
// try {
//     ob_flush();
// } catch (Exception $e) {
// }

// disable errors
error_reporting(0);

require_once __DIR__ . '/support/lib/vendor/autoload.php';

use \Approach\Service\format;
use \Approach\Service\target;
use \Approach\path;
use \Approach\Scope;
use Tokenmapper\Service\Server;

$path_to_project = __DIR__ . '/';
$path_to_approach = __DIR__ . '/support/lib/approach/';
$path_to_support = __DIR__ . '//support//';

// Defining a global scope for the application
global $scope;

$scope = new Scope(
    path: [
        path::project->value => $path_to_project,
        path::installed->value => $path_to_approach,
        path::support->value => $path_to_support,
    ],
);

// The php://input stream is needed apache2,
// but essentially for now, we are explicitly using post-requests
// So, any get requests will be ignored
$service = new Server(
    auto_dispatch: false,
    format_in: format::json,
    format_out: format::json,
    target_in: target::variable,
    target_out: target::stream,
    input: [
        $_POST['json']
    ],
    output: ['php://output'],
);

$output = $service->dispatch();
