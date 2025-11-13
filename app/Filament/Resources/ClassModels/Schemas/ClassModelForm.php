<?php

namespace App\Filament\Resources\ClassModels\Schemas;

use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ClassModelForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Identificação da Turma')
                ->description('Defina o nome, a escola, o tipo e o coordenador responsável.')
                ->columns(2)
                ->schema([
                    TextInput::make('name')
                        ->label('Nome da Turma')
                        ->placeholder('Ex.: 2026.1')
                        ->required()
                        ->maxLength(100)
                        ->prefixIcon('heroicon-m-academic-cap'),

                    Select::make('school_id')
                        ->label('Escola')
                        ->placeholder('Selecione a escola')
                        ->relationship('school', 'name')
                        ->searchable()
                        ->preload()
                        ->required()
                        ->suffixIcon('heroicon-m-building-library')
                        ->helperText('A turma ficará vinculada a esta unidade.'),

                    Select::make('class_type_id')
                        ->label('Tipo de Turma')
                        ->placeholder('Selecione o tipo de turma')
                        ->relationship('classType', 'name')
                        ->searchable()
                        ->preload()
                        ->required()
                        ->suffixIcon('heroicon-m-rectangle-stack')
                        ->helperText('Ex.: Carisma – 1º Ano, Escola de Mestres, etc.'),

                    Select::make('user_id')
                        ->label('Coordenador')
                        ->placeholder('Selecione o coordenador (opcional)')
                        ->relationship('user', 'name')
                        ->searchable()
                        ->preload()
                        ->nullable()
                        ->suffixIcon('heroicon-m-user-circle'),
                ]),

            Section::make('Agenda')
                ->description('Escolha os dias e o período em que a turma acontecerá.')
                ->columns(2)
                ->schema([
                    CheckboxList::make('weekday')
                        ->label('Dias da Semana')
                        ->options([
                            1 => 'Segunda',
                            2 => 'Terça',
                            3 => 'Quarta',
                            4 => 'Quinta',
                            5 => 'Sexta',
                            6 => 'Sábado',
                            0 => 'Domingo',
                        ])
                        ->columns(4)
                        ->columnSpanFull()
                        ->helperText('Selecione todos os dias em que a turma se reúne.'),

                    DatePicker::make('start_date')
                        ->label('Início')
                        ->native(false)
                        ->suffixIcon('heroicon-m-calendar-days')
                        ->helperText('Data de início do período letivo.'),

                    DatePicker::make('end_date')
                        ->label('Fim')
                        ->native(false)
                        ->suffixIcon('heroicon-m-calendar-days')
                        ->rule('after_or_equal:start_date')
                        ->helperText('Data de conclusão da turma.'),
                ]),
        ])->columns(1);
    }
}
