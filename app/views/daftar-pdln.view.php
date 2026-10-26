<?php

/* ---------- Konfigurasi ---------- */
$uploadDir  = __DIR__ . '/../../public/uploads/pdln_docs/';
$uploadUrl  = '/uploads/pdln_docs/';
$maxSize    = 10 * 1024 * 1024; // 10 MB

$e = fn($v) => htmlspecialchars((string)($v ?? ''), ENT_QUOTES, 'UTF-8');

$_SESSION['csrf'] = $_SESSION['csrf'] ?? bin2hex(random_bytes(16));

/* ---------- Handler POST: Pengajuan PDLN Baru ---------- */
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'submit_pdln') {
    $sessionCsrf   = $_SESSION['csrf'] ?? '';
    $submittedCsrf = (string)($_POST['csrf'] ?? '');

    if (!is_string($sessionCsrf) || !hash_equals($sessionCsrf, $submittedCsrf)) {
        $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Sesi tidak valid, silakan muat ulang halaman.'];
    } else {
        if (!is_dir($uploadDir)) {
            @mkdir($uploadDir, 0755, true);
        }

        // 1. Data Kegiatan
        $emailPengaju   = trim((string)($_POST['email_pengaju'] ?? ''));
        $jenisKegiatan  = trim((string)($_POST['jenis_kegiatan'] ?? ''));
        $namaKegiatan   = trim((string)($_POST['nama_kegiatan'] ?? ''));
        $tanggalMulai   = trim((string)($_POST['tanggal_mulai'] ?? ''));
        $tanggalSelesai = trim((string)($_POST['tanggal_selesai'] ?? ''));
        $institusi      = trim((string)($_POST['institusi_tujuan'] ?? ''));
        $tempat         = trim((string)($_POST['tempat_kegiatan'] ?? ''));
        $rencana        = trim((string)($_POST['rencana_tindak_lanjut'] ?? ''));

        // 2. Upload Dokumen PDLN
        $docKeys = ['tor', 'memo', 'surat_tugas', 'undangan', 'jadwal', 'setneg', 'relevansi', 'pembiayaan', 'draft_mou', 'brosur', 'bukti_registrasi', 'clearance_letter'];
        $uploadedDocs = [];
        foreach ($docKeys as $dk) {
            $inputName = 'doc_' . $dk;
            if (isset($_FILES[$inputName]) && $_FILES[$inputName]['error'] === UPLOAD_ERR_OK) {
                $f = $_FILES[$inputName];
                $safeName = $dk . '_' . time() . '_' . preg_replace('/[^a-zA-Z0-9_\.-]/', '_', $f['name']);
                if (move_uploaded_file($f['tmp_name'], $uploadDir . $safeName)) {
                    $uploadedDocs[$dk] = $safeName;
                }
            }
        }

        // 3. Data Peserta Kegiatan
        $pesertaPost = $_POST['peserta'] ?? [];
        $pesertaFiles = $_FILES['peserta'] ?? null;
        $pesertaList = [];

        foreach ($pesertaPost as $pIdx => $p) {
            $ktpFile = null;
            $pasporFile = null;

            if ($pesertaFiles && isset($pesertaFiles['name'][$pIdx]['ktp']) && $pesertaFiles['error'][$pIdx]['ktp'] === UPLOAD_ERR_OK) {
                $fName = 'ktp_' . $pIdx . '_' . time() . '_' . preg_replace('/[^a-zA-Z0-9_\.-]/', '_', $pesertaFiles['name'][$pIdx]['ktp']);
                if (move_uploaded_file($pesertaFiles['tmp_name'][$pIdx]['ktp'], $uploadDir . $fName)) {
                    $ktpFile = $fName;
                }
            }
            if ($pesertaFiles && isset($pesertaFiles['name'][$pIdx]['paspor']) && $pesertaFiles['error'][$pIdx]['paspor'] === UPLOAD_ERR_OK) {
                $fName = 'paspor_' . $pIdx . '_' . time() . '_' . preg_replace('/[^a-zA-Z0-9_\.-]/', '_', $pesertaFiles['name'][$pIdx]['paspor']);
                if (move_uploaded_file($pesertaFiles['tmp_name'][$pIdx]['paspor'], $uploadDir . $fName)) {
                    $pasporFile = $fName;
                }
            }

            $pesertaList[] = [
                'nama'          => (string)($p['nama'] ?? ''),
                'nip'           => (string)($p['nip'] ?? ''),
                'nik'           => (string)($p['nik'] ?? ''),
                'paspor'        => (string)($p['paspor'] ?? ''),
                'jenis_jabatan' => (string)($p['jenis_jabatan'] ?? ''),
                'nama_jabatan'  => (string)($p['nama_jabatan'] ?? ''),
                'email'         => (string)($p['email'] ?? ''),
                'telepon'       => (string)($p['telepon'] ?? ''),
                'ktp_file'      => $ktpFile,
                'paspor_file'   => $pasporFile,
            ];
        }

        if (!isset($_SESSION['pengajuan'])) {
            $_SESSION['pengajuan'] = [];
        }

        $newId = count($_SESSION['pengajuan']) > 0 ? max(array_column($_SESSION['pengajuan'], 'id')) + 1 : 101;
        $primaryPeserta = $pesertaList[0]['nama'] ?? 'Peserta PDLN';
        $primaryJabatan = $pesertaList[0]['jenis_jabatan'] ?? 'Peserta';

        $_SESSION['pengajuan'][] = [
            'id'                    => $newId,
            'kegiatan'              => $namaKegiatan,
            'peserta'               => $primaryPeserta,
            'jabatan'               => $primaryJabatan,
            'tanggal'               => $tanggalMulai ?: date('Y-m-d'),
            'status'                => 'Baru Diajukan',
            'email_pengaju'         => $emailPengaju,
            'jenis_kegiatan'        => $jenisKegiatan,
            'tanggal_mulai'         => $tanggalMulai,
            'tanggal_selesai'       => $tanggalSelesai,
            'institusi_tujuan'      => $institusi,
            'tempat_kegiatan'       => $tempat,
            'rencana_tindak_lanjut' => $rencana,
            'peserta_list'          => $pesertaList,
            'dokumen'               => $uploadedDocs,
            'created_at'            => date('Y-m-d H:i:s')
        ];

        $_SESSION['flash'] = [
            'type' => 'success',
            'msg'  => "Formulir Ajuan PDLN \"{$namaKegiatan}\" berhasil dikirim!"
        ];
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
    <form action="" method="POST" enctype="multipart/form-data" class="space-y-7">
      <input type="hidden" name="action" value="submit_pdln">
      <input type="hidden" name="csrf" value="<?= $e($_SESSION['csrf']) ?>">

      <!-- 01 DATA KEGIATAN -->
      <div class="space-y-4">
        <div class="flex items-center gap-2.5 border-b border-slate-100 pb-2">
          <span class="flex items-center justify-center w-6 h-6 rounded-md bg-[#008C95] text-white text-xs font-bold shadow-xs">01</span>
          <span class="font-bold text-xs uppercase tracking-wider text-[#008C95]">DATA KEGIATAN</span>
        </div>

        <div>
          <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
            EMAIL PENGAJU <span class="text-rose-500">*</span>
          </label>
          <input type="email" name="email_pengaju" required placeholder="contoh@pnj.ac.id"
            class="w-full px-3.5 py-2.5 text-sm bg-white border border-slate-300 rounded-xl text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-[#008C95] focus:border-transparent transition">
        </div>

        <div>
          <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
            JENIS KEGIATAN <span class="text-rose-500">*</span>
          </label>
          <select name="jenis_kegiatan" required
            class="w-full px-3.5 py-2.5 text-sm bg-white border border-slate-300 rounded-xl text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#008C95] focus:border-transparent transition">
            <option value="">Pilih jenis kegiatan</option>
            <option value="Konferensi / Seminar">Konferensi / Seminar</option>
            <option value="Kompetisi">Kompetisi / Lomba / Hackathon</option>
            <option value="Pelatihan">Pelatihan / Workshop / Short Course</option>
            <option value="Pameran / Expo">Pameran / Expo</option>
            <option value="Studi Banding">Studi Banding / Kunjungan Kerja</option>
            <option value="Riset Kolaborasi">Riset Kolaborasi</option>
          </select>
        </div>

        <div>
          <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
            NAMA KEGIATAN <span class="text-rose-500">*</span>
          </label>
          <input type="text" name="nama_kegiatan" required placeholder="Nama konferensi, seminar, dsb."
            class="w-full px-3.5 py-2.5 text-sm bg-white border border-slate-300 rounded-xl text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-[#008C95] focus:border-transparent transition">
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
              TANGGAL KEGIATAN MULAI <span class="text-rose-500">*</span>
            </label>
            <input type="date" name="tanggal_mulai" required
              class="w-full px-3.5 py-2.5 text-sm bg-white border border-slate-300 rounded-xl text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#008C95] focus:border-transparent transition">
          </div>
          <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
              TANGGAL KEGIATAN SELESAI <span class="text-rose-500">*</span>
            </label>
            <input type="date" name="tanggal_selesai" required
              class="w-full px-3.5 py-2.5 text-sm bg-white border border-slate-300 rounded-xl text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#008C95] focus:border-transparent transition">
          </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
              NAMA INSTITUSI TUJUAN <span class="text-rose-500">*</span>
            </label>
            <input type="text" name="institusi_tujuan" required placeholder="Nama universitas / lembaga"
              class="w-full px-3.5 py-2.5 text-sm bg-white border border-slate-300 rounded-xl text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-[#008C95] focus:border-transparent transition">
          </div>
          <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
              TEMPAT KEGIATAN <span class="text-rose-500">*</span>
            </label>
            <input type="text" name="tempat_kegiatan" required placeholder="Contoh: Tokyo, Jepang"
              class="w-full px-3.5 py-2.5 text-sm bg-white border border-slate-300 rounded-xl text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-[#008C95] focus:border-transparent transition">
          </div>
        </div>

        <div>
          <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
            RENCANA TINDAK LANJUT <span class="text-rose-500">*</span>
          </label>
          <textarea name="rencana_tindak_lanjut" rows="3" required placeholder="Tuliskan rencana tindak lanjut pasca kegiatan..."
            class="w-full px-3.5 py-2.5 text-sm bg-white border border-slate-300 rounded-xl text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-[#008C95] focus:border-transparent transition"></textarea>
        </div>
      </div>

      <!-- 02 UPLOAD DOKUMEN PDLN -->
      <div class="space-y-4 pt-3 border-t border-slate-100">
        <div class="flex items-center gap-2.5 border-b border-slate-100 pb-2">
          <span class="flex items-center justify-center w-6 h-6 rounded-md bg-[#008C95] text-white text-xs font-bold shadow-xs">02</span>
          <span class="font-bold text-xs uppercase tracking-wider text-[#008C95]">UPLOAD DOKUMEN PDLN</span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          
          <?php
          $docsConfig = [
            ['key' => 'tor', 'label' => 'TOR KEGIATAN', 'req' => true, 'badge' => null, 'tmpl' => true],
            ['key' => 'memo', 'label' => 'MEMO PERSETUJUAN', 'req' => false, 'badge' => null, 'tmpl' => true],
            ['key' => 'surat_tugas', 'label' => 'SURAT TUGAS', 'req' => true, 'badge' => null, 'tmpl' => true],
            ['key' => 'undangan', 'label' => 'UNDANGAN', 'req' => true, 'badge' => null, 'tmpl' => true],
            ['key' => 'jadwal', 'label' => 'JADWAL KEGIATAN', 'req' => true, 'badge' => null, 'tmpl' => true],
            ['key' => 'setneg', 'label' => 'SURAT PERSETUJUAN SETNEG', 'req' => true, 'badge' => null, 'tmpl' => true],
            ['key' => 'relevansi', 'label' => 'SURAT KETERANGAN RELEVANSI', 'req' => true, 'badge' => null, 'tmpl' => true],
            ['key' => 'pembiayaan', 'label' => 'SURAT PERNYATAAN PEMBIAYAAN', 'req' => true, 'badge' => null, 'tmpl' => true],
            ['key' => 'draft_mou', 'label' => 'DRAFT MOU / MOA', 'req' => false, 'badge' => 'OPSIONAL', 'tmpl' => true],
            ['key' => 'brosur', 'label' => 'BROSUR', 'req' => false, 'badge' => 'OPSIONAL', 'tmpl' => false],
            ['key' => 'bukti_registrasi', 'label' => 'BUKTI REGISTRASI', 'req' => false, 'badge' => 'OPSIONAL', 'tmpl' => false],
            ['key' => 'clearance_letter', 'label' => 'CLEARANCE LETTER', 'req' => false, 'badge' => 'JIKA ADA', 'tmpl' => false],
          ];
          foreach ($docsConfig as $d):
          ?>
            <div>
              <div class="flex items-center justify-between mb-1.5">
                <div class="flex items-center gap-1.5">
                  <span class="text-xs font-bold uppercase tracking-wider text-slate-700">
                    <?= $d['label'] ?> <?= $d['req'] ? '<span class="text-rose-500">*</span>' : '' ?>
                  </span>
                  <?php if ($d['badge']): ?>
                    <span class="px-1.5 py-0.5 text-[10px] font-bold uppercase bg-indigo-50 text-indigo-600 rounded">
                      <?= $d['badge'] ?>
                    </span>
                  <?php endif; ?>
                </div>
                <?php if ($d['tmpl']): ?>
                  <a href="#" class="text-xs font-semibold text-[#008C95] hover:underline">Template</a>
                <?php endif; ?>
              </div>
              <label class="group relative flex items-center gap-3 p-2 border-2 border-dashed border-cyan-200/90 hover:border-[#008C95] rounded-xl bg-cyan-50/20 hover:bg-cyan-50/50 transition cursor-pointer">
                <input type="file" name="doc_<?= $d['key'] ?>" class="pdln-file-input sr-only" data-target="name-<?= $d['key'] ?>" accept=".pdf">
                <span class="px-3 py-1.5 text-xs font-medium bg-slate-100 group-hover:bg-white text-slate-700 border border-slate-300 rounded-lg shrink-0 transition">Choose File</span>
                <span id="name-<?= $d['key'] ?>" class="text-xs text-slate-500 truncate flex-1 font-mono">No file chosen</span>
              </label>
            </div>
          <?php endforeach; ?>

        </div>
      </div>

      <!-- 03 DATA PESERTA KEGIATAN -->
      <div class="space-y-4 pt-3 border-t border-slate-100">
        <div class="flex items-center justify-between border-b border-slate-100 pb-2">
          <div class="flex items-center gap-2.5">
            <span class="flex items-center justify-center w-6 h-6 rounded-md bg-[#008C95] text-white text-xs font-bold shadow-xs">03</span>
            <span class="font-bold text-xs uppercase tracking-wider text-[#008C95]">DATA PESERTA KEGIATAN</span>
          </div>
          <button type="button" id="btn-add-peserta" class="inline-flex items-center gap-1 px-3 py-1 text-xs font-semibold text-[#008C95] hover:bg-cyan-50 rounded-lg border border-[#008C95]/30 transition cursor-pointer">
            + Tambah Peserta
          </button>
        </div>

        <div id="peserta-container" class="space-y-6">
          <!-- Default Peserta 1 -->
          <div class="peserta-card border border-slate-200/90 rounded-2xl p-5 bg-white space-y-4 shadow-xs relative">
            <div class="flex items-center justify-between border-b border-slate-100 pb-2.5">
              <span class="text-xs font-bold uppercase tracking-wider text-[#008C95]">PESERTA 1</span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                  NAMA LENGKAP & GELAR <span class="text-rose-500">*</span>
                </label>
                <input type="text" name="peserta[0][nama]" required
                  class="w-full px-3.5 py-2 text-sm bg-white border border-slate-300 rounded-xl text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#008C95] focus:border-transparent transition">
              </div>
              <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                  NIP <span class="text-rose-500">*</span>
                </label>
                <input type="text" name="peserta[0][nip]" placeholder="Isi strip (-) jika PPPK/Non PNS" required
                  class="w-full px-3.5 py-2 text-sm bg-white border border-slate-300 rounded-xl text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#008C95] focus:border-transparent transition">
              </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                  NIK <span class="text-rose-500">*</span>
                </label>
                <input type="text" name="peserta[0][nik]" required
                  class="w-full px-3.5 py-2 text-sm bg-white border border-slate-300 rounded-xl text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#008C95] focus:border-transparent transition">
              </div>
              <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                  NOMOR PASPOR <span class="text-rose-500">*</span>
                </label>
                <input type="text" name="peserta[0][paspor]" required
                  class="w-full px-3.5 py-2 text-sm bg-white border border-slate-300 rounded-xl text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#008C95] focus:border-transparent transition">
              </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                  JENIS JABATAN <span class="text-rose-500">*</span>
                </label>
                <select name="peserta[0][jenis_jabatan]" required
                  class="w-full px-3.5 py-2 text-sm bg-white border border-slate-300 rounded-xl text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#008C95] focus:border-transparent transition">
                  <option value="">Pilih jabatan</option>
                  <option value="Dosen">Dosen</option>
                  <option value="Mahasiswa">Mahasiswa</option>
                  <option value="Tenaga Kependidikan">Tenaga Kependidikan</option>
                  <option value="Pimpinan / Struktural">Pimpinan / Struktural</option>
                </select>
              </div>
              <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                  NAMA JABATAN <span class="text-rose-500">*</span>
                </label>
                <input type="text" name="peserta[0][nama_jabatan]" placeholder="Sesuai tertera dalam surat" required
                  class="w-full px-3.5 py-2 text-sm bg-white border border-slate-300 rounded-xl text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#008C95] focus:border-transparent transition">
              </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                  EMAIL PESERTA <span class="text-rose-500">*</span>
                </label>
                <input type="email" name="peserta[0][email]" required
                  class="w-full px-3.5 py-2 text-sm bg-white border border-slate-300 rounded-xl text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#008C95] focus:border-transparent transition">
              </div>
              <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                  NOMOR TELEPON <span class="text-rose-500">*</span>
                </label>
                <input type="text" name="peserta[0][telepon]" required
                  class="w-full px-3.5 py-2 text-sm bg-white border border-slate-300 rounded-xl text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#008C95] focus:border-transparent transition">
              </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">KTP</label>
                <label class="group relative flex items-center gap-3 p-2 border-2 border-dashed border-cyan-200/90 hover:border-[#008C95] rounded-xl bg-cyan-50/20 hover:bg-cyan-50/50 transition cursor-pointer">
                  <input type="file" name="peserta[0][ktp]" class="pdln-file-input sr-only" data-target="name-ktp-0" accept=".pdf,.jpg,.jpeg,.png">
                  <span class="px-3 py-1.5 text-xs font-medium bg-slate-100 group-hover:bg-white text-slate-700 border border-slate-300 rounded-lg shrink-0 transition">Choose File</span>
                  <span id="name-ktp-0" class="text-xs text-slate-500 truncate flex-1 font-mono">No file chosen</span>
                </label>
              </div>
              <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">PASPOR</label>
                <label class="group relative flex items-center gap-3 p-2 border-2 border-dashed border-cyan-200/90 hover:border-[#008C95] rounded-xl bg-cyan-50/20 hover:bg-cyan-50/50 transition cursor-pointer">
                  <input type="file" name="peserta[0][paspor]" class="pdln-file-input sr-only" data-target="name-paspor-0" accept=".pdf,.jpg,.jpeg,.png">
                  <span class="px-3 py-1.5 text-xs font-medium bg-slate-100 group-hover:bg-white text-slate-700 border border-slate-300 rounded-lg shrink-0 transition">Choose File</span>
                  <span id="name-paspor-0" class="text-xs text-slate-500 truncate flex-1 font-mono">No file chosen</span>
                </label>
              </div>
            </div>

            <div>
              <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">APAKAH ADA PESERTA LAINNYA?</label>
              <select class="peserta-lain-select w-full px-3.5 py-2 text-sm bg-white border border-slate-300 rounded-xl text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#008C95] transition">
                <option value="Tidak" selected>Tidak</option>
                <option value="Ya">Ya</option>
              </select>
            </div>
          </div>
        </div>
      </div>

      <!-- Action Buttons -->
      <div class="flex items-center justify-end gap-3 pt-5 border-t border-slate-100">
        <button type="reset" class="px-5 py-2.5 text-sm font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl transition cursor-pointer">
          Batal
        </button>
        <button type="submit" class="px-6 py-2.5 text-sm font-bold text-white bg-[#008C95] hover:bg-[#00747c] active:scale-98 rounded-xl transition shadow-md shadow-[#008C95]/20 cursor-pointer">
          Kirim Pengajuan
        </button>
      </div>
    </form>
  </div>

</div>

<script>
  (function () {
    const bindInputs = () => {
      document.querySelectorAll('.pdln-file-input').forEach(input => {
        input.onchange = function () {
          const target = document.getElementById(this.dataset.target);
          if (target) {
            target.textContent = (this.files && this.files.length > 0) ? this.files[0].name : "No file chosen";
            target.className = (this.files && this.files.length > 0) ? "text-xs text-[#008C95] font-semibold truncate flex-1 font-mono" : "text-xs text-slate-500 truncate flex-1 font-mono";
          }
        };
      });
    };
    bindInputs();

    const container = document.getElementById('peserta-container');
    const btnAdd = document.getElementById('btn-add-peserta');

    const createPesertaCard = (idx) => {
      const card = document.createElement('div');
      card.className = "peserta-card border border-slate-200/90 rounded-2xl p-5 bg-white space-y-4 shadow-xs relative";
      card.innerHTML = `
        <div class="flex items-center justify-between border-b border-slate-100 pb-2.5">
          <span class="text-xs font-bold uppercase tracking-wider text-[#008C95]">PESERTA ${idx + 1}</span>
          <button type="button" class="btn-remove-peserta text-xs text-rose-500 hover:text-rose-700 font-semibold cursor-pointer">
            Hapus Peserta
          </button>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
              NAMA LENGKAP & GELAR <span class="text-rose-500">*</span>
            </label>
            <input type="text" name="peserta[${idx}][nama]" required
              class="w-full px-3.5 py-2 text-sm bg-white border border-slate-300 rounded-xl text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#008C95] focus:border-transparent transition">
          </div>
          <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
              NIP <span class="text-rose-500">*</span>
            </label>
            <input type="text" name="peserta[${idx}][nip]" placeholder="Isi strip (-) jika PPPK/Non PNS" required
              class="w-full px-3.5 py-2 text-sm bg-white border border-slate-300 rounded-xl text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#008C95] focus:border-transparent transition">
          </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
              NIK <span class="text-rose-500">*</span>
            </label>
            <input type="text" name="peserta[${idx}][nik]" required
              class="w-full px-3.5 py-2 text-sm bg-white border border-slate-300 rounded-xl text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#008C95] focus:border-transparent transition">
          </div>
          <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
              NOMOR PASPOR <span class="text-rose-500">*</span>
            </label>
            <input type="text" name="peserta[${idx}][paspor]" required
              class="w-full px-3.5 py-2 text-sm bg-white border border-slate-300 rounded-xl text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#008C95] focus:border-transparent transition">
          </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
              JENIS JABATAN <span class="text-rose-500">*</span>
            </label>
            <select name="peserta[${idx}][jenis_jabatan]" required
              class="w-full px-3.5 py-2 text-sm bg-white border border-slate-300 rounded-xl text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#008C95] focus:border-transparent transition">
              <option value="">Pilih jabatan</option>
              <option value="Dosen">Dosen</option>
              <option value="Mahasiswa">Mahasiswa</option>
              <option value="Tenaga Kependidikan">Tenaga Kependidikan</option>
              <option value="Pimpinan / Struktural">Pimpinan / Struktural</option>
            </select>
          </div>
          <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
              NAMA JABATAN <span class="text-rose-500">*</span>
            </label>
            <input type="text" name="peserta[${idx}][nama_jabatan]" placeholder="Sesuai tertera dalam surat" required
              class="w-full px-3.5 py-2 text-sm bg-white border border-slate-300 rounded-xl text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#008C95] focus:border-transparent transition">
          </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
              EMAIL PESERTA <span class="text-rose-500">*</span>
            </label>
            <input type="email" name="peserta[${idx}][email]" required
              class="w-full px-3.5 py-2 text-sm bg-white border border-slate-300 rounded-xl text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#008C95] focus:border-transparent transition">
          </div>
          <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
              NOMOR TELEPON <span class="text-rose-500">*</span>
            </label>
            <input type="text" name="peserta[${idx}][telepon]" required
              class="w-full px-3.5 py-2 text-sm bg-white border border-slate-300 rounded-xl text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#008C95] focus:border-transparent transition">
          </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">KTP</label>
            <label class="group relative flex items-center gap-3 p-2 border-2 border-dashed border-cyan-200/90 hover:border-[#008C95] rounded-xl bg-cyan-50/20 hover:bg-cyan-50/50 transition cursor-pointer">
              <input type="file" name="peserta[${idx}][ktp]" class="pdln-file-input sr-only" data-target="name-ktp-${idx}" accept=".pdf,.jpg,.jpeg,.png">
              <span class="px-3 py-1.5 text-xs font-medium bg-slate-100 group-hover:bg-white text-slate-700 border border-slate-300 rounded-lg shrink-0 transition">Choose File</span>
              <span id="name-ktp-${idx}" class="text-xs text-slate-500 truncate flex-1 font-mono">No file chosen</span>
            </label>
          </div>
          <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">PASPOR</label>
            <label class="group relative flex items-center gap-3 p-2 border-2 border-dashed border-cyan-200/90 hover:border-[#008C95] rounded-xl bg-cyan-50/20 hover:bg-cyan-50/50 transition cursor-pointer">
              <input type="file" name="peserta[${idx}][paspor]" class="pdln-file-input sr-only" data-target="name-paspor-${idx}" accept=".pdf,.jpg,.jpeg,.png">
              <span class="px-3 py-1.5 text-xs font-medium bg-slate-100 group-hover:bg-white text-slate-700 border border-slate-300 rounded-lg shrink-0 transition">Choose File</span>
              <span id="name-paspor-${idx}" class="text-xs text-slate-500 truncate flex-1 font-mono">No file chosen</span>
            </label>
          </div>
        </div>

        <div>
          <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">APAKAH ADA PESERTA LAINNYA?</label>
          <select class="peserta-lain-select w-full px-3.5 py-2 text-sm bg-white border border-slate-300 rounded-xl text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#008C95] transition">
            <option value="Tidak" selected>Tidak</option>
            <option value="Ya">Ya</option>
          </select>
        </div>
      `;

      card.querySelector('.btn-remove-peserta')?.addEventListener('click', () => {
        card.remove();
        container.querySelectorAll('.peserta-card').forEach((c, i) => {
          c.querySelector('span').textContent = `PESERTA ${i + 1}`;
        });
      });

      card.querySelector('.peserta-lain-select')?.addEventListener('change', function () {
        if (this.value === 'Ya') {
          btnAdd.click();
        }
      });

      return card;
    };

    btnAdd?.addEventListener('click', () => {
      const currentCount = container.querySelectorAll('.peserta-card').length;
      container.appendChild(createPesertaCard(currentCount));
      bindInputs();
    });

    document.querySelectorAll('.peserta-lain-select').forEach(sel => {
      sel.addEventListener('change', function () {
        if (this.value === 'Ya') {
          btnAdd.click();
        }
      });
    });
  })();
</script>
