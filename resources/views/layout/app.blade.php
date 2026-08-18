<!doctype html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    @include('partials.pwa-head')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="Bilişim Kod - yapay zeka, kodlama ve robotik odaklı modern eğitim platformu.">
    <title>@yield('title', 'Bilişim Kod | Yapay Zeka, Kodlama, Robotik')</title>
    @php
        $pwaMeta = (array) (auth()->user()?->profile?->meta ?? []);
        $pwaConfig = [
            'enabled' => (bool) ($pwaMeta['pwa_enabled'] ?? false),
            'title' => (string) ($pwaMeta['pwa_title'] ?? config('app.name', 'Egitim Portali')),
            'subtitle' => (string) ($pwaMeta['pwa_subtitle'] ?? 'Yukleniyor...'),
            'logoUrl' => (string) ($pwaMeta['pwa_logo_url'] ?? url('/public/logo.png')),
        ];
    @endphp
