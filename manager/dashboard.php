<?php include __DIR__ . '/inc/header.php'; ?>
<div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-3">
    <div>
        <div class="text-uppercase small fw-semibold opacity-75">Manager overview</div>
        <h2 class="mb-0 mt-2">Operations dashboard</h2>
    </div>
    <a href="reports.php" class="btn btn-light text-success fw-semibold">View reports</a>
</div>

<?php
$totals = [
    'admins' => (int) mysqli_fetch_assoc(mysqli_query($db, "SELECT COUNT(*) AS total FROM users WHERE role IN (1,4,5) AND status = 1"))['total'],
    'farmers' => (int) mysqli_fetch_assoc(mysqli_query($db, "SELECT COUNT(*) AS total FROM users WHERE role = 2"))['total'],
    'transactions' => (int) mysqli_fetch_assoc(mysqli_query($db, "SELECT COUNT(*) AS total FROM order_list"))['total'],
    'tax' => (float) mysqli_fetch_assoc(mysqli_query($db, "SELECT COALESCE(SUM(tax_amount),0) AS total FROM order_list"))['total'],
    'staff' => (float) mysqli_fetch_assoc(mysqli_query($db, "SELECT COALESCE(SUM(salary),0) AS total FROM staff_payroll WHERE status = 1"))['total'],
    'costs' => (float) mysqli_fetch_assoc(mysqli_query($db, "SELECT COALESCE(SUM(amount),0) AS total FROM extra_costs"))['total'],
    'subscriptions' => (int) mysqli_fetch_assoc(mysqli_query($db, "SELECT COUNT(*) AS total FROM farmer_subscriptions WHERE status = 1"))['total'],
    'pending' => (int) mysqli_fetch_assoc(mysqli_query($db, "SELECT COUNT(*) AS total FROM users WHERE role = 2 AND status = 2"))['total'],
];

$recentOrders = mysqli_query($db, "SELECT or_name, user_phone, price, tax_amount, total_amount, quantity, status, join_date FROM order_list ORDER BY join_date DESC LIMIT 5");
$recentSubscriptions = mysqli_query($db, "SELECT fs.id, u.user_name, fs.subscription_name, fs.amount, fs.status, fs.created_at FROM farmer_subscriptions fs LEFT JOIN users u ON u.user_id = fs.farmer_id ORDER BY fs.created_at DESC LIMIT 5");
$taxRules = mysqli_query($db, "SELECT rule_name, rate_percent, min_quantity, max_quantity, applies_to FROM tax_rules WHERE status = 1 ORDER BY rate_percent DESC LIMIT 5");

$trendLabels = [];
$trendTotals = [];
$trendTaxes = [];
for ($daysAgo = 29; $daysAgo >= 0; $daysAgo--) {
    $dateKey = date('Y-m-d', strtotime("-$daysAgo days"));
    $trendLabels[] = date('M j', strtotime($dateKey));
    $trendTotals[$dateKey] = 0;
    $trendTaxes[$dateKey] = 0;
}
$trendQuery = mysqli_query($db, "SELECT DATE(join_date) AS order_day, COALESCE(SUM(total_amount), 0) AS sales_total, COALESCE(SUM(tax_amount), 0) AS tax_total FROM order_list WHERE join_date >= DATE_SUB(CURDATE(), INTERVAL 29 DAY) GROUP BY DATE(join_date) ORDER BY order_day");
if ($trendQuery) {
    while ($trend = mysqli_fetch_assoc($trendQuery)) {
        if (isset($trendTotals[$trend['order_day']])) {
            $trendTotals[$trend['order_day']] = (float) $trend['sales_total'];
            $trendTaxes[$trend['order_day']] = (float) $trend['tax_total'];
        }
    }
}

$statusLabels = ['Pending', 'Processing', 'Completed', 'Cancelled'];
$statusCounts = [0, 0, 0, 0];
$statusQuery = mysqli_query($db, "SELECT status, COUNT(*) AS total FROM order_list GROUP BY status");
if ($statusQuery) {
    while ($status = mysqli_fetch_assoc($statusQuery)) {
        $statusIndex = (int) $status['status'];
        if ($statusIndex >= 0 && $statusIndex <= 3) {
            $statusCounts[$statusIndex] = (int) $status['total'];
        }
    }
}

$costChartQuery = mysqli_query($db, "SELECT COALESCE((SELECT SUM(salary) FROM staff_payroll WHERE status = 1), 0) AS payroll_total, COALESCE((SELECT SUM(amount) FROM extra_costs), 0) AS extra_costs_total");
$costChart = $costChartQuery ? mysqli_fetch_assoc($costChartQuery) : ['payroll_total' => 0, 'extra_costs_total' => 0];
?>

