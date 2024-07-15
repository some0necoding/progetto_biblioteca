<?php
    include_once '../exceptions.php';
    include_once '../backend.php';
    include_once '../objects.php';

    enum TipoOperazione: int {
        case AGGIUNGI_AUTORE = 1;
        case SET_DATA_DI_MORTE_AUTORE = 2;
        case RIMUOVI_AUTORE = 3;
        case AGGIUNGI_LIBRO = 4;
        case RIMUOVI_LIBRO = 5;
        case AGGIUNGI_SEDE = 6;
        case RIMUOVI_SEDE = 7;
        case AGGIUNGI_COPIA = 8;
        case CAMBIA_SEDE = 9;
        case RIMUOVI_COPIA = 10;
        case AGGIUNGI_BIBLIOTECARIO = 11;
        case CAMBIA_EMAIL_BIBLIOTECARIO = 12;
        case CAMBIA_PASSWORD_BIBLIOTECARIO = 13;
        case RIMUOVI_BIBLIOTECARIO = 14;
        case AGGIUNGI_LETTORE = 15;
        case CAMBIA_EMAIL_LETTORE = 16;
        case CAMBIA_PASSWORD_LETTORE = 17;
        case CAMBIA_CATEGORIA_LETTORE = 18;
        case AZZERA_RITARDI_LETTORE = 19;
        case RIMUOVI_LETTORE = 20;
        case RICHIEDI_PRESTITO = 21;
        case RESTITUISCI_PRESTITO = 22;
        case PROROGA_PRESTITO = 23;
    }

    $operazioni = [
        TipoOperazione::AGGIUNGI_AUTORE               => 'AggiungiAutore',
        TipoOperazione::SET_DATA_DI_MORTE_AUTORE      => 'SetDataDiMorteAutore',
        TipoOperazione::RIMUOVI_AUTORE                => 'RimuoviAutore',
        TipoOperazione::AGGIUNGI_LIBRO                => 'AggiungiLibro',
        TipoOperazione::RIMUOVI_LIBRO                 => 'RimuoviLibro',
        TipoOperazione::AGGIUNGI_SEDE                 => 'AggiungiSede',
        TipoOperazione::RIMUOVI_SEDE                  => 'RimuoviSede',
        TipoOperazione::AGGIUNGI_COPIA                => 'AggiungiCopia',
        TipoOperazione::CAMBIA_SEDE                   => 'CambiaSede',
        TipoOperazione::RIMUOVI_COPIA                 => 'RimuoviCopia',
        TipoOperazione::AGGIUNGI_BIBLIOTECARIO        => 'AggiungiBibliotecario',
        TipoOperazione::CAMBIA_EMAIL_BIBLIOTECARIO    => 'CambiaEmailBibliotecario',
        TipoOperazione::CAMBIA_PASSWORD_BIBLIOTECARIO => 'CambiaPasswordBibliotecario',
        TipoOperazione::RIMUOVI_BIBLIOTECARIO         => 'RimuoviBibliotecario',
        TipoOperazione::AGGIUNGI_LETTORE              => 'AggiungiLettore',
        TipoOperazione::CAMBIA_EMAIL_LETTORE          => 'CambiaEmailLettore',
        TipoOperazione::CAMBIA_PASSWORD_LETTORE       => 'CambiaPasswordLettore',
        TipoOperazione::CAMBIA_CATEGORIA_LETTORE      => 'CambiaCategoriaLettore',
        TipoOperazione::AZZERA_RITARDI_LETTORE        => 'AzzeraRitardiLettore',
        TipoOperazione::RIMUOVI_LETTORE               => 'RimuoviLettore',
        TipoOperazione::RICHIEDI_PRESTITO             => 'RichiediPrestito',
        TipoOperazione::RESTITUISCI_PRESTITO          => 'RestituisciPrestito',
        TipoOperazione::PROROGA_PRESTITO              => 'Proroga'
    ];

    /**
     * Base class utilizzata per offrire un'interfaccia comune a tutte le operazioni.
     */
    abstract class Operazione {

        /** 
         * Array che contiene gli input necessari per l'operazione. Ogni valore 
         * è identificato da una chiave analoga al nome del tag input da cui è
         * stato estratto. Il formato specifico di questo array deve essere 
         * definito dalle classi figlie.
         *
         * Esempio:
         *      <input type="text" name="nome" value="Mario">
         *      <input type="text" name="cognome" value="Rossi">
         *      $inputs = ['nome' => 'Mario', 'cognome' => 'Rossi']
         */
        protected array $inputs;

        /**
         * Array che contiene gli errori dell'operazione. Ogni errore è identificato
         * da una chiave analoga al nome del tag input a cui si riferisce. Il formato
         * specifico di questo array deve essere definito dalle classi figlie.
         * In base settaggio di ogni chiave/errore si possono avere tre casi:
         *  - non settato:                  nessun errore;
         *  - settato a stringa vuota:      evidenzia in rosso, senza messaggio;
         *  - settato a stringa non vuota:  evidenzia in rosso, con messaggio.
         *
         * NOTA: possono esistere errori che non sono associati a nessun tag input: 
         * in questo caso verrà mostrato un messaggio senza evidenziare nessun campo.
         *
         * Esempio:
         *      <input type="text" name="nome">
         *      $errors = ['nome' => 'Il campo nome è obbligatorio']
         */
        protected array $errors;

        /**
         * Costruttore
         * 
         * @param array $inputs gli input necessari per l'operazione (@see $inputs)
         */
        public function __construct(array $inputs) {
            $this->inputs = $inputs;
            $this->errors = [];
        }

        /**
         * Esegue l'operazione e ne restituisce gli errori.
         * 
         * @return array array contenente gli errori dell'operazione (@see $errors)
         */
        abstract public function esegui(): array;
    } 

    /**
     * Operazione di aggiunta di un autore.
     */
    class AggiungiAutore extends Operazione {

        /**
         * Costruttore
         * 
         * @param array $inputs gli input necessari per l'operazione (@see $inputs)
         *                      Formato: [ 'nome', 'cognome', 'biografia', 'data_di_nascita', 'data_di_morte' ]
         */
        public function __construct(array $inputs) {
            parent::__construct($inputs);
        }

        /**
         * Esegue l'operazione e ne restituisce gli errori.
         * 
         * @return array array contenente gli errori dell'operazione (@see $errors)
         *               Formato: [ 'nome', 'cognome', 'biografia', 'data_di_nascita', 'data_di_morte' ]
         */
        public function esegui(): array {
            if (!isset(parent::$inputs['nome']) || empty(parent::$inputs['nome']))
                parent::$errors['nome'] = 'Il nome è obbligatorio';
            if (!isset(parent::$inputs['cognome']) || empty(parent::$inputs['cognome']))
                parent::$errors['cognome'] = 'Il cognome è obbligatorio';
            if (!isset(parent::$inputs['data_di_nascita']) || empty(parent::$inputs['data_di_nascita']))
                parent::$errors['data_di_nascita'] = 'La data di nascita è obbligatoria';

            if (empty(parent::$errors))
                aggiungiAutore(parent::$inputs['nome'],
                               parent::$inputs['cognome'],
                               parent::$inputs['biografia'],
                               parent::$inputs['data_di_nascita'],
                               parent::$inputs['data_di_morte']);

            return parent::$errors;
        } 
    }

    /**
     * Operazione di settaggio della data di morte di un autore.
     */
    class SetDataDiMorteAutore extends Operazione {

        /**
         * Costruttore
         * 
         * @param array $inputs gli input necessari per l'operazione (@see $inputs)
         *                      Formato: [ 'id', 'data_di_morte' ]
         */
        public function __construct(array $inputs) {
            parent::__construct($inputs);
        }

        /**
         * Esegue l'operazione e ne restituisce gli errori.
         * 
         * @return array array contenente gli errori dell'operazione (@see $errors)
         *               Formato: [ 'id', 'data_di_morte' ]
         */
        public function esegui(): array {
            if (!isset(parent::$inputs['id']) || empty(parent::$inputs['id']))
                parent::$errors['id'] = 'L\'id è obbligatorio';
            if (!isset(parent::$inputs['data_di_morte']) || empty(parent::$inputs['data_di_morte']))
                parent::$errors['data_di_morte'] = 'La data di morte è obbligatoria';

            if (empty(parent::$errors))
                setAutoreDataDiMorte(parent::$inputs['id'], parent::$inputs['data_di_morte']);

            return parent::$errors;
        }
    }

    /**
     * Operazione di rimozione di un autore.
     */
    class RimuoviAutore extends Operazione {

        /**
         * Costruttore
         * 
         * @param array $inputs gli input necessari per l'operazione (@see $inputs)
         *                      Formato: [ 'id' ]
         */
        public function __construct(array $inputs) {
            parent::__construct($inputs);
        }

        /**
         * Esegue l'operazione e ne restituisce gli errori.
         * 
         * @return array array contenente gli errori dell'operazione (@see $errors)
         *               Formato: [ 'id' ]
         */
        public function esegui(): array {
            if (!isset(parent::$inputs['id']) || empty(parent::$inputs['id']))
                parent::$errors['id'] = 'L\'id è obbligatorio';

            if (empty(parent::$errors)) {
                try {
                    rimuoviAutore(parent::$inputs['id']);
                } catch (LibriAssociatiAdAutoreException $e) {
                    parent::$errors['message'] = 'Impossibile rimuovere un autore con libri associati';
                }
            }

            return parent::$errors;
        }
    }

    /**
     * Operazione di aggiunta di un libro.
     */
    class AggiungiLibro extends Operazione {

        /**
         * Costruttore
         * 
         * @param array $inputs gli input necessari per l'operazione (@see $inputs)
         *                      Formato: [ 'titolo', 'isbn', 'trama', 'casa_editrice', 'autori' ]
         */
        public function __construct(array $inputs) {
            parent::__construct($inputs);
        }

        /**
         * Esegue l'operazione e ne restituisce gli errori.
         * 
         * @return array array contenente gli errori dell'operazione (@see $errors)
         *               Formato: [ 'titolo', 'isbn', 'trama', 'casa_editrice', 'autori' ]
         */
        public function esegui(): array {
            if (!isset(parent::$inputs['titolo']) || empty(parent::$inputs['titolo']))
                parent::$errors['titolo'] = 'Il titolo è obbligatorio';
            if (!isset(parent::$inputs['isbn']) || empty(parent::$inputs['isbn']))
                parent::$errors['isbn'] = 'L\'isbn è obbligatorio';
            if (!isset(parent::$inputs['trama']))
                parent::$inputs['trama'] = '';
            if (!isset(parent::$inputs['casa_editrice']) || empty(parent::$inputs['casa_editrice']))
                parent::$errors['casa_editrice'] = 'La casa editrice è obbligatoria';
            if (!isset(parent::$inputs['autori']) || empty(parent::$inputs['autori']))
                parent::$errors['autori'] = 'Gli autori sono obbligatori';

            try {
                parent::$inputs['isbn'] = new Isbn(parent::$inputs['isbn']);
            } catch (InvalidIsbnException $e) {
                parent::$errors['isbn'] = 'Isbn non valido';
            }

            if (empty(parent::$errors)) {
                try {
                    aggiungiLibro(parent::$inputs['titolo'],
                                  parent::$inputs['isbn'],
                                  parent::$inputs['trama'],
                                  parent::$inputs['casa_editrice'],
                                  parent::$inputs['autori']);
                } catch (IsbnGiàEsistenteException $e) {
                    parent::$errors['isbn'] = 'Isbn già esistente';
                }
            }

            return parent::$errors;
        }
    }

    /**
     * Operazione di rimozione di un libro.
     */
    class RimuoviLibro extends Operazione {

        /**
         * Costruttore
         * 
         * @param array $inputs gli input necessari per l'operazione (@see $inputs)
         *                      Formato: [ 'isbn' ]
         */
        public function __construct(array $inputs) {
            parent::__construct($inputs);
        }

        /**
         * Esegue l'operazione e ne restituisce gli errori.
         * 
         * @return array array contenente gli errori dell'operazione (@see $errors)
         *               Formato: [ 'isbn' ]
         */
        public function esegui(): array {
            if (!isset(parent::$inputs['isbn']) || empty(parent::$inputs['isbn']))
                parent::$errors['isbn'] = 'L\'isbn è obbligatorio';

            try {
                parent::$inputs['isbn'] = new Isbn(parent::$inputs['isbn']);
            } catch (InvalidIsbnException $e) {
                parent::$errors['isbn'] = 'Isbn non valido';
            }

            if (empty(parent::$errors)) {
                try {
                    rimuoviLibro(parent::$inputs['isbn']);
                } catch (CopieAssociateALibroException $e) {
                    parent::$errors['message'] = 'Impossibile rimuovere un libro con copie associate';
                }
            }

            return parent::$errors;
        }
    }

    /**
     * Operazione di aggiunta di una sede.
     */
    class AggiungiSede extends Operazione {

        /**
         * Costruttore
         * 
         * @param array $inputs gli input necessari per l'operazione (@see $inputs)
         *                      Formato: [ 'città', 'indirizzo' ]
         */
        public function __construct(array $inputs) {
            parent::__construct($inputs);
        }

        /**
         * Esegue l'operazione e ne restituisce gli errori.
         * 
         * @return array array contenente gli errori dell'operazione (@see $errors)
         *               Formato: [ 'città', 'indirizzo' ]
         */
        public function esegui(): array {
            if (!isset(parent::$inputs['città']) || empty(parent::$inputs['città']))
                parent::$errors['città'] = 'La città è obbligatoria';
            if (!isset(parent::$inputs['indirizzo']) || empty(parent::$inputs['indirizzo']))
                parent::$errors['indirizzo'] = 'L\'indirizzo è obbligatorio';

            if (empty(parent::$errors))
                aggiungiSede(parent::$inputs['nome'], parent::$inputs['indirizzo']);

            return parent::$errors;
        }
    }

    /**
     * Operazione di rimozione di una sede.
     */
    class RimuoviSede extends Operazione {

        /**
         * Costruttore
         * 
         * @param array $inputs gli input necessari per l'operazione (@see $inputs)
         *                      Formato: [ 'id' ]
         */
        public function __construct(array $inputs) {
            parent::__construct($inputs);
        }

        /**
         * Esegue l'operazione e ne restituisce gli errori.
         * 
         * @return array array contenente gli errori dell'operazione (@see $errors)
         *               Formato: [ 'id' ]
         */
        public function esegui(): array {
            if (!isset(parent::$inputs['id']) || empty(parent::$inputs['id']))
                parent::$errors['id'] = 'L\'id è obbligatorio';

            if (empty(parent::$errors)) {
                try {
                    rimuoviSede(parent::$inputs['id']);
                } catch (CopieAssociateASedeException $e) {
                    parent::$errors['message'] = 'Impossibile rimuovere una sede con copie associate';
                }
            }

            return parent::$errors;
        }
    }

    /**
     * Operazione di aggiunta di una copia.
     */ 
    class AggiungiCopia extends Operazione {

        /**
         * Costruttore
         * 
         * @param array $inputs gli input necessari per l'operazione (@see $inputs)
         *                      Formato: [ 'libro', 'sede' ]
         */
        public function __construct(array $inputs) {
            parent::__construct($inputs);
        }

        /**
         * Esegue l'operazione e ne restituisce gli errori.
         * 
         * @return array array contenente gli errori dell'operazione (@see $errors)
         *               Formato: [ 'libro', 'sede' ]
         */
        public function esegui(): array {
            if (!isset(parent::$inputs['libro']) || empty(parent::$inputs['libro']))
                parent::$errors['libro'] = 'Il libro è obbligatorio';
            if (!isset(parent::$inputs['sede']) || empty(parent::$inputs['sede']))
                parent::$errors['sede'] = 'La sede è obbligatoria';

            try {
                parent::$inputs['libro'] = new Isbn(parent::$inputs['libro']);
            } catch (InvalidIsbnException $e) {
                parent::$errors['libro'] = 'Isbn non valido';
            }

            if (empty(parent::$errors))
                aggiungiCopia(parent::$inputs['libro'], parent::$inputs['sede']);

            return parent::$errors;
        }
    }

    /**
     * Operazione di cambio di sede di una copia.
     */
    class CambiaSede extends Operazione {

        /**
         * Costruttore
         * 
         * @param array $inputs gli input necessari per l'operazione (@see $inputs)
         *                      Formato: [ 'id', 'sede' ]
         */
        public function __construct(array $inputs) {
            parent::__construct($inputs);
        }

        /**
         * Esegue l'operazione e ne restituisce gli errori.
         * 
         * @return array array contenente gli errori dell'operazione (@see $errors)
         *               Formato: [ 'id', 'sede' ]
         */
        public function esegui(): array {
            if (!isset(parent::$inputs['id']) || empty(parent::$inputs['id']))
                parent::$errors['id'] = 'L\'id è obbligatorio';
            if (!isset(parent::$inputs['sede']) || empty(parent::$inputs['sede']))
                parent::$errors['sede'] = 'La sede è obbligatoria';

            if (empty(parent::$errors)) {
                try {
                    setSede(parent::$inputs['id'], parent::$inputs['sede']);
                } catch (CopiaInPrestitoException $e) {
                    parent::$errors['message'] = 'Impossibile cambiare sede a una copia in prestito';
                }
            }

            return parent::$errors;
        }
    }

    /**
     * Operazione di rimozione di una copia.
     */
    class RimuoviCopia extends Operazione {

        /**
         * Costruttore
         * 
         * @param array $inputs gli input necessari per l'operazione (@see $inputs)
         *                      Formato: [ 'id' ]
         */
        public function __construct(array $inputs) {
            parent::__construct($inputs);
        }

        /**
         * Esegue l'operazione e ne restituisce gli errori.
         * 
         * @return array array contenente gli errori dell'operazione (@see $errors)
         *               Formato: [ 'id' ]
         */
        public function esegui(): array {
            if (!isset(parent::$inputs['id']) || empty(parent::$inputs['id']))
                parent::$errors['id'] = 'L\'id è obbligatorio';

            if (empty(parent::$errors)) {
                try {
                    rimuoviCopia(parent::$inputs['id']);
                } catch (CopiaInPrestitoException $e) {
                    parent::$errors['message'] = 'Impossibile rimuovere una copia in prestito';
                }
            }

            return parent::$errors;
        }
    }

    /**
     * Operazione di aggiunta di un bibliotecario.
     */
    class AggiungiBibliotecario extends Operazione {

        /**
         * Costruttore
         * 
         * @param array $inputs gli input necessari per l'operazione (@see $inputs)
         *                      Formato: [ 'email', 'password1', 'password2' ]
         */
        public function __construct(array $inputs) {
            parent::__construct($inputs);
        }

        /**
         * Esegue l'operazione e ne restituisce gli errori.
         * 
         * @return array array contenente gli errori dell'operazione (@see $errors)
         *               Formato: [ 'email', 'password1', 'password2 ]
         */
        public function esegui(): array {
            if (!isset(parent::$inputs['email']) || empty(parent::$inputs['email']))
                parent::$errors['email'] = 'L\'email è obbligatoria';
            if (!isset(parent::$inputs['password1']) || empty(parent::$inputs['password1']))
                parent::$errors['password1'] = 'La password è obbligatoria';
            if (!isset(parent::$inputs['password2']) || empty(parent::$inputs['password2']))
                parent::$errors['password2'] = 'La conferma della password è obbligatoria';
            if (parent::$inputs['password1'] !== parent::$inputs['password2']) {
                parent::$errors['password1'] = 'Le password non coincidono';
                parent::$errors['password2'] = '';
            }

            try {
                parent::$inputs['email'] = new Email(parent::$inputs['email']);
            } catch (InvalidEmailException $e) {
                parent::$errors['email'] = 'Email non valida';
            }

            if (empty(parent::$errors)) {
                try {
                    aggiungiBibliotecario(parent::$inputs['email'], parent::$inputs['password1']);
                } catch (BibliotecarioGiàRegistratoException $e) {
                    parent::$errors['email'] = 'Email già registrata';
                }
            }

            return parent::$errors;
        }
    }

    /**
     * Operazione di cambio di email di un bibliotecario.
     */
    class CambiaEmailBibliotecario extends Operazione {

        /**
         * Costruttore
         * 
         * @param array $inputs gli input necessari per l'operazione (@see $inputs)
         *                      Formato: [ 'id', 'email' ]
         */
        public function __construct(array $inputs) {
            parent::__construct($inputs);
        }

        /**
         * Esegue l'operazione e ne restituisce gli errori.
         * 
         * @return array array contenente gli errori dell'operazione (@see $errors)
         *               Formato: [ 'id', 'email' ]
         */
        public function esegui(): array {
            if (!isset(parent::$inputs['id']) || empty(parent::$inputs['id']))
                parent::$errors['id'] = 'L\'id è obbligatorio';
            if (!isset(parent::$inputs['email']) || empty(parent::$inputs['email']))
                parent::$errors['email'] = 'L\'email è obbligatoria';

            try {
                parent::$inputs['email'] = new Email(parent::$inputs['email']);
            } catch (InvalidEmailException $e) {
                parent::$errors['email'] = 'Email non valida';
            }

            if (empty(parent::$errors)) {
                try {
                    setBibliotecarioEmail(parent::$inputs['id'], parent::$inputs['email']);
                } catch (BibliotecarioGiàRegistratoException $e) {
                    parent::$errors['email'] = 'Email già registrata';
                }
            }

            return parent::$errors;
        }
    }

    /**
     * Operazione di cambio di password di un bibliotecario.
     */
    class CambiaPasswordBibliotecario extends Operazione {

        /**
         * Costruttore
         * 
         * @param array $inputs gli input necessari per l'operazione (@see $inputs)
         *                      Formato: [ 'id', 'vecchia_password', 'password1', 'password2' ]
         */
        public function __construct(array $inputs) {
            parent::__construct($inputs);
        }

        /**
         * Esegue l'operazione e ne restituisce gli errori.
         * 
         * @return array array contenente gli errori dell'operazione (@see $errors)
         *               Formato: [ 'id', 'vecchia_password', 'password1', 'password2' ]
         */
        public function esegui(): array {
            if (!isset(parent::$inputs['id']) || empty(parent::$inputs['id']))
                parent::$errors['id'] = 'L\'id è obbligatorio';
            if (!isset(parent::$inputs['vecchia_password']) || empty(parent::$inputs['vecchia_password']))
                parent::$errors['vecchia_password'] = 'Inserisci la vecchia password';
            if (!isset(parent::$inputs['password1']) || empty(parent::$inputs['password1']))
                parent::$errors['password1'] = 'La password è obbligatoria';
            if (!isset(parent::$inputs['password2']) || empty(parent::$inputs['password2']))
                parent::$errors['password2'] = 'La conferma della password è obbligatoria';
            if (parent::$inputs['password1'] !== parent::$inputs['password2']) {
                parent::$errors['password1'] = 'Le password non coincidono';
                parent::$errors['password2'] = '';
            }

            if (empty(parent::$errors)) {
                try {
                    setBibliotecarioPassword(parent::$inputs['id'], parent::$inputs['vecchia_password'], parent::$inputs['password1']);
                } catch (PasswordErrataException $e) {
                    parent::$errors['vecchia_password'] = 'La vecchia password è errata';
                }
            }

            return parent::$errors;
        }
    }

    /**
     * Operazione di rimozione di un bibliotecario.
     */
    class RimuoviBibliotecario extends Operazione {

        /**
         * Costruttore
         * 
         * @param array $inputs gli input necessari per l'operazione (@see $inputs)
         *                      Formato: [ 'id' ]
         */
        public function __construct(array $inputs) {
            parent::__construct($inputs);
        }

        /**
         * Esegue l'operazione e ne restituisce gli errori.
         * 
         * @return array array contenente gli errori dell'operazione (@see $errors)
         *               Formato: [ 'id' ]
         */
        public function esegui(): array {
            if (!isset(parent::$inputs['id']) || empty(parent::$inputs['id']))
                parent::$errors['id'] = 'L\'id è obbligatorio';

            if (empty(parent::$errors))
                rimuoviBibliotecario(parent::$inputs['id']);

            return parent::$errors;
        }
    }

    /**
     * Operazione di aggiunta di un lettore.
     */
    class AggiungiLettore extends Operazione {

        /**
         * Costruttore
         * 
         * @param array $inputs gli input necessari per l'operazione (@see $inputs)
         *                      Formato: [ 'codice_fiscale', 'nome', 'cognome',
         *                                 'email', 'categoria', 'password1', 'password2' ]
         */
        public function __construct(array $inputs) {
            parent::__construct($inputs);
        }

        /**
         * Esegue l'operazione e ne restituisce gli errori.
         * 
         * @return array array contenente gli errori dell'operazione (@see $errors)
         *               Formato: [ 'codice_fiscale', 'nome', 'cognome',
         *                          'email', 'categoria', 'password1', 'password2' ]
         */
        public function esegui(): array {
            if (!isset(parent::$inputs['codice_fiscale']) || empty(parent::$inputs['codice_fiscale']))
                parent::$errors['codice_fiscale'] = 'Il codice fiscale è obbligatorio';
            if (!isset(parent::$inputs['nome']) || empty(parent::$inputs['nome']))
                parent::$errors['nome'] = 'Il nome è obbligatorio';
            if (!isset(parent::$inputs['cognome']) || empty(parent::$inputs['cognome']))
                parent::$errors['cognome'] = 'Il cognome è obbligatorio';
            if (!isset(parent::$inputs['email']) || empty(parent::$inputs['email']))
                parent::$errors['email'] = 'L\'email è obbligatoria';
            if (!isset(parent::$inputs['password1']) || empty(parent::$inputs['password1']))
                parent::$errors['password1'] = 'La password è obbligatoria';
            if (!isset(parent::$inputs['password2']) || empty(parent::$inputs['password2']))
                parent::$errors['password2'] = 'La conferma della password è obbligatoria';
            if (parent::$inputs['password1'] !== parent::$inputs['password2']) {
                parent::$errors['password1'] = 'Le password non coincidono';
                parent::$errors['password2'] = '';
            }
            if (!isset(parent::$inputs['categoria']) || empty(parent::$inputs['categoria']))
                parent::$errors['categoria'] = 'La categoria è obbligatoria';

            try {
                parent::$inputs['codice_fiscale'] = new CodiceFiscale(parent::$inputs['codice_fiscale']);
                parent::$inputs['categoria'] = Categoria::from(parent::$inputs['categoria']);
                parent::$inputs['email'] = new Email(parent::$inputs['email']);
            } catch (InvalidCodiceFiscaleException $e) {
                parent::$errors['codice_fiscale'] = 'Codice fiscale non valido';
            } catch (InvalidEmailException $e) {
                parent::$errors['email'] = 'Email non valida';
            } catch (ValueError $e) {
                parent::$errors['categoria'] = 'Categoria non valida';
            }

            if (empty(parent::$errors)) {
                try {
                    aggiungiLettore(parent::$inputs['nome'],
                                    parent::$inputs['cognome'],
                                    parent::$inputs['email'],
                                    parent::$inputs['categoria'],
                                    parent::$inputs['codice_fiscale'],
                                    parent::$inputs['password1']);
                } catch (LettoreGiàRegistratoException $e) {
                    parent::$errors['email'] = 'Email già registrata';
                }
            }

            return parent::$errors;
        }
    }

    /**
     * Operazione di cambio di email di un lettore.
     */
    class CambiaEmailLettore extends Operazione {

        /**
         * Costruttore
         * 
         * @param array $inputs gli input necessari per l'operazione (@see $inputs)
         *                      Formato: [ 'codice_fiscale', 'email' ]
         */
        public function __construct(array $inputs) {
            parent::__construct($inputs);
        }

        /**
         * Esegue l'operazione e ne restituisce gli errori.
         * 
         * @return array array contenente gli errori dell'operazione (@see $errors)
         *               Formato: [ 'codice_fiscale', 'email' ]
         */
        public function esegui(): array {
            if (!isset(parent::$inputs['codice_fiscale']) || empty(parent::$inputs['codice_fiscale']))
                parent::$errors['codice_fiscale'] = 'Il codice fiscale è obbligatorio';
            if (!isset(parent::$inputs['email']) || empty(parent::$inputs['email']))
                parent::$errors['email'] = 'L\'email è obbligatoria';

            try {
                parent::$inputs['email'] = new Email(parent::$inputs['email']);
                parent::$inputs['codice_fiscale'] = new CodiceFiscale(parent::$inputs['codice_fiscale']);
            } catch (InvalidEmailException $e) {
                parent::$errors['email'] = 'Email non valida';
            } catch (InvalidCodiceFiscaleException $e) {
                parent::$errors['codice_fiscale'] = 'Codice fiscale non valido';
            }

            if (empty(parent::$errors)) {
                try {
                    setLettoreEmail(parent::$inputs['codice_fiscale'], parent::$inputs['email']);
                } catch (LettoreGiàRegistratoException $e) {
                    parent::$errors['email'] = 'Email già registrata';
                }
            }

            return parent::$errors;
        }
    }

    /**
     * Operazione di cambio di password di un lettore.
     */
    class CambiaPasswordLettore extends Operazione {

        /**
         * Costruttore
         * 
         * @param array $inputs gli input necessari per l'operazione (@see $inputs)
         *                      Formato: [ 'codice_fiscale', 'vecchia_password', 'password1', 'password2' ]
         */
        public function __construct(array $inputs) {
            parent::__construct($inputs);
        }

        /**
         * Esegue l'operazione e ne restituisce gli errori.
         * 
         * @return array array contenente gli errori dell'operazione (@see $errors)
         *               Formato: [ 'codice_fiscale', 'vecchia_password', 'password1', 'password2' ]
         */
        public function esegui(): array {
            if (!isset(parent::$inputs['codice_fiscale']) || empty(parent::$inputs['codice_fiscale']))
                parent::$errors['codice_fiscale'] = 'L\'id è obbligatorio';
            if (!isset(parent::$inputs['vecchia_password']) || empty(parent::$inputs['vecchia_password']))
                parent::$errors['vecchia_password'] = 'Inserisci la vecchia password';
            if (!isset(parent::$inputs['password1']) || empty(parent::$inputs['password1']))
                parent::$errors['password1'] = 'La password è obbligatoria';
            if (!isset(parent::$inputs['password2']) || empty(parent::$inputs['password2']))
                parent::$errors['password2'] = 'La conferma della password è obbligatoria';
            if (parent::$inputs['password1'] !== parent::$inputs['password2']) {
                parent::$errors['password1'] = 'Le password non coincidono';
                parent::$errors['password2'] = '';
            }

            try {
                parent::$inputs['codice_fiscale'] = new CodiceFiscale(parent::$inputs['codice_fiscale']);
            } catch (InvalidCodiceFiscaleException $e) {
                parent::$errors['codice_fiscale'] = 'Codice fiscale non valido';
            }

            if (empty(parent::$errors)) {
                try {
                    setLettorePassword(parent::$inputs['codice_fiscale'], parent::$inputs['vecchia_password'], parent::$inputs['password1']);
                } catch (PasswordErrataException $e) {
                    parent::$errors['vecchia_password'] = 'La vecchia password è errata';
                }
            }

            return parent::$errors;
        }
    }

    /**
     * Operazione di cambio di categoria di un lettore.
     */
    class CambiaCategoriaLettore extends Operazione {

        /**
         * Costruttore
         * 
         * @param array $inputs gli input necessari per l'operazione (@see $inputs)
         *                      Formato: [ 'codice_fiscale', 'categoria' ]
         */
        public function __construct(array $inputs) {
            parent::__construct($inputs);
        }

        /**
         * Esegue l'operazione e ne restituisce gli errori.
         * 
         * @return array array contenente gli errori dell'operazione (@see $errors)
         *               Formato: [ 'codice_fiscale', 'categoria' ]
         */
        public function esegui(): array {
            if (!isset(parent::$inputs['codice_fiscale']) || empty(parent::$inputs['codice_fiscale']))
                parent::$errors['codice_fiscale'] = 'L\'id è obbligatorio';
            if (!isset(parent::$inputs['categoria']) || empty(parent::$inputs['categoria']))
                parent::$errors['categoria'] = 'La categoria è obbligatoria';

            try {
                parent::$inputs['categoria'] = Categoria::from(parent::$inputs['categoria']);
                parent::$inputs['codice_fiscale'] = new CodiceFiscale(parent::$inputs['codice_fiscale']);
            } catch (ValueError $e) {
                parent::$errors['categoria'] = 'Categoria non valida';
            } catch (InvalidCodiceFiscaleException $e) {
                parent::$errors['codice_fiscale'] = 'Codice fiscale non valido';
            }

            if (empty(parent::$errors))
                setLettoreCategoria(parent::$inputs['codice_fiscale'], parent::$inputs['categoria']);

            return parent::$errors;
        }
    }

    /**
     * Operazione di azzeramento dei ritardi di un lettore.
     */
    class AzzeraRitardiLettore extends Operazione {

        /**
         * Costruttore
         * 
         * @param array $inputs gli input necessari per l'operazione (@see $inputs)
         *                      Formato: [ 'codice_fiscale' ]
         */
        public function __construct(array $inputs) {
            parent::__construct($inputs);
        }

        /**
         * Esegue l'operazione e ne restituisce gli errori.
         * 
         * @return array array contenente gli errori dell'operazione (@see $errors)
         *               Formato: [ 'codice_fiscale' ]
         */
        public function esegui(): array {
            if (!isset(parent::$inputs['codice_fiscale']) || empty(parent::$inputs['codice_fiscale']))
                parent::$errors['codice_fiscale'] = 'L\'id è obbligatorio';

            try {
                parent::$inputs['codice_fiscale'] = new CodiceFiscale(parent::$inputs['codice_fiscale']);
            } catch (InvalidCodiceFiscaleException $e) {
                parent::$errors['codice_fiscale'] = 'Codice fiscale non valido';
            }

            if (empty(parent::$errors))
                azzeraRitardi(parent::$inputs['codice_fiscale']);

            return parent::$errors;
        }
    }

    /**
     * Operazione di rimozione di un lettore.
     */
    class RimuoviLettore extends Operazione {

        /**
         * Costruttore
         * 
         * @param array $inputs gli input necessari per l'operazione (@see $inputs)
         *                      Formato: [ 'codice_fiscale' ]
         */
        public function __construct(array $inputs) {
            parent::__construct($inputs);
        }

        /**
         * Esegue l'operazione e ne restituisce gli errori.
         * 
         * @return array array contenente gli errori dell'operazione (@see $errors)
         *               Formato: [ 'codice_fiscale' ]
         */
        public function esegui(): array {
            if (!isset(parent::$inputs['codice_fiscale']) || empty(parent::$inputs['codice_fiscale']))
                parent::$errors['codice_fiscale'] = 'L\'id è obbligatorio';

            try {
                parent::$inputs['codice_fiscale'] = new CodiceFiscale(parent::$inputs['codice_fiscale']);
            } catch (InvalidCodiceFiscaleException $e) {
                parent::$errors['codice_fiscale'] = 'Codice fiscale non valido';
            }

            if (empty(parent::$errors)) {
                try {
                    rimuoviLettore(parent::$inputs['codice_fiscale']);
                } catch (LettorePrestitiInCorsoException $e) {
                    parent::$errors['message'] = 'Impossibile rimuovere un lettore con prestiti in corso';
                }
            }

            return parent::$errors;
        }
    }

    /**
     * Operazione di aggiunta di un prestito.
     */
    class RichiediPrestito extends Operazione {

        /**
         * Costruttore
         * 
         * @param array $inputs gli input necessari per l'operazione (@see $inputs)
         *                      Formato: [ 'libro', 'sede', 'lettore' ]
         */
        public function __construct(array $inputs) {
            parent::__construct($inputs);
        }

        /**
         * Esegue l'operazione e ne restituisce gli errori.
         * 
         * @return array array contenente gli errori dell'operazione (@see $errors)
         *               Formato: [ 'libro', 'sede', 'lettore' ]
         */
        public function esegui(): array {
            if (!isset(parent::$inputs['libro']) || empty(parent::$inputs['libro']))
                parent::$errors['libro'] = 'Il libro è obbligatorio';
            if (!isset(parent::$inputs['sede']) || empty(parent::$inputs['sede']))
                parent::$errors['sede'] = null;
            if (!isset(parent::$inputs['lettore']) || empty(parent::$inputs['lettore']))
                parent::$errors['lettore'] = 'Il lettore è obbligatorio';

            try {
                parent::$inputs['libro'] = new Isbn(parent::$inputs['libro']);
                parent::$inputs['lettore'] = new CodiceFiscale(parent::$inputs['lettore']);
            } catch (InvalidIsbnException $e) {
                parent::$errors['libro'] = 'Isbn non valido';
            } catch (InvalidCodiceFiscaleException $e) {
                parent::$errors['lettore'] = 'Codice fiscale non valido';
            }

            try {
                $id_copia = getCopiaDisponibile(parent::$inputs['libro'], parent::$inputs['sede']);
            } catch (CopiaNonDisponibileException $e) {
                parent::$errors['message'] = 'Nessuna copia disponibile';
            }

            if (empty(parent::$errors)) {
                try {
                    richiediPrestito($id_copia, parent::$inputs['lettore']);
                } catch (TroppiPrestitiInCorsoException $e) {
                    parent::$errors['message'] = 'Hai troppi prestiti in corso';
                } catch (TroppeConsegneInRitardoException $e) {
                    parent::$errors['message'] = 'Hai troppe consegne in ritardo';
                }
            }

            return parent::$errors;
        }
    }

    /**
     * Operazione di restituzione di un prestito.
     */
    class RestituisciPrestito extends Operazione {

        /**
         * Costruttore
         * 
         * @param array $inputs gli input necessari per l'operazione (@see $inputs)
         *                      Formato: [ 'copia' ]
         */
        public function __construct(array $inputs) {
            parent::__construct($inputs);
        }

        /**
         * Esegue l'operazione e ne restituisce gli errori.
         * 
         * @return array array contenente gli errori dell'operazione (@see $errors)
         *               Formato: [ 'copia' ]
         */
        public function esegui(): array {
            if (!isset(parent::$inputs['copia']) || empty(parent::$inputs['copia']))
                parent::$errors['copia'] = 'La copia è obbligatoria';

            if (empty(parent::$errors))
                restituisciPrestito(parent::$inputs['copia']);

            return parent::$errors;
        }
    }

    /**
     * Operazione di proroga di un prestito.
     */ 
    class ProrogaPrestito extends Operazione {

        /**
         * Costruttore
         * 
         * @param array $inputs gli input necessari per l'operazione (@see $inputs)
         *                      Formato: [ 'copia', 'giorni_di_proroga' ]
         */
        public function __construct(array $inputs) {
            parent::__construct($inputs);
        }

        /**
         * Esegue l'operazione e ne restituisce gli errori.
         * 
         * @return array array contenente gli errori dell'operazione (@see $errors)
         *               Formato: [ 'copia', 'giorni_di_proroga' ]
         */
        public function esegui(): array {
            if (!isset(parent::$inputs['copia']) || empty(parent::$inputs['copia']))
                parent::$errors['copia'] = 'La copia è obbligatoria';
            if (!isset(parent::$inputs['giorni_di_proroga']) || empty(parent::$inputs['giorni_di_proroga']))
                parent::$errors['giorni_di_proroga'] = 'Inserisci di quanti giorni vuoi prorogare il prestito';

            if (empty(parent::$errors)) {
                try {
                    prorogaPrestito(parent::$inputs['copia'], parent::$inputs['giorni_di_proroga']);
                } catch (PrestitoInRitardoException $e) {
                    parent::$errors['message'] = 'Il prestito è già in ritardo';
                }
            }

            return parent::$errors;
        }
    }

?>
