<?php

/* ---------- Konfigurasi ---------- */
$statusList = ['Diajukan', 'Pending', 'Tuntas'];
$badge = [
    'Diajukan' => 'bg-sky-100 text-sky-700',
    'Pending'  => 'bg-amber-100 text-amber-700',
    'Tuntas'   => 'bg-green-100 text-green-700',
];
$e = fn($v) => htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');

/* ---------- Data dummy ---------- */
// Validasi struktur session tiket agar tidak bentrok dengan data tiket penerbangan lama
if (!isset($_SESSION['tiket']) || !isset($_SESSION['tiket'][0]['pelapor'])) {
    $_SESSION['tiket'] = [
        ['id' => 501, 'pelapor' => 'Rina Marlina',        'jabatan' => 'Mahasiswa',  'kategori' => 'Teknis',      'judul' => 'Form pengajuan PDLN tidak bisa disubmit', 'deskripsi' => 'Tombol kirim tidak merespons setelah semua kolom diisi.', 'tanggal' => '2026-10-20', 'status' => 'Diajukan', 'tanggapan' => ''],
        ['id' => 502, 'pelapor' => 'Dr. Siti Nurhaliza',  'jabatan' => 'Dosen',      'kategori' => 'Administrasi','judul' => 'Data paspor salah tercatat',             'deskripsi' => 'Nomor paspor di sistem tidak sesuai dokumen.',          'tanggal' => '2026-10-18', 'status' => 'Pending',  'tanggapan' => 'Menunggu dokumen pembanding dari pelapor.'],
        ['id' => 503, 'pelapor' => 'Dimas Prasetyo',      'jabatan' => 'Mahasiswa',  'kategori' => 'Layanan',     'judul' => 'Status pengajuan tidak berubah',         'deskripsi' => 'Sudah seminggu masih berstatus diajukan.',              'tanggal' => '2026-10-10', 'status' => 'Tuntas',   'tanggapan' => 'Pengajuan sudah diproses dan statusnya diperbarui.'],
        ['id' => 504, 'pelapor' => 'Prof. Hendra Wijaya', 'jabatan' => 'Struktural', 'kategori' => 'Teknis',      'judul' => 'Tidak bisa unduh surat tugas',           'deskripsi' => 'File surat tugas gagal diunduh (error 404).',           'tanggal' => '2026-10-22', 'status' => 'Diajukan', 'tanggapan' => ''],
        ['id' => 505, 'pelapor' => 'Budi Santoso',        'jabatan' => 'Tendik',     'kategori' => 'Lainnya',     'judul' => 'Usulan penambahan kolom instansi',       'deskripsi' => 'Mohon ditambahkan kolom instansi di daftar outbound.',  'tanggal' => '2026-10-05', 'status' => 'Tuntas',   'tanggapan' => 'Usulan sudah diteruskan dan diimplementasikan.'],
        ['id' => 506, 'pelapor' => 'Ahmad Zulfikar',      'jabatan' => 'Mahasiswa',  'kategori' => 'Layanan',     'judul' => 'Jadwal keberangkatan berubah',           'deskripsi' => 'Tanggal berangkat perlu direvisi dari 15 ke 17 Juli.',  'tanggal' => '2026-10-23', 'status' => 'Pending',  'tanggapan' => 'Menunggu konfirmasi dari pihak penyelenggara.'],
    ];
}
$_SESSION['csrf'] = $_SESSION['csrf'] ?? bin2hex(random_bytes(16));

/* ---------- Handler POST ---------- */
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'update_tiket') {
    $error     = null;
    $id        = (int)($_POST['tiket_id'] ?? 0);
    $status    = (string)($_POST['status'] ?? '');
    $tanggapan = trim((string)($_POST['tanggapan'] ?? ''));
    $idx       = array_search($id, array_column($_SESSION['tiket'], 'id'), true);

    $sessionCsrf   = $_SESSION['csrf'] ?? '';
    $submittedCsrf = (string)($_POST['csrf'] ?? '');

    if (!is_string($sessionCsrf) || !hash_equals($sessionCsrf, $submittedCsrf)) {
        $error = 'Sesi tidak valid, silakan muat ulang halaman.';
    } elseif ($idx === false) {
        $error = 'Data tiket tidak ditemukan.';
    } elseif (!in_array($status, $statusList, true)) {
        $error = 'Status tidak valid.';
    } elseif (mb_strlen($tanggapan) > 1000) {
        $error = 'Tanggapan maksimal 1000 karakter.';
    } elseif ($status === 'Tuntas' && $tanggapan === '') {
        $error = 'Isi tanggapan dulu sebelum menandai tuntas.';
    }

    if (!$error) {
        $_SESSION['tiket'][$idx]['status']    = $status;
        $_SESSION['tiket'][$idx]['tanggapan'] = $tanggapan;
        $_SESSION['flash'] = ['type' => 'success', 'msg' => "Tiket #{$id} diperbarui menjadi {$status}."];
    } else {
        $_SESSION['flash'] = ['type' => 'error', 'msg' => $error];
    }

    if (!headers_sent()) {
        header('Location: ' . $_SERVER['REQUEST_URI']);
        exit;
    }
    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);
}

$tiket = $_SESSION['tiket'];
?>

