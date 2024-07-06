CREATE SCHEMA biblioteca;

CREATE TABLE biblioteca.autore (
    id SERIAL PRIMARY KEY,
    nome VARCHAR(64) NOT NULL,
    cognome VARCHAR(64) NOT NULL,
    data_di_nascita DATE NOT NULL,
    data_di_morte DATE,
    biografia text NOT NULL,
    isValido BOOLEAN NOT NULL DEFAULT true,
    CHECK (data_di_morte > data_di_nascita)
);

CREATE DOMAIN biblioteca.isbn AS CHAR(13)
CHECK (
    VALUE ~ '[0-9]{13}'
);

CREATE TABLE biblioteca.libro (
    isbn biblioteca.isbn PRIMARY KEY,
    titolo VARCHAR(128) NOT NULL,
    trama text NOT NULL,
    casa_editrice VARCHAR(128) NOT NULL,
    isValido BOOLEAN NOT NULL DEFAULT true
);

CREATE TABLE biblioteca.sede (
    id SERIAL PRIMARY KEY,
    indirizzo VARCHAR(128) NOT NULL,
    città VARCHAR(64) NOT NULL,
    isOperativa BOOLEAN NOT NULL DEFAULT true
);

CREATE TABLE biblioteca.copia (
    id SERIAL PRIMARY KEY,
    libro biblioteca.isbn NOT NULL REFERENCES biblioteca.libro(isbn) ON DELETE CASCADE,
    sede INTEGER NOT NULL REFERENCES biblioteca.sede(id) ON DELETE RESTRICT,
    isValida BOOLEAN NOT NULL DEFAULT true
);

-- regex credits https://stackoverflow.com/questions/201323/how-can-i-validate-an-email-address-using-a-regular-expression#201378
CREATE DOMAIN biblioteca.email AS VARCHAR(128)
CHECK (
    VALUE ~ $$(?:[a-z0-9!#$%&'*+/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&'*+/=?^_`{|}~-]+)*|"(?:[\x01-\x08\x0b\x0c\x0e-\x1f\x21\x23-\x5b\x5d-\x7f]|\\[\x01-\x09\x0b\x0c\x0e-\x7f])*")@(?:(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])?|\[(?:(?:(2(5[0-5]|[0-4][0-9])|1[0-9][0-9]|[1-9]?[0-9]))\.){3}(?:(2(5[0-5]|[0-4][0-9])|1[0-9][0-9]|[1-9]?[0-9])|[a-z0-9-]*[a-z0-9]:(?:[\x01-\x08\x0b\x0c\x0e-\x1f\x21-\x5a\x53-\x7f]|\\[\x01-\x09\x0b\x0c\x0e-\x7f])+)\])$$
);

CREATE TABLE biblioteca.bibliotecario (
    id SERIAL PRIMARY KEY,
    email biblioteca.email UNIQUE NOT NULL,
    hash CHAR(64) NOT NULL,
    salt CHAR(8) NOT NULL
);

CREATE DOMAIN biblioteca.categoria AS VARCHAR(8)
CHECK (
    VALUE = 'base' OR VALUE = 'premium'
);

-- regex credits http://blog.marketto.it/2016/01/regex-validazione-codice-fiscale-con-omocodia/
CREATE DOMAIN biblioteca.codice_fiscale AS CHAR(16)
CHECK (
    VALUE ~ $$/^(?:[A-Z][AEIOU][AEIOUX]|[AEIOU]X{2}|[B-DF-HJ-NP-TV-Z]{2}[A-Z]){2}(?:[\dLMNP-V]{2}(?:[A-EHLMPR-T](?:[04LQ][1-9MNP-V]|[15MR][\dLMNP-V]|[26NS][0-8LMNP-U])|[DHPS][37PT][0L]|[ACELMRT][37PT][01LM]|[AC-EHLMPR-T][26NS][9V])|(?:[02468LNQSU][048LQU]|[13579MPRTV][26NS])B[26NS][9V])(?:[A-MZ][1-9MNP-V][\dLMNP-V]{2}|[A-M][0L](?:[1-9MNP-V][\dLMNP-V]|[0L][1-9MNP-V]))[A-Z]$/i$$
);

CREATE TABLE biblioteca.lettore (
    codice_fiscale biblioteca.codice_fiscale PRIMARY KEY,
    nome VARCHAR(64) NOT NULL,
    cognome VARCHAR(64) NOT NULL,
    email biblioteca.email UNIQUE NOT NULL,
    hash CHAR(64) NOT NULL,
    salt CHAR(8) NOT NULL,
    categoria biblioteca.categoria NOT NULL,
    ritardi INTEGER NOT NULL,
    isRegistrato BOOLEAN NOT NULL DEFAULT true
);

CREATE TABLE biblioteca.prestito (
    inizio DATE,
    copia INTEGER NOT NULL REFERENCES biblioteca.copia(id) ON DELETE RESTRICT,
    lettore biblioteca.codice_fiscale NOT NULL REFERENCES biblioteca.lettore(codice_fiscale) ON DELETE RESTRICT,
    isCorrente BOOLEAN NOT NULL DEFAULT true,
    scadenza DATE NOT NULL,
    fine DATE,
    CONSTRAINT valida_scadenza CHECK(scadenza > inizio),
    CONSTRAINT valida_restituzione CHECK (
        fine >= inizio AND
        (
            (    isCorrente AND fine IS NULL) 
            OR 
            (NOT isCorrente AND fine IS NOT NULL)
        )
    ),
    PRIMARY KEY (inizio, copia, lettore)
);

CREATE TABLE biblioteca.scritto (
    autore INTEGER NOT NULL REFERENCES biblioteca.autore(id) ON DELETE RESTRICT,
    libro biblioteca.isbn NOT NULL REFERENCES biblioteca.libro(isbn) ON DELETE CASCADE,
    PRIMARY KEY (autore, libro)
);
