<?php

/* ---------- Konfigurasi ---------- */
$uploadDir  = __DIR__ . '/../../public/uploads/outbound_docs/';
$uploadUrl  = '/uploads/outbound_docs/';
$maxSize    = 10 * 1024 * 1024; // 10 MB

$e = fn($v) => htmlspecialchars((string)($v ?? ''), ENT_QUOTES, 'UTF-8');

$_SESSION['csrf'] = $_SESSION['csrf'] ?? bin2hex(random_bytes(16));

/* ---------- Handler POST: Pengajuan Outbound Baru ---------- */
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'submit_outbound') {
    $sessionCsrf   = $_SESSION['csrf'] ?? '';
    $submittedCsrf = (string)($_POST['csrf'] ?? '');

    if (!is_string($sessionCsrf) || !hash_equals($sessionCsrf, $submittedCsrf)) {
        $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Sesi tidak valid, silakan muat ulang halaman.'];
    } else {
        $nama        = trim((string)($_POST['nama'] ?? ''));
        $nim         = trim((string)($_POST['nim'] ?? ''));
        $prodi       = trim((string)($_POST['prodi'] ?? ''));
        $univ_tujuan = trim((string)($_POST['univ_tujuan'] ?? ''));
        $negara      = trim((string)($_POST['negara'] ?? ''));
        $kegiatan    = trim((string)($_POST['kegiatan'] ?? ''));
        $tgl_mulai   = trim((string)($_POST['tanggal_mulai'] ?? ''));
        $tgl_selesai = trim((string)($_POST['tanggal_selesai'] ?? ''));

        if ($nama === '' || $nim === '' || $prodi === '' || $univ_tujuan === '') {
            $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Mohon lengkapi seluruh field wajib bertanda bintang (*).'];
        } else {
            if (!is_dir($uploadDir)) {
                @mkdir($uploadDir, 0755, true);
            }

            $docFile = null;
            if (isset($_FILES['dokumen']) && $_FILES['dokumen']['error'] === UPLOAD_ERR_OK) {
                $f = $_FILES['dokumen'];
                $safeName = 'outbound_' . time() . '_' . preg_replace('/[^a-zA-Z0-9_\.-]/', '_', $f['name']);
                if (move_uploaded_file($f['tmp_name'], $uploadDir . $safeName)) {
                    $docFile = $safeName;
                }
            }

            if (!isset($_SESSION['outbound'])) {
                $_SESSION['outbound'] = [];
            }

            $newId = count($_SESSION['outbound']) > 0 ? max(array_column($_SESSION['outbound'], 'id')) + 1 : 301;

            $_SESSION['outbound'][] = [
                'id'          => $newId,
                'peserta'     => $nama,
                'nim'         => $nim,
                'prodi'       => $prodi,
                'univ_tujuan' => $univ_tujuan,
                'negara'      => $negara ?: '-',
                'kegiatan'    => $kegiatan ?: 'Outbound Program',
                'berangkat'   => $tgl_mulai ?: date('Y-m-d'),
                'kembali'     => $tgl_selesai ?: date('Y-m-d'),
                'dokumen'     => $docFile,
                'status'      => 'Baru Diajukan',
                'created_at'  => date('Y-m-d H:i:s')
            ];

            $_SESSION['flash'] = [
                'type' => 'success',
                'msg'  => "Formulir Ajuan Outbound atas nama \"{$nama}\" berhasil dikirim!"
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
      <input type="hidden" name="action" value="submit_outbound">
      <input type="hidden" name="csrf" value="<?= $e($_SESSION['csrf']) ?>">

      <!-- Section: Identitas Peserta Outbound -->
      <div class="space-y-4">
        <div class="flex items-center gap-2.5 border-b border-slate-100 pb-2">
          <span class="flex items-center justify-center w-6 h-6 rounded-md bg-[#008C95] text-white text-xs font-bold shadow-xs">01</span>
          <span class="font-bold text-xs uppercase tracking-wider text-[#008C95]">DATA MAHASISWA / PESERTA OUTBOUND</span>
        </div>

        <!-- Nama Lengkap -->
        <div>
          <label for="outbound-nama" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
            NAMA LENGKAP <span class="text-rose-500">*</span>
          </label>
          <input type="text" id="outbound-nama" name="nama" required placeholder="Masukkan nama lengkap peserta"
            class="w-full px-3.5 py-2.5 text-sm bg-white border border-slate-300 rounded-xl text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-[#008C95] focus:border-transparent transition">
        </div>

        <!-- NIM & Prodi -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label for="outbound-nim" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
              NIM <span class="text-rose-500">*</span>
            </label>
            <input type="text" id="outbound-nim" name="nim" required placeholder="Contoh: 2107411045"
              class="w-full px-3.5 py-2.5 text-sm bg-white border border-slate-300 rounded-xl text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-[#008C95] focus:border-transparent transition font-mono">
          </div>
          <div>
            <label for="outbound-prodi" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
              PROGRAM STUDI <span class="text-rose-500">*</span>
            </label>
            <select id="outbound-prodi" name="prodi" required
              class="w-full px-3.5 py-2.5 text-sm bg-white border border-slate-300 rounded-xl text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#008C95] focus:border-transparent transition">
              <option value="">Pilih Program Studi</option>
              <option value="Teknik Informatika">Teknik Informatika</option>
              <option value="Teknik Multimedia Digital">Teknik Multimedia Digital</option>
              <option value="Teknik Multimedia dan Jaringan">Teknik Multimedia dan Jaringan</option>
              <option value="Sistem Informasi Industri Otomotif">Sistem Informasi Industri Otomotif</option>
              <option value="Teknik Mesin">Teknik Mesin</option>
              <option value="Teknik Elektro">Teknik Elektro</option>
              <option value="Teknik Sipil">Teknik Sipil</option>
              <option value="Akuntansi">Akuntansi</option>
              <option value="Administrasi Niaga">Administrasi Niaga</option>
            </select>
          </div>
        </div>
      </div>

      <!-- Section: Detail Tujuan Outbound -->
      <div class="space-y-4 pt-3 border-t border-slate-100">
        <div class="flex items-center gap-2.5 border-b border-slate-100 pb-2">
          <span class="flex items-center justify-center w-6 h-6 rounded-md bg-[#008C95] text-white text-xs font-bold shadow-xs">02</span>
          <span class="font-bold text-xs uppercase tracking-wider text-[#008C95]">DETAIL UNIVERSITAS & PROGRAM TUJUAN</span>
        </div>

        <!-- Universitas Tujuan & Negara -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label for="outbound-univ" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
              UNIVERSITAS TUJUAN <span class="text-rose-500">*</span>
            </label>
            <input type="text" id="outbound-univ" name="univ_tujuan" required placeholder="Contoh: Tokyo University of Technology"
              class="w-full px-3.5 py-2.5 text-sm bg-white border border-slate-300 rounded-xl text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-[#008C95] focus:border-transparent transition">
          </div>
          <div>
            <label for="outbound-negara" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
              NEGARA TUJUAN
            </label>
            <input type="text" id="outbound-negara" name="negara" placeholder="Contoh: Jepang"
              class="w-full px-3.5 py-2.5 text-sm bg-white border border-slate-300 rounded-xl text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-[#008C95] focus:border-transparent transition">
          </div>
        </div>

        <!-- Nama Program / Kegiatan -->
        <div>
          <label for="outbound-kegiatan" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
            NAMA PROGRAM / KEGIATAN
          </label>
          <input type="text" id="outbound-kegiatan" name="kegiatan" placeholder="Contoh: Student Exchange / IISMA / Magang Internasional"
            class="w-full px-3.5 py-2.5 text-sm bg-white border border-slate-300 rounded-xl text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-[#008C95] focus:border-transparent transition">
        </div>

        <!-- Tanggal Pelaksanaan -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label for="tanggal_mulai" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
              TANGGAL MULAI KEBERANGKATAN
            </label>
            <input type="date" id="tanggal_mulai" name="tanggal_mulai"
              class="w-full px-3.5 py-2.5 text-sm bg-white border border-slate-300 rounded-xl text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#008C95] focus:border-transparent transition">
          </div>
          <div>
            <label for="tanggal_selesai" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
              TANGGAL SELESAI / KEPULANGAN
            </label>
            <input type="date" id="tanggal_selesai" name="tanggal_selesai"
              class="w-full px-3.5 py-2.5 text-sm bg-white border border-slate-300 rounded-xl text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#008C95] focus:border-transparent transition">
          </div>
        </div>

        <!-- Dokumen Pendukung -->
        <div>
          <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
            DOKUMEN PENDUKUNG (SURAT PENERIMAAN / LOA / BUKTI DAFTAR) <span class="text-slate-400 font-normal lowercase">(PDF)</span>
          </label>
          <label class="group relative flex items-center gap-3 p-3 border-2 border-dashed border-cyan-200/90 hover:border-[#008C95] rounded-xl bg-cyan-50/20 hover:bg-cyan-50/50 transition cursor-pointer">
            <input type="file" name="dokumen" id="outbound-dokumen" accept=".pdf" class="sr-only">
            <span class="px-3.5 py-1.5 text-xs font-medium bg-slate-100 group-hover:bg-white text-slate-700 border border-slate-300 rounded-lg shrink-0 transition">
              Choose File
            </span>
            <span id="outbound-file-name" class="text-xs text-slate-500 truncate flex-1 font-mono">
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
          Kirim Ajuan Outbound
        </button>
      </div>
    </form>
  </div>

</div>

<script>
  (function () {
    const fileInput = document.getElementById('outbound-dokumen');
    const fileNameDisplay = document.getElementById('outbound-file-name');
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
