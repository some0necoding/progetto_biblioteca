-- Errori --
CREATE TYPE biblioteca.error AS ENUM (
    'NESSUN_ERRORE',
    'LIBRI_ASSOCIATI_AD_AUTORE',
    'ISBN_GIÀ_ESISTENTE',
    'COPIE_ASSOCIATE_A_LIBRO',
    'COPIE_ASSOCIATE_A_SEDE',
    'COPIA_IN_PRESTITO',
    'LETTORE_GIÀ_REGISTRATO'
    'LETTORE_PRESTITI_IN_CORSO',
    'TROPPE_CONSEGNE_IN_RITARDO',
    'TROPPI_PRESTITI_IN_CORSO',
    'COPIA_NON_DISPONIBILE',
    'PRESTITO_IN_RITARDO',
    'BIBLIOTECARIO_GIÀ_REGISTRATO'
);

-- VIEWS --

CREATE VIEW biblioteca.ritardi AS (
    SELECT sede.id AS sede, lettore.codice_fiscale AS lettore, copia.id AS copia
    FROM biblioteca.prestito
    JOIN biblioteca.copia   ON prestito.copia = copia.id
    JOIN biblioteca.sede    ON copia.sede = sede.id
    JOIN biblioteca.lettore ON prestito.lettore = lettore.codice_fiscale
    WHERE prestito.scadenza < current_date
);

-- AUTORE --

-- Aggiunge un autore al database e ne restituisce l'id.
--
-- @param nome il nome dell'autore
-- @param cognome il cognome dell'autore
-- @param biografia la biografia dell'autore
-- @param data_di_nascita la data di nascita dell'autore
-- @param data_di_morte la data di morte dell'autore (opzionale)
CREATE OR REPLACE FUNCTION biblioteca.aggiungiAutore(nome biblioteca.autore.nome%TYPE,
                                                     cognome biblioteca.autore.cognome%TYPE,
                                                     biografia biblioteca.autore.biografia%TYPE,
                                                     data_di_nascita biblioteca.autore.data_di_nascita%TYPE,
                                                     data_di_morte biblioteca.autore.data_di_morte%TYPE DEFAULT NULL)
RETURNS void
AS $$
BEGIN
    INSERT INTO biblioteca.autore (nome, cognome, data_di_nascita, data_di_morte, biografia)
    VALUES (
        aggiungiAutore.nome,
        aggiungiAutore.cognome,
        aggiungiAutore.data_di_nascita,
        aggiungiAutore.data_di_morte,
        aggiungiAutore.biografia
    );
END;
$$
LANGUAGE plpgsql;

-- Restituisce tutti gli autori presenti nel database.
--
-- @return tabella di autori
CREATE OR REPLACE FUNCTION biblioteca.getAutori()
RETURNS SETOF biblioteca.autore
AS $$
BEGIN
    RETURN QUERY SELECT *
                 FROM biblioteca.autore;
    RETURN;
END;
$$
LANGUAGE plpgsql;

-- Restituisce l'autore con l'id specificato.
--
-- @param id l'id dell'autore
-- @return l'autore con l'id specificato
CREATE OR REPLACE FUNCTION biblioteca.getAutoreById(id biblioteca.autore.id%TYPE)
RETURNS SETOF biblioteca.autore
AS $$
BEGIN
    RETURN QUERY SELECT *
                 FROM biblioteca.autore
                 WHERE autore.id = getAutoreById.id;

    RETURN;
END;
$$
LANGUAGE plpgsql;

-- Aggiunge la data di morte a un autore
--
-- @param id l'id dell'autore
-- @param data_di_morte la data di morte dell'autore
CREATE OR REPLACE FUNCTION biblioteca.setAutoreDataDiMorte(id biblioteca.autore.id%TYPE,
                                                           data_di_morte biblioteca.autore.data_di_morte%TYPE)
RETURNS void
AS $$
BEGIN
    UPDATE biblioteca.autore
    SET data_di_morte = setAutoreDataDiMorte.data_di_morte
    WHERE autore.id = setAutoreDataDiMorte.id;
END;
$$
LANGUAGE plpgsql;

-- Rimuove un autore dal database. Se l'autore ha libri associati, l'operazione
-- fallisce.
--
-- @param id l'id dell'autore
-- @return 'LIBRI_ASSOCIATI_AD_AUTORE' se l'autore ha libri associati,
--         'NESSUN_ERRORE' altrimenti
CREATE OR REPLACE FUNCTION biblioteca.rimuoviAutore(id biblioteca.autore.id%TYPE)
RETURNS biblioteca.error
AS $$
BEGIN
    IF count(*) > 0
        FROM biblioteca.scritto
        JOIN biblioteca.libro ON scritto.libro = libro.isbn
        WHERE scritto.autore = rimuoviAutore.id
    THEN
        RETURN 'LIBRI_ASSOCIATI_AD_AUTORE';
    END IF;

    DELETE FROM biblioteca.autore
    WHERE autore.id = rimuoviAutore.id;

    RETURN 'NESSUN_ERRORE';
END;
$$
LANGUAGE plpgsql;

-- LIBRO --

-- Aggiunge un libro al database vincolandolo ai propri autori. Se esiste già un
-- libro con lo stesso isbn, l'operazione fallisce.
--
-- @param isbn l'isbn del libro
-- @param titolo il titolo del libro
-- @param trama la trama del libro
-- @param casa_editrice la casa editrice del libro
-- @param autori gli id degli autori del libro
-- @return 'ISBN_GIÀ_ESISTENTE' se esiste già un libro con lo stesso isbn,
--         'NESSUN_ERRORE' altrimenti
CREATE OR REPLACE FUNCTION biblioteca.aggiungiLibro(isbn biblioteca.libro.isbn%TYPE,
                                                    titolo biblioteca.libro.titolo%TYPE,
                                                    trama biblioteca.libro.trama%TYPE,
                                                    casa_editrice biblioteca.libro.casa_editrice%TYPE,
                                                    autori NUMERIC[]) -- dovrebbe essere biblioteca.autore.id%TYPE ma
                                                                      -- per qualche motivo a postgres non piace
