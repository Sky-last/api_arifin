<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ProductController extends Controller
{
    /**
     * Tampilkan semua data product Beserta pemiliknya/user.
     */
    public function index()
    {
        $products = Product::query()
            ->where('is_available', true)
            ->with('user')
            ->get();

        return response()->json([
            'status' => 'Sukses',
            'data' => $products
        ]);
    }

    /**
     * Cari product berdasarkan nama.
     */
    public function search(Request $req)
    {
        try {
            $validated = $req->validate([
                'teks' => 'required|min:3'
            ], [
                'teks.required' => 'Attribute jangan di kosongkah lah Bos!',
                'teks.min' => 'Ini kurang dari :min Bos!',
            ], [
                'teks' => 'Huruf'
            ]);

            $products = Product::query()
                ->where('name', 'like', '%' . $req->teks . '%')
                ->with('user')
                ->get();

            return response()->json([
                'status' => 'Sukses',
                'data' => $products,
            ]);
        } catch (ValidationException $ex) {
            return response()->json([
                'status' => 'Gagal',
                'errors' => $ex->errors(),
            ]);
        }
    }

    /**
     * Simpan product baru ke database.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'user_id' => 'required|exists:users,id',
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'price' => 'required|numeric|min:0',
                'stock' => 'required|integer|min:0',
                'image_path' => 'nullable|string|max:255',
                'is_available' => 'boolean',
            ], [
                'user_id.required' => 'User ID wajib diisi!',
                'user_id.exists' => 'User tidak ditemukan!',
                'name.required' => 'Nama product wajib diisi!',
                'price.required' => 'Harga product wajib diisi!',
                'price.numeric' => 'Harga harus berupa angka!',
                'stock.required' => 'Stok product wajib diisi!',
                'stock.integer' => 'Stok harus berupa angka bulat!',
            ]);

            $validated['is_available'] = $request->is_available ?? true;

            $product = Product::create($validated);

            return response()->json([
                'status' => 'Sukses',
                'message' => 'Product berhasil ditambahkan!',
                'data' => $product->load('user')
            ]);

        } catch (ValidationException $ex) {
            return response()->json([
                'status' => 'Gagal',
                'errors' => $ex->errors(),
            ]);
        }
    }

    /**
     * Tampilkan detail product berdasarkan ID.
     * ID dikirim via query parameter: ?id=1
     */
    public function show(Request $request)
    {
        $product = Product::find($request->id);

        if (!$product) {
            return response()->json([
                'status' => 'Gagal',
                'message' => 'Product tidak ditemukan!'
            ]);
        }

        return response()->json([
            'status' => 'Sukses',
            'data' => $product->load('user')
        ]);
    }

    /**
     * Update data product yang sudah ada.
     * ID dikirim via query parameter: ?id=1
     */
    public function update(Request $request)
    {
        try {
            $product = Product::find($request->id);

            if (!$product) {
                return response()->json([
                    'status' => 'Gagal',
                    'message' => 'Product tidak ditemukan!'
                ]);
            }

            $validated = $request->validate([
                'name' => 'nullable|string|max:255',
                'description' => 'nullable|string',
                'price' => 'nullable|numeric|min:0',
                'stock' => 'nullable|integer|min:0',
                'image_path' => 'nullable|string|max:255',
                'is_available' => 'boolean',
            ]);

            // Hapus field yang null agar tidak mengupdate ke null
            $validated = array_filter($validated, function($value) {
                return $value !== null;
            });

            $product->update($validated);

            return response()->json([
                'status' => 'Sukses',
                'message' => 'Product berhasil diupdate!',
                'data' => $product->load('user')
            ]);

        } catch (ValidationException $ex) {
            return response()->json([
                'status' => 'Gagal',
                'errors' => $ex->errors(),
            ]);
        }
    }

    /**
     * Hapus product dari database.
     * ID dikirim via query parameter: ?id=1
     */
    public function destroy(Request $request)
    {
        $product = Product::find($request->id);

        if (!$product) {
            return response()->json([
                'status' => 'Gagal',
                'message' => 'Product tidak ditemukan!'
            ]);
        }

        $product->delete();

        return response()->json([
            'status' => 'Sukses',
            'message' => 'Product berhasil dihapus!'
        ]);
    }
}
