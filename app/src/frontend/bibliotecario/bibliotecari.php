<?php
    include_once 'backend.php';
    
    $redirectUrl = 'bibliotecario.php?tab=bibliotecari';

    $bibliotecari = getBibliotecari();
    if ($bibliotecari == DatabaseErrors::ERRORE_INTERNO_DATABASE)
        redirect_to('internal_error.php');
?>

<?php if (empty($bibliotecari)): ?>
<div class="alert alert-warning text-center" role="alert">
    Non ci sono bibliotecari
</div>
<?php else: ?>
<table class="table">
    <thead>
        <tr>
            <th>Bibliotecari</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($bibliotecari as $bibliotecario): ?>
            <tr>
                <td><?= $bibliotecario['email'] ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php endif; ?>
<form action="<?= $redirectUrl ?>" method="POST" class="mt-4">
    <div class="row mb-2">
        <div class="col-4">
            <label for="email" class="form-label">Email</label>
            <input type="text" class="form-control <?= isset($errors['email']) ? 'border-danger' : '' ?>" id="email" name="email" value="<?= $inputs['email'] ?? '' ?>">
        </div>
        <div class="col-4">
            <label for="password1" class="form-label">Password</label>
            <input type="password" class="form-control <?= isset($errors['password1']) ? 'border-danger' : '' ?>" id="password1" name="password1" value="<?= $inputs['password1'] ?? '' ?>">
        </div>
        <div class="col-4">
            <label for="password2" class="form-label">Conferma Password</label>
            <input type="password" class="form-control <?= isset($errors['password2']) ? 'border-danger' : '' ?>" id="password2" name="password2" value="<?= $inputs['password2'] ?? '' ?>">
        </div>
    </div>
    <input type="hidden" name="operazione" value="<?= Operazione::AGGIUNGI_BIBLIOTECARIO->value ?>">
    <p class="text-danger">
        <?php 
            $error = array_filter($errors ?? [], fn($error) => !is_null($error) && $error !== '');
            echo join('<br>', $error); 
        ?>
    </p>
    <button type="submit" class="btn btn-primary mb-3">Aggiungi bibliotecario</button>
</form>
<div class="row mt-4 bg-info-subtle py-4 border border-info border-2 rounded-4">
    <h4 class="pb-2 text-info">Dati Personali</h4>
    <div class="col">
        <form action="<?= $redirectUrl ?>" method="POST">
            <input type="hidden" name="operazione" value="<?= Operazione::CAMBIA_EMAIL_BIBLIOTECARIO->value ?>">
            <label for="email_corrente" class="form-label mt-2">Email corrente</label>
            <input type="text" class="form-control" id="email_corrente" name="email_corrente" value="<?= $_COOKIE['user_email'] ?>" disabled readonly>
            <label for="cambia_email" class="form-label mt-2">Nuova email</label>
            <input type="text" class="form-control <?= isset($errors['cambia_email']) ? 'border-danger' : '' ?>" id="cambia_email" name="cambia_email" value="<?= $inputs['cambia_email'] ?? '' ?>">
            <button type="submit" class="btn btn-info mt-3">Cambia email</button>
        </form>
    </div>
    <div class="col">
        <form action="<?= $redirectUrl ?>" method="POST">
            <input type="hidden" name="operazione" value="<?= Operazione::CAMBIA_PASSWORD_BIBLIOTECARIO->value ?>">
            <div class="row">
                <div class="col">
                    <label for="vecchia_password" class="form-label mt-2">Password</label>
                    <input type="password" class="form-control <?= isset($errors['vecchia_password']) ? 'border-danger' : '' ?>" id="vecchia_password" name="vecchia_password" value="<?= $inputs['vecchia_password'] ?? '' ?>">
                </div>
            </div>
            <div class="row">
                <div class="col">
                    <label for="cambia_password1" class="form-label mt-2">Password</label>
                    <input type="password" class="form-control <?= isset($errors['cambia_password1']) ? 'border-danger' : '' ?>" id="cambia_password1" name="cambia_password1" value="<?= $inputs['cambia_password1'] ?? '' ?>">
                </div>
                <div class="col">
                    <label for="cambia_password2" class="form-label mt-2">Conferma Password</label>
                    <input type="password" class="form-control <?= isset($errors['cambia_password2']) ? 'border-danger' : '' ?>" id="cambia_password2" name="cambia_password2" value="<?= $inputs['cambia_password2'] ?? '' ?>">
                </div>
            </div>
            <button type="submit" class="btn btn-info mt-3">Cambia Password</button>
        </form>
    </div>
</div>
<div class="row mt-4 bg-danger-subtle py-4 border border-danger border-2 rounded-4">
    <div class="col">
        <form action="<?= $redirectUrl ?>" method="POST">
            <input type="hidden" name="operazione" value="<?= Operazione::RIMUOVI_BIBLIOTECARIO->value ?>">
            <button type="submit" class="btn btn-danger">Elimina Account</button>
        </form>
    </div>
</div>
