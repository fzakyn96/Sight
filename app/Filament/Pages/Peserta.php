<?php

namespace App\Filament\Pages;

use App\Service\BPJSService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;       
use Filament\Notifications\Notification;
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
        // Ambil hasil dari session jika ada
        $this->apiResult = session('pesertaResult', null);
        // Bersihkan session setelah diambil
        session()->forget('pesertaResult');
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
                        ->label(fn(callable $get) => $get('searchBy') === 'nokartu' ? 'No. Kartu' : 'NIK')
                        ->placeholder(fn(callable $get) => $get('searchBy') === 'nokartu' ? 'Masukkan No. Kartu' : 'Masukkan NIK')
                        ->minLength(fn(callable $get) => $get('searchBy') === 'nokartu' ? 13 : 16)
                        ->maxLength(fn(callable $get) => $get('searchBy') === 'nokartu' ? 13 : 16)
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

    public function submitForm()
    {
        $bpjsService = app(BPJSService::class);

        try {
            $data = $this->form->getState();
            
            if ($data['searchBy'] === 'nokartu') {
                $response = $bpjsService->vclaim()->getPesertaByNoKartu(
                    $data['identityNumber'], 
                    $data['serviceDate']
                );
            } else {
                $response = $bpjsService->vclaim()->getPesertaByNIK(
                    $data['identityNumber'], 
                    $data['serviceDate']
                );
            }
            
            if (isset($response['metaData']) && $response['metaData']['code'] == '200') {
                $result = $bpjsService->vclaim()->responseData($response);

                if (isset($result['peserta'])) {
                    $this->apiResult = $result['peserta'];
                    
                    Notification::make()
                        ->title('Pencarian Berhasil')
                        ->body($response['metaData']['message'])
                        ->success()
                        ->send();
                } else {
                    $this->apiResult = null;
                    $message = $result['metaData']['message'] ?? 'Data peserta tidak ditemukan atau respons API tidak lengkap.';
                    
                    Notification::make()
                        ->title('Data Peserta Tidak Ditemukan')
                        ->body($message)
                        ->warning()
                        ->send();
                }
            } else {
                $this->apiResult = null;
                $message = $response['metaData']['message'] ?? 'Respons API tidak valid.';
                Notification::make()
                    ->title('Pencarian Gagal (Error BPJS)')
                    ->body($message)
                    ->danger()
                    ->send();
            }
            session(['pesertaResult' => $this->apiResult]);
            return redirect(static::getUrl());
        } catch (\Throwable $e) {
           $this->apiResult = null;
            Notification::make()
                ->title('Terjadi Kesalahan')
                ->body("Gagal terhubung ke BPJS: " . $e->getMessage())
                ->danger()
                ->send();
        }
    }
}
