<x-filament-panels::page>
    <x-filament::section heading="Filter riwayat">
        {{ $this->form }}
    </x-filament::section>

    @if (! $student || ! $semester)
        <x-filament::section style="margin-top: 1.5rem">
            <x-filament::empty-state
                heading="Pilih siswa dan semester"
                description="Tampilkan riwayat kehadiran dengan memilih siswa dan semester terlebih dahulu."
                :icon="\Filament\Support\Icons\Heroicon::OutlinedClipboardDocumentList"
            />
        </x-filament::section>
    @else
        <x-filament::section
            style="margin-top: 1.5rem"
            :heading="'Riwayat Kehadiran — '.$student->name"
            :description="$semester->academicYear->name.' — '.$semester->name.' · NIS '.$student->nis"
        >
            @if ($records->isEmpty())
                <x-filament::empty-state
                    heading="Belum ada absensi"
                    description="Tidak ada catatan kehadiran untuk siswa ini pada semester yang dipilih."
                />
            @else
                <div class="fi-ta-content-ctn">
                    <table class="fi-ta-table">
                        <thead>
                            <tr>
                                <th class="fi-ta-header-cell">Tanggal</th>
                                <th class="fi-ta-header-cell">Kelas</th>
                                <th class="fi-ta-header-cell">Mapel</th>
                                <th class="fi-ta-header-cell">Status</th>
                                <th class="fi-ta-header-cell">Poin</th>
                                <th class="fi-ta-header-cell">Catatan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($records as $attendance)
                                @php
                                    $session = $attendance->attendanceSession;
                                    $assignment = $session?->schedule?->teachingAssignment;
                                    $status = $attendance->attendanceStatus;
                                    $point = (int) ($status?->numeric_value ?? 0);
                                    $color = match (true) {
                                        $point >= 75 => 'success',
                                        $point > 0 => 'warning',
                                        default => 'danger',
                                    };
                                @endphp
                                <tr>
                                    <td class="fi-ta-cell" style="padding: .5rem .75rem; white-space: nowrap">
                                        {{ $session?->date?->format('d M Y') ?? '-' }}
                                    </td>
                                    <td class="fi-ta-cell" style="padding: .5rem .75rem">
                                        {{ $assignment?->schoolClass?->name ?? '-' }}
                                    </td>
                                    <td class="fi-ta-cell" style="padding: .5rem .75rem">
                                        {{ $assignment?->subject?->code ?? '-' }}
                                    </td>
                                    <td class="fi-ta-cell" style="padding: .5rem .75rem">
                                        <x-filament::badge :color="$color">
                                            {{ $status?->name ?? '-' }}
                                        </x-filament::badge>
                                    </td>
                                    <td class="fi-ta-cell" style="padding: .5rem .75rem">{{ $point }}</td>
                                    <td class="fi-ta-cell" style="padding: .5rem .75rem">{{ $attendance->notes ?: '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </x-filament::section>

        <x-filament::section style="margin-top: 1.5rem" heading="Rekap kehadiran">
            <div style="display: grid; gap: 1rem; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr))">
                <div class="fi-wi-stats-overview-stat">
                    <div class="fi-wi-stats-overview-stat-label">Jumlah pertemuan</div>
                    <div class="fi-wi-stats-overview-stat-value" data-recap="jumlah-pertemuan">{{ $recap['jumlahPertemuan'] }}</div>
                </div>
                <div class="fi-wi-stats-overview-stat">
                    <div class="fi-wi-stats-overview-stat-label">Total poin</div>
                    <div class="fi-wi-stats-overview-stat-value" data-recap="total-poin">{{ $recap['totalPoin'] }}</div>
                </div>
                <div class="fi-wi-stats-overview-stat">
                    <div class="fi-wi-stats-overview-stat-label">Rata-rata poin kehadiran</div>
                    <div class="fi-wi-stats-overview-stat-value" data-recap="rata-rata">{{ $recap['rataRata'] }}</div>
                </div>
                <div class="fi-wi-stats-overview-stat">
                    <div class="fi-wi-stats-overview-stat-label">Persentase kehadiran</div>
                    <div class="fi-wi-stats-overview-stat-value" data-recap="persentase-kehadiran">{{ $recap['persentaseKehadiran'] }}%</div>
                </div>
            </div>

            <div class="fi-ta-content-ctn" style="margin-top: 1.5rem">
                <table class="fi-ta-table">
                    <thead>
                        <tr>
                            <th class="fi-ta-header-cell">Status</th>
                            <th class="fi-ta-header-cell">Kode</th>
                            <th class="fi-ta-header-cell">Nilai</th>
                            <th class="fi-ta-header-cell">Jumlah</th>
                            <th class="fi-ta-header-cell">Persentase</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($recap['statuses'] as $status)
                            <tr>
                                <td class="fi-ta-cell" style="padding: .5rem .75rem">{{ $status['name'] }}</td>
                                <td class="fi-ta-cell" style="padding: .5rem .75rem">{{ $status['code'] }}</td>
                                <td class="fi-ta-cell" style="padding: .5rem .75rem">{{ $status['numeric_value'] }}</td>
                                <td class="fi-ta-cell" style="padding: .5rem .75rem">{{ $status['count'] }}</td>
                                <td class="fi-ta-cell" style="padding: .5rem .75rem">{{ $status['persentase'] }}%</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-filament::section>
    @endif
</x-filament-panels::page>
