<?php
    include_once 'exceptions.php';
    include_once 'objects.php';

    define('DB_HOST', 'postgres');
    define('DB_PORT', '5432');
    define('DB_NAME', 'biblioteca');
    define('DB_USER', 'postgres');
    define('DB_PASSWORD', 'weakpassword');
    define('CONNECTION_STRING', "host=" . DB_HOST . " port=" . DB_PORT .
                               " dbname=" . DB_NAME . " user=" . DB_USER .
                               " password=" . DB_PASSWORD);

    define('HASHING_ALGORITHM', PASSWORD_DEFAULT);

    /**
     * Aggiunge un autore al database.
     *
     * @param $nome il nome dell'autore
     * @param $cognome il cognome dell'autore
     * @param $biografia la biografia dell'autore
     * @param $data_di_nascita la data di nascita dell'autore
     * @param $data_di_morte la data di morte dell'autore
     * @throws ErroreInternoDatabaseException se si verifica un errore interno al database
     */
    function aggiungiAutore(string $nome, string $cognome, string $biografia, string $data_di_nascita, string $data_di_morte): void {
        $conn = pg_connect(CONNECTION_STRING);
        if (!$conn)
           throw new ErroreInternoDatabaseException();

        $query = "SELECT biblioteca.aggiungiAutore($1, $2, $3, $4";
        $query .= empty($data_di_morte) ? ")" :  ", $5)";
        if (!pg_prepare($conn, "aggiungi_autore", $query))
            throw new ErroreInternoDatabaseException();

        $params = array($nome, $cognome, $biografia, $data_di_nascita);
        if (!empty($data_di_morte))
            $params[] = $data_di_morte;
        $result = pg_execute($conn, "aggiungi_autore", $params);
        if (!$result)
            throw new ErroreInternoDatabaseException();

        if (!pg_free_result($result))
            throw new ErroreInternoDatabaseException();

        if (!pg_close($conn))
            throw new ErroreInternoDatabaseException();
    }

    /**
     * Restituisce tutti gli autori presenti nel database.
     *
     * @return un array di array associativi contenenti i dati degli autori
     * @throws ErroreInternoDatabaseException se si verifica un errore interno al database
     */
    function getAutori(): array {
        $conn = pg_connect(CONNECTION_STRING);
        if (!$conn)
            throw new ErroreInternoDatabaseException();

        $query = "SELECT * FROM biblioteca.getAutori()";
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

    /**
     * Restituisce i dati di un autore dato il suo ID.
     *
     * @param $id l'ID dell'autore
     * @return un array contenente i dati dell'autore
     * @throws ErroreInternoDatabaseException se si verifica un errore interno al database
     */
    function getAutoreById(string $id): array {
        $conn = pg_connect(CONNECTION_STRING);
        if (!$conn)
            throw new ErroreInternoDatabaseException();

        $query = "SELECT * FROM biblioteca.getAutoreById($1)";
        if (!pg_prepare($conn, "get_autore_by_id", $query))
            throw new ErroreInternoDatabaseException();

        $result = pg_execute($conn, "get_autore_by_id", array($id));
        if (!$result)
            throw new ErroreInternoDatabaseException();

        $autore = pg_fetch_array($result);
        if (!$autore)
            $autore = [];

        if (!pg_free_result($result))
            throw new ErroreInternoDatabaseException();

        if (!pg_close($conn))
            throw new ErroreInternoDatabaseException();

        return $autore;
    }

    /**
     * Imposta la data di morte di un autore.
     *
     * @param $id_autore l'ID dell'autore
     * @param $data_di_morte la data di morte dell'autore
     * @throws ErroreInternoDatabaseException se si verifica un errore interno al database
     */
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

    /**
     * Rimuove un autore dal database.
     *
     * @param $id_autore l'ID dell'autore
     * @throws ErroreInternoDatabaseException se si verifica un errore interno al database
     * @throws LibriAssociatiAdAutoreException se ci sono libri associati all'autore
     */
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

        [$error] = pg_fetch_array($result);

        if (!pg_free_result($result))
            throw new ErroreInternoDatabaseException();

        if (!pg_close($conn))
            throw new ErroreInternoDatabaseException();

        $error = DatabaseException::fromEnumString($error);
        if (!is_null($error))
            throw $error;
    }

    /**
     * Aggiunge un libro al database.
     *
     * @param $isbn l'ISBN del libro
     * @param $titolo il titolo del libro
     * @param $trama la trama del libro
     * @param $casa_editrice la casa editrice del libro
     * @param $autori un array contenente gli ID degli autori del libro
     * @throws ErroreInternoDatabaseException se si verifica un errore interno al database
     * @throws IsbnGiàEsistenteException se l'ISBN è già presente nel database
     */
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

        [$error] = pg_fetch_array($result);

        if (!pg_free_result($result))
            throw new ErroreInternoDatabaseException();

        if (!pg_close($conn))
            throw new ErroreInternoDatabaseException();

        $error = DatabaseException::fromEnumString($error);
        if (!is_null($error))
            throw $error;
    }

    /**
     * Restituisce tutti i libri presenti nel database.
     *
     * @return un array di array associativi contenenti i dati dei libri
     * @throws ErroreInternoDatabaseException se si verifica un errore interno al database
     */
    function getLibri(): array {
        $conn = pg_connect(CONNECTION_STRING);
        if (!$conn)
            throw new ErroreInternoDatabaseException();

        $query = "SELECT * FROM biblioteca.getLibri()";
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

    /**
     * Restituisce i dati di un libro dato il suo ISBN.
     *
     * @param $isbn l'ISBN del libro
     * @return un array contenente i dati del libro
     * @throws ErroreInternoDatabaseException se si verifica un errore interno al database
     */
    function getLibroByIsbn(string $isbn): array {
        $conn = pg_connect(CONNECTION_STRING);
        if (!$conn)
            throw new ErroreInternoDatabaseException();

        $query = "SELECT * FROM biblioteca.getLibroByIsbn($1)";
        if (!pg_prepare($conn, "get_libro_by_isbn", $query))
            throw new ErroreInternoDatabaseException();

        $result = pg_execute($conn, "get_libro_by_isbn", array($isbn));
        if (!$result)
            throw new ErroreInternoDatabaseException();

        $libro = pg_fetch_array($result);
        if (!$libro)
            $libro = [];

        if (!pg_free_result($result))
            throw new ErroreInternoDatabaseException();

        if (!pg_close($conn))
            throw new ErroreInternoDatabaseException();

        return $libro;
    }

    /**
     * Restituisce tutti i libri associati ad una data sede.
     *
     * @param $id_sede l'ID della sede
     * @return un array di array associativi contenenti i dati dei libri
     * @throws ErroreInternoDatabaseException se si verifica un errore interno al database
     */
    function getLibriBySede(string $id_sede): array {
        $conn = pg_connect(CONNECTION_STRING);
        if (!$conn)
            throw new ErroreInternoDatabaseException();

        $query = "SELECT * FROM biblioteca.getLibriBySede($1)";
        if (!pg_prepare($conn, "get_libri_by_sede", $query))
            throw new ErroreInternoDatabaseException();

        $result = pg_execute($conn, "get_libri_by_sede", array($id_sede));
        if (!$result)
            throw new ErroreInternoDatabaseException();

        $libri = pg_fetch_all($result);

        if (!pg_free_result($result))
            throw new ErroreInternoDatabaseException();

        if (!pg_close($conn))
            throw new ErroreInternoDatabaseException();

        return $libri;
    }

    /**
     * Rimuove un libro dal database.
     *
     * @param $isbn l'ISBN del libro
     * @throws ErroreInternoDatabaseException se si verifica un errore interno al database
     * @throws CopieAssociateALibroException se ci sono copie associate al libro
     */
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

        [$error] = pg_fetch_array($result);

        if (!pg_free_result($result))
            throw new ErroreInternoDatabaseException();

        if (!pg_close($conn))
            throw new ErroreInternoDatabaseException();

        $error = DatabaseException::fromEnumString($error);
        if (!is_null($error))
            throw $error;
    }

    /**
     * Aggiunge una sede al database.
     *
     * @param $indirizzo l'indirizzo della sede
     * @param $citta la città della sede
     * @throws ErroreInternoDatabaseException se si verifica un errore interno al database
     */
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

    /**
     * Restituisce tutte le sedi presenti nel database.
     *
     * @return un array di array associativi contenenti i dati delle sedi
     * @throws ErroreInternoDatabaseException se si verifica un errore interno al database
     */
    function getSedi(): array {
        $conn = pg_connect(CONNECTION_STRING);
        if (!$conn)
            throw new ErroreInternoDatabaseException();

        $query = "SELECT * FROM biblioteca.getSedi()";
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

    /**
     * Restituisce i dati di una sede dato il suo ID.
     *
     * @param $id_sede l'ID della sede
     * @return un array contenente i dati della sede
     * @throws ErroreInternoDatabaseException se si verifica un errore interno al database
     */
    function getSedeById(string $id_sede): array {
        $conn = pg_connect(CONNECTION_STRING);
        if (!$conn)
            throw new ErroreInternoDatabaseException();

        $query = "SELECT * FROM biblioteca.getSedeById($1)";
        if (!pg_prepare($conn, "get_sede_by_id", $query))
            throw new ErroreInternoDatabaseException();

        $result = pg_execute($conn, "get_sede_by_id", array($id_sede));
        if (!$result)
            throw new ErroreInternoDatabaseException();

        $sede = pg_fetch_array($result);
        if (!$sede)
            $sede = [];

        if (!pg_free_result($result))
            throw new ErroreInternoDatabaseException();

        if (!pg_close($conn))
            throw new ErroreInternoDatabaseException();

        return $sede;
    }

    /**
     * Restituisce i ritardi di una sede.
     *
     * @param $id_sede l'ID della sede
     * @return un array di array associativi contenenti i dati dei ritardi
     * @throws ErroreInternoDatabaseException se si verifica un errore interno al database
     */
    function getRitardi(string $id_sede): array {
        $conn = pg_connect(CONNECTION_STRING);
        if (!$conn)
            throw new ErroreInternoDatabaseException();

        $query = "SELECT * FROM biblioteca.getRitardi($1)";
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

    /**
     * Rimuove una sede dal database.
     *
     * @param $id_sede l'ID della sede
     * @throws ErroreInternoDatabaseException se si verifica un errore interno al database
     * @throws CopieAssociateASedeException se ci sono copie associate alla sede
     */
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

        [$error] = pg_fetch_array($result);

        if (!pg_free_result($result))
            throw new ErroreInternoDatabaseException();

        if (!pg_close($conn))
            throw new ErroreInternoDatabaseException();

        $error = DatabaseException::fromEnumString($error);
        if (!is_null($error))
            throw $error;
    }

    /**
     * Aggiunge una copia al database.
     *
     * @param $isbn l'ISBN del libro della copia
     * @param $id_sede l'ID della sede della copia
     * @throws ErroreInternoDatabaseException se si verifica un errore interno al database
     */
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

    /**
     * Restituisce tutte le copie presenti nel database.
     *
     * @return un array di array associativi contenenti i dati delle copie
     * @throws ErroreInternoDatabaseException se si verifica un errore interno al database
     */
    function getCopie(): array {
        $conn = pg_connect(CONNECTION_STRING);
        if (!$conn)
            throw new ErroreInternoDatabaseException();

        $query = "SELECT * FROM biblioteca.getCopie()";
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

    /**
     * Restituisce i dati di una copia dato il suo ID.
     *
     * @param $id_copia l'ID della copia
     * @return un array contenente i dati della copia
     * @throws ErroreInternoDatabaseException se si verifica un errore interno al database
     */
    function getCopiaById(string $id_copia): array {
        $conn = pg_connect(CONNECTION_STRING);
        if (!$conn)
            throw new ErroreInternoDatabaseException();

        $query = "SELECT * FROM biblioteca.getCopiaById($1)";
        if (!pg_prepare($conn, "get_copia_by_id", $query))
            throw new ErroreInternoDatabaseException();

        $result = pg_execute($conn, "get_copia_by_id", array($id_copia));
        if (!$result)
            throw new ErroreInternoDatabaseException();

        $copia = pg_fetch_array($result);
        if (!$copia)
            $copia = [];

        if (!pg_free_result($result))
            throw new ErroreInternoDatabaseException();

        if (!pg_close($conn))
            throw new ErroreInternoDatabaseException();

        return $copia;
    }

    /**
     * Restituisce tutte le copie disponibili di un tale libro. Se sede != null la
     * ricerca viene ristretta alla sede specificata.
     *
     * @param libro l'id del libro di cui si sta cercando una copia disponibile
     * @param id_sede l'id della sede a cui si restringe la ricerca (se != NULL)
     * @return l'id della copia disponibile se esiste
     * @throws CopiaNonDisponibileException se non esiste una copia disponibile
     */
    function getCopiaDisponibile(string $libro, string $id_sede): string {
        $conn = pg_connect(CONNECTION_STRING);
        if (!$conn)
            throw new ErroreInternoDatabaseException();

        $query = "SELECT * FROM biblioteca.getCopiaDisponibile($1, $2)";
        if (!pg_prepare($conn, "get_copia_disponibile", $query))
            throw new ErroreInternoDatabaseException();

        $result = pg_execute($conn, "get_copia_disponibile", array($libro, $id_sede));
        if (!$result)
            throw new ErroreInternoDatabaseException();

        $copia = pg_fetch_array($result);
        if (is_null($copia))
            throw new CopiaNonDisponibileException();

        if (!pg_free_result($result))
            throw new ErroreInternoDatabaseException();

        if (!pg_close($conn))
            throw new ErroreInternoDatabaseException();

        return $copia;
    }

    /**
     * Cambia la sede di una copia.
     *
     * @param $id_copia l'ID della copia
     * @param $id_nuova_sede l'ID della nuova sede
     * @throws ErroreInternoDatabaseException se si verifica un errore interno al database
     * @throws CopiaInPrestitoException se la copia è in prestito
     */
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

        $error = DatabaseException::fromEnumString($error);
        if (!is_null($error))
            throw $error;
    }

    /**
     * Rimuove una copia dal database.
     *
     * @param $id_copia l'ID della copia
     * @throws ErroreInternoDatabaseException se si verifica un errore interno al database
     * @throws CopiaInPrestitoException se la copia è in prestito
     */
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
        $error = DatabaseException::fromEnumString($error);
        if (!is_null($error))
            throw $error;
    }

    /**
     * Aggiunge un lettore al database.
     *
     * @param $nome il nome del lettore
     * @param $cognome il cognome del lettore
     * @param $email l'email del lettore
     * @param $categoria la categoria del lettore
     * @param $codice_fiscale il codice fiscale del lettore
     * @param $password la password del lettore
     * @throws ErroreInternoDatabaseException se si verifica un errore interno al database
     * @throws LettoreGiàRegistratoException se il lettore è già registrato
     */
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

        $error = DatabaseException::fromEnumString($error);
        if (!is_null($error))
            throw $error;
    }

    /**
     * Restituisce tutti i lettori presenti nel database.
     *
     * @return un array di array associativi contenenti i dati dei lettori
     * @throws ErroreInternoDatabaseException se si verifica un errore interno al database
     */
    function getLettori(): array {
        $conn = pg_connect(CONNECTION_STRING);
        if (!$conn)
            throw new ErroreInternoDatabaseException();

        $query = "SELECT * FROM biblioteca.getLettori()";
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

    /**
     * Restituisce i dati di un lettore dato il suo codice fiscale.
     *
     * @param $codice_fiscale il codice fiscale del lettore
     * @return un array contenente i dati del lettore
     * @throws ErroreInternoDatabaseException se si verifica un errore interno al database
     */
    function getLettoreByCodiceFiscale(CodiceFiscale $codice_fiscale): array {
        $conn = pg_connect(CONNECTION_STRING);
        if (!$conn)
            throw new ErroreInternoDatabaseException();

        $query = "SELECT * FROM biblioteca.getLettoreByCodiceFiscale($1)";
        if (!pg_prepare($conn, "get_lettore_by_codice_fiscale", $query))
            throw new ErroreInternoDatabaseException();

        $result = pg_execute($conn, "get_lettore_by_codice_fiscale", array($codice_fiscale));
        if (!$result)
            throw new ErroreInternoDatabaseException();

        $lettore = pg_fetch_array($result);
        if (!$lettore)
            $lettore = [];

        if (!pg_free_result($result))
            throw new ErroreInternoDatabaseException();

        if (!pg_close($conn))
            throw new ErroreInternoDatabaseException();

        return $lettore;
    }

    /**
     * Restituisce i dati di un lettore dato l'email.
     *
     * @param $email l'email del lettore
     * @return un array contenente i dati del lettore
     * @throws ErroreInternoDatabaseException se si verifica un errore interno al database
     */
    function getLettoreByEmail(Email $email): array {
        $conn = pg_connect(CONNECTION_STRING);
        if (!$conn)
            throw new ErroreInternoDatabaseException();

        $query = "SELECT * FROM biblioteca.getLettoreByEmail($1)";
        if (!pg_prepare($conn, "get_lettore_by_email", $query))
            throw new ErroreInternoDatabaseException();

        $result = pg_execute($conn, "get_lettore_by_email", array($email));
        if (!$result)
            throw new ErroreInternoDatabaseException();

        $lettore = pg_fetch_array($result);
        if (!$lettore)
            $lettore = [];

        if (!pg_free_result($result))
            throw new ErroreInternoDatabaseException();

        if (!pg_close($conn))
            throw new ErroreInternoDatabaseException();

        return $lettore;
    }

    /**
     * Imposta l'email di un lettore.
     *
     * @param $codice_fiscale il codice fiscale del lettore
     * @param $nuova_email la nuova email del lettore
     * @throws ErroreInternoDatabaseException se si verifica un errore interno al database
     * @throws LettoreGiàRegistratoException se l'email è già associata ad un altro lettore
     */
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

        [$error] = pg_fetch_array($result);

        if (!pg_free_result($result))
            throw new ErroreInternoDatabaseException();

        if (!pg_close($conn))
            throw new ErroreInternoDatabaseException();

        $error = DatabaseException::fromEnumString($error);
        if (!is_null($error))
            throw $error;

        $_SESSION["user_email"] = $nuova_email->getEmail();
    }

    /**
     * Imposta la password di un lettore se la vecchia password è corretta.
     *
     * @param $codice_fiscale il codice fiscale del lettore
     * @param $vecchia_password la vecchia password del lettore
     * @param $nuova_password la nuova password del lettore
     * @throws ErroreInternoDatabaseException se si verifica un errore interno al database
     * @throws PasswordErrataException se la vecchia password è errata
     */
    function setLettorePassword(CodiceFiscale $codice_fiscale, string $vecchia_password, string $nuova_password): void {
        $password = getLettoreByCodiceFiscale($codice_fiscale)["hash"];
        if (!password_verify($vecchia_password, $password))
            throw new PasswordErrataException();

        $conn = pg_connect(CONNECTION_STRING);
        if (!$conn)
            throw new ErroreInternoDatabaseException();

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

    /**
     * Imposta la categoria di un lettore.
     *
     * @param $codice_fiscale il codice fiscale del lettore
     * @param $categoria la nuova categoria del lettore
     * @throws ErroreInternoDatabaseException se si verifica un errore interno al database
     */
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

    /**
     * Azzera i ritardi di un lettore.
     *
     * @param $codice_fiscale il codice fiscale del lettore
     * @throws ErroreInternoDatabaseException se si verifica un errore interno al database
     */
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

    /**
     * Rimuove un lettore dal database.
     *
     * @param $codice_fiscale il codice fiscale del lettore
     * @throws ErroreInternoDatabaseException se si verifica un errore interno al database
     * @throws LettorePrestitiInCorsoException se il lettore ha dei prestiti in corso
     */
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

    /**
     * Richiede una copia in prestito.
     *
     * @param $id_copia l'ID della copia
     * @param $cf_lettore il codice fiscale del lettore
     * @throws ErroreInternoDatabaseException se si verifica un errore interno al database
     * @throws CopiaNonDisponibileException se la copia non è disponibile
     * @throws TroppeConsegneInRitardoException se il lettore ha troppe consegne in ritardo
     * @throws TroppiPrestitiInCorsoException se il lettore ha troppi prestiti in corso
     */
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

    /**
     * Restituisce tutti i prestiti presenti nel database.
     *
     * @return un array di array associativi contenenti i dati dei prestiti
     * @throws ErroreInternoDatabaseException se si verifica un errore interno al database
     */
    function getPrestiti(): array {
        $conn = pg_connect(CONNECTION_STRING);
        if (!$conn)
            throw new ErroreInternoDatabaseException();

        $query = "SELECT * FROM biblioteca.getPrestiti()";
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

    /**
     * Restituisce i dati di un prestito dato l'ID della copia.
     *
     * @param $id_copia l'ID della copia
     * @return un array contenente i dati del prestito
     * @throws ErroreInternoDatabaseException se si verifica un errore interno al database
     */
    function getPrestitoByCopia(string $id_copia): array {
        $conn = pg_connect(CONNECTION_STRING);
        if (!$conn)
            throw new ErroreInternoDatabaseException();

        $query = "SELECT * FROM biblioteca.getPrestitoByCopia($1)";
        if (!pg_prepare($conn, "get_prestito_by_copia", $query))
            throw new ErroreInternoDatabaseException();

        $result = pg_execute($conn, "get_prestito_by_copia", array($id_copia));
        if (!$result)
            throw new ErroreInternoDatabaseException();

        $prestito = pg_fetch_array($result);
        if (!$prestito)
            $prestito = [];

        if (!pg_free_result($result))
            throw new ErroreInternoDatabaseException();

        if (!pg_close($conn))
            throw new ErroreInternoDatabaseException();

        return $prestito;
    }

    /**
     * Restituisce una copia presa in prestito.
     *
     * @param $id_copia l'ID della copia
     * @throws ErroreInternoDatabaseException se si verifica un errore interno al database
     */
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

        if (!pg_free_result($result))
            throw new ErroreInternoDatabaseException();

        if (!pg_close($conn))
            throw new ErroreInternoDatabaseException();
    }

    /**
     * Proroga un prestito.
     *
     * @param $id_copia l'ID della copia
     * @param $giorni_di_proroga i giorni di proroga
     * @throws ErroreInternoDatabaseException se si verifica un errore interno al database
     * @throws PrestitoInRitardoException se il prestito è già in ritardo
     */
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

    /**
     * Aggiunge un bibliotecario al database.
     *
     * @param $email l'email del bibliotecario
     * @param $password la password del bibliotecario
     * @throws ErroreInternoDatabaseException se si verifica un errore interno al database
     * @throws BibliotecarioGiàRegistratoException se il bibliotecario è già registrato
     */
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

        [$error] = pg_fetch_array($result);

        if (!pg_free_result($result))
            throw new ErroreInternoDatabaseException();

        if (!pg_close($conn))
            throw new ErroreInternoDatabaseException();

        $error = DatabaseException::fromEnumString($error);
        if (!is_null($error))
            throw $error;
    }

    /**
     * Restituisce tutti i bibliotecari presenti nel database.
     *
     * @return un array di array associativi contenenti i dati dei bibliotecari
     * @throws ErroreInternoDatabaseException se si verifica un errore interno al database
     */
    function getBibliotecari(): array {
        $conn = pg_connect(CONNECTION_STRING);
        if (!$conn)
            throw new ErroreInternoDatabaseException();

        $query = "SELECT * FROM biblioteca.getBibliotecari()";
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

    /**
     * Restituisce i dati di un bibliotecario dato il suo ID.
     *
     * @param $id_bibliotecario l'ID del bibliotecario
     * @return un array contenente i dati del bibliotecario
     * @throws ErroreInternoDatabaseException se si verifica un errore interno al database
     */
    function getBibliotecarioById(string $id_bibliotecario): array {
        $conn = pg_connect(CONNECTION_STRING);
        if (!$conn)
            throw new ErroreInternoDatabaseException();

        $query = "SELECT * FROM biblioteca.getBibliotecarioById($1)";
        if (!pg_prepare($conn, "get_bibliotecario_by_id", $query))
            throw new ErroreInternoDatabaseException();

        $result = pg_execute($conn, "get_bibliotecario_by_id", array($id_bibliotecario));
        if (!$result)
            throw new ErroreInternoDatabaseException();

        $bibliotecario = pg_fetch_array($result);
        if (!$bibliotecario)
            $bibliotecario = [];

        if (!pg_free_result($result))
            throw new ErroreInternoDatabaseException();

        if (!pg_close($conn))
            throw new ErroreInternoDatabaseException();

        return $bibliotecario;
    }

    /**
     * Restituisce i dati di un bibliotecario data l'email.
     *
     * @param $email l'email del bibliotecario
     * @return un array contenente i dati del bibliotecario
     * @throws ErroreInternoDatabaseException se si verifica un errore interno al database
     */
    function getBibliotecarioByEmail(Email $email): array {
        $conn = pg_connect(CONNECTION_STRING);
        if (!$conn)
            throw new ErroreInternoDatabaseException();

        $query = "SELECT * FROM biblioteca.getBibliotecarioByEmail($1)";
        if (!pg_prepare($conn, "get_bibliotecario_by_email", $query))
            throw new ErroreInternoDatabaseException();

        $result = pg_execute($conn, "get_bibliotecario_by_email", array($email));
        if (!$result)
            throw new ErroreInternoDatabaseException();

        $bibliotecario = pg_fetch_array($result);
        if (!$bibliotecario)
            $bibliotecario = [];

        if (!pg_free_result($result))
            throw new ErroreInternoDatabaseException();

        if (!pg_close($conn))
            throw new ErroreInternoDatabaseException();

        return $bibliotecario;
    }

    /**
     * Imposta l'email di un bibliotecario.
     *
     * @param $id_bibliotecario l'ID del bibliotecario
     * @param $nuova_email la nuova email del bibliotecario
     * @throws ErroreInternoDatabaseException se si verifica un errore interno al database
     * @throws BibliotecarioGiàRegistratoException se l'email è già associata ad un altro bibliotecario
     */
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

        $_SESSION['user_email'] = $nuova_email->getEmail();
    }

    /**
     * Imposta la password di un bibliotecario se la vecchia password è corretta.
     *
     * @param $id_bibliotecario l'ID del bibliotecario
     * @param $vecchia_password la vecchia password del bibliotecario
     * @param $nuova_password la nuova password del bibliotecario
     * @throws ErroreInternoDatabaseException se si verifica un errore interno al database
     * @throws PasswordErrataException se la vecchia password è errata
     */
    function setBibliotecarioPassword(string $id_bibliotecario, string $vecchia_password, string $nuova_password): void {
        $hash = getBibliotecarioById($id_bibliotecario)["hash"];
        if (!password_verify($vecchia_password, $hash))
            throw new PasswordErrataException();

        $conn = pg_connect(CONNECTION_STRING);
        if (!$conn)
            throw new ErroreInternoDatabaseException();

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

    /**
     * Rimuove un bibliotecario dal database.
     *
     * @param $id_bibliotecario l'ID del bibliotecario
     * @throws ErroreInternoDatabaseException se si verifica un errore interno al database
     */
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

    /**
     * Esegue il login di un utente: se la password è corretta vengono creati
     * due campi di sessione: 'user_id' e 'user_email'.
     *
     * @param $utente il tipo di utente (lettore o bibliotecario)
     * @param $email l'email dell'utente
     * @param $password la password dell'utente
     * @throws ErroreInternoDatabaseException se si verifica un errore interno al database
     * @throws PasswordErrataException se la password è errata
     * @throws UtenteInesistenteException se l'email non è registrata
     */
    function login(Utente $utente, Email $email, string $password): void {
        $conn = pg_connect(CONNECTION_STRING);
        if (!$conn)
            throw new ErroreInternoDatabaseException();

        if ($utente == Utente::BIBLIOTECARIO) {
            $bibliotecario = getBibliotecarioByEmail($email);
            if (empty($bibliotecario))
                throw new UtenteInesistenteException();
            $hash = $bibliotecario['hash'];
            $id = $bibliotecario['id'];
        } else if ($utente == Utente::LETTORE) {
            $lettore = getLettoreByEmail($email);
            if (empty($lettore))
                throw new UtenteInesistenteException();
            $hash = $lettore['hash'];
            $id = $lettore['codice_fiscale'];
        }

        if (!password_verify($password, $hash))
            throw new PasswordErrataException();

        $_SESSION['user_id'] = $id;
        $_SESSION['user_email'] = $email->getEmail();
    }

    /**
     * Controlla se un utente è loggato da lettore o da bibliotecario.
     *
     * @param $utente il tipo di utente (lettore o bibliotecario)
     * @return true se l'utente è loggato, false altrimenti
     * @throws ErroreInternoDatabaseException se si verifica un errore interno al database
     */
    function isLoggedIn(Utente $utente): bool {
        if (!isset($_SESSION['user_id']))
            return false;

        $id = $_SESSION['user_id'];
        if ($utente == Utente::BIBLIOTECARIO)
            $user = getBibliotecarioById($id);
        else if ($utente == Utente::LETTORE) {
            try {
                $codice_fiscale = new CodiceFiscale($id);
                $user = getLettoreByCodiceFiscale($codice_fiscale);
            } catch (InvalidCodiceFiscaleException $e) {
                return false;
            }
        }

        // controlla se l'utente è valido
        if (!empty($user))
            return true;

        return false;
    }

    /**
     * Esegue il logout dell'utente eliminando i campi di sessione 'user_id' e
     * 'user_email'.
     */
    function logout(): void {
        session_unset();
        session_destroy();
    }

?>
