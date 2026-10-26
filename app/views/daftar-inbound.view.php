<?php

/* ---------- Konfigurasi ---------- */
$uploadDir  = __DIR__ . '/../../public/uploads/laporan_inbound/'; // sesuaikan dengan struktur proyekmu
$uploadUrl  = '/uploads/laporan_inbound/';
$maxSize    = 5 * 1024 * 1024; // 5 MB
$statusList = ['Terjadwal', 'Berlangsung', 'Selesai', 'Dibatalkan'];
$badge = [
    'Terjadwal'   => 'bg-sky-100 text-sky-700',
    'Berlangsung' => 'bg-amber-100 text-amber-700',
    'Selesai'     => 'bg-green-100 text-green-700',
    'Dibatalkan'  => 'bg-red-100 text-red-700',
];
$e = fn($v) => htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');

/* ---------- Data dummy ---------- */
if (!isset($_SESSION['inbound'])) {
    $_SESSION['inbound'] = [
        ['id' => 401, 'kegiatan' => 'Guest Lecture',          'negara' => 'Jepang',        'peserta' => 'Prof. Kenji Tanaka',   'instansi' => 'Tokyo Institute of Technology', 'datang' => '2026-11-10', 'pulang' => '2026-11-14', 'status' => 'Terjadwal',   'dokumen' => null],
        ['id' => 402, 'kegiatan' => 'Joint Research',         'negara' => 'Jerman',        'peserta' => 'Dr. Anna Schmidt',     'instansi' => 'TU Munich',                     'datang' => '2026-10-22', 'pulang' => '2026-10-28', 'status' => 'Berlangsung', 'dokumen' => null],
        ['id' => 403, 'kegiatan' => 'Student Exchange',       'negara' => 'Malaysia',      'peserta' => 'Nur Aisyah',           'instansi' => 'Universiti Teknologi Malaysia', 'datang' => '2026-08-05', 'pulang' => '2026-09-05', 'status' => 'Selesai',     'dokumen' => null],
        ['id' => 404, 'kegiatan' => 'Kunjungan Benchmarking', 'negara' => 'Singapura',     'peserta' => 'Dr. Wei Lim',          'instansi' => 'Singapore Polytechnic',         'datang' => '2026-12-02', 'pulang' => '2026-12-04', 'status' => 'Terjadwal',   'dokumen' => null],
        ['id' => 405, 'kegiatan' => 'Workshop Teknis',        'negara' => 'Korea Selatan', 'peserta' => 'Min-jun Park',         'instansi' => 'KAIST',                         'datang' => '2026-09-15', 'pulang' => '2026-09-18', 'status' => 'Dibatalkan',  'dokumen' => null],
        ['id' => 406, 'kegiatan' => 'Seminar Internasional',  'negara' => 'Australia',     'peserta' => 'Prof. Emma Wilson',    'instansi' => 'University of Melbourne',       'datang' => '2026-07-20', 'pulang' => '2026-07-23', 'status' => 'Selesai',     'dokumen' => null],
    ];
}
$_SESSION['csrf'] = $_SESSION['csrf'] ?? bin2hex(random_bytes(16));

/* ---------- Handler POST ---------- */
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'update_inbound') {
    $error  = null;
    $id     = (int)($_POST['inbound_id'] ?? 0);
    $status = (string)($_POST['status'] ?? '');
    $idx    = array_search($id, array_column($_SESSION['inbound'], 'id'), true);

    $sessionCsrf   = $_SESSION['csrf'] ?? '';
    $submittedCsrf = (string)($_POST['csrf'] ?? '');

    if (!is_string($sessionCsrf) || !hash_equals($sessionCsrf, $submittedCsrf)) {
        $error = 'Sesi tidak valid, silakan muat ulang halaman.';
    } elseif ($idx === false) {
        $error = 'Data inbound tidak ditemukan.';
    } elseif (!in_array($status, $statusList, true)) {
        $error = 'Status tidak valid.';
    }

    // Upload laporan kunjungan (opsional kalau sudah pernah diupload)
    $file = $_FILES['dokumen'] ?? null;
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
            $name = 'laporan_' . $id . '_' . bin2hex(random_bytes(6)) . '.pdf';
            if (move_uploaded_file($file['tmp_name'], $uploadDir . $name)) {
                $_SESSION['inbound'][$idx]['dokumen'] = $name;
            } else {
                $error = 'File gagal disimpan di server.';
            }
        }
    }

    // Aturan: untuk "Selesai", laporan kunjungan wajib ada
    if (!$error && $status === 'Selesai' && empty($_SESSION['inbound'][$idx]['dokumen'])) {
        $error = 'Upload laporan kunjungan dulu sebelum menandai selesai.';
    }

    if (!$error) {
        $_SESSION['inbound'][$idx]['status'] = $status;
        $_SESSION['flash'] = ['type' => 'success', 'msg' => "Inbound #{$id} diperbarui menjadi {$status}."];
    } else {
        $_SESSION['flash'] = ['type' => 'error', 'msg' => $error];
    }

    // Post-Redirect-Get (hanya jalan kalau belum ada output)
    if (!headers_sent()) {
        header('Location: ' . $_SERVER['REQUEST_URI']);
        exit;
    }
    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);
}

