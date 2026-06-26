@extends('layouts.app')
@section('title', 'Messages')

@section('list-panel')
<div class="list-head">
    <div class="list-title-row">
        <h1 class="list-title">Messages</h1>
        <button class="list-new" title="New message">
            <svg width="19" height="19" viewBox="0 0 24 24" fill="none"><path d="M16.5 4.5 19.5 7.5 9 18l-3.6.6.6-3.6L16.5 4.5Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/></svg>
        </button>
    </div>
    <div class="search">
        <svg width="17" height="17" viewBox="0 0 24 24" fill="none"><circle cx="11" cy="11" r="6.5" stroke="currentColor" stroke-width="1.8"/><path d="m20 20-3.2-3.2" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
        <input id="search" placeholder="Search messages" />
    </div>
</div>
<div class="filters">
    <button class="filter on">All</button>
    <button class="filter">Unread</button>
    <button class="filter">Groups</button>
    <button class="filter">Scheduled</button>
</div>
<div id="convoList"></div>
@endsection

@section('content')
<header id="chatHeader"></header>
<div id="threadSearch">
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none"><circle cx="11" cy="11" r="6.5" stroke="currentColor" stroke-width="1.8"/><path d="m20 20-3.2-3.2" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
    <input id="threadSearchInput" placeholder="Search in conversation" />
    <span class="ts-count" id="tsCount"></span>
    <button class="ts-nav" id="tsPrev" title="Previous"><svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M18 15l-6-6-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg></button>
    <button class="ts-nav" id="tsNext" title="Next"><svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M6 9l6 6 6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg></button>
    <button class="ts-close" id="tsClose" title="Close"><svg width="17" height="17" viewBox="0 0 24 24" fill="none"><path d="M6 6l12 12M18 6 6 18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg></button>
</div>
<div id="thread"></div>
<div id="replyBar"></div>
<div id="attachTray"></div>
<input type="file" id="fileInput" multiple accept="image/*,.pdf,.doc,.docx,.zip,.fig" style="display:none">
<div class="composer-wrap">
    <div class="composer" id="composer-tools">
        <div id="composer-field">
            {{-- Left side: attach + emoji --}}
            <button class="ct" data-ct="plus" title="Attach file">
                <svg width="19" height="19" viewBox="0 0 24 24" fill="none"><path d="M21.44 11.05l-9.19 9.19a6 6 0 01-8.49-8.49l9.19-9.19a4 4 0 015.66 5.66l-9.2 9.19a2 2 0 01-2.83-2.83l8.49-8.48" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </button>
            <button class="ct" data-ct="emoji" title="Emoji">
                <svg width="19" height="19" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="8.3" stroke="currentColor" stroke-width="1.7"/><circle cx="9.3" cy="10.3" r="1.1" fill="currentColor"/><circle cx="14.7" cy="10.3" r="1.1" fill="currentColor"/><path d="M8.8 14.2a4 4 0 006.4 0" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg>
            </button>
            {{-- Text input --}}
            <div id="composer" contenteditable="true" data-placeholder="Message…"></div>
            {{-- Right side of input: schedule + mic --}}
            <button class="ct" data-ct="schedule" title="Schedule message">
                <svg width="19" height="19" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="8.3" stroke="currentColor" stroke-width="1.7"/><path d="M12 8v4.5l3 2" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg>
            </button>
            <button id="micBtn" title="Record voice message" class="ct">
                <svg width="19" height="19" viewBox="0 0 24 24" fill="none"><rect x="9" y="3" width="6" height="11" rx="3" stroke="currentColor" stroke-width="1.8"/><path d="M5.5 11a6.5 6.5 0 0013 0M12 17.5V21M8.5 21h7" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
            </button>
        </div>
        {{-- Send button always visible outside the field --}}
        <button id="sendBtn" title="Send">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M5 12 19 5l-4 14-3.5-5.5L5 12Z" stroke="currentColor" stroke-width="1.9" stroke-linejoin="round"/></svg>
        </button>
    </div>
    <div id="recBar">
        <button id="recCancel" title="Cancel" class="ct">
            <svg width="19" height="19" viewBox="0 0 24 24" fill="none"><path d="M5 7h14M10 7V5h4v2M6 7l1 13h10l1-13" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </button>
        <span class="rec-dot"></span>
        <span id="recTime">0:00</span>
        <div class="rec-wave"><span></span><span></span><span></span><span></span><span></span><span></span><span></span><span></span><span></span><span></span><span></span><span></span><span></span><span></span><span></span><span></span><span></span><span></span><span></span><span></span></div>
        <span class="rec-hint">‹ slide to cancel</span>
        <button id="recSend" title="Send voice message">
            <svg width="19" height="19" viewBox="0 0 24 24" fill="none"><path d="M5 12 19 5l-4 14-3.5-5.5L5 12Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/></svg>
        </button>
    </div>
</div>
@endsection

@section('scripts')
<script>
window.CP = {!! $cpData !!};
window.CP_ROUTES = {!! $cpRoutes !!};
@if($activeConvIdStr)
window.CP.activeId = '{{ $activeConvIdStr }}';
@endif
</script>
<script src="/js/chatpulsemodals.js"></script>
<script src="/js/chatpulseoverlays.js"></script>
<script src="/js/chatpulseaccount.js"></script>
<script src="/js/chatpulseapp.js"></script>
@endsection
