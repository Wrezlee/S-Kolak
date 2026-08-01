{{-- Indikator format password: minimal 8 karakter, huruf besar, huruf kecil, angka, simbol --}}
<ul id="{{ $id }}" class="flex flex-wrap gap-x-3 gap-y-1 mt-1.5 text-[10px] text-slate-400">
    <li data-rule="length" class="flex items-center gap-1"><span class="dot w-1.5 h-1.5 rounded-full bg-slate-300"></span>Min. 8 karakter</li>
    <li data-rule="upper" class="flex items-center gap-1"><span class="dot w-1.5 h-1.5 rounded-full bg-slate-300"></span>Huruf besar</li>
    <li data-rule="lower" class="flex items-center gap-1"><span class="dot w-1.5 h-1.5 rounded-full bg-slate-300"></span>Huruf kecil</li>
    <li data-rule="number" class="flex items-center gap-1"><span class="dot w-1.5 h-1.5 rounded-full bg-slate-300"></span>Angka</li>
    <li data-rule="symbol" class="flex items-center gap-1"><span class="dot w-1.5 h-1.5 rounded-full bg-slate-300"></span>Simbol</li>
</ul>