$inbound = $_SESSION['inbound'];
?>

<div class="bg-white border border-slate-200 rounded-xl shadow-sm p-4">
  <div class="flex items-center mb-4">
    <span class="text-base font-semibold text-slate-800">Daftar Inbound</span>
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
          <th scope="col" class="px-4 py-3 font-medium">Negara Asal</th>
          <th scope="col" class="px-4 py-3 font-medium">Nama Tamu</th>
          <th scope="col" class="px-4 py-3 font-medium">Instansi</th>
          <th scope="col" class="px-4 py-3 font-medium">Periode</th>
          <th scope="col" class="px-4 py-3 font-medium">Status</th>
          <th scope="col" class="px-4 py-3 font-medium">Aksi</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-100">
        <?php if (empty($inbound)): ?>
          <tr>
            <td colspan="8" class="px-4 py-3 text-slate-500">Belum ada data inbound.</td>
          </tr>
        <?php endif; ?>

        <?php foreach ($inbound as $o): ?>
          <tr class="hover:bg-slate-50 align-middle">
            <th scope="row" class="px-4 py-3 font-normal">
              <code class="font-mono text-xs text-blue-600">#<?= (int)$o['id'] ?></code>
            </th>
            <td class="px-4 py-3 font-medium text-slate-800"><?= $e($o['kegiatan']) ?></td>
            <td class="px-4 py-3 text-slate-600"><?= $e($o['negara']) ?></td>
            <td class="px-4 py-3 text-slate-600 whitespace-nowrap"><?= $e($o['peserta']) ?></td>
            <td class="px-4 py-3 text-slate-600"><?= $e($o['instansi']) ?></td>
            <td class="px-4 py-3 text-slate-600 whitespace-nowrap">
              <?= $e(date('d M', strtotime($o['datang']))) ?> – <?= $e(date('d M Y', strtotime($o['pulang']))) ?>
            </td>
            <td class="px-4 py-3">
              <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $badge[$o['status']] ?? 'bg-slate-100 text-slate-700' ?>">
                <?= $e($o['status']) ?>
              </span>
            </td>
            <td class="px-4 py-3">
              <button type="button" data-open-modal
                data-id="<?= (int)$o['id'] ?>"
                data-kegiatan="<?= $e($o['kegiatan']) ?>"
                data-peserta="<?= $e($o['peserta']) ?>"
                data-negara="<?= $e($o['negara']) ?>"
                data-status="<?= $e($o['status']) ?>"
                data-doc="<?= $e($o['dokumen'] ?? '') ?>"
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

<!-- Modal: Update Status + Upload Laporan Kunjungan -->
<div id="modal-inbound" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/20 rounded-4xl p-4">
  <div class="w-full max-w-md bg-white rounded-xl shadow-lg p-6">
    <div class="flex items-start justify-between mb-4">
      <div>
        <h3 class="text-lg font-semibold text-slate-800">Detail Inbound</h3>
        <p id="modal-info" class="text-sm text-slate-500"></p>
      </div>
      <button type="button" data-close-modal class="text-slate-400 hover:text-slate-700 text-xl leading-none">&times;</button>
    </div>

    <form action="" method="POST" enctype="multipart/form-data" class="space-y-4">
      <input type="hidden" name="action" value="update_inbound">
      <input type="hidden" name="csrf" value="<?= $e($_SESSION['csrf']) ?>">
      <input type="hidden" name="inbound_id" id="modal-id">

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
        <label for="dokumen" class="block text-sm font-medium text-slate-700 mb-1">Laporan Kunjungan (PDF, maks 5 MB)</label>
        <input type="file" id="dokumen" name="dokumen" accept="application/pdf"
          class="block w-full text-sm text-slate-600 border border-slate-300 rounded-lg cursor-pointer file:mr-3 file:px-3 file:py-2 file:border-0 file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200">
        <p id="modal-doc-current" class="hidden mt-2 text-xs text-slate-500">
          File saat ini:
          <a id="modal-doc-link" href="#" target="_blank" class="text-blue-600 hover:underline"></a>
        </p>
        <p class="mt-1 text-xs text-slate-400">Wajib ada laporan kunjungan untuk status Selesai.</p>
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
        const modal = document.getElementById("modal-inbound"); // sesuaikan id per halaman
document.body.appendChild(modal);
    const uploadUrl = <?= json_encode($uploadUrl) ?>;

    const toggle = (show) => {
      modal.classList.toggle("hidden", !show);
      modal.classList.toggle("flex", show);
    };

    document.querySelectorAll("[data-open-modal]").forEach((btn) => {
      btn.addEventListener("click", () => {
        const d = btn.dataset;
        document.getElementById("modal-id").value = d.id;
        document.getElementById("modal-info").textContent = "#" + d.id + " · " + d.kegiatan + " · " + d.negara + " · " + d.peserta;
        document.getElementById("dokumen").value = "";
        document.getElementById("modal-status").value = d.status;

        const cur = document.getElementById("modal-doc-current");
        const link = document.getElementById("modal-doc-link");
        if (d.doc) {
          link.textContent = d.doc;
          link.href = uploadUrl + encodeURIComponent(d.doc);
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