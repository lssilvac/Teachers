<?php

namespace App\Filament\Pages;

use Carbon\Carbon;
use DebugBar\DebugBar;
use Filament\Auth\Pages\EditProfile;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Tapp\FilamentGoogleAutocomplete\Forms\Components\GoogleAutocomplete;

class TeacherProfile extends EditProfile
{
    //protected static bool $shouldRegisterNavigation = false;

    protected string $view = 'filament.pages.teacher-profile';

    protected function fillForm(): void
    {
        $data = $this->getUser()->load('teacher')->toArray();

        $this->callHook('beforeFill');

        $data = $this->mutateFormDataBeforeFill($data);
        debugbar()->info($data);
        $this->form->fill($data);

        $this->callHook('afterFill');
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->inlineLabel(false)
            ->components([
                Section::make('Dados Pessoais')
                    ->description('Mantenha seus dados atualizados.')
                    ->collapsible()
                    ->icon('heroicon-o-user-circle')
                    ->columns(2)
                    ->schema([
                        TextInput::make('teacher.name')
                            ->label('Nome completo')
                            ->placeholder('Ex.: Lucas Samuel da Silva Cordeiro')
                            ->prefixIcon('heroicon-o-user')
                            ->required()
                            ->validationMessages([
                                'required' => 'O campo Nome completo é obrigatório.',
                            ]),

                        DatePicker::make('teacher.birth_date')
                            ->label('Data de nascimento')
                            ->placeholder('dd/mm/aaaa')
                            ->prefixIcon('heroicon-o-calendar-days')
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->maxDate(now())
                            ->rule('before_or_equal:today')
                            ->required()
                            ->validationMessages([
                                'required' => 'Informe sua data de nascimento.',
                                'before_or_equal' => 'A data deve ser igual ou anterior à data atual.',
                            ])
                            ->live()
                            ->dehydrated(true)
                            ->afterStateUpdated(function ($state, $set) {
                                if (empty($state)) {
                                    $set('age', null);
                                    return;
                                }
                                try {
                                    $birthDate = Carbon::parse($state);
                                    $age = (int)floor($birthDate->diffInYears(now()));
                                    $set('age', $age . ' anos');
                                } catch (\Exception $e) {
                                    $set('age', null);
                                }
                            }),


                        TextInput::make('email')
                            ->label('E-mail')
                            ->placeholder('exemplo@dominio.com')
                            ->prefixIcon('heroicon-o-envelope')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->rule(function ($get, $record) {
                                if (is_null($record)) {
                                    return null;
                                }

                                if ($get('email') === ($record->email ?? null)) {
                                    return null;
                                }

                                return $get('email') !== $get('email_confirmation') ? 'confirmed' : null;
                            })
                            ->validationMessages([
                                'required' => 'O campo E-mail é obrigatório.',
                                'unique' => 'O e-mail já consta em nossa base de dados.',
                                'confirmed' => 'Os e-mails não coincidem.',
                            ]),

                        TextInput::make('email_confirmation')
                            ->label('Confirmar e-mail')
                            ->placeholder('Repita o e-mail exatamente igual')
                            ->prefixIcon('heroicon-o-check-circle')
                            ->email()
                            ->dehydrated(false)
                            ->validationMessages([
                                'required' => 'Confirme o e-mail.',
                                'email' => 'Digite um e-mail válido.'
                            ]),


                        TextInput::make('password')
                            ->label('Senha')
                            ->placeholder('Crie uma senha segura')
                            ->prefixIcon('heroicon-o-lock-closed')
                            ->password()
                            ->revealable()
                            ->autocomplete('new-password')
                            ->dehydrated(fn($state) => filled($state))
                            ->rule('confirmed')
                            ->validationMessages([
                                'confirmed' => 'As senhas não coincidem.',
                            ])
                            ->helperText('Deixe em branco para manter a senha atual.'),

                        TextInput::make('password_confirmation')
                            ->label('Confirmar senha')
                            ->placeholder('Repita a senha')
                            ->prefixIcon('heroicon-o-key')
                            ->password()
                            ->revealable()
                            ->autocomplete('new-password')
                            ->dehydrated(false)
                            ->required(fn($get) => filled($get('password')))
                            ->validationMessages([
                                'required' => 'Confirme a senha.',
                            ]),
                    ]),

                Section::make('Endereço')
                    ->collapsed()
                    ->icon('heroicon-o-map-pin')
                    ->description('Digite e selecione uma sugestão para preencher país, estado, cidade e CEP automaticamente.')
                    ->columns(2)
                    ->schema([
                        GoogleAutocomplete::make('google_search')
                            ->label('Localização')
                            ->autocompletePlaceholder('Digite o endereço completo...')
                            ->language('pt-BR')
                            ->columns(2)
                            ->withFields([
                                TextInput::make('teacher.country')
                                    ->prefixIcon('heroicon-o-globe-alt')
                                    ->label('País')
                                    ->placeholder('Preenchido automaticamente')
                                    ->disabled()
                                    ->dehydrated(true)
                                    ->extraInputAttributes(['data-google-value' => 'short_name']),

                                TextInput::make('teacher.administrative_area_level_1')
                                    ->prefixIcon('heroicon-o-map')
                                    ->label('Estado / Região / Província')
                                    ->placeholder('Preenchido automaticamente')
                                    ->disabled()
                                    ->dehydrated(true),

                                TextInput::make('teacher.administrative_area_level_2')
                                    ->prefixIcon('heroicon-o-building-office')
                                    ->label('Cidade / Município')
                                    ->placeholder('Preenchido automaticamente')
                                    ->disabled()
                                    ->dehydrated(true),

                                TextInput::make('teacher.postal_code')
                                    ->prefixIcon('heroicon-o-envelope-open')
                                    ->label('CEP')
                                    ->placeholder('Preenchido automaticamente')
                                    ->disabled()
                                    ->dehydrated(true),

                                Hidden::make('teacher.route'),
                                Hidden::make('teacher.street_number'),
                                Hidden::make('teacher.sublocality_level_1'),
                                Hidden::make('teacher.locality'),
                                Hidden::make('teacher.place_id'),
                                Hidden::make('teacher.latitude'),
                                Hidden::make('teacher.longitude'),
                                Hidden::make('teacher.formatted_address'),
                            ]),
                    ]),

//                Section::make('Redes Sociais')
//                    ->description('Indique redes sociais para seus alunos te encontrarem.')
//                    ->collapsed()
//                    ->icon('heroicon-o-at-symbol')
//                    ->schema([
//                        Repeater::make('socialMedias')
//                            ->hiddenLabel()
//                            ->collapsible()
//                            ->reorderableWithButtons()
//                            ->addActionLabel('Adicionar rede social')
//                            ->relationship()
//                            ->columns(3)
//                            ->schema([
//                                TextInput::make('username')
//                                    ->label('Usuário')
//                                    ->placeholder('ex.: seu_usuario')
//                                    ->prefixIcon('heroicon-o-at-symbol')
//                                    ->columnSpan(2)
//                                    ->required()
//                                    ->validationMessages([
//                                        'required' => 'Informe o nome de usuário da rede social.',
//                                    ]),
//
//                                Select::make('type')
//                                    ->label('Plataforma')
//                                    ->native(false)
//                                    ->searchable()
//                                    ->preload()
//                                    ->required()
//                                    ->columnSpan(1)
//                                    ->options([
//                                        'facebook' => 'Facebook',
//                                        'twitter' => 'Twitter / X',
//                                        'tiktok' => 'TikTok',
//                                        'linkedin' => 'LinkedIn',
//                                        'instagram' => 'Instagram',
//                                        'youtube' => 'YouTube',
//                                    ])
//                                    ->validationMessages([
//                                        'required' => 'Selecione a plataforma da rede social.',
//                                    ]),
//                            ]),
//                    ]),
//
//                Section::make('Currículo')
//                    ->description('Exponha aos alunos suas formações.')
//                    ->collapsed()
//                    ->icon('heroicon-o-clipboard-document-list')
//                    ->schema([
//                        Repeater::make('curriculums')
//                            ->hiddenLabel()
//                            ->collapsible()
//                            ->reorderableWithButtons()
//                            ->addActionLabel('Adicionar especialização')
//                            ->relationship()
//                            ->schema([
//                                TextInput::make('title')
//                                    ->label('Especialização')
//                                    ->placeholder('ex.: Bacharelado em Teologia - UNIV')
//                                    ->prefixIcon('heroicon-o-academic-cap')
//                                    ->required()
//                                    ->validationMessages([
//                                        'required' => 'Informe o nome da especialização.',
//                                    ]),
//                            ])
//                            ->orderColumn('order')
//                            ->itemLabel(fn(array $state): ?string => $state['title'] ?? null),
//                    ]),
            ]);
    }
}
