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
    if (!file_exists(dirname($settings['path'])))
        mkdir(dirname($settings['path']));
    $logger = new Monolog\Logger($settings['name']);
    $logger->pushProcessor(new Monolog\Processor\UidProcessor());
    $logger->pushHandler(new Monolog\Handler\StreamHandler($settings['path'], Monolog\Logger::DEBUG));
    return $logger;
};

$container['csrf'] = function ($c) {
    $guard = new \Slim\Csrf\Guard();
    $guard->setFailureCallable(function ($request, $response, $next) {
        global $container;
        $container['flash']->addMessage('danger', "Problème de validation du formulaire. Merci de réessayer. (CSRF failure)");
        return $response->withHeader('Location', $_SERVER['REQUEST_URI']);
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

///////////////////////////
// Autre initialisations //
///////////////////////////
$container['Auth'] = function ($c) {
    $settings = $c->get('settings');
    $Auth = new \CoreHelpers\Auth($settings['Auth'], $c->DB);
    $Auth->setFlashCtrl($c->flash);
    return $Auth;
};

function getFirstAndLastDaysOfWeek($dateIso){
    $date = strtotime($dateIso);
    $dotw = intval(date('w', $date)); // day of week: Sunday = 0 to Saturday = 6
    // Make it range between Saturday (= 0) and Friday (= 6) by adding one day (and subtracting a week when the result exceeds the week boundaries)
    if(--$dotw == -1) $dotw = 6;
    // Si on est lundi: dow-1=0, on ne veut pas bouger
    // Si on est dimanche: dow-1=-1, on veut avoir le lundi d'avant, on retrait 6 jours
    $start = $date - ($dotw * 24*60*60); // Substract days for start of week
    $end = $start + (6 * 24*60*60); // Add 6 days to the start to get the end of the week

    return ['first'=>date("Y-m-d", $start), 'last'=>date("Y-m-d", $end)];
}

function getPourcent($dividende, $diviseur, $precision=0) {
    return empty($diviseur) ? null : round(100*$dividende / $diviseur *pow(10, $precision))/pow(10, $precision);
}
function getDayPlusX($date, $offset=1) {
    $d = new \DateTime( $date );
    return $d->modify( ($offset>=0?$offset:'+'.$offset).' day' )->format("Y-m-d");
}