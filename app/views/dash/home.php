<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel DEMO - Dashboard</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        :root {
            --primary: #3b5269;
            --accent: #5ac18e;
            --bg: #f6f8fc;
            --card-bg: #fff;
            --text: #2b3541;
            --border: #e2e6eb;
        }

        [data-theme="dark"] {
            --primary: #93b2d8;
            --accent: #6ddeaa;
            --bg: #1e252f;
            --card-bg: #2b3440;
            --text: #f3f5f8;
            --border: #3a4553;
        }

        * {
            box-sizing: border-box;
            transition: background 0.3s, color 0.3s, box-shadow 0.3s;
        }

        body {
            font-family: "Poppins", sans-serif;
            margin: 0;
            background: var(--bg);
            color: var(--text);
        }

        header {
            background: linear-gradient(135deg, #2c3e50, var(--primary));
            color: #fff;
            padding: 20px 50px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 3px 12px rgba(0, 0, 0, 0.15);
        }

        header h1 {
            font-size: 22px;
            margin: 0;
            font-weight: 600;
            letter-spacing: 0.5px;
        }

        .header-actions {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .header-actions a {
            background: #ffffff20;
            color: #fff;
            padding: 10px 20px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 500;
            transition: 0.3s;
        }

        .header-actions a:hover {
            background: #ffffff35;
            transform: translateY(-2px);
        }

        .dark-toggle {
            cursor: pointer;
            background: #ffffff20;
            border: none;
            color: #fff;
            font-size: 18px;
            padding: 10px 14px;
            border-radius: 8px;
            transition: 0.3s;
        }

        .dark-toggle:hover {
            background: #ffffff35;
        }

        .menu {
            background: var(--card-bg);
            border-bottom: 1px solid var(--border);
            padding: 14px 0;
            text-align: center;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
        }

        .menu a {
            margin: 0 18px;
            text-decoration: none;
            color: var(--primary);
            font-weight: 500;
            transition: 0.2s;
        }

        .menu a:hover {
            color: var(--text);
            border-bottom: 2px solid var(--primary);
            padding-bottom: 4px;
        }

        .container {
            max-width: 1150px;
            margin: 50px auto;
            padding: 0 20px;
        }

        .summary {
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 20px 25px;
            margin-bottom: 30px;
            font-size: 15px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        }

        .summary strong {
            color: var(--primary);
        }

        .cards {
            display: flex;
            gap: 25px;
            margin-bottom: 35px;
            flex-wrap: wrap;
        }

        .card {
            flex: 1;
            background: var(--card-bg);
            border-radius: 16px;
            padding: 30px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            text-align: center;
            border: 1px solid var(--border);
            transition: transform 0.3s;
        }

        .card:hover {
            transform: translateY(-6px);
        }

        .card i {
            font-size: 26px;
            color: var(--primary);
            margin-bottom: 10px;
        }

        .card h3 {
            margin: 0;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 500;
        }

        .card p {
            font-size: 36px;
            margin: 8px 0;
            font-weight: 600;
            color: var(--primary);
        }

        .trend {
            font-size: 14px;
        }

        .trend.up {
            color: var(--accent);
        }

        .trend.down {
            color: #d9534f;
        }

        .chart-section {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 25px;
            margin-bottom: 40px;
        }

        .chart-card {
            background: var(--card-bg);
            border-radius: 16px;
            padding: 30px;
            border: 1px solid var(--border);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.08);
        }

        .chart-card h3 {
            margin: 0 0 20px;
            font-size: 17px;
            font-weight: 600;
        }

        .table-card {
            background: var(--card-bg);
            border-radius: 16px;
            padding: 25px 30px;
            border: 1px solid var(--border);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.07);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        th,
        td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid var(--border);
        }

        th {
            color: var(--primary);
            text-transform: uppercase;
            font-size: 13px;
        }

        tr:hover {
            background: rgba(0, 0, 0, 0.03);
        }

        @media (max-width: 900px) {
            .chart-section {
                grid-template-columns: 1fr;
            }

            .cards {
                flex-direction: column;
            }
        }
    </style>
</head>

