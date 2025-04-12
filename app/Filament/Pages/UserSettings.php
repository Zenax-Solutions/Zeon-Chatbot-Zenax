<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserSettings extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-cog';
    protected static string $view = 'filament.pages.user-settings';
    protected static ?string $title = 'My Settings';
    protected static ?string $navigationGroup = 'Settings';
    protected static ?int $navigationSort = 99;

    public static function shouldRegisterNavigation(): bool
    {
        return true;
    }

    public $name;
    public $email;
    public $uuid;
    public $password = '';
    public $password_confirmation = '';

    public function mount(): void
    {
        $user = Auth::user();
        $this->name = $user->name;
        $this->email = $user->email;
        $this->uuid = $user->uuid;
    }

    public function save()
    {
        $user = Auth::user();

        $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        $user->name = $this->name;
        $user->email = $this->email;

        if ($this->password) {
            $user->password = Hash::make($this->password);
        }

        $user->save();

        session()->flash('success', 'Settings updated successfully.');
    }
}
