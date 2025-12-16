<?php

namespace App\Filament\Resources\Products\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Table;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                \Filament\Tables\Columns\TextColumn::make('unit_id')
                    ->label('Unit ID')
                    ->searchable()
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('serial_number')
                    ->searchable(),
                \Filament\Tables\Columns\TextColumn::make('model_number')
                    ->label('Model')
                    ->searchable(),
                \Filament\Tables\Columns\TextColumn::make('hold_status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Hold' => 'danger',
                        'Released' => 'success',
                        default => 'gray',
                    }),
                \Filament\Tables\Columns\TextColumn::make('salesman')
                    ->searchable(),
                \Filament\Tables\Columns\TextColumn::make('est_completion_date')
                    ->date()
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('total_cost')
                    ->money('USD')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
