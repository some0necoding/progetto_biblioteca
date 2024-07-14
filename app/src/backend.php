<?php
    include_once 'exceptions.php';
    include_once 'objects.php';

    define('DB_HOST', 'localhost'); 
    define('DB_PORT', '5432');
    define('DB_NAME', 'biblioteca');
    define('DB_USER', 'marco');
    define('CONNECTION_STRING', "host=" . DB_HOST . " port=" . DB_PORT . " dbname=" . DB_NAME . " user=" . DB_USER);

    define('HASHING_ALGORITHM', PASSWORD_DEFAULT);

    // TODO: spostare questo enum nel futuro file delle operazioni
    // Per consentire ad una pagina di gestire varie operazioni che richiedono
    // l'invio di dati tramite POST, si può utilizzare un enum per identificare
    // le varie operazioni.
    enum Operazione: int {
        case AGGIUNGI_AUTORE = 1;
        case SET_DATA_DI_MORTE_AUTORE = 2;
        case RIMUOVI_AUTORE = 3;
        case AGGIUNGI_LIBRO = 4;
        case RIMUOVI_LIBRO = 5;
        case AGGIUNGI_SEDE = 6;
        case RIMUOVI_SEDE = 7;
        case AGGIUNGI_COPIA = 8;
        case CAMBIA_SEDE = 9;
        case RIMUOVI_COPIA = 10;
        case AGGIUNGI_BIBLIOTECARIO = 11;
        case CAMBIA_EMAIL_BIBLIOTECARIO = 12;
        case CAMBIA_PASSWORD_BIBLIOTECARIO = 13;
        case RIMUOVI_BIBLIOTECARIO = 14;
        case AGGIUNGI_LETTORE = 15;
        case CAMBIA_EMAIL_LETTORE = 16;
        case CAMBIA_PASSWORD_LETTORE = 17;
        case CAMBIA_CATEGORIA_LETTORE = 18;
        case AZZERA_RITARDI_LETTORE = 19;
        case RIMUOVI_LETTORE = 20;
        case RICHIEDI_PRESTITO = 21;
        case RESTITUISCI_PRESTITO = 22;
        case PROROGA_PRESTITO = 23;
    }

    function aggiungiAutore(string $nome, string $cognome, string $biografia, string $data_di_nascita, string $data_di_morte): void {
        $conn = pg_connect(CONNECTION_STRING);
        if (!$conn)
           throw new ErroreInternoDatabaseException();

        $query = "SELECT biblioteca.aggiungiAutore($1, $2, $3, $4";
        $query .= empty($data_di_morte) ? ")" :  ", $5)";
        if (!pg_prepare($conn, "aggiungi_autore", $query))
            throw new ErroreInternoDatabaseException();

        $params = array($nome, $cognome, $biografia, $data_di_nascita);
        $params += empty($data_di_morte) ? [] : array($data_di_morte);
        $result = pg_execute($conn, "aggiungi_autore", $params);
        if (!$result)
            throw new ErroreInternoDatabaseException();

        [$error] = pg_fetch_row($result);

        if (!pg_free_result($result))
            throw new ErroreInternoDatabaseException();

        if (!pg_close($conn))
            throw new ErroreInternoDatabaseException();

        $error = DatabaseErrors::fromEnumString($error);
        if (!is_null($error))
            throw $error;
    }

    function getAutori(): array {
        $conn = pg_connect(CONNECTION_STRING);
        if (!$conn)
            throw new ErroreInternoDatabaseException();

        $query = "SELECT biblioteca.getAutori()";
        if (!pg_prepare($conn, "get_autori", $query))
            throw new ErroreInternoDatabaseException();

        $result = pg_execute($conn, "get_autori", []);
        if (!$result)
            throw new ErroreInternoDatabaseException();

        $autori = pg_fetch_all($result);

        if (!pg_free_result($result))
            throw new ErroreInternoDatabaseException();

        if (!pg_close($conn))
            throw new ErroreInternoDatabaseException();

        return $autori;
    }

    function getAutoreById(string $id): array {
        $conn = pg_connect(CONNECTION_STRING);
        if (!$conn)
            throw new ErroreInternoDatabaseException();

        $query = "SELECT biblioteca.getAutoreById($1)";
        if (!pg_prepare($conn, "get_autore_by_id", $query))
            throw new ErroreInternoDatabaseException();

        $result = pg_execute($conn, "get_autore_by_id", array($id));
        if (!$result)
            throw new ErroreInternoDatabaseException();

        $autore = pg_fetch_row($result);

        if (!pg_free_result($result))
            throw new ErroreInternoDatabaseException();

        if (!pg_close($conn))
            throw new ErroreInternoDatabaseException();

        return $autore;
    }

    function setAutoreDataDiMorte(string $id_autore, string $data_di_morte): void {
        $conn = pg_connect(CONNECTION_STRING);
        if (!$conn)
            throw new ErroreInternoDatabaseException();

        $query = "SELECT biblioteca.setAutoreDataDiMorte($1, $2)";
        if (!pg_prepare($conn, "set_autore_data_di_morte", $query))
            throw new ErroreInternoDatabaseException();

        $result = pg_execute($conn, "set_autore_data_di_morte", array($id_autore, $data_di_morte));
        if (!$result)
            throw new ErroreInternoDatabaseException();

        if (!pg_free_result($result))
            throw new ErroreInternoDatabaseException();

        if (!pg_close($conn))
            throw new ErroreInternoDatabaseException();
    }

    function rimuoviAutore(string $id_autore): void {
        $conn = pg_connect(CONNECTION_STRING);
        if (!$conn)
            throw new ErroreInternoDatabaseException();

        $query = "SELECT biblioteca.rimuoviAutore($1)";
        if (!pg_prepare($conn, "rimuovi_autore", $query))
            throw new ErroreInternoDatabaseException();

        $result = pg_execute($conn, "rimuovi_autore", array($id_autore));
        if (!$result)
            throw new ErroreInternoDatabaseException();

        [$error] = pg_fetch_row($result);

        if (!pg_free_result($result))
            throw new ErroreInternoDatabaseException();

        if (!pg_close($conn))
            throw new ErroreInternoDatabaseException();

        $error = DatabaseErrors::fromEnumString($error);
        if (!is_null($error))
            throw $error;
    }

    function aggiungiLibro(Isbn $isbn, string $titolo, string $trama, string $casa_editrice, array $autori): void {
        $conn = pg_connect(CONNECTION_STRING);
        if (!$conn)
            throw new ErroreInternoDatabaseException();

        $query = "SELECT biblioteca.aggiungiLibro($1, $2, $3, $4, $5)";
        if (!pg_prepare($conn, "aggiungi_libro", $query))
            throw new ErroreInternoDatabaseException();

        // $autori va convertito in un array literal
        $autori = "{" . implode(", ", $autori) . "}";

        $result = pg_execute($conn, "aggiungi_libro", array($isbn, $titolo, $trama, $casa_editrice, $autori));
        if (!$result)
            throw new ErroreInternoDatabaseException();

        [$error] = pg_fetch_row($result);

        if (!pg_free_result($result))
            throw new ErroreInternoDatabaseException();

        if (!pg_close($conn))
            throw new ErroreInternoDatabaseException();

        $error = DatabaseErrors::fromEnumString($error);
        if (!is_null($error))
            throw $error;
    }

    function getLibri(): array {
        $conn = pg_connect(CONNECTION_STRING);
        if (!$conn)
            throw new ErroreInternoDatabaseException();

        $query = "SELECT biblioteca.getLibri()";
        if (!pg_prepare($conn, "get_libri", $query))
            throw new ErroreInternoDatabaseException();

        $result = pg_execute($conn, "get_libri", []);
        if (!$result)
            throw new ErroreInternoDatabaseException();

        $libri = pg_fetch_all($result);

        if (!pg_free_result($result))
            throw new ErroreInternoDatabaseException();

        if (!pg_close($conn))
            throw new ErroreInternoDatabaseException();

        return $libri;
    }

    function getLibroByIsbn(string $isbn): array {
        $conn = pg_connect(CONNECTION_STRING);
        if (!$conn)
            throw new ErroreInternoDatabaseException();

        $query = "SELECT biblioteca.getLibroByIsbn($1)";
        if (!pg_prepare($conn, "get_libro_by_isbn", $query))
            throw new ErroreInternoDatabaseException();

        $result = pg_execute($conn, "get_libro_by_isbn", array($isbn));
        if (!$result)
            throw new ErroreInternoDatabaseException();

        $libro = pg_fetch_row($result);

        if (!pg_free_result($result))
            throw new ErroreInternoDatabaseException();

        if (!pg_close($conn))
            throw new ErroreInternoDatabaseException();

        return $libro;
    }

    function rimuoviLibro(string $isbn): void {
        $conn = pg_connect(CONNECTION_STRING);
        if (!$conn)
            throw new ErroreInternoDatabaseException();

        $query = "SELECT biblioteca.rimuoviLibro($1)";
        if (!pg_prepare($conn, "rimuovi_libro", $query))
            throw new ErroreInternoDatabaseException();

        $result = pg_execute($conn, "rimuovi_libro", array($isbn));
        if (!$result)
            throw new ErroreInternoDatabaseException();

        [$error] = pg_fetch_row($result);

        if (!pg_free_result($result))
            throw new ErroreInternoDatabaseException();

        if (!pg_close($conn))
            throw new ErroreInternoDatabaseException();

        $error = DatabaseErrors::fromEnumString($error);
        if (!is_null($error))
            throw $error;
    }

    function aggiungiSede(string $indirizzo, string $citta): void {
        $conn = pg_connect(CONNECTION_STRING);
        if (!$conn)
            throw new ErroreInternoDatabaseException();

        $query = "SELECT biblioteca.aggiungiSede($1, $2)";
        if (!pg_prepare($conn, "aggiungi_sede", $query))
            throw new ErroreInternoDatabaseException();

        $result = pg_execute($conn, "aggiungi_sede", array($indirizzo, $citta));
        if (!$result)
            throw new ErroreInternoDatabaseException();

        if (!pg_free_result($result))
            throw new ErroreInternoDatabaseException();

        if (!pg_close($conn))
            throw new ErroreInternoDatabaseException();
    }

    function getSedi(): array {
        $conn = pg_connect(CONNECTION_STRING);
        if (!$conn)
            throw new ErroreInternoDatabaseException();

        $query = "SELECT biblioteca.getSedi()";
        if (!pg_prepare($conn, "get_sedi", $query))
            throw new ErroreInternoDatabaseException();

        $result = pg_execute($conn, "get_sedi", []);
        if (!$result)
            throw new ErroreInternoDatabaseException();

        $sedi = pg_fetch_all($result);

        if (!pg_free_result($result))
            throw new ErroreInternoDatabaseException();

        if (!pg_close($conn))
            throw new ErroreInternoDatabaseException();
        
        return $sedi;
    }

    function getSedeById(string $id_sede): array {
        $conn = pg_connect(CONNECTION_STRING);
        if (!$conn)
            throw new ErroreInternoDatabaseException();

        $query = "SELECT biblioteca.getSedeById($1)";
        if (!pg_prepare($conn, "get_sede_by_id", $query))
            throw new ErroreInternoDatabaseException();

        $result = pg_execute($conn, "get_sede_by_id", array($id_sede));
        if (!$result)
            throw new ErroreInternoDatabaseException();

        $sede = pg_fetch_row($result);

        if (!pg_free_result($result))
            throw new ErroreInternoDatabaseException();

        if (!pg_close($conn))
            throw new ErroreInternoDatabaseException();
        
        return $sede;
    }

    function getRitardi(string $id_sede): array {
        $conn = pg_connect(CONNECTION_STRING);
        if (!$conn)
            throw new ErroreInternoDatabaseException();

        $query = "SELECT biblioteca.getRitardi($1)";
        if (!pg_prepare($conn, "get_ritardi", $query))
            throw new ErroreInternoDatabaseException();

        $result = pg_execute($conn, "get_ritardi", array($id_sede));
        if (!$result)
            throw new ErroreInternoDatabaseException();

        $ritardi = pg_fetch_all($result);

        if (!pg_free_result($result))
            throw new ErroreInternoDatabaseException();

        if (!pg_close($conn))
            throw new ErroreInternoDatabaseException();

        return $ritardi; 
    }

    function rimuoviSede(string $id_sede): void {
        $conn = pg_connect(CONNECTION_STRING);
        if (!$conn)
            throw new ErroreInternoDatabaseException();

        $query = "SELECT biblioteca.rimuoviSede($1)";
        if (!pg_prepare($conn, "rimuovi_sede", $query))
            throw new ErroreInternoDatabaseException();

        $result = pg_execute($conn, "rimuovi_sede", array($id_sede));
        if (!$result)
            throw new ErroreInternoDatabaseException();

        [$error] = pg_fetch_row($result);

        if (!pg_free_result($result))
            throw new ErroreInternoDatabaseException();

        if (!pg_close($conn))
            throw new ErroreInternoDatabaseException();

        $error = DatabaseErrors::fromEnumString($error);
        if (!is_null($error))
            throw $error;
    }

    function aggiungiCopia(string $isbn, string $id_sede): void {
        $conn = pg_connect(CONNECTION_STRING);
        if (!$conn)
            throw new ErroreInternoDatabaseException();

        $query = "SELECT biblioteca.aggiungiCopia($1, $2)";
        if (!pg_prepare($conn, "aggiungi_copia", $query))
            throw new ErroreInternoDatabaseException();

        $result = pg_execute($conn, "aggiungi_copia", array($isbn, $id_sede));
        if (!$result)
            throw new ErroreInternoDatabaseException();

        if (!pg_free_result($result))
            throw new ErroreInternoDatabaseException();

        if (!pg_close($conn))
            throw new ErroreInternoDatabaseException();
    }

    function getCopie(): array {
        $conn = pg_connect(CONNECTION_STRING);
        if (!$conn)
            throw new ErroreInternoDatabaseException();

        $query = "SELECT biblioteca.getCopie()";
        if (!pg_prepare($conn, "get_copie", $query))
            throw new ErroreInternoDatabaseException();

        $result = pg_execute($conn, "get_copie", []);
        if (!$result)
            throw new ErroreInternoDatabaseException();

        $copie = pg_fetch_all($result);

        if (!pg_free_result($result))
            throw new ErroreInternoDatabaseException();

        if (!pg_close($conn))
            throw new ErroreInternoDatabaseException();

        return $copie;
    }

    function getCopiaById(string $id_copia): array {
        $conn = pg_connect(CONNECTION_STRING);
        if (!$conn)
            throw new ErroreInternoDatabaseException();

        $query = "SELECT biblioteca.getCopiaById($1)";
        if (!pg_prepare($conn, "get_copia_by_id", $query))
            throw new ErroreInternoDatabaseException();

        $result = pg_execute($conn, "get_copia_by_id", array($id_copia));
        if (!$result)
            throw new ErroreInternoDatabaseException();

        $copia = pg_fetch_row($result);

        if (!pg_free_result($result))
            throw new ErroreInternoDatabaseException();

        if (!pg_close($conn))
            throw new ErroreInternoDatabaseException();

        return $copia;
    }

    function getCopieBySede(string $id_sede): array {
        $conn = pg_connect(CONNECTION_STRING);
        if (!$conn)
            throw new ErroreInternoDatabaseException();

        $query = "SELECT biblioteca.getCopieBySede($1)";
        if (!pg_prepare($conn, "get_copie_by_sede", $query))
            throw new ErroreInternoDatabaseException();

        $result = pg_execute($conn, "get_copie_by_sede", array($id_sede));
        if (!$result)
            throw new ErroreInternoDatabaseException();

        $copie = pg_fetch_all($result);
        
        if (!pg_free_result($result))
            throw new ErroreInternoDatabaseException();

        if (!pg_close($conn))
            throw new ErroreInternoDatabaseException();

        return $copie;
    }

    function setSede(string $id_copia, string $id_nuova_sede): void {
        $conn = pg_connect(CONNECTION_STRING);
        if (!$conn)
            throw new ErroreInternoDatabaseException();

        $query = "SELECT biblioteca.setSede($1, $2)";
        if (!pg_prepare($conn, "set_sede", $query))
            throw new ErroreInternoDatabaseException();

        $result = pg_execute($conn, "set_sede", array($id_copia, $id_nuova_sede));
        if (!$result)
            throw new ErroreInternoDatabaseException();

        [$error] = pg_fetch_array($result);

        if (!pg_free_result($result))
            throw new ErroreInternoDatabaseException();

        if (!pg_close($conn))
            throw new ErroreInternoDatabaseException();

        $error = DatabaseError::fromEnumString($error);
        if (!is_null($error))
            throw $error;
    }

    function rimuoviCopia(string $id_copia): void {
        $conn = pg_connect(CONNECTION_STRING);
        if (!$conn)
            throw new ErroreInternoDatabaseException();

        $query = "SELECT biblioteca.rimuoviCopia($1)";
        if (!pg_prepare($conn, "rimuovi_copia", $query))
            throw new ErroreInternoDatabaseException();

        $result = pg_execute($conn, "rimuovi_copia", array($id_copia));
        if (!$result)
            throw new ErroreInternoDatabaseException();

        [$error] = pg_fetch_array($result);

        if (!pg_free_result($result))
            throw new ErroreInternoDatabaseException();

        if (!pg_close($conn))
            throw new ErroreInternoDatabaseException();
        $error = DatabaseError::fromEnumString($error);
        if (!is_null($error))
            throw $error;
    }

    function aggiungiLettore(string $nome, string $cognome, Email $email, 
            Categoria $categoria, CodiceFiscale $codice_fiscale, string $password): void {
        $conn = pg_connect(CONNECTION_STRING);
        if (!$conn)
            throw new ErroreInternoDatabaseException();

        $hash = password_hash($password, HASHING_ALGORITHM);

        $query = "SELECT biblioteca.aggiungiLettore($1, $2, $3, $4, $5, $6)";
        if (!pg_prepare($conn, "registra_lettore", $query))
            throw new ErroreInternoDatabaseException();

        $result = pg_execute($conn, "registra_lettore", array($codice_fiscale, $nome, $cognome, $email, $hash, $categoria->encoding()));
        if (!$result)
            throw new ErroreInternoDatabaseException();

        [$error] = pg_fetch_array($result);

        if (!pg_free_result($result))
            throw new ErroreInternoDatabaseException();

        if (!pg_close($conn))
            throw new ErroreInternoDatabaseException();

        $error = DatabaseError::fromEnumString($error); 
        if (!is_null($error))
            throw $error;
    }

    function getLettori(): array {
        $conn = pg_connect(CONNECTION_STRING);
        if (!$conn)
            throw new ErroreInternoDatabaseException();

        $query = "SELECT biblioteca.getLettori()";
        if (!pg_prepare($conn, "get_lettori", $query))
            throw new ErroreInternoDatabaseException();

        $result = pg_execute($conn, "get_lettori", []);
        if (!$result)
            throw new ErroreInternoDatabaseException();

        $lettori = pg_fetch_all($result);

        if (!pg_free_result($result))
            throw new ErroreInternoDatabaseException();

        if (!pg_close($conn))
            throw new ErroreInternoDatabaseException();

        return $lettori;
    }

    function getLettoreByCodiceFiscale(CodiceFiscale $codice_fiscale): array {
        $conn = pg_connect(CONNECTION_STRING);
        if (!$conn)
            throw new ErroreInternoDatabaseException();

        $query = "SELECT biblioteca.getLettoreByCodiceFiscale($1)";
        if (!pg_prepare($conn, "get_lettore_by_codice_fiscale", $query))
            throw new ErroreInternoDatabaseException();

        $result = pg_execute($conn, "get_lettore_by_codice_fiscale", array($codice_fiscale));
        if (!$result)
            throw new ErroreInternoDatabaseException();

        $lettore = pg_fetch_row($result);

        if (!pg_free_result($result))
            throw new ErroreInternoDatabaseException();

        if (!pg_close($conn))
            throw new ErroreInternoDatabaseException();

        return $lettore;
    }

    function getLettoreByEmail(Email $email): array {
        $conn = pg_connect(CONNECTION_STRING);
        if (!$conn)
            throw new ErroreInternoDatabaseException();

        $query = "SELECT biblioteca.getLettoreByEmail($1)";
        if (!pg_prepare($conn, "get_lettore_by_email", $query))
            throw new ErroreInternoDatabaseException();

        $result = pg_execute($conn, "get_lettore_by_email", array($email));
        if (!$result)
            throw new ErroreInternoDatabaseException();

        $lettore = pg_fetch_row($result);

        if (!pg_free_result($result))
            throw new ErroreInternoDatabaseException();

        if (!pg_close($conn))
            throw new ErroreInternoDatabaseException();

        return $lettore;
    }

    function setLettoreEmail(CodiceFiscale $codice_fiscale, Email $nuova_email): void {
        $conn = pg_connect(CONNECTION_STRING);
        if (!$conn)
            throw new ErroreInternoDatabaseException();

        $query = "SELECT biblioteca.setLettoreEmail($1, $2)";
        if (!pg_prepare($conn, "set_lettore_email", $query))
            throw new ErroreInternoDatabaseException();

        $result = pg_execute($conn, "set_lettore_email", array($codice_fiscale, $nuova_email));
        if (!$result)
            throw new ErroreInternoDatabaseException();

        [$error] = pg_fetch_row($result);

        if (!pg_free_result($result))
            throw new ErroreInternoDatabaseException();

        if (!pg_close($conn))
            throw new ErroreInternoDatabaseException();

        $error = DatabaseException::fromEnumString($error);
        if (!is_null($error))
            throw $error;

        setcookie("user_email", $nuova_email, time() + 3600, "/");
    }

    function setLettorePassword(CodiceFiscale $codice_fiscale, string $vecchia_password, string $nuova_password): void {
        $conn = pg_connect(CONNECTION_STRING);
        if (!$conn)
            throw new ErroreInternoDatabaseException();

        $password = getLettoreByCodiceFiscale($codice_fiscale)["password"];
        if (!password_verify($vecchia_password, $password))
            throw new PasswordErrataException();

        $hash = password_hash($nuova_password, HASHING_ALGORITHM);

        $query = "SELECT biblioteca.setLettorePassword($1, $2)";
        if (!pg_prepare($conn, "set_lettore_password", $query))
            throw new ErroreInternoDatabaseException();

        $result = pg_execute($conn, "set_lettore_password", array($codice_fiscale, $hash));
        if (!$result)
            throw new ErroreInternoDatabaseException();

        if (!pg_free_result($result))
            throw new ErroreInternoDatabaseException();

        if (!pg_close($conn))
            throw new ErroreInternoDatabaseException();
    }

    function setLettoreCategoria(CodiceFiscale $codice_fiscale, Categoria $categoria): void {
        $conn = pg_connect(CONNECTION_STRING);
        if (!$conn)
            throw new ErroreInternoDatabaseException();

        $query = "SELECT biblioteca.setLettoreCategoria($1, $2)";
        if (!pg_prepare($conn, "set_lettore_categoria", $query))
            throw new ErroreInternoDatabaseException();

        $result = pg_execute($conn, "set_lettore_categoria", array($codice_fiscale, $categoria->encoding()));
        if (!$result)
            throw new ErroreInternoDatabaseException();

        if (!pg_free_result($result))
            throw new ErroreInternoDatabaseException();

        if (!pg_close($conn))
            throw new ErroreInternoDatabaseException();
    }

    function azzeraRitardi(CodiceFiscale $codice_fiscale): void {
        $conn = pg_connect(CONNECTION_STRING);
        if (!$conn)
            throw new ErroreInternoDatabaseException();

        $query = "SELECT biblioteca.azzeraRitardi($1)";
        if (!pg_prepare($conn, "azzera_ritardi", $query))
            throw new ErroreInternoDatabaseException();

        $result = pg_execute($conn, "azzera_ritardi", array($codice_fiscale));
        if (!$result)
            throw new ErroreInternoDatabaseException();

        if (!pg_free_result($result))
            throw new ErroreInternoDatabaseException();

        if (!pg_close($conn))
            throw new ErroreInternoDatabaseException();
    }

    function rimuoviLettore(CodiceFiscale $codice_fiscale): void {
        $conn = pg_connect(CONNECTION_STRING);
        if (!$conn)
            throw new ErroreInternoDatabaseException();

        $query = "SELECT biblioteca.rimuoviLettore($1)";
        if (!pg_prepare($conn, "rimuovi_lettore", $query))
            throw new ErroreInternoDatabaseException();

        $result = pg_execute($conn, "rimuovi_lettore", array($codice_fiscale));
        if (!$result)
            throw new ErroreInternoDatabaseException();

        [$error] = pg_fetch_array($result);

        if (!pg_free_result($result))
            throw new ErroreInternoDatabaseException();

        if (!pg_close($conn))
            throw new ErroreInternoDatabaseException();

        $error = DatabaseException::fromEnumString($error);
        if (!is_null($error))
            throw $error;
    }

    function richiediPrestito(string $id_copia, CodiceFiscale $cf_lettore): void {
        $conn = pg_connect(CONNECTION_STRING);
        if (!$conn)
            throw new ErroreInternoDatabaseException();

        $query = "SELECT biblioteca.richiediPrestito($1, $2)";
        if (!pg_prepare($conn, "richiedi_prestito", $query))
            throw new ErroreInternoDatabaseException();

        $result = pg_execute($conn, "richiedi_prestito", array($id_copia, $cf_lettore));
        if (!$result)
            throw new ErroreInternoDatabaseException();

        [$error] = pg_fetch_array($result);

        if (!pg_free_result($result))
            throw new ErroreInternoDatabaseException();

        if (!pg_close($conn))
            throw new ErroreInternoDatabaseException();

        $error =  DatabaseException::fromEnumString($error);
        if (!is_null($error))
            throw $error;
    }

    function getPrestiti(): array {
        $conn = pg_connect(CONNECTION_STRING);
        if (!$conn)
            throw new ErroreInternoDatabaseException();

        $query = "SELECT biblioteca.getPrestiti()";
        if (!pg_prepare($conn, "get_prestiti", $query))
            throw new ErroreInternoDatabaseException();

        $result = pg_execute($conn, "get_prestiti", []);
        if (!$result)
            throw new ErroreInternoDatabaseException();

        $prestiti = pg_fetch_all($result);

        if (!pg_free_result($result))
            throw new ErroreInternoDatabaseException();

        if (!pg_close($conn))
            throw new ErroreInternoDatabaseException();

        return $prestiti;
    }

    function getPrestitoByCopia(string $id_copia): array {
        $conn = pg_connect(CONNECTION_STRING);
        if (!$conn)
            throw new ErroreInternoDatabaseException();

        $query = "SELECT biblioteca.getPrestitoByCopia($1)"; 
        if (!pg_prepare($conn, "get_prestito_by_copia", $query))
            throw new ErroreInternoDatabaseException();

        $result = pg_execute($conn, "get_prestito_by_copia", array($id_copia));
        if (!$result)
            throw new ErroreInternoDatabaseException();

        $prestito = pg_fetch_row($result);
        
        if (!pg_free_result($result))
            throw new ErroreInternoDatabaseException();

        if (!pg_close($conn))
            throw new ErroreInternoDatabaseException();

        return $prestito;
    }

    function restituisciPrestito(string $id_copia): void {
        $conn = pg_connect(CONNECTION_STRING);
        if (!$conn)
            throw new ErroreInternoDatabaseException();

        $query = "SELECT biblioteca.restituisciPrestito($1)";
        if (!pg_prepare($conn, "restituisci_prestito", $query))
            throw new ErroreInternoDatabaseException();

        $result = pg_execute($conn, "restituisci_prestito", array($id_copia));
        if (!$result)
            throw new ErroreInternoDatabaseException();

        [$error] = pg_fetch_row($result);

        if (!pg_free_result($result))
            throw new ErroreInternoDatabaseException();

        if (!pg_close($conn))
            throw new ErroreInternoDatabaseException();

        $error = DatabaseException::fromEnumString($error);
        if (!is_null($error))
            throw $error;
    }

    function prorogaPrestito(string $id_copia, int $giorni_di_proroga): void {
        $conn = pg_connect(CONNECTION_STRING);
        if (!$conn)
            throw new ErroreInternoDatabaseException();

        $query = "SELECT biblioteca.prorogaPrestito($1, $2)";
        if (!pg_prepare($conn, "proroga_prestito", $query))
            throw new ErroreInternoDatabaseException();

        $result = pg_execute($conn, "proroga_prestito", array($id_copia, $giorni_di_proroga));
        if (!$result)
            throw new ErroreInternoDatabaseException();

        [$error] = pg_fetch_array($result);

        if (!pg_free_result($result))
            throw new ErroreInternoDatabaseException();

        if (!pg_close($conn))
            throw new ErroreInternoDatabaseException();

        $error = DatabaseException::fromEnumString($error);
        if (!is_null($error))
            throw $error;
    }

    function aggiungiBibliotecario(Email $email, string $password): void {
        $conn = pg_connect(CONNECTION_STRING);
        if (!$conn)
            throw new ErroreInternoDatabaseException();

        $hash = password_hash($password, HASHING_ALGORITHM);
        
        $query = "SELECT biblioteca.aggiungiBibliotecario($1, $2)";
        if (!pg_prepare($conn, "aggiungi_bibliotecario", $query))
            throw new ErroreInternoDatabaseException();

        $result = pg_execute($conn, "aggiungi_bibliotecario", array($email, $hash));
        if (!$result)
            throw new ErroreInternoDatabaseException();
        
        [$error] = pg_fetch_row($result);

        if (!pg_free_result($result))
            throw new ErroreInternoDatabaseException();

        if (!pg_close($conn))
            throw new ErroreInternoDatabaseException();

        $error = DatabaseException::fromEnumString($error);
        if (!is_null($error))
            throw $error;
    }

    function getBibliotecari(): array {
        $conn = pg_connect(CONNECTION_STRING);
        if (!$conn)
            throw new ErroreInternoDatabaseException();

        $query = "SELECT biblioteca.getBibliotecari()";
        if (!pg_prepare($conn, "get_bibliotecari", $query))
            throw new ErroreInternoDatabaseException();

        $result = pg_execute($conn, "get_bibliotecari", []);
        if (!$result)
            throw new ErroreInternoDatabaseException();

        $bibliotecari = pg_fetch_all($result);

        if (!pg_free_result($result))
            throw new ErroreInternoDatabaseException();

        if (!pg_close($conn))
            throw new ErroreInternoDatabaseException();

        return $bibliotecari;
    }

    function getBibliotecarioById(string $id_bibliotecario): array {
        $conn = pg_connect(CONNECTION_STRING);
        if (!$conn)
            throw new ErroreInternoDatabaseException();

        $query = "SELECT biblioteca.getBibliotecarioById($1)";
        if (!pg_prepare($conn, "get_bibliotecario_by_id", $query))
            throw new ErroreInternoDatabaseException();

        $result = pg_execute($conn, "get_bibliotecario_by_id", array($id_bibliotecario));
        if (!$result)
            throw new ErroreInternoDatabaseException();

        $bibliotecario = pg_fetch_row($result);

        if (!pg_free_result($result))
            throw new ErroreInternoDatabaseException();

        if (!pg_close($conn))
            throw new ErroreInternoDatabaseException();

        return $bibliotecario;
    }

    function getBibliotecarioByEmail(Email $email): array {
        $conn = pg_connect(CONNECTION_STRING);
        if (!$conn)
            throw new ErroreInternoDatabaseException();

        $query = "SELECT biblioteca.getBibliotecarioByEmail($1)";
        if (!pg_prepare($conn, "get_bibliotecario_by_email", $query))
            throw new ErroreInternoDatabaseException();

        $result = pg_execute($conn, "get_bibliotecario_by_email", array($email));
        if (!$result)
            throw new ErroreInternoDatabaseException();

        $bibliotecario = pg_fetch_row($result);

        if (!pg_free_result($result))
            throw new ErroreInternoDatabaseException();

        if (!pg_close($conn))
            throw new ErroreInternoDatabaseException();

        return $bibliotecario;
    }

    function setBibliotecarioEmail(string $id_bibliotecario, Email $nuova_email): void {
        $conn = pg_connect(CONNECTION_STRING);
        if (!$conn)
            throw new ErroreInternoDatabaseException();

        $query = "SELECT biblioteca.setBibliotecarioEmail($1, $2)";
        if (!pg_prepare($conn, "set_bibliotecario_email", $query))
            throw new ErroreInternoDatabaseException();

        $result = pg_execute($conn, "set_bibliotecario_email", array($id_bibliotecario, $nuova_email));
        if (!$result)
            throw new ErroreInternoDatabaseException();

        [$error] = pg_fetch_array($result);

        if (!pg_free_result($result))
            throw new ErroreInternoDatabaseException();

        if (!pg_close($conn))
            throw new ErroreInternoDatabaseException();

        $error = DatabaseException::fromEnumString($error);
        if (!is_null($error))
            throw $error;

        setcookie('user_email', $nuova_email, time() + 3600, '/');
    }

    function setBibliotecarioPassword(string $id_bibliotecario, string $vecchia_password, string $nuova_password): void {
        $conn = pg_connect(CONNECTION_STRING);
        if (!$conn)
            throw new ErroreInternoDatabaseException();

        $password = getBibliotecarioById($id_bibliotecario)["password"];
        if (!password_verify($vecchia_password, $password))
            throw new PasswordErrataException();

        $hash = password_hash($nuova_password, HASHING_ALGORITHM);

        $query = "SELECT biblioteca.setBibliotecarioPassword($1, $2)";
        if (!pg_prepare($conn, "set_bibliotecario_password", $query))
            throw new ErroreInternoDatabaseException();

        $result = pg_execute($conn, "set_bibliotecario_password", array($id_bibliotecario, $hash));
        if (!$result)
            throw new ErroreInternoDatabaseException();

        if (!pg_free_result($result))
            throw new ErroreInternoDatabaseException();

        if (!pg_close($conn))
            throw new ErroreInternoDatabaseException();
    }

    function rimuoviBibliotecario(string $id_bibliotecario): void {
        $conn = pg_connect(CONNECTION_STRING);
        if (!$conn)
            throw new ErroreInternoDatabaseException();

        $query = "SELECT biblioteca.rimuoviBibliotecario($1)";
        if (!pg_prepare($conn, "rimuovi_bibliotecario", $query))
            throw new ErroreInternoDatabaseException();

        $result = pg_execute($conn, "rimuovi_bibliotecario", array($id_bibliotecario));
        if (!$result)
            throw new ErroreInternoDatabaseException();

        if (!pg_free_result($result))
            throw new ErroreInternoDatabaseException();

        if (!pg_close($conn))
            throw new ErroreInternoDatabaseException();
    }

    // altre funzioni

    function login(Utente $utente, Email $email, string $password): void {
        $conn = pg_connect(CONNECTION_STRING);
        if (!$conn)
            throw new ErroreInternoDatabaseException();

        if ($utente == Utente::BIBLIOTECARIO) {
            $bibliotecario = getBibliotecarioByEmail($email);
            $hash = $bibliotecario['password'];
            $id = $bibliotecario['id'];
        } else if ($utente == Utente::LETTORE) {
            $lettore = getLettoreByEmail($email);
            $hash = $lettore['password'];
            $id = $lettore['codice_fiscale'];
        }

        if (!password_verify($password, $hash))
            throw new PasswordErrataException();

        setcookie('user_id', $id, time() + 3600, '/');
        setcookie('user_email', $email, time() + 3600, '/');
    }

    function isLoggedIn(Utente $utente): bool {
        if (!isset($_COOKIE['user_id']))
            return false;

        $id = $_COOKIE['user_id'];
        if ($utente == Utente::BIBLIOTECARIO)
            $user = getBibliotecarioById($id);
        else if ($utente == Utente::LETTORE)
            $user = getLettoreByCodiceFiscale($id);

        // controlla se l'utente è valido
        if (!empty($user))
            return true;

        return false;
    }

    function logout(): void {
        setcookie('user_id', '', time() - 3600, '/');
        setcookie('user_email', '', time() - 3600, '/');
    }

?>
