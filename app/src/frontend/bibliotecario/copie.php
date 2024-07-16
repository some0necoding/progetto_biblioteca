<?php
    include_once 'backend.php';
    
    $redirectUrl = 'bibliotecario.php?tab=copie';

    $copie = getCopie();
    if ($copie == DatabaseErrors::ERRORE_INTERNO_DATABASE)
        redirect_to('internal_error.php');

    $libri = getLibri();
    if ($libri == DatabaseErrors::ERRORE_INTERNO_DATABASE)
        redirect_to('internal_error.php');
    
    $sedi = getSedi();
    if ($sedi == DatabaseErrors::ERRORE_INTERNO_DATABASE)
        redirect_to('internal_error.php');
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
                <?php $libro = getLibroByIsbn($copia['libro'])  ?> 
                <?php if ($libro == DatabaseErrors::ERRORE_INTERNO_DATABASE): ?>
                    <td><?= $copia['libro'] ?></td>
                <?php else: ?>
                    <td><?= $libro['titolo'] ?></td>
                <?php endif; ?>
                <?php $sede = getSedeById($copia['sede'])  ?> 
                <?php if ($sede == DatabaseErrors::ERRORE_INTERNO_DATABASE): ?>
                    <td><?= $copia['sede'] ?></td>
                <?php else: ?>
                    <td><?= $sede['indirizzo'] . ', ' . $sede['città'] ?></td>
                <?php endif; ?>
                <td>
                    <a class="btn btn-light" data-bs-toggle="collapse" href="#collapse<?= $copia['id'] ?>" role="button">
                        Cambia sede
                    </a>
                </td>
                <td>
                    <form action="<?= $redirectUrl ?>" method="POST">
                        <input type="hidden" name="id" value="<?= $copia['id'] ?>">
                        <input type="hidden" name="operazione" value="<?= Operazione::RIMUOVI_COPIA->value ?>">
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
                                <input type="hidden" name="operazione" value="<?= Operazione::CAMBIA_SEDE->value ?>">
                                <select class="form-select <?= isset($errors['nuova_sede']) ? 'border-danger' : '' ?>" id="nuova_sede" name="nuova_sede" size="5">
                                <?php foreach ($sedi as $sede): ?>
                                    <option value="<?= $sede['id'] ?>" <?= (isset($inputs['nuova_sede']) && $inputs['nuova_sede'] == $sede['id']) ? 'selected' : '' ?>><?= $sede['indirizzo'] . ', ' . $sede['città'] ?></option>
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
            <select class="form-select <?= isset($errors['libro']) ? 'border-danger' : '' ?>" id="libro" name="libro" size="5">
            <?php foreach ($libri as $libro): ?>
                <option value="<?= $libro['isbn'] ?>" <?= (isset($inputs['libro']) && $inputs['libro'] == $libro['isbn']) ? 'selected' : '' ?>><?= $libro['titolo'] ?></option>
            <?php endforeach; ?>
            </select>
        </div>
        <div class="col">
            <label for="sede" class="form-label">Sede</label>
            <select class="form-select <?= isset($errors['sede']) ? 'border-danger' : '' ?>" id="sede" name="sede" size="5">
            <?php foreach ($sedi as $sede): ?>
                <option value="<?= $sede['id'] ?>" <?= (isset($inputs['sede']) && $inputs['sede'] == $sede['id']) ? 'selected' : '' ?>><?= $sede['indirizzo'] . ', ' . $sede['città'] ?></option>
            <?php endforeach; ?>
            </select>
        </div> 
    </div>
    <input type="hidden" name="operazione" value="<?= Operazione::AGGIUNGI_COPIA->value ?>">
    <p class="text-danger"><?= join('<br>', $errors) ?></p>
    <button type="submit" class="btn btn-primary">Aggiungi copia</button>
</form>
