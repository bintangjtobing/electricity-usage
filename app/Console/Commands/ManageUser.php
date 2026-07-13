<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Validator;

class ManageUser extends Command
{
    protected $signature = 'app:user';

    protected $description = 'Buat akun baru atau ganti password akun yang sudah ada';

    public function handle(): int
    {
        $name = $this->ask('Nama');
        $email = $this->ask('Email');

        // Password diketik langsung oleh pemilik akun dan tidak ditampilkan.
        $password = $this->secret('Password');
        $confirm = $this->secret('Ulangi password');

        if ($password !== $confirm) {
            $this->error('Password tidak sama.');
            return self::FAILURE;
        }

        $validator = Validator::make(
            compact('name', 'email', 'password'),
            [
                'name' => 'required|string|max:255',
                'email' => 'required|email',
                'password' => ['required', Password::min(8)],
            ]
        );

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        $user = User::updateOrCreate(
            ['email' => $email],
            ['name' => $name, 'password' => Hash::make($password)]
        );

        $this->info(
            $user->wasRecentlyCreated
                ? "Akun {$email} berhasil dibuat."
                : "Password untuk {$email} berhasil diperbarui."
        );

        return self::SUCCESS;
    }
}
