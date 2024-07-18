-- AUTORI --

INSERT INTO biblioteca.autore (id, nome, cognome, data_di_nascita, data_di_morte, biografia)
VALUES (
    1,
    'Ernest',
    'Hemingway',
    '1899-07-21',
    '1961-07-02',
    'Scrittore e giornalista statunitense che fu autore di romanzi e di racconti. Tra i suoi libri più noti si ricorda "Il vecchio e il mare".'
);

INSERT INTO biblioteca.autore (id, nome, cognome, data_di_nascita, data_di_morte, biografia)
VALUES (
    2,
    'Karl',
    'Marx',
    '1818-05-05',
    '1883-03-14',
    'Filosofo, economista, storico, sociologo e giornalista tedesco. Padre del socialismo scientifico e del comunismo moderno.'
);

INSERT INTO biblioteca.autore (id, nome, cognome, data_di_nascita, data_di_morte, biografia)
VALUES (
    3,
    'Friedrich',
    'Engels',
    '1820-11-28',
    '1895-08-05',
    'Filosofo, economista, sociologo, giornalista e rivoluzionario tedesco. Amico e collaboratore di Karl Marx.'
);

INSERT INTO biblioteca.autore (id, nome, cognome, data_di_nascita, biografia)
VALUES (
    4,
    'Alessandro',
    'Baricco',
    '1958-01-25',
    'Scrittore, saggista, giornalista e musicologo italiano. Tra le sue opere più note si ricordano "Oceano mare" e "Seta".'
);

INSERT INTO biblioteca.autore (id, nome, cognome, data_di_nascita, biografia)
VALUES (
    5,
    'Stefano',
    'Benni',
    '1947-08-12',
    'Scrittore, poeta, drammaturgo, sceneggiatore e giornalista italiano. Tra le sue opere più note si ricordano "Bar Sport" e "Elianto".'
);

-- LIBRI --

SELECT biblioteca.aggiungiLibro(
    '9788804668343',
    'Per chi suona la campana',
    'Per chi suona la campana è un romanzo dello scrittore statunitense Ernest Hemingway, pubblicato nel 1940. Il titolo del romanzo è tratto da un verso del poeta inglese John Donne.',
    'Mondadori',
    '{1}'
);

SELECT biblioteca.aggiungiLibro(
    '9788807902840',
    'Manifesto del Partito Comunista',
    'Il Manifesto del Partito Comunista è un saggio del 1848 scritto dai teorici e politici tedeschi Karl Marx e Friedrich Engels. Il saggio presenta un approccio analitico alla lotta di classe e ai problemi del capitalismo.',
    'Mondadori',
    '{2, 3}'
);

SELECT biblioteca.aggiungiLibro(
    '9788807880896',
    'Seta',
    'Hervé Joncour, negoziante francese di bachi da seta, è costretto a recarsi in Giappone per comprarne le uova. È accolto al palazzo reale di Hara Kei, un uomo enigmatico, che è sempre in compagnia di una giovane ragazza...',
    'Mondadori',
    '{4}'
);

SELECT biblioteca.aggiungiLibro(
    '9788807884627',
    'Bar Sport',
    'Bar Sport è il primo libro di Stefano Benni. Descrive in modo surreale la realtà dei bar italiani, soprattutto quelli di provincia. Sebbene sia stato pubblicato nel 1976, molte situazioni narrate sono ancora attuali.',
    'Feltrinelli',
    '{5}'
);

-- SEDI --

INSERT INTO biblioteca.sede (id, nome, indirizzo)
VALUES (
    1,
    'Via Celoria, 18',
    'Milano'
);

INSERT INTO biblioteca.sede (id, nome, indirizzo)
VALUES (
    2,
    'Corso di Porta Vittoria, 6',
    'Milano'
);

-- COPIE --

INSERT INTO biblioteca.copia (libro, sede)
VALUES (
    '9788804668343',
    1
);

INSERT INTO biblioteca.copia (libro, sede)
VALUES (
    '9788804668343',
    1
);

INSERT INTO biblioteca.copia (libro, sede)
VALUES (
    '9788807902840',
    2
);
