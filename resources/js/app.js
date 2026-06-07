import './echo.js';
import Alpine from 'alpinejs';
import persist from '@alpinejs/persist';

Alpine.plugin(persist);

// ─── Helpers ────────────────────────────────────────────────────────────────

const csrfToken = () => document.querySelector('meta[name="csrf-token"]')?.content ?? '';
window.csrfToken = csrfToken;

const apiFetch = async (url, options = {}) => {
    const headers = {
        'Accept': 'application/json',
        'X-CSRF-TOKEN': csrfToken(),
        ...(options.headers ?? {}),
    };
    if (!(options.body instanceof FormData)) {
        headers['Content-Type'] = 'application/json';
    }
    const res = await fetch(url, { ...options, headers });
    if (!res.ok) {
        const body = await res.json().catch(() => ({}));
        throw new Error(body.message || body.error || `HTTP ${res.status}`);
    }
    return res.json().catch(() => null);
};
window.apiFetch = apiFetch;

// ─── Root App Component ──────────────────────────────────────────────────────

Alpine.data('teamflowApp', () => ({
    darkMode: localStorage.getItem('darkMode') === 'true',
    toasts: [],
    _toastId: 0,
    incomingCall: null,
    activeCall: null,
    localStream: null,
    isMuted: false,
    isCameraOff: false,

    init() {
        // Apply saved dark mode immediately on load
        if (this.darkMode) document.documentElement.classList.add('dark');
        else document.documentElement.classList.remove('dark');
        this.startHeartbeat();

        if (!window.Echo) return;

        // Global presence channel
        window.Echo.join('app')
            .listen('UserPresenceUpdated', e => {
                document.querySelectorAll(`[data-user-id="${e.user_id}"]`).forEach(el => {
                    const dot = el.querySelector('.status-dot');
                    if (dot) dot.className = dot.className.replace(/(bg-green-500|bg-gray-400)/, e.is_online ? 'bg-green-500' : 'bg-gray-400');
                });
            });

        // Private user channel (calls + exports)
        const userId = document.body.dataset?.userId;
        if (userId) {
            window.Echo.private(`user.${userId}`)
                .listen('CallInitiated', e => { this.incomingCall = e; })
                .listen('ExportReady', e => {
                    this.addToast('success', 'Export ready! Downloading...');
                    setTimeout(() => { window.location.href = e.download_url; }, 800);
                });
        }
    },

    toggleDark() {
        this.darkMode = !this.darkMode;
        localStorage.setItem('darkMode', String(this.darkMode));
        if (this.darkMode) document.documentElement.classList.add('dark');
        else document.documentElement.classList.remove('dark');
        fetch('/settings/dark-mode', {
            method: 'PATCH',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken() },
            body: JSON.stringify({}),
        }).catch(() => {});
    },

    startHeartbeat() {
        const ping = () => fetch('/presence/heartbeat', { method: 'POST', headers: { 'X-CSRF-TOKEN': csrfToken() } }).catch(() => {});
        ping();
        setInterval(ping, 30000);
    },

    addToast(type, message) {
        const id = ++this._toastId;
        this.toasts.push({ id, type, message, visible: true });
        setTimeout(() => this.removeToast(id), 4000);
    },

    removeToast(id) {
        const t = this.toasts.find(t => t.id === id);
        if (t) t.visible = false;
        setTimeout(() => { this.toasts = this.toasts.filter(t => t.id !== id); }, 300);
    },

    async acceptCall() {
        const call = this.incomingCall;
        if (!call) return;
        this.incomingCall = null;
        try {
            await apiFetch(`/calls/${call.call_id}/answer`, { method: 'POST' });
            this.activeCall = call;
            await this._setupMedia(call.type !== 'audio');
        } catch (e) { this.addToast('error', 'Could not join call.'); }
    },

    declineCall() {
        if (!this.incomingCall) return;
        apiFetch(`/calls/${this.incomingCall.call_id}/decline`, { method: 'POST' }).catch(() => {});
        this.incomingCall = null;
    },

    async _setupMedia(video = true) {
        try {
            this.localStream = await navigator.mediaDevices.getUserMedia({ audio: true, video });
            const lv = document.querySelector('[x-ref="localVideo"]');
            if (lv) lv.srcObject = this.localStream;
        } catch { this.addToast('error', 'Camera/microphone access denied.'); }
    },

    toggleMic() {
        this.isMuted = !this.isMuted;
        this.localStream?.getAudioTracks().forEach(t => { t.enabled = !this.isMuted; });
    },

    toggleCamera() {
        this.isCameraOff = !this.isCameraOff;
        this.localStream?.getVideoTracks().forEach(t => { t.enabled = !this.isCameraOff; });
    },

    endCall() {
        if (this.activeCall) apiFetch(`/calls/${this.activeCall.call_id}/end`, { method: 'POST' }).catch(() => {});
        this.localStream?.getTracks().forEach(t => t.stop());
        this.localStream = null;
        this.activeCall = null;
        this.isMuted = false;
        this.isCameraOff = false;
    },
}));

