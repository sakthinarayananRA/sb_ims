<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #{{ $order->order_number }}</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background-color: #f8fafc;
            color: #1e293b;
            margin: 0;
            padding: 24px 12px;
            line-height: 1.5;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid #e2e8f0;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        }
        .header {
            background-color: #1e293b;
            color: #ffffff;
            padding: 24px;
            text-align: left;
        }
        .header h1 {
            margin: 0;
            font-size: 20px;
            font-weight: 700;
            letter-spacing: -0.02em;
        }
        .header p {
            margin: 4px 0 0;
            font-size: 12px;
            color: #94a3b8;
        }
        .content {
            padding: 24px;
        }
        .info-grid {
            display: flex;
            justify-content: space-between;
            margin-bottom: 24px;
            background-color: #f8fafc;
            padding: 16px;
            border-radius: 8px;
            font-size: 13px;
        }
        .info-col {
            flex: 1;
        }
        .info-col p {
            margin: 2px 0;
        }
        .label {
            color: #64748b;
            font-size: 11px;
            text-transform: uppercase;
            font-weight: 600;
        }
        .value {
            font-weight: 600;
            color: #0f172a;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
            margin-bottom: 24px;
        }
        th {
            background-color: #f1f5f9;
            color: #475569;
            text-align: left;
            padding: 10px 12px;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            border-bottom: 1px solid #cbd5e1;
        }
        td {
            padding: 12px;
            border-bottom: 1px solid #e2e8f0;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .totals-box {
            max-width: 260px;
            margin-left: auto;
            background-color: #f8fafc;
            padding: 16px;
            border-radius: 8px;
            font-size: 13px;
            border: 1px solid #e2e8f0;
        }
        .totals-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            color: #475569;
        }
        .totals-row.grand-total {
            border-top: 1px solid #cbd5e1;
            padding-top: 8px;
            font-size: 15px;
            font-weight: 700;
            color: #0f172a;
        }
        .totals-row.paid {
            border-top: 1px dashed #cbd5e1;
            padding-top: 8px;
            margin-top: 8px;
            font-weight: 600;
        }
        .denominations {
            margin-top: 20px;
            padding: 12px;
            background-color: #f0fdf4;
            border: 1px solid #bbf7d0;
            border-radius: 8px;
            font-size: 12px;
            color: #166534;
        }
        .footer {
            background-color: #f8fafc;
            padding: 20px;
            text-align: center;
            font-size: 11px;
            color: #64748b;
            border-top: 1px solid #e2e8f0;
        }
    </style>
</head>
<body>

    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>Store Billing POS</h1>
            <p>Official Purchase Receipt & Tax Invoice</p>
        </div>

        <!-- Content -->
        <div class="content">
            <div class="info-grid">
                <div class="info-col">
                    <p class="label">Invoice Number</p>
                    <p class="value">{{ $order->order_number }}</p>
                    <p class="label" style="margin-top: 8px;">Order Date</p>
                    <p class="value">{{ $order->created_at->format('M d, Y • h:i A') }}</p>
                </div>
                <div class="info-col text-right">
                    <p class="label">Billed To</p>
                    <p class="value">{{ $customer?->name ?? 'Guest Customer' }}</p>
                    <p style="margin: 2px 0; color: #475569;">{{ $customer?->email ?? '-' }}</p>
                    @if($customer?->phone)
                        <p style="margin: 2px 0; color: #475569;">{{ $customer->phone }}</p>
                    @endif
                </div>
            </div>

            <!-- Items Table -->
            <table>
                <thead>
                    <tr>
                        <th>Item</th>
                        <th class="text-center">Qty</th>
                        <th class="text-right">Price</th>
                        <th class="text-right">Tax</th>
                        <th class="text-right">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($items as $item)
                        <tr>
                            <td>
                                <strong>{{ $item->product?->name ?? 'Item #' . $item->product_id }}</strong>
                            </td>
                            <td class="text-center">{{ $item->quantity }}</td>
                            <td class="text-right">₹{{ number_format($item->unit_price, 2) }}</td>
                            <td class="text-right">{{ $item->tax_percentage }}%</td>
                            <td class="text-right"><strong>₹{{ number_format($item->total, 2) }}</strong></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <!-- Totals Summary -->
            <div class="totals-box">
                <div class="totals-row">
                    <span>Subtotal:</span>
                    <span>₹{{ number_format($order->subtotal, 2) }}</span>
                </div>
                <div class="totals-row">
                    <span>Tax:</span>
                    <span>₹{{ number_format($order->tax_amount, 2) }}</span>
                </div>
                <div class="totals-row grand-total">
                    <span>Grand Total:</span>
                    <span>₹{{ number_format($order->grand_total, 2) }}</span>
                </div>
                @if($order->paid_amount !== null)
                    <div class="totals-row paid">
                        <span>Amount Paid:</span>
                        <span>₹{{ number_format($order->paid_amount, 2) }}</span>
                    </div>
                    <div class="totals-row">
                        <span>Change Returned:</span>
                        <span>₹{{ number_format($order->change_amount ?? 0, 2) }}</span>
                    </div>
                @endif
            </div>

            @if(!empty($denominations['summary']) && $denominations['summary'] !== 'None')
                <div class="denominations">
                    <strong>Currency Notes Returned:</strong> {{ $denominations['summary'] }}
                </div>
            @endif
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>Thank you for shopping with us! If you have any inquiries, please reply to this email.</p>
            <p style="margin-top: 4px; color: #94a3b8;">Store Billing System • Automated Order Notification</p>
        </div>
    </div>

</body>
</html>

