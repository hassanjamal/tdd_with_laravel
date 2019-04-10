<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Test Driven Laravel</title>

        <!-- Fonts -->
        <link href="https://fonts.googleapis.com/css?family=Nunito:200,600" rel="stylesheet">

        <!-- Styles -->
        <link rel="stylesheet" href="/css/app.css">
    </head>
    <body>
        <div id="app">
            <div class="container mx-auto">
                <header class="py-6">
                    <h1>TDD</h1>
                </header>
                <main class="flex">
                    <aside class="w-1/5">
                       @yield('aside') 
                    </aside>
                    <div class="primary flex-1">
                        @yield('content')
                    </div>
                </main>
            </div>
        </div>
    </body>
</html>
