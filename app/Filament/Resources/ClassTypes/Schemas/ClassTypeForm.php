<?php

namespace App\Filament\Resources\ClassTypes\Schemas;

use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ClassTypeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Curso')
                    ->columnSpanFull()
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Nome do Curso')
                            ->placeholder('Carisma 1 Ano')
                            ->required()
                            ->validationMessages([
                                'required' => 'Informe o nome do curso.',
                            ]),
                    ]),
                Section::make('Disciplinas')
                    ->description('Matérias que compõem o curso')
                    ->columnSpanFull()
                    ->icon('heroicon-o-clipboard-document-list')
                    ->schema([
                        Repeater::make('subjects')
                            ->hiddenLabel()
                            ->itemNumbers()
                            ->reorderableWithButtons()
                            ->addActionLabel('Adicionar Disciplina')
                            ->relationship()
                            ->simple(

                                TextInput::make('name')
                                    ->label('Disciplina')
                                    ->placeholder('Nome da disciplina')
                                    ->required()
                                    ->validationMessages([
                                        'required' => 'Informe o nome da Disciplina.',
                                    ])

                            )
                            ->itemNumbers()
                            ->itemLabel(fn(array $state): ?string => $state['title'] ?? null),
                    ]),
            ]);
    }

    public static function getForm()
    {
    }
}
