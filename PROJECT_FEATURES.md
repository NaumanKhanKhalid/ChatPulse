# ChatPulse — Complete Feature Documentation

> Real-time chat application built with **Laravel 12 + Blade + Vanilla JS + Tailwind CSS v4 + Laravel Reverb (WebSocket)**.
> This document lists everything implemented in the project. Use it as context when working with AI assistants or onboarding developers.

---

## 1. Tech Stack

| Layer | Technology |
|---|---|
| Backend | Laravel 12 (PHP 8.2), MySQL (SQLite works for local) |
| Frontend | Blade templates + Vanilla JavaScript (no framework), Tailwind CSS v4 via `@tailwindcss/vite` |
| Real-time | Laravel Reverb (self-hosted WebSocket, Pusher protocol) + Laravel Echo |
| Build | Vite (`npm run build`) |
| Design | Plus Jakarta Sans, custom CSS design-token system (light + dark mode) |

### Key files
- `public/js/chatpulseapp.js` — main chat app (rendering, messaging, real-time, alerts)
- `public/js/chatpulseoverlays.js` — calls, notifications, profile & group overlays
- `public/js/chatpulsemodals.js` — shared modals (new chat, poll, forward, report)
- `public/js/chatpulseaccount.js` — account, onboarding, profile editing
- `resources/css/app.css` — entire design system (chat + admin + settings)
- `app/Http/Controllers/ConversationController.php` — builds the `window.CP` data bridge and `CP_ROUTES` endpoint map

### Data bridge pattern
Backend serializes state to `window.CP` (me + prefs, users, conversations, messages, reactionsPool, scheduled, activeId) and `window.CP_ROUTES` (endpoint URL templates, ICE servers, CSRF token). Frontend renders from this and syncs changes back via `fetch`. Conversation IDs are prefixed `c` (e.g. `c5`), DB message IDs prefixed `db` (e.g. `db123`).

### ⚠️ Important architectural note
**Every broadcast event implements `ShouldBroadcastNow`, and jobs that must not silently fail (`NotifyParticipantsJob`, `ExportChatJob`) are dispatched synchronously.** This project is expected to run *without* a queue worker. If you add a broadcast event or a user-visible job, do the same — queued ones never fire and the failure is invisible.

---

## 2. Authentication & Users

- Login / Register / Logout, plus **guest accounts** (`is_guest`, amber badge in UI)
- Roles: `admin`, `user`, `guest`
- Profiles: name, username, bio, status type (available/busy/away), status message
- **Gradient avatars** derived from an md5 hash of the username — consistent per user, no uploads needed (`User::avatarGradient()`)
- Banned users (`is_banned`, `banned_at`, `banned_reason`)
- **First-run onboarding wizard**, tracked per user (`cp-onboarded-{userId}` in localStorage)
- **Every auth event is logged** to `login_logs`: successful logins, failed attempts, logouts, with IP, device and a new-device flag

### Granular permissions
`users.permissions` JSON column plus a `User::PERMISSIONS` registry: `can_create_group`, `can_upload_files`, `can_send_voice`, `can_call`, `can_forward`. `User::hasPerm()` — admins always pass, stored JSON overrides defaults. **Enforced server-side** in Group/Message/Call controllers (403s), editable per user from the admin panel.

---

## 3. Conversations

- **Direct messages** — start from the People view (`POST /conversations/direct`)
- **Group chats** — name, description, members, public/private
- List shows last message, timestamp, unread badge, typing dots, mute icon, pin/favourite flags, and a green **`@` badge when an unread message mentions you** (clicking it opens the notification panel)
- Filters: All / Unread / Groups
- Views: Chats / People / Scheduled
- Search conversations by name or message content
- Unread divider ("New messages") — set client-side the moment a message lands in a conversation you are not reading

### Per-user conversation preferences (all persisted)
Pin, favourite, mute, archive, mark-unread, clear history and delete are stored on `conversation_participants` (`is_pinned`, `is_favourite`, `is_muted`, `is_archived`, `cleared_at`) via `ConversationPrefController`. **Clear and delete affect only the requesting user** — other participants keep their copy (implemented as a `cleared_at` cutoff, not row deletion).

---

## 4. Messaging

- Real sending with optimistic UI (temp ID swapped for `db{id}` on response)
- **Reply** with quoted preview inside the bubble
- **Edit** own messages (inline, `edited` tag)
- **Delete** own messages (soft delete)
- **Forward** to other conversations
- **Copy** text
- **Pin messages** — max 10 per conversation, shown in a WhatsApp-style bar under the header with a segment rail, jump-to, cycle and unpin; a small pin icon appears next to the timestamp
- Link detection + preview cards
- Failed state with **Retry** (offline detection)
- **Spam heuristic** — regex signals, CAPS, emoji spam; flagged messages hidden behind a warning with Show / Report
- **Report** message or user → stored in `reports`, surfaced in the admin panel (duplicate-protected, self-reporting rejected)

## 5. Message Bubbles (WhatsApp-style)

