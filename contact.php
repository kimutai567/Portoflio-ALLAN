<?php

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.html#contact');
    exit;
}

$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$message = trim($_POST['message'] ?? '');

if ($name === '' || $message === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    $title = 'Please check your message';
    $text = 'Enter your name, a valid email address, and a message.';
} else {
    try {
        require __DIR__ . '/database.php';
        $statement = $database->prepare(
            'INSERT INTO contact_messages (name, email, message) VALUES (:name, :email, :message)'
        );
        $statement->execute([
            ':name' => $name,
            ':email' => $email,
            ':message' => $message,
        ]);
        $title = 'Message sent';
        $text = 'Thanks. Your message has been saved successfully.';
    } catch (PDOException $exception) {
        http_response_code(500);
        $title = 'Message could not be saved';
        $text = 'The server could not connect to the database. Check your MySQL setup.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($title, ENT_QUOTES, 'UTF-8'); ?></title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <main class="form-response">
        <p class="eyebrow">Contact</p>
        <h1><?php echo htmlspecialchars($title, ENT_QUOTES, 'UTF-8'); ?></h1>
        <p><?php echo htmlspecialchars($text, ENT_QUOTES, 'UTF-8'); ?></p>
        <a class="primary-link" href="index.html#contact">Back to portfolio <span aria-hidden="true">↗</span></a>
    </main>
</body>
</html>