<div class="bg-white border border-slate-200 rounded-xl shadow-sm p-4">
  <div class="flex items-center mb-4">
    <span class="text-base font-semibold text-slate-800">Daftar Tiket Pelaporan</span>
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
          <th scope="col" class="px-4 py-3 font-medium">Pelapor</th>
          <th scope="col" class="px-4 py-3 font-medium">Jabatan</th>
          <th scope="col" class="px-4 py-3 font-medium">Kategori</th>
          <th scope="col" class="px-4 py-3 font-medium">Judul Laporan</th>
          <th scope="col" class="px-4 py-3 font-medium">Tanggal</th>
          <th scope="col" class="px-4 py-3 font-medium">Status</th>
          <th scope="col" class="px-4 py-3 font-medium">Aksi</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-100">
        <?php if (empty($tiket)): ?>
          <tr>
            <td colspan="8" class="px-4 py-3 text-slate-500">Belum ada tiket pelaporan.</td>
          </tr>
        <?php endif; ?>

        <?php foreach ($tiket as $t): ?>
          <tr class="hover:bg-slate-50 align-middle">
            <th scope="row" class="px-4 py-3 font-normal">
              <code class="font-mono text-xs text-blue-600">#<?= (int)$t['id'] ?></code>
            </th>
            <td class="px-4 py-3 font-medium text-slate-800 whitespace-nowrap"><?= $e($t['pelapor']) ?></td>
            <td class="px-4 py-3 text-slate-600"><?= $e($t['jabatan']) ?></td>
            <td class="px-4 py-3 text-slate-600"><?= $e($t['kategori']) ?></td>
            <td class="px-4 py-3 text-slate-600"><?= $e($t['judul']) ?></td>
            <td class="px-4 py-3 text-slate-600 whitespace-nowrap"><?= $e(date('d M Y', strtotime($t['tanggal']))) ?></td>
            <td class="px-4 py-3">
              <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $badge[$t['status']] ?? 'bg-slate-100 text-slate-700' ?>">
                <?= $e($t['status']) ?>
              </span>
            </td>
            <td class="px-4 py-3">
              <button type="button" data-open-modal
                data-id="<?= (int)$t['id'] ?>"
                data-pelapor="<?= $e($t['pelapor']) ?>"
                data-kategori="<?= $e($t['kategori']) ?>"
                data-judul="<?= $e($t['judul']) ?>"
                data-deskripsi="<?= $e($t['deskripsi']) ?>"
                data-status="<?= $e($t['status']) ?>"
                data-tanggapan="<?= $e($t['tanggapan']) ?>"
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

<!-- Modal: Detail Laporan + Update Status -->
<div id="modal-tiket" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/20 rounded-4xl p-4">
  <div class="w-full max-w-md bg-white rounded-xl shadow-lg p-6">
    <div class="flex items-start justify-between mb-4">
      <div>
        <h3 class="text-lg font-semibold text-slate-800">Detail Tiket</h3>
        <p id="modal-info" class="text-sm text-slate-500"></p>
      </div>
      <button type="button" data-close-modal class="text-slate-400 hover:text-slate-700 text-xl leading-none">&times;</button>
    </div>

    <div class="mb-4 rounded-lg bg-slate-50 p-3">
      <p id="modal-judul" class="text-sm font-medium text-slate-800"></p>
      <p id="modal-deskripsi" class="mt-1 text-sm text-slate-600"></p>
    </div>

    <form action="" method="POST" class="space-y-4">
      <input type="hidden" name="action" value="update_tiket">
      <input type="hidden" name="csrf" value="<?= $e($_SESSION['csrf']) ?>">
      <input type="hidden" name="tiket_id" id="modal-id">

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
        <label for="modal-tanggapan" class="block text-sm font-medium text-slate-700 mb-1">Tanggapan</label>
        <textarea id="modal-tanggapan" name="tanggapan" rows="3" maxlength="1000"
          class="block w-full text-sm text-slate-700 border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-200"
          placeholder="Tulis tanggapan atau catatan penyelesaian..."></textarea>
        <p class="mt-1 text-xs text-slate-400">Wajib diisi untuk status Tuntas.</p>
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
        const modal = document.getElementById("modal-tiket"); // sesuaikan id per halaman
document.body.appendChild(modal);
    const toggle = (show) => {
      modal.classList.toggle("hidden", !show);
      modal.classList.toggle("flex", show);
    };

    document.querySelectorAll("[data-open-modal]").forEach((btn) => {
      btn.addEventListener("click", () => {
        const d = btn.dataset;
        document.getElementById("modal-id").value = d.id;
        document.getElementById("modal-info").textContent = "#" + d.id + " · " + d.pelapor + " (" + d.kategori + ")";
        document.getElementById("modal-judul").textContent = d.judul;
        document.getElementById("modal-deskripsi").textContent = d.deskripsi;
        document.getElementById("modal-status").value = d.status;
        document.getElementById("modal-tanggapan").value = d.tanggapan || "";
        toggle(true);
      });
    });

    document.querySelectorAll("[data-close-modal]").forEach((b) => b.addEventListener("click", () => toggle(false)));
    modal.addEventListener("click", (ev) => { if (ev.target === modal) toggle(false); });
    document.addEventListener("keydown", (ev) => { if (ev.key === "Escape") toggle(false); });
  })();
</script>