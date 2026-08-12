# 4조 — 플랫폼

## 1. 챌린지 설명
문제 목록 조회, 닉네임 등록, 플래그 제출·채점, 리더보드를 제공하는 CTF 플랫폼입니다.

## 2. 실행 방법
```bash
docker compose up
```
종료:
```bash
docker compose down
```

## 3. 접속 주소
http://localhost:8080

AWS 배포 시 저장소 루트 `.env`의 `PUBLIC_HOST`를 EC2 공인 IP 또는 도메인으로 설정합니다. 플랫폼은 이 값을 사용해 각 챌린지 링크를 생성하므로, 참가자에게 `localhost` 링크가 보이지 않습니다.

## 4. 규칙
- 닉네임은 중복 등록 불가 (동일 닉네임 재등록 시 에러)
- 같은 닉네임이 같은 문제를 다시 맞혀도 점수는 한 번만 반영됨 (`UNIQUE(nickname, challenge_id)`)
- 문제당 100점, 총 300점 (3문제)
- 리더보드 기록은 SQLite + Docker volume(`platform_data`)으로 영속화됨 — 컨테이너를 껐다 켜도 기록 유지

## 5. 정답 플래그 설정
`docker-compose.yml`의 환경변수로 주입합니다. 실제 값은 Git에 올리지 않는 저장소 루트 `.env`에만 채워주세요.

```yaml
environment:
  FLAG_SQLI: "WUISP{실제_sqli_플래그}"
  FLAG_XSS: "WUISP{실제_xss_플래그}"
  FLAG_UPLOAD: "WUISP{실제_upload_플래그}"
```

## 6. 테스트용 초기화 방법
리더보드/등록 정보를 완전히 초기화하려면 볼륨을 삭제하고 다시 띄우면 됩니다.

```bash
docker compose down -v
docker compose up
```

`-v` 없이 `docker compose down`만 하면 데이터는 volume에 남아있고, 다시 `up` 했을 때 그대로 이어집니다.
