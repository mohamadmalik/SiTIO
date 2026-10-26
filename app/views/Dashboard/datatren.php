<?php
// app/views/Dashboard/datatren.php
// Distribusi status + tren pengajuan (landscape, data dummy).

$statusData = [
    ['label' => 'Baru Diajukan',      'value' => 3, 'color' => '#f59e0b'],
    ['label' => 'Verifikasi Dokumen', 'value' => 1, 'color' => '#00909a'],
    ['label' => 'Disetujui',          'value' => 4, 'color' => '#16a34a'],
    ['label' => 'SELESAI',            'value' => 2, 'color' => '#4ade80'],
    ['label' => 'Ditolak',            'value' => 1, 'color' => '#cf1f33'],
];

$trenData = [
    ['bulan' => 'Mei 26', 'value' => 2],
    ['bulan' => 'Jun 26', 'value' => 4],
    ['bulan' => 'Sep 26', 'value' => 5],
];

$total   = array_sum(array_column($statusData, 'value'));
$maxTren = max(array_column($trenData, 'value')) ?: 1;

// Geometri donut
$r      = 40;
$circ   = 2 * M_PI * $r;
$offset = 0.0;
?>

<div class="w-full bg-white border border-slate-200 rounded-2xl shadow-sm p-6">
  <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-0">

    <!-- Kiri: Distribusi Status -->
    <div class="lg:pr-8 lg:border-r lg:border-slate-200">
      <h5 class="text-lg font-semibold text-slate-800">Distribusi Status</h5>

      <div class="mt-4 flex flex-col sm:flex-row items-center gap-6">
        <!-- Donut -->
        <div class="relative w-44 h-44 shrink-0">
          <svg viewBox="0 0 100 100" class="w-full h-full -rotate-90">
            <circle cx="50" cy="50" r="<?= $r ?>" fill="none" stroke="#f1f5f9" stroke-width="13"/>
            <?php foreach ($statusData as $s):
                if ($s['value'] <= 0 || $total <= 0) continue;
                $len = ($s['value'] / $total) * $circ;
            ?>
              <circle cx="50" cy="50" r="<?= $r ?>" fill="none"
                      stroke="<?= $s['color'] ?>" stroke-width="13"
                      stroke-dasharray="<?= number_format(max($len - 1.2, 0), 3, '.', '') ?> <?= number_format($circ, 3, '.', '') ?>"
                      stroke-dashoffset="<?= number_format(-$offset, 3, '.', '') ?>">
                <title><?= htmlspecialchars($s['label']) ?>: <?= $s['value'] ?></title>
              </circle>
            <?php $offset += $len; endforeach; ?>
          </svg>
          <div class="absolute inset-0 flex flex-col items-center justify-center">
            <span class="text-3xl font-bold text-slate-800"><?= $total ?></span>
            <span class="text-xs text-slate-500">Total</span>
          </div>
        </div>

        <!-- Legend -->
        <ul class="w-full space-y-2.5 text-sm">
          <?php foreach ($statusData as $s): ?>
            <li class="flex items-center justify-between gap-3">
              <span class="flex items-center gap-2 text-slate-600">
                <span class="inline-block h-2.5 w-2.5 rounded-[3px]" style="background: <?= $s['color'] ?>"></span>
                <?= htmlspecialchars($s['label']) ?>
              </span>
              <span class="font-semibold text-slate-800"><?= $s['value'] ?></span>
            </li>
          <?php endforeach; ?>
        </ul>
      </div>
    </div>

    <!-- Kanan: Tren Pengajuan -->
    <div class="lg:pl-8">
      <h5 class="text-lg font-semibold text-slate-800">Tren Pengajuan</h5>

      <div class="mt-6 space-y-5">
        <?php foreach ($trenData as $i => $t):
            $w = ($t['value'] / $maxTren) * 100;
            $isLast = $i === count($trenData) - 1;
        ?>
          <div class="flex items-center gap-4">
            <span class="w-16 shrink-0 text-sm font-medium text-slate-500"><?= htmlspecialchars($t['bulan']) ?></span>
            <div class="flex-1 h-7 rounded-full bg-slate-100 overflow-hidden">
              <div class="h-full rounded-full flex items-center justify-end pr-3 text-xs font-semibold text-white
                          <?= $isLast ? 'bg-gradient-to-r from-slate-900 to-teal-700' : 'bg-gradient-to-r from-teal-600 to-cyan-500' ?>"
                   style="width: <?= number_format($w, 2, '.', '') ?>%">
                <?= $t['value'] ?>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>

  </div>
</div>