<?php
$isAdmin = strtoupper((string) ($user['role'] ?? 'CLIENTE')) === 'ADMIN';
?>
<section class="hero-banner inner">
    <div class="hero-banner-copy">
        <span class="section-kicker">Perfil</span>
        <h1><?= htmlspecialchars($user['name'] ?? 'Usuário') ?></h1>
        <p><?= htmlspecialchars($user['email'] ?? '') ?></p>
    </div>
</section>

<section class="metrics-row">
    <article class="metric-card">
        <span>Documentos totais</span>
        <strong><?= (int) ($stats['total_documentos'] ?? 0) ?></strong>
        <small><?= $isAdmin ? 'Base da plataforma' : 'Acervo do cliente' ?></small>
    </article>
    <article class="metric-card">
        <span>Fluxo normal</span>
        <strong><?= (int) ($stats['total_documentos_normal'] ?? 0) ?></strong>
        <small>Documentos do ambiente principal</small>
    </article>
    <article class="metric-card">
        <span>Cliente DEMO</span>
        <strong><?= (int) ($stats['total_documentos_demo'] ?? 0) ?></strong>
        <small>Documentos no ambiente DEMO</small>
    </article>
    <article class="metric-card">
        <span>Escopo</span>
        <strong><?= $isAdmin ? 'Admin' : (!empty($clienteAtual['nome']) ? htmlspecialchars($clienteAtual['nome']) : 'Cliente') ?></strong>
        <small>Conta atual</small>
    </article>
</section>

<section class="content-grid two-col">
    <article class="panel-card">
        <div class="panel-head">
            <div>
                <span class="section-kicker">Dados do perfil</span>
                <h3>Configurações da conta</h3>
            </div>
        </div>

        <form class="form-grid" method="POST" action="<?= URL_BASE ?>app/atualizarPerfil">
            <label><span>Nome</span><input type="text" name="nome" value="<?= htmlspecialchars($user['name'] ?? '') ?>" required></label>
            <label><span>E-mail</span><input type="email" name="email" value="<?= htmlspecialchars($user['email'] ?? '') ?>" required></label>
            <label><span>Telefone</span><input type="text" name="telefone" placeholder="Opcional"></label>
            <label><span>Cargo / função</span><input type="text" name="cargo" placeholder="Opcional"></label>
            <button type="submit" class="primary-btn">Salvar dados</button>
        </form>
    </article>

    <article class="panel-card accent-panel">
        <div class="panel-head">
            <div>
                <span class="section-kicker">Segurança</span>
                <h3>Trocar senha</h3>
            </div>
        </div>

        <form class="form-grid" method="POST" action="<?= URL_BASE ?>app/alterarSenha">
            <label><span>Senha atual</span><input type="password" name="senha_atual" required></label>
            <label><span>Nova senha</span><input type="password" name="nova_senha" required></label>
            <label><span>Confirmar nova senha</span><input type="password" name="confirmar_senha" required></label>
            <button type="submit" class="primary-btn">Atualizar senha</button>
        </form>

        <div class="panel-head" style="margin-top:22px;">
            <div>
                <span class="section-kicker">Suporte</span>
                <h3>Contato da conta</h3>
            </div>
        </div>

        <div class="info-tiles">
            <div class="mini-card">
                <strong>E-mail de suporte</strong>
                <small><?= htmlspecialchars($clienteAtual['suporte_email'] ?? 'suporte@demo.com') ?></small>
            </div>
            <div class="mini-card">
                <strong>Telefone</strong>
                <small><?= htmlspecialchars($clienteAtual['suporte_telefone'] ?? '(11) 0000-0000') ?></small>
            </div>
            <div class="mini-card">
                <strong>Cliente DEMO</strong>
                <small><?= !empty($clienteAtual['demo_habilitado']) ? 'Liberado para esta conta' : 'Não liberado para esta conta' ?></small>
            </div>
        </div>
    </article>
</section>
