<?php

namespace App\Filament\Resources\Teachers\Tables;

use App\Filament\Resources\Teachers\Pages\EditTeacher;
use App\Models\Teacher;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TeachersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nome')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('email')
                    ->label('E-mail')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('birth_date')
                    ->label('Nascimento')
                    ->date('d/m/Y')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('administrative_area_level_2')
                    ->label('Cidade')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('administrative_area_level_1')
                    ->label('UF')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('country')
                    ->label('País')
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                //
            ])
            // Torna a linha clicável para abrir a página de edição
            ->recordUrl(fn (Teacher $record) => EditTeacher::getUrl(['record' => $record]))
            ->actions([])      // sem EditAction
            ->bulkActions([]); // sem bulk actions
    }
}
