<div 
    id="app"
    data-page="profile-page"
    data-user='@json(Auth::user())'>
</div>
<meta name="csrf-token" content="{{ csrf_token() }}">

@vite(['resources/css/app.css', 'resources/js/app.ts'])