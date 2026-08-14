<?php

// ============================================================
// DB 연결
// ============================================================

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

    $pdo->setAttribute(
        PDO::ATTR_ERRMODE,
        PDO::ERRMODE_EXCEPTION
    );

} catch (PDOException $e) {

    die("DB 연결 실패: " . $e->getMessage());

}


// ============================================================
// 게시글 번호 확인
// ============================================================

$post_id = $_GET['id'] ?? '';

if (!ctype_digit($post_id)) {

    die("잘못된 게시글 번호입니다.");

}


// ============================================================
// 게시글 가져오기
// ============================================================

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


// ============================================================
// 댓글 가져오기
// ============================================================

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

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        <?= htmlspecialchars(
            $post['title'],
            ENT_QUOTES,
            'UTF-8'
        ) ?>
    </title>


    <style>

        /* =====================================================
           전체
        ===================================================== */

        * {
            box-sizing: border-box;
        }


        body {

            margin: 0;

            padding: 40px 20px;

            font-family:
                -apple-system,
                BlinkMacSystemFont,
                "Segoe UI",
                Roboto,
                "Noto Sans KR",
                sans-serif;

            background: #f5f7fb;

            color: #333;

        }


        .container {

            width: 100%;

            max-width: 850px;

            margin: 0 auto;

        }


        /* =====================================================
           상단
        ===================================================== */

        .top-bar {

            margin-bottom: 20px;

        }


        .back-link {

            color: #667085;

            text-decoration: none;

            font-size: 14px;

        }


        .back-link:hover {

            color: #4f46e5;

        }


        /* =====================================================
           게시글
        ===================================================== */

        .post-card {

            background: white;

            border-radius: 16px;

            padding: 32px;

            box-shadow:
                0 4px 20px rgba(0, 0, 0, 0.06);

            margin-bottom: 30px;

        }


        .post-number {

            display: inline-block;

            padding: 5px 10px;

            background: #eef2ff;

            color: #4f46e5;

            border-radius: 20px;

            font-size: 13px;

            font-weight: 600;

            margin-bottom: 12px;

        }


        .post-title {

            margin: 0 0 20px;

            font-size: 28px;

            color: #222;

        }


        .post-content {

            min-height: 100px;

            line-height: 1.8;

            white-space: normal;

            color: #555;

            margin-bottom: 20px;

        }


        .post-date {

            color: #999;

            font-size: 13px;

        }


        /* =====================================================
           댓글 영역
        ===================================================== */

        .section-title {

            margin: 30px 0 15px;

            font-size: 20px;

            color: #222;

        }


        .comment-card {

            background: white;

            border-radius: 12px;

            padding: 20px;

            margin-bottom: 12px;

            box-shadow:
                0 2px 10px rgba(0, 0, 0, 0.04);

        }


        .comment-author {

            font-weight: 700;

            color: #4f46e5;

            margin-bottom: 8px;

        }


        .comment-content {

            color: #444;

            line-height: 1.6;

            margin-bottom: 10px;

        }


        .comment-date {

            color: #999;

            font-size: 12px;

        }


        .empty-comment {

            background: white;

            padding: 25px;

            border-radius: 12px;

            color: #888;

            text-align: center;

        }


        /* =====================================================
           댓글 작성
        ===================================================== */

        .write-card {

            background: white;

            border-radius: 16px;

            padding: 28px;

            margin-top: 30px;

            box-shadow:
                0 4px 20px rgba(0, 0, 0, 0.06);

        }


        .write-title {

            margin: 0 0 22px;

            font-size: 20px;

            color: #222;

        }


        .form-group {

            margin-bottom: 18px;

        }


        .form-group label {

            display: block;

            margin-bottom: 8px;

            font-size: 14px;

            font-weight: 600;

            color: #444;

        }


        input,
        textarea {

            width: 100%;

            padding: 12px 14px;

            border: 1px solid #d9dce3;

            border-radius: 8px;

            font-size: 14px;

            font-family: inherit;

            outline: none;

            transition: 0.2s;

        }


        input:focus,
        textarea:focus {

            border-color: #6366f1;

            box-shadow:
                0 0 0 3px rgba(99, 102, 241, 0.1);

        }


        textarea {

            resize: vertical;

            min-height: 120px;

        }


        /* 댓글 작성 버튼 */

        .submit-button {

            width: 100%;

            border: none;

            border-radius: 8px;

            padding: 13px;

            background: #4f46e5;

            color: white;

            font-size: 15px;

            font-weight: 600;

            cursor: pointer;

            transition: 0.2s;

        }


        .submit-button:hover {

            background: #4338ca;

        }


        /* =====================================================
           관리자 확인
        ===================================================== */

        .admin-check {

            margin-top: 18px;

            text-align: right;

        }


        .admin-check a {

            display: inline-block;

            padding: 11px 20px;

            background: #fff7df;

            color: #856404;

            border: 1px solid #f0d98c;

            border-radius: 8px;

            text-decoration: none;

            font-weight: 600;

            font-size: 14px;

            transition: 0.2s;

        }


        .admin-check a:hover {

            background: #ffedb3;

            transform: translateY(-1px);

        }


        /* =====================================================
           홈으로
        ===================================================== */

        .home-button {

            display: block;

            text-align: center;

            margin-top: 25px;

            color: #667085;

            text-decoration: none;

            font-size: 14px;

        }


        .home-button:hover {

            color: #4f46e5;

        }


        /* =====================================================
           모바일
        ===================================================== */

        @media (max-width: 600px) {

            body {

                padding: 20px 12px;

            }


            .post-card,
            .write-card {

                padding: 22px;

            }


            .post-title {

                font-size: 23px;

            }

        }

    </style>

