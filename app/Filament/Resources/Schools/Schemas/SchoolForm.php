<?php

namespace App\Filament\Resources\Schools\Schemas;

use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Tapp\FilamentGoogleAutocomplete\Forms\Components\GoogleAutocomplete;


class SchoolForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                ->label('Nome'),

                GoogleAutocomplete::make('google_search')
                    ->autocompletePlaceholder('Digite o endereço...')
                    ->label('Endereço')
                    ->language('pt-BR')
                    ->columns(2)
                    ->withFields([

                        // País
                        TextInput::make('country')
                            ->label('País')
                            ->disabled()
                            ->dehydrated(true)
                            ->extraInputAttributes([
                                'data-google-value' => 'short_name',
                            ]),

                        // Estado
                        TextInput::make('administrative_area_level_1')
                            ->label('Estado / Região / Província')
                            ->disabled()
                            ->dehydrated(true),

                        // Cidade
                        TextInput::make('administrative_area_level_2')
                            ->label('Cidade / Município')
                            ->disabled()
                            ->dehydrated(true),

                        // CEP
                        TextInput::make('postal_code')
                            ->label('CEP')
                            ->disabled()
                            ->dehydrated(true),

                        // Campos ocultos
                        Hidden::make('route') ,
                        Hidden::make('street_number') ,
                        Hidden::make('sublocality_level_1') ,
                        Hidden::make('locality') ,
                        Hidden::make('place_id') ,
                        Hidden::make('latitude') ,
                        Hidden::make('longitude') ,
                        Hidden::make('formatted_address') ,
                    ])
            ]);
    }
}
