<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"><style>body{font-family:sans-serif;background:#f9fafb;margin:0;padding:20px}
.container{max-width:560px;margin:0 auto;background:#fff;border-radius:12px;overflow:hidden;border:1px solid #e5e7eb}
.header{background:#10b981;padding:24px;text-align:center;color:#fff}
.header h1{margin:0;font-size:20px}
.body{padding:24px}
.btn{display:inline-block;background:#10b981;color:#fff;text-decoration:none;padding:12px 24px;border-radius:8px;font-weight:600;margin:16px 0}
.footer{padding:16px 24px;border-top:1px solid #f3f4f6;text-align:center;font-size:12px;color:#9ca3af}
</style></head>
<body>
<div class="container">
    <div class="header">
        <h1>⚡ ChatPulse</h1>
    </div>
    <div class="body">
        <h2 style="margin:0 0 8px;font-size:16px;color:#111827">{{ $subject }}</h2>
        <p style="color:#6b7280;font-size:14px;line-height:1.6">{{ $bodyText }}</p>
        @if(!empty($data['conversation_id']))
        <a href="{{ url('/chat/' . $data['conversation_id']) }}" class="btn">Open ChatPulse</a>
        @else
        <a href="{{ url('/chat') }}" class="btn">Open ChatPulse</a>
        @endif
    </div>
    <div class="footer">
        <a href="{{ url('/settings') }}" style="color:#9ca3af">Unsubscribe from emails</a>
    </div>
</div>
</body>
</html>
