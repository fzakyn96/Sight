<?php

namespace App\Filament\Pages;

use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use UnitEnum;

class Peserta extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-user';
    protected static string|UnitEnum|null $navigationGroup = 'Vclaim';
    protected static ?string $title = 'Peserta';
    protected static ?string $slug = 'peserta-bpjs';
    protected string $view = 'filament.pages.peserta';

    public $searchBy = 'nokartu';
    public $identityNumber = '';
    public $serviceDate = '';

    public ?array $apiResult = null;

    public function mount(): void
    {
        $this->form->fill([
            'searchBy' => 'nokartu',
            'serviceDate' => now()->format('Y-m-d'),
        ]);
        $this->apiResult = session('pesertaResult', null);
    }

    protected function getFormSchema(): array
    {
        return [
            Section::make('Form Pencarian Peserta BPJS Kesehatan')
                ->columns(4)
                ->schema([
                    Radio::make('searchBy')
                        ->label('Cari Berdasarkan')
                        ->options([
                            'nokartu' => 'No. Kartu',
                            'nik' => 'NIK',
                        ])
                        ->default('nokartu')
                        ->reactive()
                        ->required()
                        ->inline()
                        ->columnSpan(1),
                    TextInput::make('identityNumber')
                        ->label(fn(callable $get) => $get('SearchBy') === 'nokartu' ? 'No. Kartu' : 'NIK')
                        ->placeholder(fn(callable $get) => $get('SearchBy') === 'nokartu' ? 'Masukkan No. Kartu' : 'Masukkan NIK')
                        ->minLength(fn(callable $get) => $get('SearchBy') === 'nokartu' ? 13 : 16)
                        ->maxLength(fn(callable $get) => $get('SearchBy') === 'nokartu' ? 13 : 16)
                        ->required()
                        ->inputMode('numeric')
                        ->columnSpan(2),
                    DatePicker::make('serviceDate')
                        ->label('Tanggal Pelayanan/SEP')
                        ->placeholder('Pilih Tanggal Pelayanan')
                        ->format('Y-m-d')
                        ->default(now())
                        ->required()
                        ->columnSpan(1),
                ])->footerActions([
                    Action::make('process')
                        ->label('Proses')
                        ->submit('submitForm'),
                ]),
        ];
    }
}
