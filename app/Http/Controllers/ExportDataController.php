<?php

namespace App\Http\Controllers;

use App\Models\Diocese;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Exports\DiocesesExport;
use App\Exports\ParishesExport;
use Maatwebsite\Excel\Facades\Excel;

class ExportDataController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $data = $request->validate([
            'type' => 'required|string|in:dioceses,parishes',
            'diocese_id' => 'required_if:type,parishes|nullable|exists:dioceses,id',
        ]);

        if ($data['type'] === 'dioceses') {
            return Excel::download(new DiocesesExport, 'dioceses.xlsx');
        }

        $diocese = Diocese::findOrFail($data['diocese_id']);

        return Excel::download(
            new ParishesExport($diocese),
            strtolower(Str::kebab($diocese->name)."-{$diocese->type}-parishes.xlsx"),
        );
    }
}
