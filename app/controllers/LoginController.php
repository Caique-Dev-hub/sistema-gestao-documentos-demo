<?php
// ARQUIVO: app/controllers/LoginController.php

require_once __DIR__ . '/../core/Auth.php';

class LoginController extends Controller
{
    private GestaoDocumentos $model;

    public function __construct()
    {
        env();
        $this->model = new GestaoDocumentos();
    }

    public function index(): void
    {
        if (Auth::check()) {
            header('Location: ' . URL_BASE . 'app');
            exit;
        }

        $erro = $_SESSION['flash_login'] ?? '';
        unset($_SESSION['flash_login']);
        $this->view('login', ['erro' => $erro]);
    }

    public function auth(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . URL_BASE . 'login');
            exit;
        }

        $email = mb_strtolower(trim((string) ($_POST['email'] ?? '')));
        $senha = trim((string) ($_POST['senha'] ?? ''));

        if ($email === '' || $senha === '') {
            $_SESSION['flash_login'] = 'Informe e-mail e senha para continuar.';
            header('Location: ' . URL_BASE . 'login');
            exit;
        }

        $usuario = $this->model->buscarUsuarioPorEmail($email);

        if (!$usuario) {
            $_SESSION['flash_login'] = 'Usuário não encontrado.';
            header('Location: ' . URL_BASE . 'login');
            exit;
        }

        if (strtoupper((string) ($usuario['status'] ?? 'ATIVO')) !== 'ATIVO') {
            $_SESSION['flash_login'] = 'Seu acesso está bloqueado. Fale com o administrador.';
            header('Location: ' . URL_BASE . 'login');
            exit;
        }

        if (!password_verify($senha, (string) $usuario['senha_hash'])) {
            $_SESSION['flash_login'] = 'Credenciais inválidas.';
            header('Location: ' . URL_BASE . 'login');
            exit;
        }

        $this->model->atualizarUltimoLogin((int) $usuario['id']);
        $this->model->criarLogAtividade([
            'empresa_id' => (int) $usuario['empresa_id'],
            'usuario_id' => (int) $usuario['id'],
            'cliente_id' => !empty($usuario['cliente_id']) ? (int) $usuario['cliente_id'] : null,
            'acao' => 'LOGIN',
            'entidade' => 'usuarios',
            'entidade_id' => (int) $usuario['id'],
            'descricao' => 'Login realizado na plataforma.',
            'ip' => $_SERVER['REMOTE_ADDR'] ?? null,
        ]);

        $usuario['ultimo_login_em'] = date('Y-m-d H:i:s');
        Auth::login($usuario);

        header('Location: ' . URL_BASE . 'app');
        exit;
    }

    public function logout(): void
    {
        if (Auth::check()) {
            $usuario = Auth::user();

            $this->model->criarLogAtividade([
                'empresa_id' => (int) ($usuario['empresa_id'] ?? 1),
                'usuario_id' => (int) ($usuario['id'] ?? 0),
                'cliente_id' => !empty($usuario['cliente_id']) ? (int) $usuario['cliente_id'] : null,
                'acao' => 'LOGOUT',
                'entidade' => 'usuarios',
                'entidade_id' => (int) ($usuario['id'] ?? 0),
                'descricao' => 'Logout realizado na plataforma.',
                'ip' => $_SERVER['REMOTE_ADDR'] ?? null,
            ]);
        }

        Auth::logout();
    }
}
