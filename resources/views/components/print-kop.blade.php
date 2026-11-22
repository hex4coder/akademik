@props(['schoolProfile' => null, 'title' => '', 'subtitle' => null])

<div class="print-kop">
    <div class="print-kop-inner">
        @if ($schoolProfile?->logo_path)
            <div class="print-kop-logo">
                <img src="{{ asset('storage/'.$schoolProfile->logo_path) }}" alt="Logo sekolah">
            </div>
        @endif
        <div class="print-kop-text">
            <div class="print-kop-school">{{ $schoolProfile?->name ?? config('app.name') }}</div>
            <div class="print-kop-meta">
                @if ($schoolProfile?->npsn)
                    NPSN: {{ $schoolProfile->npsn }}
                @endif
                @if ($schoolProfile?->address)
                    <span>{{ $schoolProfile->npsn ? ' · ' : '' }}{{ $schoolProfile->address }}</span>
                @endif
            </div>
            <div class="print-kop-meta">
                {{ collect([$schoolProfile?->phone ? 'Telp. '.$schoolProfile->phone : null, $schoolProfile?->email, $schoolProfile?->website])->filter()->implode(' · ') }}
            </div>
        </div>
    </div>
    <div class="print-kop-title">{{ $title }}</div>
    @if ($subtitle)
        <div class="print-kop-subtitle">{{ $subtitle }}</div>
    @endif
</div>
