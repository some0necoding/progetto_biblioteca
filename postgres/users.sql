-- Inserisce l'utente amministratore
--
-- Email:    admin@biblioteca.org
-- Password: admin
--
INSERT INTO biblioteca.bibliotecario (email, hash)
VALUES (
    'admin@biblioteca.org',
    '$2y$10$WpQPcrKRbmwl3cJA1Sx0p.Au63M.ycTp5vWT9sqC5gxobrI/UQasq'
);

-- Inserisce un lettore
--
-- Email: lettore@biblioteca.org
-- Password: lettore
--
INSERT INTO biblioteca.lettore (codice_fiscale, nome, cognome, email, hash, categoria)
VALUES (
    'MNTMRC03P04I577Y',
    'Marco',
    'Montali',
    'mmontali@gmail.com',
    '$2y$10$x0v8KhLiEa/2ZYXCsFG.9enXcjZaJBWsrb/CMZ.d./OpT6t4cWGb6',
    'base'
);
