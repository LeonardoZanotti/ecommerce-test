<?php

use Illuminate\Database\Seeder;
use App\User;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::create([
            'name' => 'Admin',
            'lastName' => 'Admin',
            'cpf' => '123.456.789-10',
            'telefone' => '+55 27 12345-6789',
            'email' => 'admin@ecomp.co',
            'codigo' => 'WPEOWPEawoekqO20392017woqaWUWJszxcvbNCZXAVZasiWP',
            'password' => bcrypt('secret'),
            'admin' => true
        ]);
    }
}
