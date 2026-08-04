<?php

use App\Http\Controllers\Auth\GuestLoginController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\CallController;
use App\Http\Controllers\ConversationController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PeopleController;
use App\Http\Controllers\PinController;
use App\Http\Controllers\PollController;
use App\Http\Controllers\PresenceController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReactionController;
use App\Http\Controllers\ScheduledMessageController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\StatusController;
use Illuminate\Support\Facades\Route;

// Auth routes (guests)
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'show'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->middleware('throttle:login');
    Route::get('/register', [RegisterController::class, 'show'])->name('register');
    Route::post('/register', [RegisterController::class, 'store']);
    Route::get('/guest-login', [GuestLoginController::class, 'show'])->name('guest-login');
    Route::post('/guest-login', [GuestLoginController::class, 'store'])->middleware('throttle:guest-login');
});

// Logout
Route::post('/logout', LogoutController::class)->name('logout')->middleware('auth');

// Root redirect
Route::get('/', fn() => redirect()->route('chat.index'));

// Main authenticated routes
Route::middleware(['auth', 'banned.user'])->group(function () {

    // Chat
    Route::get('/chat', [ConversationController::class, 'index'])->name('chat.index');
    Route::get('/chat/{conversation}', [ConversationController::class, 'show'])->name('chat.conversation');
    Route::post('/conversations/{conversation}/read', [ConversationController::class, 'markRead'])->name('conversations.read');
    Route::post('/conversations/direct', [ConversationController::class, 'startDirect'])->name('conversations.direct');

    // Messages
    Route::post('/conversations/{conversation}/messages', [MessageController::class, 'store'])->name('messages.store')->middleware('throttle:messages');
    Route::patch('/messages/{message}', [MessageController::class, 'update'])->name('messages.update');
    Route::delete('/messages/{message}', [MessageController::class, 'destroy'])->name('messages.destroy');
    Route::post('/messages/{message}/forward', [MessageController::class, 'forward'])->name('messages.forward');

    // Reactions
    Route::post('/messages/{message}/reactions', [ReactionController::class, 'toggle'])->name('reactions.toggle');

    // Pins
    Route::post('/conversations/{conversation}/pins', [PinController::class, 'store'])->name('pins.store');
    Route::delete('/conversations/{conversation}/pins/{message}', [PinController::class, 'destroy'])->name('pins.destroy');

    // Polls
    Route::post('/conversations/{conversation}/polls', [PollController::class, 'store'])->name('polls.store');
    Route::post('/polls/{poll}/vote', [PollController::class, 'vote'])->name('polls.vote');
    Route::patch('/polls/{poll}/close', [PollController::class, 'close'])->name('polls.close');

    // Groups
    Route::get('/groups/explore', [GroupController::class, 'explore'])->name('groups.explore');
    Route::get('/groups/create', [GroupController::class, 'create'])->name('groups.create')->middleware('not.guest');
    Route::post('/groups', [GroupController::class, 'store'])->name('groups.store')->middleware('not.guest');
    Route::patch('/groups/{conversation}', [GroupController::class, 'update'])->name('groups.update');
    Route::post('/groups/{conversation}/join', [GroupController::class, 'join'])->name('groups.join');
    Route::post('/groups/{conversation}/leave', [GroupController::class, 'leave'])->name('groups.leave');
    Route::post('/groups/{conversation}/members', [GroupController::class, 'addMembers'])->name('groups.members.add');
    Route::delete('/groups/{conversation}/members/{user}', [GroupController::class, 'removeMember'])->name('groups.members.remove');
    Route::post('/groups/{conversation}/invite', [GroupController::class, 'generateInvite'])->name('groups.invite');
    Route::get('/invite/{token}', [GroupController::class, 'joinViaInvite'])->name('groups.join-invite');

    // People
    Route::get('/people', [PeopleController::class, 'index'])->name('people.index');
    Route::get('/people/{user}', [PeopleController::class, 'profile'])->name('people.profile');
    Route::post('/people/{user}/dm', [PeopleController::class, 'startDm'])->name('people.dm');

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/fetch', [NotificationController::class, 'fetch'])->name('notifications.fetch');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.read-all');
    Route::patch('/notifications/{notification}/read', [NotificationController::class, 'markRead'])->name('notifications.read');

    // Presence
    Route::post('/presence/heartbeat', [PresenceController::class, 'heartbeat'])->name('presence.heartbeat');

    // Status
    Route::patch('/profile/status', [StatusController::class, 'update'])->name('status.update');

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');

    // Settings (non-guests)
    Route::middleware('not.guest')->group(function () {
        Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
        Route::patch('/settings/dark-mode', [SettingController::class, 'toggleDarkMode'])->name('settings.dark-mode');
        Route::patch('/settings/notifications', [SettingController::class, 'updateNotifications'])->name('settings.notifications');
        Route::patch('/settings/privacy', [SettingController::class, 'updatePrivacy'])->name('settings.privacy');
        Route::patch('/settings/appearance', [SettingController::class, 'updateAppearance'])->name('settings.appearance');
        Route::patch('/settings/password', [SettingController::class, 'updatePassword'])->name('settings.password');
    });

    // Scheduled messages
    Route::get('/scheduled', [ScheduledMessageController::class, 'index'])->name('scheduled.index');
    Route::patch('/scheduled/{message}', [ScheduledMessageController::class, 'update'])->name('scheduled.update');
    Route::delete('/scheduled/{message}', [ScheduledMessageController::class, 'destroy'])->name('scheduled.destroy');

    // Calls
    Route::get('/calls', [CallController::class, 'index'])->name('calls.index');
    Route::post('/conversations/{conversation}/call', [CallController::class, 'initiate'])->name('calls.initiate');
    Route::post('/calls/{call}/answer', [CallController::class, 'answer'])->name('calls.answer');
    Route::post('/calls/{call}/decline', [CallController::class, 'decline'])->name('calls.decline');
    Route::post('/calls/{call}/end', [CallController::class, 'end'])->name('calls.end');
    Route::post('/calls/{call}/signal', [CallController::class, 'signal'])->name('calls.signal');

    // Exports
    Route::post('/conversations/{conversation}/export', [ExportController::class, 'store'])->name('exports.store');
    Route::get('/exports/download', [ExportController::class, 'download'])->name('exports.download');

    // Admin
    Route::prefix('admin')->name('admin.')->middleware('admin')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');
        Route::get('/users', [\App\Http\Controllers\Admin\UserController::class, 'index'])->name('users');
        Route::get('/users/export', [\App\Http\Controllers\Admin\UserController::class, 'export'])->name('users.export');
        Route::post('/users/bulk', [\App\Http\Controllers\Admin\UserController::class, 'bulk'])->name('users.bulk');
        Route::get('/users/{user}', [\App\Http\Controllers\Admin\UserController::class, 'show'])->name('users.show');
        Route::post('/users/{user}/logout', [\App\Http\Controllers\Admin\UserController::class, 'forceLogout'])->name('users.logout');
        Route::post('/users/{user}/ban', [\App\Http\Controllers\Admin\UserController::class, 'ban'])->name('users.ban');
        Route::post('/users/{user}/unban', [\App\Http\Controllers\Admin\UserController::class, 'unban'])->name('users.unban');
        Route::patch('/users/{user}/role', [\App\Http\Controllers\Admin\UserController::class, 'changeRole'])->name('users.role');
        Route::patch('/users/{user}/permissions', [\App\Http\Controllers\Admin\UserController::class, 'updatePermissions'])->name('users.permissions');
        Route::get('/activity', [\App\Http\Controllers\Admin\ActivityLogController::class, 'index'])->name('activity');
        Route::get('/security-log', [\App\Http\Controllers\Admin\MonitoringController::class, 'security'])->name('security-log');
        Route::get('/logs', [\App\Http\Controllers\Admin\MonitoringController::class, 'logs'])->name('logs');
        Route::post('/logs/clear', [\App\Http\Controllers\Admin\MonitoringController::class, 'clearLogs'])->name('logs.clear');
        Route::get('/jobs', [\App\Http\Controllers\Admin\MonitoringController::class, 'jobs'])->name('jobs');
        Route::post('/jobs/retry-all', [\App\Http\Controllers\Admin\MonitoringController::class, 'retryAllJobs'])->name('jobs.retry-all');
        Route::post('/jobs/{uuid}/retry', [\App\Http\Controllers\Admin\MonitoringController::class, 'retryJob'])->name('jobs.retry');
        Route::delete('/jobs/{uuid}', [\App\Http\Controllers\Admin\MonitoringController::class, 'deleteJob'])->name('jobs.delete');
        Route::get('/health/history', [\App\Http\Controllers\Admin\MonitoringController::class, 'history'])->name('health.history');
        Route::get('/health', [\App\Http\Controllers\Admin\HealthController::class, 'index'])->name('health');
        Route::get('/conversations', [\App\Http\Controllers\Admin\ConversationController::class, 'index'])->name('conversations');
        Route::get('/conversations/{conversation}', [\App\Http\Controllers\Admin\ConversationController::class, 'show'])->name('conversations.show');
        Route::delete('/conversations/{conversation}', [\App\Http\Controllers\Admin\ConversationController::class, 'destroy'])->name('conversations.destroy');
        Route::get('/reports', [\App\Http\Controllers\Admin\ReportController::class, 'index'])->name('reports');
        Route::post('/reports/{report}/dismiss', [\App\Http\Controllers\Admin\ReportController::class, 'dismiss'])->name('reports.dismiss');
        Route::post('/reports/{report}/ban', [\App\Http\Controllers\Admin\ReportController::class, 'banUser'])->name('reports.ban');
        Route::get('/messages', [\App\Http\Controllers\Admin\MessageController::class, 'index'])->name('messages');
        Route::delete('/messages/{message}', [\App\Http\Controllers\Admin\MessageController::class, 'destroy'])->name('messages.destroy');
        Route::get('/groups', [\App\Http\Controllers\Admin\GroupController::class, 'index'])->name('groups');
        Route::delete('/groups/{conversation}', [\App\Http\Controllers\Admin\GroupController::class, 'destroy'])->name('groups.destroy');
        Route::get('/security', [\App\Http\Controllers\Admin\SecurityController::class, 'index'])->name('security');
        Route::post('/security/ban-ip', [\App\Http\Controllers\Admin\SecurityController::class, 'banIp'])->name('security.ban-ip');
        Route::delete('/security/ip-bans/{ipBan}', [\App\Http\Controllers\Admin\SecurityController::class, 'unbanIp'])->name('security.unban-ip');
    });
});
