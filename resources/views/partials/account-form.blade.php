<div class="sm-home sm-account-page"><section class="sm-container sm-account-layout">
    <div class="sm-account-intro"><p class="sm-kicker">{{ $siteSettings->brand_name }} account</p><h1>{{ $register ? 'A fresh start.' : 'Welcome back.' }}<br><em>{{ $register ? 'One place to explore.' : 'Pick up where you left off.' }}</em></h1><p>Your website account is separate from your social accounts. Public lookup tools do not require connecting a Facebook, X, Threads or LinkedIn account.</p><div class="sm-account-note"><i class="ph ph-compass" aria-hidden="true"></i><div><strong>Just looking for a tool?</strong><p>You can browse the toolkit without signing in.</p><a href="{{ route('tools') }}">Explore all tools <i class="ph ph-arrow-up-right" aria-hidden="true"></i></a></div></div></div>
    <div class="sm-account-card"><h2>{{ $register ? 'Create your account.' : 'Sign in to your account.' }}</h2><p>{{ $register ? 'Use your name, email address and a new password.' : 'Enter the email and password you use for this website.' }}</p>
        @if($errors->any())<div class="sm-contact-errors" role="alert"><strong>Please check your details.</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
        <form action="{{ route($register ? 'signup.post' : 'signin.post') }}" method="POST">
            @csrf
            @if($register)<div><label for="account-name">Your name</label><input id="account-name" name="name" required maxlength="255" autocomplete="name" value="{{ old('name') }}" placeholder="Your name"></div>@endif
            <div><label for="account-email">Email address</label><input id="account-email" name="email" type="email" required maxlength="255" autocomplete="email" value="{{ old('email') }}" placeholder="you@example.com"></div>
            <div><label for="account-password">Password</label><div class="sm-account-password"><input id="account-password" name="password" type="password" required {{ $register ? 'minlength=6' : '' }} autocomplete="{{ $register ? 'new-password' : 'current-password' }}" placeholder="{{ $register ? 'At least 6 characters' : 'Your password' }}"><button type="button" data-password-toggle="account-password" aria-label="Show password" aria-pressed="false"><i class="ph ph-eye" aria-hidden="true"></i></button></div></div>
            @if($register)<div><label for="account-password-confirm">Confirm password</label><div class="sm-account-password"><input id="account-password-confirm" name="password_confirmation" type="password" required minlength="6" autocomplete="new-password" placeholder="Enter your password again"><button type="button" data-password-toggle="account-password-confirm" aria-label="Show password" aria-pressed="false"><i class="ph ph-eye" aria-hidden="true"></i></button></div></div>
            <p class="sm-account-policy">Review our <a href="{{ route('terms') }}">Terms of Service</a> and <a href="{{ route('privacy') }}">Privacy Policy</a> before creating an account.</p>
            @else<div class="sm-account-remember"><input id="account-remember" name="remember" type="checkbox" value="1" {{ old('remember') ? 'checked' : '' }}><label for="account-remember">Remember me on this device</label></div>@endif
            <button class="sm-button" type="submit">{{ $register ? 'Sign up' : 'Sign in' }} <i class="ph ph-arrow-right" aria-hidden="true"></i></button>
        </form>
        <p class="sm-account-switch">{{ $register ? 'Already have an account?' : 'New here?' }} <a href="{{ route($register ? 'signin' : 'signup') }}">{{ $register ? 'Sign in' : 'Create an account' }}</a></p>
    </div>
</section></div>
@push('scripts')
<script>
    document.querySelectorAll('[data-password-toggle]').forEach(button => button.addEventListener('click', () => {
        const input = document.getElementById(button.dataset.passwordToggle);
        const visible = input.type === 'password';
        input.type = visible ? 'text' : 'password';
        button.setAttribute('aria-pressed', String(visible));
        button.setAttribute('aria-label', visible ? 'Hide password' : 'Show password');
        button.querySelector('i').className = visible ? 'ph ph-eye-slash' : 'ph ph-eye';
    }));
</script>
@endpush
