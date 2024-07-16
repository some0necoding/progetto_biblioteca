<?php
    include '../src/utils.php';
    include '../src/backend.php';

    session_start();

    $errors = [];
    $inputs = $_POST;

    $tab = $_GET['tab'] ?? 'libri';

    controllaPermessiOReindirizza(Utente::LETTORE, $inputs, $errors, 'login.php');

    if (post()) {
        $inputs['operazione'] = Operazione::from($inputs['operazione']);
        switch($inputs['operazione']) {
            case Operazione::RICHIEDI_PRESTITO:
        }
    } else if (get()) {
        [$inputs, $errors] = session_get('inputs', 'errors');
    }

?>

<?php view('header', ['title' => 'Lettore']); ?>
<?php view('lettore/lettore_navbar', [ 'active' => $tab, 'page' => 'lettore.php' ]); ?>
<div class="container">
    <div class="row">
        <div class="col mt-4">
            <?php 
                view('lettore/' . $tab, [
                    'inputs' => $inputs,
                    'errors' => $errors
                ]);
            ?>
        </div>
    </div>
</div>
<?php view('footer'); ?>
