<x-filament-panels::page>
    <style>
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
        @media screen {
            .print-only { display: none !important; }
        }
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
                    description="Tampilkan riwayat nilai tugas dengan memilih siswa dan semester terlebih dahulu."
                    :icon="\Filament\Support\Icons\Heroicon::OutlinedAcademicCap"
                />
            </x-filament::section>
        </div>
    @else
        <div class="print-only" style="margin-bottom: 1rem;">
            <h1 style="font-size: 20px; font-weight: 700; margin: 0 0 4px 0;">Riwayat Nilai Tugas Siswa</h1>
            <div style="font-size: 13px; color: #374151;">
                {{ $student->name }} · NIS {{ $student->nis }} · {{ $semester->academicYear?->name }} — {{ $semester->name }}
            </div>
        </div>

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
                                    <td class="fi-ta-cell" style="padding: .5rem .75rem; text-align: right;">{{ number_format((float) $score->score, 2) }}</td>
                                    <td class="fi-ta-cell" style="padding: .5rem .75rem; text-align: right;">{{ $assignment?->max_score ?? '-' }}</td>
                                    <td class="fi-ta-cell" style="padding: .5rem .75rem">{{ $score->notes ?: '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </x-filament::section>

        <x-filament::section style="margin-top: 1.5rem" heading="Rekap nilai tugas">
            <div class="rekap-stats">
                <div class="rekap-stat-card">
                    <div class="rekap-stat-label">Jumlah tugas dinilai</div>
                    <div class="rekap-stat-value" data-recap="jumlah-tugas">{{ $recap['jumlahTugas'] ?? 0 }}</div>
                </div>
                <div class="rekap-stat-card">
                    <div class="rekap-stat-label">Rata-rata nilai (skala 100)</div>
                    <div class="rekap-stat-value" data-recap="rata-rata">{{ $recap['rataRata'] ?? 0 }}</div>
                </div>
                <div class="rekap-stat-card">
                    <div class="rekap-stat-label">Nilai tertinggi</div>
                    <div class="rekap-stat-value" data-recap="tertinggi">{{ $recap['tertinggi'] ?? 0 }}</div>
                </div>
                <div class="rekap-stat-card">
                    <div class="rekap-stat-label">Nilai terendah</div>
                    <div class="rekap-stat-value" data-recap="terendah">{{ $recap['terendah'] ?? 0 }}</div>
                </div>
            </div>
        </x-filament::section>
    @endif
</x-filament-panels::page>
