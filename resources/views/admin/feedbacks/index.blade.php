@extends('layouts.admin', [
    'title' => 'Müşteri İstek / Öneri / Şikayet Yönetimi',
    'subtitle' => 'QR menü üzerinden otomatik şube, masa, tarih, gün ve saat bilgisiyle kaydedilen müşteri bildirimleri.',
])

@section('header_actions')
    <a href="{{ route('admin.feedbacks.export', request()->query()) }}"
       class="px-5 py-2.5 rounded-xl bg-obsidian text-cream text-xs font-bold uppercase tracking-wider hover:bg-amber-harvest transition">
        Excel'e Aktar (.xlsx)
    </a>
@endsection

@section('content')
    <div class="space-y-6">
        <!-- Filter Bar -->
        <form method="GET" action="{{ route('admin.feedbacks.index') }}" class="rounded-2xl bg-white border border-obsidian/10 p-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3 items-end">
            <div>
                <label class="block text-[10px] font-bold uppercase text-obsidian/60 mb-1">Şube</label>
                <select name="branch_id" class="w-full rounded-xl border border-obsidian/20 px-3 py-2 text-xs bg-white">
                    <option value="">Tüm Yetkili Şubeler</option>
                    @foreach($branches as $b)
                        <option value="{{ $b->id }}" @selected(request('branch_id') == $b->id)>{{ $b->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-[10px] font-bold uppercase text-obsidian/60 mb-1">Tür</label>
                <select name="type" class="w-full rounded-xl border border-obsidian/20 px-3 py-2 text-xs bg-white">
                    <option value="">Tümü</option>
                    @foreach(\App\Models\Feedback::typeLabels() as $k => $lbl)
                        <option value="{{ $k }}" @selected(request('type') === $k)>{{ $lbl }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-[10px] font-bold uppercase text-obsidian/60 mb-1">Durum</label>
                <select name="status" class="w-full rounded-xl border border-obsidian/20 px-3 py-2 text-xs bg-white">
                    <option value="">Tümü</option>
                    @foreach(\App\Models\Feedback::statusLabels() as $k => $lbl)
                        <option value="{{ $k }}" @selected(request('status') === $k)>{{ $lbl }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-[10px] font-bold uppercase text-obsidian/60 mb-1">Başlangıç Tarihi</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}" class="w-full rounded-xl border border-obsidian/20 px-3 py-2 text-xs">
            </div>
            <div>
                <label class="block text-[10px] font-bold uppercase text-obsidian/60 mb-1">Bitiş Tarihi</label>
                <input type="date" name="date_to" value="{{ request('date_to') }}" class="w-full rounded-xl border border-obsidian/20 px-3 py-2 text-xs">
            </div>
            <div>
                <button type="submit" class="w-full py-2 px-4 rounded-xl bg-obsidian text-cream text-xs font-bold cursor-pointer">Filtrele</button>
            </div>
        </form>

        @if($feedbacks->isEmpty())
            <div class="rounded-3xl bg-white border border-obsidian/10 p-12 text-center space-y-2">
                <h2 class="text-lg font-display font-bold text-obsidian">Kayıtlı Geri Bildirim Bulunmuyor</h2>
                <p class="text-xs text-obsidian/60">Müşteriler QR menü üzerinden istek, öneri veya şikayet gönderdiğinde burada listelenecektir.</p>
            </div>
        @else
            <div class="space-y-4">
                @foreach($feedbacks as $fb)
                    <form method="POST" action="{{ route('admin.feedbacks.update', $fb->id) }}"
                          class="rounded-2xl bg-white border border-obsidian/10 p-5 space-y-4 shadow-2xs">
                        @csrf
                        @method('PUT')

                        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-obsidian/10 pb-3 text-xs">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="px-2.5 py-1 rounded-lg font-bold {{ $fb->type === 'complaint' ? 'bg-terracotta text-white' : ($fb->type === 'suggestion' ? 'bg-amber-harvest text-white' : 'bg-obsidian text-cream') }}">
                                    {{ $fb->typeLabel() }}
                                </span>
                                <span class="font-bold text-obsidian">{{ $fb->branch?->name ?? '-' }}</span>
                                <span class="px-2.5 py-0.5 rounded bg-sand text-obsidian font-semibold">
                                    Masa: {{ $fb->diningTable ? $fb->diningTable->name . ' (#' . $fb->diningTable->table_number . ')' : 'Belirtilmedi' }}
                                </span>
                            </div>

                            <div class="text-obsidian/60 font-medium">
                                {{ $fb->submitted_date?->format('d.m.Y') }} ({{ $fb->day_name }}) — Saat: {{ substr((string)$fb->submitted_time, 0, 5) }}
                            </div>
                        </div>

                        <p class="text-sm text-obsidian leading-relaxed bg-cream p-4 rounded-xl border border-obsidian/5">{{ $fb->message }}</p>

                        <div class="grid grid-cols-1 md:grid-cols-12 gap-3 items-end">
                            <div class="md:col-span-3">
                                <label class="block text-[11px] font-bold text-obsidian/70 mb-1">Durum</label>
                                <select name="status" class="w-full rounded-xl border border-obsidian/20 px-3 py-2 text-xs bg-white font-bold">
                                    @foreach(\App\Models\Feedback::statusLabels() as $sKey => $sLabel)
                                        <option value="{{ $sKey }}" @selected($fb->status === $sKey)>{{ $sLabel }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="md:col-span-7">
                                <label class="block text-[11px] font-bold text-obsidian/70 mb-1">İç Not (Müşteri Görmez)</label>
                                <input type="text" name="internal_note" value="{{ $fb->internal_note }}" placeholder="Aksiyon veya inceleme notu ekleyin..."
                                       class="w-full rounded-xl border border-obsidian/20 px-3.5 py-2 text-xs">
                            </div>
                            <div class="md:col-span-2">
                                <button type="submit" class="w-full py-2 px-4 rounded-xl bg-obsidian text-cream text-xs font-bold hover:bg-amber-harvest transition cursor-pointer">
                                    Kaydet
                                </button>
                            </div>
                        </div>
                    </form>
                @endforeach

                <div>{{ $feedbacks->links() }}</div>
            </div>
        @endif
    </div>
@endsection
