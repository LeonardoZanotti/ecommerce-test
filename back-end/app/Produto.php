<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Compra;

class Produto extends Model
{
    protected $fillable = ['descricao', 'preco', 'peso', 'imagem'];

    public function compras()
    {
        return $this->belongsToMany(Compra::class)->withPivot('quantidade');
    }

}