RETURNS biblioteca.error
AS $$
DECLARE
    libro biblioteca.libro.isbn%TYPE;
    autore biblioteca.autore.id%TYPE;
BEGIN
    SELECT libro.isbn
    FROM biblioteca.libro
    WHERE libro.isbn = aggiungiLibro.isbn;

    IF FOUND THEN
        RETURN 'ISBN_GIÀ_ESISTENTE';
    ELSE
        INSERT INTO biblioteca.libro (isbn, titolo, trama, casa_editrice)
        VALUES (
            aggiungiLibro.isbn,
            aggiungiLibro.titolo,
            aggiungiLibro.trama,
            aggiungiLibro.casa_editrice
        ) RETURNING libro.isbn INTO libro;

        FOREACH autore IN ARRAY aggiungiLibro.autori
        LOOP
            INSERT INTO biblioteca.scritto
            VALUES (
                autore,
                libro
            );
        END LOOP;
    END IF;

    RETURN 'NESSUN_ERRORE';
END;
$$
LANGUAGE plpgsql;

-- Restituisce tutti i libri presenti nel database.
--
-- @return tabella di libri
CREATE OR REPLACE FUNCTION biblioteca.getLibri()
RETURNS SETOF biblioteca.libro
AS $$
BEGIN
    RETURN QUERY SELECT *
                 FROM biblioteca.libro;
    RETURN;
END;
$$
LANGUAGE plpgsql;

-- Restituisce il libro con l'isbn specificato.
--
-- @param isbn l'isbn del libro
-- @return il libro con l'isbn specificato
CREATE OR REPLACE FUNCTION biblioteca.getLibroByIsbn(isbn biblioteca.libro.isbn%TYPE)
RETURNS SETOF biblioteca.libro
AS $$
BEGIN
    RETURN QUERY SELECT *
                 FROM biblioteca.libro
                 WHERE libro.isbn = getLibroByIsbn.isbn;

    RETURN;
END;
$$
LANGUAGE plpgsql;

-- Restituisce tutti i libri associati ad una sede, i.e. che hanno almeno una
-- copia di tali libri associata.
--
-- @param sede l'id della sede
-- @return tabella di libri
CREATE OR REPLACE FUNCTION biblioteca.getLibriBySede(sede biblioteca.sede.id%TYPE)
RETURNS SETOF biblioteca.libro
AS $$
BEGIN
    RETURN QUERY SELECT DISTINCT libro.isbn, libro.titolo, libro.trama, libro.casa_editrice
                 FROM biblioteca.copia
                 JOIN biblioteca.libro ON copia.libro = libro.isbn
                 WHERE copia.sede = getCopieBySede.sede;
    RETURN;
END;
$$
LANGUAGE plpgsql;

-- Rimuove un libro dal database. Se il libro ha copie associate, l'operazione fallisce.
--
-- @param libro l'isbn del libro
-- @return 'COPIE_ASSOCIATE_A_LIBRO' se il libro ha copie associate,
--         'NESSUN_ERRORE' altrimenti
CREATE OR REPLACE FUNCTION biblioteca.rimuoviLibro(libro biblioteca.libro.isbn%TYPE)
RETURNS biblioteca.error
AS $$
BEGIN
    IF count(*) > 0
        FROM biblioteca.copia
        WHERE copia.libro = rimuoviLibro.libro
    THEN
        RETURN 'COPIE_ASSOCIATE_A_LIBRO';
    END IF;

    DELETE FROM biblioteca.libro
    WHERE libro.isbn = rimuoviLibro.libro;

    RETURN 'NESSUN_ERRORE';
END;
$$
LANGUAGE plpgsql;

-- SEDE --

-- Aggiunge una sede al database.
--
-- @param indirizzo l'indirizzo della sede
-- @param città la città della sede
CREATE OR REPLACE FUNCTION biblioteca.aggiungiSede(indirizzo biblioteca.sede.indirizzo%TYPE,
                                                   città biblioteca.sede.città%TYPE)
RETURNS void
AS $$
BEGIN
    INSERT INTO biblioteca.sede (indirizzo, città)
    VALUES (
        aggiungiSede.indirizzo,
        aggiungiSede.città
    );
END;
$$
LANGUAGE plpgsql;

-- Restituisce tutte le sedi presenti nel database.
--
-- @return tabella di sedi
CREATE OR REPLACE FUNCTION biblioteca.getSedi()
RETURNS SETOF biblioteca.sede
AS $$
BEGIN
    RETURN QUERY SELECT *
                 FROM biblioteca.sede;
    RETURN;
END;
$$
LANGUAGE plpgsql;

-- Restituisce la sede con l'id specificato.
--
-- @param id l'id della sede
-- @return la sede con l'id specificato
CREATE OR REPLACE FUNCTION biblioteca.getSedeById(id biblioteca.sede.id%TYPE)
RETURNS SETOF biblioteca.sede
AS $$
BEGIN
    RETURN QUERY SELECT *
                 FROM biblioteca.sede
                 WHERE sede.id = getSedeById.id;

    RETURN;
END;
$$
LANGUAGE plpgsql;

