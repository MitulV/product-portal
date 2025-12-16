<?php

namespace App\Filament\Resources\Products\Schemas;

use Filament\Schemas\Schema;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                \Filament\Schemas\Components\Section::make('Order Information')
                    ->columns(2)
                    ->schema([
                        \Filament\Forms\Components\TextInput::make('salesman'),
                        \Filament\Forms\Components\TextInput::make('opportunity_name')
                            ->label('Opportunity Name'),
                        \Filament\Forms\Components\TextInput::make('sales_order_number')
                            ->label('Sales Order #'),
                        \Filament\Forms\Components\TextInput::make('ipas_cpq_number')
                            ->label('IPAS/CPQ #'),
                        \Filament\Forms\Components\TextInput::make('cps_po_number')
                            ->label('CPS PO#'),
                        \Filament\Forms\Components\DatePicker::make('est_completion_date')
                            ->label('Est Completion Date'),
                        \Filament\Forms\Components\DatePicker::make('ship_date')
                            ->label('Ship Date'),
                    ]),

                \Filament\Schemas\Components\Section::make('Unit Specifications')
                    ->columns(3) // 3 columns for spec details
                    ->schema([
                        \Filament\Forms\Components\TextInput::make('brand'),
                        \Filament\Forms\Components\TextInput::make('model_number')
                            ->label('Model Number'),
                        \Filament\Forms\Components\TextInput::make('serial_number')
                            ->unique(ignoreRecord: true),
                        \Filament\Forms\Components\TextInput::make('unit_id')
                            ->label('Unit ID')
                            ->unique(ignoreRecord: true),
                        \Filament\Forms\Components\TextInput::make('voltage'),
                        \Filament\Forms\Components\TextInput::make('phase'),
                        \Filament\Forms\Components\TextInput::make('enclosure'),
                        \Filament\Forms\Components\TextInput::make('enclosure_type'),
                        \Filament\Forms\Components\TextInput::make('tank'),
                        \Filament\Forms\Components\TextInput::make('controller_series'),
                        \Filament\Forms\Components\TextInput::make('breakers'),
                    ]),

                \Filament\Schemas\Components\Section::make('Financials')
                    ->columns(2)
                    ->schema([
                        \Filament\Forms\Components\TextInput::make('total_cost')
                            ->numeric()
                            ->prefix('$'),
                        \Filament\Forms\Components\TextInput::make('tariff_cost')
                            ->numeric()
                            ->prefix('$')
                            ->label('Tariff (Included in Total)'),
                    ]),

                \Filament\Schemas\Components\Section::make('Hold Status')
                    ->schema([
                        \Filament\Forms\Components\TextInput::make('hold_status'),
                        \Filament\Forms\Components\TextInput::make('hold_branch'),
                        \Filament\Forms\Components\DatePicker::make('hold_expiration_date'),
                    ])->columns(3),

                \Filament\Schemas\Components\Section::make('Additional Details')
                    ->schema([
                        \Filament\Forms\Components\Select::make('category_id')
                            ->relationship('category', 'name')
                            ->label('Category')
                            ->searchable()
                            ->preload(),
                        \Filament\Forms\Components\Textarea::make('notes')
                            ->columnSpanFull(),
                        \Filament\Forms\Components\Textarea::make('tech_spec')
                            ->label('Tech Specs')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
