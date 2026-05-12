<?php
// ARQUIVO: app/models/GestaoDocumentos.php

class GestaoDocumentos extends Database
{
    private PDO $conn;

    public function __construct()
    {
        parent::__construct();
        $this->conn = $this->getDb();
    }

    private function appendClienteScope(string &$sql, array &$params, string $alias, ?int $clienteId, bool $allowShared = true): void
    {
        if ($clienteId !== null && $clienteId > 0) {
            if ($allowShared) {
                $sql .= " AND ({$alias}.cliente_id = :scope_cliente_id OR {$alias}.cliente_id IS NULL)";
            } else {
                $sql .= " AND {$alias}.cliente_id = :scope_cliente_id";
            }
            $params[':scope_cliente_id'] = $clienteId;
        }
    }

    private function normalizeAmbiente(string $ambiente): string
    {
        $ambiente = strtoupper(trim($ambiente));
        return in_array($ambiente, ['NORMAL', 'DEMO'], true) ? $ambiente : 'NORMAL';
    }

    private function resolveStatus(string $status): string
    {
        $status = strtoupper(trim($status));
        return in_array($status, ['ATIVO', 'BLOQUEADO'], true) ? $status : 'ATIVO';
    }

    private function resolveOrigemUpload(string $origem): string
    {
        $origem = strtoupper(trim($origem));
        return in_array($origem, ['ADMIN', 'CLIENTE'], true) ? $origem : 'ADMIN';
    }

