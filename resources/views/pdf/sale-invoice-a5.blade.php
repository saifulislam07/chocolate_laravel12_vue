{{--
    The A5 invoice as a downloadable PDF.

    A deliberate second rendering of the sheet in Admin/Sales/Show.vue: dompdf
    has no flexbox, so the same document is laid out here with tables. The
    figures, the wording and the ink (near-black for what matters, grey for
    labels, one hairline for structure) are kept identical to the printed sheet,
    so a downloaded invoice and a browser-printed one are the same document.
--}}
@php
    $money = fn ($value) => number_format((float) ($value ?? 0), 2);
    $amount = fn ($value) => (float) ($value ?? 0);
    $isPos = $sale->order_source === 'pos';
    $subtotal = $sale->subtotal ?? $sale->items->sum(fn ($item) => $item->price * $item->quantity);
@endphp
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice {{ $sale->order_number }}</title>
    <style>
        /* DejaVu — dompdf's bundled unicode font — carries no Bengali, so the
           taka sign gets the one family here that has it, used for that glyph
           alone (.tk) rather than for the document's Latin text. */
        @font-face {
            font-family: 'taka';
            font-style: normal;
            font-weight: normal;
            src: url("{{ $fonts['regular'] }}") format("truetype");
        }

        @font-face {
            font-family: 'taka';
            font-style: normal;
            font-weight: bold;
            src: url("{{ $fonts['bold'] }}") format("truetype");
        }

        /* The sheet's own 12mm/11mm padding becomes the page box here: dompdf
           paints straight onto the paper, with no print dialog in between to
           override a margin. */
        @page { margin: 12mm 11mm; }

        body {
            margin: 0;
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 8pt;
            line-height: 1.5;
            color: #111;
        }

        table { border-collapse: collapse; width: 100%; }
        td, th { padding: 0; vertical-align: top; }

        .muted { color: #8c8c8c; font-size: 7pt; }
        .strong { font-weight: bold; }
        .right { text-align: right; }
        .ok { color: #1a7f4b; }
        .due { color: #c0392b; }
        .tk { font-family: 'taka'; }

        .label {
            text-transform: uppercase;
            letter-spacing: 0.16em;
            font-size: 6pt;
            color: #a3a3a3;
            padding-bottom: 2px;
        }

        /* Header */
        .logo { max-height: 30px; max-width: 130px; margin-bottom: 4px; }
        .shop { font-size: 10pt; font-weight: bold; }

        .word {
            text-transform: uppercase;
            letter-spacing: 0.28em;
            font-size: 6.5pt;
            color: #9a9a9a;
        }

        .number { font-weight: bold; font-size: 9pt; }

        .stamp {
            font-size: 6.5pt;
            font-weight: bold;
            letter-spacing: 0.16em;
            padding-top: 2px;
        }

        .parties { margin-top: 6mm; }

        /* Items — a rule instead of a filled band, so nothing depends on the
           printer reproducing a background. */
        .items { margin-top: 6mm; }

        .items th {
            text-align: left;
            font-size: 6pt;
            text-transform: uppercase;
            letter-spacing: 0.16em;
            color: #a3a3a3;
            font-weight: normal;
            border-bottom: 1px solid #111;
            padding: 0 3px 4px;
        }

        .items td {
            padding: 2.5px 3px;
            border-bottom: 1px solid #ececec;
        }

        .items .num { text-align: right; }
        .items .idx { width: 16px; color: #b0b0b0; }
        .sku { color: #a3a3a3; font-size: 6pt; }

        /* Summary — the words go in the half that sits empty beside the
           figures, so spelling the total out costs the items table no room. */
        .summary { margin-top: 6mm; }
        .in-words { font-size: 7pt; color: #444; padding-right: 8mm; }
        .totals { width: 52%; }
        .totals td { padding: 2.5px 3px; color: #555; }

        /* The one figure the reader is looking for. */
        .grand td {
            border-top: 1px solid #111;
            padding-top: 4px;
            padding-bottom: 4px;
            font-weight: bold;
            font-size: 9pt;
            color: #111;
        }

        .note {
            margin-top: 8mm;
            font-size: 7pt;
            color: #6b6b6b;
            width: 88mm;
        }

        .foot { margin-top: 8mm; }

        .signs td { padding-top: 10mm; }

        /* The rules sit at the two edges of the sheet, as they do in the
           browser's copy; the cell between them is what holds them apart. */
        .signs .slot { width: 42mm; }

        .sign {
            border-top: 1px solid #cfcfcf;
            padding-top: 3px;
            text-align: center;
            font-size: 6.5pt;
            letter-spacing: 0.04em;
            color: #9a9a9a;
        }

        .thanks {
            margin-top: 6mm;
            text-align: center;
            font-size: 7pt;
            letter-spacing: 0.06em;
            color: #b0b0b0;
        }
    </style>
</head>
<body>
    <table>
        <tr>
            <td>
                @if ($logo)
                    <img src="{{ $logo }}" alt="" class="logo">
                @endif
                <div class="shop">{{ $shop->site_name ?? 'Invoice' }}</div>
                @if ($shop?->address)
                    <div class="muted">{{ $shop->address }}</div>
                @endif
                @php $contact = collect([$shop?->phone, $shop?->email])->filter()->implode(' · '); @endphp
                @if ($contact)
                    <div class="muted">{{ $contact }}</div>
                @endif
            </td>
            <td class="right" style="width: 45%;">
                <div class="word">Invoice</div>
                <div class="number">#{{ $sale->order_number }}</div>
                <div class="muted">Order Date: {{ $orderDate }}</div>
                <div class="stamp {{ $sale->payment_status === 'paid' ? 'ok' : 'due' }}">
                    {{ strtoupper($sale->payment_status ?? '') }}
                </div>
            </td>
        </tr>
    </table>

    <table class="parties">
        <tr>
            <td style="width: 62%;">
                <div class="label">Billed To</div>
                @if ($isPos)
                    <div class="strong">{{ $sale->customer->name ?? 'Walk-in Customer' }}</div>
                    @if ($sale->customer?->address)<div>{{ $sale->customer->address }}</div>@endif
                    @if ($sale->customer?->phone)<div>{{ $sale->customer->phone }}</div>@endif
                    @if ($sale->customer?->email)<div>{{ $sale->customer->email }}</div>@endif
                @else
                    <div class="strong">{{ $sale->customer_name ?: ($sale->user->name ?? 'Guest User') }}</div>
                    @if ($sale->shipping_address)
                        {{-- The address is stored with its own line breaks; nl2br keeps them. --}}
                        <div>{!! nl2br(e($sale->shipping_address)) !!}</div>
                    @endif
                    @if ($sale->customer_phone)<div>{{ $sale->customer_phone }}</div>@endif
                    @if ($sale->customer?->email ?? $sale->user?->email)
                        <div>{{ $sale->customer?->email ?? $sale->user?->email }}</div>
                    @endif
                @endif
            </td>
            <td class="right">
                <div class="label">Payment</div>
                <div class="strong">{{ strtoupper($sale->payment_method ?? '') }}</div>
                @if ($sale->lead_source)
                    <div class="muted">{{ strtoupper($sale->lead_source) }}</div>
                @endif
            </td>
        </tr>
    </table>

    <table class="items">
        <thead>
            <tr>
                <th class="idx">#</th>
                <th>Item</th>
                <th class="num">Qty</th>
                <th class="num">Price</th>
                <th class="num">Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($sale->items as $index => $item)
                <tr>
                    <td class="idx">{{ $index + 1 }}</td>
                    <td>
                        <div>{{ $item->product_name ?: $item->product?->name }}</div>
                        @if ($item->product?->sku)
                            <div class="sku">{{ $item->product->sku }}</div>
                        @endif
                    </td>
                    <td class="num">{{ $item->quantity }}</td>
                    <td class="num"><span class="tk">৳</span>{{ $money($item->price) }}</td>
                    <td class="num"><span class="tk">৳</span>{{ $money($item->price * $item->quantity) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="summary">
        <tr>
            <td class="in-words">
                <div class="label">In Words</div>
                {{ $sale->total_in_words }}
            </td>
            <td class="totals">
                <table>
                    <tr>
                        <td>Subtotal</td>
                        <td class="right"><span class="tk">৳</span>{{ $money($subtotal) }}</td>
                    </tr>
                    @if ($amount($sale->discount) > 0)
                        <tr>
                            <td>Discount</td>
                            <td class="right due">-<span class="tk">৳</span>{{ $money($sale->discount) }}</td>
                        </tr>
                    @endif
                    @if ($amount($sale->tax) > 0)
                        <tr>
                            <td>Tax</td>
                            <td class="right"><span class="tk">৳</span>{{ $money($sale->tax) }}</td>
                        </tr>
                    @endif
                    @if ($amount($sale->shipping_cost) > 0)
                        <tr>
                            <td>Shipping</td>
                            <td class="right"><span class="tk">৳</span>{{ $money($sale->shipping_cost) }}</td>
                        </tr>
                    @endif
                    <tr class="grand">
                        <td>Total</td>
                        <td class="right"><span class="tk">৳</span>{{ $money($sale->total) }}</td>
                    </tr>
                    <tr>
                        <td>Paid</td>
                        <td class="right ok"><span class="tk">৳</span>{{ $money($sale->paid_amount) }}</td>
                    </tr>
                    @if ($amount($sale->due_amount) > 0)
                        <tr>
                            <td>Due</td>
                            <td class="right due"><span class="tk">৳</span>{{ $money($sale->due_amount) }}</td>
                        </tr>
                    @endif
                </table>
            </td>
        </tr>
    </table>

    @if ($sale->notes)
        <div class="note">
            <div class="label">Note</div>
            {{ $sale->notes }}
        </div>
    @endif

    <div class="foot">
        <table class="signs">
            <tr>
                <td class="slot"><div class="sign">Customer Signature</div></td>
                <td></td>
                <td class="slot"><div class="sign">Authorised Signature</div></td>
            </tr>
        </table>
        <div class="thanks">Thank you for your order</div>
    </div>
</body>
</html>
