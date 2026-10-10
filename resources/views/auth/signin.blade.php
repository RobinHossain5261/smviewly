@extends('layouts.app')
@section('title', 'Sign In | '.$siteSettings->brand_name)
@section('robots', 'noindex, nofollow')
@section('content')
@include('partials.account-form', ['register' => false])
@endsection
