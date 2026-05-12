<?php
$isAdmin = !empty($isAdmin);
$clienteDemoAtivo = !empty($clienteDemoAtivo);
?>
<section class="hero-banner inner">
    <div class="hero-banner-copy">
        <span class="section-kicker">Cliente DEMO</span>
        <h1><?= $isAdmin ? 'Ambiente DEMO separado da operação normal.' : 'Área exclusiva da sua relação com a DEMO.' ?></h1>
        <p><?= $isAdmin ? 'Aqui você configura a estrutura DEMO, publica arquivos e abre solicitações para os clientes habilitados.' : 'A estrutura desta área já é definida pela DEMO. Você acessa os arquivos liberados e pode anexar o que foi solicitado.' ?></p>
    </div>
</section>

<?php if (!$isAdmin && !$clienteDemoAtivo): ?>
<section class="panel-card accent-panel">
    <div class="panel-head">
        <div>
            <span class="section-kicker">Acesso bloqueado</span>
            <h3>Cliente DEMO indisponível para esta conta</h3>
        </div>
    </div>
    <div class="info-tiles">
        <div class="mini-card"><strong>Status</strong><small>Seu acesso ainda não foi habilitado para o ambiente Cliente DEMO.</small></div>
        <div class="mini-card"><strong>Próximo passo</strong><small>Solicite a liberação à equipe DEMO ou ao administrador da sua conta.</small></div>
        <div class="mini-card"><strong>Suporte</strong><small><?= htmlspecialchars($clienteAtual['suporte_email'] ?? 'suporte@demo.com') ?></small></div>
    </div>
</section>
<?php else: ?>
<section class="metrics-row">
    <article class="metric-card"><span>Arquivos DEMO</span><strong><?= count($documentosDemo ?? []) ?></strong><small>Documentos publicados neste ambiente</small></article>
    <article class="metric-card"><span>Solicitações</span><strong><?= count($solicitacoesDemo ?? []) ?></strong><small>Pedidos abertos e concluídos</small></article>
    <article class="metric-card"><span>Estruturas DEMO</span><strong><?= count($setoresDemo ?? []) ?></strong><small>Pastas definidas pela DEMO</small></article>
    <article class="metric-card"><span>Categorias</span><strong><?= count($tiposDemo ?? []) ?></strong><small>Classificações da área DEMO</small></article>
</section>

