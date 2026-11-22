<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            Peran saat ini
        </x-slot>

        <x-filament::badge :color="$roleColor">
            {{ $roleLabel }}
        </x-filament::badge>
    </x-filament::section>
</x-filament-widgets::widget>
