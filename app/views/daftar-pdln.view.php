<?php

/* ---------- Konfigurasi ---------- */
$uploadDir  = __DIR__ . '/../../public/uploads/sp_setneg/'; // sesuaikan dengan struktur proyekmu
$uploadUrl  = '/uploads/sp_setneg/';
$maxSize    = 5 * 1024 * 1024; // 5 MB
$statusList = ['Diproses', 'Disetujui', 'Ditolak'];
$badge = [
    'Baru Diajukan' => 'bg-sky-100 text-sky-700',
    'Diproses'      => 'bg-amber-100 text-amber-700',
    'Disetujui'     => 'bg-green-100 text-green-700',
    'Ditolak'       => 'bg-red-100 text-red-700',
];
$e = fn($v) => htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');

/* ---------- Data dummy ---------- */
if (!isset($_SESSION['pengajuan'])) {
    $_SESSION['pengajuan'] = [
        ['id' => 101, 'kegiatan' => 'Enthufest',          'peserta' => 'Ahmad Zulfikar',     'jabatan' => 'Mahasiswa',  'tanggal' => '2026-04-09', 'status' => 'Baru Diajukan', 'sp_setneg' => null],
        ['id' => 102, 'kegiatan' => 'Hackathon',          'peserta' => 'Dr. Siti Nurhaliza', 'jabatan' => 'Dosen',      'tanggal' => '2026-05-02', 'status' => 'Disetujui',     'sp_setneg' => null],
        ['id' => 103, 'kegiatan' => 'Campfest',           'peserta' => 'Budi Santoso',       'jabatan' => 'Tendik',     'tanggal' => '2026-05-20', 'status' => 'Diproses',      'sp_setneg' => null],
        ['id' => 104, 'kegiatan' => 'Techno Expo',        'peserta' => 'Rina Marlina',       'jabatan' => 'Mahasiswa',  'tanggal' => '2026-06-03', 'status' => 'Ditolak',       'sp_setneg' => null],
        ['id' => 105, 'kegiatan' => 'Seminar Nasional',   'peserta' => 'Prof. Hendra Wijaya', 'jabatan' => 'Struktural', 'tanggal' => '2026-06-17', 'status' => 'Baru Diajukan', 'sp_setneg' => null],
        ['id' => 106, 'kegiatan' => 'Robotics Challenge', 'peserta' => 'Dimas Prasetyo',     'jabatan' => 'Mahasiswa',  'tanggal' => '2026-06-28', 'status' => 'Disetujui',     'sp_setneg' => null],
    ];
}
$_SESSION['csrf'] = $_SESSION['csrf'] ?? bin2hex(random_bytes(16));

/* ---------- Handler POST ---------- */
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'update_status') {
    $error  = null;
    $id     = (int)($_POST['pengajuan_id'] ?? 0);
    $status = (string)($_POST['status'] ?? '');
    $idx    = array_search($id, array_column($_SESSION['pengajuan'], 'id'), true);

    $sessionCsrf = $_SESSION['csrf'] ?? '';
    $submittedCsrf = (string)($_POST['csrf'] ?? '');

    if (!is_string($sessionCsrf) || !hash_equals($sessionCsrf, $submittedCsrf)) {
        $error = 'Sesi tidak valid, silakan muat ulang halaman.';
    } elseif ($idx === false) {
        $error = 'Data pengajuan tidak ditemukan.';
    } elseif (!in_array($status, $statusList, true)) {
        $error = 'Status tidak valid.';
    }

    // Upload SP Setneg (opsional kalau sudah pernah diupload)
    $file = $_FILES['sp_setneg'] ?? null;
    if (!$error && $file && $file['error'] !== UPLOAD_ERR_NO_FILE) {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $error = 'Upload gagal, coba lagi.';
        } elseif ($file['size'] > $maxSize) {
            $error = 'Ukuran file maksimal 5 MB.';
        } elseif ((new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']) !== 'application/pdf') {
            $error = 'File harus berformat PDF.';
        } else {
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            $name = 'sp_' . $id . '_' . bin2hex(random_bytes(6)) . '.pdf';
            if (move_uploaded_file($file['tmp_name'], $uploadDir . $name)) {
                $_SESSION['pengajuan'][$idx]['sp_setneg'] = $name;
            } else {
                $error = 'File gagal disimpan di server.';
            }
        }
    }

    // Aturan: untuk "Disetujui", SP Setneg wajib ada
    if (!$error && $status === 'Disetujui' && empty($_SESSION['pengajuan'][$idx]['sp_setneg'])) {
        $error = 'Upload SP Setneg dulu sebelum menyetujui pengajuan.';
    }

    if (!$error) {
        $_SESSION['pengajuan'][$idx]['status'] = $status;
        $_SESSION['flash'] = ['type' => 'success', 'msg' => "Pengajuan #{$id} diperbarui menjadi {$status}."];
    } else {
        $_SESSION['flash'] = ['type' => 'error', 'msg' => $error];
    }

    // Post-Redirect-Get (hanya jalan kalau belum ada output; kalau di-include sebagai partial, pindahkan handler ini ke atas index.view.php)
    if (!headers_sent()) {
        header('Location: ' . $_SERVER['REQUEST_URI']);
        exit;
    }
    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);
}

