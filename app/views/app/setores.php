<?php
$isAdmin = !empty($isAdmin);
?>
<section class="hero-banner inner">
    <div class="hero-banner-copy">
        <span class="section-kicker">Organização</span>
        <h1><?= $isAdmin ? 'Estruture o ambiente normal da operação.' : 'Organize sua estrutura de pastas
' ?></h1>
        <p><?= $isAdmin ? 'Crie estruturas para o fluxo principal e deixe a área Cliente DEMO separada.' : 'Crie e organize pastas para manter seus documentos estruturados do seu jeito.
' ?></p>
    </div>
</section>

<section class="content-grid split-rail">
    <article class="panel-card sticky-panel">
        <div class="panel-head">
            <div>
                <span class="section-kicker">Cadastro</span>
                <h3>Nova organização</h3>
            </div>
        </div>

        <form class="form-grid" method="POST" action="<?= URL_BASE ?>app/salvarSetor">
            <label><span>Nome da pasta / área</span><input type="text" name="nome" required></label>
            <label><span>Descrição</span><textarea name="descricao" placeholder="Ex.: Financeiro, contratos, documentos de clientes ou outra lógica interna."></textarea></label>
            <button type="submit" class="primary-btn">Salvar organização</button>
        </form>
    </article>

    <article class="panel-card">
        <div class="panel-head">
            <div>
                <span class="section-kicker">Estrutura</span>
                <h3>Organizações cadastradas</h3>
            </div>
        </div>

        <div class="card-list">
            <?php if (empty($setores)): ?><div class="empty-state">Nenhuma organização encontrada.</div><?php endif; ?>
            <?php foreach ($setores as $setor): ?>
                <a class="entity-card" href="<?= URL_BASE ?>app/setor/<?= (int) $setor['id'] ?>">
                    <div class="entity-icon"><i class="fa-solid fa-layer-group"></i></div>
                    <div class="entity-copy">
                        <strong><?= htmlspecialchars($setor['nome']) ?></strong>
                        <small><?= (int) ($setor['total_documentos'] ?? 0) ?> documentos</small>
                        <small><?= (int) ($setor['total_tipos'] ?? 0) ?> categorias</small>
                    </div>
                    <i class="fa-solid fa-chevron-right entity-arrow"></i>
                </a>
            <?php endforeach; ?>
        </div>
    </article>
</section>
