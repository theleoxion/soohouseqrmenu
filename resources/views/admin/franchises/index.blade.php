@extends('layouts.admin', [
    'title' => 'Franchise Başvuruları Yönetimi',
    'subtitle' => 'Yatırımcı adaylarının başvurularını inceleyin, durum takibi (Yeni, Arandı, Görüşüldü, Olumlu, Olumsuz) yapın ve iç not ekleyin.',
])

@section('header_actions')
    <a href="{{ route('admin.franchises.export', request()->query()) }}"
       class="px-5 py-2.5 rounded-xl bg-obsidian text-cream text-xs font-bold uppercase tracking-wider hover:bg-amber-harvest transition">
        Excel'e Aktar (.xlsx)
    </a>
@endsection

@section('content')
    <div class="space-y-6">
        <form method="GET" action="{{ route('admin.franchises.index') }}" class="rounded-2xl bg-white border border-obsidian/10 p-4 flex flex-wrap items-center gap-3">
            <select name="status" class="rounded-xl border border-obsidian/20 px-3.5 py-2 text-xs bg-white font-semibold">
                <option value="">Tüm Durumlar</option>
                @foreach(\App\Models\FranchiseApplication::statusLabels() as $k => $lbl)
                    <option value="{{ $k }}" @selected(request('status') === $k)>{{ $lbl }}</option>
                @endforeach
            </select>
            <input type="text" name="city" value="{{ request('city') }}" placeholder="Şehir filtrele..."
                   class="rounded-xl border border-obsidian/20 px-3.5 py-2 text-xs">
            <input type="search" name="q" value="{{ request('q') }}" placeholder="Ad soyad, telefon veya e-posta..."
                   class="rounded-xl border border-obsidian/20 px-3.5 py-2 text-xs flex-1 min-w-48">
            <button type="submit" class="px-4 py-2 rounded-xl bg-obsidian text-cream text-xs font-bold cursor-pointer">Filtrele</button>
        </form>

        @if($applications->isEmpty())
            <div class="rounded-3xl bg-white border border-obsidian/10 p-12 text-center space-y-2">
                <h2 class="text-lg font-display font-bold text-obsidian">Henüz Franchise Başvurusu Bulunmuyor</h2>
                <p class="text-xs text-obsidian/60">Web sitesindeki /franchise formundan yapılan başvurular burada listelenecektir.</p>
            </div>
        @else
            <div class="space-y-4">
                @foreach($applications as $app)
                    <form method="POST" action="{{ route('admin.franchises.update', $app->id) }}"
                          class="rounded-2xl bg-white border border-obsidian/10 p-6 space-y-4 shadow-2xs">
                        @csrf
                        @method('PUT')

                        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-obsidian/10 pb-3">
                            <div>
                                <h3 class="text-lg font-display font-bold text-obsidian">{{ $app->full_name }}</h3>
                                <p class="text-xs text-obsidian/65">
                                    <strong>Şehir:</strong> {{ $app->city }}{{ $app->district ? ' / ' . $app->district : '' }}
                                    • <strong>Bütçe:</strong> <span class="text-amber-harvest font-bold">{{ $app->investment_budget }}</span>
                                    • <strong>Tel:</strong> <a href="tel:{{ $app->phone }}" class="underline">{{ $app->phone }}</a>
                                    • <strong>E-posta:</strong> {{ $app->email }}
                                </p>
                            </div>
                            <span class="text-xs text-obsidian/50">{{ $app->created_at?->format('d.m.Y H:i') }}</span>
                        </div>

                        @if($app->preferred_location_note || $app->message)
                            <div class="p-4 rounded-xl bg-cream text-xs space-y-1 text-obsidian/80">
                                @if($app->preferred_location_note)
                                    <p><strong>Lokasyon Durumu:</strong> {{ $app->preferred_location_note }}</p>
                                @endif
                                @if($app->message)
                                    <p><strong>Aday Mesajı:</strong> {{ $app->message }}</p>
                                @endif
                            </div>
                        @endif

                        <div class="grid grid-cols-1 md:grid-cols-12 gap-3 items-end">
                            <div class="md:col-span-3">
                                <label class="block text-[11px] font-bold text-obsidian/70 mb-1">Başvuru Durumu</label>
                                <select name="status" class="w-full rounded-xl border border-obsidian/20 px-3 py-2 text-xs bg-white font-bold">
                                    @foreach(\App\Models\FranchiseApplication::statusLabels() as $sKey => $sLabel)
                                        <option value="{{ $sKey }}" @selected($app->status === $sKey)>{{ $sLabel }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="md:col-span-7">
                                <label class="block text-[11px] font-bold text-obsidian/70 mb-1">Görüşme / Değerlendirme Notu</label>
                                <input type="text" name="internal_notes" value="{{ $app->internal_notes }}" placeholder="Satış birimi iç notu..."
                                       class="w-full rounded-xl border border-obsidian/20 px-3.5 py-2 text-xs">
                            </div>
                            <div class="md:col-span-2">
                                <button type="submit" class="w-full py-2 px-4 rounded-xl bg-obsidian text-cream text-xs font-bold hover:bg-amber-harvest transition cursor-pointer">
                                    Güncelle
                                </button>
                            </div>
                        </div>
                    </form>
                @endforeach

                <div>{{ $applications->links() }}</div>
            </div>
        @endif
    </div>
@endsection
