<?php include('includes/sidebar.php');
include('includes/header.php');

?>


        <div class="content-wrapper">
            <div class="page-header">
                <div>
                    <h2>Coupons & Discounts</h2>
                    <p class="text-muted mb-0">Create, control and monitor coupons with real-time validation.</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="coupon-form.php" class="btn btn-success">
                        <i class="fas fa-plus me-2"></i>Add Coupon
                    </a>
                </div>
            </div>

            <!-- Overview Cards -->
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <div class="stat-card stat-primary">
                        <div class="stat-icon"><i class="fas fa-ticket-alt"></i></div>
                        <div class="stat-content">
                            <h3 id="cardTotalCoupons">0</h3>
                            <p>Total Coupons</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card stat-success">
                        <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
                        <div class="stat-content">
                            <h3 id="cardActiveCoupons">0</h3>
                            <p>Active Coupons</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card stat-warning">
                        <div class="stat-icon"><i class="fas fa-hourglass-end"></i></div>
                        <div class="stat-content">
                            <h3 id="cardExpiredCoupons">0</h3>
                            <p>Expired Coupons</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card stat-info">
                        <div class="stat-icon"><i class="fas fa-rupee-sign"></i></div>
                        <div class="stat-content">
                            <h3 id="cardDiscountGiven">₹0</h3>
                            <p>Total Discount Given</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-body">
                    <div class="row g-2 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label">Status</label>
                            <select class="form-select" id="couponStatusFilter">
                                <option value="all" selected>All</option>
                                <option value="active">Active</option>
                                <option value="expired">Expired</option>
                                <option value="upcoming">Upcoming</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Type</label>
                            <select class="form-select" id="couponTypeFilter">
                                <option value="all" selected>All</option>
                                <option value="percent">Percentage (%)</option>
                                <option value="flat">Flat (₹)</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Rows</label>
                            <select class="form-select" id="couponRows">
                                <option value="5">5</option>
                                <option value="10" selected>10</option>
                                <option value="20">20</option>
                            </select>
                        </div>
                        <div class="col-md-3 d-flex gap-2">
                            <button class="btn btn-outline-secondary w-100" id="couponReset">
                                <i class="fas fa-rotate-left me-2"></i>Reset
                            </button>
                            <button class="btn btn-outline-success w-100" id="couponBulkEnable">
                                <i class="fas fa-toggle-on me-2"></i>Enable
                            </button>
                            <button class="btn btn-outline-danger w-100" id="couponBulkDisable">
                                <i class="fas fa-toggle-off me-2"></i>Disable
                            </button>
                        </div>
                    </div>
                    <div class="small text-muted mt-2">Tip: select rows to bulk enable/disable. Delete requires confirmation.</div>
                </div>
            </div>

            <!-- Coupon List Table -->
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Sr No</th>
                                    <th>Coupon Code</th>
                                    <th>Discount Type</th>
                                    <th>Discount Value</th>
                                    <th>Validity</th>
                                    <th>Usage</th>
                                    <th>Status</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                                </thead>
                            <tbody>
                                <tr>
                                    <td>1</td>
                                    <td><strong>GROC10</strong></td>
                                    <td>Percentage</td>
                                    <td>10%</td>
                                    <td>01 Feb 2026 – 28 Feb 2026</td>
                                    <td>120 / 500</td>
                                    <td><span class="badge bg-success">Active</span></td>
                                    <td class="text-end">
                                        <button class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i></button>
                                        <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                                    </td>
                                </tr>

                                <tr>
                                    <td>2</td>
                                    <td><strong>FRESH50</strong></td>
                                    <td>Flat</td>
                                    <td>₹50</td>
                                    <td>05 Feb 2026 – 20 Feb 2026</td>
                                    <td>180 / 200</td>
                                    <td><span class="badge bg-warning">Expiring</span></td>
                                    <td class="text-end">
                                        <button class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i></button>
                                        <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                                    </td>
                                </tr>

                                <tr>
                                    <td>3</td>
                                    <td><strong>NEWUSER100</strong></td>
                                    <td>Flat</td>
                                    <td>₹100</td>
                                    <td>01 Jan 2026 – 31 Dec 2026</td>
                                    <td>Unlimited</td>
                                    <td><span class="badge bg-success">Active</span></td>
                                    <td class="text-end">
                                        <button class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i></button>
                                        <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                                    </td>
                                </tr>

                                <tr>
                                    <td>4</td>
                                    <td><strong>WEEKEND20</strong></td>
                                    <td>Percentage</td>
                                    <td>20%</td>
                                    <td>01 Jan 2025 – 31 Jan 2025</td>
                                    <td>300 / 300</td>
                                    <td><span class="badge bg-secondary">Expired</span></td>
                                    <td class="text-end">
                                        <button class="btn btn-sm btn-outline-secondary" disabled>
                                            <i class="fas fa-ban"></i>
                                        </button>
                                    </td>
                                </tr>

                                <tr>
                                    <td>5</td>
                                    <td><strong>DIWALI30</strong></td>
                                    <td>Percentage</td>
                                    <td>30%</td>
                                    <td>25 Oct 2026 – 05 Nov 2026</td>
                                    <td>0 / 1000</td>
                                    <td><span class="badge bg-info">Upcoming</span></td>
                                    <td class="text-end">
                                        <button class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i></button>
                                        <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                                    </td>
                                </tr>
                            </tbody>

                        </table>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mt-3 flex-wrap gap-2">
                        <div class="small text-muted" id="couponPageInfo">Showing 0</div>
                        <nav>
                            <ul class="pagination pagination-sm mb-0" id="couponPagination"></ul>
                        </nav>
                    </div>
                </div>
            </div>

        </div>
    </div>

<?php 
include('includes/footer.php');
?>
