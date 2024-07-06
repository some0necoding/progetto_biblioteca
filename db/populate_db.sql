-- Restituisce l'id di un autore dato
CREATE OR REPLACE FUNCTION biblioteca.getAutoreId(nome biblioteca.autore.nome%TYPE,
                                                  cognome biblioteca.autore.cognome%TYPE)
RETURNS biblioteca.autore.id%TYPE
AS $$
DECLARE
    autore_id biblioteca.autore.id%TYPE;
BEGIN
    SELECT biblioteca.autore.id INTO autore_id
    FROM biblioteca.autore
    WHERE biblioteca.autore.nome ilike getAutoreId.nome AND
          biblioteca.autore.cognome ilike getAutoreId.cognome;
    RETURN autore_id;
END;
$$
LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION biblioteca.getLibroIsbn(titolo biblioteca.libro.titolo%TYPE)
RETURNS biblioteca.libro.isbn%TYPE
AS $$
DECLARE
    libro biblioteca.libro.isbn%TYPE;
BEGIN
    SELECT biblioteca.libro.isbn INTO libro
    FROM biblioteca.libro
    WHERE biblioteca.libro.titolo ilike getLibroIsbn.titolo;
    RETURN libro;
END;
$$
LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION biblioteca.getSedeId(indirizzo biblioteca.sede.indirizzo%TYPE,
                                                città biblioteca.sede.città%TYPE)
RETURNS biblioteca.sede.id%TYPE
AS $$
DECLARE
    sede biblioteca.sede.id%TYPE;
BEGIN
    SELECT biblioteca.sede.id INTO sede
    FROM biblioteca.sede
    WHERE biblioteca.sede.indirizzo ilike getSedeId.indirizzo AND
          biblioteca.sede.città ilike getSedeId.città;
    RETURN sede;
END
$$
LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION biblioteca.getBibliotecarioId(email biblioteca.bibliotecario.email%TYPE)
RETURNS biblioteca.bibliotecario.id%TYPE
AS $$
DECLARE
    bibliotecario biblioteca.bibliotecario.id%TYPE;
BEGIN
    SELECT bibliotecario.id INTO bibliotecario
    FROM biblioteca.bibliotecario
    WHERE bibliotecario.email = getBibliotecarioId.email;
    RETURN bibliotecario;
END;
$$
LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION biblioteca.getLettoreId(email biblioteca.lettore.email%TYPE)
RETURNS biblioteca.lettore.id%TYPE
AS $$
DECLARE
    lettore biblioteca.lettore.id%TYPE;
BEGIN
    SELECT lettore.id INTO lettore
    FROM biblioteca.lettore
    WHERE lettore.email = getLettoreId.email;
    RETURN lettore;
END;
$$
LANGUAGE plpgsql;

-- AUTORI --

SELECT biblioteca.aggiungiAutore('Jane', 'Austen', 'Jane Austen è stata una scrittrice inglese, conosciuta per i suoi romanzi che esplorano il comportamento delle donne dell’epoca.', '16-12-1775', '18-07-1817');

SELECT biblioteca.aggiungiAutore('George', 'Orwell', 'George Orwell è stato uno scrittore e giornalista britannico, noto per i suoi romanzi "1984" e "La fattoria degli animali".', '25-06-1903', '21-01-1950');

SELECT biblioteca.aggiungiAutore('Lev', 'Tolstoy', 'Lev Tolstoy è stato uno scrittore russo, noto per i suoi romanzi "Guerra e pace" e "Anna Karenina".', '09-09-1828', '20-11-1910');

SELECT biblioteca.aggiungiAutore('William', 'Shakespeare', 'William Shakespeare è stato un drammaturgo, poeta e attore inglese, considerato uno dei più grandi scrittori di lingua inglese.', '1564-04-23', '1616-04-23');

SELECT biblioteca.aggiungiAutore('Charles', 'Dickens', 'Charles Dickens è stato uno scrittore e critico sociale inglese, noto per i suoi romanzi come "Oliver Twist" e "Canto di Natale".', '1812-02-07', '1870-06-09');

SELECT biblioteca.aggiungiAutore('Fyodor', 'Dostoevsky', 'Fyodor Dostoevsky è stato uno scrittore russo, autore di classici come "Delitto e castigo" e "I fratelli Karamazov".', '1821-11-11', '1881-02-09');

SELECT biblioteca.aggiungiAutore('Herman', 'Melville', 'Herman Melville è stato uno scrittore americano, noto per il suo romanzo "Moby Dick".', '1819-08-01', '1891-09-28');

SELECT biblioteca.aggiungiAutore('Mark', 'Twain', 'Mark Twain è stato uno scrittore e umorista americano, famoso per "Le avventure di Tom Sawyer" e "Le avventure di Huckleberry Finn".', '1835-11-30', '1910-04-21');

SELECT biblioteca.aggiungiAutore('Ernest', 'Hemingway', 'Ernest Hemingway è stato uno scrittore e giornalista americano, vincitore del Premio Nobel per la letteratura nel 1954.', '1899-07-21', '1961-07-02');

SELECT biblioteca.aggiungiAutore('James', 'Joyce', 'James Joyce è stato uno scrittore e poeta irlandese, noto per il suo romanzo "Ulisse".', '1882-02-02', '1941-01-13');

SELECT biblioteca.aggiungiAutore('F. Scott', 'Fitzgerald', 'F. Scott Fitzgerald è stato uno scrittore americano, noto per il suo romanzo "Il grande Gatsby".', '1896-09-24', '1940-12-21');

