<html lang="En">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="description" content="" />

    <title>Payment</title>
    <link rel="stylesheet" href="css/style.css" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
        href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;1,100;1,200;1,300;1,400;1,500;1,600;1,700&display=swap"
        rel="stylesheet" />

    <style>
        * {
            box-sizing: border-box;
            padding: 0;
            margin: 0;
            font-family: "IBM Plex Sans", sans-serif;
            font-style: normal;
        }

        .page {
            padding: 5px 10px;
            background-image: url(https://img.freepik.com/free-photo/abstract-blur-empty-green-gradient-studio-well-use-as-background-website-template-frame-business-report_1258-72517.jpg?t=st=1719736808~exp=1719740408~hmac=f99762645ff9c3315edfd8e07cdd5bcf5c7bbc68050e05e9f76abc3c65e9894a&w=740);
            display: flex;
            flex-direction: column;
            background-size: cover;
            gap: 6rem;
            min-height: 100vh;
        }

        .Payment_Title {
            color: #fff;
            display: flex;
            align-items: center;
            gap: 15px;
            padding-top: 22px;
        }

        .payment_Body {
            position: relative;
        }

        .payment_Body::after {
            content: "";
            border-radius: 50%;
            position: absolute;
            top: 0%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 150px;
            height: 150px;
            opacity: 0.2;
            background-color: #25ff99;
        }

        .payment_Body .inner {
            width: min(100% - 24px, 500px);
            margin: auto;
            display: flex;
            flex-direction: column;
            gap: 4rem;
            color: #f7f7f7;
            background-color: #1c1c1c;
            padding: 10px 22px;
            border-radius: 18px;
            text-align: center;
            box-shadow: rgba(255, 255, 255, 0.1) 0px 1px 1px 0px inset,
                rgba(50, 50, 93, 0.25) 0px 50px 100px -20px,
                rgba(0, 0, 0, 0.3) 0px 30px 60px -30px;
            position: relative;
            z-index: 3;
            padding-top: 120px;
        }

        .payment_Body .inner .icon {
            width: 100px;
            text-align: center;
            padding: 20px;
            margin: 0 auto;
            position: absolute;
            top: 0;
            right: 50%;
            transform: translateX(50%) translateY(-50%);
        }

        .payment_Body .inner .icon svg {
            z-index: 3;
        }

        .payment_Body .inner .icon::before {
            content: "";
            border-radius: 50%;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background-color: #12b76a;
            width: 100%;
            height: 100%;
            opacity: 0.3;
            z-index: 2;
            -webkit-backdrop-filter: blur(8px);
            backdrop-filter: blur(8px);
        }

        .payment_Body .inner .icon svg {
            width: 55px;
            height: 55px;
            z-index: 3;
            position: relative;
        }

        .payment_Body .inner .Body_Title h3 {
            font-size: 35px;
            color: #f7f7f7;
            padding: 5px;
        }

        .payment_Body .inner .Body_Title p {
            font-size: 19px;
            color: #8b8989;
        }

        .payment_Body .inner .price {
            text-align: start;
            background-color: #292929;
            padding: 20px 10px;
            border-radius: 15px;
            box-shadow: rgba(0, 0, 0, 0.05) 0px 0px 0px 1px;
        }

        .payment_Body .inner .price h4 {
            font-size: 29px;
            padding: 20px;
        }

        .payment_Body .inner .price p {
            color: #8b8989;
            padding-top: 10px;
        }

        .payment_Body .inner .price h5 {
            color: #fff;
            padding-top: 10px;
        }

        .payment_Body .inner .ref {
            display: flex;
            gap: 20px;
            padding: 20px;
        }

        .payment_Body .inner .ref .order p {
            color: #868686;
            padding-bottom: 12px;
        }

        .payment_Body .inner .ref .order span {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .payment_Body .inner .ref .order span h4 {
            margin: 0;
            padding: 0;
        }

        /*# sourceMappingURL=style.css.map */
    </style>
</head>

<body>
    <!-- header -->
    <div class="page"
        style="
        background-image: url(https://img.freepik.com/free-photo/abstract-blur-empty-green-gradient-studio-well-use-as-background-website-template-frame-business-report_1258-72517.jpg?t=st=1719736808~exp=1719740408~hmac=f99762645ff9c3315edfd8e07cdd5bcf5c7bbc68050e05e9f76abc3c65e9894a&w=740);
      ">
        <div class="container">
            <div class="Payment_Title">
            </div>
        </div>

        <section class="payment_Body">
            <div class="inner">
                <div class="icon">
                    <svg fill="#fff" version="1.1" id="Capa_1" class="img" xmlns="http://www.w3.org/2000/svg"
                        xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 78.369 78.369" xml:space="preserve"
                        stroke="#fff">
                        <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                        <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
                        <g id="SVGRepo_iconCarrier">
                            <g>
                                <path
                                    d="M78.049,19.015L29.458,67.606c-0.428,0.428-1.121,0.428-1.548,0L0.32,40.015c-0.427-0.426-0.427-1.119,0-1.547l6.704-6.704 c0.428-0.427,1.121-0.427,1.548,0l20.113,20.112l41.113-41.113c0.429-0.427,1.12-0.427,1.548,0l6.703,6.704 C78.477,17.894,78.477,18.586,78.049,19.015z">
                                </path>
                            </g>
                        </g>
                    </svg>
                </div>

                <div class="Body_Title">
                    <h3>تمت عملية الدفع بنجاح</h3>
                    {{-- <p>Funds well arive in 4 Hours</p> --}}
                </div>

                <div class="Price" style="text-align: center">
                    <h4>مدفوع</h4>
                    <hr />

                    {{-- <p>Merchent Name</p>
                    <h5>Swellcrop</h5> --}}
                </div>

                {{-- <div class="ref">
                    <div class="order">
                        <p>Order Referance</p>
                        <span>
                            <h4>Dario</h4>
                            <svg fill="#fff" height="20px" width="20px" version="1.1" id="Capa_1"
                                xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"
                                viewBox="0 0 29.536 29.536" xml:space="preserve" stroke="#fff">
                                <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
                                <g id="SVGRepo_iconCarrier">
                                    <g>
                                        <path
                                            d="M14.768,0C6.611,0,0,6.609,0,14.768c0,8.155,6.611,14.767,14.768,14.767s14.768-6.612,14.768-14.767 C29.535,6.609,22.924,0,14.768,0z M14.768,27.126c-6.828,0-12.361-5.532-12.361-12.359c0-6.828,5.533-12.362,12.361-12.362 c6.826,0,12.359,5.535,12.359,12.362C27.127,21.594,21.594,27.126,14.768,27.126z">
                                        </path>
                                        <path
                                            d="M14.385,19.337c-1.338,0-2.289,0.951-2.289,2.34c0,1.336,0.926,2.339,2.289,2.339c1.414,0,2.314-1.003,2.314-2.339 C16.672,20.288,15.771,19.337,14.385,19.337z">
                                        </path>
                                        <path
                                            d="M14.742,6.092c-1.824,0-3.34,0.513-4.293,1.053l0.875,2.804c0.668-0.462,1.697-0.772,2.545-0.772 c1.285,0.027,1.879,0.644,1.879,1.543c0,0.85-0.67,1.697-1.494,2.701c-1.156,1.364-1.594,2.701-1.516,4.012l0.025,0.669h3.42 v-0.463c-0.025-1.158,0.387-2.162,1.311-3.215c0.979-1.08,2.211-2.366,2.211-4.321C19.705,7.968,18.139,6.092,14.742,6.092z">
                                        </path>
                                        <g></g>
                                        <g></g>
                                        <g></g>
                                        <g></g>
                                        <g></g>
                                        <g></g>
                                        <g></g>
                                        <g></g>
                                        <g></g>
                                        <g></g>
                                        <g></g>
                                        <g></g>
                                        <g></g>
                                        <g></g>
                                        <g></g>
                                    </g>
                                </g>
                            </svg>
                        </span>
                    </div>

                    <div class="order">
                        <p>Memo</p>
                        <h4>Guitar Lessons</h4>
                    </div>
                </div> --}}
            </div>
        </section>
    </div>

    <!-- js -->
</body>

</html>
