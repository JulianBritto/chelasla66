<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Micheladas la 66 - Inventario y Facturación</title>
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>🍺 Inventario y sistema de facturación Micheladas la 66</h1>
        </div>

        <!-- Alert Messages -->
        <div style="padding: 0 30px; padding-top: 20px;">
            <div class="alert alert-success" id="successAlert"></div>
            <div class="alert alert-danger" id="errorAlert"></div>
        </div>

        <!-- Navigation Tabs -->
        <div class="nav-tabs">
            <button class="nav-tab active" onclick="switchTab('dashboard', this)">🏠 Principal</button>
            <button class="nav-tab" onclick="switchTab('inventory', this)">📊 Inventario</button>
            <button class="nav-tab" onclick="switchTab('profits', this)">💰 Ganancias</button>
            <button class="nav-tab" onclick="switchTab('invoices', this)">🧾 Facturas</button>
            <button class="nav-tab" onclick="switchTab('daily-close', this)">🔒 Cierre del Día</button>
            <button class="nav-tab" onclick="switchTab('statistics', this)">📈 Estadísticas</button>
        </div>

        <!-- Content -->
        <div class="content">
                        <!-- Daily Close Section -->
                        <div id="daily-close" class="section">
                            <div class="section-header">
                                <h2>🔒 Cierre del Día</h2>
                            </div>
                            <div class="search-bar" style="margin-bottom: 30px;">
                                <input type="date" id="closeDateFilter" style="padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 16px;" onchange="loadDailyCloseDashboard()">
                            </div>
                            <!-- Dashboard Totals -->
                            <div class="charts-container" style="display: flex; gap: 20px; justify-content: center; align-items: stretch; flex-wrap: nowrap; margin-bottom: 30px;">
                                <div class="stat-summary-card">
                                    <div class="summary-header">
                                        <h3>📊 Total de Transacciones</h3>
                                    </div>
                                    <div class="summary-content">
                                        <div class="summary-value" id="dailyTransactionCount" style="font-size: 36px; text-align: center;">0</div>
                                    </div>
                                </div>
                                <div class="stat-summary-card">
                                    <div class="summary-header">
                                        <h3>🧾 Total de Facturas</h3>
                                    </div>
                                    <div class="summary-content">
                                        <div class="summary-value" id="dailyInvoiceCount" style="font-size: 36px; text-align: center;">0</div>
                                    </div>
                                </div>
                                <div class="stat-summary-card">
                                    <div class="summary-header">
                                        <h3>💰 Total de Ventas</h3>
                                    </div>
                                    <div class="summary-content">
                                        <div class="summary-value" id="dailyTotalSales" style="font-size: 36px; text-align: center;">$0</div>
                                    </div>
                                </div>
                            </div>
                            <!-- Products Summary Table -->
                            <div style="margin-top: 30px;">
                                <h3 style="margin-bottom: 20px;">📦 Resumen de Productos Vendidos</h3>
                                <div class="table-container">
                                    <table>
                                        <thead>
                                            <tr>
                                                <th>Producto</th>
                                                <th>Cantidad Vendida</th>
                                                <th>Precio Total</th>
                                                <th>Hora</th>
                                                <th>Hora actualización</th>
                                                <th>Nº Factura</th>
                                            </tr>
                                        </thead>
                                        <tbody id="dailyProductsTable">
                                            <tr>
                                                <td colspan="6" class="text-center">Selecciona una fecha para ver los datos</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="pagination-container" id="dailyProductsPagination"></div>
                            </div>
                        </div>
            <!-- Dashboard Section -->
            <div id="dashboard" class="section active">
                <div class="section-header">
                    <h2>Dashboard</h2>
                </div>

                <div class="stats-grid">
                    <div class="stat-card">
                        <h3>Total de Productos</h3>
                        <div class="value">{{ $totalProducts }}</div>
                    </div>
                    <div class="stat-card">
                        <h3>Stock Total</h3>
                        <div class="value">{{ $totalStock }}</div>
                    </div>
                    <div class="stat-card">
                        <h3>Total de Facturas</h3>
                        <div class="value">{{ $totalInvoices }}</div>
                    </div>
                    <div class="stat-card">
                        <h3>Ingresos Totales</h3>
                        <div class="value">${{ number_format($totalRevenue, 2) }}</div>
                    </div>
                </div>

                <div style="display: flex; gap: 20px; margin-top: 20px;">
                    <!-- Productos con Bajo Stock -->
                    <div style="flex: 1;">
                        <h3>Productos con Bajo Stock</h3>
                        <div class="table-container">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Producto</th>
                                        <th>Stock</th>
                                    </tr>
                                </thead>
                                <tbody id="lowStockTable">
                                </tbody>
                            </table>
                        </div>
                        <div class="low-stock-pagination-container">
                            <div id="lowStockPagination" class="pagination"></div>
                        </div>
                    </div>

                    <!-- Últimas Facturas -->
                    <div style="flex: 1;">
                        <h3>Últimas Facturas</h3>
                        <div class="table-container">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Factura</th>
                                        <th>Fecha</th>
                                        <th>Total</th>
                                        <th>Estado</th>
                                        <th>Ver</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($recentInvoices as $invoice)
                                        <tr>
                                            <td>{{ $invoice->invoice_number }}</td>
                                            <td>{{ $invoice->invoice_date->format('d/m/Y H:i') }}</td>
                                            <td>${{ number_format($invoice->total, 2) }}</td>
                                            <td><span class="badge badge-success">{{ $invoice->status }}</span></td>
                                            <td>
                                                <button class="btn btn-info" onclick="viewInvoice({{ $invoice->id }})">Ver</button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center">No hay facturas registradas</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Inventory Section -->
            <div id="inventory" class="section">
                <div class="section-header">
                    <h2>Gestión de Inventario</h2>
                    <button class="btn btn-primary" onclick="openAddProductModal()">+ Agregar Producto</button>
                </div>

                <div class="search-bar">
                    <input type="text" id="productSearch" placeholder="🔍 Buscar producto por nombre..." onkeyup="filterProducts()">
                </div>

                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Precio</th>
                                <th>Stock</th>
                                <th>Descripción</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="productsTable">
                            <tr>
                                <td colspan="5" class="text-center">Cargando productos...</td>
                            </tr>
                        </tbody>
                    </table>
                    <div class="pagination-container" id="productsPagination"></div>
                </div>
            </div>

            <!-- Invoices Section -->
            <div id="invoices" class="section">
                <div class="section-header">
                    <h2>Gestión de Facturas</h2>
                    <button class="btn btn-primary" onclick="openCreateInvoiceModal()">+ Nueva Factura</button>
                </div>

                <div class="search-bar">
                    <input type="text" id="invoiceSearch" placeholder="🔍 Buscar factura..." onkeyup="filterInvoices()">
                </div>

                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Número de Factura</th>
                                <th>Fecha</th>
                                <th>Fecha actualización</th>
                                <th>Total</th>
                                <th>Items</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="invoicesTable">
                            <tr>
                                <td colspan="7" class="text-center">Cargando facturas...</td>
                            </tr>
                        </tbody>
                    </table>
                    <div class="pagination-container" id="invoicesPagination"></div>
                </div>
            </div>

            <!-- Profits Section -->
            <div id="profits" class="section">
                <div class="section-header">
                    <h2>💰 Gestión de Ganancias</h2>
                </div>

                <div class="search-bar">
                    <input type="text" id="profitSearch" placeholder="Buscar por nombre de producto" onkeyup="filterProfits()">
                </div>

                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Producto</th>
                                <th>Precio Venta</th>
                                <th>Costo Compra</th>
                                <th>Ganancia</th>
                                <th>Margen %</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="profitsTable">
                            <tr>
                                <td colspan="6" style="text-align: center;">Cargando...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="pagination-container" id="profitsPagination"></div>
            </div>

            <!-- Statistics Section (placeholder) -->
            <div id="statistics" class="section">
                <div class="section-header">
                    <h2>📈 Estadísticas</h2>
                </div>

                <div style="margin-top: 10px;">
                    <h3 style="margin-bottom: 15px;">🏆 Top 5 productos más vendidos</h3>
                    <div class="table-container">
                        <table class="stats-table">
                            <thead>
                                <tr>
                                    <th>Producto</th>
                                    <th>Cantidad</th>
                                    <th>Total vendido</th>
                                </tr>
                            </thead>
                            <tbody id="topProductsTable">
                                <tr>
                                    <td colspan="3" class="text-center">Cargando...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div style="margin-top: 30px;">
                    <h3 style="margin-bottom: 15px;">📅 Top 4 días con más productos vendidos</h3>
                    <div class="table-container">
                        <table class="stats-table">
                            <thead>
                                <tr>
                                    <th>Fecha (Año-Mes-Día)</th>
                                    <th>Productos vendidos</th>
                                    <th>Top 3 productos</th>
                                    <th>Total vendido</th>
                                </tr>
                            </thead>
                            <tbody id="topDaysTable">
                                <tr>
                                    <td colspan="4" class="text-center">Cargando...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>

            <!-- ...existing code... -->

    <!-- Add/Edit Product Modal -->
    <div id="productModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="productModalTitle">Agregar Producto</h2>
                <button class="close-btn" onclick="closeProductModal()">&times;</button>
            </div>
            <form id="productForm">
                <div class="form-group">
                    <label for="productName">Nombre del Producto *</label>
                    <input type="text" id="productName" name="name" required>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="productPrice">Precio *</label>
                        <input type="number" id="productPrice" name="price" step="0.01" min="0" required>
                    </div>
                    <div class="form-group">
                        <label for="productStock">Stock *</label>
                        <input type="number" id="productStock" name="stock" min="0" required>
                    </div>
                </div>
                <div class="form-group">
                    <label for="productDescription">Descripción</label>
                    <textarea id="productDescription" name="description" rows="4"></textarea>
                </div>
                <div style="display: flex; gap: 10px;">
                    <button type="submit" class="btn btn-primary" style="flex: 1;">Guardar Producto</button>
                    <button type="button" class="btn btn-secondary" onclick="closeProductModal()" style="flex: 1;">Cancelar</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Confirm Delete Product Modal -->
    <div id="confirmDeleteProductModal" class="modal">
        <div class="modal-content confirm-modal-content">
            <div class="modal-header confirm-modal-header">
                <div class="confirm-modal-icon" aria-hidden="true">!</div>
                <button class="close-btn" onclick="closeConfirmDeleteProductModal()">&times;</button>
            </div>
            <div class="confirm-modal-body">
                <h2 style="margin-bottom: 10px;">Eliminar producto</h2>
                <p style="margin-bottom: 8px; color: #333;">
                    ¿Seguro que deseas eliminar <strong id="confirmDeleteProductName">este producto</strong>?
                </p>
                <p class="confirm-modal-subtext">Esta acción no se puede deshacer.</p>
            </div>
            <div class="confirm-modal-actions">
                <button type="button" class="btn btn-secondary" onclick="closeConfirmDeleteProductModal()" style="flex: 1;">Cancelar</button>
                <button type="button" id="confirmDeleteProductBtn" class="btn btn-danger" onclick="confirmDeleteProduct()" style="flex: 1;">Eliminar</button>
            </div>
        </div>
    </div>

    <!-- Confirm Delete Invoice Modal -->
    <div id="confirmDeleteInvoiceModal" class="modal">
        <div class="modal-content confirm-modal-content">
            <div class="modal-header confirm-modal-header">
                <div class="confirm-modal-icon" aria-hidden="true">!</div>
                <button class="close-btn" onclick="closeConfirmDeleteInvoiceModal()">&times;</button>
            </div>
            <div class="confirm-modal-body">
                <h2 style="margin-bottom: 10px;">Eliminar factura</h2>
                <p style="margin-bottom: 8px; color: #333;">
                    ¿Seguro que deseas eliminar <strong id="confirmDeleteInvoiceNumber">esta factura</strong>?
                </p>
                <p class="confirm-modal-subtext">Esto restaurará el stock de los productos. Esta acción no se puede deshacer.</p>
            </div>
            <div class="confirm-modal-actions">
                <button type="button" class="btn btn-secondary" onclick="closeConfirmDeleteInvoiceModal()" style="flex: 1;">Cancelar</button>
                <button type="button" id="confirmDeleteInvoiceBtn" class="btn btn-danger" onclick="confirmDeleteInvoice()" style="flex: 1;">Eliminar</button>
            </div>
        </div>
    </div>

    <!-- Edit Profit Modal -->
    <div id="profitModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Editar Ganancia</h2>
                <button class="close-btn" onclick="closeProfitModal()">&times;</button>
            </div>
            <form id="profitForm">
                <input type="hidden" id="profitProductId">
                <div class="form-group">
                    <label for="profitProductName">Producto</label>
                    <input type="text" id="profitProductName" disabled style="background-color: #f5f5f5;">
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="profitSalePrice">Precio Venta *</label>
                        <input type="number" id="profitSalePrice" step="0.01" min="0" required readonly style="background-color: #f5f5f5;">
                    </div>
                    <div class="form-group">
                        <label for="profitPurchasePrice">Costo Compra *</label>
                        <input type="number" id="profitPurchasePrice" step="0.01" min="0" required>
                    </div>
                </div>
                <div class="form-group">
                    <label for="profitGanancia">Ganancia</label>
                    <input type="number" id="profitGanancia" step="0.01" min="0" disabled style="background-color: #f5f5f5;">
                </div>
                <div style="display: flex; gap: 10px;">
                    <button type="submit" class="btn btn-primary" style="flex: 1;">Guardar Ganancia</button>
                    <button type="button" class="btn btn-secondary" onclick="closeProfitModal()" style="flex: 1;">Cancelar</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Create Invoice Modal -->
    <div id="invoiceModal" class="modal">
        <div class="modal-content" style="max-width: 700px;">
            <div class="modal-header">
                <h2>Nueva Factura</h2>
                <button class="close-btn" onclick="closeInvoiceModal()">&times;</button>
            </div>
            <form id="invoiceForm">
                <div class="invoice-items-container">
                    <h3 style="margin-bottom: 15px;">Seleccionar Productos</h3>
                    <div id="invoiceItemsContainer"></div>
                    <button type="button" class="btn btn-secondary" onclick="addInvoiceItem()">+ Agregar Producto</button>
                </div>

                <div class="form-group">
                    <label for="invoiceNotes">Notas</label>
                    <textarea id="invoiceNotes" name="notes" rows="3"></textarea>
                </div>

                <div style="background: #f0f0f0; padding: 15px; border-radius: 4px; margin-bottom: 20px;">
                    <strong>Total: $<span id="invoiceTotal">0.00</span></strong>
                </div>

                <div style="display: flex; gap: 10px;">
                    <button type="submit" class="btn btn-primary" style="flex: 1;">Crear Factura</button>
                    <button type="button" class="btn btn-secondary" onclick="closeInvoiceModal()" style="flex: 1;">Cancelar</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Invoice Modal -->
    <div id="invoiceEditModal" class="modal">
        <div class="modal-content" style="max-width: 700px;">
            <div class="modal-header">
                <h2>Editar Factura</h2>
                <button class="close-btn" onclick="closeEditInvoiceModal()">&times;</button>
            </div>
            <form id="invoiceEditForm">
                <div class="invoice-items-container">
                    <h3 style="margin-bottom: 15px;">Productos y Cantidades</h3>
                    <div id="invoiceEditItemsContainer"></div>
                    <button type="button" class="btn btn-secondary" onclick="addEditInvoiceItem()">+ Agregar Producto</button>
                </div>

                <div style="background: #f0f0f0; padding: 15px; border-radius: 4px; margin-bottom: 20px;">
                    <strong>Total: $<span id="invoiceEditTotal">0.00</span></strong>
                </div>

                <div style="display: flex; gap: 10px;">
                    <button type="submit" class="btn btn-primary" style="flex: 1;">Guardar Cambios</button>
                    <button type="button" class="btn btn-secondary" onclick="closeEditInvoiceModal()" style="flex: 1;">Cancelar</button>
                </div>
            </form>
        </div>
    </div>

    <!-- View Invoice Modal -->
    <div id="invoiceDetailsModal" class="modal">
        <div class="modal-content modal-content-large">
            <div class="modal-header">
                <h2 id="invoiceNumberDisplay">Factura</h2>
                <button class="close-btn" onclick="closeInvoiceDetailsModal()">&times;</button>
            </div>
            <div id="invoiceDetailsContent">
                <!-- Contenido dinámico -->
            </div>
            <div style="display: flex; gap: 10px; margin-top: 20px;">
                <button onclick="printInvoice()" class="btn btn-secondary" style="flex: 1;">Imprimir</button>
                <button type="button" class="btn btn-secondary" onclick="closeInvoiceDetailsModal()" style="flex: 1;">Cerrar</button>
            </div>
        </div>
    </div>

    <script>
                // Daily Close Dashboard Functions
                let dailyCloseData = [];
                let dailyCloseCurrentPage = 1;
                let dailyCloseItemsPerPage = 7;

                function initializeDailyClose() {
                    const dateInput = document.getElementById('closeDateFilter');
                    if (!dateInput) return;
                    if (!dateInput.value) {
                        const today = new Date();
                        dateInput.value = today.toISOString().split('T')[0];
                    }
                    loadDailyCloseDashboard();
                }

                function loadDailyCloseDashboard() {
                    const selectedDate = document.getElementById('closeDateFilter').value;
                    if (!selectedDate) return;

                    fetch(`/api/sold-products?date=${selectedDate}`)
                        .then(res => res.json())
                        .then(data => {
                            dailyCloseData = data;
                            // Totals
                            // Sumar todas las cantidades vendidas
                            const totalCantidadVendida = data.reduce((s, d) => s + parseInt(d.quantity || 0), 0);
                            document.getElementById('dailyTransactionCount').textContent = totalCantidadVendida;
                            // Facturas distintas
                            const invoicesSet = new Set(data.map(d => d.invoice_number));
                            document.getElementById('dailyInvoiceCount').textContent = invoicesSet.size;
                            const totalSales = data.reduce((s, d) => s + parseFloat(d.price_total || 0), 0);
                            document.getElementById('dailyTotalSales').textContent = '$' + totalSales.toFixed(2);
                            dailyCloseCurrentPage = 1;
                            displayDailyClosePage();
                        })
                        .catch(err => showError('Error al cargar cierre del día: ' + err));
                }

                function displayDailyClosePage() {
                    const tbody = document.getElementById('dailyProductsTable');
                    const startIndex = (dailyCloseCurrentPage - 1) * dailyCloseItemsPerPage;
                    const pageData = dailyCloseData.slice(startIndex, startIndex + dailyCloseItemsPerPage);

                    if (!pageData.length) {
                        tbody.innerHTML = '<tr><td colspan="6" class="text-center">No hay productos vendidos en esta fecha</td></tr>';
                        document.getElementById('dailyProductsPagination').innerHTML = '';
                        return;
                    }

                    tbody.innerHTML = pageData.map(item => {
                        const dateStr = item.invoice_created_at || item.invoice_date || item.created_at;
                        const dt = new Date(dateStr);
                        const timeString = isNaN(dt.getTime())
                            ? '—'
                            : dt.toLocaleString('es-CO', { month: '2-digit', day: '2-digit', hour: '2-digit', minute: '2-digit' });

                        const updatedStr = item.updated_at || null;
                        const udt = updatedStr ? new Date(updatedStr) : null;
                        const updatedTimeString = (!udt || isNaN(udt.getTime()))
                            ? '—'
                            : udt.toLocaleString('es-CO', { month: '2-digit', day: '2-digit', hour: '2-digit', minute: '2-digit' });
                        return `
                        <tr>
                            <td>${item.product_name || '—'}</td>
                            <td>${item.quantity}</td>
                            <td>$${parseFloat(item.price_total).toFixed(2)}</td>
                            <td>${timeString}</td>
                            <td>${updatedTimeString}</td>
                            <td>${item.invoice_number}</td>
                        </tr>
                    `;
                    }).join('');

                    const totalPages = Math.ceil(dailyCloseData.length / dailyCloseItemsPerPage);
                    let paginationHTML = '';
                    for (let i = 1; i <= totalPages; i++) {
                        paginationHTML += `<button class="pagination-btn ${i === dailyCloseCurrentPage ? 'active' : ''}" onclick="dailyCloseCurrentPage = ${i}; displayDailyClosePage();">${i}</button>`;
                    }
                    document.getElementById('dailyProductsPagination').innerHTML = paginationHTML;
                }
        let products = [];
        let allInvoices = [];
        let lowStockProducts = [];
        let profitsData = [];
        let currentProductId = null;
        let invoiceItemCount = 0;
        let editingInvoiceId = null;
        let editInvoiceItemCount = 0;
        let currentInvoiceDetails = null;

        // Pagination variables
        let productsCurrentPage = 1;
        let invoicesCurrentPage = 1;
        let lowStockCurrentPage = 1;
        let profitsCurrentPage = 1;
        const itemsPerPage = 4;
        const itemsPerPageProducts = 7;
        const itemsPerPageInvoices = 6;
        const itemsPerPageLowStock = 5;
        const itemsPerPageProfits = 7;

        let pendingDeleteProductId = null;
        let pendingDeleteInvoiceId = null;

        // Tab Switching
        // ...la versión correcta de switchTab ya está definida más abajo...

        // Load Products
        function loadProducts() {
            fetch('/api/products')
                .then(response => response.json())
                .then(data => {
                    products = data;
                    productsCurrentPage = 1;
                    displayProductsPage(1);
                })
                .catch(error => showError('Error al cargar productos: ' + error));
        }

        // Display Products Page
        function displayProductsPage(page) {
            const tbody = document.getElementById('productsTable');
            
            if (products.length === 0) {
                tbody.innerHTML = '<tr><td colspan="5" class="text-center"><div class="empty-state"><h3>Productos</h3><p>Comienza agregando un nuevo producto</p></div></td></tr>';
                document.getElementById('productsPagination').innerHTML = '';
                return;
            }

            const start = (page - 1) * itemsPerPageProducts;
            const end = start + itemsPerPageProducts;
            const pageProducts = products.slice(start, end);

            tbody.innerHTML = pageProducts.map(product => `
                <tr>
                    <td><strong>${product.name}</strong></td>
                    <td>$${parseFloat(product.price).toFixed(2)}</td>
                    <td>${Number(product.stock) <= 5 ? `<span class="badge badge-warning">${product.stock}</span>` : product.stock}</td>
                    <td>${product.description || '-'}</td>
                    <td>
                        <div class="action-buttons">
                            <button class="btn btn-secondary" onclick="editProduct(${product.id})">Editar</button>
                            <button class="btn btn-danger" onclick="deleteProduct(${product.id})">Eliminar</button>
                        </div>
                    </td>
                </tr>
            `).join('');

            // Generar paginación
            generateProductsPagination();
        }

        // Generate Products Pagination
        function generateProductsPagination() {
            const paginationContainer = document.getElementById('productsPagination');
            paginationContainer.innerHTML = '';

            const totalPages = Math.ceil(products.length / itemsPerPageProducts);

            if (totalPages <= 1) return;

            // Botón anterior
            const prevBtn = document.createElement('button');
            prevBtn.className = 'pagination-btn';
            prevBtn.textContent = '< Anterior';
            prevBtn.disabled = productsCurrentPage === 1;
            prevBtn.onclick = () => {
                if (productsCurrentPage > 1) {
                    productsCurrentPage--;
                    displayProductsPage(productsCurrentPage);
                }
            };
            paginationContainer.appendChild(prevBtn);

            // Números de página
            for (let i = 1; i <= totalPages; i++) {
                const btn = document.createElement('button');
                btn.className = 'pagination-btn' + (i === productsCurrentPage ? ' active' : '');
                btn.textContent = i;
                btn.onclick = () => {
                    productsCurrentPage = i;
                    displayProductsPage(i);
                };
                paginationContainer.appendChild(btn);
            }

            // Botón siguiente
            const nextBtn = document.createElement('button');
            nextBtn.className = 'pagination-btn';
            nextBtn.textContent = 'Siguiente >';
            nextBtn.disabled = productsCurrentPage === totalPages;
            nextBtn.onclick = () => {
                if (productsCurrentPage < totalPages) {
                    productsCurrentPage++;
                    displayProductsPage(productsCurrentPage);
                }
            };
            paginationContainer.appendChild(nextBtn);
        }

        // Render Products Table (legacy)
        function renderProducts() {
            displayProductsPage(productsCurrentPage);
        }

        // Filter Products
        function filterProducts() {
            const searchTerm = document.getElementById('productSearch').value.toLowerCase();
            const filtered = products.filter(p => 
                p.name.toLowerCase().includes(searchTerm)
            );
            const tbody = document.getElementById('productsTable');
            if (filtered.length === 0) {
                tbody.innerHTML = '<tr><td colspan="5" class="text-center">No se encontraron productos</td></tr>';
                return;
            }

            tbody.innerHTML = filtered.map(product => `
                <tr>
                    <td><strong>${product.name}</strong></td>
                    <td>$${parseFloat(product.price).toFixed(2)}</td>
                    <td>${Number(product.stock) <= 5 ? `<span class="badge badge-warning">${product.stock}</span>` : product.stock}</td>
                    <td>${product.description || '-'}</td>
                    <td>
                        <div class="action-buttons">
                            <button class="btn btn-secondary" onclick="editProduct(${product.id})">Editar</button>
                            <button class="btn btn-danger" onclick="deleteProduct(${product.id})">Eliminar</button>
                        </div>
                    </td>
                </tr>
            `).join('');
        }

        // Open Add Product Modal
        function openAddProductModal() {
            currentProductId = null;
            document.getElementById('productForm').reset();
            document.getElementById('productModalTitle').textContent = 'Agregar Producto';
            document.getElementById('productModal').classList.add('show');
        }

        // Close Product Modal
        function closeProductModal() {
            document.getElementById('productModal').classList.remove('show');
        }

        // Edit Product
        function editProduct(id) {
            const product = products.find(p => p.id === id);
            if (!product) return;

            currentProductId = id;
            document.getElementById('productName').value = product.name;
            document.getElementById('productPrice').value = product.price;
            document.getElementById('productStock').value = product.stock;
            document.getElementById('productDescription').value = product.description || '';
            
            document.getElementById('productModalTitle').textContent = 'Editar Producto';
            document.getElementById('productModal').classList.add('show');
        }

        // Save Product
        document.getElementById('productForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const data = Object.fromEntries(formData);

            // Validaciones
            if (!data.name.trim()) {
                showError('El nombre del producto es requerido');
                return;
            }
            if (data.price <= 0) {
                showError('El precio debe ser mayor a 0');
                return;
            }
            if (data.stock < 0) {
                showError('El stock no puede ser negativo');
                return;
            }

            const method = currentProductId ? 'PUT' : 'POST';
            const url = currentProductId ? `/api/products/${currentProductId}` : '/api/products';

            fetch(url, {
                method: method,
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                },
                body: JSON.stringify(data)
            })
            .then(response => {
                if (!response.ok) throw new Error('Error en la respuesta');
                return response.json();
            })
            .then(data => {
                showSuccess(currentProductId ? '✓ Producto actualizado correctamente' : '✓ Producto agregado correctamente');
                closeProductModal();
                loadProducts();
            })
            .catch(error => showError('Error: ' + error.message));
        });

        // Delete Product
        function deleteProduct(id) {
            openConfirmDeleteProductModal(id);
        }

        function openConfirmDeleteProductModal(id) {
            pendingDeleteProductId = id;

            const modal = document.getElementById('confirmDeleteProductModal');
            const nameEl = document.getElementById('confirmDeleteProductName');
            const btn = document.getElementById('confirmDeleteProductBtn');

            const product = Array.isArray(products) ? products.find(p => p.id === id) : null;
            nameEl.textContent = product?.name ? `"${product.name}"` : 'este producto';

            if (btn) {
                btn.disabled = false;
                btn.textContent = 'Eliminar';
            }

            modal.classList.add('show');
        }

        function closeConfirmDeleteProductModal() {
            const modal = document.getElementById('confirmDeleteProductModal');
            modal.classList.remove('show');
            pendingDeleteProductId = null;
        }

        function confirmDeleteProduct() {
            if (!pendingDeleteProductId) return;

            const btn = document.getElementById('confirmDeleteProductBtn');
            if (btn) {
                btn.disabled = true;
                btn.textContent = 'Eliminando...';
            }

            fetch(`/api/products/${pendingDeleteProductId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                }
            })
                .then(response => {
                    if (!response.ok) throw new Error('No se pudo eliminar el producto');
                    return response.json();
                })
                .then(() => {
                    showDanger('✓ Producto eliminado correctamente');
                    closeConfirmDeleteProductModal();
                    loadProducts();
                    loadLowStockProducts();
                })
                .catch(error => {
                    if (btn) {
                        btn.disabled = false;
                        btn.textContent = 'Eliminar';
                    }
                    showError('Error: ' + error.message);
                });
        }

        // Load Invoices
        function loadInvoices() {
            fetch('/api/invoices')
                .then(response => response.json())
                .then(data => {
                    allInvoices = data;
                    invoicesCurrentPage = 1;
                    displayInvoicesPage(1, data);
                })
                .catch(error => showError('Error al cargar facturas: ' + error));
        }

        // Display Invoices Page
        function displayInvoicesPage(page, invoicesToDisplay) {
            const tbody = document.getElementById('invoicesTable');
            const invoices = invoicesToDisplay || allInvoices;

            if (invoices.length === 0) {
                tbody.innerHTML = '<tr><td colspan="7" class="text-center"><div class="empty-state"><h3>Facturas</h3><p>Comienza creando una nueva factura</p></div></td></tr>';
                document.getElementById('invoicesPagination').innerHTML = '';
                return;
            }

            const start = (page - 1) * itemsPerPageInvoices;
            const end = start + itemsPerPageInvoices;
            const pageInvoices = invoices.slice(start, end);

            tbody.innerHTML = pageInvoices.map(invoice => `
                <tr>
                    <td><strong>${invoice.invoice_number}</strong></td>
                    <td>${invoice.created_at ? new Date(invoice.created_at).toLocaleDateString('es-ES', {year: 'numeric', month: '2-digit', day: '2-digit', hour: '2-digit', minute: '2-digit'}) : '—'}</td>
                    <td>${invoice.updated_at ? new Date(invoice.updated_at).toLocaleDateString('es-ES', {year: 'numeric', month: '2-digit', day: '2-digit', hour: '2-digit', minute: '2-digit'}) : '—'}</td>
                    <td><strong>$${parseFloat(invoice.total).toFixed(2)}</strong></td>
                    <td>${invoice.items ? invoice.items.length : 0}</td>
                    <td><span class="badge badge-success">${invoice.status}</span></td>
                    <td>
                        <div class="action-buttons">
                            <button class="btn btn-info" onclick="viewInvoice(${invoice.id})">Ver</button>
                            <button class="btn btn-secondary" onclick="openEditInvoiceModal(${invoice.id})">Editar</button>
                            <button class="btn btn-danger" onclick="deleteInvoice(${invoice.id})">Eliminar</button>
                        </div>
                    </td>
                </tr>
            `).join('');

            // Generar paginación
            generateInvoicesPagination(invoices);
        }

        // Generate Invoices Pagination
        function generateInvoicesPagination(invoices) {
            const paginationContainer = document.getElementById('invoicesPagination');
            paginationContainer.innerHTML = '';

            const totalPages = Math.ceil(invoices.length / itemsPerPageInvoices);

            if (totalPages <= 1) return;

            // Botón anterior
            const prevBtn = document.createElement('button');
            prevBtn.className = 'pagination-btn';
            prevBtn.textContent = '< Anterior';
            prevBtn.disabled = invoicesCurrentPage === 1;
            prevBtn.onclick = () => {
                if (invoicesCurrentPage > 1) {
                    invoicesCurrentPage--;
                    displayInvoicesPage(invoicesCurrentPage, invoices);
                }
            };
            paginationContainer.appendChild(prevBtn);

            // Números de página
            for (let i = 1; i <= totalPages; i++) {
                const btn = document.createElement('button');
                btn.className = 'pagination-btn' + (i === invoicesCurrentPage ? ' active' : '');
                btn.textContent = i;
                btn.onclick = () => {
                    invoicesCurrentPage = i;
                    displayInvoicesPage(i, invoices);
                };
                paginationContainer.appendChild(btn);
            }

            // Botón siguiente
            const nextBtn = document.createElement('button');
            nextBtn.className = 'pagination-btn';
            nextBtn.textContent = 'Siguiente >';
            nextBtn.disabled = invoicesCurrentPage === totalPages;
            nextBtn.onclick = () => {
                if (invoicesCurrentPage < totalPages) {
                    invoicesCurrentPage++;
                    displayInvoicesPage(invoicesCurrentPage, invoices);
                }
            };
            paginationContainer.appendChild(nextBtn);
        }

        // Render Invoices Table (legacy)
        function renderInvoices(invoices) {
            invoicesCurrentPage = 1;
            displayInvoicesPage(1, invoices);
        }

        // Filter Invoices
        function filterInvoices() {
            const searchTerm = document.getElementById('invoiceSearch').value.toLowerCase();
            const filtered = allInvoices.filter(inv => 
                inv.invoice_number.toLowerCase().includes(searchTerm)
            );
            invoicesCurrentPage = 1;
            displayInvoicesPage(1, filtered);
        }

        // Open Create Invoice Modal
        function openCreateInvoiceModal() {
            if (products.length === 0) {
                showError('⚠️ Debe agregar productos primero');
                return;
            }
            invoiceItemCount = 0;
            document.getElementById('invoiceForm').reset();
            document.getElementById('invoiceItemsContainer').innerHTML = '';
            addInvoiceItem();
            document.getElementById('invoiceModal').classList.add('show');
        }

        // Close Invoice Modal
        function closeInvoiceModal() {
            document.getElementById('invoiceModal').classList.remove('show');
        }

        // Open Edit Invoice Modal
        function openEditInvoiceModal(id) {
            if (products.length === 0) {
                showError('⚠️ Debe agregar productos primero');
                return;
            }

            editingInvoiceId = id;
            editInvoiceItemCount = 0;
            const container = document.getElementById('invoiceEditItemsContainer');
            container.innerHTML = '';
            document.getElementById('invoiceEditTotal').textContent = '0.00';

            fetch(`/api/invoices/${id}`)
                .then(r => {
                    if (!r.ok) throw new Error('No se pudo cargar la factura');
                    return r.json();
                })
                .then(invoice => {
                    const items = invoice.items || [];
                    if (!items.length) {
                        addEditInvoiceItem();
                    } else {
                        items.forEach(it => addEditInvoiceItem({ product_id: it.product_id, quantity: it.quantity, price: it.price }));
                    }
                    updateEditInvoiceTotal();
                    document.getElementById('invoiceEditModal').classList.add('show');
                })
                .catch(err => showError('Error al cargar factura: ' + err.message));
        }

        // Close Edit Invoice Modal
        function closeEditInvoiceModal() {
            document.getElementById('invoiceEditModal').classList.remove('show');
            editingInvoiceId = null;
        }

        // Add Edit Invoice Item
        function addEditInvoiceItem(prefill = null) {
            editInvoiceItemCount++;
            const itemHtml = `
                <div class="invoice-item-row invoice-edit-item-row" id="edit-item-${editInvoiceItemCount}">
                    <select class="product-select edit-product-select" onchange="updateEditInvoiceTotal()">
                        <option value="">Seleccionar producto...</option>
                        ${products.map(p => `<option value="${p.id}" data-price="${p.price}" data-stock="${p.stock}">${p.name} (Stock: ${p.stock}) - $${p.price}</option>`).join('')}
                    </select>
                    <input type="number" class="quantity-input edit-quantity-input" value="1" min="1" onchange="updateEditInvoiceTotal()" placeholder="Cantidad">
                    <input type="number" class="price-input edit-price-input" value="0" min="0" step="0.01" placeholder="Precio" onchange="updateEditInvoiceTotal()" readonly style="background: #f5f5f5;">
                    <div class="text-right subtotal edit-subtotal">$0.00</div>
                    <button type="button" class="btn btn-danger" onclick="removeEditInvoiceItem(${editInvoiceItemCount})">✕</button>
                </div>
            `;
            document.getElementById('invoiceEditItemsContainer').insertAdjacentHTML('beforeend', itemHtml);

            if (prefill) {
                const row = document.getElementById(`edit-item-${editInvoiceItemCount}`);
                const select = row.querySelector('.edit-product-select');
                const qty = row.querySelector('.edit-quantity-input');
                const priceInput = row.querySelector('.edit-price-input');

                if (prefill.product_id) select.value = String(prefill.product_id);
                if (prefill.quantity) qty.value = String(prefill.quantity);
                if (prefill.price) priceInput.value = String(prefill.price);
            }
        }

        // Update Edit Invoice Total
        function updateEditInvoiceTotal() {
            let total = 0;
            document.querySelectorAll('.invoice-edit-item-row').forEach(row => {
                const select = row.querySelector('.edit-product-select');
                const quantity = parseFloat(row.querySelector('.edit-quantity-input').value) || 0;
                const priceInput = row.querySelector('.edit-price-input');

                let price = parseFloat(priceInput.value) || 0;
                if (select.value) {
                    const selectedOption = select.querySelector(`option[value="${select.value}"]`);
                    const optionPrice = parseFloat(selectedOption?.dataset?.price) || 0;
                    if (!price || price !== optionPrice) {
                        price = optionPrice;
                        priceInput.value = optionPrice;
                    }
                }

                const subtotal = quantity * price;
                row.querySelector('.edit-subtotal').textContent = '$' + subtotal.toFixed(2);
                total += subtotal;
            });
            document.getElementById('invoiceEditTotal').textContent = total.toFixed(2);
        }

        // Remove Edit Invoice Item
        function removeEditInvoiceItem(itemCount) {
            const row = document.getElementById(`edit-item-${itemCount}`);
            if (row) row.remove();
            updateEditInvoiceTotal();
        }

        // Save Invoice Edits
        document.getElementById('invoiceEditForm').addEventListener('submit', function(e) {
            e.preventDefault();

            if (!editingInvoiceId) {
                showError('No hay factura seleccionada para editar');
                return;
            }

            const rows = Array.from(document.querySelectorAll('.invoice-edit-item-row'));
            if (!rows.length) {
                showError('Debe agregar al menos un producto');
                return;
            }

            const items = [];
            const used = new Set();
            for (const row of rows) {
                const productId = row.querySelector('.edit-product-select').value;
                const quantity = parseInt(row.querySelector('.edit-quantity-input').value, 10);

                if (!productId) {
                    showError('Seleccione un producto en todos los items');
                    return;
                }
                if (!quantity || quantity < 1) {
                    showError('La cantidad debe ser mayor a 0');
                    return;
                }
                if (used.has(productId)) {
                    showError('No se permiten productos duplicados');
                    return;
                }
                used.add(productId);

                items.push({ product_id: parseInt(productId, 10), quantity });
            }

            fetch(`/api/invoices/${editingInvoiceId}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                },
                body: JSON.stringify({ items })
            })
            .then(async response => {
                const data = await response.json().catch(() => ({}));
                if (!response.ok) {
                    throw new Error(data.message || 'No se pudo actualizar la factura');
                }
                return data;
            })
            .then(data => {
                showSuccess('✓ Factura actualizada correctamente');
                closeEditInvoiceModal();
                loadInvoices();
                loadProducts();
                if (typeof loadLowStockProducts === 'function') loadLowStockProducts();

                const closeDateFilter = document.getElementById('closeDateFilter');
                if (closeDateFilter && closeDateFilter.value) {
                    loadDailyCloseDashboard();
                }

                if (currentInvoiceDetails && currentInvoiceDetails.id === editingInvoiceId) {
                    viewInvoice(editingInvoiceId);
                }
            })
            .catch(error => showError('Error: ' + error.message));
        });

        // Add Invoice Item
        function addInvoiceItem() {
            invoiceItemCount++;
            const itemHtml = `
                <div class="invoice-item-row" id="item-${invoiceItemCount}">
                    <select class="product-select" onchange="updateInvoiceTotal()">
                        <option value="">Seleccionar producto...</option>
                        ${products.map(p => `<option value="${p.id}" data-price="${p.price}" data-stock="${p.stock}">${p.name} (Stock: ${p.stock}) - $${p.price}</option>`).join('')}
                    </select>
                    <input type="number" class="quantity-input" value="1" min="1" onchange="updateInvoiceTotal()" placeholder="Cantidad">
                    <input type="number" class="price-input" value="0" min="0" step="0.01" placeholder="Precio" onchange="updateInvoiceTotal()" readonly style="background: #f5f5f5;">
                    <div class="text-right subtotal">$0.00</div>
                    <button type="button" class="btn btn-danger" onclick="removeInvoiceItem(${invoiceItemCount})">✕</button>
                </div>
            `;
            document.getElementById('invoiceItemsContainer').insertAdjacentHTML('beforeend', itemHtml);
        }

        // Update Invoice Total
        function updateInvoiceTotal() {
            let total = 0;
            document.querySelectorAll('.invoice-item-row').forEach(row => {
                const select = row.querySelector('.product-select');
                const quantity = parseFloat(row.querySelector('.quantity-input').value) || 0;
                const priceInput = row.querySelector('.price-input');
                let price = parseFloat(priceInput.value) || 0;

                if (price === 0 && select.value) {
                    const selectedOption = select.querySelector(`option[value="${select.value}"]`);
                    price = parseFloat(selectedOption.dataset.price) || 0;
                    priceInput.value = price;
                }

                const subtotal = quantity * price;
                row.querySelector('.subtotal').textContent = '$' + subtotal.toFixed(2);
                total += subtotal;
            });
            document.getElementById('invoiceTotal').textContent = total.toFixed(2);
        }

        // Remove Invoice Item
        function removeInvoiceItem(itemCount) {
            document.getElementById(`item-${itemCount}`).remove();
            updateInvoiceTotal();
        }

        // Save Invoice
        document.getElementById('invoiceForm').addEventListener('submit', function(e) {
            e.preventDefault();

            const items = [];
            document.querySelectorAll('.invoice-item-row').forEach(row => {
                const productId = row.querySelector('.product-select').value;
                const quantity = parseInt(row.querySelector('.quantity-input').value);
                const price = parseFloat(row.querySelector('.price-input').value) || 0;
                const stock = parseInt(row.querySelector('.product-select').selectedOptions[0]?.dataset.stock || 0);

                if (productId && quantity > 0 && price > 0) {
                    if (quantity > stock) {
                        showError(`Stock insuficiente para ${row.querySelector('.product-select').selectedOptions[0].text.split('(')[0]}`);
                        return;
                    }
                    items.push({
                        product_id: productId,
                        quantity: quantity,
                        price: price
                    });
                }
            });

            if (items.length === 0) {
                showError('⚠️ Debe agregar al menos un producto a la factura');
                return;
            }

            const data = {
                items: items,
                notes: document.getElementById('invoiceNotes').value
            };

            fetch('/api/invoices', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                },
                body: JSON.stringify(data)
            })
            .then(response => {
                if (!response.ok) throw new Error('Error en la respuesta');
                return response.json();
            })
            .then(data => {
                showSuccess('✓ Factura creada exitosamente: ' + data.invoice_number);
                closeInvoiceModal();
                loadInvoices();
                loadProducts(); // Actualizar stock
                loadLowStockProducts();
            })
            .catch(error => showError('Error: ' + error.message));
        });

        // View Invoice
        function viewInvoice(id) {
            fetch(`/api/invoices/${id}`)
                .then(response => response.json())
                .then(invoice => {
                    currentInvoiceDetails = invoice;
                    displayInvoiceDetails(invoice);
                })
                .catch(error => showError('Error al cargar factura: ' + error));
        }

        // Display Invoice Details
        function displayInvoiceDetails(invoice) {
            const content = document.getElementById('invoiceDetailsContent');
            const itemsHtml = invoice.items.map(item => `
                <tr>
                    <td>${item.product.name}</td>
                    <td>${item.quantity}</td>
                    <td>$${parseFloat(item.price).toFixed(2)}</td>
                    <td>$${parseFloat(item.subtotal).toFixed(2)}</td>
                </tr>
            `).join('');

            content.innerHTML = `
                <div class="invoice-details">
                    <h3>Información de la Factura</h3>
                    <div class="invoice-details-table" style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                        <div>
                            <strong>Número:</strong> ${invoice.invoice_number}<br>
                            <strong>Fecha:</strong> ${(invoice.created_at ? new Date(invoice.created_at) : new Date(invoice.invoice_date)).toLocaleDateString('es-ES', {year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit'})}
                        </div>
                        <div>
                            <strong>Estado:</strong> <span class="badge badge-success">${invoice.status}</span><br>
                            <strong>Notas:</strong> ${invoice.notes || 'Sin notas'}
                        </div>
                    </div>

                    <h3>Detalles de Productos</h3>
                    <table class="invoice-details-table">
                        <thead>
                            <tr>
                                <th>Producto</th>
                                <th>Cantidad</th>
                                <th>Precio Unit.</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${itemsHtml}
                        </tbody>
                    </table>

                    <div class="invoice-summary">
                        <div class="summary-row total">
                            <span>Total a Pagar:</span>
                            <span>$${parseFloat(invoice.total).toFixed(2)}</span>
                        </div>
                    </div>
                </div>
            `;

            document.getElementById('invoiceNumberDisplay').textContent = `Factura: ${invoice.invoice_number}`;
            document.getElementById('invoiceDetailsModal').classList.add('show');
        }

        // Close Invoice Details Modal
        function closeInvoiceDetailsModal() {
            document.getElementById('invoiceDetailsModal').classList.remove('show');
        }

        // Print Invoice
        function printInvoice() {
            if (!currentInvoiceDetails) return;

            if (!currentInvoiceDetails.id) {
                showError('No se encontró el ID de la factura para imprimir.');
                return;
            }

            const pdfUrl = `/invoices/${currentInvoiceDetails.id}/ticket?paper=a6&_=${Date.now()}`;

            // Print without opening a new tab/window: load the PDF in a hidden iframe and call print().
            const iframe = document.createElement('iframe');
            iframe.setAttribute('aria-hidden', 'true');
            iframe.style.position = 'fixed';
            iframe.style.right = '0';
            iframe.style.bottom = '0';
            iframe.style.width = '0';
            iframe.style.height = '0';
            iframe.style.border = '0';
            iframe.src = pdfUrl;

            const cleanup = () => {
                try { iframe.remove(); } catch (e) { /* ignore */ }
                window.removeEventListener('afterprint', cleanup);
            };

            window.addEventListener('afterprint', cleanup);
            document.body.appendChild(iframe);

            iframe.onload = () => {
                setTimeout(() => {
                    try {
                        iframe.contentWindow.focus();
                        iframe.contentWindow.print();
                    } catch (e) {
                        // Fallback: if the browser blocks printing from iframe, open in same tab.
                        window.location.href = pdfUrl;
                    }
                }, 200);
            };

            // Fallback cleanup in case afterprint doesn't fire.
            setTimeout(cleanup, 60_000);
        }

        // Delete Invoice
        function deleteInvoice(id) {
            openConfirmDeleteInvoiceModal(id);
        }

        function openConfirmDeleteInvoiceModal(id) {
            pendingDeleteInvoiceId = id;

            const modal = document.getElementById('confirmDeleteInvoiceModal');
            const numberEl = document.getElementById('confirmDeleteInvoiceNumber');
            const btn = document.getElementById('confirmDeleteInvoiceBtn');

            const invoice = Array.isArray(allInvoices) ? allInvoices.find(inv => inv.id === id) : null;
            numberEl.textContent = invoice?.invoice_number ? `la factura "${invoice.invoice_number}"` : 'esta factura';

            if (btn) {
                btn.disabled = false;
                btn.textContent = 'Eliminar';
            }

            modal.classList.add('show');
        }

        function closeConfirmDeleteInvoiceModal() {
            const modal = document.getElementById('confirmDeleteInvoiceModal');
            modal.classList.remove('show');
            pendingDeleteInvoiceId = null;
        }

        function confirmDeleteInvoice() {
            if (!pendingDeleteInvoiceId) return;

            const btn = document.getElementById('confirmDeleteInvoiceBtn');
            if (btn) {
                btn.disabled = true;
                btn.textContent = 'Eliminando...';
            }

            fetch(`/api/invoices/${pendingDeleteInvoiceId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                }
            })
                .then(response => {
                    if (!response.ok) throw new Error('No se pudo eliminar la factura');
                    return response.json();
                })
                .then(() => {
                    showDanger('✓ Factura eliminada correctamente y stock restaurado');
                    closeConfirmDeleteInvoiceModal();
                    loadInvoices();
                    loadProducts();
                    loadLowStockProducts();
                })
                .catch(error => {
                    if (btn) {
                        btn.disabled = false;
                        btn.textContent = 'Eliminar';
                    }
                    showError('Error: ' + error.message);
                });
        }

        // Show Success Message
        function showSuccess(message) {
            const alert = document.getElementById('successAlert');
            const errorAlert = document.getElementById('errorAlert');

            errorAlert.classList.remove('show');
            alert.textContent = message;
            alert.classList.add('show');
            setTimeout(() => alert.classList.remove('show'), 3000);
        }

        // Show Danger Message (for deletes)
        function showDanger(message) {
            const alert = document.getElementById('errorAlert');
            const successAlert = document.getElementById('successAlert');

            successAlert.classList.remove('show');
            alert.textContent = message;
            alert.classList.add('show');
            setTimeout(() => alert.classList.remove('show'), 3500);
        }

        // Show Error Message
        function showError(message) {
            const alert = document.getElementById('errorAlert');
            const successAlert = document.getElementById('successAlert');

            successAlert.classList.remove('show');
            alert.textContent = message;
            alert.classList.add('show');
            setTimeout(() => alert.classList.remove('show'), 4000);
        }

        // Close modals when clicking outside
        window.onclick = function(event) {
            const productModal = document.getElementById('productModal');
            const invoiceModal = document.getElementById('invoiceModal');
            const detailsModal = document.getElementById('invoiceDetailsModal');
            const confirmDeleteProductModal = document.getElementById('confirmDeleteProductModal');
            const confirmDeleteInvoiceModal = document.getElementById('confirmDeleteInvoiceModal');
            
            if (event.target === productModal) {
                productModal.classList.remove('show');
            }
            if (event.target === invoiceModal) {
                invoiceModal.classList.remove('show');
            }
            if (event.target === detailsModal) {
                detailsModal.classList.remove('show');
            }
            if (event.target === confirmDeleteProductModal) {
                closeConfirmDeleteProductModal();
            }
            if (event.target === confirmDeleteInvoiceModal) {
                closeConfirmDeleteInvoiceModal();
            }
        }

        function switchTab(tabName, tabButton) {
            // Hide all sections
            document.querySelectorAll('.section').forEach(section => {
                section.classList.remove('active');
            });

            // Remove active class from all tabs
            document.querySelectorAll('.nav-tab').forEach(tab => {
                tab.classList.remove('active');
            });

            // Show selected section
            const section = document.getElementById(tabName);
            if (section) {
                section.classList.add('active');
            }

            // Mark clicked tab as active (avoid relying on global `event`)
            if (tabButton) {
                tabButton.classList.add('active');
            }

            // Load data based on tab
            if (tabName === 'inventory') {
                loadProducts();
            } else if (tabName === 'profits') {
                loadProfits();
            } else if (tabName === 'invoices') {
                loadInvoices();
            } else if (tabName === 'daily-close') {
                initializeDailyClose();
            } else if (tabName === 'statistics') {
                loadStatistics();
            }
        }

        function loadStatistics() {
            loadTopProducts();
            loadTopDays();
        }

        function loadTopProducts() {
            const tbody = document.getElementById('topProductsTable');
            if (!tbody) return;

            tbody.innerHTML = '<tr><td colspan="3" class="text-center">Cargando...</td></tr>';

            fetch('/api/statistics/top-products?limit=5')
                .then(r => {
                    if (!r.ok) throw new Error('No se pudo cargar el top de productos');
                    return r.json();
                })
                .then(rows => {
                    if (!rows || rows.length === 0) {
                        tbody.innerHTML = '<tr><td colspan="3" class="text-center">Aún no hay ventas registradas</td></tr>';
                        return;
                    }

                    tbody.innerHTML = rows.map(row => {
                        return `
                            <tr>
                                <td><strong>${row.product_name}</strong></td>
                                <td>${row.total_quantity}</td>
                                <td><strong>$${parseFloat(row.total_sales).toFixed(2)}</strong></td>
                            </tr>
                        `;
                    }).join('');
                })
                .catch(err => {
                    tbody.innerHTML = '<tr><td colspan="3" class="text-center">Error al cargar</td></tr>';
                    showError('Error al cargar estadísticas: ' + err.message);
                });
        }

        function loadTopDays() {
            const tbody = document.getElementById('topDaysTable');
            if (!tbody) return;

            tbody.innerHTML = '<tr><td colspan="4" class="text-center">Cargando...</td></tr>';

            fetch('/api/statistics/top-days?limit=4&topProducts=3')
                .then(r => {
                    if (!r.ok) throw new Error('No se pudo cargar el top de días');
                    return r.json();
                })
                .then(rows => {
                    if (!rows || rows.length === 0) {
                        tbody.innerHTML = '<tr><td colspan="4" class="text-center">Aún no hay ventas registradas</td></tr>';
                        return;
                    }

                    tbody.innerHTML = rows.map(day => {
                        const topProducts = (day.top_products || [])
                            .map(p => `${p.product_name} - ${p.quantity}`)
                            .join('<br>') || '-';

                        return `
                            <tr>
                                <td><strong>${day.date}</strong></td>
                                <td>${day.total_quantity}</td>
                                <td>${topProducts}</td>
                                <td><strong>$${parseFloat(day.total_sales).toFixed(2)}</strong></td>
                            </tr>
                        `;
                    }).join('');
                })
                .catch(err => {
                    tbody.innerHTML = '<tr><td colspan="4" class="text-center">Error al cargar</td></tr>';
                    showError('Error al cargar estadísticas: ' + err.message);
                });
        }

        // Load Low Stock Products
        function loadLowStockProducts() {
            fetch('/api/products')
                .then(response => response.json())
                .then(data => {
                    lowStockProducts = data.filter(p => p.stock <= 5).sort((a, b) => b.stock - a.stock);
                    lowStockCurrentPage = 1;
                    displayLowStockPage(1);
                })
                .catch(error => showError('Error al cargar productos con bajo stock: ' + error));
        }

        // Display Low Stock Products Page
        function displayLowStockPage(page) {
            const tbody = document.getElementById('lowStockTable');
            
            if (lowStockProducts.length === 0) {
                tbody.innerHTML = '<tr><td colspan="2" class="text-center">No hay productos con bajo stock</td></tr>';
                document.getElementById('lowStockPagination').innerHTML = '';
                return;
            }

            const start = (page - 1) * itemsPerPageLowStock;
            const end = start + itemsPerPageLowStock;
            const pageData = lowStockProducts.slice(start, end);

            tbody.innerHTML = pageData.map(product => `
                <tr>
                    <td><strong>${product.name}</strong></td>
                    <td><span class="badge badge-warning">${product.stock}</span></td>
                </tr>
            `).join('');

            // Generate pagination buttons
            const totalPages = Math.ceil(lowStockProducts.length / itemsPerPageLowStock);
            const paginationContainer = document.getElementById('lowStockPagination');
            paginationContainer.innerHTML = '';

            for (let i = 1; i <= totalPages; i++) {
                const btn = document.createElement('button');
                btn.className = 'pagination-btn ' + (i === page ? 'active' : '');
                btn.textContent = i;
                btn.onclick = () => displayLowStockPage(i);
                paginationContainer.appendChild(btn);
            }
        }

        // Load Profits
        function loadProfits() {
            fetch('/api/products')
                .then(response => response.json())
                .then(data => {
                    profitsData = data;
                    profitsCurrentPage = 1;
                    displayProfitsPage(1);
                })
                .catch(error => showError('Error al cargar ganancias: ' + error));
        }

        // Display Profits Page
        function displayProfitsPage(page) {
            const tbody = document.getElementById('profitsTable');
            
            if (profitsData.length === 0) {
                tbody.innerHTML = '<tr><td colspan="6" class="text-center">No hay productos</td></tr>';
                document.getElementById('profitsPagination').innerHTML = '';
                return;
            }

            const start = (page - 1) * itemsPerPageProfits;
            const end = start + itemsPerPageProfits;
            const pageData = profitsData.slice(start, end);

            tbody.innerHTML = pageData.map(product => {
                const cost = product.cost || {};
                const salePrice = product.price;
                const purchasePrice = cost.purchase_price || 0;
                const profit = salePrice - purchasePrice;
                const margin = salePrice > 0 ? ((profit / salePrice) * 100).toFixed(1) : 0;

                return `
                    <tr>
                        <td><strong>${product.name}</strong></td>
                        <td>$${parseFloat(salePrice).toFixed(2)}</td>
                        <td>$${parseFloat(purchasePrice).toFixed(2)}</td>
                        <td><span class="badge badge-success">$${parseFloat(profit).toFixed(2)}</span></td>
                        <td>${margin}%</td>
                        <td>
                            <div class="action-buttons">
                                <button class="btn btn-secondary" onclick="editProfit(${product.id}, '${product.name}', ${salePrice}, ${purchasePrice}, ${profit})">Editar</button>
                            </div>
                        </td>
                    </tr>
                `;
            }).join('');

            // Generate pagination buttons
            const totalPages = Math.ceil(profitsData.length / itemsPerPageProfits);
            const paginationContainer = document.getElementById('profitsPagination');
            paginationContainer.innerHTML = '';

            for (let i = 1; i <= totalPages; i++) {
                const btn = document.createElement('button');
                btn.className = 'pagination-btn ' + (i === page ? 'active' : '');
                btn.textContent = i;
                btn.onclick = () => displayProfitsPage(i);
                paginationContainer.appendChild(btn);
            }
        }

        // Filter Profits
        function filterProfits() {
            const searchTerm = document.getElementById('profitSearch').value.toLowerCase();
            const filtered = profitsData.filter(p => 
                p.name.toLowerCase().includes(searchTerm)
            );
            const tbody = document.getElementById('profitsTable');
            if (filtered.length === 0) {
                tbody.innerHTML = '<tr><td colspan="6" class="text-center">No se encontraron productos</td></tr>';
                return;
            }

            tbody.innerHTML = filtered.map(product => {
                const cost = product.cost || {};
                const salePrice = product.price;
                const purchasePrice = cost.purchase_price || 0;
                const profit = salePrice - purchasePrice;
                const margin = salePrice > 0 ? ((profit / salePrice) * 100).toFixed(1) : 0;

                return `
                    <tr>
                        <td><strong>${product.name}</strong></td>
                        <td>$${parseFloat(salePrice).toFixed(2)}</td>
                        <td>$${parseFloat(purchasePrice).toFixed(2)}</td>
                        <td><span class="badge badge-success">$${parseFloat(profit).toFixed(2)}</span></td>
                        <td>${margin}%</td>
                        <td>
                            <div class="action-buttons">
                                <button class="btn btn-secondary" onclick="editProfit(${product.id}, '${product.name}', ${salePrice}, ${purchasePrice}, ${profit})">Editar</button>
                            </div>
                        </td>
                    </tr>
                `;
            }).join('');
        }

        // Edit Profit
        function editProfit(productId, productName, salePrice, purchasePrice, profit) {
            document.getElementById('profitProductId').value = productId;
            document.getElementById('profitProductName').value = productName;
            document.getElementById('profitSalePrice').value = salePrice;
            document.getElementById('profitPurchasePrice').value = purchasePrice;
            document.getElementById('profitGanancia').value = profit;
            document.getElementById('profitModal').classList.add('show');
        }

        // Close Profit Modal
        function closeProfitModal() {
            document.getElementById('profitModal').classList.remove('show');
        }

        // Save Profit
        document.getElementById('profitForm')?.addEventListener('submit', function(e) {
            e.preventDefault();
            const productId = document.getElementById('profitProductId').value;
            const purchasePrice = parseFloat(document.getElementById('profitPurchasePrice').value);
            const salePrice = parseFloat(document.getElementById('profitSalePrice').value);
            const profit = salePrice - purchasePrice;

            fetch(`/api/products/${productId}/cost`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                },
                body: JSON.stringify({
                    purchase_price: purchasePrice,
                    profit: profit
                })
            })
            .then(response => response.json())
            .then(data => {
                showSuccess('✓ Ganancia actualizada correctamente');
                closeProfitModal();
                loadProfits();
            })
            .catch(error => showError('Error: ' + error.message));
        });

        // Load initial data
        // ...existing code...

        document.addEventListener('DOMContentLoaded', function() {
            loadProducts();
            loadLowStockProducts();
        });
    </script>
</body>
</html>
