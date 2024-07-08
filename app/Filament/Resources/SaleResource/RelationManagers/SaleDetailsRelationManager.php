<?php

namespace App\Filament\Resources\SaleResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SaleDetailsRelationManager extends RelationManager
{
    protected static string $relationship = 'sale_details';
    protected static ?string $title = 'Sale';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('sale_id')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('sale_id')
            ->columns([
                Tables\Columns\TextColumn::make('sale.voucher_no')
                    ->label('Voucher'),
                Tables\Columns\TextColumn::make('sale.sale_date')
                    ->label('Sale Date'),
                Tables\Columns\TextColumn::make('sale.customer.name')
                    ->label('Customer'),
                Tables\Columns\TextColumn::make('sale.status')
                    ->label('Status')
                ,
            ])
            ->filters([
                //
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
                TextEntry::make('sale.voucher_no'),
                TextEntry::make('sale.sale_date')
                    ->dateTime(),
                TextEntry::make('sale.customer.name'),
                TextEntry::make('sale.status')
                    ->label('Status'),

            ]);
    }
}
