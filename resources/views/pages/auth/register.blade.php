<?php

use Livewire\Volt\Component;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

new #[\Livewire\Attributes\Layout('layouts.auth')] #[\Livewire\Attributes\Title('Daftar')] class extends Component {
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';

    public function register()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6|confirmed',
        ]);

        $user = User::create([
            'name' => $this->name,
            'email' => $this->email,
            'password' => Hash::make($this->password),
            'role' => 'operator',
        ]);

        Auth::login($user);

        return redirect('/dashboard');
    }
}; ?>
<div>
<div class="auth-card">
        <h2 style="font-size: 1.25rem; font-weight: 700; margin-bottom: 24px; text-align: center;">Buat Akun Baru</h2>

        <form wire:submit="register">
            <div class="form-group">
                <label class="form-label">Nama Lengkap</label>
                <input type="text" wire:model="name" class="form-input" placeholder="John Doe" autofocus>
                @error('name') <div class="form-error">{{ $message }}</div> @enderror
            </div>

            <div class="form-group">
                <label class="form-label">Email</label>
                <input type="email" wire:model="email" class="form-input" placeholder="nama@email.com">
                @error('email') <div class="form-error">{{ $message }}</div> @enderror
            </div>

            <div class="form-group">
                <label class="form-label">Password</label>
                <input type="password" wire:model="password" class="form-input" placeholder="Min. 6 karakter">
                @error('password') <div class="form-error">{{ $message }}</div> @enderror
            </div>

            <div class="form-group">
                <label class="form-label">Konfirmasi Password</label>
                <input type="password" wire:model="password_confirmation" class="form-input" placeholder="Ulangi password">
            </div>

            <button type="submit" class="btn-primary" wire:loading.attr="disabled">
                <span wire:loading.remove>Daftar</span>
                <span wire:loading>Memproses...</span>
            </button>
        </form>

        <div class="auth-footer">
            Sudah punya akun? <a href="/login">Masuk disini</a>
        </div>
    </div>


</div>
