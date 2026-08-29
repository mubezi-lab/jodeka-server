@php
    /*
    |--------------------------------------------------------------------------
    | MIKROTIK PARAMETERS
    |--------------------------------------------------------------------------
    */

    $loginUrl = $loginUrl ?? request('login');
    $originalUrl = $originalUrl ?? request('dst');
    $mac = $mac ?? request('mac');
    $ip = $ip ?? request('ip');

    /*
    |--------------------------------------------------------------------------
    | DEVICE TYPE
    |--------------------------------------------------------------------------
    */

    if (!isset($deviceType)) {
        $userAgent = strtolower(request()->userAgent() ?? '');

        if (
            str_contains($userAgent, 'android') ||
            str_contains($userAgent, 'iphone') ||
            str_contains($userAgent, 'mobile')
        ) {
            $deviceType = 'Simu';
        } else {
            $deviceType = 'Kompyuta';
        }
    }
@endphp

<!DOCTYPE html>
<html lang="sw">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">

    <title>Jodeka Hotspot</title>

    {{-- ============================================================
    GOOGLE FONT
    ============================================================ --}}

    <link rel="preconnect" href="https://fonts.googleapis.com">

    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800;900&display=swap"
        rel="stylesheet">


    {{-- ============================================================
    FONT AWESOME
    ============================================================ --}}

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">


    <style>
        /*
        |--------------------------------------------------------------------------
        | RESET
        |--------------------------------------------------------------------------
        */

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --navy: #092d79;
            --green: #079c50;
            --green-dark: #057b3f;
            --blue: #168bec;
            --white: #ffffff;

            --text: #092d79;
            --muted: #577292;

            --border: #cfe1ed;

            --light-blue: #eaf7ff;
            --light-green: #effdf5;

            --danger: #991b1b;
        }

        html,
        body {
            width: 100%;
            min-height: 100%;
        }

        body,
        button,
        input {
            font-family:
                'Poppins',
                sans-serif;
        }

        body {
            min-height: 100vh;

            color: var(--text);

            background-image:
                url('{{ asset('images/hotspot/bus-stand-bg.png') }}');

            background-size: cover;
            background-position: center top;
            background-repeat: no-repeat;
            background-attachment: fixed;

            background-color: #eef3f6;
        }

        button,
        input {
            -webkit-tap-highlight-color: transparent;
        }

        .hidden {
            display: none !important;
        }


        /*
        |--------------------------------------------------------------------------
        | PAGE
        |--------------------------------------------------------------------------
        */

        .portal-page {
            width: 100%;
            min-height: 100vh;

            display: flex;
            flex-direction: column;
        }

        .page-content {
            width: 100%;
            flex: 1;

            padding:
                max(16px, env(safe-area-inset-top)) 16px 20px;
        }

        .page-inner {
            width: 100%;
            max-width: 1100px;

            margin: 0 auto;
        }


        /*
        |--------------------------------------------------------------------------
        | TOP
        |--------------------------------------------------------------------------
        */

        .topbar {
            width: 100%;

            min-height: 355px;

            display: flex;
            align-items: flex-start;
            justify-content: space-between;

            position: relative;
            z-index: 5;
        }


        /*
        |--------------------------------------------------------------------------
        | LOGO
        |--------------------------------------------------------------------------
        */

        .brand {
            display: flex;
            align-items: center;
        }

        .brand-logo {
            width: 58px;
            height: 58px;

            object-fit: contain;

            filter:
                drop-shadow(0 3px 7px rgba(0, 0, 0, .17));

            animation:
                jodeka-spin 12s linear infinite;
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

            padding: 4px;

            border-radius: 999px;

            background:
                rgba(255, 255, 255, .96);

            box-shadow:
                0 6px 22px rgba(0, 0, 0, .16);
        }

        .language button {
            min-width: 46px;

            padding: 8px 11px;

            border: 0;
            border-radius: 999px;

            color: var(--navy);
            background: transparent;

            cursor: pointer;

            font-size: 12px;
            font-weight: 800;
        }

        .language button.active {
            color: #ffffff;

            background:
                linear-gradient(135deg,
                    #08a958,
                    #00d778);
        }


        /*
        |--------------------------------------------------------------------------
        | STACK OF 3 CARDS
        |--------------------------------------------------------------------------
        */

        .portal-stack {
            width: 100%;
            max-width: 430px;

            margin: 0 auto;

            position: relative;
            z-index: 6;
        }

        .portal-card {
            width: 100%;

            border:
                1px solid rgba(255, 255, 255, .87);

            border-radius: 18px;

            background:
                rgba(255, 255, 255, .96);

            box-shadow:
                0 14px 34px rgba(0, 20, 45, .18);

            backdrop-filter: blur(6px);
            -webkit-backdrop-filter: blur(6px);
        }


        /*
        |--------------------------------------------------------------------------
        | CARD 1 - INSTRUCTIONS
        |--------------------------------------------------------------------------
        */

        .instructions-card {
            padding: 11px;

            margin-bottom: 9px;

            background:
                rgba(235, 248, 255, .97);
        }

        .instructions-header {
            display: flex;
            align-items: center;

            gap: 7px;

            margin-bottom: 6px;

            color: var(--navy);

            font-size: 11px;
            font-weight: 800;
        }

        .instructions-icon {
            width: 25px;
            height: 25px;

            display: flex;
            align-items: center;
            justify-content: center;

            flex: 0 0 25px;

            color: var(--blue);

            background: transparent;

            font-size: 22px;
        }

        .instructions-card ol {
            margin: 0;

            padding-left: 19px;

            color: #174f8f;

            font-size: 8px;
            line-height: 1.55;
        }

        .instructions-card li+li {
            margin-top: 2px;
        }

        .instructions-card strong {
            color: var(--navy);
            font-weight: 700;
        }


        /*
        |--------------------------------------------------------------------------
        | CARD 2 - PAYMENT + LOGIN
        |--------------------------------------------------------------------------
        */

        .access-card {
            padding: 11px;

            margin-bottom: 9px;
        }


        /*
        |--------------------------------------------------------------------------
        | PAYMENT
        |--------------------------------------------------------------------------
        */

        .payment-box {
            display: grid;

            grid-template-columns:
                52px minmax(0, 1fr);

            align-items: center;

            gap: 9px;

            padding: 9px;

            margin-bottom: 8px;

            border:
                1px solid #d3eddf;

            border-radius: 13px;

            background:
                linear-gradient(135deg,
                    #effdf5,
                    #e7faf0);
        }

        .payment-icon {
            text-align: center;

            color: var(--green);
        }

        .payment-phone-icon {
            width: 30px;
            height: 34px;

            display: inline-flex;
            align-items: center;
            justify-content: center;

            color: var(--green);

            font-size: 26px;
        }

        .payment-icon strong {
            display: block;

            margin-top: 2px;

            font-size: 8px;
            font-weight: 800;
        }

        .payment-label {
            color: var(--navy);

            font-size: 8px;
            font-weight: 700;
        }

        .payment-number {
            margin: 1px 0;

            color: var(--green);

            font-size: 23px;
            font-weight: 900;

            line-height: 1.05;
            letter-spacing: .4px;
        }

        .account-name {
            color: var(--navy);

            font-size: 6.5px;
            font-weight: 500;
        }

        .account-name strong {
            font-weight: 800;
        }


        /*
        |--------------------------------------------------------------------------
        | LOGIN
        |--------------------------------------------------------------------------
        */

        .login-box {
            padding: 9px;

            border:
                1px solid #d0e7f5;

            border-radius: 13px;

            background:
                linear-gradient(135deg,
                    #eef9ff,
                    #e1f4ff);
        }

        .login-title {
            color: var(--navy);

            font-size: 13px;
            font-weight: 800;

            line-height: 1.15;
        }

        .login-description {
            margin-top: 3px;
            margin-bottom: 7px;

            color: #285c98;

            font-size: 7.5px;
            font-weight: 500;

            line-height: 1.35;
        }


        /*
        |--------------------------------------------------------------------------
        | INPUT
        |--------------------------------------------------------------------------
        */

        .input-wrap {
            position: relative;
        }

        .input-icon {
            position: absolute;

            left: 12px;
            top: 50%;

            transform:
                translateY(-50%);

            width: 20px;

            display: flex;
            align-items: center;
            justify-content: center;

            color: #8da2bc;

            font-size: 17px;

            pointer-events: none;
        }

        .access-input {
            width: 100%;

            min-height: 42px;

            padding:
                10px 10px 10px 42px;

            border:
                1px solid #bfd2e4;

            border-radius: 11px;

            outline: none;

            color: var(--navy);

            background: #ffffff;

            font-size: 10.5px;
            font-weight: 500;
        }

        .access-input::placeholder {
            color: #93a4bb;
        }

        .access-input:focus {
            border-color: var(--green);

            box-shadow:
                0 0 0 3px rgba(8, 151, 77, .10);
        }


        /*
        |--------------------------------------------------------------------------
        | CONNECT
        |--------------------------------------------------------------------------
        */

        .connect-button {
            width: 100%;

            min-height: 42px;

            display: flex;
            align-items: center;
            justify-content: center;

            gap: 8px;

            margin-top: 8px;

            padding: 9px 12px;

            border: 0;
            border-radius: 11px;

            color: #ffffff;

            background:
                linear-gradient(90deg,
                    #06994d,
                    #08b45b);

            cursor: pointer;

            font-size: 13px;
            font-weight: 800;

            box-shadow:
                0 8px 18px rgba(8, 151, 77, .17);
        }

        .connect-button:disabled {
            opacity: .55;
            cursor: not-allowed;
        }

        .wifi-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;

            font-size: 16px;

            line-height: 1;
        }


        /*
        |--------------------------------------------------------------------------
        | STATUS
        |--------------------------------------------------------------------------
        */

        .status-message {
            margin-top: 7px;

            padding: 8px;

            border-radius: 9px;

            text-align: center;

            font-size: 8px;
            font-weight: 500;

            line-height: 1.4;
        }

        .status-message.info {
            color: #075985;
            background: #dff3ff;
        }

        .status-message.success {
            color: #166534;
            background: #dcfce7;
        }

        .status-message.error {
            color: #991b1b;
            background: #fee2e2;
        }


        /*
        |--------------------------------------------------------------------------
        | CARD 3
        |--------------------------------------------------------------------------
        */

        .info-card {
            padding: 10px;
        }


        /*
        |--------------------------------------------------------------------------
        | DEVICE INFO
        |--------------------------------------------------------------------------
        */

        .device-info {
            display: grid;

            grid-template-columns:
                repeat(3, minmax(0, 1fr));

            overflow: hidden;

            border:
                1px solid #d9e5ee;

            border-radius: 11px;

            background:
                rgba(248, 251, 254, .98);
        }

        .device-item {
            padding: 7px;
        }

        .device-item+.device-item {
            border-left:
                1px solid #d9e5ee;
        }

        .device-label {
            margin-bottom: 2px;

            color: #68809d;

            font-size: 6px;
            font-weight: 500;
        }

        .device-value {
            color: var(--navy);

            font-size: 7px;
            font-weight: 700;

            overflow-wrap: anywhere;
        }


        /*
        |--------------------------------------------------------------------------
        | SERVICE GRID
        |--------------------------------------------------------------------------
        */

        .service-grid {
            display: grid;

            grid-template-columns:
                repeat(2, minmax(0, 1fr));

            margin-top: 8px;

            overflow: hidden;

            border:
                1px solid #dce7ef;

            border-radius: 11px;

            background:
                rgba(255, 255, 255, .93);
        }

        .service-item {
            display: flex;
            align-items: center;

            min-width: 0;

            gap: 7px;

            padding: 8px;
        }

        .service-item:nth-child(2),
        .service-item:nth-child(4) {
            border-left:
                1px solid #dce7ef;
        }

        .service-item:nth-child(3),
        .service-item:nth-child(4) {
            border-top:
                1px solid #dce7ef;
        }

        .service-icon {
            width: 25px;

            flex: 0 0 25px;

            display: flex;
            align-items: center;
            justify-content: center;

            color: var(--green);

            font-size: 15px;
        }

        .service-title {
            color: var(--navy);

            font-size: 6px;
            font-weight: 800;
        }

        .service-text {
            margin-top: 1px;

            color: #607895;

            font-size: 5px;
            font-weight: 500;

            line-height: 1.25;
        }


        /*
        |--------------------------------------------------------------------------
        | FOOTER
        |--------------------------------------------------------------------------
        */

        .footer {
            width: 100%;

            margin-top: 10px;

            padding:
                9px 10px max(9px, env(safe-area-inset-bottom));

            color: #ffffff;

            background:
                rgba(0, 21, 55, .46);

            backdrop-filter: blur(4px);
            -webkit-backdrop-filter: blur(4px);

            text-align: center;

            font-size: 8px;
            font-weight: 500;

            font-style: italic;
        }


        /*
        |--------------------------------------------------------------------------
        | TABLET
        |--------------------------------------------------------------------------
        */

        @media (min-width: 701px) and (max-width: 1100px) {

            body {
                background-size: cover;
                background-position: center top;
            }

            .page-content {
                padding:
                    14px 18px 20px;
            }

            .topbar {
                min-height: 390px;
            }

            .portal-stack {
                max-width: 420px;
            }

        }


        /*
        |--------------------------------------------------------------------------
        | MOBILE
        |--------------------------------------------------------------------------
        */

        @media (max-width: 700px) {

            html,
            body {
                width: 100%;
                min-height: 100%;
            }

            body {
                min-height: 100vh;

                background-image:
                    url('{{ asset('images/hotspot/bus-stand-bg.png') }}');

                background-size: cover;

                background-position:
                    center top;

                background-repeat:
                    no-repeat;

                background-attachment:
                    fixed;

                background-color:
                    transparent;
            }

            .portal-page {
                width: 100%;
                min-height: 100vh;

                background: transparent;
            }

            .page-content {
                width: 100%;

                padding:
                    max(8px, env(safe-area-inset-top)) 7px 10px;

                background: transparent;
            }

            .page-inner {
                width: 100%;
            }


            /*
            |--------------------------------------------------------------------------
            | HERO SPACE
            |--------------------------------------------------------------------------
            */

            .topbar {
                width: 100%;

                min-height:
                    clamp(300px,
                        74vw,
                        345px);

                position: relative;
                z-index: 5;
            }


            /*
            |--------------------------------------------------------------------------
            | LOGO
            |--------------------------------------------------------------------------
            */

            .brand-logo {
                width: 44px;
                height: 44px;
            }


            /*
            |--------------------------------------------------------------------------
            | LANGUAGE
            |--------------------------------------------------------------------------
            */

            .language {
                padding: 3px;
            }

            .language button {
                min-width: 35px;

                padding: 5px 7px;

                font-size: 9px;
            }


            /*
            |--------------------------------------------------------------------------
            | STACK
            |--------------------------------------------------------------------------
            */

            .portal-stack {
                width: 86%;
                max-width: 360px;

                margin: 0 auto;
            }


            /*
            |--------------------------------------------------------------------------
            | CARD 1
            |--------------------------------------------------------------------------
            */

            .instructions-card {
                margin-top: -20px;

                margin-bottom: 8px;

                padding: 9px;

                border-radius: 14px;
            }

            .instructions-header {
                gap: 6px;

                margin-bottom: 5px;

                font-size: 11px;
            }

            .instructions-icon {
                width: 22px;
                height: 22px;

                flex-basis: 22px;

                font-size: 19px;
            }

            .instructions-card ol {
                padding-left: 17px;

                font-size: 8px;

                line-height: 1.5;
            }


            /*
            |--------------------------------------------------------------------------
            | CARD 2
            |--------------------------------------------------------------------------
            */

            .access-card {
                padding: 8px;

                margin-bottom: 8px;

                border-radius: 14px;
            }


            /*
            |--------------------------------------------------------------------------
            | PAYMENT
            |--------------------------------------------------------------------------
            */

            .payment-box {
                grid-template-columns:
                    43px minmax(0, 1fr);

                gap: 7px;

                padding: 7px;

                margin-bottom: 7px;

                border-radius: 11px;
            }

            .payment-phone-icon {
                width: 24px;
                height: 28px;

                font-size: 22px;
            }

            .payment-icon strong {
                margin-top: 2px;

                font-size: 8px;
            }

            .payment-label {
                font-size: 8px;
            }

            .payment-number {
                font-size: 20px;
            }

            .account-name {
                font-size: 7px;
            }


            /*
            |--------------------------------------------------------------------------
            | LOGIN
            |--------------------------------------------------------------------------
            */

            .login-box {
                padding: 7px;

                border-radius: 11px;
            }

            .login-title {
                font-size: 14px;
            }

            .login-description {
                margin-top: 3px;
                margin-bottom: 7px;

                font-size: 8px;

                line-height: 1.35;
            }

            .access-input {
                min-height: 40px;

                padding:
                    9px 9px 9px 38px;

                font-size: 11px;
            }

            .input-icon {
                left: 11px;

                width: 18px;

                font-size: 15px;
            }

            .connect-button {
                min-height: 40px;

                margin-top: 7px;

                padding: 8px;

                font-size: 13px;
            }

            .wifi-icon {
                font-size: 15px;
            }


            /*
            |--------------------------------------------------------------------------
            | CARD 3
            |--------------------------------------------------------------------------
            */

            .info-card {
                padding: 8px;

                border-radius: 14px;
            }

            .device-item {
                padding: 6px;
            }

            .device-label {
                font-size: 6.5px;
            }

            .device-value {
                font-size: 7.5px;
            }

            .service-grid {
                margin-top: 7px;
            }

            .service-item {
                padding: 7px;
            }

            .service-icon {
                width: 21px;
                flex-basis: 21px;

                font-size: 13px;
            }

            .service-title {
                font-size: 7px;
            }

            .service-text {
                font-size: 6px;
            }


            /*
            |--------------------------------------------------------------------------
            | FOOTER
            |--------------------------------------------------------------------------
            */

            .footer {
                margin-top: 7px;

                padding:
                    7px 5px max(7px, env(safe-area-inset-bottom));

                font-size: 7px;
            }

        }


        /*
        |--------------------------------------------------------------------------
        | SMALL MOBILE
        |--------------------------------------------------------------------------
        */

        @media (max-width: 390px) {

            .page-content {
                padding-left: 5px;
                padding-right: 5px;
            }

            .topbar {
                min-height:
                    clamp(280px,
                        76vw,
                        310px);
            }

            .brand-logo {
                width: 40px;
                height: 40px;
            }

            .language button {
                min-width: 32px;

                padding: 4px 6px;

                font-size: 8px;
            }

            .portal-stack {
                width: 90%;
                max-width: 340px;
            }

            .instructions-card {
                margin-top: -16px;
            }

            .instructions-card ol {
                font-size: 7.5px;
            }

            .payment-number {
                font-size: 19px;
            }

            .login-title {
                font-size: 13px;
            }

            .login-description {
                font-size: 7.5px;
            }

            .device-label {
                font-size: 6px;
            }

            .device-value {
                font-size: 7px;
            }

            .service-title {
                font-size: 6.5px;
            }

            .service-text {
                font-size: 5.5px;
            }

        }


        /*
        |--------------------------------------------------------------------------
        | VERY SMALL MOBILE
        |--------------------------------------------------------------------------
        */

        @media (max-width: 340px) {

            .page-content {
                padding-left: 4px;
                padding-right: 4px;
            }

            .topbar {
                min-height: 265px;
            }

            .portal-stack {
                width: 94%;
            }

            .instructions-card,
            .access-card,
            .info-card {
                padding: 6px;
            }

            .instructions-card ol {
                font-size: 7px;
            }

            .login-title {
                font-size: 12px;
            }

            .login-description {
                font-size: 7px;
            }

            .device-info {
                grid-template-columns: 1fr;
            }

            .device-item+.device-item {
                border-left: 0;

                border-top:
                    1px solid #d9e5ee;
            }

            .service-grid {
                grid-template-columns: 1fr;
            }

            .service-item:nth-child(2),
            .service-item:nth-child(3),
            .service-item:nth-child(4) {
                border-left: 0;

                border-top:
                    1px solid #dce7ef;
            }

        }
    </style>

</head>

<body>

    <div class="portal-page">

        <div class="page-content">

            <div class="page-inner">


                {{-- ============================================================
                TOP
                ============================================================ --}}

                <header class="topbar">

                    <div class="brand">

                        <img src="{{ asset('images/jodeka-logo.png') }}" alt="Jodeka" class="brand-logo">

                    </div>

                    <div class="language">

                        <button type="button" id="swButton" class="active" onclick="setLanguage('sw')">

                            SW

                        </button>

                        <button type="button" id="enButton" onclick="setLanguage('en')">

                            EN

                        </button>

                    </div>

                </header>


                {{-- ============================================================
                THREE CARDS
                ============================================================ --}}

                <div class="portal-stack">


                    {{-- ========================================================
                    CARD 1 : INSTRUCTIONS
                    ========================================================= --}}

                    <section class="portal-card instructions-card">

                        <div class="instructions-header">

                            <span class="instructions-icon">

                                <i class="fa-solid fa-circle-info"></i>

                            </span>

                            <span data-sw="Maelekezo" data-en="Instructions">

                                Maelekezo

                            </span>

                        </div>

                        <ol>

                            <li>

                                <strong data-sw="Kulipia kwa Lipa Namba:" data-en="Pay using Lipa Number:">

                                    Kulipia kwa Lipa Namba:

                                </strong>

                                <span
                                    data-sw=" Lipa kifurushi unachotaka, kisha ingiza namba ya simu iliyofanya malipo."
                                    data-en=" Pay for your preferred package, then enter the phone number used for payment.">

                                    Lipa kifurushi unachotaka,
                                    kisha ingiza namba ya simu iliyofanya malipo.

                                </span>

                            </li>

                            <li>

                                <strong data-sw="Kulipia cash:" data-en="Paying cash:">

                                    Kulipia cash:

                                </strong>

                                <span data-sw=" Ingiza voucher uliyopewa." data-en=" Enter the voucher you received.">

                                    Ingiza voucher uliyopewa.

                                </span>

                            </li>

                            <li data-sw="Hakikisha unaingiza namba ileile iliyotumika kufanya malipo."
                                data-en="Make sure you enter the same phone number used for payment.">

                                Hakikisha unaingiza namba ileile iliyotumika kufanya malipo.

                            </li>

                        </ol>

                    </section>


                    {{-- ========================================================
                    CARD 2 : PAYMENT + LOGIN
                    ========================================================= --}}

                    <section class="portal-card access-card">


                        {{-- PAYMENT --}}

                        <div class="payment-box">

                            <div class="payment-icon">

                                <span class="payment-phone-icon">

                                    <i class="fa-solid fa-mobile-screen-button"></i>

                                </span>

                                <strong>
                                    LIPA
                                </strong>

                            </div>

                            <div>

                                <div class="payment-label" data-sw="Lipa kwa Lipa Namba"
                                    data-en="Pay using Lipa Number">

                                    Lipa kwa Lipa Namba

                                </div>

                                <div class="payment-number">
                                    19361296
                                </div>

                                <div class="account-name">

                                    <span data-sw="Jina la akaunti:" data-en="Account name:">

                                        Jina la akaunti:

                                    </span>

                                    <strong>
                                        JACKSON
                                    </strong>

                                </div>

                            </div>

                        </div>


                        {{-- LOGIN --}}

                        <div class="login-box">

                            <div class="login-title" data-sw="Namba ya simu au Voucher"
                                data-en="Phone Number or Voucher">

                                Namba ya simu au Voucher

                            </div>

                            <div class="login-description"
                                data-sw="Ingiza namba ya simu uliyotumia kulipia au voucher uliyopewa."
                                data-en="Enter the phone number used for payment or the voucher you received.">

                                Ingiza namba ya simu uliyotumia kulipia
                                au voucher uliyopewa.

                            </div>

                            <div class="input-wrap">

                                <span class="input-icon">

                                    <i class="fa-regular fa-user"></i>

                                </span>

                                <input type="text" id="accessCode" class="access-input" autocomplete="off"
                                    autocapitalize="characters" spellcheck="false" inputmode="text"
                                    placeholder="Mfano: 0659840000 au JDK34587">

                            </div>

                            <button type="button" id="connectButton" class="connect-button" onclick="processAccess()">

                                <span class="wifi-icon">

                                    <i class="fa-solid fa-wifi"></i>

                                </span>

                                <span data-sw="INGIA" data-en="CONNECT">

                                    INGIA

                                </span>

                            </button>

                            <div id="statusMessage" class="status-message hidden">
                            </div>

                        </div>

                    </section>


                    {{-- ========================================================
                    CARD 3 : DEVICE + SERVICES
                    ========================================================= --}}

                    <section class="portal-card info-card">


                        {{-- DEVICE INFO --}}

                        <div class="device-info">

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
                                    {{ $deviceType }}
                                </div>

                            </div>

                        </div>


                        {{-- SERVICES --}}

                        <div class="service-grid">


                            <div class="service-item">

                                <span class="service-icon">

                                    <i class="fa-solid fa-globe"></i>

                                </span>

                                <div>

                                    <div class="service-title" data-sw="Intaneti ya Haraka" data-en="Fast Internet">

                                        Intaneti ya Haraka

                                    </div>

                                    <div class="service-text" data-sw="Browse, stream na kazi"
                                        data-en="Browse, stream and work">

                                        Browse, stream na kazi

                                    </div>

                                </div>

                            </div>


                            <div class="service-item">

                                <span class="service-icon">

                                    <i class="fa-solid fa-shield-halved"></i>

                                </span>

                                <div>

                                    <div class="service-title" data-sw="Salama na ya Kuaminika"
                                        data-en="Secure & Reliable">

                                        Salama na ya Kuaminika

                                    </div>

                                    <div class="service-text" data-sw="Huduma bora ya intaneti"
                                        data-en="Reliable internet service">

                                        Huduma bora ya intaneti

                                    </div>

                                </div>

                            </div>


                            <div class="service-item">

                                <span class="service-icon">

                                    <i class="fa-solid fa-users"></i>

                                </span>

                                <div>

                                    <div class="service-title" data-sw="Kwa Wote" data-en="For Everyone">

                                        Kwa Wote

                                    </div>

                                    <div class="service-text" data-sw="Wakazi na wasafiri"
                                        data-en="Residents and travellers">

                                        Wakazi na wasafiri

                                    </div>

                                </div>

                            </div>


                            <div class="service-item">

                                <span class="service-icon">

                                    <i class="fa-solid fa-signal"></i>

                                </span>

                                <div>

                                    <div class="service-title" data-sw="Maeneo Zaidi" data-en="More Coverage">

                                        Maeneo Zaidi

                                    </div>

                                    <div class="service-text" data-sw="Tunaendelea kupanua huduma"
                                        data-en="We continue expanding coverage">

                                        Tunaendelea kupanua huduma

                                    </div>

                                </div>

                            </div>

                        </div>

                    </section>


                    {{-- ========================================================
                    MIKROTIK LOGIN FORM
                    ========================================================= --}}

                    <form id="mikrotikLoginForm" method="post" action="{{ $loginUrl ?: '#' }}" style="display:none">

                        <input type="hidden" name="dst" value="{{ $originalUrl ?? '' }}">

                        <input type="hidden" name="popup" value="true">

                        <input type="hidden" id="username" name="username">

                        <input type="hidden" id="password" name="password">

                    </form>

                </div>

            </div>

        </div>


        <footer class="footer">

            “JODEKA Hotspot — Intaneti kwa Maisha Bora”

        </footer>

    </div>


    <script>

        let currentLanguage = 'sw';


        const loginUrl =
            @json($loginUrl);


        const deviceMac =
            @json($mac);


        const deviceIp =
            @json($ip);


        /*
        |--------------------------------------------------------------------------
        | PROCESS INPUT
        |--------------------------------------------------------------------------
        */

        async function processAccess() {
            const input =
                document
                    .getElementById(
                        'accessCode'
                    )
                    .value
                    .trim();


            if (!input) {

                showStatus(
                    translate(
                        'Ingiza namba ya simu iliyofanya malipo au Voucher Code.',
                        'Enter the phone number used for payment or your Voucher Code.'
                    ),
                    'error'
                );

                return;
            }


            const normalizedInput =
                input
                    .replace(
                        /\s+/g,
                        ''
                    )
                    .toUpperCase();


            /*
            |--------------------------------------------------------------------------
            | VOUCHER
            |--------------------------------------------------------------------------
            */

            if (
                /^JDK[A-Z0-9]+$/i.test(
                    normalizedInput
                )
            ) {

                disableButton();

                showStatus(
                    translate(
                        'Inaunganisha kwa voucher...',
                        'Connecting using voucher...'
                    ),
                    'info'
                );

                loginWithVoucher(
                    normalizedInput
                );

                return;
            }


            /*
            |--------------------------------------------------------------------------
            | PHONE
            |--------------------------------------------------------------------------
            */

            const phone =
                normalizePhoneInput(
                    normalizedInput
                );


            if (!phone) {

                showStatus(
                    translate(
                        'Namba ya simu au Voucher Code uliyoingiza si sahihi.',
                        'The phone number or Voucher Code you entered is invalid.'
                    ),
                    'error'
                );

                return;
            }


            await verifyPhonePayment(
                phone
            );
        }


        /*
        |--------------------------------------------------------------------------
        | VERIFY PHONE PAYMENT
        |--------------------------------------------------------------------------
        */

        async function verifyPhonePayment(
            phone
        ) {
            disableButton();


            showStatus(
                translate(
                    'Inatafuta na kuthibitisha malipo yako...',
                    'Searching for and verifying your payment...'
                ),
                'info'
            );


            try {

                const response =
                    await fetch(
                        '/api/hotspot/payments/verify',
                        {
                            method:
                                'POST',

                            headers: {

                                'Content-Type':
                                    'application/json',

                                'Accept':
                                    'application/json'

                            },

                            body:
                                JSON.stringify({

                                    payer_phone:
                                        phone,

                                    mac:
                                        deviceMac,

                                    ip:
                                        deviceIp

                                })
                        }
                    );


                let data = {};


                try {

                    data =
                        await response.json();

                } catch (error) {

                    data = {};
                }


                if (
                    !response.ok ||
                    !data.success
                ) {

                    throw new Error(
                        data.message
                        ||
                        translate(
                            'Malipo hayakuweza kuthibitishwa.',
                            'Payment could not be verified.'
                        )
                    );
                }


                if (!data.voucher) {

                    throw new Error(
                        translate(
                            'Malipo yamethibitishwa lakini Voucher Code haijapatikana.',
                            'Payment was verified but the Voucher Code was not found.'
                        )
                    );
                }


                showStatus(
                    translate(
                        'Malipo yamethibitishwa. Inaunganisha intaneti...',
                        'Payment verified. Connecting to the internet...'
                    ),
                    'success'
                );


                setTimeout(
                    function () {

                        loginWithVoucher(
                            data.voucher
                        );

                    },
                    500
                );


            } catch (error) {

                showStatus(
                    error.message
                    ||
                    translate(
                        'Imeshindikana kuthibitisha malipo.',
                        'Unable to verify payment.'
                    ),
                    'error'
                );


                enableButton();
            }
        }


        /*
        |--------------------------------------------------------------------------
        | LOGIN WITH VOUCHER
        |--------------------------------------------------------------------------
        */

        function loginWithVoucher(
            voucherCode
        ) {
            const voucher =
                String(
                    voucherCode
                )
                    .trim()
                    .replace(
                        /\s+/g,
                        ''
                    )
                    .toUpperCase();


            if (!voucher) {

                showStatus(
                    translate(
                        'Voucher Code haijapatikana.',
                        'Voucher Code was not found.'
                    ),
                    'error'
                );

                enableButton();

                return;
            }


            if (!loginUrl) {

                showStatus(
                    translate(
                        'MikroTik login URL haijapatikana. Fungua ukurasa kupitia Jodeka Hotspot.',
                        'MikroTik login URL was not received. Open this page through Jodeka Hotspot.'
                    ),
                    'error'
                );


                enableButton();

                return;
            }


            document
                .getElementById(
                    'username'
                )
                .value =
                voucher;


            document
                .getElementById(
                    'password'
                )
                .value =
                voucher;


            showStatus(
                translate(
                    'Inaunganisha intaneti...',
                    'Connecting to the internet...'
                ),
                'success'
            );


            setTimeout(
                function () {

                    document
                        .getElementById(
                            'mikrotikLoginForm'
                        )
                        .submit();

                },
                250
            );
        }


        /*
        |--------------------------------------------------------------------------
        | NORMALIZE PHONE
        |--------------------------------------------------------------------------
        */

        function normalizePhoneInput(
            value
        ) {
            let phone =
                String(
                    value
                )
                    .replace(
                        /[^\d+]/g,
                        ''
                    );


            if (
                phone.startsWith(
                    '+255'
                )
            ) {

                phone =
                    '0'
                    +
                    phone.substring(
                        4
                    );
            }


            if (
                phone.startsWith(
                    '255'
                )
                &&
                phone.length === 12
            ) {

                phone =
                    '0'
                    +
                    phone.substring(
                        3
                    );
            }


            if (
                /^0\d{9}$/.test(
                    phone
                )
            ) {

                return phone;
            }


            if (
                /^\d{9}$/.test(
                    phone
                )
            ) {

                return '0' + phone;
            }


            return null;
        }


        /*
        |--------------------------------------------------------------------------
        | STATUS
        |--------------------------------------------------------------------------
        */

        function showStatus(
            message,
            type
        ) {
            const element =
                document
                    .getElementById(
                        'statusMessage'
                    );


            element.textContent =
                message;


            element.className =
                'status-message '
                +
                type;
        }


        function clearStatus() {
            const element =
                document
                    .getElementById(
                        'statusMessage'
                    );


            element.textContent =
                '';


            element.className =
                'status-message hidden';
        }


        /*
        |--------------------------------------------------------------------------
        | BUTTON
        |--------------------------------------------------------------------------
        */

        function disableButton() {
            document
                .getElementById(
                    'connectButton'
                )
                .disabled =
                true;
        }


        function enableButton() {
            document
                .getElementById(
                    'connectButton'
                )
                .disabled =
                false;
        }


        /*
        |--------------------------------------------------------------------------
        | LANGUAGE
        |--------------------------------------------------------------------------
        */

        function setLanguage(
            language
        ) {
            currentLanguage =
                language;


            document
                .querySelectorAll(
                    '[data-sw][data-en]'
                )
                .forEach(
                    function (element) {

                        element.textContent =
                            element.dataset[
                            language
                            ];
                    }
                );


            const input =
                document
                    .getElementById(
                        'accessCode'
                    );


            if (
                language === 'sw'
            ) {

                input.placeholder =
                    'Mfano: 0659840000 au JDK34587';


                document
                    .documentElement
                    .lang =
                    'sw';

            } else {

                input.placeholder =
                    'Example: 0659840000 or JDK34587';


                document
                    .documentElement
                    .lang =
                    'en';
            }


            document
                .getElementById(
                    'swButton'
                )
                .classList
                .toggle(
                    'active',
                    language === 'sw'
                );


            document
                .getElementById(
                    'enButton'
                )
                .classList
                .toggle(
                    'active',
                    language === 'en'
                );
        }


        /*
        |--------------------------------------------------------------------------
        | TRANSLATION
        |--------------------------------------------------------------------------
        */

        function translate(
            sw,
            en
        ) {
            return currentLanguage === 'sw'
                ? sw
                : en;
        }


        /*
        |--------------------------------------------------------------------------
        | ENTER KEY
        |--------------------------------------------------------------------------
        */

        document
            .getElementById(
                'accessCode'
            )
            .addEventListener(
                'keydown',
                function (event) {

                    if (
                        event.key ===
                        'Enter'
                    ) {

                        event.preventDefault();

                        processAccess();
                    }
                }
            );


        /*
        |--------------------------------------------------------------------------
        | INPUT CHANGE
        |--------------------------------------------------------------------------
        */

        document
            .getElementById(
                'accessCode'
            )
            .addEventListener(
                'input',
                function () {

                    clearStatus();

                    enableButton();
                }
            );


        /*
        |--------------------------------------------------------------------------
        | INITIAL LANGUAGE
        |--------------------------------------------------------------------------
        */

        setLanguage(
            'sw'
        );

    </script>

</body>

</html>