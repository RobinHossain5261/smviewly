@extends('layouts.app')
@section('title', 'Contact '.$siteSettings->brand_name.' | Support & Feedback')
@section('description', 'Send questions, feedback or a public lookup issue to '.$siteSettings->brand_name.'. Contact details and a secure message form.')
@section('canonical', route('contact'))
@section('content')
<div class="sm-home sm-contact-page">
    <section class="sm-hub-hero"><div class="sm-container"><nav class="sm-hub-breadcrumb" aria-label="Breadcrumb"><a href="{{ route('home') }}">Home</a><i class="ph ph-caret-right" aria-hidden="true"></i><span>Contact</span></nav><p class="sm-kicker">We are listening</p><h1>A question, an idea,<br><em>or something to fix?</em></h1><p class="sm-hub-description">Tell us what you are working on or where you got stuck. Share a public link and the tool name if your message concerns a lookup.</p></div></section>
    <div class="sm-section-band"><div class="sm-container sm-contact-layout">
        <aside class="sm-contact-info"><p class="sm-kicker">Get in touch</p><h2>Start a conversation.</h2><p>Use the form to send a message to our team, or contact us using the details below.</p>
            @if(!empty($siteSettings->contact_email))<div class="sm-contact-channel"><span class="sm-platform-icon"><i class="ph ph-envelope" aria-hidden="true"></i></span><div><h3>Email support</h3><a href="mailto:{{ $siteSettings->contact_email }}">{{ $siteSettings->contact_email }}</a></div></div>@endif
            @if(!empty($siteSettings->contact_phone))<div class="sm-contact-channel"><span class="sm-platform-icon"><i class="ph ph-phone" aria-hidden="true"></i></span><div><h3>Phone</h3><a href="tel:{{ preg_replace('/[^+0-9]/', '', $siteSettings->contact_phone) }}">{{ $siteSettings->contact_phone }}</a></div></div>@endif
            <div class="sm-contact-note"><h3>A little context helps</h3><ul><li>Which platform and tool were you using?</li><li>What public link or input did you try?</li><li>What happened, and what did you expect?</li></ul><p>Please do not send passwords, login cookies, or private account information.</p></div>
            <a class="sm-text-link" href="{{ route('blog.index') }}">Browse practical guides <i class="ph ph-arrow-up-right" aria-hidden="true"></i></a>
        </aside>
        <section class="sm-contact-form-card" aria-labelledby="contact-form-title"><h2 id="contact-form-title">Send us a message.</h2><p>Your message will be saved for our team to review.</p>
            @if(session('success'))
            <div data-toast-message="{{ session('success') }}" hidden></div>
            <noscript><div class="sm-contact-success" role="status">{{ session('success') }}</div></noscript>
            @endif
            @if($errors->any())<div class="sm-contact-errors" role="alert"><strong>Please check the form.</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
            <form action="{{ route('contact.submit') }}" method="POST">
                @csrf
                <div class="sm-contact-fields"><div><label for="contact-name">Your name</label><input id="contact-name" name="name" value="{{ old('name') }}" required maxlength="255" autocomplete="name" placeholder="Your name"></div><div><label for="contact-email">Email address</label><input id="contact-email" name="email" type="email" value="{{ old('email') }}" required maxlength="255" autocomplete="email" placeholder="you@example.com"></div></div>
                <div><label for="contact-subject">Subject <span>(optional)</span></label><input id="contact-subject" name="subject" value="{{ old('subject') }}" maxlength="255" placeholder="What would you like to discuss?"></div>
                <div><label for="contact-message">Your message</label><textarea id="contact-message" name="message" rows="6" required minlength="10" maxlength="10000" placeholder="Describe your question or issue, including a public link if relevant.">{{ old('message') }}</textarea></div>
                <div><label for="contact-captcha">Security code</label><div class="sm-contact-security"><span class="captcha-img">{!! captcha_img('easy') !!}</span><button type="button" id="contact-refresh" aria-label="Refresh security code"><i class="ph ph-arrows-clockwise" aria-hidden="true"></i></button><input id="contact-captcha" name="captcha" required maxlength="20" autocomplete="off" placeholder="Enter the code"></div><p id="contact-refresh-status" role="status"></p></div>
                <div class="sm-contact-submit"><p>Read our <a href="{{ route('privacy') }}">privacy policy</a> for how we handle your information.</p><button class="sm-button" type="submit">Send message <i class="ph ph-arrow-right" aria-hidden="true"></i></button></div>
            </form>
        </section>
    </div></div>
</div>
@endsection
@push('scripts')
<script>
    document.getElementById('contact-refresh').addEventListener('click', async function () {
        this.disabled = true;
        const status = document.getElementById('contact-refresh-status');
        try {
            const response = await fetch('{{ url('/reload-captcha') }}', {headers: {'Accept': 'application/json'}});
            if (!response.ok) throw new Error('Refresh failed');
            const data = await response.json();
            document.querySelector('.sm-contact-security .captcha-img').innerHTML = data.captcha;
            document.getElementById('contact-captcha').value = '';
            status.textContent = 'New security code ready.';
        } catch (_) { status.textContent = 'Could not refresh the code. Please try again.'; }
        finally { this.disabled = false; }
    });
</script>
@endpush
