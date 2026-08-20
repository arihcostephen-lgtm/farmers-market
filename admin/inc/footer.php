<footer class="page-footer">
			<p class="mb-0">© 2026 Farmers Market. All rights reserved.</p>
		</footer>
	</div>
	<!--end wrapper-->
	
	<!-- Bootstrap JS -->
	<script src="assets/js/bootstrap.bundle.min.js"></script>
	<!--plugins-->
	<script src="assets/js/jquery.min.js"></script>
	<script src="assets/plugins/simplebar/js/simplebar.min.js"></script>
	<script src="assets/plugins/metismenu/js/metisMenu.min.js"></script>
	<script src="assets/plugins/perfect-scrollbar/js/perfect-scrollbar.js"></script>
	<script src="assets/plugins/chartjs/chart.min.js"></script>
	<script src="assets/plugins/vectormap/jquery-jvectormap-2.0.2.min.js"></script>
    <script src="assets/plugins/vectormap/jquery-jvectormap-world-mill-en.js"></script>
	<script src="assets/plugins/jquery.easy-pie-chart/jquery.easypiechart.min.js"></script>
	<script src="assets/plugins/sparkline-charts/jquery.sparkline.min.js"></script>
	<script src="assets/plugins/jquery-knob/excanvas.js"></script>
	<script src="assets/plugins/jquery-knob/jquery.knob.js"></script>
	<!-- Datatable js -->
	<script src="assets/plugins/datatable/js/jquery.dataTables.min.js"></script>
	<script src="assets/plugins/datatable/js/dataTables.bootstrap5.min.js"></script>
	
	<script>
		$(document).ready(function() {
			var siteTitle = <?php echo json_encode($adminSiteTitle ?? 'Farmers Market'); ?>;
			var logoPath = 'assets/images/logo.png';

			function getBase64ImageFromURL(url, callback) {
				var xhr = new XMLHttpRequest();
				xhr.onload = function() {
					var reader = new FileReader();
					reader.onloadend = function() { callback(reader.result); };
					reader.readAsDataURL(xhr.response);
				};
				xhr.onerror = function() { callback(null); };
				xhr.open('GET', url);
				xhr.responseType = 'blob';
				xhr.send();
			}

			function initTable(logoData) {
				if (!$('#example3').length) {
					return;
				}

				var table = $('#example3').DataTable({
					lengthChange: false,
					dom: 'Bfrtip',
					buttons: [
						'copy', 'excel',
						{
							extend: 'pdfHtml5',
							text: 'PDF',
							orientation: 'portrait',
							pageSize: 'A4',
							filename: siteTitle + '_export',
							exportOptions: {
								columns: function(columnIndex, columnData, headerNode) {
									var headerText = $(headerNode).text().trim().toLowerCase();
									return headerText !== 'action' && !$(headerNode).hasClass('noExport') && !$(headerNode).hasClass('action');
								}
							},
							customize: function(doc) {
								doc.styles = doc.styles || {};
								doc.styles.title = { fontSize: 16, bold: true, margin: [0, 8, 0, 8] };
								if (logoData) {
									var headerCols = [
										{ image: logoData, width: 60, alignment: 'left', margin: [0, 0, 8, 0] },
										{ text: siteTitle, style: 'title', alignment: 'center' }
									];
									doc.content.splice(0, 0, { columns: headerCols, margin: [0, 0, 0, 12] });
								} else {
									doc.content.unshift({ text: siteTitle, style: 'title', alignment: 'center', margin: [0, 0, 0, 12] });
								}

								// Remove any page-level messages that look like confirmation/deletion alerts
								doc.content = (doc.content || []).filter(function(item){
									var text = '';
									if (!item) return false;
									if (typeof item === 'string') text = item;
									else if (item.text && typeof item.text === 'string') text = item.text;
									else if (item.columns && Array.isArray(item.columns)) {
										text = item.columns.map(function(c){ return (c && c.text && typeof c.text === 'string') ? c.text : ''; }).join(' ');
									}
									// Pattern matches common confirmation/deletion phrases
									if (text && /are you sure|confirm|confirmation|delete(ed|ing)?|deleted|remove(d)?/i.test(text)) {
										return false;
									}
									return true;
								});
							}
						},
						'print'
					]
				});
				
				table.buttons().container().prependTo('#example3_wrapper');
			}

			// preload logo then initialize table
			getBase64ImageFromURL(logoPath, function(logoData) {
				initTable(logoData);
			});
		});
	</script>
	<!-- Datatable js -->
	  <script>
		  $(function() {
			  $(".knob").knob();
		  });
	  </script>
	  <script src="assets/js/index.js"></script>
	<!--app JS-->
	<script src="assets/js/app.js"></script>

	<?php  
		ob_end_flush();
	?>
</body>
</html>
