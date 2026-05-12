<?php
$isAdmin = !empty($isAdmin);
$clienteTemDemo = !empty($clienteAtual['demo_habilitado']);
?>
<section class="hero-banner">
    <div class="hero-banner-copy">
        <span class="section-kicker"><?= $isAdmin ? 'Visão geral' : 'Minha área' ?></span>
        <h1><?= $isAdmin ? 'Todos os documentos da sua operação, organizados em um só lugar.' : 'Tudo o que você precisa, em um único painel
' ?></h1>
        <p><?= $isAdmin ? 'Controle o ambiente normal e o ambiente Cliente DEMO com uma navegação mais clara.' : 'Gerencie documentos, acompanhe avisos e monitore seu ambiente com facilidade.
' ?></p>
        <div class="hero-actions">
            <a class="outline-btn" href="<?= URL_BASE ?>app/setores">Abrir organização</a>
            <a class="solid-btn" href="<?= $isAdmin ? URL_BASE . 'app/upload' : URL_BASE . 'app/documentos' ?>"><?= $isAdmin ? 'Enviar documento' : 'Meus documentos' ?></a>
        </div>
    </div>

    <div class="hero-banner-side">
        <div class="hero-orb"></div>
        <div class="hero-mini-card">
            <span><?= $isAdmin ? 'Documentos totais' : 'Meu acervo' ?></span>
            <strong><?= (int) ($stats['total_documentos'] ?? 0) ?></strong>
            <small><?= $isAdmin ? 'Ambiente normal + Cliente DEMO' : 'Arquivos visíveis neste login' ?></small>
        </div>
    </div>
</section>

<section class="metrics-row">
    <article class="metric-card"><span>Documentos</span><strong><?= (int) ($stats['total_documentos'] ?? 0) ?></strong><small><?= $isAdmin ? 'Base geral' : 'Total visível' ?></small></article>
    <article class="metric-card"><span>Normal</span><strong><?= (int) ($stats['total_documentos_normal'] ?? 0) ?></strong><small>Ambiente principal</small></article>
    <article class="metric-card"><span>Cliente DEMO</span><strong><?= (int) ($stats['total_documentos_demo'] ?? 0) ?></strong><small><?= $isAdmin ? 'Arquivos DEMO publicados' : ($clienteTemDemo ? 'Seu ambiente DEMO ativo' : 'Aguardando liberação') ?></small></article>
    <article class="metric-card"><span>Estruturas</span><strong><?= (int) ($stats['total_setores'] ?? 0) ?></strong><small>Pastas e categorias ativas</small></article>
</section>

<section class="content-grid two-col">
    <article class="panel-card">
        <div class="panel-head">
            <div>
                <span class="section-kicker">Organização</span>
                <h3><?= $isAdmin ? 'Estruturas do ambiente normal' : 'Sua organização principal' ?></h3>
            </div>
            <a href="<?= URL_BASE ?>app/setores">Ver todos</a>
        </div>

        <div class="card-list">
            <?php if (empty($setores)): ?><div class="empty-state">Nenhuma estrutura encontrada.</div><?php endif; ?>
            <?php foreach ($setores as $setor): ?>
                <a class="entity-card" href="<?= URL_BASE ?>app/setor/<?= (int) $setor['id'] ?>">
                    <div class="entity-icon"><i class="fa-solid fa-layer-group"></i></div>
                    <div class="entity-copy">
                        <strong><?= htmlspecialchars($setor['nome']) ?></strong>
                        <small><?= (int) $setor['total_documentos'] ?> documentos • <?= (int) $setor['total_tipos'] ?> categorias</small>
                    </div>
                    <i class="fa-solid fa-chevron-right entity-arrow"></i>
                </a>
            <?php endforeach; ?>
        </div>
    </article>

    <article class="panel-card">
        <div class="panel-head">
            <div>
                <span class="section-kicker"><?= $isAdmin ? 'Histórico' : 'Movimentação' ?></span>
                <h3><?= $isAdmin ? 'Documentos recentes' : 'Últimos documentos recebidos' ?></h3>
            </div>
            <a href="<?= URL_BASE ?>app/documentos">Abrir lista</a>
        </div>

        <div class="timeline-list">
            <?php if (empty($recentes)): ?>
                <div class="empty-state">Nenhum documento enviado ainda.</div>
            <?php else: ?>
                <?php foreach ($recentes as $doc): ?>
                    <div class="timeline-item">
                        <span class="timeline-dot"></span>
                        <div>
                            <strong><?= htmlspecialchars($doc['nome_documento']) ?></strong>
                            <small><?= htmlspecialchars($doc['cliente_nome']) ?> • <?= htmlspecialchars($doc['setor_nome']) ?> • <?= date('d/m/Y', strtotime($doc['data_documento'])) ?></small>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </article>
</section>

<section class="panel-card" style="margin-top:18px;">
    <div class="panel-head">
        <div>
            <span class="section-kicker">Ambientes</span>
            <h3>Fluxos disponíveis</h3>
        </div>
    </div>
    <div class="card-list">
        <a class="entity-card" href="<?= URL_BASE ?>app/documentos">
            <div class="entity-icon"><i class="fa-solid fa-folder-open"></i></div>
            <div class="entity-copy">
                <strong>Documentos</strong>
                <small>Ambiente normal com o acervo principal da conta</small>
            </div>
            <i class="fa-solid fa-chevron-right entity-arrow"></i>
        </a>
        <a class="entity-card" href="<?= URL_BASE ?>app/clienteDemo">
            <div class="entity-icon"><i class="fa-solid fa-briefcase"></i></div>
            <div class="entity-copy">
                <strong>Cliente DEMO</strong>
                <small><?= $isAdmin ? 'Gerencie pedidos, estrutura e arquivos DEMO' : ($clienteTemDemo ? 'Acesse o ambiente configurado pela DEMO' : 'Área separada com liberação controlada') ?></small>
            </div>
            <i class="fa-solid fa-chevron-right entity-arrow"></i>
        </a>
    </div>
</section>

<?php if (!$isAdmin): ?>
    <section class="panel-card" style="margin-top:18px;">
        <div class="panel-head">
            <div>
                <span class="section-kicker">Comunicação</span>
                <h3>Notificações recentes</h3>
            </div>
            <a href="<?= URL_BASE ?>app/notificacoes">Abrir central</a>
        </div>

        <div class="timeline-list">
            <?php if (empty($notificacoes)): ?>
                <div class="empty-state">Nenhuma notificação para mostrar.</div>
            <?php else: ?>
                <?php foreach ($notificacoes as $item): ?>
                    <div class="timeline-item">
                        <span class="timeline-dot"></span>
                        <div>
                            <strong><?= htmlspecialchars($item['titulo']) ?></strong>
                            <small><?= htmlspecialchars(mb_strimwidth($item['mensagem'], 0, 100, '...')) ?></small>
                            <small><?= date('d/m/Y H:i', strtotime($item['created_at'])) ?></small>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </section>
<?php endif; ?>