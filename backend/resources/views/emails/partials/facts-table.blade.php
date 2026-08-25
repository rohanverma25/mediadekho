{{-- Expects $rows: array<string, string|null> of label => value pairs. --}}
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:16px 0;">
    @foreach ($rows as $label => $value)
        @continue(blank($value))
        <tr>
            <td style="padding:8px 0; border-bottom:1px solid #f1f5f9; font-size:12px; color:#94a3b8; font-weight:bold; text-transform:uppercase; width:140px; vertical-align:top;">{{ $label }}</td>
            <td style="padding:8px 0; border-bottom:1px solid #f1f5f9; font-size:13px; color:#1e293b;">{{ $value }}</td>
        </tr>
    @endforeach
</table>
