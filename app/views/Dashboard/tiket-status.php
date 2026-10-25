<?php
// app/views/Dashboard/tiket-status.php
// Card ringkasan status pengajuan tiket (data dummy dulu, belum konek database).

$judul      = 'Status tiket';
$linkDaftar = '/daftar-tiket';

if (isset($_SESSION['tiket']) && is_array($_SESSION['tiket'])) {
    $counts = [
        'Diajukan' => count(array_filter($_SESSION['tiket'], fn($t) => ($t['status'] ?? '') === 'Diajukan')),
        'Pending'  => count(array_filter($_SESSION['tiket'], fn($t) => ($t['status'] ?? '') === 'Pending')),
        'Tuntas'   => count(array_filter($_SESSION['tiket'], fn($t) => ($t['status'] ?? '') === 'Tuntas')),
    ];
} else {
    $counts = [
        'Diajukan' => 2,
        'Pending'  => 2,
        'Tuntas'   => 2,
    ];
}
$statusConfig = [
    'Diajukan' => ['label' => 'Diajukan', 'bar' => 'bg-[#4aa8f8]', 'dot' => 'bg-[#4aa8f8]'],
    'Pending'  => ['label' => 'Pending',  'bar' => 'bg-[#f4a340]', 'dot' => 'bg-[#f4a340]'],
    'Tuntas'   => ['label' => 'Tuntas',   'bar' => 'bg-[#22c55e]', 'dot' => 'bg-[#22c55e]'],
];
$errorStatus = null;

$total  = array_sum($counts);
$persen = fn(int $n): float => $total > 0 ? ($n / $total) * 100 : 0;
$e      = fn($v) => htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
?>

<div class="w-full bg-radial-[at_25%_25%] from-[#ffd3d395] to-[#fff1e97a] to-75% border border-slate-200 rounded-2xl shadow-sm p-5">
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
      <div class="mt-3 grid grid-cols-2 gap-4 sm:grid-cols-3">
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
