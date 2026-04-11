<x-filament-widgets::widget>
    <div class="ha-welcome-hero">
        <div class="ha-welcome-hero__glow"></div>
        <div class="ha-welcome-hero__content">
            <div class="ha-welcome-hero__main">
                <span class="ha-welcome-hero__date">
                    <x-heroicon-o-calendar-days class="ha-welcome-hero__date-icon" />
                    {{ $date }}
                </span>
                <h1 class="ha-welcome-hero__title">
                    {{ $greeting }}{{ $isAr ? '، ' : ', ' }}{{ $name }}
                    <span class="ha-welcome-hero__wave">👋</span>
                </h1>
                <p class="ha-welcome-hero__subtitle">
                    {{ $isAr ? 'إليك ملخص سريع عن حالة الموقع اليوم' : "Here's a quick overview of your site today" }}
                </p>
            </div>

            <div class="ha-welcome-hero__quick">
                <a href="{{ \App\Filament\Resources\ContactMessages\ContactMessageResource::getUrl() }}" class="ha-welcome-hero__chip ha-welcome-hero__chip--danger">
                    <x-heroicon-o-envelope class="ha-welcome-hero__chip-icon" />
                    <div>
                        <div class="ha-welcome-hero__chip-value">{{ number_format($unreadMessages) }}</div>
                        <div class="ha-welcome-hero__chip-label">{{ $isAr ? 'رسائل جديدة' : 'New Messages' }}</div>
                    </div>
                </a>

                <div class="ha-welcome-hero__chip ha-welcome-hero__chip--success">
                    <x-heroicon-o-eye class="ha-welcome-hero__chip-icon" />
                    <div>
                        <div class="ha-welcome-hero__chip-value">{{ number_format($todayVisits) }}</div>
                        <div class="ha-welcome-hero__chip-label">{{ $isAr ? 'زوار اليوم' : 'Today Visitors' }}</div>
                    </div>
                </div>

                @if($pendingNews > 0)
                    <a href="{{ \App\Filament\Resources\NewsPosts\NewsPostResource::getUrl() }}" class="ha-welcome-hero__chip ha-welcome-hero__chip--warning">
                        <x-heroicon-o-clock class="ha-welcome-hero__chip-icon" />
                        <div>
                            <div class="ha-welcome-hero__chip-value">{{ number_format($pendingNews) }}</div>
                            <div class="ha-welcome-hero__chip-label">{{ $isAr ? 'بانتظار الموافقة' : 'Pending Review' }}</div>
                        </div>
                    </a>
                @endif
            </div>
        </div>
    </div>
</x-filament-widgets::widget>