// ─── Chat Conversation Component ─────────────────────────────────────────────

Alpine.data('chatConversation', (conversationId, currentUserId, initialMessages) => ({
    messages: Array.isArray(initialMessages) ? initialMessages : [],
    loading: false,
    isDark: document.documentElement.classList.contains('dark'),
    newMessage: '',
    typingUsers: [],
    _typingTimeout: null,
    _typingClear: null,
    replyTo: null,
    editingMessageId: null,
    editBody: '',
    selectedFiles: [],
    scheduledAt: null,
    forwardModalOpen: false,
    forwardingMessage: null,
    forwardTargets: [],
    showExportModal: false,
    exportFormat: 'csv',
    exportFrom: '',
    exportTo: '',
    firstUnreadId: null,

    get typing() { return this.typingUsers.length > 0; },
    get typingName() { return this.typingUsers[0] ?? ''; },

    init() {
        this.$nextTick(() => this.scrollToBottom());
        this.markRead();

        // Keep isDark in sync when dark mode toggled globally
        const observer = new MutationObserver(() => {
            this.isDark = document.documentElement.classList.contains('dark');
        });
        observer.observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });

        if (!window.Echo) return;

        window.Echo.private(`conversation.${conversationId}`)
            .listen('MessageSent', e => {
                if (!this.messages.find(m => m.id === e.message.id)) {
                    this.messages.push({ ...e.message, link_previews: [] });
                    this.$nextTick(() => this.scrollToBottom());
                    if (e.message.user_id !== currentUserId) this.markRead();
                }
            })
            .listen('MessageUpdated', e => {
                const m = this.messages.find(m => m.id === e.message.id);
                if (m) { m.body = e.message.body; m.is_edited = true; }
            })
            .listen('MessageDeleted', e => {
                this.messages = this.messages.filter(m => m.id !== e.message_id);
            })
            .listen('ReactionToggled', e => {
                const m = this.messages.find(m => m.id === e.message_id);
                if (m) m.reactions = e.reactions;
            })
            .listen('PollUpdated', e => {
                const m = this.messages.find(m => m.id === e.message_id);
                if (m?.poll) { m.poll.total_votes = e.total_votes; m.poll.options = e.options; }
            })
            .listen('LinkPreviewReady', e => {
                const m = this.messages.find(m => m.id === e.message_id);
                if (m) {
                    if (!m.link_previews) m.link_previews = [];
                    if (!m.link_previews.find(p => p.url === e.preview.url)) m.link_previews.push(e.preview);
                }
            })
            .listenForWhisper('typing', e => {
                if (String(e.user_id) === String(currentUserId)) return;
                if (!this.typingUsers.includes(e.name)) this.typingUsers.push(e.name);
                clearTimeout(this._typingClear);
                this._typingClear = setTimeout(() => {
                    this.typingUsers = this.typingUsers.filter(n => n !== e.name);
                }, 3000);
            });
    },

    isGrouped(index) {
        if (index === 0) return false;
        const prev = this.messages[index - 1];
        const curr = this.messages[index];
        if (!prev || !curr || prev.user_id !== curr.user_id) return false;
        return (new Date(curr.created_at) - new Date(prev.created_at)) < 300000;
    },

    isSameUserAsPrev(index) { return this.isGrouped(index); },

    shouldShowDayDivider(index) {
        if (index === 0) return true;
        const prev = this.messages[index - 1];
        const curr = this.messages[index];
        if (!prev || !curr) return false;
        const pDate = new Date(prev.created_at).toDateString();
        const cDate = new Date(curr.created_at).toDateString();
        return pDate !== cDate;
    },

    getDayLabel(message) {
        if (!message?.created_at) return '';
        const d = new Date(message.created_at);
        const today = new Date();
        const yesterday = new Date(today);
        yesterday.setDate(yesterday.getDate() - 1);
        if (d.toDateString() === today.toDateString()) return 'Today';
        if (d.toDateString() === yesterday.toDateString()) return 'Yesterday';
        return d.toLocaleDateString([], { weekday: 'long', month: 'short', day: 'numeric' });
    },

    setReply(message) { this.replyTo = message; },

    openReactionPicker(messageId, event) {
        const emojis = ['👍','🔥','🎉','❤️','😂','👀','✅','🙏'];
        const msg = this.messages.find(m => m.id === messageId);
        if (!msg) return;
        const existing = document.getElementById('cp-emoji-pop');
        if (existing) existing.remove();
        const pop = document.createElement('div');
        pop.id = 'cp-emoji-pop';
        pop.className = 'emoji-pop';
        pop.style.cssText = `position:fixed;z-index:100;top:${event.clientY - 60}px;left:${Math.min(event.clientX - 80, window.innerWidth - 280)}px;`;
        emojis.forEach(e => {
            const btn = document.createElement('button');
            btn.textContent = e;
            btn.style.cssText = 'width:34px;height:34px;border-radius:8px;font-size:18px;display:grid;place-items:center;cursor:pointer;border:none;background:none;';
            btn.onmouseenter = () => { btn.style.background = 'var(--hover)'; btn.style.transform = 'scale(1.2)'; };
            btn.onmouseleave = () => { btn.style.background = ''; btn.style.transform = ''; };
            btn.onclick = () => { this.toggleReaction(msg, e); pop.remove(); };
            pop.appendChild(btn);
        });
        document.body.appendChild(pop);
        const close = (ev) => { if (!pop.contains(ev.target)) { pop.remove(); document.removeEventListener('click', close); } };
        setTimeout(() => document.addEventListener('click', close), 10);
    },

    handleScroll(event) {
        // Could load more messages when scrolled to top
    },

    onTyping() { this.handleTyping(); },

    formatTime(iso) {
        if (!iso) return '';
        return new Date(iso).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    },

    async sendMessage() {
        const body = this.newMessage.trim();
        if (!body && this.selectedFiles.length === 0) return;

        const fd = new FormData();
        if (body) fd.append('body', body);
        if (this.replyTo) fd.append('parent_id', this.replyTo.id);
        if (this.scheduledAt) fd.append('scheduled_at', this.scheduledAt);
        this.selectedFiles.forEach(f => fd.append('attachments[]', f));

        // Optimistic message — shown instantly, replaced when server confirms
        const tempId = 'temp-' + Date.now();
        const optimistic = {
            id: tempId,
            user_id: currentUserId,
            body: body || null,
            type: 'text',
            is_edited: false,
            created_at: new Date().toISOString(),
            sent_at: new Date().toISOString(),
            user: { id: currentUserId, name: document.body.dataset?.userName ?? '', avatar_url: null, is_guest: false },
            attachments: [],
            reactions: [],
            parent: this.replyTo ? { id: this.replyTo.id, body: this.replyTo.body, user: { name: this.replyTo.user?.name } } : null,
            link_previews: [],
            _pending: true,
        };
        this.messages.push(optimistic);
        this.$nextTick(() => this.scrollToBottom());

        const capturedReplyTo = this.replyTo;
        this.newMessage = '';
        this.replyTo = null;
        this.scheduledAt = null;
        this.selectedFiles = [];

        try {
            const socketId = window.Echo?.socketId() ?? null;
            const headers = { 'X-CSRF-TOKEN': csrfToken(), 'Accept': 'application/json' };
            if (socketId) headers['X-Socket-ID'] = socketId;

            const res = await fetch(`/conversations/${conversationId}/messages`, {
                method: 'POST',
                headers,
                body: fd,
            });
            if (!res.ok) throw new Error(`HTTP ${res.status}`);
            const data = await res.json();

            // Replace optimistic message with the confirmed server message
            const idx = this.messages.findIndex(m => m.id === tempId);
            if (idx !== -1) {
                if (data.message) {
                    this.messages.splice(idx, 1, { ...data.message, link_previews: [] });
                } else {
                    this.messages.splice(idx, 1);
                }
            }
            this.$nextTick(() => this.scrollToBottom());
        } catch (e) {
            // Remove optimistic message on failure
            this.messages = this.messages.filter(m => m.id !== tempId);
            console.error('Send failed:', e);
        }
    },

    handleTyping() {
        if (!window.Echo) return;
        clearTimeout(this._typingTimeout);
        window.Echo.private(`conversation.${conversationId}`).whisper('typing', {
            user_id: currentUserId,
            name: document.body.dataset?.userName ?? 'Someone',
        });
        this._typingTimeout = setTimeout(() => {}, 3000);
    },

    async toggleReaction(message, emoji) {
        try {
            const data = await apiFetch(`/messages/${message.id}/reactions`, { method: 'POST', body: JSON.stringify({ emoji }) });
            message.reactions = data.reactions;
        } catch (e) { console.error(e); }
    },

    startEdit(msg) { this.editingMessageId = msg.id; this.editBody = msg.body ?? ''; },

    async saveEdit(msg) {
        if (!this.editBody.trim()) return;
        try {
            await apiFetch(`/messages/${msg.id}`, { method: 'PATCH', body: JSON.stringify({ body: this.editBody }) });
            msg.body = this.editBody; msg.is_edited = true; this.editingMessageId = null;
        } catch (e) { console.error(e); }
    },

    async deleteMessage(msgOrId) {
        const id = typeof msgOrId === 'object' ? msgOrId.id : msgOrId;
        if (!confirm('Delete this message?')) return;
        try {
            await apiFetch(`/messages/${id}`, { method: 'DELETE' });
            this.messages = this.messages.filter(m => m.id !== id);
        } catch (e) { console.error(e); }
    },

    async toggleBookmark(msg) {
        try { await apiFetch(`/messages/${msg.id}/bookmark`, { method: 'POST' }); }
        catch (e) { console.error(e); }
    },

    openForwardModal(msg) { this.forwardingMessage = msg; this.forwardTargets = []; this.forwardModalOpen = true; },

    async forwardMessage() {
        if (!this.forwardingMessage || !this.forwardTargets.length) return;
        try {
            await apiFetch(`/messages/${this.forwardingMessage.id}/forward`, {
                method: 'POST',
                body: JSON.stringify({ conversation_ids: this.forwardTargets }),
            });
            this.forwardModalOpen = false;
        } catch (e) { console.error(e); }
    },

    handleFileSelect(e) { this.selectedFiles = [...this.selectedFiles, ...Array.from(e.target.files)].slice(0, 10); },
    removeFile(i) { this.selectedFiles = this.selectedFiles.filter((_, idx) => idx !== i); },

    async exportChat() {
        try {
            await apiFetch(`/conversations/${conversationId}/export`, {
                method: 'POST',
                body: JSON.stringify({ format: this.exportFormat, from: this.exportFrom || null, to: this.exportTo || null }),
            });
            this.showExportModal = false;
            alert('Export started! You will receive a download link shortly.');
        } catch (e) { console.error(e); }
    },

    async startCall(userId, type) {
        try {
            await apiFetch(`/conversations/${conversationId}/call`, { method: 'POST', body: JSON.stringify({ type }) });
        } catch (e) { alert('Could not start call: ' + e.message); }
    },

    scrollToBottom() {
        const c = this.$refs?.messagesContainer || document.getElementById('messages-container');
        if (c) c.scrollTop = c.scrollHeight;
    },

    markRead() {
        fetch(`/conversations/${conversationId}/read`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken(), 'Accept': 'application/json' },
        }).catch(() => {});
    },
}));

