<?php

function sendContactConfirmationEmail(string $recipientEmail, string $recipientName): bool
{
    $fromEmail = getenv('PORTFOLIO_CONTACT_FROM_EMAIL') ?: 'noreply@localhost';
    $siteName = getenv('PORTFOLIO_SITE_NAME') ?: 'Portfolio Website';
    $smtpHost = getenv('PORTFOLIO_SMTP_HOST') ?: '';
    $smtpPort = (int) (getenv('PORTFOLIO_SMTP_PORT') ?: 587);
    $smtpUsername = getenv('PORTFOLIO_SMTP_USERNAME') ?: '';
    $smtpPassword = getenv('PORTFOLIO_SMTP_PASSWORD') ?: '';
    $smtpSecure = getenv('PORTFOLIO_SMTP_SECURE') ?: 'tls';

    $subject = 'Your message has been received';
    $body = "Hello {$recipientName},\r\n\r\n"
        . "Thank you for contacting {$siteName}. We have received your message and will reply shortly.\r\n\r\n"
        . "Best regards,\r\n"
        . $siteName;

    $mailerClass = 'PHPMailer\\PHPMailer\\PHPMailer';
    if (!class_exists($mailerClass)) {
        error_log('PHPMailer is not installed. Install it with Composer or configure PHP mail().');
        return false;
    }

    try {
        $mail = new $mailerClass(true);

        if ($smtpHost !== '') {
            $mail->isSMTP();
            $mail->Host = $smtpHost;
            $mail->SMTPAuth = $smtpUsername !== '' || $smtpPassword !== '';
            $mail->Username = $smtpUsername;
            $mail->Password = $smtpPassword;
            $mail->SMTPSecure = $smtpSecure === '' ? false : $smtpSecure;
            $mail->Port = $smtpPort;
            $mail->SMTPDebug = 0;
        }

        $mail->CharSet = 'UTF-8';
        $mail->setFrom($fromEmail, $siteName);
        $mail->addAddress($recipientEmail, $recipientName);
        $mail->Subject = $subject;
        $mail->Body = $body;
        $mail->AltBody = strip_tags(str_replace(["\r\n", "\n"], "\n", $body));

        return $mail->send();
    } catch (\Throwable $exception) {
        error_log('Contact confirmation email failed: ' . $exception->getMessage());
        return false;
    }
}

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

        $confirmationEmailSent = sendContactConfirmationEmail($email, $name);

        $title = 'Message sent';
        if ($confirmationEmailSent) {
            $text = 'Thanks. Your message has been saved and a confirmation email has been sent.';
        } else {
            $text = 'Thanks. Your message has been saved successfully. Email notifications are not configured on this server.';
        }
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