</head>


<body>


<div class="container">


    <!-- =====================================================
         상단
    ====================================================== -->

    <div class="top-bar">

        <a
            href="index.php"
            class="back-link"
        >
            ← 게시판으로 돌아가기
        </a>

    </div>


    <!-- =====================================================
         게시글
    ====================================================== -->

    <div class="post-card">


        <div class="post-number">

            게시글 #

            <?= htmlspecialchars(
                $post['id'],
                ENT_QUOTES,
                'UTF-8'
            ) ?>

        </div>


        <h1 class="post-title">

            <?= htmlspecialchars(
                $post['title'],
                ENT_QUOTES,
                'UTF-8'
            ) ?>

        </h1>


        <div class="post-content">

            <?= nl2br(
                htmlspecialchars(
                    $post['content'],
                    ENT_QUOTES,
                    'UTF-8'
                )
            ) ?>

        </div>


        <div class="post-date">

            작성일:

            <?= htmlspecialchars(
                $post['created_at'],
                ENT_QUOTES,
                'UTF-8'
            ) ?>

        </div>


    </div>


    <!-- =====================================================
         댓글
    ====================================================== -->

    <h2 class="section-title">
        댓글
    </h2>


    <?php if (count($comments) === 0): ?>


        <div class="empty-comment">

            아직 댓글이 없습니다.

        </div>


    <?php else: ?>


        <?php foreach ($comments as $comment): ?>


            <div class="comment-card">


                <!-- 작성자 -->

                <div class="comment-author">

                    <?= htmlspecialchars(
                        $comment['author'],
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>

                </div>


                <!--
                    중요:
                    댓글 content는 의도적으로
                    htmlspecialchars()를 사용하지 않음.

                    Stored XSS 발생 지점
                -->

                <div class="comment-content">

                    <?= $comment['content'] ?>

                </div>


                <!-- 작성일 -->

                <div class="comment-date">

                    <?= htmlspecialchars(
                        $comment['created_at'],
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>

                </div>


            </div>


        <?php endforeach; ?>


    <?php endif; ?>


    <!-- =====================================================
         댓글 작성
    ====================================================== -->

    <div class="write-card">


        <h2 class="write-title">
            댓글 작성
        </h2>


        <form
            method="POST"
            action="comment.php"
        >


            <!-- 게시글 번호 -->

            <input
                type="hidden"
                name="post_id"
                value="<?= htmlspecialchars(
                    $post['id'],
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>"
            >


            <!-- 작성자 -->

            <div class="form-group">

                <label for="author">
                    작성자
                </label>


                <input
                    type="text"
                    id="author"
                    name="author"
                    placeholder="작성자 이름을 입력하세요"
                    required
                >

            </div>


            <!-- 댓글 -->

            <div class="form-group">

                <label for="content">
                    댓글
                </label>


                <textarea
                    id="content"
                    name="content"
                    placeholder="댓글 내용을 입력하세요"
                    required
                ></textarea>

            </div>


            <!-- 댓글 작성 버튼 -->

            <button
                type="submit"
                class="submit-button"
            >
                댓글 작성
            </button>


        </form>


    </div>


    <!-- =====================================================
         관리자 확인
    ====================================================== -->

    <div class="admin-check">


        <a
            href="admin-check.php?id=<?= htmlspecialchars(
                $post['id'],
                ENT_QUOTES,
                'UTF-8'
            ) ?>"
        >
            🔑 관리자 확인
        </a>


    </div>


    <!-- =====================================================
         게시판 목록
    ====================================================== -->

    <a
        href="index.php"
        class="home-button"
    >
        게시판 목록으로 돌아가기
    </a>


</div>


</body>

</html>
