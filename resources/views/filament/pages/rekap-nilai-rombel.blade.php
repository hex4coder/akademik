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
        <x-filament::section heading="Pilih penugasan mengajar">
            {{ $this->form }}
        </x-filament::section>
    </div>

    @if (! $teachingAssignment)
        <div class="fi-no-print">
            <x-filament::section style="margin-top: 1.5rem">
                <x-filament::empty-state
                    heading="Pilih penugasan mengajar"
                    description="Rekap nilai akhir akan ditampilkan berdasarkan komponen penilaian dan bobotnya."
                    :icon="\Filament\Support\Icons\Heroicon::OutlinedChartBar"
                />
            </x-filament::section>
        </div>
    @else
        <div class="print-only" style="margin-bottom: 1rem;">
            <h1 style="font-size: 20px; font-weight: 700; margin: 0 0 4px 0;">Rekap Nilai Rombel</h1>
            <div style="font-size: 13px; color: #374151;">
                {{ $teachingAssignment->schoolClass?->name ?? '-' }} · {{ $teachingAssignment->subject?->code ?? '-' }} ·
                {{ $teachingAssignment->semester?->academicYear?->name ?? '-' }} {{ $teachingAssignment->semester?->name ?? '-' }} ·
                Guru: {{ $teachingAssignment->teacher?->name ?? '-' }} · Total bobot aktif: {{ $totalActiveWeight }}%
            </div>
        </div>

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
                                        <td class="fi-ta-cell" style="padding: .5rem .75rem; text-align: right;">
                                            {{ $value === null ? '-' : number_format((float) $value, 2) }}
                                        </td>
                                    @endforeach
                                    <td class="fi-ta-cell" style="padding: .5rem .75rem; font-weight: 700; text-align: right;">
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
