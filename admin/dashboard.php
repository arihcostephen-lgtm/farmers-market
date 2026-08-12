<?php include"inc/header.php"; ?>

<style>
    .page-wrapper,
    .page-content {
        background: #050a14;
        color: #e9fff4;
    }
    .page-content {
        min-height: 100vh;
    }
    .page-content .card {
        background: #0d1725;
        border-color: rgba(16, 184, 130, 0.3);
        color: #d7ffe8;
        transition: transform .25s ease, box-shadow .25s ease, border-color .25s ease;
    }
    .page-content .card:hover {
        transform: translateY(-3px);
        box-shadow: 0 18px 35px rgba(0, 0, 0, 0.35);
        border-color: #10b981;
    }
    .page-content .card .card-header,
    .page-content .card .card-body {
        background: transparent;
    }
    /* Headings and important text brighter for contrast */
    .page-content h1,
    .page-content h2,
    .page-content h3,
    .page-content h4,
    .page-content h5,
    .card .card-header h5 {
        color: #f3fff7;
    }
    .page-content p,
    .page-content small,
    .page-content .card .card-body,
    .page-content .table td,
    .page-content .table th {
        color: rgba(235, 255, 244, 0.92);
    }
    .page-content .btn-outline-success:hover,
    .page-content .btn-outline-success:focus {
        background-color: #10b981;
        border-color: #10b981;
        color: #fff;
    }
    .page-content .icon-square.bg-success {
        background-color: #10b981 !important;
    }
    .page-content .table thead {
        background: rgba(16, 184, 129, 0.06);
        color: rgba(235,255,244,0.9);
    }
    .page-content .table tbody tr:hover {
        background: rgba(16, 184, 129, 0.06);
    }
    .page-content .text-muted {
        color: rgba(223, 255, 228, 0.7) !important;
    }
    .chart-canvas { width: 100%; height: 320px; }
    @media (max-width: 768px) {
        .chart-canvas { height: 220px; }
    }
</style>

