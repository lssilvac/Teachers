<?php

namespace App\Filament\Resources\ClassModels\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ClassModelsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Turma')
                    ->searchable()
                    ->sortable(),

                // 👇 colunas de relações (usa dot notation)
                TextColumn::make('school.name')
                    ->label('Escola')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('classType.name')
                    ->label('Tipo')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('user.name')
                    ->label('Coordenador')
                    ->toggleable(),

                TextColumn::make('start_date')
                    ->label('Início')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('end_date')
                    ->label('Fim')
                    ->date('d/m/Y')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
