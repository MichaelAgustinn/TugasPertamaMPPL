<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BarangController extends Controller
{
    public function dashboard()
    {
        $totalBarang   = Barang::count();
        $totalKategori = Barang::distinct('kategori')->count('kategori');
        $stokHabis     = Barang::where('stok', '<=', 5)->count();
        $barangHariIni = Barang::whereDate('created_at', now())->count();

        $barangs = Barang::latest()->paginate(10);

        return view('dashboard', compact(
            'totalBarang',
            'totalKategori',
            'stokHabis',
            'barangHariIni',
            'barangs'
        ));
    }

    public function index()
    {
        $barangs = Barang::latest()->paginate(10);
        return view('barang.index', compact('barangs'));
    }

    public function create()
    {
        return view('barang.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kode_barang' => 'required|unique:barangs,kode_barang',
            'nama_barang' => 'required|string|max:255',
            'kategori'    => 'required|string|max:255',
            'satuan'      => 'required|string|max:50',
            'stok'        => 'required|integer',
            'harga'       => 'required|numeric',
            'gambar'      => 'nullable|image|max:2048',
        ]);

        // Upload gambar (jika ada)
        if ($request->hasFile('gambar')) {
            $validated['gambar'] = $request->file('gambar')->store('gambar', 'public');
        }

        Barang::create($validated);

        return redirect()
            ->route('barang.index')
            ->with('success', 'Barang berhasil ditambahkan.');
    }

    public function edit(Barang $barang)
    {
        return view('barang.edit', compact('barang'));
    }

    public function update(Request $request, Barang $barang)
    {
        $validated = $request->validate([
            'kode_barang' => 'required|unique:barangs,kode_barang,' . $barang->id,
            'nama_barang' => 'required|string|max:255',
            'kategori'    => 'required|string|max:255',
            'satuan'      => 'required|string|max:50',
            'stok'        => 'required|integer',
            'harga'       => 'required|numeric',
            'gambar'      => 'nullable|image|max:2048',
        ]);

        // Jika upload gambar baru
        if ($request->hasFile('gambar')) {
            // Hapus gambar lama
            if ($barang->gambar) {
                Storage::disk('public')->delete($barang->gambar);
            }

            $validated['gambar'] = $request->file('gambar')->store('gambar', 'public');
        }

        $barang->update($validated);

        return redirect()
            ->route('barang.index')
            ->with('success', 'Barang berhasil diperbarui.');
    }

    public function destroy(Barang $barang)
    {
        if ($barang->gambar) {
            Storage::disk('public')->delete($barang->gambar);
        }

        $barang->delete();

        return redirect()
            ->route('barang.index')
            ->with('success', 'Barang berhasil dihapus.');
    }
}
