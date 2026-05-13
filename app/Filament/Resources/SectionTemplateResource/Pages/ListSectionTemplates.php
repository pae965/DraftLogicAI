<?php

namespace App\Filament\Resources\SectionTemplateResource\Pages;

use App\Filament\Resources\SectionTemplateResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSectionTemplates extends ListRecords
{
    protected static string $resource = SectionTemplateResource::class;

    protected function getActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
