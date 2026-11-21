<?php include('includes/sidebar.php');
include('includes/header.php');
$page = 'dashboard';
?>



        <!-- Dashboard Content -->
        <div class="content-wrapper">
            <div class="page-header">
                <h2>Dashboard</h2>
                <p>Welcome back! Here's what's happening today.</p>
            </div>

            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-md-3 mb-3">
                    <div class="stat-card stat-primary">
                        <div class="stat-icon">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                        <div class="stat-content">
                            <h3 id="today-orders">0</h3>
                            <p>Orders Today</p>
                            <small id="monthly-orders">0 this month</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="stat-card stat-success">
                        <div class="stat-icon">
                            <i class="fas fa-dollar-sign"></i>
                        </div>
                        <div class="stat-content">
                            <h3 id="total-revenue">$0</h3>
                            <p>Total Revenue</p>
                            <small id="revenue-period">This month</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="stat-card stat-warning">
                        <div class="stat-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="stat-content">
                            <h3 id="pending-orders">0</h3>
                            <p>Pending Orders</p>
                            <small>Needs attention</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="stat-card stat-danger">
                        <div class="stat-icon">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <div class="stat-content">
                            <h3 id="low-stock">0</h3>
                            <p>Low Stock Items</p>
                            <small>Restock needed</small>
                        </div>
                    </div>
                </div>
            </div>


 

<?php 
include('includes/footer.php');
?>