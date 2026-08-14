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

} catch (PDOException $e) {
    die("DB 연결 실패: " . $e->getMessage());
}


/*
 * 게시글 작성
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');

    if ($title === '' || $content === '') {
        die('제목과 내용을 모두 입력해주세요.');
    }

    $stmt = $pdo->prepare(
        "INSERT INTO posts (title, content)
         VALUES (:title, :content)"
    );

    $stmt->execute([
        ':title' => $title,
        ':content' => $content
    ]);

    // 작성한 게시글 번호
    $post_id = $pdo->lastInsertId();

    // 게시글 페이지로 이동
    header("Location: post.php?id=" . urlencode($post_id));
    exit;
}

?>

<!DOCTYPE html>
<html lang="ko">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>게시글 작성 | XSS Challenge</title>

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

        .container {
            width: 90%;
            max-width: 1000px;
            margin: 0 auto;
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

            font-size: 30px;

            letter-spacing: -1px;
        }

        .page-title p {
            margin: 0;

            color: #6b7280;

            font-size: 15px;
        }


        /* =========================
           Form Card
        ========================= */

        .form-card {
            background: white;

            border: 1px solid #e5e7eb;

            border-radius: 12px;

            padding: 30px;

            box-shadow:
                0 4px 15px rgba(0, 0, 0, 0.04);
        }


        /* =========================
           Form
        ========================= */

        .form-group {
            margin-bottom: 24px;
        }

        .form-group label {
            display: block;

            margin-bottom: 8px;

            font-size: 14px;

            font-weight: 600;

            color: #374151;
        }

        .required {
            color: #2563eb;
        }

        input[type="text"],
        textarea {

            width: 100%;

            border: 1px solid #d1d5db;

            border-radius: 8px;

            padding: 12px 14px;

            font-family: inherit;

            font-size: 14px;

            color: #1f2937;

            background: #fff;

            outline: none;

            transition: 0.2s;
        }

        input[type="text"]:focus,
        textarea:focus {

            border-color: #2563eb;

            box-shadow:
                0 0 0 3px rgba(37, 99, 235, 0.10);
        }

        input[type="text"]::placeholder,
        textarea::placeholder {

            color: #9ca3af;
        }

        textarea {

            min-height: 260px;

            resize: vertical;

            line-height: 1.6;
        }


        /* =========================
           Buttons
        ========================= */

        .button-area {

            display: flex;

            justify-content: flex-end;

            gap: 10px;

            padding-top: 5px;
        }

        .button {

            display: inline-block;

            padding: 11px 18px;

            border-radius: 8px;

            font-size: 14px;

            font-weight: 600;

            text-decoration: none;

            cursor: pointer;

            transition: 0.2s;

            border: none;
        }

        .cancel-button {

            background: #f3f4f6;

            color: #4b5563;
        }

        .cancel-button:hover {

            background: #e5e7eb;
        }

        .submit-button {

            background: #2563eb;

            color: white;
        }

        .submit-button:hover {

            background: #1d4ed8;

            transform: translateY(-1px);
        }


        /* =========================
           Info Box
        ========================= */

        .info-box {

            margin-top: 18px;

            padding: 14px 16px;

            background: #eff6ff;

            border: 1px solid #dbeafe;

            border-radius: 8px;

            color: #1e40af;

            font-size: 13px;

            line-height: 1.6;
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

            .page-title h1 {
                font-size: 25px;
            }

            .form-card {
                padding: 20px;
            }

            .button-area {

                flex-direction: column-reverse;
            }

            .button {

                width: 100%;

                text-align: center;
            }

        }

    </style>

</head>


<body>


<!-- =========================
     Header
========================= -->

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



<!-- =========================
     Main
========================= -->

<main>

    <div class="container">


        <div class="page-title">

            <h1>게시글 작성</h1>

            <p>
                새로운 게시글을 작성해주세요.
            </p>

        </div>



        <div class="form-card">


            <form method="POST" action="write.php">


                <!-- 제목 -->

                <div class="form-group">

                    <label for="title">
                        제목
                        <span class="required">*</span>
                    </label>

                    <input
                        type="text"
                        id="title"
                        name="title"
                        placeholder="게시글 제목을 입력하세요."
                        required
                    >

                </div>



                <!-- 내용 -->

                <div class="form-group">

                    <label for="content">
                        내용
                        <span class="required">*</span>
                    </label>

                    <textarea
                        id="content"
                        name="content"
                        placeholder="게시글 내용을 입력하세요."
                        required
                    ></textarea>

                </div>



                <!-- 버튼 -->

                <div class="button-area">

                    <a
                        href="index.php"
                        class="button cancel-button"
                    >
                        취소
                    </a>

                    <button
                        type="submit"
                        class="button submit-button"
                    >
                        게시글 등록
                    </button>

                </div>


            </form>


            <div class="info-box">
                게시글을 작성한 후 등록 버튼을 누르면
                해당 게시글 페이지로 이동합니다.
            </div>


        </div>

    </div>

</main>



<!-- =========================
     Footer
========================= -->

<footer>

    <div class="container">

        XSS Challenge · Web Security Practice

    </div>

</footer>


</body>

</html>
