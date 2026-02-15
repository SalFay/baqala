<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12px;
            line-height: 1.5;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
        }
        .header p {
            margin: 5px 0;
            color: #666;
        }
        .info-section {
            margin-bottom: 20px;
        }
        .info-row {
            display: flex;
            margin-bottom: 5px;
        }
        .info-label {
            font-weight: bold;
            width: 150px;
        }
        .summary-table {
            width: 100%;
            margin-bottom: 30px;
        }
        .summary-table td {
            padding: 10px;
            text-align: center;
            border: 1px solid #ddd;
        }
        .summary-table .label {
            font-size: 11px;
            color: #666;
        }
        .summary-table .value {
            font-size: 16px;
            font-weight: bold;
        }
        .transactions-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .transactions-table th,
        .transactions-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        .transactions-table th {
            background-color: #f5f5f5;
            font-weight: bold;
        }
        .transactions-table .amount {
            text-align: right;
        }
        .debit {
            color: #dc3545;
        }
        .credit {
            color: #28a745;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ config('app.name', 'Baqala POS') }}</h1>
        <p>{{ $title }}</p>
    </div>

    <div class="info-section">
        <table width="100%">
            <tr>
                <td width="50%" valign="top">
                    <p><strong>{{ ucfirst($statement['entity_type']) }}:</strong> {{ $statement['entity']['name'] }}</p>
                    @if(isset($statement['entity']['phone']))
                        <p><strong>Phone:</strong> {{ $statement['entity']['phone'] }}</p>
                    @endif
                    @if(isset($statement['entity']['email']))
                        <p><strong>Email:</strong> {{ $statement['entity']['email'] }}</p>
                    @endif
                </td>
                <td width="50%" valign="top" align="right">
                    <p><strong>Statement Period:</strong></p>
                    <p>{{ $statement['period']['from'] }} to {{ $statement['period']['to'] }}</p>
                    <p><strong>Generated:</strong> {{ now()->format('Y-m-d H:i') }}</p>
                </td>
            </tr>
        </table>
    </div>

    <table class="summary-table">
        <tr>
            <td>
                <div class="label">Opening Balance</div>
                <div class="value">{{ number_format($statement['opening_balance'], 2) }} SAR</div>
            </td>
            <td>
                <div class="label">Total Debit</div>
                <div class="value debit">{{ number_format($statement['total_debit'], 2) }} SAR</div>
            </td>
            <td>
                <div class="label">Total Credit</div>
                <div class="value credit">{{ number_format($statement['total_credit'], 2) }} SAR</div>
            </td>
            <td>
                <div class="label">Closing Balance</div>
                <div class="value {{ $statement['closing_balance'] >= 0 ? 'debit' : 'credit' }}">
                    {{ number_format(abs($statement['closing_balance']), 2) }} SAR
                    {{ $statement['closing_balance'] >= 0 ? 'DR' : 'CR' }}
                </div>
            </td>
        </tr>
    </table>

    <h3>Transactions</h3>
    <table class="transactions-table">
        <thead>
            <tr>
                <th>Date</th>
                <th>Type</th>
                <th>Reference</th>
                <th>Description</th>
                <th class="amount">Debit</th>
                <th class="amount">Credit</th>
                <th class="amount">Balance</th>
            </tr>
        </thead>
        <tbody>
            @forelse($statement['transactions'] as $tx)
                <tr>
                    <td>{{ $tx['date'] }}</td>
                    <td>{{ ucwords(str_replace('_', ' ', $tx['type'])) }}</td>
                    <td>{{ $tx['reference'] }}</td>
                    <td>{{ $tx['description'] }}</td>
                    <td class="amount debit">{{ $tx['debit'] > 0 ? number_format($tx['debit'], 2) : '-' }}</td>
                    <td class="amount credit">{{ $tx['credit'] > 0 ? number_format($tx['credit'], 2) : '-' }}</td>
                    <td class="amount {{ $tx['balance'] >= 0 ? 'debit' : 'credit' }}">
                        {{ number_format(abs($tx['balance']), 2) }} {{ $tx['balance'] >= 0 ? 'DR' : 'CR' }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" style="text-align: center;">No transactions found</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <p>This is a computer-generated statement and does not require a signature.</p>
        <p>Generated by {{ config('app.name', 'Baqala POS') }} on {{ now()->format('Y-m-d H:i:s') }}</p>
    </div>
</body>
</html>
