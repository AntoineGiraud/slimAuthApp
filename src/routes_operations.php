<?php

///////////////////////////////
// Routes pour les dispatchs //
///////////////////////////////

$app->group('/operations', function () {
    // Page ouverte à tous
    $this->get('/vue_operations', function ($request, $response, $args) {
        $RouteHelper = new \CoreHelpers\RouteHelper($this, $request, $response, 'vue opérations');

        $this->renderer->render($response, 'header.php', compact('RouteHelper', $args));
        $this->renderer->render($response, 'operations/vue_operations.php', compact('RouteHelper', $args));
        return $this->renderer->render($response, 'footer.php', compact('RouteHelper', $args));
    })->setName('operations/vue_operations');

    /////////////////
    // Espace Icam //
    /////////////////

    $this->get('/vuePersoOperations', function ($request, $response, $args) {
        $RouteHelper = new \CoreHelpers\RouteHelper($this, $request, $response, 'vue perso opérations');

        // Sample log message
        // $this->logger->info("Slim-Skeleton '/' index");

        // Render index view
        $this->renderer->render($response, 'header.php', compact('RouteHelper', $args));
        $this->renderer->render($response, 'operations/vuePersoOperations.php', compact('RouteHelper', $args));
        return $this->renderer->render($response, 'footer.php', compact('RouteHelper', $args));
    })->setName('operations/vuePersoOperations');
});