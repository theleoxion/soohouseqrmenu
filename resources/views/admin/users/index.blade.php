@extends('layouts.admin', [
    'title' => 'Kullanıcılar, Çoklu Rol ve Şube Kapsamı Yönetimi',
    'subtitle' => 'Bir kullanıcıya birden fazla rol atayabilir ve erişebileceği şubeleri (branch scope) sınırlandırabilirsiniz.',
])

@section('content')
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        <!-- Left 5 Cols: Create User -->
        <div class="lg:col-span-5">
            <form method="POST" action="{{ route('admin.users.store') }}"
                  class="rounded-3xl bg-white border border-obsidian/10 p-6 sm:p-8 space-y-4">
                @csrf
                <h2 class="text-base font-display font-bold text-obsidian border-b border-obsidian/10 pb-3">Yeni Kullanıcı Oluştur</h2>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-obsidian/75 mb-1">Ad Soyad *</label>
                    <input type="text" name="name" required class="w-full rounded-xl border border-obsidian/20 px-3.5 py-2 text-sm">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-obsidian/75 mb-1">E-posta *</label>
                        <input type="email" name="email" required class="w-full rounded-xl border border-obsidian/20 px-3.5 py-2 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-obsidian/75 mb-1">Telefon</label>
                        <input type="text" name="phone" class="w-full rounded-xl border border-obsidian/20 px-3.5 py-2 text-sm">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-obsidian/75 mb-1">Başlangıç Şifresi (En az 8 karakter) *</label>
                    <input type="password" name="password" required class="w-full rounded-xl border border-obsidian/20 px-3.5 py-2 text-sm">
                </div>

                <!-- Multi-Role Selection -->
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-obsidian/75 mb-1.5">Roller (Birden Fazla Seçilebilir) *</label>
                    <div class="space-y-1.5 p-3 rounded-2xl bg-cream border border-obsidian/10">
                        @foreach($roles as $role)
                            <label class="flex items-start gap-2 text-xs cursor-pointer">
                                <input type="checkbox" name="role_ids[]" value="{{ $role->id }}" class="mt-0.5 rounded">
                                <div>
                                    <span class="font-bold text-obsidian">{{ $role->name }}</span>
                                    <span class="block text-[11px] text-obsidian/55">{{ $role->description }}</span>
                                </div>
                            </label>
                        @endforeach
                    </div>
                </div>

                <!-- Branch Scope Assignment -->
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-obsidian/75 mb-1.5">Bağlı Şubeler (Branch Scope)</label>
                    <div class="p-3 rounded-2xl bg-cream border border-obsidian/10 space-y-2">
                        <label class="flex items-center gap-2 text-xs font-bold text-amber-harvest cursor-pointer">
                            <input type="checkbox" name="all_branches" value="1">
                            <span>Tüm Şubelere Erişim Yetkisi Ver</span>
                        </label>
                        @if($branches->isNotEmpty())
                            <div class="pt-2 border-t border-obsidian/10 grid grid-cols-1 sm:grid-cols-2 gap-1.5">
                                @foreach($branches as $b)
                                    <label class="flex items-center gap-2 text-xs cursor-pointer">
                                        <input type="checkbox" name="branch_ids[]" value="{{ $b->id }}">
                                        <span>{{ $b->name }}</span>
                                    </label>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>

                <label class="inline-flex items-center gap-2 text-xs font-bold cursor-pointer">
                    <input type="checkbox" name="is_active" value="1" checked>
                    <span>Hesap Aktif</span>
                </label>

                <button type="submit" class="w-full py-3.5 rounded-xl bg-obsidian text-cream text-xs font-bold uppercase tracking-widest hover:bg-amber-harvest transition cursor-pointer">
                    + Kullanıcıyı Oluştur
                </button>
            </form>
        </div>

        <!-- Right 7 Cols: Existing Users -->
        <div class="lg:col-span-7 space-y-4">
            @foreach($users as $u)
                <form method="POST" action="{{ route('admin.users.update', $u->id) }}"
                      class="rounded-3xl bg-white border border-obsidian/10 p-6 space-y-4 shadow-2xs"
                      x-data="{ openEdit: false }">
                    @csrf
                    @method('PUT')

                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <div class="flex items-center gap-2">
                                <h3 class="text-base font-display font-bold text-obsidian">{{ $u->name }}</h3>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $u->is_active ? 'bg-sage/15 text-sage' : 'bg-terracotta/15 text-terracotta' }}">
                                    {{ $u->is_active ? 'Aktif' : 'Pasif' }}
                                </span>
                            </div>
                            <p class="text-xs text-obsidian/60">{{ $u->email }}</p>
                            <div class="flex flex-wrap gap-1.5 mt-2">
                                @foreach($u->roles as $r)
                                    <span class="px-2.5 py-0.5 rounded-md bg-obsidian text-cream text-[11px] font-bold">{{ $r->name }}</span>
                                @endforeach
                                @if($u->all_branches || $u->isSuperAdmin())
                                    <span class="px-2.5 py-0.5 rounded-md bg-amber-harvest/15 text-amber-harvest text-[11px] font-bold">Kapsam: Tüm Şubeler</span>
                                @else
                                    @foreach($u->branches as $ub)
                                        <span class="px-2.5 py-0.5 rounded-md bg-sand text-obsidian text-[11px] font-semibold">Şube: {{ $ub->name }}</span>
                                    @endforeach
                                @endif
                            </div>
                        </div>

                        <button type="button" @click="openEdit = !openEdit"
                                class="px-4 py-2 rounded-xl bg-sand text-obsidian text-xs font-bold hover:bg-obsidian hover:text-cream transition cursor-pointer">
                            Düzenle / Yetkilendir
                        </button>
                    </div>

                    <div x-show="openEdit" x-cloak class="pt-4 border-t border-obsidian/10 space-y-4 text-xs">
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <div>
                                <label class="block font-bold mb-1">Ad Soyad</label>
                                <input type="text" name="name" value="{{ $u->name }}" required class="w-full rounded-lg border border-obsidian/20 px-3 py-2">
                            </div>
                            <div>
                                <label class="block font-bold mb-1">E-posta</label>
                                <input type="email" name="email" value="{{ $u->email }}" required class="w-full rounded-lg border border-obsidian/20 px-3 py-2">
                            </div>
                            <div>
                                <label class="block font-bold mb-1">Yeni Şifre (Opsiyonel)</label>
                                <input type="password" name="password" placeholder="Değiştirmek için yazın" class="w-full rounded-lg border border-obsidian/20 px-3 py-2">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="p-3 rounded-xl bg-cream space-y-1.5">
                                <p class="font-bold text-obsidian">Roller:</p>
                                @php $uRoleIds = $u->roles->pluck('id')->all(); @endphp
                                @foreach($roles as $role)
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="checkbox" name="role_ids[]" value="{{ $role->id }}" @checked(in_array($role->id, $uRoleIds))>
                                        <span>{{ $role->name }}</span>
                                    </label>
                                @endforeach
                            </div>

                            <div class="p-3 rounded-xl bg-cream space-y-1.5">
                                <p class="font-bold text-obsidian">Şube Kapsamı:</p>
                                <label class="flex items-center gap-2 font-bold text-amber-harvest cursor-pointer">
                                    <input type="checkbox" name="all_branches" value="1" @checked($u->all_branches)>
                                    <span>Tüm Şubeler</span>
                                </label>
                                @php $uBranchIds = $u->branches->pluck('id')->all(); @endphp
                                @foreach($branches as $b)
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="checkbox" name="branch_ids[]" value="{{ $b->id }}" @checked(in_array($b->id, $uBranchIds))>
                                        <span>{{ $b->name }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <div class="flex items-center justify-between pt-2">
                            <label class="inline-flex items-center gap-2 font-bold cursor-pointer">
                                <input type="checkbox" name="is_active" value="1" @checked($u->is_active)>
                                <span>Hesap Aktif (İşaret kaldırılırsa giriş yapamaz)</span>
                            </label>
                            <button type="submit" class="px-5 py-2 rounded-xl bg-obsidian text-cream font-bold cursor-pointer">Kaydet</button>
                        </div>
                    </div>
                </form>
            @endforeach
        </div>
    </div>
@endsection
