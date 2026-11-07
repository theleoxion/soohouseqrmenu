@extends('layouts.admin', [
    'title' => 'Değiştirilemez Denetim Kayıtları (Audit Logs)',
    'subtitle' => 'Tüm fiyat, indirim, menü, şube, kullanıcı, rol ve ayar değişiklikleri eski → yeni değerleriyle burada kayıt altına alınır. Bu kayıtlar silinemez ve değiştirilemez.',
])

@section('header_actions')
    <a href="{{ route('admin.audit_logs.export', request()->query()) }}"
       class="px-5 py-2.5 rounded-xl bg-obsidian text-cream text-xs font-bold uppercase tracking-wider hover:bg-amber-harvest transition">
        Excel'e Aktar (.xlsx)
    </a>
@endsection

@section('content')
    <div class="space-y-6">
        <!-- Filter Bar -->
        <form method="GET" action="{{ route('admin.audit_logs.index') }}" class="rounded-2xl bg-white border border-obsidian/10 p-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3 items-end">
            <div>
                <label class="block text-[10px] font-bold uppercase text-obsidian/60 mb-1">Kullanıcı</label>
                <select name="user_id" class="w-full rounded-xl border border-obsidian/20 px-3 py-2 text-xs bg-white">
                    <option value="">Tümü</option>
                    @foreach($users as $u)
                        <option value="{{ $u->id }}" @selected(request('user_id') == $u->id)>{{ $u->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-[10px] font-bold uppercase text-obsidian/60 mb-1">Şube</label>
                <select name="branch_id" class="w-full rounded-xl border border-obsidian/20 px-3 py-2 text-xs bg-white">
                    <option value="">Tümü / Merkez</option>
                    @foreach($branches as $b)
                        <option value="{{ $b->id }}" @selected(request('branch_id') == $b->id)>{{ $b->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-[10px] font-bold uppercase text-obsidian/60 mb-1">İşlem Türü</label>
                <select name="action_type" class="w-full rounded-xl border border-obsidian/20 px-3 py-2 text-xs bg-white">
                    <option value="">Tümü</option>
                    @foreach($actionTypes as $aKey => $aLabel)
                        <option value="{{ $aKey }}" @selected(request('action_type') === $aKey)>{{ $aLabel }}</option>
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

        @if($logs->isEmpty())
            <div class="rounded-3xl bg-white border border-obsidian/10 p-12 text-center space-y-2">
                <h2 class="text-lg font-display font-bold text-obsidian">Denetim Kaydı Bulunamadı</h2>
            </div>
        @else
            <div class="rounded-3xl bg-white border border-obsidian/10 overflow-hidden shadow-2xs">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="bg-sand/60 border-b border-obsidian/10 font-bold uppercase tracking-wider text-obsidian/70">
                                <th class="py-3.5 px-4">Tarih / Saat</th>
                                <th class="py-3.5 px-4">Kullanıcı &amp; IP</th>
                                <th class="py-3.5 px-4">Şube</th>
                                <th class="py-3.5 px-4">İşlem Türü</th>
                                <th class="py-3.5 px-4">Açıklama &amp; Değişim (Eski → Yeni)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-obsidian/10">
                            @foreach($logs as $log)
                                <tr class="hover:bg-cream/40 align-top">
                                    <td class="py-3.5 px-4 whitespace-nowrap font-mono text-obsidian/70">
                                        {{ $log->created_at?->format('d.m.Y H:i:s') }}
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <span class="font-bold text-obsidian block">{{ $log->user_name ?? 'Sistem' }}</span>
                                        <span class="text-[11px] font-mono text-obsidian/50">{{ $log->ip_address }}</span>
                                    </td>
                                    <td class="py-3.5 px-4 font-semibold">
                                        {{ $log->branch?->name ?? 'Genel Merkez' }}
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <span class="px-2.5 py-0.5 rounded bg-sand text-obsidian font-bold">
                                            {{ $log->actionLabel() }}
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-4 space-y-1.5">
                                        <p class="font-semibold text-obsidian">{{ $log->description }}</p>
                                        @if(!empty($log->old_values) || !empty($log->new_values))
                                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-[11px] font-mono bg-cream p-2.5 rounded-xl border border-obsidian/10">
                                                @if(!empty($log->old_values))
                                                    <div>
                                                        <strong class="text-terracotta">Eski:</strong>
                                                        {{ json_encode($log->old_values, JSON_UNESCAPED_UNICODE) }}
                                                    </div>
                                                @endif
                                                @if(!empty($log->new_values))
                                                    <div>
                                                        <strong class="text-sage">Yeni:</strong>
                                                        {{ json_encode($log->new_values, JSON_UNESCAPED_UNICODE) }}
                                                    </div>
                                                @endif
                                            </div>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div>{{ $logs->links() }}</div>
        @endif
    </div>
@endsection
