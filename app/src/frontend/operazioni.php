<?php
    include_once '../src/backend/exceptions.php';
    include_once '../src/backend/backend.php';
    include_once '../src/backend/objects.php';

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
        case ELIMINA_ACCOUNT_BIBLIOTECARIO = 14;
        case AGGIUNGI_LETTORE = 15;
        case CAMBIA_EMAIL_LETTORE = 16;
        case CAMBIA_PASSWORD_LETTORE = 17;
        case CAMBIA_CATEGORIA_LETTORE = 18;
        case AZZERA_RITARDI_LETTORE = 19;
        case ELIMINA_ACCOUNT_LETTORE = 20;
        case RICHIEDI_PRESTITO = 21;
        case RESTITUISCI_PRESTITO = 22;
        case PROROGA_PRESTITO = 23;
        case LOGIN = 24;
        case LOGOUT = 25;
    }

    const OPERAZIONI = [
        TipoOperazione::AGGIUNGI_AUTORE->value               => 'AggiungiAutore',
        TipoOperazione::SET_DATA_DI_MORTE_AUTORE->value      => 'SetDataDiMorteAutore',
        TipoOperazione::RIMUOVI_AUTORE->value                => 'RimuoviAutore',
        TipoOperazione::AGGIUNGI_LIBRO->value                => 'AggiungiLibro',
        TipoOperazione::RIMUOVI_LIBRO->value                 => 'RimuoviLibro',
        TipoOperazione::AGGIUNGI_SEDE->value                 => 'AggiungiSede',
        TipoOperazione::RIMUOVI_SEDE->value                  => 'RimuoviSede',
        TipoOperazione::AGGIUNGI_COPIA->value                => 'AggiungiCopia',
        TipoOperazione::CAMBIA_SEDE->value                   => 'CambiaSede',
        TipoOperazione::RIMUOVI_COPIA->value                 => 'RimuoviCopia',
        TipoOperazione::AGGIUNGI_BIBLIOTECARIO->value        => 'AggiungiBibliotecario',
        TipoOperazione::CAMBIA_EMAIL_BIBLIOTECARIO->value    => 'CambiaEmailBibliotecario',
        TipoOperazione::CAMBIA_PASSWORD_BIBLIOTECARIO->value => 'CambiaPasswordBibliotecario',
        TipoOperazione::ELIMINA_ACCOUNT_BIBLIOTECARIO->value => 'EliminaAccountBibliotecario',
        TipoOperazione::AGGIUNGI_LETTORE->value              => 'AggiungiLettore',
        TipoOperazione::CAMBIA_EMAIL_LETTORE->value          => 'CambiaEmailLettore',
        TipoOperazione::CAMBIA_PASSWORD_LETTORE->value       => 'CambiaPasswordLettore',
        TipoOperazione::CAMBIA_CATEGORIA_LETTORE->value      => 'CambiaCategoriaLettore',
        TipoOperazione::AZZERA_RITARDI_LETTORE->value        => 'AzzeraRitardiLettore',
        TipoOperazione::ELIMINA_ACCOUNT_LETTORE->value       => 'EliminaAccountLettore',
        TipoOperazione::RICHIEDI_PRESTITO->value             => 'RichiediPrestito',
        TipoOperazione::RESTITUISCI_PRESTITO->value          => 'RestituisciPrestito',
        TipoOperazione::PROROGA_PRESTITO->value              => 'ProrogaPrestito',
        TipoOperazione::LOGIN->value                         => 'Login',
        TipoOperazione::LOGOUT->value                        => 'Logout'
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
            if (!isset($this->inputs['nome']) || empty($this->inputs['nome']))
                $this->errors['nome'] = 'Il nome è obbligatorio';
            if (!isset($this->inputs['cognome']) || empty($this->inputs['cognome']))
                $this->errors['cognome'] = 'Il cognome è obbligatorio';
            if (!isset($this->inputs['data_di_nascita']) || empty($this->inputs['data_di_nascita']))
                $this->errors['data_di_nascita'] = 'La data di nascita è obbligatoria';

            if (empty($this->errors))
                aggiungiAutore($this->inputs['nome'],
                               $this->inputs['cognome'],
                               $this->inputs['biografia'],
                               $this->inputs['data_di_nascita'],
                               $this->inputs['data_di_morte']);

            return $this->errors;
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
            if (!isset($this->inputs['id']) || empty($this->inputs['id']))
                $this->errors['id'] = 'L\'id è obbligatorio';
            if (!isset($this->inputs['data_di_morte']) || empty($this->inputs['data_di_morte']))
                $this->errors['data_di_morte'] = 'La data di morte è obbligatoria';

            if (empty($this->errors))
                setAutoreDataDiMorte($this->inputs['id'], $this->inputs['data_di_morte']);

            return $this->errors;
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
            if (!isset($this->inputs['id']) || empty($this->inputs['id']))
                $this->errors['id'] = 'L\'id è obbligatorio';

            if (empty($this->errors)) {
                try {
                    rimuoviAutore($this->inputs['id']);
                } catch (LibriAssociatiAdAutoreException $e) {
                    $this->errors['message'] = 'Impossibile rimuovere un autore con libri associati';
                }
            }

            return $this->errors;
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
            if (!isset($this->inputs['titolo']) || empty($this->inputs['titolo']))
                $this->errors['titolo'] = 'Il titolo è obbligatorio';
            if (!isset($this->inputs['trama']))
                $this->inputs['trama'] = '';
            if (!isset($this->inputs['casa_editrice']) || empty($this->inputs['casa_editrice']))
                $this->errors['casa_editrice'] = 'La casa editrice è obbligatoria';
            if (!isset($this->inputs['autori']) || empty($this->inputs['autori']))
                $this->errors['autori'] = 'Gli autori sono obbligatori';

            try {
                if (!isset($this->inputs['isbn']) || empty($this->inputs['isbn']))
                    $this->errors['isbn'] = 'L\'isbn è obbligatorio';
                else
                    $this->inputs['isbn'] = new Isbn($this->inputs['isbn']);
            } catch (InvalidIsbnException $e) {
                $this->errors['isbn'] = 'Isbn non valido';
            }

            if (empty($this->errors)) {
                try {
                    aggiungiLibro($this->inputs['isbn'],
                                  $this->inputs['titolo'],
                                  $this->inputs['trama'],
                                  $this->inputs['casa_editrice'],
                                  $this->inputs['autori']);
                } catch (IsbnGiàEsistenteException $e) {
                    $this->errors['isbn'] = 'Isbn già esistente';
                }
            }

            return $this->errors;
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
            try {
                if (!isset($this->inputs['isbn']) || empty($this->inputs['isbn']))
                    $this->errors['isbn'] = 'L\'isbn è obbligatorio';
                else
                    $this->inputs['isbn'] = new Isbn($this->inputs['isbn']);
            } catch (InvalidIsbnException $e) {
                $this->errors['isbn'] = 'Isbn non valido';
            }

            if (empty($this->errors)) {
                try {
                    rimuoviLibro($this->inputs['isbn']);
                } catch (CopieAssociateALibroException $e) {
                    $this->errors['message'] = 'Impossibile rimuovere un libro con copie associate';
                }
            }

            return $this->errors;
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
            if (!isset($this->inputs['città']) || empty($this->inputs['città']))
                $this->errors['città'] = 'La città è obbligatoria';
            if (!isset($this->inputs['indirizzo']) || empty($this->inputs['indirizzo']))
                $this->errors['indirizzo'] = 'L\'indirizzo è obbligatorio';

            if (empty($this->errors))
                aggiungiSede($this->inputs['indirizzo'], $this->inputs['città']);

            return $this->errors;
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
            if (!isset($this->inputs['id']) || empty($this->inputs['id']))
                $this->errors['id'] = 'L\'id è obbligatorio';

            if (empty($this->errors)) {
                try {
                    rimuoviSede($this->inputs['id']);
                } catch (CopieAssociateASedeException $e) {
                    $this->errors['message'] = 'Impossibile rimuovere una sede con copie associate';
                }
            }

            return $this->errors;
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
            if (!isset($this->inputs['sede']) || empty($this->inputs['sede']))
                $this->errors['sede'] = 'La sede è obbligatoria';

            try {
                if (!isset($this->inputs['libro']) || empty($this->inputs['libro']))
                    $this->errors['libro'] = 'Il libro è obbligatorio';
                else
                    $this->inputs['libro'] = new Isbn($this->inputs['libro']);
            } catch (InvalidIsbnException $e) {
                $this->errors['libro'] = 'Isbn non valido';
            }

            if (empty($this->errors))
                aggiungiCopia($this->inputs['libro'], $this->inputs['sede']);

            return $this->errors;
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
            if (!isset($this->inputs['id']) || empty($this->inputs['id']))
                $this->errors['id'] = 'L\'id è obbligatorio';
            if (!isset($this->inputs['sede']) || empty($this->inputs['sede']))
                $this->errors['sede'] = 'La sede è obbligatoria';

            if (empty($this->errors)) {
                try {
                    setSede($this->inputs['id'], $this->inputs['sede']);
                } catch (CopiaInPrestitoException $e) {
                    $this->errors['message'] = 'Impossibile cambiare sede a una copia in prestito';
                }
            }

            return $this->errors;
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
            if (!isset($this->inputs['id']) || empty($this->inputs['id']))
                $this->errors['id'] = 'L\'id è obbligatorio';

            if (empty($this->errors)) {
                try {
                    rimuoviCopia($this->inputs['id']);
                } catch (CopiaInPrestitoException $e) {
                    $this->errors['message'] = 'Impossibile rimuovere una copia in prestito';
                }
            }

            return $this->errors;
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
            if (!isset($this->inputs['password1']) || empty($this->inputs['password1']))
                $this->errors['password1'] = 'La password è obbligatoria';
            if (!isset($this->inputs['password2']) || empty($this->inputs['password2']))
                $this->errors['password2'] = 'La conferma della password è obbligatoria';
            if ($this->inputs['password1'] !== $this->inputs['password2']) {
                $this->errors['password1'] = 'Le password non coincidono';
                $this->errors['password2'] = '';
            }

            try {
                if (!isset($this->inputs['email']) || empty($this->inputs['email']))
                    $this->errors['email'] = 'L\'email è obbligatoria';
                else
                    $this->inputs['email'] = new Email($this->inputs['email']);
            } catch (InvalidEmailException $e) {
                $this->errors['email'] = 'Email non valida';
            }

            if (empty($this->errors)) {
                try {
                    aggiungiBibliotecario($this->inputs['email'], $this->inputs['password1']);
                } catch (BibliotecarioGiàRegistratoException $e) {
                    $this->errors['email'] = 'Email già registrata';
                }
            }

            return $this->errors;
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
            if (!isset($this->inputs['id']) || empty($this->inputs['id']))
                $this->errors['id'] = 'L\'id è obbligatorio';

            try {
                if (!isset($this->inputs['email']) || empty($this->inputs['email']))
                    $this->errors['email'] = 'L\'email è obbligatoria';
                else
                    $this->inputs['email'] = new Email($this->inputs['email']);
            } catch (InvalidEmailException $e) {
                $this->errors['email'] = 'Email non valida';
            }

            if (empty($this->errors)) {
                try {
                    setBibliotecarioEmail($this->inputs['id'], $this->inputs['email']);
                } catch (BibliotecarioGiàRegistratoException $e) {
                    $this->errors['email'] = 'Email già registrata';
                }
            }

            return $this->errors;
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
            if (!isset($this->inputs['id']) || empty($this->inputs['id']))
                $this->errors['id'] = 'L\'id è obbligatorio';
            if (!isset($this->inputs['vecchia_password']) || empty($this->inputs['vecchia_password']))
                $this->errors['vecchia_password'] = 'Inserisci la vecchia password';
            if (!isset($this->inputs['password1']) || empty($this->inputs['password1']))
                $this->errors['password1'] = 'La password è obbligatoria';
            if (!isset($this->inputs['password2']) || empty($this->inputs['password2']))
                $this->errors['password2'] = 'La conferma della password è obbligatoria';
            if ($this->inputs['password1'] !== $this->inputs['password2']) {
                $this->errors['password1'] = 'Le password non coincidono';
                $this->errors['password2'] = '';
            }

            if (empty($this->errors)) {
                try {
                    setBibliotecarioPassword($this->inputs['id'], $this->inputs['vecchia_password'], $this->inputs['password1']);
                } catch (PasswordErrataException $e) {
                    $this->errors['vecchia_password'] = 'La vecchia password è errata';
                }
            }

            return $this->errors;
        }
    }

    /**
     * Operazione di rimozione dell'account di un bibliotecario.
     */
    class EliminaAccountBibliotecario extends Operazione {

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
            if (!isset($this->inputs['id']) || empty($this->inputs['id']))
                $this->errors['id'] = 'L\'id è obbligatorio';

            if (empty($this->errors)) {
                rimuoviBibliotecario($this->inputs['id']);
                logout();
            }

            return $this->errors;
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
            if (!isset($this->inputs['nome']) || empty($this->inputs['nome']))
                $this->errors['nome'] = 'Il nome è obbligatorio';
            if (!isset($this->inputs['cognome']) || empty($this->inputs['cognome']))
                $this->errors['cognome'] = 'Il cognome è obbligatorio';
            if (!isset($this->inputs['password1']) || empty($this->inputs['password1']))
                $this->errors['password1'] = 'La password è obbligatoria';
            if (!isset($this->inputs['password2']) || empty($this->inputs['password2']))
                $this->errors['password2'] = 'La conferma della password è obbligatoria';
            if ($this->inputs['password1'] !== $this->inputs['password2']) {
                $this->errors['password1'] = 'Le password non coincidono';
                $this->errors['password2'] = '';
            }

            try {
                if (!isset($this->inputs['codice_fiscale']) || empty($this->inputs['codice_fiscale']))
                    $this->errors['codice_fiscale'] = 'Il codice fiscale è obbligatorio';
                else
                    $this->inputs['codice_fiscale'] = new CodiceFiscale($this->inputs['codice_fiscale']);

                if (!isset($this->inputs['categoria']) || empty($this->inputs['categoria']))
                    $this->errors['categoria'] = 'La categoria è obbligatoria';
                else
                    $this->inputs['categoria'] = Categoria::from($this->inputs['categoria']);

                if (!isset($this->inputs['email']) || empty($this->inputs['email']))
                    $this->errors['email'] = 'L\'email è obbligatoria';
                else
                    $this->inputs['email'] = new Email($this->inputs['email']);
            } catch (InvalidCodiceFiscaleException $e) {
                $this->errors['codice_fiscale'] = 'Codice fiscale non valido';
            } catch (InvalidEmailException $e) {
                $this->errors['email'] = 'Email non valida';
            } catch (ValueError $e) {
                $this->errors['categoria'] = 'Categoria non valida';
            }

            if (empty($this->errors)) {
                try {
                    aggiungiLettore($this->inputs['nome'],
                                    $this->inputs['cognome'],
                                    $this->inputs['email'],
                                    $this->inputs['categoria'],
                                    $this->inputs['codice_fiscale'],
                                    $this->inputs['password1']);
                } catch (LettoreGiàRegistratoException $e) {
                    $this->errors['email'] = 'Email o codice fiscale già registrati';
                    $this->errors['codice_fiscale'] = '';
                }
            }

            return $this->errors;
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
            try {
                if (!isset($this->inputs['codice_fiscale']) || empty($this->inputs['codice_fiscale']))
                    $this->errors['codice_fiscale'] = 'Il codice fiscale è obbligatorio';
                else
                    $this->inputs['codice_fiscale'] = new CodiceFiscale($this->inputs['codice_fiscale']);

                if (!isset($this->inputs['email']) || empty($this->inputs['email']))
                    $this->errors['email'] = 'L\'email è obbligatoria';
                else
                    $this->inputs['email'] = new Email($this->inputs['email']);
            } catch (InvalidEmailException $e) {
                $this->errors['email'] = 'Email non valida';
            } catch (InvalidCodiceFiscaleException $e) {
                $this->errors['codice_fiscale'] = 'Codice fiscale non valido';
            }

            if (empty($this->errors)) {
                try {
                    setLettoreEmail($this->inputs['codice_fiscale'], $this->inputs['email']);
                } catch (LettoreGiàRegistratoException $e) {
                    $this->errors['email'] = 'Email già registrata';
                }
            }

            return $this->errors;
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
            if (!isset($this->inputs['vecchia_password']) || empty($this->inputs['vecchia_password']))
                $this->errors['vecchia_password'] = 'Inserisci la vecchia password';
            if (!isset($this->inputs['password1']) || empty($this->inputs['password1']))
                $this->errors['password1'] = 'La password è obbligatoria';
            if (!isset($this->inputs['password2']) || empty($this->inputs['password2']))
                $this->errors['password2'] = 'La conferma della password è obbligatoria';
            if ($this->inputs['password1'] !== $this->inputs['password2']) {
                $this->errors['password1'] = 'Le password non coincidono';
                $this->errors['password2'] = '';
            }

            try {
                if (!isset($this->inputs['codice_fiscale']) || empty($this->inputs['codice_fiscale']))
                    $this->errors['codice_fiscale'] = 'L\'id è obbligatorio';
                else
                    $this->inputs['codice_fiscale'] = new CodiceFiscale($this->inputs['codice_fiscale']);
            } catch (InvalidCodiceFiscaleException $e) {
                $this->errors['codice_fiscale'] = 'Codice fiscale non valido';
            }

            if (empty($this->errors)) {
                try {
                    setLettorePassword($this->inputs['codice_fiscale'], $this->inputs['vecchia_password'], $this->inputs['password1']);
                } catch (PasswordErrataException $e) {
                    $this->errors['vecchia_password'] = 'La vecchia password è errata';
                }
            }

            return $this->errors;
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
            try {
                if (!isset($this->inputs['codice_fiscale']) || empty($this->inputs['codice_fiscale']))
                    $this->errors['codice_fiscale'] = 'L\'id è obbligatorio';
                else
                    $this->inputs['categoria'] = Categoria::from($this->inputs['categoria']);

                if (!isset($this->inputs['categoria']) || empty($this->inputs['categoria']))
                    $this->errors['categoria'] = 'La categoria è obbligatoria';
                else
                    $this->inputs['codice_fiscale'] = new CodiceFiscale($this->inputs['codice_fiscale']);
            } catch (ValueError $e) {
                $this->errors['categoria'] = 'Categoria non valida';
            } catch (InvalidCodiceFiscaleException $e) {
                $this->errors['codice_fiscale'] = 'Codice fiscale non valido';
            }

            if (empty($this->errors))
                setLettoreCategoria($this->inputs['codice_fiscale'], $this->inputs['categoria']);

            return $this->errors;
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
            try {
                if (!isset($this->inputs['codice_fiscale']) || empty($this->inputs['codice_fiscale']))
                    $this->errors['codice_fiscale'] = 'L\'id è obbligatorio';
                else
                    $this->inputs['codice_fiscale'] = new CodiceFiscale($this->inputs['codice_fiscale']);
            } catch (InvalidCodiceFiscaleException $e) {
                $this->errors['codice_fiscale'] = 'Codice fiscale non valido';
            }

            if (empty($this->errors))
                azzeraRitardi($this->inputs['codice_fiscale']);

            return $this->errors;
        }
    }

    /**
     * Operazione di rimozione di un lettore.
     */
    class EliminaAccountLettore extends Operazione {

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
            try {
                if (!isset($this->inputs['codice_fiscale']) || empty($this->inputs['codice_fiscale']))
                    $this->errors['codice_fiscale'] = 'L\'id è obbligatorio';
                else
                    $this->inputs['codice_fiscale'] = new CodiceFiscale($this->inputs['codice_fiscale']);
            } catch (InvalidCodiceFiscaleException $e) {
                $this->errors['codice_fiscale'] = 'Codice fiscale non valido';
            }

            if (empty($this->errors)) {
                try {
                    rimuoviLettore($this->inputs['codice_fiscale']);
                    logout();
                } catch (LettorePrestitiInCorsoException $e) {
                    $this->errors['message'] = 'Impossibile rimuovere un lettore con prestiti in corso';
                }
            }

            return $this->errors;
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
            if (!isset($this->inputs['sede']) || empty($this->inputs['sede']))
                $this->inputs['sede'] = null;

            try {
                if (!isset($this->inputs['libro']) || empty($this->inputs['libro']))
                    $this->errors['libro'] = 'Il libro è obbligatorio';
                else
                    $this->inputs['libro'] = new Isbn($this->inputs['libro']);

                if (!isset($this->inputs['lettore']) || empty($this->inputs['lettore']))
                    $this->errors['lettore'] = 'Il lettore è obbligatorio';
                else
                    $this->inputs['lettore'] = new CodiceFiscale($this->inputs['lettore']);
            } catch (InvalidIsbnException $e) {
                $this->errors['libro'] = 'Isbn non valido';
            } catch (InvalidCodiceFiscaleException $e) {
                $this->errors['lettore'] = 'Codice fiscale non valido';
            }

            try {
                $id_copia = getCopiaDisponibile($this->inputs['libro'], $this->inputs['sede']);
            } catch (CopiaNonDisponibileException $e) {
                $this->errors['message'] = 'Nessuna copia disponibile';
            }

            if (empty($this->errors)) {
                try {
                    richiediPrestito($id_copia, $this->inputs['lettore']);
                } catch (TroppiPrestitiInCorsoException $e) {
                    $this->errors['message'] = 'Hai troppi prestiti in corso';
                } catch (TroppeConsegneInRitardoException $e) {
                    $this->errors['message'] = 'Hai troppe consegne in ritardo';
                }
            }

            return $this->errors;
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
            if (!isset($this->inputs['copia']) || empty($this->inputs['copia']))
                $this->errors['copia'] = 'La copia è obbligatoria';

            if (empty($this->errors))
                restituisciPrestito($this->inputs['copia']);

            return $this->errors;
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
            if (!isset($this->inputs['copia']) || empty($this->inputs['copia']))
                $this->errors['copia'] = 'La copia è obbligatoria';
            if (!isset($this->inputs['giorni_di_proroga']) || empty($this->inputs['giorni_di_proroga']))
                $this->errors['giorni_di_proroga'] = 'Inserisci di quanti giorni vuoi prorogare il prestito';

            if (empty($this->errors)) {
                try {
                    prorogaPrestito($this->inputs['copia'], $this->inputs['giorni_di_proroga']);
                } catch (PrestitoInRitardoException $e) {
                    $this->errors['message'] = 'Il prestito è già in ritardo';
                }
            }

            return $this->errors;
        }
    }

    /**
     * Operazione di login
     */
    class Login extends Operazione {

        /**
         * Costruttore
         *
         * @param array $inputs gli input necessari per l'operazione (@see $inputs)
         *                      Formato: [ 'utente', 'email', 'password' ]
         */
        public function __construct(array $inputs) {
            parent::__construct($inputs);
        }

        /**
         * Esegue l'operazione e ne restituisce gli errori.
         *
         * @return array array contenente gli errori dell'operazione (@see $errors)
         *               Formato: [ 'utente', 'email', 'password' ]
         */
        public function esegui(): array {
            if (!isset($this->inputs['password']) || empty($this->inputs['password']))
                $this->errors['password'] = 'La password è obbligatoria';

            try {
                if (!isset($this->inputs['utente']) || empty($this->inputs['utente']))
                    $this->errors['utente'] = 'L\'utente è obbligatorio';
                else
                    $this->inputs['utente'] = Utente::from($this->inputs['utente']);

                if (!isset($this->inputs['email']) || empty($this->inputs['email']))
                    $this->errors['email'] = 'L\'email è obbligatoria';
                else
                    $this->inputs['email'] = new Email($this->inputs['email']);
            } catch (InvalidEmailException $e) {
                $this->errors['email'] = 'Inserisci una mail valida';
            } catch (ValueError $e) {
                $this->errors['utente'] = 'Inserisci un utente valido';
            }

            if (empty($this->errors)) {
                try {
                    login($this->inputs['utente'], $this->inputs['email'], $this->inputs['password']);
                } catch (PasswordErrataException | UtenteInesistenteException $e) {
                    $this->errors['email'] = 'Email o password sono errati';
                    $this->errors['password'] = '';
                }
            }

            return $this->errors;
        }
    }

    /**
     * Operazione di logout
     */
    class Logout extends Operazione {

        /**
         * Costruttore
         */
        public function __construct() {
            parent::__construct([]);
        }

        /**
         * Esegue l'operazione e ne restituisce gli errori.
         *
         * @return array array vuoto
         */
        public function esegui(): array {
            logout();
            return $this->errors;
        }
    }

?>
