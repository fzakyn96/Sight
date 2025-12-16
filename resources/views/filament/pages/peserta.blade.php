<x-filament-panels::page>
    <form wire:submit.prevent="submitForm">
        {{ $this->form }}
    </form>

    @if ($apiResult)
        {{ $this->searchResult }}
    @endif
</x-filament-panels::page>