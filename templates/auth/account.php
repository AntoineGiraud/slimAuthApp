<?php $user = $Auth->getSessionUser() ?>

<h1 class="page-header"><span class="glyphicon glyphicon-user"></span> Mon compte</h1>
<h2>Informations personnelles</h2>
<dl class="dl-horizontal">
    <dt>Email</dt>
    <dd><?= $user['email'] ?></dd>
    <dt>Prénom</dt>
    <dd><?= $user['prenom'] ?></dd>
    <dt>Nom</dt>
    <dd><?= $user['nom'] ?></dd>
    <dt>Etat du compte</dt>
    <dd>
        <?php if ($user['online' ]) { ?>
            <span class="label label-success">en ligne</span>
        <?php } else { ?>
            <span class="label label-danger">hors ligne</span>
        <?php } ?>
    </dd>
</dl>

<h2>Permissions</h2>
<p>
    <strong>Rôle: </strong> <em><?= $user['name'] ?></em>
</p>
<?php if (is_array($user['permissions'])) { ?>
    <h4>Pages des groupes dont vous faites partie</h4>
    <table class="table table-condensed table-bordered table-hover table-striped table-nonfluid">
        <thead>
            <tr>
                <th>type</th>
                <th>nom</th>
                <th>pages accessibles</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach($user['permissions']['forRole'] as $k => $routes): ?>
            <tr>
                <td><span class="label <?= $k == 'allUsers' ? 'label-info' : 'label-primary' ?>">groupe</span></td>
                <td><?= $k ?></td>
                <td><code><?= implode('</code>, <code>', $routes) ?></code></td>
            </tr>
        <?php endforeach ?>
        </tbody>
    </table>
    <h4>Autres pages authorisées pour vous</h4>
    <?php if (!empty($user['permissions']['forUser'])){ ?>
        <p><code><?= implode('</code>, <code>', $user['permissions']['forUser']) ?></code></p>
    <?php } else { ?>
        <p>Aucunes</p>
    <?php } ?>
<?php } else if ($Auth->hasRole('admin')) { ?>
    <p class="alert alert-info">En tant qu'administrateur, vous avez accès à toutes les pages.</p>
<?php } else { ?>
    <?= $user['permissions'] ?>
<?php } ?>