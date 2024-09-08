<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ContactResource\Pages;
use App\Filament\Resources\ContactResource\RelationManagers;
use App\Models\Contact;
use Filament\Actions\ViewAction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\Split;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class ContactResource extends Resource
{
    protected static ?string $model = Contact::class;

    protected static ?string $navigationIcon = 'heroicon-o-identification';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('email')
                    ->unique(Contact::class, 'email', ignoreRecord: true)
                    ->email()
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('phone')
                    ->unique(Contact::class, 'phone', ignoreRecord: true)
                    ->tel()
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('address')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('type')
                    ->label('Contact type')
                    ->options([
                        'customer' => 'Customer',
                        'supplier' => 'Supplier',
                    ])
                    ->native(false)
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('phone')
                    ->searchable(),
                Tables\Columns\TextColumn::make('address')
                    ->searchable(),
                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'customer' => 'success',
                        'supplier' => 'danger',
                    }),
                Tables\Columns\TextColumn::make('creator.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('updater.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordUrl(
                fn (Model $record): string => false,
            )
            ->actions([
                Tables\Actions\EditAction::make(),

                // Custom 'View' Action to display the InfoList in a modal
                Tables\Actions\Action::make('view')
                    ->label('View') // Label for the action
                    ->modalHeading('View Details') // Modal title
                    ->modalSubheading(fn ($record) => 'Details of ' . $record->name) // Subheading
                    ->modalSubmitAction(false)
                    ->modalCancelAction(false)
                    ->modalCloseButton(true)
                    ->infolist([ // InfoList to show in the modal
                        Split::make([
                            Section::make([
                                TextEntry::make('name'),
                                TextEntry::make('email')
                                    ->icon('heroicon-m-envelope'),
                                TextEntry::make('phone')
                                    ->icon('heroicon-s-phone'),
                                TextEntry::make('address'),
                                TextEntry::make('type')
                                    ->badge()
                                    ->color(fn (string $state): string => match ($state) {
                                        'customer' => 'success',
                                        'supplier' => 'danger',
                                    }),
                            ]),
                            Section::make([
                                TextEntry::make('created_at')
                                    ->dateTime()
                                    ->icon('heroicon-s-clock'),
                                TextEntry::make('updated_at')
                                    ->dateTime()
                                    ->icon('heroicon-s-clock'),
                            ])->grow(false),
                        ])->from('md'),
                    ])
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
            'index' => Pages\ListContacts::route('/'),
            'create' => Pages\CreateContact::route('/create'),
            'edit' => Pages\EditContact::route('/{record}/edit'),
        ];
    }
}
