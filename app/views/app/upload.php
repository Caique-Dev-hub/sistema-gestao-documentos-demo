<?php $isAdmin = !empty($isAdmin); ?>
<section class="hero-banner inner">
    <div class="hero-banner-copy">
        <span class="section-kicker">Enviar documento</span>
        <h1><?= $isAdmin ? 'Publique arquivos para o ambiente normal.' : 'Anexe arquivos no seu ambiente normal.' ?></h1>
        <p><?= $isAdmin ? 'Esta tela continua dedicada ao fluxo principal. O módulo Cliente DEMO possui uma área própria, separada da operação normal.' : 'Use esta tela para enviar arquivos do seu ambiente normal. O módulo Cliente DEMO segue separado no menu lateral.' ?></p>
    </div>
</section>

<section class="content-grid split-rail">
    <article class="panel-card sticky-panel accent-panel">
        <div class="panel-head">
            <div>
                <span class="section-kicker">Regras</span>
                <h3>Padrão aceito</h3>
            </div>
        </div>
        <div class="info-tiles">
            <div class="mini-card"><strong>Formatos</strong><small>PDF, JPG e PNG</small></div>
            <div class="mini-card"><strong>Obrigatório</strong><small><?= $isAdmin ? 'Escolher cliente, organização e categoria' : 'Escolher sua organização e categoria' ?></small></div>
            <div class="mini-card"><strong>Cliente DEMO</strong><small>Possui módulo próprio no menu lateral</small></div>
        </div>
    </article>

    <article class="panel-card">
        <div class="panel-head">
            <div>
                <span class="section-kicker">Novo envio</span>
                <h3>Preencher documento</h3>
            </div>
        </div>

        <form class="form-grid" method="POST" action="<?= URL_BASE ?>app/salvarDocumento" enctype="multipart/form-data">
            <label><span>Nome do documento</span><input type="text" name="nome_documento" placeholder="Ex.: Contrato social" required></label>

            <?php if ($isAdmin): ?>
            <label>
                <span>Cliente</span>
                <select name="cliente_id" required>
                    <option value="">Selecione o cliente</option>
                    <?php foreach ($clientes as $cliente): ?>
                        <option value="<?= (int) $cliente['id'] ?>"><?= htmlspecialchars($cliente['nome']) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <?php else: ?>
            <div class="mini-card"><strong>Escopo do envio</strong><small><?= htmlspecialchars($clientes[0]['nome'] ?? 'Seu cliente') ?></small></div>
            <?php endif; ?>

            <div class="duo-fields">
                <label><span>Organização</span><select name="setor_id" required>
                    <option value="">Selecione</option>
                    <?php foreach ($setores as $setor): ?>
                        <option value="<?= (int) $setor['id'] ?>"><?= htmlspecialchars($setor['nome']) ?></option>
                    <?php endforeach; ?>
                </select></label>

                <label><span>Categoria</span><select name="tipo_documento_id" required>
                    <option value="">Selecione</option>
                    <?php foreach ($tipos as $tipo): ?>
                        <option value="<?= (int) $tipo['id'] ?>"><?= htmlspecialchars($tipo['nome']) ?> • <?= htmlspecialchars($tipo['setor_nome']) ?></option>
                    <?php endforeach; ?>
                </select></label>
            </div>

            <div class="duo-fields">
                <label><span>Data do documento</span><input type="date" name="data_documento" required></label>
                <label><span>Número do documento</span><input type="text" name="numero_documento" placeholder="Opcional"></label>
            </div>

            <div class="duo-fields">
                <label><span>Valor</span><input type="number" step="0.01" name="valor" placeholder="Opcional"></label>
                <label><span>Arquivo</span><input type="file" name="arquivo" accept=".pdf,.jpg,.jpeg,.png" required></label>
            </div>

            <label><span>Observação</span><textarea name="observacao" placeholder="Descrição complementar"></textarea></label>
            <button type="submit" class="primary-btn">Enviar documento</button>
        </form>
    </article>
</section>
