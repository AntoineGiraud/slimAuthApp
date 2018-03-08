<h1 class="page-header clearfix">
  <div class="pull-left"><span class="glyphicon glyphicon-book"></span> Liste des utilisateurs</div>
  <div class="pull-right">
    <a id="export" href="<?= $RouteHelper->getPathFor('auth/users/export') ?>" class="btn btn-primary" onlick="">Exporter</a>
    <a href="<?= $RouteHelper->getPathFor('auth/users/edit') ?>" class="btn btn-info">Ajouter</a>
  </div>
</h1>

<?php if ($RouteHelper->Auth->sourceConfig != 'database'): ?>
    <p class="alert alert-warning"> Il n'est pas possible d'éditer les membres avec une configuration fichier. Migrez vers une configuration base de données.</p>
<?php endif ?>

<table class="table table-condensed table-bordered table-hover table-striped">
    <thead>
        <tr>
            <th>id</th>
            <th>email</th>
            <th>prenom</th>
            <th>nom</th>
            <th>roles</th>
            <?php if (!empty($RouteHelper->conf['Auth']['casUrl'])): ?>
                <th>cas<br>only</th>
            <?php endif ?>
            <?php if (!empty($RouteHelper->conf['Auth']['ldapUrl'])): ?>
                <th>ldap<br>only</th>
            <?php endif ?>
            <th>actions</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach($users as $user): ?>
        <tr>
            <td><span class="label <?= ($user['is_active'])?"label-success":"label-danger" ?>"><?= $user['id'] ?></span></td>
            <td><?= $user['email'] ?></td>
            <td><?= $user['first_name'] ?></td>
            <td><?= $user['last_name'] ?></td>
            <td><?= ((!empty($user['roles']) && in_array('superadmin', $user['roles'])) ? '<span class="glyphicon glyphicon-king"></span>' : '') . ' ' .implode(', ', $user['roles']) ?></td>
            <?php if (!empty($RouteHelper->conf['Auth']['casUrl'])): ?>
                <td><?php if (!empty($user['cas_only'])): ?>
                    <span class="glyphicon glyphicon-lock"></span>
                <?php endif ?></td>
            <?php endif ?>
            <?php if (!empty($RouteHelper->conf['Auth']['ldapUrl'])): ?>
                <td><?php if (!empty($user['ldap_only'])): ?>
                    <span class="glyphicon glyphicon-lock"></span>
                <?php endif ?></td>
            <?php endif ?>
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
<div class="alert alert-info">
Pour que les modifications soient portées sur le compte d'un usager, celui-ci doit :
<ul><li>aller à la page <a href="<?= $RouteHelper->getPathFor('account') ?>"><em>mon compte</em></a></li><li><strong>ou</strong> se reconnecter</li></ul>
</div>