<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

// ── Admin credentials (change before deploying!) ──────────────────────────
define('ADMIN_USER', 'admin');
define('ADMIN_PASS', '$2y$12$2ptnKYGvkn1nWPgv9XcCHeVejpO5q4NO04QWxVKq47WyYAUSGt5qq'); // default: blogadmin – change in production!

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$error   = '';
$success = '';
$action  = $_GET['action'] ?? 'dashboard';

// ── Login ──────────────────────────────────────────────────────────────────
if ($action === 'login' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) {
        $error = 'Invalid request. Please try again.';
    } else {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        if ($username === ADMIN_USER && password_verify($password, ADMIN_PASS)) {
            session_regenerate_id(true);
            $_SESSION['admin_logged_in'] = true;
            header('Location: admin.php');
            exit;
        }
        $error = 'Invalid username or password.';
    }
}

// ── Logout ─────────────────────────────────────────────────────────────────
if ($action === 'logout') {
    session_destroy();
    header('Location: admin.php');
    exit;
}

// ── Redirect to login when not authenticated ───────────────────────────────
if (!is_admin_logged_in() && $action !== 'login') {
    header('Location: admin.php?action=login');
    exit;
}

$pdo = get_db();

// ── Create post ────────────────────────────────────────────────────────────
if ($action === 'new' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) {
        $error = 'Invalid request. Please try again.';
    } else {
        $title   = trim($_POST['title']   ?? '');
        $content = trim($_POST['content'] ?? '');
        if ($title === '' || $content === '') {
            $error = 'Title and content are required.';
        } else {
            $stmt = $pdo->prepare("INSERT INTO posts (title, content) VALUES (:title, :content)");
            $stmt->execute([':title' => $title, ':content' => $content]);
            $success = 'Post published successfully!';
            $action  = 'dashboard';
        }
    }
}

// ── Delete post ────────────────────────────────────────────────────────────
if ($action === 'delete' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) {
        $error = 'Invalid request. Please try again.';
    } else {
        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        if ($id) {
            $stmt = $pdo->prepare("DELETE FROM posts WHERE id = :id");
            $stmt->execute([':id' => $id]);
            $success = 'Post deleted.';
        }
        $action = 'dashboard';
    }
}

// ── Fetch posts for dashboard ──────────────────────────────────────────────
$posts = [];
if ($action === 'dashboard') {
    $posts = $pdo->query("SELECT id, title, created_at FROM posts ORDER BY created_at DESC")->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin – My Blog</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>

<header class="site-header">
    <h1><a href="index.php">My Blog</a></h1>
    <nav>
        <a href="index.php">View Site</a>
        <?php if (is_admin_logged_in()): ?>
            <a href="admin.php">Dashboard</a>
            <a href="admin.php?action=logout">Log out</a>
        <?php endif; ?>
    </nav>
</header>

<main class="container">

<?php if (!is_admin_logged_in()): ?>
    <!-- ── Login form ── -->
    <div class="admin-card" style="max-width:380px;margin:3rem auto;">
        <h2>Admin Login</h2>
        <?php if ($error): ?><div class="alert alert-error"><?= h($error) ?></div><?php endif; ?>
        <form method="POST" action="admin.php?action=login">
            <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required autofocus>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit" class="btn btn-primary">Log in</button>
        </form>
    </div>

<?php elseif ($action === 'new'): ?>
    <!-- ── New post form ── -->
    <div class="admin-card">
        <h2>New Post</h2>
        <?php if ($error): ?><div class="alert alert-error"><?= h($error) ?></div><?php endif; ?>
        <form method="POST" action="admin.php?action=new">
            <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
            <div class="form-group">
                <label for="title">Title</label>
                <input type="text" id="title" name="title"
                       value="<?= h($_POST['title'] ?? '') ?>" required autofocus>
            </div>
            <div class="form-group">
                <label for="content">Content</label>
                <textarea id="content" name="content" required><?= h($_POST['content'] ?? '') ?></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Publish</button>
            <a href="admin.php" class="btn btn-secondary" style="margin-left:.5rem;">Cancel</a>
        </form>
    </div>

<?php else: ?>
    <!-- ── Dashboard ── -->
    <?php if ($success): ?><div class="alert alert-success"><?= h($success) ?></div><?php endif; ?>
    <?php if ($error):   ?><div class="alert alert-error"><?= h($error) ?></div><?php endif; ?>

    <div class="admin-card">
        <h2>Posts
            <a href="admin.php?action=new" class="btn btn-primary"
               style="float:right;font-size:.85rem;padding:.4rem 1rem;">+ New Post</a>
        </h2>

        <?php if (empty($posts)): ?>
            <p style="color:#64748b;font-family:sans-serif;">No posts yet. <a href="admin.php?action=new">Create one!</a></p>
        <?php else: ?>
        <table class="post-table">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Date</th>
                    <th class="actions">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($posts as $post): ?>
                <tr>
                    <td><a href="post.php?id=<?= h((string)$post['id']) ?>"><?= h($post['title']) ?></a></td>
                    <td><?= h(format_date($post['created_at'])) ?></td>
                    <td class="actions">
                        <form method="POST" action="admin.php?action=delete"
                              onsubmit="return confirm('Delete this post?')">
                            <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
                            <input type="hidden" name="id" value="<?= h((string)$post['id']) ?>">
                            <button type="submit" class="btn btn-danger" style="font-size:.82rem;padding:.3rem .75rem;">Delete</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
<?php endif; ?>

</main>

<footer class="site-footer">
    &copy; <?= date('Y') ?> My Blog · <a href="index.php">View site</a>
</footer>

</body>
</html>
