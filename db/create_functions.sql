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
    'PRESTITO_IN_RITARDO'
);

-- AUTORE --

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

-- Aggiunge un autore al database e ne restituisce l'id.
CREATE OR REPLACE FUNCTION biblioteca.aggiungiAutore(nome biblioteca.autore.nome%TYPE,
                                                     cognome biblioteca.autore.cognome%TYPE,
                                                     biografia biblioteca.autore.biografia%TYPE,
                                                     data_di_nascita biblioteca.autore.data_di_nascita%TYPE,
                                                     data_di_morte biblioteca.autore.data_di_morte%TYPE DEFAULT NULL)
RETURNS biblioteca.autore.id%TYPE
AS $$
DECLARE
    autore_id biblioteca.autore.id%TYPE;
BEGIN
    INSERT INTO biblioteca.autore (nome, cognome, data_di_nascita, data_di_morte, biografia)
    VALUES (
        aggiungiAutore.nome, 
        aggiungiAutore.cognome, 
        aggiungiAutore.data_di_nascita, 
        aggiungiAutore.data_di_morte, 
        aggiungiAutore.biografia
    )
    RETURNING id INTO autore_id;
    RETURN autore_id;
END;
$$
LANGUAGE plpgsql;

-- Aggiunge la data di morte a un autore
CREATE OR REPLACE FUNCTION biblioteca.setAutoreDataDiMorte(id biblioteca.autore.id%TYPE, 
                                                           data_di_morte biblioteca.autore.data_di_morte%TYPE)
RETURNS void
AS $$
BEGIN
    UPDATE biblioteca.autoreValido
    SET autore.data_di_morte = setAutoreDataDiMorte.data_di_morte
    WHERE autore.id = setAutoreDataDiMorte.id;
END;
$$
LANGUAGE plpgsql;

-- Rimuove un autore dal database
CREATE OR REPLACE FUNCTION biblioteca.rimuoviAutore(id biblioteca.autore.id%TYPE)
RETURNS biblioteca.error
AS $$
BEGIN
    IF count(*) > 0
        FROM biblioteca.scritto
        JOIN biblioteca.libroValido ON scritto.libro = libro.isbn
        WHERE scritto.autore = rimuoviAutore.id;
    THEN
        RETURN 'LIBRI_ASSOCIATI_AD_AUTORE';
    END IF;

    UPDATE biblioteca.autoreValido
    SET autoreValido.isValido = false
    WHERE autore.id = rimuoviAutore.id;

    RETURN 'NESSUN_ERRORE';
END;
$$
LANGUAGE plpgsql;

-- LIBRO --

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

-- Aggiunge un libro al database vincolandolo al proprio autore e ne restituisce l'id
CREATE OR REPLACE FUNCTION biblioteca.aggiungiLibro(isbn biblioteca.libro.isbn%TYPE,
                                                    titolo biblioteca.libro.titolo%TYPE,
                                                    trama biblioteca.libro.trama%TYPE,
                                                    casa_editrice biblioteca.libro.casa_editrice%TYPE,
                                                    autore biblioteca.autore.id%TYPE)
RETURNS biblioteca.libro.isbn%TYPE
AS $$
DECLARE
    libro biblioteca.libro.isbn%TYPE;
BEGIN
    INSERT INTO biblioteca.libro
    VALUES (
        aggiungiLibro.isbn,
        aggiungiLibro.titolo,
        aggiungiLibro.trama,
        aggiungiLibro.casa_editrice
    )
    RETURNING biblioteca.libro.isbn INTO libro;

    INSERT INTO biblioteca.scritto
    VALUES (
        aggiungiLibro.autore,
        aggiungiLibro.isbn
    );

    RETURN libro;
END;
$$
LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION biblioteca.rimuoviLibro(libro biblioteca.libro.isbn%TYPE)
RETURNS biblioteca.error
AS $$
BEGIN
    IF count(*) > 0
        FROM biblioteca.copiaValida
        WHERE copiaValida.libro = rimuoviLibro.libro;
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

CREATE VIEW biblioteca.ritardi AS (
    SELECT sedeOperativa.id AS sede, lettoreRegistrato.codice_fiscale AS lettore, copiaValida.id AS copia 
    FROM biblioteca.prestitoCorrente
    JOIN biblioteca.copiaValida       ON prestitoCorrente.copia = copiaValida.id 
    JOIN biblioteca.sedeOperativa     ON copiaValida.sede = sedeOperativa.id 
    JOIN biblioteca.lettoreRegistrato ON prestitoCorrente.lettore = lettoreRegistrato.codice_fiscale 
    WHERE prestitoCorrente.scadenza < current_date;
);

-- Aggiunge una sede al database e ne restituisce l'id.
CREATE OR REPLACE FUNCTION biblioteca.aggiungiSede(indirizzo biblioteca.sede.indirizzo%TYPE, 
                                                   città biblioteca.sede.città%TYPE)
