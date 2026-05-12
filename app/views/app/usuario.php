<?php
// ARQUIVO: app/views/app/usuario.php
$usuario = $usuarioDetalhe;
?>
<section class="hero-banner inner">
    <div class="hero-banner-copy">
        <span class="section-kicker">Usuário</span>
        <h1><?= htmlspecialchars($usuario['nome']) ?></h1>
        <p><?= htmlspecialchars($usuario['email']) ?> • <?= htmlspecialchars($usuario['perfil']) ?> • <?= htmlspecialchars($usuario['status']) ?></p>
    </div>
    <a class="outline-btn" href="<?= URL_BASE ?>app/acessos">Voltar</a>
</section>

<section class="metrics-row">
    <article class="metric-card">
        <span>Perfil</span>
        <strong><?= htmlspecialchars($usuario['perfil']) ?></strong>
        <small>Tipo de acesso</small>
    </article>
    <article class="metric-card">
        <span>Status</span>
        <strong><?= htmlspecialchars($usuario['status']) ?></strong>
        <small>Situação atual</small>
    </article>
    <article class="metric-card">
        <span>Cliente vinculado</span>
        <strong><?= htmlspecialchars($usuario['cliente_nome'] ?? 'Sem vínculo') ?></strong>
        <small>Escopo do login</small>
    </article>
    <article class="metric-card">
        <span>Último login</span>
        <strong><?= !empty($usuario['ultimo_login_em']) ? date('d/m/Y H:i', strtotime($usuario['ultimo_login_em'])) : 'Nunca' ?></strong>
        <small>Último acesso registrado</small>
    </article>
</section>

<section class="content-grid split-rail">
    <article class="panel-card sticky-panel">
        <div class="panel-head">
            <div>
                <span class="section-kicker">Ações</span>
                <h3>Gerenciar login</h3>
            </div>
        </div>

        <div class="form-grid">
            <a class="primary-btn" href="<?= URL_BASE ?>app/alternarStatusUsuario/<?= (int) $usuario['id'] ?>">
                <?= strtoupper((string) $usuario['status']) === 'ATIVO' ? 'Bloquear acesso' : 'Liberar acesso' ?>
            </a>
        </div>

        <?php if (!empty($usuario['cliente_id'])): ?>
            <div class="panel-head" style="margin-top:20px;">
                <div>
                    <span class="section-kicker">Aviso ao cliente</span>
                    <h3>Enviar notificação</h3>
                </div>
            </div>

            <form class="form-grid" method="POST" action="<?= URL_BASE ?>app/enviarNotificacao">
                <input type="hidden" name="cliente_id" value="<?= (int) $usuario['cliente_id'] ?>">
                <input type="hidden" name="usuario_id" value="<?= (int) $usuario['id'] ?>">
                <label><span>Título</span><input type="text" name="titulo" required></label>
                <label><span>Mensagem</span><textarea name="mensagem" required></textarea></label>
                <button type="submit" class="primary-btn">Enviar notificação</button>
            </form>
        <?php endif; ?>
    </article>

    <div class="content-stack">
        <form method="GET" class="form-grid">
            <label>
                <span>Data inicial</span>
                <input type="date" name="data_inicio" value="<?= htmlspecialchars($_GET['data_inicio'] ?? '') ?>">
            </label>

            <label>
                <span>Data final</span>
                <input type="date" name="data_fim" value="<?= htmlspecialchars($_GET['data_fim'] ?? '') ?>">
            </label>

            <button type="submit" class="primary-btn">Filtrar</button>
        </form>
        <article class="panel-card">

            <div class="panel-head">
                <div>
                    <span class="section-kicker">Atividade</span>
                    <h3>Logs do usuário</h3>
                </div>
            </div>

            <div class="timeline-list">
                <?php if (empty($logs)): ?>
                    <div class="empty-state">Nenhuma atividade registrada.</div>
                <?php endif; ?>

                <?php foreach ($logs as $log): ?>
                    <div class="timeline-item">
                        <span class="timeline-dot"></span>
                        <div>
                            <strong><?= htmlspecialchars($log['acao']) ?></strong>
                            <small><?= htmlspecialchars($log['descricao'] ?? 'Sem descrição') ?></small>
                            <small><?= date('d/m/Y H:i', strtotime($log['created_at'])) ?></small>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </article>

        <article class="panel-card">
            <div class="panel-head">
                <div>
                    <span class="section-kicker">Acervo relacionado</span>
                    <h3>Documentos do cliente vinculado</h3>
                </div>
            </div>

            <div class="document-list-v2">
                <?php if (empty($documentos)): ?>
                    <div class="empty-state">Nenhum documento encontrado para esse vínculo.</div>
                <?php endif; ?>

                <?php foreach ($documentos as $doc): ?>
                    <article class="document-row">
                        <div class="document-icon"><i class="fa-solid fa-file-lines"></i></div>
                        <div class="document-copy">
                            <strong><?= htmlspecialchars($doc['nome_documento']) ?></strong>
                            <small><?= htmlspecialchars($doc['cliente_nome']) ?> • <?= htmlspecialchars($doc['tipo_nome']) ?></small>
                            <small><?= date('d/m/Y', strtotime($doc['data_documento'])) ?></small>
                        </div>
                        <div class="document-tools">
                            <a href="<?= URL_BASE ?>app/visualizar/<?= (int) $doc['id'] ?>" target="_blank"><i class="fa-regular fa-eye"></i></a>
                            <a href="<?= URL_BASE ?>app/download/<?= (int) $doc['id'] ?>"><i class="fa-solid fa-download"></i></a>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </article>
    </div>
</section>
<style>
    .timeline-list,
    .document-list-v2 {
        max-height: 420px;
        overflow-y: auto;
        padding-right: 8px;
    }
</style>