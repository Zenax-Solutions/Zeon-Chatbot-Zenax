<?php

namespace App\Filament\Resources;

use App\Models\Payment;
use App\Filament\Resources\PaymentResource\Pages;
use Filament\Forms;
use Filament\Tables;
use Filament\Resources\Resource;

class PaymentResource extends Resource
{
    protected static ?string $model = Payment::class;

    protected static ?string $navigationIcon = 'heroicon-o-receipt-refund';
    protected static ?string $navigationLabel = 'Payment History';
    protected static ?string $pluralLabel = 'Payments';
    protected static ?string $navigationGroup = 'Account';
    protected static ?string $label = 'Payment';

    public static function form(\Filament\Forms\Form $form): \Filament\Forms\Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('stripe_invoice_id')->disabled(),
                Forms\Components\TextInput::make('amount')->disabled(),
                Forms\Components\TextInput::make('currency')->disabled(),
                Forms\Components\TextInput::make('status')->disabled(),
                Forms\Components\DateTimePicker::make('paid_at')->disabled(),
                Forms\Components\TextInput::make('invoice_url')->disabled(),
            ]);
    }

    public static function table(\Filament\Tables\Table $table): \Filament\Tables\Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('stripe_invoice_id')->label('Invoice ID')->searchable(),
                Tables\Columns\TextColumn::make('amount')
                    ->label('Amount')
                    ->formatStateUsing(fn($state, $record) => strtoupper($record->currency) . ' ' . number_format($state / 100, 2)),
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'success' => 'paid',
                        'secondary' => ['open', 'draft'],
                        'danger' => ['uncollectible', 'void'],
                    ])
                    ->icons([
                        'heroicon-o-check-circle' => 'paid',
                        'heroicon-o-clock' => ['open', 'draft'],
                        'heroicon-o-x-circle' => ['uncollectible', 'void'],
                    ]),
                Tables\Columns\TextColumn::make('paid_at')->label('Paid At')->dateTime(),
            ])
            ->filters([])
            ->defaultSort('paid_at', 'desc')
            ->actions([
                Tables\Actions\Action::make('view_invoice')
                    ->label('View Invoice')
                    ->url(fn($record) => $record->invoice_url)
                    ->openUrlInNewTab()
                    ->icon('heroicon-o-link'),
            ]);
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()->where('user_id', auth()->id());
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPayments::route('/'),
        ];
    }
}
