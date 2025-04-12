<?php

namespace App\Filament\Pages;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Section;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Support\Exceptions\Halt;

class UserSettings extends Page implements HasForms
{

    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-cog';
    protected static string $view = 'filament.pages.user-settings';
    protected static ?string $title = 'My Settings';
    protected static ?string $navigationGroup = 'Settings';
    protected static ?int $navigationSort = 99;

    public ?array $data = [];


    public function mount(): void
    {
        $this->form->fill([
            'name' => Auth::user()->name,
            'email' => Auth::user()->email,
            'phone' => Auth::user()->phone,
            'uuid' => Auth::user()->uuid,
        ]);
    }

    public function form(Form $form): Form
    {
        $user = Auth::user();

        return $form
            ->schema([
                Section::make('Profile Information')
                    ->schema([
                        TextInput::make('name')
                            ->label('Name')
                            ->required()
                            ->disabled()
                            ->maxLength(255),

                        TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->required()
                            ->disabled()
                            ->maxLength(255),

                        TextInput::make('uuid')
                            ->label('Your Unique UUID')
                            ->disabled(),

                        TextInput::make('phone')
                            ->label('Phone Number (E.164 format)')
                            ->maxLength(32)
                            ->unique(),


                    ]),

                Section::make('Change Password')
                    ->description('Leave blank if you do not want to change your password.')
                    ->schema([
                        TextInput::make('password')
                            ->label('New Password')
                            ->password()
                            ->minLength(8)
                            ->same('password_confirmation')
                            ->dehydrateStateUsing(fn($state) => $state ? Hash::make($state) : null)
                            ->nullable(),
                        TextInput::make('password_confirmation')
                            ->label('Confirm New Password')
                            ->password()
                            ->nullable(),
                    ]),
            ])
            ->statePath('data');
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label(__('filament-panels::resources/pages/edit-record.form.actions.save.label'))
                ->submit('save'),
        ];
    }

    public function save(): void
    {
        $user = Auth::user();
        $data = $this->form->getState();
        $user->phone = $data['phone'];

        if (!empty($data['password'])) {
            $user->password = $data['password'];
        }

        $user->save();

        Notification::make()
            ->title('Settings updated successfully')
            ->success()
            ->send();
    }
}