$pengajuan = $_SESSION['pengajuan'];
?>

<div class="bg-white border border-slate-200 rounded-xl shadow-sm p-4">
  <div class="flex items-center mb-4">
    <span class="text-base font-semibold text-slate-800">Recent Country Visit</span>
    <a href="#" class="ml-auto text-sm font-medium text-slate-500 hover:text-slate-800 px-2 py-1 rounded-md hover:bg-slate-100">View all</a>
  </div>

  <?php if ($flash): ?>
    <div class="mb-4 px-4 py-3 rounded-lg text-sm <?= $flash['type'] === 'success' ? 'bg-green-50 text-green-700 border border-green-200' : 'bg-red-50 text-red-700 border border-red-200' ?>">
      <?= $e($flash['msg']) ?>
    </div>
  <?php endif; ?>

  <div class="overflow-x-auto">
    <table class="w-full text-sm text-left">
      <thead class="bg-slate-50 text-xs uppercase text-slate-500">
        <tr>
          <th scope="col" class="px-4 py-3 font-medium">ID</th>
          <th scope="col" class="px-4 py-3 font-medium">Nama Kegiatan</th>
          <th scope="col" class="px-4 py-3 font-medium">Nama Peserta</th>
          <th scope="col" class="px-4 py-3 font-medium">Jabatan</th>
          <th scope="col" class="px-4 py-3 font-medium">Tanggal Keberangkatan</th>
          <th scope="col" class="px-4 py-3 font-medium">Status</th>
          <th scope="col" class="px-4 py-3 font-medium">Aksi</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-100">
        <?php if (empty($pengajuan)): ?>
          <tr>
            <td colspan="7" class="px-4 py-3 text-slate-500">Belum ada pengajuan.</td>
          </tr>
        <?php endif; ?>

        <?php foreach ($pengajuan as $p): ?>
          <tr class="hover:bg-slate-50 align-middle">
            <th scope="row" class="px-4 py-3 font-normal">
              <code class="font-mono text-xs text-blue-600">#<?= (int)$p['id'] ?></code>
            </th>
            <td class="px-4 py-3 font-medium text-slate-800"><?= $e($p['kegiatan']) ?></td>
            <td class="px-4 py-3 text-slate-600 whitespace-nowrap"><?= $e($p['peserta']) ?></td>
            <td class="px-4 py-3 text-slate-600"><?= $e($p['jabatan']) ?></td>
            <td class="px-4 py-3 text-slate-600 whitespace-nowrap"><?= $e(date('d M Y', strtotime($p['tanggal']))) ?></td>
            <td class="px-4 py-3">
              <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $badge[$p['status']] ?? 'bg-slate-100 text-slate-700' ?>">
                <?= $e($p['status']) ?>
              </span>
            </td>
            <td class="px-4 py-3">
              <button type="button" data-open-modal
                data-id="<?= (int)$p['id'] ?>"
                data-kegiatan="<?= $e($p['kegiatan']) ?>"
                data-peserta="<?= $e($p['peserta']) ?>"
                data-status="<?= $e($p['status']) ?>"
                data-sp="<?= $e($p['sp_setneg'] ?? '') ?>"
                class="px-3 py-1 text-xs font-medium text-blue-600 border border-blue-200 rounded-md hover:bg-blue-50">
                View
              </button>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Modal: Upload SP Setneg + Update Status -->
