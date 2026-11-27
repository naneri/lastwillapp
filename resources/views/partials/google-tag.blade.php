@if (app()->environment('prod'))
    <script async src="https://www.googletagmanager.com/gtm.js?id=GTM-5WMWQH4K"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
    </script>
@endif
