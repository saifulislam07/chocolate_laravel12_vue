<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ReturnRefund;
use Inertia\Inertia;
use Inertia\Response;

class ReturnRefundController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/Refunds/Index', [
            'refunds' => ReturnRefund::with(['salesReturn:id,return_number,order_id', 'customer:id,name,phone'])
                ->latest()
                ->get(),
        ]);
    }
}