RETURNS biblioteca.sede.id%TYPE
AS $$
DECLARE
    sede biblioteca.autore.id%TYPE;
BEGIN
    INSERT INTO biblioteca.sede (indirizzo, città)
    VALUES (
        aggiungiSede.indirizzo,
        aggiungiSede.città
    )
    RETURNING biblioteca.id INTO sede;
    RETURN sede;
END;
$$
LANGUAGE plpgsql;

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

CREATE OR REPLACE FUNCTION biblioteca.getNumeroDiPrestitiInCorso(sede biblioteca.sede.id%TYPE)
RETURNS INTEGER
AS $$
DECLARE
    numero_prestiti INTEGER;
BEGIN
    SELECT count(*) INTO numero_prestiti
    FROM biblioteca.prestito
    WHERE prestito.isCorrente AND prestito.sede = getNumeroDiPrestitiInCorso.sede;
    RETURN numero_prestiti;
$$
LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION biblioteca.getRitardi(sede biblioteca.sede.id%TYPE)
RETURNS SETOF biblioteca.ritardi%ROWTYPE
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

CREATE OR REPLACE FUNCTION biblioteca.rimuoviSede(sede biblioteca.sede.id%TYPE)
RETURNS void
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

CREATE VIEW copiaDisponibile AS (
    SELECT copia.id as copia, copia.libro, copia.sede
    FROM biblioteca.copiaValida
) EXCEPT (
    SELECT * FROM copiaInPrestito
);

-- ex PrestitiInCorso
CREATE VIEW copiaInPrestito AS (
    SELECT copia.id as copia, copia.libro, copia.sede
    FROM biblioteca.copiaValida
    JOIN biblioteca.prestito ON copia.id = prestito.copia
    WHERE prestito.isCorrente;
);

-- Aggiunge una copia al database e ne restituisce l'id
CREATE OR REPLACE FUNCTION biblioteca.aggiungiCopia(libro biblioteca.libro.isbn%TYPE, 
                                                    sede biblioteca.sede.id%TYPE)
RETURNS biblioteca.copia.id%TYPE
AS $$
DECLARE
    copia_id biblioteca.copia.id%TYPE;
BEGIN
    INSERT INTO biblioteca.copia (libro, sede)
    VALUES (
        aggiungiCopia.libro,
        aggiungiCopia.sede
    )
    RETURNING id INTO copia_id;
    RETURN copia_id;
END;
$$
LANGUAGE plpgsql;

-- Restituisce l'id di una copia disponibile di libro. Se sede != NULL la ricerca
-- viene ristretta alla sede specificata.
-- Gestisce il requisito 6.
-- 
-- Returns:
--      l'id di una copia disponibile se esiste, NULL altrimenti
--
CREATE OR REPLACE FUNCTION biblioteca.trovaCopiaDisponibile(libro biblioteca.libro.isbn%TYPE,
                                                            sede biblioteca.sede.id%TYPE DEFAULT NULL)
RETURNS biblioteca.copia.id%TYPE
AS $$
DECLARE
    copiaTrovata biblioteca.copia.id%TYPE;
BEGIN
    IF sede IS NULL THEN
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

CREATE OR REPLACE FUNCTION biblioteca.cambiaSede(copia biblioteca.copia.id%TYPE, 
                                                 nuovaSede biblioteca.sede.id%TYPE)
RETURNS void
AS $$
BEGIN
    UPDATE biblioteca.copiaValida
    SET copiaValida.sede = cambiaSede.nuovaSede
    WHERE copiaValida.id = cambiaSede.copia;
END;
$$
LANGUAGE plpgsql;

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

-- Aggiunge un bibliotecario al database e ne restituisce l'id
CREATE OR REPLACE FUNCTION biblioteca.aggiungiBibliotecario(email biblioteca.bibliotecario.email%TYPE,
                                                            hash biblioteca.bibliotecario.hash%TYPE,
                                                            salt biblioteca.bibliotecario.salt%TYPE)
RETURNS biblioteca.bibliotecario.id%TYPE;
AS $$
DECLARE
    bibliotecario biblioteca.bibliotecario.id%TYPE;
BEGIN
    INSERT INTO biblioteca.bibliotecario (email, hash, salt)
    VALUES (
        aggiungiBibliotecario.email,
        aggiungiBibliotecario.hash,
        aggiungiBibliotecario.salt
    )
    RETURNING biblioteca.bibliotecario.id INTO bibliotecario;
    RETURN bibliotecario;
END;
$$
LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION biblioteca.cambiaBibliotecarioEmail(id biblioteca.bibliotecario.id%TYPE, 
                                                               email biblioteca.bibliotecario.email%TYPE)
RETURNS void
AS $$
BEGIN
    UPDATE biblioteca.bibliotecario
    SET bibliotecario.email = cambiaBibliotecarioEmail.email
    WHERE bibliotecario.id = cambiaBibliotecarioEmail.id;
END;
$$
LANGUAGE plpgsql;

