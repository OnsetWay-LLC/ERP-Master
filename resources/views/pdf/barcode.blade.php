<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            width: 100%;
            height: 100%;
            font-family: DejaVu Sans, sans-serif;
            text-align: center;
            padding: 8px 6px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .label {
            width: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 4px;
        }

        .name {
            font-size: 10px;
            font-weight: bold;
            margin-bottom: 4px;
            word-break: break-word;
        }

        .barcode-img {
            width: 180px;
            height: 55px;
            display: block;
        }

        .code {
            font-size: 9px;
            letter-spacing: 1px;
            margin-top: 2px;
        }

        .price {
            font-size: 10px;
            font-weight: bold;
            margin-top: 4px;
        }
    </style>
</head>
<body>
    <div class="label">
      
        <img class="barcode-img" src="{{ $barcodeImg }}" alt="barcode">

        <div class="code">{{ $item->barcode }}</div>

      
    </div>
</body>
</html>