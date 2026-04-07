<?php

namespace App\Http\Controllers;

use App\Models\StockMovement;

class StockMovementController extends Controller
{
    public function index()
    {
        $stockMovements = StockMovement::with('product')->latest()->paginate(20);
        return view('stock-movements.index', compact('stockMovements'));
    }
}
