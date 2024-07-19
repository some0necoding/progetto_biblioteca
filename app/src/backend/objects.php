<?php
    include_once 'exceptions.php';

    /**
     * Rappresentazione di un codice ISBN.
     */
    class Isbn {
        private string $isbn;
        private const PATTERN = '[0-9]{13}';

        /**
         * Costruisce un nuovo codice ISBN.
         *
         * @param string $isbn il codice ISBN
         * @throws InvalidIsbnException se il codice ISBN non è valido
         */
        public function __construct(string $isbn) {
            if (!preg_match('/' . self::PATTERN . '/', $isbn))
                throw new InvalidIsbnException($isbn);
            $this->isbn = $isbn;
        }

        /**
         * Restituisce il codice ISBN.
         *
         * @return string il codice ISBN
         */
        public function getIsbn(): string {
            return $this->isbn;
        }

        public function __toString(): string {
            return $this->isbn;
        }
    }

    /**
     * Rappresentazione di un indirizzo email.
     */
    class Email {
        private string $email;
        private const PATTERN = <<<'regex'
            (?:[a-z0-9!#$%&'*+/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&'*+/=?^_`{|}~-]+)*|"(?:[\x01-\x08\x0b\x0c\x0e-\x1f\x21\x23-\x5b\x5d-\x7f]|\\[\x01-\x09\x0b\x0c\x0e-\x7f])*")@(?:(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])?|\[(?:(?:(2(5[0-5]|[0-4][0-9])|1[0-9][0-9]|[1-9]?[0-9]))\.){3}(?:(2(5[0-5]|[0-4][0-9])|1[0-9][0-9]|[1-9]?[0-9])|[a-z0-9-]*[a-z0-9]:(?:[\x01-\x08\x0b\x0c\x0e-\x1f\x21-\x5a\x53-\x7f]|\\[\x01-\x09\x0b\x0c\x0e-\x7f])+)\])
            regex;

        /**
         * Costruisce un nuovo indirizzo email.
         *
         * @param string $email l'indirizzo email
         * @throws InvalidEmailException se l'indirizzo email non è valido
         */
        public function __construct(string $email) {
            if (!preg_match('<' . self::PATTERN . '>', $email))
                throw new InvalidEmailException($email);
            $this->email = $email;
        }

        /**
         * Restituisce l'indirizzo email.
         *
         * @return string l'indirizzo email
         */
        public function getEmail(): string {
            return $this->email;
        }

        public function __toString(): string {
            return $this->email;
        }
    }

    /**
     * Rappresentazione della categoria di un lettore.
     */
    enum Categoria: int {

        /** Lettore base */
        case BASE = 1;

        /** Lettore premium */
        case PREMIUM = 2;

        public function toString(): string {
            return match ($this) {
                Categoria::BASE => "Base",
                Categoria::PREMIUM => "Premium",
            };
        }

        public function encoding(): string {
            return match ($this) {
                Categoria::BASE => "base",
                Categoria::PREMIUM => "premium",
            };
        }
    }

    /**
     * Rappresentazione di un codice fiscale.
     */
    class CodiceFiscale {
        private string $codice_fiscale;
        private const PATTERN = <<<'regex'
            ^(?:[A-Z][AEIOU][AEIOUX]|[AEIOU]X{2}|[B-DF-HJ-NP-TV-Z]{2}[A-Z]){2}(?:[\dLMNP-V]{2}(?:[A-EHLMPR-T](?:[04LQ][1-9MNP-V]|[15MR][\dLMNP-V]|[26NS][0-8LMNP-U])|[DHPS][37PT][0L]|[ACELMRT][37PT][01LM]|[AC-EHLMPR-T][26NS][9V])|(?:[02468LNQSU][048LQU]|[13579MPRTV][26NS])B[26NS][9V])(?:[A-MZ][1-9MNP-V][\dLMNP-V]{2}|[A-M][0L](?:[1-9MNP-V][\dLMNP-V]|[0L][1-9MNP-V]))[A-Z]$
            regex;

        /**
         * Costruisce un nuovo codice fiscale.
         *
         * @param string $codice_fiscale il codice fiscale
         * @throws InvalidCodiceFiscaleException se il codice fiscale non è valido
         */
        public function __construct(string $codice_fiscale) {
            if (!preg_match('/' . self::PATTERN . '/i', $codice_fiscale))
                throw new InvalidCodiceFiscaleException($codice_fiscale);
            $this->codice_fiscale = $codice_fiscale;
        }

        /**
         * Restituisce il codice fiscale.
         *
         * @return string il codice fiscale
         */
        public function getCodiceFiscale(): string {
            return $this->codice_fiscale;
        }

        public function __toString(): string {
            return $this->codice_fiscale;
        }
    }

    /**
     * Rappresentazione della tipologia di un utente.
     */
    enum Utente: int {

        /** Lettore della biblioteca */
        case LETTORE = 1;

        /** Bibliotecario della biblioteca */
        case BIBLIOTECARIO = 2;
    }

?>
