<?php
// ARQUIVO: app/controllers/AppController.php

require_once __DIR__ . '/../core/Auth.php';

class AppController extends Controller
{
    private GestaoDocumentos $model;
    private int $empresaId = 1;

    public function __construct()
    {
        env();
        $this->model = new GestaoDocumentos();

        if (Auth::check()) {
            $this->empresaId = Auth::empresaId() ?: 1;
        }
    }

    private function scopeClienteId(): ?int
    {
        return Auth::isCliente() ? Auth::clienteId() : null;
    }

    private function clienteAtual(): ?array
    {
        $clienteId = $this->scopeClienteId();
        if (!$clienteId) {
            return null;
        }

        $cliente = $this->model->buscarClientePorId($clienteId, $this->empresaId);
        return $cliente ?: null;
    }

    private function clienteTemDemo(): bool
    {
        $cliente = $this->clienteAtual();
        return !empty($cliente['demo_habilitado']);
    }

    private function notificationBadge(): int
    {
        if (!Auth::isCliente() || !Auth::clienteId()) {
            return 0;
        }

        return $this->model->contarNotificacoesNaoLidas(
            Auth::clienteId(),
            $this->empresaId,
            Auth::id()
        );
    }

    public function index(): void
    {
        Auth::requireLogin();

        $scopeClienteId = $this->scopeClienteId();
        $stats = $this->model->totaisDashboard($this->empresaId, $scopeClienteId);
        $organizacoes = $this->model->listarSetoresComTotais($this->empresaId, $scopeClienteId, 'NORMAL');
        $recentes = $this->model->listarDocumentos(
            ['ordenacao' => 'upload_desc', 'scope_cliente_id' => $scopeClienteId, 'ambiente' => 'NORMAL'],
            $this->empresaId,
            6,
            $scopeClienteId
        );

        $notificacoes = [];
        if (Auth::isCliente() && $scopeClienteId) {
            $notificacoes = $this->model->listarNotificacoes(
                $scopeClienteId,
                $this->empresaId,
                Auth::id(),
                5
            );
        }

        $this->render('home', [
            'pageTitle' => Auth::isAdmin() ? 'Visão geral' : 'Minha área',
            'activeTab' => 'home',
            'stats' => $stats,
            'setores' => $organizacoes,
            'recentes' => $recentes,
            'notificacoes' => $notificacoes,
            'notificationBadge' => $this->notificationBadge(),
            'isAdmin' => Auth::isAdmin(),
            'clienteAtual' => $this->clienteAtual(),
            'flash' => $this->flash(),
        ]);
    }

    public function setores(): void
    {
        Auth::requireLogin();

        $scopeClienteId = $this->scopeClienteId();

        $this->render('setores', [
            'pageTitle' => 'Organização',
            'activeTab' => 'organizacao',
            'setores' => $this->model->listarSetoresComTotais($this->empresaId, $scopeClienteId, 'NORMAL'),
            'isAdmin' => Auth::isAdmin(),
            'notificationBadge' => $this->notificationBadge(),
            'flash' => $this->flash(),
        ]);
    }

    public function setor($id = ''): void
    {
        Auth::requireLogin();

        $setorId = (int) $id;
        $scopeClienteId = $this->scopeClienteId();
        $setor = $this->model->buscarSetor($setorId, $this->empresaId, $scopeClienteId, 'NORMAL');

        if (!$setor) {
            $this->setFlash('error', 'Estrutura não encontrada.');
            header('Location: ' . URL_BASE . 'app/setores');
            exit;
        }

        $this->render('setor', [
            'pageTitle' => $setor['nome'],
            'activeTab' => 'organizacao',
            'setor' => $setor,
            'tipos' => $this->model->listarTipos($this->empresaId, $setorId, $scopeClienteId, 'NORMAL'),
            'documentos' => $this->model->listarDocumentos(
                ['setor_id' => $setorId, 'ordenacao' => 'data_documento_desc', 'scope_cliente_id' => $scopeClienteId, 'ambiente' => 'NORMAL'],
                $this->empresaId,
                50,
                $scopeClienteId
            ),
            'statsSetor' => $this->model->totaisSetor($setorId, $this->empresaId, $scopeClienteId, 'NORMAL'),
            'isAdmin' => Auth::isAdmin(),
            'notificationBadge' => $this->notificationBadge(),
            'flash' => $this->flash(),
        ]);
    }

    public function clientes(): void
    {
        Auth::requireLogin();

        if (Auth::isCliente()) {
            $cliente = $this->clienteAtual();

            $this->render('clientes', [
                'pageTitle' => 'Meu cadastro',
                'activeTab' => 'clientes',
                'clientes' => $cliente ? [$cliente] : [],
                'clienteAtual' => $cliente,
                'isAdmin' => false,
                'notificationBadge' => $this->notificationBadge(),
                'flash' => $this->flash(),
            ]);
            return;
        }

        $this->render('clientes', [
            'pageTitle' => 'Clientes vinculados',
            'activeTab' => 'clientes',
            'clientes' => $this->model->listarClientesComTotais($this->empresaId),
            'clienteAtual' => null,
            'isAdmin' => true,
            'notificationBadge' => 0,
            'flash' => $this->flash(),
        ]);
    }

