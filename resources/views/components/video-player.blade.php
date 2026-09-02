@props([
    'url',
    'thumbnail' => null,
    'label',
    'wireKey',
])

<div
    class="relative aspect-video bg-black"
    x-data="{ frozen: false }"
    wire:key="{{ $wireKey }}"
>
    <video
        x-ref="video"
        class="absolute inset-0 w-full h-full object-contain"
        controls
        autoplay
        muted
        loop
        playsinline
        preload="metadata"
        aria-label="{{ $label }}"
        x-on:stalled="frozen = true"
        x-on:error="frozen = true"
        x-on:playing="frozen = false"
        @if ($thumbnail) poster="{{ $thumbnail }}" @endif
    >
        <source src="{{ $url }}" type="video/mp4">
    </video>

    <div
        x-show="frozen"
        x-cloak
        class="absolute inset-0 bg-black/60 flex flex-col items-center justify-center gap-3"
    >
        <svg class="w-8 h-8 text-white/70" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
        </svg>
        <button
            @click="frozen = false; $refs.video.load(); $refs.video.play();"
            class="px-4 py-2 rounded-lg bg-white text-slate-800 text-sm font-semibold hover:bg-slate-100 transition-colors"
        >
            Relancer la vidéo
        </button>
    </div>
</div>