-- Restituisce i ritardi in corso per una data sede.
--
-- @param sede l'id della sede
-- @return tabella di copie in ritardo con i relativi lettori
CREATE OR REPLACE FUNCTION biblioteca.getRitardi(sede biblioteca.sede.id%TYPE)
RETURNS TABLE (lettore biblioteca.lettore.codice_fiscale%TYPE, copia biblioteca.copia.id%TYPE)
AS $$
DECLARE
BEGIN
    RETURN QUERY SELECT ritardi.lettore, ritardi.copia
                 FROM biblioteca.ritardi
                 WHERE ritardi.sede = getRitardi.sede;
    RETURN;
END;
$$
LANGUAGE plpgsql;

-- Rimuove una sede dal database. Se la sede ha copie associate, l'operazione fallisce.
--
-- @param sede l'id della sede
-- @return 'COPIE_ASSOCIATE_A_SEDE' se la sede ha copie associate,
--         'NESSUN_ERRORE' altrimenti
CREATE OR REPLACE FUNCTION biblioteca.rimuoviSede(sede biblioteca.sede.id%TYPE)
RETURNS biblioteca.error
AS $$
BEGIN
    IF count(*) > 0
        FROM biblioteca.copia
        WHERE copia.sede = rimuoviSede.sede
    THEN
        RETURN 'COPIE_ASSOCIATE_A_SEDE';
    END IF;

    DELETE FROM biblioteca.sede
    WHERE sede.id = rimuoviSede.sede;

    RETURN 'NESSUN_ERRORE';
END
$$
LANGUAGE plpgsql;

-- COPIA --

-- Aggiunge una copia al database.
--
-- @param libro l'isbn del libro
-- @param sede l'id della sede
CREATE OR REPLACE FUNCTION biblioteca.aggiungiCopia(libro biblioteca.libro.isbn%TYPE,
                                                    sede biblioteca.sede.id%TYPE)
RETURNS void
AS $$
BEGIN
    INSERT INTO biblioteca.copia (libro, sede)
    VALUES (
        aggiungiCopia.libro,
        aggiungiCopia.sede
    );
END;
$$
LANGUAGE plpgsql;

-- Restituisce tutte le copie presenti nel database.
--
-- @return tabella di copie
CREATE OR REPLACE FUNCTION biblioteca.getCopie()
RETURNS SETOF biblioteca.copia
AS $$
BEGIN
    RETURN QUERY SELECT *
                 FROM biblioteca.copia;
    RETURN;
END;
$$
LANGUAGE plpgsql;

-- Restituisce la copia con l'id specificato.
--
-- @param id l'id della copia
-- @return la copia con l'id specificato
CREATE OR REPLACE FUNCTION biblioteca.getCopiaById(id biblioteca.copia.id%TYPE)
RETURNS SETOF biblioteca.copia
AS $$
BEGIN
    RETURN QUERY SELECT *
                 FROM biblioteca.copia
                 WHERE copia.id = getCopiaById.id;

    RETURN;
END;
$$
LANGUAGE plpgsql;

-- Restituisce tutte le copie disponibili di un tale libro. Se sede != NULL la
-- ricerca viene ristretta alla sede specificata.
--
-- @param libro l'id del libro di cui si sta cercando una copia disponibile
-- @param sede l'id della sede a cui si restringe la ricerca (se != NULL)
-- @return l'id della copia disponibile se esiste, NULL altrimenti
CREATE OR REPLACE FUNCTION biblioteca.getCopiaDisponibile(libro biblioteca.libro.isbn%TYPE,
                                                          sede biblioteca.sede.id%TYPE)
RETURNS biblioteca.copia.id%TYPE
AS $$
DECLARE
    copiaTrovata biblioteca.copia.id%TYPE;
BEGIN
    IF SEDE IS NOT NULL THEN
        SELECT copia.id INTO copiaTrovata
        FROM biblioteca.copia
        WHERE copia.sede = getCopiaDisponibile.sede AND
              copia.libro = getCopiaDisponibile.isbn AND
              copia.isDisponibile;
    ELSE
        SELECT copia.id INTO copiaTrovata
        FROM biblioteca.copia
        WHERE copia.libro = getCopiaDisponibile.isbn AND;
              copia.isDisponibile;
    END IF;

    IF NOT FOUND THEN
        RETURN NULL;
    END IF;

    RETURN copiaTrovata;
END;
$$
LANGUAGE plpgsql;

-- Assegna una copia a una nuova sede. Se la copia è in prestito, l'operazione fallisce.
--
-- @param copia l'id della copia
-- @param nuovaSede l'id della nuova sede
-- @return 'COPIA_IN_PRESTITO' se la copia è in prestito,
--         'NESSUN_ERRORE' altrimenti
CREATE OR REPLACE FUNCTION biblioteca.setSede(copia biblioteca.copia.id%TYPE,
                                              nuovaSede biblioteca.sede.id%TYPE)
RETURNS biblioteca.error
AS $$
BEGIN
    IF count(*) > 0
        FROM biblioteca.prestito
        WHERE prestito.copia = setSede.copia
    THEN
        RETURN 'COPIA_IN_PRESTITO';
    END IF;

    UPDATE biblioteca.copia
    SET sede = setSede.nuovaSede
    WHERE copia.id = setSede.copia;

    RETURN 'NESSUN_ERRORE';
END;
$$
LANGUAGE plpgsql;

