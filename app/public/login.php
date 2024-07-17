<?php
    include_once '../src/frontend/utils.php';
    include_once '../src/backend/backend.php';
    include_once '../src/frontend/operazioni.php';

    $errors = [];
    $inputs = $_POST;

    if (post()) {
        $login = new Login($inputs);

        try {
            $errors = $login->esegui();
            if (!empty($errors)) {
                redirect_with('login.php', [
                    'inputs' => $inputs,
                    'errors' => $errors
                ]);
            }
        } catch (ErroreInternoDatabaseException $e) {
            redirect_to('internal_error.php');
        }

        $utente = Utente::from($inputs['utente']);
        redirect_to(strtolower($utente->name) . ".php");
    } else if (get()) {
        [$inputs, $errors] = session_get('inputs', 'errors');

        foreach (Utente::cases() as $utente) {
            try {
                if (isLoggedIn($utente))
                    redirect_to(strtolower($utente->name) . ".php");
            } catch (ErroreInternoDatabaseException $e) {
                redirect_to('internal_error.php');
            }
        }
    }
?>

<?php view('header', ['title' => 'Login']) ?>

<div class="container">
    <div class="row vh-100 justify-content-center align-items-center">
        <div class="col p-4 bg-primary text-center border rounded-5" style="max-width: 512px;">
            <h1 class="my-5">Accedi</h1>
            <form class="py-4" method="POST" action="login.php">
                <div class="input-group mb-4">
                    <input type="email" class="form-control mb-4 <?= isset($errors['email']) ? 'border-danger' : '' ?>"
                           id="email" name="email" placeholder="Email" value="<?= $inputs['email'] ?? '' ?>">
                    <select type="text" class="form-select mb-4" id="utente" name="utente">
                        <option value="<?= Utente::LETTORE->value ?>" selected>Lettore</option>
                        <option value="<?= Utente::BIBLIOTECARIO->value ?>">Bibliotecario</option>
                    </select>
                </div>
                <input type="password" class="form-control mb-4 <?= isset($errors['password']) ? 'border-danger' : '' ?>"
                       id="password" name="password" placeholder="Password" value="<?= $inputs['email'] ?? '' ?>">
                <p class="text-danger mb-4 <?= empty($errors) ? 'd-none' : '' ?>"><?= join("<br>", $errors) ?></p>
                <button type="submit" class="btn btn-light mt-4 mb-5 w-25 border rounded-5">
                    Login
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                         class="bi bi-box-arrow-in-right" viewBox="0 0 16 16">
                        <path fill-rule="evenodd" d="M6 3.5a.5.5 0 0 1 .5-.5h8a.5.5 0 0 1 .5.5v9a.5.5 0 0 1-.5.5h-8a.5.5 0 0 1-.5-.5v-2a.5.5 0 0 0-1 0v2A1.5 1.5 0 0 0 6.5 14h8a1.5 1.5 0 0 0 1.5-1.5v-9A1.5 1.5 0 0 0 14.5 2h-8A1.5 1.5 0 0 0 5 3.5v2a.5.5 0 0 0 1 0z"/>
                        <path fill-rule="evenodd" d="M11.854 8.354a.5.5 0 0 0 0-.708l-3-3a.5.5 0 1 0-.708.708L10.293 7.5H1.5a.5.5 0 0 0 0 1h8.793l-2.147 2.146a.5.5 0 0 0 .708.708z"/>
                    </svg>
                </button>
            </form>
        </div>
    </div>
</div>

<?php view('footer') ?>
