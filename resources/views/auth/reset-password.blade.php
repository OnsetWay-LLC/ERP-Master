<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=1280">
    <title>Reset Password – onsetway ERB</title>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --c1: #001D39;
            --c2: #0A4174;
            --c3: #49769F;
            --c4: #4E8EA2;
            --c5: #6EA2B3;
            --c6: #7BBDE8;
            --c7: #BDD8E9;
        }

        html, body { width: 100%; height: 100%; }

        body {
            font-family: 'DM Sans', sans-serif;
            background: var(--c1);
            min-height: 100vh;
            display: flex;
            overflow: hidden;
            position: relative;
        }

        .bg-grid {
            position: fixed; inset: 0; z-index: 0;
            background-image:
                linear-gradient(rgba(78,142,162,0.05) 1px, transparent 1px),
                linear-gradient(90deg, rgba(78,142,162,0.05) 1px, transparent 1px);
            background-size: 56px 56px;
        }

        .blob {
            position: fixed; border-radius: 50%;
            pointer-events: none; filter: blur(90px); z-index: 0;
        }
        .blob-1 {
            width: 800px; height: 800px; top: -250px; right: -200px;
            background: radial-gradient(circle, rgba(78,142,162,0.2) 0%, transparent 70%);
            animation: float 9s ease-in-out infinite;
        }
        .blob-2 {
            width: 600px; height: 600px; bottom: -200px; left: -100px;
            background: radial-gradient(circle, rgba(10,65,116,0.28) 0%, transparent 70%);
            animation: float 12s ease-in-out infinite reverse;
        }
        @keyframes float {
            0%,100% { transform: translate(0,0); }
            50%      { transform: translate(20px,-20px); }
        }

        /* ══ LEFT PANEL ══ */
        .left-panel {
            position: relative; z-index: 10;
            width: 42%; min-height: 100vh;
            display: flex; flex-direction: column;
            justify-content: space-between;
            padding: 52px 70px;
            background: linear-gradient(155deg, rgba(0,29,57,0.72) 0%, rgba(10,65,116,0.3) 100%);
            border-right: 1px solid rgba(123,189,232,0.1);
            backdrop-filter: blur(4px);
        }

        .brand { display: flex; align-items: center; gap: 18px; }
        .logo-box {
            width: 56px; height: 56px;
            background: rgba(255,255,255,0.06);
            border: 1px solid rgba(123,189,232,0.28);
            border-radius: 16px;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
        }
        .wordmark { font-family: 'Bebas Neue', sans-serif; font-size: 26px; letter-spacing: 3px; color: var(--c7); }
        .wordmark span { color: var(--c6); }
        .wordmark small {
            display: block; font-family: 'DM Sans', sans-serif;
            font-size: 10px; letter-spacing: 2.5px;
            color: rgba(189,216,233,0.4); font-weight: 300; margin-top: 4px;
        }

        .hero { flex: 1; display: flex; flex-direction: column; justify-content: center; }
        .hero-line {
            font-size: 10px; letter-spacing: 3.5px; text-transform: uppercase;
            color: var(--c5); font-weight: 500; margin-bottom: 22px;
            display: flex; align-items: center; gap: 12px;
        }
        .hero-line::before { content: ''; display: inline-block; width: 32px; height: 1px; background: var(--c5); }
        .hero-title { font-family: 'Bebas Neue', sans-serif; font-size: 96px; line-height: 0.92; letter-spacing: 3px; color: var(--c7); }
        .hero-title .ghost { color: rgba(189,216,233,0.18); }
        .hero-desc { margin-top: 30px; font-size: 15px; line-height: 1.9; color: rgba(110,162,179,0.7); font-weight: 300; max-width: 380px; }

        .stripe-row { display: flex; gap: 7px; }
        .stripe-seg { height: 4px; border-radius: 2px; flex: 1; }

        /* ══ RIGHT PANEL ══ */
        .right-panel {
            position: relative; z-index: 10;
            width: 58%; min-height: 100vh;
            display: flex; align-items: center; justify-content: center;
            padding: 48px 80px;
        }

        .form-card {
            width: 100%; max-width: 800px;
            background: rgba(255,255,255,0.975);
            border-radius: 28px; overflow: hidden;
            box-shadow:
                0 0 0 1px rgba(123,189,232,0.18),
                0 48px 120px rgba(0,0,0,0.5),
                0 8px 32px rgba(0,29,57,0.22);
            animation: cardIn 0.6s cubic-bezier(0.16,1,0.3,1) both;
        }
        @keyframes cardIn {
            from { opacity: 0; transform: translateY(28px) scale(0.98); }
            to   { opacity: 1; transform: translateY(0) scale(1); }
        }

        .top-bar {
            height: 5px;
            background: linear-gradient(90deg, var(--c1), var(--c2), var(--c4), var(--c6), var(--c7));
        }

        /* two columns inside card */
        .card-inner {
            display: grid;
            grid-template-columns: 1fr 1fr;
        }

        /* ── info col ── */
        .card-info {
            padding: 52px 40px 44px;
            border-right: 1px solid rgba(78,142,162,0.12);
            display: flex; flex-direction: column; justify-content: space-between;
        }

        .card-icon {
            width: 60px; height: 60px;
            background: linear-gradient(135deg, var(--c2), var(--c4));
            border-radius: 18px;
            display: flex; align-items: center; justify-content: center;
            margin-bottom: 28px;
            box-shadow: 0 10px 28px rgba(10,65,116,0.28);
        }

        .section-tag {
            display: inline-block; font-size: 10px; letter-spacing: 3px;
            text-transform: uppercase; color: var(--c4); font-weight: 600;
            background: rgba(78,142,162,0.08); border: 1px solid rgba(78,142,162,0.2);
            border-radius: 20px; padding: 5px 14px; margin-bottom: 18px;
        }

        .card-title {
            font-family: 'Bebas Neue', sans-serif; font-size: 52px;
            line-height: 1.0; letter-spacing: 2px; color: var(--c1); margin-bottom: 6px;
        }
        .card-title span { color: var(--c2); }

        .card-divider {
            width: 44px; height: 3px; border-radius: 2px;
            background: linear-gradient(90deg, var(--c2), var(--c6));
            margin: 20px 0;
        }

        .card-desc { font-size: 13.5px; line-height: 1.9; color: var(--c3); font-weight: 300; flex: 1; }
        .card-desc strong { color: var(--c1); font-weight: 600; }

        .security-note {
            display: flex; align-items: flex-start; gap: 12px;
            background: linear-gradient(135deg, rgba(0,29,57,0.04), rgba(78,142,162,0.06));
            border: 1px solid rgba(78,142,162,0.18);
            border-left: 4px solid var(--c4);
            border-radius: 12px; padding: 16px 18px; margin-top: 28px;
        }
        .note-icon {
            flex-shrink: 0; width: 30px; height: 30px;
            background: var(--c2); border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
        }
        .note-text { font-size: 12px; color: var(--c3); line-height: 1.7; }
        .note-text strong {
            display: block; font-size: 10px; text-transform: uppercase;
            letter-spacing: 1.5px; color: var(--c2); margin-bottom: 3px;
        }

        /* ── form col ── */
        .card-form {
            padding: 52px 40px 44px;
            display: flex; flex-direction: column; justify-content: center;
        }

        .msg {
            display: none; padding: 13px 16px; border-radius: 12px;
            font-size: 13px; line-height: 1.6; margin-bottom: 22px;
            align-items: center; gap: 10px;
        }
        .msg.show { display: flex; }
        .msg-success { background: linear-gradient(135deg,#e8f7f0,#d4f0e3); color: #1a5c34; border: 1px solid rgba(26,92,52,0.15); }
        .msg-error   { background: linear-gradient(135deg,#fef0f0,#fde0e0); color: #8b1c1c; border: 1px solid rgba(139,28,28,0.15); }

        .form-group { margin-bottom: 22px; }
        .form-label {
            display: block; font-size: 10px; font-weight: 600;
            letter-spacing: 2px; text-transform: uppercase;
            color: var(--c2); margin-bottom: 9px;
        }
        .input-wrap { position: relative; }
        .input-icon {
            position: absolute; left: 16px; top: 50%;
            transform: translateY(-50%); color: var(--c5); pointer-events: none;
        }
        input[type="password"] {
            width: 100%; padding: 15px 16px 15px 48px;
            border: 1.5px solid #dde8f0; border-radius: 12px;
            font-family: 'DM Sans', sans-serif; font-size: 15px;
            color: var(--c1); background: #f8fbfd; outline: none; transition: all 0.25s;
        }
        input::placeholder { color: #a8c4d4; }
        input:focus { border-color: var(--c4); background: #fff; box-shadow: 0 0 0 4px rgba(78,142,162,0.12); }

        .strength-wrap { display: flex; gap: 5px; margin-top: 10px; }
        .strength-bar { height: 3px; flex: 1; border-radius: 2px; background: #e0eaf2; transition: background 0.35s; }
        .strength-label { font-size: 11px; color: var(--c5); margin-top: 6px; text-align: right; min-height: 16px; }

        .btn-submit {
            width: 100%; padding: 17px;
            background: linear-gradient(135deg, var(--c1) 0%, var(--c2) 50%, var(--c4) 100%);
            color: var(--c7); border: none; border-radius: 12px;
            font-family: 'Bebas Neue', sans-serif; font-size: 20px; letter-spacing: 3px;
            cursor: pointer; position: relative; overflow: hidden;
            box-shadow: 0 8px 30px rgba(10,65,116,0.38);
            transition: transform 0.2s, box-shadow 0.2s; margin-top: 8px;
        }
        .btn-submit::after {
            content: ''; position: absolute; inset: 0;
            background: linear-gradient(135deg, rgba(255,255,255,0.1), transparent);
        }
        .btn-submit:hover { transform: translateY(-2px); box-shadow: 0 14px 40px rgba(10,65,116,0.48); }
        .btn-submit:active { transform: translateY(0); }

        .back-link { text-align: center; margin-top: 22px; }
        .back-link a {
            color: var(--c4); font-size: 13px; text-decoration: none;
            display: inline-flex; align-items: center; gap: 6px; transition: color 0.2s;
        }
        .back-link a:hover { color: var(--c2); }

        .bottom-bar { height: 4px; background: linear-gradient(90deg, var(--c7), var(--c5), var(--c2), var(--c1)); }
    </style>
</head>
<body>

    <div class="bg-grid"></div>
    <div class="blob blob-1"></div>
    <div class="blob blob-2"></div>

    <!-- LEFT -->
    <div class="left-panel">
        <div class="brand">
            <div class="logo-box">
                <svg width="34" height="34" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
                    <g fill="#BDD8E9">
                        <circle cx="50" cy="8"  r="5.5"/><circle cx="72" cy="15" r="5.5"/>
                        <circle cx="88" cy="34" r="5.5"/><circle cx="90" cy="57" r="5.5"/>
                        <circle cx="78" cy="78" r="5.5"/><circle cx="58" cy="91" r="5.5"/>
                        <circle cx="35" cy="91" r="5.5"/><circle cx="18" cy="78" r="5.5"/>
                        <circle cx="8"  cy="57" r="5.5"/><circle cx="10" cy="34" r="5.5"/>
                        <circle cx="25" cy="15" r="5.5"/><circle cx="40" cy="8"  r="5.5"/>
                    </g>
                    <g stroke="#7BBDE8" stroke-width="2" fill="none" opacity="0.7">
                        <line x1="50" y1="8"  x2="72" y2="15"/><line x1="72" y1="15" x2="88" y2="34"/>
                        <line x1="88" y1="34" x2="90" y2="57"/><line x1="90" y1="57" x2="78" y2="78"/>
                        <line x1="78" y1="78" x2="58" y2="91"/><line x1="58" y1="91" x2="35" y2="91"/>
                        <line x1="35" y1="91" x2="18" y2="78"/><line x1="18" y1="78" x2="8"  y2="57"/>
                        <line x1="8"  y1="57" x2="10" y2="34"/><line x1="10" y1="34" x2="25" y2="15"/>
                        <line x1="25" y1="15" x2="40" y2="8"/> <line x1="40" y1="8"  x2="50" y2="8"/>
                    </g>
                    <g stroke="#4E8EA2" stroke-width="1.5" fill="none" opacity="0.9">
                        <line x1="8"  y1="57" x2="90" y2="57"/><line x1="8"  y1="57" x2="72" y2="15"/>
                        <line x1="10" y1="34" x2="78" y2="78"/><line x1="25" y1="15" x2="58" y2="91"/>
                        <line x1="40" y1="8"  x2="35" y2="91"/><line x1="50" y1="8"  x2="18" y2="78"/>
                        <line x1="72" y1="15" x2="35" y2="91"/><line x1="88" y1="34" x2="25" y2="15"/>
                        <line x1="90" y1="57" x2="10" y2="34"/>
                    </g>
                </svg>
            </div>
            <div class="wordmark">
                onsetway <span>ERB</span>
                <small>Your Path to Excellence</small>
            </div>
        </div>

        <div class="hero">
            <div class="hero-line">Security Center</div>
            <div class="hero-title">Secure<br>Your<br><span class="ghost">Account</span></div>
            <p class="hero-desc">Create a strong new password to protect your <strong style="color:var(--c6);font-weight:500;">onsetway ERB</strong> account and keep your data safe.</p>
        </div>

        <div class="stripe-row">
            <div class="stripe-seg" style="background:#001D39;"></div>
            <div class="stripe-seg" style="background:#0A4174;"></div>
            <div class="stripe-seg" style="background:#49769F;"></div>
            <div class="stripe-seg" style="background:#4E8EA2;"></div>
            <div class="stripe-seg" style="background:#6EA2B3;"></div>
            <div class="stripe-seg" style="background:#7BBDE8;"></div>
            <div class="stripe-seg" style="background:#BDD8E9;"></div>
        </div>
    </div>

    <!-- RIGHT -->
    <div class="right-panel">
        <div class="form-card">
            <div class="top-bar"></div>

            <div class="card-inner">

                <!-- Info col -->
                <div class="card-info">
                    <div>
                        <div class="card-icon">
                            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#BDD8E9" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="11" width="18" height="11" rx="2"/>
                                <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                            </svg>
                        </div>
                        <div class="section-tag">Password Recovery</div>
                        <h1 class="card-title">Reset<br>Your<br><span>Password</span></h1>
                        <div class="card-divider"></div>
                        <p class="card-desc">Create a new secure password for your <strong>onsetway ERB</strong> account. Make sure it's strong and unique to keep your account protected.</p>
                    </div>
                    <div class="security-note">
                        <div class="note-icon">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#BDD8E9" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                            </svg>
                        </div>
                        <div class="note-text">
                            <strong>Security Notice</strong>
                            If you did not request this reset, no action is required — your account remains safe.
                        </div>
                    </div>
                </div>

                <!-- Form col -->
                <div class="card-form">
                    <div class="msg msg-success" id="successMsg">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                        <span>Password reset successfully! Redirecting…</span>
                    </div>
                    <div class="msg msg-error" id="errorMsg">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                        <span id="errorText">Something went wrong. Please try again.</span>
                    </div>

                    <form id="resetForm">
                        <div class="form-group">
                            <label class="form-label" for="password">New Password</label>
                            <div class="input-wrap">
                                <span class="input-icon">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                                </span>
                                <input type="password" id="password" placeholder="Enter new password" required minlength="8">
                            </div>
                            <div class="strength-wrap">
                                <div class="strength-bar" id="s1"></div>
                                <div class="strength-bar" id="s2"></div>
                                <div class="strength-bar" id="s3"></div>
                                <div class="strength-bar" id="s4"></div>
                            </div>
                            <div class="strength-label" id="strengthLabel"></div>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="confirm">Confirm Password</label>
                            <div class="input-wrap">
                                <span class="input-icon">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                                </span>
                                <input type="password" id="confirm" placeholder="Confirm your password" required>
                            </div>
                        </div>

                        <button type="submit" class="btn-submit">Reset Password</button>
                    </form>

                    <div class="back-link">
                        <a href="#">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
                            Back to Login
                        </a>
                    </div>
                </div>

            </div>
            <div class="bottom-bar"></div>
        </div>
    </div>

    <script>
        const pwInput = document.getElementById('password');
        const bars = [document.getElementById('s1'),document.getElementById('s2'),
                      document.getElementById('s3'),document.getElementById('s4')];
        const strengthLabel = document.getElementById('strengthLabel');
        const colors = ['#e05050','#e08040','#7BBDE8','#4E8EA2'];
        const labels = ['Weak','Fair','Good','Strong'];

        function calcStrength(pw) {
            let score = 0;
            if (pw.length >= 8)  score++;
            if (pw.length >= 12) score++;
            if (/[A-Z]/.test(pw) && /[a-z]/.test(pw)) score++;
            if (/[0-9]/.test(pw) && /[^A-Za-z0-9]/.test(pw)) score++;
            return score;
        }

        pwInput.addEventListener('input', () => {
            const score = calcStrength(pwInput.value);
            bars.forEach((b,i) => { b.style.background = i < score ? colors[Math.min(score-1,3)] : '#e0eaf2'; });
            strengthLabel.textContent = pwInput.value.length ? (labels[Math.min(score-1,3)] || '') : '';
            strengthLabel.style.color = score ? colors[Math.min(score-1,3)] : '#a8c4d4';
        });

        document.getElementById('resetForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const pw  = document.getElementById('password').value;
            const cpw = document.getElementById('confirm').value;
            document.getElementById('successMsg').classList.remove('show');
            document.getElementById('errorMsg').classList.remove('show');

            if (pw !== cpw) {
                document.getElementById('errorText').textContent = 'Passwords do not match.';
                document.getElementById('errorMsg').classList.add('show');
                return;
            }
            try {
                const params = new URLSearchParams(window.location.search);
                const res = await fetch('/api/auth/reset-password', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                    body: JSON.stringify({
                        token: params.get('token'), email: params.get('email'),
                        password: pw, password_confirmation: cpw
                    })
                });
                const data = await res.json();
                if (!res.ok) throw data;
                document.getElementById('successMsg').classList.add('show');
                setTimeout(() => window.location.href = '/login', 2500);
            } catch (err) {
                let msg = 'Something went wrong. Please try again.';
                if (err.message) msg = err.message;
                else if (err.errors?.password?.length) msg = err.errors.password[0];
                document.getElementById('errorText').textContent = msg;
                document.getElementById('errorMsg').classList.add('show');
            }
        });
    </script>
</body>
</html>