<?php

namespace App\Filament\Resources\PurchaseResource\RelationManagers;

use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Infolists\Infolist;

class PurchaseDetailsRelationManager extends RelationManager
{
    protected static string $relationship = 'purchase_details';

    protected static ?string $title = 'Purchase';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('purchase.voucher_no')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('purchase_id')
            ->columns([
                Tables\Columns\TextColumn::make('purchase.voucher_no')
                ->label('Voucher'),
                Tables\Columns\TextColumn::make('purchase.purchase_date')
                    ->label('Purchased Date'),
                Tables\Columns\TextColumn::make('purchase.supplier.name')
                    ->label('Supplier'),
                Tables\Columns\TextColumn::make('purchase.status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'order' => 'warning',
                        'completed' => 'success',
                        'cancel' => 'danger',
                        'draft' => 'gray',
                    }),
            ])
            ->headerActions([
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
            ]);
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                TextEntry::make('purchase.voucher_no'),
                TextEntry::make('purchase.purchase_date')
                    ->dateTime(),
                TextEntry::make('purchase.received_date')
                    ->label('Received')
                    ->since(),
                TextEntry::make('purchase.supplier.name'),
                TextEntry::make('purchase.status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'order' => 'warning',
                        'completed' => 'success',
                        'cancel' => 'danger',
                        'draft' => 'gray',
                    }),

            ]);
    }



}
