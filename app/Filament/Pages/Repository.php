<?php

namespace App\Filament\Pages;

use App\Models\Project;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables\Table;

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
