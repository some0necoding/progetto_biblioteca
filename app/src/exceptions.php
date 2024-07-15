<?php

    class InvalidIsbnException extends Exception {
        public function __construct(string $isbn) {
            parent::__construct($isbn . " non è un codice isbn valido");
        }
    }

    class InvalidEmailException extends Exception {
        public function __construct(string $email) {
            parent::__construct($email . " non è una mail valida");
        }
    }

    class InvalidCodiceFiscaleException extends Exception {
        public function __construct(string $codiceFiscale) {
            parent::__construct($codiceFiscale . " non è un codice fiscale valido");
        }
    }

    // exceptions riguardanti il database

    class DatabaseException extends Exception {
        private static $exceptions = [
            'LIBRI_ASSOCIATI_AD_AUTORE'    => 'LibriAssociatiAdAutoreException',
            'ISBN_GIÀ_ESISTENTE'           => 'IsbnGiàEsistenteException',
            'COPIE_ASSOCIATE_A_LIBRO'      => 'CopieAssociateALibroException',
            'COPIE_ASSOCIATE_A_SEDE'       => 'CopieAssociateASedeException',
            'COPIA_IN_PRESTITO'            => 'CopiaInPrestitoException',
            'LETTORE_GIÀ_REGISTRATO'       => 'LettoreGiàRegistratoException',
            'LETTORE_PRESTITI_IN_CORSO'    => 'LettorePrestitiInCorsoException',
            'TROPPE_CONSEGNE_IN_RITARDO'   => 'TroppeConsegneInRitardoException',
            'TROPPI_PRESTITI_IN_CORSO'     => 'TroppiPrestitiInCorsoException',
            'COPIA_NON_DISPONIBILE'        => 'CopiaNonDisponibileException',
            'PRESTITO_IN_RITARDO'          => 'PrestitoInRitardoException',
            'BIBLIOTECARIO_GIÀ_REGISTRATO' => 'BibliotecarioGiàRegistratoException'
        ];

        public function __construct(string $message) {
            parent::__construct("Database error: $message");
        }

        public static function fromEnumString(string $error): DatabaseException {
            if (!array_key_exists($error, self::$exceptions))
                return null;

            return new $self::$exceptions[$error];
        }
    }

    class LibriAssociatiAdAutoreException extends DatabaseException {
        public function __construct() {
            parent::__construct("Libri associati ad autore");
        }
    }

    class IsbnGiàEsistenteException extends DatabaseException {
        public function __construct() {
            parent::__construct("ISBN già esistente");
        }
    }

    class CopieAssociateALibroException extends DatabaseException {
        public function __construct() {
            parent::__construct("Copie associate a libro");
        }
    }

    class CopieAssociateASedeException extends DatabaseException {
        public function __construct() {
            parent::__construct("Copie associate a sede");
        }
    }

    class CopiaInPrestitoException extends DatabaseException {
        public function __construct() {
            parent::__construct("Copia in prestito");
        }
    }

    class LettoreGiàRegistratoException extends DatabaseException {
        public function __construct() {
            parent::__construct("Lettore già registrato");
        }
    }

    class LettorePrestitiInCorsoException extends DatabaseException {
        public function __construct() {
            parent::__construct("Lettore con prestiti in corso");
        }
    }

    class TroppeConsegneInRitardoException extends DatabaseException {
        public function __construct() {
            parent::__construct("Troppe consegne in ritardo");
        }
    }

    class TroppiPrestitiInCorsoException extends DatabaseException {
        public function __construct() {
            parent::__construct("Troppo prestiti in corso");
        }
    }

    class CopiaNonDisponibileException extends DatabaseException {
        public function __construct() {
            parent::__construct("Copia non disponibile");
        }
    }

    class PrestitoInRitardoException extends DatabaseException {
        public function __construct() {
            parent::__construct("Prestito in ritardo");
        }
    }

    class BibliotecarioGiàRegistratoException extends DatabaseException {
        public function __construct() {
            parent::__construct("Bibliotecario già registrato");
        }
    }

    class ErroreInternoDatabaseException extends DatabaseException {
        public function __construct() {
            parent::__construct("Errore interno database");
        }
    }

    // altre exceptions

    class PasswordErrataException extends Exception {
        public function __construct() {
            parent::__construct("Password errata");
        }
    }
?>
