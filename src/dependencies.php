<?php
// DIC configuration

$container = $app->getContainer();

// view renderer
$container['renderer'] = function ($c) {
    $settings = $c->get('settings')['renderer'];
    return new Slim\Views\PhpRenderer($settings['template_path']);
};

// view renderer
$container['flash'] = function ($c) {
    return new \Slim\Flash\Messages();
};

// monolog
$container['logger'] = function ($c) {
    $settings = $c->get('settings')['logger'];
    $logger = new Monolog\Logger($settings['name']);
    $logger->pushProcessor(new Monolog\Processor\UidProcessor());
    $logger->pushHandler(new Monolog\Handler\StreamHandler($settings['path'], Monolog\Logger::DEBUG));
    return $logger;
};

$container['csrf'] = function ($c) {
    $guard = new \Slim\Csrf\Guard();
    $guard->setFailureCallable(function ($request, $response, $next) {
        $request = $request->withAttribute("csrf_status", false);
        return $next($request, $response);
    });
    $guard->setStorageLimit(50);
    return $guard;
};

///////////////////////////////
// Connexion base de données //
///////////////////////////////
$container['DB'] = function ($c) {
    $confSQL = $c->get('settings')['confSQL'];
    try {
        $DB = new \CoreHelpers\DB($confSQL['sql_host'],$confSQL['sql_user'],$confSQL['sql_pass'],$confSQL['sql_db']);
    } catch (Exception $e) {
        $DB = null;
    }
    return $DB;
};
$DB = $container['DB'];

$container['Auth'] = function ($c) {
    $settings = $c->get('settings');
    $Auth = new \CoreHelpers\Auth($settings['Auth'], $c->DB);
    $Auth->setFlashCtrl($c->flash);
    return $Auth;
};
///////////////////////////
// Autre initialisations //
///////////////////////////