SELECT biblioteca.aggiungiAutore('J.R.R.', 'Tolkien', 'J.R.R. Tolkien è stato uno scrittore e filologo britannico, famoso per la trilogia "Il Signore degli Anelli".', '1892-01-03', '1973-09-02');

SELECT biblioteca.aggiungiAutore('Isaac', 'Asimov', 'Isaac Asimov è stato uno scrittore e biochimico americano, noto per i suoi romanzi di fantascienza e divulgazione scientifica.', '1920-01-02', '1992-04-06');

-- LIBRI --

SELECT biblioteca.aggiungiLibro('9788807900242', 'Orgoglio e pregiudizio',
		'La storia di Elizabeth Bennet e Mr. Darcy, un classico della letteratura romantica.',
		'Einaudi',
        getAutoreId('Jane', 'Austen'));

SELECT biblioteca.aggiungiLibro('9788807900044', '1984',
		'Un romanzo distopico che esplora un futuro totalitario e la soppressione della libertà individuale.',
		'Einaudi',
        getAutoreId('George', 'Orwell'));

SELECT biblioteca.aggiungiLibro('9788807901515', 'La fattoria degli animali',
		'Una satira della dittatura stalinista ambientata in una fattoria dove gli animali si ribellano agli umani.',
		'Mondadori',
        getAutoreId('George', 'Orwell'));

SELECT biblioteca.aggiungiLibro('9788807900303', 'Anna Karenina',
		$$La tragica storia d'amore di Anna Karenina e il conte Vronskij, ambientata nella Russia zarista.$$,
		'Mondadori',
        getAutoreId('Lev', 'Tolstoy'));

SELECT biblioteca.aggiungiLibro('9788807900440', 'Romeo e Giulietta',
		$$La tragica storia d'amore di Romeo e Giulietta, due giovani amanti di Verona.$$,
		'Feltrinelli',
        getAutoreId('William', 'Shakespeare'));

SELECT biblioteca.aggiungiLibro('9788807901965', 'Oliver Twist',
		'La storia di un orfano che lotta per sopravvivere nelle strade di Londra e si unisce a una banda di giovani ladri.',
		'Einaudi',
        getAutoreId('Charles', 'Dickens'));

SELECT biblioteca.aggiungiLibro('9788807900723', 'Delitto e castigo',
		'La storia di Raskolnikov, un giovane che commette un omicidio e deve affrontare le conseguenze morali.',
		'Einaudi',
        getAutoreId('Fyodor', 'Dostoevsky'));

SELECT biblioteca.aggiungiLibro('9788807900785', 'Moby Dick',
		'La storia della caccia alla balena bianca Moby Dick, guidata dal capitano Achab.',
		'Feltrinelli',
        getAutoreId('Herman', 'Melville'));

SELECT biblioteca.aggiungiLibro('9788807900891', 'Le avventure di Huckleberry Finn',
		'Le avventure di Huck Finn lungo il fiume Mississippi, un classico della letteratura americana.',
		'Mondadori',
        getAutoreId('Mark', 'Twain'));

SELECT biblioteca.aggiungiLibro('9788807900983', 'Il vecchio e il mare',
		'La storia di un vecchio pescatore cubano e la sua epica battaglia con un enorme marlin.',
		'Einaudi',
        getAutoreId('Ernest', 'Hemingway'));

SELECT biblioteca.aggiungiLibro('9788807901065', 'Ulisse',
		'Un romanzo sperimentale che segue le peregrinazioni di Leopold Bloom attraverso Dublino in un solo giorno.',
		'Mondadori',
        getAutoreId('James', 'Joyce'));

SELECT biblioteca.aggiungiLibro('9788807901157', 'Il grande Gatsby',
		'La storia di Jay Gatsby e il suo amore per Daisy Buchanan, ambientata negli anni ruggenti.',
		'Feltrinelli',
        getAutoreId('F. Scott', 'Fitzgerald'));

SELECT biblioteca.aggiungiLibro('9788807901249', 'Lo Hobbit',
		'Le avventure di Bilbo Baggins, un hobbit che parte per un viaggio epico con una compagnia di nani.',
		'Bompiani',
        getAutoreId('J.R.R.', 'Tolkien'));

SELECT biblioteca.aggiungiLibro('9788804665222', 'Io, Robot',
		$$Una raccolta di racconti di Isaac Asimov che esplora le implicazioni etiche e sociali dell'intelligenza artificiale.$$,
		'Mondadori',
        getAutoreId('Isaac', 'Asimov'));

-- SEDI --

SELECT biblioteca.aggiungiSede('Via Giovanni Celoria, 18', 'Milano');
SELECT biblioteca.aggiungiSede('Viale Regina Elena, 295',  'Roma');
SELECT biblioteca.aggiungiSede('Corso Umberto I, 40',      'Napoli');

-- COPIE --

SELECT aggiungiCopia(getIsbnByTitle('1984'),
                      getSedeId('Via Giovanni Celoria, 18', 'Milano'));
SELECT aggiungiCopia(getIsbnByTitle('1984'),
                      getSedeId('Via Giovanni Celoria, 18', 'Milano'));
SELECT aggiungiCopia(getIsbnByTitle('Il vecchio e il mare'),
                      getSedeId('Via Giovanni Celoria, 18', 'Milano'));
SELECT aggiungiCopia(getIsbnByTitle('1984'), 
                      getSedeId('Viale Regina Elena, 295', 'Roma'));
SELECT aggiungiCopia(getIsbnByTitle('1984'), 
                      getSedeId('Corso Umberto I, 40', 'Napoli'));
