# WUISP_프로젝트

스터디 2~5주차(XSS·SQLi·파일 업로드·Docker/AWS)에서 배운 내용을 바탕으로, 직접 만든 웹해킹 CTF 챌린지 3종과 플랫폼을 제작·배포·운영하는 프로젝트입니다.

## 폴더 구조

```
ctf-project/
├── challenges/
│   ├── sqli/       # 1조 — SQL Injection (로그인 우회)
│   ├── xss/        # 2조 — XSS (Stored XSS → 관리자 쿠키 탈취)
│   └── upload/      # 3조 — 파일 업로드 (웹쉘 → RCE)
├── platform/         # 4조 — 문제 목록 / 플래그 제출 / 리더보드
└── docs/             # 규칙, 템플릿, 체크리스트
```

## 빠른 시작 (전체 통합 실행)

저장소 루트에서 실행합니다. 루트 `compose.yaml`은 각 조의 Compose 파일을 include하므로, 각 조의 상대 경로가 깨지지 않습니다.

```bash
cp .env.example .env
docker compose up --build
```

종료는 `docker compose down`입니다. 각 조의 단독 실행은 해당 조 폴더에서 `docker compose up`을 사용합니다.

| 서비스 | 주소 |
|---|---|
| 플랫폼 | http://localhost:8080 |
| SQLi | http://localhost:8081 |
| XSS | http://localhost:8082 |
| 파일 업로드 | http://localhost:8083 |

## 꼭 읽어야 할 문서

- [`docs/프로젝트_규칙.md`](docs/통합_계약서.md) — 포트, 서비스명, 실행 규칙 (모든 조 필독)
- [`docs/handoffs/xss-bot-kit/`](docs/handoffs/xss-bot-kit/) — (2조) XSS 봇 최소 템플릿
- [`docs/AWS_운영_주의.md`](docs/AWS_운영_경계.md) — (4조) 실제 배포·운영 시 지킬 것
- [`docs/README_템플릿.md`](docs/README_템플릿.md) / [`docs/Writeup_템플릿.md`](docs/Writeup_템플릿.md) — 각 조 산출물 양식

```

## 배포 전 확인

- 실제 플래그, `SECRET_KEY`, EC2 주소는 Git에 올리지 않는 `.env`에만 둡니다.
- EC2에 배포할 때는 `.env`의 `PUBLIC_HOST`를 EC2 공인 IP 또는 도메인으로 변경합니다.
- `FLAG_XSS`와 XSS 봇의 `XSS_FLAG`는 반드시 같은 값으로 설정합니다.
