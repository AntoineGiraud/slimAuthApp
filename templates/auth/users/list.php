<h1 class="page-header clearfix">
  <div class="pull-left"><span class="glyphicon glyphicon-book"></span> Liste des utilisateurs</div>
  <div class="pull-right">
    <a id="export" href="<?= $RouteHelper->getPathFor('auth/users/export') ?>" class="btn btn-primary" onlick="">Exporter</a>
    <a href="<?= $RouteHelper->getPathFor('auth/users/add') ?>" class="btn btn-info">Ajouter</a>
  </div>
</h1>

<?php if ($RouteHelper->Auth->sourceConfig != 'database'): ?>
    <p class="alert alert-warning"> Il n'est pas possible d'éditer les membres avec une configuration fichier. Migrez vers une configuration base de données.</p>
<?php endif ?>

<table class="table table-condensed table-bordered table-hover table-striped">
    <thead>
        <tr>
            <th>online</th>
            <th>email</th>
            <th>prenom</th>
            <th>nom</th>
            <th>roles</th>
            <th>actions</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach($users as $user): ?>
        <tr>
            <td><span class="label <?= ($user['online'])?"label-success":"label-danger" ?>"><?= $user['online'] ?></span></td>
            <td><?= $user['email'] ?></td>
            <td><?= $user['first_name'] ?></td>
            <td><?= $user['last_name'] ?></td>
            <td><?= ((!empty($user['roles']) && in_array('superadmin', $user['roles'])) ? '<span class="glyphicon glyphicon-king"></span>' : '') . ' ' .implode(', ', $user['roles']) ?></td>
            <td>
                <div class="pull-right">
                  <a href="<?= $RouteHelper->getPathFor('auth/users/edit/'.$user['id']) ?>" title="Editer l'utilisateur #<?= $user['id']; ?>"><i class="glyphicon glyphicon-pencil"></i></a>
                  <a href="<?= $RouteHelper->getPathFor('auth/users/delete/'.$user['id'].'/'.$token['name'].'/'.$token['value']) ?>" title="Supprimer l'utilisateur #<?= $user['id']; ?>" onclick="return confirm('Voulez-vous vraiment supprimer cet utilisateur ?');"><i class="glyphicon glyphicon-trash"></i></a>
                </div>
            </td>
        </tr>
    <?php endforeach ?>
    </tbody>
</table>