    public function buscarUsuarioPorEmail(string $email): array|false
    {
        $stmt = $this->conn->prepare('SELECT * FROM usuarios WHERE email = :email LIMIT 1');
        $stmt->execute([':email' => mb_strtolower(trim($email))]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function buscarUsuarioPorId(int $id, int $empresaId = 1): array|false
    {
        $sql = "SELECT u.*, c.nome AS cliente_nome, c.demo_habilitado AS cliente_demo_habilitado
                FROM usuarios u
                LEFT JOIN clientes c ON c.id = u.cliente_id AND c.empresa_id = u.empresa_id
                WHERE u.id = :id AND u.empresa_id = :empresa_id
                LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':id' => $id, ':empresa_id' => $empresaId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function listarUsuarios(int $empresaId = 1, array $filtros = []): array
    {
        $sql = "SELECT u.*, c.nome AS cliente_nome, c.documento AS cliente_documento, c.demo_habilitado AS cliente_demo_habilitado,
                    (SELECT COUNT(*) FROM documentos d WHERE d.usuario_id = u.id) AS total_documentos,
                    (SELECT COUNT(*) FROM logs_atividade l WHERE l.usuario_id = u.id) AS total_atividades
                FROM usuarios u
                LEFT JOIN clientes c ON c.id = u.cliente_id AND c.empresa_id = u.empresa_id
                WHERE u.empresa_id = :empresa_id";
        $params = [':empresa_id' => $empresaId];

        if (!empty($filtros['perfil'])) {
            $sql .= ' AND u.perfil = :perfil';
            $params[':perfil'] = strtoupper((string) $filtros['perfil']);
        }

        if (!empty($filtros['status'])) {
            $sql .= ' AND u.status = :status';
            $params[':status'] = strtoupper((string) $filtros['status']);
        }

        if (!empty($filtros['busca'])) {
            $sql .= ' AND (u.nome LIKE :busca OR u.email LIKE :busca OR c.nome LIKE :busca OR c.documento LIKE :busca)';
            $params[':busca'] = '%' . trim((string) $filtros['busca']) . '%';
        }

        $sql .= ' ORDER BY u.created_at DESC, u.id DESC';
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function emailUsuarioExiste(string $email, int $empresaId = 1, int $ignorarId = 0): bool
    {
        $sql = 'SELECT COUNT(*) FROM usuarios WHERE email = :email AND empresa_id = :empresa_id';
        $params = [':email' => mb_strtolower(trim($email)), ':empresa_id' => $empresaId];

        if ($ignorarId > 0) {
            $sql .= ' AND id <> :ignorar_id';
            $params[':ignorar_id'] = $ignorarId;
        }

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn() > 0;
    }

    public function criarUsuario(array $dados): bool
    {
        return (bool) $this->criarUsuarioRetornandoId($dados);
    }

    public function criarUsuarioRetornandoId(array $dados): int|false
    {
        $stmt = $this->conn->prepare(
            'INSERT INTO usuarios (empresa_id, cliente_id, nome, email, senha_hash, perfil, status, telefone, cargo)
             VALUES (:empresa_id, :cliente_id, :nome, :email, :senha_hash, :perfil, :status, :telefone, :cargo)'
        );

        $ok = $stmt->execute([
            ':empresa_id' => (int) ($dados['empresa_id'] ?? 1),
            ':cliente_id' => !empty($dados['cliente_id']) ? (int) $dados['cliente_id'] : null,
            ':nome' => trim((string) ($dados['nome'] ?? '')),
            ':email' => mb_strtolower(trim((string) ($dados['email'] ?? ''))),
            ':senha_hash' => (string) ($dados['senha_hash'] ?? ''),
            ':perfil' => strtoupper((string) ($dados['perfil'] ?? 'CLIENTE')),
            ':status' => $this->resolveStatus((string) ($dados['status'] ?? 'ATIVO')),
            ':telefone' => trim((string) ($dados['telefone'] ?? '')) ?: null,
            ':cargo' => trim((string) ($dados['cargo'] ?? '')) ?: null,
        ]);

        return $ok ? (int) $this->conn->lastInsertId() : false;
    }

    public function clientePossuiUsuario(int $clienteId, int $empresaId = 1): bool
    {
        $stmt = $this->conn->prepare('SELECT COUNT(*) FROM usuarios WHERE cliente_id = :cliente_id AND empresa_id = :empresa_id');
        $stmt->execute([':cliente_id' => $clienteId, ':empresa_id' => $empresaId]);
        return (int) $stmt->fetchColumn() > 0;
    }

    public function atualizarStatusUsuario(int $id, int $empresaId, string $status): bool
    {
        $stmt = $this->conn->prepare('UPDATE usuarios SET status = :status WHERE id = :id AND empresa_id = :empresa_id');
        return $stmt->execute([
            ':status' => $this->resolveStatus($status),
            ':id' => $id,
            ':empresa_id' => $empresaId,
        ]);
    }

    public function atualizarUltimoLogin(int $usuarioId): bool
    {
        $stmt = $this->conn->prepare('UPDATE usuarios SET ultimo_login_em = NOW() WHERE id = :id');
        return $stmt->execute([':id' => $usuarioId]);
    }

    public function atualizarPerfilUsuario(int $id, int $empresaId, array $dados): bool
    {
        $stmt = $this->conn->prepare(
            'UPDATE usuarios
             SET nome = :nome,
                 email = :email,
                 telefone = :telefone,
                 cargo = :cargo,
                 updated_at = NOW()
             WHERE id = :id AND empresa_id = :empresa_id'
        );

        return $stmt->execute([
            ':id' => $id,
            ':empresa_id' => $empresaId,
            ':nome' => trim((string) ($dados['nome'] ?? '')),
            ':email' => mb_strtolower(trim((string) ($dados['email'] ?? ''))),
            ':telefone' => trim((string) ($dados['telefone'] ?? '')) ?: null,
            ':cargo' => trim((string) ($dados['cargo'] ?? '')) ?: null,
        ]);
    }

    public function atualizarSenhaUsuario(int $id, int $empresaId, string $senhaHash): bool
    {
        $stmt = $this->conn->prepare('UPDATE usuarios SET senha_hash = :senha_hash, updated_at = NOW() WHERE id = :id AND empresa_id = :empresa_id');
        return $stmt->execute([
            ':id' => $id,
            ':empresa_id' => $empresaId,
            ':senha_hash' => $senhaHash,
        ]);
    }

    public function criarLogAtividade(array $dados): bool
    {
        $stmt = $this->conn->prepare(
            'INSERT INTO logs_atividade (empresa_id, usuario_id, cliente_id, acao, entidade, entidade_id, descricao, ip)
             VALUES (:empresa_id, :usuario_id, :cliente_id, :acao, :entidade, :entidade_id, :descricao, :ip)'
        );

        return $stmt->execute([
            ':empresa_id' => (int) ($dados['empresa_id'] ?? 1),
            ':usuario_id' => !empty($dados['usuario_id']) ? (int) $dados['usuario_id'] : null,
            ':cliente_id' => !empty($dados['cliente_id']) ? (int) $dados['cliente_id'] : null,
            ':acao' => strtoupper(trim((string) ($dados['acao'] ?? 'ACAO'))),
            ':entidade' => trim((string) ($dados['entidade'] ?? '')) ?: null,
            ':entidade_id' => !empty($dados['entidade_id']) ? (int) $dados['entidade_id'] : null,
            ':descricao' => trim((string) ($dados['descricao'] ?? '')) ?: null,
            ':ip' => trim((string) ($dados['ip'] ?? '')) ?: null,
        ]);
    }

    public function listarLogsUsuario(int $usuarioId, int $empresaId = 1, int $limit = 50): array
    {
        $stmt = $this->conn->prepare(
            'SELECT * FROM logs_atividade
             WHERE usuario_id = :usuario_id AND empresa_id = :empresa_id
             ORDER BY created_at DESC, id DESC
             LIMIT ' . max(1, (int) $limit)
        );
        $stmt->execute([':usuario_id' => $usuarioId, ':empresa_id' => $empresaId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function listarNotificacoes(int $clienteId, int $empresaId = 1, ?int $usuarioId = null, int $limit = 50): array
    {
        $sql = 'SELECT * FROM notificacoes WHERE cliente_id = :cliente_id AND empresa_id = :empresa_id';
        $params = [':cliente_id' => $clienteId, ':empresa_id' => $empresaId];

        if ($usuarioId !== null && $usuarioId > 0) {
            $sql .= ' AND (usuario_id IS NULL OR usuario_id = :usuario_id)';
            $params[':usuario_id'] = $usuarioId;
        }

        $sql .= ' ORDER BY created_at DESC, id DESC LIMIT ' . max(1, (int) $limit);
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function contarNotificacoesNaoLidas(int $clienteId, int $empresaId = 1, ?int $usuarioId = null): int
    {
        $sql = 'SELECT COUNT(*) FROM notificacoes WHERE cliente_id = :cliente_id AND empresa_id = :empresa_id AND lida_em IS NULL';
        $params = [':cliente_id' => $clienteId, ':empresa_id' => $empresaId];

        if ($usuarioId !== null && $usuarioId > 0) {
            $sql .= ' AND (usuario_id IS NULL OR usuario_id = :usuario_id)';
            $params[':usuario_id'] = $usuarioId;
        }

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    public function enviarNotificacao(array $dados): bool
    {
        $stmt = $this->conn->prepare(
            'INSERT INTO notificacoes (empresa_id, cliente_id, usuario_id, titulo, mensagem, tipo)
             VALUES (:empresa_id, :cliente_id, :usuario_id, :titulo, :mensagem, :tipo)'
        );

        return $stmt->execute([
            ':empresa_id' => (int) ($dados['empresa_id'] ?? 1),
            ':cliente_id' => (int) ($dados['cliente_id'] ?? 0),
            ':usuario_id' => !empty($dados['usuario_id']) ? (int) $dados['usuario_id'] : null,
            ':titulo' => trim((string) ($dados['titulo'] ?? 'Aviso')),
            ':mensagem' => trim((string) ($dados['mensagem'] ?? '')),
            ':tipo' => strtoupper(trim((string) ($dados['tipo'] ?? 'INFO'))),
        ]);
    }

    public function marcarNotificacaoLida(int $id, int $clienteId, int $empresaId = 1, ?int $usuarioId = null): bool
    {
        $sql = 'UPDATE notificacoes SET lida_em = NOW() WHERE id = :id AND cliente_id = :cliente_id AND empresa_id = :empresa_id';
        $params = [':id' => $id, ':cliente_id' => $clienteId, ':empresa_id' => $empresaId];

        if ($usuarioId !== null && $usuarioId > 0) {
            $sql .= ' AND (usuario_id IS NULL OR usuario_id = :usuario_id)';
            $params[':usuario_id'] = $usuarioId;
        }

        $stmt = $this->conn->prepare($sql);
        return $stmt->execute($params);
    }

    public function listarSetores(int $empresaId = 1, ?int $clienteId = null, string $ambiente = 'NORMAL'): array
    {
        $ambiente = $this->normalizeAmbiente($ambiente);
        $sql = 'SELECT * FROM setores WHERE empresa_id = :empresa_id AND ambiente = :ambiente';
        $params = [':empresa_id' => $empresaId, ':ambiente' => $ambiente];
        $this->appendClienteScope($sql, $params, 'setores', $clienteId, true);
        $sql .= ' ORDER BY nome ASC';
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function listarSetoresComTotais(int $empresaId = 1, ?int $clienteId = null, string $ambiente = 'NORMAL'): array
    {
        $ambiente = $this->normalizeAmbiente($ambiente);
        $sql = "SELECT s.*,
                    COUNT(DISTINCT d.id) AS total_documentos,
                    COUNT(DISTINCT td.id) AS total_tipos
                FROM setores s
                LEFT JOIN documentos d ON d.setor_id = s.id AND d.empresa_id = s.empresa_id AND d.ambiente = s.ambiente
                LEFT JOIN tipos_documento td ON td.setor_id = s.id AND td.empresa_id = s.empresa_id AND td.ambiente = s.ambiente
                WHERE s.empresa_id = :empresa_id AND s.ambiente = :ambiente";
        $params = [':empresa_id' => $empresaId, ':ambiente' => $ambiente];
        $this->appendClienteScope($sql, $params, 's', $clienteId, true);

        if ($clienteId !== null && $clienteId > 0) {
            $sql .= ' AND (d.cliente_id = :docs_cliente_id OR d.cliente_id IS NULL OR s.cliente_id = :setor_cliente_id OR s.cliente_id IS NULL)';
            $params[':docs_cliente_id'] = $clienteId;
            $params[':setor_cliente_id'] = $clienteId;
        }

        $sql .= ' GROUP BY s.id ORDER BY s.nome ASC';
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function buscarSetor(int $id, int $empresaId = 1, ?int $clienteId = null, string $ambiente = 'NORMAL'): array|false
    {
        $ambiente = $this->normalizeAmbiente($ambiente);
        $sql = 'SELECT * FROM setores WHERE id = :id AND empresa_id = :empresa_id AND ambiente = :ambiente';
        $params = [':id' => $id, ':empresa_id' => $empresaId, ':ambiente' => $ambiente];
        $this->appendClienteScope($sql, $params, 'setores', $clienteId, true);
        $sql .= ' LIMIT 1';
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function listarClientes(int $empresaId = 1, ?int $clienteId = null, ?bool $somenteDemo = null): array
    {
        $sql = 'SELECT * FROM clientes WHERE empresa_id = :empresa_id';
        $params = [':empresa_id' => $empresaId];

        if ($clienteId !== null && $clienteId > 0) {
            $sql .= ' AND id = :cliente_id';
            $params[':cliente_id'] = $clienteId;
        }

        if ($somenteDemo !== null) {
            $sql .= ' AND demo_habilitado = :demo_habilitado';
            $params[':demo_habilitado'] = $somenteDemo ? 1 : 0;
        }

        $sql .= ' ORDER BY nome ASC';
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function buscarClientePorId(int $id, int $empresaId = 1): array|false
    {
        $stmt = $this->conn->prepare('SELECT * FROM clientes WHERE id = :id AND empresa_id = :empresa_id LIMIT 1');
        $stmt->execute([':id' => $id, ':empresa_id' => $empresaId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function listarClientesComTotais(int $empresaId = 1, ?int $clienteId = null): array
    {
        $sql = "SELECT c.*,
                    COUNT(DISTINCT d.id) AS total_documentos,
                    COUNT(DISTINCT u.id) AS total_usuarios
                FROM clientes c
                LEFT JOIN documentos d ON d.cliente_id = c.id AND d.empresa_id = c.empresa_id
                LEFT JOIN usuarios u ON u.cliente_id = c.id AND u.empresa_id = c.empresa_id
                WHERE c.empresa_id = :empresa_id";
        $params = [':empresa_id' => $empresaId];

        if ($clienteId !== null && $clienteId > 0) {
            $sql .= ' AND c.id = :cliente_id';
            $params[':cliente_id'] = $clienteId;
        }

        $sql .= ' GROUP BY c.id ORDER BY c.nome ASC';
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function listarTipos(int $empresaId = 1, ?int $setorId = null, ?int $clienteId = null, string $ambiente = 'NORMAL'): array
    {
        $ambiente = $this->normalizeAmbiente($ambiente);
        $sql = "SELECT td.*, s.nome AS setor_nome, COUNT(DISTINCT d.id) AS total_documentos
                FROM tipos_documento td
                INNER JOIN setores s ON s.id = td.setor_id AND s.empresa_id = td.empresa_id AND s.ambiente = td.ambiente
                LEFT JOIN documentos d ON d.tipo_documento_id = td.id AND d.empresa_id = td.empresa_id AND d.ambiente = td.ambiente
                WHERE td.empresa_id = :empresa_id AND td.ambiente = :ambiente";
        $params = [':empresa_id' => $empresaId, ':ambiente' => $ambiente];

        if ($setorId !== null && $setorId > 0) {
            $sql .= ' AND td.setor_id = :setor_id';
            $params[':setor_id'] = $setorId;
        }

        if ($clienteId !== null && $clienteId > 0) {
            $sql .= ' AND ((td.cliente_id = :cliente_id OR td.cliente_id IS NULL) AND (s.cliente_id = :setor_cliente_id OR s.cliente_id IS NULL))';
            $params[':cliente_id'] = $clienteId;
            $params[':setor_cliente_id'] = $clienteId;
        }

        $sql .= ' GROUP BY td.id ORDER BY s.nome ASC, td.nome ASC';
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function adicionarSetor(array $dados): bool
    {
        $stmt = $this->conn->prepare(
            'INSERT INTO setores (empresa_id, cliente_id, ambiente, nome, descricao, created_at)
             VALUES (:empresa_id, :cliente_id, :ambiente, :nome, :descricao, NOW())'
        );

        return $stmt->execute([
            ':empresa_id' => (int) ($dados['empresa_id'] ?? 1),
            ':cliente_id' => !empty($dados['cliente_id']) ? (int) $dados['cliente_id'] : null,
            ':ambiente' => $this->normalizeAmbiente((string) ($dados['ambiente'] ?? 'NORMAL')),
            ':nome' => trim((string) ($dados['nome'] ?? '')),
            ':descricao' => trim((string) ($dados['descricao'] ?? '')) ?: null,
        ]);
    }

    public function adicionarCliente(array $dados): bool
    {
        return (bool) $this->criarClienteRetornandoId($dados);
    }

    public function criarClienteRetornandoId(array $dados): int|false
    {
        $stmt = $this->conn->prepare(
            'INSERT INTO clientes (empresa_id, nome, documento, responsavel, observacao, demo_habilitado, suporte_email, suporte_telefone)
             VALUES (:empresa_id, :nome, :documento, :responsavel, :observacao, :demo_habilitado, :suporte_email, :suporte_telefone)'
        );

        $ok = $stmt->execute([
            ':empresa_id' => (int) ($dados['empresa_id'] ?? 1),
            ':nome' => trim((string) ($dados['nome'] ?? '')),
            ':documento' => trim((string) ($dados['documento'] ?? '')) ?: null,
            ':responsavel' => trim((string) ($dados['responsavel'] ?? '')) ?: null,
            ':observacao' => trim((string) ($dados['observacao'] ?? '')) ?: null,
            ':demo_habilitado' => !empty($dados['demo_habilitado']) ? 1 : 0,
            ':suporte_email' => trim((string) ($dados['suporte_email'] ?? '')) ?: null,
            ':suporte_telefone' => trim((string) ($dados['suporte_telefone'] ?? '')) ?: null,
        ]);

        return $ok ? (int) $this->conn->lastInsertId() : false;
    }

    public function criarClienteEUsuario(array $clienteDados, array $usuarioDados): int|false
    {
        $this->conn->beginTransaction();

        try {
            $clienteId = $this->criarClienteRetornandoId($clienteDados);
            if (!$clienteId) {
                $this->conn->rollBack();
                return false;
            }

            $usuarioDados['cliente_id'] = $clienteId;
            $usuarioId = $this->criarUsuarioRetornandoId($usuarioDados);
            if (!$usuarioId) {
                $this->conn->rollBack();
                return false;
            }

            $this->conn->commit();
            return (int) $usuarioId;
        } catch (Throwable $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            return false;
        }
    }

    public function adicionarTipo(array $dados): bool
    {
        $stmt = $this->conn->prepare(
            'INSERT INTO tipos_documento (empresa_id, setor_id, cliente_id, ambiente, nome, created_at)
             VALUES (:empresa_id, :setor_id, :cliente_id, :ambiente, :nome, NOW())'
        );

        return $stmt->execute([
            ':empresa_id' => (int) ($dados['empresa_id'] ?? 1),
            ':setor_id' => (int) ($dados['setor_id'] ?? 0),
            ':cliente_id' => !empty($dados['cliente_id']) ? (int) $dados['cliente_id'] : null,
            ':ambiente' => $this->normalizeAmbiente((string) ($dados['ambiente'] ?? 'NORMAL')),
            ':nome' => trim((string) ($dados['nome'] ?? '')),
        ]);
    }

    public function adicionarDocumento(array $dados): bool
    {
        $stmt = $this->conn->prepare(
            'INSERT INTO documentos
            (empresa_id, setor_id, cliente_id, usuario_id, tipo_documento_id, ambiente, nome_documento, data_documento, numero_documento, valor, observacao, arquivo_url, arquivo_nome_original, arquivo_extensao, arquivo_mime, uploaded_by, origem_upload, created_at)
            VALUES
            (:empresa_id, :setor_id, :cliente_id, :usuario_id, :tipo_documento_id, :ambiente, :nome_documento, :data_documento, :numero_documento, :valor, :observacao, :arquivo_url, :arquivo_nome_original, :arquivo_extensao, :arquivo_mime, :uploaded_by, :origem_upload, NOW())'
        );

        return $stmt->execute([
            ':empresa_id' => (int) ($dados['empresa_id'] ?? 1),
            ':setor_id' => (int) ($dados['setor_id'] ?? 0),
            ':cliente_id' => (int) ($dados['cliente_id'] ?? 0),
            ':usuario_id' => !empty($dados['usuario_id']) ? (int) $dados['usuario_id'] : null,
            ':tipo_documento_id' => (int) ($dados['tipo_documento_id'] ?? 0),
            ':ambiente' => $this->normalizeAmbiente((string) ($dados['ambiente'] ?? 'NORMAL')),
            ':nome_documento' => trim((string) ($dados['nome_documento'] ?? '')),
            ':data_documento' => trim((string) ($dados['data_documento'] ?? date('Y-m-d'))),
            ':numero_documento' => trim((string) ($dados['numero_documento'] ?? '')) ?: null,
            ':valor' => ($dados['valor'] ?? '') !== '' ? (float) $dados['valor'] : null,
            ':observacao' => trim((string) ($dados['observacao'] ?? '')) ?: null,
            ':arquivo_url' => trim((string) ($dados['arquivo_url'] ?? '')),
            ':arquivo_nome_original' => trim((string) ($dados['arquivo_nome_original'] ?? '')),
            ':arquivo_extensao' => trim((string) ($dados['arquivo_extensao'] ?? '')),
            ':arquivo_mime' => trim((string) ($dados['arquivo_mime'] ?? '')),
            ':uploaded_by' => trim((string) ($dados['uploaded_by'] ?? 'Usuário App')),
            ':origem_upload' => $this->resolveOrigemUpload((string) ($dados['origem_upload'] ?? 'ADMIN')),
        ]);
    }

    public function listarDocumentosPorUsuario(int $usuarioId, int $empresaId = 1, int $limit = 50): array
    {
        $sql = "SELECT d.*, c.nome AS cliente_nome, s.nome AS setor_nome, td.nome AS tipo_nome
                FROM documentos d
                INNER JOIN clientes c ON c.id = d.cliente_id
                INNER JOIN setores s ON s.id = d.setor_id
                INNER JOIN tipos_documento td ON td.id = d.tipo_documento_id
                WHERE d.empresa_id = :empresa_id AND (d.usuario_id = :usuario_id OR d.cliente_id IN (SELECT cliente_id FROM usuarios WHERE id = :usuario_id_sub))
                ORDER BY d.created_at DESC, d.id DESC
                LIMIT " . max(1, (int) $limit);
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            ':empresa_id' => $empresaId,
            ':usuario_id' => $usuarioId,
            ':usuario_id_sub' => $usuarioId,
        ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function listarDocumentos(array $filtros = [], int $empresaId = 1, ?int $limit = null, ?int $clienteId = null): array
    {
        $ambiente = $this->normalizeAmbiente((string) ($filtros['ambiente'] ?? 'NORMAL'));
        $sql = "SELECT d.*, c.nome AS cliente_nome, s.nome AS setor_nome, td.nome AS tipo_nome
                FROM documentos d
                INNER JOIN clientes c ON c.id = d.cliente_id AND c.empresa_id = d.empresa_id
                INNER JOIN setores s ON s.id = d.setor_id AND s.empresa_id = d.empresa_id
                INNER JOIN tipos_documento td ON td.id = d.tipo_documento_id AND td.empresa_id = d.empresa_id
                WHERE d.empresa_id = :empresa_id AND d.ambiente = :ambiente";
        $params = [':empresa_id' => $empresaId, ':ambiente' => $ambiente];

        if ($clienteId !== null && $clienteId > 0) {
            $sql .= ' AND d.cliente_id = :scope_cliente_id';
            $params[':scope_cliente_id'] = $clienteId;
        }

        if (!empty($filtros['setor_id'])) {
            $sql .= ' AND d.setor_id = :setor_id';
            $params[':setor_id'] = (int) $filtros['setor_id'];
        }

        if (!empty($filtros['cliente_id'])) {
            $sql .= ' AND d.cliente_id = :cliente_id';
            $params[':cliente_id'] = (int) $filtros['cliente_id'];
        }

        if (!empty($filtros['tipo_documento_id'])) {
            $sql .= ' AND d.tipo_documento_id = :tipo_documento_id';
            $params[':tipo_documento_id'] = (int) $filtros['tipo_documento_id'];
        }

        if (!empty($filtros['mes_referencia'])) {
            $sql .= ' AND DATE_FORMAT(d.data_documento, "%Y-%m") = :mes_referencia';
            $params[':mes_referencia'] = (string) $filtros['mes_referencia'];
        }

        if (!empty($filtros['busca'])) {
            $sql .= ' AND (d.nome_documento LIKE :busca OR d.numero_documento LIKE :busca OR c.nome LIKE :busca OR s.nome LIKE :busca OR td.nome LIKE :busca)';
            $params[':busca'] = '%' . trim((string) $filtros['busca']) . '%';
        }

        $ordenacao = (string) ($filtros['ordenacao'] ?? 'data_documento_desc');
        $mapaOrdenacao = [
            'upload_desc' => 'd.created_at DESC, d.id DESC',
            'upload_asc' => 'd.created_at ASC, d.id ASC',
            'nome_asc' => 'd.nome_documento ASC',
            'nome_desc' => 'd.nome_documento DESC',
            'data_documento_asc' => 'd.data_documento ASC, d.id ASC',
            'data_documento_desc' => 'd.data_documento DESC, d.id DESC',
        ];
        $sql .= ' ORDER BY ' . ($mapaOrdenacao[$ordenacao] ?? $mapaOrdenacao['data_documento_desc']);

        if ($limit !== null && $limit > 0) {
            $sql .= ' LIMIT ' . (int) $limit;
        }

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function buscarDocumentoPorId(int $id, int $empresaId = 1, ?int $clienteId = null): array|false
    {
        $sql = "SELECT d.*, c.nome AS cliente_nome, s.nome AS setor_nome, td.nome AS tipo_nome
                FROM documentos d
                INNER JOIN clientes c ON c.id = d.cliente_id
                INNER JOIN setores s ON s.id = d.setor_id
                INNER JOIN tipos_documento td ON td.id = d.tipo_documento_id
                WHERE d.id = :id AND d.empresa_id = :empresa_id";
        $params = [':id' => $id, ':empresa_id' => $empresaId];

        if ($clienteId !== null && $clienteId > 0) {
            $sql .= ' AND d.cliente_id = :cliente_id';
            $params[':cliente_id'] = $clienteId;
        }

        $sql .= ' LIMIT 1';
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function excluirDocumento(int $id, int $empresaId = 1, ?int $clienteId = null): bool
    {
        $sql = 'DELETE FROM documentos WHERE id = :id AND empresa_id = :empresa_id';
        $params = [':id' => $id, ':empresa_id' => $empresaId];

        if ($clienteId !== null && $clienteId > 0) {
            $sql .= ' AND cliente_id = :cliente_id';
            $params[':cliente_id'] = $clienteId;
        }

        $stmt = $this->conn->prepare($sql);
        return $stmt->execute($params);
    }

    public function totaisDashboard(int $empresaId = 1, ?int $clienteId = null): array
    {
        $sql = "SELECT
                    COUNT(DISTINCT d.id) AS total_documentos,
                    COUNT(DISTINCT c.id) AS total_clientes,
                    COUNT(DISTINCT s.id) AS total_setores,
                    COUNT(DISTINCT td.id) AS total_tipos,
                    SUM(CASE WHEN d.ambiente = 'DEMO' THEN 1 ELSE 0 END) AS total_documentos_demo,
                    SUM(CASE WHEN d.ambiente = 'NORMAL' THEN 1 ELSE 0 END) AS total_documentos_normal
                FROM clientes c
                LEFT JOIN documentos d ON d.cliente_id = c.id AND d.empresa_id = c.empresa_id
                LEFT JOIN setores s ON s.empresa_id = c.empresa_id AND (s.cliente_id = c.id OR s.cliente_id IS NULL)
                LEFT JOIN tipos_documento td ON td.empresa_id = c.empresa_id AND (td.cliente_id = c.id OR td.cliente_id IS NULL)
                WHERE c.empresa_id = :empresa_id";
        $params = [':empresa_id' => $empresaId];

        if ($clienteId !== null && $clienteId > 0) {
            $sql .= ' AND c.id = :cliente_id';
            $params[':cliente_id'] = $clienteId;
        }

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

        return [
            'total_documentos' => (int) ($row['total_documentos'] ?? 0),
            'total_clientes' => (int) ($row['total_clientes'] ?? 0),
            'total_setores' => (int) ($row['total_setores'] ?? 0),
            'total_tipos' => (int) ($row['total_tipos'] ?? 0),
            'total_documentos_demo' => (int) ($row['total_documentos_demo'] ?? 0),
            'total_documentos_normal' => (int) ($row['total_documentos_normal'] ?? 0),
        ];
    }

    public function totaisSetor(int $setorId, int $empresaId = 1, ?int $clienteId = null, string $ambiente = 'NORMAL'): array
    {
        $ambiente = $this->normalizeAmbiente($ambiente);
        $sql = "SELECT
                    COUNT(DISTINCT d.id) AS total_documentos,
                    COUNT(DISTINCT td.id) AS total_tipos,
                    COUNT(DISTINCT d.cliente_id) AS total_clientes
                FROM setores s
                LEFT JOIN documentos d ON d.setor_id = s.id AND d.empresa_id = s.empresa_id AND d.ambiente = s.ambiente
                LEFT JOIN tipos_documento td ON td.setor_id = s.id AND td.empresa_id = s.empresa_id AND td.ambiente = s.ambiente
                WHERE s.id = :setor_id AND s.empresa_id = :empresa_id AND s.ambiente = :ambiente";
        $params = [':setor_id' => $setorId, ':empresa_id' => $empresaId, ':ambiente' => $ambiente];

        if ($clienteId !== null && $clienteId > 0) {
            $sql .= ' AND (s.cliente_id = :cliente_id OR s.cliente_id IS NULL)';
            $params[':cliente_id'] = $clienteId;
        }

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

        return [
            'total_documentos' => (int) ($row['total_documentos'] ?? 0),
            'total_tipos' => (int) ($row['total_tipos'] ?? 0),
            'total_clientes' => (int) ($row['total_clientes'] ?? 0),
        ];
    }

    public function listarSolicitacoesDemo(int $empresaId = 1, ?int $clienteId = null, string $status = ''): array
    {
        $sql = "SELECT hs.*, c.nome AS cliente_nome, s.nome AS setor_nome, td.nome AS tipo_nome
                FROM demo_solicitacoes hs
                INNER JOIN clientes c ON c.id = hs.cliente_id AND c.empresa_id = hs.empresa_id
                LEFT JOIN setores s ON s.id = hs.setor_id AND s.empresa_id = hs.empresa_id
                LEFT JOIN tipos_documento td ON td.id = hs.tipo_documento_id AND td.empresa_id = hs.empresa_id
                WHERE hs.empresa_id = :empresa_id";
        $params = [':empresa_id' => $empresaId];

        if ($clienteId !== null && $clienteId > 0) {
            $sql .= ' AND hs.cliente_id = :cliente_id';
            $params[':cliente_id'] = $clienteId;
        }

        if ($status !== '') {
            $sql .= ' AND hs.status = :status';
            $params[':status'] = strtoupper(trim($status));
        }

        $sql .= ' ORDER BY hs.created_at DESC, hs.id DESC';
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function criarSolicitacaoDemo(array $dados): bool
    {
        $stmt = $this->conn->prepare(
            'INSERT INTO demo_solicitacoes (empresa_id, cliente_id, setor_id, tipo_documento_id, titulo, descricao, status, created_by_user_id, created_at, updated_at)
             VALUES (:empresa_id, :cliente_id, :setor_id, :tipo_documento_id, :titulo, :descricao, :status, :created_by_user_id, NOW(), NOW())'
        );

        return $stmt->execute([
            ':empresa_id' => (int) ($dados['empresa_id'] ?? 1),
            ':cliente_id' => (int) ($dados['cliente_id'] ?? 0),
            ':setor_id' => !empty($dados['setor_id']) ? (int) $dados['setor_id'] : null,
            ':tipo_documento_id' => !empty($dados['tipo_documento_id']) ? (int) $dados['tipo_documento_id'] : null,
            ':titulo' => trim((string) ($dados['titulo'] ?? '')),
            ':descricao' => trim((string) ($dados['descricao'] ?? '')) ?: null,
            ':status' => strtoupper(trim((string) ($dados['status'] ?? 'ABERTA'))),
            ':created_by_user_id' => !empty($dados['created_by_user_id']) ? (int) $dados['created_by_user_id'] : null,
        ]);
    }

    public function marcarSolicitacaoDemoAtendida(int $solicitacaoId, int $empresaId, int $documentoId): bool
    {
        $stmt = $this->conn->prepare(
            'UPDATE demo_solicitacoes
             SET status = :status, atendida_documento_id = :documento_id, updated_at = NOW()
             WHERE id = :id AND empresa_id = :empresa_id'
        );

        return $stmt->execute([
            ':status' => 'ATENDIDA',
            ':documento_id' => $documentoId,
            ':id' => $solicitacaoId,
            ':empresa_id' => $empresaId,
        ]);
    }

    public function buscarSolicitacaoDemo(int $id, int $empresaId = 1, ?int $clienteId = null): array|false
    {
        $sql = 'SELECT * FROM demo_solicitacoes WHERE id = :id AND empresa_id = :empresa_id';
        $params = [':id' => $id, ':empresa_id' => $empresaId];

        if ($clienteId !== null && $clienteId > 0) {
            $sql .= ' AND cliente_id = :cliente_id';
            $params[':cliente_id'] = $clienteId;
        }

        $sql .= ' LIMIT 1';
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
