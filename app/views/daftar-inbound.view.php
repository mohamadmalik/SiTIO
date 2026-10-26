<?php

/* ---------- Konfigurasi ---------- */
$uploadDir  = __DIR__ . '/../../public/uploads/inbound_docs/';
$uploadUrl  = '/uploads/inbound_docs/';
$maxSize    = 10 * 1024 * 1024; // 10 MB

$e = fn($v) => htmlspecialchars((string)($v ?? ''), ENT_QUOTES, 'UTF-8');

$_SESSION['csrf'] = $_SESSION['csrf'] ?? bin2hex(random_bytes(16));

/* ---------- Handler POST: Pengajuan Inbound Baru ---------- */
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'submit_inbound') {
    $sessionCsrf   = $_SESSION['csrf'] ?? '';
    $submittedCsrf = (string)($_POST['csrf'] ?? '');

    if (!is_string($sessionCsrf) || !hash_equals($sessionCsrf, $submittedCsrf)) {
        $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Sesi tidak valid, silakan muat ulang halaman.'];
    } else {
        $nama            = trim((string)($_POST['nama'] ?? ''));
        $paspor          = trim((string)($_POST['paspor'] ?? ''));
        $kewarganegaraan = trim((string)($_POST['kewarganegaraan'] ?? ''));
        $instansi_asal   = trim((string)($_POST['instansi_asal'] ?? ''));
        $tujuan          = trim((string)($_POST['tujuan'] ?? ''));
        $durasi          = trim((string)($_POST['durasi'] ?? ''));
        $tanggal_datang  = trim((string)($_POST['tanggal_datang'] ?? ''));
        $tanggal_pulang  = trim((string)($_POST['tanggal_pulang'] ?? ''));

        if ($nama === '' || $paspor === '' || $kewarganegaraan === '' || $tujuan === '') {
            $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Mohon lengkapi seluruh field wajib bertanda bintang (*).'];
        } else {
            if (!is_dir($uploadDir)) {
                @mkdir($uploadDir, 0755, true);
            }

            // Upload Dokumen Pendukung (CV / Surat Pengantar / Paspor) jika ada
            $filePaspor = null;
            if (isset($_FILES['file_paspor']) && $_FILES['file_paspor']['error'] === UPLOAD_ERR_OK) {
                $f = $_FILES['file_paspor'];
                $safeName = 'paspor_' . time() . '_' . preg_replace('/[^a-zA-Z0-9_\.-]/', '_', $f['name']);
                if (move_uploaded_file($f['tmp_name'], $uploadDir . $safeName)) {
                    $filePaspor = $safeName;
                }
            }

            // Simpan ke session daftar inbound user
            if (!isset($_SESSION['inbound'])) {
                $_SESSION['inbound'] = [];
            }

            $newId = count($_SESSION['inbound']) > 0 ? max(array_column($_SESSION['inbound'], 'id')) + 1 : 401;

            $_SESSION['inbound'][] = [
                'id'            => $newId,
                'peserta'       => $nama,
                'paspor'        => $paspor,
                'negara'        => $kewarganegaraan,
                'instansi'      => $instansi_asal,
                'tujuan'        => $tujuan,
                'durasi'        => $durasi,
                'datang'        => $tanggal_datang,
                'pulang'        => $tanggal_pulang,
                'file_paspor'   => $filePaspor,
                'status'        => 'Baru Diajukan',
                'created_at'    => date('Y-m-d H:i:s')
            ];

            $_SESSION['flash'] = [
                'type' => 'success',
                'msg'  => "Formulir Ajuan Inbound atas nama \"{$nama}\" berhasil dikirim dan tersimpan!"
            ];
        }
    }

    if (!headers_sent()) {
        header('Location: ' . $_SERVER['REQUEST_URI']);
        exit;
    }
    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);
}
?>

