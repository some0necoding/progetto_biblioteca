<?php 
    include 'utils.php';
    include 'backend.php';

    session_start(); 

    $errors = [];
    $inputs = [];

    if (post()) {
        $inputs = $_POST;

        $utente = $inputs['utente'] == 'bibliotecario' ? Utente::BIBLIOTECARIO : Utente::LETTORE;
        [$user_id, $error] = login($utente, $inputs['email'], $inputs['password']);
        if ($error == DatabaseErrors::PASSWORD_ERRATA) {
            $errors['email'] = 'border-danger';
            $errors['password'] = 'border-danger';
            $errors['message'] = 'Email o password non validi';

            redirect_with('login.php', [
                'inputs' => $inputs, 
                'errors' => $errors
            ]);
        } else if ($error == DatabaseErrors::ERRORE_INTERNO_DATABASE) {
            $errors['message'] = 'Errore interno del database';

            redirect_with('login.php', [
                'inputs' => $inputs, 
                'errors' => $errors
            ]);
        } else if ($error = DatabaseErrors::NESSUN_ERRORE) {
            unset($errors['email'], $errors['password'], $errors['message']);
            if ($inputs['utente'] == 'bibliotecario') {
                redirect_with('bibliotecario.php', [
                    'user_id' => $user_id
                ]);
            } else if ($inputs['utente'] == 'lettore') {
                redirect_with('lettore.php', [
                    'user_id' => $user_id
                ]);
            }
        }
    } else if (get()) {
        [$inputs, $errors, $user_id] = session_get('inputs', 'errors', 'user_id');
        if ($user_id != []) {
            [$esisteUtente, $utente] = esisteUtente($user_id);
            if ($esisteUtente) {
                if ($utente == Utente::BIBLIOTECARIO)
                    redirect_with('bibliotecario.php', [ 'user_id' => $user_id ]);
                else if ($utente == Utente::LETTORE)
                    redirect_with('lettore.php',       [ 'user_id' => $user_id ]);
            }
        }
    }
?>

<!DOCTYPE html>

<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Login</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    </head>
    <body>
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
                                    <option value="lettore" selected>Lettore</option>
                                    <option value="bibliotecario" >Bibliotecario</option>
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
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    </body>
</html>


