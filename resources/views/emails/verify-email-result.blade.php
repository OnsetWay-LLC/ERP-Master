<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Verification — Onsetway ERB</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: Arial, sans-serif;
            background-color: #0A3259;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 50px 20px;
        }

        .card-wrapper { width: 100%; max-width: 520px; }

        .card {
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid #1a4a7a;
        }

        .accent-top {
            height: 3px;
            background: linear-gradient(90deg, #0C447C, #378ADD, #85B7EB);
        }

        .header {
            background-color: #ffffff;
            padding: 28px 36px 24px;
            border-bottom: 1px solid #f0f0f0;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .logo-box {
            width: 36px; height: 36px;
            border-radius: 8px;
            background-color: #E6F1FB;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .header-text p:first-child {
            font-size: 14px;
            font-weight: 600;
            color: #042C53;
        }

        .header-text p:last-child {
            font-size: 11px;
            color: #378ADD;
            letter-spacing: 0.08em;
        }

        .body {
            background: #ffffff;
            padding: 36px 36px 32px;
            text-align: center;
        }

        .status-icon {
            width: 68px; height: 68px;
            border-radius: 50%;
            margin: 0 auto 22px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .status-icon.success { background-color: #EAF3DE; }
        .status-icon.warning { background-color: #FAEEDA; }
        .status-icon.error   { background-color: #FCEBEB; }

        .label {
            font-size: 11px;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            font-weight: 500;
            margin-bottom: 6px;
        }

        .label.success { color: #185FA5; }
        .label.warning { color: #BA7517; }
        .label.error   { color: #A32D2D; }

        .heading {
            font-size: 24px;
            font-weight: 500;
            color: #042C53;
            line-height: 1.25;
            margin-bottom: 16px;
        }

        .divider {
            width: 28px; height: 2px;
            border-radius: 1px;
            margin: 0 auto 20px;
        }

        .divider.success { background-color: #3B6D11; }
        .divider.warning { background-color: #EF9F27; }
        .divider.error   { background-color: #E24B4A; }

        .message-text {
            font-size: 14px;
            line-height: 1.75;
            color: #378ADD;
            font-weight: 300;
            margin-bottom: 20px;
        }

        .info-box {
            border-left: 2px solid;
            border-radius: 0 8px 8px 0;
            padding: 12px 16px;
            font-size: 13px;
            line-height: 1.7;
            text-align: left;
            margin-bottom: 24px;
        }

        .info-box.success { background-color: #EAF3DE; border-color: #3B6D11; color: #27500A; }
        .info-box.warning { background-color: #FAEEDA; border-color: #BA7517; color: #633806; }
        .info-box.error   { background-color: #FCEBEB; border-color: #E24B4A; color: #791F1F; }

        .btn {
            display: inline-block;
            background-color: #185FA5;
            color: #ffffff;
            text-decoration: none;
            padding: 12px 28px;
            font-size: 12px;
            font-weight: 500;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            border-radius: 50px;
            border: none;
            cursor: pointer;
            font-family: Arial, sans-serif;
        }

        .footer {
            background-color: #ffffff;
            padding: 18px 36px;
            border-top: 1px solid #f0f0f0;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .footer p { font-size: 11px; color: #85B7EB; }

        .dots { display: flex; gap: 4px; align-items: center; }

        .dot {
            display: inline-block;
            width: 5px; height: 5px;
            border-radius: 50%;
        }

        .accent-bottom {
            height: 3px;
            background: linear-gradient(90deg, #85B7EB, #378ADD, #0C447C);
        }

        /* SweetAlert overrides */
        .swal2-popup { font-family: Arial, sans-serif !important; border-radius: 12px !important; }
    </style>
</head>
<body>

<div class="card-wrapper">
    <div class="card">

        <div class="accent-top"></div>

        <div class="header">
            <div class="logo-box">
                <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                    <path d="M8 1l1.8 3.6L14 5.5l-3 2.9.7 4.1L8 10.4l-3.7 2.1.7-4.1L2 5.5l4.2-.9L8 1z" fill="#185FA5"/>
                </svg>
            </div>
            <div class="header-text">
                <p>onsetway ERB</p>
                <p>Your path to excellence</p>
            </div>
        </div>

        <div class="body" id="bodyContent"></div>

        <div class="footer">
            <p>© {{ date('Y') }} onsetway ERB. All rights reserved.</p>
            <div class="dots">
                <span class="dot" style="background:#042C53"></span>
                <span class="dot" style="background:#0C447C"></span>
                <span class="dot" style="background:#185FA5"></span>
                <span class="dot" style="background:#378ADD"></span>
                <span class="dot" style="background:#85B7EB"></span>
            </div>
        </div>

        <div class="accent-bottom"></div>

    </div>
</div>

<script>
    const MESSAGE_MAP = {
        'Email verified successfully.': {
            type: 'success',
            iconSvg: '<svg width="28" height="28" viewBox="0 0 24 24" fill="none"><path d="M5 12l5 5L19 7" stroke="#3B6D11" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>',
            label: 'Account Activated',
            heading: 'Email Verified',
            body: 'Your email address has been confirmed and your <strong style="color:#042C53">Onsetway ERB</strong> account is now fully active.',
            swalType: 'success',
            swalTitle: 'Verified!',
            swalText: 'Your email has been verified successfully.',
        },
        'Email already verified.': {
            type: 'warning',
            iconSvg: '<svg width="28" height="28" viewBox="0 0 24 24" fill="none"><path d="M12 9v4M12 17h.01" stroke="#854F0B" stroke-width="2.5" stroke-linecap="round"/><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z" stroke="#854F0B" stroke-width="2"/></svg>',
            label: 'Already Verified',
            heading: 'Already Confirmed',
            body: 'Your email address was already verified. No further action is needed.',
            infoText: 'You can <strong>log in</strong> directly to your account.',
            btnText: 'Go to Login',
            btnHref: '/login',
            swalType: 'info',
            swalTitle: 'Already Verified',
            swalText: 'Your email was already verified',
        },
        'Invalid verification link.': {
            type: 'error',
            iconSvg: '<svg width="28" height="28" viewBox="0 0 24 24" fill="none"><path d="M18 6L6 18M6 6l12 12" stroke="#A32D2D" stroke-width="2.5" stroke-linecap="round"/></svg>',
            label: 'Verification Failed',
            heading: 'Invalid Link',
            body: 'This verification link is invalid or may have expired. Please request a new one.',
            infoText: 'Links expire after <strong>24 hours</strong>. Please resend the verification email.',
            btnText: 'Resend Verification',
            btnHref: '/resend-verification',
            swalType: 'error',
            swalTitle: 'Invalid Link',
            swalText: 'This verification link is invalid or has expired.',
        },
    };

    const apiMessage = @json($message ?? 'Invalid verification link.');

    const config = MESSAGE_MAP[apiMessage] || {
        type: 'error',
        iconSvg: '<svg width="28" height="28" viewBox="0 0 24 24" fill="none"><path d="M18 6L6 18M6 6l12 12" stroke="#A32D2D" stroke-width="2.5" stroke-linecap="round"/></svg>',
        label: 'Error',
        heading: 'Something Went Wrong',
        body: apiMessage,
        infoText: 'Please try again or contact support.',
        btnText: 'Go Home',
        btnHref: '/',
        swalType: 'error',
        swalTitle: 'Error',
        swalText: apiMessage,
    };

  document.getElementById('bodyContent').innerHTML = `
 <div class="status-icon ${config.type}">${config.iconSvg}</div>
        <p class="label ${config.type}">${config.label}</p>
        <h2 class="heading">${config.heading}</h2>
        <div class="divider ${config.type}"></div>
        <p class="message-text">${config.body}</p>
        ${config.infoText ? `<div class="info-box ${config.type}">${config.infoText}</div>` : ''}
        ${config.btnText ? `<a href="${config.btnHref}" class="btn">${config.btnText}</a>` : ''}
    `;

    Swal.fire({
        icon: config.swalType,
        title: config.swalTitle,
        text: config.swalText,
        confirmButtonColor: '#185FA5',
        confirmButtonText: 'OK',
        background: '#ffffff',
        color: '#042C53',
        borderRadius: '12px',
    });
</script>

</body>
</html>