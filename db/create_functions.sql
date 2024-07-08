-- Errori --
CREATE TYPE biblioteca.error AS ENUM (
    'NESSUN_ERRORE',
    'PASSWORD_ERRATA',
    'TROPPE_CONSEGNE_IN_RITARDO',
    'TROPPI_PRESTITI_IN_CORSO',
    'LIBRO_NON_DISPONIBILE',
    'LIBRI_ASSOCIATI_AD_AUTORE',
    'COPIE_ASSOCIATE_A_LIBRO',
    'COPIE_ASSOCIATE_A_SEDE',
    'COPIA_IN_PRESTITO',
    'LETTORE_PRESTITI_IN_CORSO',
    'PRESTITO_IN_RITARDO',
    'LETTORE_GIÀ_REGISTRATO'
);

-- VIEWS --

CREATE VIEW biblioteca.autoreValido AS (
    SELECT *
    FROM biblioteca.autore
    WHERE autore.isValido = true
);

CREATE VIEW biblioteca.autoreEliminato AS (
    SELECT *
    FROM biblioteca.autore
    WHERE autore.isValido = false
);

CREATE VIEW biblioteca.libroValido AS (
    SELECT *
    FROM biblioteca.libro
    WHERE libro.isValido = true
);

CREATE VIEW biblioteca.libroEliminato AS (
    SELECT *
    FROM biblioteca.libro
    WHERE libro.isValido = false
);

CREATE VIEW biblioteca.sedeOperativa AS (
    SELECT *
    FROM biblioteca.sede
    WHERE sede.isOperativa = true
);

CREATE VIEW biblioteca.sedeNonOperativa AS (
    SELECT *
    FROM biblioteca.sede
    WHERE sede.isOperativa = false
);

CREATE VIEW biblioteca.copiaValida AS (
    SELECT *
    FROM biblioteca.copia
    WHERE copia.isValida = true
);

CREATE VIEW biblioteca.copiaEliminata AS (
    SELECT *
    FROM biblioteca.copia
    WHERE copia.isValida = false
);

CREATE VIEW biblioteca.lettoreRegistrato AS (
    SELECT *
    FROM biblioteca.lettore
    WHERE lettore.isRegistrato = true
);

CREATE VIEW biblioteca.lettoreNonRegistrato AS (
    SELECT *
    FROM biblioteca.lettore
    WHERE lettore.isRegistrato = false
);

CREATE VIEW biblioteca.prestitoCorrente AS (
    SELECT *
    FROM biblioteca.prestito
    WHERE prestito.isCorrente = true
);

CREATE VIEW biblioteca.prestitoPassato AS (
    SELECT *
    FROM biblioteca.prestito
    WHERE prestito.isCorrente = false
);

CREATE VIEW biblioteca.copiaInPrestito AS (
    SELECT copiaValida.id as copia, copiaValida.libro, copiaValida.sede
    FROM biblioteca.copiaValida
    JOIN biblioteca.prestitoCorrente ON copiaValida.id = prestitoCorrente.copia
);

CREATE VIEW biblioteca.copiaDisponibile AS (
    SELECT copiaValida.id as copia, copiaValida.libro, copiaValida.sede
    FROM biblioteca.copiaValida
) EXCEPT (
    SELECT * FROM biblioteca.copiaInPrestito
);

CREATE VIEW biblioteca.ritardi AS (
    SELECT sedeOperativa.id AS sede, lettoreRegistrato.codice_fiscale AS lettore, copiaValida.id AS copia
    FROM biblioteca.prestitoCorrente
    JOIN biblioteca.copiaValida       ON prestitoCorrente.copia = copiaValida.id
    JOIN biblioteca.sedeOperativa     ON copiaValida.sede = sedeOperativa.id
    JOIN biblioteca.lettoreRegistrato ON prestitoCorrente.lettore = lettoreRegistrato.codice_fiscale
    WHERE prestitoCorrente.scadenza < current_date
);

-- Aggiunge un autore al database e ne restituisce l'id. Se esiste già un autore
-- eliminato con lo stesso nome e cognome, lo riabilita aggiornandonone i dati;
-- se esiste già un autore valido con lo stesso nome e cognome, non fa nulla.
--
-- @param nome il nome dell'autore
-- @param cognome il cognome dell'autore
-- @param biografia la biografia dell'autore
-- @param data_di_nascita la data di nascita dell'autore
-- @param data_di_morte la data di morte dell'autore
-- @return 'NESSUN_ERRORE' in ogni caso
CREATE OR REPLACE FUNCTION biblioteca.aggiungiAutore(nome biblioteca.autore.nome%TYPE,
                                                     cognome biblioteca.autore.cognome%TYPE,
                                                     biografia biblioteca.autore.biografia%TYPE,
                                                     data_di_nascita biblioteca.autore.data_di_nascita%TYPE,
                                                     data_di_morte biblioteca.autore.data_di_morte%TYPE DEFAULT NULL)
