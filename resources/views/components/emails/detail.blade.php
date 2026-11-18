@props(['label'])
<tr>
    <td style="padding:14px 20px;border-bottom:1px solid #E4DECE;">
        <p style="margin:0 0 4px;color:#6D665A;font-size:10px;line-height:16px;font-weight:600;letter-spacing:2px;text-transform:uppercase;">{{ $label }}</p>
        <div style="color:#000000;font-size:15px;line-height:23px;overflow-wrap:anywhere;word-break:break-word;">{{ $slot }}</div>
    </td>
</tr>
