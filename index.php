<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

spl_autoload_register('autoloader');
function autoloader($classname) {
    include_once 'classes/' . $classname . '.php';
}
$request_uri = explode('?', $_SERVER['REQUEST_URI'], 2);

$Controller = new Controller();

if($_GET){
    switch ($request_uri[0]) {
        case '/results':
            if($_GET['mode'] == 'arrivals'){
                $data = $Controller->getArrivals($_GET['icao'], $_GET['pagination']);
            } else {
                $data = $Controller->getDepartures($_GET['icao'], $_GET['pagination']);
            }
            include_once ('views/results.php');
            break;
        default:
            header('HTTP/1.0 404 Not Found');
            include_once ('views/404.php');
            break;
    }
} else {
    switch ($request_uri[0]) {
        case '/':
            include_once ('views/home.php');
            break;
        default:
            header('HTTP/1.0 404 Not Found');
            include_once ('views/404.php');
            break;
    }
}








?>

