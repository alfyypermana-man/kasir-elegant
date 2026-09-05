<?php
require_once __DIR__ . '/config/database.php';

$page_title = 'Dashboard';

$totalItems = (int)$pdo->query('SELECT COUNT(*) FROM item')->fetchColumn();
$totalCustomers = (int)$pdo->query('SELECT COUNT(*) FROM customer')->fetchColumn();
$totalTransactions = (int)$pdo->query('SELECT COUNT(*) FROM "transaction"')->fetchColumn();
$totalRevenue = (float)$pdo->query('SELECT COALESCE(SUM(total_price), 0) FROM "transaction"')->fetchColumn();

$lowStocks = $pdo->query('SELECT * FROM item WHERE stock <= 10 ORDER BY stock ASC, name ASC LIMIT 6')->fetchAll();
$expiredSoon = $pdo->query("SELECT * FROM item WHERE expired_date IS NOT NULL AND expired_date BETWEEN CURRENT_DATE AND (CURRENT_DATE + INTERVAL '45 days') ORDER BY expired_date ASC LIMIT 6")->fetchAll();
$latestTransactions = $pdo->query('SELECT t.*, c.name AS customer_name FROM "transaction" t JOIN customer c ON c.id = t.customer_id ORDER BY t.date DESC, t.id DESC LIMIT 6')->fetchAll();
// Grafik transaksi per bulan
$chartQuery = $pdo->query("
    SELECT
        TO_CHAR(date, 'Mon') AS month,
        EXTRACT(MONTH FROM date)::int AS month_number,
        COUNT(*) AS total_transactions,
        COALESCE(SUM(total_price), 0) AS total_revenue
    FROM \"transaction\"
    WHERE EXTRACT(YEAR FROM date) = EXTRACT(YEAR FROM CURRENT_DATE)
    GROUP BY EXTRACT(MONTH FROM date), TO_CHAR(date, 'Mon')
    ORDER BY month_number
");

$chartData = $chartQuery->fetchAll();
$months = [];
$transactionTotals = [];
$revenueTotals = [];

foreach ($chartData as $row) {
    $months[] = $row['month'];
    $transactionTotals[] = (int)$row['total_transactions'];
    $revenueTotals[] = (float)$row['total_revenue'];
}
require_once __DIR__ . '/includes/header.php';
?>

<section class="grid grid-4">
    <div class="card stat-card">
        <span>Total Item</span>
        <strong><?= e($totalItems) ?></strong>
        <small>Produk aktif di sistem</small>
    </div>
    <div class="card stat-card">
        <span>Customer</span>
        <strong><?= e($totalCustomers) ?></strong>
        <small>Data pelanggan tersimpan</small>
    </div>
    <div class="card stat-card">
        <span>Transaction</span>
        <strong><?= e($totalTransactions) ?></strong>
        <small>Total transaksi dibuat</small>
    </div>
    <div class="card stat-card">
        <span>Revenue</span>
        <strong><?= rupiah($totalRevenue) ?></strong>
        <small>Akumulasi total penjualan</small>
    </div>
</section>
<section class="grid grid-2 mt-18">
    <div class="card card-pad">
        <h2>Grafik Transaksi</h2>
        <canvas id="transactionChart"></canvas>
    </div>

    <div class="card card-pad">
        <h2>Grafik Revenue</h2>
        <canvas id="revenueChart"></canvas>
    </div>
</section>
<section class="grid grid-2 mt-18">
    <div class="card card-pad">
        <h2>Stok Menipis</h2>
        <p class="muted">Item dengan stok 10 atau kurang.</p>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Kode</th>
                        <th>Nama</th>
                        <th>Stok</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!$lowStocks): ?>
                        <tr><td colspan="3" class="empty-state">Tidak ada stok menipis.</td></tr>
                    <?php endif; ?>

                    <?php foreach ($lowStocks as $item): ?>
                        <tr>
                            <td><?= e($item['code']) ?></td>
                            <td><?= e($item['name']) ?></td>
                            <td><span class="badge badge-rose"><?= e($item['stock']) ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="card card-pad">
        <h2>Expired Mendekat</h2>
        <p class="muted">Item yang expired dalam 45 hari ke depan.</p>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Kode</th>
                        <th>Nama</th>
                        <th>Expired</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!$expiredSoon): ?>
                        <tr><td colspan="3" class="empty-state">Tidak ada item mendekati expired.</td></tr>
                    <?php endif; ?>

                    <?php foreach ($expiredSoon as $item): ?>
                        <tr>
                            <td><?= e($item['code']) ?></td>
                            <td><?= e($item['name']) ?></td>
                            <td><span class="badge badge-gold"><?= e(date('d M Y', strtotime($item['expired_date']))) ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<section class="card card-pad mt-18">
    <div class="toolbar">
        <div>
            <h2>Transaksi Terbaru</h2>
            <p class="muted">Ringkasan aktivitas kasir paling akhir.</p>
        </div>
        <a href="<?= url('transactions/index.php') ?>" class="btn btn-ghost">Lihat Semua</a>
    </div>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Tanggal</th>
                    <th>Customer</th>
                    <th>Total</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php if (!$latestTransactions): ?>
                    <tr><td colspan="5" class="empty-state">Belum ada transaksi.</td></tr>
                <?php endif; ?>

                <?php foreach ($latestTransactions as $transaction): ?>
                    <tr>
                        <td>#<?= e($transaction['id']) ?></td>
                        <td><?= e(date('d M Y H:i', strtotime($transaction['date']))) ?></td>
                        <td><?= e($transaction['customer_name']) ?></td>
                        <td><?= rupiah($transaction['total_price']) ?></td>
                        <td class="text-right">
                            <a class="btn btn-small btn-ghost" href="<?= url('transactions/show.php?id=' . $transaction['id']) ?>">Detail</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <style>
