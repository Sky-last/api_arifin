<?php

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;

// Defaut endpoint API: http://api-arifin.test/api

// Product Controller
// menampung semua logika dan perintah yang diarahkan
// dari endpoint url disini (api.php)
use App\Http\Controllers\ProductController;

/*
API Resource untuk model Product
*/
// 1. ambil semua data porduck beserta pemiliknya (user)
// action url = [NameController::Class, 'method']
Route::get('/products/semuanya', [ProductController::class,'index']);

// Route Cari product berdasarkan nama
Route::get('/products/search', [ProductController::class, 'search']);

// route Tambah product baru
Route::post('/products', [ProductController::class, 'store']);

// route Lihat detail product berdasarkan ID
Route::get('/products/find', [ProductController::class, 'show']);

// route Update product
Route::put('/products/update', [ProductController::class, 'update']);

// route Hapus product
Route::delete('/products/delete', [ProductController::class, 'destroy']);


// route ambil semua data user
// Method Get
Route::get('/users',function(){
    // Panggil semua data user dan simpan dalam variabel $User
    $users = User::all();
    // Kembalikan data user dalam bentuk JSON
    $json_users = json_encode($users);
    // berikan data (response) json ke apliasi yang meminta(request)
    return $json_users;
});

// route cari user berdasarkan id
// Method Get
Route::get('/user/find', function(Request $request){
    // cari user
    $user = User::find($request->id);
    return json_encode($user);
});

// route cari user berdasarkan kemiripan nama atau email
// Method Get
Route::get('/user/search', function(Request $request){
    // cari user berdasarkan string nama
    $users = User::where('name', 'like', '%'.$request->nama.'%')
    ->orWhere('email', 'like', '%'.$request->nama.'%')->get();
    // SELECT * FROM users WHERE name OR email LIKE '%ahmad%';
    return json_encode($users);
});

// registrasi user
// parameter nama, email, phone, password
// password harus di hash sebelum disimpan ke table


Route::post('/register', function (Request $r) {
   try {
     // validasi data
    $validate = $r->validate([
        // parameter = > rules
        'nama' => 'required|max:255',
        'surel' => 'required|email|unique:users,email',
        'sandi' => 'required|min:6',
        'telp' => 'required|unique:users,phone'
    ]);
        // tambahkan data userbaru
        $new_user = User::query()->create([
            // field => parameter
            'name' => $r->nama,
            'email' => $r->surel,
            'password' => Hash::make($r->sandi),
            'phone' => $r->telp

        ]);
        return response()->json($new_user);
   } catch (ValidationException $e){
    return $e->validator->errors();
   }

});

// ubah data user
// parameter nama, surel, telp, sandi
// method 'PUT' atau 'PATCH'
// data user yang akan di ubah di cari berdasarkan id yang dikirim
// pada comtoh ini, id akan langsung diasosiasikan ke model user
Route::put('/user/edit/{user}', function (Request $r, User $user){
    // validasi ubah data
    try{
        // code...
     $validate = $r->validate([
        // parameter = > rules
        'nama' => 'max:255',
        'surel' => 'email|unique:users,email,'.$user->id,
        'sandi' => 'min:6',
        'telp' => 'unique:users,phone,'.$user->id
    ]);

    // ------ cara yang sederhana
    // $user->update([
    //     'name' => $r->nama ?? $user->name,
    //     'email' => $r->surel ?? $user->email,
    //     'password' => $r->sandi ? Hash::make($r->sandi):$user->password,
    //     'phone' => $r->telp ?? $user->phone
    // ]);

    // ------ cara yang kompleks
    // salin data yang diterima ke variabel baru
    $data = $r->all();
    //jika ada data password pada array $data

    if (array_key_exists('sandi', $data)) {
        // replace isi 'sandi' dengan hasil Hash 'sandi'
        $data['sandi'] = Hash::make($data['sandi']);
    }
    // ubah data user
    $user->update([
        'name' => $data['nama'] ?? $user->name,
        'email' => $data['surel'] ?? $user->email,
        'password' => $data['sandi'] ?? $user->password,
        'phone' => $data['telp'] ?? $user->phone
    ]);
    // ------ berakhir lah cara yang kompleks
    // kembalikan data user yang sudah diubah berserta pesan sukses
    return response()->json([
        'pesan' => 'Sukses diubah!', 'user' => $user
    ]);

    }catch (ValidationException $e){
    return $e->validator->errors();
   }

});

// Hapus data user
// method 'DELETE'
// request dilakukan dengan menyertakan id user
Route::delete('/user/delete', function (Request $r){
    // temukan user berdasarkan id yang dikirim
    $user = User::find($r->id);
    // respon jika user tidak ditemukan
    if (! $user) {
      return response()->json([
        'pesan' => 'Gagal! user tidak ditemukan.'
      ]);
    }

    // hapus data user
    $user->delete();
    return response()->json([
        'pesan' => 'Sukses! User berhasil dihapus'
    ]);
});

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');
