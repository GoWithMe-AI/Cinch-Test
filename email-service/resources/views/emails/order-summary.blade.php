<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Order Summary</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #4CAF50;
            color: white;
            padding: 20px;
            text-align: center;
        }
        .content {
            background-color: #f9f9f9;
            padding: 20px;
            margin-top: 20px;
        }
        .order-info {
            background-color: white;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        .order-items {
            background-color: white;
            padding: 15px;
            border-radius: 5px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #f2f2f2;
        }
        .total {
            font-size: 18px;
            font-weight: bold;
            color: #4CAF50;
            margin-top: 15px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Order Summary</h1>
        </div>
        <div class="content">
            <div class="order-info">
                <h2>Order Details</h2>
                <p><strong>Order ID:</strong> #{{ $order['id'] }}</p>
                <p><strong>Order Date:</strong> {{ date('F j, Y g:i A', strtotime($order['created_at'])) }}</p>
                <p><strong>Status:</strong> {{ ucfirst($order['status']) }}</p>
            </div>

            <div class="order-items">
                <h2>Order Items</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Product ID</th>
                            <th>Quantity</th>
                            <th>Price</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($items as $item)
                        <tr>
                            <td>{{ $item['product_id'] }}</td>
                            <td>{{ $item['quantity'] }}</td>
                            <td>${{ number_format($item['price'], 2) }}</td>
                            <td>${{ number_format($item['price'] * $item['quantity'], 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="total">
                    <p>Total Amount: ${{ number_format($order['total_amount'], 2) }}</p>
                </div>
            </div>

            <p style="margin-top: 20px; text-align: center; color: #666;">
                Thank you for your order!
            </p>
        </div>
    </div>
</body>
</html>