canvas {
    height: 320px !important;
}
</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</section>

<script>
const months = <?= json_encode($months) ?>;
const transactionTotals = <?= json_encode($transactionTotals) ?>;
const revenueTotals = <?= json_encode($revenueTotals) ?>;


// ==============================
// GRAFIK TRANSAKSI
// ==============================

const trxCtx = document.getElementById('transactionChart').getContext('2d');

const trxGradient = trxCtx.createLinearGradient(0, 0, 0, 300);
trxGradient.addColorStop(0, 'rgba(59,130,246,0.45)');
trxGradient.addColorStop(1, 'rgba(59,130,246,0.02)');

new Chart(trxCtx, {
    type: 'line',
    data: {
        labels: months,
        datasets: [{
            data: transactionTotals,
            borderColor: '#3b82f6',
            backgroundColor: trxGradient,
            fill: true,
            tension: 0.45,
            borderWidth: 3,
            pointRadius: 0,
            pointHoverRadius: 6,
            pointBackgroundColor: '#3b82f6'
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,

        plugins: {
            legend: {
                display: false
            },

            tooltip: {
                backgroundColor: '#111827',
                padding: 12,
                displayColors: false,
                titleColor: '#fff',
                bodyColor: '#d1d5db'
            }
        },

        scales: {
            x: {
                grid: {
                    display: false
                },
                ticks: {
                    color: '#9ca3af'
                }
            },

            y: {
                grid: {
                    color: 'rgba(156,163,175,0.1)'
                },
                ticks: {
                    color: '#9ca3af'
                },
                border: {
                    display: false
                }
            }
        }
    }
});


// ==============================
// GRAFIK REVENUE
// ==============================

const revCtx = document.getElementById('revenueChart').getContext('2d');

const revGradient = revCtx.createLinearGradient(0, 0, 0, 300);
revGradient.addColorStop(0, 'rgba(16,185,129,0.45)');
revGradient.addColorStop(1, 'rgba(16,185,129,0.02)');

new Chart(revCtx, {
    type: 'line',
    data: {
        labels: months,
      datasets: [{
    data: revenueTotals,
    stepped: true,
    borderColor: '#10b981',
    backgroundColor: revGradient,
    fill: true,
    tension: 0,
    borderWidth: 2,
    pointRadius: 0

        }]
    },

    options: {
        responsive: true,
        maintainAspectRatio: false,

        plugins: {
            legend: {
                display: false
            },

            tooltip: {
                backgroundColor: '#111827',
                padding: 12,
                displayColors: false,
                titleColor: '#fff',
                bodyColor: '#d1d5db',

                callbacks: {
                    label: function(context) {
                        return 'Rp ' + context.raw.toLocaleString('id-ID');
                    }
                }
            }
        },

        scales: {
            x: {
                grid: {
                    display: false
                },
                ticks: {
                    color: '#9ca3af'
                }
            },

            y: {
                grid: {
                    color: 'rgba(156,163,175,0.1)'
                },
                ticks: {
                    color: '#9ca3af',
                    callback: function(value) {
                        return 'Rp ' + value.toLocaleString('id-ID');
                    }
                },
                border: {
                    display: false
                }
            }
        }
    }
});
</script>
</script>
<?php require_once __DIR__ . '/includes/footer.php'; ?>