-- Rimuove una copia dal database. Se la copia è in prestito, l'operazione fallisce.
--
-- @param copia l'id della copia
-- @return 'COPIA_IN_PRESTITO' se la copia è in prestito,
--         'NESSUN_ERRORE' altrimenti
CREATE OR REPLACE FUNCTION biblioteca.rimuoviCopia(copia biblioteca.copia.id%TYPE)
RETURNS biblioteca.error
AS $$
BEGIN
    IF count(*) > 0
        FROM biblioteca.prestito
        WHERE prestito.copia = rimuoviCopia.copia
    THEN
        RETURN 'COPIA_IN_PRESTITO';
    END IF;

    DELETE FROM biblioteca.copia
    WHERE copia.id = rimuoviCopia.copia;

    RETURN 'NESSUN_ERRORE';
END;
$$
LANGUAGE plpgsql;

-- Trigger Function. Incrementa il numero di copie e (se necessario) di libri
-- gestiti dalla sede.
-- 
-- @return NULL
CREATE OR REPLACE FUNCTION biblioteca.aggiornaSede_AI()
RETURNS trigger
AS $$
BEGIN
    UPDATE biblioteca.sede
    SET copie_gestite = copie_gestite + 1
    WHERE sede.id = NEW.sede;

    IF count(*) = 1 -- la copia inserita è l'unica per quel libro
        FROM biblioteca.copia
        WHERE copia.sede = NEW.sede AND
              copia.libro = NEW.libro
    THEN
        UPDATE biblioteca.sede
        SET isbn_gestiti = isbn_gestiti + 1
        WHERE sede.id = NEW.sede;
    END IF;

    RETURN NULL;
END;
$$
LANGUAGE plpgsql;

-- Trigger. Aggiorna il numero di copie e (se necessario) di libri gestiti dalla sede.
CREATE OR REPLACE TRIGGER aggiornaSede_AI
AFTER INSERT ON biblioteca.copia
FOR EACH ROW
EXECUTE FUNCTION biblioteca.aggiornaSede_AI();

-- Trigger Function. Decrementa il numero di copie e (se necessario) di libri
-- gestiti dalla sede.
--
-- @return NULL
CREATE OR REPLACE FUNCTION biblioteca.aggiornaSede_AD()
RETURNS trigger
AS $$
BEGIN
    UPDATE biblioteca.sede
    SET copie_gestite = copie_gestite - 1
    WHERE sede.id = OLD.sede;

    IF count(*) = 0 -- la copia rimossa era l'unica per quel libro
        FROM biblioteca.copia
        WHERE copia.sede = OLD.sede AND
              copia.libro = OLD.libro
    THEN
        UPDATE biblioteca.sede
        SET isbn_gestiti = isbn_gestiti - 1
        WHERE sede.id = OLD.sede;
    END IF;

    RETURN NULL;
END;
$$
LANGUAGE plpgsql;

-- Trigger. Aggiorna il numero di copie e (se necessario) di libri gestiti dalla sede.
CREATE OR REPLACE TRIGGER aggiornaSede_AD
AFTER DELETE ON biblioteca.copia
FOR EACH ROW
EXECUTE FUNCTION biblioteca.aggiornaSede_AD();

-- Trigger Function. Decrementa e incrementa il numero di copie e (se necessario) di libri
-- gestiti dalla sede.
--
-- @return NULL
CREATE OR REPLACE FUNCTION biblioteca.aggiornaSede_AU()
RETURNS trigger
AS $$
BEGIN
    -- se la copia è stata spostata in un'altra sede
    IF OLD.sede <> NEW.sede AND OLD.libro = NEW.libro THEN
        UPDATE biblioteca.sede
        SET copie_gestite = copie_gestite - 1
        WHERE sede.id = OLD.sede;

        IF count(*) = 0 -- la copia rimossa era l'unica per quel libro
            FROM biblioteca.copia
            WHERE copia.sede = OLD.sede AND
                  copia.libro = OLD.libro
        THEN
            UPDATE biblioteca.sede
            SET isbn_gestiti = isbn_gestiti - 1
            WHERE sede.id = OLD.sede;
        END IF;

        UPDATE biblioteca.sede
        SET copie_gestite = copie_gestite + 1
        WHERE sede.id = NEW.sede;

        IF count(*) = 1 -- la copia inserita è l'unica per quel libro
            FROM biblioteca.copia
            WHERE copia.sede = NEW.sede AND
                  copia.libro = NEW.libro
        THEN
            UPDATE biblioteca.sede
            SET isbn_gestiti = isbn_gestiti + 1
            WHERE sede.id = NEW.sede;
        END IF;
    END IF;

    RETURN NULL;
END;
$$
LANGUAGE plpgsql;

-- Trigger. Aggiorna il numero di copie e (se necessario) di libri gestiti dalla sede.
CREATE OR REPLACE TRIGGER aggiornaSede_AU
AFTER UPDATE ON biblioteca.copia
FOR EACH ROW
EXECUTE FUNCTION biblioteca.aggiornaSede_AU();

-- LETTORE --

-- Aggiunge un lettore al database e ne restituisce l'id. Se esiste già un lettore
-- con lo stesso codice fiscale o la stessa email, l'operazione fallisce.
--
-- @param codice_fiscale il codice fiscale del lettore
-- @param nome il nome del lettore
-- @param cognome il cognome del lettore
-- @param email l'email del lettore
-- @param hash l'hash della password del lettore
-- @param categoria la categoria del lettore
-- @return 'LETTORE_GIÀ_REGISTRATO' se il lettore è già registrato,
--         'NESSUN_ERRORE' altrimenti
CREATE OR REPLACE FUNCTION biblioteca.aggiungiLettore(codice_fiscale biblioteca.lettore.codice_fiscale%TYPE,
                                                      nome biblioteca.lettore.nome%TYPE,
                                                      cognome biblioteca.lettore.cognome%TYPE,
                                                      email biblioteca.lettore.email%TYPE,
                                                      hash biblioteca.lettore.hash%TYPE,
                                                      categoria biblioteca.lettore.categoria%TYPE)
