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


$post_id = $_POST['post_id'] ?? '';
$author = $_POST['author'] ?? '';
$content = $_POST['content'] ?? '';


// 게시글 번호 확인
if (!ctype_digit($post_id)) {
    die("잘못된 게시글 번호입니다.");
}


// 빈 값 확인
if (trim($author) === '' || trim($content) === '') {
    die("작성자와 댓글 내용을 입력해주세요.");
}


// 댓글 저장
$stmt = $pdo->prepare(
    "INSERT INTO comments (post_id, author, content)
     VALUES (:post_id, :author, :content)"
);

$stmt->execute([
    ':post_id' => $post_id,
    ':author' => $author,
    ':content' => $content
]);


// 작성한 게시글로 돌아가기
header("Location: post.php?id=" . $post_id);
exit;