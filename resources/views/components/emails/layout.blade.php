@props(['title', 'accent' => null, 'eyebrow', 'preheader', 'footer', 'lang' => 'es'])
@php
    $siteUrl = app(\App\Services\SiteSeo::class)->origin();
    $font = "'Space Grotesk',Helvetica,Arial,sans-serif";
@endphp
<!DOCTYPE html>
<html lang="{{ $lang }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light">
    <title>{{ $title }} · Feat.</title>
</head>
<body style="margin:0;padding:0;background-color:#F7F3EA;color:#000000;font-family:{{ $font }};-webkit-text-size-adjust:100%;">
    <div style="display:none;font-size:1px;line-height:1px;color:#F7F3EA;max-height:0;max-width:0;opacity:0;overflow:hidden;mso-hide:all;">{{ $preheader }}</div>
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color:#F7F3EA;">
        <tr>
            <td align="center" style="padding:36px 12px;">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="max-width:600px;background-color:#FFFFFF;border:1px solid #E4DECE;border-radius:24px;overflow:hidden;">
                    <tr>
                        <td style="padding:26px 32px;background-color:#000000;">
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
                                <tr>
                                    <td valign="middle"><a href="{{ $siteUrl }}" style="text-decoration:none;"><img src="{{ asset('assets/email/feat-white.png') }}" width="76" height="40" alt="Feat." style="display:block;border:0;width:76px;height:40px;"></a></td>
                                    <td valign="middle" align="right" style="color:#9E9E9E;font-family:{{ $font }};font-size:10px;line-height:16px;letter-spacing:3px;text-transform:uppercase;">Collab Driven Growth</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:0;font-size:0;line-height:0;">
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
                                <tr>
                                    @foreach (['#2D48F2', '#FB6C26', '#15F7A8', '#FDDB02', '#D48BFD'] as $color)
                                        <td height="6" style="height:6px;background-color:{{ $color }};font-size:0;line-height:0;">&nbsp;</td>
                                    @endforeach
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:40px 32px 8px;">
                            <p style="margin:0 0 14px;color:#6D665A;font-family:{{ $font }};font-size:11px;font-weight:600;line-height:18px;letter-spacing:3px;text-transform:uppercase;">{{ $eyebrow }}</p>
                            <h1 style="margin:0;color:#000000;font-family:{{ $font }};font-size:34px;font-weight:700;line-height:38px;letter-spacing:-1px;">{{ $title }}@if ($accent)<br><span style="color:#FB6C26;">{{ $accent }}</span>@endif</h1>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:16px 32px 40px;color:#1F1F1F;font-family:{{ $font }};font-size:15px;line-height:25px;overflow-wrap:anywhere;word-break:break-word;">{{ $slot }}</td>
                    </tr>
                    <tr>
                        <td style="padding:24px 32px;background-color:#000000;">
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
                                <tr>
                                    <td valign="top" style="color:#9E9E9E;font-family:{{ $font }};font-size:12px;line-height:19px;">
                                        <p style="margin:0 0 6px;color:#FFFFFF;font-weight:600;">Feat. — Collab Driven Growth</p>
                                        <p style="margin:0;">Guatemala · Costa Rica · El Salvador · USA</p>
                                        <p style="margin:12px 0 0;">{{ $footer }}</p>
                                    </td>
                                    <td valign="top" align="right" style="padding-left:16px;"><a href="{{ $siteUrl }}" style="display:inline-block;color:#FDDB02;font-family:{{ $font }};font-size:12px;font-weight:600;line-height:19px;text-decoration:none;white-space:nowrap;">{{ parse_url($siteUrl, PHP_URL_HOST) }} ↗</a></td>
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
