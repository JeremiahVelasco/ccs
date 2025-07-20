<?php

namespace App\Filament\Resources\FileResource\Pages;

use App\Filament\Resources\FileResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Storage;

class ViewFile extends ViewRecord
{
    protected static string $resource = FileResource::class;

    protected function getHeaderActions(): array
    {
        $actions = [
            Actions\EditAction::make(),
        ];

        // Add download action if file exists
        if ($this->record->file_path && Storage::disk('public')->exists($this->record->file_path)) {
            $actions[] = Actions\Action::make('download')
                ->label('Download')
                ->icon('heroicon-m-arrow-down-tray')
                ->color('success')
                ->url(Storage::url($this->record->file_path))
                ->openUrlInNewTab();
        }

        // Add preview action for supported file types
        if ($this->record->file_path && in_array(pathinfo($this->record->file_path, PATHINFO_EXTENSION), ['pdf', 'jpg', 'jpeg', 'png', 'gif'])) {
            $actions[] = Actions\Action::make('preview')
                ->label('Preview')
                ->icon('heroicon-m-eye')
                ->color('info')
                ->url(Storage::url($this->record->file_path))
                ->openUrlInNewTab();
        }

        return $actions;
    }
}
