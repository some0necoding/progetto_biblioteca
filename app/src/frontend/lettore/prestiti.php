<?php
    include_once '../src/backend/backend.php';
    
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
      <th scope="col">Data Inizio</th>
      <th scope="col">Data Scadenza</th>
      <th scope="col">Stato</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($prestiti as $prestito): ?>
      <?php if ($prestito['lettore'] == $_SESSION['user_id']): ?>
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
          <td><?= $prestito['inizio'] ?></td>
          <td><?= $prestito['scadenza'] ?></td>
          <?php if ($prestito['scadenza'] < date('Y-m-d')): ?>
            <td class="text-danger">In ritardo</td>
          <?php else: ?>
            <td>Attivo</td>
          <?php endif; ?>
        </tr>
      <?php endif; ?>
    <?php endforeach; ?>
  </tbody>
</table>
<?php endif; ?>