RETURNS biblioteca.error
AS $$
DECLARE
    codice_fiscale biblioteca.lettore.codice_fiscale%TYPE;
BEGIN
    SELECT lettore.codice_fiscale INTO codice_fiscale
    FROM biblioteca.lettore
    WHERE lettore.codice_fiscale = aggiungiLettore.codice_fiscale OR
          lettore.email = aggiungiLettore.email;

    IF FOUND THEN
        RETURN 'LETTORE_GIÀ_REGISTRATO';
    END IF;

    INSERT INTO biblioteca.lettore
    VALUES (
        aggiungiLettore.codice_fiscale,
        aggiungiLettore.nome,
        aggiungiLettore.cognome,
        aggiungiLettore.email,
        aggiungiLettore.hash,
        aggiungiLettore.categoria
    );

    RETURN 'NESSUN_ERRORE';
END;
$$
LANGUAGE plpgsql;

-- Restituisce tutti i lettori presenti nel database.
--
-- @return tabella di lettori
CREATE OR REPLACE FUNCTION biblioteca.getLettori()
RETURNS SETOF biblioteca.lettore
AS $$
BEGIN
    RETURN QUERY SELECT *
                 FROM biblioteca.lettore;
    RETURN;
END;
$$
LANGUAGE plpgsql;

-- Restituisce il lettore con il codice fiscale specificato.
--
-- @param codice_fiscale il codice fiscale del lettore
-- @return il lettore con il codice fiscale specificato
CREATE OR REPLACE FUNCTION biblioteca.getLettoreByCodiceFiscale(codice_fiscale biblioteca.lettore.codice_fiscale%TYPE)
RETURNS SETOF biblioteca.lettore
AS $$
BEGIN
    RETURN QUERY SELECT *
         FROM biblioteca.lettore
         WHERE lettore.codice_fiscale = getLettoreByCodiceFiscale.codice_fiscale;

    RETURN;
END;
$$
LANGUAGE plpgsql;

-- Restituisce il lettore con l'email specificata.
--
-- @param email l'email del lettore
-- @return il lettore con l'email specificata
CREATE OR REPLACE FUNCTION biblioteca.getLettoreByEmail(email biblioteca.lettore.email%TYPE)
RETURNS SETOF biblioteca.lettore
AS $$
BEGIN
    RETURN QUERY SELECT *
                 FROM biblioteca.lettore
                 WHERE lettore.email = getLettoreByEmail.email;

    RETURN;
END;
$$
LANGUAGE plpgsql;

-- Cambia l'email di un lettore.
--
-- @param codice_fiscale il codice fiscale del lettore
-- @param email la nuova email del lettore
-- @return 'LETTORE_GIÀ_REGISTRATO' se esiste già un lettore registrato con la mail specificata,
--         'NESSUN_ERRORE' altrimenti
CREATE OR REPLACE FUNCTION biblioteca.setLettoreEmail(codice_fiscale biblioteca.lettore.codice_fiscale%TYPE,
                                                      email biblioteca.lettore.email%TYPE)
RETURNS biblioteca.error
AS $$
DECLARE
    codice_fiscale biblioteca.lettore.codice_fiscale%TYPE;
BEGIN
    SELECT lettore.codice_fiscale INTO codice_fiscale
    FROM biblioteca.lettore
    WHERE lettore.email = setLettoreEmail.email;

    IF FOUND THEN
        RETURN 'LETTORE_GIÀ_REGISTRATO';
    END IF;

    UPDATE biblioteca.lettore
    SET email = setLettoreEmail.email
    WHERE lettore.codice_fiscale = setLettoreEmail.codice_fiscale;

    RETURN 'NESSUN_ERRORE';
END;
$$
LANGUAGE plpgsql;

-- Cambia la password di un lettore.
--
-- @param codice_fiscale il codice fiscale del lettore
-- @param newHash l'hash della nuova password del lettore
CREATE OR REPLACE FUNCTION biblioteca.setLettorePassword(codice_fiscale biblioteca.lettore.codice_fiscale%TYPE,
                                                         newHash biblioteca.lettore.hash%TYPE)
RETURNS void
AS $$
BEGIN
    UPDATE biblioteca.lettore
    SET hash = setLettorePassword.newHash
    WHERE lettore.codice_fiscale = setLettorePassword.codice_fiscale;
END;
$$
LANGUAGE plpgsql;

-- Cambia la categoria di un lettore.
--
-- @param codice_fiscale il codice fiscale del lettore
-- @param categoria la nuova categoria del lettore
CREATE OR REPLACE FUNCTION biblioteca.setLettoreCategoria(codice_fiscale biblioteca.lettore.codice_fiscale%TYPE,
                                                          categoria biblioteca.lettore.categoria%TYPE)
RETURNS void
AS $$
BEGIN
    UPDATE biblioteca.lettore
    SET categoria = setLettoreCategoria.categoria
    WHERE lettore.codice_fiscale = setLettoreCategoria.codice_fiscale;
END;
$$
LANGUAGE plpgsql;

-- Azzera il numero di ritardi di un lettore.
--
-- @param codice_fiscale il codice fiscale del lettore
CREATE OR REPLACE FUNCTION biblioteca.azzeraRitardi(codice_fiscale biblioteca.lettore.codice_fiscale%TYPE)
RETURNS void
AS $$
BEGIN
    UPDATE biblioteca.lettore
    SET ritardi = 0
    WHERE lettore.codice_fiscale = azzeraRitardi.codice_fiscale;
