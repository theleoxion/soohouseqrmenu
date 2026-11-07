@extends('layouts.admin', [
    'title' => 'Yönetim Gösterge Paneli',
    'subtitle' => 'Rol ve şube yetkinize göre anlık operasyon özeti.',
])

@section('content')
    <div class="space-y-8">
        <!-- Stats Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
            <div class="rounded-2xl bg-white border border-obsidian/10 p-6 shadow-2xs">
                <p class="text-xs font-bold uppercase tracking-wider text-obsidian/50">Erişilebilir Şubeler</p>
                <p class="text-3xl font-display font-extrabold text-obsidian mt-2">{{ $stats['branches_count'] }}</p>
                <p class="text-xs text-sage font-semibold mt-1">{{ $stats['active_branches_count'] }} aktif şube</p>
            </div>

            @if($user->hasPermission('products.manage') || $user->hasPermission('categories.manage') || $user->hasPermission('branch_menu.manage'))
                <div class="rounded-2xl bg-white border border-obsidian/10 p-6 shadow-2xs">
                    <p class="text-xs font-bold uppercase tracking-wider text-obsidian/50">Merkez Ürün Havuzu</p>
                    <p class="text-3xl font-display font-extrabold text-obsidian mt-2">{{ $stats['products_count'] }}</p>
                    <p class="text-xs text-obsidian/60 mt-1">{{ $stats['categories_count'] }} kategori</p>
                </div>
            @endif

            @if($user->hasPermission('feedbacks.view'))
                <div class="rounded-2xl bg-white border border-obsidian/10 p-6 shadow-2xs">
                    <p class="text-xs font-bold uppercase tracking-wider text-obsidian/50">Yeni Geri Bildirimler</p>
                    <p class="text-3xl font-display font-extrabold text-amber-harvest mt-2">{{ $stats['new_feedbacks_count'] }}</p>
                    <a href="{{ route('admin.feedbacks.index') }}" class="inline-block text-xs font-bold text-obsidian hover:underline mt-1">Geri Bildirimlere Git →</a>
                </div>
            @endif

            @if($user->hasPermission('franchises.view'))
                <div class="rounded-2xl bg-white border border-obsidian/10 p-6 shadow-2xs">
                    <p class="text-xs font-bold uppercase tracking-wider text-obsidian/50">Yeni Franchise Başvurusu</p>
                    <p class="text-3xl font-display font-extrabold text-obsidian mt-2">{{ $stats['new_franchises_count'] }}</p>
                    <a href="{{ route('admin.franchises.index') }}" class="inline-block text-xs font-bold text-amber-harvest hover:underline mt-1">Başvuruları İncele →</a>
                </div>
            @endif
        </div>

        <!-- Accessible Branches Quick Operations -->
        @if($user->hasPermission('branches.manage') || $user->hasPermission('branch_menu.manage') || $user->hasPermission('tables.manage'))
            <div class="rounded-3xl bg-white border border-obsidian/10 p-6 sm:p-8 space-y-5 shadow-2xs">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h2 class="text-lg font-display font-bold text-obsidian">Şube Hızlı Yönetim &amp; QR Menü Erişimi</h2>
                        <p class="text-xs text-obsidian/60">Yetkili olduğunuz şubelerin menü, fiyat, masa kartı ve çalışma saatlerini yönetin.</p>
                    </div>
                    @if($user->hasPermission('branches.manage'))
                        <a href="{{ route('admin.branches.create') }}"
                           class="px-4 py-2.5 rounded-xl bg-obsidian text-cream text-xs font-bold uppercase tracking-wider hover:bg-amber-harvest transition">
                            + Yeni Şube Ekle
                        </a>
                    @endif
                </div>

                @if($accessibleBranches->isEmpty())
                    <div class="p-8 rounded-2xl bg-cream border border-obsidian/10 text-center space-y-3">
                        <p class="text-sm font-bold text-obsidian">Henüz tanımlı bir şube bulunmuyor.</p>
                        <p class="text-xs text-obsidian/65 max-w-md mx-auto">
                            İlk şubenizi eklediğinizde `/subelerimiz/{slug}` ve `/qrmenu/{slug}` sayfaları otomatik olarak oluşturulacaktır.
                        </p>
                    </div>
                @else
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                        @foreach($accessibleBranches as $branch)
                            @php $status = $branch->openStatus(); @endphp
                            <div class="p-5 rounded-2xl bg-cream border border-obsidian/10 flex flex-col justify-between space-y-4">
                                <div>
                                    <div class="flex items-center justify-between gap-2">
                                        <span class="text-xs font-bold text-amber-harvest">{{ $branch->city }}</span>
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $status['is_open'] ? 'bg-sage/15 text-sage' : 'bg-terracotta/15 text-terracotta' }}">
                                            {{ $status['status_text'] }}
                                        </span>
                                    </div>
                                    <h3 class="text-base font-display font-bold text-obsidian mt-1">{{ $branch->name }}</h3>
                                    <p class="text-xs text-obsidian/60 mt-1">{{ $branch->diningTables->count() }} masa tanımlı</p>
                                </div>

                                <div class="grid grid-cols-2 gap-2 pt-3 border-t border-obsidian/10 text-xs font-bold">
                                    @if($user->hasPermission('branch_menu.manage') || $user->hasPermission('prices.branch_update'))
                                        <a href="{{ route('admin.branch.menu', $branch->slug) }}" class="py-2 px-3 rounded-lg bg-obsidian text-cream text-center hover:bg-amber-harvest transition">
                                            Menü &amp; Fiyat
                                        </a>
                                    @endif
                                    @if($user->hasPermission('tables.manage'))
                                        <a href="{{ route('admin.branch.tables', $branch->slug) }}" class="py-2 px-3 rounded-lg bg-white border border-obsidian/15 text-obsidian text-center hover:bg-sand transition">
                                            Masalar ({{ $branch->diningTables->count() }})
                                        </a>
                                    @endif
                                    @if($user->hasPermission('branch_settings.manage') || $user->hasPermission('branches.manage'))
                                        <a href="{{ route('admin.branch.settings', $branch->slug) }}" class="py-2 px-3 rounded-lg bg-white border border-obsidian/15 text-obsidian text-center hover:bg-sand transition">
                                            Saatler &amp; Bilgi
                                        </a>
                                    @endif
                                    <a href="{{ route('qrmenu.show', $branch->slug) }}" target="_blank" class="py-2 px-3 rounded-lg bg-amber-harvest/15 text-amber-harvest text-center hover:bg-amber-harvest hover:text-white transition">
                                        Canlı QR Menü ↗
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        @endif

        <!-- Recent Feedbacks & Audit Logs -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            @if($user->hasPermission('feedbacks.view'))
                <div class="rounded-3xl bg-white border border-obsidian/10 p-6 space-y-4">
                    <div class="flex items-center justify-between">
                        <h2 class="text-base font-display font-bold text-obsidian">Son Müşteri Geri Bildirimleri</h2>
                        <a href="{{ route('admin.feedbacks.index') }}" class="text-xs font-bold text-amber-harvest hover:underline">Tümü →</a>
                    </div>
                    @if($recentFeedbacks->isEmpty())
                        <p class="text-xs text-obsidian/55 py-6 text-center">Henüz müşteri geri bildirimi bulunmuyor.</p>
                    @else
                        <div class="divide-y divide-obsidian/10 text-xs">
                            @foreach($recentFeedbacks as $fb)
                                <div class="py-3 space-y-1">
                                    <div class="flex items-center justify-between font-bold">
                                        <span>{{ $fb->branch?->name }} — {{ $fb->typeLabel() }}</span>
                                        <span class="text-obsidian/50">{{ $fb->submitted_date?->format('d.m.Y') }} {{ substr((string)$fb->submitted_time, 0, 5) }}</span>
                                    </div>
                                    <p class="text-obsidian/75 line-clamp-2">{{ $fb->message }}</p>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endif

            @if($user->isSuperAdmin())
                <div class="rounded-3xl bg-white border border-obsidian/10 p-6 space-y-4">
                    <div class="flex items-center justify-between">
                        <h2 class="text-base font-display font-bold text-obsidian">Son Denetim Kayıtları (Audit Log)</h2>
                        <a href="{{ route('admin.audit_logs.index') }}" class="text-xs font-bold text-amber-harvest hover:underline">Tümü →</a>
                    </div>
                    @if($recentLogs->isEmpty())
                        <p class="text-xs text-obsidian/55 py-6 text-center">Henüz denetim kaydı bulunmuyor.</p>
                    @else
                        <div class="divide-y divide-obsidian/10 text-xs">
                            @foreach($recentLogs as $log)
                                <div class="py-2.5 flex items-start justify-between gap-3">
                                    <div>
                                        <span class="font-bold text-obsidian">{{ $log->user_name }}:</span>
                                        <span class="text-obsidian/75">{{ $log->description }}</span>
                                    </div>
                                    <span class="text-[11px] text-obsidian/45 shrink-0">{{ $log->created_at?->format('d.m H:i') }}</span>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </div>
@endsection
