<?php
    include_once 'backend.php';
    
    $redirectUrl = 'bibliotecario.php?tab=libri';

    $libri = getLibri();
    if ($libri == DatabaseErrors::ERRORE_INTERNO_DATABASE)
        redirect_to('internal_error.php');

    $autori = getAutori();
    if ($autori == DatabaseErrors::ERRORE_INTERNO_DATABASE)
        redirect_to('internal_error.php');
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
            <th scope="col">trama</th>
            <th scope="col">casa editrice</th>
            <th scope="col"></th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($libri as $libro): ?>
            <tr>
                <th scope='row'><?= $libro['isbn'] ?></th>
                <td><?= $libro['titolo'] ?></td>
                <td><?= $libro['trama'] ?></td>
                <td><?= $libro['casa_editrice'] ?></td>
                <td>
                    <form action="<?= $redirectUrl ?>" method="POST">
                        <input type="hidden" name="isbn" value="<?= $libro['isbn'] ?>">
                        <input type="hidden" name="operazione" value="<?= Operazione::RIMUOVI_LIBRO->value ?>">
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
            <input type="number" class="form-control <?= isset($errors['isbn']) ? 'border-danger' : '' ?>" id="isbn" name="isbn" value="<?= $inputs['isbn'] ?? '' ?>">
        </div>
        <div class="col-4">
            <label for="titolo" class="form-label">Titolo</label>
            <input type="text" class="form-control <?= isset($errors['titolo']) ? 'border-danger' : '' ?>" id="titolo" name="titolo" value="<?= $inputs['titolo'] ?? '' ?>">
        </div>
        <div class="col-4">
            <label for="casa_editrice" class="form-label">Casa Editrice</label>
            <input type="text" class="form-control <?= isset($errors['casa_editrice']) ? 'border-danger' : '' ?>" id="casa_editrice" name="casa_editrice" value="<?= $inputs['casa_editrice'] ?? '' ?>">
        </div>
    </div>
    <div class="row mb-3">
        <div class="col">
            <label for="trama" class="form-label">Trama</label>
            <textarea class="form-control" id="trama" name="trama" value="<?= $inputs['trama'] ?? '' ?>"></textarea>
        </div>
    </div>
    <div class="row mb-3">
        <div class="col">
            <label for="autori" class="form-label">Autori</label>
            <select class="form-select <?= isset($errors['autori']) ? 'border-danger' : '' ?>" id="autori" name="autori[]" aria-label="multiple select example" size="5" multiple>
            <?php foreach ($autori as $autore): ?>
                <option value="<?= $autore['id'] ?>" <?= (isset($inputs['autori']) && in_array($autore['id'], $inputs['autori'])) ? 'selected' : '' ?>><?= $autore['nome'] . ' ' . $autore['cognome'] ?></option>
            <?php endforeach; ?>
            </select>
        </div>
    </div>
    <input type="hidden" name="operazione" value="<?= Operazione::AGGIUNGI_LIBRO->value ?>">
    <p class="text-danger"><?= join('<br>', $errors) ?></p>
    <button type="submit" class="btn btn-primary">Aggiungi libro</button>
</form>
