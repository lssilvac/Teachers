<x-filament-panels::page>
    {{-- https://filamentphp.com/docs/4.x/components/overview --}}

    <form wire:submit="save">
        {{ $this->form }}

        <x-filament::button style="margin-top: 20px" type="submit" color="success" outlined>
            Salvar
        </x-filament::button>

    </form>


</x-filament-panels::page>
