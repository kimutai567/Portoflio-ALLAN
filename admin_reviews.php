<?php
declare(strict_types=1);

session_start();
require __DIR__ . '/database.php';

$adminPassword = getenv('PORTFOLIO_ADMIN_PASSWORD') ?: 'change-this-password';

if (isset($_POST['logout'])) {
    session_destroy();
    header('Location: admin_reviews.php');
    exit;
}

if (!($_SESSION['reviews_admin'] ?? false)) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && hash_equals($adminPassword, (string) ($_POST['password'] ?? ''))) {
        $_SESSION['reviews_admin'] = true;
        header('Location: admin_reviews.php');
        exit;
    }
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Review Admin</title><link rel="stylesheet" href="style.css"></head>
    <body>
        <main class="admin-page">
            <p class="eyebrow">Review administration</p>
            <h1>Sign in to manage reviews.</h1>
            <form class="review-form" method="post">
                <label for="password">Admin password</label>
                <input id="password" name="password" type="password" required>
                <button type="submit">Sign in</button>
            </form>
        </main>
    </body>
    </html>
    <?php
    exit;
}

if (isset($_POST['review_id'], $_POST['status']) && in_array($_POST['status'], ['approved', 'rejected'], true)) {
    $statement = $database->prepare('UPDATE reviews SET status = :status WHERE id = :id');
    $statement->execute([':status' => $_POST['status'], ':id' => (int) $_POST['review_id']]);
}

$reviews = $database->query('SELECT id, name, rating, message, status, created_at FROM reviews ORDER BY created_at DESC')->fetchAll();
$messages = $database->query('SELECT name, email, message, created_at FROM contact_messages ORDER BY created_at DESC')->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Manage Reviews</title><link rel="stylesheet" href="style.css"></head>
<body>
    <main class="admin-page">
        <div class="admin-heading"><div><p class="eyebrow">Review administration</p><h1>Manage reviews.</h1></div><form method="post"><button name="logout" type="submit">Log out</button></form></div>
        <div class="admin-reviews">
            <?php foreach ($reviews as $review): ?>
                <article class="admin-review">
                    <div><h2><?php echo htmlspecialchars($review['name'], ENT_QUOTES, 'UTF-8'); ?></h2><span class="review-stars"><?php echo str_repeat('★', (int) $review['rating']); ?></span></div>
                    <p><?php echo htmlspecialchars($review['message'], ENT_QUOTES, 'UTF-8'); ?></p>
                    <p class="review-status">Status: <?php echo htmlspecialchars($review['status'], ENT_QUOTES, 'UTF-8'); ?></p>
                    <?php if ($review['status'] === 'pending'): ?>
                        <form method="post"><input type="hidden" name="review_id" value="<?php echo (int) $review['id']; ?>"><button name="status" value="approved" type="submit">Approve</button><button name="status" value="rejected" type="submit">Reject</button></form>
                    <?php endif; ?>
                </article>
            <?php endforeach; ?>
        </div>
        <section class="admin-messages">
            <p class="eyebrow">Contact messages</p>
            <h2>Messages from visitors.</h2>
            <div class="admin-reviews">
                <?php foreach ($messages as $message): ?>
                    <article class="admin-review">
                        <h2><?php echo htmlspecialchars($message['name'], ENT_QUOTES, 'UTF-8'); ?></h2>
                        <p><?php echo htmlspecialchars($message['email'], ENT_QUOTES, 'UTF-8'); ?></p>
                        <p><?php echo nl2br(htmlspecialchars($message['message'], ENT_QUOTES, 'UTF-8')); ?></p>
                        <p class="review-status"><?php echo htmlspecialchars($message['created_at'], ENT_QUOTES, 'UTF-8'); ?></p>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>
    </main>
</body>
</html>
