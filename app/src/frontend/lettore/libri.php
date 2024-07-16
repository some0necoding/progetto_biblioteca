<?php 
    include_once '../src/backend.php';
    
    $redirectUrl = 'lettore.php?tab=libri';

    $libri = getLibri();
    if ($libri == DatabaseErrors::ERRORE_INTERNO_DATABASE)
        redirect_to('internal_error.php');

    $sedi = getSedi();
    if ($sedi == DatabaseErrors::ERRORE_INTERNO_DATABASE)
        redirect_to('internal_error.php');

    $sedeCorrente = $_GET['sede'] ?? 0;
?>

<div class="row">
<select class="form-select" id="sede" name="sede" onchange="window.location = '<?= $redirectUrl . "&sede=" ?>' + this.selectedOptions[0].value;">
        <option value="0" <?= $sedeCorrente == 0 ? 'selected' : '' ?>>Tutte le sedi</option>
        <?php foreach($sedi as $sede): ?>
            <option value="<?= $sede['id'] ?>" <?= $sedeCorrente == $sede['id'] ? 'selected' : '' ?>><?= $sede['indirizzo'] . ', ' . $sede['città'] ?></option>
        <?php endforeach; ?>
    </select>
</div>
