<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Product extends Model
{
    use HasFactory;
    // ijin kan semua kolom di isi secara massal
    protected $guarded = [];

    // format data saat di panggil
    protected $casts = [
        'is_available' => 'boolean',
    ];

    // sembunyikan kolom tertentu
    protected $hidden = ['image_path'];

    // sisipkan data baru pada objek produk
    protected $appends = ['image_url'];

    // format alamat gambar menjadi url
    public function ImageUrl(): Attribute{
        return Attribute::make(
            // get : fotmat data saat di panggil
            get: fn () => Storage::disk('public')->url($this->image_path),
            // set : fotmat data yang akan disimpan ke database
            // set:
        );
    }

    // ini sambungannya
    public function user():BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
