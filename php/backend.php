<?php
    enum DatabaseErrors {
        case NESSUN_ERRORE;
        case PASSWORD_ERRATA;
        case TROPPE_CONSEGNE_IN_RITARDO;
        case TROPPI_PRESTITI_IN_CORSO;
        case LIBRO_NON_DISPONIBILE;
        case LIBRI_ASSOCIATI_AD_AUTORE;
        case COPIE_ASSOCIATE_A_LIBRO;
        case COPIE_ASSOCIATE_A_SEDE;
        case COPIA_IN_PRESTITO;
        case LETTORE_PRESTITI_IN_CORSO;
        case PRESTITO_IN_RITARDO;
        case LETTORE_GIA_REGISTRATO;
        case BIBLIOTECARIO_GIA_REGISTRATO;
        case ERRORE_INTERNO_DATABASE;
        case UTENTE_NON_ESISTENTE;
    }

    enum Utente {
        case LETTORE;
        case BIBLIOTECARIO;
    }

    function aggiungiAutore($nome, $cognome, $data_di_nascita, $data_di_morte, $biografia) {
        $conn = pg_connect("host=localhost port=5432 dbname=biblioteca user=marco");
        if (!$conn) {
            return DatabaseErrors::ERRORE_INTERNO_DATABASE;
        }

        $query = "SELECT biblioteca.aggiungiAutore($1, $2, $3, $4, $5)";
        if (!pg_prepare($conn, "aggiungi_autore", $query)) {
            return DatabaseErrors::ERRORE_INTERNO_DATABASE;
        }

        $result = pg_execute($conn, "aggiungi_autore", array($nome, $cognome, $data_di_nascita, $data_di_morte, $biografia));
        if (!$result) {
            return DatabaseErrors::ERRORE_INTERNO_DATABASE;
        }

        return DatabaseErrors::NESSUN_ERRORE;
    }

    function setAutoreDataDiMorte($id_autore, $data_di_morte) {
        $conn = pg_connect("host=localhost port=5432 dbname=biblioteca user=marco");
        if (!$conn) {
            return DatabaseErrors::ERRORE_INTERNO_DATABASE;
        }

        $query = "SELECT biblioteca.setAutoreDataDiMorte($1, $2)";
        if (!pg_prepare($conn, "set_autore_data_di_morte", $query)) {
            return DatabaseErrors::ERRORE_INTERNO_DATABASE;
        }

        $result = pg_execute($conn, "set_autore_data_di_morte", array($id_autore, $data_di_morte));
        if (!$result) {
            return DatabaseErrors::ERRORE_INTERNO_DATABASE;
        }

        return DatabaseErrors::NESSUN_ERRORE;
    }

    function rimuoviAutore($id_autore) {
        $conn = pg_connect("host=localhost port=5432 dbname=biblioteca user=marco");
        if (!$conn) {
            return DatabaseErrors::ERRORE_INTERNO_DATABASE;
        }

        $query = "SELECT biblioteca.rimuoviAutore($1)";
        if (!pg_prepare($conn, "rimuovi_autore", $query)) {
            return DatabaseErrors::ERRORE_INTERNO_DATABASE;
        }

        $result = pg_execute($conn, "rimuovi_autore", array($id_autore));
        if (!$result) {
            return DatabaseErrors::ERRORE_INTERNO_DATABASE;
        }

        $error = pg_fetch_array($result);
        return $error[0];
    }

    function aggiungiLibro($isbn, $titolo, $trama, $casa_editrice, $autori) {
        $conn = pg_connect("host=localhost port=5432 dbname=biblioteca user=marco");
        if (!$conn) {
            return DatabaseErrors::ERRORE_INTERNO_DATABASE;
        }

        $query = "SELECT biblioteca.aggiungiLibro($1, $2, $3, $4, $5)";
        if (!pg_prepare($conn, "aggiungi_libro", $query)) {
            return DatabaseErrors::ERRORE_INTERNO_DATABASE;
        }

        $result = pg_execute($conn, "aggiungi_libro", array($isbn, $titolo, $trama, $casa_editrice, $autori));
        if (!$result) {
            return DatabaseErrors::ERRORE_INTERNO_DATABASE;
        }

        return DatabaseErrors::NESSUN_ERRORE;
    }

    function rimuoviLibro($isbn) {
        $conn = pg_connect("host=localhost port=5432 dbname=biblioteca user=marco");
        if (!$conn) {
            return DatabaseErrors::ERRORE_INTERNO_DATABASE;
        }

        $query = "SELECT biblioteca.rimuoviLibro($1)";
        if (!pg_prepare($conn, "rimuovi_libro", $query)) {
            return DatabaseErrors::ERRORE_INTERNO_DATABASE;
        }

        $result = pg_execute($conn, "rimuovi_libro", array($isbn));
        if (!$result) {
            return DatabaseErrors::ERRORE_INTERNO_DATABASE;
        }

        $error = pg_fetch_array($result);
        return $error[0];
    }

    function aggiungiSede($indirizzo, $citta) {
        $conn = pg_connect("host=localhost port=5432 dbname=biblioteca user=marco");
        if (!$conn) {
            return DatabaseErrors::ERRORE_INTERNO_DATABASE;
        }

        $query = "SELECT biblioteca.aggiungiSede($1, $2)";
        if (!pg_prepare($conn, "aggiungi_sede", $query)) {
            return DatabaseErrors::ERRORE_INTERNO_DATABASE;
        }

        $result = pg_execute($conn, "aggiungi_sede", array($indirizzo, $citta));
        if (!$result) {
            return DatabaseErrors::ERRORE_INTERNO_DATABASE;
        }

        return DatabaseErrors::NESSUN_ERRORE;
    }

    function getNumeroDiCopieGestite($id_sede) {
        $conn = pg_connect("host=localhost port=5432 dbname=biblioteca user=marco");
        if (!$conn) {
            return DatabaseErrors::ERRORE_INTERNO_DATABASE;
        }

        $query = "SELECT biblioteca.getNumeroDiCopieGestite($1)";
        if (!pg_prepare($conn, "get_numero_di_copie_gestite", $query)) {
            return DatabaseErrors::ERRORE_INTERNO_DATABASE;
        }

        $result = pg_execute($conn, "get_numero_di_copie_gestite", array($id_sede));
        if (!$result) {
            return DatabaseErrors::ERRORE_INTERNO_DATABASE;
        }

        $numero_di_copie_gestite = pg_fetch_array($result);
        return $numero_di_copie_gestite[0]; 
    }

    function getNumeroDiIsbnGestiti($id_sede) {
        $conn = pg_connect("host=localhost port=5432 dbname=biblioteca user=marco");
        if (!$conn) {
            return DatabaseErrors::ERRORE_INTERNO_DATABASE;
        }

        $query = "SELECT biblioteca.getNumeroDiIsbnGestiti($1)";
        if (!pg_prepare($conn, "get_numero_di_isbn_gestiti", $query)) {
            return DatabaseErrors::ERRORE_INTERNO_DATABASE;
        }

        $result = pg_execute($conn, "get_numero_di_isbn_gestiti", array($id_sede));
        if (!$result) {
            return DatabaseErrors::ERRORE_INTERNO_DATABASE;
        }

        $numero_di_isbn_gestiti = pg_fetch_array($result);
        return $numero_di_isbn_gestiti[0]; 
    }

    function getNumeroDiPrestitiInCorso($id_sede) {
        $conn = pg_connect("host=localhost port=5432 dbname=biblioteca user=marco");
        if (!$conn) {
            return DatabaseErrors::ERRORE_INTERNO_DATABASE;
        }

        $query = "SELECT biblioteca.getNumeroDiPrestitiInCorso($1)";
        if (!pg_prepare($conn, "get_numero_di_prestiti_in_corso", $query)) {
            return DatabaseErrors::ERRORE_INTERNO_DATABASE;
        }

        $result = pg_execute($conn, "get_numero_di_prestiti_in_corso", array($id_sede));
        if (!$result) {
            return DatabaseErrors::ERRORE_INTERNO_DATABASE;
        }

        $numero_di_prestiti_in_corso = pg_fetch_array($result);
        return $numero_di_prestiti_in_corso[0]; 
    }

    function getRitardi($id_sede) {
        $conn = pg_connect("host=localhost port=5432 dbname=biblioteca user=marco");
        if (!$conn) {
            return DatabaseErrors::ERRORE_INTERNO_DATABASE;
        }

        $query = "SELECT biblioteca.getRitardi($1)";
        if (!pg_prepare($conn, "get_ritardi", $query)) {
            return DatabaseErrors::ERRORE_INTERNO_DATABASE;
        }

        $result = pg_execute($conn, "get_ritardi", array($id_sede));
        if (!$result) {
            return DatabaseErrors::ERRORE_INTERNO_DATABASE;
        }

        $ritardi = pg_fetch_all($result);
        return $ritardi; 
    }

    function rimuoviSede($id_sede) {
        $conn = pg_connect("host=localhost port=5432 dbname=biblioteca user=marco");
        if (!$conn) {
            return DatabaseErrors::ERRORE_INTERNO_DATABASE;
        }

        $query = "SELECT biblioteca.rimuoviSede($1)";
        if (!pg_prepare($conn, "rimuovi_sede", $query)) {
            return DatabaseErrors::ERRORE_INTERNO_DATABASE;
        }

        $result = pg_execute($conn, "rimuovi_sede", array($id_sede));
        if (!$result) {
            return DatabaseErrors::ERRORE_INTERNO_DATABASE;
        }

        $error = pg_fetch_array($result);
        return $error[0];
    }

    function aggiungiCopia($isbn, $id_sede) {
        $conn = pg_connect("host=localhost port=5432 dbname=biblioteca user=marco");
        if (!$conn) {
            return DatabaseErrors::ERRORE_INTERNO_DATABASE;
        }

        $query = "SELECT biblioteca.aggiungiCopia($1, $2)";
        if (!pg_prepare($conn, "aggiungi_copia", $query)) {
            return DatabaseErrors::ERRORE_INTERNO_DATABASE;
        }

        $result = pg_execute($conn, "aggiungi_copia", array($isbn, $id_sede));
        if (!$result) {
            return DatabaseErrors::ERRORE_INTERNO_DATABASE;
        }

        return DatabaseErrors::NESSUN_ERRORE;
    }

    function trovaCopiaDisponibile($isbn, $id_sede) {
        $conn = pg_connect("host=localhost port=5432 dbname=biblioteca user=marco");
        if (!$conn) {
            return DatabaseErrors::ERRORE_INTERNO_DATABASE;
        }

        $query = "SELECT biblioteca.trovaCopiaDisponibile($1, $2)";
        if (!pg_prepare($conn, "trova_copia_disponibile", $query)) {
            return DatabaseErrors::ERRORE_INTERNO_DATABASE;
        }

        $result = pg_execute($conn, "trova_copia_disponibile", array($isbn, $id_sede));
        if (!$result) {
            return DatabaseErrors::ERRORE_INTERNO_DATABASE;
        }

        $copia_disponibile = pg_fetch_array($result);
        return $copia_disponibile[0];
    }

    function cambiaSede($id_copia, $id_nuova_sede) {
        $conn = pg_connect("host=localhost port=5432 dbname=biblioteca user=marco");
        if (!$conn) {
            return DatabaseErrors::ERRORE_INTERNO_DATABASE;
        }

        $query = "SELECT biblioteca.cambiaSede($1, $2)";
        if (!pg_prepare($conn, "cambia_sede", $query)) {
            return DatabaseErrors::ERRORE_INTERNO_DATABASE;
        }

        $result = pg_execute($conn, "cambia_sede", array($id_copia, $id_nuova_sede));
        if (!$result) {
            return DatabaseErrors::ERRORE_INTERNO_DATABASE;
        }

        $error = pg_fetch_array($result);
        return $error[0];
    }

    function rimuoviCopia($id_copia) {
        $conn = pg_connect("host=localhost port=5432 dbname=biblioteca user=marco");
        if (!$conn) {
            return DatabaseErrors::ERRORE_INTERNO_DATABASE;
        }

        $query = "SELECT biblioteca.rimuoviCopia($1)";
        if (!pg_prepare($conn, "rimuovi_copia", $query)) {
            return DatabaseErrors::ERRORE_INTERNO_DATABASE;
        }

        $result = pg_execute($conn, "rimuovi_copia", array($id_copia));
        if (!$result) {
            return DatabaseErrors::ERRORE_INTERNO_DATABASE;
        }

        $error = pg_fetch_array($result);
        return $error[0];
    }

    function registraBibliotecario($email, $password) {
        $conn = pg_connect("host=localhost port=5432 dbname=biblioteca user=marco");
        if (!$conn) {
            return DatabaseErrors::ERRORE_INTERNO_DATABASE;
        }

        // controlla se esiste già un bibliotecario con la stessa email
        $query = "SELECT count(*) FROM biblioteca.bibliotecario WHERE email = $1";
        if (!pg_prepare($conn, "controlla_email", $query)) {
            return DatabaseErrors::ERRORE_INTERNO_DATABASE;
        }

        $result = pg_execute($conn, "controlla_email", array($email));
        if (!$result) {
            return DatabaseErrors::ERRORE_INTERNO_DATABASE;
        }

        [$count] = pg_fetch_array($result);
        if ($count > 0) {
            return DatabaseErrors::BIBLIOTECARIO_GIA_REGISTRATO;
        }

        $hash = password_hash($password, PASSWORD_DEFAULT);
        
        $query = "SELECT biblioteca.aggiungiBibliotecario($1, $2)";
        if (!pg_prepare($conn, "aggiungi_bibliotecario", $query)) {
            return DatabaseErrors::ERRORE_INTERNO_DATABASE;
        }

        $result = pg_execute($conn, "aggiungi_bibliotecario", array($email, $hash));
        if (!$result) {
            return DatabaseErrors::ERRORE_INTERNO_DATABASE;
        }

        return DatabaseErrors::NESSUN_ERRORE;
    }

    function cambiaBibliotecarioEmail($id_bibliotecario, $nuova_email) {
        $conn = pg_connect("host=localhost port=5432 dbname=biblioteca user=marco");
        if (!$conn) {
            return DatabaseErrors::ERRORE_INTERNO_DATABASE;
        }

        // controlla se esiste già un bibliotecario con la stessa email
        $query = "SELECT count(*) FROM biblioteca.bibliotecario WHERE email = $1";
        if (!pg_prepare($conn, "controlla_email", $query)) {
            return DatabaseErrors::ERRORE_INTERNO_DATABASE;
        }

        $result = pg_execute($conn, "controlla_email", array($nuova_email));
        if (!$result) {
            return DatabaseErrors::ERRORE_INTERNO_DATABASE;
        }

        $count = pg_fetch_array($result);
        if ($count[0] > 0) {
            return DatabaseErrors::BIBLIOTECARIO_GIA_REGISTRATO;
        }

        $query = "SELECT biblioteca.cambiaBibliotecarioEmail($1, $2)";
        if (!pg_prepare($conn, "cambia_bibliotecario_email", $query)) {
            return DatabaseErrors::ERRORE_INTERNO_DATABASE;
        }

        $result = pg_execute($conn, "cambia_bibliotecario_email", array($id_bibliotecario, $nuova_email));
        if (!$result) {
            return DatabaseErrors::ERRORE_INTERNO_DATABASE;
        }

        $error = pg_fetch_array($result);
        return $error[0];
    }

    function cambiaBibliotecarioPassword($id_bibliotecario, $vecchia_password, $nuova_password) {
        $conn = pg_connect("host=localhost port=5432 dbname=biblioteca user=marco");
        if (!$conn) {
            return DatabaseErrors::ERRORE_INTERNO_DATABASE;
        }

        $query = "SELECT bibliotecario.hash FROM biblioteca.bibliotecario WHERE id = $1";
        if (!pg_prepare($conn, "get_bibliotecario_password", $query)) {
            return DatabaseErrors::ERRORE_INTERNO_DATABASE;
        }

        $result = pg_execute($conn, "get_bibliotecario_password", array($id_bibliotecario));
        if (!$result) {
            return DatabaseErrors::ERRORE_INTERNO_DATABASE;
        }

        [$hash] = pg_fetch_array($result, NULL, PGSQL_NUM);
        if (!password_verify($vecchia_password, $hash)) {
            return DatabaseErrors::PASSWORD_ERRATA;
        }

        $hash = password_hash($nuova_password, PASSWORD_DEFAULT);

        $query = "SELECT biblioteca.cambiaBibliotecarioPassword($1, $2)";
        if (!pg_prepare($conn, "cambia_bibliotecario_password", $query)) {
            return DatabaseErrors::ERRORE_INTERNO_DATABASE;
        }

        $result = pg_execute($conn, "cambia_bibliotecario_password", array($id_bibliotecario, $hash));
        if (!$result) {
            return DatabaseErrors::ERRORE_INTERNO_DATABASE;
        }

        return DatabaseErrors::NESSUN_ERRORE;
    }

