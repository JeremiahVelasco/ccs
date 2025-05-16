<?php

namespace App\Filament\Pages;

use App\Filament\Resources\ProjectResource;
use App\Models\Project;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Dashboard;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class Repository extends Page implements HasForms, HasTable
{
    use InteractsWithTable;
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-server-stack';

    protected static string $view = 'filament.pages.repository';

    public function table(Table $table): Table
    {
        return $table
            ->query(Project::query()->where('status', 'Done'))
            ->recordUrl(
                fn(Model $project): string => ProjectResource::getUrl('view', ['record' => $project->id]),
            )
            ->columns([
                TextColumn::make('title')
                    ->searchable(),
                TextColumn::make('description')
                    ->searchable(),
                TextColumn::make('final_grade'),
                TextColumn::make('awards'),
            ]);
    }
}
