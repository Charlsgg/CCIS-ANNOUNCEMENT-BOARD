<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>CSU CCIS</title>

    @vite(['resources/css/app.css', 'resources/js/app.ts'])
</head>
<body class="bg-[#221610]">

    <div 
        id="app"
        data-page="forgot-password"
        data-user='@json(Auth::user())'>
    </div>

</body>
</html>