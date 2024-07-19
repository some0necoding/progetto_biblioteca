<?php
    include_once '../src/frontend/utils.php';
    include_once '../src/backend/backend.php';
    include_once '../src/frontend/operazioni.php';

    $errors = [];
    $inputs = $_POST;

    $tab = $_GET['tab'] ?? 'libri';

    if (!isLoggedIn(Utente::LETTORE))
        redirect_to('login.php');

    if (post()) {
        $tipoOperazione = TipoOperazione::from($inputs['operazione']);
        unset($inputs['operazione']);

        $classeOperazione = OPERAZIONI[$tipoOperazione->value];
        $operazione = new $classeOperazione($inputs);

        try {
            $errors = $operazione->esegui();

            // sposta tutti gli elementi di $inputs (i.e. $_POST) dentro
            // $inputs[$tipoOperazione] per distinguerli dagli input di altre
            // operazioni nel frontend.
            classificaPerOperazione($inputs, $tipoOperazione);

            // lo stesso per $errors
            classificaPerOperazione($errors, $tipoOperazione);

            if (!empty($errors[$tipoOperazione->value])) {
                redirect_with('lettore.php?tab=' . $tab, [
                    'inputs' => $inputs,
                    'errors' => $errors
                ]);
            }

            if ($tipoOperazione === TipoOperazione::ELIMINA_ACCOUNT_LETTORE ||
                $tipoOperazione === TipoOperazione::LOGOUT)
            {
                redirect_to('login.php');
            }

            unset($inputs[$tipoOperazione->value]);
        } catch (ErroreInternoDatabaseException $e) {
            redirect_to('internal_error.php');
        }

    } else if (get()) {
        [$inputs, $errors] = session_get('inputs', 'errors');
    }
?>

<?php view('header', ['title' => 'Lettore']); ?>
<?php view('lettore/navbar', [ 'active' => $tab, 'page' => 'lettore.php' ]); ?>
<div class="container">
    <div class="row">
        <div class="col mt-4 p-0">
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