<div class="max-w-4xl mx-auto space-y-6">



  <?php if ($flash): ?>
    <div class="px-4 py-3.5 rounded-xl text-xs sm:text-sm font-medium flex items-center gap-2 <?= $flash['type'] === 'success' ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 'bg-rose-50 text-rose-800 border border-rose-200' ?>">
      <svg class="w-4 h-4 shrink-0 <?= $flash['type'] === 'success' ? 'text-emerald-600' : 'text-rose-600' ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
      <span><?= $e($flash['msg']) ?></span>
    </div>
  <?php endif; ?>

  <!-- Card Form Utama -->
  <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-6 sm:p-8">
    <form action="" method="POST" enctype="multipart/form-data" class="space-y-6">
      <input type="hidden" name="action" value="submit_inbound">
      <input type="hidden" name="csrf" value="<?= $e($_SESSION['csrf']) ?>">

      <!-- Section: Identitas Peserta Inbound -->
      <div class="space-y-4">
        <div class="flex items-center gap-2.5 border-b border-slate-100 pb-2">
          <span class="flex items-center justify-center w-6 h-6 rounded-md bg-[#008C95] text-white text-xs font-bold shadow-xs">01</span>
          <span class="font-bold text-xs uppercase tracking-wider text-[#008C95]">IDENTITAS PESERTA INBOUND</span>
        </div>

        <!-- Nama Lengkap -->
        <div>
          <label for="nama" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
            NAMA LENGKAP & GELAR <span class="text-rose-500">*</span>
          </label>
          <input type="text" id="nama" name="nama" required placeholder="Contoh: Prof. Kenji Tanaka, Ph.D"
            class="w-full px-3.5 py-2.5 text-sm bg-white border border-slate-300 rounded-xl text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-[#008C95] focus:border-transparent transition">
        </div>

        <!-- Paspor & Kewarganegaraan -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label for="paspor" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
              NOMOR PASPOR <span class="text-rose-500">*</span>
            </label>
            <input type="text" id="paspor" name="paspor" required placeholder="Contoh: TK8899201"
              class="w-full px-3.5 py-2.5 text-sm bg-white border border-slate-300 rounded-xl text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-[#008C95] focus:border-transparent transition font-mono">
          </div>
          <div>
            <label for="kewarganegaraan" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
              KEWARGANEGARAAN <span class="text-rose-500">*</span>
            </label>
            <input type="text" id="kewarganegaraan" name="kewarganegaraan" required placeholder="Contoh: Jepang / Jerman / Malaysia"
              class="w-full px-3.5 py-2.5 text-sm bg-white border border-slate-300 rounded-xl text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-[#008C95] focus:border-transparent transition">
          </div>
        </div>

        <!-- Instansi Asal -->
        <div>
          <label for="instansi_asal" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
            INSTITUSI / UNIVERSITAS ASAL
          </label>
          <input type="text" id="instansi_asal" name="instansi_asal" placeholder="Contoh: Tokyo Institute of Technology"
            class="w-full px-3.5 py-2.5 text-sm bg-white border border-slate-300 rounded-xl text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-[#008C95] focus:border-transparent transition">
        </div>
      </div>

      <!-- Section: Detail Kunjungan & Tujuan -->
      <div class="space-y-4 pt-3 border-t border-slate-100">
        <div class="flex items-center gap-2.5 border-b border-slate-100 pb-2">
          <span class="flex items-center justify-center w-6 h-6 rounded-md bg-[#008C95] text-white text-xs font-bold shadow-xs">02</span>
          <span class="font-bold text-xs uppercase tracking-wider text-[#008C95]">DETAIL KUNJUNGAN KE PNJ</span>
        </div>

        <!-- Tujuan Kegiatan -->
        <div>
          <label for="tujuan" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
            TUJUAN KEGIATAN <span class="text-rose-500">*</span>
          </label>
          <textarea id="tujuan" name="tujuan" rows="3" required placeholder="Jelaskan tujuan kegiatan inbound (contoh: Guest lecture, workshop teknis, pertukaran mahasiswa, dsb.)..."
            class="w-full px-3.5 py-2.5 text-sm bg-white border border-slate-300 rounded-xl text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-[#008C95] focus:border-transparent transition"></textarea>
        </div>

        <!-- Estimasi Tanggal -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label for="tanggal_datang" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
              RENCANA TANGGAL KEDATANGAN
            </label>
            <input type="date" id="tanggal_datang" name="tanggal_datang"
              class="w-full px-3.5 py-2.5 text-sm bg-white border border-slate-300 rounded-xl text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#008C95] focus:border-transparent transition">
          </div>
          <div>
            <label for="tanggal_pulang" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
              RENCANA TANGGAL KEPULANGAN
            </label>
            <input type="date" id="tanggal_pulang" name="tanggal_pulang"
              class="w-full px-3.5 py-2.5 text-sm bg-white border border-slate-300 rounded-xl text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#008C95] focus:border-transparent transition">
          </div>
        </div>

        <!-- Upload Salinan Paspor (PDF/Gambar) -->
        <div>
          <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
            SALINAN PASPOR / IDENTITAS <span class="text-slate-400 font-normal lowercase">(opsional, PDF/JPG/PNG)</span>
          </label>
          <label class="group relative flex items-center gap-3 p-3 border-2 border-dashed border-cyan-200/90 hover:border-[#008C95] rounded-xl bg-cyan-50/20 hover:bg-cyan-50/50 transition cursor-pointer">
            <input type="file" name="file_paspor" id="file_paspor" accept=".pdf,.jpg,.jpeg,.png" class="sr-only">
            <span class="px-3.5 py-1.5 text-xs font-medium bg-slate-100 group-hover:bg-white text-slate-700 border border-slate-300 rounded-lg shrink-0 transition">
              Choose File
            </span>
            <span id="file-paspor-name" class="text-xs text-slate-500 truncate flex-1 font-mono">
              Belum ada file dipilih
            </span>
          </label>
        </div>
      </div>

      <!-- Action Buttons -->
      <div class="flex items-center justify-end gap-3 pt-5 border-t border-slate-100">
        <button type="reset" class="px-5 py-2.5 text-sm font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl transition cursor-pointer">
          Reset Formulir
        </button>
        <button type="submit" class="px-6 py-2.5 text-sm font-bold text-white bg-[#008C95] hover:bg-[#00747c] active:scale-98 rounded-xl transition shadow-md shadow-[#008C95]/20 cursor-pointer">
          Kirim Ajuan Inbound
        </button>
      </div>
    </form>
  </div>

</div>

<script>
  (function () {
    const fileInput = document.getElementById('file_paspor');
    const fileNameDisplay = document.getElementById('file-paspor-name');
    if (fileInput && fileNameDisplay) {
      fileInput.addEventListener('change', function () {
        if (this.files && this.files.length > 0) {
          fileNameDisplay.textContent = this.files[0].name;
          fileNameDisplay.className = "text-xs text-[#008C95] font-semibold truncate flex-1 font-mono";
        } else {
          fileNameDisplay.textContent = "Belum ada file dipilih";
          fileNameDisplay.className = "text-xs text-slate-500 truncate flex-1 font-mono";
        }
      });
    }
  })();
</script>
