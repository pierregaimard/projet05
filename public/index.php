<?php

    // autoload

    require_once __DIR__ . "/../vendor/autoload.php";

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
