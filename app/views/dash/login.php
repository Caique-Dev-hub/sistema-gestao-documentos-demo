<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Painel DEMO</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap');

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: "Poppins", sans-serif;
        }

        body {
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            background: #0a0a0a url('') center/cover no-repeat;
            /* adicione a imagem aqui */
            position: relative;
            overflow: hidden;
        }

        /* camada de cor e degradê sobre a imagem */
        body::before {
            content: "";
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, #152e33 0%, #3b5269 100%);
            opacity: 0.82;
            z-index: 0;
        }

        .login-card {
            position: relative;
            z-index: 1;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(16px);
            border-radius: 24px;
            padding: 48px 40px;
            width: 460px;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.45);
            text-align: center;
            color: #fff;
            transition: transform 0.3s ease;
        }

        .login-card:hover {
            transform: translateY(-5px);
        }

        .login-card h2 {
            font-size: 30px;
            font-weight: 600;
            color: #fff;
            margin-bottom: 28px;
            letter-spacing: -0.5px;
        }

        .input-group {
            margin-bottom: 22px;
            text-align: left;
        }

        .input-group label {
            display: block;
            margin-bottom: 6px;
            color: #b0cad6;
            font-size: 15px;
            font-weight: 500;
        }

        .input-group input {
            width: 100%;
            padding: 12px 16px;
            border: 1px solid rgba(255, 255, 255, 0.25);
            border-radius: 12px;
            font-size: 15px;
            color: #fff;
            background: rgba(21, 46, 51, 0.35);
            transition: all 0.3s;
            outline: none;
        }

        .input-group input:focus {
            border-color: #3b5269;
            box-shadow: 0 0 0 3px rgba(59, 82, 105, 0.3);
            background: rgba(21, 46, 51, 0.55);
        }

        .login-btn {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #152e33 0%, #3b5269 100%);
            color: #fff;
            border: none;
            border-radius: 999px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 10px;
            letter-spacing: 0.4px;
        }

        .login-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(59, 82, 105, 0.35);
        }

        .forgot-password {
            display: block;
            margin-top: 16px;
            color: #b0cad6;
            text-decoration: none;
            font-size: 14px;
            transition: color 0.3s;
        }

        .forgot-password:hover {
            color: #fff;
        }

        .divider {
            margin: 25px 0;
            height: 1px;
            background: linear-gradient(to right, transparent, rgba(255, 255, 255, 0.2), transparent);
        }

        .social-login {
            display: flex;
            justify-content: center;
            gap: 16px;
        }

        .social-btn {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(255, 255, 255, 0.1);
            color: #fff;
            text-decoration: none;
            transition: all 0.3s;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .social-btn:hover {
            transform: translateY(-3px);
            background: rgba(255, 255, 255, 0.2);
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.25);
        }

        @media (max-width: 520px) {
            .login-card {
                width: 90%;
                padding: 36px 24px;
            }

            .login-card h2 {
                font-size: 24px;
            }
        }
        
    </style>
</head>

<body>
    <div class="login-card">
        <h2>Área Administrativa</h2>
        <?php if (!empty($erro)): ?>
            <p style="
        background: rgba(255, 60, 60, 0.1);
        border: 1px solid rgba(255, 60, 60, 0.3);
        color: #ff9b9b;
        text-align: center;
        border-radius: 8px;
        padding: 10px;
        margin-bottom: 20px;
    ">
                <?= htmlspecialchars($erro) ?>
            </p>
        <?php endif; ?>
        <form action="<?= URL_BASE ?>dash/index" method="POST">
            <div class="input-group">
                <label for="nome">Usuário</label>
                <input type="email" name="email" placeholder="Usuário Demo" required>
                <div class="input-group">
                    <label for="senha">Senha</label>
                    <input type="password" name="senha" placeholder="••••••••" required>
                </div>
            </div>
            <button type="submit" class="login-btn">Entrar</button>
        </form>

        <div class="divider"></div>
    </div>
</body>

</html>