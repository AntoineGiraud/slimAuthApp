<!DOCTYPE html>
<html lang="fr">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
     <!-- Le styles -->
    <link href="<?= $RouteHelper->publicUrl ?>css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= $RouteHelper->publicUrl ?>css/main.css" rel="stylesheet">

    <meta name="description" content="VisuLignes - Connexion">
    <meta name="author" content="Antoine Giraud">
    <link rel="shortcut icon" href="<?= $RouteHelper->publicUrl ?>/img/favicon.ico">

    <!-- IE10 viewport hack for Surface/desktop Windows 8 bug -->
    <link href="http://getbootstrap.com/assets/css/ie10-viewport-bug-workaround.css" rel="stylesheet">

    <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
      <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->

    <title><?= $RouteHelper->getPageTitle() ?></title>
  </head>
  <body>
    <nav class="navbar navbar-fixed-top navbar-inverse">
      <div class="container">
        <div class="navbar-header">
          <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <a class="navbar-brand" href="<?= $RouteHelper->getPathFor() ?>">SlimAuthApp</a>
        </div>
        <div id="navbar" class="collapse navbar-collapse">
          <ul class="nav navbar-nav">
            <li<?php if($RouteHelper->curPage == '/') echo ' class="active"'; ?>><a href="<?= $RouteHelper->getPathFor() ?>">Home</a></li>
            <li<?php if($RouteHelper->curPage == 'about') echo ' class="active"'; ?>><a href="<?= $RouteHelper->getPathFor('about') ?>">A propos</a></li>
            <?php if ($Auth->memberCanAccessPages('operations/vue_operations', 'operations/vuePersoOperations')): ?>
            <li class="dropdown">
              <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Opérations <span class="caret"></span></a>
              <ul class="dropdown-menu">
                <li class="dropdown-header">Liens pour les opérations</li>
                <?= $RouteHelper->showLinkLi('operations/vue_operations', 'Vue opérations') ?>
                <li role="separator" class="divider"></li>
                <?= $RouteHelper->showLinkLi('operations/vuePersoOperations', 'Vue perso opérations', '?param=1') ?>
              </ul>
            </li>
            <?php endif ?>
          </ul>
          <?php if ($Auth->isLogged()): ?>
          <ul class="nav navbar-nav navbar-right">
            <li><p class="navbar-text"><small><em><?= $Auth->getSessionUserField('prenom') ?></em></small></p></li>
            <?php if ($Auth->memberCanAccessPages('account')): ?>
            <li class="dropdown">
              <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Compte<?= $Auth->isSuperAdmin()?'s':'' ?> <span class="caret"></span></a>
              <ul class="dropdown-menu">
                <?= $RouteHelper->showLinkLi('account', '<span class="glyphicon glyphicon-user"></span> Mon compte') ?>
                <?php if ($Auth->isSuperAdmin()): ?>
                  <li role="separator" class="divider"></li>
                  <li class="dropdown-header">Administration des utilisateurs</li>
                  <?= $RouteHelper->showLinkLi('auth/list_droits', '<span class="glyphicon glyphicon-certificate"></span> Liste des droits') ?>
                  <?= $RouteHelper->showLinkLi('auth/users/list', '<span class="glyphicon glyphicon-user"></span> Liste des utilisateurs') ?>
                <?php endif ?>
              </ul>
            </li>
            <?php endif ?>
            <li><a href="<?= $RouteHelper->getPathFor('logout') ?>">Déconnexion</a></li>
          </ul>
          <?php endif ?>
        </div><!-- /.nav-collapse -->
      </div><!-- /.container -->
    </nav><!-- /.navbar -->

    <div class="container">
      <?php foreach ($flash->getMessages() as $key => $flashs): ?>
        <?php foreach ($flashs as $flashMsg): ?>
          <div class="alert alert-<?= $key ?>"><button class="close" data-dismiss="alert">×</button><?php echo $flashMsg ?></div>
        <?php endforeach ?>
      <?php endforeach ?>