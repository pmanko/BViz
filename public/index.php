<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require __DIR__ . '/../vendor/autoload.php';
require '../models/user.php';
require '../handlers/exceptions.php';

$config = include('../config.php');

$app = new \Slim\App(['settings' => $config ]);

$container = $app->getContainer();

$capsule = new \Illuminate\Database\Capsule\Manager;
$capsule->addConnection($container['settings']['db']);
$capsule->setAsGlobal();
$capsule->bootEloquent();

$capsule->getContainer()->singleton(
  Illuminate\Contracts\Debug\ExceptionHandler::class,
  App\Exceptions\Handler::class
);


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

// USERS
$app->get('/users/', function($request, $response) {
  return $response->getBody()->write(User::all()->toJson());
});

$app->get('/users/{id}/', function($request, $response, $args) {
  $id = $args['id'];
  $user = User::find($id);
  $response->getBody()->write($user->toJson());
  return $response;
});

$app->post('/users/', function($request, $response, $args) {
  $data = $request->getParsedBody();
  $user = new User();
  $user->username = $data['username'];
  $user->id = $data['id'];

  $user->save();

  return $response->withStatus(201)->getBody()->write($user->toJson());
});

$app->run();