END;
$$
LANGUAGE plpgsql;

-- Rimuove un lettore dal database. Se il lettore ha prestiti in corso, l'operazione fallisce.
--
-- @param codice_fiscale il codice fiscale del lettore
-- @return 'LETTORE_PRESTITI_IN_CORSO' se il lettore ha prestiti in corso,
--         'NESSUN_ERRORE' altrimenti
CREATE OR REPLACE FUNCTION biblioteca.rimuoviLettore(codice_fiscale biblioteca.lettore.codice_fiscale%TYPE)
RETURNS biblioteca.error
AS $$
BEGIN
    IF count(*) > 0
        FROM biblioteca.prestito
        WHERE prestito.lettore = rimuoviLettore.codice_fiscale
    THEN
        RETURN 'LETTORE_PRESTITI_IN_CORSO';
    END IF;

    DELETE FROM biblioteca.lettore
    WHERE lettore.codice_fiscale = rimuoviLettore.codice_fiscale;

    RETURN 'NESSUN_ERRORE';
END;
$$
LANGUAGE plpgsql;

-- PRESTITO --

-- Aggiunge un prestito di copia fatto a lettore nella data corrente. Il prestito
-- non viene consentito se lettore ha 5 o più consegne in ritardo, se lettore ha
-- già 3 o 5 (in base alla categoria) prestiti in corso, oppure se copia non è 
-- disponibile.
--
-- @param copia l'id della copia
-- @param lettore il codice fiscale del lettore
-- @return 'TROPPE_CONSEGNE_IN_RITARDO' se lettore ha 5 o più consegne in ritardo,
--         'TROPPI_PRESTITI_IN_CORSO' se lettore ha già 3 o 5 prestiti in corso,
--         'COPIA_NON_DISPONIBILE' se copia non è disponibile,
--         'NESSUN_ERRORE' altrimenti
CREATE OR REPLACE FUNCTION biblioteca.richiediPrestito(copia biblioteca.copia.id%TYPE,
                                                       lettore biblioteca.lettore.codice_fiscale%TYPE)
RETURNS biblioteca.error
AS $$
DECLARE
    categoria biblioteca.lettore.categoria%TYPE;
    max_prestiti INTEGER;
BEGIN
    SELECT lettore.categoria INTO categoria
    FROM biblioteca.lettore
    WHERE lettore.codice_fiscale = richiediPrestito.lettore;

    CASE categoria
        WHEN 'premium' THEN
            max_prestiti := 5;
        WHEN 'base' THEN
            max_prestiti := 3;
    END CASE;

    CASE
        WHEN lettore.ritardi >= 5
            FROM biblioteca.lettore
            WHERE lettore.codice_fiscale = richiediPrestito.codice_fiscale
        THEN
            RETURN 'TROPPE_CONSEGNE_IN_RITARDO';
        WHEN lettore.prestiti_in_corso >= max_prestiti
            FROM biblioteca.lettore
            WHERE lettore.codice_fiscale = richiediPrestito.codice_fiscale
        THEN
            RETURN 'TROPPI_PRESTITI_IN_CORSO';
        WHEN NOT copia.isDisponibile
            FROM biblioteca.copia
            WHERE copia.copia = richiediPrestito.copia
        THEN
            RETURN 'COPIA_NON_DISPONIBILE';
    END CASE;

    INSERT INTO biblioteca.prestito
    VALUES (
        copia,
        lettore
    );

    RETURN 'NESSUN_ERRORE';
END;
$$
LANGUAGE plpgsql;

-- Restituisce tutti i prestiti presenti nel database.
--
-- @return tabella di prestiti
CREATE OR REPLACE FUNCTION biblioteca.getPrestiti()
RETURNS SETOF biblioteca.prestito
AS $$
BEGIN
    RETURN QUERY SELECT *
                 FROM biblioteca.prestito;
    RETURN;
END;
$$
LANGUAGE plpgsql;

-- Restituisce il prestito della copia specificata.
--
-- @param copia l'id della copia
-- @return il prestito della copia specificata
CREATE OR REPLACE FUNCTION biblioteca.getPrestitoByCopia(copia biblioteca.copia.id%TYPE)
RETURNS SETOF biblioteca.prestito
AS $$
BEGIN
    RETURN QUERY SELECT *
                 FROM biblioteca.prestito
                 WHERE prestito.copia = getPrestitoByCopia.copia;

    RETURN;
END;
$$
LANGUAGE plpgsql;

-- Conclude un prestito rimuovendolo dal database e aggiungendo la data di restituzione
-- alla copia.
--
-- @param copia l'id della copia
CREATE OR REPLACE FUNCTION biblioteca.restituisciPrestito(copia biblioteca.copia.id%TYPE)
RETURNS void
AS $$
BEGIN
    DELETE FROM biblioteca.prestito
    WHERE prestito.copia = restituisciPrestito.copia;

    IF FOUND THEN
        INSERT INTO biblioteca.restituzione
        VALUES (
            restituisciPrestito.copia
        );
    END IF;
END;
$$
LANGUAGE plpgsql;

-- Proroga del numero di giorni specificato un prestito. Se il prestito è in ritardo,
-- l'operazione fallisce.
-- 
-- @return 'PRESTITO_IN_RITARDO' se il prestito è in ritardo,
--         'NESSUN_ERRORE' altrimenti
CREATE OR REPLACE FUNCTION biblioteca.prorogaPrestito(copia biblioteca.copia.id%TYPE,
                                                      giorniDiProroga INTEGER)
