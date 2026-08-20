	<!--sidebar wrapper -->
	<div class="sidebar-wrapper" data-simplebar="true" style="background: #e6fff0;">
		<style>
			/* Dark sidebar background with green hover accents */
			.sidebar-wrapper { background: #06130d !important; transition: background .2s ease; }
			.sidebar-wrapper .sidebar-header { border-bottom: 1px solid rgba(255,255,255,0.03); }
			.sidebar-wrapper .metismenu a { color: #cfeee0; transition: color .15s ease, background .15s ease; }
			.sidebar-wrapper .metismenu .menu-label { color: #bff7d8; }
			.sidebar-wrapper .metismenu li a .menu-title { color: #cfeee0; }
			.sidebar-wrapper .metismenu li a .parent-icon { color: #9fe9c1; }
			/* hover/active: bright green accent */
			.sidebar-wrapper .metismenu li a:hover,
			.sidebar-wrapper .metismenu li a.active,
			.sidebar-wrapper .metismenu li a:focus {
				background: linear-gradient(90deg, #0b8a4a 0%, #0b5b33 100%);
				color: #ffffff !important;
				box-shadow: inset 0 2px 0 rgba(0,0,0,0.12);
			}
			.sidebar-wrapper .metismenu li a:hover .menu-title,
			.sidebar-wrapper .metismenu li a.active .menu-title {
				color: #ffffff !important;
			}
			.sidebar-wrapper .metismenu li a { padding: 10px 12px; border-radius: 6px; }
		</style>
		<div class="sidebar-header">
			<div>
				<img src="assets/images/logo-icon.png" class="logo-icon" alt="logo icon">
			</div>
			<div>
				<h4 class="logo-text"><?php echo htmlspecialchars($adminSiteTitle ?? 'Farmers Market'); ?></h4>
			</div>
			<div class="toggle-icon ms-auto"><i class='bx bx-arrow-to-left'></i>
			</div>
		</div>
		<!--navigation-->
		<ul class="metismenu" id="menu">
			<li>
				<a href="dashboard.php" class="">
					<div class="parent-icon text-success"><i class='bx bx-home-circle'></i>
					</div>
					<div class="menu-title text-success">Dashboard</div>
				</a>
			</li>

			<li class="menu-label text-success">Market</li>
			<li>
				<a href="order_list.php?do=Manage" class="">
					<div class="parent-icon text-success"><i class='bx bx-cart'></i>
					</div>
					<div class="menu-title text-success">Orders</div>
				</a>
			</li>

			<li class="menu-label text-success">Products</li>
			<li>
				<a href="category.php?do=Manage" class="">
					<div class="parent-icon text-success"><i class='bx bx-category'></i>
					</div>
					<div class="menu-title text-success">Categories</div>
				</a>
			</li>

			<li>
				<a href="post.php?do=Manage" class="">
					<div class="parent-icon text-success"><i class='bx bx-news'></i>
					</div>
					<div class="menu-title text-success">Blog Management</div>
				</a>
			</li>

			<li>
				<a href="products.php" class="">
					<div class="parent-icon text-success"><i class='bx bx-store'></i>
					</div>
					<div class="menu-title text-success">Products</div>
				</a>
			</li>

			<li>
				<a href="analytics.php" class="">
					<div class="parent-icon text-success"><i class='bx bx-line-chart'></i>
					</div>
					<div class="menu-title text-success">Analytics</div>
				</a>
			</li>

			<li>
				<a href="help_support.php" class="">
					<div class="parent-icon text-success"><i class='bx bx-help-circle'></i>
					</div>
					<div class="menu-title text-success">Help & Support</div>
				</a>
			</li>

			<li>
				<a href="documentation.php" class="">
					<div class="parent-icon text-success"><i class='bx bx-book'></i>
					</div>
					<div class="menu-title text-success">Documentation</div>
				</a>
			</li>

			<li>
				<a href="view_documents.php" class="">
					<div class="parent-icon text-success"><i class='bx bx-show'></i>
					</div>
					<div class="menu-title text-success">View Documents</div>
				</a>
			</li>

			<li class="menu-label text-success">Management</li>
			<li>
				<a href="users.php?do=Manage" class="">
					<div class="parent-icon text-success"><i class='bx bx-user-pin'></i>
					</div>
					<div class="menu-title text-success">Manage Farmers</div>
				</a>
			</li>
			<li>
				<a href="users.php?do=customerManage" class="">
					<div class="parent-icon text-success"><i class='bx bx-user'></i>
					</div>
					<div class="menu-title text-success">Customers</div>
				</a>
			</li>
			<li>
				<a href="about.php?do=Manage" class="">
					<div class="parent-icon text-success"><i class='bx bx-info-circle'></i>
					</div>
					<div class="menu-title text-success">About Us</div>
				</a>
			</li>
			<li>
				<a href="system_config.php" class="">
					<div class="parent-icon text-success"><i class='bx bx-cog'></i>
					</div>
					<div class="menu-title text-success">System Config</div>
				</a>
			</li>
			<li>
				<a href="logout.php" class="">
					<div class="parent-icon text-danger"><i class='bx bx-log-out-circle'></i>
					</div>
					<div class="menu-title text-danger">Logout</div>
				</a>
			</li>
		</ul>
		<!--end navigation-->
	</div>
	<!--end sidebar wrapper -->
