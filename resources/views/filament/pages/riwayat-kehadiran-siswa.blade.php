<x-filament-panels::page>
    <x-filament::section heading="Filter riwayat">
        {{ $this->form }}
    </x-filament::section>

    @if (! $student || ! $semester)
        <x-filament::section class="mt-6">
            <x-filament::empty-state
                heading="Pilih siswa dan semester"
                description="Tampilkan riwayat kehadiran dengan memilih siswa dan semester terlebih dahulu."
                :icon="\Filament\Support\Icons\Heroicon::OutlinedClipboardDocumentList"
            />
        </x-filament::section>
    @else
        <x-filament::section
            class="mt-6"
            :heading="'Riwayat Kehadiran — '.$student->name"
            :description="$semester->academicYear->name.' — '.$semester->name.' · NIS '.$student->nis"
        >
            @if ($records->isEmpty())
                <x-filament::empty-state
                    heading="Belum ada absensi"
                    description="Tidak ada catatan kehadiran untuk siswa ini pada semester yang dipilih."
                />
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-200 dark:border-white/10">
                                <th class="px-3 py-2 text-left font-medium">Tanggal</th>
                                <th class="px-3 py-2 text-left font-medium">Kelas</th>
                                <th class="px-3 py-2 text-left font-medium">Mapel</th>
                                <th class="px-3 py-2 text-left font-medium">Status</th>
                                <th class="px-3 py-2 text-left font-medium">Poin</th>
                                <th class="px-3 py-2 text-left font-medium">Catatan</th>
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
                                <tr class="border-b border-gray-100 dark:border-white/5">
                                    <td class="px-3 py-2 whitespace-nowrap">
                                        {{ $session?->date?->format('d M Y') ?? '-' }}
                                    </td>
                                    <td class="px-3 py-2">
                                        {{ $assignment?->schoolClass?->name ?? '-' }}
                                    </td>
                                    <td class="px-3 py-2">
                                        {{ $assignment?->subject?->code ?? '-' }}
                                    </td>
                                    <td class="px-3 py-2">
                                        <x-filament::badge :color="$color">
                                            {{ $status?->name ?? '-' }}
                                        </x-filament::badge>
                                    </td>
                                    <td class="px-3 py-2">{{ $point }}</td>
                                    <td class="px-3 py-2">{{ $attendance->notes ?: '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </x-filament::section>

        <x-filament::section class="mt-6" heading="Rekap kehadiran">
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div class="rounded-lg bg-gray-50 p-4 dark:bg-white/5">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Jumlah pertemuan</p>
                    <p class="text-2xl font-semibold" data-recap="jumlah-pertemuan">{{ $recap['jumlahPertemuan'] }}</p>
                </div>
                <div class="rounded-lg bg-gray-50 p-4 dark:bg-white/5">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Total poin</p>
                    <p class="text-2xl font-semibold" data-recap="total-poin">{{ $recap['totalPoin'] }}</p>
                </div>
                <div class="rounded-lg bg-gray-50 p-4 dark:bg-white/5">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Rata-rata poin kehadiran</p>
                    <p class="text-2xl font-semibold" data-recap="rata-rata">{{ $recap['rataRata'] }}</p>
                </div>
                <div class="rounded-lg bg-gray-50 p-4 dark:bg-white/5">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Persentase kehadiran</p>
                    <p class="text-2xl font-semibold" data-recap="persentase-kehadiran">{{ $recap['persentaseKehadiran'] }}%</p>
                </div>
            </div>

            <div class="mt-6 overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-200 dark:border-white/10">
                            <th class="px-3 py-2 text-left font-medium">Status</th>
                            <th class="px-3 py-2 text-left font-medium">Kode</th>
                            <th class="px-3 py-2 text-left font-medium">Nilai</th>
                            <th class="px-3 py-2 text-left font-medium">Jumlah</th>
                            <th class="px-3 py-2 text-left font-medium">Persentase</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($recap['statuses'] as $status)
                            <tr class="border-b border-gray-100 dark:border-white/5">
                                <td class="px-3 py-2">{{ $status['name'] }}</td>
                                <td class="px-3 py-2">{{ $status['code'] }}</td>
                                <td class="px-3 py-2">{{ $status['numeric_value'] }}</td>
                                <td class="px-3 py-2">{{ $status['count'] }}</td>
                                <td class="px-3 py-2">{{ $status['persentase'] }}%</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-filament::section>
    @endif
</x-filament-panels::page>
