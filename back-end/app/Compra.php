<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\User;
use App\Produto;

class Compra extends Model
{
    protected $fillable = ['codigo', 'preco', 'status', 'autorizacao'];

    protected $with = ['produtos'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function produtos()
    {
        return $this->belongsToMany(Produto::class)->withPivot('quantidade');
    }
}
