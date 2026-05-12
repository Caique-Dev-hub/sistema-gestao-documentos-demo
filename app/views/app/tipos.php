<?php
$isAdmin = !empty($isAdmin);
?>
<section class="hero-banner inner">
    <div class="hero-banner-copy">
        <span class="section-kicker">Categorias</span>
        <h1><?= $isAdmin ? 'Defina categorias do ambiente normal.' : 'Crie e organize as categorias do seu ambiente normal.' ?></h1>
        <p><?= $isAdmin ? 'Centralize os tipos de arquivos do fluxo principal.' : 'Essas categorias ajudam a localizar e classificar melhor o seu acervo normal.' ?></p>
    </div>
</section>

<section class="content-grid split-rail">
    <article class="panel-card sticky-panel">
        <div class="panel-head">
            <div><span class="section-kicker">Cadastro</span>
                <h3>Nova categoria</h3>
            </div>
        </div>
        <form class="form-grid" method="POST" action="<?= URL_BASE ?>app/salvarTipo">
            <input type="hidden" name="redirect" value="app/tipos">
            <label><span>Organização</span><select name="setor_id" required>
                    <option value="">Selecione</option><?php foreach ($setores as $setor): ?><option value="<?= (int) $setor['id'] ?>"><?= htmlspecialchars($setor['nome']) ?></option><?php endforeach; ?>
                </select></label>
            <label><span>Nome da categoria</span><input type="text" name="nome" placeholder="Ex.: Boleto, contrato, folha, relatório" required></label>
            <button type="submit" class="primary-btn">Salvar categoria</button>
        </form>
    </article>

    <article class="panel-card">
        <div class="panel-head">
            <div><span class="section-kicker">Estrutura</span>
                <h3>Categorias cadastradas</h3>
            </div>
        </div>
        <div class="pill-list">
            <?php foreach ($tipos as $tipo): ?>
                <div class="mini-card">
                    <strong><?= htmlspecialchars($tipo['nome']) ?></strong>
                    <small><?= htmlspecialchars($tipo['setor_nome']) ?> • <?= (int) $tipo['total_documentos'] ?> docs</small>
                </div>
            <?php endforeach; ?>

            <?php if (empty($tipos)): ?>
                <div class="empty-state">Nenhuma categoria encontrada.</div>
            <?php endif; ?>
        </div>
    </article>
</section>
