<?php
$isAdmin = !empty($isAdmin);
?>
<section class="hero-banner inner">
    <div class="hero-banner-copy">
        <span class="section-kicker">Organização</span>
        <h1><?= htmlspecialchars($setor['nome']) ?></h1>
        <p><?= htmlspecialchars($setor['descricao'] ?: 'Estrutura sem descrição cadastrada.') ?></p>
    </div>
    <?php if ($isAdmin): ?>
        <a class="solid-btn" href="<?= URL_BASE ?>app/upload">Enviar documento</a>
    <?php endif; ?>
</section>

<section class="metrics-row">
    <article class="metric-card"><span>Documentos</span><strong><?= (int) ($statsSetor['total_documentos'] ?? 0) ?></strong><small>Arquivos nesta estrutura</small></article>
    <article class="metric-card"><span>Categorias</span><strong><?= (int) ($statsSetor['total_tipos'] ?? 0) ?></strong><small>Classificações relacionadas</small></article>
    <article class="metric-card"><span>Clientes</span><strong><?= (int) ($statsSetor['total_clientes'] ?? 0) ?></strong><small>Escopo atual</small></article>
</section>

<section class="content-grid two-col">
    <article class="panel-card">
        <div class="panel-head">
            <div>
                <span class="section-kicker">Categorias</span>
                <h3>Tipos desta estrutura</h3>
            </div>
        </div>
        <div class="pill-list">
            <?php if (empty($tipos)): ?><div class="empty-state">Nenhuma categoria cadastrada.</div><?php endif; ?>
            <?php foreach ($tipos as $tipo): ?>
                <div class="mini-card">
                    <strong><?= htmlspecialchars($tipo['nome']) ?></strong>
                    <small><?= (int) $tipo['total_documentos'] ?> documentos</small>
                </div>
            <?php endforeach; ?>
        </div>

        <form class="form-grid" method="POST" action="<?= URL_BASE ?>app/salvarTipo" style="margin-top:18px;">
            <input type="hidden" name="setor_id" value="<?= (int) $setor['id'] ?>">
            <input type="hidden" name="redirect" value="app/setor/<?= (int) $setor['id'] ?>">
            <label><span>Nova categoria</span><input type="text" name="nome" placeholder="Ex.: Contratos, comprovantes, notas" required></label>
            <button type="submit" class="primary-btn">Criar categoria</button>
        </form>
    </article>

    <article class="panel-card">
        <div class="panel-head">
            <div>
                <span class="section-kicker">Documentos</span>
                <h3>Últimos arquivos desta organização</h3>
            </div>
        </div>
        <div class="document-list-v2">
            <?php if (empty($documentos)): ?><div class="empty-state">Nenhum documento nesta estrutura.</div><?php endif; ?>
            <?php foreach ($documentos as $doc): ?>
                <article class="document-row">
                    <div class="document-icon"><i class="fa-solid fa-file-lines"></i></div>
                    <div class="document-copy">
                        <strong><?= htmlspecialchars($doc['nome_documento']) ?></strong>
                        <small><?= htmlspecialchars($doc['tipo_nome']) ?> • <?= htmlspecialchars($doc['cliente_nome']) ?></small>
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
</section>
<style>.document-list-v2{max-height:420px;overflow-y:auto;padding-right:8px}</style>
