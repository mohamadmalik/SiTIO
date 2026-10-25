<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="/dist/style.css">
</head>
<body class="bg-slate-50 font-sans text-slate-800">
    <div class="max-w-8xl mx-auto px-6 py-8">
        <h1 class="text-2xl font-bold mb-6">Dashboard</h1>

        <!-- Container Grid / Flex untuk menampung kedua komponen -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-start">
            
            <!-- Komponen Status -->
            <div>
                <?php require __DIR__ . '/status.view.php'; ?>
            </div>

            <!-- Komponen Klasemen -->
            <div>
                <?php require __DIR__ . '/klasemen.view.php'; ?>
            </div>

            <div>
                <?php require __DIR__ . '/daftar-pdln.php'; ?>
            </div>

        </div>
    </div>



        <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
    let selected = null;
    const refresh = () => {
        const el = document.getElementById("donut-chart");
        const g = chart.w.globals;
        const label = el.querySelector(".apexcharts-datalabel-label");
        const value = el.querySelector(".apexcharts-datalabel-value");
        if (selected === null) {
            label.textContent = "Total pengguna";
            value.textContent = g.seriesTotals.reduce((a, b) => a + b, 0);
        } else {
            label.textContent = g.labels[selected];
            value.textContent = g.series[selected];
        }
    };

    const chart = new ApexCharts(document.getElementById("donut-chart"), {
        chart: {
            type: "donut",
            height: 220,
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
        colors: ["#008C95", "#F5B52E", "#D9361E", "#16A34A"],
        stroke: { width: 0 },
        dataLabels: { enabled: false },
        legend: {
            position: "bottom",
            fontSize: "14px",
            markers: { size: 6, offsetX: -2 },
            itemMargin: { horizontal: 10, vertical: 4 }
        },
        plotOptions: {
            pie: {
                donut: {
                    size: "78%",
                    labels: {
                        show: true,
                        name: { offsetY: 20, color: "#64748b", fontSize: "14px" },
                        value: { offsetY: -20, color: "#1e293b", fontSize: "28px", fontWeight: 700 },
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
        }
    });
    chart.render();
</script>
</body>
</body>
</html>