DROP SCHEMA IF EXISTS biblioteca CASCADE;

CREATE SCHEMA biblioteca;

CREATE TABLE biblioteca.autore (
    id SERIAL PRIMARY KEY,
    nome VARCHAR(64) NOT NULL,
    cognome VARCHAR(64) NOT NULL,
    data_di_nascita DATE NOT NULL,
    data_di_morte DATE,
    biografia text NOT NULL,
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
    isDisponibile BOOLEAN NOT NULL DEFAULT true
);

CREATE TABLE biblioteca.scritto (
    autore INTEGER NOT NULL REFERENCES biblioteca.autore(id) ON DELETE RESTRICT,
    libro biblioteca.isbn NOT NULL REFERENCES biblioteca.libro(isbn) ON DELETE CASCADE,
    PRIMARY KEY (autore, libro)
);

CREATE TABLE biblioteca.sede (
    id SERIAL PRIMARY KEY,
    indirizzo VARCHAR(128) NOT NULL,
    città VARCHAR(64) NOT NULL,
    copie_gestite INTEGER NOT NULL DEFAULT 0,
    isbn_gestiti INTEGER NOT NULL DEFAULT 0,
    prestiti_in_corso INTEGER NOT NULL DEFAULT 0
);

CREATE TABLE biblioteca.copia (
    id SERIAL PRIMARY KEY,
    libro biblioteca.isbn NOT NULL REFERENCES biblioteca.libro(isbn) ON DELETE RESTRICT,
    sede INTEGER NOT NULL REFERENCES biblioteca.sede(id) ON DELETE RESTRICT,
    isDisponibile BOOLEAN NOT NULL DEFAULT true
);

CREATE TABLE biblioteca.restituzione (
    copia INTEGER NOT NULL REFERENCES biblioteca.copia(id) ON DELETE CASCADE,
    data TIMESTAMP PRIMARY KEY DEFAULT CURRENT_TIMESTAMP
);

-- regex credits https://stackoverflow.com/questions/201323/how-can-i-validate-an-email-address-using-a-regular-expression#201378
CREATE DOMAIN biblioteca.email AS VARCHAR(128)
CHECK (
    VALUE ~ $$(?:[a-z0-9!#$%&'*+/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&'*+/=?^_`{|}~-]+)*|"(?:[\x01-\x08\x0b\x0c\x0e-\x1f\x21\x23-\x5b\x5d-\x7f]|\\[\x01-\x09\x0b\x0c\x0e-\x7f])*")@(?:(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])?|\[(?:(?:(2(5[0-5]|[0-4][0-9])|1[0-9][0-9]|[1-9]?[0-9]))\.){3}(?:(2(5[0-5]|[0-4][0-9])|1[0-9][0-9]|[1-9]?[0-9])|[a-z0-9-]*[a-z0-9]:(?:[\x01-\x08\x0b\x0c\x0e-\x1f\x21-\x5a\x53-\x7f]|\\[\x01-\x09\x0b\x0c\x0e-\x7f])+)\])$$
);

CREATE DOMAIN biblioteca.categoria AS VARCHAR(8)
CHECK (
    VALUE = 'base' OR VALUE = 'premium'
);

-- regex credits http://blog.marketto.it/2016/01/regex-validazione-codice-fiscale-con-omocodia/
CREATE DOMAIN biblioteca.codice_fiscale AS CHAR(16)
CHECK (
    VALUE ~ $regex$^(?:[A-Z][AEIOU][AEIOUX]|[AEIOU]X{2}|[B-DF-HJ-NP-TV-Z]{2}[A-Z]){2}(?:[\dLMNP-V]{2}(?:[A-EHLMPR-T](?:[04LQ][1-9MNP-V]|[15MR][\dLMNP-V]|[26NS][0-8LMNP-U])|[DHPS][37PT][0L]|[ACELMRT][37PT][01LM]|[AC-EHLMPR-T][26NS][9V])|(?:[02468LNQSU][048LQU]|[13579MPRTV][26NS])B[26NS][9V])(?:[A-MZ][1-9MNP-V][\dLMNP-V]{2}|[A-M][0L](?:[1-9MNP-V][\dLMNP-V]|[0L][1-9MNP-V]))[A-Z]$$regex$
);

CREATE TABLE biblioteca.lettore (
    codice_fiscale biblioteca.codice_fiscale PRIMARY KEY,
    nome VARCHAR(64) NOT NULL,
    cognome VARCHAR(64) NOT NULL,
    email biblioteca.email UNIQUE NOT NULL,
    hash VARCHAR(255) NOT NULL,
    categoria biblioteca.categoria NOT NULL,
    ritardi INTEGER NOT NULL DEFAULT 0,
    prestiti_in_corso INTEGER NOT NULL DEFAULT 0
);

CREATE TABLE biblioteca.prestito (
    inizio DATE DEFAULT CURRENT_DATE,
    copia INTEGER PRIMARY KEY REFERENCES biblioteca.copia(id) ON DELETE RESTRICT,
    lettore biblioteca.codice_fiscale REFERENCES biblioteca.lettore(codice_fiscale) ON DELETE RESTRICT,
    scadenza DATE NOT NULL DEFAULT CURRENT_DATE + 30,
    CONSTRAINT valida_scadenza CHECK(scadenza > inizio)
);

CREATE TABLE biblioteca.bibliotecario (
    id SERIAL PRIMARY KEY,
    email biblioteca.email UNIQUE NOT NULL,
    hash VARCHAR(255) NOT NULL
);
