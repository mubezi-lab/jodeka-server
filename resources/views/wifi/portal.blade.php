<!DOCTYPE html>
<html lang="sw">

<head>
    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Jodeka Wi-Fi</title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, Helvetica, sans-serif;
        }

        :root {
            --bg: #06101d;
            --panel: #030a14;
            --card: #0b1829;

            --white: #f8fafc;
            --muted: #a7b3c6;

            --green: #00df82;
            --cyan: #19c9ed;
            --blue: #2d8cff;

            --border: rgba(80, 151, 215, .23);
        }

        body {
            min-height: 100vh;

            display: flex;
            justify-content: center;

            padding: 20px 14px;

            color: var(--white);

            background:
                radial-gradient(circle at top,
                    #112947 0%,
                    #071525 42%,
                    #030913 100%);
        }

        .portal {
            width: 100%;
            max-width: 500px;

            padding: 25px;

            border: 1px solid var(--border);
            border-radius: 30px;

            background:
                linear-gradient(180deg,
                    rgba(4, 13, 25, .98),
                    rgba(2, 9, 18, .99));

            box-shadow:
                0 30px 90px rgba(0, 0, 0, .45);
        }

        /*
        |--------------------------------------------------------------------------
        | HEADER
        |--------------------------------------------------------------------------
        */

        .header {
            width: 100%;

            display: flex;
            align-items: center;
            justify-content: space-between;

            margin-bottom: 6px;
        }

        .logo {
            display: flex;
            align-items: center;
        }

        .logo img {
            width: 72px;
            height: 72px;

            display: block;

            object-fit: contain;

            animation:
                jodeka-spin 10s linear infinite;
        }

        @keyframes jodeka-spin {
            from {
                transform: rotate(0deg);
            }

            to {
                transform: rotate(360deg);
            }
        }

        /*
        |--------------------------------------------------------------------------
        | LANGUAGE
        |--------------------------------------------------------------------------
        */

        .language {
            display: flex;
            align-items: center;

            padding: 3px;

            border-radius: 30px;

            background: #17243a;

            border:
                1px solid rgba(148, 163, 184, .09);
        }

        .language button {
            min-width: 47px;

            padding: 9px 13px;

            border: none;
            border-radius: 25px;

            background: transparent;

            color: #d5deea;

            cursor: pointer;

            font-size: 14px;
            font-weight: 800;
        }

        .language button.active {
            color: #ffffff;

            background:
                linear-gradient(135deg,
                    #00c96d,
                    #00e784);
        }

        /*
        |--------------------------------------------------------------------------
        | TITLE
        |--------------------------------------------------------------------------
        */

        .title {
            margin-top: 4px;
            margin-bottom: 24px;

            text-align: center;
        }

        .title h1 {
            margin-bottom: 5px;

            color: #ffffff;

            font-size: 30px;
            font-weight: 800;
        }

        .title p {
            color: #b8c4d4;

            font-size: 14px;
        }

        /*
        |--------------------------------------------------------------------------
        | GENERAL CARD
        |--------------------------------------------------------------------------
        */

        .card {
            margin-bottom: 18px;

            padding: 20px;

            border:
                1px solid var(--border);

            border-radius: 19px;

            background:
                linear-gradient(145deg,
                    rgba(13, 30, 51, .97),
                    rgba(7, 18, 32, .98));
        }

        /*
        |--------------------------------------------------------------------------
        | PAYMENT
        |--------------------------------------------------------------------------
        */

        .payment-title {
            margin-bottom: 18px;

            color: var(--green);

            text-align: center;

            font-size: 18px;
            font-weight: 800;
        }

        .payment-grid {
            display: grid;

            grid-template-columns:
                repeat(2, minmax(0, 1fr));

            text-align: center;
        }

        .payment-item:first-child {
            border-right:
                1px solid rgba(148, 163, 184, .22);
        }

        .payment-label {
            margin-bottom: 8px;

            color: #d2dbe7;

            font-size: 11px;
            font-weight: 600;
        }

        .payment-value {
            font-size: 22px;
            font-weight: 800;
        }

        .green {
            color: var(--green);
        }

        .blue {
            color: var(--blue);
        }

        .payment-note {
            margin-top: 16px;

            color: #dce4ee;

            text-align: center;

            font-size: 12px;
            line-height: 1.6;
        }

        /*
        |--------------------------------------------------------------------------
        | PACKAGES
        |--------------------------------------------------------------------------
        */

        .packages {
            margin-bottom: 18px;
        }

        .packages-title {
            margin-bottom: 12px;

            color: #ffffff;

            font-size: 14px;
            font-weight: 800;
        }

        .package-grid {
            display: grid;

            grid-template-columns:
                repeat(2, minmax(0, 1fr));

            gap: 12px;
        }

        .package {
            padding: 17px 12px;

            text-align: center;

            border:
                1px solid var(--border);

            border-radius: 16px;

            background:
                linear-gradient(145deg,
                    #0d1b2d,
                    #081525);
        }

        .package-price {
            margin-bottom: 9px;

            color: var(--green);

            font-size: 20px;
            font-weight: 800;
        }

        .package-duration {
            padding-top: 9px;

            border-top:
                1px solid rgba(148, 163, 184, .22);

            color: #ffffff;

            font-size: 14px;
            font-weight: 700;
        }

        /*
        |--------------------------------------------------------------------------
        | VOUCHER INPUT
        |--------------------------------------------------------------------------
        */

        .voucher-area {
            margin-bottom: 18px;
        }

        .voucher-input {
            width: 100%;

            margin-bottom: 11px;

            padding: 15px 17px;

            border:
                1px solid rgba(91, 155, 211, .35);

            border-radius: 12px;

            outline: none;

            color: #ffffff;

            background:
                linear-gradient(145deg,
                    #11243b,
                    #0e1d31);

            font-size: 16px;
        }

        .voucher-input::placeholder {
            color: #92a1b7;
        }

        .voucher-input:focus {
            border-color:
                rgba(0, 223, 130, .65);
        }

        /*
        |--------------------------------------------------------------------------
        | CONNECT BUTTON
        |--------------------------------------------------------------------------
        */

        .connect-button {
            width: 100%;

            display: flex;
            align-items: center;
            justify-content: center;

            gap: 12px;

            padding: 15px;

            border: none;
            border-radius: 13px;

            cursor: pointer;

            color: #ffffff;

            background:
                linear-gradient(105deg,
                    #00dd78,
                    #16cbed);

            font-size: 17px;
            font-weight: 800;

            box-shadow:
                0 10px 25px rgba(0, 215, 120, .15);
        }

        .connect-button:disabled {
            opacity: .55;
            cursor: not-allowed;
        }

        /*
        |--------------------------------------------------------------------------
        | WI-FI ICON
        |--------------------------------------------------------------------------
        */

        .wifi-icon {
            position: relative;

            width: 30px;
            height: 23px;

            display: inline-block;
        }

        .wifi-icon span {
            position: absolute;

            left: 50%;

            transform:
                translateX(-50%);

            border-style: solid;

            border-color:
                #ffffff transparent transparent transparent;

            border-radius: 50%;
        }

        .wifi-icon .wifi-one {
            top: 0;

            width: 30px;
            height: 23px;

            border-width: 4px;
        }

        .wifi-icon .wifi-two {
            top: 7px;

            width: 20px;
            height: 15px;

            border-width: 4px;
        }

        .wifi-icon .wifi-three {
            top: 17px;

            width: 6px;
            height: 6px;

            border: none;

            background: #ffffff;

            border-radius: 50%;
        }

        /*
        |--------------------------------------------------------------------------
        | DEVICE INFORMATION
        |--------------------------------------------------------------------------
        */

        .device-card {
            display: grid;

            grid-template-columns:
                repeat(3, minmax(0, 1fr));

            margin-top: 16px;

            overflow: hidden;

            border:
                1px solid var(--border);

            border-radius: 15px;

            background:
                linear-gradient(145deg,
                    #0d1d31,
                    #091625);
        }

        .device-item {
            min-width: 0;

            padding: 14px 12px;
        }

        .device-item+.device-item {
            border-left:
                1px solid rgba(148, 163, 184, .25);
        }

        .device-label {
            margin-bottom: 5px;

            color: #b1bdcc;

            font-size: 11px;
        }

        .device-value {
            color: #ffffff;

            font-size: 13px;
            font-weight: 700;

            overflow-wrap: anywhere;
        }

        /*
        |--------------------------------------------------------------------------
        | FOOTER
        |--------------------------------------------------------------------------
        */

        .footer {
            margin-top: 19px;

            padding-top: 17px;

            border-top:
                1px solid rgba(203, 213, 225, .45);

            color: #c4cfdd;

            text-align: center;

            font-size: 12px;
        }

        /*
        |--------------------------------------------------------------------------
        | MOBILE - COMPACT
        |--------------------------------------------------------------------------
        */

        @media (max-width: 480px) {

            body {
                padding: 0;

                background: #040b14;
            }

            .portal {
                min-height: 100vh;

                padding:
                    14px 12px 16px;

                border: none;
                border-radius: 0;
            }

            /*
            | Header
            */

            .header {
                margin-bottom: 0;
            }

            .logo img {
                width: 55px;
                height: 55px;
            }

            .language {
                padding: 2px;
            }

            .language button {
                min-width: 40px;

                padding:
                    7px 10px;

                font-size: 12px;
            }

            /*
            | Title
            */

            .title {
                margin-top: 0;
                margin-bottom: 16px;
            }

            .title h1 {
                margin-bottom: 3px;

                font-size: 23px;
            }

            .title p {
                font-size: 11px;
            }

            /*
            | Payment
            */

            .card {
                padding: 14px;

                margin-bottom: 13px;

                border-radius: 15px;
            }

            .payment-title {
                margin-bottom: 12px;

                font-size: 15px;
            }

            .payment-label {
                margin-bottom: 4px;

                font-size: 9px;
            }

            .payment-value {
                font-size: 17px;
            }

            .payment-note {
                margin-top: 10px;

                font-size: 10px;

                line-height: 1.4;
            }

            /*
            | Packages
            */

            .packages {
                margin-bottom: 13px;
            }

            .packages-title {
                margin-bottom: 9px;

                font-size: 12px;
            }

            .package-grid {
                gap: 9px;
            }

            .package {
                padding:
                    11px 7px;

                border-radius: 13px;
            }

            .package-price {
                margin-bottom: 5px;

                font-size: 16px;
            }

            .package-duration {
                padding-top: 6px;

                font-size: 11px;
            }

            /*
            | Voucher
            */

            .voucher-area {
                margin-bottom: 12px;
            }

            .voucher-input {
                margin-bottom: 9px;

                padding:
                    12px 13px;

                font-size: 13px;
            }

            /*
            | Button
            */

            .connect-button {
                padding: 12px;

                gap: 8px;

                font-size: 14px;
            }

            .wifi-icon {
                transform: scale(.80);
            }

            /*
            | Device Info
            */

            .device-card {
                grid-template-columns:
                    repeat(3, minmax(0, 1fr));

                margin-top: 12px;
            }

            .device-item {
                padding:
                    10px 8px;
            }

            .device-item+.device-item {
                border-top: none;

                border-left:
                    1px solid rgba(148, 163, 184, .20);
            }

            .device-label {
                margin-bottom: 3px;

                font-size: 8px;
            }

            .device-value {
                font-size: 10px;
            }

            /*
            | Footer
            */

            .footer {
                margin-top: 13px;

                padding-top: 12px;

                font-size: 10px;
            }
        }
    </style>
</head>


<body>

    <div class="portal">

        {{-- ============================================================
        HEADER
        ============================================================= --}}

        <div class="header">

            <div class="logo">

                <img src="{{ asset('images/jodeka-logo.png') }}" alt="Jodeka Logo">

            </div>


            <div class="language">

                <button type="button" id="swButton" class="active" onclick="setLanguage('sw')">
                    SW
                </button>

                <button type="button" id="enButton" onclick="setLanguage('en')">
                    EN
                </button>

            </div>

        </div>


        {{-- ============================================================
        TITLE
        ============================================================= --}}

        <div class="title">

            <h1>
                Jodeka Hotspot
            </h1>

            <p data-sw="Haraka • Salama • Rahisi" data-en="Fast • Secure • Easy">
                Haraka • Salama • Rahisi
            </p>

        </div>


        {{-- ============================================================
        PAYMENT INFORMATION
        ============================================================= --}}

        <div class="card">

            <div class="payment-title" data-sw="JINSI YA KUPATA VOUCHER" data-en="HOW TO GET A VOUCHER">
                JINSI YA KUPATA VOUCHER
            </div>


            <div class="payment-grid">

                <div class="payment-item">

                    <div class="payment-label" data-sw="LIPA NAMBA YAS" data-en="PAY NUMBER">
                        LIPA NAMBA YAS
                    </div>

                    <div class="payment-value green">
                        19361296
                    </div>

                </div>


                <div class="payment-item">

                    <div class="payment-label" data-sw="TUMA UJUMBE" data-en="SEND MESSAGE">
                        TUMA UJUMBE
                    </div>

                    <div class="payment-value blue">
                        0659840000
                    </div>

                </div>

            </div>


            <div class="payment-note"
                data-sw="Lipa kwa Lipa Namba au Cash. Ukilipa kwa Lipa Namba, tuma ujumbe wa muamala ili utumiwe Voucher Code."
                data-en="Pay using the Pay Number or Cash. If you pay using the Pay Number, send the transaction message to receive your Voucher Code.">
                Lipa kwa Lipa Namba au Cash.
                Ukilipa kwa Lipa Namba,
                tuma ujumbe wa muamala ili utumiwe Voucher Code.
            </div>

        </div>


        {{-- ============================================================
        PACKAGES
        ============================================================= --}}

        <div class="packages">

            <div class="packages-title" data-sw="VIFURUSHI VINAVYOPATIKANA" data-en="AVAILABLE PACKAGES">
                VIFURUSHI VINAVYOPATIKANA
            </div>


            <div class="package-grid">

                <div class="package">

                    <div class="package-price">
                        200 TZS
                    </div>

                    <div class="package-duration" data-sw="Dakika 30" data-en="30 Minutes">
                        Dakika 30
                    </div>

                </div>


                <div class="package">

                    <div class="package-price">
                        500 TZS
                    </div>

                    <div class="package-duration" data-sw="Saa 12" data-en="12 Hours">
                        Saa 12
                    </div>

                </div>


                <div class="package">

                    <div class="package-price">
                        1000 TZS
                    </div>

                    <div class="package-duration" data-sw="Saa 24" data-en="24 Hours">
                        Saa 24
                    </div>

                </div>


                <div class="package">

                    <div class="package-price">
                        3000 TZS
                    </div>

                    <div class="package-duration" data-sw="Siku 7" data-en="7 Days">
                        Siku 7
                    </div>

                </div>

            </div>

        </div>


        {{-- ============================================================
        VOUCHER LOGIN
        ============================================================= --}}

        <div class="voucher-area">

            <form method="post" action="{{ $loginUrl ?: '#' }}" onsubmit="return prepareLogin(event)">

                <input type="hidden" name="dst" value="{{ $originalUrl ?? '' }}">

                <input type="hidden" name="popup" value="true">


                <input type="text" id="voucher" class="voucher-input" placeholder="Ingiza Voucher Code"
                    autocomplete="off" required>


                <input type="hidden" id="username" name="username">

                <input type="hidden" id="password" name="password">


                <button type="submit" class="connect-button" {{ $loginUrl ? '' : 'disabled' }}>

                    <span class="wifi-icon">

                        <span class="wifi-one"></span>

                        <span class="wifi-two"></span>

                        <span class="wifi-three"></span>

                    </span>


                    <span data-sw="UNGANISHA SASA" data-en="CONNECT NOW">
                        UNGANISHA SASA
                    </span>

                </button>

            </form>


            {{-- ========================================================
            DEVICE INFORMATION
            ========================================================= --}}

            <div class="device-card">

                <div class="device-item">

                    <div class="device-label">
                        MAC Address
                    </div>

                    <div class="device-value">
                        {{ $mac ?: '-' }}
                    </div>

                </div>


                <div class="device-item">

                    <div class="device-label">
                        IP Address
                    </div>

                    <div class="device-value">
                        {{ $ip ?: '-' }}
                    </div>

                </div>


                <div class="device-item">

                    <div class="device-label" data-sw="Aina ya Kifaa" data-en="Device Type">
                        Aina ya Kifaa
                    </div>

                    <div class="device-value">
                        {{ $deviceType ?? '-' }}
                    </div>

                </div>

            </div>

        </div>


        {{-- ============================================================
        FOOTER
        ============================================================= --}}

        <div class="footer">
            Powered by Jodeka Enterprises Ltd
        </div>

    </div>


    <script>
        function prepareLogin(event) {

            const loginUrl = @json($loginUrl);

            if (!loginUrl) {
                event.preventDefault();

                return false;
            }

            const voucher =
                document
                    .getElementById('voucher')
                    .value
                    .trim()
                    .toUpperCase();


            document
                .getElementById('username')
                .value = voucher;


            document
                .getElementById('password')
                .value = voucher;


            return true;
        }


        function setLanguage(language) {

            document
                .querySelectorAll('[data-sw][data-en]')
                .forEach(function (element) {

                    element.textContent =
                        element.dataset[language];
                });


            const voucherInput =
                document.getElementById('voucher');


            if (language === 'sw') {

                voucherInput.placeholder =
                    'Ingiza Voucher Code';

                document.documentElement.lang =
                    'sw';

            } else {

                voucherInput.placeholder =
                    'Enter Voucher Code';

                document.documentElement.lang =
                    'en';
            }


            document
                .getElementById('swButton')
                .classList
                .toggle(
                    'active',
                    language === 'sw'
                );


            document
                .getElementById('enButton')
                .classList
                .toggle(
                    'active',
                    language === 'en'
                );
        }


        setLanguage('sw');
    </script>

</body>

</html>