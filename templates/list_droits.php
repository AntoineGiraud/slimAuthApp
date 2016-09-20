<h1 class="page-header">Liste des droits accordés</h1>

<h2>Liste des roles</h2>
<table class="table table-condensed table-bordered table-hover table-striped table-nonfluid">
    <thead>
        <tr>
            <th>level</th>
            <th>name</th>
            <th>slug</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach($SettingsAuth['roles'] as $role): ?>
        <tr>
            <?php foreach ($role as $k => $v): ?>
                <td><?= $v ?></td>
            <?php endforeach ?>
        </tr>
    <?php endforeach ?>
    </tbody>
</table>

<h2>Liste des routes</h2>
<table class="table table-condensed table-bordered table-hover table-striped table-nonfluid">
    <thead>
        <tr>
            <th>type</th>
            <th>nom</th>
            <th>pages accessibles</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td><span class="label label-success">groupe</span></td>
            <td>admin</td>
            <td>toutes les pages du site</td>
        </tr>
        <tr>
            <td><span class="label label-info">groupe</span></td>
            <td>tout membre</td>
            <td>home, login, logout, about</td>
        </tr>
    <?php foreach($SettingsAuth['allowedRoutes']['forRole'] as $k => $routes): ?>
        <tr>
            <td><span class="label label-primary">groupe</span></td>
            <td><?= $k ?></td>
            <td><?= implode(', ', $routes) ?></td>
        </tr>
    <?php endforeach ?>
    <?php foreach($SettingsAuth['allowedRoutes']['forUser'] as $k => $routes): ?>
        <tr>
            <td><span class="label label-warning">user</span></td>
            <td><?= $k ?></td>
            <td><?= implode(', ', $routes) ?></td>
        </tr>
    <?php endforeach ?>
    </tbody>
</table>

<h2>Liste des users</h2>
<table class="table table-condensed table-bordered table-hover table-striped table-nonfluid">
    <thead>
        <tr>
            <th>online</th>
            <th>email</th>
            <th>prenom</th>
            <th>nom</th>
            <th>role</th>
            <th>level</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach($SettingsAuth['users'] as $role): ?>
        <tr>
            <td><span class="label <?= ($role['online'])?"label-success":"label-danger" ?>"><?= $role['online'] ?></span></td>
            <td><?= $role['email'] ?></td>
            <td><?= $role['prenom'] ?></td>
            <td><?= $role['nom'] ?></td>
            <td><?= (($role['slug'] == 'admin')?'<span class="glyphicon glyphicon-king"></span>':'') . ' ' .$role['slug'] ?></td>
            <td><?= $role['level'] ?></td>
        </tr>
    <?php endforeach ?>
    </tbody>
</table>