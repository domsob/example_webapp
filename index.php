<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

$pdo   = get_db();
$posts = $pdo->query("SELECT id, title, content, created_at FROM posts ORDER BY created_at DESC")
             ->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Blog</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>

<header class="site-header">
    <h1><a href="index.php">My Blog</a></h1>
    <nav>
        <a href="index.php">Home</a>
        <a href="admin.php">Admin</a>
    </nav>
</header>

<main class="container">
    <?php if (empty($posts)): ?>
        <div class="empty-state">
            <p>No posts yet. <a href="admin.php">Add the first one!</a></p>
        </div>
    <?php else: ?>
        <ul class="post-list">
            <?php foreach ($posts as $post): ?>
            <li class="post-card">
                <h2><a href="post.php?id=<?= h((string)$post['id']) ?>"><?= h($post['title']) ?></a></h2>
                <p class="post-meta"><?= h(format_date($post['created_at'])) ?></p>
                <p class="post-excerpt"><?= h(mb_strimwidth($post['content'], 0, 200, '…')) ?></p>
                <a class="read-more" href="post.php?id=<?= h((string)$post['id']) ?>">Read more →</a>
            </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</main>

<footer class="site-footer">
    &copy; <?= date('Y') ?> My Blog
</footer>

</body>
</html>
