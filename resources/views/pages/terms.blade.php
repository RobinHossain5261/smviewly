@extends('layouts.app')
@section('title', 'Terms of Service | '.$siteSettings->brand_name)
@section('description', 'Terms for using '.$siteSettings->brand_name.' public-content tools, including responsible use, source availability and third-party content.')
@section('canonical', route('terms'))
@section('content')
@php
$pageTitle = 'Terms of Service';
$pageIntro = 'The conditions for using our public-content tools and website.';
$sections = [
    ['Agreement to these terms', ['By accessing and using this service, you agree to these terms. If you do not agree, do not use the tools. These terms apply to your use of the website and its public-content lookup functions.']],
    ['Permitted use', ['The service is intended for personal research, educational review and non-commercial analysis of available public content. Use the tool that matches your input and follow the guidance displayed on its page.', 'Do not use the service for impersonation, harassment, cyberstalking, fraud or other unlawful activity. Do not submit private credentials, attempt to bypass access controls, or automate bulk requests that degrade the service.']],
    ['Public content and privacy boundaries', ['The tools request information made available by supported sources. They do not unlock private profiles, closed groups or restricted posts. Availability in your own signed-in social account does not guarantee that a public lookup can access the same content.', 'Respect other people’s privacy and use only the information relevant to a legitimate task.']],
    ['Third-party content and downloads', ['The service does not claim ownership of third-party posts, photographs, videos or profile material returned by a lookup. Rights remain with their respective owners.', 'Public visibility or a download option does not automatically grant permission to reuse content. Save or use media only when you own it or have appropriate permission. Keep the original source for context and attribution.']],
    ['Availability and accuracy', ['Tools are provided on an as-is and as-available basis. We do not guarantee uninterrupted access, complete results, specific media quality, accuracy or continued compatibility with a source platform.', 'Content may be removed or restricted, and external data services may fail or limit requests. Verify information against its original source before relying on it for an important decision.']],
    ['Accounts and contact messages', ['If you use a website account, keep your credentials secure. Contact messages must not include passwords, private session details or content unrelated to a legitimate support request.', 'How submitted information is handled is described in the Privacy Policy.']],
    ['Changes and questions', ['We may update the service and these terms as functionality changes. Contact support with questions about the tools or these conditions.']],
];
@endphp
@include('partials.legal-page')
@endsection
