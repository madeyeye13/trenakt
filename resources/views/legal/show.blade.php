<x-layouts.guest :title="$title">
    <h1 class="text-2xl font-bold mb-6">{{ $title }}</h1>

    <div class="text-sm text-gray-600 dark:text-white/60 space-y-4">
        @foreach (\App\Support\LegalContentRenderer::blocks($content) as $block)
            @if ($block['heading'])
                <p class="text-sm font-semibold text-trenakt-dark dark:text-white pt-2">{{ $block['text'] }}</p>
            @else
                <p class="leading-relaxed">{!! nl2br(e($block['text'])) !!}</p>
            @endif
        @endforeach
    </div>
</x-layouts.guest>
