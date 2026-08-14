<?php
$host = getenv('DB_HOST') ?: 'xss-mysql';
$dbname = getenv('DB_NAME') ?: 'xss_db';
$username = getenv('DB_USER') ?: 'xss_user';
$password = getenv('DB_PASSWORD') ?: 'xsspassword';

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password
    );

    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 게시글 목록 조회
    $stmt = $pdo->query(
        "SELECT id, title, content, created_at
         FROM posts
         ORDER BY id DESC"
    );

    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("DB 연결 실패: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>XSS Challenge</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            background: #f5f7fb;
            color: #1f2937;
            font-family:
                -apple-system,
                BlinkMacSystemFont,
                "Segoe UI",
                "Noto Sans KR",
                sans-serif;
        }

        /* 전체 영역 */
        .container {
            width: 90%;
            max-width: 1000px;
            margin: 0 auto;
        }

        /* 상단 헤더 */
        .header {
            background: #111827;
            color: white;
            padding: 28px 0;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
        }

        .header-inner {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .logo {
            font-size: 22px;
            font-weight: 700;
            letter-spacing: -0.5px;
        }

        .logo span {
            color: #60a5fa;
        }

        .challenge-badge {
            font-size: 13px;
            color: #d1d5db;
            background: #1f2937;
            border: 1px solid #374151;
            padding: 7px 12px;
            border-radius: 20px;
        }

        /* 메인 */
        main {
            padding: 45px 0 70px;
        }

        .page-title {
            margin-bottom: 28px;
        }

        .page-title h1 {
            margin: 0 0 8px;
            font-size: 30px;
            letter-spacing: -1px;
        }

        .page-title p {
            margin: 0;
            color: #6b7280;
            font-size: 15px;
        }

        /* 게시글 작성 버튼 */
        .toolbar {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 18px;
        }

        .write-button {
            display: inline-block;
            padding: 11px 18px;
            background: #2563eb;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            transition: 0.2s;
        }

        .write-button:hover {
            background: #1d4ed8;
            transform: translateY(-1px);
        }

        /* 게시글 카드 */
        .post-card {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 24px;
            margin-bottom: 14px;
            transition: 0.2s;
        }

        .post-card:hover {
            border-color: #bfdbfe;
            box-shadow: 0 5px 18px rgba(0, 0, 0, 0.06);
            transform: translateY(-1px);
        }

        .post-number {
            font-size: 12px;
            color: #6b7280;
            margin-bottom: 7px;
        }

        .post-title {
            margin: 0 0 10px;
            font-size: 19px;
        }

        .post-title a {
            color: #111827;
            text-decoration: none;
        }

        .post-title a:hover {
            color: #2563eb;
        }

        .post-content {
            color: #4b5563;
            font-size: 14px;
            line-height: 1.6;
            margin-bottom: 16px;

            /* 너무 긴 내용 제한 */
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .post-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-top: 1px solid #f0f0f0;
            padding-top: 14px;
        }

        .date {
            color: #9ca3af;
            font-size: 12px;
        }

        .detail-button {
            display: inline-block;
            padding: 8px 13px;
            border: 1px solid #d1d5db;
            border-radius: 7px;
            color: #374151;
            text-decoration: none;
            font-size: 13px;
            font-weight: 500;
            transition: 0.2s;
        }

        .detail-button:hover {
            background: #f3f4f6;
            border-color: #9ca3af;
        }

        /* 게시글 없음 */
        .empty {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 60px 20px;
            text-align: center;
            color: #9ca3af;
        }

        /* 하단 */
        footer {
            border-top: 1px solid #e5e7eb;
            padding: 25px 0;
            text-align: center;
            color: #9ca3af;
            font-size: 12px;
        }

        /* 모바일 */
        @media (max-width: 600px) {

            .container {
                width: 92%;
            }

            .header-inner {
                flex-direction: column;
                align-items: flex-start;
                gap: 12px;
            }

            .page-title h1 {
                font-size: 25px;
            }

            .post-card {
                padding: 20px;
            }

            .post-footer {
                flex-direction: column;
                align-items: flex-start;
                gap: 12px;
            }

            .detail-button {
                width: 100%;
                text-align: center;
            }
        }
    </style>
</head>

<body>

<!-- 헤더 -->
<header class="header">
    <div class="container header-inner">

        <div class="logo">
            XSS <span>Challenge</span>
        </div>

        <div class="challenge-badge">
            Web Security Lab
        </div>

    </div>
</header>


<!-- 메인 -->
<main>

    <div class="container">

        <div class="page-title">
            <h1>게시판</h1>
            <p>
                게시글을 확인하고 댓글을 작성할 수 있습니다.
            </p>
        </div>


        <!-- 게시글 작성 -->
        <div class="toolbar">
            <a href="write.php" class="write-button">
                + 게시글 작성
            </a>
        </div>


        <!-- 게시글 목록 -->
        <?php if (empty($posts)): ?>

            <div class="empty">
                등록된 게시글이 없습니다.
            </div>

        <?php else: ?>

            <?php foreach ($posts as $post): ?>

                <article class="post-card">

                    <div class="post-number">
                        POST #<?= htmlspecialchars($post['id'], ENT_QUOTES, 'UTF-8') ?>
                    </div>

                    <h2 class="post-title">
                        <a href="post.php?id=<?= urlencode($post['id']) ?>">
                            <?= htmlspecialchars($post['title'], ENT_QUOTES, 'UTF-8') ?>
                        </a>
                    </h2>

                    <div class="post-content">
                        <?= htmlspecialchars($post['content'], ENT_QUOTES, 'UTF-8') ?>
                    </div>

                    <div class="post-footer">

                        <span class="date">
                            <?= htmlspecialchars($post['created_at'], ENT_QUOTES, 'UTF-8') ?>
                        </span>

                        <a
                            href="post.php?id=<?= urlencode($post['id']) ?>"
                            class="detail-button"
                        >
                            게시글 확인 · 댓글 작성 →
                        </a>

                    </div>

                </article>

            <?php endforeach; ?>

        <?php endif; ?>

    </div>

</main>


<!-- 푸터 -->
<footer>
    <div class="container">
        XSS Challenge · Web Security Practice
    </div>
</footer>

</body>
</html>
