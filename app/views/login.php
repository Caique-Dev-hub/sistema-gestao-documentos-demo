<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Entrar DEMO Gestão</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="<?= URL_BASE ?>assets/css/app.css">
    <link rel="shortcut icon" href="<?= URL_BASE ?>assets/img/logo.png" type="imagem.iconx">

</head>

<body class="login-page">
    <div class="login-shell">
        <section class="login-panel">
            <div class="login-panel-head">
                <span class="section-kicker">Área do cliente</span>
                <h2>Entrar na plataforma</h2>
                <p>Acesse seu hub de documentos, uploads e histórico de movimentações.</p>
            </div>

            <?php if (!empty($erro)): ?>
                <div class="flash error"><?= htmlspecialchars($erro) ?></div>
            <?php endif; ?>

            <form method="POST" action="<?= URL_BASE ?>login/auth" class="auth-form-demo">
                <label>
                    <span>E-mail</span>
                    <div class="input-shell">
                        <i class="fa-regular fa-envelope"></i>
                        <input type="email" name="email" placeholder="seu@email.com" required>
                    </div>
                </label>

                <label>
                    <span>Senha</span>
                    <div class="input-shell">
                        <i class="fa-solid fa-lock"></i>
                        <input type="password" name="senha" placeholder="••••••••" required>
                    </div>
                </label>

                <button type="submit" class="primary-btn">Entrar</button>
            </form>

            <div class="login-demo">
                <small>Acesso padrão para teste</small>
                <strong>admin@demo.com</strong>
                <strong>cliente@demo.com</strong>
                <span>Senha: 123456</span>
            </div>
        </section>
    </div>
</body>

</html>