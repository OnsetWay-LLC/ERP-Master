<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - onsetway ERB</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: Arial, sans-serif;
            background-color: #0A3259;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 20px;
        }

        .wrapper { width: 100%; max-width: 440px; }

        .card {
            background: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid #1a4a7a;
        }

        .accent-top {
            height: 3px;
            background: linear-gradient(90deg, #0C447C, #378ADD, #85B7EB);
        }

        .header {
            padding: 26px 32px 22px;
            border-bottom: 1px solid #f0f0f0;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .logo-box {
            width: 34px; height: 34px;
            border-radius: 8px;
            background: #E6F1FB;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .header-text p:first-child {
            font-size: 13px;
            font-weight: 600;
            color: #042C53;
        }

        .header-text p:last-child {
            font-size: 11px;
            color: #378ADD;
            letter-spacing: 0.06em;
        }

        .content { padding: 32px 32px 28px; }

        .section-label {
            font-size: 11px;
            letter-spacing: 0.14em;
            color: #185FA5;
            text-transform: uppercase;
            font-weight: 500;
            margin-bottom: 5px;
        }

        .content h2 {
            font-size: 22px;
            font-weight: 500;
            color: #042C53;
            line-height: 1.25;
            margin-bottom: 16px;
        }

        .divider {
            width: 28px; height: 2px;
            background: #378ADD;
            border-radius: 1px;
            margin-bottom: 16px;
        }

        .content > p {
            font-size: 13px;
            line-height: 1.75;
            color: #378ADD;
            margin-bottom: 24px;
        }

        .message {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            padding: 12px 14px;
            border-radius: 8px;
            font-size: 13px;
            line-height: 1.6;
            margin-bottom: 16px;
        }

        .message svg { flex-shrink: 0; margin-top: 1px; }

        .message.success {
            background: #EAF3DE;
            border-left: 2px solid #3B6D11;
            color: #27500A;
        }

        .message.error {
            background: #FCEBEB;
            border-left: 2px solid #E24B4A;
            color: #791F1F;
        }

        .form-group { margin-bottom: 16px; }

        label {
            display: block;
            font-size: 11px;
            font-weight: 500;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            color: #0C447C;
            margin-bottom: 7px;
        }

        .input-wrap { position: relative; }

        .input-wrap svg {
            position: absolute;
            left: 13px; top: 50%;
            transform: translateY(-50%);
            pointer-events: none;
        }

        input[type="email"] {
            width: 100%;
            padding: 11px 14px 11px 38px;
            border: 1.5px solid #dde8f0;
            border-radius: 8px;
            font-family: Arial, sans-serif;
            font-size: 13.5px;
            color: #042C53;
            background: #f8fbfd;
            outline: none;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        input[type="email"]::placeholder { color: #85B7EB; }

        input[type="email"]:focus {
            border-color: #378ADD;
            background: #fff;
            box-shadow: 0 0 0 3px rgba(55,138,221,0.12);
        }

        .btn {
            width: 100%;
            background: #185FA5;
            color: #ffffff;
            border: none;
            border-radius: 50px;
            padding: 13px;
            font-family: Arial, sans-serif;
            font-size: 11px;
            font-weight: 500;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            cursor: pointer;
            transition: background 0.2s;
            margin-bottom: 18px;
        }

        .btn:hover { background: #0C447C; }
        .btn:disabled { background: #85B7EB; cursor: not-allowed; }

        .back-link {
            text-align: center;
        }

        .back-link a {
            color: #378ADD;
            font-size: 13px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            transition: color 0.2s;
        }

        .back-link a:hover { color: #0C447C; }

        .footer {
            padding: 16px 32px;
            border-top: 1px solid #f0f0f0;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .footer p { font-size: 11px; color: #85B7EB; }

        .dots { display: flex; gap: 3px; align-items: center; }

        .dot {
            width: 4px; height: 4px;
            border-radius: 50%;
            display: inline-block;
        }

        .accent-bottom {
            height: 3px;
            background: linear-gradient(90deg, #85B7EB, #378ADD, #0C447C);
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="card">

            <div class="accent-top"></div>

            <div class="header">
                <div class="logo-box">
                    <svg width="14" height="14" viewBox="0 0 16 16" fill="none">
                        <path d="M8 1l1.8 3.6L14 5.5l-3 2.9.7 4.1L8 10.4l-3.7 2.1.7-4.1L2 5.5l4.2-.9L8 1z" fill="#185FA5"/>
                    </svg>
                </div>
                <div class="header-text">
                    <p>onsetway ERB</p>
                    <p>Your path to excellence</p>
                </div>
            </div>

            <div class="content">
                <p class="section-label">Password Recovery</p>
                <h2>Forgot your password?</h2>
                <div class="divider"></div>
                <p>Enter your email and we'll send you a secure reset link to create a new password.</p>

                <div id="responseBox"></div>

                <form id="forgotPasswordForm">
                    @csrf
                    <div class="form-group">
                        <label for="email">Email address</label>
                        <div class="input-wrap">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
                                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z" stroke="#85B7EB" stroke-width="1.5"/>
                                <polyline points="22,6 12,13 2,6" stroke="#85B7EB" stroke-width="1.5"/>
                            </svg>
                            <input
                                type="email"
                                id="email"
                                name="email"
                                placeholder="Enter your email address"
                                required
                            >
                        </div>
                    </div>

                    <button type="submit" class="btn" id="submitBtn">Send reset link</button>
                </form>

               
            </div>

            <div class="footer">
                <p>© {{ date('Y') }} onsetway ERB. All rights reserved.</p>
                <div class="dots">
                    <span class="dot" style="background:#042C53"></span>
                    <span class="dot" style="background:#185FA5"></span>
                    <span class="dot" style="background:#378ADD"></span>
                    <span class="dot" style="background:#85B7EB"></span>
                </div>
            </div>

            <div class="accent-bottom"></div>

        </div>
    </div>

    <script>
        const form = document.getElementById('forgotPasswordForm');
        const responseBox = document.getElementById('responseBox');
        const submitBtn = document.getElementById('submitBtn');

        form.addEventListener('submit', async function (e) {
            e.preventDefault();
            responseBox.innerHTML = '';
            submitBtn.disabled = true;
            submitBtn.textContent = 'Sending...';

            const email = document.getElementById('email').value;

            try {
                const response = await fetch('/api/auth/forgot-password', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                    },
                    body: JSON.stringify({ email })
                });

                const data = await response.json();
                if (!response.ok) throw data;

                responseBox.innerHTML = `
                    <div class="message success">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
                            <path d="M5 12l5 5L19 7" stroke="#3B6D11" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        <span>${data.message}</span>
                    </div>`;
                form.reset();

            } catch (error) {
                let message = 'Something went wrong. Please try again.';
                if (error.message) message = error.message;
                else if (error.errors?.email?.length) message = error.errors.email[0];

                responseBox.innerHTML = `
                    <div class="message error">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
                            <path d="M18 6L6 18M6 6l12 12" stroke="#A32D2D" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                        <span>${message}</span>
                    </div>`;
            } finally {
                submitBtn.disabled = false;
                submitBtn.textContent = 'Send reset link';
            }
        });
    </script>
</body>
</html>