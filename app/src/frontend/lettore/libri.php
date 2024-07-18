<?php 
    include_once '../src/backend/backend.php';
    include_once '../src/frontend/operazioni.php';
    
    $redirectUrl = 'lettore.php?tab=libri';

    $sedeCorrente = $_GET['sede'] ?? 0;

    try {
        if ($sedeCorrente == 0)
            $libri = getLibri();
        else
            $libri = getLibriBySede($sedeCorrente);
        $sedi = getSedi();
    } catch (ErroreInternoDatabaseException $e) {
        redirect_to('internal_error.php');
    }
?>

<select class="form-select" id="sede" name="sede" onchange="window.location = '<?= $redirectUrl . "&sede=" ?>' + this.selectedOptions[0].value;">
  <option value="0" <?= $sedeCorrente == 0 ? 'selected' : '' ?>>Tutte le sedi</option>
  <?php foreach($sedi as $sede): ?>
  <option value="<?= $sede['id'] ?>" <?= $sedeCorrente == $sede['id'] ? 'selected' : '' ?>><?= $sede['indirizzo'] . ', ' . $sede['città'] ?></option>
  <?php endforeach; ?>
</select>
<div class="row row-cols-5 mt-4 g-2" style="margin: auto">
  <?php foreach($libri as $libro): ?>
    <?php $isdisponibile = $libro['isdisponibile'] == "t"; ?>
    <div class="col">
      <div class="card h-100 <?= !$isdisponibile ? 'bg-secondary-subtle' : '' ?>">
        <div class="card-body">
          <h5 class="card-title"><?= $libro['titolo'] ?></h5>
          <?php
            try {
              $isbn = new Isbn($libro['isbn']);
              $autori = getAutoriByIsbn($isbn);
              $listaAutori = '';
              foreach($autori as $autore) {
                $listaAutori .= $autore['nome'] . ' ' . $autore['cognome'] . ', ';
              }
              $listaAutori = rtrim($listaAutori, ', ');
            } catch (InvalidIsbnException $e) {
              // if this happens, the database is broken
            } catch (ErroreInternoDatabaseException $e) {
              $listaAutori = 'Non disponibile';
            }
          ?>
          <h6 class="card-subtitle mt-2 text-body-secondary"><?= $listaAutori ?></h6>
          <p class="card-subtitle mt-1 mb-3 text-body-secondary"><?= $libro['casa_editrice'] ?></p>
          <p class="card-text"><?= $libro['trama'] ?></p>
        </div>
        <div class="card-footer d-flex justify-content-center bg-transparent border border-0 mb-2">
          <?php if ($isdisponibile): ?>
          <form action="<?= $redirectUrl ?>" method="POST">
            <input type="hidden" name="libro" value="<?= $libro['isbn'] ?>">
            <input type="hidden" name="sede" value="<?= $sedeCorrente == 0 ? '' : $sedeCorrente ?>">
            <input type="hidden" name="lettore" value="<?= $_SESSION['user_id'] ?>">
            <input type="hidden" name="operazione" value="<?= TipoOperazione::RICHIEDI_PRESTITO->value ?>">
            <button type="submit" class="btn btn-primary">Prendi in prestito</button>
          </form>
          <?php else: ?>
          <p class="text-danger">Non disponibile</p>
          <?php endif; ?>
        </div>
      </div>
    </div>
  <?php endforeach; ?>
</div>
