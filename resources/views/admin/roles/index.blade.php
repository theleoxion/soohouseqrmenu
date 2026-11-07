@extends('layouts.admin', [
    'title' => 'Rol ve İzin Matrisi Yönetimi (RBAC)',
    'subtitle' => 'Varsayılan rolleri düzenleyin veya yeni özel roller oluşturup izinlerini ayrıntılı olarak seçin.',
])

@section('content')
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        <!-- Left 4 Cols: Create New Role -->
        <div class="lg:col-span-4">
            <form method="POST" action="{{ route('admin.roles.store') }}"
                  class="rounded-3xl bg-white border border-obsidian/10 p-6 space-y-4">
                @csrf
                <h2 class="text-base font-display font-bold text-obsidian border-b border-obsidian/10 pb-3">Yeni Rol Oluştur</h2>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-obsidian/75 mb-1">Rol Adı *</label>
                    <input type="text" name="name" required placeholder="Örn: Bölge Denetçisi"
                           class="w-full rounded-xl border border-obsidian/20 px-3.5 py-2 text-sm">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-obsidian/75 mb-1">Açıklama</label>
                    <input type="text" name="description" class="w-full rounded-xl border border-obsidian/20 px-3.5 py-2 text-sm">
                </div>

                <div class="space-y-3 max-h-96 overflow-y-auto p-3 rounded-2xl bg-cream border border-obsidian/10">
                    @foreach($permissionGroups as $groupName => $perms)
                        <div class="space-y-1">
                            <p class="text-[11px] font-bold uppercase text-amber-harvest">{{ $groupName }}</p>
                            @foreach($perms as $perm)
                                <label class="flex items-center gap-2 text-xs cursor-pointer">
                                    <input type="checkbox" name="permission_ids[]" value="{{ $perm->id }}">
                                    <span>{{ $perm->name }}</span>
                                </label>
                            @endforeach
                        </div>
                    @endforeach
                </div>

                <button type="submit" class="w-full py-3 rounded-xl bg-obsidian text-cream text-xs font-bold uppercase tracking-wider cursor-pointer">
                    + Rolü Kaydet
                </button>
            </form>
        </div>

        <!-- Right 8 Cols: Existing Roles & Permission Matrix -->
        <div class="lg:col-span-8 space-y-5">
            @foreach($roles as $role)
                <form method="POST" action="{{ route('admin.roles.update', $role->id) }}"
                      class="rounded-3xl bg-white border border-obsidian/10 p-6 space-y-4">
                    @csrf
                    @method('PUT')

                    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-obsidian/10 pb-3">
                        <div>
                            <div class="flex items-center gap-2">
                                <h3 class="text-base font-display font-bold text-obsidian">{{ $role->name }}</h3>
                                <span class="text-xs font-mono text-obsidian/45">({{ $role->slug }})</span>
                                <span class="px-2 py-0.5 rounded bg-sand text-[11px] font-bold">{{ $role->users_count }} kullanıcı</span>
                            </div>
                            <p class="text-xs text-obsidian/60 mt-0.5">{{ $role->description }}</p>
                        </div>

                        @if($role->slug !== 'super_admin')
                            <button type="submit" class="px-4 py-2 rounded-xl bg-obsidian text-cream text-xs font-bold hover:bg-amber-harvest transition cursor-pointer">
                                İzinleri Güncelle
                            </button>
                        @else
                            <span class="px-3 py-1 rounded-full bg-amber-harvest/15 text-amber-harvest text-xs font-bold">Tam Yetkili Sistem Rolü</span>
                        @endif
                    </div>

                    <input type="hidden" name="name" value="{{ $role->name }}">
                    <input type="hidden" name="description" value="{{ $role->description }}">

                    @php $rolePermIds = $role->permissions->pluck('id')->all(); @endphp
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                        @foreach($permissionGroups as $groupName => $perms)
                            <div class="p-3 rounded-xl bg-cream space-y-1.5">
                                <p class="font-bold text-amber-harvest text-[11px] uppercase">{{ $groupName }}</p>
                                @foreach($perms as $perm)
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="checkbox" name="permission_ids[]" value="{{ $perm->id }}"
                                               @checked($role->slug === 'super_admin' || in_array($perm->id, $rolePermIds))
                                               @disabled($role->slug === 'super_admin')>
                                        <span>{{ $perm->name }}</span>
                                    </label>
                                @endforeach
                            </div>
                        @endforeach
                    </div>
                </form>
            @endforeach
        </div>
    </div>
@endsection
