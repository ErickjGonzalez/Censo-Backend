<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta http-equiv="X-UA-Compatible" content="ie=edge" />
  <title>{{ $mailSubject }}</title>
</head>

<body style="margin:0; padding:0; background:#eef2f7; font-family: Arial, Helvetica, sans-serif; color:#111827;">
  <div style="display:none; font-size:1px; line-height:1px; max-height:0; max-width:0; opacity:0; overflow:hidden;">
    {{ $mailSubject }}
  </div>

  <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background:#eef2f7; padding:28px 0;">
    <tr>
      <td align="center" style="padding:0 12px;">

        <table role="presentation" width="640" cellspacing="0" cellpadding="0" border="0"
          style="width:640px; max-width:640px; background:#ffffff; border:1px solid #e5e7eb; border-radius:12px; overflow:hidden; box-shadow:0 10px 24px rgba(17,24,39,.06);">

          <tr>
            <td style="background:#7a0019; height:20px; line-height:10px; font-size:0;">&nbsp;</td>
          </tr>

          <tr>
            <td style="padding:18px 26px 12px 26px; text-align:center;">
              <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
                <tr>
                  <td align="center" style="vertical-align:middle;">
                  </td>
                </tr>
              </table>
            </td>
          </tr>

          <tr>
            <td style="padding:0 26px;">
              <div style="height:2px; background:#c8a24a; line-height:2px; font-size:0;">&nbsp;</div>
            </td>
          </tr>

          <tr>
            <td style="padding:16px 26px 0 26px; text-align:center;">
              <h1 style="margin:0; font-size:20px; line-height:28px; color:#111827; font-weight:800; text-align:center;">
                {{ $mailSubject }}
              </h1>
            </td>
          </tr>
          <tr>
            <td style="padding:18px 26px 6px 26px; text-align:center;">
              <p style="margin:0 0 14px 0; font-size:15px; line-height:23px; color:#374151; text-align:center;">
                {!! nl2br(e($mailMessage)) !!}
              </p>
              <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0"
                style="background:#fbfbfc; border:1px solid #e5e7eb; border-radius:10px; margin:12px 0 16px 0;">
                <tr>
              </table>

              <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin:0 auto 14px auto;">
                <tr>
                  <td align="center" bgcolor="#7a0019" style="border-radius:8px;">
                    <a href="{{ $link }}"
                      style="display:inline-block; padding:12px 22px; font-size:14px; font-weight:800; color:#ffffff; text-decoration:none; border-radius:8px; letter-spacing:.2px;">
                      Acceder al Sistema
                    </a>
                  </td>
                </tr>
              </table>

              <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0"
                style="background:#fff7ed; border:1px solid #fed7aa; border-radius:10px; margin:0 0 6px 0;">
                <tr>
                  <td style="padding:12px 14px; font-size:12px; line-height:18px; color:#7c2d12; text-align:center;">
                    <strong>Importante:</strong> Si no solicitaste esta acción, ignora este mensaje. No compartas el enlace con terceros.
                  </td>
                </tr>
              </table>
            </td>
          </tr>

          <tr>
            <td style="padding:16px 26px; background:#f9fafb; border-top:1px solid #e5e7eb; text-align:center;">
              <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
                <tr>
                  <td align="center" style="font-size:12px; color:#6b7280; line-height:18px;">
                    © {{ date('Y') }} Gobierno del Estado de Hidalgo.
                  </td>
                </tr>
              </table>
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
</body>
</html>
