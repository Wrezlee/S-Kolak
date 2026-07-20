@if ($paginator->hasPages())
    <div class="flex items-center justify-between flex-wrap gap-3">

        {{-- Teks "Showing X to Y of Z entries" --}}
        <p class="text-xs text-slate-500">
            Showing {{ $paginator->firstItem() }} to {{ $paginator->lastItem() }} of {{ $paginator->total() }} entries
        </p>

        {{-- Tombol navigasi --}}
        <div class="flex items-center gap-1">

            {{-- First «  --}}
            @if ($paginator->onFirstPage())
                <span class="w-8 h-8 flex items-center justify-center rounded-md border border-slate-200 text-slate-300 text-sm cursor-not-allowed select-none">&laquo;</span>
            @else
                <a href="{{ $paginator->url(1) }}" class="w-8 h-8 flex items-center justify-center rounded-md border border-slate-200 text-slate-500 text-sm hover:bg-slate-50 transition-colors">&laquo;</a>
            @endif

            {{-- Previous ‹ --}}
            @if ($paginator->onFirstPage())
                <span class="w-8 h-8 flex items-center justify-center rounded-md border border-slate-200 text-slate-300 text-sm cursor-not-allowed select-none">&lsaquo;</span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" class="w-8 h-8 flex items-center justify-center rounded-md border border-slate-200 text-slate-500 text-sm hover:bg-slate-50 transition-colors">&lsaquo;</a>
            @endif

            {{-- Nomor halaman --}}
            @foreach ($elements as $element)
                @if (is_string($element))
                    <span class="w-8 h-8 flex items-center justify-center text-slate-400 text-sm select-none">{{ $element }}</span>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span class="w-8 h-8 flex items-center justify-center rounded-md text-sm font-semibold" style="background-color:#EFF6FF; color:#2563EB;">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}" class="w-8 h-8 flex items-center justify-center rounded-md border border-slate-200 text-slate-500 text-sm hover:bg-slate-50 transition-colors">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Next › --}}
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" class="w-8 h-8 flex items-center justify-center rounded-md border border-slate-200 text-slate-500 text-sm hover:bg-slate-50 transition-colors">&rsaquo;</a>
            @else
                <span class="w-8 h-8 flex items-center justify-center rounded-md border border-slate-200 text-slate-300 text-sm cursor-not-allowed select-none">&rsaquo;</span>
            @endif

            {{-- Last » --}}
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->url($paginator->lastPage()) }}" class="w-8 h-8 flex items-center justify-center rounded-md border border-slate-200 text-slate-500 text-sm hover:bg-slate-50 transition-colors">&raquo;</a>
            @else
                <span class="w-8 h-8 flex items-center justify-center rounded-md border border-slate-200 text-slate-300 text-sm cursor-not-allowed select-none">&raquo;</span>
            @endif

        </div>
    </div>
@endif