<div class="row g-4 mb-4">
    <div class="col-lg-3 col-md-6">
        <div class="card metric p-4 h-100">
            <div class="small text-uppercase text-muted">Managers</div>
            <h3 class="mt-2 mb-1"><?php echo number_format($totals['admins']); ?></h3>
            <small class="text-success">accounts active</small>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="card metric p-4 h-100">
            <div class="small text-uppercase text-muted">Farmers</div>
            <h3 class="mt-2 mb-1"><?php echo number_format($totals['farmers']); ?></h3>
            <small class="text-success"><?php echo number_format($totals['pending']); ?> pending approval</small>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="card metric p-4 h-100">
            <div class="small text-uppercase text-muted">Transactions</div>
            <h3 class="mt-2 mb-1"><?php echo number_format($totals['transactions']); ?></h3>
            <small class="text-success">UGX <?php echo number_format($totals['tax'], 2); ?> taxes logged</small>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="card metric p-4 h-100">
            <div class="small text-uppercase text-muted">Active subscriptions</div>
            <h3 class="mt-2 mb-1"><?php echo number_format($totals['subscriptions']); ?></h3>
            <small class="text-success">UGX <?php echo number_format($totals['costs'], 2); ?> extra costs</small>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-xl-8">
        <div class="card p-4 h-100">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h5 class="mb-1">Sales performance</h5>
                    <small class="text-muted">Daily sales and tax collected over the last 30 days</small>
                </div>
                <i class="fa-solid fa-chart-line text-success fs-4"></i>
            </div>
            <div class="chart-wrap"><canvas id="salesTrendChart"></canvas></div>
        </div>
    </div>
    <div class="col-xl-4">
        <div class="card p-4 h-100">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h5 class="mb-1">Order status</h5>
                    <small class="text-muted">Current transaction distribution</small>
                </div>
                <i class="fa-solid fa-chart-pie text-success fs-4"></i>
            </div>
            <div class="chart-wrap chart-wrap-doughnut"><canvas id="orderStatusChart"></canvas></div>
        </div>
    </div>
</div>

<div class="row g-4 mt-1">
    <div class="col-xl-5">
        <div class="card p-4 h-100">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h5 class="mb-1">Operating costs</h5>
                    <small class="text-muted">Active payroll compared with extra costs</small>
                </div>
                <i class="fa-solid fa-chart-column text-success fs-4"></i>
            </div>
            <div class="chart-wrap"><canvas id="costChart"></canvas></div>
        </div>
    </div>
</div>

