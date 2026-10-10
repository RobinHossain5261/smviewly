@extends('layouts.admin')
@section('content')
<div class="ad-dashboard">
<div class="ad-page-heading"><div><p class="ad-eyebrow">COMMUNITY</p><h1>Contact messages</h1><p>{{ $contacts->total() }} {{ request('q') ? 'matching' : 'received' }} messages. Open a conversation to read the full message.</p></div><a class="ad-button" href="{{ route('contact') }}">View contact page &nearr;</a></div>
<form method="GET" action="{{ route('admin.contacts.index') }}" class="ad-search"><label for="contact-search">Search inbox</label><div><input id="contact-search" type="search" name="q" maxlength="200" value="{{ request('q') }}" placeholder="Name, email, subject, or message"><button class="ad-button" type="submit">Search</button>@if(request('q'))<a href="{{ route('admin.contacts.index') }}">Clear</a>@endif</div></form>
@if($contacts->isNotEmpty())
<form id="contact-bulk-delete" method="POST" action="{{ route('admin.contacts.bulk-destroy') }}" class="ad-bulk-toolbar">
    @csrf @method('DELETE')
    <label class="ad-select-all"><input type="checkbox" id="contact-select-all"> <span>Select all on this page</span></label>
    <span id="contact-selection-count" role="status" aria-live="polite">0 selected</span>
    <button type="submit" class="ad-delete" id="contact-delete-selected" disabled><i class="ph ph-trash" aria-hidden="true"></i> Delete selected</button>
</form>
@endif
<section class="ad-panel ad-inbox">
@forelse($contacts as $contact)
<div class="ad-selectable-message"><label class="ad-message-select"><input type="checkbox" name="ids[]" value="{{ $contact->id }}" form="contact-bulk-delete" class="contact-selection"><span class="sr-only">Select message from {{ $contact->name }}: {{ $contact->subject ?: 'General enquiry' }}</span></label><details class="ad-message"><summary><span class="ad-avatar">{{ mb_substr($contact->name, 0, 1) }}</span><span class="ad-message-copy"><strong>{{ $contact->subject ?: 'General enquiry' }}</strong><small>{{ $contact->name }} &middot; {{ $contact->created_at->format('M d, Y H:i') }}</small><span class="ad-message-preview">{{ Str::limit($contact->message, 110) }}</span></span><i class="ph ph-caret-down" aria-hidden="true"></i></summary><div class="ad-message-body"><a href="mailto:{{ $contact->email }}">{{ $contact->email }}</a><p>{{ $contact->message }}</p><form action="{{ route('admin.contacts.destroy', $contact->id) }}" method="POST" onsubmit="return confirm('Delete this message permanently?')">@csrf @method('DELETE')<button class="ad-delete" type="submit"><i class="ph ph-trash" aria-hidden="true"></i> Delete message</button></form></div></details></div>
@empty
<div class="ad-empty"><i class="ph ph-envelope-open" aria-hidden="true"></i><strong>{{ request('q') ? 'No matching messages.' : 'No messages yet.' }}</strong><p>{{ request('q') ? 'Try a different name, email, or keyword.' : 'Contact form submissions will appear here.' }}</p></div>
@endforelse
</section><div class="ad-pagination">{{ $contacts->links() }}</div></div>
@endsection
