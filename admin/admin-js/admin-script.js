// ===== Admin Panel JavaScript =====

// Initialize on page load
document.addEventListener('DOMContentLoaded', () => {
    loadDashboardData();
    initializePage();
});

// ===== Sidebar Toggle =====
function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    sidebar.classList.toggle('show');
}

// ===== Initialize Page Based on Route =====
function initializePage() {
    const path = window.location.pathname;
    
    if (path.includes('index.php') || path.endsWith('admin/')) {
        initializeDashboard();
    } else if (path.includes('products.php')) {
        initializeProducts();
    } else if (path.includes('orders.php')) {
        initializeOrders();
    } else if (path.includes('customers.php')) {
        initializeCustomers();
    } else if (path.includes('offers.php')) {
        initializeOffers();
    } else if (path.includes('gallery.php')) {
        initializeGallery();
    } else if (path.includes('settings.php')) {
        initializeSettings();
    }
}

// ===== Dashboard Functions =====
function initializeDashboard() {
    loadDashboardStats();
    loadPopularFlavors();
    loadRecentOrders();
    initializeCharts();
}

function loadDashboardStats() {
    const orders = JSON.parse(localStorage.getItem('orders')) || [];
    const products = JSON.parse(localStorage.getItem('adminProducts')) || getDefaultProducts();
    
    const today = new Date().toDateString();
    const thisMonth = new Date().getMonth();
    
    const todayOrders = orders.filter(o => new Date(o.timestamp || Date.now()).toDateString() === today);
    const monthlyOrders = orders.filter(o => new Date(o.timestamp || Date.now()).getMonth() === thisMonth);
    const pendingOrders = orders.filter(o => o.status === 'pending' || !o.status);
    const lowStockItems = products.filter(p => p.stock < 10);
    
    const totalRevenue = monthlyOrders.reduce((sum, o) => sum + (o.total || 0), 0);
    
    document.getElementById('today-orders').textContent = todayOrders.length;
    document.getElementById('monthly-orders').textContent = `${monthlyOrders.length} this month`;
    document.getElementById('total-revenue').textContent = `$${totalRevenue.toFixed(2)}`;
    document.getElementById('revenue-period').textContent = 'This month';
    document.getElementById('pending-orders').textContent = pendingOrders.length;
    document.getElementById('low-stock').textContent = lowStockItems.length;
}

function loadPopularFlavors() {
    const orders = JSON.parse(localStorage.getItem('orders')) || [];
    const flavorCounts = {};
    
    orders.forEach(order => {
        if (order.items) {
            order.items.forEach(item => {
                flavorCounts[item.name] = (flavorCounts[item.name] || 0) + item.quantity;
            });
        }
    });
    
    const popular = Object.entries(flavorCounts)
        .sort((a, b) => b[1] - a[1])
        .slice(0, 5);
    
    const container = document.getElementById('popular-flavors-list');
    if (popular.length === 0) {
        container.innerHTML = '<p class="text-muted text-center">No data available</p>';
        return;
    }
    
    container.innerHTML = popular.map(([name, count], index) => `
        <div class="popular-flavor-item">
            <div class="flavor-number">${index + 1}</div>
            <div class="flex-grow-1">
                <strong>${name}</strong>
                <small class="text-muted d-block">${count} orders</small>
            </div>
        </div>
    `).join('');
}

function loadRecentOrders() {
    const orders = JSON.parse(localStorage.getItem('orders')) || [];
    const recent = orders.slice(-5).reverse();
    
    const tbody = document.getElementById('recent-orders-table');
    if (recent.length === 0) {
        tbody.innerHTML = '<tr><td colspan="4" class="text-center text-muted">No recent orders</td></tr>';
        return;
    }
    
    tbody.innerHTML = recent.map(order => `
        <tr>
            <td>${order.orderId || 'N/A'}</td>
            <td>${order.name || 'N/A'}</td>
            <td>$${(order.total || 0).toFixed(2)}</td>
            <td><span class="badge badge-${order.status || 'pending'}">${(order.status || 'pending').charAt(0).toUpperCase() + (order.status || 'pending').slice(1)}</span></td>
        </tr>
    `).join('');
}

