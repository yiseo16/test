<?php

$host = getenv('DB_HOST') ?: 'xss-mysql';
$dbname = getenv('DB_NAME') ?: 'xss';
$username = getenv('DB_USER') ?: 'xss';
$password = getenv('DB_PASSWORD') ?: 'xsspassword';

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password
    );

    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch (PDOException $e) {
    die("DB 연결 실패: " . $e->getMessage());
}


// 게시글 번호
$post_id = $_GET['id'] ?? '';

if (!ctype_digit($post_id)) {
    die("잘못된 게시글 번호입니다.");
}


// 관리자 확인 버튼을 눌렀을 때 봇 실행
$botUrl = getenv('BOT_URL') ?: 'http://xss-bot:3000';

$context = stream_context_create([
    'http' => [
        'timeout' => 10
    ]
]);

$botResponse = @file_get_contents(
    $botUrl . '/check?post_id=' . urlencode($post_id),
    false,
    $context
);


// 댓글 조회
$stmt = $pdo->prepare(
    "SELECT author, content, created_at
     FROM comments
     WHERE post_id = :post_id
     ORDER BY created_at ASC"
);

$stmt->execute([
    ':post_id' => $post_id
]);

$comments = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <title>관리자 댓글 확인</title>
</head>

<body>

<h1>관리자 댓글 확인</h1>

<p>게시글 번호: <?= htmlspecialchars($post_id, ENT_QUOTES, 'UTF-8') ?></p>

<?php if ($botResponse !== false): ?>
    <p>관리자가 게시글을 확인했습니다.</p>
<?php else: ?>
    <p>관리자 봇 실행에 실패했습니다.</p>
<?php endif; ?>

<hr>

<?php if (empty($comments)): ?>

    <p>댓글이 없습니다.</p>

<?php else: ?>

    <?php foreach ($comments as $comment): ?>

        <div>
            <p>
                작성자:
                <?= htmlspecialchars($comment['author'], ENT_QUOTES, 'UTF-8') ?>
            </p>

            <p>
                댓글:
                <?= $comment['content'] ?>
            </p>

            <p>
                작성일:
                <?= htmlspecialchars($comment['created_at'], ENT_QUOTES, 'UTF-8') ?>
            </p>
        </div>

        <hr>

    <?php endforeach; ?>

<?php endif; ?>

</body>
</html>