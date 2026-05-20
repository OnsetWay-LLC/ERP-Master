<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Verify Email</title>
</head>
<body style="margin:0; padding:0; background-color:#001D39; font-family: Arial, sans-serif;">

  <table width="100%" cellpadding="0" cellspacing="0" border="0"
    style="background-color:#0A3259; padding:50px 0; min-height:100vh;">
    <tr>
      <td align="center">
        <table width="520" cellpadding="0" cellspacing="0" border="0"
          style="background-color:#ffffff; border-radius:12px; overflow:hidden;">

          <!-- TOP ACCENT LINE -->
          <tr>
            <td style="background: linear-gradient(90deg, #0C447C, #378ADD, #85B7EB); height:3px; padding:0;"></td>
          </tr>

          <!-- HEADER -->
          <tr>
            <td style="background-color:#ffffff; padding:32px 36px 28px; border-bottom:1px solid #f0f0f0;">
              <table cellpadding="0" cellspacing="0" border="0">
                <tr>
                  <td style="vertical-align:middle;">
                    <div style="width:36px; height:36px; border-radius:8px; background-color:#E6F1FB;
                      display:inline-block; text-align:center; line-height:36px; font-size:16px;">✦</div>
                  </td>
                  <td style="padding-left:12px; vertical-align:middle;">
                    <p style="margin:0; font-size:14px; font-weight:600; color:#042C53;">onsetway ERB</p>
                    <p style="margin:0; font-size:11px; color:#378ADD; letter-spacing:0.08em;">Your path to excellence</p>
                  </td>
                </tr>
              </table>
            </td>
          </tr>

          <!-- BODY -->
          <tr>
            <td style="background-color:#ffffff; padding:36px 36px 32px;">

              <!-- Label -->
              <p style="margin:0 0 6px; font-size:11px; letter-spacing:0.14em;
                color:#185FA5; text-transform:uppercase; font-weight:500;">
                Account Activation
              </p>

              <!-- Heading -->
              <h2 style="margin:0 0 20px; font-size:26px; font-weight:500; color:#042C53; line-height:1.25;">
                Verify your email address
              </h2>

              <!-- Divider -->
              <div style="width:32px; height:2px; background-color:#378ADD;
                border-radius:1px; margin-bottom:24px;"></div>

              <!-- Body text -->
              <p style="margin:0 0 16px; font-size:14px; line-height:1.75; color:#378ADD; font-weight:300;">
                Welcome to <strong style="color:#042C53; font-weight:500;">onsetway ERB</strong>.
                We're glad to have you on board.
              </p>
              <p style="margin:0 0 28px; font-size:14px; line-height:1.75; color:#378ADD; font-weight:300;">
                To complete your registration, please confirm your email address
                by clicking the button below.
              </p>

              <!-- CTA Button -->
              <table cellpadding="0" cellspacing="0" border="0" style="margin-bottom:28px;">
                <tr>
                  <td>
                    <a href="{{ $verificationUrl }}"
                      style="display:inline-block;
                        background-color:#185FA5;
                        color:#ffffff;
                        text-decoration:none;
                        padding:12px 28px;
                        font-size:12px;
                        font-weight:500;
                        letter-spacing:0.12em;
                        text-transform:uppercase;
                        border-radius:50px;">
                      Confirm email address
                    </a>
                  </td>
                </tr>
              </table>

              <!-- Info box -->
              <table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom:20px;">
                <tr>
                  <td style="background-color:#E6F1FB;
                    border-left:2px solid #378ADD;
                    border-radius:0 8px 8px 0;
                    padding:12px 16px;">
                    <p style="margin:0; font-size:13px; line-height:1.6; color:#185FA5;">
                      This verification link expires in
                      <strong style="color:#0C447C;">24 hours</strong>.
                    </p>
                  </td>
                </tr>
              </table>

              <p style="margin:0; font-size:12px; color:#85B7EB; line-height:1.6;">
                If you did not create an account, no further action is required.
              </p>
            </td>
          </tr>

          <!-- FOOTER -->
          <tr>
            <td style="background-color:#ffffff; padding:20px 36px;
              border-top:1px solid #f0f0f0;">
              <table width="100%" cellpadding="0" cellspacing="0" border="0">
                <tr>
                  <td>
                    <p style="margin:0; font-size:11px; color:#85B7EB;">
                      © {{ date('Y') }} onsetway ERB. All rights reserved.
                    </p>
                  </td>
                  <td align="right">
                    <span style="display:inline-block; width:5px; height:5px; border-radius:50%; background-color:#042C53; margin:0 2px;"></span>
                    <span style="display:inline-block; width:5px; height:5px; border-radius:50%; background-color:#0C447C; margin:0 2px;"></span>
                    <span style="display:inline-block; width:5px; height:5px; border-radius:50%; background-color:#185FA5; margin:0 2px;"></span>
                    <span style="display:inline-block; width:5px; height:5px; border-radius:50%; background-color:#378ADD; margin:0 2px;"></span>
                    <span style="display:inline-block; width:5px; height:5px; border-radius:50%; background-color:#85B7EB; margin:0 2px;"></span>
                  </td>
                </tr>
              </table>
            </td>
          </tr>

          <!-- BOTTOM ACCENT LINE -->
          <tr>
            <td style="background: linear-gradient(90deg, #85B7EB, #378ADD, #0C447C); height:3px; padding:0;"></td>
          </tr>

        </table>
      </td>
    </tr>
  </table>

</body>
</html>