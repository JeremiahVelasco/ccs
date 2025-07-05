<?php

namespace App\Filament\Widgets;

use App\Models\Project;
use App\Services\ProjectPredictionService;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ProjectPredictionWidget extends BaseWidget
{
    public ?Project $record = null;

    protected function getStats(): array
    {
        if (!$this->record) {
            return [];
        }

        $predictionService = app(ProjectPredictionService::class);
        $prediction = $predictionService->predictCompletion($this->record);

        $riskColor = match ($prediction['risk_level']) {
            'low' => 'success',
            'medium' => 'warning',
            'high' => 'danger',
            'critical' => 'danger',
            default => 'gray'
        };

        return [
            Stat::make('Completion Probability', $prediction['percentage'] . '%')
                ->description('Based on Bayesian Network analysis')
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color($riskColor),

            Stat::make('Risk Level', ucfirst($prediction['risk_level']))
                ->description('Project completion risk')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($riskColor),
        ];
    }
}
