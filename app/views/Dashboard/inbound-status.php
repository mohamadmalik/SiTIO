<?php
// app/views/Dashboard/inbound-status.php
// Card ringkasan status pengajuan Inbound + Modal View Detail

$judul      = 'Status Inbound';
$linkDaftar = defined('BASE_URL') ? rtrim(BASE_URL, '/') . '/daftar-inbound' : '/daftar-inbound';

$statusConfig = [
    'Baru Diajukan' => ['label' => 'Diajukan',  'bar' => 'bg-[#4aa8f8]', 'dot' => 'bg-[#4aa8f8]'],
    'Diproses'      => ['label' => 'Diproses',  'bar' => 'bg-[#8584e8]', 'dot' => 'bg-[#8584e8]'],
    'Disetujui'     => ['label' => 'Disetujui', 'bar' => 'bg-[#f4a340]', 'dot' => 'bg-[#f4a340]'],
    'Ditolak'       => ['label' => 'Ditolak',   'bar' => 'bg-[#FF6467]', 'dot' => 'bg-[#FF6467]'],
];

/* ---------- Data dummy ---------- */
$counts = [
    'Baru Diajukan' => 5,
    'Diproses'      => 6,
    'Disetujui'     => 13,
    'Ditolak'       => 1,
];
$errorStatus = null;

$total  = array_sum($counts);
$persen = fn(int $n): float => $total > 0 ? ($n / $total) * 100 : 0;
$e      = fn($v) => htmlspecialchars((string)($v ?? ''), ENT_QUOTES, 'UTF-8');

// Sample data inbound untuk view modal langsung
$sampleInbound = [
    'nama'            => 'Prof. Kenji Tanaka',
    'paspor'          => 'TK8899201',
    'kewarganegaraan' => 'Jepang',
    'tujuan'          => 'Guest Lecture & Riset Kolaborasi IoT'
];
?>

