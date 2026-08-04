<?php

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

new class extends Component
{
    public string $email = '';

    public string $password = '';

    public function mount(): void
    {
        if (Auth::check()) {
            $this->redirect(route('admin.countries'), navigate: true);
        }
    }

    public function authenticate(): void
    {
        $this->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (! Auth::attempt(['email' => $this->email, 'password' => $this->password])) {
            $this->addError('email', 'Identifiants incorrects.');

            return;
        }

        session()->regenerate();
        $this->redirect(route('admin.countries'), navigate: true);
    }
};
?>

<div class="bg-white rounded-xl shadow-sm border border-slate-200 p-8">
    <div class="mb-6">
        <h2 class="text-xl font-bold text-slate-900">Connexion</h2>
        <p class="text-sm text-slate-500 mt-1">Accès réservé aux administrateurs</p>
    </div>

    <form wire:submit="authenticate" class="space-y-4">
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1.5">Email</label>
            <input
                type="email"
                wire:model="email"
                autocomplete="email"
                autofocus
                class="w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
            >
            @error('email')
                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1.5">Mot de passe</label>
            <input
                type="password"
                wire:model="password"
                autocomplete="current-password"
                class="w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
            >
            @error('password')
                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
            @enderror
        </div>

        <button
            type="submit"
            class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2.5 px-4 rounded-lg text-sm transition-colors"
            wire:loading.attr="disabled"
        >
            <span wire:loading.remove>Se connecter</span>
            <span wire:loading class="opacity-70">Connexion…</span>
        </button>
    </form>
</div>
