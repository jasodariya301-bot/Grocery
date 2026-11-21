<?php
$page_title = "account - Fresh Groceries Online";
include 'includes/header.php';
?>

    <!-- Breadcrumb -->
    <div class="bg-light py-3">
        <div class="container">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                    <li class="breadcrumb-item active">My Account</li>
                </ol>
            </nav>
        </div>
    </div>

    <!-- Account Section -->
    <section class="py-5">
        <div class="container">
            <div class="row">
                <!-- Sidebar -->
                <div class="col-lg-3 mb-4">
                    <div class="card shadow-sm">
                        <div class="card-body text-center">
                            <i class="fas fa-user-circle fa-4x text-success mb-3"></i>
                            <h5 id="userName">akhil visani</h5>
                            <p class="text-muted mb-0" id="userEmail">user@gmail.com</p>
                        </div>
                    </div>
                    <div class="list-group mt-3 shadow-sm">
                        <a href="#" class="list-group-item list-group-item-action active" onclick="showSection('profile')">
                            <i class="fas fa-user me-2"></i>Profile
                        </a>
                        <!-- <a href="#" class="list-group-item list-group-item-action" onclick="showSection('orders')">
                            <i class="fas fa-shopping-bag me-2"></i>Order History
                        </a> -->
                        <a href="#" class="list-group-item list-group-item-action" onclick="showSection('addresses')">
                            <i class="fas fa-map-marker-alt me-2"></i>Addresses
                        </a>
                        <!-- <a href="#" class="list-group-item list-group-item-action" onclick="showSection('track')">
                            <i class="fas fa-truck me-2"></i>Track Order
                        </a> -->
                    </div>
                </div>

                <!-- Content Area -->
                <div class="col-lg-9">
                    <!-- Profile Section -->
                    <div id="profileSection" class="account-section">
                        <div class="card shadow-sm">
                            <div class="card-header bg-success text-white">
                                <h5 class="mb-0"><i class="fas fa-user me-2"></i>Profile Information</h5>
                            </div>
                            <div class="card-body">
                                <form id="profileForm">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">First Name</label>
                                            <input type="text" class="form-control" id="profileFirstName" value="akhil">
                                            <div class="error-message" id="error-profile-fname"></div>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Last Name</label>
                                            <input type="text" class="form-control" id="profileLastName" value="visani">
                                            <div class="error-message" id="error-profile-lname"></div>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Email Address</label>
                                        <input type="email" class="form-control" id="profileEmail" value="akhilvisani@gmail.com">
                                        <div class="error-message" id="error-profile-email"></div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Phone Number</label>
                                        <input type="tel" class="form-control" id="profilePhone" value="+91 9876543210">
                                        <div class="error-message" id="error-profile-phone"></div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Date of Birth</label>
                                        <input type="date" class="form-control" id="profileDOB" value="2004-01-01">
                                    </div>
                                    <button type="submit" class="btn btn-success">Save Changes</button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Order History Section -->
                    <div id="ordersSection" class="account-section d-none">
                        <div class="card shadow-sm">
                            <div class="card-header bg-success text-white">
                                <h5 class="mb-0"><i class="fas fa-shopping-bag me-2"></i>Order History</h5>
                            </div>
                            <div class="card-body">
                                <div id="orderHistory">
                                    <!-- Orders will be loaded by JavaScript -->
                                    <div class="order-item border-bottom pb-3 mb-3">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <div>
                                                <h6>Order #ORD-2024-001</h6>
                                                <small class="text-muted">Placed on: 15 Jan 2024</small>
                                            </div>
                                            <span class="badge bg-success">Delivered</span>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-8">
                                                <p class="mb-1"><strong>Items:</strong> Fresh Tomatoes, Organic Carrots, Apples</p>
                                                <p class="mb-0"><strong>Total:</strong> ₹450</p>
                                            </div>
                                            <div class="col-md-4 text-md-end">
                                                <button class="btn btn-sm btn-outline-success">View Details</button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="order-item border-bottom pb-3 mb-3">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <div>
                                                <h6>Order #ORD-2024-002</h6>
                                                <small class="text-muted">Placed on: 10 Jan 2024</small>
                                            </div>
                                            <span class="badge bg-warning">In Transit</span>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-8">
                                                <p class="mb-1"><strong>Items:</strong> Milk, Bread, Eggs</p>
                                                <p class="mb-0"><strong>Total:</strong> ₹280</p>
                                            </div>
                                            <div class="col-md-4 text-md-end">
                                                <button class="btn btn-sm btn-outline-success">Track Order</button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="order-item">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <div>
                                                <h6>Order #ORD-2024-003</h6>
                                                <small class="text-muted">Placed on: 5 Jan 2024</small>
                                            </div>
                                            <span class="badge bg-info">Processing</span>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-8">
                                                <p class="mb-1"><strong>Items:</strong> Chips, Beverages, Snacks</p>
                                                <p class="mb-0"><strong>Total:</strong> ₹320</p>
                                            </div>
                                            <div class="col-md-4 text-md-end">
                                                <button class="btn btn-sm btn-outline-success">View Details</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Addresses Section -->
                    <div id="addressesSection" class="account-section d-none">
                        <div class="card shadow-sm">
                            <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                                <h5 class="mb-0"><i class="fas fa-map-marker-alt me-2"></i>Saved Addresses</h5>
                                <button class="btn btn-sm btn-light" onclick="showAddAddressForm()">
                                    <i class="fas fa-plus me-1"></i>Add New
                                </button>
                            </div>
                            <div class="card-body">
                                <div id="addressList">
                                    <div class="card mb-3">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div>
                                                    <h6>Home</h6>
                                                    <p class="mb-0">123 Main Street<br>City, State - 123456<br>Phone: +91 9876543210</p>
                                                </div>
                                                <div>
                                                    <button class="btn btn-sm btn-outline-primary me-2">Edit</button>
                                                    <button class="btn btn-sm btn-outline-danger">Delete</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card mb-3">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div>
                                                    <h6>Work</h6>
                                                    <p class="mb-0">456 Office Road<br>City, State - 123456<br>Phone: +91 9876543210</p>
                                                </div>
                                                <div>
                                                    <button class="btn btn-sm btn-outline-primary me-2">Edit</button>
                                                    <button class="btn btn-sm btn-outline-danger">Delete</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Add/Edit Address Form -->
                                <div id="addAddressForm" class="mt-4 p-3 bg-light rounded d-none">
                                    <h6>Add New Address</h6>
                                    <form id="addressForm">
                                        <div class="row">
                                            <div class="col-md-6 mb-2">
                                                <input type="text" class="form-control" id="addressLabel" placeholder="Label (Home/Work)">
                                                <div class="error-message" id="error-addr-label"></div>
                                            </div>
                                            <div class="col-md-12 mb-2">
                                                <textarea class="form-control" id="fullAddress" rows="2" placeholder="Full Address"></textarea>
                                                <div class="error-message" id="error-addr-full"></div>
                                            </div>
                                            <div class="col-md-6 mb-2">
                                                <input type="text" class="form-control" id="addressCity" placeholder="City">
                                                <div class="error-message" id="error-addr-city"></div>
                                            </div>
                                            <div class="col-md-6 mb-2">
                                                <input type="text" class="form-control" id="addressPincode" placeholder="Pincode">
                                                <div class="error-message" id="error-addr-pincode"></div>
                                            </div>
                                            <div class="col-md-12">
                                                <button type="submit" class="btn btn-success">Save Address</button>
                                                <button type="button" class="btn btn-outline-secondary" onclick="hideAddAddressForm()">Cancel</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Track Order Section -->
                    <div id="trackSection" class="account-section d-none">
                        <div class="card shadow-sm">
                            <div class="card-header bg-success text-white">
                                <h5 class="mb-0"><i class="fas fa-truck me-2"></i>Track Order</h5>
                            </div>
                            <div class="card-body">
                                <div class="mb-4">
                                    <label class="form-label">Enter Order Number</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="trackOrderNumber" placeholder="e.g., ORD-2024-001">
                                        <button class="btn btn-success" onclick="trackOrder()">Track</button>
                                    </div>
                                    <div class="error-message" id="error-track-order"></div>
                                </div>

                                <div id="trackResult">
                                    <!-- Tracking details will be shown here -->
                                    <div class="tracking-timeline">
                                        <div class="timeline-item completed">
                                            <div class="timeline-marker bg-success"></div>
                                            <div class="timeline-content">
                                                <h6>Order Placed</h6>
                                                <p class="text-muted mb-0">Order #ORD-2024-002 placed successfully</p>
                                                <small class="text-muted">10 Jan 2024, 10:30 AM</small>
                                            </div>
                                        </div>
                                        <div class="timeline-item completed">
                                            <div class="timeline-marker bg-success"></div>
                                            <div class="timeline-content">
                                                <h6>Order Confirmed</h6>
                                                <p class="text-muted mb-0">Your order has been confirmed</p>
                                                <small class="text-muted">10 Jan 2024, 11:00 AM</small>
                                            </div>
                                        </div>
                                        <div class="timeline-item completed">
                                            <div class="timeline-marker bg-success"></div>
                                            <div class="timeline-content">
                                                <h6>Order Packed</h6>
                                                <p class="text-muted mb-0">Your order is being packed</p>
                                                <small class="text-muted">10 Jan 2024, 12:00 PM</small>
                                            </div>
                                        </div>
                                        <div class="timeline-item active">
                                            <div class="timeline-marker bg-warning"></div>
                                            <div class="timeline-content">
                                                <h6>Out for Delivery</h6>
                                                <p class="text-muted mb-0">Your order is on the way</p>
                                                <small class="text-muted">10 Jan 2024, 2:00 PM</small>
                                            </div>
                                        </div>
                                        <div class="timeline-item">
                                            <div class="timeline-marker"></div>
                                            <div class="timeline-content">
                                                <h6>Delivered</h6>
                                                <p class="text-muted mb-0">Order will be delivered soon</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

 <?php include 'includes/footer.php'; ?>