- Sent right-aligned, received left; received = top-left sharp corner, sent = top-right
- `max-width: 62%`, min-width for short messages
- **Timestamp and ticks inside the bubble**, bottom-right (file cards carry their own footer)
- Own messages show no avatar or name header
- Consecutive same-sender messages hide the avatar
- Typography: sender 13px/600, body 14px/400, timestamp 11px at ~0.5 opacity
- Hover: subtle row tint plus **side controls** — react smiley and a chevron opening the message menu (Reply, Forward, Copy, Pin, Edit, Delete/Report)
- System notices (group joins) render as a centered pill, not a bubble

---

## 6. Reactions

- **One reaction per user per message** — picking a new emoji replaces the previous one, same emoji removes it (enforced frontend and backend)
- Reaction chips **overlap the bubble's bottom edge** with a chat-background ring; own and others' reactions look identical
- Quick bar of 6 emojis plus a **+** opening the full picker

## 7. Emoji Picker

380px panel with **search**, 6 icon-tab categories (Smileys & People, Hearts & Symbols, Animals & Nature, Food & Drink, Activity & Travel, Objects), ~600 emojis, hover zoom. Used for both composer and reactions.

---

## 8. Real-time (Laravel Reverb)

**Channels**
- `private-conversation.{id}` — messages, edits, deletes, reactions, pins, read receipts, typing whispers
- `presence-app` — online/offline tracking
- `private-user.{id}` — incoming calls
- `private-call.{callId}` — WebRTC signalling
- `private-admin.activity` — admin live feed (admins only)

**Events** (all `ShouldBroadcastNow`): `MessageSent`, `MessageUpdated`, `MessageDeleted`, `MessageForwarded`, `ReactionToggled`, `MessagePinned`, `MessageUnpinned`, `ConversationRead`, `UserPresenceUpdated`, `UserStatusUpdated`, `PollUpdated`, `LinkPreviewReady`, `ExportReady`, `CallInitiated`, `CallAnswered`, `CallEnded`, `WebRTCSignal`, `AdminActivity`

**Whispers** (client-to-client): `typing` / `stop-typing`, `message-delivered`

Heartbeat every 60s and on tab visibility change.

## 9. Delivery Status

`sending` → clock · `sent` → single grey tick · `delivered` → double grey (peer whispered back) · `read` → double blue (`ConversationRead`).

- Read state **persists across reloads** — computed from every other participant's `last_read_at`
- Delivered receipts arriving before the HTTP response are buffered and applied once the DB id is known
- Read only registers when the tab is visible
- **Message Info panel** on tick click showing Sent / Delivered / Read times

## 10. Typing & Presence

- Typing via whispers (800ms auto-stop, cleared instantly when the message arrives), suppressed when the user disables it in Privacy
- Presence channel plus `UserPresenceUpdated` for instant offline on logout
- Status dots: available green, busy red, away amber — hidden entirely when the user turns off online status
- Header, sidebar and right panel all refresh live on presence changes

---

## 11. Files & Media

- **Uploads** via XHR with real progress, retry on failure, 50MB max
- **Multiple images send as one album message** — 2-column grid, 3-photo layout, `+N` overlay past 4, lightbox per image
- File cards with type-coloured icon, size and a **working download button**
- **Voice messages** — MediaRecorder capture with waveform/timer UI, and **real `<audio>` playback**: the waveform follows actual position, duration is stored so it survives a reload
- Images and files both downloadable; lightbox has its own download button
- Requires `php artisan storage:link` for uploads to be served

## 12. Polls

Create from the composer "+" menu (question, options, multiple-choice, anonymous). Voting is optimistic and server-synced with animated percentage fills.

## 13. Scheduled Messages

Backend fully working (`scheduled_at`, `is_scheduled`, scheduled list with edit/cancel/send-now). **UI is currently commented out** — the filter pill, composer clock button and plus-menu entry. Re-enable by removing those comments.

## 14. Calls (currently disabled)

WebRTC audio/video with real `getUserMedia`, `addTrack`/`ontrack`, `<video>`/`<audio>` elements, working mute/camera toggles and screen share via `replaceTrack`. Signalling over Reverb, call records with `answered_at`-based duration, 45s ring timeout, decline propagation.

**All entry points are commented out** — header buttons, right-panel buttons, incoming-call listener, rail button, call-log call-back links. Backend, overlay and CSS are intact.

**Notes for re-enabling:** needs HTTPS (localhost exempt); ICE servers are configurable via `config/webrtc.php` (`STUN_URLS`, `TURN_URLS`, `TURN_USERNAME`, `TURN_CREDENTIAL`) so a coturn or Metered TURN server drops in without code changes. Group calls are 1-to-1 only — real group calling needs an SFU.

## 15. Notifications (WhatsApp model — no bell)

There is deliberately **no notification bell**. Unread state lives on the conversation list.

- Notifications are created **only for @mentions** (plain messages are covered by the unread badge)
- The green **`@` badge** on a conversation row opens the notification panel
- **Sound alert** — a short WebAudio chirp when a message lands somewhere you are not reading, honouring the Sound Alerts setting
- **Desktop notification** when the tab is hidden, showing the message or just "New message" if previews are off; clicking focuses the tab and opens that conversation
- Permission requested once after the first click

