@extends('layouts.app')
@section('title', 'Sign Up | '.$siteSettings->brand_name)
@section('robots', 'noindex, nofollow')
@section('content')
@include('partials.account-form', ['register' => true])
@endsection
