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
        $tipoOperazione = TipoOperazione::from($inputs['operazione'])->value;
        unset($inputs['operazione']);

        $classeOperazione = OPERAZIONI[$tipoOperazione];
        $operazione = new $classeOperazione($inputs);

        try {
            $errors = $operazione->esegui();

            foreach ($errors as $key => $value) {
                $errors[$tipoOperazione][$key] = $value;
                unset($errors[$key]);
            }

            if (empty($errors))
                $errors[$tipoOperazione] = [];

            foreach ($inputs as $key => $value) {
                $inputs[$tipoOperazione][$key] = $value;
                unset($inputs[$key]);
            }

            if (empty($inputs))
                $inputs[$tipoOperazione] = [];

            if (!empty($errors[$tipoOperazione])) {
                redirect_with('lettore.php?tab=' . $tab, [
                    'inputs' => $inputs,
                    'errors' => $errors
                ]);
            }

            if ($tipoOperazione === TipoOperazione::ELIMINA_ACCOUNT_LETTORE->value ||
                $tipoOperazione === TipoOperazione::LOGOUT->value)
            {
                redirect_to('login.php');
            }

            unset($inputs);
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
