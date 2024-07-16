<?php
    include_once 'backend.php';
    
    $redirectUrl = 'bibliotecario.php?tab=sedi';

    $sedi = getSedi();
    if ($sedi == DatabaseErrors::ERRORE_INTERNO_DATABASE)
        redirect_to('internal_error.php');
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
            <th scope="col">Isbn gestiti</th>
            <th scope="col">Copie gestite</th>
            <th scope="col">Prestiti in corso</th>
            <th scope="col"></th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($sedi as $sede): ?>
            <tr>
                <th scope='row'><?= $sede['id'] ?></th>
                <td><?= $sede['indirizzo'] ?></td>
                <td><?= $sede['città'] ?></td>
                <?php $isbnGestiti = getNumeroDiIsbnGestiti($sede['id']); ?>
                <?php if ($isbnGestiti == DatabaseErrors::ERRORE_INTERNO_DATABASE): ?>
                    <td>Errore</td>
                <?php else: ?>
                    <td><?= $isbnGestiti ?></td>
                <?php endif; ?>
                <?php $copieGestite = getNumeroDiCopieGestite($sede['id']); ?>
                <?php if ($copieGestite == DatabaseErrors::ERRORE_INTERNO_DATABASE): ?>
                    <td>Errore</td>
                <?php else: ?>
                    <td><?= $copieGestite ?></td>
                <?php endif; ?>
                <?php $prestitiInCorso = getNumeroDiPrestitiInCorso($sede['id']); ?>
                <?php if ($prestitiInCorso == DatabaseErrors::ERRORE_INTERNO_DATABASE): ?>
                    <td>Errore</td>
                <?php else: ?>
                    <td><?= $prestitiInCorso ?></td>
                <?php endif; ?>
                <td>
                    <form action="<?= $redirectUrl ?>" method="POST">
                        <input type="hidden" name="id" value="<?= $sede['id'] ?>">
                        <input type="hidden" name="operazione" value="<?= Operazione::RIMUOVI_SEDE->value ?>">
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
            <input type="text" class="form-control <?= isset($errors['indirizzo']) ? 'border-danger' : '' ?>" id="indirizzo" name="indirizzo" value="<?= $inputs['indirizzo'] ?? '' ?>">
        </div>
        <div class="col">
            <label for="citta" class="form-label">Città</label>
            <input type="text" class="form-control <?= isset($errors['citta']) ? 'border-danger' : '' ?>" id="citta" name="citta" value="<?= $inputs['citta'] ?? '' ?>">
        </div>
    </div>
    <input type="hidden" name="operazione" value="<?= Operazione::AGGIUNGI_SEDE->value ?>">
    <p class="text-danger"><?= join('<br>', $errors) ?></p>
    <button type="submit" class="btn btn-primary">Aggiungi sede</button>
</form>
