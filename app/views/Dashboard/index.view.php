<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= ["pdln" => "Daftar PDLN", "outbound" => "Daftar Outbound", "inbound" => "Daftar Inbound", "tiket" => "Daftar Tiket"][$page ?? "dashboard"] ?? "Dashboard" ?></title>
    <link rel="stylesheet" href="<?= (defined("BASE_URL") ? BASE_URL : "") ?>/dist/style.css">
</head>
<body class="min-h-screen bg-gradient-to-br  from-[#d1ddc1] via-[#9ec2be] to-[#D9E7E8] bg-fixed font-sans text-slate-800">

    <!-- Tombol Toggle Mobile -->
    <button data-drawer-target="separator-sidebar" data-drawer-toggle="separator-sidebar" aria-controls="separator-sidebar" type="button" class="ms-3 mt-3 inline-flex sm:hidden p-2 text-sm text-slate-600 rounded-lg hover:bg-slate-100 focus:outline-none focus:ring-4 focus:ring-slate-200">
        <span class="sr-only">Open sidebar</span>
        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-width="2" d="M5 7h14M5 12h14M5 17h10"/></svg>
    </button>

    <!-- Sidebar -->
    <aside id="separator-sidebar" class="fixed top-0 left-0 z-40 w-64 h-screen transition-transform -translate-x-full sm:translate-x-0 bg-white sm:bg-transparent flex flex-col px-5 py-6" aria-label="Sidebar">

        <!-- Logo -->
        <div class="flex items-center gap-2 px-2 mb-10">
            <img src="<?= (defined('BASE_URL') ? BASE_URL : '') ?>/assets/images/LogoPNJ.png" alt="Logo PNJ" class="w-20 h-20 object-contain">
            <span class="text-2gixl font-bold text-slate-800">STIO PNJ</span>
        </div>

        <!-- Menu -->
        <ul class="space-y-2 text-sm font-medium flex-1">
            <?php
            $basePath = defined("BASE_URL") ? rtrim(BASE_URL, "/") : "";
            $activePage = $page ?? "dashboard";
            $menu = [
                ["key" => "dashboard", "href" => ($basePath ?: "") . "/",            "label" => "Dashboard",       "icon" => "M3 12l9-9 9 9M5 10v10h5v-6h4v6h5V10"],
                ["key" => "pdln",      "href" => ($basePath ?: "") . "/daftar-pdln", "label" => "Daftar PDLN",     "icon" => "M3 12a9 9 0 1018 0 9 9 0 00-18 0zm0 0h18M12 3c2.5 2.5 3.5 5.5 3.5 9S14.5 18.5 12 21c-2.5-2.5-3.5-5.5-3.5-9S9.5 5.5 12 3z"],
                ["key" => "outbound",  "href" => ($basePath ?: "") . "/daftar-outbound", "label" => "Daftar Outbound", "icon" => "M7 17L17 7m0 0H8m9 0v9"],
                ["key" => "inbound",   "href" => ($basePath ?: "") . "/daftar-inbound",  "label" => "Daftar Inbound",  "icon" => "M17 7L7 17m0 0h9m-9 0V8"],
                ["key" => "tiket",     "href" => ($basePath ?: "") . "/daftar-tiket",    "label" => "Daftar Tiket",    "icon" => "M3 9a2 2 0 002-2h14a2 2 0 002 2v1a2 2 0 100 4v1a2 2 0 00-2 2H5a2 2 0 00-2-2v-1a2 2 0 100-4V9z"],
            ];
            foreach ($menu as $item):
                $isActive = ($activePage === $item["key"]);
            ?>
            <li>
                <a href="<?= $item["href"] ?>" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition <?= $isActive ? "bg-[#008C95] text-white shadow-md shadow-indigo-300/50" : "text-slate-700 hover:bg-white/70" ?>">
                    <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="<?= $item["icon"] ?>"/></svg>
                    <span class="flex-1"><?= $item["label"] ?></span>
                    <?php if ($isActive): ?>
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14m-5-5l5 5-5 5"/></svg>
                    <?php endif; ?>
                </a>
            </li>
            <?php endforeach; ?>
        </ul>

        <!-- Keluar -->
        <a href="<?= ($basePath ?: '') ?>/logout.php" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-slate-700 hover:bg-white/70 transition">
            <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 12H4m12 0l-4 4m4-4l-4-4m3-4h2a3 3 0 013 3v10a3 3 0 01-3 3h-2"/></svg>
            <span>Keluar</span>
        </a>
    </aside>

    <!-- Konten Utama (Berada di sebelah kanan sidebar) -->
    <div class="p-3 sm:ml-64 sm:p-4">
        <div class="rounded-b-lg max-w-8xl mx-auto h-300 rounded-[2rem] bg-white/80 backdrop-blur shadow-sm px-6 py-6 ">
            <?php if (($page ?? "dashboard") === "pdln"): ?>
                <h1 class="text-2xl font-bold mb-6">Daftar PDLN</h1>
                <div>
                    <?php require __DIR__ . "/../daftar-pdln.view.php"; ?>
                </div>
                        <?php elseif (($page ?? "dashboard") === "outbound"): ?>
                <h1 class="text-2xl font-bold mb-6">Daftar Outbound</h1>
                <div>
                    <?php require __DIR__ . "/../daftar-outbound.view.php"; ?>
                </div>
            <?php elseif (($page ?? "dashboard") === "inbound"): ?>
                <h1 class="text-2xl font-bold mb-6">Daftar Inbound</h1>
                <div>
                    <?php require __DIR__ . "/../daftar-inbound.view.php"; ?>
                </div>
            <?php elseif (($page ?? "dashboard") === "tiket"): ?>
                <h1 class="text-2xl font-bold mb-6">Daftar Tiket</h1>
                <div>
                    <?php require __DIR__ . "/../daftar-tiket.view.php"; ?>
                </div>
            <?php else: ?>
                <h1 class="text-2xl font-bold mb-6">Dashboard</h1>

                <!-- Container Grid / Flex untuk menampung komponen -->
                <div class="grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-4 items-start">
                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 md:col-span-2">
                        <?php require __DIR__ . "/pdln-status.php"; ?>
                        <?php require __DIR__ . "/outbound-status.php"; ?>
                        <?php require __DIR__ . "/inbound-status.php"; ?>
                        <?php require __DIR__ . "/tiket-status.php"; ?>
                    </div>

                    <div class="xl:row-span-2">
                        <?php require __DIR__ . "/status.view.php"; ?>
                    </div>

                    <div class="xl:row-span-2">
                        <?php require __DIR__ . "/klasemen.view.php"; ?>
                    </div>
                    
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/flowbite@2.5.2/dist/flowbite.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <?php if (($page ?? "dashboard") === "dashboard"): ?>
    <script>
        let selected = null;
        const refresh = () => {
            const el = document.getElementById("donut-chart");
            if (!el) return;
            const g = chart.w.globals;
            const label = el.querySelector(".apexcharts-datalabel-label");
            const value = el.querySelector(".apexcharts-datalabel-value");
            if (selected === null) {
                if (label) label.textContent = "Total pengguna";
                if (value) value.textContent = g.seriesTotals.reduce((a, b) => a + b, 0);
            } else {
                if (label) label.textContent = g.labels[selected];
                if (value) value.textContent = g.series[selected];
            }
        };

        const chart = new ApexCharts(document.getElementById("donut-chart"), {
    chart: {
        type: "donut",
        height: 326,
        fontFamily: "Inter, sans-serif",
        events: {
            dataPointSelection: (e, ctx, cfg) => {
                selected = selected === cfg.dataPointIndex ? null : cfg.dataPointIndex;
                setTimeout(refresh, 0);
            },
            dataPointMouseLeave: () => setTimeout(refresh, 0)
        }
    },
    series: [40, 10, 60, 10],
    labels: ["Dosen", "Tendik", "Mahasiswa", "Struktural"],
    colors: ["#4aa8f8", "#8584e8", "#f4a340", "#FF6467"],
    stroke: { width: 2, colors: ["#fff"] },
    dataLabels: {
    enabled: true,
    style: { fontSize: "12px", fontWeight: 600, colors: ["#fff"] },
    formatter: (val, opts) => opts.w.globals.series[opts.seriesIndex],
    dropShadow: { enabled: false }
},
    legend: {
        position: "bottom",
        fontSize: "13px",
        markers: { size: 6, offsetX: -2 },
        itemMargin: { horizontal: 8, vertical: 4 }
    },
    plotOptions: {
        pie: {
            donut: {
                size: "55%",
                labels: {
                    show: true,
                    name: { offsetY: 20, color: "#64748b", fontSize: "13px" },
                    value: { offsetY: -20, color: "#1e293b", fontSize: "26px", fontWeight: 700 },
                    total: {
                        show: true,
                        label: "Total pengguna",
                        fontSize: "12px",
                        color: "#64748b",
                        formatter: (w) => w.globals.seriesTotals.reduce((a, b) => a + b, 0)
                    }
                }
            }
        }
<<<<<<< HEAD
    }
});
chart.render();
const dataset = {
    "7d":  [8, 2, 15, 3],
    "1m":  [40, 10, 60, 10],
    "1y":  [120, 30, 340, 40],
    "ytd": [95, 25, 280, 30],
};
const activeCls = ["bg-slate-800", "text-white"];
const idleCls   = ["bg-slate-100", "text-slate-600", "hover:bg-slate-200"];

document.querySelectorAll(".range-btn").forEach(btn => {
    btn.addEventListener("click", () => {
        document.querySelectorAll(".range-btn").forEach(b => {
            b.classList.remove(...activeCls);
            b.classList.add(...idleCls);
        });
        btn.classList.remove(...idleCls);
        btn.classList.add(...activeCls);

        selected = null;
        chart.updateSeries(dataset[btn.dataset.range]);
        setTimeout(refresh, 0);
    });
});
    </script>
    <?php endif; ?>
=======
    });
    chart.render();
</script>
</body>
>>>>>>> master
</body>
</html>