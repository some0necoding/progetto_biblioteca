<?php
    include '../src/utils.php';
    include '../src/backend.php';

    session_start(); 
    
    $errors = [];
    $inputs = $_POST;

    if (post()) {
        $inputs['utente'] = Utente::from($inputs['utente']);

        $error = login($inputs['utente'], $inputs['email'], $inputs['password']);
        switch($error) {
            case DatabaseErrors::PASSWORD_ERRATA:
                $errors['email'] = 'border-danger';
                $errors['password'] = 'border-danger';
                $errors['message'] = 'Email o password non validi';

                redirect_with('login.php', [
                    'inputs' => $inputs, 
                    'errors' => $errors
                ]);
            case DatabaseErrors::ERRORE_INTERNO_DATABASE:
                $errors['message'] = 'Errore interno del database';

                redirect_with('login.php', [
                    'inputs' => $inputs, 
                    'errors' => $errors
                ]);
            case DatabaseErrors::NESSUN_ERRORE:
                unset($errors['email'], $errors['password'], $errors['message']);
                redirect_to(strtolower($inputs['utente']->name) . ".php");
        } 
    } else if (get()) {
        [$inputs, $errors] = session_get('inputs', 'errors');

        foreach (Utente::cases() as $utente) {
            [$isLoggedIn, $error] = isLoggedIn($utente);
            if ($error == DatabaseErrors::ERRORE_INTERNO_DATABASE)
                redirect_to('internal_error.php');
            if ($isLoggedIn)
                redirect_to(strtolower($utente->name) . ".php");
        }
    }
?>

<?php view('header', ['title' => 'Login']) ?>

<div class="container">
    <div class="row justify-content-center pt-4">
        <div class="col p-4 bg-primary-subtle border rounded-4" style="max-width: 720px;">
            <div class="row text-center">
                <h1>Accedi</h1>
            </div>
            <form class="py-4" method="POST" action="login.php">
                <div class="row">
                    <div class="col">
                        <label for="email">Email</label>
                        <input type="email" class="form-control mb-4 <?= $errors['email'] ?? '' ?>" id="email" name="email" value="<?= $inputs['email'] ?? '' ?>">
                    </div>
                    <div class="col">
                        <label for="utente">Utente</label>
                        <select type="text" class="form-select mb-4" id="utente" name="utente">
                            <option value="<?= Utente::LETTORE->value ?>" selected>Lettore</option>
                            <option value="<?= Utente::BIBLIOTECARIO->value ?>">Bibliotecario</option>
                        </select>
                    </div>
                </div>
                <div class="row">
                    <div class="col">
                        <label for="password">Password</label>
                        <input type="password" class="form-control mb-4 <?= $errors['password'] ?? '' ?>" id="password" name="password" value="<?= $inputs['email'] ?? '' ?>">
</div>
                    </div>
                <p class="text-danger mb-4 <?= !isset($errors['message']) ? 'd-none' : '' ?>"><?= $errors['message'] ?? '' ?></p>
                <button type="submit" class="btn btn-primary">Login</button>
            </form>
        </div>
    </div>
</div>

<?php view('footer') ?>
