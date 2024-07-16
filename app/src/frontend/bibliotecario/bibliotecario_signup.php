<?php 
    include 'utils.php';
    include 'backend.php';

    session_start(); 

    $errors = [];
    $inputs = $_POST;

    //controllaPermessiOReindirizza(Utente::BIBLIOTECARIO, $inputs, $errors, 'login.php');

    if (post()) {
        if (isset($inputs['email']) && isset($inputs['password'])) {
            $result = registraBibliotecario($inputs['email'], $inputs['password']);
            if ($result == DatabaseErrors::BIBLIOTECARIO_GIA_REGISTRATO) {
                $errors['email'] = 'border-danger';
                $errors['message'] = 'Email già registrata';

                redirect_with('bibliotecario_signup.php', [
                    'inputs' => $inputs, 
                    'errors' => $errors
                ]);
            } else if ($result == DatabaseErrors::ERRORE_INTERNO_DATABASE) {
                $errors['message'] = 'Errore interno del database';

                redirect_with('bibliotecario_signup.php', [
                    'inputs' => $inputs, 
                    'errors' => $errors
                ]);
            } else {
                unset($errors['email'], $errors['password'], $errors['message']);
                redirect_to('login.php');
            }
        } 
    } else if (get()) {
        [$inputs, $errors] = session_get('inputs', 'errors');
    }
?>

<!DOCTYPE html>

<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Signup</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    </head>
    <body>
        <div class="container">
            <div class="row justify-content-center pt-4">
                <div class="col p-4 bg-primary-subtle border rounded-4" style="max-width: 720px;">
                    <div class="row text-center">
                        <h1>Registra Bibliotecario</h1>
                    </div>
                    <form class="py-4" method="POST" action="bibliotecario_signup.php">
                        <label for="email">Email</label>
                        <input type="email" class="form-control mb-4 <?= $errors['email'] ?? '' ?>" id="email" name="email" value="<?= $inputs['email'] ?? '' ?>">
                        <label for="password">Password</label>
                        <input type="password" class="form-control mb-4 <?= $errors['password'] ?? '' ?>" id="password" name="password" value="<?= $inputs['email'] ?? '' ?>">
                        <p class="text-danger mb-4 <?= !isset($errors['message']) ? 'd-none' : '' ?>"><?= $errors['message'] ?? '' ?></p>
                        <button type="submit" class="btn btn-primary">Login</button>
                    </form>
                </div>
            </div>
        </div>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    </body>
</html>
