<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\ActivityResource;
use App\Models\Activity;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Widgets\Widget;
use Saade\FilamentFullCalendar\Widgets\FullCalendarWidget;

class CalendarWidget extends FullCalendarWidget
{
    public function fetchEvents(array $fetchInfo): array
    {
        return Activity::query()
            ->where('date', '>=', $fetchInfo['start'])
            ->where('date', '<=', $fetchInfo['end'])
            ->get()
            ->map(
                fn(Activity $activity) => [
                    'title' => $activity->title,
                    'date' => $activity->date,
                    'url' => ActivityResource::getUrl(name: 'edit', parameters: ['record' => $activity]),
                    'shouldOpenUrlInNewTab' => true
                ]
            )
            ->all();
    }

    public function getFormSchema(): array
    {
        return [
            Grid::make()
                ->schema([
                    TextInput::make('title'),
                    DateTimePicker::make('date'),
                ]),
            Textarea::make('description'),
        ];
    }
}