RETURNS biblioteca.error
AS $$
DECLARE
    autore biblioteca.autore.id%TYPE;
BEGIN
    SELECT autore.id INTO autore
    FROM biblioteca.autore
    WHERE autore.nome = aggiungiAutore.nome AND
          autore.cognome = aggiungiAutore.cognome;

    IF FOUND THEN
        IF NOT autore.isValido
            FROM biblioteca.autore
            WHERE autore.id = autore
        THEN
            UPDATE biblioteca.autore
            SET autore.isValido = true,
                autore.biografia = aggiungiAutore.biografia,
                autore.data_di_nascita = aggiungiAutore.data_di_nascita,
                autore.data_di_morte = aggiungiAutore.data_di_morte
            WHERE autore.id = autore;
        END IF;
    ELSE
        INSERT INTO biblioteca.autore (nome, cognome, data_di_nascita, data_di_morte, biografia)
        VALUES (
            aggiungiAutore.nome,
            aggiungiAutore.cognome,
            aggiungiAutore.data_di_nascita,
            aggiungiAutore.data_di_morte,
            aggiungiAutore.biografia
        );
    END IF;

    RETURN 'NESSUN_ERRORE';
END;
$$
LANGUAGE plpgsql;

-- Aggiunge la data di morte a un autore
--
-- @param id l'id dell'autore
-- @param data_di_morte la data di morte dell'autore
-- @return 'NESSUN_ERRORE' in ogni caso
CREATE OR REPLACE FUNCTION biblioteca.setAutoreDataDiMorte(id biblioteca.autore.id%TYPE,
                                                           data_di_morte biblioteca.autore.data_di_morte%TYPE)
RETURNS biblioteca.error
AS $$
BEGIN
    UPDATE biblioteca.autoreValido
    SET autore.data_di_morte = setAutoreDataDiMorte.data_di_morte
    WHERE autore.id = setAutoreDataDiMorte.id;

    RETURN 'NESSUN_ERRORE';
END;
$$
LANGUAGE plpgsql;

-- Rimuove un autore dal database. Se l'autore ha libri associati, l'operazione
-- fallisce.
--
-- @param id l'id dell'autore
-- @return un errore se ci sono libri associati all'autore, altrimenti 'NESSUN_ERRORE'
CREATE OR REPLACE FUNCTION biblioteca.rimuoviAutore(id biblioteca.autore.id%TYPE)
RETURNS biblioteca.error
AS $$
BEGIN
    IF count(*) > 0
        FROM biblioteca.scritto
        JOIN biblioteca.libroValido ON scritto.libro = libro.isbn
        WHERE scritto.autore = rimuoviAutore.id
    THEN
        RETURN 'LIBRI_ASSOCIATI_AD_AUTORE';
    END IF;

    UPDATE biblioteca.autoreValido
    SET autoreValido.isValido = false
    WHERE autoreValido.id = rimuoviAutore.id;

    RETURN 'NESSUN_ERRORE';
END;
$$
LANGUAGE plpgsql;

-- LIBRO --

-- Aggiunge un libro al database vincolandolo al proprio autore e ne restituisce l'id.
-- Se esiste già un libro eliminato con lo stesso isbn, lo riabilita aggiornandone i dati
-- eccetto gli autori; se esiste già un libro valido con lo stesso isbn, non fa nulla.
--
-- @param isbn l'isbn del libro
-- @param titolo il titolo del libro
-- @param trama la trama del libro
-- @param casa_editrice la casa editrice del libro
-- @param autore l'id dell'autore del libro
-- @return 'NESSUN_ERRORE' in ogni caso
CREATE OR REPLACE FUNCTION biblioteca.aggiungiLibro(isbn biblioteca.libro.isbn%TYPE,
                                                    titolo biblioteca.libro.titolo%TYPE,
                                                    trama biblioteca.libro.trama%TYPE,
                                                    casa_editrice biblioteca.libro.casa_editrice%TYPE,
                                                    VARIADIC autore NUMERIC[]) -- dovrebbe essere biblioteca.autore.id%TYPE ma
                                                                               -- per qualche motivo a postgres non piace
RETURNS biblioteca.error
AS $$
DECLARE
    libro biblioteca.libro.isbn%TYPE;
