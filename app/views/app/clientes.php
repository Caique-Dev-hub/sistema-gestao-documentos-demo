<?php
$isAdmin = !empty($isAdmin);
?>
<section class="hero-banner inner">
    <div class="hero-banner-copy">
        <span class="section-kicker"><?= $isAdmin ? 'Clientes' : 'Meu cadastro' ?></span>
        <h1><?= $isAdmin ? 'Clientes vinculados aos acessos da plataforma.' : 'Dados do cliente vinculado ao seu login.' ?></h1>
        <p><?= $isAdmin ? 'O cadastro de novos clientes foi centralizado em Liberar acesso para evitar duplicidade.' : 'Seu login enxerga somente o cadastro ao qual ele está vinculado.' ?></p>
    </div>
</section>

<section class="panel-card">
    <div class="panel-head">
        <div>
            <span class="section-kicker"><?= $isAdmin ? 'Listagem' : 'Cadastro' ?></span>
            <h3><?= $isAdmin ? 'Clientes atuais' : 'Informações do cliente' ?></h3>
        </div>
    </div>

    <div class="card-list">
        <?php if (empty($clientes)): ?>
            <div class="empty-state"><?= $isAdmin ? 'Nenhum cliente encontrado.' : 'Nenhum cliente está vinculado a este login.' ?></div>
        <?php endif; ?>

        <?php foreach ($clientes as $cliente): ?>
            <div class="entity-card static-card">
                <div class="entity-icon"><i class="fa-regular fa-building"></i></div>
                <div class="entity-copy">
                    <strong><?= htmlspecialchars($cliente['nome']) ?></strong>
                    <small><?= htmlspecialchars($cliente['documento'] ?: 'Sem documento') ?></small>
                    <small>Responsável: <?= htmlspecialchars($cliente['responsavel'] ?: 'Não informado') ?></small>
                    <small>Cliente DEMO: <?= !empty($cliente['demo_habilitado']) ? 'Liberado' : 'Não liberado' ?></small>
                    <small>Suporte: <?= htmlspecialchars($cliente['suporte_email'] ?: 'Não informado') ?></small>
                    <?php if ($isAdmin): ?>
                        <small><?= (int) ($cliente['total_documentos'] ?? 0) ?> documentos</small>
                    <?php else: ?>
                        <small><?= htmlspecialchars($cliente['observacao'] ?: 'Sem observações') ?></small>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</section>
