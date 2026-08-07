<?php include "inc/header.php"; ?>

<div class="page-wrapper">
  <div class="page-content">
    <div class="card shadow-sm">
      <div class="card-body">
        <h4>Analytics</h4>
        <p class="text-muted">Quick overview charts.</p>

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

      </div>
    </div>
  </div>
</div>

<script>
const ctx1 = document.getElementById('analyticsChart1')?.getContext('2d');
if (ctx1) {
  new Chart(ctx1, { type: 'line', data: { labels: ['Mon','Tue','Wed','Thu','Fri','Sat','Sun'], datasets:[{label:'Visits',data:[120,150,170,160,180,200,210],borderColor:'#10b981',backgroundColor:'rgba(16,185,129,0.08)',fill:true}]}, options:{responsive:true}});
}
const ctx2 = document.getElementById('analyticsChart2')?.getContext('2d');
if (ctx2) {
  new Chart(ctx2, { type: 'bar', data: { labels:['Direct','Organic','Social','Referral'], datasets:[{data:[35,28,15,22],backgroundColor:['#10b981','#38bdf8','#8b5cf6','#f97316']}]}, options:{responsive:true}});
}
</script>

<?php include "inc/footer.php"; ?>
