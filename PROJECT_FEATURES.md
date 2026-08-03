# ChatPulse — Complete Feature Documentation

> Real-time chat application built with **Laravel 12 + Blade + Vanilla JS + Tailwind CSS v4 + Laravel Reverb (WebSocket)**.
> This document lists everything implemented in the project, A to Z. Use it as context when working with AI assistants or onboarding developers.

---

## 1. Tech Stack

| Layer | Technology |
|---|---|
| Backend | Laravel 12 (PHP), MySQL/SQLite |
| Frontend | Blade templates + Vanilla JavaScript (no framework), Tailwind CSS v4 via `@tailwindcss/vite` |
| Real-time | Laravel Reverb (self-hosted WebSocket, Pusher protocol) + Laravel Echo |
| Build | Vite (`npm run build`) |
| Fonts/Design | Plus Jakarta Sans, custom CSS design-token system (light + dark mode) |

### Key files
- `public/js/chatpulseapp.js` — main chat app logic (rendering, messaging, real-time)
- `public/js/chatpulseoverlays.js` — calls, notifications, profile overlays
- `public/js/chatpulseaccount.js` — account, onboarding, privacy prefs
- `resources/css/app.css` — entire design system (CSS variables, light/dark)
- `resources/views/chat/index.blade.php` — main chat page
- `app/Http/Controllers/ConversationController.php` — builds `window.CP` data bridge + `CP_ROUTES` endpoint map

### Data bridge pattern
Backend serializes everything to `window.CP` (me, users, conversations, messages, reactionsPool, scheduled, activeId) and `window.CP_ROUTES` (all endpoint URL templates + CSRF token). Frontend JS renders from this and syncs changes back via `fetch`. Conversation IDs are prefixed `c` (e.g. `c5`), DB message IDs prefixed `db` (e.g. `db123`).

---

## 2. Authentication & Users

- Login / Register / Logout (Laravel auth)
- **Guest accounts** support (`is_guest` flag, amber "guest" badge in UI)
- Roles: `admin`, `user`, `guest`
- User profiles: name, username, bio, status type (available/busy/away), status message
- **Avatar system**: no image uploads needed — gradient avatars generated from an md5 hash of the username (consistent color per user, `User::avatarGradient()`)
- Banned users (`is_banned`, `banned_at`, `banned_reason`)
- **First-run onboarding wizard** (welcome → profile → status → notifications → done), stored per-user in localStorage (`cp-onboarded-{userId}`) so it shows only once per user

---

## 3. Conversations

- **Direct messages (DMs)** — start a DM with any user from the People view (`POST /conversations/direct`, finds or creates)
- **Group chats** — create group with name, description, members, public/private flag
- Conversation list with: last message preview, timestamp, unread badge, typing indicator (mini dots), muted icon, pinned/favorite flags
- **Filters**: All / Unread / Groups / (Scheduled — currently commented out)
- List views: Chats / People / Saved (bookmarks) / Scheduled
- Search conversations by name or message content
- Archive, mute, favorite, pin conversations (context menu on each list item)
- Unread divider ("New messages") inside thread
- "Load earlier messages" + "beginning of conversation" states

---

## 4. Messaging (Core)

- **Real message sending** to backend (`POST /conversations/{conv}/messages`) with optimistic UI (temp ID swapped for `db{id}` on response)
- **Reply** to messages (quoted preview inside bubble — WhatsApp style: rounded container, green accent bar, sender name, truncated text)
- **Edit** own messages (inline edit box, `edited` tag shown)
- **Delete** own messages (soft delete, "This message was deleted" placeholder)
- **Forward** messages to other conversations (forwarded label shown)
- **Copy** message text
- **Pin messages** (max 10 per conversation, amber pill badge on message, pinned list in right panel with jump-to)
- **Bookmark/Save** messages (Saved view in list panel)
- Link detection (`linkify`) + link preview cards
- Message failed state with **Retry** button (offline detection via `navigator.onLine`)
- **Spam detection** (heuristic): regex signals (free/click here/crypto/urls…), CAPS, emoji spam; suspicious users flagged; spam messages hidden behind "Show message / Report" warning
- **Report message** flow (reason + optional block user)

## 5. Message Bubbles UI (WhatsApp-style)

- Sent messages right-aligned, received left-aligned
- Bubble corners: received = top-left sharp, sent = top-right sharp
- `max-width: 62%`, min-width for tiny messages
- **Timestamp + ticks INSIDE the bubble** (floated bottom-right, WhatsApp style)
- Typography scale: sender name 13px/600, body 14px/400, timestamp 11px at ~0.5 opacity
- **Consecutive message grouping**: same sender back-to-back → avatar & name hidden (only first of group shows)
- Hover: subtle row tint + **WhatsApp-style side controls** appear next to bubble:
  - Smiley button → quick-reaction bar
  - Chevron/arrow button → dropdown menu (Reply, Forward, Copy, Pin/Unpin, Save, Edit, Delete/Report)
