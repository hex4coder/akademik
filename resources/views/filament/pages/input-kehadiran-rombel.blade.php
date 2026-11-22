<x-filament-panels::page>
    <x-filament::section heading="Pilih sesi absensi">
        {{ $this->form }}
    </x-filament::section>

    @if (! $session)
        <x-filament::section style="margin-top: 1.5rem">
            <x-filament::empty-state
                heading="Pilih sesi absensi terlebih dahulu"
                description="Setelah sesi dipilih, daftar siswa aktif di rombel akan muncul untuk diisi kehadirannya."
                :icon="\Filament\Support\Icons\Heroicon::OutlinedClipboardDocumentCheck"
            />
        </x-filament::section>
    @else
        <x-filament::section
            style="margin-top: 1.5rem"
            :heading="'Input Kehadiran — '.($session->schedule?->teachingAssignment?->schoolClass?->name ?? '-').' · '.($session->schedule?->teachingAssignment?->subject?->code ?? '-')"
            :description="($session->date?->format('d M Y') ?? '-').' · '.($session->schedule?->teachingAssignment?->semester?->academicYear?->name ?? '-').' — '.($session->schedule?->teachingAssignment?->semester?->name ?? '-')"
        >
            @if ($students->isEmpty())
                <x-filament::empty-state
                    heading="Tidak ada siswa aktif"
                    description="Rombel pada sesi ini belum memiliki siswa dengan status aktif."
                />
            @else
                <form wire:submit="save">
                    <div class="fi-ta-content-ctn">
                        <table class="fi-ta-table">
                            <thead>
                                <tr>
                                    <th class="fi-ta-header-cell">NIS</th>
                                    <th class="fi-ta-header-cell">Siswa</th>
                                    <th class="fi-ta-header-cell" style="width: 220px">Status Kehadiran</th>
                                    <th class="fi-ta-header-cell">Catatan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($students as $student)
                                    <tr wire:key="attendance-row-{{ $student->id }}">
                                        <td class="fi-ta-cell" style="padding: .5rem .75rem; white-space: nowrap">{{ $student->nis }}</td>
                                        <td class="fi-ta-cell" style="padding: .5rem .75rem">{{ $student->name }}</td>
                                        <td class="fi-ta-cell" style="padding: .5rem .75rem">
                                            <select
                                                wire:model.blur="statuses.{{ $student->id }}"
                                                wire:key="attendance-status-{{ $student->id }}"
                                                class="fi-input w-full"
                                            >
                                                @foreach ($statusOptions as $status)
                                                    <option value="{{ $status->id }}">{{ $status->name }} ({{ $status->numeric_value }})</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td class="fi-ta-cell" style="padding: .5rem .75rem">
                                            <input
                                                type="text"
                                                wire:model.blur="notes.{{ $student->id }}"
                                                wire:key="attendance-note-{{ $student->id }}"
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
