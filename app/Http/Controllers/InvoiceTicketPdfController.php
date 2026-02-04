<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class InvoiceTicketPdfController extends Controller
{
    public function __invoke(Request $request, Invoice $invoice): Response
    {
        $invoice->load('items.product');

        $pdf = Pdf::loadView('invoices.ticket-pdf', [
            'invoice' => $invoice,
        ]);

        $paper = strtolower((string) $request->query('paper', 'a6'));

        if ($paper === '80mm' || $paper === '80') {
            // 80mm receipt width ≈ 226.77pt. Height is generous; Dompdf will paginate if needed.
            $pdf->setPaper([0, 0, 226.77, 1000], 'portrait');
        } else {
            // A6 is a good “medium” size for normal printers (not too big/small).
            $pdf->setPaper('a6', 'portrait');
        }

        return $pdf->stream('ticket-' . $invoice->invoice_number . '.pdf');
    }
}
