<?php
    include_once '../src/backend/backend.php';
    
    $redirectUrl = 'bibliotecario.php?tab=bibliotecari';

    try {
        $bibliotecari = getBibliotecari();
    } catch (ErroreInternoDatabaseException $e) {
        redirect_to('internal_error.php');
    }
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
      <input type="text" class="form-control <?= isset($errors[TipoOperazione::AGGIUNGI_BIBLIOTECARIO->value]['email']) ? 'border-danger' : '' ?>" 
             id="email" name="email" value="<?= $inputs[TipoOperazione::AGGIUNGI_BIBLIOTECARIO->value]['email'] ?? '' ?>">
    </div>
    <div class="col-4">
      <label for="password1" class="form-label">Password</label>
      <input type="password" class="form-control <?= isset($errors[TipoOperazione::AGGIUNGI_BIBLIOTECARIO->value]['password1']) ? 'border-danger' : '' ?>"
             id="password1" name="password1" value="<?= $inputs[TipoOperazione::AGGIUNGI_BIBLIOTECARIO->value]['password1'] ?? '' ?>">
    </div>
    <div class="col-4">
      <label for="password2" class="form-label">Conferma Password</label>
      <input type="password" class="form-control <?= isset($errors[TipoOperazione::AGGIUNGI_BIBLIOTECARIO->value]['password2']) ? 'border-danger' : '' ?>"
             id="password2" name="password2" value="<?= $inputs[TipoOperazione::AGGIUNGI_BIBLIOTECARIO->value]['password2'] ?? '' ?>">
    </div>
  </div>
  <input type="hidden" name="operazione" value="<?= TipoOperazione::AGGIUNGI_BIBLIOTECARIO->value ?>">
  <?php 
    $aggiungiErrors = $errors[TipoOperazione::AGGIUNGI_BIBLIOTECARIO->value] ?? [];
    $aggiungiErrors = array_filter($aggiungiErrors ?? [], fn($error) => !is_null($error) && $error !== '');
  ?>
  <p class="text-danger mt-4 <? empty($aggiungiErrors) ? 'd-none' : '' ?>">
    <?= join('<br>', $aggiungiErrors); ?>
  </p>
  <button type="submit" class="btn btn-primary mb-3">Aggiungi bibliotecario</button>
</form>
