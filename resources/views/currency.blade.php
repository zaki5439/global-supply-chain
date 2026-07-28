<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Penukaran Mata Uang - Intelijen Risiko</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body { 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
        }
        
        #sidebar { 
            min-height: 100vh; 
            background: linear-gradient(180deg, #1a1d2e 0%, #16213e 100%);
            color: #fff;
            box-shadow: 4px 0 15px rgba(0,0,0,0.3);
            position: sticky;
            top: 0;
        }
        
        #sidebar .logo {
            font-size: 24px;
            font-weight: 900;
            background: linear-gradient(135deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        #sidebar .nav-link { 
            color: #adb5bd; 
            border-radius: 8px; 
            margin-bottom: 8px;
            padding: 12px 16px !important;
            transition: all 0.3s ease;
            border-left: 3px solid transparent;
            font-weight: 500;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        #sidebar .nav-link:hover, #sidebar .nav-link.active { 
            background: rgba(102, 126, 234, 0.15);
            color: #667eea;
            border-left-color: #667eea;
        }
        
        main { background: linear-gradient(135deg, #f5f7fa 0%, #e9ecef 100%); }
        
        .header-section {
            background: linear-gradient(135deg, rgba(102,126,234,0.1) 0%, rgba(118,75,162,0.1) 100%);
            border-bottom: 1px solid rgba(102,126,234,0.2);
            border-radius: 12px;
            padding: 32px;
            margin-bottom: 32px;
        }
        
        .header-section h2 {
            color: #1a1d2e;
            font-weight: 700;
            font-size: 32px;
            margin: 0 0 8px 0;
        }
        
        .header-section p {
            color: #666;
            margin: 0;
            font-size: 16px;
        }
        
        .stat-card {
            background: white;
            border-radius: 12px;
            border: none;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            padding: 24px;
            transition: all 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.12);
        }
        
        .form-label {
            font-weight: 600;
            color: #1a1d2e;
            margin-bottom: 10px;
            font-size: 14px;
        }
        
        .form-control, .form-select {
            border: 2px solid rgba(102,126,234,0.2) !important;
            border-radius: 8px;
            padding: 12px 16px !important;
            font-size: 14px;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: #667eea !important;
            box-shadow: 0 0 0 3px rgba(102,126,234,0.1) !important;
        }
        
        .exchange-card {
            background: linear-gradient(135deg, rgba(102,126,234,0.05) 0%, rgba(118,75,162,0.05) 100%);
            border: 2px solid rgba(102,126,234,0.15);
            border-radius: 12px;
            padding: 20px;
            transition: all 0.3s ease;
        }
        
        .exchange-card:hover {
            transform: translateY(-4px);
            border-color: rgba(102,126,234,0.4);
            box-shadow: 0 8px 20px rgba(102,126,234,0.1);
        }
        
        .rate-badge {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 6px 12px;
            border-radius: 8px;
            font-size: 11px;
            font-weight: 600;
            display: inline-block;
        }
        
        .currency-pair {
            font-size: 18px;
            font-weight: 700;
            color: #1a1d2e;
            margin-bottom: 8px;
        }
        
        .exchange-rate {
            font-size: 28px;
            font-weight: 700;
            color: #667eea;
            margin-bottom: 4px;
        }
        
        .rate-label {
            font-size: 12px;
            color: #888;
        }
        
        .checkbox-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(170px, 1fr));
            gap: 12px;
            max-height: 200px;
            overflow-y: auto;
            padding: 12px;
            background: white;
            border-radius: 8px;
            border: 1px solid rgba(102,126,234,0.1);
        }
        
        .form-check {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .form-check-input {
            width: 18px;
            height: 18px;
            cursor: pointer;
            border: 2px solid rgba(102,126,234,0.3);
            border-radius: 4px;
        }
        
        .form-check-input:checked {
            background-color: #667eea;
            border-color: #667eea;
        }
        
        .form-check-label {
            cursor: pointer;
            font-size: 13px;
            font-weight: 500;
            margin: 0;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .currency-code-tag {
            font-weight: 700;
            color: #1a1d2e;
        }

        .currency-name-tag {
            color: #666;
            font-size: 11px;
        }

        /* Search box */
        .currency-search-wrap {
            position: relative;
            margin-bottom: 10px;
        }

        .currency-search-wrap .bi-search {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #aaa;
            font-size: 14px;
            pointer-events: none;
        }

        #currencySearch {
            padding-left: 36px !important;
            border-radius: 8px !important;
            font-size: 13px !important;
            height: 38px;
            transition: all 0.2s ease;
        }

        #currencySearch:focus {
            border-color: #667eea !important;
            box-shadow: 0 0 0 3px rgba(102,126,234,0.12) !important;
        }

        .search-action-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
            font-size: 12px;
        }

        .search-action-row .btn-link-sm {
            background: none;
            border: none;
            padding: 0;
            font-size: 12px;
            color: #667eea;
            cursor: pointer;
            text-decoration: underline;
        }

        .search-action-row .btn-link-sm:hover {
            color: #764ba2;
        }

        #searchCount {
            color: #888;
        }

        .form-check.hidden-by-search {
            display: none;
        }
        
        .conversion-result {
            background: linear-gradient(135deg, rgba(102,126,234,0.1) 0%, rgba(118,75,162,0.1) 100%);
            border: 2px solid rgba(102,126,234,0.2);
            border-radius: 12px;
            padding: 24px;
        }
        
        .result-amount {
            font-size: 48px;
            font-weight: 700;
            color: #667eea;
            margin-bottom: 8px;
        }
        
        .result-label {
            font-size: 14px;
            color: #888;
            margin: 0;
        }
        
        .input-group-text {
            background: rgba(102,126,234,0.1);
            border: none;
            border-left: 2px solid rgba(102,126,234,0.2);
        }
        
        .empty-state {
            text-align: center;
            padding: 40px 20px;
        }
        
        .empty-state-icon {
            font-size: 64px;
            color: #ddd;
            margin-bottom: 16px;
        }
        
        @media (max-width: 768px) {
            #sidebar { width: 100% !important; }
            .header-section { padding: 16px; }
            .header-section h2 { font-size: 24px; }
            .checkbox-grid { grid-template-columns: repeat(auto-fill, minmax(100px, 1fr)); }
        }

        /* ===== Custom Dropdown (selalu terbuka ke bawah) ===== */
        .custom-select-wrap {
            position: relative;
            user-select: none;
        }

        .custom-select-btn {
            width: 100%;
            background: white;
            border: 2px solid rgba(102,126,234,0.2);
            border-radius: 8px;
            padding: 12px 42px 12px 16px;
            font-size: 15px;
            font-weight: 500;
            color: #1a1d2e;
            cursor: pointer;
            text-align: left;
            display: flex;
            align-items: center;
            justify-content: space-between;
            transition: all 0.2s ease;
            gap: 8px;
        }

        .custom-select-btn:hover,
        .custom-select-wrap.open .custom-select-btn {
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102,126,234,0.1);
        }

        .custom-select-btn .arrow {
            flex-shrink: 0;
            font-size: 12px;
            color: #888;
            transition: transform 0.2s ease;
        }

        .custom-select-wrap.open .custom-select-btn .arrow {
            transform: rotate(180deg);
        }

        .custom-select-btn .selected-label {
            flex: 1;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .custom-select-btn .selected-code {
            font-weight: 700;
            color: #667eea;
            margin-right: 6px;
        }

        /* Dropdown panel — selalu di bawah tombol */
        .custom-dropdown-panel {
            display: none;
            position: absolute;
            top: calc(100% + 6px);
            left: 0;
            right: 0;
            background: white;
            border: 2px solid rgba(102,126,234,0.25);
            border-radius: 10px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.12);
            z-index: 9999;
            overflow: hidden;
            animation: dropDown 0.15s ease;
        }

        @keyframes dropDown {
            from { opacity: 0; transform: translateY(-6px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .custom-select-wrap.open .custom-dropdown-panel {
            display: block;
        }

        .dropdown-search-box {
            padding: 10px 12px;
            border-bottom: 1px solid rgba(102,126,234,0.1);
            position: relative;
        }

        .dropdown-search-box input {
            width: 100%;
            border: 1px solid rgba(102,126,234,0.3);
            border-radius: 6px;
            padding: 7px 10px 7px 32px;
            font-size: 13px;
            outline: none;
            transition: border 0.2s;
        }

        .dropdown-search-box input:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 2px rgba(102,126,234,0.12);
        }

        .dropdown-search-box .bi-search {
            position: absolute;
            left: 22px;
            top: 50%;
            transform: translateY(-50%);
            color: #aaa;
            font-size: 13px;
            pointer-events: none;
        }

        .custom-option-list {
            max-height: 240px;
            overflow-y: auto;
            padding: 4px 0;
        }

        .custom-option {
            padding: 10px 16px;
            cursor: pointer;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: background 0.1s;
        }

        .custom-option:hover {
            background: rgba(102,126,234,0.07);
        }

        .custom-option.selected {
            background: rgba(102,126,234,0.12);
            font-weight: 600;
        }

        .custom-option .opt-code {
            font-weight: 700;
            color: #667eea;
            min-width: 38px;
        }

        .custom-option .opt-name {
            color: #444;
        }

        .custom-option.hidden-opt {
            display: none;
        }
    </style>
</head>
<body>
<div class="d-flex">
    <!-- Sidebar -->
    @if(isset($isAdminLayout) && $isAdminLayout)
        @include('admin.sidebar')
    @else
    <nav id="sidebar" class="p-4" style="width: 260px;">
        <div class="logo mb-5">
            <i class="bi bi-geo-alt"></i>
            <span>Intelijen Risiko</span>
        </div>
        <ul class="nav flex-column">
            <li class="nav-item">
                <a href="/" class="nav-link" title="Dasbor">
                    <i class="bi bi-speedometer2 me-2"></i> Dasbor
                </a>
            </li>
            <li class="nav-item">
                <a href="/country" class="nav-link" title="Intelijen Negara">
                    <i class="bi bi-globe me-2"></i> Intelijen Negara
                </a>
            </li>
            
            <li class="nav-item">
                <a href="/port" class="nav-link" title="Pemantauan Pelabuhan">
                    <i class="bi bi-geo-alt me-2"></i> Pemantauan Pelabuhan
                </a>
            </li>
            <li class="nav-item">
                <a href="/news" class="nav-link" title="Berita & Sentimen">
                    <i class="bi bi-newspaper me-2"></i> Berita & Sentimen
                </a>
            </li>
            <li class="nav-item">
                <a href="/currency" class="nav-link active" title="Penukaran Mata Uang">
                    <i class="bi bi-cash-coin me-2"></i> Penukaran Mata Uang
                </a>
            </li>
            <li class="nav-item">
                <a href="/watchlist" class="nav-link" title="Daftar Pantauan">
                    <i class="bi bi-star me-2"></i> Daftar Pantauan
                </a>
            </li>
            @if(Auth::check() && Auth::user()->role === 'admin')
            <li class="nav-item mt-2">
                <a href="/admin/dashboard" class="nav-link" title="Panel Admin" style="color: #c084fc;">
                    <i class="bi bi-shield-lock me-2"></i> Panel Admin
                </a>
            </li>
            @endif
            
            @if(Auth::check())
            <li class="nav-item mt-4 pt-3 border-top" style="border-color: rgba(255,255,255,0.1) !important;">
                <div class="px-3 mb-3 text-muted" style="font-size: 12px;">
                    Login sebagai:<br>
                    <strong class="text-white">{{ Auth::user()->name }}</strong>
                </div>
                <form action="/logout" method="POST" class="d-grid px-3">
                    @csrf
                    <button type="submit" class="btn btn-outline-danger btn-sm text-start" style="border-radius: 8px;">
                        <i class="bi bi-box-arrow-right me-2"></i> Keluar
                    </button>
                </form>
            </li>
            @endif
        </ul>
    </nav>
    @endif
    
    <!-- Main Content -->
    <main class="flex-grow-1 p-4" id="main-content">
        <!-- Header -->
        <div class="header-section">
            <h2><i class="bi bi-cash-coin me-2"></i>Nilai Tukar Mata Uang</h2>
            <p>Konversi multi-mata uang dengan nilai tukar langsung</p>
        </div>

        <!-- Currency Selection Section -->
        <div class="row g-4 mb-4">
            <div class="col-lg-4">
                <div class="stat-card">
                    <label class="form-label">Pilih Mata Uang Dasar</label>

                    <!-- Hidden native select (dipakai JS lama) -->
                    <select id="baseCurrency" style="display:none;">
                        <option value="USD">USD</option>
                        <option value="EUR">EUR</option>
                        <option value="GBP">GBP</option>
                        <option value="JPY">JPY</option>
                        <option value="CNY">CNY</option>
                        <option value="INR">INR</option>
                        <option value="SGD">SGD</option>
                        <option value="IDR">IDR</option>
                        <option value="MYR">MYR</option>
                        <option value="PHP">PHP</option>
                        <option value="THB">THB</option>
                        <option value="VND">VND</option>
                        <option value="KRW">KRW</option>
                        <option value="AUD">AUD</option>
                        <option value="NZD">NZD</option>
                        <option value="CAD">CAD</option>
                        <option value="CHF">CHF</option>
                        <option value="HKD">HKD</option>
                        <option value="AED">AED</option>
                        <option value="SAR">SAR</option>
                        <option value="RUB">RUB</option>
                        <option value="BRL">BRL</option>
                        <option value="MXN">MXN</option>
                        <option value="ZAR">ZAR</option>
                    </select>

                    <!-- Custom dropdown (selalu terbuka ke bawah) -->
                    <div class="custom-select-wrap" id="baseCurrencyDropdown">
                        <button type="button" class="custom-select-btn" id="baseCurrencyBtn"
                                onclick="toggleCustomDropdown()">
                            <span class="selected-label">
                                <span class="selected-code" translate="no">USD</span>
                                <span id="baseCurrencyLabel">Dolar AS</span>
                            </span>
                            <i class="bi bi-chevron-down arrow"></i>
                        </button>

                        <div class="custom-dropdown-panel" id="baseCurrencyPanel">
                            <div class="dropdown-search-box">
                                <i class="bi bi-search"></i>
                                <input type="text" id="baseSearch"
                                    placeholder="Cari mata uang dasar..."
                                    oninput="filterBaseOptions(this.value)"
                                    autocomplete="off">
                            </div>
                            <div class="custom-option-list" id="baseOptionList">
                                <!-- Diisi JS -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-8">
                <div class="stat-card">
                    <label class="form-label">Pilih Mata Uang Target (Centang untuk Ditampilkan)</label>

                    <div class="checkbox-grid" id="currencyCheckboxes">
                        <!-- Dibuat oleh JavaScript -->
                    </div>

                    <!-- Select All / Clear All -->
                    <div class="search-action-row" style="margin-top: 10px; margin-bottom: 8px;">
                        <span id="searchCount">Menampilkan mata uang</span>
                        <div style="display: flex; gap: 12px;">
                            <button class="btn-link-sm" onclick="selectAllVisible()">Pilih Semua</button>
                            <button class="btn-link-sm" onclick="clearAllVisible()">Hapus Semua</button>
                        </div>
                    </div>

                    <!-- Search box -->
                    <div class="currency-search-wrap">
                        <i class="bi bi-search"></i>
                        <input type="text" id="currencySearch" class="form-control"
                            placeholder="Cari mata uang... (contoh: USD, Euro, Yen)"
                            autocomplete="off">
                    </div>
                </div>
            </div>
        </div>

        <!-- Exchange Rates Grid -->
        <div class="row g-4 mb-4" id="exchangeRatesGrid">
            <div class="col-12">
                <div class="empty-state">
                    <div class="empty-state-icon"><i class="bi bi-currency-exchange"></i></div>
                    <h5 class="text-muted">Pilih mata uang target untuk melihat kurs</h5>
                    <p class="text-muted">Centang mata uang di atas untuk melihat nilai tukar</p>
                </div>
            </div>
        </div>

        <!-- Amount Converter Section -->
        <div class="mt-5">
            <h4 style="color: #1a1d2e; font-weight: 700; margin-bottom: 24px;">
                <i class="bi bi-arrow-left-right me-2" style="color: #667eea;"></i>Konversi Jumlah
            </h4>
            
            <div class="row g-4">
                <!-- From Amount -->
                <div class="col-lg-5">
                    <div class="stat-card">
                        <label class="form-label">Jumlah</label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <strong id="fromSymbol">$</strong>
                            </span>
                            <input type="number" id="convertAmount" class="form-control" value="100" placeholder="Masukkan jumlah" style="padding: 12px;">
                        </div>
                    </div>
                </div>

                <!-- To Amount -->
                <div class="col-lg-5">
                    <div class="stat-card">
                        <label class="form-label">Konversi ke</label>
                        <select id="convertTarget" class="form-select" style="font-size: 16px;">
                            <option value="EUR" translate="no">EUR &ndash; Euro</option>
                            <option value="GBP" translate="no">GBP &ndash; Pound Sterling Inggris</option>
                            <option value="JPY" translate="no">JPY &ndash; Yen Jepang</option>
                            <option value="CNY" translate="no">CNY &ndash; Yuan Tiongkok</option>
                            <option value="INR" translate="no">INR &ndash; Rupee India</option>
                            <option value="SGD" translate="no">SGD &ndash; Dolar Singapura</option>
                            <option value="IDR" translate="no">IDR &ndash; Rupiah Indonesia</option>
                            <option value="MYR" translate="no">MYR &ndash; Ringgit Malaysia</option>
                            <option value="THB" translate="no">THB &ndash; Baht Thailand</option>
                            <option value="KRW" translate="no">KRW &ndash; Won Korea Selatan</option>
                            <option value="AUD" translate="no">AUD &ndash; Dolar Australia</option>
                            <option value="CAD" translate="no">CAD &ndash; Dolar Kanada</option>
                            <option value="CHF" translate="no">CHF &ndash; Franc Swiss</option>
                            <option value="HKD" translate="no">HKD &ndash; Dolar Hong Kong</option>
                            <option value="SAR" translate="no">SAR &ndash; Riyal Arab Saudi</option>
                            <option value="AED" translate="no">AED &ndash; Dirham UEA</option>
                        </select>
                    </div>
                </div>

                <!-- Swap Button -->
                <div class="col-lg-2 d-flex align-items-end">
                    <button class="btn btn-primary w-100" onclick="swapCurrencies()" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none; height: 52px; border-radius: 8px; font-weight: 600;">
                        <i class="bi bi-arrow-left-right"></i>
                    </button>
                </div>
            </div>

            <!-- Result -->
            <div class="row g-4 mt-2">
                <div class="col-12">
                    <div class="conversion-result">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <div>
                                <p class="result-label">Hasil Konversi</p>
                                <div class="result-amount" id="convertResult" translate="no">0.00 EUR</div>
                            </div>
                            <div style="text-align: right;">
                                <p class="result-label">Nilai Tukar</p>
                                <div style="font-size: 24px; font-weight: 700; color: #667eea;" translate="no">
                                    1 <span id="fromCode">USD</span> = <span id="rateDisplay">0.88</span> <span id="toCode">EUR</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="mt-5 pt-4 border-top text-center text-muted small">
            <p><i class="bi bi-info-circle me-1"></i>Lebih dari 140 Mata Uang Didukung dengan Nilai Tukar Langsung</p>
            <p class="mb-0">Terakhir diperbarui: <strong id="lastUpdated">--</strong></p>
        </div>
    </main>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    let exchangeRates = {};
    const allCurrencies = ['AED', 'AFN', 'ALL', 'AMD', 'AOA', 'ARS', 'AUD', 'AWG', 'AZN', 'BAM', 'BBD', 'BDT', 'BGN', 'BHD', 'BIF', 'BMD', 'BRL', 'BSD', 'BTN', 'BWP', 'BYR', 'BZD', 'CAD', 'CFA', 'CHF', 'CLP', 'CNY', 'COP', 'CRC', 'CUP', 'DJF', 'DKK', 'DOP', 'DZD', 'ECS', 'EGP', 'ERN', 'ETB', 'EUR', 'GBP', 'GEL', 'GHS', 'GIP', 'GMD', 'GNF', 'GYD', 'HKD', 'HNL', 'HRK', 'HTG', 'HUF', 'IDR', 'ILS', 'INR', 'IQD', 'ISK', 'JMD', 'JOD', 'JPY', 'KES', 'KGS', 'KHR', 'KMF', 'KWD', 'KYD', 'KZT', 'LBP', 'LKR', 'LRD', 'LSL', 'LTL', 'LVL', 'MAD', 'MGF', 'MKD', 'MMR', 'MNT', 'MOP', 'MRO', 'MUR', 'MVR', 'MWK', 'MXN', 'MYR', 'MZN', 'NAD', 'NGN', 'NIO', 'NOK', 'NPR', 'NZD', 'OMR', 'PAB', 'PEN', 'PGK', 'PHP', 'PKR', 'PLN', 'PYG', 'QAR', 'QTQ', 'RON', 'RSD', 'RUB', 'RWF', 'SAR', 'SBD', 'SCR', 'SDG', 'SEK', 'SGD', 'SLL', 'SOS', 'SRD', 'SSP', 'STD', 'SVC', 'THB', 'TJS', 'TMT', 'TND', 'TOP', 'TTD', 'UAH', 'UGX', 'USD', 'UYU', 'UZS', 'VUV', 'WST', 'XAF', 'XCD', 'XOF', 'XPF', 'YER', 'ZAR', 'ZMW', 'ZWD'];

    // Nama mata uang dalam Bahasa Indonesia
    const currencyNames = {
        'USD': 'Dolar AS',
        'EUR': 'Euro',
        'GBP': 'Pound Inggris',
        'JPY': 'Yen Jepang',
        'CNY': 'Yuan Tiongkok',
        'INR': 'Rupee India',
        'SGD': 'Dolar Singapura',
        'IDR': 'Rupiah Indonesia',
        'MYR': 'Ringgit Malaysia',
        'PHP': 'Peso Filipina',
        'THB': 'Baht Thailand',
        'VND': 'Dong Vietnam',
        'KRW': 'Won Korea Selatan',
        'AUD': 'Dolar Australia',
        'NZD': 'Dolar Selandia Baru',
        'CAD': 'Dolar Kanada',
        'CHF': 'Franc Swiss',
        'HKD': 'Dolar Hong Kong',
        'AED': 'Dirham UEA',
        'SAR': 'Riyal Arab Saudi',
        'RUB': 'Rubel Rusia',
        'BRL': 'Real Brasil',
        'MXN': 'Peso Meksiko',
        'ZAR': 'Rand Afrika Selatan'
    };
    
    const currencySymbols = {
        'USD': '$', 'EUR': '€', 'GBP': '£', 'JPY': '¥', 'CNY': '¥', 'INR': '₹', 
        'SGD': '$', 'IDR': 'Rp', 'MYR': 'RM', 'PHP': '₱', 'THB': '฿', 'VND': '₫',
        'KRW': '₩', 'AUD': '$', 'NZD': '$', 'CAD': '$', 'CHF': 'CHF', 'HKD': '$',
        'AED': 'د.إ', 'SAR': 'ر.س', 'RUB': '₽', 'BRL': 'R$', 'MXN': '$', 'ZAR': 'R'
    };

    document.addEventListener('DOMContentLoaded', () => {
        initBaseDropdown();
        initializeCurrencyCheckboxes();
        document.getElementById('convertAmount').addEventListener('input', updateConversion);
        document.getElementById('convertTarget').addEventListener('change', updateConversion);
        updateExchangeRates();

        // Tutup dropdown jika klik di luar
        document.addEventListener('click', (e) => {
            const wrap = document.getElementById('baseCurrencyDropdown');
            if (wrap && !wrap.contains(e.target)) {
                wrap.classList.remove('open');
            }
        });
    });

    /* ===== Custom Dropdown Logic ===== */

    function initBaseDropdown() {
        const list = document.getElementById('baseOptionList');
        if (!list) return;

        list.innerHTML = allCurrencies.map(curr => `
            <div class="custom-option ${curr === 'USD' ? 'selected' : ''}"
                 data-value="${curr}"
                 data-name="${(currencyNames[curr] || '').toLowerCase()}"
                 onclick="selectBaseCurrency('${curr}')">
                <span class="opt-code" translate="no">${curr}</span>
                <span class="opt-name">${currencyNames[curr] || curr}</span>
            </div>
        `).join('');
    }

    function toggleCustomDropdown() {
        const wrap = document.getElementById('baseCurrencyDropdown');
        const isOpen = wrap.classList.contains('open');
        wrap.classList.toggle('open');

        if (!isOpen) {
            // Fokus ke input pencarian saat dibuka
            setTimeout(() => {
                const inp = document.getElementById('baseSearch');
                if (inp) { inp.value = ''; filterBaseOptions(''); inp.focus(); }
            }, 50);
        }
    }

    function selectBaseCurrency(code) {
        // Update hidden native select
        document.getElementById('baseCurrency').value = code;

        // Update tampilan tombol
        document.querySelector('#baseCurrencyBtn .selected-code').textContent = code;
        document.getElementById('baseCurrencyLabel').textContent = currencyNames[code] || code;

        // Highlight opsi yang dipilih
        document.querySelectorAll('#baseOptionList .custom-option').forEach(opt => {
            opt.classList.toggle('selected', opt.dataset.value === code);
        });

        // Tutup dropdown
        document.getElementById('baseCurrencyDropdown').classList.remove('open');

        // Trigger update
        initializeCurrencyCheckboxes();
        updateExchangeRates();
    }

    function filterBaseOptions(query) {
        const q = (query || '').trim().toLowerCase();
        document.querySelectorAll('#baseOptionList .custom-option').forEach(opt => {
            const code = (opt.dataset.value || '').toLowerCase();
            const name = (opt.dataset.name || '').toLowerCase();
            const match = !q || code.includes(q) || name.includes(q);
            opt.classList.toggle('hidden-opt', !match);
        });
    }


    function initializeCurrencyCheckboxes() {
        const container = document.getElementById('currencyCheckboxes');
        const baseCurrency = document.getElementById('baseCurrency').value;
        
        // Tampilkan kode + nama Bahasa Indonesia agar tidak salah diterjemahkan
        container.innerHTML = allCurrencies.map(curr => `
            <div class="form-check" data-code="${curr}" data-name="${(currencyNames[curr] || '').toLowerCase()}">
                <input type="checkbox" class="form-check-input currency-checkbox" id="curr_${curr}" value="${curr}" 
                    ${curr !== baseCurrency && curr !== 'EUR' ? 'checked' : curr === 'EUR' ? 'checked' : ''}>
                <label class="form-check-label" for="curr_${curr}">
                    <span class="currency-code-tag" translate="no">${curr}</span><span class="currency-name-tag"> &ndash; ${currencyNames[curr] || curr}</span>
                </label>
            </div>
        `).join('');

        container.querySelectorAll('.currency-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', updateExchangeRates);
        });

        // Wire up search input (only once)
        const searchInput = document.getElementById('currencySearch');
        if (searchInput && !searchInput.dataset.bound) {
            searchInput.dataset.bound = '1';
            searchInput.addEventListener('input', filterCurrencies);
        }

        updateSearchCount();
    }

    function filterCurrencies() {
        const query = document.getElementById('currencySearch').value.trim().toLowerCase();
        const items = document.querySelectorAll('#currencyCheckboxes .form-check');
        let visible = 0;

        items.forEach(item => {
            const code = (item.dataset.code || '').toLowerCase();
            const name = (item.dataset.name || '').toLowerCase();
            const matches = !query || code.includes(query) || name.includes(query);
            item.classList.toggle('hidden-by-search', !matches);
            if (matches) visible++;
        });

        updateSearchCount(visible);
    }

    function updateSearchCount(visible) {
        const total = allCurrencies.length;
        if (visible === undefined) visible = total;
        const el = document.getElementById('searchCount');
        if (el) {
            el.textContent = visible === total
                ? `Menampilkan ${total} mata uang`
                : `${visible} dari ${total} mata uang`;
        }
    }

    function selectAllVisible() {
        document.querySelectorAll('#currencyCheckboxes .form-check:not(.hidden-by-search) .currency-checkbox')
            .forEach(cb => { if (!cb.checked) { cb.checked = true; } });
        updateExchangeRates();
    }

    function clearAllVisible() {
        document.querySelectorAll('#currencyCheckboxes .form-check:not(.hidden-by-search) .currency-checkbox')
            .forEach(cb => { if (cb.checked) { cb.checked = false; } });
        updateExchangeRates();
    }

    async function updateExchangeRates() {
        const baseCurrency = document.getElementById('baseCurrency').value;
        const selectedTargets = Array.from(document.querySelectorAll('.currency-checkbox:checked'))
            .map(cb => cb.value)
            .filter(curr => curr !== baseCurrency);

        document.getElementById('fromSymbol').textContent = currencySymbols[baseCurrency] || baseCurrency;
        
        if (selectedTargets.length === 0) {
            document.getElementById('exchangeRatesGrid').innerHTML = `
                <div class="col-12">
                    <div class="empty-state">
                        <div class="empty-state-icon"><i class="bi bi-currency-exchange"></i></div>
                        <h5 class="text-muted">Pilih mata uang target untuk melihat kurs</h5>
                        <p class="text-muted">Centang mata uang di atas untuk melihat nilai tukar</p>
                    </div>
                </div>
            `;
            return;
        }

        try {
            const targetString = selectedTargets.join(',');
            const response = await fetch(`/api/exchange-rates?base=${baseCurrency}&targets=${targetString}`);
            const data = await response.json();

            if (data.status === 'success') {
                exchangeRates = data.data.rates || data.data;
                displayExchangeRates(baseCurrency, selectedTargets);
                updateConversion();
                document.getElementById('lastUpdated').textContent = new Date().toLocaleTimeString('id-ID');
            }
        } catch (error) {
            console.error('Error:', error);
        }
    }

    function displayExchangeRates(baseCurrency, targetCurrencies) {
        const container = document.getElementById('exchangeRatesGrid');
        
        const html = targetCurrencies.map(targetCurrency => {
            const rate = exchangeRates[targetCurrency] || 0;
            return `
                <div class="col-md-6 col-lg-4">
                    <div class="exchange-card">
                        <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 12px;">
                            <div>
                                <div class="currency-pair" translate="no">${baseCurrency} &rarr; ${targetCurrency}</div>
                                <div style="font-size: 12px; color: #888;">${currencyNames[baseCurrency] || baseCurrency} ke ${currencyNames[targetCurrency] || targetCurrency}</div>
                            </div>
                            <div class="rate-badge">LANGSUNG</div>
                        </div>
                        <div class="exchange-rate" translate="no">${currencySymbols[targetCurrency] || targetCurrency}${rate.toFixed(4)}</div>
                        <div class="rate-label">per 1 <span translate="no">${baseCurrency}</span></div>
                        <hr style="margin: 12px 0; opacity: 0.3;">
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; font-size: 12px;">
                            <div>
                                <p style="color: #888; margin: 0 0 4px 0;">Jenis Kurs</p>
                                <p style="color: #1a1d2e; font-weight: 600; margin: 0;">Spot</p>
                            </div>
                            <div style="text-align: right;">
                                <p style="color: #888; margin: 0 0 4px 0;">Pembaruan</p>
                                <p style="color: #1a1d2e; font-weight: 600; margin: 0;">Per Jam</p>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }).join('');

        container.innerHTML = html;
    }

    function updateConversion() {
        const baseCurrency = document.getElementById('baseCurrency').value;
        const amount = parseFloat(document.getElementById('convertAmount').value) || 0;
        const targetCurrency = document.getElementById('convertTarget').value;
        
        const rate = exchangeRates[targetCurrency] || 0;
        const result = (amount * rate).toFixed(2);

        document.getElementById('convertResult').textContent = `${result} ${targetCurrency}`;
        document.getElementById('rateDisplay').textContent = rate.toFixed(4);
        document.getElementById('fromCode').textContent = baseCurrency;
        document.getElementById('toCode').textContent = targetCurrency;
    }

    function swapCurrencies() {
        const baseCurrency = document.getElementById('baseCurrency').value;
        const targetCurrency = document.getElementById('convertTarget').value;
        
        document.getElementById('baseCurrency').value = targetCurrency;
        document.getElementById('convertTarget').value = baseCurrency;
        
        initializeCurrencyCheckboxes();
        updateExchangeRates();
    }
</script>
</body>
</html>