-- Se viene inserita correttamente la vecchia password, consente di aggiornare la password di un bibliotecario.
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

-- Rimuove un bibliotecario dal database
CREATE OR REPLACE FUNCTION biblioteca.rimuoviBibliotecario(id biblioteca.bibliotecario.id%TYPE)
RETURNS void
AS $$
BEGIN
    DELETE FROM biblioteca.bibliotecario
    WHERE bibliotecario.id = rimuoviBibliotecario.id;
END;
$$
LANGUAGE plpgsql;

-- LETTORE --

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

-- Aggiunge un lettore al database e ne restituisce l'id
CREATE OR REPLACE FUNCTION biblioteca.aggiungiLettore(codice_fiscale biblioteca.lettore.codice_fiscale%TYPE,
                                                      nome biblioteca.lettore.nome%TYPE,
                                                      cognome biblioteca.lettore.cognome%TYPE,
                                                      email biblioteca.lettore.email%TYPE,
                                                      hash biblioteca.lettore.hash%TYPE,
                                                      salt biblioteca.lettore.salt%TYPE,
                                                      categoria biblioteca.lettore.categoria%TYPE)
RETURNS biblioteca.lettore.codice_fiscale%TYPE
AS $$
DECLARE
    codice_fiscale biblioteca.lettore.codice_fiscale%TYPE; 
BEGIN
    INSERT INTO biblioteca.lettore
    VALUES (
        aggiungiLettore.codice_fiscale,
        aggiungiLettore.nome,
        aggiungiLettore.cognome,
        aggiungiLettore.email,
        aggiungiLettore.hash,
        aggiungiLettore.salt,
        aggiungiLettore.categoria
    )
    RETURNING biblioteca.lettore.codice_fiscale INTO codice_fiscale;
    RETURN codice_fiscale;
END;
$$
LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION biblioteca.cambiaLettoreEmail(codice_fiscale biblioteca.lettore.codice_fiscale%TYPE,
                                                         email biblioteca.lettore.email%TYPE)
RETURNS void
AS $$
BEGIN
    UPDATE biblioteca.lettoreRegistrato
    SET lettoreRegistrato.email = cambiaLettoreEmail.email
    WHERE lettoreRegistrato.codice_fiscale = cambiaLettoreEmail.codice_fiscale;
END;
$$
LANGUAGE plpgsql;

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

CREATE OR REPLACE FUNCTION biblioteca.cambiaLettoreCategoria(codice_fiscale biblioteca.lettore.codice_fiscale%TYPE,
                                                             categoria biblioteca.lettore.categoria%TYPE)
RETURNS void
AS $$
BEGIN
    UPDATE biblioteca.lettoreRegistrato
    SET lettoreRegistrato.categoria = cambiaLettoreCategoria.categoria
    WHERE lettoreRegistrato.codice_fiscale = cambiaLettoreCategoria.codice_fiscale;
END;

CREATE OR REPLACE FUNCTION biblioteca.resetRitardi(codice_fiscale biblioteca.lettore.codice_fiscale%TYPE)
RETURNS void
AS $$
BEGIN
    UPDATE biblioteca.lettoreRegistrato
    SET lettoreRegistrato.ritardi = 0
    WHERE lettoreRegistrato.codice_fiscale = resetRitardi.codice_fiscale;
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

-- Aggiunge un prestito di copia fatto a lettore nella data corrente. Il prestito
-- non viene consentito se lettore ha 5 o più consegne in ritardo, se lettore ha
-- già 3 o 5 prestiti in corso, oppure se copia non è disponibile.
-- Gestisce i requisiti 1, 2 e 3.
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
CREATE OR REPLACE FUNCTION biblioteca.restituisciPrestito(copia biblioteca.copia.id%TYPE)
RETURNS void
AS $$
BEGIN
    UPDATE biblioteca.prestitoCorrente
    SET prestitoCorrente.isCorrente = false,
        prestitoCorrente.fine = current_date
    WHERE prestitoCorrente.copia = copia;
END;
$$
LANGUAGE plpgsql;

-- Proroga del numero di giorni specificato un prestito corrente se non è già in ritardo.
CREATE OR REPLACE FUNCTION biblioteca.prorogaPrestito(copia biblioteca.copia.id%TYPE,
                                                      giorniDiProroga INTEGER)
RETURNS biblioteca.error
AS $$
BEGIN
    IF count(*) = 0
        FROM biblioteca.ritardi
        WHERE ritardi.copia = prorogaPrestito.copia;
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

-- Funzione chiamata dal trigger aggiornaRitardi. Aumenta di 1 il contatore dei
-- ritardi del lettore se questo ha consegnato in ritardo.
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

-- Aggiorna il numero di ritardi di un lettore alla consegna di una copia.
CREATE OR REPLACE TRIGGER aggiornaRitardi
AFTER UPDATE ON biblioteca.prestitoCorrente
FOR EACH ROW
EXECUTE FUNCTION biblioteca.aggiornaRitardi();
