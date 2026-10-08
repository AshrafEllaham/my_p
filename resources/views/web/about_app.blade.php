<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <title>{{ $settings?->website_name ?? '' }}</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=IBM+Plex+Sans+Arabic:wght@100;200;300;400;500;600;700&display=swap');

        * {
            font-family: "IBM Plex Sans Arabic", sans-serif;
        }

        .custom-card {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .logo {
            width: 100px;
            /* Adjust as needed */
            margin-bottom: 20px;
        }
    </style>

</head>

<body>
    @include('web.page_loader_webView')
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="custom-card text-center">
                    <img src="{{ get_file(isset($settings) ? $settings->logo_header : asset('webview/Assets/images/logo.png')) }}"
                        class="banner" alt="" style="width: 50%;margin-bottom: 20px;" />
                    <main class="main">
                        <div class="WhoWeAre">
                            <div>
                                <div class="Title">
                                    <h3>{{ __('about app') }}</h3>
                                </div>

                                <div class="DESC">
                                    {!! $settings?->about_app ?? '' !!}
                                </div>
                            </div>
                        </div>
                    </main>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"
        integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        $(document).ready(function() {
            $(".preloader").delay(1200).fadeOut(300);
        });
    </script>
    {{-- <script>
        function changeLanguage(url) {
            if (url) {
                window.location.href = LaravelLocalization::getLocalizedURL($language);
            }
        }
    </script> --}}
</body>

</html>
