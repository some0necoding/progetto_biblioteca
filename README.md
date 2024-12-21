# Biblioteca

Implementazione del database di una biblioteca e degli strumenti necessari per accedervi.

# Struttura del database
##### Schema ER
<h1 align="center">
    <img width="500" alt="schema ER" src="https://github.com/some0necoding/progetto_biblioteca/blob/main/docs/tecnica/schema_concettuale.svg">
</h1>

##### Schema ER Ristrutturato e Schema Logico
<h1 align="center">
    <img width="500" alt="schema logico" src="https://github.com/some0necoding/progetto_biblioteca/blob/main/docs/tecnica/schema_logico.svg">
</h1>

### Struttura del progetto
Il progetto è strutturato come segue

```
.
 |- app                             codice php che include backend e frontend
 |  |- public                       pagine esposte dal server
 |  `- src                          codice php
 |- docs                            documentazione
 |  |- tecnica
 |  `- utente
 |- nginx                           configurazione del server nginx
 |- postgres                        database
 |  |- tables.sql
 |  |- functions.sql
 |  |- users.sql                    utenti preregistrati
 |  `- samples.sql                  dati di esempio già inseriti nel db
 |- compose-prod.yml                compose file production environment
 |- compose.yml                     compose file development environment
 |- Doxyfile                        configurazione di doxygen
 `- README.md                       questo file
```

Si faccia riferimento alla documentazione tecnica (`docs/tecnica`) e utente (`docs/utente`) per maggiori dettagli.