// ─── Poll Widget ─────────────────────────────────────────────────────────────

Alpine.data('pollWidget', (pollId, initialData) => ({
    poll: { options: [], total_votes: 0, is_closed: false, ...initialData },
    loading: false,

    async vote(optionId) {
        if (this.loading || this.poll.is_closed) return;
        this.loading = true;
        try {
            const data = await apiFetch(`/polls/${pollId}/vote`, { method: 'POST', body: JSON.stringify({ option_id: optionId }) });
            Object.assign(this.poll, data);
        } catch (e) { console.error(e); }
        finally { this.loading = false; }
    },

    percentage(option) {
        const total = this.poll.total_votes ?? 0;
        return total > 0 ? Math.round(((option.votes_count ?? 0) / total) * 100) : 0;
    },
}));

// ─── Status Picker ───────────────────────────────────────────────────────────

Alpine.data('statusPicker', (initial) => ({
    open: false,
    statusType: initial?.type ?? 'available',
    statusText: initial?.message ?? '',
    statusEmoji: initial?.emoji ?? '',
    clearAfter: '',
    emojis: ['😊','😎','🤔','😴','🏖️','💼','🎯','🔥','✅','❌','📚','🎮','🍕','☕','🚀','💡','🎵','🏃','🤒','🔕'],

    async save() {
        try {
            await apiFetch('/profile/status', {
                method: 'PATCH',
                body: JSON.stringify({ status_type: this.statusType, status_message: this.statusText, status_emoji: this.statusEmoji, clear_after: this.clearAfter || null }),
            });
            this.open = false;
        } catch (e) { console.error(e); }
    },
}));

// ─── Scheduled Picker ────────────────────────────────────────────────────────

Alpine.data('scheduledPicker', () => ({
    open: false,
    scheduledAt: '',
    minDate: new Date(Date.now() + 60000).toISOString().slice(0, 16),

    applySchedule() {
        if (!this.scheduledAt) return;
        // Find parent chatConversation and set scheduledAt
        const chat = this.$el.closest('[x-data]').__x?.$data;
        if (chat) chat.scheduledAt = this.scheduledAt;
        this.open = false;
    },
}));

window.Alpine = Alpine;
Alpine.start();
