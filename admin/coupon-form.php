<?php include('includes/sidebar.php');
include('includes/header.php');
$page = 'coupons';
?>


        <div class="content-wrapper">
            <div class="page-header">
                <div>
                    <h2 id="couponFormTitle">Add Coupon</h2>
                    <p class="text-muted mb-0">Clear validation + real-time discount preview.</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="coupons.php" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Back
                    </a>
                    <button class="btn btn-success" id="couponSaveTop">
                        <i class="fas fa-save me-2"></i>Save
                    </button>
                </div>
            </div>

            <div class="row g-4">
                <!-- Form -->
                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <form id="couponForm" novalidate>
                                <input type="hidden" id="couponId">

                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Coupon Code <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <input type="text" class="form-control" id="couponCode" placeholder="SAVE10" required>
                                            <button class="btn btn-outline-success" type="button" id="couponGenerate">
                                                <i class="fas fa-wand-magic-sparkles"></i>
                                            </button>
                                        </div>
                                        <div class="invalid-feedback">Coupon code is required and must be unique.</div>
                                        <div class="form-text">Allowed: A-Z, 0-9, length 3–16.</div>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">Coupon Status</label>
                                        <select class="form-select" id="couponStatus">
                                            <option value="active" selected>Active</option>
                                            <option value="inactive">Inactive</option>
                                        </select>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">Coupon Type <span class="text-danger">*</span></label>
                                        <select class="form-select" id="couponType" required>
                                            <option value="percent" selected>Percentage (%)</option>
                                            <option value="flat">Flat Amount (₹)</option>
                                        </select>
                                        <div class="invalid-feedback">Please choose a type.</div>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">Discount Value <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control" id="couponValue" min="1" step="1" required>
                                        <div class="invalid-feedback">Enter a valid discount value.</div>
                                        <div class="form-text" id="couponValueHint">Example: 10 means 10% off.</div>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">Minimum Cart Amount (₹)</label>
                                        <input type="number" class="form-control" id="couponMinCart" min="0" step="1" value="0">
                                        <div class="invalid-feedback">Min cart must be 0 or more.</div>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">Maximum Discount Limit (₹)</label>
                                        <input type="number" class="form-control" id="couponMaxDiscount" min="0" step="1" value="0">
                                        <div class="form-text">0 = no cap (for percent coupons we recommend a cap).</div>
                                        <div class="invalid-feedback">Max discount must be 0 or more.</div>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">Valid From <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control" id="couponValidFrom" required>
                                        <div class="invalid-feedback">Valid From is required.</div>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">Valid To <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control" id="couponValidTo" required>
                                        <div class="invalid-feedback">Valid To is required and must be after Valid From.</div>
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label">Usage Limit</label>
                                        <input type="number" class="form-control" id="couponUsageLimit" min="0" step="1" value="0">
                                        <div class="form-text">0 = unlimited.</div>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Total Usage</label>
                                        <input type="number" class="form-control" id="couponTotalUsage" min="0" step="1" value="0" readonly>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Per User Limit</label>
                                        <input type="number" class="form-control" id="couponPerUserLimit" min="0" step="1" value="0">
                                        <div class="form-text">0 = unlimited per user.</div>
                                    </div>

                                    <div class="col-12">
                                        <label class="form-label">Applicable On</label>
                                        <select class="form-select" id="couponApplicableOn">
                                            <option value="all" selected>All Products</option>
                                            <option value="categories">Selected Categories</option>
                                            <option value="products">Selected Products</option>
                                        </select>
                                        <div class="form-text">For demo, selection is stored but not linked to catalog yet.</div>
                                    </div>

                                    <div class="col-12 d-none" id="couponApplicableListWrap">
                                        <label class="form-label">Selected IDs (comma separated)</label>
                                        <input type="text" class="form-control" id="couponApplicableList" placeholder="Example: veg,fruits or 12,15,18">
                                    </div>

                                    <div class="col-12">
                                        <label class="form-label">User Eligibility</label>
                                        <select class="form-select" id="couponEligibility">
                                            <option value="all" selected>All Users</option>
                                            <option value="new">New Users Only</option>
                                            <option value="specific">Specific Users</option>
                                        </select>
                                    </div>

                                    <div class="col-12 d-none" id="couponEligibleUsersWrap">
                                        <label class="form-label">Specific User Emails (comma separated)</label>
                                        <input type="text" class="form-control" id="couponEligibleUsers" placeholder="user1@email.com,user2@email.com">
                                    </div>

                                    <div class="col-12">
                                        <label class="form-label">Description / Notes</label>
                                        <textarea class="form-control" id="couponNotes" rows="3" placeholder="Optional notes..."></textarea>
                                    </div>
                                </div>

                                <hr class="my-4">

                                <div class="d-flex gap-2 sticky-actions">
                                    <button class="btn btn-success btn-lg flex-grow-1" type="submit">
                                        <i class="fas fa-save me-2"></i>Save Coupon
                                    </button>
                                    <button class="btn btn-outline-danger btn-lg" type="button" id="couponDeleteBtn">
                                        <i class="fas fa-trash me-2"></i>Delete
                                    </button>
                                </div>

                                <div class="small text-muted mt-3">
                                    Security checks: prevents duplicate codes, prevents expired usage, confirms delete.
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Preview -->
                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <h5 class="mb-3"><i class="fas fa-eye me-2 text-success"></i>Real-time Preview</h5>
                            <div class="preview-box">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <div class="small text-muted">Coupon</div>
                                        <div class="h4 mb-0" id="previewCode">—</div>
                                    </div>
                                    <span class="badge bg-success align-self-start" id="previewStatus">ACTIVE</span>
                                </div>
                                <hr>
                                <div class="small text-muted mb-1">Example Cart Amount (₹)</div>
                                <input type="number" class="form-control mb-3" id="previewCartAmount" value="1000" min="0" step="1">
                                <div class="row g-2">
                                    <div class="col-6">
                                        <div class="small text-muted">Discount</div>
                                        <div class="fw-bold" id="previewDiscount">₹0</div>
                                    </div>
                                    <div class="col-6 text-end">
                                        <div class="small text-muted">Final Total</div>
                                        <div class="fw-bold" id="previewFinal">₹0</div>
                                    </div>
                                </div>
                                <div class="alert alert-warning mt-3 mb-0 d-none" id="previewWarn"></div>
                                <div class="alert alert-success mt-3 mb-0 d-none" id="previewOk"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

<?php 
include('includes/footer.php');
?>

