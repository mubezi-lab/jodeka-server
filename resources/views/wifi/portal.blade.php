<!DOCTYPE html>
<html lang="sw">

<head>
    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Jodeka Wi-Fi</title>

    <!-- Google Font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap"
        rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
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
            --danger: #ff6262;

            --border: rgba(80, 151, 215, .23);
        }

        body,
        button,
        input {
            font-family: 'Inter', sans-serif;
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

        .hidden {
            display: none !important;
        }

        /* HEADER */

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
            animation: jodeka-spin 10s linear infinite;
        }

        @keyframes jodeka-spin {
            from {
                transform: rotate(0deg);
            }

            to {
                transform: rotate(360deg);
            }
        }

        /* LANGUAGE */

        .language {
            display: flex;
            align-items: center;
            padding: 3px;
            border-radius: 30px;
            background: #17243a;
            border: 1px solid rgba(148, 163, 184, .09);
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

        /* TITLE */

        .title {
            margin-top: 4px;
            margin-bottom: 22px;
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

        /* GENERAL CARD */

        .card {
            margin-bottom: 18px;
            padding: 20px;
            border: 1px solid var(--border);
            border-radius: 19px;

            background:
                linear-gradient(145deg,
                    rgba(13, 30, 51, .97),
                    rgba(7, 18, 32, .98));
        }

        .section-title {
            margin-bottom: 14px;
            text-align: center;
            color: #ffffff;
            font-size: 16px;
            font-weight: 800;
        }

        .payment-title {
            margin-bottom: 10px;
            color: var(--green);
            text-align: center;
            font-size: 18px;
            font-weight: 800;
        }

        .payment-note {
            color: #dce4ee;
            text-align: center;
            font-size: 12px;
            line-height: 1.6;
        }

        /* MAIN OPTIONS */

        .main-options {
            display: grid;
            grid-template-columns: 1fr;
            gap: 12px;
        }

        .main-option {
            width: 100%;
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 17px;

            border: 1px solid var(--border);
            border-radius: 16px;

            color: #ffffff;

            background:
                linear-gradient(145deg,
                    #10233a,
                    #0a1728);

            cursor: pointer;
            text-align: left;

            transition:
                transform .15s ease,
                border-color .15s ease,
                box-shadow .15s ease;
        }

        .main-option:hover {
            transform: translateY(-1px);
            border-color: rgba(0, 223, 130, .55);
        }

        .option-icon {
            width: 48px;
            height: 48px;
            flex: 0 0 48px;

            display: flex;
            align-items: center;
            justify-content: center;

            border-radius: 14px;
            background: rgba(0, 223, 130, .10);
        }

        .option-icon i {
            color: var(--green);
            font-size: 22px;
        }

        .option-content {
            flex: 1;
            min-width: 0;
        }

        .option-title {
            margin-bottom: 4px;
            color: var(--green);
            font-size: 16px;
            font-weight: 800;
        }

        .option-description {
            color: #b9c5d4;
            font-size: 11px;
            line-height: 1.4;
        }

        .option-arrow {
            color: #9fb0c4;
            font-size: 17px;
        }

        /* BACK BUTTON */

        .back-button {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            margin-bottom: 12px;
            padding: 0;
            border: none;
            background: transparent;
            color: #aebdce;
            cursor: pointer;
            font-size: 12px;
            font-weight: 700;
        }

        .back-button:hover {
            color: var(--green);
        }

        .back-button i {
            font-size: 11px;
        }

        /* PACKAGE CARDS */

        .package-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
        }

        .package {
            width: 100%;
            padding: 17px 12px;
            text-align: center;

            border: 1px solid var(--border);
            border-radius: 16px;

            color: #ffffff;

            background:
                linear-gradient(145deg,
                    #0d1b2d,
                    #081525);

            cursor: pointer;

            transition:
                transform .15s ease,
                border-color .15s ease,
                box-shadow .15s ease;
        }

        .package:hover {
            transform: translateY(-1px);
            border-color: rgba(0, 223, 130, .50);
        }

        .package.selected {
            border-color: var(--green);

            box-shadow:
                0 0 0 2px rgba(0, 223, 130, .12),
                0 10px 28px rgba(0, 223, 130, .10);
        }

        .package-price {
            margin-bottom: 9px;
            color: var(--green);
            font-size: 20px;
            font-weight: 800;
        }

        .package-duration {
            padding-top: 9px;
            border-top: 1px solid rgba(148, 163, 184, .22);
            color: #ffffff;
            font-size: 14px;
            font-weight: 700;
        }

        /* PACKAGE TABLE */

        .package-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            overflow: hidden;

            border: 1px solid rgba(91, 155, 211, .28);
            border-radius: 13px;

            font-size: 12px;
        }

        .package-table th,
        .package-table td {
            padding: 10px 12px;
            border-bottom: 1px solid rgba(148, 163, 184, .15);
        }

        .package-table th {
            text-align: left;
            color: #dbe6f2;
            background: rgba(255, 255, 255, .04);
            font-size: 10px;
            font-weight: 800;
        }

        .package-table td {
            color: #ffffff;
        }

        .package-table td:first-child {
            color: var(--green);
            font-weight: 800;
        }

        .package-table tr:last-child td {
            border-bottom: none;
        }

        /* LIPA NAMBA */

        .pay-number-box {
            margin-bottom: 14px;
            padding: 16px;
            text-align: center;

            border: 1px solid rgba(0, 223, 130, .30);
            border-radius: 14px;

            background:
                linear-gradient(145deg,
                    rgba(0, 223, 130, .07),
                    rgba(7, 18, 32, .95));
        }

        .pay-number-label {
            margin-bottom: 5px;
            color: #d2dbe7;
            font-size: 10px;
            font-weight: 700;
        }

        .pay-number {
            margin-bottom: 12px;
            color: var(--green);
            font-size: 27px;
            font-weight: 900;
            letter-spacing: 1px;
        }

        .pay-amount-label {
            margin-bottom: 4px;
            color: #d2dbe7;
            font-size: 10px;
        }

        .pay-amount {
            color: #ffffff;
            font-size: 19px;
            font-weight: 800;
        }

        .reference-note {
            margin-bottom: 10px;
            color: #cdd8e7;
            text-align: center;
            font-size: 11px;
            line-height: 1.5;
        }

        /* INPUTS */

        .voucher-input,
        .payment-input {
            width: 100%;
            margin-bottom: 11px;
            padding: 15px 17px;

            border: 1px solid rgba(91, 155, 211, .35);
            border-radius: 12px;

            outline: none;
            color: #ffffff;

            background:
                linear-gradient(145deg,
                    #11243b,
                    #0e1d31);

            font-size: 16px;
        }

        .voucher-input::placeholder,
        .payment-input::placeholder {
            color: #92a1b7;
        }

        .voucher-input:focus,
        .payment-input:focus {
            border-color: rgba(0, 223, 130, .65);
        }

        /* ACTION BUTTONS */

        .connect-button,
        .verify-button {
            width: 100%;

            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;

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

        .connect-button i,
        .verify-button i {
            font-size: 18px;
        }

        .connect-button:disabled,
        .verify-button:disabled {
            opacity: .55;
            cursor: not-allowed;
        }

        /* STATUS */

        .status-message {
            margin-top: 11px;
            padding: 11px 12px;
            border-radius: 10px;
            text-align: center;
            font-size: 11px;
            line-height: 1.5;
        }

        .status-message.info {
            color: #d9ecff;
            border: 1px solid rgba(45, 140, 255, .30);
            background: rgba(45, 140, 255, .08);
        }

        .status-message.success {
            color: #caffdf;
            border: 1px solid rgba(0, 223, 130, .30);
            background: rgba(0, 223, 130, .08);
        }

        .status-message.error {
            color: #ffd3d3;
            border: 1px solid rgba(255, 98, 98, .32);
            background: rgba(255, 98, 98, .08);
        }

        /* DEVICE INFO */

        .device-card {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));

            margin-top: 16px;
            overflow: hidden;

            border: 1px solid var(--border);
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
            border-left: 1px solid rgba(148, 163, 184, .25);
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

        /* FOOTER */

        .footer {
            margin-top: 19px;
            padding-top: 17px;
            border-top: 1px solid rgba(203, 213, 225, .45);
            color: #c4cfdd;
            text-align: center;
            font-size: 12px;
        }

        /* MOBILE */

        @media (max-width: 480px) {

            body {
                padding: 0;
                background: #040b14;
            }

            .portal {
                min-height: 100vh;
                padding: 14px 12px 16px;
                border: none;
                border-radius: 0;
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
                padding: 7px 10px;
                font-size: 12px;
            }

            .title {
                margin-top: 0;
                margin-bottom: 15px;
            }

            .title h1 {
                margin-bottom: 3px;
                font-size: 23px;
            }

            .title p {
                font-size: 11px;
            }

            .card {
                padding: 14px;
                margin-bottom: 13px;
                border-radius: 15px;
            }

            .section-title {
                margin-bottom: 11px;
                font-size: 13px;
            }

            .payment-title {
                font-size: 15px;
            }

            .payment-note {
                font-size: 10px;
            }

            .main-option {
                padding: 13px;
                gap: 11px;
            }

            .option-icon {
                width: 42px;
                height: 42px;
                flex-basis: 42px;
            }

            .option-icon i {
                font-size: 19px;
            }

            .option-title {
                font-size: 14px;
            }

            .option-description {
                font-size: 9px;
            }

            .option-arrow {
                font-size: 14px;
            }

            .package-grid {
                gap: 9px;
            }

            .package {
                padding: 11px 7px;
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

            .package-table {
                font-size: 10px;
            }

            .package-table th,
            .package-table td {
                padding: 8px 9px;
            }

            .pay-number-box {
                padding: 12px;
            }

            .pay-number {
                font-size: 22px;
            }

            .pay-amount {
                font-size: 15px;
            }

            .reference-note {
                font-size: 10px;
            }

            .voucher-input,
            .payment-input {
                margin-bottom: 9px;
                padding: 12px 13px;
                font-size: 13px;
            }

            .connect-button,
            .verify-button {
                padding: 12px;
                gap: 8px;
                font-size: 14px;
            }

            .connect-button i,
            .verify-button i {
                font-size: 15px;
            }

            .device-card {
                margin-top: 12px;
            }

            .device-item {
                padding: 10px 8px;
            }

            .device-label {
                margin-bottom: 3px;
                font-size: 8px;
            }

            .device-value {
                font-size: 10px;
            }

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

        {{-- HEADER --}}

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


        {{-- TITLE --}}

        <div class="title">

            <h1>Jodeka Hotspot</h1>

            <p data-sw="Haraka • Salama • Rahisi" data-en="Fast • Secure • Easy">
                Haraka • Salama • Rahisi
            </p>

        </div>


        {{-- WELCOME --}}

        <div id="welcomeCard" class="card">

            <div class="payment-title" data-sw="KARIBU JODEKA HOTSPOT" data-en="WELCOME TO JODEKA HOTSPOT">
                KARIBU JODEKA HOTSPOT
            </div>

            <div class="payment-note"
                data-sw="Furahia internet ya haraka, salama na rahisi. Chagua njia unayotaka kutumia hapa chini ili kuunganishwa."
                data-en="Enjoy fast, secure and easy internet. Choose how you want to connect below.">

                Furahia internet ya haraka, salama na rahisi.
                Chagua njia unayotaka kutumia hapa chini ili kuunganishwa.

            </div>

        </div>


        {{-- MAIN OPTIONS --}}

        <div id="mainChoiceCard" class="card">

            <div class="section-title" data-sw="CHAGUA NJIA YA KUUNGANISHWA" data-en="CHOOSE HOW TO CONNECT">
                CHAGUA NJIA YA KUUNGANISHWA
            </div>

            <div class="main-options">

                <button type="button" class="main-option" onclick="openPhonePayment()">

                    <div class="option-icon">
                        <i class="fa-solid fa-mobile-screen-button"></i>
                    </div>

                    <div class="option-content">

                        <div class="option-title" data-sw="LIPA KWA SIMU" data-en="PAY BY PHONE">
                            LIPA KWA SIMU
                        </div>

                        <div class="option-description" data-sw="Chagua kifurushi na ulipe kupitia Lipa Namba."
                            data-en="Select a package and pay using the Pay Number.">
                            Chagua kifurushi na ulipe kupitia Lipa Namba.
                        </div>

                    </div>

                    <div class="option-arrow">
                        <i class="fa-solid fa-chevron-right"></i>
                    </div>

                </button>


                <button type="button" class="main-option" onclick="openCashVoucher()">

                    <div class="option-icon">
                        <i class="fa-solid fa-ticket"></i>
                    </div>

                    <div class="option-content">

                        <div class="option-title" data-sw="CASH / VOUCHER" data-en="CASH / VOUCHER">
                            CASH / VOUCHER
                        </div>

                        <div class="option-description"
                            data-sw="Lipia Cash ofisini kwetu na upewe Voucher Code, au ingiza voucher uliyonayo."
                            data-en="Pay cash at our office and receive a Voucher Code, or enter a voucher you already have.">
                            Lipia Cash ofisini kwetu na upewe Voucher Code, au ingiza voucher uliyonayo.
                        </div>

                    </div>

                    <div class="option-arrow">
                        <i class="fa-solid fa-chevron-right"></i>
                    </div>

                </button>

            </div>

        </div>


        {{-- PHONE PAYMENT PACKAGES --}}

        <div id="phonePackagesCard" class="card hidden">

            <button type="button" class="back-button" onclick="goHome()">

                <i class="fa-solid fa-arrow-left"></i>

                <span data-sw="Rudi" data-en="Back">
                    Rudi
                </span>

            </button>

            <div class="payment-title" data-sw="CHAGUA KIFURUSHI" data-en="SELECT A PACKAGE">
                CHAGUA KIFURUSHI
            </div>

            <div class="package-grid">

                <button type="button" class="package" data-amount="200" onclick="selectPackage(this)">

                    <div class="package-price">
                        200 TZS
                    </div>

                    <div class="package-duration" data-sw="Dakika 30" data-en="30 Minutes">
                        Dakika 30
                    </div>

                </button>


                <button type="button" class="package" data-amount="500" onclick="selectPackage(this)">

                    <div class="package-price">
                        500 TZS
                    </div>

                    <div class="package-duration" data-sw="Saa 12" data-en="12 Hours">
                        Saa 12
                    </div>

                </button>


                <button type="button" class="package" data-amount="1000" onclick="selectPackage(this)">

                    <div class="package-price">
                        1,000 TZS
                    </div>

                    <div class="package-duration" data-sw="Saa 24" data-en="24 Hours">
                        Saa 24
                    </div>

                </button>


                <button type="button" class="package" data-amount="3000" onclick="selectPackage(this)">

                    <div class="package-price">
                        3,000 TZS
                    </div>

                    <div class="package-duration" data-sw="Siku 7" data-en="7 Days">
                        Siku 7
                    </div>

                </button>

            </div>

        </div>


        {{-- LIPA NAMBA --}}

        <div id="lipaNumberCard" class="card hidden">

            <button type="button" class="back-button" onclick="backToPackages()">

                <i class="fa-solid fa-arrow-left"></i>

                <span data-sw="Badili kifurushi" data-en="Change package">
                    Badili kifurushi
                </span>

            </button>

            <div class="payment-title" data-sw="LIPA KWA SIMU" data-en="PAY BY PHONE">
                LIPA KWA SIMU
            </div>

            <div class="pay-number-box">

                <div class="pay-number-label" data-sw="LIPA NAMBA YAS" data-en="YAS PAY NUMBER">
                    LIPA NAMBA YAS
                </div>

                <div class="pay-number">
                    19361296
                </div>

                <div class="pay-amount-label" data-sw="KIASI CHA KULIPA" data-en="AMOUNT TO PAY">
                    KIASI CHA KULIPA
                </div>

                <div id="payAmountText" class="pay-amount">
                </div>

            </div>

            <div class="reference-note" data-sw="Baada ya kulipa, ingiza namba ya simu uliyotumia kufanya malipo."
                data-en="After paying, enter the phone number you used to make the payment.">

                Baada ya kulipa, ingiza namba ya simu uliyotumia kufanya malipo.

            </div>

            <input type="tel" id="payerPhone" class="payment-input" inputmode="tel" autocomplete="tel" maxlength="16"
                placeholder="Mfano: 0659840000">

            <button type="button" id="verifyPaymentButton" class="verify-button" onclick="verifyPayment()">

                <i class="fa-solid fa-circle-check"></i>

                <span data-sw="THIBITISHA MALIPO" data-en="VERIFY PAYMENT">
                    THIBITISHA MALIPO
                </span>

            </button>

            <div id="paymentStatus" class="status-message hidden">
            </div>

        </div>


        {{-- CASH / VOUCHER --}}

        <div id="cashVoucherCard" class="card hidden">

            <button type="button" class="back-button" onclick="goHome()">

                <i class="fa-solid fa-arrow-left"></i>

                <span data-sw="Rudi" data-en="Back">
                    Rudi
                </span>

            </button>

            <div class="payment-title" data-sw="CASH / VOUCHER" data-en="CASH / VOUCHER">
                CASH / VOUCHER
            </div>

            <div class="section-title" data-sw="VIFURUSHI VINAVYOPATIKANA" data-en="AVAILABLE PACKAGES">
                VIFURUSHI VINAVYOPATIKANA
            </div>

            <table class="package-table">

                <thead>

                    <tr>

                        <th data-sw="BEI" data-en="PRICE">
                            BEI
                        </th>

                        <th data-sw="MUDA" data-en="DURATION">
                            MUDA
                        </th>

                    </tr>

                </thead>

                <tbody>

                    <tr>
                        <td>TZS 200</td>

                        <td data-sw="Dakika 30" data-en="30 Minutes">
                            Dakika 30
                        </td>
                    </tr>

                    <tr>
                        <td>TZS 500</td>

                        <td data-sw="Saa 12" data-en="12 Hours">
                            Saa 12
                        </td>
                    </tr>

                    <tr>
                        <td>TZS 1,000</td>

                        <td data-sw="Saa 24" data-en="24 Hours">
                            Saa 24
                        </td>
                    </tr>

                    <tr>
                        <td>TZS 3,000</td>

                        <td data-sw="Siku 7" data-en="7 Days">
                            Siku 7
                        </td>
                    </tr>

                </tbody>

            </table>

            <div class="reference-note" style="margin-top: 14px;"
                data-sw="Lipia Cash ofisini kwetu kulingana na kifurushi unachotaka. Utapewa Voucher Code; ingiza voucher hiyo hapa."
                data-en="Pay cash at our office according to the package you want. You will receive a Voucher Code; enter it below.">

                Lipia Cash ofisini kwetu kulingana na kifurushi unachotaka.
                Utapewa Voucher Code; ingiza voucher hiyo hapa.

            </div>

            <form id="mikrotikLoginForm" method="post" action="{{ $loginUrl ?: '#' }}"
                onsubmit="return prepareLogin(event)">

                <input type="hidden" name="dst" value="{{ $originalUrl ?? '' }}">

                <input type="hidden" name="popup" value="true">

                <input type="text" id="voucher" class="voucher-input" placeholder="Ingiza Voucher Code"
                    autocomplete="off">

                <input type="hidden" id="username" name="username">

                <input type="hidden" id="password" name="password">

                <button type="submit" class="connect-button" {{ $loginUrl ? '' : 'disabled' }}>

                    <i class="fa-solid fa-wifi"></i>

                    <span data-sw="UNGANISHA SASA" data-en="CONNECT NOW">
                        UNGANISHA SASA
                    </span>

                </button>

            </form>

        </div>


        {{-- DEVICE INFO --}}

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


        <div class="footer">
            Powered by Jodeka Enterprises Ltd
        </div>

    </div>


    <script>
        let currentLanguage = 'sw';

        let selectedAmount = null;

        const loginUrl = @json($loginUrl);

        const deviceMac = @json($mac);

        const deviceIp = @json($ip);


        /*
        |--------------------------------------------------------------------------
        | FLOW VISIBILITY
        |--------------------------------------------------------------------------
        */

        function hideAllFlows() {

            document
                .getElementById('phonePackagesCard')
                .classList
                .add('hidden');


            document
                .getElementById('lipaNumberCard')
                .classList
                .add('hidden');


            document
                .getElementById('cashVoucherCard')
                .classList
                .add('hidden');
        }


        function goHome() {

            hideAllFlows();


            document
                .getElementById('welcomeCard')
                .classList
                .remove('hidden');


            document
                .getElementById('mainChoiceCard')
                .classList
                .remove('hidden');


            selectedAmount = null;


            document
                .querySelectorAll('.package')
                .forEach(function (element) {

                    element
                        .classList
                        .remove('selected');
                });


            document
                .getElementById('payerPhone')
                .value = '';


            document
                .getElementById('voucher')
                .value = '';


            clearPaymentStatus();


            document
                .getElementById('verifyPaymentButton')
                .disabled = false;
        }


        /*
        |--------------------------------------------------------------------------
        | PHONE PAYMENT
        |--------------------------------------------------------------------------
        */

        function openPhonePayment() {

            hideAllFlows();


            document
                .getElementById('welcomeCard')
                .classList
                .add('hidden');


            document
                .getElementById('mainChoiceCard')
                .classList
                .add('hidden');


            document
                .getElementById('phonePackagesCard')
                .classList
                .remove('hidden');
        }


        function selectPackage(element) {

            document
                .querySelectorAll('.package')
                .forEach(function (packageElement) {

                    packageElement
                        .classList
                        .remove('selected');
                });


            element
                .classList
                .add('selected');


            selectedAmount =
                Number(element.dataset.amount);


            document
                .getElementById('payAmountText')
                .textContent =
                formatMoney(selectedAmount)
                + ' TZS';


            document
                .getElementById('phonePackagesCard')
                .classList
                .add('hidden');


            document
                .getElementById('lipaNumberCard')
                .classList
                .remove('hidden');


            document
                .getElementById('payerPhone')
                .value = '';


            clearPaymentStatus();


            document
                .getElementById('verifyPaymentButton')
                .disabled = false;


            setTimeout(function () {

                document
                    .getElementById('payerPhone')
                    .focus();

            }, 200);
        }


        function backToPackages() {

            document
                .getElementById('lipaNumberCard')
                .classList
                .add('hidden');


            document
                .getElementById('phonePackagesCard')
                .classList
                .remove('hidden');


            document
                .getElementById('payerPhone')
                .value = '';


            clearPaymentStatus();


            document
                .getElementById('verifyPaymentButton')
                .disabled = false;
        }


        /*
        |--------------------------------------------------------------------------
        | CASH / VOUCHER
        |--------------------------------------------------------------------------
        */

        function openCashVoucher() {

            hideAllFlows();


            document
                .getElementById('welcomeCard')
                .classList
                .add('hidden');


            document
                .getElementById('mainChoiceCard')
                .classList
                .add('hidden');


            document
                .getElementById('cashVoucherCard')
                .classList
                .remove('hidden');


            setTimeout(function () {

                document
                    .getElementById('voucher')
                    .focus();

            }, 200);
        }


        /*
        |--------------------------------------------------------------------------
        | VERIFY PAYMENT
        |--------------------------------------------------------------------------
        */

        async function verifyPayment() {

            const payerPhone =
                document
                    .getElementById('payerPhone')
                    .value
                    .trim();


            if (!selectedAmount) {

                showPaymentStatus(
                    translate(
                        'Chagua kifurushi kwanza.',
                        'Please select a package first.'
                    ),
                    'error'
                );

                return;
            }


            if (!payerPhone) {

                showPaymentStatus(
                    translate(
                        'Ingiza namba ya simu uliyotumia kulipia.',
                        'Enter the phone number you used to make the payment.'
                    ),
                    'error'
                );

                return;
            }


            if (!deviceMac) {

                showPaymentStatus(
                    translate(
                        'MAC Address ya kifaa haijapatikana. Fungua portal kupitia JODEKA WiFi.',
                        'Device MAC Address was not received. Open this portal through JODEKA WiFi.'
                    ),
                    'error'
                );

                return;
            }


            const button =
                document
                    .getElementById('verifyPaymentButton');


            button.disabled = true;


            showPaymentStatus(
                translate(
                    'Inathibitisha malipo...',
                    'Verifying payment...'
                ),
                'info'
            );


            try {

                const response =
                    await fetch(
                        '/api/hotspot/payments/verify',
                        {
                            method: 'POST',

                            headers: {
                                'Content-Type':
                                    'application/json',

                                'Accept':
                                    'application/json'
                            },

                            body:
                                JSON.stringify({
                                    payer_phone: payerPhone,
                                    amount: selectedAmount,
                                    mac: deviceMac,
                                    ip: deviceIp
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
                    !response.ok
                    ||
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
                            'Malipo yamethibitishwa lakini voucher haijapatikana.',
                            'Payment was verified but the voucher was not found.'
                        )
                    );
                }


                showPaymentStatus(
                    translate(
                        'Malipo yamethibitishwa. Inaunganisha internet...',
                        'Payment verified. Connecting to the internet...'
                    ),
                    'success'
                );


                setTimeout(function () {

                    loginWithVoucher(
                        data.voucher
                    );

                }, 700);


            } catch (error) {

                showPaymentStatus(
                    error.message,
                    'error'
                );


                button.disabled = false;
            }
        }


        /*
        |--------------------------------------------------------------------------
        | AUTO LOGIN
        |--------------------------------------------------------------------------
        */

        function loginWithVoucher(voucherCode) {

            const voucher =
                String(voucherCode)
                    .trim()
                    .toUpperCase();


            if (!voucher) {
                return;
            }


            if (!loginUrl) {

                showPaymentStatus(
                    translate(
                        'MikroTik login URL haijapatikana.',
                        'MikroTik login URL was not received.'
                    ),
                    'error'
                );


                document
                    .getElementById('verifyPaymentButton')
                    .disabled = false;

                return;
            }


            document
                .getElementById('voucher')
                .value = voucher;


            document
                .getElementById('username')
                .value = voucher;


            document
                .getElementById('password')
                .value = voucher;


            document
                .getElementById('mikrotikLoginForm')
                .submit();
        }


        /*
        |--------------------------------------------------------------------------
        | MANUAL VOUCHER LOGIN
        |--------------------------------------------------------------------------
        */

        function prepareLogin(event) {

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


            if (!voucher) {

                event.preventDefault();

                return false;
            }


            document
                .getElementById('voucher')
                .value = voucher;


            document
                .getElementById('username')
                .value = voucher;


            document
                .getElementById('password')
                .value = voucher;


            return true;
        }


        /*
        |--------------------------------------------------------------------------
        | STATUS
        |--------------------------------------------------------------------------
        */

        function showPaymentStatus(
            message,
            type
        ) {

            const status =
                document
                    .getElementById('paymentStatus');


            status.textContent =
                message;


            status.className =
                'status-message ' + type;
        }


        function clearPaymentStatus() {

            const status =
                document
                    .getElementById('paymentStatus');


            status.textContent = '';

            status.className =
                'status-message hidden';
        }


        /*
        |--------------------------------------------------------------------------
        | LANGUAGE
        |--------------------------------------------------------------------------
        */

        function setLanguage(language) {

            currentLanguage =
                language;


            document
                .querySelectorAll('[data-sw][data-en]')
                .forEach(function (element) {

                    element.textContent =
                        element.dataset[language];
                });


            const voucherInput =
                document
                    .getElementById('voucher');


            const phoneInput =
                document
                    .getElementById('payerPhone');


            if (language === 'sw') {

                voucherInput.placeholder =
                    'Ingiza Voucher Code';


                phoneInput.placeholder =
                    'Mfano: 0659840000';


                document.documentElement.lang =
                    'sw';

            } else {

                voucherInput.placeholder =
                    'Enter Voucher Code';


                phoneInput.placeholder =
                    'Example: 0659840000';


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


        /*
        |--------------------------------------------------------------------------
        | HELPERS
        |--------------------------------------------------------------------------
        */

        function translate(sw, en) {

            return currentLanguage === 'sw'
                ? sw
                : en;
        }


        function formatMoney(amount) {

            return Number(amount)
                .toLocaleString('en-US');
        }


        setLanguage('sw');
    </script>

</body>

</html>