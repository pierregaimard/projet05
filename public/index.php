<?php

    // autoload

    require_once __DIR__ . "/../vendor/autoload.php";

    // Error Handeler

    // Todo: Delete Error handeler when blog is in production.
    error_reporting(E_ALL);
    ini_set("display_errors", 1);

    // Use

    use Climb\Controller\FrontController;
    use Climb\Exception\AppException;

    // Session

    session_start();

    // Front controller

try {
    $frontController = new FrontController();
    $response        = $frontController->getResponse();
    $response->send();
} catch (AppException $exception) {
    echo $exception;
}
