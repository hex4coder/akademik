<x-filament-panels::page>
    <x-filament::section heading="Pilih penugasan mengajar">
        {{ $this->form }}
    </x-filament::section>

    @if (! $teachingAssignment)
        <x-filament::section style="margin-top: 1.5rem">
            <x-filament::empty-state
                heading="Pilih penugasan mengajar"
                description="Rekap nilai akhir akan ditampilkan berdasarkan komponen penilaian dan bobotnya."
                :icon="\Filament\Support\Icons\Heroicon::OutlinedChartBar"
            />
        </x-filament::section>
    @else
        <x-filament::section
            style="margin-top: 1.5rem"
            :heading="'Rekap Nilai — '.($teachingAssignment->schoolClass?->name ?? '-').' · '.($teachingAssignment->subject?->code ?? '-')"
            :description="($teachingAssignment->semester?->academicYear?->name ?? '-').' — '.($teachingAssignment->semester?->name ?? '-').' · Guru: '.($teachingAssignment->teacher?->name ?? '-').' · Total bobot aktif: '.$totalActiveWeight.'%'"
        >
            @if ($rows->isEmpty())
                <x-filament::empty-state
                    heading="Tidak ada siswa aktif"
                    description="Rombel ini belum memiliki siswa aktif."
                />
            @elseif ($components->isEmpty())
                <x-filament::empty-state
                    heading="Belum ada komponen penilaian aktif"
                    description="Buat komponen penilaian aktif terlebih dahulu agar nilai akhir dapat dihitung."
                />
            @else
                <div class="fi-ta-content-ctn">
                    <table class="fi-ta-table">
                        <thead>
                            <tr>
                                <th class="fi-ta-header-cell">NIS</th>
                                <th class="fi-ta-header-cell">Siswa</th>
                                @foreach ($components as $component)
                                    <th class="fi-ta-header-cell">
                                        {{ $component->name }}
                                        <div style="font-size: .75rem; font-weight: 400; opacity: .75">{{ $component->weight }}%</div>
                                    </th>
                                @endforeach
                                <th class="fi-ta-header-cell">Nilai Akhir</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($rows as $row)
                                <tr>
                                    <td class="fi-ta-cell" style="padding: .5rem .75rem; white-space: nowrap">{{ $row['student']->nis }}</td>
                                    <td class="fi-ta-cell" style="padding: .5rem .75rem">{{ $row['student']->name }}</td>
                                    @foreach ($components as $component)
                                        @php
                                            $value = $row['components'][$component->id] ?? null;
                                        @endphp
                                        <td class="fi-ta-cell" style="padding: .5rem .75rem">
                                            {{ $value === null ? '-' : number_format((float) $value, 2) }}
                                        </td>
                                    @endforeach
                                    <td class="fi-ta-cell" style="padding: .5rem .75rem; font-weight: 600">
                                        {{ number_format((float) $row['final_score'], 2) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <p style="margin-top: .75rem; font-size: .875rem; opacity: .8">
                    Nilai komponen = rata-rata (nilai / nilai maksimal × 100) dari tugas pada komponen tersebut. Nilai akhir = jumlah (nilai komponen × bobot / 100).
                </p>
            @endif
        </x-filament::section>
    @endif
</x-filament-panels::page>
