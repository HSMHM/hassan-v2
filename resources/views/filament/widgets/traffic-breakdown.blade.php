<x-filament-widgets::widget>
    <div class="grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-4">

        {{-- Browsers --}}
        <div class="ha-breakdown-card">
            <div class="ha-breakdown-header">
                <x-heroicon-o-globe-alt class="ha-breakdown-icon" />
                <h3>{{ $isAr ? 'المتصفحات' : 'Browsers' }}</h3>
            </div>
            <div class="ha-breakdown-list">
                @forelse($browsers as $name => $count)
                    @php $pct = $totalBrowsers > 0 ? round(($count / $totalBrowsers) * 100) : 0; @endphp
                    <div class="ha-breakdown-item">
                        <div class="ha-breakdown-item-info">
                            <span class="ha-breakdown-name">{{ $name }}</span>
                            <span class="ha-breakdown-count">{{ number_format($count) }}</span>
                        </div>
                        <div class="ha-breakdown-bar">
                            <div class="ha-breakdown-bar-fill ha-fill-info" style="width: {{ $pct }}%"></div>
                        </div>
                        <span class="ha-breakdown-pct">{{ $pct }}%</span>
                    </div>
                @empty
                    <p class="ha-breakdown-empty">{{ $isAr ? 'لا توجد بيانات' : 'No data' }}</p>
                @endforelse
            </div>
        </div>

        {{-- Devices --}}
        <div class="ha-breakdown-card">
            <div class="ha-breakdown-header">
                <x-heroicon-o-device-phone-mobile class="ha-breakdown-icon" />
                <h3>{{ $isAr ? 'الأجهزة' : 'Devices' }}</h3>
            </div>
            <div class="ha-breakdown-list">
                @forelse($devices as $name => $count)
                    @php $pct = $totalDevices > 0 ? round(($count / $totalDevices) * 100) : 0; @endphp
                    <div class="ha-breakdown-item">
                        <div class="ha-breakdown-item-info">
                            <span class="ha-breakdown-name">
                                @if($name === 'Desktop') 🖥️
                                @elseif($name === 'Mobile') 📱
                                @elseif($name === 'Tablet') 📟
                                @elseif($name === 'Bot') 🤖
                                @endif
                                {{ $name }}
                            </span>
                            <span class="ha-breakdown-count">{{ number_format($count) }}</span>
                        </div>
                        <div class="ha-breakdown-bar">
                            <div class="ha-breakdown-bar-fill ha-fill-warning" style="width: {{ $pct }}%"></div>
                        </div>
                        <span class="ha-breakdown-pct">{{ $pct }}%</span>
                    </div>
                @empty
                    <p class="ha-breakdown-empty">{{ $isAr ? 'لا توجد بيانات' : 'No data' }}</p>
                @endforelse
            </div>
        </div>

        {{-- Platforms --}}
        <div class="ha-breakdown-card">
            <div class="ha-breakdown-header">
                <x-heroicon-o-cpu-chip class="ha-breakdown-icon" />
                <h3>{{ $isAr ? 'أنظمة التشغيل' : 'Platforms' }}</h3>
            </div>
            <div class="ha-breakdown-list">
                @forelse($platforms as $name => $count)
                    @php $pct = $totalPlatforms > 0 ? round(($count / $totalPlatforms) * 100) : 0; @endphp
                    <div class="ha-breakdown-item">
                        <div class="ha-breakdown-item-info">
                            <span class="ha-breakdown-name">{{ $name }}</span>
                            <span class="ha-breakdown-count">{{ number_format($count) }}</span>
                        </div>
                        <div class="ha-breakdown-bar">
                            <div class="ha-breakdown-bar-fill ha-fill-success" style="width: {{ $pct }}%"></div>
                        </div>
                        <span class="ha-breakdown-pct">{{ $pct }}%</span>
                    </div>
                @empty
                    <p class="ha-breakdown-empty">{{ $isAr ? 'لا توجد بيانات' : 'No data' }}</p>
                @endforelse
            </div>
        </div>

        {{-- Referers --}}
        <div class="ha-breakdown-card">
            <div class="ha-breakdown-header">
                <x-heroicon-o-arrow-top-right-on-square class="ha-breakdown-icon" />
                <h3>{{ $isAr ? 'مصادر الزيارات' : 'Top Referers' }}</h3>
            </div>
            <div class="ha-breakdown-list">
                @forelse($topReferers as $url => $count)
                    <div class="ha-breakdown-item">
                        <div class="ha-breakdown-item-info">
                            <span class="ha-breakdown-name ha-breakdown-referer" title="{{ $url }}">
                                {{ Str::limit(parse_url($url, PHP_URL_HOST) ?: $url, 25) }}
                            </span>
                            <span class="ha-breakdown-count">{{ number_format($count) }}</span>
                        </div>
                    </div>
                @empty
                    <p class="ha-breakdown-empty">{{ $isAr ? 'جميع الزيارات مباشرة' : 'All visits are direct' }}</p>
                @endforelse
            </div>
        </div>

    </div>
</x-filament-widgets::widget>
