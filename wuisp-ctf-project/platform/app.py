import os
import sqlite3
from flask import Flask, request, redirect, url_for, render_template, session, flash

app = Flask(__name__)
app.secret_key = os.environ.get("SECRET_KEY", "dev-secret-change-me")

DB_PATH = os.environ.get("DB_PATH", "/data/ctf.db")
POINTS_PER_CHALLENGE = 100

CHALLENGES = {
    "sqli": {"name": "SQL Injection — 관리자 로그인 페이지", "port": 8081},
    "xss": {"name": "XSS — 공지사항 댓글", "port": 8082},
    "upload": {"name": "File Upload — 프로필 사진 업로드", "port": 8083},
}

# 로컬에서는 localhost, AWS에서는 EC2 공인 IP 또는 도메인을 .env로 주입합니다.
PUBLIC_HOST = os.environ.get("PUBLIC_HOST", "localhost")

# 정답 플래그 (환경변수로 주입, 각 조가 확정한 값으로 docker-compose.yml에서 설정)
CORRECT_FLAGS = {
    "sqli": os.environ.get("FLAG_SQLI", "WUISP{change_me_sqli}"),
    "xss": os.environ.get("FLAG_XSS", "WUISP{change_me_xss}"),
    "upload": os.environ.get("FLAG_UPLOAD", "WUISP{change_me_upload}"),
}


def get_db():
    conn = sqlite3.connect(DB_PATH)
    conn.row_factory = sqlite3.Row
    return conn


def get_challenges_with_urls():
    """플랫폼을 여는 사용자가 실제로 접속할 수 있는 챌린지 URL을 만든다."""
    return {
        challenge_id: {
            **challenge,
            "url": f"http://{PUBLIC_HOST}:{challenge['port']}",
        }
        for challenge_id, challenge in CHALLENGES.items()
    }


def init_db():
    os.makedirs(os.path.dirname(DB_PATH), exist_ok=True)
    conn = get_db()
    # 닉네임은 중복 불가 (UNIQUE 제약)
    conn.execute("""
        CREATE TABLE IF NOT EXISTS players (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            nickname TEXT UNIQUE NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    """)
    # 같은 닉네임이 같은 문제를 다시 맞혀도 UNIQUE(nickname, challenge_id)로 인해 점수는 한 번만 반영됨
    conn.execute("""
        CREATE TABLE IF NOT EXISTS solves (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            nickname TEXT NOT NULL,
            challenge_id TEXT NOT NULL,
            solved_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE(nickname, challenge_id)
        )
    """)
    conn.commit()
    conn.close()


def get_leaderboard():
    conn = get_db()
    rows = conn.execute("""
        SELECT nickname, COUNT(*) as solved_count, MIN(solved_at) as first_solve
        FROM solves
        GROUP BY nickname
        ORDER BY solved_count DESC, first_solve ASC
    """).fetchall()
    conn.close()
    return [
        {
            "nickname": r["nickname"],
            "score": r["solved_count"] * POINTS_PER_CHALLENGE,
            "solved_count": r["solved_count"],
        }
        for r in rows
    ]


@app.route("/", methods=["GET"])
def index():
    nickname = session.get("nickname")
    solved = set()
    if nickname:
        conn = get_db()
        rows = conn.execute(
            "SELECT challenge_id FROM solves WHERE nickname=?", (nickname,)
        ).fetchall()
        solved = {r["challenge_id"] for r in rows}
        conn.close()

    return render_template(
        "index.html",
        challenges=get_challenges_with_urls(),
        nickname=nickname,
        solved=solved,
        leaderboard=get_leaderboard(),
        points=POINTS_PER_CHALLENGE,
        total_points=POINTS_PER_CHALLENGE * len(CHALLENGES),
    )


@app.route("/register", methods=["POST"])
def register():
    nickname = request.form.get("nickname", "").strip()
    if not nickname:
        flash("닉네임을 입력해주세요.")
        return redirect(url_for("index"))

    conn = get_db()
    try:
        conn.execute("INSERT INTO players (nickname) VALUES (?)", (nickname,))
        conn.commit()
        session["nickname"] = nickname
        flash(f"'{nickname}'(으)로 등록되었습니다.")
    except sqlite3.IntegrityError:
        flash("이미 사용 중인 닉네임입니다. 다른 닉네임을 입력해주세요.")
    finally:
        conn.close()
    return redirect(url_for("index"))


@app.route("/submit", methods=["POST"])
def submit():
    nickname = session.get("nickname")
    if not nickname:
        flash("먼저 닉네임을 등록해주세요.")
        return redirect(url_for("index"))

    challenge_id = request.form.get("challenge_id")
    flag = request.form.get("flag", "").strip()

    if challenge_id not in CHALLENGES:
        flash("잘못된 챌린지입니다.")
        return redirect(url_for("index"))

    if flag != CORRECT_FLAGS[challenge_id]:
        flash("플래그가 올바르지 않습니다.")
        return redirect(url_for("index"))

    conn = get_db()
    try:
        conn.execute(
            "INSERT INTO solves (nickname, challenge_id) VALUES (?, ?)",
            (nickname, challenge_id),
        )
        conn.commit()
        flash(f"정답입니다! {CHALLENGES[challenge_id]['name']} 클리어 (+{POINTS_PER_CHALLENGE}점)")
    except sqlite3.IntegrityError:
        # 같은 닉네임이 같은 문제를 다시 맞혀도 점수는 한 번만 반영
        flash("이미 획득한 플래그입니다. (점수는 한 번만 반영됩니다)")
    finally:
        conn.close()
    return redirect(url_for("index"))


if __name__ == "__main__":
    init_db()
    app.run(host="0.0.0.0", port=80, debug=False)
