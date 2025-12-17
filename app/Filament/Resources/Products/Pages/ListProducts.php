<?php

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Resources\Products\ProductResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListProducts extends ListRecords
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('import')
                ->label('Import Products')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('success')
                ->form([
                    \Filament\Forms\Components\FileUpload::make('attachment')
                        ->label('Upload Excel File')
                        // ->acceptedFileTypes(['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/vnd.ms-excel', 'text/csv'])
                        // ->disk('public')
                        // ->directory('product-imports')
                        ->required(),
                ])
                ->action(function (array $data) {
                    $file = public_path('storage/' . $data['attachment']);
                    // If directory is used, attachment contains full path relative to disk root.
                    // But public_path('storage/'...) assumes 'storage' link points to 'app/public'.
                    // If we use directory('product-imports'), key is 'product-imports/filename.xlsx'.
                    
                    // $file = public_path('storage/' . $data['attachment']);
                    // Filament stores temp file path in $data['attachment'] if using default disk? 
                    // No, invalid.
                    
                    // Let's try to get the path directly.
                    // When 'disk' is not specified, it uses default (usually public or local).
                    // Let's debug by assuming it's in the default storage.
                    
                   $file = \Illuminate\Support\Facades\Storage::path($data['attachment']);
                    
                    \Maatwebsite\Excel\Facades\Excel::import(new \App\Imports\ProductImport, $file);
                    
                    \Filament\Notifications\Notification::make()
                        ->title('Products imported successfully')
                        ->success()
                        ->send();
                }),
        ];
    }
}
