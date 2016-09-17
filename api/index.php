 <?php
require 'Slim/Slim.php';
// Set a namespace called Slim
\Slim\Slim::registerAutoloader();
 
// Start the application
$app = new \Slim\Slim();

// logs
$app->config('debug', true);
$app->log->setEnabled(true);
error_reporting(E_ALL);
ini_set("display_errors","On");
ini_set("display_startup_errors","On");
// timezone
date_default_timezone_set("America/Argentina/Buenos_Aires");

// variables and constants
include('config.php');
// customs functions 
include('functions.php');

$app->get('/', function () {
    echo '<h1>Welcome to our API</h1><p>More information: www.loultimoenlaweb.com</p>';
    echo '<h3>If you are view this page, please contact to Web administrator.</h3>';
	echo '<p>More information: www.loultimoenlaweb.com</p>';
	echo '<p>Last update: v.'.vAPI().'</p>';
});

// this is magic stuff and must be remove will all stuff will be done!
include('setup.php');

// dynamic routes
include('routes.php');


// execute app
$app->run();
?>