BEGIN
    SELECT biblioteca.libro.isbn INTO libro
    FROM biblioteca.libro
    WHERE biblioteca.libro.isbn = aggiungiLibro.isbn;

    IF FOUND THEN
        IF NOT libro.isValido
            FROM biblioteca.libro
            WHERE libro.isbn = libro
        THEN
            UPDATE biblioteca.libro
            SET libro.isValido = true,
                libro.titolo = aggiungiLibro.titolo,
                libro.trama = aggiungiLibro.trama,
                libro.casa_editrice = aggiungiLibro.casa_editrice
            WHERE libro.isbn = libro;
        END IF;
    ELSE
        INSERT INTO biblioteca.libro (isbn, titolo, trama, casa_editrice)
        VALUES (
            aggiungiLibro.isbn,
            aggiungiLibro.titolo,
            aggiungiLibro.trama,
            aggiungiLibro.casa_editrice
        );

        FOREACH autore IN ARRAY aggiungiLibro.autore
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

-- Rimuove un libro dal database. Se il libro ha copie associate, l'operazione fallisce.
--
-- @param libro l'isbn del libro
-- @return un errore se ci sono copie associate al libro, altrimenti 'NESSUN_ERRORE'
CREATE OR REPLACE FUNCTION biblioteca.rimuoviLibro(libro biblioteca.libro.isbn%TYPE)
RETURNS biblioteca.error
AS $$
BEGIN
    IF count(*) > 0
        FROM biblioteca.copiaValida
        WHERE copiaValida.libro = rimuoviLibro.libro
    THEN
        RETURN 'COPIE_ASSOCIATE_A_LIBRO';
    END IF;

    UPDATE biblioteca.libroValido
    SET libroValido.isValido = false
    WHERE libroValido.isbn = rimuoviLibro.libro;

    RETURN 'NESSUN_ERRORE';
END;
$$
LANGUAGE plpgsql;

-- SEDE --

-- Aggiunge una sede al database e ne restituisce l'id. Se esiste già una sede
-- eliminata con lo stesso indirizzo e città, la riabilita; se esiste già una sede
-- operativa con lo stesso indirizzo e città, non fa nulla.
--
-- @param indirizzo l'indirizzo della sede
-- @param città la città della sede
-- @return 'NESSUN_ERRORE' in ogni caso
CREATE OR REPLACE FUNCTION biblioteca.aggiungiSede(indirizzo biblioteca.sede.indirizzo%TYPE,
                                                   città biblioteca.sede.città%TYPE)
RETURNS biblioteca.error
AS $$
DECLARE
    sede biblioteca.autore.id%TYPE;
BEGIN
    SELECT biblioteca.sede.id INTO sede
    FROM biblioteca.sede
    WHERE biblioteca.sede.indirizzo ilike aggiungiSede.indirizzo AND
          biblioteca.sede.città ilike aggiungiSede.città;

    IF FOUND THEN
        IF NOT sede.isOperativa
            FROM biblioteca.sede
            WHERE sede.id = sede
        THEN
            UPDATE biblioteca.sede
            SET sede.isOperativa = true
            WHERE sede.id = sede;
        END IF;
    ELSE
        INSERT INTO biblioteca.sede (indirizzo, città)
        VALUES (
            aggiungiSede.indirizzo,
            aggiungiSede.città
        );
    END IF;

    RETURN 'NESSUN_ERRORE';
END;
$$
LANGUAGE plpgsql;

-- Restituisce il numero di copie gestite da una sede.
--
-- @param sede l'id della sede
-- @return il numero di copie gestite dalla sede
CREATE OR REPLACE FUNCTION biblioteca.getNumeroDiCopieGestite(sede biblioteca.sede.id%TYPE)
RETURNS INTEGER
AS $$
DECLARE
    numero_copie INTEGER;
BEGIN
    SELECT count(*) INTO numero_copie
    FROM biblioteca.copiaValida
    WHERE copiaValida.sede = getNumeroDiCopieGestite.sede;
    RETURN numero_copie;
END;
$$
LANGUAGE plpgsql;

-- Restituisce il numero di isbn gestiti da una sede.
--
-- @param sede l'id della sede
-- @return il numero di isbn gestiti dalla sede
CREATE OR REPLACE FUNCTION biblioteca.getNumeroDiIsbnGestiti(sede biblioteca.sede.id%TYPE)
RETURNS INTEGER
AS $$
DECLARE
    numero_isbn INTEGER;
BEGIN
    SELECT count(DISTINCT libro) INTO numero_isbn
    FROM biblioteca.copiaValida
    WHERE copiaValida.sede = getNumeroDiIsbnGestiti.sede;
    RETURN numero_isbn;
END;
$$
LANGUAGE plpgsql;

-- Restituisce il numero di prestiti in corso gestiti da una sede.
--
-- @param sede l'id della sede
-- @return il numero di prestiti in corso gestiti dalla sede
CREATE OR REPLACE FUNCTION biblioteca.getNumeroDiPrestitiInCorso(sede biblioteca.sede.id%TYPE)
RETURNS INTEGER
AS $$
DECLARE
    numero_prestiti INTEGER;
