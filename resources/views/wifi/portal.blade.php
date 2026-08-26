<!DOCTYPE html>
<html lang="en">

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

        body {
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
            background:
                linear-gradient(rgba(2, 8, 20, .94), rgba(2, 8, 20, .98));
            color: white;
        }

        .container {
            width: 100%;
            max-width: 430px;
            background: rgba(5, 10, 20, .96);
            border-radius: 28px;
            padding: 32px 28px;
            border: 1px solid rgba(255, 255, 255, .06);
            box-shadow: 0 20px 60px rgba(0, 0, 0, .45);
        }

        .brand {
            text-align: center;
            margin-bottom: 24px;
        }

        .brand h1 {
            font-size: 34px;
            margin-bottom: 6px;
        }

        .brand p {
            color: #9ca3af;
            font-size: 14px;
        }

        .payment-box {
            background: #0b1222;
            border: 1px solid rgba(34, 197, 94, .18);
            border-radius: 22px;
            padding: 20px;
            margin-bottom: 22px;
        }

        .payment-title {
            text-align: center;
            font-size: 18px;
            font-weight: bold;
            color: #22c55e;
            margin-bottom: 18px;
        }

        .payment-flex {
            display: flex;
            gap: 14px;
        }

        .payment-item {
            width: 50%;
            text-align: center;
        }

        .payment-label {
            color: #cbd5e1;
            font-size: 12px;
            margin-bottom: 8px;
        }

        .payment-number {
            font-size: 21px;
            font-weight: bold;
        }

        .green {
            color: #22c55e;
        }

        .blue {
            color: #3b82f6;
        }

        .payment-note {
            text-align: center;
            color: #d1d5db;
            font-size: 13px;
            line-height: 1.6;
            margin-top: 16px;
        }

        .input-box {
            margin-bottom: 16px;
        }

        .input-box input {
            width: 100%;
            padding: 17px;
            border: none;
            outline: none;
            border-radius: 16px;
            background: #111827;
            color: white;
            font-size: 16px;
        }

        .input-box input:focus {
            box-shadow: 0 0 0 2px rgba(34, 197, 94, .35);
        }

        button {
            width: 100%;
            padding: 17px;
            border: none;
            border-radius: 16px;
            background: linear-gradient(135deg, #22c55e, #06b6d4);
            color: white;
            font-size: 18px;
            font-weight: bold;
            cursor: pointer;
        }

        .packages {
            margin-top: 28px;
        }

        .packages h3 {
            margin-bottom: 14px;
            color: #d1d5db;
            font-size: 14px;
        }

        .pkg {
            background: #0b1222;
            border: 1px solid rgba(255, 255, 255, .05);
            border-radius: 16px;
            padding: 15px;
            margin-bottom: 10px;
        }

        .highlight {
            color: #22c55e;
            font-weight: bold;
            font-size: 20px;
        }

        .info {
            margin-top: 18px;
            font-size: 12px;
            line-height: 1.6;
            color: #94a3b8;
        }

        .footer {
            margin-top: 22px;
            text-align: center;
            font-size: 12px;
            color: #9ca3af;
        }

        @media (max-width: 480px) {
            .brand h1 {
                font-size: 30px;
            }

            .payment-number {
                font-size: 18px;
            }
        }
    </style>
</head>

<body>

    <div class="container">

        <div class="brand">
            <h1>Jodeka Hotspot</h1>

            <p>
                Fast • Secure • Unlimited
            </p>
        </div>

        <div class="payment-box">

            <div class="payment-title">
                JINSI YA KUPATA VOUCHER
            </div>

            <div class="payment-flex">

                <div class="payment-item">
                    <div class="payment-label">
                        LIPA NAMBA YAS
                    </div>

                    <div class="payment-number green">
                        19361296
                    </div>
                </div>

                <div class="payment-item">
                    <div class="payment-label">
                        TUMA MSG
                    </div>

                    <div class="payment-number blue">
                        0659840000
                    </div>
                </div>

            </div>

            <div class="payment-note">
                Tuma uthibitisho wa malipo upate Voucher Code.
            </div>

        </div>


        @if($loginUrl)

            <form method="post" action="{{ $loginUrl }}">

                <input type="hidden" name="dst" value="{{ $originalUrl ?? '' }}">

                <input type="hidden" name="popup" value="true">

                <div class="input-box">
                    <input type="text" id="voucher" placeholder="Enter Voucher Code" autocomplete="off" required>
                </div>

                <input type="hidden" id="username" name="username">

                <input type="hidden" id="password" name="password">

                <button type="submit" onclick="
                        const code = document.getElementById('voucher').value.trim();
                        document.getElementById('username').value = code;
                        document.getElementById('password').value = code;
                    ">
                    Connect Now
                </button>

            </form>

        @else

            <div class="payment-box">
                <div class="payment-note">
                    Wi-Fi login information was not received from MikroTik.
                </div>
            </div>

        @endif


        <div class="packages">

            <h3>
                AVAILABLE PACKAGES
            </h3>

            <div class="pkg">
                <span class="highlight">200 TZS</span>
                — 30 Minutes
            </div>

            <div class="pkg">
                <span class="highlight">500 TZS</span>
                — 12 Hours
            </div>

            <div class="pkg">
                <span class="highlight">1000 TZS</span>
                — 24 Hours
            </div>

            <div class="pkg">
                <span class="highlight">3000 TZS</span>
                — 7 Days
            </div>

        </div>


        @if($mac || $ip)

            <div class="info">

                @if($ip)
                    IP: {{ $ip }}
                @endif

                @if($mac)
                    <br>
                    Device: {{ $mac }}
                @endif

            </div>

        @endif


        <div class="footer">
            Powered by Jodeka Enterprises Ltd
        </div>

    </div>

</body>

</html>