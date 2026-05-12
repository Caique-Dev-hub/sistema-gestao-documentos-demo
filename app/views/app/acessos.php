<?php
?>
<section class="hero-banner inner">
    <div class="hero-banner-copy">
        <span class="section-kicker">Liberar acesso</span>
        <h1>Crie login, cliente e regras da conta no mesmo fluxo.</h1>
        <p>Agora você pode definir, no mesmo cadastro, se o cliente terá acesso ao ambiente Cliente DEMO e quais serão os contatos de suporte exibidos no perfil.</p>
    </div>
</section>

<section class="content-grid split-rail">
    <article class="panel-card sticky-panel">
        <div class="panel-head">
            <div>
                <span class="section-kicker">Novo acesso</span>
                <h3>Cadastro centralizado</h3>
            </div>
        </div>

        <form class="form-grid" method="POST" action="<?= URL_BASE ?>app/salvarUsuario">
            <label><span>Nome do login</span><input type="text" name="nome" required></label>
            <label><span>E-mail</span><input type="email" name="email" required></label>
            <div class="duo-fields">
                <label>
                    <span>Perfil</span>
                    <select name="perfil" required>
                        <option value="CLIENTE">CLIENTE</option>
                        <option value="ADMIN">ADMIN</option>
                    </select>
                </label>
                <label><span>Senha inicial</span><input type="text" name="senha" value="123456"></label>
            </div>
            <div class="duo-fields">
                <label><span>Telefone</span><input type="text" name="telefone"></label>
                <label><span>Cargo / função</span><input type="text" name="cargo"></label>
            </div>

            <div class="mini-card">
                <strong>Vincular cliente existente</strong>
                <small>Use este campo se o cliente já estiver cadastrado.</small>
            </div>
            <label>
                <span>Cliente existente</span>
                <select name="cliente_id">
                    <option value="">Nenhum / vou cadastrar um novo abaixo</option>
                    <?php foreach ($clientes as $cliente): ?>
                        <option value="<?= (int) $cliente['id'] ?>"><?= htmlspecialchars($cliente['nome']) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>

            <div class="mini-card">
                <strong>Novo cliente</strong>
                <small>Preencha esta parte apenas quando o acesso for de um cliente novo.</small>
            </div>
            <label><span>Nome do cliente</span><input type="text" name="cliente_nome"></label>
            <label><span>CPF/CNPJ</span><input type="text" name="cliente_documento"></label>
            <label><span>Responsável</span><input type="text" name="cliente_responsavel"></label>
            <label><span>Observações</span><textarea name="cliente_observacao"></textarea></label>
            <label style="display:flex;align-items:center;gap:10px;"><input type="checkbox" name="cliente_demo_habilitado" value="1"> <span>Liberar ambiente Cliente DEMO para este cliente</span></label>
            <div class="duo-fields">
                <label><span>E-mail de suporte</span><input type="email" name="suporte_email" placeholder="suporte@demo.com"></label>
                <label><span>Telefone de suporte</span><input type="text" name="suporte_telefone" placeholder="(11) 0000-0000"></label>
            </div>

            <button type="submit" class="primary-btn">Criar acesso</button>
        </form>
    </article>

    <article class="panel-card">
        <div class="panel-head">
            <div>
                <span class="section-kicker">Listagem</span>
                <h3>Acessos cadastrados</h3>
            </div>
        </div>

        <form class="form-grid" method="GET" action="<?= URL_BASE ?>app/acessos" style="margin-bottom:16px;">
            <label><span>Busca</span><input type="text" name="busca" value="<?= htmlspecialchars($filtros['busca'] ?? '') ?>" placeholder="Nome, e-mail, cliente, documento"></label>
            <div class="duo-fields">
                <label>
                    <span>Perfil</span>
                    <select name="perfil">
                        <option value="">Todos</option>
                        <option value="ADMIN" <?= ($filtros['perfil'] ?? '') === 'ADMIN' ? 'selected' : '' ?>>ADMIN</option>
                        <option value="CLIENTE" <?= ($filtros['perfil'] ?? '') === 'CLIENTE' ? 'selected' : '' ?>>CLIENTE</option>
                    </select>
                </label>
                <label>
                    <span>Status</span>
                    <select name="status">
                        <option value="">Todos</option>
                        <option value="ATIVO" <?= ($filtros['status'] ?? '') === 'ATIVO' ? 'selected' : '' ?>>ATIVO</option>
                        <option value="BLOQUEADO" <?= ($filtros['status'] ?? '') === 'BLOQUEADO' ? 'selected' : '' ?>>BLOQUEADO</option>
                    </select>
                </label>
            </div>
            <button type="submit" class="primary-btn">Filtrar</button>
        </form>

        <div class="card-list">
            <?php if (empty($usuarios)): ?>
                <div class="empty-state">Nenhum usuário encontrado.</div>
            <?php endif; ?>

            <?php foreach ($usuarios as $usuario): ?>
                <a class="entity-card" href="<?= URL_BASE ?>app/usuario/<?= (int) $usuario['id'] ?>">
                    <div class="entity-icon"><i class="fa-solid fa-user-shield"></i></div>
                    <div class="entity-copy">
                        <strong><?= htmlspecialchars($usuario['nome']) ?></strong>
                        <small><?= htmlspecialchars($usuario['email']) ?></small>
                        <small><?= htmlspecialchars($usuario['perfil']) ?> • <?= htmlspecialchars($usuario['status']) ?> • <?= htmlspecialchars($usuario['cliente_nome'] ?? 'Sem cliente') ?></small>
                        <?php if (!empty($usuario['cliente_nome'])): ?>
                            <small>Cliente DEMO: <?= !empty($usuario['cliente_demo_habilitado']) ? 'Sim' : 'Não' ?></small>
                        <?php endif; ?>
                    </div>
                    <i class="fa-solid fa-chevron-right entity-arrow"></i>
                </a>
            <?php endforeach; ?>
        </div>
    </article>
</section>