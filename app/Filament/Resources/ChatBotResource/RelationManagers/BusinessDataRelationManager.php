<?php

namespace App\Filament\Resources\ChatBotResource\RelationManagers;

use Filament\Forms;
use Filament\Tables;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;
use Filament\Forms\Form;

class BusinessDataRelationManager extends RelationManager
{
    protected static string $relationship = 'businessData';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Textarea::make('content')
                    ->label('Content')
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('content')
                    ->label('Content')
                    ->limit(50),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }
}
