<?php

namespace App\Filament\Resources\ProjectResource\Pages;

use App\Filament\Resources\ProjectResource;
use App\Models\User;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateProject extends CreateRecord
{
    protected static string $resource = ProjectResource::class;

    public function afterCreate(): void
    {
        Notification::make()
            ->title('Project Created')
            ->body(auth()->user()->name . ' created project ' . $this->record->title . ' for ' . $this->record->group->name)
            ->success()
            ->sendToDatabase(User::faculty()->get())
            ->send();
    }
}