<div class="page-wrapper">
    <div class="page-content">
        <div class="card radius-10 border-0 shadow-sm">
            <div class="card-body">
                <h1 class="mb-2">Welcome to Farmers Market Admin</h1>
        <?php
        $usersCount = (int) $db->query("SELECT COUNT(*) AS total FROM users WHERE status = 1")->fetch_assoc()['total'];
        $farmersCount = (int) $db->query("SELECT COUNT(*) AS total FROM farmer WHERE status = 1")->fetch_assoc()['total'];
        $categoriesCount = (int) $db->query("SELECT COUNT(*) AS total FROM category WHERE status = 1")->fetch_assoc()['total'];
        $ordersCount = (int) $db->query("SELECT COUNT(*) AS total FROM order_list")->fetch_assoc()['total'];
        $pendingOrdersCount = (int) $db->query("SELECT COUNT(*) AS total FROM order_list WHERE status = 0")->fetch_assoc()['total'];
        $completedOrdersCount = (int) $db->query("SELECT COUNT(*) AS total FROM order_list WHERE status = 1")->fetch_assoc()['total'];

        $monthLabels = [];
        $userMonthly = [];
        $farmerMonthly = [];
        $orderMonthly = [];

        for ($i = 5; $i >= 0; $i--) {
            $monthName = date('M', strtotime("-{$i} months"));
            $monthLabels[] = $monthName;
            $userMonthly[$monthName] = 0;
            $farmerMonthly[$monthName] = 0;
            $orderMonthly[$monthName] = 0;
        }

        $monthlyUsers = $db->query("SELECT DATE_FORMAT(join_date, '%b') AS month, COUNT(*) AS total FROM users WHERE status = 1 AND join_date >= DATE_SUB(CURDATE(), INTERVAL 5 MONTH) GROUP BY month ORDER BY MIN(join_date)");
        while ($row = mysqli_fetch_assoc($monthlyUsers)) {
            if (isset($userMonthly[$row['month']])) {
                $userMonthly[$row['month']] = (int) $row['total'];
            }
        }

        $monthlyFarmers = $db->query("SELECT DATE_FORMAT(join_date, '%b') AS month, COUNT(*) AS total FROM farmer WHERE status = 1 AND join_date >= DATE_SUB(CURDATE(), INTERVAL 5 MONTH) GROUP BY month ORDER BY MIN(join_date)");
        while ($row = mysqli_fetch_assoc($monthlyFarmers)) {
            if (isset($farmerMonthly[$row['month']])) {
                $farmerMonthly[$row['month']] = (int) $row['total'];
            }
        }

        $monthlyOrders = $db->query("SELECT DATE_FORMAT(join_date, '%b') AS month, COUNT(*) AS total FROM order_list WHERE join_date >= DATE_SUB(CURDATE(), INTERVAL 5 MONTH) GROUP BY month ORDER BY MIN(join_date)");
        while ($row = mysqli_fetch_assoc($monthlyOrders)) {
            if (isset($orderMonthly[$row['month']])) {
                $orderMonthly[$row['month']] = (int) $row['total'];
            }
        }

        $topCategoryOrders = [];
        $topCategories = $db->query("SELECT c.cat_name, COUNT(o.or_id) AS total_orders FROM category c LEFT JOIN order_list o ON o.or_category = c.cat_id WHERE c.status = 1 GROUP BY c.cat_id ORDER BY total_orders DESC LIMIT 5");
        while ($row = mysqli_fetch_assoc($topCategories)) {
            $topCategoryOrders[] = $row;
        }
        ?>

        <div class="row g-3 mt-3">
            <div class="col-xl-3 col-md-6">
                <div class="card border-success shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-start justify-content-between">
                            <div>
                                <p class="text-uppercase text-success small mb-1">Total Orders</p>
                                <h3 class="fw-bold mb-1"><?php echo number_format($ordersCount); ?></h3>
                                <p class="mb-0 text-success"><i class="fas fa-arrow-up me-1"></i>2.8% this month</p>
                            </div>
                            <div class="icon-square bg-success text-white rounded-circle p-3 shadow-sm">
                                <i class="fas fa-shopping-cart fa-lg"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card border-success shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-start justify-content-between">
                            <div>
                                <p class="text-uppercase text-success small mb-1">Active Users</p>
                                <h3 class="fw-bold mb-1"><?php echo number_format($usersCount); ?></h3>
                                <p class="mb-0 text-success"><i class="fas fa-arrow-up me-1"></i>4.3% growth</p>
                            </div>
                            <div class="icon-square bg-success text-white rounded-circle p-3 shadow-sm">
                                <i class="fas fa-users fa-lg"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card border-success shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-start justify-content-between">
                            <div>
                                <p class="text-uppercase text-success small mb-1">Farmers</p>
                                <h3 class="fw-bold mb-1"><?php echo number_format($farmersCount); ?></h3>
                                <p class="mb-0 text-success"><i class="fas fa-arrow-up me-1"></i>1.9% new farms</p>
                            </div>
                            <div class="icon-square bg-success text-white rounded-circle p-3 shadow-sm">
                                <i class="fas fa-tractor fa-lg"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card border-success shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-start justify-content-between">
                            <div>
                                <p class="text-uppercase text-success small mb-1">Categories</p>
                                <h3 class="fw-bold mb-1"><?php echo number_format($categoriesCount); ?></h3>
                                <p class="mb-0 text-success"><i class="fas fa-arrow-up me-1"></i>5.1% catalog</p>
                            </div>
                            <div class="icon-square bg-success text-white rounded-circle p-3 shadow-sm">
                                <i class="fas fa-tags fa-lg"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3 mt-3">
            <div class="col-md-6 col-lg-6">
                <div class="card border-success shadow-sm h-100">
                    <div class="card-header border-success bg-success bg-opacity-10 text-success">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="mb-0">Monthly Overview</h5>
                                <small class="text-success">Orders, users, and farmers performance</small>
                            </div>
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-sm btn-outline-success active">Orders</button>
                                <button type="button" class="btn btn-sm btn-outline-success">Users</button>
                                <button type="button" class="btn btn-sm btn-outline-success">Farmers</button>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <canvas id="growthHistogram" class="chart-canvas"></canvas>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-lg-6">
                <div class="card border-success shadow-sm h-100">
                    <div class="card-header border-success bg-success bg-opacity-10 text-success">
                        <h5 class="mb-0">Traffic Sources</h5>
                    </div>
                    <div class="card-body text-center">
                        <canvas id="trafficChart" class="chart-canvas"></canvas>
                        <div class="mt-4 text-start">
                            <div class="d-flex justify-content-between mb-2">
                                <span>Direct</span><span class="text-success">35%</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Organic</span><span class="text-success">28%</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Social</span><span class="text-success">15%</span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span>Referral</span><span class="text-success">22%</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4 mt-3">
            <div class="col-12">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Top categories by orders</h5>
                            <small class="text-white-50">Most ordered categories this quarter.</small>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th scope="col">Category</th>
                                        <th scope="col">Orders</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (count($topCategoryOrders) > 0) {
                                        foreach ($topCategoryOrders as $category) {
                                            ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($category['cat_name']); ?></td>
                                                <td><?php echo number_format($category['total_orders']); ?></td>
                                            </tr>
                                        <?php }
                                    } else { ?>
                                        <tr>
                                            <td colspan="2" class="text-center text-muted py-4">No category order data available yet.</td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
  const growthCtx = document.getElementById('growthHistogram').getContext('2d');
  new Chart(growthCtx, {
    type: 'bar',
    data: {
      labels: <?php echo json_encode($monthLabels); ?>,
      datasets: [
        {
          label: 'Users',
          data: <?php echo json_encode(array_values($userMonthly)); ?>,
          backgroundColor: '#16a34a',
          borderRadius: 6
        },
        {
          label: 'Farmers',
          data: <?php echo json_encode(array_values($farmerMonthly)); ?>,
          backgroundColor: '#2563eb',
          borderRadius: 6
        },
        {
          label: 'Orders',
          data: <?php echo json_encode(array_values($orderMonthly)); ?>,
          backgroundColor: '#f59e0b',
          borderRadius: 6
        }
      ]
    },
    options: {
      responsive: true,
      scales: {
        x: { stacked: false, grid: { display: false } },
        y: { beginAtZero: true }
      },
      plugins: {
        legend: { position: 'top' }
      }
    }
  });
</script>

<script>
  const orderStatusCtx = document.getElementById('orderStatusChart')?.getContext('2d');
  if (orderStatusCtx) {
    new Chart(orderStatusCtx, {
      type: 'doughnut',
      data: {
        labels: ['Pending', 'Completed'],
        datasets: [{
          data: [<?php echo $pendingOrdersCount; ?>, <?php echo $completedOrdersCount; ?>],
          backgroundColor: ['#f97316', '#10b981']
        }]
      },
      options: {
        responsive: true,
        cutout: '70%',
        plugins: {
          legend: { position: 'bottom' }
        }
      }
    });
  }

  const trafficCtx = document.getElementById('trafficChart')?.getContext('2d');
  if (trafficCtx) {
    new Chart(trafficCtx, {
      type: 'doughnut',
      data: {
        labels: ['Direct', 'Organic', 'Social', 'Referral'],
        datasets: [{
          data: [35, 28, 15, 22],
          backgroundColor: ['#10b981', '#38bdf8', '#8b5cf6', '#f97316']
        }]
      },
      options: {
        responsive: true,
        cutout: '70%',
        plugins: {
          legend: { display: false }
        }
      }
    });
  }

<?php include"inc/footer.php"; ?>