<section class="content-grid split-rail">
    <article class="panel-card sticky-panel">
        <div class="panel-head">
            <div>
                <span class="section-kicker"><?= $isAdmin ? 'Gestão DEMO' : 'Upload DEMO' ?></span>
                <h3><?= $isAdmin ? 'Publicar ou solicitar arquivos' : 'Anexar documento solicitado' ?></h3>
            </div>
        </div>

        <?php if ($isAdmin): ?>
            <form class="form-grid" method="POST" action="<?= URL_BASE ?>app/salvarSetor">
                <input type="hidden" name="ambiente" value="DEMO">
                <label><span>Nova estrutura DEMO</span><input type="text" name="nome" required></label>
                <label><span>Descrição</span><textarea name="descricao"></textarea></label>
                <button type="submit" class="primary-btn">Criar estrutura DEMO</button>
            </form>

            <form class="form-grid" method="POST" action="<?= URL_BASE ?>app/salvarTipo" style="margin-top:18px;">
                <input type="hidden" name="ambiente" value="DEMO">
                <input type="hidden" name="redirect" value="app/clienteDemo">
                <label><span>Estrutura DEMO</span><select name="setor_id" required>
                    <option value="">Selecione</option>
                    <?php foreach ($setoresDemo as $setor): ?><option value="<?= (int) $setor['id'] ?>"><?= htmlspecialchars($setor['nome']) ?></option><?php endforeach; ?>
                </select></label>
                <label><span>Nova categoria DEMO</span><input type="text" name="nome" required></label>
                <button type="submit" class="outline-btn">Criar categoria DEMO</button>
            </form>

            <form class="form-grid" method="POST" action="<?= URL_BASE ?>app/salvarSolicitacaoDemo" style="margin-top:18px;">
                <label><span>Cliente DEMO</span><select name="cliente_id" required>
                    <option value="">Selecione</option>
                    <?php foreach ($clientesDemo as $cliente): ?><option value="<?= (int) $cliente['id'] ?>"><?= htmlspecialchars($cliente['nome']) ?></option><?php endforeach; ?>
                </select></label>
                <label><span>Estrutura DEMO</span><select name="setor_id">
                    <option value="">Selecione</option>
                    <?php foreach ($setoresDemo as $setor): ?><option value="<?= (int) $setor['id'] ?>"><?= htmlspecialchars($setor['nome']) ?></option><?php endforeach; ?>
                </select></label>
                <label><span>Categoria DEMO</span><select name="tipo_documento_id">
                    <option value="">Selecione</option>
                    <?php foreach ($tiposDemo as $tipo): ?><option value="<?= (int) $tipo['id'] ?>"><?= htmlspecialchars($tipo['nome']) ?> • <?= htmlspecialchars($tipo['setor_nome']) ?></option><?php endforeach; ?>
                </select></label>
                <label><span>Título da solicitação</span><input type="text" name="titulo" required></label>
                <label><span>Descrição</span><textarea name="descricao"></textarea></label>
                <button type="submit" class="primary-btn">Criar solicitação</button>
            </form>
        <?php endif; ?>

        <form class="form-grid" method="POST" action="<?= URL_BASE ?>app/salvarDocumentoDemo" enctype="multipart/form-data" style="margin-top:18px;">
            <?php if ($isAdmin): ?>
                <label><span>Cliente DEMO</span><select name="cliente_id" required>
                    <option value="">Selecione</option>
                    <?php foreach ($clientesDemo as $cliente): ?><option value="<?= (int) $cliente['id'] ?>"><?= htmlspecialchars($cliente['nome']) ?></option><?php endforeach; ?>
                </select></label>
            <?php endif; ?>
            <label><span>Estrutura DEMO</span><select name="setor_id" required>
                <option value="">Selecione</option>
                <?php foreach ($setoresDemo as $setor): ?><option value="<?= (int) $setor['id'] ?>"><?= htmlspecialchars($setor['nome']) ?></option><?php endforeach; ?>
            </select></label>
            <label><span>Categoria DEMO</span><select name="tipo_documento_id" required>
                <option value="">Selecione</option>
                <?php foreach ($tiposDemo as $tipo): ?><option value="<?= (int) $tipo['id'] ?>"><?= htmlspecialchars($tipo['nome']) ?> • <?= htmlspecialchars($tipo['setor_nome']) ?></option><?php endforeach; ?>
            </select></label>
            <label><span>Título do arquivo</span><input type="text" name="nome_documento" required></label>
            <label><span>Solicitação vinculada</span><select name="solicitacao_demo_id">
                <option value="">Opcional</option>
                <?php foreach ($solicitacoesDemo as $sol): ?>
                    <?php if (strtoupper((string) $sol['status']) === 'ABERTA'): ?>
                        <option value="<?= (int) $sol['id'] ?>"><?= htmlspecialchars($sol['titulo']) ?><?= !empty($sol['cliente_nome']) ? ' • ' . htmlspecialchars($sol['cliente_nome']) : '' ?></option>
                    <?php endif; ?>
                <?php endforeach; ?>
            </select></label>
            <div class="duo-fields">
                <label><span>Data</span><input type="date" name="data_documento" required></label>
                <label><span>Arquivo</span><input type="file" name="arquivo" accept=".pdf,.jpg,.jpeg,.png" required></label>
            </div>
            <label><span>Observação</span><textarea name="observacao"></textarea></label>
            <button type="submit" class="primary-btn"><?= $isAdmin ? 'Publicar no Cliente DEMO' : 'Enviar para a DEMO' ?></button>
        </form>
    </article>

    <div class="content-stack">
        <article class="panel-card">
            <div class="panel-head">
                <div>
                    <span class="section-kicker">Solicitações</span>
                    <h3>Pendências e históricos DEMO</h3>
                </div>
            </div>
            <div class="timeline-list">
                <?php if (empty($solicitacoesDemo)): ?>
                    <div class="empty-state">Nenhuma solicitação DEMO encontrada.</div>
                <?php endif; ?>
                <?php foreach ($solicitacoesDemo as $item): ?>
                    <div class="timeline-item">
                        <span class="timeline-dot"></span>
                        <div>
                            <strong><?= htmlspecialchars($item['titulo']) ?></strong>
                            <small><?= htmlspecialchars($item['cliente_nome'] ?? '') ?><?= !empty($item['setor_nome']) ? ' • ' . htmlspecialchars($item['setor_nome']) : '' ?><?= !empty($item['tipo_nome']) ? ' • ' . htmlspecialchars($item['tipo_nome']) : '' ?></small>
                            <small>Status: <?= htmlspecialchars($item['status']) ?> • <?= date('d/m/Y H:i', strtotime($item['created_at'])) ?></small>
                            <?php if (!empty($item['descricao'])): ?><small><?= nl2br(htmlspecialchars($item['descricao'])) ?></small><?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </article>

        <article class="panel-card">
            <div class="panel-head">
                <div>
                    <span class="section-kicker">Documentos DEMO</span>
                    <h3>Arquivos publicados neste ambiente</h3>
                </div>
            </div>
            <div class="document-list-v2">
                <?php if (empty($documentosDemo)): ?>
                    <div class="empty-state">Nenhum documento DEMO encontrado.</div>
                <?php endif; ?>
                <?php foreach ($documentosDemo as $doc): ?>
                    <article class="document-row">
                        <div class="document-icon"><i class="fa-solid fa-file-lines"></i></div>
                        <div class="document-copy">
                            <strong><?= htmlspecialchars($doc['nome_documento']) ?></strong>
                            <small><?= htmlspecialchars($doc['setor_nome']) ?> • <?= htmlspecialchars($doc['tipo_nome']) ?></small>
                            <small><?= htmlspecialchars($doc['cliente_nome']) ?> • <?= htmlspecialchars($doc['origem_upload']) ?> • <?= date('d/m/Y', strtotime($doc['data_documento'])) ?></small>
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
<style>.document-list-v2,.timeline-list{max-height:420px;overflow-y:auto;padding-right:8px}</style>
<?php endif; ?>
