<?php
    include_once '../src/backend/backend.php';
    include_once '../src/frontend/operazioni.php';

    $redirectUrl = 'bibliotecario.php?tab=autori';
    
    try {
        $autori = getAutori();
    } catch (ErroreInternoDatabaseException $e) {
        redirect_to('internal_error.php');
    }
?>

<?php if (empty($autori)): ?>
<div class="alert alert-warning text-center" role="alert">
  Non ci sono autori
</div>
<?php else: ?>
<table class="table">
  <thead>
    <tr>
      <th scope="col">#</th>
      <th scope="col">nome</th>
      <th scope="col">cognome</th>
      <th scope="col">data di nascita</th>
      <th scope="col">data di morte</th>
      <th scope="col">biografia</th>
      <th scope="col"></th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($autori as $autore): ?>
      <tr>
        <th scope='row'><?= $autore['id'] ?></th>
        <td><?= $autore['nome'] ?></td>
        <td><?= $autore['cognome'] ?></td>
        <td><?= $autore['data_di_nascita'] ?></td>
        <td>
          <?php if (isset($autore['data_di_morte'])): ?>
            <?= $autore['data_di_morte'] ?>
          <?php else: ?>
            <a class="btn btn-light" data-bs-toggle="collapse" href="#collapse<?= $autore['id'] ?>" role="button">
              Aggiungi
            </a>
          <?php endif; ?>
        </td>
        <td><?= $autore['biografia'] ?></td>
        <td>
          <form action="<?= $redirectUrl ?>" method="POST">
            <input type="hidden" name="id" value="<?= $autore['id'] ?>">
            <input type="hidden" name="operazione" value="<?= TipoOperazione::RIMUOVI_AUTORE->value ?>">
            <button type="submit" class="btn btn-danger">Rimuovi</button>
          </form>
        </td>
      </tr>
      <?php if (!isset($autore['data_di_morte'])): ?>
        <tr class="collapse" id="collapse<?= $autore['id'] ?>">
          <td colspan="7">
            <form action="<?= $redirectUrl ?>" method="POST">
              <div class="row">
                <div class="col-9">
                  <input type="hidden" name="id" value="<?= $autore['id'] ?>">
                  <input type="hidden" name="operazione" value="<?= TipoOperazione::SET_DATA_DI_MORTE_AUTORE->value ?>">
                  <input type="date" class="form-control" id="data_di_morte" name="data_di_morte">
                </div>
                <div class="col-3">
                  <button type="submit" class="btn btn-primary">Aggiungi data di morte</button>
                </div>
              </div>
            </form>
          </td>
        </tr>
      <?php endif; ?>
    <?php endforeach; ?>
  </tbody>
</table>
<?php endif; ?>
<form action="bibliotecario.php" method="POST" class="mt-4">
  <div class="row mb-2">
    <div class="col-3">
      <label for="nome" class="form-label">Nome</label>
      <input type="text" class="form-control <?= isset($errors[TipoOperazione::AGGIUNGI_AUTORE->value]['nome']) ? 'border-danger' : '' ?>"
             id="nome" name="nome" value="<?= $inputs[TipoOperazione::AGGIUNGI_AUTORE->value]['nome'] ?? '' ?>">
    </div>
    <div class="col-3">
      <label for="cognome" class="form-label">Cognome</label>
      <input type="text" class="form-control <?= isset($errors[TipoOperazione::AGGIUNGI_AUTORE->value]['cognome']) ? 'border-danger' : '' ?>"
             id="cognome" name="cognome" value="<?= $inputs[TipoOperazione::AGGIUNGI_AUTORE->value]['cognome'] ?? '' ?>">
    </div>
    <div class="col-3">
      <label for="data_di_nascita" class="form-label">Data di Nascita</label>
      <input type="date" class="form-control <?= isset($errors[TipoOperazione::AGGIUNGI_AUTORE->value]['data_di_nascita']) ? 'border-danger' : '' ?>"
             id="data_di_nascita" name="data_di_nascita" value="<?= $inputs[TipoOperazione::AGGIUNGI_AUTORE->value]['data_di_nascita'] ?? '' ?>">
    </div>
    <div class="col-3">
      <label for="data_di_morte" class="form-label">Data di Morte</label>
      <input type="date" class="form-control" id="data_di_morte" name="data_di_morte" 
             value="<?= $inputs[TipoOperazione::AGGIUNGI_AUTORE->value]['data_di_morte'] ?? '' ?>">
    </div>
  </div>
  <div class="row mb-3">
    <div class="col">
      <label for="biografia" class="form-label">Biografia</label>
      <textarea class="form-control" id="biografia" name="biografia" 
                value="<?= $inputs[TipoOperazione::AGGIUNGI_AUTORE->value]['biografia'] ?? '' ?>"></textarea>
    </div>
  </div>
  <input type="hidden" name="operazione" value="<?= TipoOperazione::AGGIUNGI_AUTORE->value ?>">
  <?php
    $aggiungiErrors = $errors[TipoOperazione::AGGIUNGI_AUTORE->value] ?? [];
    $aggiungiErrors = array_filter($aggiungiErrors, fn($error) => !is_null($error) && $error !== '');

    $rimuoviErrors = $errors[TipoOperazione::RIMUOVI_AUTORE->value] ?? [];
    $rimuoviErrors = array_filter($rimuoviErrors, fn($error) => !is_null($error) && $error !== '');
  ?>
  <p class="text-danger <?= empty($aggiungiErrors) ? 'd-none' : '' ?>">
    <?= join('<br>', $aggiungiErrors) ?>
  </p>
  <p class="text-danger <?= empty($rimuoviErrors) ? 'd-none' : '' ?>">
    <?= join('<br>', $rimuoviErrors) ?>
  </p>
  <button type="submit" class="btn btn-primary">Aggiungi autore</button>
</form>
