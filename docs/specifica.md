# Documentazione tecnica

## Autore
```
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
```
### Funzioni associate
 - `aggiungiAutore(nome, cognome, data di nascita, data di morte, biografia)`
 - `setAutoreDataMorte(id, dataMorte)`
 - `rimuoviAutore(id)`: setta `isValido = false`. Nessun autore viene mai elminato affinchè lo storico dei prestiti possa rimanere sempre consultabile. L'operazione fallisce se ci sono libri validi associati.

## Libro
```
CREATE TABLE biblioteca.libro (
    isbn biblioteca.isbn PRIMARY KEY,
    titolo VARCHAR(128) NOT NULL,
    trama text NOT NULL,
    casa_editrice VARCHAR(128) NOT NULL,
    isValido BOOLEAN NOT NULL DEFAULT true
);
```
### Funzioni associate
 - `aggiungiLibro(isbn, titolo, trama, casa editrice, autori[])`
 - `rimuoviLibro(isbn)`: setta `isValido = false`. Nessun libro viene mai elminato affinchè lo storico dei prestiti possa rimanere sempre consultabile. L'operazione fallisce se ci sono copie valide associate.

## Sede
```
CREATE TABLE biblioteca.sede (
    id SERIAL PRIMARY KEY,
    indirizzo VARCHAR(128) NOT NULL,
    città VARCHAR(64) NOT NULL,
    isOperativa BOOLEAN NOT NULL DEFAULT true
);
```
### Funzioni associate
 - `aggiungiSede(indirizzo, città)`
 - `getNumeroCopieGestite(id)`
 - `getNumeroIsbnGestiti(id)`
 - `getNumeroPrestitiInCorso(id)`
 - `getRitardi(id)`: genera un report dei ritardi
 - `rimuoviSede(id):` setta `isOperativa = false`. Nessuna sede viene mai eliminata affinchè lo storico dei prestiti possa rimanere sempre consultabile. L'operazione fallisce se ci sono copie valide associate.

## Copia
```
CREATE TABLE biblioteca.copia (
    id SERIAL PRIMARY KEY,
    libro biblioteca.isbn NOT NULL REFERENCES biblioteca.libro(isbn) ON DELETE RESTRICT,
    sede INTEGER NOT NULL REFERENCES biblioteca.sede(id) ON DELETE RESTRICT,
    isValida BOOLEAN NOT NULL DEFAULT true
);
```
### Funzioni associate
 - `aggiungiCopia(isbn, sede)`
 - `trovaCopiaDisponibile(libro, sede)`: restituisce una copia disponibile di un libro in un data sede (opzionale).
 - `cambiaSede(id, sede)`: assegna una copia ad un'altra sede. Se la copia è in prestito l'operazione fallisce.
 - `rimuoviCopia(id)`: setta `isValida = false`. Nessuna copia viene mai elminata affinchè lo storico dei prestiti possa rimanere sempre consultabile. L'operazione fallisce se la copia è in prestito.

## Bibliotecario
```
CREATE TABLE biblioteca.bibliotecario (
    id SERIAL PRIMARY KEY,
    email biblioteca.email UNIQUE NOT NULL,
    hash CHAR(64) NOT NULL,
    salt CHAR(8) NOT NULL
);
```
### Funzioni associate
 - `aggiungiBibliotecario(email, hash, salt)`
 - `cambiaBibliotecarioEmail(id, email)`
 - `cambiaBibliotecarioPassword(id, oldHash, newHash, newSalt)`
 - `rimuoviBibliotecario(id)`

## Lettore
```
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
```
### Funzioni associate
 - `aggiungiLettore(nome, cognome, hash, salt, email, categoria, codice fiscale)`
 - `cambiaLettoreEmail(codice_fiscale, email)`
 - `cambiaLettorePassword(codice_fiscale, oldHash, newHash, newSalt)`
 - `cambiaCategoria(codice_fiscale, categoria)`
 - `resetRitardi(codice_fiscale)`
 - `rimuoviLettore(codice_fiscale)`: setta `isRegistrato = false`. Nessun lettore viene mai elminato affinchè lo storico dei prestiti possa rimanere sempre consultabile. L'operazione fallisce se il lettore ha un prestito in corso.

## Prestito
```
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
```
### Funzioni associate
 - `richiediPrestito(copia, lettore)`
 - `restituisciPrestito(copia)`
 - `prorogaPrestito(copia, giorniDiProroga)`
### Trigger associati
 - `aggiornaRitardi`: se necessario incrementa il numero di volumi consegnati in ritardo dal lettore.
