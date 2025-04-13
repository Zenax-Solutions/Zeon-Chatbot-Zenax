<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ChatSessionResource\Pages;
use App\Filament\Resources\ChatSessionResource\RelationManagers;
use App\Models\ChatSession;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ChatSessionResource extends Resource
{
    protected static ?string $model = ChatSession::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?int $navigationSort = 100;
    protected static ?string $navigationGroup = 'ChatBot';
    protected static ?string $label = 'Chat Sessions';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable()->label('Session ID'),
                Tables\Columns\TextColumn::make('chatBot.website_name')->label('ChatBot')->sortable(),
                Tables\Columns\TextColumn::make('title')->limit(30),
                Tables\Columns\TextColumn::make('guest_ip')->label('Guest IP')->limit(30),
                Tables\Columns\TextColumn::make('messages_count')->counts('messages')->label('Messages'),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
                \Filament\Tables\Columns\BadgeColumn::make('lead_status')
                    ->label('Lead Status')
                    ->colors([
                        'success' => 'Positive',
                        'secondary' => 'NotPositive',
                        'warning' => 'Unknown',
                    ])
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('user_id')
                    ->label('User')
                    ->relationship('user', 'id'),
                Tables\Filters\SelectFilter::make('chat_bot_id')
                    ->label('ChatBot')
                    ->relationship('chatBot', 'id'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListChatSessions::route('/'),
            'edit' => Pages\EditChatSession::route('/{record}/edit'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }



    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = \Illuminate\Support\Facades\Auth::user();
        if ($user) {
            return $query->where('user_id', $user->id)->orWhereNull('user_id');
        }
        return $query;
    }
}