function initializeCharts() {
    // Sales Chart
    const salesCtx = document.getElementById('salesChart');
    if (salesCtx) {
        const orders = JSON.parse(localStorage.getItem('orders')) || [];
        const weeklyData = getWeeklySalesData(orders);
        
        new Chart(salesCtx, {
            type: 'line',
            data: {
                labels: weeklyData.labels,
                datasets: [{
                    label: 'Sales ($)',
                    data: weeklyData.data,
                    borderColor: '#ff6b9d',
                    backgroundColor: 'rgba(255, 107, 157, 0.1)',
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });
    }
    
    // Order Status Chart
    const statusCtx = document.getElementById('orderStatusChart');
    if (statusCtx) {
        const orders = JSON.parse(localStorage.getItem('orders')) || [];
        const statusCounts = {
            pending: 0,
            preparing: 0,
            ready: 0,
            delivered: 0,
            cancelled: 0
        };
        
        orders.forEach(o => {
            const status = o.status || 'pending';
            statusCounts[status] = (statusCounts[status] || 0) + 1;
        });
        
        new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: Object.keys(statusCounts),
                datasets: [{
                    data: Object.values(statusCounts),
                    backgroundColor: [
                        '#fff3cd',
                        '#cfe2ff',
                        '#d1e7dd',
                        '#d1e7dd',
                        '#f8d7da'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });
    }
}

function getWeeklySalesData(orders) {
    const days = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
    const data = [0, 0, 0, 0, 0, 0, 0];
    
    orders.forEach(order => {
        const date = new Date(order.timestamp || Date.now());
        const day = (date.getDay() + 6) % 7; // Monday = 0
        data[day] += order.total || 0;
    });
    
    return { labels: days, data };
}

function loadDashboardData() {
    // This function can be used to refresh all dashboard data
    if (window.location.pathname.includes('index.php') || window.location.pathname.endsWith('admin/')) {
        initializeDashboard();
    }
}

// ===== Product Management =====
function initializeProducts() {
    loadProducts();
    
    document.getElementById('product-search')?.addEventListener('input', (e) => {
        filterProducts(e.target.value);
    });
    
    document.getElementById('category-filter')?.addEventListener('change', (e) => {
        filterProductsByCategory(e.target.value);
    });
    
    document.getElementById('status-filter')?.addEventListener('change', (e) => {
        filterProductsByStatus(e.target.value);
    });
}

function loadProducts() {
    const products = JSON.parse(localStorage.getItem('adminProducts')) || getDefaultProducts();
    const tbody = document.getElementById('products-table');
    
    if (products.length === 0) {
        tbody.innerHTML = '<tr><td colspan="8" class="text-center text-muted">No products found. Add your first product!</td></tr>';
        return;
    }
    
    tbody.innerHTML = products.map((product, index) => `
        <tr>
            <td><input type="checkbox" class="product-checkbox" data-id="${index}"></td>
            <td>
                <div style="width: 50px; height: 50px; background: linear-gradient(135deg, ${getCategoryColor(product.category)}); border-radius: 5px;"></div>
            </td>
            <td><strong>${product.name}</strong></td>
            <td><span class="badge">${product.category}</span></td>
            <td>$${product.price.toFixed(2)}</td>
            <td>${product.stock || 'N/A'}</td>
            <td><span class="badge badge-${product.status}">${product.status === 'in-stock' ? 'In Stock' : 'Out of Stock'}</span></td>
            <td>
                <button class="btn btn-sm btn-primary" onclick="editProduct(${index})">
                    <i class="fas fa-edit"></i>
                </button>
                <button class="btn btn-sm btn-danger" onclick="deleteProduct(${index})">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        </tr>
    `).join('');
}

function getDefaultProducts() {
    return [
        { name: 'Vanilla Dream', category: 'classic', price: 4.49, stock: 50, status: 'in-stock', description: 'Classic Madagascar vanilla bean ice cream' },
        { name: 'Chocolate Fudge', category: 'chocolate', price: 5.99, stock: 45, status: 'in-stock', description: 'Rich Belgian chocolate with fudge chunks' },
        { name: 'Strawberry Delight', category: 'premium', price: 4.99, stock: 8, status: 'in-stock', description: 'Fresh strawberries in creamy vanilla' }
    ];
}

function getCategoryColor(category) {
    const colors = {
        'classic': '#ffd700, #ffa500',
        'premium': '#ff6b9d, #ffc3a0',
        'chocolate': '#8b4513, #d2691e',
        'fruit': '#ff6b6b, #ffa07a',
        'sugar-free': '#e0e0e0, #f5f5f5'
    };
    return colors[category] || '#ccc, #ddd';
}

function saveProduct() {
    const products = JSON.parse(localStorage.getItem('adminProducts')) || getDefaultProducts();
    const productId = document.getElementById('product-id').value;
    
    const product = {
        name: document.getElementById('product-name').value,
        category: document.getElementById('product-category').value,
        price: parseFloat(document.getElementById('product-price').value),
        status: document.getElementById('product-status').value,
        description: document.getElementById('product-description').value,
        stock: 50 // Default stock
    };
    
    if (productId) {
        products[productId] = product;
    } else {
        products.push(product);
    }
    
    localStorage.setItem('adminProducts', JSON.stringify(products));
    bootstrap.Modal.getInstance(document.getElementById('addProductModal')).hide();
    loadProducts();
    showNotification('Product saved successfully!');
}

function editProduct(index) {
    const products = JSON.parse(localStorage.getItem('adminProducts')) || getDefaultProducts();
    const product = products[index];
    
    document.getElementById('product-id').value = index;
    document.getElementById('product-name').value = product.name;
    document.getElementById('product-category').value = product.category;
    document.getElementById('product-price').value = product.price;
    document.getElementById('product-status').value = product.status;
    document.getElementById('product-description').value = product.description;
    document.getElementById('productModalTitle').textContent = 'Edit Product';
    
    new bootstrap.Modal(document.getElementById('addProductModal')).show();
}

function deleteProduct(index) {
    if (confirm('Are you sure you want to delete this product?')) {
        const products = JSON.parse(localStorage.getItem('adminProducts')) || getDefaultProducts();
        products.splice(index, 1);
        localStorage.setItem('adminProducts', JSON.stringify(products));
        loadProducts();
        showNotification('Product deleted successfully!');
    }
}

function filterProducts(search) {
    // Implementation for search filtering
    loadProducts();
}

function filterProductsByCategory(category) {
    // Implementation for category filtering
    loadProducts();
}

function filterProductsByStatus(status) {
    // Implementation for status filtering
    loadProducts();
}

// ===== Order Management =====
function initializeOrders() {
    loadOrders();
}

function loadOrders() {
    const orders = JSON.parse(localStorage.getItem('orders')) || [];
    const tbody = document.getElementById('orders-table');
    
    if (orders.length === 0) {
        tbody.innerHTML = '<tr><td colspan="8" class="text-center text-muted">No orders found</td></tr>';
        return;
    }
    
    tbody.innerHTML = orders.map((order, index) => `
        <tr>
            <td><strong>${order.orderId || 'N/A'}</strong></td>
            <td>${order.name || 'N/A'}</td>
            <td>${order.items ? order.items.length : 0} items</td>
            <td>$${(order.total || 0).toFixed(2)}</td>
            <td>${order.payment || 'N/A'}</td>
            <td>${new Date(order.timestamp || Date.now()).toLocaleDateString()}</td>
            <td><span class="badge badge-${order.status || 'pending'}">${(order.status || 'pending').charAt(0).toUpperCase() + (order.status || 'pending').slice(1)}</span></td>
            <td>
                <button class="btn btn-sm btn-primary" onclick="viewOrderDetails(${index})">
                    <i class="fas fa-eye"></i>
                </button>
            </td>
        </tr>
    `).join('');
}

function viewOrderDetails(index) {
    const orders = JSON.parse(localStorage.getItem('orders')) || [];
    const order = orders[index];
    
    document.getElementById('modal-order-id').textContent = order.orderId || 'N/A';
    document.getElementById('modal-customer-name').textContent = order.name || 'N/A';
    document.getElementById('modal-customer-mobile').textContent = order.mobile || 'N/A';
    document.getElementById('modal-customer-email').textContent = order.email || 'N/A';
    document.getElementById('modal-customer-address').textContent = order.address || 'N/A';
    document.getElementById('modal-order-date').textContent = new Date(order.timestamp || Date.now()).toLocaleString();
    document.getElementById('modal-payment-method').textContent = order.payment || 'N/A';
    document.getElementById('modal-delivery-time').textContent = order.deliveryTime || 'N/A';
    document.getElementById('modal-order-status').textContent = order.status || 'pending';
    document.getElementById('update-order-status').value = order.status || 'pending';
    document.getElementById('modal-order-total').textContent = (order.total || 0).toFixed(2);
    document.getElementById('modal-order-notes').textContent = order.notes || 'No special notes';
    
    const itemsTbody = document.getElementById('modal-order-items');
    if (order.items && order.items.length > 0) {
        itemsTbody.innerHTML = order.items.map(item => `
            <tr>
                <td>${item.name}</td>
                <td>${item.quantity}</td>
                <td>$${item.price.toFixed(2)}</td>
                <td>$${(item.price * item.quantity).toFixed(2)}</td>
            </tr>
        `).join('');
    } else {
        itemsTbody.innerHTML = '<tr><td colspan="4" class="text-center text-muted">No items</td></tr>';
    }
    
    new bootstrap.Modal(document.getElementById('orderDetailsModal')).show();
}

function updateOrderStatus() {
    const orders = JSON.parse(localStorage.getItem('orders')) || [];
    const orderId = document.getElementById('modal-order-id').textContent;
    const newStatus = document.getElementById('update-order-status').value;
    
    const orderIndex = orders.findIndex(o => o.orderId === orderId);
    if (orderIndex !== -1) {
        orders[orderIndex].status = newStatus;
        localStorage.setItem('orders', JSON.stringify(orders));
        loadOrders();
        bootstrap.Modal.getInstance(document.getElementById('orderDetailsModal')).hide();
        showNotification('Order status updated successfully!');
    }
}

function filterOrders() {
    // Implementation for order filtering
    loadOrders();
}

// ===== Customer Management =====
function initializeCustomers() {
    loadCustomers();
    loadCustomerStats();
}

function loadCustomerStats() {
    const users = JSON.parse(localStorage.getItem('users')) || [];
    const orders = JSON.parse(localStorage.getItem('orders')) || [];
    
    document.getElementById('total-customers').textContent = users.length;
    document.getElementById('active-customers').textContent = users.length;
    document.getElementById('new-customers').textContent = users.filter(u => {
        const created = new Date(u.createdAt || Date.now());
        return created.getMonth() === new Date().getMonth();
    }).length;
    document.getElementById('blocked-customers').textContent = 0;
}

function loadCustomers() {
    const users = JSON.parse(localStorage.getItem('users')) || [];
    const orders = JSON.parse(localStorage.getItem('orders')) || [];
    const tbody = document.getElementById('customers-table');
    
    if (users.length === 0) {
        tbody.innerHTML = '<tr><td colspan="8" class="text-center text-muted">No customers found</td></tr>';
        return;
    }
    
    tbody.innerHTML = users.map((user, index) => {
        const userOrders = orders.filter(o => o.email === user.email);
        const totalSpent = userOrders.reduce((sum, o) => sum + (o.total || 0), 0);
        
        return `
            <tr>
                <td>C${String(index + 1).padStart(4, '0')}</td>
                <td>${user.firstName} ${user.lastName}</td>
                <td>${user.email}</td>
                <td>${user.phone || 'N/A'}</td>
                <td>${userOrders.length}</td>
                <td>$${totalSpent.toFixed(2)}</td>
                <td><span class="badge badge-active">Active</span></td>
                <td>
                    <button class="btn btn-sm btn-primary" onclick="viewCustomerDetails(${index})">
                        <i class="fas fa-eye"></i>
                    </button>
                </td>
            </tr>
        `;
    }).join('');
}

function viewCustomerDetails(index) {
    const users = JSON.parse(localStorage.getItem('users')) || [];
    const orders = JSON.parse(localStorage.getItem('orders')) || [];
    const user = users[index];
    
    const userOrders = orders.filter(o => o.email === user.email);
    const totalSpent = userOrders.reduce((sum, o) => sum + (o.total || 0), 0);
    
    document.getElementById('modal-cust-name').textContent = `${user.firstName} ${user.lastName}`;
    document.getElementById('modal-cust-email').textContent = user.email;
    document.getElementById('modal-cust-phone').textContent = user.phone || 'N/A';
    document.getElementById('modal-cust-address').textContent = user.address || 'N/A';
    document.getElementById('modal-cust-orders').textContent = userOrders.length;
    document.getElementById('modal-cust-spent').textContent = totalSpent.toFixed(2);
    document.getElementById('modal-cust-joined').textContent = new Date(user.createdAt || Date.now()).toLocaleDateString();
    document.getElementById('modal-cust-status').textContent = 'Active';
    
    const historyTbody = document.getElementById('modal-cust-order-history');
    if (userOrders.length > 0) {
        historyTbody.innerHTML = userOrders.slice(0, 5).map(order => `
            <tr>
                <td>${order.orderId || 'N/A'}</td>
                <td>${new Date(order.timestamp || Date.now()).toLocaleDateString()}</td>
                <td>${order.items ? order.items.length : 0}</td>
                <td>$${(order.total || 0).toFixed(2)}</td>
                <td><span class="badge badge-${order.status || 'pending'}">${order.status || 'pending'}</span></td>
            </tr>
        `).join('');
    } else {
        historyTbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted">No orders</td></tr>';
    }
    
    new bootstrap.Modal(document.getElementById('customerDetailsModal')).show();
}

// ===== Offers Management =====
function initializeOffers() {
    loadOffers();
}

function loadOffers() {
    const offers = JSON.parse(localStorage.getItem('adminOffers')) || [];
    const tbody = document.getElementById('offers-table');
    
    if (offers.length === 0) {
        tbody.innerHTML = '<tr><td colspan="9" class="text-center text-muted">No offers found. Create your first offer!</td></tr>';
        return;
    }
    
    tbody.innerHTML = offers.map((offer, index) => `
        <tr>
            <td><strong>${offer.code}</strong></td>
            <td>${offer.title}</td>
            <td>${offer.discountType === 'percentage' ? offer.discountValue + '%' : '$' + offer.discountValue}</td>
            <td>${new Date(offer.validFrom).toLocaleDateString()}</td>
            <td>${new Date(offer.validTo).toLocaleDateString()}</td>
            <td>${offer.usageLimit || 'Unlimited'}</td>
            <td>${offer.used || 0}</td>
            <td><span class="badge badge-active">Active</span></td>
            <td>
                <button class="btn btn-sm btn-primary" onclick="editOffer(${index})">
                    <i class="fas fa-edit"></i>
                </button>
                <button class="btn btn-sm btn-danger" onclick="deleteOffer(${index})">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        </tr>
    `).join('');
}

function saveOffer() {
    const offers = JSON.parse(localStorage.getItem('adminOffers')) || [];
    const offerId = document.getElementById('offer-id').value;
    
    const offer = {
        code: document.getElementById('offer-code').value,
        title: document.getElementById('offer-title').value,
        description: document.getElementById('offer-description').value,
        discountType: document.getElementById('offer-discount-type').value,
        discountValue: parseFloat(document.getElementById('offer-discount-value').value),
        minPurchase: parseFloat(document.getElementById('offer-min-purchase').value) || 0,
        validFrom: document.getElementById('offer-valid-from').value,
        validTo: document.getElementById('offer-valid-to').value,
        usageLimit: parseInt(document.getElementById('offer-usage-limit').value) || 0,
        applyTo: document.getElementById('offer-apply-to').value,
        used: 0
    };
    
    if (offerId) {
        offers[offerId] = offer;
    } else {
        offers.push(offer);
    }
    
    localStorage.setItem('adminOffers', JSON.stringify(offers));
    bootstrap.Modal.getInstance(document.getElementById('addOfferModal')).hide();
    loadOffers();
    showNotification('Offer saved successfully!');
}

function deleteOffer(index) {
    if (confirm('Are you sure you want to delete this offer?')) {
        const offers = JSON.parse(localStorage.getItem('adminOffers')) || [];
        offers.splice(index, 1);
        localStorage.setItem('adminOffers', JSON.stringify(offers));
        loadOffers();
        showNotification('Offer deleted successfully!');
    }
}

// ===== Gallery Management =====
function initializeGallery() {
    loadGallery();
}

function loadGallery() {
    const gallery = JSON.parse(localStorage.getItem('adminGallery')) || [];
    const grid = document.getElementById('gallery-grid');
    
    if (gallery.length === 0) {
        grid.innerHTML = '<div class="col-12 text-center text-muted py-5">No images found. Upload your first image!</div>';
        return;
    }
    
    grid.innerHTML = gallery.map((item, index) => `
        <div class="col-md-3 col-sm-4 col-6">
            <div class="gallery-item" onclick="previewImage(${index})">
                <div style="width: 100%; height: 200px; background: linear-gradient(135deg, #ff6b9d, #ffc3a0); border-radius: 10px;"></div>
                <div class="gallery-item-overlay">
                    <div class="gallery-item-actions">
                        <button class="btn btn-sm btn-danger" onclick="event.stopPropagation(); deleteGalleryImage(${index})">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `).join('');
}

function uploadImages() {
    const files = document.getElementById('gallery-images').files;
    const category = document.getElementById('image-category').value;
    const title = document.getElementById('image-title').value;
    
    if (!category) {
        alert('Please select a category');
        return;
    }
    
    const gallery = JSON.parse(localStorage.getItem('adminGallery')) || [];
    
    Array.from(files).forEach(file => {
        const reader = new FileReader();
        reader.onload = (e) => {
            gallery.push({
                url: e.target.result,
                category: category,
                title: title || file.name,
                uploaded: new Date().toISOString()
            });
            localStorage.setItem('adminGallery', JSON.stringify(gallery));
        };
        reader.readAsDataURL(file);
    });
    
    bootstrap.Modal.getInstance(document.getElementById('uploadImageModal')).hide();
    loadGallery();
    showNotification('Images uploaded successfully!');
}

function deleteGalleryImage(index) {
    if (confirm('Are you sure you want to delete this image?')) {
        const gallery = JSON.parse(localStorage.getItem('adminGallery')) || [];
        gallery.splice(index, 1);
        localStorage.setItem('adminGallery', JSON.stringify(gallery));
        loadGallery();
        showNotification('Image deleted successfully!');
    }
}

function filterGallery(category) {
    // Implementation for gallery filtering
    loadGallery();
}

// ===== Settings =====
function initializeSettings() {
    loadSettings();
}

function loadSettings() {
    const settings = JSON.parse(localStorage.getItem('adminSettings')) || getDefaultSettings();
    
    // Shop Details
    document.getElementById('shop-name').value = settings.shopName || '';
    document.getElementById('shop-tagline').value = settings.tagline || '';
    document.getElementById('shop-address').value = settings.address || '';
    document.getElementById('shop-phone').value = settings.phone || '';
    document.getElementById('shop-email').value = settings.email || '';
    document.getElementById('shop-website').value = settings.website || '';
    
    // Charges
    document.getElementById('tax-rate').value = settings.taxRate || 0;
    document.getElementById('delivery-charge').value = settings.deliveryCharge || 0;
    document.getElementById('free-delivery-min').value = settings.freeDeliveryMin || 0;
}

function getDefaultSettings() {
    return {
        shopName: 'BharatMart',
        tagline: 'Happiness in Every Scoop',
        address: '123 Sweet Street, Barat City, IC 12345',
        phone: '+1 (555) 123-4567',
        email: 'info@BharatMart.com',
        taxRate: 8.5,
        deliveryCharge: 5.00,
        freeDeliveryMin: 25.00
    };
}

// ===== Utility Functions =====
function showNotification(message) {
    // Create a simple notification
    const notification = document.createElement('div');
    notification.className = 'alert alert-success position-fixed top-0 end-0 m-3';
    notification.style.zIndex = '9999';
    notification.innerHTML = `<i class="fas fa-check-circle"></i> ${message}`;
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.remove();
    }, 3000);
}

function logout() {
    if (confirm('Are you sure you want to logout?')) {
        window.location.href = '../index.php';
    }
}

function exportProducts() {
    showNotification('Export feature coming soon!');
}

function exportOrders() {
    showNotification('Export feature coming soon!');
}

function exportCustomers() {
    showNotification('Export feature coming soon!');
}

function downloadTemplate() {
    showNotification('Template download coming soon!');
}

function processBulkUpload() {
    showNotification('Bulk upload feature coming soon!');
}

function addAdmin() {
    showNotification('Add admin feature coming soon!');
}
