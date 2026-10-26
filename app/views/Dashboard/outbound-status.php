<?php
// app/views/Dashboard/outbound-status.php
// Card ringkasan status pengajuan Outbound (data dummy dulu, belum konek database).

$judul      = 'Status Outbound';
$linkDaftar = '/daftar-outbound';

$statusConfig = [
    'Baru Diajukan' => ['label' => 'Diajukan',  'bar' => 'bg-[#4aa8f8]', 'dot' => 'bg-[#4aa8f8]'],
    'Diproses'      => ['label' => 'Diproses',  'bar' => 'bg-[#8584e8]', 'dot' => 'bg-[#8584e8]'],
    'Disetujui'     => ['label' => 'Disetujui', 'bar' => 'bg-[#f4a340]', 'dot' => 'bg-[#f4a340]'],
    'Ditolak'       => ['label' => 'Ditolak',   'bar' => 'bg-[#FF6467]', 'dot' => 'bg-[#FF6467]'],
];

/* ---------- Data dummy (ganti dengan query DB nanti) ---------- */
$counts = [
    'Baru Diajukan' => 7,
    'Diproses'      => 9,
    'Disetujui'     => 7,
    'Ditolak'       => 4,
];
$errorStatus = null;

$total  = array_sum($counts);
$persen = fn(int $n): float => $total > 0 ? ($n / $total) * 100 : 0;
$e      = fn($v) => htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
?>

<div class="w-full bg-radial-[at_25%_25%] from-[#cde0fd79] to-[#e1ecfd5a] to-75% border border-slate-200 rounded-2xl shadow-sm p-5">
  <div class="flex items-center justify-between">
    <h5 class="text-lg font-semibold text-slate-800"><?= $e($judul) ?></h5>
    <a href="<?= $e($linkDaftar) ?>" class="text-sm font-medium text-[#7f7f80] hover:text-slate-700">Lihat semua</a>
  </div>

  <p class="mt-1 text-sm text-[#545353]">
    Total <span class="font-semibold text-slate-800"><?= $total ?></span> pengajuan
  </p>

  <?php if ($errorStatus): ?>
    <div class="mt-4 px-4 py-3 rounded-lg text-sm bg-red-50 text-red-700 border border-red-200">
      <?= $e($errorStatus) ?>
    </div>
  <?php else: ?>
    <div class="mt-4 rounded-xl bg-white p-4">
      <!-- Bar segmen -->
      <div class="flex h-2.5 w-full gap-1">
        <?php foreach ($statusConfig as $key => $cfg): ?>
          <?php if ($counts[$key] > 0): ?>
            <div class="<?= $cfg['bar'] ?> h-full rounded-md"
                 style="width: <?= number_format($persen($counts[$key]), 2, '.', '') ?>%"
                 title="<?= $e($cfg['label']) ?>: <?= $counts[$key] ?>"></div>
          <?php endif; ?>
        <?php endforeach; ?>
      </div>

      <!-- Label + angka -->
      <div class="mt-3 grid grid-cols-2 gap-4 sm:grid-cols-4">
        <?php foreach ($statusConfig as $key => $cfg): ?>
          <div>
            <div class="text-xs text-slate-500"><?= $e($cfg['label']) ?></div>
            <div class="mt-1 flex items-center gap-2">
              <span class="inline-block h-2.5 w-2.5 rounded-[4px] <?= $cfg['dot'] ?>"></span>
              <span class="text-xm font-medium text-slate-800"><?= $counts[$key] ?></span>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  <?php endif; ?>
</div>