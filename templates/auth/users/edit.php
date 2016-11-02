<?php if ($RouteHelper->Auth->sourceConfig != 'database'): ?>
    <p class="alert alert-warning">
        Il n'est pas possible d'éditer les membres avec une configuration fichier. Migrez vers une configuration base de données.
    </p>
<?php endif ?>

<h1 class="page-header clearfix">
    <div class="pull-left"><span class="glyphicon glyphicon-tower"></span> <?= $RouteHelper->pageName ?> </div>
    <div class="pull-right">
        <a href="<?= $RouteHelper->getPathFor('auth/users/add') ?>" class="btn btn-info">Ajouter</a>
        <a href="<?= $RouteHelper->getPathFor('auth/users/list') ?>" class="btn btn-primary" onlick="">Retour liste</a>
    </div>
</h1>

<form class="form-horizontal" role="form" action="<?= $RouteHelper->getPathFor('auth/users/edit') ?>" method="post">
    <input type="hidden" name="<?= $token['nameKey'] ?>" value="<?= $token['name'] ?>">
    <input type="hidden" name="<?= $token['valueKey'] ?>" value="<?= $token['value'] ?>">
    <div class="row">
        <fieldset>
            <legend>Informations générales :</legend>
            <div>
                <input name="id" type="hidden" value="<?= $user['id'] ?>">
                <div class="form-group <?= $ErrorsCtrl->hasError('email')?'has-error':'' ?>">
                    <label class="col-sm-2 control-label" for="inputemail">Email : </label>
                    <div class="col-sm-10">
                        <div class="input-group">
                            <span class="input-group-addon">@</span>
                            <input name="email" class="form-control" id="inputemail" type="text" maxlength="105" value="<?= $user['email'] ?>">
                        </div>
                        <span class="help-block"><em>Identifiant</em></span>
                    </div>
                </div>
                <div class="form-group <?= $ErrorsCtrl->hasError('first_name')?'has-error':'' ?>">
                    <label class="col-sm-2 control-label" for="inputprenom">Prénom : </label>
                    <div class="col-sm-10">
                        <input name="first_name" class="form-control" id="inputprenom" type="text" maxlength="55" value="<?= $user['first_name'] ?>">
                    </div>
                </div>
                <div class="form-group <?= $ErrorsCtrl->hasError('last_name')?'has-error':'' ?>">
                    <label class="col-sm-2 control-label" for="inputnom">Nom : </label>
                    <div class="col-sm-10">
                        <input name="last_name" class="form-control" id="inputnom" type="text" maxlength="55" value="<?= $user['last_name'] ?>">
                    </div>
                </div>
                <div class="form-group ">
                    <label class="col-sm-2 control-label" for="inputonline">En ligne :</label>
                    <div class="col-sm-10">
                        <div class="checkbox">
                            <label>
                                <input name="online" type="hidden" value="0">
                                <input name="online" id="inputonline" type="checkbox" <?= $user['online']?'checked="checked"':'' ?> value="1">
                            </label>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-2 control-label" for="selectrole_id">Rôles : </label>
                    <div class="col-sm-10">
                        <input name="roles[]" type="hidden" value="">
                        <select name="roles[]" class="form-control" id="selectrole_id" multiple>
                            <option value="">Aucun rôle particulier</option>
                            <?php foreach ($RouteHelper->Auth->roles as $role): if ($role['slug'] == $RouteHelper->Auth->allUserRole) {continue;} ?>
                                <option <?= array_key_exists($role['slug'], $user['roles'])?'selected="selected"':'' ?> value="<?= $role['slug'] ?>"><?= $role['name'] ?></option>
                            <?php endforeach ?>
                        </select>
                    </div>
                </div>
            </div>
        </fieldset>
        <fieldset class="password clear">
            <legend>Modifier son mot de passe :</legend>
            <?php if ($ErrorsCtrl->hasError('password')): ?>
                <div class="col-sm-offset-2 col-sm-10">
                    <p class="alert alert-warning"><?= $ErrorsCtrl->errors['password']['msg'] ?></p>
                </div>
            <?php endif ?>
            <div class="pass">
                <div class="form-group <?= $ErrorsCtrl->hasError('password')?'has-error':'' ?>">
                    <label class="col-sm-2 control-label" for="inputpass_new">Nouveau mot de passe :</label>
                    <div class="col-sm-10">
                        <input name="password" class="form-control" id="inputpass_new" type="password" maxlength="55" value="">
                    </div>
                </div>
                <div class="form-group <?= $ErrorsCtrl->hasError('password')?'has-error':'' ?>">
                    <label class="col-sm-2 control-label" for="inputpass_new2">Confirmez le :</label>
                    <div class="col-sm-10">
                        <input name="password_confirm" class="form-control" id="inputpass_new2" type="password" maxlength="55" value="">
                    </div>
                </div>
            </div>
        </fieldset>
    </div>
    <div class="form-group">
        <div class="col-sm-offset-2 col-sm-10">
            <button class="btn btn-primary" type="submit">Save changes</button>
            &nbsp;
            <button class="btn btn-default" type="reset">Cancel</button>
        </div>
    </div>
</form>
