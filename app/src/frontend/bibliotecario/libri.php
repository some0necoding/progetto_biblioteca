<?php
    include_once '../src/backend/backend.php';
    
    $redirectUrl = 'bibliotecario.php?tab=libri';

    try {
        $libri = getLibri();
        $autori = getAutori();
    } catch (ErroreInternoDatabaseException $e) {
        redirect_to('internal_error.php');
    }
?>

<?php if (empty($libri)): ?>
<div class="alert alert-warning text-center" role="alert">
  Non ci sono libri
</div>
<?php else: ?>
<table class="table">
  <thead>
    <tr>
      <th scope="col">isbn</th>
      <th scope="col">titolo</th>
      <th scope="col">autori</th>
      <th scope="col">trama</th>
      <th scope="col">casa editrice</th>
      <th scope="col"></th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($libri as $libro): ?>
      <tr>
        <td scope='row'><?= $libro['isbn'] ?></td>
        <td><?= $libro['titolo'] ?></td>
        <?php
          try {
            $isbn = new Isbn($libro['isbn']);
            $autori = getAutoriByIsbn($isbn);
            $listaAutori = "";
            foreach ($autori as $autore) {
              $listaAutori .= $autore['nome'] . ' ' . $autore['cognome'] . ', '; 
            }
            $listaAutori = rtrim($listaAutori, ", ");
          } catch (InvalidIsbnException $e) {
            // if this happens, the database is broken
          } catch (ErroreInternoDatabaseException $e) {
            $listaAutori = "Non disponibili";
          }
        ?>
        <td><?= $listaAutori ?></td>
        <td><?= $libro['trama'] ?></td>
        <td><?= $libro['casa_editrice'] ?></td>
        <td>
          <form action="<?= $redirectUrl ?>" method="POST">
            <input type="hidden" name="isbn" value="<?= $libro['isbn'] ?>">
            <input type="hidden" name="operazione" value="<?= TipoOperazione::RIMUOVI_LIBRO->value ?>">
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
    <div class="col-4">
      <label for="isbn" class="form-label">Isbn</label>
      <input type="number" class="form-control <?= isset($errors[TipoOperazione::AGGIUNGI_LIBRO->value]['isbn']) ? 'border-danger' : '' ?>" 
             id="isbn" name="isbn" value="<?= $inputs[TipoOperazione::AGGIUNGI_LIBRO->value]['isbn'] ?? '' ?>">
    </div>
    <div class="col-4">
      <label for="titolo" class="form-label">Titolo</label>
      <input type="text" class="form-control <?= isset($errors[TipoOperazione::AGGIUNGI_LIBRO->value]['titolo']) ? 'border-danger' : '' ?>"
             id="titolo" name="titolo" value="<?= $inputs[TipoOperazione::AGGIUNGI_LIBRO->value]['titolo'] ?? '' ?>">
    </div>
    <div class="col-4">
      <label for="casa_editrice" class="form-label">Casa Editrice</label>
      <input type="text" class="form-control <?= isset($errors[TipoOperazione::AGGIUNGI_LIBRO->value]['casa_editrice']) ? 'border-danger' : '' ?>"
             id="casa_editrice" name="casa_editrice" value="<?= $inputs[TipoOperazione::AGGIUNGI_LIBRO->value]['casa_editrice'] ?? '' ?>">
    </div>
  </div>
  <div class="row mb-3">
    <div class="col">
      <label for="trama" class="form-label">Trama</label>
      <textarea class="form-control" id="trama" name="trama" value="<?= $inputs[TipoOperazione::AGGIUNGI_LIBRO->value]['trama'] ?? '' ?>"></textarea>
    </div>
  </div>
  <div class="row mb-3">
    <div class="col">
      <label for="autori" class="form-label">Autori</label>
      <select class="form-select <?= isset($errors[TipoOperazione::AGGIUNGI_LIBRO->value]['autori']) ? 'border-danger' : '' ?>" id="autori" name="autori[]" aria-label="multiple select example" size="5" multiple>
      <?php foreach ($autori as $autore): ?>
        <option value="<?= $autore['id'] ?>" <?= (isset($inputs[TipoOperazione::AGGIUNGI_LIBRO->value]['autori']) && in_array($autore['id'], $inputs[TipoOperazione::AGGIUNGI_LIBRO->value]['autori'])) ? 'selected' : '' ?>>
          <?= $autore['nome'] . ' ' . $autore['cognome'] ?>
        </option>
      <?php endforeach; ?>
      </select>
    </div>
  </div>
  <input type="hidden" name="operazione" value="<?= TipoOperazione::AGGIUNGI_LIBRO->value ?>">
  <?php
    $aggiungiErrors = $errors[TipoOperazione::AGGIUNGI_LIBRO->value] ?? [];
    $aggiungiErrors = array_filter($aggiungiErrors, fn($error) => !is_null($error) && $error !== '');

    $rimuoviErrors = $errors[TipoOperazione::RIMUOVI_LIBRO->value] ?? [];
    $rimuoviErrors = array_filter($rimuoviErrors, fn($error) => !is_null($error) && $error !== '');
  ?>
  <p class="text-danger <?= empty($aggiungiErrors) ? 'd-none' : '' ?>">
    <?= join('<br>', $aggiungiErrors) ?>
  </p>
  <p class="text-danger <?= empty($rimuoviErrors) ? 'd-none' : '' ?>">
    <?= join('<br>', $rimuoviErrors) ?>
  </p>
  <button type="submit" class="btn btn-primary">Aggiungi libro</button>
</form>
