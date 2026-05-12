<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formulários Recebidos — Painel DEMO</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        :root {
            --primary: #3b5269;
            --accent: #5ac18e;
            --bg: #f6f8fc;
            --text: #2b3541;
            --card-bg: #fff;
            --border: #e1e6ec;
        }

        [data-theme="dark"] {
            --primary: #93b2d8;
            --accent: #6ddeaa;
            --bg: #1e252f;
            --text: #f4f7f9;
            --card-bg: #2c3440;
            --border: #3a4553;
        }

        body {
            background: var(--bg);
            color: var(--text);
            margin: 0;
            font-family: "Poppins", sans-serif;
        }

        header {
            background: linear-gradient(135deg, #2c3e50, var(--primary));
            padding: 22px 50px;
            color: #fff;
            display: flex;
            justify-content: space-between;
        }

        .container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .filter-box {
            background: var(--card-bg);
            border: 1px solid var(--border);
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 25px;
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }

        .filter-box input,
        .filter-box select {
            padding: 12px;
            border-radius: 10px;
            border: 1px solid var(--border);
            background: var(--bg);
        }

        .filter-box button {
            padding: 12px 18px;
            background: var(--primary);
            color: #fff;
            border: none;
            border-radius: 10px;
            cursor: pointer;
        }

        .table-card {
            background: var(--card-bg);
            border-radius: 12px;
            border: 1px solid var(--border);
            padding: 25px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            font-size: 13px;
            padding: 10px;
            text-transform: uppercase;
            color: var(--primary);
            border-bottom: 1px solid var(--border);
        }

        td {
            padding: 12px;
            border-bottom: 1px solid var(--border);
        }

        tr:hover td {
            background: rgba(0, 0, 0, 0.03);
        }

        .paginacao {
            margin-top: 20px;
            text-align: center;
        }

        .paginacao a {
            padding: 8px 12px;
            background: var(--primary);
            color: #fff;
            margin: 3px;
            border-radius: 8px;
            text-decoration: none;
        }

        .modal {
            position: fixed;
            inset: 0;
            display: none;
            background: rgba(0, 0, 0, 0.6);
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            background: var(--card-bg);
            padding: 30px;
            border-radius: 12px;
            max-width: 500px;
            width: 90%;
        }
    </style>
</head>

<body>

    <header>
        <h2>Formulários Recebidos</h2>
        <a href="<?= URL_BASE ?>dash/home" style="color:#fff;text-decoration:none;">Voltar</a>
    </header>

    <div class="container">

        <!-- FILTROS -->
        <form class="filter-box" method="GET">
            <input type="text" name="busca" value="<?= $busca ?>" placeholder="Buscar nome, e-mail ou telefone">
            <button>Filtrar</button>
        </form>

        <!-- TABELA -->
        <div class="table-card">
            <table>
                <thead>
                    <tr>
                        <th>Data</th>
                        <th>Nome</th>
                        <th>Email</th>
                        <th>Telefone</th>
                        <th>Tipo</th>
                        <th>Área</th>
                    </tr>
                </thead>

                <tbody>
                    <?php foreach ($lista as $f): ?>
                        <tr onclick="abrirModal(<?= htmlspecialchars(json_encode($f)) ?>)">
                            <td><?= date('d/m/Y H:i', strtotime($f['criado_em'])) ?></td>
                            <td><?= $f['nome'] ?></td>
                            <td><?= $f['email'] ?></td>
                            <td><?= $f['telefone'] ?></td>
                            <td><?= $f['tipo'] ?></td>
                            <td><?= $f['area'] ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <!-- PAGINAÇÃO -->
            <div class="paginacao">
                <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
                    <a href="?p=<?= $i ?>&busca=<?= $busca ?>&tipo=<?= $tipo ?>&area=<?= $area ?>">
                        <?= $i ?>
                    </a>
                <?php endfor; ?>
            </div>
        </div>

    </div>

    <!-- MODAL -->
    <div class="modal" id="modal">
        <div class="modal-content" id="modalContent"></div>
    </div>

    <script>
        function abrirModal(dados) {
            let html = `
            <h3>Detalhes do Formulário</h3>
            <p><strong>Nome:</strong> ${dados.nome}</p>
            <p><strong>Email:</strong> ${dados.email}</p>
            <p><strong>Telefone:</strong> ${dados.telefone}</p>
            <p><strong>Tipo:</strong> ${dados.tipo}</p>
            <p><strong>Área:</strong> ${dados.area}</p>
            <p><strong>Data:</strong> ${dados.criado_em}</p>
            <br>
            <button onclick="fecharModal()" style="padding:10px 18px;border:none;background:var(--primary);color:#fff;border-radius:10px">Fechar</button>
        `;

            document.getElementById('modalContent').innerHTML = html;
            document.getElementById('modal').style.display = 'flex';
        }

        function fecharModal() {
            document.getElementById('modal').style.display = 'none';
        }
    </script>

</body>

</html>