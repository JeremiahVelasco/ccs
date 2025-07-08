<?php

namespace App\Filament\Widgets;

use App\Models\Activity;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Tables\Columns\TextColumn;

class StudentRecentActivitiesWidget extends BaseWidget
{
    protected static ?string $heading = 'Recent Activities & Announcements';

    protected int | string | array $columnSpan = 1;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Activity::query()
                    ->orderBy('start_date', 'desc')
                    ->limit(5)
            )
            ->columns([
                TextColumn::make('title')
                    ->searchable()
                    ->weight('bold'),

                TextColumn::make('description')
                    ->limit(100)
                    ->wrap(),

                TextColumn::make('start_date')
                    ->dateTime()
                    ->sortable()
                    ->since(),
            ])
            ->emptyStateHeading('No recent activities')
            ->emptyStateDescription('There are no recent activities or announcements.')
            ->emptyStateIcon('heroicon-o-calendar-days');
    }
}
