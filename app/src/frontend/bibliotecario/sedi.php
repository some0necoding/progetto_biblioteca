<?php
    include_once '../src/backend/backend.php';
    
    $redirectUrl = 'bibliotecario.php?tab=sedi';

    try {
        $sedi = getSedi();
    } catch (ErroreInternoDatabaseException $e) {
        redirect_to('internal_error.php');
    }
?>

<?php if (empty($sedi)): ?>
<div class="alert alert-warning text-center" role="alert">
  Non ci sono sedi
</div>
<?php else: ?>
<table class="table">
  <thead>
    <tr>
      <th scope="col">#</th>
      <th scope="col">Indirizzo</th>
      <th scope="col">Città</th>
      <th scope="col">Isbn</th>
      <th scope="col">Copie</th>
      <th scope="col">Prestiti</th>
      <th scope="col"></th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($sedi as $sede): ?>
      <tr>
        <th scope='row'><?= $sede['id'] ?></th>
        <td><?= $sede['indirizzo'] ?></td>
        <td><?= $sede['città'] ?></td>
        <td><?= $sede['isbn_gestiti'] ?></td>
        <td><?= $sede['copie_gestite'] ?></td>
        <td><?= $sede['prestiti_in_corso'] ?></td>
        <td>
          <form action="<?= $redirectUrl ?>" method="POST">
            <input type="hidden" name="id" value="<?= $sede['id'] ?>">
            <input type="hidden" name="operazione" value="<?= TipoOperazione::RIMUOVI_SEDE->value ?>">
            <button type="submit" class="btn btn-danger">Rimuovi</button>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>
<?php endif; ?>
<form action="<?= $redirectUrl ?>" method="POST" class="mt-4">
  <div class="row mb-2">
    <div class="col">
      <label for="indirizzo" class="form-label">Indirizzo</label>
      <input type="text" class="form-control <?= isset($errors[TipoOperazione::AGGIUNGI_SEDE->value]['indirizzo']) ? 'border-danger' : '' ?>" 
             id="indirizzo" name="indirizzo" value="<?= $inputs[TipoOperazione::AGGIUNGI_SEDE->value]['indirizzo'] ?? '' ?>">
    </div>
    <div class="col">
      <label for="città" class="form-label">Città</label>
      <input type="text" class="form-control <?= isset($errors[TipoOperazione::AGGIUNGI_SEDE->value]['città']) ? 'border-danger' : '' ?>" 
             id="città" name="città" value="<?= $inputs[TipoOperazione::AGGIUNGI_SEDE->value]['città'] ?? '' ?>">
    </div>
  </div>
  <input type="hidden" name="operazione" value="<?= TipoOperazione::AGGIUNGI_SEDE->value ?>">
  <?php
    $aggiungiErrors = $errors[TipoOperazione::AGGIUNGI_SEDE->value] ?? [];
    $aggiungiErrors = array_filter($aggiungiErrors, fn($error) => !is_null($error) && $error !== '');

    $rimuoviErrors = $errors[TipoOperazione::RIMUOVI_SEDE->value] ?? [];
    $rimuoviErrors = array_filter($rimuoviErrors, fn($error) => !is_null($error) && $error !== '');
  ?>
  <p class="text-danger mt-4 <?= empty($aggiungiErrors) ? 'd-none' : '' ?>">
    <?= join('<br>', $aggiungiErrors) ?>
  </p>
  <p class="text-danger mt-4 <?= empty($rimuoviErrors) ? 'd-none' : '' ?>">
    <?= join('<br>', $rimuoviErrors) ?>
  </p>
  <button type="submit" class="btn btn-primary mt-3">Aggiungi sede</button>
</form>