RETURNS biblioteca.error
AS $$
BEGIN
    IF count(*) > 0
        FROM biblioteca.ritardi
        WHERE ritardi.copia = prorogaPrestito.copia
    THEN
        RETURN 'PRESTITO_IN_RITARDO';
    END IF;

    UPDATE biblioteca.prestito
    SET scadenza = prestito.scadenza + giorniDiProroga
    WHERE prestito.copia = copia;

    RETURN 'NESSUN_ERRORE';
END;
$$
LANGUAGE plpgsql;

-- Trigger Function. Incrementa il numero di prestiti in corso per la sede e il lettore.
--
-- @return NULL
CREATE OR REPLACE FUNCTION biblioteca.aggiornaPrestiti_AI()
RETURNS trigger
AS $$
DECLARE
    sedePrestito biblioteca.copia.sede%TYPE;
BEGIN
    SELECT copia.sede INTO sedePrestito
    FROM biblioteca.copia
    WHERE copia.id = NEW.copia;

    UPDATE biblioteca.sede
    SET prestiti_in_corso = prestiti_in_corso + 1
    WHERE sede.id = sedePrestito;

    UPDATE biblioteca.lettore
    SET prestiti_in_corso = prestiti_in_corso + 1
    WHERE lettore.codice_fiscale = NEW.lettore;

    RETURN NULL;
END;
$$
LANGUAGE plpgsql;

-- Trigger. Incrementa il numero di prestiti in corso per la sede e il lettore.
CREATE OR REPLACE TRIGGER aggiornaPrestiti_AI
AFTER INSERT ON biblioteca.prestito
FOR EACH ROW
EXECUTE FUNCTION biblioteca.aggiornaPrestiti_AI();

-- Trigger Function. Decrementa il numero di prestiti in corso per la sede e il lettore.
--
-- @return NULL
CREATE OR REPLACE FUNCTION biblioteca.aggiornaPrestiti_AD()
RETURNS trigger
AS $$
DECLARE
    sedePrestito biblioteca.copia.sede%TYPE;
BEGIN
    SELECT copia.sede INTO sedePrestito
    FROM biblioteca.copia
    WHERE copia.id = OLD.copia;

    UPDATE biblioteca.sede
    SET prestiti_in_corso = prestiti_in_corso - 1
    WHERE sede.id = sedePrestito;

    UPDATE biblioteca.lettore
    SET prestiti_in_corso = prestiti_in_corso - 1
    WHERE lettore.codice_fiscale = OLD.lettore;

    RETURN NULL;
END;
$$
LANGUAGE plpgsql;

-- Trigger. Decrementa il numero di prestiti in corso per la sede e il lettore.
CREATE OR REPLACE TRIGGER aggiornaPrestiti_AD
AFTER DELETE ON biblioteca.prestito
FOR EACH ROW
EXECUTE FUNCTION biblioteca.aggiornaPrestiti_AD();

-- Trigger Function. Aggiorna (se necessario) il numero di ritardi per il lettore.
--
-- @return NULL
CREATE OR REPLACE FUNCTION biblioteca.aggiornaRitardi_AD()
RETURNS trigger
AS $$
BEGIN
    IF current_date > OLD.scadenza THEN
        UPDATE biblioteca.lettore
        SET ritardi = ritardi + 1
        WHERE lettore.codice_fiscale = OLD.lettore;
    END IF;

    RETURN NULL;
END;
$$
LANGUAGE plpgsql;

-- Trigger. Aggiorna (se necessario) il numero di ritardi per il lettore.
CREATE OR REPLACE TRIGGER aggiornaRitardi_AD
AFTER DELETE ON biblioteca.prestito
FOR EACH ROW
EXECUTE FUNCTION biblioteca.aggiornaRitardi_AD();

-- Trigger Function. Revoca la disponibilità della copia e (se necessario) del libro associato.
--
-- @return NULL
CREATE OR REPLACE FUNCTION biblioteca.aggiornaDisponibilità_AI()
RETURNS trigger
AS $$
DECLARE
    libroAssociato biblioteca.copia.libro%TYPE;
BEGIN
    SELECT copia.libro INTO libroAssociato
    FROM biblioteca.copia
    WHERE copia.id = NEW.copia;

    UPDATE biblioteca.copia
    SET isDisponibile = false
    WHERE copia.id = NEW.copia;

    IF count(*) = 1 -- la copia è l'unica disponibile per quel libro
        FROM biblioteca.copia
        WHERE copia.libro = libroAssociato AND
              copia.isDisponibile
    THEN
        UPDATE biblioteca.libro
        SET isDisponibile = false
        WHERE libro.isbn = libroAssociato;
    END IF;

    RETURN NULL;
END;
$$
LANGUAGE plpgsql;

-- Trigger. Revoca la disponibilità della copia e (se necessario) del libro associato.
CREATE OR REPLACE TRIGGER aggiornaDisponibilità_AI
AFTER INSERT ON biblioteca.prestito
FOR EACH ROW
EXECUTE FUNCTION biblioteca.aggiornaDisponibilità_AI();

-- Trigger Function. Ripristina la disponibilità della copia e (se necessario) del libro associato.
--
-- @return NULL
CREATE OR REPLACE FUNCTION biblioteca.aggiornaDisponibilità_AD()
RETURNS trigger
AS $$
DECLARE
    libroAssociato biblioteca.copia.libro%TYPE;
BEGIN
    SELECT copia.libro INTO libroAssociato
    FROM biblioteca.copia
    WHERE copia.id = OLD.copia;

    UPDATE biblioteca.copia
    SET isDisponibile = true
    WHERE copia.id = OLD.copia;

    IF count(*) = 0 -- non ci sono altre copie disponibili per quel libro
        FROM biblioteca.copia
        WHERE copia.libro = libroAssociato AND
              copia.isDisponibile
    THEN
        UPDATE biblioteca.libro
        SET isDisponibile = true
        WHERE libro.isbn = libroAssociato;
    END IF;

    RETURN NULL;
