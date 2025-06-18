<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Bestelling #{{ $order->id }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f4f4f4;
        }

        .email-container {
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }

        .header {
            background-color: #343a40;
            color: #fff;
            padding: 20px;
            text-align: center;
        }

        .header h1 {
            margin: 0;
            font-size: 20px;
        }

        .content {
            padding: 20px;
        }

        .footer {
            background-color: #f8f9fa;
            text-align: center;
            padding: 15px 20px;
            font-size: 13px;
            color: #666;
            border-top: 1px solid #e1e1e1;
        }

        .footer a {
            color: #007bff;
            text-decoration: none;
        }

        .footer a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <h1>Festival Bestelling #{{ $order->id }}</h1>
        </div>
        
        <div class="content">
            {!! $emailMessage !!}
        </div>
        
        <div class="footer">
            <p>Deze e-mail is automatisch gegenereerd via <strong>Eventory</strong>, het festivalbeheerplatform.</p>
            <p>Bezoek ons op <a href="https://eventory.app" target="_blank">eventory.app</a> voor meer informatie.</p>
        </div>
    </div>
</body>
</html>
