<?php include "inc/header.php"; ?>

<div class="page-wrapper">
  <div class="page-content">
        <?php
        $viewTotalResult = mysqli_query($db, "SELECT COALESCE(SUM(view_count), 0) AS total FROM products WHERE status != 0");
        $viewTotal = $viewTotalResult ? (int) mysqli_fetch_assoc($viewTotalResult)['total'] : 0;
        $viewedProducts = mysqli_query($db, "SELECT product_name, view_count, seller_email FROM products WHERE status != 0 ORDER BY view_count DESC, product_name ASC LIMIT 10");
        $viewRows = [];
        $viewLabels = [];
        $viewValues = [];
        if ($viewedProducts) {
            while ($viewRow = mysqli_fetch_assoc($viewedProducts)) {
            $viewRows[] = $viewRow;
                $viewLabels[] = $viewRow['product_name'];
                $viewValues[] = (int) $viewRow['view_count'];
            }
        }
        $noViewResult = mysqli_query($db, "SELECT COUNT(*) AS total FROM products WHERE status != 0 AND view_count = 0");
        $noViewCount = $noViewResult ? (int) mysqli_fetch_assoc($noViewResult)['total'] : 0;
        $salesSummary = mysqli_fetch_assoc(mysqli_query($db, "SELECT COUNT(*) AS orders, COALESCE(SUM(CASE WHEN status != 3 THEN price ELSE 0 END), 0) AS revenue, COALESCE(SUM(CASE WHEN status != 3 THEN quantity ELSE 0 END), 0) AS units FROM order_list")) ?: ['orders' => 0, 'revenue' => 0, 'units' => 0];
        $statusCounts = [0 => 0, 1 => 0, 2 => 0, 3 => 0];
        $statusQuery = mysqli_query($db, "SELECT status, COUNT(*) AS total FROM order_list GROUP BY status");
        if ($statusQuery) { while ($statusRow = mysqli_fetch_assoc($statusQuery)) { $statusCounts[(int) $statusRow['status']] = (int) $statusRow['total']; } }
        $monthlySales = [];
        $monthlyRevenue = [];
        for ($monthIndex = 5; $monthIndex >= 0; $monthIndex--) {
          $monthKey = date('M', strtotime("-$monthIndex months"));
          $monthlySales[$monthKey] = 0;
          $monthlyRevenue[$monthKey] = 0;
        }
        $monthlyQuery = mysqli_query($db, "SELECT DATE_FORMAT(join_date, '%b') AS month, COUNT(*) AS orders, COALESCE(SUM(price), 0) AS revenue FROM order_list WHERE status != 3 AND join_date >= DATE_SUB(CURDATE(), INTERVAL 5 MONTH) GROUP BY month ORDER BY MIN(join_date)");
        if ($monthlyQuery) { while ($monthRow = mysqli_fetch_assoc($monthlyQuery)) { if (isset($monthlySales[$monthRow['month']])) { $monthlySales[$monthRow['month']] = (int) $monthRow['orders']; $monthlyRevenue[$monthRow['month']] = (float) $monthRow['revenue']; } } }
        $bestSelling = mysqli_query($db, "SELECT COALESCE(p.product_name, o.or_name) AS product_name, COALESCE(SUM(CASE WHEN o.status != 3 THEN o.quantity ELSE 0 END), 0) AS units, COALESCE(SUM(CASE WHEN o.status != 3 THEN o.price ELSE 0 END), 0) AS revenue FROM order_list o LEFT JOIN products p ON p.product_id=o.or_category GROUP BY o.or_category, o.or_name ORDER BY units DESC LIMIT 10");
        $farmerMetrics = mysqli_query($db, "SELECT p.seller_email, COUNT(DISTINCT p.product_id) AS products, COALESCE(SUM(p.view_count), 0) AS views, COUNT(DISTINCT CASE WHEN o.status != 3 THEN o.or_id END) AS orders, COALESCE(SUM(CASE WHEN o.status != 3 THEN o.price ELSE 0 END), 0) AS revenue FROM products p LEFT JOIN order_list o ON o.or_category=p.product_id WHERE p.seller_email IS NOT NULL AND p.seller_email != '' GROUP BY p.seller_email ORDER BY revenue DESC, views DESC");
        ?>
    <div class="card shadow-sm">
      <div class="card-body">
        <h4>Analytics</h4>
            <p class="text-muted">Product view activity and marketplace performance.</p>

            <div class="row g-3 mb-3">
              <div class="col-md-4">
                <div class="card border-success"><div class="card-body">
                  <small class="text-muted">Total Product Views</small>
                  <h3 class="mb-0"><?php echo number_format($viewTotal); ?></h3>
                </div></div>
              </div>
              <div class="col-md-4"><div class="card border-primary"><div class="card-body"><small class="text-muted">Sales Revenue</small><h3 class="mb-0">UGX <?php echo number_format((float) $salesSummary['revenue'], 2); ?></h3></div></div></div>
              <div class="col-md-4"><div class="card border-info"><div class="card-body"><small class="text-muted">Units Sold</small><h3 class="mb-0"><?php echo number_format((int) $salesSummary['units']); ?></h3></div></div></div>
            </div>

            <div class="row g-3 mb-3"><div class="col-12"><div class="card"><div class="card-body"><h5>Order Status Summary</h5><div class="d-flex flex-wrap gap-3"><span class="badge bg-warning">Pending: <?php echo $statusCounts[0]; ?></span><span class="badge bg-info">Confirmed: <?php echo $statusCounts[1]; ?></span><span class="badge bg-success">Fulfilled: <?php echo $statusCounts[2]; ?></span><span class="badge bg-danger">Cancelled: <?php echo $statusCounts[3]; ?></span></div></div></div></div></div>

        <div class="row">
          <div class="col-md-6">
            <div class="card mt-2"><div class="card-body">
              <canvas id="analyticsChart1"></canvas>
            </div></div>
          </div>
          <div class="col-md-6">
            <div class="card mt-2"><div class="card-body">
              <canvas id="analyticsChart2"></canvas>
            </div></div>
          </div>
        </div>

        <div class="card shadow-sm mt-4"><div class="card-body">
          <h5>Most Viewed Products</h5>
          <div class="table-responsive">
            <table class="table table-striped table-hover">
              <thead><tr><th>Product</th><th>Farmer</th><th>Views</th></tr></thead>
              <tbody>
                <?php if (count($viewRows) > 0) { foreach ($viewRows as $viewRow) { ?>
                    <tr><td><?php echo htmlspecialchars($viewRow['product_name']); ?></td><td><?php echo htmlspecialchars($viewRow['seller_email'] ?: 'N/A'); ?></td><td><?php echo number_format((int) $viewRow['view_count']); ?></td></tr>
                  <?php } } else { ?><tr><td colspan="3" class="text-center text-muted">No product views recorded yet.</td></tr><?php } ?>
              </tbody>
            </table>
          </div>
        </div></div>

        <div class="card shadow-sm mt-4"><div class="card-body"><h5>Sales Report</h5><div class="table-responsive"><table class="table table-striped"><thead><tr><th>Product</th><th>Units Sold</th><th>Revenue</th></tr></thead><tbody><?php if ($bestSelling && mysqli_num_rows($bestSelling)) { while ($sale = mysqli_fetch_assoc($bestSelling)) { ?><tr><td><?php echo htmlspecialchars($sale['product_name']); ?></td><td><?php echo number_format((int) $sale['units']); ?></td><td>UGX <?php echo number_format((float) $sale['revenue'], 2); ?></td></tr><?php } } else { ?><tr><td colspan="3" class="text-center text-muted">No sales recorded yet.</td></tr><?php } ?></tbody></table></div></div></div>

        <div class="card shadow-sm mt-4"><div class="card-body"><h5>Farmer Performance Metrics</h5><div class="table-responsive"><table class="table table-striped"><thead><tr><th>Farmer</th><th>Products</th><th>Views</th><th>Orders</th><th>Revenue</th></tr></thead><tbody><?php if ($farmerMetrics && mysqli_num_rows($farmerMetrics)) { while ($farmer = mysqli_fetch_assoc($farmerMetrics)) { ?><tr><td><?php echo htmlspecialchars($farmer['seller_email']); ?></td><td><?php echo number_format((int) $farmer['products']); ?></td><td><?php echo number_format((int) $farmer['views']); ?></td><td><?php echo number_format((int) $farmer['orders']); ?></td><td>UGX <?php echo number_format((float) $farmer['revenue'], 2); ?></td></tr><?php } } else { ?><tr><td colspan="5" class="text-center text-muted">No farmer performance data yet.</td></tr><?php } ?></tbody></table></div></div></div>

      </div>
    </div>
  </div>
</div>

<script>
window.addEventListener('load', function() {
  const ctx1 = document.getElementById('analyticsChart1')?.getContext('2d');
  if (ctx1) new Chart(ctx1, { type: 'bar', data: { labels: <?php echo json_encode($viewLabels); ?>, datasets:[{label:'Product Views',data:<?php echo json_encode($viewValues); ?>,backgroundColor:'#10b981'}]}, options:{responsive:true, plugins:{legend:{display:false}}}});
  const ctx2 = document.getElementById('analyticsChart2')?.getContext('2d');
  if (ctx2) new Chart(ctx2, { type: 'line', data: { labels: <?php echo json_encode(array_keys($monthlySales)); ?>, datasets:[{label:'Orders',data:<?php echo json_encode(array_values($monthlySales)); ?>,borderColor:'#38bdf8',backgroundColor:'rgba(56,189,248,.12)',fill:true},{label:'Revenue (UGX)',data:<?php echo json_encode(array_values($monthlyRevenue)); ?>,borderColor:'#f97316',backgroundColor:'rgba(249,115,22,.08)',fill:false}]}, options:{responsive:true}});
});
</script>

<?php include "inc/footer.php"; ?>
