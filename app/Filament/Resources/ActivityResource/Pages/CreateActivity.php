<?php

namespace App\Filament\Resources\ActivityResource\Pages;

use App\Filament\Resources\ActivityResource;
use App\Models\User;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateActivity extends CreateRecord
{
    protected static string $resource = ActivityResource::class;

    public function afterCreate(): void
    {
        Notification::make()
            ->title('Activity Scheduled')
            ->body(auth()->user()->name . ' scheduled an activity ' . $this->record->title . ' for ' . $this->record->start_date->format('F d, Y'))
            ->success()
            ->sendToDatabase(User::all())
            ->send();
    }
}
