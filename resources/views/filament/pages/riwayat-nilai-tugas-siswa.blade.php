<x-filament-panels::page>
    <x-filament::section heading="Filter riwayat">
        {{ $this->form }}
    </x-filament::section>

    @if (! $student || ! $semester)
        <x-filament::section style="margin-top: 1.5rem">
            <x-filament::empty-state
                heading="Pilih siswa dan semester"
                description="Tampilkan riwayat nilai tugas dengan memilih siswa dan semester terlebih dahulu."
                :icon="\Filament\Support\Icons\Heroicon::OutlinedAcademicCap"
            />
        </x-filament::section>
    @else
        <x-filament::section
            style="margin-top: 1.5rem"
            :heading="'Riwayat Nilai Tugas — '.$student->name"
            :description="$semester->academicYear?->name.' — '.$semester->name.' · NIS '.$student->nis"
        >
            @if ($records->isEmpty())
                <x-filament::empty-state
                    heading="Belum ada nilai tugas"
                    description="Tidak ada nilai tugas untuk siswa ini pada semester yang dipilih."
                />
            @else
                <div class="fi-ta-content-ctn">
                    <table class="fi-ta-table">
                        <thead>
                            <tr>
                                <th class="fi-ta-header-cell">Tenggat</th>
                                <th class="fi-ta-header-cell">Kelas</th>
                                <th class="fi-ta-header-cell">Mapel</th>
                                <th class="fi-ta-header-cell">Tugas</th>
                                <th class="fi-ta-header-cell">Komponen</th>
                                <th class="fi-ta-header-cell">Nilai</th>
                                <th class="fi-ta-header-cell">Maks</th>
                                <th class="fi-ta-header-cell">Catatan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($records as $score)
                                @php
                                    $assignment = $score->assignment;
                                    $teachingAssignment = $assignment?->teachingAssignment;
                                @endphp
                                <tr>
                                    <td class="fi-ta-cell" style="padding: .5rem .75rem; white-space: nowrap">
                                        {{ $assignment?->due_date?->format('d M Y') ?? '-' }}
                                    </td>
                                    <td class="fi-ta-cell" style="padding: .5rem .75rem">{{ $teachingAssignment?->schoolClass?->name ?? '-' }}</td>
                                    <td class="fi-ta-cell" style="padding: .5rem .75rem">{{ $teachingAssignment?->subject?->code ?? '-' }}</td>
                                    <td class="fi-ta-cell" style="padding: .5rem .75rem">{{ $assignment?->title ?? '-' }}</td>
                                    <td class="fi-ta-cell" style="padding: .5rem .75rem">{{ $assignment?->assessmentComponent?->name ?? '-' }}</td>
                                    <td class="fi-ta-cell" style="padding: .5rem .75rem">{{ number_format((float) $score->score, 2) }}</td>
                                    <td class="fi-ta-cell" style="padding: .5rem .75rem">{{ $assignment?->max_score ?? '-' }}</td>
                                    <td class="fi-ta-cell" style="padding: .5rem .75rem">{{ $score->notes ?: '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </x-filament::section>

        <x-filament::section style="margin-top: 1.5rem" heading="Rekap nilai tugas">
            <div style="display: grid; gap: 1rem; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr))">
                <div class="fi-wi-stats-overview-stat">
                    <div class="fi-wi-stats-overview-stat-label">Jumlah tugas dinilai</div>
                    <div class="fi-wi-stats-overview-stat-value" data-recap="jumlah-tugas">{{ $recap['jumlahTugas'] ?? 0 }}</div>
                </div>
                <div class="fi-wi-stats-overview-stat">
                    <div class="fi-wi-stats-overview-stat-label">Rata-rata nilai (skala 100)</div>
                    <div class="fi-wi-stats-overview-stat-value" data-recap="rata-rata">{{ $recap['rataRata'] ?? 0 }}</div>
                </div>
                <div class="fi-wi-stats-overview-stat">
                    <div class="fi-wi-stats-overview-stat-label">Nilai tertinggi</div>
                    <div class="fi-wi-stats-overview-stat-value" data-recap="tertinggi">{{ $recap['tertinggi'] ?? 0 }}</div>
                </div>
                <div class="fi-wi-stats-overview-stat">
                    <div class="fi-wi-stats-overview-stat-label">Nilai terendah</div>
                    <div class="fi-wi-stats-overview-stat-value" data-recap="terendah">{{ $recap['terendah'] ?? 0 }}</div>
                </div>
            </div>
        </x-filament::section>
    @endif
</x-filament-panels::page>
