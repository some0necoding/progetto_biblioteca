<?php
    include_once 'backend.php';
    
    $redirectUrl = 'bibliotecario.php?tab=prestiti';

    $prestiti = getPrestiti();
    if ($prestiti == DatabaseErrors::ERRORE_INTERNO_DATABASE)
        redirect_to('internal_error.php');
?>

<?php if (empty($prestiti)): ?>
<div class="alert alert-warning text-center" role="alert">
    Non ci sono prestiti
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
        </tr>
    </thead>
    <tbody>
        <?php foreach ($prestiti as $prestito): ?>
            <tr>
                <?php $copia = getCopiaById($prestito['copia']); ?>
                <?php if ($copia == DatabaseErrors::ERRORE_INTERNO_DATABASE): ?>
                    <td>Errore</td>
                <?php else: ?>
                    <?php $libro = getLibroByIsbn($copia['isbn']); ?>
                    <?php if ($libro == DatabaseErrors::ERRORE_INTERNO_DATABASE): ?>
                        <td>Errore</td>
                    <?php else: ?>
                        <td><?= $libro['titolo'] ?></td>
                    <?php endif; ?> 
                <?php endif; ?>
                <?php $lettore = getLettoreByCodiceFiscale($prestito['lettore']); ?>
                <?php if ($lettore == DatabaseErrors::ERRORE_INTERNO_DATABASE): ?>
                    <td>Errore</td>
                <?php else: ?>
                    <td><?= $lettore['nome'] . ' ' . $lettore['cognome'] ?></td>
                <?php endif; ?>
                <td><?= $prestito['data_inizio'] ?></td>
                <td><?= $prestito['data_scadenza'] ?></td>
                <?php if ($prestito['data_scadenza'] < date('Y-m-d')): ?>
                    <td class="text-danger">In ritardo</td>
                <?php else: ?>
                    <td>Attivo</td>
                <?php endif; ?>
                <td>
                    <a class="btn btn-light" data-bs-toggle="collapse" href="#collapse<?= $prestito['copia'] ?>" role="button">
                        Proroga
                    </a>
                </td>
            </tr>
            <tr class="collapse" id="collapse<?= $prestito['copia'] ?>">
                <td colspan="6">
                    <form action="<?= $redirectUrl ?>" method="POST">
                        <input type="hidden" name="copia" value="<?= $prestito['copia'] ?>">
                        <input type="hidden" name="operazione" value="<?= Operazione::PROROGA_PRESTITO->value ?>">
                        <input type="number" name="giorni_di_proroga" class="form-control" placeholder="Giorni di proroga" required>
                        <button type="submit" class="btn btn-primary">Proroga</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php endif; ?>
