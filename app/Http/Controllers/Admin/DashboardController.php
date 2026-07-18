<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NeracaPangan;
use App\Models\Notifikasi;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    private const STOK_BAR_COLORS = [
        '#2563EB', '#1D4ED8', '#60A5FA', '#1E40AF',
        '#3B82F6', '#93C5FD', '#2563EB', '#60A5FA',
    ];

    /** Nama bulan Indonesia (singkatan), dipakai untuk format label "Jan 2025" dsb. */
    private const BULAN_INDO = [
        1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'Mei', 6 => 'Jun',
        7 => 'Jul', 8 => 'Agt', 9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des',
    ];

    public function index(Request $request)
    {
        $trend = $this->getTrend();
        $adminId = $request->user()->id;

        return view('admin.dashboard', [
            'summary'     => $this->getSummary(),
            'trendLabels' => $trend->pluck('periode')->all(),
            'trendValues' => $trend->pluck('nilai')->all(),
            'statusPie'   => $this->getStatusPie(),
            'stokBars'    => $this->getStokBars(),
            'aktivitas'   => $this->getAktivitas($adminId),
            'notifCount'  => Notifikasi::where('user_id', $adminId)
                ->where('dibaca', false)
                ->count(),
            'activeMenu'  => 'dashboard',
        ]);
    }

    private function getSummary(): array
    {
        return [
            'total'    => NeracaPangan::count(),
            'valid'    => NeracaPangan::where('status', 'valid')->count(),
            'menunggu' => NeracaPangan::where('status', 'menunggu')->count(),
            'revisi'   => NeracaPangan::where('status', 'revisi')->count(),
        ];
    }

    /**
     * Tren nilai neraca (total nilai_neraca per bulan, hanya data valid),
     * diurutkan kronologis, dibatasi 9 periode terakhir.
     *
     * `periode` adalah kolom DATE, jadi tahun & bulan diambil langsung
     * dari situ via YEAR()/MONTH() — tidak butuh kolom bantu.
     * `nilai_neraca` dihitung on-the-fly dari kolom-kolom stok karena
     * tidak disimpan sebagai kolom terpisah.
     */
    private function getTrend()
    {
        $rows = NeracaPangan::selectRaw("
                YEAR(periode) as tahun,
                MONTH(periode) as bulan,
                SUM(stok_awal + produksi + masuk - keluar - kebutuhan_rumah_tangga - kebutuhan_non_rumah_tangga) as nilai
            ")
            ->where('status', 'valid')
            ->groupBy('tahun', 'bulan')
            ->orderBy('tahun')
            ->orderBy('bulan')
            ->get();

        return $rows->map(fn ($r) => [
            'periode' => (self::BULAN_INDO[(int) $r->bulan] ?? $r->bulan) . ' ' . $r->tahun,
            'nilai'   => (float) $r->nilai,
        ])->take(-9)->values();
    }

    private function getStatusPie(): array
    {
        $summary = $this->getSummary();

        return [
            ['label' => 'Valid',               'value' => $summary['valid'],    'color' => '#16A34A'],
            ['label' => 'Menunggu Verifikasi', 'value' => $summary['menunggu'], 'color' => '#EA580C'],
            ['label' => 'Perlu Revisi',        'value' => $summary['revisi'],   'color' => '#DC2626'],
        ];
    }

    /**
     * Nilai neraca terbaru (data valid) per komoditas.
     * Nama komoditas diambil dari relasi `komoditas()` (belongsTo).
     */
    private function getStokBars(): array
    {
        $latestIdsPerKomoditas = NeracaPangan::where('status', 'valid')
            ->selectRaw('MAX(id) as id')
            ->groupBy('komoditas_id')
            ->pluck('id');

        return NeracaPangan::with('komoditas')
            ->whereIn('id', $latestIdsPerKomoditas)
            ->get()
            ->map(fn ($item) => [
                'name'  => $item->komoditas->nama ?? '-',
                'nilai' => (int) (
                    $item->stok_awal + $item->produksi + $item->masuk
                    - $item->keluar - $item->kebutuhan_rumah_tangga - $item->kebutuhan_non_rumah_tangga
                ),
            ])
            ->sortByDesc('nilai')
            ->take(8)
            ->values()
            ->all();
    }

    private function getAktivitas(int $adminId): array
    {
        return Notifikasi::where('user_id', $adminId)
            ->latest()
            ->limit(5)
            ->get()
            ->map(fn ($n) => [
                'tipe'  => $this->tentukanTipe($n->pesan),
                'pesan' => $n->pesan,
                'waktu' => Carbon::parse($n->created_at)->diffForHumans(),
                'baca'  => (bool) $n->dibaca,
            ])
            ->all();
    }

    /**
     * Tentukan tipe (ikon & warna) notifikasi berdasarkan isi pesan,
     * karena tabel `notifikasi` belum memiliki kolom `tipe` tersendiri.
     */
    private function tentukanTipe(string $pesan): string
    {
        $pesanLower = \Illuminate\Support\Str::lower($pesan);

        if (\Illuminate\Support\Str::contains($pesanLower, ['revisi', 'dikembalikan', 'ditolak'])) {
            return 'warning';
        }

        if (\Illuminate\Support\Str::contains($pesanLower, ['divalidasi', 'valid', 'disetujui'])) {
            return 'success';
        }

        return 'info';
    }
}