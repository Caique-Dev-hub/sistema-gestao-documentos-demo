<?php
// ARQUIVO: app/views/app/notificacoes.php
?>
<section class="hero-banner inner">
    <div class="hero-banner-copy">
        <span class="section-kicker">Notificações</span>
        <h1>Atualizações enviadas pelo administrador.</h1>
        <p>Veja novos documentos, pendências e comunicados da sua área.</p>
    </div>
</section>

<section class="panel-card">
    <div class="panel-head">
        <div>
            <span class="section-kicker">Central</span>
            <h3>Minhas notificações</h3>
        </div>
    </div>

    <div class="timeline-list">
        <?php if (empty($notificacoes)): ?>
            <div class="empty-state">Você ainda não possui notificações.</div>
        <?php endif; ?>

        <?php foreach ($notificacoes as $item): ?>
            <div class="timeline-item" style="<?= empty($item['lida_em']) ? 'background:#f8fafc;border-radius:16px;padding:12px;' : '' ?>">
                <span class="timeline-dot"></span>
                <div style="flex:1;">
                    <strong><?= htmlspecialchars($item['titulo']) ?></strong>
                    <small><?= nl2br(htmlspecialchars($item['mensagem'])) ?></small>
                    <small><?= date('d/m/Y H:i', strtotime($item['created_at'])) ?></small>
                </div>
                <?php if (empty($item['lida_em'])): ?>
                    <a class="outline-btn" href="<?= URL_BASE ?>app/marcarNotificacao/<?= (int) $item['id'] ?>">Marcar como lida</a>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
</section>