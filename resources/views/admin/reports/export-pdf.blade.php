<!DOCTYPE html>
<html dir="ltr">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ __('messages.hospital_reports_analytics') }}</title>
    <style>
        * {
            unicode-bidi: bidi-override;
            box-sizing: border-box;
        }
        body {
            font-family: "DejaVu Sans", sans-serif;
            color: #333;
            margin: 20px;
            direction: ltr;
            unicode-bidi: embed;
        }
        h1, h2, h3, th, td, p, div, span {
            unicode-bidi: embed;
            direction: ltr;
        }
        h1 {
            text-align: center;
            color: #1f2937;
            border-bottom: 3px solid #3b82f6;
            padding-bottom: 10px;
            font-size: 2.2rem;
        }
        h2 {
            color: #374151;
            margin-top: 25px;
            border-left: 4px solid #3b82f6;
            padding-left: 10px;
            font-size: 1.2rem;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
            font-size: 1rem;
            /* overflow-x: auto; */
            /* display: block; */
        }
        th, td {
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #f3f4f6;
            border: 1px solid #d1d5db;
            font-weight: bold;
        }
        td {
            border: 1px solid #d1d5db;
        }
        tr:nth-child(even) {
            background-color: #f9fafb;
        }
        .summary {
            display: flex;
            justify-content: space-around;
            margin: 20px 0;
            flex-wrap: wrap;
            gap: 1rem;
        }
        .summary-box {
            background-color: #f3f4f6;
            border-left: 4px solid #3b82f6;
            padding: 15px 20px;
            margin: 10px 0;
            border-radius: 4px;
            flex: 1 1 180px;
            min-width: 120px;
            max-width: 300px;
            box-sizing: border-box;
        }
        .summary-box label {
            font-size: 13px;
            color: #6b7280;
            font-weight: bold;
        }
        .summary-box .value {
            font-size: 1.5rem;
            font-weight: bold;
            color: #1f2937;
            margin-top: 5px;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            font-size: 12px;
            color: #9ca3af;
            border-top: 1px solid #e5e7eb;
            padding-top: 15px;
        }
        @media (max-width: 900px) {
            .summary {
                flex-direction: column;
                align-items: stretch;
            }
            .summary-box {
                max-width: 100%;
                margin: 8px 0;
            }
            table {
                font-size: 0.95rem;
            }
        }
        @media (max-width: 600px) {
            body {
                margin: 6px;
            }
            h1 {
                font-size: 1.3rem;
            }
            h2 {
                font-size: 1rem;
            }
            .summary-box .value {
                font-size: 1.1rem;
            }
            table, th, td {
                font-size: 0.85rem;
            }
            th, td {
                padding: 6px;
            }
        }
    </style>
</head>
<body>
    <h1>{{ __('messages.hospital_reports_analytics') }}</h1>

    <div class="summary">
        <div class="summary-box">
            <label>{{ __('messages.total_appointments') }}</label>
            <div class="value">{{ number_format($totals['appointments']) }}</div>
        </div>
        <div class="summary-box">
            <label>{{ __('messages.total_patients') }}</label>
            <div class="value">{{ number_format($totals['patients']) }}</div>
        </div>
        <div class="summary-box">
            <label>{{ __('messages.total_doctors') }}</label>
            <div class="value">{{ number_format($totals['doctors']) }}</div>
        </div>
    </div>

    @php($statsTitle = ($seriesTitle ?? 'Weekly') === 'Daily' ? __('messages.daily_statistics') : ((($seriesTitle ?? 'Weekly') === 'Monthly') ? __('messages.monthly_statistics') : __('messages.weekly_statistics')))
    <h2>{{ $statsTitle }} ({{ $timeframeLabel ?? __('messages.last_7_weeks') }})</h2>
    <table>
        <thead>
            <tr>
                <th>
                    @php($hc = $headerColumn ?? ($seriesUnitSingular ?? 'Week'))
                    @if($hc === 'Date')
                        {{ __('messages.date') }}
                    @elseif($hc === 'Month')
                        {{ __('messages.month') }}
                    @else
                        {{ __('messages.week') }}
                    @endif
                </th>
                <th>{{ __('messages.appointments') }}</th>
                <th>{{ __('messages.patients') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach($weeklySeries['labels'] as $i => $label)
                <tr>
                    <td>{{ $label }}</td>
                    <td>{{ $weeklySeries['appointments'][$i] ?? 0 }}</td>
                    <td>{{ $weeklySeries['patients'][$i] ?? 0 }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <h2>{{ __('messages.top_doctors') }} ({{ $timeframeLabel ?? __('messages.last_7_weeks') }})</h2>
    <table>
        <thead>
            <tr>
                <th>{{ __('messages.doctor_name') }}</th>
                <th>{{ __('messages.appointments') }}</th>
                <th>{{ __('messages.performance') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($topDoctors as $doctor)
                <tr>
                    <td>{{ $doctor->name }}</td>
                    <td>{{ $doctor->total }}</td>
                    <td>{{ $doctor->percentage }}%</td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" style="text-align: center; color: #9ca3af;">{{ __('messages.no_doctor_data') }}</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <p>{{ __('messages.report_generated') }}: {{ $generatedAt }}</p>
        <p>{{ __('messages.hospital_management_system') }}</p>
    </div>
</body>
</html>
