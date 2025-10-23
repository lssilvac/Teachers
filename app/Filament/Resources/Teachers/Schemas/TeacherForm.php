<?php

namespace App\Filament\Resources\Teachers\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Support\Carbon;
use Tapp\FilamentGoogleAutocomplete\Forms\Components\GoogleAutocomplete;

class TeacherForm
{
    /**
     * @throws \Exception
     */
    public static function configure(Schema $schema): Schema
    {
        return $schema->components(components: [
            TextInput::make('name')
                ->label('Nome')
                ->required(),

            TextInput::make('email')
                ->label('Endereço de e-mail')
                ->email()
                ->required()
                ->unique(ignoreRecord: true)
                // aplicar 'confirmed' somente quando necessário (create ou email alterado)
                ->rule(function ($get, $record) {
                    // se for criação => aplicar confirmed
                    if (is_null($record)) {
                        return 'confirmed';
                    }

                    // se for edição, aplicar confirmed apenas se o e-mail foi alterado
                    return $get('email') !== ($record->email ?? null) ? 'confirmed' : null;
                })
                ->validationMessages([
                    'unique' => 'O e-mail já consta em nossa base de dados.',
                    'confirmed' => 'Os e-mails não coincidem.',
                ]),

            TextInput::make('email_confirmation')
                ->label('Confirmar e-mail')
                ->email()
                // não persiste no modelo (opcional). se sua versão do Filament não validar campos dehydrated(false),
                // remova este ->dehydrated(false) para garantir validação backend.
                ->dehydrated(false)
                // obrigatório apenas em criação ou quando o e-mail foi alterado
                ->required(function ($get, $record) {
                    if (is_null($record)) {
                        // em criação: exigir confirmação se houver um e-mail preenchido
                        return ! empty($get('email'));
                    }

                    // em edição: exigir confirmação somente se o e-mail for diferente do que está no registro
                    return $get('email') !== ($record->email ?? null);
                })
                ->validationMessages([
                    'required' => 'Confirme o e-mail.',
                    'email' => 'Digite um e-mail válido.',
                ]),

            DatePicker::make('birth_date')
                ->label('Data de nascimento')
                ->required()
                ->native(false)
                ->displayFormat('d/m/Y')
                ->dehydrated(true)
                ->maxDate(now()) // ⛔ bloqueia datas futuras
                ->rule('before_or_equal:today')
                ->live()
                ->afterStateUpdated(function ($state, $set) {
                    if (empty($state)) {
                        $set('age', null);
                        return;
                    }

                    try {
                        $birthDate = Carbon::parse($state);
                        $age = floor($birthDate->diffInYears(now()));
                        $set('age', $age . ' anos');
                    } catch (\Exception $e) {
                        $set('age', null);
                    }
                }),


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
