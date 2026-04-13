<!DOCTYPE html>
<html lang="en">
<head>
    <title>CSUCCIS</title>
    
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script>
        window.csrfToken = "{{ csrf_token() }}";
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.ts'])
</head>
<body class="font-sans antialiased bg-[#2a1c15]">
    
    <div id="app" data-page="login"></div>

</body>
</html>