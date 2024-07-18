# Entità Principali

## Autore
```
CREATE TABLE biblioteca.autore (
    id SERIAL PRIMARY KEY,
    nome VARCHAR(64) NOT NULL,
    cognome VARCHAR(64) NOT NULL,
    data_di_nascita DATE NOT NULL,
    data_di_morte DATE,
    biografia text NOT NULL,
    CHECK (data_di_morte > data_di_nascita)
);
```
### Funzioni associate
 - `aggiungiAutore(nome, cognome, data di nascita, data di morte, biografia)`
 - `getAutori()`
 - `getAutoreById(id)`
 - `getAutoriByIsbn(isbn)`
 - `setAutoreDataMorte(id, dataMorte)`
 - `rimuoviAutore(id)`

## Libro
```
CREATE TABLE biblioteca.libro (
    isbn biblioteca.isbn PRIMARY KEY,
    titolo VARCHAR(128) NOT NULL,
    trama text NOT NULL,
    casa_editrice VARCHAR(128) NOT NULL,
    isDisponibile BOOLEAN NOT NULL DEFAULT true
);
```
### Funzioni associate
 - `aggiungiLibro(isbn, titolo, trama, casa editrice, autori[])`
 - `getLibri()`
 - `getLibroByIsbn(isbn)`
 - `rimuoviLibro(isbn)`

## Sede
```
CREATE TABLE biblioteca.sede (
    id SERIAL PRIMARY KEY,
    indirizzo VARCHAR(128) NOT NULL,
    città VARCHAR(64) NOT NULL,
    copie_gestite INTEGER NOT NULL DEFAULT 0,
    isbn_gestiti INTEGER NOT NULL DEFAULT 0,
    prestiti_in_corso INTEGER NOT NULL DEFAULT 0
);
```
### Funzioni associate
 - `aggiungiSede(indirizzo, città)`
 - `getSedi()`
 - `getSedeById(id)`
 - `getRitardi(id)`: genera un report dei ritardi
 - `rimuoviSede(id)`

## Copia
```
CREATE TABLE biblioteca.copia (
    id SERIAL PRIMARY KEY,
    libro biblioteca.isbn NOT NULL REFERENCES biblioteca.libro(isbn) ON DELETE RESTRICT,
    sede INTEGER NOT NULL REFERENCES biblioteca.sede(id) ON DELETE RESTRICT,
    isDisponibile BOOLEAN NOT NULL DEFAULT true
);
```
### Funzioni associate
 - `aggiungiCopia(isbn, sede)`
 - `getCopie()`
 - `getCopiaById(id)`
 - `getCopieBySede(sede)`
 - `setSede(id, sede)`
 - `rimuoviCopia(id)`

### Trigger associati
 - `aggiornaSede_AI`: incrementa il numero di copie e (se necessario) di libri gestiti dalla sede.
 - `aggiornaSede_AD`: decrementa il numero di copie e (se necessario) di libri gestiti dalla sede.
 - `aggiornaSede_AU`: decrementa e incrementa il numero di copie e (se necessario) di libri gestiti dalle sedi.

## Lettore
```
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
```
### Funzioni associate
 - `aggiungiLettore(nome, cognome, hash, salt, email, categoria, codice fiscale)`
 - `getLettori()`
 - `getLettoreByCodiceFiscale(codice_fiscale)`
 - `setLettoreEmail(codice_fiscale, email)`
 - `setLettorePassword(codice_fiscale, newHash)`
 - `setLettoreCategoria(codice_fiscale, categoria)`
 - `azzeraRitardi(codice_fiscale)`
 - `rimuoviLettore(codice_fiscale)`

## Prestito
```
CREATE TABLE biblioteca.prestito (
    inizio DATE DEFAULT CURRENT_DATE,
    copia INTEGER NOT NULL REFERENCES biblioteca.copia(id) ON DELETE RESTRICT,
    lettore biblioteca.codice_fiscale NOT NULL REFERENCES biblioteca.lettore(codice_fiscale) ON DELETE RESTRICT,
    scadenza DATE NOT NULL DEFAULT CURRENT_DATE + 30,
    CONSTRAINT valida_scadenza CHECK(scadenza > inizio),
    PRIMARY KEY (inizio, copia, lettore)
);
```
### Funzioni associate
 - `richiediPrestito(copia, lettore)`
 - `getPrestiti()`
 - `getPrestitoByCopia(copia)`
 - `restituisciPrestito(copia)`
 - `prorogaPrestito(copia, giorniDiProroga)`

### Trigger associati
 - `aggiornaPrestiti_AI`: incrementa il numero di prestiti in corso per la sede e il lettore
 - `aggiornaPrestiti_AD`: decrementa il numero di prestiti in corso per la sede e il lettore
 - `aggiornaRitardi_AD`: incrementa (se necessario) il numero di ritardi per il lettore
 - `aggiornaDisponibilità_AI`: revoca la disponibilità della copia presa in prestito e (se necessario) del libro associato
 - `aggiornaDisponibilità_AD`: ripristina la disponibilità della copia presa in prestito e (se necessario) del libro associato

## Bibliotecario
```
CREATE TABLE biblioteca.bibliotecario (
    id SERIAL PRIMARY KEY,
    email biblioteca.email UNIQUE NOT NULL,
    hash VARCHAR(255) NOT NULL
);
```
### Funzioni associate
 - `aggiungiBibliotecario(email, hash)`
 - `getBibliotecari()`
 - `getBibliotecarioById(id)`
 - `setBibliotecarioEmail(id, email)`
 - `setBibliotecarioPassword(id, newHash)`
 - `rimuoviBibliotecario(id)`
