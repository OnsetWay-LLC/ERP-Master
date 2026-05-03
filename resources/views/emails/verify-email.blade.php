<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Verify Email</title>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;600;700&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
</head>
<body style="margin:0; padding:0; background-color:#001D39; font-family:'DM Sans', Arial, sans-serif;">

  <table width="100%" cellpadding="0" cellspacing="0" border="0"
    style="background: linear-gradient(160deg, #001D39 0%, #0A4174 50%, #49769F 100%); padding:50px 0; min-height:100vh;">
    <tr>
      <td align="center">
        <table width="580" cellpadding="0" cellspacing="0" border="0"
          style="border-radius:20px; overflow:hidden; box-shadow: 0 30px 80px rgba(0,0,0,0.5);">

          <!-- TOP ACCENT LINE -->
          <tr>
            <td style="background: linear-gradient(90deg, #001D39, #4E8EA2, #BDD8E9, #6EA2B3, #001D39); height:4px; padding:0;"></td>
          </tr>

          <!-- HEADER -->
          <tr>
            <td style="background: linear-gradient(135deg, #001D39 0%, #0A4174 100%); padding:45px 40px 35px; text-align:center; position:relative;">

              <!-- Decorative circles -->
              <div style="position:absolute; top:-30px; right:-30px; width:160px; height:160px;
                border-radius:50%; background:rgba(78,142,162,0.12); pointer-events:none;"></div>
              <div style="position:absolute; bottom:-20px; left:-20px; width:100px; height:100px;
                border-radius:50%; background:rgba(189,216,233,0.08); pointer-events:none;"></div>

              <!-- Logo Icon -->
              <div style="display:inline-block; background:linear-gradient(135deg, #4E8EA2, #7BBDE8);
                width:56px; height:56px; border-radius:16px; margin-bottom:18px;
                line-height:56px; font-size:26px; box-shadow:0 8px 24px rgba(78,142,162,0.4);">
                ✦
              </div>

              <h1 style="margin:0 0 8px; font-family:'Cormorant Garamond', serif; color:#BDD8E9;
                font-size:30px; font-weight:700; letter-spacing:0.04em;">
                onsetway ERB
              </h1>
              <p style="margin:0; color:#6EA2B3; font-size:12px; letter-spacing:0.18em;
                text-transform:uppercase; font-weight:300;">
                Your Path to Excellence
              </p>
            </td>
          </tr>

          <!-- BODY -->
          <tr>
            <td style="background-color:#ffffff; padding:50px 45px 40px;">

              <!-- Label -->
              <p style="margin:0 0 12px; font-size:11px; letter-spacing:0.22em;
                color:#4E8EA2; text-transform:uppercase; font-weight:500;">
                Account Activation
              </p>

              <!-- Heading -->
              <h2 style="margin:0 0 24px; font-family:'Cormorant Garamond', serif;
                font-size:38px; line-height:1.2; color:#001D39; font-weight:700;">
                Verify Your<br>
                <span style="color:#0A4174;">Email Address</span>
              </h2>

              <!-- Divider -->
              <div style="width:48px; height:3px; border-radius:2px;
                background:linear-gradient(90deg, #4E8EA2, #BDD8E9); margin-bottom:28px;"></div>

              <!-- Body text -->
              <p style="margin:0 0 18px; font-size:15.5px; line-height:1.85; color:#49769F; font-weight:300;">
                Welcome to <strong style="color:#0A4174; font-weight:500;">onsetway ERB</strong>.
                We're glad to have you on board.
              </p>
              <p style="margin:0 0 36px; font-size:15.5px; line-height:1.85; color:#49769F; font-weight:300;">
                To complete your registration and activate your account, please confirm
                your email address by clicking the button below.
              </p>

              <!-- CTA Button -->
              <table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom:36px;">
                <tr>
                  <td align="center">
                    <a href="{{ $verificationUrl }}"
                      style="display:inline-block;
                        background: linear-gradient(135deg, #0A4174 0%, #4E8EA2 100%);
                        color:#ffffff;
                        text-decoration:none;
                        padding:17px 40px;
                        font-size:12px;
                        font-weight:500;
                        letter-spacing:0.16em;
                        text-transform:uppercase;
                        border-radius:50px;
                        box-shadow: 0 10px 30px rgba(10,65,116,0.35);">
                      Confirm Email Address
                    </a>
                  </td>
                </tr>
              </table>

              <!-- Info box -->
              <table width="100%" cellpadding="0" cellspacing="0" border="0">
                <tr>
                  <td style="background: linear-gradient(135deg, #f0f7fc, #e8f4fa);
                    border-left:3px solid #7BBDE8;
                    border-radius:0 10px 10px 0;
                    padding:16px 20px;">
                    <p style="margin:0; font-size:13px; line-height:1.7; color:#49769F;">
                      ⏱ &nbsp;This verification link will expire in <strong style="color:#0A4174;">24 hours</strong>.
                    </p>
                  </td>
                </tr>
              </table>

              <p style="margin:24px 0 0; font-size:13px; line-height:1.7; color:#6EA2B3; font-weight:300;">
                If you did not create an account, no further action is required.
              </p>
            </td>
          </tr>

          <!-- FOOTER -->
          <tr>
            <td style="background: linear-gradient(135deg, #001D39 0%, #0A4174 100%);
              padding:24px 40px; text-align:center;">
              <p style="margin:0; font-size:11px; color:#49769F; letter-spacing:0.08em;">
                © {{ date('Y') }} onsetway ERB. All rights reserved.
              </p>
              <div style="margin-top:14px; display:inline-block;">
                <span style="display:inline-block; width:6px; height:6px; border-radius:50%;
                  background:#001D39; margin:0 3px;"></span>
                <span style="display:inline-block; width:6px; height:6px; border-radius:50%;
                  background:#0A4174; margin:0 3px;"></span>
                <span style="display:inline-block; width:6px; height:6px; border-radius:50%;
                  background:#49769F; margin:0 3px;"></span>
                <span style="display:inline-block; width:6px; height:6px; border-radius:50%;
                  background:#4E8EA2; margin:0 3px;"></span>
                <span style="display:inline-block; width:6px; height:6px; border-radius:50%;
                  background:#6EA2B3; margin:0 3px;"></span>
                <span style="display:inline-block; width:6px; height:6px; border-radius:50%;
                  background:#7BBDE8; margin:0 3px;"></span>
                <span style="display:inline-block; width:6px; height:6px; border-radius:50%;
                  background:#BDD8E9; margin:0 3px;"></span>
              </div>
            </td>
          </tr>

          <!-- BOTTOM ACCENT LINE -->
          <tr>
            <td style="background: linear-gradient(90deg, #BDD8E9, #4E8EA2, #001D39, #4E8EA2, #BDD8E9); height:4px; padding:0;"></td>
          </tr>

        </table>
      </td>
    </tr>
  </table>

</body>
</html>