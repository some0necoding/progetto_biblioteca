<?php
    include_once 'utils.php';
    include_once 'backend.php';

    session_start(); 

    $errors = [];
    $inputs = $_POST;

    $tab = $_GET['tab'] ?? 'autori';

    controllaPermessiOReindirizza(Utente::BIBLIOTECARIO, $inputs, $errors, 'login.php');

    if (post()) {
        $inputs['operazione'] = Operazione::from($inputs['operazione']);
        switch($inputs['operazione']) {
            case Operazione::AGGIUNGI_AUTORE:
                if (empty($inputs['nome']))
                    $errors['nome'] = 'Il nome è obbligatorio';
                if (empty($inputs['cognome']))
                    $errors['cognome'] = 'Il cognome è obbligatorio';
                if (empty($inputs['data_di_nascita']))
                    $errors['data_di_nascita'] = 'La data di nascita è obbligatoria';

                if (!empty($errors))
                    redirect_with('bibliotecario.php?tab=' . $tab, [ 
                        'inputs' => $inputs,
                        'errors' => $errors 
                    ]);

                $error = aggiungiAutore($inputs['nome'],
                                        $inputs['cognome'],
                                        $inputs['biografia'],
                                        $inputs['data_di_nascita'],
                                        $inputs['data_di_morte']);

                if ($error == DatabaseErrors::ERRORE_INTERNO_DATABASE)
                    redirect_to('internal_error.php');
                else
                    redirect_to('bibliotecario.php?tab=' . $tab);
            case Operazione::SET_DATA_DI_MORTE_AUTORE:
                $error = setAutoreDataDiMorte($inputs['id'], $inputs['data_di_morte']);
                
                if ($error == DatabaseErrors::ERRORE_INTERNO_DATABASE)
                    redirect_to('internal_error.php');
                else
                    redirect_to('bibliotecario.php?tab=' . $tab); 
            case Operazione::RIMUOVI_AUTORE:
                $error = rimuoviAutore($inputs['id']);
                
                if ($error == DatabaseErrors::LIBRI_ASSOCIATI_AD_AUTORE) {
                    $errors['message'] = 'Non puoi rimuovere un autore con libri associati';
                    redirect_with('bibliotecario.php?tab=' . $tab, [
                        'inputs' => $inputs,
                        'errors' => $errors
                    ]);
                } else if ($error == DatabaseErrors::ERRORE_INTERNO_DATABASE)
                    redirect_to('internal_error.php');
                else
                    redirect_to('bibliotecario.php?tab=' . $tab);
            case Operazione::AGGIUNGI_LIBRO:
                if (empty($inputs['isbn']))
                    $errors['isbn'] = 'L\'isbn è obbligatorio';
                if (empty($inputs['titolo']))
                    $errors['titolo'] = 'Il titolo è obbligatorio';
                if (empty($inputs['casa_editrice']))
                    $errors['casa_editrice'] = 'La casa editrice è obbligatoria';
                if (empty($inputs['autori']))
                    $errors['autori'] = 'Gli autori sono obbligatori';
                
                if (!empty($errors))
                    redirect_with('bibliotecario.php?tab=' . $tab, [ 
                        'inputs' => $inputs,
                        'errors' => $errors 
                    ]);

                $error = aggiungiLibro($inputs['isbn'],
                                        $inputs['titolo'],
                                        $inputs['trama'],
                                        $inputs['casa_editrice'],
                                        $inputs['autori']);

                if ($error == DatabaseErrors::ERRORE_INTERNO_DATABASE)
                    redirect_to('internal_error.php');
                else
                    redirect_to('bibliotecario.php?tab=' . $tab);
            case Operazione::RIMUOVI_LIBRO:
                $error = rimuoviLibro($inputs['isbn']);
                
                if ($error == DatabaseErrors::COPIE_ASSOCIATE_A_LIBRO)
                    $errors['message'] = 'Non puoi rimuovere un libro con copie associate';
                    redirect_with('bibliotecario.php?tab=' . $tab, [
                        'inputs' => $inputs,
                        'errors' => $errors
                    ]);
                if ($error == DatabaseErrors::ERRORE_INTERNO_DATABASE)
                    redirect_to('internal_error.php');
                else
                    redirect_to('bibliotecario.php?tab=' . $tab);
            case Operazione::AGGIUNGI_SEDE:
                if (empty($inputs['indirizzo']))
                    $errors['indirizzo'] = 'L\'indirizzo è obbligatorio';
                if (empty($inputs['citta']))
                    $errors['citta'] = 'La città è obbligatoria';

                if (!empty($errors))
                    redirect_with('bibliotecario.php?tab=' . $tab, [ 
                        'inputs' => $inputs,
                        'errors' => $errors 
                    ]);

                $error = aggiungiSede($inputs['indirizzo'], $inputs['citta']);
                
                if ($error == DatabaseErrors::ERRORE_INTERNO_DATABASE)
                    redirect_to('internal_error.php');
                else
                    redirect_to('bibliotecario.php?tab=' . $tab);
            case Operazione::RIMUOVI_SEDE:
                $error = rimuoviSede($inputs['id']);
                
                if ($error == DatabaseErrors::COPIE_ASSOCIATE_A_SEDE) {
                    $errors['message'] = 'Non puoi rimuovere una sede con copie associate';
                    redirect_with('bibliotecario.php?tab=' . $tab, [
                        'inputs' => $inputs,
                        'errors' => $errors
                    ]);
                } else if ($error == DatabaseErrors::ERRORE_INTERNO_DATABASE)
                    redirect_to('internal_error.php');
                else
                    redirect_to('bibliotecario.php?tab=' . $tab);
            case Operazione::AGGIUNGI_COPIA:
                if (empty($inputs['libro']))
                    $errors['libro'] = 'Devi selezionare un libro';
                if (empty($inputs['sede']))
                    $errors['sede'] = 'Devi selezionare una sede';

                if (!empty($errors))
                    redirect_with('bibliotecario.php?tab=' . $tab, [ 
                        'inputs' => $inputs,
                        'errors' => $errors 
                    ]);

                $error = aggiungiCopia($inputs['libro'], $inputs['sede']);

                if ($error == DatabaseErrors::ERRORE_INTERNO_DATABASE)
                    redirect_to('internal_error.php');
                else
                    redirect_to('bibliotecario.php?tab=' . $tab);
            case Operazione::CAMBIA_SEDE:
                if (empty($inputs['nuova_sede']))
                    $errors['nuova_sede'] = 'Devi selezionare una sede';

                if (!empty($errors))
                    redirect_with('bibliotecario.php?tab=' . $tab, [ 
                        'inputs' => $inputs,
                        'errors' => $errors 
                    ]);

                $error = cambiaSede($inputs['id'], $inputs['nuova_sede']);

                if ($error == DatabaseErrors::COPIA_IN_PRESTITO) {
                    $errors['message'] = 'Non puoi cambiare la sede di una copia in prestito';
                    redirect_with('bibliotecario.php?tab=' . $tab, [
                        'inputs' => $inputs,
                        'errors' => $errors
                    ]);
                } else if ($error == DatabaseErrors::ERRORE_INTERNO_DATABASE)
                    redirect_to('internal_error.php');
                else
                    redirect_to('bibliotecario.php?tab=' . $tab);
            case Operazione::RIMUOVI_COPIA:
                $error = rimuoviCopia($inputs['id']);
                
                if ($error == DatabaseErrors::COPIA_IN_PRESTITO) {
                    $errors['message'] = 'Non puoi rimuovere una copia in prestito';
                    redirect_with('bibliotecario.php?tab=' . $tab, [
                        'inputs' => $inputs,
                        'errors' => $errors
                    ]);
                } else if ($error == DatabaseErrors::ERRORE_INTERNO_DATABASE)
                    redirect_to('internal_error.php');
                else
                    redirect_to('bibliotecario.php?tab=' . $tab);
            case Operazione::AGGIUNGI_BIBLIOTECARIO:
                if (empty($inputs['email']))
                    $errors['email'] = 'L\'email è obbligatoria';
                if (empty($inputs['password1']))
                    $errors['password1'] = 'La password è obbligatoria';
                if (empty($inputs['password2']))
                    $errors['password2'] = 'La conferma della password è obbligatoria';  
                
                if (!empty($errors))
                    redirect_with('bibliotecario.php?tab=' . $tab, [ 
                        'inputs' => $inputs,
                        'errors' => $errors 
                    ]);

                if ($inputs['password1'] != $inputs['password2']) {
                    $errors['password1'] = $errors['password2'] = '';
                    $errors['message'] = 'Le password non coincidono';
                    redirect_with('bibliotecario.php?tab=' . $tab, [
                        'inputs' => $inputs,
                        'errors' => $errors
                    ]);
                }

                $error = registraBibliotecario($inputs['email'], $inputs['password1']);
                if ($error == DatabaseErrors::BIBLIOTECARIO_GIA_REGISTRATO) {
                    $errors['message'] = 'Bibliotecario già registrato';
                    redirect_with('bibliotecario.php?tab=' . $tab, [
                        'inputs' => $inputs,
                        'errors' => $errors
                    ]);
                } else if ($error == DatabaseErrors::ERRORE_INTERNO_DATABASE)
                    redirect_to('internal_error.php');
                else
                    redirect_to('bibliotecario.php?tab=' . $tab);
            case Operazione::CAMBIA_EMAIL_BIBLIOTECARIO:
                if (empty($inputs['cambia_email']))
                    $errors['cambia_email'] = 'Inserisci una nuova email';
                
                if (!empty($errors))
                    redirect_with('bibliotecario.php?tab=' . $tab, [ 
                        'inputs' => $inputs,
                        'errors' => $errors 
                    ]);

                $error = cambiaBibliotecarioEmail($_COOKIE['user_id'], $inputs['cambia_email']);

                if ($error == DatabaseErrors::BIBLIOTECARIO_GIA_REGISTRATO) {
                    $errors['message'] = 'Email già registrata';
                    redirect_with('bibliotecario.php?tab=' . $tab, [
                        'inputs' => $inputs,
                        'errors' => $errors
                    ]);
                } else if ($error == DatabaseErrors::ERRORE_INTERNO_DATABASE)
                    redirect_to('internal_error.php');
                else
                    redirect_to('bibliotecario.php?tab=' . $tab);
            case Operazione::CAMBIA_PASSWORD_BIBLIOTECARIO:
                if (empty($inputs['vecchia_password']))
                    $errors['vecchia_password'] = 'Inserisci la vecchia password';
                if (empty($inputs['cambia_password1']))
                    $errors['cambia_password1'] = 'Inserisci una nuova password';
                if (empty($inputs['cambia_password2']))
                    $errors['cambia_password2'] = 'Conferma la nuova password';

                if (!empty($errors))
                    redirect_with('bibliotecario.php?tab=' . $tab, [ 
                        'inputs' => $inputs,
                        'errors' => $errors 
                    ]);

                if ($inputs['cambia_password1'] != $inputs['cambia_password2']) {
                    $errors['cambia_password1'] = $errors['cambia_password2'] = '';
                    $errors['message'] = 'Le password non coincidono';
                    redirect_with('bibliotecario.php?tab=' . $tab, [
                        'inputs' => $inputs,
                        'errors' => $errors
                    ]);
                }
                
                $error = cambiaBibliotecarioPassword($_COOKIE['user_id'], $inputs['vecchia_password'], $inputs['cambia_password1']);
                if ($error == DatabaseErrors::PASSWORD_ERRATA) {
                    $errors['vecchia_password'] = 'Password errata';
                    redirect_with('bibliotecario.php?tab=' . $tab, [
                        'inputs' => $inputs,
                        'errors' => $errors
                    ]);
                } else if ($error == DatabaseErrors::ERRORE_INTERNO_DATABASE)
                    redirect_to('internal_error.php');
                else
                    redirect_to('bibliotecario.php?tab=' . $tab);
            case Operazione::RIMUOVI_BIBLIOTECARIO:
                $error = rimuoviBibliotecario($_COOKIE['user_id']);
                
                if ($error == DatabaseErrors::ERRORE_INTERNO_DATABASE)
                    redirect_to('internal_error.php');
                else
                    redirect_to('login.php');
            case Operazione::AGGIUNGI_LETTORE:
                if (empty($inputs['codice_fiscale']))
                    $errors['codice_fiscale'] = 'Il codice fiscale è obbligatorio';
                if (!isValidCodiceFiscale($inputs['codice_fiscale']))
                    $errors['codice_fiscale'] = 'Inserici un codice fiscale valido';
                if (empty($inputs['nome']))
                    $errors['nome'] = 'Inserisci un nome';
                if (empty($inputs['cognome']))
                    $errors['cognome'] = 'Inserisci un cognome';
                if (empty($inputs['email']))
                    $errors['email'] = 'Inserisci un\'email';
                if (empty($inputs['password1']))
                    $errors['password1'] = 'Inserisci una password';
                if (empty($inputs['password2']))
                    $errors['password2'] = 'Inserisci la conferma della password';

                if (!empty($errors))
                    redirect_with('bibliotecario.php?tab=' . $tab, [
                        'inputs' => $inputs,
                        'errors' => $errors  
                    ]);

                if ($inputs['password1'] != $inputs['password2']) {
                    $errors['password1'] = '';
                    $errors['password2'] = 'Le password non corrispondono';
                    redirect_with('bibliotecario.php?tab=' . $tab, [
                        'inputs' => $inputs,
                        'errors' => $errors  
                    ]);
                }

                $inputs['categoria'] = Categoria::from($inputs['categoria']);

                $error = registraLettore($inputs['nome'],
                                         $inputs['cognome'],
                                         $inputs['email'],
                                         $inputs['categoria'],
                                         $inputs['codice_fiscale'],
                                         $inputs['password1']);

                if ($error == DatabaseErrors::LETTORE_GIA_REGISTRATO) {
                    $errors['message'] = 'Esiste già un lettore con la stessa mail o con lo stesso codice fiscale';
                    redirect_with('bibliotecario.php?tab=' . $tab, [
                        'inputs' => $inputs,
                        'errors' => $inputs
                    ]);
                } else if ($error == DatabaseErrors::ERRORE_INTERNO_DATABASE)
                    redirect_to('internal_error.php');
                else
                    redirect_to('bibliotecario.php?tab=' . $tab);
            case Operazione::CAMBIA_CATEGORIA_LETTORE:
                $inputs['nuova_categoria'] = Categoria::from($inputs['nuova_categoria']);
                $error = cambiaLettoreCategoria($inputs['codice_fiscale'], $inputs['nuova_categoria']);

                if ($error == DatabaseErrors::ERRORE_INTERNO_DATABASE)
                    redirect_to('internal_error.php');
                else
                    redirect_to('bibliotecario.php?tab=' . $tab);
            case Operazione::AZZERA_RITARDI_LETTORE:
                $error = resetRitardi($inputs['codice_fiscale']);

                if ($error == DatabaseErrors::ERRORE_INTERNO_DATABASE)
                    redirect_to('internal_error.php');
                else
                    redirect_to('bibliotecario.php?tab=' . $tab);
            case Operazione::RIMUOVI_LETTORE:
                $error = rimuoviLettore($inputs['codice_fiscale']);

                if ($error == DatabaseErrors::LETTORE_PRESTITI_IN_CORSO) {
                    $errors['message'] = 'Non puoi rimuovere un lettore con prestiti in corso';
                    redirect_with('bibliotecario.php?tab=' . $tab, [
                        'inputs' => $inputs,
                        'errors' => $errors
                    ]);
                } else if ($error == DatabaseErrors::ERRORE_INTERNO_DATABASE)
                    redirect_to('internal_error.php');
                else
                    redirect_to('bibliotecario.php?tab=' . $tab);
            case Operazione::PROROGA_PRESTITO:
                $error = prorogaPrestito($inputs['copia']);

                if ($error == DatabaseErrors::PRESTITO_IN_RITARDO) {
                    $errors['message'] = 'Non puoi prorogare un prestito in ritardo';
                    redirect_with('bibliotecario.php?tab=' . $tab, [
                        'inputs' => $inputs,
                        'errors' => $errors
                    ]);
                } else if ($error == DatabaseErrors::ERRORE_INTERNO_DATABASE)
                    redirect_to('internal_error.php');
                else
                    redirect_to('bibliotecario.php?tab=' . $tab);
        }
    } else if (get()) {
        [$inputs, $errors] = session_get('inputs', 'errors');
    }
?>

<?php view('header', ['title' => 'Bibliotecario']); ?>
<?php view('bibliotecario_navbar', [ 'active' => $tab, 'page' => 'bibliotecario.php' ]); ?>
<div class="container">
    <div class="row">
        <div class="col mt-4">
            <?php 
                view($tab, [
                    'inputs' => $inputs,
                    'errors' => $errors
                ]);
            ?>
        </div>
    </div>
</div>
<?php view('footer'); ?>
