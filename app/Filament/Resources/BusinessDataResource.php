<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BusinessDataResource\Pages;
use App\Filament\Resources\BusinessDataResource\RelationManagers;
use App\Models\BusinessData;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BusinessDataResource extends Resource
{
    protected static ?string $model = BusinessData::class;

    protected static ?string $navigationIcon = 'heroicon-o-briefcase';
    protected static ?int $navigationSort = 2;
    protected static ?string $navigationGroup = 'ChatBot';
    protected static ?string $label = 'Business Data';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Business Information')
                    ->description('Enter the business details below.')
                    ->schema([
                        Forms\Components\Textarea::make('content')
                            ->label('Business Content')
                            ->rows(6)
                            ->placeholder('Enter business description, details, or notes...')
                            ->helperText('This information will be used to provide context to the chatbot.')
                            ->required(),
                    ])->visible(fn() => \App\Models\ChatBot::where('user_id', auth()->user()?->id)->exists())

                    ->columns(1),

                Forms\Components\Section::make('Assign to ChatBots')
                    ->description('Link this business data to one or more of your chatbots.')
                    ->schema([
                        Forms\Components\MultiSelect::make('chatBots')
                            ->label('Select ChatBots')
                            ->relationship('chatBots', 'website_name')
                            ->options(fn() => \App\Models\ChatBot::where('user_id', auth()->user()?->id)->pluck('website_name', 'id'))
                            ->placeholder('Choose chatbots...')
                            ->required()
                            ->visible(fn() => \App\Models\ChatBot::where('user_id', auth()->user()?->id)->exists())
                            ->columnSpanFull(),

                        Forms\Components\Placeholder::make('no_chatbots')
                            ->content('No chatbots found. Please create one to assign business data.')
                            ->extraAttributes(['class' => 'text-center text-lg font-semibold p-4 bg-yellow-50 border border-yellow-300 rounded'])
                            ->visible(fn() => !\App\Models\ChatBot::where('user_id', auth()->user()?->id)->exists())
                            ->hintAction(
                                \Filament\Forms\Components\Actions\Action::make('createChatBot')
                                    ->label('Add a ChatBot')
                                    ->url('/admin/chat-bots/create')
                                    ->button()
                                    ->color('primary')
                            ),
                    ])
                    ->columns(1)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('content')
                    ->label('Content')
                    ->limit(50),

                Tables\Columns\TextColumn::make('chatBots.website_name')
                    ->label('ChatBots')
                    ->badge()
                    ->separator(', '),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('chat_bot_id')
                    ->label('ChatBot')
                    ->options(fn() => \App\Models\ChatBot::where('user_id', auth()->user()?->id)->pluck('website_name', 'id'))
                    ->query(function (Builder $query, array $data) {
                        if (filled($data['value'] ?? null)) {
                            $query->whereHas('chatBots', function ($q) use ($data) {
                                $q->where('id', $data['value']);
                            });
                        }
                    }),
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
            'index' => Pages\ListBusinessData::route('/'),
            'create' => Pages\CreateBusinessData::route('/create'),
            'edit' => Pages\EditBusinessData::route('/{record}/edit'),
        ];
    }
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        return $query->where(function ($q) {
            $q->where('user_id', auth()->user()?->id)
                ->orWhereHas('chatBots', function ($q2) {
                    $q2->where('user_id', auth()->user()?->id);
                });
        });
    }
    public static function canViewAny(): bool
    {
        return true;
    }
}
