<?php
// DB 연결 정보
$host = getenv('DB_HOST') ?: 'mysql';
$dbname = getenv('DB_NAME') ?: 'xss';
$username = getenv('DB_USER') ?: 'xss';
$password = getenv('DB_PASSWORD') ?: 'xsspassword';

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password
    );

    // 오류가 발생하면 예외를 발생시킴
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch (PDOException $e) {
    die("DB 연결 실패: " . $e->getMessage());
}


// 게시글 작성 처리
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $title = $_POST['title'] ?? '';
    $content = $_POST['content'] ?? '';

    // 제목과 내용이 비어 있는지 확인
    if (trim($title) === '' || trim($content) === '') {
        die("제목과 내용을 입력해주세요.");
    }

    // 게시글 저장
    $stmt = $pdo->prepare(
        "INSERT INTO posts (title, content)
         VALUES (:title, :content)"
    );

    $stmt->execute([
        ':title' => $title,
        ':content' => $content
    ]);

    // 방금 작성한 게시글 번호
    $post_id = $pdo->lastInsertId();

    // 게시글 페이지로 이동
    header("Location: post.php?id=" . $post_id);
    exit;
}
?>

<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <title>게시글 작성</title>
</head>

<body>

    <h1>게시글 작성</h1>

    <form method="POST" action="write.php">

        <div>
            <label for="title">제목</label>
            <input
                type="text"
                id="title"
                name="title"
                required
            >
        </div>

        <br>

        <div>
            <label for="content">내용</label>
            <br>
            <textarea
                id="content"
                name="content"
                rows="10"
                cols="50"
                required
            ></textarea>
        </div>

        <br>

        <button type="submit">작성</button>

    </form>

</body>
</html>