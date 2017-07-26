<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require __DIR__ . '/../vendor/autoload.php';

$config['displayErrorDetails'] = true;
$config['addContentLengthHeader'] = false;

$config['db']['host']   = "localhost";
$config['db']['user']   = "user";
$config['db']['pass']   = "password";
$config['db']['dbname'] = "exampleapp";

$app = new \Slim\App(['settings' => $config ]);
$container = $app->getContainer();

// Register component on container
// Register component on container
$container['view'] = function ($container) {
    $view = new \Slim\Views\Twig('../templates', [
        'cache' => false
    ]);

    // Instantiate and add Slim specific extension
    $basePath = rtrim(str_ireplace('index.php', '', $container['request']->getUri()->getBasePath()), '/');
    $view->addExtension(new Slim\Views\TwigExtension($container['router'], $basePath));

    return $view;
};

$container['logger'] = function($c) {
    $logger = new \Monolog\Logger('my_logger');
    $file_handler = new \Monolog\Handler\StreamHandler("../logs/app.log");
    $logger->pushHandler($file_handler);
    return $logger;
};

// $container['db'] = function ($c) {
//     $db = $c['settings']['db'];
//     $pdo = new PDO("mysql:host=" . $db['host'] . ";dbname=" . $db['dbname'],
//         $db['user'], $db['pass']);
//     $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
//     $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
//     return $pdo;
// };

$app->get('/patient', function (Request $request, Response $response) {
    $this->logger->addInfo("Patient Screen");

    $response = $this->view->render($response, "patient.html.twig");
    return $response;
});

$app->get('/login', function (Request $request, Response $response) {
    $this->logger->addInfo("Login Screen");

    $response = $this->view->render($response, "login.html.twig");
    return $response;
});

$app->get('/provider', function (Request $request, Response $response) {
    $this->logger->addInfo("Provider Screen");

    $response = $this->view->render($response, "provider.phtml");
    return $response;
});

$app->get('/patient-profile', function (Request $request, Response $response) {
    $this->logger->addInfo("Profile Screen");

    $response = $this->view->render($response, "profile.phtml");
    return $response;
});

$app->run();
