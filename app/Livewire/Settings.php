<?php

namespace App\Livewire;

use Livewire\Component;

class Settings extends Component
{
    public string $name = '';

    public string $email = '';

    public bool $notifEmail = true;

    public bool $notifPush = true;

    public function mount(): void
    {
        $this->name = auth()->user()->name;
        $this->email = auth()->user()->email;
    }

    public function updateProfile(): void
    {
        auth()->user()->update([
            'name' => $this->name,
            'email' => $this->email,
        ]);

        session()->flash('saved', 'Perubahan tersimpan.');
    }

    public function deactivatePremium(): void
    {
        auth()->user()->deactivatePremium();

        session()->flash('saved', 'Akunmu kembali ke Free. Data yang sudah tersimpan tetap aman.');
    }

    public function deleteAccount(): void
    {
        auth()->user()->delete();
        auth()->guard('web')->logout();
        $this->redirect('/', navigate: true);
    }

    public function render()
    {
        return view('livewire.settings');
    }
}
