<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password – onsetway ERB</title>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            background: linear-gradient(160deg, #001D39 0%, #0A4174 40%, #4E8EA2 100%);
            font-family: 'DM Sans', sans-serif;
            min-height: 100vh;
            padding: 40px 20px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .email-wrapper {
            width: 100%;
            max-width: 620px;
            margin: 0 auto;
        }

        /* ─── CARD ─── */
        .card {
            background: #ffffff;
            border-radius: 20px;
            overflow: hidden;
            box-shadow:
                0 0 0 1px rgba(123,189,232,0.25),
                0 32px 80px rgba(0,29,57,0.55),
                0 8px 24px rgba(0,29,57,0.3);
        }

        /* ─── HEADER ─── */
        .header {
            position: relative;
            background: linear-gradient(135deg, #001D39 0%, #0A4174 60%, #49769F 100%);
            padding: 44px 40px 36px;
            overflow: hidden;
            text-align: center;
        }

        /* decorative rings */
        .header::before,
        .header::after {
            content: '';
            position: absolute;
            border-radius: 50%;
            border: 1px solid rgba(123,189,232,0.18);
        }
        .header::before {
            width: 340px; height: 340px;
            top: -100px; right: -80px;
        }
        .header::after {
            width: 200px; height: 200px;
            bottom: -60px; left: -40px;
        }

        /* SVG graph logo */
        .logo-icon {
            display: block;
            margin: 0 auto 18px;
            width: 72px;
            height: 72px;
            background: rgba(255,255,255,0.06);
            border: 1px solid rgba(123,189,232,0.3);
            border-radius: 18px;
            padding: 10px;
            backdrop-filter: blur(6px);
        }

        .brand-name {
            font-family: 'Bebas Neue', sans-serif;
            font-size: 36px;
            letter-spacing: 4px;
            color: #BDD8E9;
            line-height: 1;
        }

        .brand-name span {
            color: #7BBDE8;
        }

        .brand-tagline {
            margin-top: 8px;
            font-size: 11px;
            letter-spacing: 3px;
            text-transform: uppercase;
            color: rgba(189,216,233,0.55);
            font-weight: 300;
        }

        /* ─── ACCENT STRIPE ─── */
        .stripe {
            height: 4px;
            background: linear-gradient(90deg, #001D39, #0A4174, #4E8EA2, #6EA2B3, #7BBDE8, #BDD8E9);
        }

        /* ─── BODY ─── */
        .body {
            padding: 50px 44px 40px;
            background: #ffffff;
        }

        .label {
            display: inline-block;
            font-size: 10px;
            letter-spacing: 3px;
            text-transform: uppercase;
            color: #49769F;
            font-weight: 600;
            background: rgba(78,142,162,0.08);
            border: 1px solid rgba(78,142,162,0.2);
            border-radius: 20px;
            padding: 5px 14px;
        }

        .heading {
            margin-top: 20px;
            font-family: 'Bebas Neue', sans-serif;
            font-size: 52px;
            line-height: 1.05;
            color: #001D39;
            letter-spacing: 2px;
        }

        .heading span {
            color: #0A4174;
        }

        .divider {
            width: 48px;
            height: 3px;
            background: linear-gradient(90deg, #0A4174, #7BBDE8);
            border-radius: 2px;
            margin: 24px 0;
        }

        .body-text {
            font-size: 15.5px;
            line-height: 1.85;
            color: #49769F;
            font-weight: 300;
        }

        .body-text strong {
            color: #001D39;
            font-weight: 600;
        }

        /* ─── CTA ─── */
        .cta-wrap {
            margin: 38px 0;
            text-align: center;
        }

        .cta-btn {
            display: inline-block;
            text-decoration: none;
            background: linear-gradient(135deg, #001D39 0%, #0A4174 100%);
            color: #BDD8E9;
            font-family: 'Bebas Neue', sans-serif;
            font-size: 18px;
            letter-spacing: 3px;
            padding: 18px 48px;
            border-radius: 10px;
            position: relative;
            overflow: hidden;
            box-shadow:
                0 4px 24px rgba(10,65,116,0.35),
                0 1px 4px rgba(0,29,57,0.2);
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .cta-btn::after {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, rgba(123,189,232,0.15), transparent);
            border-radius: inherit;
        }

        .cta-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 32px rgba(10,65,116,0.45);
        }

        /* ─── SECURITY NOTE ─── */
        .security-box {
            background: linear-gradient(135deg, rgba(0,29,57,0.04), rgba(78,142,162,0.06));
            border: 1px solid rgba(78,142,162,0.18);
            border-left: 4px solid #4E8EA2;
            border-radius: 10px;
            padding: 18px 20px;
            display: flex;
            gap: 14px;
            align-items: flex-start;
            margin-top: 32px;
        }

        .security-icon {
            flex-shrink: 0;
            width: 32px;
            height: 32px;
            background: #0A4174;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .security-text {
            font-size: 13px;
            color: #49769F;
            line-height: 1.7;
        }

        .security-text strong {
            display: block;
            color: #0A4174;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            margin-bottom: 3px;
        }

        /* ─── FOOTER ─── */
        .footer {
            background: linear-gradient(135deg, #001D39 0%, #0A4174 100%);
            padding: 26px 44px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            flex-wrap: wrap;
        }

        .footer-brand {
            font-family: 'Bebas Neue', sans-serif;
            font-size: 16px;
            letter-spacing: 2px;
            color: #6EA2B3;
        }

        .footer-copy {
            font-size: 12px;
            color: rgba(110,162,179,0.6);
        }

        .footer-dots {
            display: flex;
            gap: 6px;
            align-items: center;
        }

        .dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
        }
    </style>
</head>
<body>
<div class="email-wrapper">
<div class="card">

    <!-- HEADER -->
    <div class="header">
        <!-- Graph logo reproduced as SVG -->
        <svg class="logo-icon" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
            <!-- 14 nodes arranged in a circle, cross-edges like the uploaded logo -->
            <g fill="#BDD8E9" stroke="none">
                <circle cx="50" cy="10" r="5"/>
                <circle cx="72" cy="17" r="5"/>
                <circle cx="88" cy="35" r="5"/>
                <circle cx="90" cy="58" r="5"/>
                <circle cx="78" cy="78" r="5"/>
                <circle cx="58" cy="90" r="5"/>
                <circle cx="35" cy="90" r="5"/>
                <circle cx="18" cy="78" r="5"/>
                <circle cx="8"  cy="58" r="5"/>
                <circle cx="10" cy="35" r="5"/>
                <circle cx="25" cy="17" r="5"/>
                <circle cx="40" cy="10" r="5"/>
            </g>
            <!-- outer ring edges -->
            <g stroke="#7BBDE8" stroke-width="1.5" fill="none" opacity="0.7">
                <line x1="50" y1="10" x2="72" y2="17"/>
                <line x1="72" y1="17" x2="88" y2="35"/>
                <line x1="88" y1="35" x2="90" y2="58"/>
                <line x1="90" y1="58" x2="78" y2="78"/>
                <line x1="78" y1="78" x2="58" y2="90"/>
                <line x1="58" y1="90" x2="35" y2="90"/>
                <line x1="35" y1="90" x2="18" y2="78"/>
                <line x1="18" y1="78" x2="8"  y2="58"/>
                <line x1="8"  y1="58" x2="10" y2="35"/>
                <line x1="10" y1="35" x2="25" y2="17"/>
                <line x1="25" y1="17" x2="40" y2="10"/>
                <line x1="40" y1="10" x2="50" y2="10"/>
            </g>
            <!-- cross edges -->
            <g stroke="#4E8EA2" stroke-width="1.2" fill="none" opacity="0.85">
                <line x1="10" y1="58" x2="90" y2="58"/>
                <line x1="8"  y1="58" x2="72" y2="17"/>
                <line x1="10" y1="35" x2="78" y2="78"/>
                <line x1="25" y1="17" x2="58" y2="90"/>
                <line x1="40" y1="10" x2="35" y2="90"/>
                <line x1="50" y1="10" x2="18" y2="78"/>
                <line x1="72" y1="17" x2="35" y2="90"/>
                <line x1="88" y1="35" x2="25" y2="17"/>
                <line x1="90" y1="58" x2="10" y2="35"/>
            </g>
        </svg>

        <div class="brand-name">onsetway <span>ERB</span></div>
        <div class="brand-tagline">Your Path to Excellence</div>
    </div>

    <!-- COLOR STRIPE -->
    <div class="stripe"></div>

    <!-- BODY -->
    <div class="body">
        <div class="label">Password Recovery</div>

        <h1 class="heading">Reset Your<br><span>Password</span></h1>

        <div class="divider"></div>

        <p class="body-text">
            We received a request to reset the password for your <strong>onsetway ERB</strong> account.
            Click the button below to securely choose a new password.
        </p>

        <div class="cta-wrap">
            <a href="{{ $resetUrl }}" class="cta-btn">Reset Password</a>
        </div>

        <!-- Security notice -->
        <div class="security-box">
            <div class="security-icon">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#BDD8E9" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                </svg>
            </div>
            <div class="security-text">
                <strong>Security Notice</strong>
                If you did not request a password reset, no action is required — your account remains safe and secure.
            </div>
        </div>
    </div>

    <!-- FOOTER -->
    <div class="footer">
        <div class="footer-brand">onsetway ERB</div>
        <div class="footer-dots">
            <div class="dot" style="background:#001D39;"></div>
            <div class="dot" style="background:#0A4174;"></div>
            <div class="dot" style="background:#49769F;"></div>
            <div class="dot" style="background:#4E8EA2;"></div>
            <div class="dot" style="background:#6EA2B3;"></div>
            <div class="dot" style="background:#7BBDE8;"></div>
            <div class="dot" style="background:#BDD8E9;"></div>
        </div>
        <div class="footer-copy">© {{ date('Y') }} All rights reserved.</div>
    </div>

</div>
</div>
</body>
</html>