<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Leave Request {{ ucfirst($leave->status) }}</title>
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

              <p style="margin:0 0 6px; font-size:11px; letter-spacing:0.14em;
                color:#185FA5; text-transform:uppercase; font-weight:500;">
                Leave Management
              </p>

              <h2 style="margin:0 0 8px; font-size:26px; font-weight:500; color:#042C53; line-height:1.25;">
                Leave Request
                <span style="color:{{ $leave->status === 'approved' ? '#0f9d58' : '#d93025' }};">
                  {{ ucfirst($leave->status) }}
                </span>
              </h2>

              <div style="width:32px; height:2px; background-color:#378ADD;
                border-radius:1px; margin-bottom:24px;"></div>

              <p style="margin:0 0 24px; font-size:14px; line-height:1.75; color:#378ADD; font-weight:300;">
                Hello <strong style="color:#042C53; font-weight:500;">{{ $employee->full_name_en ?? $employee->full_name_ar }}</strong>,
                your leave request has been reviewed and a decision has been made.
              </p>

              <!-- Details Box -->
              <table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom:24px;">
                <tr>
                  <td style="background-color:#E6F1FB; border-left:2px solid #378ADD;
                    border-radius:0 8px 8px 0; padding:16px 20px;">
                    <table width="100%" cellpadding="0" cellspacing="0" border="0"
                      style="font-size:13px; border-collapse:collapse;">
                      <tr>
                        <td style="padding:6px 0; color:#185FA5; width:45%;">Leave Type</td>
                        <td style="padding:6px 0; color:#042C53; font-weight:500;">{{ $leave->leave_type }}</td>
                      </tr>
                      <tr>
                        <td style="padding:6px 0; color:#185FA5; border-top:1px solid #cce0f5;">From</td>
                        <td style="padding:6px 0; color:#042C53; font-weight:500; border-top:1px solid #cce0f5;">{{ $leave->from_date->format('Y-m-d') }}</td>
                      </tr>
                      <tr>
                        <td style="padding:6px 0; color:#185FA5; border-top:1px solid #cce0f5;">To</td>
                        <td style="padding:6px 0; color:#042C53; font-weight:500; border-top:1px solid #cce0f5;">{{ $leave->to_date->format('Y-m-d') }}</td>
                      </tr>
                      <tr>
                        <td style="padding:6px 0; color:#185FA5; border-top:1px solid #cce0f5;">Days</td>
                        <td style="padding:6px 0; color:#042C53; font-weight:500; border-top:1px solid #cce0f5;">{{ $leave->days_count }}</td>
                      </tr>
                      <tr>
                        <td style="padding:6px 0; color:#185FA5; border-top:1px solid #cce0f5;">Status</td>
                        <td style="padding:6px 0; font-weight:500; border-top:1px solid #cce0f5;
                          color:{{ $leave->status === 'approved' ? '#0f9d58' : '#d93025' }};">
                          {{ ucfirst($leave->status) }}
                        </td>
                      </tr>
                    </table>
                  </td>
                </tr>
              </table>

              <!-- Salary Deduction Box -->
              @if((float) $leave->salary_deduction_amount > 0)
              <table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom:20px;">
                <tr>
                  <td style="background-color:#fff8ec; border-left:2px solid #e6a817;
                    border-radius:0 8px 8px 0; padding:12px 16px;">
                    <p style="margin:0; font-size:13px; line-height:1.6; color:#7a5200;">
                      Salary deduction of
                      <strong style="color:#4a3000;">{{ $leave->salary_deduction_amount }}</strong>
                      will be applied.
                    </p>
                  </td>
                </tr>
              </table>
              @endif

              <!-- Description Box -->
              @if($leave->description)
              <table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom:24px;">
                <tr>
                  <td style="background-color:#f0f4f8; border-left:2px solid #85B7EB;
                    border-radius:0 8px 8px 0; padding:12px 16px;">
                    <p style="margin:0; font-size:13px; line-height:1.6; color:#185FA5;">
                      <strong style="color:#0C447C;">Description:</strong> {{ $leave->description }}
                    </p>
                  </td>
                </tr>
              </table>
              @endif

              <p style="margin:0; font-size:12px; color:#85B7EB; line-height:1.6;">
                If you have any questions, please contact HR directly.
              </p>
            </td>
          </tr>

          <!-- FOOTER -->
          <tr>
            <td style="background-color:#ffffff; padding:20px 36px; border-top:1px solid #f0f0f0;">
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