BEGIN
    SELECT count(*) INTO numero_prestiti
    FROM biblioteca.prestitoCorrente
    WHERE prestito.sede = getNumeroDiPrestitiInCorso.sede;
    RETURN numero_prestiti;
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
-- @return un errore se ci sono copie associate alla sede, altrimenti 'NESSUN_ERRORE'
CREATE OR REPLACE FUNCTION biblioteca.rimuoviSede(sede biblioteca.sede.id%TYPE)
RETURNS biblioteca.error
AS $$
BEGIN
    IF count(*) > 0
        FROM biblioteca.copiaValida
        WHERE copiaValida.sede = rimuoviSede.sede
    THEN
        RETURN 'COPIE_ASSOCIATE_A_SEDE';
    END IF;

    UPDATE biblioteca.sedeOperativa
    SET sedeOperativa.isOperativa = false
    WHERE sedeOperativa.id = rimuoviSede.sede;

    RETURN 'NESSUN_ERRORE';
END
$$
LANGUAGE plpgsql;

-- COPIA --

-- Aggiunge una copia al database e ne restituisce l'id. Poichè possono esistere
-- copie con lo stesso libro e la stessa sede, le copie eliminate non possono essere
-- riabilitate.
--
-- @param libro l'isbn del libro
-- @param sede l'id della sede
-- @return 'NESSUN_ERRORE' in ogni caso
CREATE OR REPLACE FUNCTION biblioteca.aggiungiCopia(libro biblioteca.libro.isbn%TYPE,
                                                    sede biblioteca.sede.id%TYPE)
RETURNS biblioteca.error
AS $$
BEGIN
    INSERT INTO biblioteca.copia (libro, sede)
    VALUES (
        aggiungiCopia.libro,
        aggiungiCopia.sede
    );

    RETURN 'NESSUN_ERRORE';
END;
$$
LANGUAGE plpgsql;

-- Restituisce l'id di una copia disponibile di libro. Se sede != NULL la ricerca
-- viene ristretta alla sede specificata.
--
-- @param libro l'isbn del libro
-- @param sede l'id della sede
-- @return l'id della copia disponibile o NULL se non esiste
CREATE OR REPLACE FUNCTION biblioteca.trovaCopiaDisponibile(libro biblioteca.libro.isbn%TYPE,
                                                            sede biblioteca.sede.id%TYPE DEFAULT NULL)
RETURNS biblioteca.copia.id%TYPE
AS $$
DECLARE
    copiaTrovata biblioteca.copia.id%TYPE;
BEGIN
    IF trovaCopiaDisponibile.sede IS NULL THEN
        SELECT copiaDisponibile.id INTO copiaTrovata
        FROM copiaDisponibile
        WHERE copiaDisponibile.libro = trovaCopiaDisponibile.libro;
    ELSE
        SELECT copiaDisponibile.id INTO copiaTrovata
        FROM copiaDisponibile
        WHERE copiaDisponibile.libro = trovaCopiaDisponibile.libro AND
              copiaDisponibile.sede = trovaCopiaDisponibile.sede;
    END IF;
    RETURN copiaTrovata;
END;
$$
LANGUAGE plpgsql;

-- Assegna una copia a una nuova sede. Se la copia è in prestito, l'operazione fallisce.
--
-- @param copia l'id della copia
-- @param nuovaSede l'id della nuova sede
-- @return un errore se la copia è in prestito, altrimenti 'NESSUN_ERRORE'
CREATE OR REPLACE FUNCTION biblioteca.cambiaSede(copia biblioteca.copia.id%TYPE,
                                                 nuovaSede biblioteca.sede.id%TYPE)
RETURNS biblioteca.error
AS $$
BEGIN
    IF count(*) > 0
        FROM biblioteca.prestitoCorrente
        WHERE prestitoCorrente.copia = cambiaSede.copia
    THEN
        RETURN 'COPIA_IN_PRESTITO';
    END IF;

    UPDATE biblioteca.copiaValida
    SET copiaValida.sede = cambiaSede.nuovaSede
    WHERE copiaValida.id = cambiaSede.copia;

    RETURN 'NESSUN_ERRORE';
END;
$$
LANGUAGE plpgsql;