function rimuoviBibliotecario($id_bibliotecario) {
    $conn = pg_connect("host=localhost port=5432 dbname=biblioteca user=marco");
    if (!$conn) {
        return DatabaseErrors::ERRORE_INTERNO_DATABASE;
    }

    $query = "SELECT biblioteca.rimuoviBibliotecario($1)";
    if (!pg_prepare($conn, "rimuovi_bibliotecario", $query)) {
        return DatabaseErrors::ERRORE_INTERNO_DATABASE;
    }

    $result = pg_execute($conn, "rimuovi_bibliotecario", array($id_bibliotecario));
    if (!$result) {
        return DatabaseErrors::ERRORE_INTERNO_DATABASE;
    }

    $error = pg_fetch_array($result);
    return $error[0];
}

    function registraLettore($nome, $cognome, $email, $categoria, $codice_fiscale, $password) {
        $conn = pg_connect("host=localhost port=5432 dbname=biblioteca user=marco");
        if (!$conn) {
            return DatabaseErrors::ERRORE_INTERNO_DATABASE;
        }

        // controlla se esiste già un lettore con la stessa email
        $query = "SELECT count(*) FROM biblioteca.lettore WHERE email = $1";
        if (!pg_prepare($conn, "controlla_email", $query)) {
            return DatabaseErrors::ERRORE_INTERNO_DATABASE;
        }

        $result = pg_execute($conn, "controlla_email", array($email));
        if (!$result) {
            return DatabaseErrors::ERRORE_INTERNO_DATABASE;
        }

        $count = pg_fetch_array($result);
        if ($count[0] > 0) {
            return DatabaseErrors::LETTORE_GIA_REGISTRATO;
        }

        $hash = password_hash($password, PASSWORD_DEFAULT);

        $query = "SELECT biblioteca.aggiungiLettore($1, $2, $3, $4, $5, $6)";
        if (!pg_prepare($conn, "registra_lettore", $query)) {
            return DatabaseErrors::ERRORE_INTERNO_DATABASE;
        }

        $result = pg_execute($conn, "registra_lettore", array($codice_fiscale, $nome, $cognome, $email, $hash, $categoria));
        if (!$result) {
            return DatabaseErrors::ERRORE_INTERNO_DATABASE;
        }

        $error = pg_fetch_array($result);
        return $error[0];
    }

    function login(Utente $utente, string $email, string $password): array {
        $conn = pg_connect("host=localhost port=5432 dbname=biblioteca user=marco");
        if (!$conn) {
            return DatabaseErrors::ERRORE_INTERNO_DATABASE;
        }

        $query;
        if ($utente == Utente::BIBLIOTECARIO) {
            $query = "SELECT bibliotecario.id, bibliotecario.hash FROM biblioteca.bibliotecario WHERE email = $1";
        } else if ($utente == Utente::LETTORE) {
            $query = "SELECT lettore.codice_fiscale, lettore.hash FROM biblioteca.lettore WHERE email = $1";
        }
        if (!pg_prepare($conn, "get_password", $query)) {
            return DatabaseErrors::ERRORE_INTERNO_DATABASE;
        }

        $result = pg_execute($conn, "get_password", array($email));
        if (!$result) {
            return DatabaseErrors::ERRORE_INTERNO_DATABASE;
        }

        [$id, $hash] = pg_fetch_array($result, NULL, PGSQL_NUM);
        $hash = trim($hash);
        if (!password_verify($password, $hash)) {
            return DatabaseErrors::PASSWORD_ERRATA;
        }

        return [$id, DatabaseErrors::NESSUN_ERRORE];
    }

    function esisteUtente(string $id): array {
        $conn = pg_connect("host=localhost port=5432 dbname=biblioteca user=marco");
        if (!$conn) {
            return DatabaseErrors::ERRORE_INTERNO_DATABASE;
        }

        $query = "SELECT count(*) FROM biblioteca.lettore WHERE codice_fiscale = $1";
        if (!pg_prepare($conn, "esiste_lettore", $query)) {
            return DatabaseErrors::ERRORE_INTERNO_DATABASE;
        }

        $result = pg_execute($conn, "esiste_lettore", array($id));
        if (!$result) {
            return DatabaseErrors::ERRORE_INTERNO_DATABASE;
        }

        [$countLettore] = pg_fetch_array($result);

        $query = "SELECT count(*) FROM biblioteca.bibliotecario WHERE id = $1";
        if (!pg_prepare($conn, "esiste_bibliotecario", $query)) {
            return DatabaseErrors::ERRORE_INTERNO_DATABASE;
        }

        $result = pg_execute($conn, "esiste_bibliotecario", array($id));
        if (!$result) {
            return DatabaseErrors::ERRORE_INTERNO_DATABASE;
        }

        [$countBibliotecario] = pg_fetch_array($result);

        $utente = null;
        if ($countLettore == 1)
            $utente = Utente::LETTORE;
        else if ($countBibliotecario == 1)
            $utente = Utente::BIBLIOTECARIO;

        return [$countLettore == 1 || $countBibliotecario == 1, $utente];
    }

    function cambiaLettoreEmail($id_lettore, $nuova_email) {
        $conn = pg_connect("host=localhost port=5432 dbname=biblioteca user=marco");
        if (!$conn) {
            return DatabaseErrors::ERRORE_INTERNO_DATABASE;
        }

        // controlla se esiste già un lettore con la stessa email
        $query = "SELECT count(*) FROM biblioteca.lettore WHERE email = $1";
        if (!pg_prepare($conn, "controlla_email", $query)) {
            return DatabaseErrors::ERRORE_INTERNO_DATABASE;
        }

        $result = pg_execute($conn, "controlla_email", array($nuova_email));
        if (!$result) {
            return DatabaseErrors::ERRORE_INTERNO_DATABASE;
        }

        $count = pg_fetch_array($result);
        if ($count[0] > 0) {
            return DatabaseErrors::LETTORE_GIA_REGISTRATO;
        }

        $query = "SELECT biblioteca.cambiaLettoreEmail($1, $2)";
        if (!pg_prepare($conn, "cambia_lettore_email", $query)) {
            return DatabaseErrors::ERRORE_INTERNO_DATABASE;
        }

        $result = pg_execute($conn, "cambia_lettore_email", array($id_lettore, $nuova_email));
        if (!$result) {
            return DatabaseErrors::ERRORE_INTERNO_DATABASE;
        }

        $error = pg_fetch_array($result);
        return $error[0];
    }

    function cambiaLettorePassword($codice_fiscale, $vecchia_password, $nuova_password) {
        $conn = pg_connect("host=localhost port=5432 dbname=biblioteca user=marco");
        if (!$conn) {
            return DatabaseErrors::ERRORE_INTERNO_DATABASE;
        }

        $query = "SELECT lettore.hash FROM biblioteca.lettore WHERE codice_fiscale = $1";
        if (!pg_prepare($conn, "get_lettore_password", $query)) {
            return DatabaseErrors::ERRORE_INTERNO_DATABASE;
        }

        $result = pg_execute($conn, "get_lettore_password", array($codice_fiscale));
        if (!$result) {
            return DatabaseErrors::ERRORE_INTERNO_DATABASE;
        }

        [$hash] = pg_fetch_array($result, NULL, PGSQL_NUM);
        if (!password_verify($vecchia_password, $hash)) {
            return DatabaseErrors::PASSWORD_ERRATA;
        }

        $hash = password_hash($nuova_password, PASSWORD_DEFAULT);

        $query = "SELECT biblioteca.cambiaLettorePassword($1, $2)";
        if (!pg_prepare($conn, "cambia_lettore_password", $query)) {
            return DatabaseErrors::ERRORE_INTERNO_DATABASE;
        }

        $result = pg_execute($conn, "cambia_lettore_password", array($codice_fiscale, $hash));
        if (!$result) {
            return DatabaseErrors::ERRORE_INTERNO_DATABASE;
        }

        $error = pg_fetch_array($result);
        return $error[0];
    }

    function cambiaLettoreCategoria($codice_fiscale, $categoria) {
        if ($categoria != "base" && $categoria != "premium") {
            throw new Exception("Categoria non valida"); // non dovrebbe mai accadere
        }

        $conn = pg_connect("host=localhost port=5432 dbname=biblioteca user=marco");
        if (!$conn) {
            return DatabaseErrors::ERRORE_INTERNO_DATABASE;
        } 

        $query = "SELECT biblioteca.cambiaLettoreCategoria($1, $2)";
        if (!pg_prepare($conn, "cambia_lettore_categoria", $query)) {
            return DatabaseErrors::ERRORE_INTERNO_DATABASE;
        }

        $result = pg_execute($conn, "cambia_lettore_categoria", array($codice_fiscale, $categoria));
        if (!$result) {
            return DatabaseErrors::ERRORE_INTERNO_DATABASE;
        }

        $error = pg_fetch_array($result);
        return $error[0];
    }

    function resetRitardi($codice_fiscale) {
        $conn = pg_connect("host=localhost port=5432 dbname=biblioteca user=marco");
        if (!$conn) {
            return DatabaseErrors::ERRORE_INTERNO_DATABASE;
        } 

        $query = "SELECT biblioteca.resetRitardi($1)";
        if (!pg_prepare($conn, "reset_ritardi", $query)) {
            return DatabaseErrors::ERRORE_INTERNO_DATABASE;
        }

        $result = pg_execute($conn, "reset_ritardi", array($codice_fiscale));
        if (!$result) {
            return DatabaseErrors::ERRORE_INTERNO_DATABASE;
        }

        $error = pg_fetch_array($result);
        return $error[0];
    }

    function rimuoviLettore($codice_fiscale) {
        $conn = pg_connect("host=localhost port=5432 dbname=biblioteca user=marco");
        if (!$conn) {
            return DatabaseErrors::ERRORE_INTERNO_DATABASE;
        } 

        $query = "SELECT biblioteca.rimuoviLettore($1)";
        if (!pg_prepare($conn, "rimuovi_lettore", $query)) {
            return DatabaseErrors::ERRORE_INTERNO_DATABASE;
        }

        $result = pg_execute($conn, "rimuovi_lettore", array($codice_fiscale));
        if (!$result) {
            return DatabaseErrors::ERRORE_INTERNO_DATABASE;
        }

        $error = pg_fetch_array($result);
        return $error[0];
    }

    function richiediPrestito($id_copia, $cf_lettore) {
        $conn = pg_connect("host=localhost port=5432 dbname=biblioteca user=marco");
        if (!$conn) {
            return DatabaseErrors::ERRORE_INTERNO_DATABASE;
        } 

        $query = "SELECT biblioteca.richiediPrestito($1, $2)";
        if (!pg_prepare($conn, "richiedi_prestito", $query)) {
            return DatabaseErrors::ERRORE_INTERNO_DATABASE;
        }

        $result = pg_execute($conn, "richiedi_prestito", array($id_copia, $cf_lettore));
        if (!$result) {
            return DatabaseErrors::ERRORE_INTERNO_DATABASE;
        }

        $error = pg_fetch_array($result);
        return $error[0];
    }

    function restituisciPrestito($id_copia) {
        $conn = pg_connect("host=localhost port=5432 dbname=biblioteca user=marco");
        if (!$conn) {
            return DatabaseErrors::ERRORE_INTERNO_DATABASE;
        } 

        $query = "SELECT biblioteca.restituisciPrestito($1)";
        if (!pg_prepare($conn, "restituisci_prestito", $query)) {
            return DatabaseErrors::ERRORE_INTERNO_DATABASE;
        }

        $result = pg_execute($conn, "restituisci_prestito", array($id_copia));
        if (!$result) {
            return DatabaseErrors::ERRORE_INTERNO_DATABASE;
        }

        $error = pg_fetch_array($result);
        return $error[0];
    }

    function prorogaPrestito($id_copia, $giorni_di_proroga) {
        $conn = pg_connect("host=localhost port=5432 dbname=biblioteca user=marco");
        if (!$conn) {
            return DatabaseErrors::ERRORE_INTERNO_DATABASE;
        } 

        $query = "SELECT biblioteca.prorogaPrestito($1, $2)";
        if (!pg_prepare($conn, "proroga_prestito", $query)) {
            return DatabaseErrors::ERRORE_INTERNO_DATABASE;
        }

        $result = pg_execute($conn, "proroga_prestito", array($id_copia, $giorni_di_proroga));
        if (!$result) {
            return DatabaseErrors::ERRORE_INTERNO_DATABASE;
        }

        $error = pg_fetch_array($result);
        return $error[0];
    }

?>
