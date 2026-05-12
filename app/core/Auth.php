<?php
// ARQUIVO: app/core/Auth.php

class Auth
{
    public static function check(): bool
    {
        return !empty($_SESSION['gestao_user']) && is_array($_SESSION['gestao_user']);
    }

    public static function user(): ?array
    {
        return self::check() ? $_SESSION['gestao_user'] : null;
    }

    public static function login(array $usuario): void
    {
        $perfil = strtoupper((string) ($usuario['perfil'] ?? $usuario['role'] ?? 'CLIENTE'));
        $nome = trim((string) ($usuario['nome'] ?? $usuario['name'] ?? 'Usuário'));
        $email = trim((string) ($usuario['email'] ?? ''));

        $_SESSION['gestao_user'] = [
            'id' => (int) ($usuario['id'] ?? 0),
            'empresa_id' => (int) ($usuario['empresa_id'] ?? 1),
            'cliente_id' => !empty($usuario['cliente_id']) ? (int) $usuario['cliente_id'] : null,
            'name' => $nome,
            'email' => $email,
            'role' => $perfil,
            'status' => strtoupper((string) ($usuario['status'] ?? 'ATIVO')),
            'ultimo_login_em' => $usuario['ultimo_login_em'] ?? null,
        ];

        $_SESSION['usuario_nome'] = $nome;
        session_regenerate_id(true);
    }

    public static function requireLogin(): void
    {
        if (!self::check()) {
            header('Location: ' . URL_BASE . 'login');
            exit;
        }
    }

    public static function id(): int
    {
        return (int) (self::user()['id'] ?? 0);
    }

    public static function empresaId(): int
    {
        return (int) (self::user()['empresa_id'] ?? 1);
    }

    public static function clienteId(): ?int
    {
        $clienteId = self::user()['cliente_id'] ?? null;
        return $clienteId ? (int) $clienteId : null;
    }

    public static function role(): string
    {
        return (string) (self::user()['role'] ?? '');
    }

    public static function isAdmin(): bool
    {
        return self::role() === 'ADMIN';
    }

    public static function isCliente(): bool
    {
        return self::role() === 'CLIENTE';
    }

    public static function requireAdmin(): void
    {
        self::requireLogin();

        if (!self::isAdmin()) {
            http_response_code(403);
            exit('Acesso negado.');
        }
    }

    public static function canAccessCliente(int $clienteId): bool
    {
        if (self::isAdmin()) {
            return true;
        }

        return self::isCliente() && self::clienteId() === $clienteId;
    }

    public static function logout(): void
    {
        unset($_SESSION['gestao_user'], $_SESSION['usuario_nome']);
        session_regenerate_id(true);
        header('Location: ' . URL_BASE . 'login');
        exit;
    }
}