-- Rimuove una copia dal database. Se la copia è in prestito, l'operazione fallisce.
--
-- @param copia l'id della copia
-- @return un errore se la copia è in prestito, altrimenti 'NESSUN_ERRORE'
CREATE OR REPLACE FUNCTION biblioteca.rimuoviCopia(copia biblioteca.copia.id%TYPE)
RETURNS biblioteca.error
AS $$
BEGIN
    IF count(*) > 0
        FROM biblioteca.prestito
        WHERE prestito.copia = rimuoviCopia.copia AND prestito.isCorrente
    THEN
        RETURN 'COPIA_IN_PRESTITO';
    END IF;

    UPDATE biblioteca.copiaValida
    SET copiaValida.isValida = false
    WHERE copiaValida.id = rimuoviCopia.copia;

    RETURN 'NESSUN_ERRORE';
END;
$$
LANGUAGE plpgsql;

-- BIBLIOTECARIO --

-- Aggiunge un bibliotecario al database e ne restituisce l'id.
--
-- @param email l'email del bibliotecario
-- @param hash l'hash della password del bibliotecario
-- @param salt il salt della password del bibliotecario
-- @return 'NESSUN_ERRORE' in ogni caso
CREATE OR REPLACE FUNCTION biblioteca.aggiungiBibliotecario(email biblioteca.bibliotecario.email%TYPE,
                                                            hash biblioteca.bibliotecario.hash%TYPE,
                                                            salt biblioteca.bibliotecario.salt%TYPE)
RETURNS biblioteca.error
AS $$
BEGIN
    INSERT INTO biblioteca.bibliotecario (email, hash, salt)
    VALUES (
        aggiungiBibliotecario.email,
        aggiungiBibliotecario.hash,
        aggiungiBibliotecario.salt
    );

    RETURN 'NESSUN_ERRORE';
END;
$$
LANGUAGE plpgsql;

-- Cambia l'email di un bibliotecario. L'operazione fallisce se la password non è corretta.
--
-- @param id l'id del bibliotecario
-- @param email la nuova email del bibliotecario
-- @param hash l'hash della password del bibliotecario
-- @return un errore se la password non è corretta, altrimenti 'NESSUN_ERRORE'
CREATE OR REPLACE FUNCTION biblioteca.cambiaBibliotecarioEmail(id biblioteca.bibliotecario.id%TYPE,
                                                               email biblioteca.bibliotecario.email%TYPE,
                                                               hash biblioteca.bibliotecario.hash%TYPE)
RETURNS biblioteca.error
AS $$
BEGIN
    IF bibliotecario.hash = cambiaBibliotecarioEmail.hash
        FROM biblioteca.bibliotecario
        WHERE bibliotecario.id = cambiaBibliotecarioEmail.id
    THEN
        UPDATE biblioteca.bibliotecario
        SET bibliotecario.email = cambiaBibliotecarioEmail.email
        WHERE bibliotecario.id = cambiaBibliotecarioEmail.id;
        RETURN 'NESSUN_ERRORE';
    END IF;

    RETURN 'PASSWORD_ERRATA';
END;
$$
LANGUAGE plpgsql;

-- Cambia la password di un bibliotecario. Se la vecchia password non è corretta, l'operazione fallisce.
--
-- @param id l'id del bibliotecario
-- @param oldHash l'hash della vecchia password del bibliotecario
-- @param newHash l'hash della nuova password del bibliotecario
-- @param newSalt il salt della nuova password del bibliotecario
-- @return un errore se la vecchia password non è corretta, altrimenti 'NESSUN_ERRORE'
CREATE OR REPLACE FUNCTION biblioteca.cambiaBibliotecarioPassword(id biblioteca.bibliotecario.id%TYPE,
                                                                  oldHash biblioteca.bibliotecario.hash%TYPE,
                                                                  newHash biblioteca.bibliotecario.hash%TYPE,
                                                                  newSalt biblioteca.bibliotecario.salt%TYPE)
RETURNS biblioteca.error
AS $$
BEGIN
    IF bibliotecario.hash = cambiaBibliotecarioPassword.oldHash
        FROM biblioteca.bibliotecario
        WHERE bibliotecario.id = cambiaBibliotecarioPassword.id
    THEN
        UPDATE biblioteca.bibliotecario
        SET bibliotecario.hash = cambiaBibliotecarioPassword.newHash,
            bibliotecario.salt = cambiaBibliotecarioPassword.newSalt
        WHERE bibliotecario.id = cambiaBibliotecarioPassword.id;

        RETURN 'NESSUN_ERRORE';
    END IF;

    RETURN 'PASSWORD_ERRATA';
END;
$$
LANGUAGE plpgsql;

-- Rimuove un bibliotecario dal database.
--
-- @param id l'id del bibliotecario
-- @return 'NESSUN_ERRORE' in ogni caso
CREATE OR REPLACE FUNCTION biblioteca.rimuoviBibliotecario(id biblioteca.bibliotecario.id%TYPE)
RETURNS biblioteca.error
AS $$
BEGIN
    DELETE FROM biblioteca.bibliotecario
    WHERE bibliotecario.id = rimuoviBibliotecario.id;

    RETURN 'NESSUN_ERRORE';
