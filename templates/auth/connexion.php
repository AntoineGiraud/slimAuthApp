<!DOCTYPE html>
<html lang="fr">
  <head>
    <meta charset="utf-8">
    <title><?= $RouteHelper->getPageTitle() ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="VisuLignes RTL - Connexion">
    <meta name="author" content="Antoine Giraud">
    <link rel="shortcut icon" href="<?= $RouteHelper->publicPath ?>/img/favicon.png">

    <!-- Le styles -->
    <link href="<?= $RouteHelper->publicPath ?>css/bootstrap.min.css" rel="stylesheet">
    <style type="text/css">
        body {
          padding-top: 40px;
          padding-bottom: 40px;
          background-color: #eee;
        }

        .form-signin {
          max-width: 330px;
          padding: 15px;
          margin: 0 auto;
        }
        .form-signin .form-signin-heading,
        .form-signin .checkbox {
          margin-bottom: 10px;
        }
        .form-signin .checkbox {
          font-weight: normal;
        }
        .form-signin .form-control {
          position: relative;
          height: auto;
          -webkit-box-sizing: border-box;
             -moz-box-sizing: border-box;
                  box-sizing: border-box;
          padding: 10px;
          font-size: 16px;
        }
        .form-signin .form-control:focus {
          z-index: 2;
        }
        .form-signin input[type="email"] {
          margin-bottom: -1px;
          border-bottom-right-radius: 0;
          border-bottom-left-radius: 0;
        }
        .form-signin input[type="password"] {
          margin-bottom: 10px;
          border-top-left-radius: 0;
          border-top-right-radius: 0;
        }
    </style>

    <!-- Le HTML5 shim, for IE6-8 support of HTML5 elements -->
    <!--[if lt IE 9]>
      <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]-->

    <!-- Le favicon (img du site ds le navigateur) -->
  </head>

  <body>

    <div class="container">
      <?php foreach ($flash->getMessages() as $key => $flashs): ?>
        <?php foreach ($flashs as $flashMsg): ?>
          <div class="alert alert-<?= $key ?>"><button class="close" data-dismiss="alert">×</button><?php echo $flashMsg ?></div>
        <?php endforeach ?>
      <?php endforeach ?>

      <form class="form-signin<?= (isset($_GET['errorLogin']))?' has-error':''; ?>" role="form" action="<?= $RouteHelper->getPathFor('login') ?>" method="POST">
        <p style="text-align:center"><img src="<?= $RouteHelper->publicPath ?>/img/logo.png" style="max-width:200px;"></p>
        <h2 class="form-signin-heading">Identifiez-vous !</h2>
        <input type="hidden" name="<?= $token['nameKey'] ?>" value="<?= $token['name'] ?>">
        <input type="hidden" name="<?= $token['valueKey'] ?>" value="<?= $token['value'] ?>">
        <input type="email" name="email" class="form-control" placeholder="Email" required autofocus>
        <input type="password" name="password" class="form-control" placeholder="Password" required>
        <button class="btn btn-lg btn-primary btn-block" type="submit">Se connecter</button>
        <?php if (!empty($Auth->casUrl)): ?>
        <a href="<?= $casUrl ?>" class="btn btn-lg btn-info btn-block">Ou avec <?= basename(dirname($Auth->casUrl)) ?></a>
        <?php endif; ?>
      </form>
        <hr>
        <p style="text-align:center"><em>Page d'authentification vers le portail <strong><?= $RouteHelper->webSiteTitle ?></strong></em></p>
    </div>
    <script src="<?= $RouteHelper->publicPath ?>js/bootstrap.min.js"></script>
  </body>
</html>