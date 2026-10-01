<?php

use Livewire\Volt\Component;
use Illuminate\Support\Facades\Auth;

new #[\Livewire\Attributes\Layout('layouts.auth')] #[\Livewire\Attributes\Title('Login — E-Arsip')] class extends Component {
    public string $email = '';
    public string $password = '';
    public bool $remember = false;

    public function login()
    {
        $this->validate([
            'email' => 'required|email',
            'password' => 'required|min:6',
        ]);

        if (Auth::attempt(['email' => $this->email, 'password' => $this->password], $this->remember)) {
            session()->regenerate();
            return redirect()->intended('/dashboard');
        }

        $this->addError('email', 'Email atau password yang Anda masukkan salah.');
    }
}; ?>

<div class="auth-card" x-data="{ showPassword: false }">
    <div style="margin-bottom: 24px; text-align: center;">
        <h2 style="font-size: 1.25rem; font-weight: 700; color: var(--text-main); margin-bottom: 4px;">Masuk ke Akun Anda</h2>
        <p style="font-size: 0.85rem; color: var(--text-secondary);">Silakan masukkan kredensial untuk melanjutkan</p>
    </div>

    @if (session('status'))
        <div style="background: rgba(16, 185, 129, 0.12); border: 1px solid rgba(16, 185, 129, 0.3); color: #34d399; padding: 10px 14px; border-radius: var(--radius-md); font-size: 0.825rem; margin-bottom: 18px;">
            {{ session('status') }}
        </div>
    @endif

    <form wire:submit="login">
        <!-- Email Input -->
        <div class="form-group">
            <label class="form-label" for="email">
                <span>Alamat Email</span>
            </label>
            <div class="input-wrapper">
                <span class="input-icon">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
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
                    required
                    autofocus>
            </div>
            @error('email') 
                <div class="form-error">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                    <span>{{ $message }}</span>
                </div> 
            @enderror
        </div>

        <!-- Password Input -->
        <div class="form-group">
            <label class="form-label" for="password">
                <span>Password</span>
            </label>
            <div class="input-wrapper">
                <span class="input-icon">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect width="18" height="11" x="3" y="11" rx="2" ry="2"/>
                        <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                    </svg>
                </span>
                <input 
                    id="password"
                    :type="showPassword ? 'text' : 'password'" 
                    wire:model="password" 
                    class="form-input has-toggle" 
                    placeholder="••••••••" 
                    autocomplete="current-password"
                    required>
                <button 
                    type="button" 
                    class="password-toggle-btn" 
                    @click="showPassword = !showPassword"
                    :aria-label="showPassword ? 'Sembunyikan password' : 'Lihat password'"
                    tabindex="-1">
                    <svg x-show="!showPassword" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/>
                        <circle cx="12" cy="12" r="3"/>
                    </svg>
                    <svg x-show="showPassword" x-cloak width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
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

        <!-- Remember Me Checkbox -->
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 22px; font-size: 0.85rem;">
            <label style="display: inline-flex; align-items: center; gap: 8px; cursor: pointer; color: var(--text-secondary); user-select: none;">
                <input 
                    type="checkbox" 
                    wire:model="remember" 
                    style="accent-color: var(--accent-primary); width: 16px; height: 16px; cursor: pointer; border-radius: 4px;">
                <span>Ingat saya</span>
            </label>
        </div>

        <!-- Submit Button -->
        <button type="submit" class="btn-primary" wire:loading.attr="disabled">
            <span wire:loading.remove style="display: inline-flex; align-items: center; gap: 8px;">
                <span>Masuk Sekarang</span>
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M5 12h14"/><path d="m12 5 7 7-7 7"/>
                </svg>
            </span>
            <span wire:loading style="display: inline-flex; align-items: center; gap: 8px;">
                <svg class="animate-spin" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="animation: spin 1s linear infinite;">
                    <circle cx="12" cy="12" r="10" stroke-opacity="0.25"/>
                    <path d="M12 2a10 10 0 0 1 10 10" stroke-linecap="round"/>
                </svg>
                <span>Memproses Masuk...</span>
            </span>
        </button>
    </form>

    <div class="auth-footer">
        Belum punya akun? <a href="/register">Daftar akun disini</a>
    </div>
</div>

<style>
    @keyframes spin {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }
</style>