END;
$$
LANGUAGE plpgsql;

-- LETTORE --

-- Aggiunge un lettore al database e ne restituisce l'id. Se esiste già un lettore
-- eliminato con lo stesso codice fiscale, lo riabilita aggiornandone i dati eccetto
-- per la password (per evitare il furto degli account eliminati). Se esiste già un
-- lettore registrato con lo stesso codice fiscale, l'operazione fallisce.
--
-- @param codice_fiscale il codice fiscale del lettore
-- @param nome il nome del lettore
-- @param cognome il cognome del lettore
-- @param email l'email del lettore
-- @param hash l'hash della password del lettore
-- @param salt il salt della password del lettore
-- @param categoria la categoria del lettore
-- @return 'LETTORE_GIÀ_REGISTRATO' se il lettore è già registrato,
--         'NESSUN_ERRORE' altrimenti
CREATE OR REPLACE FUNCTION biblioteca.aggiungiLettore(codice_fiscale biblioteca.lettore.codice_fiscale%TYPE,
                                                      nome biblioteca.lettore.nome%TYPE,
                                                      cognome biblioteca.lettore.cognome%TYPE,
                                                      email biblioteca.lettore.email%TYPE,
                                                      hash biblioteca.lettore.hash%TYPE,
                                                      salt biblioteca.lettore.salt%TYPE,
                                                      categoria biblioteca.lettore.categoria%TYPE)
RETURNS biblioteca.error
AS $$
DECLARE
    codice_fiscale biblioteca.lettore.codice_fiscale%TYPE;
BEGIN
    SELECT biblioteca.lettore.codice_fiscale INTO codice_fiscale
    FROM biblioteca.lettore
    WHERE biblioteca.lettore.codice_fiscale = aggiungiLettore.codice_fiscale;

    IF FOUND
    THEN
        IF biblioteca.lettore.isRegistrato
            FROM biblioteca.lettore
            WHERE biblioteca.lettore.codice_fiscale = codice_fiscale
        THEN
            RETURN 'LETTORE_GIÀ_REGISTRATO';
        END IF;

        UPDATE biblioteca.lettoreNonRegistrato
        SET lettoreNonRegistrato.isRegistrato = true,
            lettoreNonRegistrato.nome = aggiungiLettore.nome,
            lettoreNonRegistrato.cognome = aggiungiLettore.cognome,
            lettoreNonRegistrato.email = aggiungiLettore.email,
            lettoreNonRegistrato.categoria = aggiungiLettore.categoria
        WHERE lettoreNonRegistrato.codice_fiscale = codice_fiscale;

        RETURN 'NESSUN_ERRORE';
    END IF;

    INSERT INTO biblioteca.lettore
    VALUES (
        aggiungiLettore.codice_fiscale,
        aggiungiLettore.nome,
        aggiungiLettore.cognome,
        aggiungiLettore.email,
        aggiungiLettore.hash,
        aggiungiLettore.salt,
        aggiungiLettore.categoria
    );

    RETURN 'NESSUN_ERRORE';
END;
$$
LANGUAGE plpgsql;

-- Cambia l'email di un lettore. Se la password non è corretta, l'operazione fallisce.
--
-- @param codice_fiscale il codice fiscale del lettore
-- @param email la nuova email del lettore
-- @param hash l'hash della password del lettore
-- @return un errore se la password non è corretta, altrimenti 'NESSUN_ERRORE'
CREATE OR REPLACE FUNCTION biblioteca.cambiaLettoreEmail(codice_fiscale biblioteca.lettore.codice_fiscale%TYPE,
                                                         email biblioteca.lettore.email%TYPE,
                                                         hash biblioteca.lettore.hash%TYPE)
RETURNS biblioteca.error
AS $$
BEGIN
    IF lettoreRegistrato.hash = cambiaLettoreEmail.hash
        FROM biblioteca.lettoreRegistrato
        WHERE lettoreRegistrato.codice_fiscale = cambiaLettoreEmail.codice_fiscale
    THEN
        UPDATE biblioteca.lettoreRegistrato
        SET lettoreRegistrato.email = cambiaLettoreEmail.email
        WHERE lettoreRegistrato.codice_fiscale = cambiaLettoreEmail.codice_fiscale;
        RETURN 'NESSUN_ERRORE';
    END IF;

    RETURN 'PASSWORD_ERRATA';
END;
$$
LANGUAGE plpgsql;

