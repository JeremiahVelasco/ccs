<?php

namespace App\Filament\Clusters;

use Filament\Clusters\Cluster;

class Tasks extends Cluster
{
    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';

    protected static ?string $navigationGroup = 'My Group';

    public static function canAccess(): bool
    {
        return auth()->user()->isStudent();
    }
}
