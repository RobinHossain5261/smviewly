@extends('layouts.app')
@section('title', 'Privacy Policy | '.$siteSettings->brand_name)
@section('description', 'How '.$siteSettings->brand_name.' handles lookup inputs, contact messages, account information, sessions and external services.')
@section('canonical', route('privacy'))
@section('content')
@php
$pageTitle = 'Privacy Policy';
$pageIntro = 'How this website handles information when you browse tools, create an account, or contact our team.';
$sections = [
    ['Using public-content tools', ['Our tools request available public information from supported social platforms through a data service. You do not need to connect your personal social account or provide a social password to run a public lookup.', 'Your lookup input, selected platform and tool are processed to fulfill the request. Private and restricted content remains outside the scope of these tools.']],
    ['Information you provide', ['When you send a contact message, we store your name, email address, optional subject, message and submission time so our team can review it. Authorized administrators can view and manage these submissions.', 'If you create a website account, account information is stored for authentication. Passwords are stored as hashes. Do not send social passwords, login cookies or unnecessary sensitive information through the contact form.']],
    ['Sessions and security', ['The website uses sessions and cookies for functions such as sign-in, security-code validation and form feedback. These are different from connecting your social account.', 'Operational infrastructure may process request information for security, troubleshooting and service delivery. We do not promise that every server or external service operates without logs.']],
    ['External services and content', ['Lookup requests are sent to an external data service with the platform and input needed to complete the request. Media previews and files can also involve external source infrastructure. Those services have their own information-handling practices.', 'Facebook, X, Threads and LinkedIn control the availability of their content. Fonts, icons or other configured assets may load from external hosts. Administrators may configure additional site scripts; the behavior of a deployed website depends on that configuration.']],
    ['Managing your information', ['Contact us with questions about information you submitted or requests concerning your account or contact message. Provide enough detail to identify the request without sending passwords. We may need to verify a request before making changes.', 'Information is used to operate the website, provide requested functions and manage support. This page does not promise a fixed deletion schedule or an absolute anonymity guarantee.']],
    ['Changes to this page', ['We may update this page when the website’s functionality or information handling changes. Review the current page before submitting information if you have questions about how the service works.']],
];
@endphp
@include('partials.legal-page')
@endsection
