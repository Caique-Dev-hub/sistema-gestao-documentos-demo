<?php
// ARQUIVO: app/views/app/documentos.php
$canManage = !empty($canManage);
$userRole = strtoupper((string) ($user['role'] ?? 'CLIENTE'));
?>
<section class="hero-banner inner">
    <div class="hero-banner-copy">
        <span class="section-kicker">Documentos</span>
        <h1><?= $canManage ? 'Acervo completo da plataforma.' : 'Faça upload dos seus documentos' ?></h1>
        <p><?= $canManage ? 'Filtre por setor, cliente, categoria e período para localizar arquivos rapidamente.' : 'Adicione arquivos e organize seus documentos com facilidade.

' ?></p>
    </div>
    <a class="solid-btn" href="<?= URL_BASE ?>app/upload"><?= $canManage ? 'Novo documento' : 'Anexar arquivo' ?></a>
</section>

<section class="content-grid split-rail">
    <article class="panel-card sticky-panel">
        <div class="panel-head">
            <div>
                <span class="section-kicker">Filtro</span>
                <h3>Refinar listagem</h3>
            </div>
        </div>

        <form class="form-grid" method="GET" action="<?= URL_BASE ?>app/documentos">
            <label>
                <span>Busca</span>
                <input type="text" name="busca" value="<?= htmlspecialchars($filtros['busca'] ?? '') ?>" placeholder="Nome, número ou cliente">
            </label>

            <label>
                <span>Setor</span>
                <select name="setor_id">
                    <option value="">Todos</option>
                    <?php foreach ($setores as $setor): ?>
                        <option value="<?= (int) $setor['id'] ?>" <?= ((string) ($filtros['setor_id'] ?? '') === (string) $setor['id']) ? 'selected' : '' ?>><?= htmlspecialchars($setor['nome']) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>

            <?php if ($canManage): ?>
                <label>
                    <span>Cliente</span>
                    <select name="cliente_id">
                        <option value="">Todos</option>
                        <?php foreach ($clientes as $cliente): ?>
                            <option value="<?= (int) $cliente['id'] ?>" <?= ((string) ($filtros['cliente_id'] ?? '') === (string) $cliente['id']) ? 'selected' : '' ?>><?= htmlspecialchars($cliente['nome']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
            <?php endif; ?>

            <label>
                <span>Categoria</span>
                <select name="tipo_documento_id">
                    <option value="">Todas</option>
                    <?php foreach ($tipos as $tipo): ?>
                        <option value="<?= (int) $tipo['id'] ?>" <?= ((string) ($filtros['tipo_documento_id'] ?? '') === (string) $tipo['id']) ? 'selected' : '' ?>><?= htmlspecialchars($tipo['nome']) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>

            <div class="duo-fields">
                <label>
                    <span>Mês</span>
                    <input type="month" name="mes_referencia" value="<?= htmlspecialchars($filtros['mes_referencia'] ?? '') ?>">
                </label>
                <label>
                    <span>Ordenação</span>
                    <select name="ordenacao">
                        <option value="data_documento_desc" <?= ($filtros['ordenacao'] ?? '') === 'data_documento_desc' ? 'selected' : '' ?>>Data doc ↓</option>
                        <option value="data_documento_asc" <?= ($filtros['ordenacao'] ?? '') === 'data_documento_asc' ? 'selected' : '' ?>>Data doc ↑</option>
                        <option value="upload_desc" <?= ($filtros['ordenacao'] ?? '') === 'upload_desc' ? 'selected' : '' ?>>Upload ↓</option>
                        <option value="upload_asc" <?= ($filtros['ordenacao'] ?? '') === 'upload_asc' ? 'selected' : '' ?>>Upload ↑</option>
                    </select>
                </label>
            </div>

            <button type="submit" class="primary-btn">Aplicar filtros</button>
        </form>
    </article>

    <article class="panel-card">
        <div class="panel-head">
            <div>
                <span class="section-kicker">Resultado</span>
                <h3>Lista de documentos</h3>
            </div>
        </div>

        <div class="document-list-v2">
            <?php if (empty($documentos)): ?>
                <div class="empty-state">Nenhum documento encontrado com os filtros informados.</div>
            <?php endif; ?>

            <?php foreach ($documentos as $doc): ?>
                <article class="document-row">
                    <div class="document-icon"><i class="fa-solid fa-file-lines"></i></div>
                    <div class="document-copy">
                        <strong><?= htmlspecialchars($doc['nome_documento']) ?></strong>
                        <small><?= htmlspecialchars($doc['setor_nome']) ?> • <?= htmlspecialchars($doc['tipo_nome']) ?><?php if ($canManage): ?> • <?= htmlspecialchars($doc['cliente_nome']) ?><?php endif; ?></small>
                        <small><?= date('d/m/Y', strtotime($doc['data_documento'])) ?><?php if (!empty($doc['numero_documento'])): ?> • Nº <?= htmlspecialchars($doc['numero_documento']) ?><?php endif; ?></small>
                    </div>
                    <div class="document-tools">
                        <a href="<?= URL_BASE ?>app/visualizar/<?= (int) $doc['id'] ?>" target="_blank" title="Visualizar"><i class="fa-regular fa-eye"></i></a>
                        <a href="<?= URL_BASE ?>app/download/<?= (int) $doc['id'] ?>" title="Baixar"><i class="fa-solid fa-download"></i></a>
                        <?php if ($canManage): ?>
                            <a href="<?= URL_BASE ?>app/excluir/<?= (int) $doc['id'] ?>" onclick="return confirm('Excluir este documento?')" title="Excluir"><i class="fa-regular fa-trash-can"></i></a>
                        <?php endif; ?>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </article>
</section>
