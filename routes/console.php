<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('presence:cleanup')->everyMinute();
Schedule::command('messages:send-scheduled')->everyMinute();
Schedule::command('presence:clear-expired-status')->hourly();
Schedule::command('mail:send-digest')->dailyAt('09:00');
Schedule::command('storage:cleanup-exports')->daily();
Schedule::command('guests:anonymize-old')->dailyAt('02:00');
Schedule::command('horizon:snapshot')->everyFiveMinutes();
Schedule::command('queue:prune-failed', ['--hours=48'])->daily();
