<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - onsetway ERB</title>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;600;700&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
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

        body {
            font-family: 'DM Sans', Arial, sans-serif;
            background: var(--c1);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            position: relative;
        }

        /* Animated background blobs */
        body::before {
            content: '';
            position: fixed;
            top: -200px; right: -200px;
            width: 600px; height: 600px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(78,142,162,0.18) 0%, transparent 70%);
            animation: floatBlob 8s ease-in-out infinite;
            pointer-events: none;
        }
        body::after {
            content: '';
            position: fixed;
            bottom: -200px; left: -200px;
            width: 500px; height: 500px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(10,65,116,0.25) 0%, transparent 70%);
            animation: floatBlob 10s ease-in-out infinite reverse;
            pointer-events: none;
        }

        @keyframes floatBlob {
            0%, 100% { transform: translate(0, 0) scale(1); }
            50% { transform: translate(30px, -30px) scale(1.05); }
        }

        /* Grid texture overlay */
        .bg-grid {
            position: fixed; inset: 0;
            background-image:
                linear-gradient(rgba(78,142,162,0.04) 1px, transparent 1px),
                linear-gradient(90deg, rgba(78,142,162,0.04) 1px, transparent 1px);
            background-size: 48px 48px;
            pointer-events: none;
        }

        .wrapper {
            position: relative; z-index: 10;
            width: 100%; max-width: 480px;
            padding: 20px;
        }

        .card {
            background: rgba(255,255,255,0.97);
            border-radius: 24px;
            overflow: hidden;
            box-shadow:
                0 40px 100px rgba(0,0,0,0.5),
                0 0 0 1px rgba(123,189,232,0.15);
            animation: slideUp 0.6s cubic-bezier(0.16,1,0.3,1) both;
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(40px) scale(0.97); }
            to   { opacity: 1; transform: translateY(0) scale(1); }
        }

        /* Top rainbow bar */
        .top-bar {
            height: 4px;
            background: linear-gradient(90deg, var(--c1), var(--c2), var(--c4), var(--c6), var(--c7));
        }

        /* Header */
        .header {
            background: linear-gradient(135deg, var(--c1) 0%, var(--c2) 100%);
            padding: 38px 40px 32px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        .header::before {
            content: '';
            position: absolute;
            top: -60px; right: -60px;
            width: 180px; height: 180px;
            border-radius: 50%;
            background: rgba(78,142,162,0.14);
        }
        .header::after {
            content: '';
            position: absolute;
            bottom: -40px; left: -40px;
            width: 120px; height: 120px;
            border-radius: 50%;
            background: rgba(189,216,233,0.08);
        }

        .logo-icon {
            display: inline-flex;
            align-items: center; justify-content: center;
            width: 52px; height: 52px;
            background: linear-gradient(135deg, var(--c4), var(--c6));
            border-radius: 14px;
            font-size: 22px;
            margin-bottom: 16px;
            box-shadow: 0 8px 24px rgba(78,142,162,0.4);
            position: relative; z-index: 1;
        }

        .header h1 {
            font-family: 'Cormorant Garamond', serif;
            color: var(--c7);
            font-size: 26px;
            font-weight: 700;
            letter-spacing: 0.05em;
            position: relative; z-index: 1;
        }

        .header p {
            color: var(--c5);
            font-size: 11px;
            letter-spacing: 0.2em;
            text-transform: uppercase;
            font-weight: 300;
            margin-top: 6px;
            position: relative; z-index: 1;
        }

        /* Content */
        .content {
            padding: 44px 40px 36px;
        }

        .section-label {
            font-size: 10px;
            letter-spacing: 0.22em;
            color: var(--c4);
            text-transform: uppercase;
            font-weight: 500;
            margin-bottom: 10px;
        }

        .content h2 {
            font-family: 'Cormorant Garamond', serif;
            font-size: 36px;
            font-weight: 700;
            color: var(--c1);
            line-height: 1.15;
            margin-bottom: 6px;
        }
        .content h2 span { color: var(--c2); }

        .divider {
            width: 40px; height: 3px;
            border-radius: 2px;
            background: linear-gradient(90deg, var(--c4), var(--c7));
            margin: 18px 0 22px;
        }

        .content > p {
            color: var(--c3);
            font-size: 14.5px;
            line-height: 1.8;
            font-weight: 300;
            margin-bottom: 30px;
        }

        /* Form */
        .form-group { margin-bottom: 22px; }

        label {
            display: block;
            font-size: 11px;
            font-weight: 500;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: var(--c2);
            margin-bottom: 8px;
        }

        .input-wrap {
            position: relative;
        }
        .input-icon {
            position: absolute;
            left: 16px; top: 50%;
            transform: translateY(-50%);
            color: var(--c5);
            font-size: 16px;
            pointer-events: none;
        }

        input[type="email"] {
            width: 100%;
            padding: 14px 16px 14px 44px;
            border: 1.5px solid #dde8f0;
            border-radius: 12px;
            font-family: 'DM Sans', Arial, sans-serif;
            font-size: 14.5px;
            color: var(--c1);
            background: #f8fbfd;
            outline: none;
            transition: all 0.25s ease;
        }
        input[type="email"]::placeholder { color: #a8c4d4; }
        input[type="email"]:focus {
            border-color: var(--c4);
            background: #fff;
            box-shadow: 0 0 0 4px rgba(78,142,162,0.12);
        }

        .btn {
            width: 100%;
            background: linear-gradient(135deg, var(--c2) 0%, var(--c4) 100%);
            color: #ffffff;
            border: none;
            border-radius: 50px;
            padding: 16px;
            font-family: 'DM Sans', Arial, sans-serif;
            font-size: 11px;
            font-weight: 500;
            letter-spacing: 0.18em;
            text-transform: uppercase;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 10px 28px rgba(10,65,116,0.35);
            position: relative;
            overflow: hidden;
        }
        .btn::after {
            content: '';
            position: absolute; inset: 0;
            background: linear-gradient(135deg, rgba(255,255,255,0.1), transparent);
        }
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 16px 36px rgba(10,65,116,0.45);
        }
        .btn:active { transform: translateY(0); }

        /* Messages */
        .message {
            margin-bottom: 22px;
            padding: 14px 16px;
            border-radius: 12px;
            font-size: 13.5px;
            line-height: 1.6;
            display: flex;
            align-items: flex-start;
            gap: 10px;
        }
        .message-icon { font-size: 16px; flex-shrink: 0; margin-top: 1px; }

        .success {
            background: linear-gradient(135deg, #e8f7f0, #d4f0e3);
            color: #1a5c34;
            border: 1px solid rgba(26,92,52,0.15);
        }
        .error {
            background: linear-gradient(135deg, #fef0f0, #fde0e0);
            color: #8b1c1c;
            border: 1px solid rgba(139,28,28,0.15);
        }

        /* Back to login link */
        .back-link {
            text-align: center;
            margin-top: 24px;
        }
        .back-link a {
            color: var(--c4);
            font-size: 13px;
            text-decoration: none;
            font-weight: 400;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: color 0.2s;
        }
        .back-link a:hover { color: var(--c2); }

        /* Footer */
        .footer {
            background: linear-gradient(135deg, var(--c1), var(--c2));
            padding: 20px 40px;
            text-align: center;
        }
        .footer p {
            color: var(--c3);
            font-size: 11px;
            letter-spacing: 0.08em;
        }
        .dot-row {
            display: flex;
            justify-content: center;
            gap: 6px;
            margin-top: 12px;
        }
        .dot {
            width: 6px; height: 6px;
            border-radius: 50%;
        }

        /* Bottom bar */
        .bottom-bar {
            height: 4px;
            background: linear-gradient(90deg, var(--c7), var(--c5), var(--c1), var(--c5), var(--c7));
        }
    </style>
</head>
<body>
    <div class="bg-grid"></div>

    <div class="wrapper">
        <div class="card">
            <div class="top-bar"></div>

            <div class="header">
                <div class="logo-icon">✦</div>
                <h1>onsetway ERB</h1>
                <p>Your Path to Excellence</p>
            </div>

            <div class="content">
                <p class="section-label">Password Recovery</p>
                <h2>Forgot Your<br><span>Password?</span></h2>
                <div class="divider"></div>
                <p>Enter your email address and we'll send you a secure reset link to create a new password.</p>

                <div id="responseBox"></div>

                <form id="forgotPasswordForm">
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <div class="input-wrap">
                            <span class="input-icon">✉</span>
                            <input
                                type="email"
                                id="email"
                                name="email"
                                placeholder="Enter your email address"
                                required
                            >
                        </div>
                    </div>

                    <button type="submit" class="btn">Send Reset Link</button>
                </form>

                <div class="back-link">
                    <a href="#">← Back to Login</a>
                </div>
            </div>

            <div class="footer">
                <p>© {{ date('Y') }} onsetway ERB. All rights reserved.</p>
                <div class="dot-row">
                    <div class="dot" style="background:#001D39;"></div>
                    <div class="dot" style="background:#0A4174;"></div>
                    <div class="dot" style="background:#49769F;"></div>
                    <div class="dot" style="background:#4E8EA2;"></div>
                    <div class="dot" style="background:#6EA2B3;"></div>
                    <div class="dot" style="background:#7BBDE8;"></div>
                    <div class="dot" style="background:#BDD8E9;"></div>
                </div>
            </div>

            <div class="bottom-bar"></div>
        </div>
    </div>

    <script>
        document.getElementById('forgotPasswordForm').addEventListener('submit', async function (e) {
            e.preventDefault();
            const responseBox = document.getElementById('responseBox');
            responseBox.innerHTML = '';
            const email = document.getElementById('email').value;

            try {
                const response = await fetch('/api/auth/forgot-password', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                    body: JSON.stringify({ email })
                });
                const data = await response.json();
                if (!response.ok) throw data;

                responseBox.innerHTML = `
                    <div class="message success">
                        <span class="message-icon">✓</span>
                        <span>${data.message}</span>
                    </div>`;
            } catch (error) {
                let message = 'Something went wrong. Please try again.';
                if (error.message) message = error.message;
                else if (error.errors?.email?.length) message = error.errors.email[0];

                responseBox.innerHTML = `
                    <div class="message error">
                        <span class="message-icon">✕</span>
                        <span>${message}</span>
                    </div>`;
            }
        });
    </script>
</body>
</html>