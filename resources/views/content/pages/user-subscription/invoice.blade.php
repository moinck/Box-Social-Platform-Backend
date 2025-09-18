<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice INV-2025-001</title>
    <style>
        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 13px;
            color: #333;
        }
        .invoice-box {
            width: 98%;
            padding: 20px;
            border: 1px solid #eee;
        }
        .heading {
            font-size: 16px;
            margin: 20px 0 10px;
            font-weight: bold;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        table th, table td {
            border: 1px solid #ddd;
        }
        table th {
            background: #f8f8f8;
            text-align: left;
        }
        .text-right {
            text-align: right;
        }
        .total {
            font-weight: bold;
            background: #f0f0f0;
        }
        .footer {
            margin-top: 30px;
            font-size: 12px;
            text-align: center;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="invoice-box">
        <!-- Header -->
        <table style="width:100%; margin-bottom:15px; border:none;">
            <tr style="border:none;">
                <td style="border:none; display:flex; align-items:center;">
                    <img src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('assets/img/box-socials-tm.png'))) }}" style="height:95px;"></br>
                    
                </td>
                <td style="border:none; text-align:right; vertical-align:middle;">
                    @if ($subscription->invoice_number)
                    <strong>Invoice #: </strong> {{ $subscription->invoice_number }} <br>  
                    @endif
                    <strong>Date: </strong> {{ \Carbon\Carbon::parse($subscription->created_at)->format('d-m-Y') }}
                </td>
            </tr>
        </table>
        <table style="width:100%; margin-bottom:20px; border:none;">
            <tr style="border:none;">
                <td style="border:none; display:flex; align-items:center;">
                    <p><strong>IsocialCircle Ltd trading as Box Socials</strong><br><strong>Company number: </strong>10916328</p>
                    
                </td>
                <td style="border:none; text-align:right; vertical-align:middle;">
                    <p><strong>Registered address:</strong><br>4 Capricorn Centre, Cranes Farm Road, <br> Basildon Essex SS14 3JJ United Kingdom</p>
                </td>
            </tr>
        </table>
        <hr>
        <!-- Billed To -->
        <div class="heading">Billed To</div>
        <p>
            {{ $subscription->user->first_name }} {{ $subscription->user->last_name }} <br>
            {{ $subscription->user->email }}
        </p>

        <!-- Subscription / Plan -->
        <div class="heading">Subscription Details</div>
        <table>
            <thead>
                <tr>
                    <th>Plan</th>
                    <th>Start Date</th>
                    <th>End Date</th>
                    <th>Duration</th>
                    <th class="text-right">Price</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>{{ $subscription->plan->name }}</td>
                    <td>{{ \Carbon\Carbon::parse($subscription->current_period_start)->format('d-m-Y') }}</td>
                    <td>{{ \Carbon\Carbon::parse($subscription->current_period_end)->format('d-m-Y') }}</td>
                    <td>{{ $subscription->plan->interval_count }} {{ ucwords($subscription->plan->interval) }}</td>
                    <td class="text-right">£{{ $subscription->amount_paid ? number_format($subscription->amount_paid + $subscription->coupon_discount,2,'.', '') : 0 }}</td>
                </tr>
            </tbody>
        </table>

        <!-- Payment Summary -->
        <div class="heading">Payment Summary</div>
        <table style="width:100%; margin-bottom:35px;">
            <tbody>
                <tr>
                    <td>Subtotal</td>
                    <td class="text-right">£{{ $subscription->amount_paid ? number_format($subscription->amount_paid + $subscription->coupon_discount,2,'.', '') : 0 }}</td>
                </tr>
                @if($subscription->coupon_code)
                <tr>
                    <td>{{ $subscription->coupon_code }} (£{{ $subscription->coupon_discount }} Off)</td>
                    <td class="text-right" style="color: red;">- £{{ $subscription->coupon_discount ? $subscription->coupon_discount : 0 }}</td>
                </tr>
                @endif
                <tr class="total" style="font-size: 15px;">
                    <td>Total Amount</td>
                    <td class="text-right">£{{ $subscription->amount_paid ? $subscription->amount_paid : 0 }}</td>
                </tr>
                @if ($subscription->plan_id != 1)
                <tr>
                    <td>Status</td>
                    <td class="text-right" style="color:green;"><strong>{{ ucwords($subscription->stripe_status) }}</strong></td>
                </tr>
                @endif
            </tbody>
        </table>

        <hr>

        <table style="width:100%; margin-bottom:20px; border:none;">
            <tr style="border:none;">
                <td style="border:none; display:flex; align-items:center;">
                    <p><strong>Need help?</strong><br> Reach out to us at <a href="mailto:help@boxsocials.com">help@boxsocials.com   </a></p>
                </td>
                <td style="border:none; text-align:right; vertical-align:middle;">
                    <p><strong>Website: </strong><br><a href="www.boxsocials.com">www.boxsocials.com</a></p>
                </td>
            </tr>
        </table>

        <!-- Footer -->
        <div class="footer">
            Thank you for your business! <br>
            This is a system-generated invoice and does not require signature.
        </div>
    </div>
</body>
</html>