END;
$$
LANGUAGE plpgsql;

-- Trigger. Ripristina la disponibilità della copia e (se necessario) del libro associato.
CREATE OR REPLACE TRIGGER aggiornaDisponibilità_AD
AFTER DELETE ON biblioteca.prestito
FOR EACH ROW
EXECUTE FUNCTION biblioteca.aggiornaDisponibilità_AD();

-- BIBLIOTECARIO --

-- Aggiunge un bibliotecario al database. Se esiste già un bibliotecario con la stessa
-- email, l'operazione fallisce.
--
-- @param email l'email del bibliotecario
-- @param hash l'hash della password del bibliotecario
-- @return 'BIBLIOTECARIO_GIÀ_REGISTRATO' se il bibliotecario è già registrato,
--         'NESSUN_ERRORE' altrimenti
CREATE OR REPLACE FUNCTION biblioteca.aggiungiBibliotecario(email biblioteca.bibliotecario.email%TYPE,
                                                            hash biblioteca.bibliotecario.hash%TYPE)
RETURNS biblioteca.error
AS $$
BEGIN
    IF count(*) > 0
        FROM biblioteca.bibliotecario
        WHERE bibliotecario.email = aggiungiBibliotecario.email
    THEN
        RETURN 'BIBLIOTECARIO_GIÀ_REGISTRATO';
    END IF;

    INSERT INTO biblioteca.bibliotecario (email, hash)
    VALUES (
        aggiungiBibliotecario.email,
        aggiungiBibliotecario.hash
    );

    RETURN 'NESSUN_ERRORE';
END;
$$
LANGUAGE plpgsql;

-- Restituisce tutti i bibliotecari presenti nel database.
--
-- @return tabella di bibliotecari
CREATE OR REPLACE FUNCTION biblioteca.getBibliotecari()
RETURNS SETOF biblioteca.bibliotecario
AS $$
BEGIN
    RETURN QUERY SELECT *
                 FROM biblioteca.bibliotecario;
    RETURN;
END;
$$
LANGUAGE plpgsql;

-- Restituisce il bibliotecario con l'id specificato.
--
-- @param id l'id del bibliotecario
-- @return il bibliotecario con l'id specificato
CREATE OR REPLACE FUNCTION biblioteca.getBibliotecarioById(id biblioteca.bibliotecario.id%TYPE)
RETURNS SETOF biblioteca.bibliotecario
AS $$
BEGIN
    RETURN QUERY SELECT *
                 FROM biblioteca.bibliotecario
                 WHERE bibliotecario.id = getBibliotecarioById.id;

    RETURN;
END;
$$
LANGUAGE plpgsql;

-- Restituisce il bibliotecario con la mail specificata.
--
-- @param email l'email del bibliotecario
-- @return il bibliotecario con la mail specificata
CREATE OR REPLACE FUNCTION biblioteca.getBibliotecarioByEmail(email biblioteca.bibliotecario.email%TYPE)
RETURNS SETOF biblioteca.bibliotecario
AS $$
BEGIN
    RETURN QUERY SELECT *
                 FROM biblioteca.bibliotecario
                 WHERE bibliotecario.email = getBibliotecarioByEmail.email;

    RETURN;
END;
$$
LANGUAGE plpgsql;

-- Cambia l'email di un bibliotecario.
--
-- @param id l'id del bibliotecario
-- @param email la nuova email del bibliotecario
-- @return 'BIBLIOTECARIO_GIÀ_REGISTRATO' se esiste già un bibliotecario registrato con la mail specificata,
--         'NESSUN_ERRORE' altrimenti
CREATE OR REPLACE FUNCTION biblioteca.setBibliotecarioEmail(id biblioteca.bibliotecario.id%TYPE,
                                                            email biblioteca.bibliotecario.email%TYPE)
RETURNS biblioteca.error
AS $$
BEGIN
    IF count(*) > 0
        FROM biblioteca.bibliotecario
        WHERE bibliotecario.email = setBibliotecarioEmail.email
    THEN
        RETURN 'BIBLIOTECARIO_GIÀ_REGISTRATO';
    END IF;

    UPDATE biblioteca.bibliotecario
    SET email = setBibliotecarioEmail.email
    WHERE bibliotecario.id = setBibliotecarioEmail.id;

    RETURN 'NESSUN_ERRORE';
END;
$$
LANGUAGE plpgsql;

-- Cambia la password di un bibliotecario.
--
-- @param id l'id del bibliotecario
-- @param newHash l'hash della nuova password del bibliotecario
CREATE OR REPLACE FUNCTION biblioteca.setBibliotecarioPassword(id biblioteca.bibliotecario.id%TYPE,
                                                               newHash biblioteca.bibliotecario.hash%TYPE)
RETURNS void
AS $$
BEGIN
    UPDATE biblioteca.bibliotecario
    SET hash = setBibliotecarioPassword.newHash
    WHERE bibliotecario.id = setBibliotecarioPassword.id;
END;
$$
LANGUAGE plpgsql;

-- Rimuove un bibliotecario dal database.
--
-- @param id l'id del bibliotecario
CREATE OR REPLACE FUNCTION biblioteca.rimuoviBibliotecario(id biblioteca.bibliotecario.id%TYPE)
RETURNS void
AS $$
BEGIN
    DELETE FROM biblioteca.bibliotecario
    WHERE bibliotecario.id = rimuoviBibliotecario.id;
END;
$$
LANGUAGE plpgsql;
