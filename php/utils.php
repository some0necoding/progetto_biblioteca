<?php
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
?>
