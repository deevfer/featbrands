@props(['href'])
<table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin:28px 0 0;">
    <tr><td style="background-color:#FDDB02;border-radius:100px;mso-padding-alt:14px 26px;">
        <a href="{{ $href }}" style="display:inline-block;padding:14px 26px;color:#000000;font-family:'Space Grotesk',Helvetica,Arial,sans-serif;font-size:14px;font-weight:600;text-decoration:none;">{{ $slot }} ↗</a>
    </td></tr>
</table>