    public function tipos(): void
    {
        Auth::requireLogin();

        $scopeClienteId = $this->scopeClienteId();

        $this->render('tipos', [
            'pageTitle' => 'Categorias',
            'activeTab' => 'categorias',
            'setores' => $this->model->listarSetores($this->empresaId, $scopeClienteId, 'NORMAL'),
            'tipos' => $this->model->listarTipos($this->empresaId, null, $scopeClienteId, 'NORMAL'),
            'canManage' => true,
            'notificationBadge' => $this->notificationBadge(),
            'flash' => $this->flash(),
            'isAdmin' => Auth::isAdmin(),
        ]);
    }

    public function documentos(): void
    {
        Auth::requireLogin();

        $scopeClienteId = $this->scopeClienteId();
        $filtros = [
            'setor_id' => $_GET['setor_id'] ?? '',
            'cliente_id' => Auth::isCliente() ? ($scopeClienteId ?? '') : ($_GET['cliente_id'] ?? ''),
            'tipo_documento_id' => $_GET['tipo_documento_id'] ?? '',
            'mes_referencia' => $_GET['mes_referencia'] ?? '',
            'busca' => $_GET['busca'] ?? '',
            'ordenacao' => $_GET['ordenacao'] ?? 'data_documento_desc',
            'scope_cliente_id' => $scopeClienteId,
            'ambiente' => 'NORMAL',
        ];

        $this->render('documentos', [
            'pageTitle' => 'Documentos',
            'activeTab' => 'documentos',
            'documentos' => $this->model->listarDocumentos($filtros, $this->empresaId, 100, $scopeClienteId),
            'setores' => $this->model->listarSetores($this->empresaId, $scopeClienteId, 'NORMAL'),
            'clientes' => $this->model->listarClientes($this->empresaId, $scopeClienteId),
            'tipos' => $this->model->listarTipos($this->empresaId, null, $scopeClienteId, 'NORMAL'),
            'filtros' => $filtros,
            'canManage' => Auth::isAdmin(),
            'notificationBadge' => $this->notificationBadge(),
            'flash' => $this->flash(),
        ]);
    }

    public function upload(): void
    {
        Auth::requireLogin();

        $scopeClienteId = $this->scopeClienteId();
        $this->render('upload', [
            'pageTitle' => 'Novo documento',
            'activeTab' => 'upload',
            'setores' => $this->model->listarSetores($this->empresaId, $scopeClienteId, 'NORMAL'),
            'clientes' => $this->model->listarClientes($this->empresaId, $scopeClienteId),
            'tipos' => $this->model->listarTipos($this->empresaId, null, $scopeClienteId, 'NORMAL'),
            'notificationBadge' => $this->notificationBadge(),
            'flash' => $this->flash(),
            'isAdmin' => Auth::isAdmin(),
        ]);
    }

    public function notificacoes(): void
    {
        Auth::requireLogin();

        if (!Auth::isCliente() || !Auth::clienteId()) {
            $this->setFlash('error', 'Essa área é destinada ao login do cliente.');
            header('Location: ' . URL_BASE . 'app');
            exit;
        }

        $this->render('notificacoes', [
            'pageTitle' => 'Notificações',
            'activeTab' => 'notificacoes',
            'notificacoes' => $this->model->listarNotificacoes(
                Auth::clienteId(),
                $this->empresaId,
                Auth::id(),
                100
            ),
            'notificationBadge' => $this->notificationBadge(),
            'flash' => $this->flash(),
        ]);
    }

    public function marcarNotificacao($id = ''): void
    {
        Auth::requireLogin();

        if (!Auth::isCliente() || !Auth::clienteId()) {
            header('Location: ' . URL_BASE . 'app');
            exit;
        }

        $this->model->marcarNotificacaoLida(
            (int) $id,
            Auth::clienteId(),
            $this->empresaId,
            Auth::id()
        );

        $this->model->criarLogAtividade([
            'empresa_id' => $this->empresaId,
            'usuario_id' => Auth::id(),
            'cliente_id' => Auth::clienteId(),
            'acao' => 'NOTIFICACAO_LIDA',
            'entidade' => 'notificacoes',
            'entidade_id' => (int) $id,
            'descricao' => 'Notificação marcada como lida.',
            'ip' => $_SERVER['REMOTE_ADDR'] ?? null,
        ]);

        header('Location: ' . URL_BASE . 'app/notificacoes');
        exit;
    }

    public function perfil(): void
    {
        Auth::requireLogin();

        $this->render('perfil', [
            'pageTitle' => 'Perfil',
            'activeTab' => 'perfil',
            'stats' => $this->model->totaisDashboard($this->empresaId, $this->scopeClienteId()),
            'clienteAtual' => $this->clienteAtual(),
            'notificationBadge' => $this->notificationBadge(),
            'flash' => $this->flash(),
        ]);
    }

