<?php
// DB 연결 정보
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


// 게시글 번호 확인
$post_id = $_GET['id'] ?? '';

if (!ctype_digit($post_id)) {
    die("잘못된 게시글 번호입니다.");
}


// 게시글 가져오기
$stmt = $pdo->prepare(
    "SELECT id, title, content, created_at
     FROM posts
     WHERE id = :id"
);

$stmt->execute([
    ':id' => $post_id
]);

$post = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$post) {
    die("게시글을 찾을 수 없습니다.");
}


// 댓글 가져오기
$stmt = $pdo->prepare(
    "SELECT id, author, content, created_at
     FROM comments
     WHERE post_id = :post_id
     ORDER BY id ASC"
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
    <title>
        <?= htmlspecialchars($post['title']) ?>
    </title>
</head>

<body>

    <h1><?= htmlspecialchars($post['title']) ?></h1>

    <div>
        <?= nl2br(htmlspecialchars($post['content'])) ?>
    </div>

    <hr>

    <h2>댓글</h2>

    <?php if (count($comments) === 0): ?>

        <p>아직 댓글이 없습니다.</p>

    <?php else: ?>

        <?php foreach ($comments as $comment): ?>

            <div>
                <strong>
                    <?= htmlspecialchars($comment['author']) ?>
                </strong>

                <p>
                    <?php
                    // 의도적으로 HTML escape를 하지 않음
                    // Stored XSS 발생 지점
                    echo $comment['content'];
                    ?>
                </p>

                <small>
                    <?= htmlspecialchars($comment['created_at']) ?>
                </small>
            </div>

            <hr>

        <?php endforeach; ?>

    <?php endif; ?>


    <h2>댓글 작성</h2>

    <form method="POST" action="comment.php">

        <input
            type="hidden"
            name="post_id"
            value="<?= htmlspecialchars($post['id']) ?>"
        >

        <div>
            <label for="author">작성자</label>
            <input
                type="text"
                id="author"
                name="author"
                required
            >
        </div>

        <br>

        <div>
            <label for="content">댓글</label>
            <br>
            <textarea
                id="content"
                name="content"
                rows="5"
                cols="50"
                required
            ></textarea>
        </div>

        <br>

        <button type="submit">댓글 작성</button>

    </form>


    <br>

    <a href="admin-check.php?id=<?= htmlspecialchars($post['id']) ?>">
        🔑 관리자 확인
    </a>

</body>
</html>