<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">

<!-- SEO Meta Tags -->
<title>@yield('title', 'Lighthouse Academy - Leading Christian Education in Africa')</title>
<meta name="description" content="@yield('meta_description', 'Lighthouse Leading Academy offers world-class Christian education with excellence in academics, character development, and spiritual growth.')">
<meta name="keywords" content="@yield('meta_keywords', 'lighthouse academy, christian education, school, makurdi, benue state, nigeria, education, academy')">
<meta name="author" content="Lighthouse Leading Academy">

<!-- Open Graph / Facebook -->
<meta property="og:type" content="@yield('og_type', 'website')">
<meta property="og:url" content="{{ request()->url() }}">
<meta property="og:title" content="@yield('og_title', 'Lighthouse Academy - Leading Christian Education')">
<meta property="og:description" content="@yield('og_description', 'Excellence in Christian education with world-class facilities and dedicated faculty.')">
<meta property="og:image" content="@yield('og_image', asset('images/logo2.png'))">

<!-- Twitter -->
<meta property="twitter:card" content="summary_large_image">
<meta property="twitter:url" content="{{ request()->url() }}">
<meta property="twitter:title" content="@yield('twitter_title', 'Lighthouse Academy - Leading Christian Education')">
<meta property="twitter:description" content="@yield('twitter_description', 'Excellence in Christian education with world-class facilities and dedicated faculty.')">
<meta property="twitter:image" content="@yield('twitter_image', asset('images/logo2.png'))">

<!-- Canonical URL -->
<link rel="canonical" href="{{ request()->url() }}">

<!-- Favicon -->
<link rel="icon" href="{{ asset('images/favicon.png') }}" type="image/x-icon">
<link rel="shortcut icon" href="{{ asset('images/favicon.png') }}" type="image/x-icon">
<link rel="apple-touch-icon" href="{{ asset('images/favicon.png') }}">

<!-- Fonts -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,300;0,400;0,500;0,700;0,900;1,300;1,400;1,500;1,700;1,900&display=swap" rel="stylesheet">

<!-- CSS Files -->
<link rel="stylesheet" href="{{ asset('css/preset.css') }}">
<link rel="stylesheet" href="{{ asset('css/theme.css') }}">
<link rel="stylesheet" href="{{ asset('css/responsive.css') }}">
<link rel="stylesheet" href="{{ asset('css/animate.css') }}">
<link rel="stylesheet" href="{{ asset('css/settings.css') }}">
<link rel="stylesheet" href="{{ asset('css/quera-icon.css') }}">
<link rel="stylesheet" href="{{ asset('css/themewar-font.css') }}">
<link rel="stylesheet" href="{{ asset('css/owl.carousel.min.css') }}">
<link rel="stylesheet" href="{{ asset('css/owl.theme.default.min.css') }}">
<link rel="stylesheet" href="{{ asset('css/nice-select.css') }}">
<link rel="stylesheet" href="{{ asset('css/lightcase.css') }}">
<link rel="stylesheet" href="{{ asset('css/jquery.datetimepicker.min.css') }}">

<!-- Laravel Mix CSS -->
<link rel="stylesheet" href="{{ mix('css/website.css') }}">

<!-- Google Analytics 4 -->
@if(config('services.google.analytics_id'))
<script async src="https://www.googletagmanager.com/gtag/js?id={{ config('services.google.analytics_id') }}"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());
  gtag('config', '{{ config('services.google.analytics_id') }}');
</script>
@endif

<!-- PWA Manifest -->
<link rel="manifest" href="{{ asset('manifest.json') }}">
<meta name="theme-color" content="#007bff">

<!-- Structured Data -->
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "EducationalOrganization",
  "name": "Lighthouse Leading Academy",
  "url": "{{ config('app.url') }}",
  "logo": "{{ asset('images/logo2.png') }}",
  "description": "A Christian educational institution providing world-class education in Makurdi, Benue State, Nigeria.",
  "address": {
    "@type": "PostalAddress",
    "streetAddress": "No. 20 Genabe Zone, beside Shammah Plaza, Welfare Quarters",
    "addressLocality": "Makurdi",
    "addressRegion": "Benue State",
    "addressCountry": "Nigeria"
  },
  "telephone": "+2348127823406",
  "email": "support@llacademy.ng",
  "foundingDate": "2020",
  "sameAs": [
    "https://facebook.com/lighthouseacademy",
    "https://twitter.com/lighthouseacademy",
    "https://instagram.com/lighthouseacademy"
  ]
}
</script>

@stack('head')