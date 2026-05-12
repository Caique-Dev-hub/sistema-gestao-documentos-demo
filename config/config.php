<?php

define('URL_BASE', 'http://localhost/app_gestao_demo/public/');

spl_autoload_register(function ($class) {
    $caminhos = [
        "../app/controllers/$class.php",
        "../app/models/$class.php",
        "../app/core/$class.php",
        "../routes/$class.php"
    ];

    foreach ($caminhos as $valor) {
        if (file_exists($valor)) {
            require_once($valor);
        }
    }
});

function env(): void
{
    $arquivo = file("../.env", FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    foreach ($arquivo as $valor) {
        if (str_contains($valor, '#')) {
            continue;
        }

        $env = explode('=', $valor, 2);

        $_ENV[$env[0]] = $env[1];
    }
}

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