<div class="row g-4 mt-1">
    <div class="col-xl-8">
        <div class="card p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0">Recent transactions</h5>
                <a href="transactions.php" class="btn btn-sm btn-outline-success">Open transactions</a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover data-table">
                    <thead>
                        <tr>
                            <th>Order</th>
                            <th>Customer</th>
                            <th>Qty</th>
                            <th>Subtotal</th>
                            <th>Tax</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($recentOrders) > 0): while ($order = mysqli_fetch_assoc($recentOrders)): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($order['or_name']); ?></td>
                                <td><?php echo htmlspecialchars($order['user_phone'] ?: 'Walk-in'); ?></td>
                                <td><?php echo (int) $order['quantity']; ?></td>
                                <td>UGX <?php echo number_format((float) $order['price'], 2); ?></td>
                                <td>UGX <?php echo number_format((float) $order['tax_amount'], 2); ?></td>
                                <td>UGX <?php echo number_format((float) $order['total_amount'], 2); ?></td>
                            </tr>
                        <?php endwhile; else: ?>
                            <tr><td colspan="6" class="text-center text-muted">No transactions yet.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-xl-4">
        <div class="card p-4 h-100">
            <h5 class="mb-3">Live tax rules</h5>
            <div class="list-group list-group-flush">
                <?php if (mysqli_num_rows($taxRules) > 0): while ($rule = mysqli_fetch_assoc($taxRules)): ?>
                    <div class="list-group-item px-0">
                        <div class="d-flex justify-content-between">
                            <strong><?php echo htmlspecialchars($rule['rule_name']); ?></strong>
                            <span class="badge bg-success-subtle text-success"><?php echo number_format((float) $rule['rate_percent'], 2); ?>%</span>
                        </div>
                        <small class="text-muted">Qty <?php echo (int) $rule['min_quantity']; ?> to <?php echo $rule['max_quantity'] ? (int) $rule['max_quantity'] : 'unlimited'; ?> · <?php echo htmlspecialchars($rule['applies_to']); ?></small>
                    </div>
                <?php endwhile; else: ?>
                    <div class="text-muted">No tax rules configured.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mt-1">
    <div class="col-xl-6">
        <div class="card p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0">Recent farmer subscriptions</h5>
                <a href="farmers.php" class="btn btn-sm btn-outline-primary">Manage</a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover data-table">
                    <thead>
                        <tr>
                            <th>Farmer</th>
                            <th>Plan</th>
                            <th>Amount</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($recentSubscriptions) > 0): while ($subscription = mysqli_fetch_assoc($recentSubscriptions)): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($subscription['user_name'] ?: 'Farmer'); ?></td>
                                <td><?php echo htmlspecialchars($subscription['subscription_name']); ?></td>
                                <td>UGX <?php echo number_format((float) $subscription['amount'], 2); ?></td>
                                <td>
                                    <?php if ((int) $subscription['status'] === 1): ?>
                                        <span class="badge bg-success">Approved</span>
                                    <?php elseif ((int) $subscription['status'] === 2): ?>
                                        <span class="badge bg-warning text-dark">Rejected</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Pending</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; else: ?>
                            <tr><td colspan="4" class="text-center text-muted">No subscriptions yet.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-xl-6">
        <div class="card p-4">
            <h5 class="mb-3">Management summary</h5>
            <ul class="list-group list-group-flush">
                <li class="list-group-item d-flex justify-content-between px-0"><span>Payroll</span><strong>UGX <?php echo number_format($totals['staff'], 2); ?></strong></li>
                <li class="list-group-item d-flex justify-content-between px-0"><span>Extra costs</span><strong>UGX <?php echo number_format($totals['costs'], 2); ?></strong></li>
                <li class="list-group-item d-flex justify-content-between px-0"><span>Approved subscriptions</span><strong><?php echo number_format($totals['subscriptions']); ?></strong></li>
                <li class="list-group-item d-flex justify-content-between px-0"><span>Pending farmer approvals</span><strong><?php echo number_format($totals['pending']); ?></strong></li>
            </ul>
        </div>
    </div>
</div>

<script>
    const chartFont = { family: 'Arial, sans-serif' };
    const currencyTooltip = (value) => 'UGX ' + Number(value).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });

    new Chart(document.getElementById('salesTrendChart'), {
        type: 'line',
        data: {
            labels: <?php echo json_encode($trendLabels); ?>,
            datasets: [
                {
                    label: 'Sales',
                    data: <?php echo json_encode(array_values($trendTotals)); ?>,
                    borderColor: '#0d8b47',
                    backgroundColor: 'rgba(13, 139, 71, 0.12)',
                    fill: true,
                    tension: 0.35,
                    pointRadius: 2
                },
                {
                    label: 'Tax',
                    data: <?php echo json_encode(array_values($trendTaxes)); ?>,
                    borderColor: '#f59e0b',
                    backgroundColor: 'rgba(245, 158, 11, 0.08)',
                    fill: true,
                    tension: 0.35,
                    pointRadius: 2
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            scales: { x: { grid: { display: false } }, y: { beginAtZero: true } },
            plugins: {
                legend: { position: 'top', labels: { font: chartFont } },
                tooltip: { callbacks: { label: (context) => context.dataset.label + ': ' + currencyTooltip(context.raw) } }
            }
        }
    });

    new Chart(document.getElementById('orderStatusChart'), {
        type: 'doughnut',
        data: {
            labels: <?php echo json_encode($statusLabels); ?>,
            datasets: [{
                data: <?php echo json_encode($statusCounts); ?>,
                backgroundColor: ['#f59e0b', '#38bdf8', '#10b981', '#ef4444'],
                borderWidth: 3,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '66%',
            plugins: { legend: { position: 'bottom', labels: { font: chartFont, padding: 14 } } }
        }
    });

    new Chart(document.getElementById('costChart'), {
        type: 'bar',
        data: {
            labels: ['Payroll', 'Extra costs'],
            datasets: [{
                label: 'UGX',
                data: [<?php echo (float) $costChart['payroll_total']; ?>, <?php echo (float) $costChart['extra_costs_total']; ?>],
                backgroundColor: ['#2563eb', '#f97316'],
                borderRadius: 7,
                maxBarThickness: 56
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: { x: { grid: { display: false } }, y: { beginAtZero: true } },
            plugins: {
                legend: { display: false },
                tooltip: { callbacks: { label: (context) => currencyTooltip(context.raw) } }
            }
        }
    });
</script>

<?php include __DIR__ . '/inc/footer.php'; ?>
