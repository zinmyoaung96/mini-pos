<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PurchaseResource\Pages;
use App\Filament\Resources\PurchaseResource\RelationManagers;
use App\Models\Product;
use App\Models\Purchase;
use Awcodes\TableRepeater\Components\TableRepeater;
use Awcodes\TableRepeater\Header;
use Filament\Actions\DeleteAction;
use Filament\Forms;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Notifications\Action;
use Illuminate\Support\HtmlString;

class PurchaseResource extends Resource
{
    protected static ?string $model = Purchase::class;

    public $status = 'draft';
    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';
    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {

        //
//                Placeholder::make('voucher_no')
//                    ->content(function ($record): string {
//                        if ($record instanceof Purchase) {
//                            return $record->voucher_no ?? '';
//                        } else {
//                            return '';
//                        }
//                    }),

        return $form
            ->schema([
                Wizard::make([
                    Wizard\Step::make('Voucher')
                        ->columns([
                            'sm' => 3,
                            'xl' => 3,
                            '2xl' => 3,
                        ])
                        ->schema([
                                    Forms\Components\TextInput::make('voucher_no')
                                        ->default(fn() => Purchase::generateVoucherNo())
                                        ->readOnly()
                                        ->required(),
                                    Forms\Components\Select::make('supplier_id')
                                        ->label('Supplier')
                                        ->native(false)
                                        ->createOptionAction(
                                            fn (Action $action) => $action->modalWidth('md'),
                                        )
                                        ->relationship(name: 'supplier', titleAttribute: 'name')
                                        ->searchable(['name'])
                                        ->preload(),
                                    Forms\Components\DateTimePicker::make('purchase_date')
                                        ->default(now())
                                        ->required(),
                                    Forms\Components\Select::make('status')
                                        ->default('draft')
                                        ->required()
                                        ->options([
                                            'draft' => 'Draft',
                                            'order' => 'Order',
                                            'completed' => 'Completed',
                                            'cancel' => 'Cancel',
                                            ])
                                        ->native(false)
                                        ->reactive(),
                                    Forms\Components\DateTimePicker::make('received_date')
                                        ->required(function (callable $get) {
                                            return $get('status') === 'completed';
                                        }),

                                    Forms\Components\Textarea::make('remark')
                                        ->columns(2),
                        ]),
                    Wizard\Step::make('Purchase Items')
                        ->schema([
                           TableRepeater::make('details')
                                ->label(false)
                               ->addActionLabel('Add Product')
                               ->cloneable()
                               ->headers([
                                   Header::make('Product'),
                                   Header::make('Price')->width('150px'),
                                   Header::make('Quantity')->width('150px'),
                                   Header::make('Unit')->width('200px'),
                                   Header::make('Total')->width('150px'),
                               ])
                                ->relationship('details')
                                ->schema([
                                    Forms\Components\Select::make('product_id')
                                        ->label('Product')
                                        ->native(false)
                                        ->relationship(name: 'product', titleAttribute: 'name')
                                        ->searchable(['name'])
                                        ->preload()
                                        ->reactive()
                                        ->afterStateUpdated(function ($state, callable $set) {
                                            $product = Product::find($state);
                                            $set('purchase_price', $product ? $product->purchase_price : null);
                                            $set('quantity', 0);
                                            $set('unit', $product ? $product->unit : null);
                                            $set('subtotal_price', $product ? $product->purchase_price : null);
                                            $set('total_price', $product->purchase_price);
                                        }),
                                    TextInput::make('purchase_price')
                                        ->label('Purchase Price')
                                        ->reactive()
                                        ->afterStateUpdated(fn ($state, callable $set, $get) => $set('subtotal_price', $state * $get('quantity'))),
                                    Forms\Components\TextInput::make('quantity')
                                        ->reactive()
                                        ->afterStateUpdated(fn ($state, callable $set, $get) => $set('subtotal_price', $state * $get('purchase_price'))),
                                    Forms\Components\Select::make('unit')
                                        ->required()
                                        ->options([
                                            'piece' => 'Piece',
                                            'kg' => 'KG',
                                            'gram' => 'Gram',
                                            'liter' => 'Liter',
                                            'meter' => 'Meter',
                                            'box' => 'Box',
                                        ])
                                        ->native(false),
                                    Forms\Components\TextInput::make('subtotal_price')
                                        ->label('Subtotal Price')
                                        ->readOnly(),
                                ])
                               ->columnSpan('full')
                               ->live()
                               // After adding a new row, we need to update the totals
                               ->afterStateUpdated(function (Get $get, Set $set) {
                                   self::updateTotals($get, $set);
                               }),
                            Forms\Components\TextInput::make('total_price')
                                ->required()
                                ->numeric(),
                        ]),
                ]) ->columnSpanFull(),
            ]);
    }

    public static function updateTotals(Get $get, Set $set): void
    {
        // Retrieve all selected products and remove empty rows
        $selectedProducts = collect($get('details'))->filter(fn($item) => !empty($item['product_id']) && !empty($item['quantity']));

        // Retrieve prices for all selected products
        $prices = Product::find($selectedProducts->pluck('product_id'))->pluck('purchase_price', 'id');

        // Calculate subtotal based on the selected products and quantities
        $subtotal = $selectedProducts->reduce(function ($subtotal, $product) use ($prices) {
            return $subtotal + ($prices[$product['product_id']] * $product['quantity']);
        }, 0);

        // Update the state with the new values
//        $set('subtotal', number_format($subtotal, 2, '.', ''));
        $set('total_price', number_format($subtotal + ($subtotal * ($get('taxes') / 100)), 2, '.', ''));

    }


    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('voucher_no')
                    ->searchable(),
                Tables\Columns\TextColumn::make('purchase_date')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'order' => 'warning',
                        'completed' => 'success',
                        'cancel' => 'danger',
                        'draft' => 'gray',
                    }),
                Tables\Columns\TextColumn::make('received_date')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_price')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('creator.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('updater.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),


                Tables\Actions\Action::make('updateStatus')
                    ->form([
                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->default('draft')
                            ->required()
                            ->options([
                                'draft' => 'Draft',
                                'order' => 'Order',
                                'completed' => 'Completed',
                                'cancel' => 'Cancel',
                            ])
                            ->native(false)
                    ])
                    ->action(function (array $data, Purchase $record): void {
                        $record->save();
                    }),
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
//            RelationManagers\DetailsRelationManager::make()
        ];
    }


    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPurchases::route('/'),
            'create' => Pages\CreatePurchase::route('/create'),
            'edit' => Pages\EditPurchase::route('/{record}/edit'),
        ];
    }
}
