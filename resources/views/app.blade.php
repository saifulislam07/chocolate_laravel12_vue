<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title inertia>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link href="https://fonts.bunny.net/css?family=outfit:400,500,600,700|cormorant-garamond:400,500,600,700" rel="stylesheet" />

        <!-- Scripts -->
        @routes
        @vite(['resources/js/app.js', "resources/js/Pages/{$page['component']}.vue"])
        @inertiaHead

        @php
            $webSettings = \App\Models\WebSetting::first();
        @endphp
        @if($webSettings && $webSettings->favicon)
            <link rel="icon" type="image/png" href="{{ $webSettings->favicon }}">
        @endif
        @if($webSettings?->meta_pixel_enabled && $webSettings?->meta_pixel_id && ! request()->is('admin*'))
            <script>
                !function(f,b,e,v,n,t,s)
                {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
                n.callMethod.apply(n,arguments):n.queue.push(arguments)};
                if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
                n.queue=[];t=b.createElement(e);t.async=!0;
                t.src=v;s=b.getElementsByTagName(e)[0];
                s.parentNode.insertBefore(t,s)}(window, document,'script',
                'https://connect.facebook.net/en_US/fbevents.js');
                fbq('init', @json($webSettings->meta_pixel_id));
                fbq('track', 'PageView');
            </script>
        @endif
    </head>
    <body class="font-sans antialiased">
        @if($webSettings?->meta_pixel_enabled && $webSettings?->meta_pixel_id && ! request()->is('admin*'))
            <noscript>
                <img height="1" width="1" style="display:none"
                    src="https://www.facebook.com/tr?id={{ urlencode($webSettings->meta_pixel_id) }}&ev=PageView&noscript=1"
                    alt="">
            </noscript>
        @endif
        @inertia
    </body>
</html>
