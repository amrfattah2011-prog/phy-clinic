<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إيصال قبض حراري - {{ $payment->receipt_number }}</title>
    <style>
        /* 80mm Thermal Printer Styling (Compatible with 58mm) */
        @page {
            margin: 0;
            size: 80mm auto;
        }

        * {
            box-sizing: border-box;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        body {
            font-family: 'Courier New', Courier, monospace, 'Segoe UI', Tahoma, Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f3f4f6;
            color: #000;
            font-size: 13px;
            line-height: 1.35;
        }

        .receipt-container {
            width: 80mm;
            max-width: 80mm;
            margin: 15px auto;
            background: #fff;
            padding: 4mm 5mm 6mm 5mm;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        .screen-toolbar {
            max-width: 80mm;
            margin: 10px auto;
            display: flex;
            gap: 8px;
            justify-content: center;
        }

        .btn {
            background-color: #059669;
            color: #fff;
            border: none;
            padding: 8px 14px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: bold;
            cursor: pointer;
            font-family: inherit;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .btn-secondary {
            background-color: #4b5563;
        }

        .btn:hover {
            opacity: 0.9;
        }

        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        .font-bold { font-weight: bold; }
        
        .header {
            text-align: center;
            border-bottom: 2px dashed #000;
            padding-bottom: 8px;
            margin-bottom: 8px;
        }

        .clinic-title {
            font-size: 16px;
            font-weight: 900;
            margin: 0;
            line-height: 1.2;
        }

        .clinic-subtitle {
            font-size: 12px;
            font-weight: bold;
            margin: 3px 0;
        }

        .clinic-info {
            font-size: 11px;
            margin: 2px 0;
        }

        .receipt-badge {
            display: inline-block;
            border: 1px solid #000;
            padding: 2px 8px;
            font-size: 12px;
            font-weight: bold;
            margin-top: 4px;
            border-radius: 3px;
        }

        .divider-dashed {
            border-top: 1px dashed #000;
            margin: 6px 0;
        }

        .divider-solid {
            border-top: 2px solid #000;
            margin: 6px 0;
        }

        .row {
            display: flex;
            justify-content: space-between;
            margin: 3px 0;
            font-size: 12px;
        }

        .row .label {
            font-weight: bold;
        }

        .row .value {
            text-align: left;
            direction: ltr;
        }

        .row .value-rtl {
            text-align: left;
            font-weight: bold;
        }

        .highlight-box {
            border: 2px solid #000;
            padding: 6px 8px;
            margin: 8px 0;
            text-align: center;
            border-radius: 4px;
        }

        .paid-amount {
            font-size: 20px;
            font-weight: 900;
            margin: 2px 0;
            direction: ltr;
        }

        .tafqeet {
            font-size: 11px;
            font-style: italic;
            margin-top: 3px;
        }

        .footer {
            margin-top: 8px;
            border-top: 1px dashed #000;
            padding-top: 6px;
            text-align: center;
            font-size: 11px;
        }

        .barcode-box {
            margin: 6px auto 3px auto;
            text-align: center;
            letter-spacing: 3px;
            font-size: 10px;
        }

        /* Print Media Query */
        @media print {
            body {
                background: none;
                margin: 0;
                padding: 0;
            }

            .screen-toolbar {
                display: none !important;
            }

            .receipt-container {
                width: 80mm;
                max-width: 80mm;
                margin: 0;
                padding: 2mm 3mm 4mm 3mm;
                box-shadow: none;
                border: none;
            }
        }
    </style>
</head>
<body>

    @php
        $settings = $settings ?? \App\Models\ClinicSetting::getSettings();
        $waPhone = $payment->patient->whatsapp_phone ?? '';
        $waMsg = "إيصال سداد - " . ($settings->clinic_name ?? 'العيادة') . "\n"
               . "المريض: " . ($payment->patient->name ?? '') . "\n"
               . "رقم الإيصال: " . $payment->receipt_number . "\n"
               . "المبلغ المسدد: " . number_format($payment->amount, 2) . " " . ($settings->currency ?? 'ج.م') . "\n"
               . "طريقة الدفع: " . $payment->payment_method_name . "\n"
               . "التاريخ: " . $payment->payment_date->format('Y-m-d') . "\n"
               . "المتبقي على الحساب: " . number_format($payment->patient->remaining_balance ?? 0, 2) . " " . ($settings->currency ?? 'ج.م') . "\n"
               . "نتمنى لكم دوام الصحة والعافية.";
        $waUrl = $waPhone ? "https://wa.me/{$waPhone}?text=" . urlencode($waMsg) : '#';
    @endphp

    <!-- On-screen Action Toolbar -->
    <div class="screen-toolbar">
        <button class="btn" onclick="window.print()">
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
            <span>طباعة الإيصال (80mm)</span>
        </button>
        @if($waPhone)
            <a href="{{ $waUrl }}" target="_blank" class="btn" style="background-color: #10b981; text-decoration: none;">
                <svg width="16" height="16" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
                <span>إرسال واتساب</span>
            </a>
        @endif
        <button class="btn btn-secondary" onclick="window.close(); history.back();">
            <span>إغلاق / رجوع</span>
        </button>
    </div>
    <!-- 80mm Thermal Receipt Content -->
    <div class="receipt-container">
        <!-- Clinic Header -->
        <div class="header">
            <h1 class="clinic-title">{{ $settings->clinic_name }}</h1>
            <div class="clinic-subtitle">{{ $settings->doctor_title }}</div>
            <div class="clinic-info">العنوان: {{ $settings->address }}</div>
            <div class="clinic-info">هاتف الحجز: {{ $settings->phone_1 }} @if($settings->phone_2) / {{ $settings->phone_2 }} @endif</div>
            @if($settings->license_number)
                <div class="clinic-info">ترخيص: {{ $settings->license_number }}</div>
            @endif
            <div class="receipt-badge">إيصال تحصيل نقدية</div>
        </div>

        <!-- Receipt & Patient Meta Info -->
        <div class="row">
            <span class="label">رقم السند:</span>
            <span class="value font-bold">{{ $payment->receipt_number }}</span>
        </div>
        <div class="row">
            <span class="label">تاريخ / وقت:</span>
            <span class="value">{{ $payment->payment_date->format('Y-m-d') }} {{ $payment->created_at->format('H:i') }}</span>
        </div>
        <div class="row">
            <span class="label">المحصل:</span>
            <span class="value-rtl font-bold">{{ $payment->creator ? $payment->creator->name : 'الاستقبال' }}</span>
        </div>
        <div class="divider-dashed"></div>

        <div class="row">
            <span class="label">اسم المريض:</span>
            <span class="value-rtl">{{ $patient->name }}</span>
        </div>
        <div class="row">
            <span class="label">رقم الملف:</span>
            <span class="value font-bold">#{{ str_pad($patient->id, 5, '0', STR_PAD_LEFT) }}</span>
        </div>
        <div class="row">
            <span class="label">رقم الهاتف:</span>
            <span class="value">{{ $patient->phone }}</span>
        </div>

        <div class="divider-dashed"></div>

        <!-- Service Info -->
        <div class="row">
            <span class="label">الخدمة / البيان:</span>
            <span class="value-rtl">{{ $serviceName }}</span>
        </div>
        <div class="row">
            <span class="label">طريقة السداد:</span>
            <span class="value-rtl">{{ $payment->payment_method_arabic }}</span>
        </div>
        @if($payment->notes)
            <div class="row">
                <span class="label">ملاحظات:</span>
                <span class="value-rtl">{{ $payment->notes }}</span>
            </div>
        @endif

        <!-- Amount Box -->
        <div class="highlight-box">
            <div style="font-size: 12px; font-weight: bold;">المبلغ المدفوع حالياً</div>
            <div class="paid-amount">{{ number_format($payment->amount, 2) }} {{ $settings->currency }}</div>
            <div class="tafqeet">{{ $tafqeetAmount }}</div>
        </div>

        <!-- Patient Ledger Balance Snapshot -->
        <div class="row">
            <span class="label">إجمالي تكلفة الخدمات:</span>
            <span class="value">{{ number_format($totalCost, 2) }} {{ $settings->currency }}</span>
        </div>
        <div class="row">
            <span class="label">إجمالي المسدد حتى الآن:</span>
            <span class="value font-bold">{{ number_format($totalPaid, 2) }} {{ $settings->currency }}</span>
        </div>
        <div class="divider-solid"></div>
        <div class="row" style="font-size: 13px; font-weight: 900;">
            <span>المتبقي في الحساب:</span>
            <span class="value font-bold" style="{{ $remainingBalance > 0 ? 'text-decoration: underline;' : '' }}">
                {{ number_format($remainingBalance, 2) }} {{ $settings->currency }}
            </span>
        </div>

        <!-- Barcode simulation -->
        <div class="barcode-box">
            <div style="letter-spacing: 6px; font-family: monospace; font-size: 18px; font-weight: bold; margin: 4px 0;">
                *{{ $payment->receipt_number }}*
            </div>
            <div>رقم الفاتورة الإلكترونية المعتمد</div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <div>{{ $settings->receipt_footer ?: 'نتمنى لكم دوام الصحة والعافية والشفاء العاجل' }}</div>
            <div style="margin-top: 4px; font-weight: bold;">{{ $settings->clinic_name }} - {{ $settings->doctor_name }}</div>
            <div style="margin-top: 3px; font-size: 9px; color: #333;">المحصل: {{ $payment->creator ? $payment->creator->name . ' (' . ($payment->creator->role?->display_name ?: 'موظف') . ')' : 'قسم الاستقبال' }} • طبع بتاريخ: {{ date('Y-m-d H:i') }}</div>
        </div>
    </div>

    <!-- Auto-Print Script -->
    <script>
        window.addEventListener('DOMContentLoaded', () => {
            // Auto trigger print dialog after 400ms for layout stabilization
            setTimeout(() => {
                window.print();
            }, 400);
        });
    </script>
</body>
</html>
