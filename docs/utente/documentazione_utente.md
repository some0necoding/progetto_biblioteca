# Documentazione Utente

### Installazione

Il progetto è interamente gestito tramite docker per garantire maggiore semplicità nell'installazione. Eseguendo, nella root del progetto, il comando
```
docker-compose -f compose-prod.yml up -d
```
si mette in ascolto il server sulla porta 8080.

### Primo Login

All'indirizzo `localhost:8080` si raggiunge la pagina di login, il quale può essere eseguito da bibliotecario o da lettore. Per entrambe le categorie di utenti esiste già un account registrato con le seguenti credenziali:

##### Bibliotecario
email: admin&commat;biblioteca&period;org <br>
password: admin

##### Lettore
email: mmontali&commat;gmail&period;com <br>
password: lettore
