<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Micheladas la 66 - Inventario y Facturación</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f5f5;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            background: white;
            min-height: 100vh;
        }

        /* Header */
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .header h1 {
            font-size: 28px;
            margin-bottom: 10px;
        }

        /* Navigation Tabs */
        .nav-tabs {
            display: flex;
            border-bottom: 2px solid #e0e0e0;
            background: #f9f9f9;
            flex-wrap: wrap;
        }

        .nav-tab {
            padding: 15px 30px;
            cursor: pointer;
            border: none;
            background: none;
            font-size: 16px;
            font-weight: 500;
            color: #666;
            transition: all 0.3s ease;
            position: relative;
        }

        .nav-tab:hover {
            color: #667eea;
        }

        .nav-tab.active {
            color: #667eea;
            border-bottom: 3px solid #667eea;
            margin-bottom: -2px;
        }

        /* Content Sections */
        .content {
            padding: 30px;
        }

        .section {
            display: none;
        }

        .section.active {
            display: block;
        }

        /* Search Bar */
        .search-bar {
            margin-bottom: 20px;
            display: flex;
            gap: 10px;
        }

        .search-bar input {
            flex: 1;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }

        /* Dashboard Section */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
            transition: transform 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-card h3 {
            font-size: 14px;
            opacity: 0.9;
            margin-bottom: 10px;
        }

        .stat-card .value {
            font-size: 32px;
            font-weight: bold;
        }

        /* Tables */
        .table-container {
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            margin-top: 20px;
            max-height: 600px;
            overflow-y: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            background: #f5f5f5;
            padding: 15px;
            text-align: left;
            font-weight: 600;
            color: #333;
            border-bottom: 2px solid #e0e0e0;
            position: sticky;
            top: 0;
        }

        td {
            padding: 15px;
            border-bottom: 1px solid #e0e0e0;
        }

        tr:hover {
            background: #f9f9f9;
        }

        /* Buttons */
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background: #667eea;
            color: white;
        }

        .btn-primary:hover {
            background: #5568d3;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
        }

        .btn-secondary {
            background: #f0f0f0;
            color: #333;
        }

        .btn-secondary:hover {
            background: #e0e0e0;
        }

        .btn-danger {
            background: #ff6b6b;
            color: white;
            padding: 8px 12px;
            font-size: 12px;
        }

        .btn-danger:hover {
            background: #ee5a52;
        }

        .btn-info {
            background: #4ecdc4;
            color: white;
            padding: 8px 12px;
            font-size: 12px;
        }

        .btn-info:hover {
            background: #45b8af;
        }

        .btn-success {
            background: #51cf66;
            color: white;
        }

        .btn-success:hover {
            background: #40c057;
        }

        /* Forms */
        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
            color: #333;
        }

        input, textarea, select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
            font-family: inherit;
        }

        input:focus, textarea:focus, select:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        /* Modals */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            animation: fadeIn 0.3s ease;
        }

        .modal.show {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .modal-content {
            background-color: white;
            padding: 30px;
            border-radius: 8px;
            width: 90%;
            max-width: 500px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.3);
            animation: slideIn 0.3s ease;
            margin: auto;
        }

        .modal-content-large {
            max-width: 700px;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes slideIn {
            from {
                transform: translateY(-50px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .modal-header {
            margin-bottom: 20px;
            border-bottom: 1px solid #e0e0e0;
            padding-bottom: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-header h2 {
            margin: 0;
            font-size: 20px;
        }

        .close-btn {
            background: none;
            border: none;
            font-size: 28px;
            cursor: pointer;
            color: #999;
        }

        .close-btn:hover {
            color: #333;
        }

        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 10px;
        }

        .action-buttons button {
            padding: 8px 12px;
            font-size: 12px;
        }

        /* Alert Messages */
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
            display: none;
            animation: slideDown 0.3s ease;
        }

        .alert.show {
            display: block;
        }

        @keyframes slideDown {
            from {
                transform: translateY(-20px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        /* Badge */
        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 3px;
            font-size: 12px;
            font-weight: 600;
        }

        .badge-warning {
            background: #fff3cd;
            color: #856404;
        }

        .badge-success {
            background: #d4edda;
            color: #155724;
        }

        .badge-danger {
            background: #f8d7da;
            color: #721c24;
        }

        /* Section Headers */
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            flex-wrap: wrap;
            gap: 10px;
        }

        .section-header h2 {
            font-size: 24px;
            color: #333;
        }

        /* Invoice Items Table */
        .invoice-items-container {
            background: #f9f9f9;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .invoice-item-row {
            display: grid;
            grid-template-columns: 1fr 100px 100px 100px 100px;
            gap: 10px;
            align-items: center;
            margin-bottom: 10px;
            padding: 10px;
            background: white;
            border-radius: 4px;
            border: 1px solid #e0e0e0;
        }

        .invoice-item-row button {
            padding: 6px 10px;
            font-size: 12px;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .low-stock {
            color: #ff6b6b;
            font-weight: 600;
        }

        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #999;
        }

        .empty-state h3 {
            font-size: 18px;
            margin-bottom: 10px;
        }

        /* Invoice Details Modal */
        .invoice-details {
            margin-top: 20px;
        }

        .invoice-details h3 {
            margin-bottom: 15px;
            color: #333;
        }

        .invoice-details-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        .invoice-details-table th,
        .invoice-details-table td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        .invoice-details-table th {
            background: #f5f5f5;
            font-weight: 600;
        }

        .invoice-summary {
            background: #f5f5f5;
            padding: 15px;
            border-radius: 4px;
            margin-top: 15px;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
        }

        .summary-row.total {
            font-weight: bold;
            font-size: 16px;
            border-top: 2px solid #ddd;
            padding-top: 10px;
            margin-top: 10px;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .invoice-item-row {
                grid-template-columns: 1fr;
            }

            .nav-tab {
                padding: 12px 15px;
                font-size: 14px;
            }

            .section-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .modal-content {
                width: 95%;
                padding: 20px;
            }
        }

        .product-select {
            width: 100%;
        }

        /* Charts */
        .charts-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(500px, 1fr));
            gap: 30px;
            margin-bottom: 30px;
        }

        .chart-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .chart-card h3 {
            margin-bottom: 20px;
            color: #333;
            font-size: 16px;
        }

        .chart-canvas {
            position: relative;
            height: 300px;
        }

        /* Summary Cards */
        .stat-summary-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            color: white;
        }

        .summary-header h3 {
            color: white;
            margin: 0;
            font-size: 16px;
        }

        .summary-content {
            margin-top: 15px;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
            padding-bottom: 10px;
            border-bottom: 1px solid rgba(255,255,255,0.2);
        }

        .summary-row:last-child {
            margin-bottom: 0;
            border-bottom: none;
        }

        .summary-label {
            font-size: 14px;
            font-weight: 500;
            opacity: 0.9;
        }

        .summary-value {
            font-size: 20px;
            font-weight: bold;
            color: #fff;
        }

        /* Tables Container */
        .tables-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(500px, 1fr));
            gap: 30px;
            margin-bottom: 30px;
        }

        .data-table-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .data-table-card h3 {
            margin-bottom: 20px;
            color: #333;
            font-size: 16px;
        }

        .stats-table {
            width: 100%;
            border-collapse: collapse;
        }

        .stats-table thead {
            background-color: #f5f5f5;
        }

        .stats-table th {
            padding: 12px;
            text-align: left;
            font-weight: 600;
            color: #333;
            border-bottom: 2px solid #ddd;
        }

        .stats-table td {
            padding: 12px;
            border-bottom: 1px solid #eee;
            color: #666;
        }

        .stats-table tbody tr:hover {
            background-color: #f9f9f9;
        }

        .stats-table tbody tr:last-child td {
            border-bottom: none;
        }

        /* Pagination Styles */
        .pagination-container {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-top: 20px;
            gap: 5px;
        }

        .low-stock-pagination-container {
            margin-top: 35px;
            display: flex;
            justify-content: center;
        }

        .pagination-btn {
            padding: 8px 12px;
            border: 1px solid #ddd;
            background-color: white;
            color: #333;
            cursor: pointer;
            border-radius: 4px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .pagination-btn:hover {
            background-color: #f0f0f0;
            border-color: #667eea;
        }

        .pagination-btn.active {
            background-color: #667eea;
            color: white;
            border-color: #667eea;
        }

        .pagination-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .pagination-info {
            color: #666;
            font-size: 14px;
            margin: 0 10px;
        }
    </style>
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
                                        <div class="summary-value" id="dailyTotalSales" style="font-size: 36px; text-align: center; color: #667eea;">$0</div>
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
                                                <th>Nº Factura</th>
                                            </tr>
                                        </thead>
                                        <tbody id="dailyProductsTable">
                                            <tr>
                                                <td colspan="5" class="text-center">Selecciona una fecha para ver los datos</td>
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
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($recentInvoices as $invoice)
                                        <tr>
                                            <td>{{ $invoice->invoice_number }}</td>
                                            <td>{{ $invoice->invoice_date->format('d/m/Y H:i') }}</td>
                                            <td>${{ number_format($invoice->total, 2) }}</td>
                                            <td><span class="badge badge-success">{{ $invoice->status }}</span></td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center">No hay facturas registradas</td>
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
                                <th>Total</th>
                                <th>Items</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="invoicesTable">
                            <tr>
                                <td colspan="6" class="text-center">Cargando facturas...</td>
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
                        tbody.innerHTML = '<tr><td colspan="5" class="text-center">No hay productos vendidos en esta fecha</td></tr>';
                        document.getElementById('dailyProductsPagination').innerHTML = '';
                        return;
                    }

                    tbody.innerHTML = pageData.map(item => {
                        const dateStr = item.invoice_date || item.created_at;
                        const dt = new Date(dateStr);
                        const timeString = isNaN(dt.getTime()) ? '—' : dt.toLocaleTimeString('es-CO', { hour: '2-digit', minute: '2-digit' });
                        return `
                        <tr>
                            <td>${item.product_name || '—'}</td>
                            <td>${item.quantity}</td>
                            <td>$${parseFloat(item.price_total).toFixed(2)}</td>
                            <td>${timeString}</td>
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
                    <td>${product.stock < 10 ? `<span class="badge badge-warning">${product.stock}</span>` : product.stock}</td>
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
                    <td>${product.stock < 10 ? `<span class="badge badge-warning">${product.stock}</span>` : product.stock}</td>
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
            if (!confirm('¿Está seguro de que desea eliminar este producto?')) return;

            fetch(`/api/products/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                }
            })
            .then(response => response.json())
            .then(data => {
                showSuccess('✓ Producto eliminado correctamente');
                loadProducts();
            })
            .catch(error => showError('Error: ' + error));
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
                tbody.innerHTML = '<tr><td colspan="6" class="text-center"><div class="empty-state"><h3>Facturas</h3><p>Comienza creando una nueva factura</p></div></td></tr>';
                document.getElementById('invoicesPagination').innerHTML = '';
                return;
            }

            const start = (page - 1) * itemsPerPageInvoices;
            const end = start + itemsPerPageInvoices;
            const pageInvoices = invoices.slice(start, end);

            tbody.innerHTML = pageInvoices.map(invoice => `
                <tr>
                    <td><strong>${invoice.invoice_number}</strong></td>
                    <td>${new Date(invoice.invoice_date).toLocaleDateString('es-ES', {year: 'numeric', month: '2-digit', day: '2-digit', hour: '2-digit', minute: '2-digit'})}</td>
                    <td><strong>$${parseFloat(invoice.total).toFixed(2)}</strong></td>
                    <td>${invoice.items ? invoice.items.length : 0}</td>
                    <td><span class="badge badge-success">${invoice.status}</span></td>
                    <td>
                        <div class="action-buttons">
                            <button class="btn btn-info" onclick="viewInvoice(${invoice.id})">Ver</button>
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
                            <strong>Fecha:</strong> ${new Date(invoice.invoice_date).toLocaleDateString('es-ES', {year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit'})}
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
            
            const printContent = `
                <!DOCTYPE html>
                <html lang="es">
                <head>
                    <meta charset="UTF-8">
                    <title>Factura ${currentInvoiceDetails.invoice_number}</title>
                    <style>
                        body { font-family: Arial, sans-serif; margin: 20px; }
                        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #333; padding-bottom: 20px; }
                        h1 { margin: 0; }
                        .invoice-details { margin-bottom: 20px; }
                        .invoice-details p { margin: 5px 0; }
                        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
                        th, td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }
                        th { background: #f5f5f5; font-weight: bold; }
                        .total { font-weight: bold; font-size: 16px; text-align: right; padding-top: 20px; border-top: 2px solid #333; }
                        .footer { text-align: center; margin-top: 30px; color: #999; }
                    </style>
                </head>
                <body>
                    <div class="header">
                        <h1>FACTURA</h1>
                        <p style="margin: 10px 0 0 0; font-size: 12px; color: #666;">Micheladas la 66</p>
                    </div>
                    
                    <div class="invoice-details">
                        <p><strong>Factura:</strong> ${currentInvoiceDetails.invoice_number}</p>
                        <p><strong>Fecha:</strong> ${new Date(currentInvoiceDetails.invoice_date).toLocaleDateString('es-ES', {year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit'})}</p>
                        <p><strong>Estado:</strong> ${currentInvoiceDetails.status}</p>
                    </div>

                    <table>
                        <thead>
                            <tr>
                                <th>Producto</th>
                                <th>Cantidad</th>
                                <th>Precio Unit.</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${currentInvoiceDetails.items.map(item => `
                                <tr>
                                    <td>${item.product.name}</td>
                                    <td>${item.quantity}</td>
                                    <td>$${parseFloat(item.price).toFixed(2)}</td>
                                    <td>$${parseFloat(item.subtotal).toFixed(2)}</td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>

                    <div class="total">
                        <div>TOTAL: $${parseFloat(currentInvoiceDetails.total).toFixed(2)}</div>
                    </div>

                    ${currentInvoiceDetails.notes ? `<p><strong>Notas:</strong> ${currentInvoiceDetails.notes}</p>` : ''}

                    <div class="footer">
                        <p>Gracias por su compra</p>
                        <p style="font-size: 12px;">Impreso el ${new Date().toLocaleDateString('es-ES')}</p>
                    </div>
                </body>
                </html>
            `;

            const printWindow = window.open('', '', 'width=800,height=600');
            printWindow.document.write(printContent);
            printWindow.document.close();
            printWindow.print();
        }

        // Delete Invoice
        function deleteInvoice(id) {
            if (!confirm('¿Está seguro de que desea eliminar esta factura? Esto restaurará el stock de los productos.')) return;

            fetch(`/api/invoices/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                }
            })
            .then(response => response.json())
            .then(data => {
                showSuccess('✓ Factura eliminada correctamente y stock restaurado');
                loadInvoices();
                loadProducts(); // Actualizar stock
                loadLowStockProducts();
            })
            .catch(error => showError('Error: ' + error));
        }

        // Show Success Message
        function showSuccess(message) {
            const alert = document.getElementById('successAlert');
            alert.textContent = message;
            alert.classList.add('show');
            setTimeout(() => alert.classList.remove('show'), 3000);
        }

        // Show Error Message
        function showError(message) {
            const alert = document.getElementById('errorAlert');
            alert.textContent = message;
            alert.classList.add('show');
            setTimeout(() => alert.classList.remove('show'), 4000);
        }

        // Close modals when clicking outside
        window.onclick = function(event) {
            const productModal = document.getElementById('productModal');
            const invoiceModal = document.getElementById('invoiceModal');
            const detailsModal = document.getElementById('invoiceDetailsModal');
            
            if (event.target === productModal) {
                productModal.classList.remove('show');
            }
            if (event.target === invoiceModal) {
                invoiceModal.classList.remove('show');
            }
            if (event.target === detailsModal) {
                detailsModal.classList.remove('show');
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
            }
        }

        // Load Low Stock Products
        function loadLowStockProducts() {
            fetch('/api/products')
                .then(response => response.json())
                .then(data => {
                    lowStockProducts = data.filter(p => p.stock < 5).sort((a, b) => b.stock - a.stock);
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