-- Cambia la password di un lettore. Se la vecchia password non è corretta, l'operazione fallisce.
--
-- @param codice_fiscale il codice fiscale del lettore
-- @param oldHash l'hash della vecchia password del lettore
-- @param newHash l'hash della nuova password del lettore
-- @param newSalt il salt della nuova password del lettore
-- @return un errore se la vecchia password non è corretta, altrimenti 'NESSUN_ERRORE'
CREATE OR REPLACE FUNCTION biblioteca.cambiaLettorePassword(codice_fiscale biblioteca.lettore.codice_fiscale%TYPE,
                                                            oldHash biblioteca.lettore.hash%TYPE,
                                                            newHash biblioteca.lettore.hash%TYPE,
                                                            newSalt biblioteca.lettore.salt%TYPE)
RETURNS biblioteca.error
AS $$
BEGIN
    IF lettoreRegistrato.hash = cambiaLettorePassword.oldHash
        FROM biblioteca.lettoreRegistrato
        WHERE lettoreRegistrato.codice_fiscale = cambiaLettorePassword.codice_fiscale
    THEN
        UPDATE biblioteca.lettoreRegistrato
        SET lettoreRegistrato.hash = cambiaLettorePassword.newHash,
            lettoreRegistrato.salt = cambiaLettorePassword.newSalt
        WHERE lettoreRegistrato.codice_fiscale = cambiaLettorePassword.codice_fiscale;

        RETURN 'NESSUN_ERRORE';
    END IF;

    RETURN 'PASSWORD_ERRATA';
END;
$$
LANGUAGE plpgsql;

-- Cambia la categoria di un lettore. Se la password del bibliotecario non è corretta, l'operazione fallisce.
--
-- @param codice_fiscale il codice fiscale del lettore
-- @param categoria la nuova categoria del lettore
-- @return 'NESSUN_ERRORE' in ogni caso
CREATE OR REPLACE FUNCTION biblioteca.cambiaLettoreCategoria(codice_fiscale biblioteca.lettore.codice_fiscale%TYPE,
                                                             categoria biblioteca.lettore.categoria%TYPE)
RETURNS biblioteca.error
AS $$
BEGIN
    UPDATE biblioteca.lettoreRegistrato
    SET lettoreRegistrato.categoria = cambiaLettoreCategoria.categoria
    WHERE lettoreRegistrato.codice_fiscale = cambiaLettoreCategoria.codice_fiscale;

    RETURN 'NESSUN_ERRORE';
END;
$$
LANGUAGE plpgsql;

-- Azzera il numero di ritardi di un lettore.
--
-- @param codice_fiscale il codice fiscale del lettore
-- @return 'NESSUN_ERRORE' in ogni caso
CREATE OR REPLACE FUNCTION biblioteca.resetRitardi(codice_fiscale biblioteca.lettore.codice_fiscale%TYPE)
RETURNS biblioteca.error
AS $$
BEGIN
    UPDATE biblioteca.lettoreRegistrato
    SET lettoreRegistrato.ritardi = 0
    WHERE lettoreRegistrato.codice_fiscale = resetRitardi.codice_fiscale;

    RETURN 'NESSUN_ERRORE';
END;
$$
LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION biblioteca.rimuoviLettore(codice_fiscale biblioteca.lettore.codice_fiscale%TYPE)
RETURNS biblioteca.error
AS $$
BEGIN
    IF count(*) > 0
        FROM biblioteca.prestito
        WHERE prestito.lettore = rimuoviLettore.codice_fiscale AND prestito.isCorrente
    THEN
        RETURN 'LETTORE_PRESTITI_IN_CORSO';
    END IF;

    UPDATE biblioteca.lettoreRegistrato
    SET lettoreRegistrato.isRegistrato = false
    WHERE lettoreRegistrato.codice_fiscale = rimuoviLettore.codice_fiscale;

    RETURN 'NESSUN_ERRORE';
END;
$$
LANGUAGE plpgsql;

-- PRESTITO --

-- Aggiunge un prestito di copia fatto a lettore nella data corrente. Il prestito
-- non viene consentito se lettore ha 5 o più consegne in ritardo, se lettore ha
-- già 3 o 5 prestiti in corso, oppure se copia non è disponibile.
--
-- @param copia l'id della copia
-- @param lettore il codice fiscale del lettore
-- @return 'TRPPE_CONSEGNE_IN_RITARDO' se lettore ha 5 o più consegne in ritardo,
--         'TROPPI_PRESTITI_IN_CORSO' se lettore ha già 3 o 5 prestiti in corso,
--         'LIBRO_NON_DISPONIBILE' se copia non è disponibile,
--         'NESSUN_ERRORE' altrimenti
CREATE OR REPLACE FUNCTION biblioteca.richiediPrestito(copia biblioteca.copia.id%TYPE,
                                                       lettore biblioteca.lettore.codice_fiscale%TYPE)
