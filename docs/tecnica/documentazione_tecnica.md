# Documentazione Tecnica

### Materiale
La cartella `docs/tecnica` contiene tutto il materiale riguardante la documentazione tecnica:

```
docs/tecnica
 |- html                            documentazione delle funzioni del backend (doxygen)
 |- documentazione_tecnica.md       questo file
 |- prova_di_funzionamento.webm     video che mostra una demo della piattaforma
 |- schema_concettuale.svg          schema er
 `- schema_logico.svg               schema er ristrutturato + schema logico
```

### Funzioni
Le funzioni del database sono wrappate nel backend in PHP. Poichè doxygen non supporta SQL, la versione html della documentazione è disponibile soltanto per il backend. Tuttavia nel codice sono documentate anche le funzioni del database (`postgres/funzioni.sql`).

Per generare la documentazione è sufficiente eseguire, nella root del progetto
```
doxygen
```
la documentazione sarà disponibile nella cartella `docs/tecnica/html`.
