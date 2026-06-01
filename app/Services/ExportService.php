<?php
namespace App\Services;

use App\Models\Conversation;
use App\Models\Message;
use Barryvdh\DomPDF\Facade\Pdf;
use League\Csv\Writer;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class ExportService
{
    public function exportToCsv(Conversation $conversation, ?string $from = null, ?string $to = null): string
    {
        $messages = $this->getMessages($conversation, $from, $to);
        $filename = 'exports/' . Str::uuid() . '.csv';

        $csv = Writer::createFromString();
        $csv->insertOne(['id','sender','message','type','sent_at','attachments']);

        foreach ($messages as $msg) {
            $attachments = $msg->attachments->pluck('original_name')->implode(', ');
            $csv->insertOne([
                $msg->id,
                $msg->user?->name ?? 'System',
                $msg->body ?? '',
                $msg->type,
                $msg->created_at?->toDateTimeString(),
                $attachments,
            ]);
        }

        Storage::put($filename, $csv->toString());
        return $filename;
    }

    public function exportToPdf(Conversation $conversation, ?string $from = null, ?string $to = null): string
    {
        $messages = $this->getMessages($conversation, $from, $to);
        $filename = 'exports/' . Str::uuid() . '.pdf';

        $pdf = Pdf::loadView('exports.chat', [
            'conversation' => $conversation,
            'messages' => $messages,
            'exportedAt' => now(),
        ]);

        Storage::put($filename, $pdf->output());
        return $filename;
    }

    private function getMessages(Conversation $conversation, ?string $from, ?string $to)
    {
        $query = $conversation->messages()->with(['user', 'attachments'])->orderBy('created_at');
        if ($from) $query->where('created_at', '>=', $from);
        if ($to) $query->where('created_at', '<=', $to);
        return $query->get();
    }
}
