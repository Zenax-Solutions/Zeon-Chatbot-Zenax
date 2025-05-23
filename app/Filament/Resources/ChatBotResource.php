<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ChatBotResource\Pages;
use App\Filament\Resources\ChatBotResource\RelationManagers;
use App\Models\ChatBot;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Set;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Columns\IconColumn;

class ChatBotResource extends Resource
{
    protected static ?string $model = ChatBot::class;

    protected static ?string $navigationIcon = 'heroicon-o-cube-transparent';
    protected static ?int $navigationSort = 1;
    protected static ?string $navigationGroup = 'ChatBot';


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Grid::make()
                    ->columns(2)
                    ->schema([
                        // Column 1: Website/chatbot data
                        Forms\Components\Section::make('Website Data')
                            ->schema([
                                Forms\Components\TextInput::make('website_name')
                                    ->label('Website Name')
                                    ->unique(ignoreRecord: true)
                                    ->required(),

                                Forms\Components\TextInput::make('website_url')
                                    ->label('Website URL')
                                    ->unique(ignoreRecord: true)
                                    ->url()
                                    ->required()
                                    ->live(onBlur: true)
                                    ->formatStateUsing(function (?string $state) {
                                        if (!$state) return null;

                                        $state = rtrim($state, '/');                        // remove trailing slash

                                        return $state;
                                    })
                                    ->afterStateUpdated(function (Set $set, ?string $state) {
                                        if (!$state) return;

                                        $cleanUrl = rtrim($state, '/');

                                        $set('website_url', $cleanUrl); // update the input with cleaned URL

                                        $set('widget_code', '<script src="' . env('APP_URL') . '/chatbot.js?website=' . $cleanUrl . '&user=' . auth()->user()?->uuid . '"></script>');
                                    }),
                                Forms\Components\Textarea::make('widget_code')
                                    ->label('Chat Widget Code')
                                    ->rows(5)
                                    ->readOnly(true)
                                    ->placeholder('Widget code will be generated automatically based on the website URL.')
                                    ->helperText('This code is generated automatically.'),

                                Forms\Components\Toggle::make('status')
                                    ->label('Active')
                                    ->default(true),
                            ]),

                        // Column 2: WhatsApp integration data
                        Forms\Components\Section::make('WhatsApp Integration')
                            ->description('Manage WhatsApp Business API credentials for this chatbot')
                            ->collapsible()
                            ->collapsed()
                            ->schema([
                                Forms\Components\TextInput::make('whatsapp_token')
                                    ->label('WhatsApp API Token')
                                    ->placeholder('WhatsApp API Token')
                                    ->helperText('This is the token used to authenticate requests to the WhatsApp Business API.')
                                    ->password(),
                                Forms\Components\TextInput::make('whatsapp_phone_number_id')
                                    ->placeholder('WhatsApp phone number ID')
                                    ->helperText('This is the ID of the WhatsApp phone number associated with your WhatsApp Business API account.')
                                    ->label('Phone Number ID'),
                                Forms\Components\TextInput::make('whatsapp_verify_token')
                                    ->placeholder('WhatsApp verify token, used to verify the webhook | Ex : my_secret_token')
                                    ->helperText('This token is used to verify the webhook URL with WhatsApp. It should be a random string.')
                                    ->unique(ignoreRecord: true)
                                    ->label('Verify Token'),
                                Forms\Components\TextInput::make('webhook_url')
                                    ->url()
                                    ->readOnly()
                                    ->placeholder('Webhook URL will be generated automatically')
                                    ->helperText('This URL is used to receive incoming messages and notifications from WhatsApp.')
                                    ->label('Webhook URL'),
                            ])->relationship(
                                'whatsappIntegration',
                                'whatsapp_token',
                                'whatsapp_phone_number_id',
                                'whatsapp_verify_token',
                                'webhook_url',
                            ),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('website_name')
                    ->label('Website Name'),

                Tables\Columns\TextColumn::make('website_url')
                    ->label('Website URL')
                    ->limit(30),

                Tables\Columns\TextColumn::make('widget_code')
                    ->label('Copy Widget Code')
                    ->formatStateUsing(fn() => 'Copy Widget Code')
                    ->color('primary')
                    ->copyable(fn($record) => $record->widget_code)
                    ->icon('heroicon-o-document-text')
                    ->size('md')
                    ->tooltip('Copy widget code'),


                Tables\Columns\IconColumn::make('status')
                    ->label('Active')
                    ->boolean(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\Action::make('visit')
                    ->label('Visit')
                    ->url(fn($record) => $record->website_url)
                    ->openUrlInNewTab()
                    ->icon('heroicon-o-link'),

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
            \App\Filament\Resources\ChatBotResource\RelationManagers\BusinessDataRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListChatBots::route('/'),
            'create' => Pages\CreateChatBot::route('/create'),
            'edit' => Pages\EditChatBot::route('/{record}/edit'),
        ];
    }
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('user_id', auth()->user()?->id);
    }
}
