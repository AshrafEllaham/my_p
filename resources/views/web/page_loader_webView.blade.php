@php
    $logo = isset($settings->logo_header)
        ? get_file($settings->logo_header)
        : asset('admin/login/imgs/Frame 36125.svg');

@endphp


<div class="preloader">
    <div class="preloaderImg"
        style="-webkit-mask-image: url('{{ $logo }}');
    mask-image: url('{{ $logo }}');"></div>
</div>
<style>
    .preloader {
        background-color: white;
        position: fixed;
        top: 0;
        left: 0;
        z-index: 9999;
        width: 100%;
        height: 100%;
        display: flex;
        justify-content: center;
        align-items: center;
    }

    .preloader .preloaderImg {
        width: 100px;
        height: 100px;

        -webkit-mask-size: contain;
        mask-size: contain;
        -webkit-mask-position: center;
        mask-position: center;
        -webkit-mask-repeat: no-repeat;
        mask-repeat: no-repeat;
        position: relative;
    }

    @media screen and (max-width: 1024px) {
        .preloader .preloaderImg {
            width: 80px;
            height: 80px;
        }
    }

    .preloader .preloaderImg::after {
        content: "";
        position: absolute;
        left: 0;
        top: 0;
        width: 100px;
        height: 100px;
        z-index: 2;
        background-color: #276ee6;
        transform: translateY(100%);
        animation: slide-up 2.5s forwards;
    }

    @media screen and (max-width: 1024px) {
        .preloader .preloaderImg::after {
            width: 80px;
            height: 80px;
        }
    }

    .preloader .preloaderImg::before {
        content: "";
        display: block;
        width: 100px;
        height: 100px;
        -o-object-fit: contain;
        object-fit: contain;
        background-image: url("{{ $logo }}");
        background-size: contain;
        background-position: center;
        background-repeat: no-repeat;
        filter: grayscale(1) opacity(0.2);
    }

    @keyframes slide-up {
        from {
            transform: translateY(100%);
        }

        to {
            transform: translateY(0%);
        }
    }
</style>
