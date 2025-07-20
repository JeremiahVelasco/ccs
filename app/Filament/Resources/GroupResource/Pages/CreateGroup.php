<?php

namespace App\Filament\Resources\GroupResource\Pages;

use App\Filament\Resources\GroupResource;
use App\Models\User;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateGroup extends CreateRecord
{
    protected static string $resource = GroupResource::class;

    public function afterCreate(): void
    {
        Notification::make()
            ->title('Group Created')
            ->body(auth()->user()->name . ' created group ' . $this->record->name)
            ->success()
            ->sendToDatabase(User::all())
            ->send();
    }
}
