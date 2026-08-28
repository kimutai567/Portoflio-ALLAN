<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

try {
    require __DIR__ . '/database.php';

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $statement = $database->query(
            "SELECT name, rating, message, created_at FROM reviews WHERE status = 'approved' ORDER BY created_at DESC"
        );
        echo json_encode(['reviews' => $statement->fetchAll()]);
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['message' => 'Method not allowed.']);
        exit;
    }

    $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
    $name = trim((string) ($input['name'] ?? ''));
    $message = trim((string) ($input['message'] ?? ''));
    $rating = filter_var($input['rating'] ?? null, FILTER_VALIDATE_INT);

    if ($name === '' || strlen($name) > 100 || $message === '' || strlen($message) > 2000 || $rating === false || $rating < 1 || $rating > 5) {
        http_response_code(400);
        echo json_encode(['message' => 'Enter a name, a review, and a rating from 1 to 5.']);
        exit;
    }

    $statement = $database->prepare(
        'INSERT INTO reviews (name, rating, message) VALUES (:name, :rating, :message)'
    );
    $statement->execute([
        ':name' => $name,
        ':rating' => $rating,
        ':message' => $message,
    ]);

    http_response_code(201);
    echo json_encode(['message' => 'Thank you for sharing your review.']);
} catch (Throwable $exception) {
    http_response_code(500);
    echo json_encode(['message' => 'The review service is unavailable. Check the database setup.']);
}
