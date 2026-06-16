<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Imposta il colore della UI del browser (barra degli indirizzi) sui dispositivi mobili -->
    <meta name="theme-color" content="#d71e2b">

    <title>FilleaGO - Mappa Cantieri</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

    {{-- Passa l'URL di base dell'applicazione a JavaScript --}}
    <script>
        // Definisce un oggetto globale per le variabili dell'app
        window.App = {
            // Usiamo l'helper route() che è il modo più robusto in Laravel per generare URL,
            // specialmente quando l'applicazione è servita da una sottocartella.
            // Questo dovrebbe generare l'URL completo inclusa la sottocartella 'public'.
            cantieriApiUrl: '{{ route("api.cantieri") }}'
        };
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="antialiased">
    <div id="app">
        <header>
            <nav class="navbar navbar-expand-lg navbar-dark" style="background-color: #d71e2b;">
                <div class="container-fluid">
                    <a class="navbar-brand app-logo" href="{{ route('mappa') }}">FilleaGoNext</a>
                    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                        <span class="navbar-toggler-icon"></span>
                    </button>
                    <div class="collapse navbar-collapse" id="navbarNav">
                        <ul class="navbar-nav">
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('mappa') ? 'active' : '' }}" href="{{ route('mappa') }}">Mappa Cantieri</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('elenco') ? 'active' : '' }}" href="{{ route('elenco') }}">Elenco Cantieri</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('vicinanze') ? 'active' : '' }}" href="{{ route('vicinanze') }}">Cantieri nelle mie vicinanze</a>
                            </li>
                        </ul>
                    </div>
                </div>
            </nav>
        </header>
        <main>
            @yield('content')
        </main>
    </div>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>