<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StockBalanceResource\Pages;
use App\Filament\Resources\StockBalanceResource\RelationManagers;
use App\Models\StockBalance;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\DB;

class StockBalanceResource extends Resource
{
    protected static ?string $model = StockBalance::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-trending-up';
    protected static ?int $navigationSort = 7;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('transaction_id')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('product_id')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('batch_no')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('purchase_quantity')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('unit')
                    ->required(),
                Forms\Components\TextInput::make('sale_price')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('current_quantity')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('created_by')
                    ->numeric(),
                Forms\Components\TextInput::make('updated_by')
                    ->numeric(),
            ]);
    }


    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('transaction.voucher_no')
                    ->label('Voucher No')
                    ->sortable(),
                Tables\Columns\TextColumn::make('product.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('batch_no')
                    ->searchable(),
                Tables\Columns\TextColumn::make('purchase_quantity')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('unit'),
                Tables\Columns\TextColumn::make('sale_price')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('current_quantity')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('creator.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('updater.name')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
//            ->modifyQueryUsing(function (Builder $query) {
//                $query->select([
//                    'transaction_id', 'product_id', 'batch_no', 'unit',
//                    'sale_price', 'created_by', 'updated_by'
//                ])
//                    ->selectRaw('SUM(current_quantity) as total_current_quantity')
//                    ->selectRaw('SUM(purchase_quantity) as total_purchase_quantity')
//                    ->groupBy('transaction_id', 'product_id', 'batch_no', 'unit', 'sale_price', 'created_by', 'updated_by');
//            })
            ->filters([
                SelectFilter::make('transaction_id')
                    ->label('Voucher No')
                    ->native(false)
                    ->relationship(name: 'transaction', titleAttribute: 'voucher_no')
                    ->preload(),
                SelectFilter::make('product_id')
                    ->label('Product')
                    ->native(false)
                    ->relationship(name: 'product', titleAttribute: 'name')
                    ->preload(),
            ], layout: FiltersLayout::AboveContentCollapsible)
            ->actions([
//                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
//                Tables\Actions\BulkActionGroup::make([
//                    Tables\Actions\DeleteBulkAction::make(),
//                ]),
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
            'index' => Pages\ListStockBalances::route('/'),
//            'create' => Pages\CreateStockBalance::route('/create'),
//            'edit' => Pages\EditStockBalance::route('/{record}/edit'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
