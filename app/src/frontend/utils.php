<?php

    function view(string $filename, array $data = []): void {
        foreach ($data as $key => $value) {
            $$key = $value;
        }

        require_once __DIR__ . '/' . $filename . '.php';
    }

    function post(): bool {
        return strtoupper($_SERVER['REQUEST_METHOD']) === 'POST';
    }

    function get(): bool {
        return strtoupper($_SERVER['REQUEST_METHOD']) === 'GET';
    }
    
    function redirect_to(string $url): void {
        header('Location:' . $url);
        exit();
    }

    function redirect_with(string $url, array $items): void {
        foreach ($items as $key => $value) {
            $_SESSION[$key] = $value;
        }

        redirect_to($url);
    }

    function session_get(...$keys): array {
        $data = [];
        foreach ($keys as $key) {
            if (isset($_SESSION[$key])) {
                $data[] = $_SESSION[$key];
                unset($_SESSION[$key]);
            } else {
                $data[] = [];
            }
        }
        return $data;
    }

    function classificaPerOperazione(array &$array, TipoOperazione $tipoOperazione): void {
        foreach ($array as $key => $value) {
            $array[$tipoOperazione->value][$key] = $value;
            unset($array[$key]);
        }

        if (empty($array))
            $array[$tipoOperazione->value] = [];
    }

?>
