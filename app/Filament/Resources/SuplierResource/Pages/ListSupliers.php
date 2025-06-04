<?php

namespace App\Filament\Resources\SuplierResource\Pages;

use App\Filament\Resources\SuplierResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSupliers extends ListRecords
{
    protected static string $resource = SuplierResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
