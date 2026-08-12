const express = require('express');
const puppeteer = require('puppeteer');

const app = express();
const PORT = 3000;
const FLAG = process.env.FLAG || 'WUISP{missing}';
const TARGET_BASE = process.env.TARGET_BASE || 'http://xss-app';

// "관리자 확인" 요청이 올 때만 봇을 1회 실행합니다.
// (상시로 도는 스케줄러가 아닙니다 — 통합 계약서 상 별도 network 없이도
//  동작하도록 xss-app, xss-bot이라는 서비스명으로 서로를 찾습니다)
app.get('/check', async (req, res) => {
  const postId = req.query.post_id || '1';
  console.log(`[xss-bot] 관리자 확인 요청 수신: post_id=${postId}`);

  let browser;
  try {
    browser = await puppeteer.launch({
      executablePath: process.env.PUPPETEER_EXECUTABLE_PATH,
      args: ['--no-sandbox', '--disable-setuid-sandbox'],
    });
    const page = await browser.newPage();

    // 관리자 세션 쿠키 — 이 값이 곧 플래그입니다.
    // domain 대신 방문 URL을 지정해 xss-app 호스트 전용 쿠키로 설정합니다.
    await page.setCookie({
      name: 'admin_session',
      value: FLAG,
      url: TARGET_BASE,
      path: '/',
    });

    await page.goto(`${TARGET_BASE}/post.php?id=${postId}`, {
      waitUntil: 'networkidle0',
      timeout: 10000,
    });

    console.log('[xss-bot] 게시글 확인 완료');
    await browser.close();
    res.send('관리자가 게시글을 확인했습니다.');
  } catch (err) {
    if (browser) await browser.close();
    console.error('[xss-bot] 에러:', err.message);
    res.status(500).send('봇 실행 중 오류 발생: ' + err.message);
  }
});

app.listen(PORT, () => console.log(`[xss-bot] 대기 중 (포트 ${PORT})`));
