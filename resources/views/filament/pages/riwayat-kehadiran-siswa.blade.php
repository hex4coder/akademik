<x-filament-panels::page>
    <style>
        
        .print-kop { display: none; margin-bottom: 16px; }
        .print-kop-inner { display: grid; grid-template-columns: 72px 1fr; gap: 12px; align-items: center; padding-bottom: 10px; border-bottom: 3px double #111827; }
        .print-kop-logo img { width: 72px; height: 72px; object-fit: contain; }
        .print-kop-text { text-align: center; }
        .print-kop-school { font-size: 20px; font-weight: 700; text-transform: uppercase; line-height: 1.2; }
        .print-kop-meta { font-size: 12px; color: #374151; margin-top: 2px; }
        .print-kop-title { text-align: center; font-size: 15px; font-weight: 700; margin-top: 10px; text-transform: uppercase; }
        .print-kop-subtitle { text-align: center; font-size: 12px; color: #374151; margin-top: 2px; }
        @media print {
            .print-kop { display: block !important; }
        }

        @media print {
            body { background: #fff !important; }
            .fi-sidebar, .fi-topbar-ctn, .fi-sidebar-close-overlay,
            .fi-header-actions-ctn, .fi-page-header-actions,
            .rekap-filter-section, .fi-no-print { display: none !important; }
            .fi-main, .fi-main-ctn, .fi-page, .fi-body { margin: 0 !important; padding: 0 !important; width: 100% !important; max-width: 100% !important; }
            .fi-section { box-shadow: none !important; border: 1px solid #d1d5db !important; break-inside: avoid; }
            .fi-ta-content-ctn { overflow: visible !important; }
            .fi-ta-table { width: 100% !important; border-collapse: collapse !important; font-size: 12px !important; }
            .fi-ta-header-cell, .fi-ta-cell { border: 1px solid #d1d5db !important; padding: 6px 8px !important; color: #111827 !important; background: #fff !important; }
            .fi-ta-header-cell { background: #f3f4f6 !important; font-weight: 600 !important; }
            .print-only { display: block !important; }
            .rekap-stats { display: grid !important; grid-template-columns: repeat(4, minmax(0, 1fr)) !important; gap: 12px !important; }
            .rekap-stat-card { border: 1px solid #d1d5db !important; border-radius: 8px !important; padding: 10px 12px !important; background: #fff !important; }
        }
        @media screen { .print-only { display: none !important; } }
        .rekap-stats { display: grid; gap: 1rem; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); }
        .rekap-stat-card { border: 1px solid rgba(0,0,0,.08); border-radius: .75rem; padding: 1rem; }
        .dark .rekap-stat-card { border-color: rgba(255,255,255,.12); }
        .rekap-stat-label { font-size: .875rem; opacity: .75; }
        .rekap-stat-value { font-size: 1.5rem; font-weight: 700; line-height: 1.2; margin-top: .25rem; }
    </style>

    <div class="rekap-filter-section">
        <x-filament::section heading="Filter riwayat">
            {{ $this->form }}
        </x-filament::section>
    </div>

    @if (! $student || ! $semester)
        <div class="fi-no-print">
            <x-filament::section style="margin-top: 1.5rem">
                <x-filament::empty-state
                    heading="Pilih siswa dan semester"
                    description="Tampilkan riwayat kehadiran dengan memilih siswa dan semester terlebih dahulu."
                    :icon="\Filament\Support\Icons\Heroicon::OutlinedClipboardDocumentList"
                />
            </x-filament::section>
        </div>
    @else
        <x-print-kop
            :school-profile="$schoolProfile"
            title="Riwayat Kehadiran Siswa"
            :subtitle="$student->name . ' · NIS ' . $student->nis . ' · ' . ($semester->academicYear?->name ?? '-') . ' — ' . ($semester->name ?? '-')"
        />

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
                                    <td class="fi-ta-cell" style="padding: .5rem .75rem">{{ $assignment?->schoolClass?->name ?? '-' }}</td>
                                    <td class="fi-ta-cell" style="padding: .5rem .75rem">{{ $assignment?->subject?->code ?? '-' }}</td>
                                    <td class="fi-ta-cell" style="padding: .5rem .75rem">
                                        <x-filament::badge :color="$color">{{ $status?->name ?? '-' }}</x-filament::badge>
                                    </td>
                                    <td class="fi-ta-cell" style="padding: .5rem .75rem; text-align: right;">{{ $point }}</td>
                                    <td class="fi-ta-cell" style="padding: .5rem .75rem">{{ $attendance->notes ?: '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </x-filament::section>

        <x-filament::section style="margin-top: 1.5rem" heading="Rekap kehadiran">
            <div class="rekap-stats">
                <div class="rekap-stat-card">
                    <div class="rekap-stat-label">Jumlah pertemuan</div>
                    <div class="rekap-stat-value" data-recap="jumlah-pertemuan">{{ $recap['jumlahPertemuan'] }}</div>
                </div>
                <div class="rekap-stat-card">
                    <div class="rekap-stat-label">Total poin</div>
                    <div class="rekap-stat-value" data-recap="total-poin">{{ $recap['totalPoin'] }}</div>
                </div>
                <div class="rekap-stat-card">
                    <div class="rekap-stat-label">Rata-rata poin kehadiran</div>
                    <div class="rekap-stat-value" data-recap="rata-rata">{{ $recap['rataRata'] }}</div>
                </div>
                <div class="rekap-stat-card">
                    <div class="rekap-stat-label">Persentase kehadiran</div>
                    <div class="rekap-stat-value" data-recap="persentase-kehadiran">{{ $recap['persentaseKehadiran'] }}%</div>
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
                                <td class="fi-ta-cell" style="padding: .5rem .75rem; text-align: right;">{{ $status['numeric_value'] }}</td>
                                <td class="fi-ta-cell" style="padding: .5rem .75rem; text-align: right;">{{ $status['count'] }}</td>
                                <td class="fi-ta-cell" style="padding: .5rem .75rem; text-align: right;">{{ $status['persentase'] }}%</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-filament::section>
    @endif
</x-filament-panels::page>