<body>
    <header>
        <h1>Painel DEMO</h1>
        <div class="header-actions">
            <button class="dark-toggle" id="darkModeBtn"><i class="fa-solid fa-moon"></i></button>
            <a href="<?= URL_BASE ?>dash/logout">Sair</a>
        </div>
    </header>

    <div class="menu">
        <a href="<?= URL_BASE ?>dash/home">Dashboard</a>
        <a href="<?= URL_BASE ?>dash/formularios">Formulários</a>
    </div>

    <div class="container">
        <div class="summary">
            <?php
            $trendIcon = $diferenca >= 0
                ? '<i class="fa-solid fa-arrow-up" style="color:#5ac18e"></i>'
                : '<i class="fa-solid fa-arrow-down" style="color:#d9534f"></i>';
            ?>
            Hoje você recebeu <strong><?= $totalHoje ?></strong> novos formulários,
            <?= $trendIcon ?>
            <strong><?= abs(round($diferenca, 1)) ?>%</strong>
            <?= $diferenca >= 0 ? 'a mais' : 'a menos' ?> que ontem.
        </div>

        <div class="cards">
            <div class="card">
                <i class="fa-solid fa-paper-plane"></i>
                <h3>Enviados Hoje</h3>
                <p><?= $totalHoje ?></p>
                <div class="trend <?= $diferenca >= 0 ? 'up' : 'down' ?>">
                    <?= $diferenca >= 0 ? '▲ ' : '▼ ' ?><?= abs(round($diferenca, 1)) ?>%
                </div>
            </div>
            <div class="card">
                <i class="fa-solid fa-calendar-week"></i>
                <h3>Total na Semana</h3>
                <p><?= array_sum($formulariosSemana) ?></p>
            </div>
            <div class="card">
                <i class="fa-solid fa-chart-line"></i>
                <h3>Média Diária</h3>
                <p><?= round(array_sum($formulariosSemana) / max(1, count($formulariosSemana))) ?></p>
            </div>
        </div>

        <div class="chart-section">
            <div class="chart-card">
                <h3>Envios de formulários nesta semana</h3>
                <canvas id="formChart"></canvas>
            </div>
            <div class="chart-card">
                <h3>Distribuição por tipo</h3>
                <canvas id="pieChart"></canvas>
            </div>
        </div>

        <div class="chart-card">
            <h3>Envios mensais (últimos 6 meses)</h3>
            <canvas id="chartMensal"></canvas>
        </div>

        <div class="table-card">
            <h3>Atividades recentes</h3>
            <table>
                <thead>
                    <tr>
                        <th>Data</th>
                        <th>Formulário</th>
                        <th>Responsável</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($ultimosFormularios as $f): ?>
                        <tr>
                            <td><?= date('d/m/Y H:i', strtotime($f['criado_em'])) ?></td>
                            <td><?= $f['tipo'] ?? '-' ?></td>
                            <td><?= $f['nome'] ?></td>
                        </tr>
                    <?php endforeach; ?>

                </tbody>
            </table>
        </div>
    </div>

    <script>
        const darkBtn = document.getElementById('darkModeBtn');
        const body = document.body;
        if (localStorage.getItem('theme') === 'dark') {
            body.dataset.theme = 'dark';
            darkBtn.innerHTML = '<i class="fa-solid fa-sun"></i>';
        }
        darkBtn.onclick = () => {
            const dark = body.dataset.theme === 'dark';
            body.dataset.theme = dark ? '' : 'dark';
            darkBtn.innerHTML = dark ? '<i class="fa-solid fa-moon"></i>' : '<i class="fa-solid fa-sun"></i>';
            localStorage.setItem('theme', dark ? 'light' : 'dark');
        };

        // Gráfico semanal
        const ctx = document.getElementById('formChart').getContext('2d');
        const grad = ctx.createLinearGradient(0, 0, 0, 300);
        grad.addColorStop(0, 'rgba(59,82,105,0.4)');
        grad.addColorStop(1, 'rgba(59,82,105,0)');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?= json_encode($dias) ?>,
                datasets: [{
                    label: 'Envios',
                    data: <?= json_encode($formulariosSemana) ?>,
                    borderColor: '#3b5269',
                    backgroundColor: grad,
                    fill: true,
                    tension: 0.35
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });

        // Gráfico pizza
        new Chart(document.getElementById('pieChart'), {
            type: 'doughnut',
            data: {
                labels: <?= json_encode(array_keys($distribuicaoTipos)) ?>,
                datasets: [{
                    data: <?= json_encode(array_values($distribuicaoTipos)) ?>,
                    backgroundColor: ['#3b5269', '#5ac18e', '#93b2d8', '#b0c4de', '#869ab8']
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });

        // Gráfico mensal
        new Chart(document.getElementById('chartMensal'), {
            type: 'bar',
            data: {
                labels: <?= json_encode(array_column($resumoMensal, 'mes')) ?>,
                datasets: [{
                    label: 'Formulários',
                    data: <?= json_encode(array_column($resumoMensal, 'total')) ?>,
                    backgroundColor: 'rgba(59,82,105,0.7)',
                    borderRadius: 8
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });
    </script>
</body>

</html>