<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Profit & Loss</title>
  <style>
    body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #0f172a; }
    h1 { font-size: 18px; margin-bottom: 0; }
    .muted { color: #64748b; margin-top: 0; }
    table { width: 100%; border-collapse: collapse; margin-top: 12px; }
    th, td { padding: 6px; border-bottom: 1px solid #e2e8f0; text-align: left; }
    .right { text-align: right; }
    .total { font-weight: bold; }
  </style>
</head>
<body>
  <h1>{{ setting('site_name') }} - Profit & Loss Statement</h1>
  <p class="muted">{{ $report['period']['from'] }} to {{ $report['period']['to'] }}</p>

  <h3>Income</h3>
  <table>
    @foreach($report['income']['items'] as $item)
    <tr><td>{{ $item['name'] }}</td><td class="right">{{ number_format($item['amount'], 2) }}</td></tr>
    @endforeach
    <tr class="total"><td>Total Income</td><td class="right">{{ number_format($report['income']['total'], 2) }}</td></tr>
  </table>

  <h3>Cost of Goods Sold</h3>
  <table>
    @foreach($report['cost_of_goods']['items'] as $item)
    <tr><td>{{ $item['name'] }}</td><td class="right">{{ number_format($item['amount'], 2) }}</td></tr>
    @endforeach
    <tr class="total"><td>Total COGS</td><td class="right">{{ number_format($report['cost_of_goods']['total'], 2) }}</td></tr>
  </table>

  <h3>Expenses</h3>
  <table>
    @foreach($report['expenses']['items'] as $item)
    <tr><td>{{ $item['name'] }}</td><td class="right">{{ number_format($item['amount'], 2) }}</td></tr>
    @endforeach
    <tr class="total"><td>Total Expenses</td><td class="right">{{ number_format($report['expenses']['total'], 2) }}</td></tr>
  </table>

  <h3>Summary</h3>
  <table>
    <tr><td>Gross Profit</td><td class="right">{{ number_format($report['gross_profit'], 2) }}</td></tr>
    <tr class="total"><td>Net Profit</td><td class="right">{{ number_format($report['net_profit'], 2) }}</td></tr>
  </table>
</body>
</html>

