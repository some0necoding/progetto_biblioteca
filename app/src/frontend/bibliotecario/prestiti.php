<?php
    include_once '../src/backend/backend.php';
    
    $redirectUrl = 'bibliotecario.php?tab=prestiti';

    try {
        $prestiti = getPrestiti();
    } catch (ErroreInternoDatabaseException $e) {
        redirect_to('internal_error.php');
    }
?>

<?php if (empty($prestiti)): ?>
<div class="alert alert-warning text-center" role="alert">
    Non ci sono prestiti in corso
</div>
<?php else: ?>
<table class="table">
  <thead>
    <tr>
      <th scope="col">Copia</th>
      <th scope="col">Lettore</th>
      <th scope="col">Data Inizio</th>
      <th scope="col">Data Scadenza</th>
      <th scope="col">Stato</th>
      <th scope="col"></th>
      <th scope="col"></th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($prestiti as $prestito): ?>
      <tr>
        <?php 
          try {
              $copia = getCopiaById($prestito['copia']);
              $isbn = new Isbn($copia['libro']);
              $libro = getLibroByIsbn($isbn);
              $titolo = $libro['titolo'];
          } catch (InvalidIsbnException $e) {
              // if this happens, the database is broken
          } catch (ErroreInternoDatabaseException $e) {
              $titolo = 'Non disponibile';
          }
        ?>
        <td><?= $titolo ?></td>
        <?php
          try {
              $codice_fiscale = new CodiceFiscale($prestito['lettore']);
              $lettore = getLettoreByCodiceFiscale($codice_fiscale);
              $nome = $lettore['nome'] . ' ' . $lettore['cognome'];
          } catch (InvalidCodiceFiscaleException $e) {
              // if this happens, the database is broken
          } catch (ErroreInternoDatabaseException $e) {
              $nome = 'Non disponibile';
          }
        ?>
        <td><?= $nome ?></td>
        <td><?= $prestito['inizio'] ?></td>
        <td><?= $prestito['scadenza'] ?></td>
        <?php if ($prestito['scadenza'] < date('Y-m-d')): ?>
          <td class="text-danger">In ritardo</td>
        <?php else: ?>
          <td>Attivo</td>
        <?php endif; ?>
        <td>
          <a class="btn btn-light" data-bs-toggle="collapse"
             href="#collapse<?= $prestito['copia'] ?>" role="button">
              Proroga
          </a>
        </td>
        <td>
          <form action="<?= $redirectUrl ?>" method="POST">
            <input type="hidden" name="copia" value="<?= $prestito['copia'] ?>">
            <input type="hidden" name="operazione" value="<?= TipoOperazione::RESTITUISCI_PRESTITO->value ?>">
            <button type="submit" class="btn btn-primary">Restituisci</button>
          </form>
        </td>
      </tr>
      <tr class="collapse" id="collapse<?= $prestito['copia'] ?>">
        <td colspan="7">
          <form action="<?= $redirectUrl ?>" method="POST" class="d-flex">
            <input type="hidden" name="copia" value="<?= $prestito['copia'] ?>">
            <input type="hidden" name="operazione" value="<?= TipoOperazione::PROROGA_PRESTITO->value ?>">
            <input type="number" name="giorni_di_proroga" class="form-control me-3" placeholder="Giorni di proroga" required>
            <button type="submit" class="btn btn-primary">Proroga</button>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>
<?php endif; ?>