- Day dividers, admin/guest badges next to names

---

## 6. Reactions

- **One reaction per user per message** (WhatsApp behavior — picking a new emoji replaces the previous one; same emoji again removes it). Enforced on frontend AND backend (`ReactionController` deletes user's other reactions before adding)
- Quick-react bar: 6 emojis in rounded pill + **"+" button** opening the full emoji picker
- Reaction pills under message with counts, own reaction highlighted
- Real-time sync via `ReactionToggled` broadcast

## 7. Emoji Picker (Professional / WhatsApp-style)

- 380px panel with **search input**
- 6 icon-tab categories: Smileys & People, Hearts & Symbols, Animals & Nature, Food & Drink, Activity & Travel, Objects (~600 emojis)
- Category label header, hover zoom effect on emojis
- Used for both composer emoji insert and message reactions

---

## 8. Real-time (Laravel Reverb WebSocket)

- **Channels**:
  - `private-conversation.{id}` — messages, reactions, edits, deletes, read receipts, whispers
  - `presence-app` — online/offline tracking (`.here/.joining/.leaving`)
  - `private-user.{id}` — incoming call notifications
  - `private-call.{callId}` — call signaling
- **Events broadcast**: `MessageSent`, `MessageUpdated`, `MessageDeleted`, `ReactionToggled`, `ConversationRead`, `UserPresenceUpdated`, `CallInitiated`, `CallAnswered`, `CallEnded`
- **Whispers (client-to-client, no server)**:
  - `typing` / `stop-typing` — typing indicators (800ms auto-stop, 1200ms receiver clear)
  - `message-delivered` — delivered receipts
- On boot all users assumed offline except self; presence channel corrects
- **Heartbeat**: `POST /presence/heartbeat` every 60s + on tab visibility change

## 9. Delivery Status — WhatsApp Tick System

- `sending` → clock icon
- `sent` → single grey tick (server accepted)
- `delivered` → double grey tick (receiver's client whispered back)
- `read` → double blue tick (`ConversationRead` broadcast when receiver opens conversation)
- **Read only counts when tab is visible** (`document.hidden` check — background tab does not auto-read)
- **Message Info panel** (click on ticks): shows Sent / Delivered / Read timestamps like WhatsApp

---

## 10. Typing Indicators

- Real-time via Echo whispers
- Shows animated dots in conversation list AND as a bubble in the thread
- Auto-stops 800ms after last keystroke; cleared immediately when message arrives

## 11. Presence / Online Status

- Presence channel tracks join/leave instantly
- `UserPresenceUpdated` event on logout → instant offline (no stale "Active now")
- Status dot colors: available=green, busy=red, away=amber
- "Last seen" (`diffForHumans`) when offline

---

## 12. File & Media

- **File/image upload**: real XHR multipart upload with **progress bar** (percentage overlay for images, bar for files), retry on failure, 50MB max
- Image messages with **lightbox** viewer
- File messages with type-colored icon, size, download button
- **Voice messages**: MediaRecorder API (mic permission → record bar with waveform animation, timer, cancel/send) → uploads blob as webm. Note: requires HTTPS (or localhost)
- Attachment tray in composer (multiple pending files before send)

## 13. Polls

- Create poll (question + options, multiple-choice and anonymous flags) via composer "+" menu
- Vote via `POST /polls/{poll}/vote` — optimistic UI, server-synced counts, animated percentage fills

## 14. Scheduled Messages

- Backend fully working: `scheduled_at` on messages, `is_scheduled` flag, scheduled list view with edit/cancel/send-now
- **UI currently disabled** (commented out): Scheduled filter pill, composer clock button, plus-menu option — easy to re-enable

## 15. Calls (WebRTC)

- Audio + video calls: `RTCPeerConnection` with Google STUN, SDP offer/answer + ICE via `POST /calls/{call}/signal`, Reverb `call.{id}` channel for signaling
- Incoming call UI via `user.{id}` channel (`CallInitiated`)
- Answer / Decline / End flows with backend records
- Note: production needs HTTPS + TURN server (Metered.ca free tier suggested for portfolio)

## 16. Notifications

- In-app notification center (bell icon): fetches `/notifications/fetch` — real DB notifications with unread counts, mark read / mark all read
- Bell badge dot with count

---

## 17. Composer

- Rounded pill input field, raised from background, green focus ring
- Left: attach (paperclip) + emoji buttons; right: mic button
- **Send button: always-visible green circle** outside the field; mic hides while typing
- contenteditable input with placeholder, Enter to send, auto-grow
- Reply bar above composer when replying
- Voice recording bar replaces field while recording (slide-to-cancel hint, waveform, timer)

## 18. Thread Extras

- **In-thread search** (search bar with prev/next navigation, match highlighting, current-match outline)
- Jump-to-message (from pinned list)
- Empty states designed for all views (no conversations / people / saved / scheduled)
- Toast notifications for all actions
- Network banner (offline / reconnecting)
- Skeleton loaders on boot

---

## 19. Settings Page (full redesign)

Uses the chat layout (#list sidebar + #chat content), all ChatPulse design tokens, dark-mode aware, right panel hidden, sections centered at 860px:

- **Profile**: avatar hero card, display name, @username, bio with 200-char counter, status type + status message
- **Appearance**: dark mode toggle (persists to DB + localStorage), bubble style pills, font size pills
- **Notifications**: email notifications toggle, message previews, sound alerts, email digest (never/daily/weekly)
- **Privacy**: read receipts, online status, typing indicator toggles, who-can-message radio
- **Account**: email + verified badge, change password (validates current password, `PATCH /settings/password`), danger zone (delete account)
- Saves via fetch with animated "✓ Saved" feedback

## 20. Admin Panel (full rebuild)

ChatPulse-styled (dark sidebar, green accents, dark-mode toggle, admin avatar in header), grouped nav (Overview / Manage / Safety):

- **Dashboard**: 8 stat cards with icons (total users, online now, messages today, total messages, groups, conversations, guests, banned), **7-day message activity bar chart**, recent users with role/guest/banned tags, latest messages table
- **Users**: search (name/email/username), filters (role, online/banned), gradient avatars, inline role change dropdown, **ban with reason prompt** / unban, cannot ban admins or self
- **Messages** (moderation): search all message text, view sender + conversation, delete any message
- **Groups**: search, member + message counts, public/private badge, delete with confirm
- **Security**: IP ban form (IP + reason + optional expiry), active IP bans list with permanent/expiring tags, banned users list with avatars + reasons, unban actions
- Middleware-protected (`admin`), flash success/error messages

### 20a. Real-time Admin Monitoring
- **Live System Health widget** (`GET /admin/health`, 10s refresh): CPU load (normalized per core), memory %, disk %, DB ping latency, **Reverb WebSocket port check**, queue pending jobs, failed jobs count, messages/hour — color-coded meters (green/amber/red)
- **Live "Online Right Now" panel**: subscribes to the same Reverb `presence-app` channel as the chat — users appear/disappear **instantly via WebSocket push (no polling)** with avatars and live count
- **Hourly Activity heatmap**: last 24 hours message volume as opacity-scaled heat cells (on top of the 7-day bar chart)

### 20b. Granular Permissions (custom, Spatie-style)
- `permissions` JSON column on users; `User::PERMISSIONS` registry: `can_create_group`, `can_upload_files`, `can_send_voice`, `can_call`, `can_forward`
- `User::hasPerm($key)` — admins always pass, stored JSON overrides defaults
- Admin Users page: **"Perms" button** expands per-user checkbox row, saved via `PATCH /admin/users/{user}/permissions`
- **Enforced server-side** in GroupController (create group), MessageController (attachments, voice, forward), CallController (initiate) — 403 with clear message

### 20c. Audit Trail (Activity Log)
- `admin_logs` table: admin, action, target (type/id/label), details, IP, timestamp
- `AdminLog::record()` helper (never throws) wired into: user ban/unban/role-change/permissions, message delete, group delete, IP ban/unban
- **Activity Log page** in admin nav: searchable, filterable by action, color-coded badges (ban/delete=red, unban=green), admin avatars, relative timestamps

---

## 21. Design System

- CSS variables in `:root` + `html.dark` overrides — full **dark mode** across chat, settings, admin
- Palette: primary green `#10b981`, dark rail `#111827`, bubble colors for mine/incoming
- Consistent components: cards, badges, toggle switches, choice pills, pop menus, toasts, modals
- Sidebar/list hover states (subtle green tint), active states that don't merge with hover
- Responsive: mobile tabs bar, mobile-chat mode, media queries

## 22. Misc / Infrastructure

- CSRF handling on all fetch/XHR calls
- Optimistic UI everywhere with server reconciliation
- Soft deletes on messages
- Seeders: users (all `is_online => false` by default), conversations, messages
- Export chat backend (`POST /conversations/{id}/export` + download)
- Deployment files present: Dockerfile, Dockerfile.reverb, nixpacks.toml, start scripts (Railway-ready)

---

## 23. Known Notes / Pending

- Voice message **playback** uses fake duration bars (real audio URL playback pending)
- Group member add/remove UI pending (backend ready)
- WebRTC calls need HTTPS + TURN for production
- Scheduled messages UI commented out (backend intact)
- Appearance options (bubble style / font size) are UI-only, not persisted yet
- Privacy toggles (read receipts / online status / typing) are UI-only, not enforced yet

---

## 24. How to Run

```bash
composer install && npm install
cp .env.example .env && php artisan key:generate
# set DB + BROADCAST_CONNECTION=reverb + REVERB_* keys (self-invented strings)
php artisan migrate --seed
npm run build

php artisan serve          # terminal 1
php artisan reverb:start   # terminal 2 (required for real-time)
```

Open two browsers with two users to test real-time features.
