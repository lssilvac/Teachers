<?php

namespace App\Filament\Widgets;

use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\DB;
use InfinityXTech\FilamentWorldMapWidget\Widgets\WorldMapWidget;

class TeachersMap extends WorldMapWidget
{


    // faz o widget ocupar todas as colunas do dashboard
    protected int|string|array $columnSpan = 'full';

    // (opcional) ordenação entre os widgets
    protected static ?int $sort = 10;


    public function heading(): string|Htmlable|null
    {
        return 'Professores'; // Default heading for the widget
    }

    public function tooltip(): string|Htmlable
    {
        return 'professores'; // Tooltip text displayed on hover
    }

    public function height(): string
    {
        return '600px'; // Default widget height
    }

    public function stats(): array
    {
        return DB::table('teachers')
            ->select('country', DB::raw('COUNT(*) as total'))
            ->groupBy('country')
            ->pluck('total', 'country')
            ->map(fn($value) => (int) $value)
            ->toArray();

    }
    public function color(): array
    {
        return [165, 21, 24];
    }




}
