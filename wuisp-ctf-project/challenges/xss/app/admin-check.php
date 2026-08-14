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

    $pdo->setAttribute(
        PDO::ATTR_ERRMODE,
        PDO::ERRMODE_EXCEPTION
    );

} catch (PDOException $e) {

    die("DB 연결 실패: " . $e->getMessage());

}


// 게시글 번호
$post_id = $_GET['id'] ?? '';

if (!ctype_digit($post_id)) {
    die("잘못된 게시글 번호입니다.");
}


// 관리자 봇 실행

$botUrl = getenv('BOT_URL') ?: 'http://xss-bot:3000';

$context = stream_context_create([
    'http' => [
        'timeout' => 15
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

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>관리자 확인 | XSS Challenge</title>


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

        .container {
            width: 90%;

            max-width: 1000px;

            margin: 0 auto;
        }


        /* =========================
           Header
        ========================= */

        .header {
            background: #111827;

            color: white;

            padding: 28px 0;

            box-shadow:
                0 2px 10px rgba(0, 0, 0, 0.08);
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


        /* =========================
           Main
        ========================= */

        main {
            padding: 45px 0 70px;
        }

        .page-title {
            margin-bottom: 25px;
        }

        .page-title h1 {
            margin: 0 0 8px;

            font-size: 28px;

            letter-spacing: -0.8px;
        }

        .page-title p {
            margin: 0;

            color: #6b7280;

            font-size: 14px;
        }


        /* =========================
           Status
        ========================= */

        .status {
            padding: 18px 20px;

            border-radius: 10px;

            margin-bottom: 25px;

            font-size: 14px;
        }

        .status.success {
            background: #ecfdf5;

            border: 1px solid #a7f3d0;

            color: #047857;
        }

        .status.fail {
            background: #fef2f2;

            border: 1px solid #fecaca;

            color: #b91c1c;
        }


        /* =========================
           Comments
        ========================= */

        .section-title {
            font-size: 19px;

            margin: 30px 0 15px;
        }

        .comment-count {
            color: #2563eb;

            font-size: 14px;

            margin-left: 5px;
        }

        .comment-card {
            background: white;

            border: 1px solid #e5e7eb;

            border-radius: 10px;

            padding: 20px;

            margin-bottom: 10px;
        }

        .comment-header {
            display: flex;

            justify-content: space-between;

            margin-bottom: 12px;
        }

        .comment-author {
            font-weight: 600;

            font-size: 14px;

            color: #374151;
        }

        .comment-date {
            font-size: 11px;

            color: #9ca3af;
        }

        .comment-content {
            color: #4b5563;

            font-size: 14px;

            line-height: 1.6;

            white-space: pre-wrap;
        }

        .empty {
            background: white;

            border: 1px solid #e5e7eb;

            border-radius: 10px;

            padding: 40px;

            text-align: center;

            color: #9ca3af;

            font-size: 14px;
        }


        /* =========================
           Back Button
        ========================= */

        .bottom-area {
            margin-top: 30px;
        }

        .back-button {
            display: inline-block;

            padding: 10px 16px;

            background: #f3f4f6;

            color: #4b5563;

            border-radius: 8px;

            text-decoration: none;

            font-size: 13px;

            font-weight: 600;
        }

        .back-button:hover {
            background: #e5e7eb;
        }


        /* =========================
           Footer
        ========================= */

        footer {
            border-top: 1px solid #e5e7eb;

            padding: 25px 0;

            text-align: center;

            color: #9ca3af;

            font-size: 12px;
        }


        /* =========================
           Mobile
        ========================= */

        @media (max-width: 600px) {

            .container {
                width: 92%;
            }

            .header-inner {
                flex-direction: column;

                align-items: flex-start;

                gap: 12px;
            }

            .comment-header {
                flex-direction: column;

                gap: 5px;
            }

        }

    </style>

</head>


<body>


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


<main>

    <div class="container">


        <div class="page-title">

            <h1>관리자 댓글 확인</h1>

            <p>
                관리자가 게시글의 댓글을 확인합니다.
            </p>

        </div>


        <!-- 게시글 번호 -->

        <div class="status success">

            게시글 번호:
            <strong>
                <?= htmlspecialchars(
                    $post_id,
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>
            </strong>

        </div>


        <!-- 봇 실행 결과 -->

        <?php if ($botResponse !== false): ?>

            <div class="status success">

                ✓ 관리자가 게시글을 확인했습니다.

            </div>

        <?php else: ?>

            <div class="status fail">

                ✕ 관리자 봇 실행에 실패했습니다.

            </div>

        <?php endif; ?>


        <!-- 댓글 목록 -->

        <h2 class="section-title">

            댓글

            <span class="comment-count">
                <?= count($comments) ?>
            </span>

        </h2>


        <?php if (empty($comments)): ?>

            <div class="empty">
                등록된 댓글이 없습니다.
            </div>

        <?php else: ?>

            <?php foreach ($comments as $comment): ?>

                <div class="comment-card">

                    <div class="comment-header">

                        <span class="comment-author">

                            작성자:
                            <?= htmlspecialchars(
                                $comment['author'],
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>

                        </span>

                        <span class="comment-date">

                            <?= htmlspecialchars(
                                $comment['created_at'],
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>

                        </span>

                    </div>


                    <div class="comment-content">

                        <?php
                        /*
                         * 의도적인 Stored XSS 취약점
                         */
                        echo $comment['content'];
                        ?>

                    </div>

                </div>

            <?php endforeach; ?>

        <?php endif; ?>


        <div class="bottom-area">

            <a
                href="post.php?id=<?= htmlspecialchars(
                    $post_id,
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>"
                class="back-button"
            >
                ← 게시글로 돌아가기
            </a>

        </div>

    </div>

</main>


<footer>

    <div class="container">
        XSS Challenge · Web Security Practice
    </div>

</footer>


</body>

</html>