<div id="modal-spsetneg" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/20 rounded-4xl p-4">
  <div class="w-full max-w-md bg-white rounded-xl shadow-lg p-6">
    <div class="flex items-start justify-between mb-4">
      <div>
        <h3 class="text-lg font-semibold text-slate-800">Detail Pengajuan</h3>
        <p id="modal-info" class="text-sm text-slate-500"></p>
      </div>
      <button type="button" data-close-modal class="text-slate-400 hover:text-slate-700 text-xl leading-none">&times;</button>
    </div>

    <form action="" method="POST" enctype="multipart/form-data" class="space-y-4">
      <input type="hidden" name="action" value="update_status">
      <input type="hidden" name="csrf" value="<?= $e($_SESSION['csrf']) ?>">
      <input type="hidden" name="pengajuan_id" id="modal-id">

      <div>
        <label for="modal-status" class="block text-sm font-medium text-slate-700 mb-1">Status</label>
        <select id="modal-status" name="status" required
          class="block w-full text-sm text-slate-700 border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-200">
          <?php foreach ($statusList as $s): ?>
            <option value="<?= $e($s) ?>"><?= $e($s) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div>
        <label for="sp_setneg" class="block text-sm font-medium text-slate-700 mb-1">File SP Setneg (PDF, maks 5 MB)</label>
        <input type="file" id="sp_setneg" name="sp_setneg" accept="application/pdf"
          class="block w-full text-sm text-slate-600 border border-slate-300 rounded-lg cursor-pointer file:mr-3 file:px-3 file:py-2 file:border-0 file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200">
        <p id="modal-sp-current" class="hidden mt-2 text-xs text-slate-500">
          File saat ini:
          <a id="modal-sp-link" href="#" target="_blank" class="text-blue-600 hover:underline"></a>
        </p>
        <p class="mt-1 text-xs text-slate-400">Wajib ada SP Setneg untuk status Disetujui.</p>
      </div>

      <div class="flex justify-end gap-2 pt-2">
        <button type="button" data-close-modal class="px-4 py-2 text-sm font-medium text-slate-600 rounded-lg hover:bg-slate-100">Batal</button>
        <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700">Simpan</button>
      </div>
    </form>
  </div>
</div>

<script>
  (function () {
    const modal = document.getElementById("modal-spsetneg");
    const uploadUrl = <?= json_encode($uploadUrl) ?>;

    const toggle = (show) => {
      modal.classList.toggle("hidden", !show);
      modal.classList.toggle("flex", show);
    };

    document.querySelectorAll("[data-open-modal]").forEach((btn) => {
      btn.addEventListener("click", () => {
        const d = btn.dataset;
        document.getElementById("modal-id").value = d.id;
        document.getElementById("modal-info").textContent = "#" + d.id + " · " + d.kegiatan + " · " + d.peserta;
        document.getElementById("sp_setneg").value = "";

        // Status "Baru Diajukan" tidak ada di pilihan, default ke Diproses
        const select = document.getElementById("modal-status");
        select.value = [...select.options].some((o) => o.value === d.status) ? d.status : "Diproses";

        const cur = document.getElementById("modal-sp-current");
        const link = document.getElementById("modal-sp-link");
        if (d.sp) {
          link.textContent = d.sp;
          link.href = uploadUrl + encodeURIComponent(d.sp);
          cur.classList.remove("hidden");
        } else {
          cur.classList.add("hidden");
        }
        toggle(true);
      });
    });

    document.querySelectorAll("[data-close-modal]").forEach((b) => b.addEventListener("click", () => toggle(false)));
    modal.addEventListener("click", (ev) => { if (ev.target === modal) toggle(false); });
    document.addEventListener("keydown", (ev) => { if (ev.key === "Escape") toggle(false); });
  })();
</script>