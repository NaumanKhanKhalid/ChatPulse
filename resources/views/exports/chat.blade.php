<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"><title>Chat Export</title>
<style>body{font-family:sans-serif;font-size:12px;color:#333}
.header{border-bottom:2px solid #10b981;padding-bottom:8px;margin-bottom:16px}
.message{margin:8px 0;padding:8px;border-left:3px solid #e5e7eb}
.meta{color:#9ca3af;font-size:10px}</style>
</head>
<body>
<div class="header">
    <h2 style="margin:0;color:#10b981">ChatPulse</h2>
    <p style="margin:4px 0 0;color:#6b7280">Conversation: {{ $conversation->name ?? 'Direct Message' }}</p>
    <p style="margin:2px 0 0;color:#9ca3af;font-size:10px">Exported: {{ $exportedAt->format('M j, Y g:i A') }}</p>
</div>
@foreach($messages as $msg)
<div class="message">
    <div class="meta">{{ $msg->user?->name ?? 'System' }} • {{ $msg->created_at?->format('M j, Y g:i A') }}</div>
    <p style="margin:4px 0 0">{{ $msg->body }}</p>
</div>
@endforeach
</body>
</html>
