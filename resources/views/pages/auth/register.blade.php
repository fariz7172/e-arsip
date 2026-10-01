<?php

use Livewire\Volt\Component;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

new #[\Livewire\Attributes\Layout('layouts.auth')] #[\Livewire\Attributes\Title('Daftar Akun — E-Arsip')] class extends Component {
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

<div class="auth-card" x-data="{ showPass: false, showConfirm: false }">
    <div style="margin-bottom: 24px; text-align: center;">
        <h2 style="font-size: 1.25rem; font-weight: 700; color: var(--text-main); margin-bottom: 4px;">Buat Akun Baru</h2>
        <p style="font-size: 0.85rem; color: var(--text-secondary);">Daftarkan akun operator untuk akses sistem</p>
    </div>

    <form wire:submit="register">
        <!-- Nama Lengkap -->
        <div class="form-group">
            <label class="form-label" for="name">Nama Lengkap</label>
            <div class="input-wrapper">
                <span class="input-icon">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/>
                        <circle cx="12" cy="7" r="4"/>
                    </svg>
                </span>
                <input 
                    id="name"
                    type="text" 
                    wire:model="name" 
                    class="form-input" 
                    placeholder="Nama Lengkap Anda" 
                    autocomplete="name"
                    required
                    autofocus>
            </div>
            @error('name') 
                <div class="form-error">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                    <span>{{ $message }}</span>
                </div> 
            @enderror
        </div>

        <!-- Email -->
        <div class="form-group">
            <label class="form-label" for="email">Alamat Email</label>
            <div class="input-wrapper">
                <span class="input-icon">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect width="20" height="16" x="2" y="4" rx="2"/>
                        <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/>
                    </svg>
                </span>
                <input 
                    id="email"
                    type="email" 
                    wire:model="email" 
                    class="form-input" 
                    placeholder="nama@email.com" 
                    autocomplete="email"
                    required>
            </div>
            @error('email') 
                <div class="form-error">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                    <span>{{ $message }}</span>
                </div> 
            @enderror
        </div>

        <!-- Password -->
        <div class="form-group">
            <label class="form-label" for="password">Password</label>
            <div class="input-wrapper">
                <span class="input-icon">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect width="18" height="11" x="3" y="11" rx="2" ry="2"/>
                        <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                    </svg>
                </span>
                <input 
                    id="password"
                    :type="showPass ? 'text' : 'password'" 
                    wire:model="password" 
                    class="form-input has-toggle" 
                    placeholder="Minimal 6 karakter" 
                    autocomplete="new-password"
                    required>
                <button 
                    type="button" 
                    class="password-toggle-btn" 
                    @click="showPass = !showPass"
                    :aria-label="showPass ? 'Sembunyikan password' : 'Lihat password'"
                    tabindex="-1">
                    <svg x-show="!showPass" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/>
                        <circle cx="12" cy="12" r="3"/>
                    </svg>
                    <svg x-show="showPass" x-cloak width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="m9.88 9.88 4.24 4.24"/>
                        <path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"/>
                        <path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"/>
                        <line x1="2" y1="2" x2="22" y2="22"/>
                    </svg>
                </button>
            </div>
            @error('password') 
                <div class="form-error">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                    <span>{{ $message }}</span>
                </div> 
            @enderror
        </div>

        <!-- Konfirmasi Password -->
        <div class="form-group">
            <label class="form-label" for="password_confirmation">Konfirmasi Password</label>
            <div class="input-wrapper">
                <span class="input-icon">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10"/>
                    </svg>
                </span>
                <input 
                    id="password_confirmation"
                    :type="showConfirm ? 'text' : 'password'" 
                    wire:model="password_confirmation" 
                    class="form-input has-toggle" 
                    placeholder="Ulangi password di atas" 
                    autocomplete="new-password"
                    required>
                <button 
                    type="button" 
                    class="password-toggle-btn" 
                    @click="showConfirm = !showConfirm"
                    :aria-label="showConfirm ? 'Sembunyikan konfirmasi password' : 'Lihat konfirmasi password'"
                    tabindex="-1">
                    <svg x-show="!showConfirm" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/>
                        <circle cx="12" cy="12" r="3"/>
                    </svg>
                    <svg x-show="showConfirm" x-cloak width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="m9.88 9.88 4.24 4.24"/>
                        <path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"/>
                        <path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"/>
                        <line x1="2" y1="2" x2="22" y2="22"/>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Submit Button -->
        <button type="submit" class="btn-primary" wire:loading.attr="disabled">
            <span wire:loading.remove style="display: inline-flex; align-items: center; gap: 8px;">
                <span>Daftar Akun</span>
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <path d="M5 12h14"/><path d="m12 5 7 7-7 7"/>
                </svg>
            </span>
            <span wire:loading style="display: inline-flex; align-items: center; gap: 8px;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="animation: spin 1s linear infinite;">
                    <circle cx="12" cy="12" r="10" stroke-opacity="0.25"/>
                    <path d="M12 2a10 10 0 0 1 10 10" stroke-linecap="round"/>
                </svg>
                <span>Mendaftarkan Akun...</span>
            </span>
        </button>
    </form>

    <div class="auth-footer">
        Sudah punya akun? <a href="/login">Masuk disini</a>
    </div>
</div>

<style>
    @keyframes spin {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }
</style>
