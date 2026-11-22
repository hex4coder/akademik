<x-filament-panels::page>
    <x-filament::section heading="Pilih tugas">
        {{ $this->form }}
    </x-filament::section>

    @if (! $assignment)
        <x-filament::section style="margin-top: 1.5rem">
            <x-filament::empty-state
                heading="Pilih tugas terlebih dahulu"
                description="Setelah tugas dipilih, daftar siswa aktif di rombel akan muncul untuk diisi nilainya."
                :icon="\Filament\Support\Icons\Heroicon::OutlinedTableCells"
            />
        </x-filament::section>
    @else
        <x-filament::section
            style="margin-top: 1.5rem"
            :heading="'Input Nilai — '.$assignment->title"
            :description="($assignment->teachingAssignment?->schoolClass?->name ?? '-').' · '.($assignment->teachingAssignment?->subject?->code ?? '-').' · Nilai maksimal '.$assignment->max_score"
        >
            @if ($students->isEmpty())
                <x-filament::empty-state
                    heading="Tidak ada siswa aktif"
                    description="Rombel pada tugas ini belum memiliki siswa dengan status aktif."
                />
            @else
                <form wire:submit="save">
                    <div class="fi-ta-content-ctn">
                        <table class="fi-ta-table">
                            <thead>
                                <tr>
                                    <th class="fi-ta-header-cell">NIS</th>
                                    <th class="fi-ta-header-cell">Siswa</th>
                                    <th class="fi-ta-header-cell" style="width: 140px">Nilai</th>
                                    <th class="fi-ta-header-cell">Catatan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($students as $student)
                                    <tr wire:key="score-row-{{ $student->id }}">
                                        <td class="fi-ta-cell" style="padding: .5rem .75rem; white-space: nowrap">{{ $student->nis }}</td>
                                        <td class="fi-ta-cell" style="padding: .5rem .75rem">{{ $student->name }}</td>
                                        <td class="fi-ta-cell" style="padding: .5rem .75rem">
                                            <input
                                                type="number"
                                                min="0"
                                                max="{{ $assignment->max_score }}"
                                                step="0.01"
                                                wire:model.blur="scores.{{ $student->id }}"
                                                wire:key="score-input-{{ $student->id }}"
                                                class="fi-input w-full"
                                                style="max-width: 120px"
                                            >
                                        </td>
                                        <td class="fi-ta-cell" style="padding: .5rem .75rem">
                                            <input
                                                type="text"
                                                wire:model.blur="notes.{{ $student->id }}"
                                                wire:key="note-input-{{ $student->id }}"
                                                class="fi-input w-full"
                                                placeholder="Opsional"
                                            >
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    @error('save')
                        <p style="margin-top: .75rem; color: rgb(220 38 38); font-size: .875rem">{{ $message }}</p>
                    @enderror

                    <div style="margin-top: 1rem; display: flex; justify-content: flex-end">
                        <x-filament::button type="submit">
                            Simpan Semua
                        </x-filament::button>
                    </div>
                </form>
            @endif
        </x-filament::section>
    @endif
</x-filament-panels::page>
