<?php
    include_once '../src/backend/backend.php';
    
    $redirectUrl = 'lettore.php?tab=account';
?>

<div class="row mt-4 py-4">
  <div class="col">
    <form action="<?= $redirectUrl ?>" method="POST">
      <input type="hidden" name="codice_fiscale" value="<?= $_SESSION['user_id'] ?>">
      <input type="hidden" name="operazione" value="<?= TipoOperazione::CAMBIA_EMAIL_LETTORE->value ?>">
      <label for="email_corrente" class="form-label mt-2">Email corrente</label>
      <input type="text" class="form-control" id="email_corrente" name="email_corrente" value="<?= $_SESSION['user_email'] ?>" disabled readonly>
      <label for="email" class="form-label mt-2">Nuova email</label>
      <input type="text" class="form-control <?= isset($errors[TipoOperazione::CAMBIA_EMAIL_LETTORE->value]['email']) ? 'border-danger' : '' ?>" id="email" name="email" value="<?= $inputs[TipoOperazione::CAMBIA_EMAIL_LETTORE->value]['email'] ?? '' ?>">
      <button type="submit" class="btn btn-primary mt-3">Cambia email</button>
      <?php 
        $cambiaEmailErrors = $errors[TipoOperazione::CAMBIA_EMAIL_LETTORE->value] ?? [];
        $cambiaEmailErrors = array_filter($cambiaEmailErrors ?? [], fn($error) => !is_null($error) && $error !== '');
      ?>
      <p class="text-danger mt-4 <?= empty($cambiaEmailErrors) ? 'd-none' : '' ?>">
        <?= join('<br>', $cambiaEmailErrors) ?>
      </p>
    </form>
  </div>
  <div class="col">
    <form action="<?= $redirectUrl ?>" method="POST">
      <input type="hidden" name="codice_fiscale" value="<?= $_SESSION['user_id'] ?>">
      <input type="hidden" name="operazione" value="<?= TipoOperazione::CAMBIA_PASSWORD_LETTORE->value ?>">
      <div class="row">
        <div class="col">
          <label for="vecchia_password" class="form-label mt-2">Password</label>
          <input type="password" class="form-control <?= isset($errors[TipoOperazione::CAMBIA_PASSWORD_LETTORE->value]['vecchia_password']) ? 'border-danger' : '' ?>" id="vecchia_password" name="vecchia_password" value="<?= $inputs[TipoOperazione::CAMBIA_PASSWORD_LETTORE->value]['vecchia_password'] ?? '' ?>">
        </div>
      </div>
      <div class="row">
        <div class="col">
          <label for="password1" class="form-label mt-2">Password</label>
          <input type="password" class="form-control <?= isset($errors[TipoOperazione::CAMBIA_PASSWORD_LETTORE->value]['password1']) ? 'border-danger' : '' ?>" id="password1" name="password1" value="<?= $inputs[TipoOperazione::CAMBIA_PASSWORD_LETTORE->value]['password1'] ?? '' ?>">
        </div>
        <div class="col">
          <label for="password2" class="form-label mt-2">Conferma Password</label>
          <input type="password" class="form-control <?= isset($errors[TipoOperazione::CAMBIA_PASSWORD_LETTORE->value]['password2']) ? 'border-danger' : '' ?>" id="password2" name="password2" value="<?= $inputs[TipoOperazione::CAMBIA_PASSWORD_LETTORE->value]['password2'] ?? '' ?>">
        </div>
      </div>
      <button type="submit" class="btn btn-primary mt-3">Cambia Password</button>
      <?php 
        $cambiaPasswordErrors = $errors[TipoOperazione::CAMBIA_PASSWORD_LETTORE->value] ?? [];
        $cambiaPasswordErrors = array_filter($cambiaPasswordErrors ?? [], fn($error) => !is_null($error) && $error !== '');
      ?>
      <p class="text-danger mt-4 <?= empty($cambiaPasswordErrors) ? 'd-none' : '' ?>">
        <?= join('<br>', $cambiaPasswordErrors); ?>
      </p>
    </form>
  </div>
</div>
<div class="row mt-4 bg-danger-subtle py-4 border border-danger border-2 rounded-4">
  <div class="col">
    <form action="<?= $redirectUrl ?>" method="POST">
      <input type="hidden" name="codice_fiscale" value="<?= $_SESSION['user_id'] ?>">
      <input type="hidden" name="operazione" value="<?= TipoOperazione::ELIMINA_ACCOUNT_LETTORE->value ?>">
      <button type="submit" class="btn btn-danger">Elimina Account</button>
    </form>
  </div>
</div>
