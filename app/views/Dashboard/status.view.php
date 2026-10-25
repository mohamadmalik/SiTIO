<!-- app/views/Dashboard/status.view.php -->
<div class="w-full h-full bg-white border border-slate-200 rounded-2xl shadow-sm p-5 flex flex-col">
  <h5 class="text-lg font-semibold text-slate-800 text-center">Jumlah Pengguna</h5>

  <div id="range-filter" class="mt-3 flex justify-center gap-1">
    <?php foreach (['7d' => '7 Hari', '1m' => '1 Bulan', '1y' => '1 Tahun', 'ytd' => 'YTD'] as $key => $label): ?>
      <button type="button" data-range="<?= $key ?>"
              class="range-btn px-2.5 py-1 rounded-full text-xs font-medium whitespace-nowrap transition <?= $key === '1m' ? 'bg-slate-800 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' ?>">
        <?= $label ?>
      </button>
    <?php endforeach; ?>
  </div>

  <div class="flex-1 flex items-center justify-center mt-2">
    <div class="w-full" id="donut-chart"></div>
  </div>
</div>