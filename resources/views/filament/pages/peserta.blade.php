<x-filament-panels::page>
    <form wire:submit.prevent="submitForm">
        {{ $this->form }}
    </form>

    @if ($apiResult)
        <x-filament::section class="mt-6">
            <x-slot name="heading">
                Hasil Pencarian Peserta
            </x-slot>

                @php
                    $peserta = $apiResult;
                @endphp

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="space-y-2">
                        <p><strong>Nama:</strong> {{ $peserta['nama'] }}</p>
                        <p><strong>No Kartu:</strong> {{ $peserta['noKartu'] }}</p>
                        <p><strong>NIK:</strong> {{ $peserta['nik'] }}</p>
                        <p><strong>Tgl Lahir:</strong> {{ \Carbon\Carbon::parse($peserta['tglLahir'])->format('d M Y') }}</p>
                        <p><strong>Jenis Kelamin:</strong> {{ $peserta['sex'] === 'L' ? 'Laki-laki' : 'Perempuan' }}</p>
                    </div>
                    <div class="space-y-2">
                        <p><strong>Status Peserta:</strong> <span class="font-semibold">{{ $peserta['statusPeserta']['keterangan'] }}</span></p>
                        <p><strong>Jenis Peserta:</strong> {{ $peserta['jenisPeserta']['keterangan'] }}</p>
                        <p><strong>Hak Kelas:</strong> {{ $peserta['hakKelas']['keterangan'] }}</p>
                        <p><strong>Provider Utama:</strong> {{ $peserta['provUmum']['nmProvider'] }} ({{ $peserta['provUmum']['kdProvider'] }})</p>
                        <p><strong>Umur Saat Pelayanan:</strong> {{ $peserta['umur']['umurSaatPelayanan'] }}</p>
                    </div>
                </div>

                <h4 class="mt-6 text-lg font-semibold border-t pt-4">Informasi Lain</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <p><strong>Tgl TMT:</strong> {{ $peserta['tglTMT'] }}</p>
                    <p><strong>Tgl TAT:</strong> {{ $peserta['tglTAT'] }}</p>
                </div>
        </x-filament::section>
    @endif
</x-filament-panels::page>