## 16. Feedback & Support

- **"Send feedback"** in the avatar menu — four intents (broken / idea / help / other), message with counter, optional reply email, thank-you state
- Browser, screen size and current page attached automatically; rate limited to 6/minute
- Admin side: Open/Reviewing/Resolved tabs, search, type filter, inline status + internal note, sidebar count badge

---

## 17. Composer

Rounded pill field raised from the background, attach and emoji on the left, mic on the right, **always-green circular send button** outside the field. Reply bar above; recording replaces the field with a waveform bar.

## 18. Thread Extras

In-thread search with prev/next and highlighting, jump-to-message, designed empty states, toasts, offline/reconnecting banner, skeleton loaders.

---

## 19. Settings

Uses the chat layout, dark-mode aware, right panel hidden:

- **Profile** — name, @username, bio with counter, status type + message
- **Appearance** — dark mode, **bubble style and font size (both applied to the chat)**
- **Notifications** — email notifications, message previews, sound alerts, email digest
- **Privacy** — read receipts, online status, typing indicator, who-can-message
- **Account** — email + verified badge, change password, danger zone

**All privacy and appearance switches are persisted and enforced**, not decorative: read receipts gate the `ConversationRead` broadcast, online status hides presence and last-seen, typing gates the whisper, and contacts-only rejects DMs from strangers with a 403.

## 20. Admin Panel

Own dark layout with a **collapsible sidebar** (state persisted) and a **⌘/Ctrl+K command palette** searching every admin page.

| Page | What it does |
|---|---|
| **Dashboard** | 8 stat cards, live System Health widget, 24h health trend chart, **live activity feed over WebSocket**, online-now panel via presence, top groups, hourly heatmap, 7-day chart, recent users and messages |
| **Users** | Table with search and role/status filters, inline role change, ban with reason, permissions drawer, **bulk ban/unban/role**, **CSV export honouring filters**, click through to a detail page |
| **User detail** | Profile header, activity stats, recent messages, conversations, **active sessions with IP/device**, force sign-out, full moderation history |
| **Conversations** | Card list with stacked avatars and last-message preview; click opens a **read-only chat popup** |
| **Messages** | Moderation list with search and delete |
| **Groups** | Member and message counts, public/private, delete |
| **Feedback** | User-submitted feedback with status workflow |
| **Reports** | Abuse reports with Dismiss / Ban user |
| **Security Log** | Logins, failed attempts, logouts, devices, plus a **brute-force panel** for IPs with 3+ failures |
| **Error Logs** | Parsed `laravel.log` entries with expandable stack traces, level filter, clear |
| **Queue Jobs** | Pending count and failed jobs with **retry / retry-all / delete** |
| **Activity Log** | Full admin audit trail |
| **Security** | IP bans with optional expiry, banned users |

### Monitoring internals
- `GET /admin/health` — CPU, memory, disk, DB ping, Reverb port check, queue depth, messages/hour (polled every 10s)
- `health:snapshot` scheduled every 5 minutes into `health_snapshots`, pruned after 14 days, powering the trend chart
- `logs:prune` daily — trims login/audit history past 90 days, rotates `laravel.log` past 20MB
- **Audit trail** (`admin_logs`) records every admin action: bans, role and permission changes, deletions, IP bans, feedback and report handling, exports

---

## 21. Design System

CSS variables in `:root` with `html.dark` overrides across chat, settings and admin. Primary green `#10b981`. Small laptops (≤1440px) get genuinely smaller panels and fonts rather than CSS `zoom`, which rendered text blurry.

## 22. Database Tables Added

`admin_logs`, `reports`, `login_logs`, `health_snapshots`, `feedback`, plus columns on `users` (permissions, privacy and appearance preferences), `calls` (`answered_at`) and `conversation_participants` (pinned, favourite, archived, cleared_at).

---

## 23. Known Gaps

- **No tests.** `tests/` contains only Laravel's two example stubs. Most bugs found during development (queued events never firing, `MessagePin` timestamps, notifications never created, status never saved) would have been caught by a modest feature-test suite.
- Calls and scheduled-message UI are commented out (see sections 13 and 14)
- Group calling needs an SFU
- No global cross-conversation message search — search is per-thread and per-conversation-list only

## 24. Running It

```bash
composer install && npm install
cp .env.example .env && php artisan key:generate
# set DB, BROADCAST_CONNECTION=reverb, and REVERB_* keys (any strings you choose)
php artisan migrate --seed
php artisan storage:link      # required for uploads to be served
npm run build

php artisan serve             # terminal 1
php artisan reverb:start      # terminal 2 — required for anything real-time
php artisan schedule:work     # terminal 3 — health snapshots, scheduled messages, pruning
```

Open two browsers with two users to exercise real-time features. Use `localhost`, not `127.0.0.1`, if you re-enable calls — browsers only grant camera/mic on `localhost` or HTTPS.
