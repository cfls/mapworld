@extends('layouts.admin')

@section('content')
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold text-slate-900">Mers & Océans</h2>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 border-b border-slate-200">
                <tr>
                    <th class="px-4 py-3 text-left font-semibold text-slate-600">Nom</th>
                    <th class="px-4 py-3 text-left font-semibold text-slate-600">Type</th>
                    <th class="px-4 py-3 text-left font-semibold text-slate-600">Groupe</th>
                    <th class="px-4 py-3 text-left font-semibold text-slate-600">Vidéos</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach (\App\Models\MarineArea::withCount('signVideos')->orderBy('name')->get() as $area)
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-4 py-3 font-medium text-slate-900">{{ $area->name }}</td>
                        <td class="px-4 py-3 text-slate-500 capitalize">{{ $area->type }}</td>
                        <td class="px-4 py-3 text-slate-500">{{ $area->ocean_group_label ?? '—' }}</td>
                        <td class="px-4 py-3">
                            @if ($area->sign_videos_count > 0)
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">
                                    {{ $area->sign_videos_count }} vidéo{{ $area->sign_videos_count > 1 ? 's' : '' }}
                                </span>
                            @else
                                <span class="text-slate-300 text-xs">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            <a
                                href="{{ route('admin.marine-area-videos', $area) }}"
                                class="text-xs font-semibold text-sky-600 hover:text-sky-800 transition-colors"
                            >
                                Gérer les vidéos →
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