RETURNS biblioteca.error
AS $$
DECLARE
    max_prestiti INTEGER;
    max_durata_prestito CONSTANT INTEGER := 30;
    data_corrente CONSTANT DATE := current_date;
    scadenza CONSTANT DATE := data_corrente + max_durata_prestito;
BEGIN
    IF lettoreRegistrato.categoria = 'premium'
        FROM biblioteca.lettoreRegistrato
        WHERE lettoreRegistrato.codice_fiscale = richiediPrestito.codice_fiscale
    THEN
        max_prestiti := 5;
    ELSE
        max_prestiti := 3;
    END IF;

    CASE
        WHEN lettoreRegistrato.ritardi >= 5
            FROM biblioteca.lettoreRegistrato
            WHERE lettoreRegistrato.codice_fiscale = richiediPrestito.codice_fiscale
        THEN
            RETURN 'TROPPE_CONSEGNE_IN_RITARDO';
        WHEN count(*) >= max_prestiti
            FROM biblioteca.prestitoCorrente
            JOIN biblioteca.lettoreRegistrato ON prestitoCorrente.lettore = lettoreRegistrato.codice_fiscale
        THEN
            RETURN 'TROPPI_PRESTITI_IN_CORSO';
        WHEN count(*) = 0
            FROM biblioteca.copiaDisponibile
            WHERE copiaDisponibile.copia = richiediPrestito.copia
        THEN
            RETURN 'LIBRO_NON_DISPONIBILE';
    END CASE;

    INSERT INTO biblioteca.prestito
    VALUES (
        data_corrente,
        copia,
        lettore,
        'corrente',
        scadenza,
        NULL
    );

    RETURN 'NESSUN_ERRORE';
END;
$$
LANGUAGE plpgsql;

-- Conclude un prestito corrente e lo aggiunge allo storico.
--
-- @param copia l'id della copia
-- @return 'NESSUN_ERRORE' in ogni caso
CREATE OR REPLACE FUNCTION biblioteca.restituisciPrestito(copia biblioteca.copia.id%TYPE)
RETURNS biblioteca.error
AS $$
BEGIN
    UPDATE biblioteca.prestitoCorrente
    SET prestitoCorrente.isCorrente = false,
        prestitoCorrente.fine = current_date
    WHERE prestitoCorrente.copia = copia;

    RETURN 'NESSUN_ERRORE';
END;
$$
LANGUAGE plpgsql;

-- Proroga del numero di giorni specificato un prestito corrente se non è già in ritardo.
-- @return 'PRESTITO_IN_RITARDO' se il prestito è in ritardo, 'NESSUN_ERRORE' altrimenti
CREATE OR REPLACE FUNCTION biblioteca.prorogaPrestito(copia biblioteca.copia.id%TYPE,
                                                      giorniDiProroga INTEGER)
RETURNS biblioteca.error
AS $$
BEGIN
    IF count(*) = 0
        FROM biblioteca.ritardi
        WHERE ritardi.copia = prorogaPrestito.copia
    THEN
        UPDATE biblioteca.prestitoCorrente
        SET prestitoCorrente.scadenza = prestitoCorrente.scadenza + giorniDiProroga
        WHERE prestitoCorrente.copia = copia;
        RETURN 'NESSUN_ERRORE';
    END IF;

    RETURN 'PRESTITO_IN_RITARDO';
END;
$$
LANGUAGE plpgsql;

-- Trigger. Se un prestito è in ritardo, incrementa il contatore dei ritardi del lettore.
-- @return NULL
CREATE OR REPLACE FUNCTION aggiornaRitardi()
RETURNS trigger
AS $$
BEGIN
    -- questa funzione incrementa il contatore dei ritardi se OLD.storicità = 'corrente',
    -- NEW.storicità = 'passato' e NEW.data_restituzione > NEW.scadenza_prestito.
    -- Restituisce NULL.

    IF OLD.isCorrente AND
        NOT NEW.isCorrente AND
        NEW.fine > NEW.scadenza
    THEN
        UPDATE biblioteca.lettoreRegistrato
        SET lettoreRegistrato.ritardi = lettoreRegistrato.ritardi + 1
        WHERE lettoreRegistrato.codice_fiscale = lettore;
    END IF;
    RETURN NULL;
END;
$$
LANGUAGE plpgsql;

-- Trigger. Se necessario aggiorna i ritardi quando un prestito viene restituito.
CREATE OR REPLACE TRIGGER aggiornaRitardi
AFTER UPDATE ON biblioteca.prestito
FOR EACH ROW
EXECUTE FUNCTION biblioteca.aggiornaRitardi();
