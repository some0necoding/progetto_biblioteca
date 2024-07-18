<?php
    include_once '../src/backend/backend.php';
    
    $redirectUrl = 'bibliotecario.php?tab=copie';

    try {
        $copie = getCopie();
        $libri = getLibri();
        $sedi = getSedi();
    } catch (ErroreInternoDatabaseException $e) {
        redirect_to('internal_error.php');
    }
?>

<?php if (empty($copie)): ?>
<div class="alert alert-warning text-center" role="alert">
  Non ci sono copie
</div>
<?php else: ?>
<table class="table">
  <thead>
    <tr>
      <th scope="col">#</th>
      <th scope="col">Isbn</th>
      <th scope="col">Titolo</th>
      <th scope="col">Sede</th>
      <th scope="col"></th>
      <th scope="col"></th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($copie as $copia): ?>
      <tr>
        <th scope='row'><?= $copia['id'] ?></th>
        <td><?= $copia['libro'] ?></td>
        <?php 
          try {
            $isbn = new Isbn($copia['libro']);
            $libro = getLibroByIsbn($isbn);
            $titolo = $libro['titolo'];
          } catch (InvalidIsbnException $e) {
            // if this happends, the database is broken
          } catch (ErroreInternoDatabaseException $e) {
            $titolo = 'Non disponibile';
          }
        ?> 
        <td><?= $titolo ?></td>
        <?php
          try {
            $sede = getSedeById($copia['sede']);
            $indirizzo = $sede['indirizzo'] . ', ' . $sede['città'];
          } catch (ErroreInternoDatabaseException $e) {
            $indirizzo = 'Non disponibile';
          }
        ?>
        <td><?= $indirizzo ?></td>
        <td>
          <a class="btn btn-light" data-bs-toggle="collapse" 
             href="#collapse<?= $copia['id'] ?>" role="button">
            Cambia sede
          </a>
        </td>
        <td>
          <form action="<?= $redirectUrl ?>" method="POST">
            <input type="hidden" name="id" value="<?= $copia['id'] ?>">
            <input type="hidden" name="operazione" value="<?= TipoOperazione::RIMUOVI_COPIA->value ?>">
            <button type="submit" class="btn btn-danger">Rimuovi</button>
          </form>
        </td>
      </tr>
      <tr class="collapse" id="collapse<?= $copia['id'] ?>">
        <td colspan="5">
          <form action="<?= $redirectUrl ?>" method="POST">
            <div class="row">
              <div class="col-9">
                <input type="hidden" name="id" value="<?= $copia['id'] ?>">
                <input type="hidden" name="operazione" value="<?= TipoOperazione::CAMBIA_SEDE->value ?>">
                <select class="form-select <?= isset($errors[TipoOperazione::CAMBIA_SEDE->value]['sede']) ? 'border-danger' : '' ?>"
                        id="sede" name="sede" size="5">
                <?php foreach ($sedi as $sede): ?>
                  <option value="<?= $sede['id'] ?>"
                          <?php 
                            if (isset($inputs[TipoOperazione::CAMBIA_SEDE->value]['sede']) && $inputs[TipoOperazione::CAMBIA_SEDE->value]['sede'] == $sede['id'])
                              echo 'selected';
                          ?>>
                    <?= $sede['indirizzo'] . ', ' . $sede['città'] ?> 
                  </option>
                <?php endforeach; ?>
                </select>
              </div>
              <div class="col-3">
                  <button type="submit" class="btn btn-primary">Cambia</button>
              </div>
            </div>
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
      <label for="libro" class="form-label">Libro</label>
      <select class="form-select <?= isset($errors[TipoOperazione::AGGIUNGI_COPIA->value]['libro']) ? 'border-danger' : '' ?>"
              id="libro" name="libro" size="5">
        <?php foreach ($libri as $libro): ?>
          <option value="<?= $libro['isbn'] ?>"
                  <?php if (isset($inputs[TipoOperazione::AGGIUNGI_COPIA->value]['libro']) && $inputs[TipoOperazione::AGGIUNGI_COPIA->value]['libro'] == $libro['isbn'])
                    echo 'selected';
                  ?>>
            <?= $libro['titolo'] ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col">
      <label for="sede" class="form-label">Sede</label>
      <select class="form-select <?= isset($errors[TipoOperazione::AGGIUNGI_COPIA->value]['sede']) ? 'border-danger' : '' ?>" 
              id="sede" name="sede" size="5">
      <?php foreach ($sedi as $sede): ?>
        <option value="<?= $sede['id'] ?>"
                <?php 
                  if (isset($inputs[TipoOperazione::AGGIUNGI_COPIA->value]['sede']) && $inputs[TipoOperazione::AGGIUNGI_COPIA->value]['sede'] == $sede['id']) 
                    echo 'selected';
                ?>>
          <?= $sede['indirizzo'] . ', ' . $sede['città'] ?>
        </option>
      <?php endforeach; ?>
      </select>
    </div> 
  </div>
  <input type="hidden" name="operazione" value="<?= TipoOperazione::AGGIUNGI_COPIA->value ?>">
  <?php
    $aggiungiErrors = $errors[TipoOperazione::AGGIUNGI_COPIA->value] ?? [];
    $aggiungiErrors = array_filter($aggiungiErrors, fn($error) => !is_null($error) && $error !== '');

    $cambiaErrors = $errors[TipoOperazione::CAMBIA_SEDE->value] ?? [];
    $cambiaErrors = array_filter($cambiaErrors, fn($error) => !is_null($error) && $error !== '');

    $rimuoviErrors = $errors[TipoOperazione::RIMUOVI_COPIA->value] ?? [];
    $rimuoviErrors = array_filter($rimuoviErrors, fn($error) => !is_null($error) && $error !== '');
  ?>
  <p class="text-danger <?= empty($aggiungiErrors) ? 'd-none' : '' ?>">
    <?= join('<br>', $aggiungiErrors) ?>
  </p>
  <p class="text-danger <?= empty($cambiaErrors) ? 'd-none' : '' ?>">
    <?= join('<br>', $cambiaErrors) ?>
  </p>
  <p class="text-danger <?= empty($rimuoviErrors) ? 'd-none' : '' ?>">
    <?= join('<br>', $rimuoviErrors) ?>
  </p>
  <button type="submit" class="btn btn-primary">Aggiungi copia</button>
</form>
