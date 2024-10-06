<?php

namespace App\Http\Controllers;

use App\Models\Sewa;
use Illuminate\Http\Request;

class SewaController extends Controller
{
    public function index()
    {
        return Sewa::all();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'kmg_floor' => ['required'],
            'kmg_unit' => ['required'],
            'kmg_periode' => ['required'],
            'kmg_price' => ['required'],
            'kmg_check_in' => ['nullable'],
            'kmg_agent' => ['required'],
            'kmg_keterangan' => ['nullable'],
        ]);

        $data['kmg_check_in'] = now();

        return Sewa::create($data);
    }

    public function show(Sewa $sewa)
    {
        return $sewa;
    }

    public function update(Request $request, $sewa)
    {
        $data = $request->validate([
            'kmg_floor' => ['required'],
            'kmg_unit' => ['required'],
            'kmg_periode' => ['required'],
            'kmg_price' => ['required'],
            'kmg_agent' => ['required'],
            'kmg_keterangan' => ['nullable'],
        ]);

        Sewa::find($sewa)->update($data);

        return $sewa;
    }

    public function destroy($sewa)
    {
        Sewa::find($sewa)->delete();

        return response()->json();
    }
}
