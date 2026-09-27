<?php

use Livewire\Volt\Component;
use Illuminate\Support\Facades\Auth;

new #[\Livewire\Attributes\Layout('layouts.auth')] #[\Livewire\Attributes\Title('Login')] class extends Component {
    public string $email = '';
    public string $password = '';

    public function login()
    {
        $this->validate([
            'email' => 'required|email',
            'password' => 'required|min:6',
        ]);

        if (Auth::attempt(['email' => $this->email, 'password' => $this->password])) {
            session()->regenerate();
            return redirect()->intended('/dashboard');
        }

        $this->addError('email', 'Email atau password salah.');
    }
}; ?>
<div>
<div class="auth-card">
        <h2 style="font-size: 1.25rem; font-weight: 700; margin-bottom: 24px; text-align: center;">Masuk ke Akun Anda</h2>

        <form wire:submit="login">
            <div class="form-group">
                <label class="form-label">Email</label>
                <input type="email" wire:model="email" class="form-input" placeholder="nama@email.com" autofocus>
                @error('email') <div class="form-error">{{ $message }}</div> @enderror
            </div>

            <div class="form-group">
                <label class="form-label">Password</label>
                <input type="password" wire:model="password" class="form-input" placeholder="••••••••">
                @error('password') <div class="form-error">{{ $message }}</div> @enderror
            </div>

            <button type="submit" class="btn-primary" wire:loading.attr="disabled">
                <span wire:loading.remove>Masuk</span>
                <span wire:loading>Memproses...</span>
            </button>
        </form>

        <div class="auth-footer">
            Belum punya akun? <a href="/register">Daftar disini</a>
        </div>
    </div>


</div>
