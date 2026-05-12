<?php
// ARQUIVO: app/views/layouts/app.php

$userName = $user['name'] ?? ($_SESSION['gestao_user']['name'] ?? 'Usuário');
$userEmail = $user['email'] ?? ($_SESSION['gestao_user']['email'] ?? '');
$userRole = strtoupper((string) ($user['role'] ?? ($_SESSION['gestao_user']['role'] ?? 'CLIENTE')));
$initial = strtoupper(mb_substr($userName, 0, 1));
$notificationBadge = (int) ($notificationBadge ?? 0);

$menu = [
    ['key' => 'home', 'href' => URL_BASE . 'app', 'icon' => 'fa-solid fa-house', 'label' => 'Home'],
    ['key' => 'organizacao', 'href' => URL_BASE . 'app/setores', 'icon' => 'fa-solid fa-table-cells-large', 'label' => 'Organização'],
    ['key' => 'documentos', 'href' => URL_BASE . 'app/documentos', 'icon' => 'fa-solid fa-folder-open', 'label' => 'Documentos'],
];

if ($userRole === 'ADMIN') {
    $menu[] = ['key' => 'upload', 'href' => URL_BASE . 'app/upload', 'icon' => 'fa-solid fa-cloud-arrow-up', 'label' => 'Enviar'];
    $menu[] = ['key' => 'cliente_demo', 'href' => URL_BASE . 'app/clienteDemo', 'icon' => 'fa-solid fa-briefcase', 'label' => 'Cliente DEMO'];
    $menu[] = ['key' => 'clientes', 'href' => URL_BASE . 'app/clientes', 'icon' => 'fa-regular fa-building', 'label' => 'Clientes'];
    $menu[] = ['key' => 'categorias', 'href' => URL_BASE . 'app/tipos', 'icon' => 'fa-solid fa-tags', 'label' => 'Categorias'];
    $menu[] = ['key' => 'acessos', 'href' => URL_BASE . 'app/acessos', 'icon' => 'fa-solid fa-user-shield', 'label' => 'Liberar acesso'];
} else {
    $menu[] = ['key' => 'notificacoes', 'href' => URL_BASE . 'app/notificacoes', 'icon' => 'fa-regular fa-bell', 'label' => 'Notificações', 'badge' => $notificationBadge];
    $menu[] = ['key' => 'cliente_demo', 'href' => URL_BASE . 'app/clienteDemo', 'icon' => 'fa-solid fa-briefcase', 'label' => 'Cliente DEMO'];
}

$menu[] = ['key' => 'perfil', 'href' => URL_BASE . 'app/perfil', 'icon' => 'fa-regular fa-user', 'label' => 'Perfil'];
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title><?= htmlspecialchars($pageTitle ?? 'DEMO Gestão') ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="<?= URL_BASE ?>assets/css/app.css">
</head>

<body>
    <div class="app-shell">
        <aside class="side-nav">
            <a class="side-brand" href="<?= URL_BASE ?>app">
                <span class="side-brand-logo">
                    <img src="<?= URL_BASE ?>assets/img/logo.png" alt="DEMO">
                </span>
                <span class="side-brand-copy">
                    <strong>DEMO Gestão</strong>
                    <small><?= $userRole === 'ADMIN' ? 'admin' : 'cliente' ?></small>
                </span>
            </a>

            <nav class="side-links">
                <?php foreach ($menu as $item): ?>
                    <a href="<?= $item['href'] ?>" class="<?= $activeTab === $item['key'] ? 'active' : '' ?>">
                        <i class="<?= $item['icon'] ?>"></i>
                        <span><?= htmlspecialchars($item['label']) ?></span>
                        <?php if (!empty($item['badge'])): ?>
                            <small style="margin-left:auto;background:#111827;color:#fff;border-radius:999px;padding:2px 8px;font-size:11px;"><?= (int) $item['badge'] ?></small>
                        <?php endif; ?>
                    </a>
                <?php endforeach; ?>
            </nav>

            <div class="side-footer">
                <div class="side-user-mini">
                    <span class="avatar-dot"><?= htmlspecialchars($initial) ?></span>
                    <div>
                        <strong><?= htmlspecialchars($userName) ?></strong>
                        <small><?= htmlspecialchars($userEmail) ?></small>
                    </div>
                </div>
                <a class="logout-side" href="<?= URL_BASE ?>login/logout">
                    <i class="fa-solid fa-arrow-right-from-bracket"></i>
                    <span>Sair</span>
                </a>
            </div>
        </aside>

        <div class="app-main">
            <header class="chrome-bar">
                <div class="chrome-copy">
                    <span class="section-kicker"><?= $userRole === 'ADMIN' ? 'Administração' : 'Área do cliente' ?></span>
                    <strong><?= htmlspecialchars($pageTitle ?? 'DEMO Gestão') ?></strong>
                </div>

                <div class="chrome-actions">
                    <div class="user-chip">
                        <span class="avatar-dot"><?= htmlspecialchars($initial) ?></span>
                        <div>
                            <strong><?= htmlspecialchars($userName) ?></strong>
                            <small><?= htmlspecialchars($userEmail) ?></small>
                        </div>
                    </div>
                    <a class="logout-btn" href="<?= URL_BASE ?>login/logout" title="Sair">
                        <i class="fa-solid fa-arrow-right-from-bracket"></i>
                    </a>
                </div>
            </header>

            <main class="app-content">
                <?php if (!empty($flash)): ?>
                    <div class="flash <?= htmlspecialchars($flash['type']) ?>"><?= htmlspecialchars($flash['message']) ?></div>
                <?php endif; ?>
                <?php require $content; ?>
            </main>
        </div>

        <nav class="dock-nav">
            <?php foreach (array_slice($menu, 0, 6) as $item): ?>
                <a href="<?= $item['href'] ?>" class="<?= $activeTab === $item['key'] ? 'active' : '' ?>">
                    <i class="<?= $item['icon'] ?>"></i>
                    <span><?= htmlspecialchars($item['label']) ?></span>
                </a>
            <?php endforeach; ?>
        </nav>
    </div>
</body>

</html>