<div class="w-full bg-radial-[at_25%_25%] from-[#c3ffcf53] to-[#ebffd55a] to-75% border border-slate-200 rounded-2xl shadow-sm p-5">
  <div class="flex items-center justify-between">
    <h5 class="text-lg font-semibold text-slate-800"><?= $e($judul) ?></h5>
    <div class="flex items-center gap-2">
      <button type="button" id="btn-view-inbound-status"
        data-detail="<?= $e(json_encode($sampleInbound)) ?>"
        class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-semibold text-[#008C95] bg-white/80 hover:bg-[#008C95] hover:text-white border border-[#008C95]/30 rounded-lg transition-colors cursor-pointer shadow-xs">
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
        View
      </button>
      <a href="<?= $e($linkDaftar) ?>" class="text-xs font-medium text-[#7f7f80] hover:text-slate-700">Lihat semua</a>
    </div>
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

<!-- Modal: View / Form Inbound (Nama, Paspor, Kewarganegaraan, Tujuan) -->
<div id="modal-inbound-status" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/60 p-3 sm:p-6 backdrop-blur-xs">
  <div class="relative w-full max-w-lg bg-white rounded-2xl shadow-2xl overflow-hidden animate-fadeIn">
    
    <!-- Modal Header -->
    <div class="flex items-center justify-between px-6 py-4 bg-[#01585f] text-white">
      <div class="flex items-center gap-2.5">
        <div class="w-2.5 h-2.5 rounded-full bg-emerald-300"></div>
        <h3 class="text-base sm:text-lg font-bold tracking-tight">Detail Pengajuan Inbound</h3>
      </div>
      <button type="button" data-close-inbound-modal class="w-8 h-8 inline-flex items-center justify-center rounded-lg bg-white/10 hover:bg-white/25 text-white transition text-lg leading-none cursor-pointer" aria-label="Tutup">
        ✕
      </button>
    </div>

    <!-- Modal Form -->
    <form action="" method="POST" id="form-inbound-status" class="p-6 space-y-4 text-sm text-slate-800">
      
      <!-- Input Nama -->
      <div>
        <label for="inbound-nama" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
          NAMA LENGKAP <span class="text-rose-500">*</span>
        </label>
        <input type="text" id="inbound-nama" name="nama" required placeholder="Masukkan nama peserta inbound"
          class="w-full px-3.5 py-2.5 text-sm bg-white border border-slate-300 rounded-xl text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-[#008C95] focus:border-transparent transition">
      </div>

      <!-- Input Nomor Paspor -->
      <div>
        <label for="inbound-paspor" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
          NOMOR PASPOR <span class="text-rose-500">*</span>
        </label>
        <input type="text" id="inbound-paspor" name="paspor" required placeholder="Contoh: TK8899201"
          class="w-full px-3.5 py-2.5 text-sm bg-white border border-slate-300 rounded-xl text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-[#008C95] focus:border-transparent transition font-mono">
      </div>

      <!-- Input Kewarganegaraan -->
      <div>
        <label for="inbound-kewarganegaraan" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
          KEWARGANEGARAAN <span class="text-rose-500">*</span>
        </label>
        <input type="text" id="inbound-kewarganegaraan" name="kewarganegaraan" required placeholder="Contoh: Jepang, Jerman, Malaysia"
          class="w-full px-3.5 py-2.5 text-sm bg-white border border-slate-300 rounded-xl text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-[#008C95] focus:border-transparent transition">
      </div>

      <!-- Input Tujuan -->
      <div>
        <label for="inbound-tujuan" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
          TUJUAN KEGIATAN <span class="text-rose-500">*</span>
        </label>
        <textarea id="inbound-tujuan" name="tujuan" rows="3" required placeholder="Contoh: Guest lecture, workshop teknis, pertukaran mahasiswa..."
          class="w-full px-3.5 py-2.5 text-sm bg-white border border-slate-300 rounded-xl text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-[#008C95] focus:border-transparent transition"></textarea>
      </div>

      <!-- Modal Footer -->
      <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
        <button type="button" data-close-inbound-modal class="px-5 py-2 text-sm font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl transition cursor-pointer">
          Batal
        </button>
        <button type="button" data-close-inbound-modal class="px-5 py-2 text-sm font-bold text-white bg-[#008C95] hover:bg-[#00747c] rounded-xl transition shadow-md shadow-[#008C95]/20 cursor-pointer">
          Simpan Data
        </button>
      </div>
    </form>
  </div>
</div>

<script>
  (function () {
    const modal = document.getElementById("modal-inbound-status");
    if (modal && modal.parentElement !== document.body) {
      document.body.appendChild(modal);
    }

    const toggle = (show) => {
      if (!modal) return;
      modal.classList.toggle("hidden", !show);
      modal.classList.toggle("flex", show);
    };

    const btnView = document.getElementById("btn-view-inbound-status");
    if (btnView) {
      btnView.addEventListener("click", () => {
        const raw = btnView.getAttribute("data-detail");
        if (raw) {
          const d = JSON.parse(raw);
          const elNama   = document.getElementById("inbound-nama");
          const elPaspor = document.getElementById("inbound-paspor");
          const elWarga  = document.getElementById("inbound-kewarganegaraan");
          const elTujuan = document.getElementById("inbound-tujuan");

          if (elNama)   elNama.value = d.nama || "";
          if (elPaspor) elPaspor.value = d.paspor || "";
          if (elWarga)  elWarga.value = d.kewarganegaraan || "";
          if (elTujuan) elTujuan.value = d.tujuan || "";
        }
        toggle(true);
      });
    }

    document.querySelectorAll("[data-close-inbound-modal]").forEach((btn) => {
      btn.addEventListener("click", () => toggle(false));
    });

    if (modal) {
      modal.addEventListener("click", (e) => {
        if (e.target === modal) toggle(false);
      });
    }

    document.addEventListener("keydown", (e) => {
      if (e.key === "Escape" && modal && !modal.classList.contains("hidden")) {
        toggle(false);
      }
    });
  })();
</script>
