<?php

session_start();


$request = $_SERVER['REQUEST_URI'];


$request = parse_url($request, PHP_URL_PATH);


switch ($request) {
    case '/Admin_login':
        require __DIR__ . '/Admin_login.php';
        break;

    case '/contact':
        require __DIR__ . '/contact.php';
        break;

    case '/customerdashboard':
        require __DIR__ . '/customerdashboard.php';
        break;

    case '/vehicle-list':
        require __DIR__ . '/vehicle_list.php';
        break;

    case '/book-vehicle':
        require __DIR__ . '/book_vehicle.php';
        break;

  

    default:
      
        http_response_code(404);
        echo "404 - Page not found";
        break;
}
?>