    public function atualizarPerfil(): void
    {
        Auth::requireLogin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . URL_BASE . 'app/perfil');
            exit;
        }

        $nome = trim((string) ($_POST['nome'] ?? ''));
        $email = mb_strtolower(trim((string) ($_POST['email'] ?? '')));
        $telefone = trim((string) ($_POST['telefone'] ?? ''));
        $cargo = trim((string) ($_POST['cargo'] ?? ''));

        if ($nome === '' || $email === '') {
            $this->setFlash('error', 'Preencha nome e e-mail para atualizar o perfil.');
            header('Location: ' . URL_BASE . 'app/perfil');
            exit;
        }

        if ($this->model->emailUsuarioExiste($email, $this->empresaId, Auth::id())) {
            $this->setFlash('error', 'Já existe outro usuário com esse e-mail.');
            header('Location: ' . URL_BASE . 'app/perfil');
            exit;
        }

        $ok = $this->model->atualizarPerfilUsuario(Auth::id(), $this->empresaId, [
            'nome' => $nome,
            'email' => $email,
            'telefone' => $telefone,
            'cargo' => $cargo,
        ]);

        if ($ok) {
            $usuarioAtual = Auth::user() ?? [];
            $usuarioAtual['nome'] = $nome;
            $usuarioAtual['email'] = $email;
            Auth::login($usuarioAtual);

            $this->model->criarLogAtividade([
                'empresa_id' => $this->empresaId,
                'usuario_id' => Auth::id(),
                'cliente_id' => Auth::clienteId(),
                'acao' => 'ATUALIZAR_PERFIL',
                'entidade' => 'usuarios',
                'entidade_id' => Auth::id(),
                'descricao' => 'Dados do perfil atualizados.',
                'ip' => $_SERVER['REMOTE_ADDR'] ?? null,
            ]);
        }

        $this->setFlash($ok ? 'success' : 'error', $ok ? 'Perfil atualizado com sucesso.' : 'Não foi possível atualizar o perfil.');
        header('Location: ' . URL_BASE . 'app/perfil');
        exit;
    }

    public function alterarSenha(): void
    {
        Auth::requireLogin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . URL_BASE . 'app/perfil');
            exit;
        }

        $senhaAtual = trim((string) ($_POST['senha_atual'] ?? ''));
        $novaSenha = trim((string) ($_POST['nova_senha'] ?? ''));
        $confirmacao = trim((string) ($_POST['confirmar_senha'] ?? ''));

        $usuario = $this->model->buscarUsuarioPorId(Auth::id(), $this->empresaId);
        if (!$usuario || !password_verify($senhaAtual, (string) ($usuario['senha_hash'] ?? ''))) {
            $this->setFlash('error', 'A senha atual não confere.');
            header('Location: ' . URL_BASE . 'app/perfil');
            exit;
        }

        if (strlen($novaSenha) < 6) {
            $this->setFlash('error', 'A nova senha precisa ter pelo menos 6 caracteres.');
            header('Location: ' . URL_BASE . 'app/perfil');
            exit;
        }

        if ($novaSenha !== $confirmacao) {
            $this->setFlash('error', 'A confirmação da nova senha não confere.');
            header('Location: ' . URL_BASE . 'app/perfil');
            exit;
        }

        $ok = $this->model->atualizarSenhaUsuario(Auth::id(), $this->empresaId, password_hash($novaSenha, PASSWORD_DEFAULT));

        if ($ok) {
            $this->model->criarLogAtividade([
                'empresa_id' => $this->empresaId,
                'usuario_id' => Auth::id(),
                'cliente_id' => Auth::clienteId(),
                'acao' => 'ALTERAR_SENHA',
                'entidade' => 'usuarios',
                'entidade_id' => Auth::id(),
                'descricao' => 'Senha alterada pelo próprio usuário.',
                'ip' => $_SERVER['REMOTE_ADDR'] ?? null,
            ]);
        }

        $this->setFlash($ok ? 'success' : 'error', $ok ? 'Senha alterada com sucesso.' : 'Não foi possível alterar a senha.');
        header('Location: ' . URL_BASE . 'app/perfil');
        exit;
    }

    public function clienteDemo(): void
    {
        Auth::requireLogin();

        $scopeClienteId = $this->scopeClienteId();
        $clienteAtual = $this->clienteAtual();
        $clienteDemoAtivo = Auth::isAdmin() ? true : $this->clienteTemDemo();

        $this->render('cliente_demo', [
            'pageTitle' => 'Cliente DEMO',
            'activeTab' => 'cliente_demo',
            'isAdmin' => Auth::isAdmin(),
            'clienteAtual' => $clienteAtual,
            'clienteDemoAtivo' => $clienteDemoAtivo,
            'setoresDemo' => $this->model->listarSetoresComTotais($this->empresaId, Auth::isAdmin() ? null : $scopeClienteId, 'DEMO'),
            'tiposDemo' => $this->model->listarTipos($this->empresaId, null, Auth::isAdmin() ? null : $scopeClienteId, 'DEMO'),
            'documentosDemo' => $this->model->listarDocumentos([
                'ordenacao' => 'upload_desc',
                'ambiente' => 'DEMO',
                'cliente_id' => Auth::isAdmin() ? ($_GET['cliente_id'] ?? '') : ($scopeClienteId ?? ''),
                'busca' => $_GET['busca'] ?? '',
            ], $this->empresaId, 100, Auth::isAdmin() ? null : $scopeClienteId),
            'solicitacoesDemo' => $this->model->listarSolicitacoesDemo($this->empresaId, Auth::isAdmin() ? (!empty($_GET['cliente_id']) ? (int) $_GET['cliente_id'] : null) : $scopeClienteId, ''),
            'clientesDemo' => $this->model->listarClientes($this->empresaId, null, true),
            'notificationBadge' => $this->notificationBadge(),
            'flash' => $this->flash(),
        ]);
    }

    public function salvarSolicitacaoDemo(): void
    {
        Auth::requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . URL_BASE . 'app/clienteDemo');
            exit;
        }

        $clienteId = (int) ($_POST['cliente_id'] ?? 0);
        $titulo = trim((string) ($_POST['titulo'] ?? ''));
        $setorId = !empty($_POST['setor_id']) ? (int) $_POST['setor_id'] : null;
        $tipoId = !empty($_POST['tipo_documento_id']) ? (int) $_POST['tipo_documento_id'] : null;

        if ($clienteId <= 0 || $titulo === '') {
            $this->setFlash('error', 'Preencha cliente e título da solicitação DEMO.');
            header('Location: ' . URL_BASE . 'app/clienteDemo');
            exit;
        }

        $ok = $this->model->criarSolicitacaoDemo([
            'empresa_id' => $this->empresaId,
            'cliente_id' => $clienteId,
            'setor_id' => $setorId,
            'tipo_documento_id' => $tipoId,
            'titulo' => $titulo,
            'descricao' => $_POST['descricao'] ?? '',
            'created_by_user_id' => Auth::id(),
        ]);

        if ($ok) {
            $this->model->enviarNotificacao([
                'empresa_id' => $this->empresaId,
                'cliente_id' => $clienteId,
                'usuario_id' => null,
                'titulo' => 'Nova solicitação da DEMO',
                'mensagem' => 'Existe uma nova solicitação de documento pendente na sua área Cliente DEMO.',
                'tipo' => 'INFO',
            ]);
        }

        $this->setFlash($ok ? 'success' : 'error', $ok ? 'Solicitação DEMO criada com sucesso.' : 'Não foi possível criar a solicitação DEMO.');
        header('Location: ' . URL_BASE . 'app/clienteDemo');
        exit;
    }

    public function acessos(): void
    {
        Auth::requireAdmin();

        $filtros = [
            'perfil' => $_GET['perfil'] ?? '',
            'status' => $_GET['status'] ?? '',
            'busca' => $_GET['busca'] ?? '',
        ];

        $this->render('acessos', [
            'pageTitle' => 'Liberar acesso',
            'activeTab' => 'acessos',
            'usuarios' => $this->model->listarUsuarios($this->empresaId, $filtros),
            'clientes' => $this->model->listarClientes($this->empresaId),
            'filtros' => $filtros,
            'notificationBadge' => 0,
            'flash' => $this->flash(),
        ]);
    }

    public function usuario($id = ''): void
    {
        Auth::requireAdmin();

        $usuarioId = (int) $id;
        $usuario = $this->model->buscarUsuarioPorId($usuarioId, $this->empresaId);

        if (!$usuario) {
            $this->setFlash('error', 'Usuário não encontrado.');
            header('Location: ' . URL_BASE . 'app/acessos');
            exit;
        }

        $documentos = $this->model->listarDocumentosPorUsuario($usuarioId, $this->empresaId, 15);

        $this->render('usuario', [
            'pageTitle' => 'Usuário',
            'activeTab' => 'acessos',
            'usuarioDetalhe' => $usuario,
            'logs' => $this->model->listarLogsUsuario($usuarioId, $this->empresaId, 100),
            'documentos' => $documentos,
            'notificationBadge' => 0,
            'flash' => $this->flash(),
        ]);
    }

    public function salvarUsuario(): void
    {
        Auth::requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . URL_BASE . 'app/acessos');
            exit;
        }

        $nome = trim((string) ($_POST['nome'] ?? ''));
        $email = mb_strtolower(trim((string) ($_POST['email'] ?? '')));
        $perfil = strtoupper(trim((string) ($_POST['perfil'] ?? 'CLIENTE')));
        $senha = trim((string) ($_POST['senha'] ?? '123456'));
        $telefone = trim((string) ($_POST['telefone'] ?? ''));
        $cargo = trim((string) ($_POST['cargo'] ?? ''));
        $clienteId = !empty($_POST['cliente_id']) ? (int) $_POST['cliente_id'] : null;
        $clienteNome = trim((string) ($_POST['cliente_nome'] ?? ''));
        $clienteDocumento = trim((string) ($_POST['cliente_documento'] ?? ''));
        $clienteResponsavel = trim((string) ($_POST['cliente_responsavel'] ?? ''));
        $clienteObservacao = trim((string) ($_POST['cliente_observacao'] ?? ''));
        $clienteDemo = !empty($_POST['cliente_demo_habilitado']);
        $suporteEmail = trim((string) ($_POST['suporte_email'] ?? ''));
        $suporteTelefone = trim((string) ($_POST['suporte_telefone'] ?? ''));

        if ($nome === '' || $email === '') {
            $this->setFlash('error', 'Preencha nome e e-mail.');
            header('Location: ' . URL_BASE . 'app/acessos');
            exit;
        }

        if ($this->model->emailUsuarioExiste($email, $this->empresaId)) {
            $this->setFlash('error', 'Já existe um login com esse e-mail.');
            header('Location: ' . URL_BASE . 'app/acessos');
            exit;
        }

        $usuarioDados = [
            'empresa_id' => $this->empresaId,
            'cliente_id' => null,
            'nome' => $nome,
            'email' => $email,
            'senha_hash' => password_hash($senha, PASSWORD_DEFAULT),
            'perfil' => $perfil,
            'status' => 'ATIVO',
            'telefone' => $telefone,
            'cargo' => $cargo,
        ];

        $ok = false;
        $clienteLogId = null;
        $usuarioIdCriado = null;

        if ($perfil === 'CLIENTE') {
            if ($clienteId) {
                $cliente = $this->model->buscarClientePorId($clienteId, $this->empresaId);
                if (!$cliente) {
                    $this->setFlash('error', 'Cliente selecionado não encontrado.');
                    header('Location: ' . URL_BASE . 'app/acessos');
                    exit;
                }
                if ($this->model->clientePossuiUsuario($clienteId, $this->empresaId)) {
                    $this->setFlash('error', 'Esse cliente já possui um login vinculado.');
                    header('Location: ' . URL_BASE . 'app/acessos');
                    exit;
                }

                $usuarioDados['cliente_id'] = $clienteId;
                $usuarioIdCriado = $this->model->criarUsuarioRetornandoId($usuarioDados);
                $ok = (bool) $usuarioIdCriado;
                $clienteLogId = $clienteId;
            } else {
                if ($clienteNome === '') {
                    $this->setFlash('error', 'Para perfil CLIENTE, selecione um cliente existente ou preencha os dados de um cliente novo.');
                    header('Location: ' . URL_BASE . 'app/acessos');
                    exit;
                }

                $usuarioIdCriado = $this->model->criarClienteEUsuario([
                    'empresa_id' => $this->empresaId,
                    'nome' => $clienteNome,
                    'documento' => $clienteDocumento,
                    'responsavel' => $clienteResponsavel,
                    'observacao' => $clienteObservacao,
                    'demo_habilitado' => $clienteDemo,
                    'suporte_email' => $suporteEmail,
                    'suporte_telefone' => $suporteTelefone,
                ], $usuarioDados);

                $ok = (bool) $usuarioIdCriado;
            }
        } else {
            $usuarioIdCriado = $this->model->criarUsuarioRetornandoId($usuarioDados);
            $ok = (bool) $usuarioIdCriado;
        }

        if ($ok) {
            $this->model->criarLogAtividade([
                'empresa_id' => $this->empresaId,
                'usuario_id' => Auth::id(),
                'cliente_id' => $clienteLogId,
                'acao' => 'CRIAR_USUARIO',
                'entidade' => 'usuarios',
                'descricao' => 'Novo login criado pelo administrador.',
                'ip' => $_SERVER['REMOTE_ADDR'] ?? null,
            ]);
        }

        $this->setFlash($ok ? 'success' : 'error', $ok ? 'Acesso criado com sucesso.' : 'Não foi possível criar o acesso.');
        header('Location: ' . URL_BASE . 'app/acessos');
        exit;
    }

    public function alternarStatusUsuario($id = ''): void
    {
        Auth::requireAdmin();

        $usuario = $this->model->buscarUsuarioPorId((int) $id, $this->empresaId);
        if (!$usuario) {
            $this->setFlash('error', 'Usuário não encontrado.');
            header('Location: ' . URL_BASE . 'app/acessos');
            exit;
        }

        $novoStatus = strtoupper((string) $usuario['status']) === 'ATIVO' ? 'BLOQUEADO' : 'ATIVO';
        $ok = $this->model->atualizarStatusUsuario((int) $usuario['id'], $this->empresaId, $novoStatus);

        if ($ok) {
            $this->model->criarLogAtividade([
                'empresa_id' => $this->empresaId,
                'usuario_id' => Auth::id(),
                'cliente_id' => !empty($usuario['cliente_id']) ? (int) $usuario['cliente_id'] : null,
                'acao' => 'ALTERAR_STATUS_USUARIO',
                'entidade' => 'usuarios',
                'entidade_id' => (int) $usuario['id'],
                'descricao' => 'Status alterado para ' . $novoStatus . '.',
                'ip' => $_SERVER['REMOTE_ADDR'] ?? null,
            ]);
        }

        $this->setFlash($ok ? 'success' : 'error', $ok ? 'Status atualizado com sucesso.' : 'Não foi possível alterar o status.');
        header('Location: ' . URL_BASE . 'app/usuario/' . (int) $usuario['id']);
        exit;
    }

    public function enviarNotificacao(): void
    {
        Auth::requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . URL_BASE . 'app/acessos');
            exit;
        }

        $clienteId = (int) ($_POST['cliente_id'] ?? 0);
        $usuarioId = !empty($_POST['usuario_id']) ? (int) $_POST['usuario_id'] : null;
        $titulo = trim((string) ($_POST['titulo'] ?? ''));
        $mensagem = trim((string) ($_POST['mensagem'] ?? ''));

        if ($clienteId <= 0 || $titulo === '' || $mensagem === '') {
            $this->setFlash('error', 'Preencha cliente, título e mensagem.');
            header('Location: ' . URL_BASE . 'app/acessos');
            exit;
        }

        $ok = $this->model->enviarNotificacao([
            'empresa_id' => $this->empresaId,
            'cliente_id' => $clienteId,
            'usuario_id' => $usuarioId,
            'titulo' => $titulo,
            'mensagem' => $mensagem,
            'tipo' => 'INFO',
        ]);

        if ($ok) {
            $this->model->criarLogAtividade([
                'empresa_id' => $this->empresaId,
                'usuario_id' => Auth::id(),
                'cliente_id' => $clienteId,
                'acao' => 'ENVIAR_NOTIFICACAO',
                'entidade' => 'notificacoes',
                'descricao' => 'Notificação enviada ao cliente.',
                'ip' => $_SERVER['REMOTE_ADDR'] ?? null,
            ]);
        }

        $redirect = $usuarioId ? ('app/usuario/' . $usuarioId) : 'app/acessos';
        $this->setFlash($ok ? 'success' : 'error', $ok ? 'Notificação enviada com sucesso.' : 'Não foi possível enviar a notificação.');
        header('Location: ' . URL_BASE . $redirect);
        exit;
    }

    public function salvarSetor(): void
    {
        Auth::requireLogin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['nome'])) {
            header('Location: ' . URL_BASE . 'app/setores');
            exit;
        }

        $ambiente = Auth::isAdmin() ? (string) ($_POST['ambiente'] ?? 'NORMAL') : 'NORMAL';
        $ok = $this->model->adicionarSetor([
            'empresa_id' => $this->empresaId,
            'cliente_id' => Auth::isCliente() ? Auth::clienteId() : null,
            'ambiente' => $ambiente,
            'nome' => $_POST['nome'],
            'descricao' => $_POST['descricao'] ?? '',
        ]);

        $this->setFlash($ok ? 'success' : 'error', $ok ? 'Estrutura cadastrada com sucesso.' : 'Não foi possível cadastrar a estrutura.');
        $redirect = strtoupper($ambiente) === 'DEMO' ? 'app/clienteDemo' : 'app/setores';
        header('Location: ' . URL_BASE . $redirect);
        exit;
    }

    public function salvarCliente(): void
    {
        Auth::requireAdmin();
        $this->setFlash('error', 'O cadastro de cliente foi centralizado na área de Liberar acesso.');
        header('Location: ' . URL_BASE . 'app/acessos');
        exit;
    }

    public function salvarTipo(): void
    {
        Auth::requireLogin();

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['nome']) && !empty($_POST['setor_id'])) {
            $ambiente = Auth::isAdmin() ? (string) ($_POST['ambiente'] ?? 'NORMAL') : 'NORMAL';
            $setor = $this->model->buscarSetor((int) $_POST['setor_id'], $this->empresaId, $this->scopeClienteId(), $ambiente);

            if (!$setor) {
                $this->setFlash('error', 'Estrutura selecionada não encontrada.');
                $redirect = !empty($_POST['redirect']) ? $_POST['redirect'] : 'app/tipos';
                header('Location: ' . URL_BASE . ltrim($redirect, '/'));
                exit;
            }

            $ok = $this->model->adicionarTipo([
                'empresa_id' => $this->empresaId,
                'setor_id' => (int) $_POST['setor_id'],
                'cliente_id' => Auth::isCliente() ? Auth::clienteId() : ($setor['cliente_id'] ?? null),
                'ambiente' => $ambiente,
                'nome' => $_POST['nome'],
            ]);
            $this->setFlash($ok ? 'success' : 'error', $ok ? 'Categoria criada com sucesso.' : 'Não foi possível criar a categoria.');
        }

        $redirect = !empty($_POST['redirect']) ? $_POST['redirect'] : 'app/tipos';
        header('Location: ' . URL_BASE . ltrim($redirect, '/'));
        exit;
    }

    public function salvarDocumento(): void
    {
        Auth::requireLogin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . URL_BASE . 'app/upload');
            exit;
        }

        $clienteId = Auth::isCliente() ? (Auth::clienteId() ?? 0) : (int) ($_POST['cliente_id'] ?? 0);

        if ($clienteId <= 0) {
            $this->setFlash('error', 'Selecione o cliente que vai receber este documento.');
            header('Location: ' . URL_BASE . 'app/upload');
            exit;
        }

        $upload = $this->realizarUpload($_FILES['arquivo'] ?? []);
        if (!$upload['ok']) {
            $this->setFlash('error', $upload['message']);
            header('Location: ' . URL_BASE . 'app/upload');
            exit;
        }

        $ok = $this->model->adicionarDocumento([
            'empresa_id' => $this->empresaId,
            'setor_id' => (int) ($_POST['setor_id'] ?? 0),
            'cliente_id' => $clienteId,
            'usuario_id' => Auth::id(),
            'tipo_documento_id' => (int) ($_POST['tipo_documento_id'] ?? 0),
            'ambiente' => 'NORMAL',
            'nome_documento' => $_POST['nome_documento'] ?? '',
            'data_documento' => $_POST['data_documento'] ?? date('Y-m-d'),
            'numero_documento' => $_POST['numero_documento'] ?? '',
            'valor' => $_POST['valor'] ?? '',
            'observacao' => $_POST['observacao'] ?? '',
            'arquivo_url' => $upload['arquivo_url'],
            'arquivo_nome_original' => $upload['arquivo_nome_original'],
            'arquivo_extensao' => $upload['arquivo_extensao'],
            'arquivo_mime' => $upload['arquivo_mime'],
            'uploaded_by' => Auth::user()['name'] ?? 'Usuário App',
            'origem_upload' => Auth::isAdmin() ? 'ADMIN' : 'CLIENTE',
        ]);

        if ($ok) {
            $this->model->criarLogAtividade([
                'empresa_id' => $this->empresaId,
                'usuario_id' => Auth::id(),
                'cliente_id' => $clienteId,
                'acao' => 'UPLOAD_DOCUMENTO',
                'entidade' => 'documentos',
                'descricao' => 'Documento enviado no ambiente normal.',
                'ip' => $_SERVER['REMOTE_ADDR'] ?? null,
            ]);

            if (Auth::isCliente()) {
                $this->model->enviarNotificacao([
                    'empresa_id' => $this->empresaId,
                    'cliente_id' => $clienteId,
                    'usuario_id' => null,
                    'titulo' => 'Novo arquivo enviado pelo cliente',
                    'mensagem' => 'Um novo arquivo foi anexado pelo cliente no ambiente normal.',
                    'tipo' => 'INFO',
                ]);
            } else {
                $this->model->enviarNotificacao([
                    'empresa_id' => $this->empresaId,
                    'cliente_id' => $clienteId,
                    'usuario_id' => null,
                    'titulo' => 'Novo documento disponível',
                    'mensagem' => 'Um novo documento foi enviado para sua área.',
                    'tipo' => 'INFO',
                ]);
            }

            $this->setFlash('success', 'Documento enviado com sucesso.');
            $redirect = !empty($_POST['setor_redirect']) ? 'app/setor/' . (int) $_POST['setor_id'] : 'app/documentos';
            header('Location: ' . URL_BASE . $redirect);
            exit;
        }

        $filePath = dirname(__DIR__, 2) . '/public/' . ltrim($upload['arquivo_url'], '/');
        if (file_exists($filePath)) {
            unlink($filePath);
        }

        $this->setFlash('error', 'Não foi possível salvar o documento no banco.');
        header('Location: ' . URL_BASE . 'app/upload');
        exit;
    }

    public function salvarDocumentoDemo(): void
    {
        Auth::requireLogin();

        if (Auth::isCliente() && !$this->clienteTemDemo()) {
            $this->setFlash('error', 'Seu acesso ainda não possui o ambiente Cliente DEMO liberado.');
            header('Location: ' . URL_BASE . 'app/clienteDemo');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . URL_BASE . 'app/clienteDemo');
            exit;
        }

        $clienteId = Auth::isCliente() ? (Auth::clienteId() ?? 0) : (int) ($_POST['cliente_id'] ?? 0);
        if ($clienteId <= 0) {
            $this->setFlash('error', 'Selecione o cliente DEMO do envio.');
            header('Location: ' . URL_BASE . 'app/clienteDemo');
            exit;
        }

        $upload = $this->realizarUpload($_FILES['arquivo'] ?? []);
        if (!$upload['ok']) {
            $this->setFlash('error', $upload['message']);
            header('Location: ' . URL_BASE . 'app/clienteDemo');
            exit;
        }

        $origem = Auth::isAdmin() ? 'ADMIN' : 'CLIENTE';
        $ok = $this->model->adicionarDocumento([
            'empresa_id' => $this->empresaId,
            'setor_id' => (int) ($_POST['setor_id'] ?? 0),
            'cliente_id' => $clienteId,
            'usuario_id' => Auth::id(),
            'tipo_documento_id' => (int) ($_POST['tipo_documento_id'] ?? 0),
            'ambiente' => 'DEMO',
            'nome_documento' => $_POST['nome_documento'] ?? '',
            'data_documento' => $_POST['data_documento'] ?? date('Y-m-d'),
            'numero_documento' => $_POST['numero_documento'] ?? '',
            'valor' => $_POST['valor'] ?? '',
            'observacao' => $_POST['observacao'] ?? '',
            'arquivo_url' => $upload['arquivo_url'],
            'arquivo_nome_original' => $upload['arquivo_nome_original'],
            'arquivo_extensao' => $upload['arquivo_extensao'],
            'arquivo_mime' => $upload['arquivo_mime'],
            'uploaded_by' => Auth::user()['name'] ?? 'Usuário App',
            'origem_upload' => $origem,
        ]);

        if ($ok) {
            $this->model->criarLogAtividade([
                'empresa_id' => $this->empresaId,
                'usuario_id' => Auth::id(),
                'cliente_id' => $clienteId,
                'acao' => 'UPLOAD_DOCUMENTO_DEMO',
                'entidade' => 'documentos',
                'descricao' => 'Documento enviado no ambiente Cliente DEMO.',
                'ip' => $_SERVER['REMOTE_ADDR'] ?? null,
            ]);

            $solicitacaoId = !empty($_POST['solicitacao_demo_id']) ? (int) $_POST['solicitacao_demo_id'] : 0;
            if ($solicitacaoId > 0) {
                $documentosRecentes = $this->model->listarDocumentos([
                    'ambiente' => 'DEMO',
                    'cliente_id' => $clienteId,
                    'ordenacao' => 'upload_desc',
                ], $this->empresaId, 1, $clienteId);

                if (!empty($documentosRecentes[0]['id'])) {
                    $this->model->marcarSolicitacaoDemoAtendida($solicitacaoId, $this->empresaId, (int) $documentosRecentes[0]['id']);
                }
            }

            if (Auth::isCliente()) {
                $this->model->enviarNotificacao([
                    'empresa_id' => $this->empresaId,
                    'cliente_id' => $clienteId,
                    'usuario_id' => null,
                    'titulo' => 'Novo arquivo enviado para a DEMO',
                    'mensagem' => 'Um arquivo foi anexado por você no ambiente Cliente DEMO.',
                    'tipo' => 'INFO',
                ]);
            }

            $this->setFlash('success', 'Arquivo DEMO enviado com sucesso.');
            header('Location: ' . URL_BASE . 'app/clienteDemo');
            exit;
        }

        $filePath = dirname(__DIR__, 2) . '/public/' . ltrim($upload['arquivo_url'], '/');
        if (file_exists($filePath)) {
            unlink($filePath);
        }

        $this->setFlash('error', 'Não foi possível salvar o arquivo DEMO.');
        header('Location: ' . URL_BASE . 'app/clienteDemo');
        exit;
    }

    public function visualizar($id = ''): void
    {
        Auth::requireLogin();

        $documento = $this->model->buscarDocumentoPorId((int) $id, $this->empresaId, $this->scopeClienteId());
        if (!$documento) {
            http_response_code(404);
            exit('Documento não encontrado.');
        }

        $filePath = dirname(__DIR__, 2) . '/public/' . ltrim($documento['arquivo_url'], '/');
        if (!file_exists($filePath)) {
            http_response_code(404);
            exit('Arquivo não encontrado.');
        }

        header('Content-Type: ' . $documento['arquivo_mime']);
        header('Content-Disposition: inline; filename="' . basename($documento['arquivo_nome_original']) . '"');
        readfile($filePath);
        exit;
    }

    public function download($id = ''): void
    {
        Auth::requireLogin();

        $documento = $this->model->buscarDocumentoPorId((int) $id, $this->empresaId, $this->scopeClienteId());
        if (!$documento) {
            http_response_code(404);
            exit('Documento não encontrado.');
        }

        $filePath = dirname(__DIR__, 2) . '/public/' . ltrim($documento['arquivo_url'], '/');
        if (!file_exists($filePath)) {
            http_response_code(404);
            exit('Arquivo não encontrado.');
        }

        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($documento['arquivo_nome_original']) . '"');
        header('Content-Length: ' . filesize($filePath));
        readfile($filePath);
        exit;
    }

    public function excluir($id = ''): void
    {
        Auth::requireLogin();

        if (!Auth::isAdmin()) {
            $this->setFlash('error', 'Apenas o administrador pode excluir documentos.');
            header('Location: ' . URL_BASE . 'app/documentos');
            exit;
        }

        $documento = $this->model->buscarDocumentoPorId((int) $id, $this->empresaId, null);
        if ($documento) {
            $filePath = dirname(__DIR__, 2) . '/public/' . ltrim($documento['arquivo_url'], '/');
            if (file_exists($filePath)) {
                unlink($filePath);
            }

            $this->model->excluirDocumento((int) $id, $this->empresaId, null);

            $this->model->criarLogAtividade([
                'empresa_id' => $this->empresaId,
                'usuario_id' => Auth::id(),
                'cliente_id' => !empty($documento['cliente_id']) ? (int) $documento['cliente_id'] : null,
                'acao' => 'EXCLUIR_DOCUMENTO',
                'entidade' => 'documentos',
                'entidade_id' => (int) $id,
                'descricao' => 'Documento excluído na plataforma.',
                'ip' => $_SERVER['REMOTE_ADDR'] ?? null,
            ]);

            $this->setFlash('success', 'Documento excluído com sucesso.');
        }

        header('Location: ' . URL_BASE . 'app/documentos');
        exit;
    }

    private function render(string $view, array $dados = []): void
    {
        extract($dados);
        $user = Auth::user();
        $content = dirname(__DIR__) . '/views/app/' . $view . '.php';
        require dirname(__DIR__) . '/views/layouts/app.php';
    }

    private function setFlash(string $type, string $message): void
    {
        $_SESSION['flash_app'] = ['type' => $type, 'message' => $message];
    }

    private function flash(): ?array
    {
        $flash = $_SESSION['flash_app'] ?? null;
        unset($_SESSION['flash_app']);
        return $flash;
    }

    private function realizarUpload(array $arquivo): array
    {
        if (!isset($arquivo['error']) || $arquivo['error'] !== UPLOAD_ERR_OK) {
            return ['ok' => false, 'message' => 'Selecione um arquivo válido para continuar.'];
        }

        $permitidos = [
            'application/pdf' => 'pdf',
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
        ];

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($arquivo['tmp_name']);
        if (!isset($permitidos[$mime])) {
            return ['ok' => false, 'message' => 'Formato inválido. Envie PDF, JPG ou PNG.'];
        }

        $ano = date('Y');
        $mes = date('m');
        $destinoDir = dirname(__DIR__, 2) . '/public/uploads/documentos/' . $ano . '/' . $mes;
        if (!is_dir($destinoDir) && !mkdir($destinoDir, 0775, true) && !is_dir($destinoDir)) {
            return ['ok' => false, 'message' => 'Não foi possível preparar a pasta de upload.'];
        }

        $ext = $permitidos[$mime];
        $safeName = uniqid('doc_', true) . '.' . $ext;
        $destino = $destinoDir . '/' . $safeName;
        if (!move_uploaded_file($arquivo['tmp_name'], $destino)) {
            return ['ok' => false, 'message' => 'Falha ao mover o arquivo enviado.'];
        }

        return [
            'ok' => true,
            'arquivo_url' => 'uploads/documentos/' . $ano . '/' . $mes . '/' . $safeName,
            'arquivo_nome_original' => $arquivo['name'],
            'arquivo_extensao' => $ext,
            'arquivo_mime' => $mime,
        ];
    }
}
