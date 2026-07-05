<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

$id   = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$post = null;

if ($id !== false && $id !== null) {
    $pdo  = get_db();
    $stmt = $pdo->prepare("SELECT id, title, content, created_at FROM posts WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $post = $stmt->fetch();
}

if (!$post) {
    http_response_code(404);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $post ? h($post['title']) . ' – My Blog' : '404 Not Found – My Blog' ?></title>
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
    <a class="back-link" href="index.php">← Back to all posts</a>

    <?php if ($post): ?>
    <article class="post-full">
        <h1><?= h($post['title']) ?></h1>
        <p class="post-meta"><?= h(format_date($post['created_at'])) ?></p>
        <div class="post-body"><?= h($post['content']) ?></div>
    </article>
    <?php else: ?>
    <div class="empty-state">
        <p>Post not found.</p>
    </div>
    <?php endif; ?>
</main>

<footer class="site-footer">
    &copy; <?= date('Y') ?> My Blog
</footer>

</body>
</html>
