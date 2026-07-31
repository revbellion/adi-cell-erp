@php
    $title = 'Cash Counter';
@endphp
@extends('layouts.app')

@section('content')
<div class="cc-layout">
    {{-- LEFT: Denomination Input --}}
    <div class="cc-denoms">
        <div class="cc-denom-card">
            <div class="cc-denom-content">
                <div id="denom-container" class="cc-denom-grid"></div>
            </div>
        </div>
    </div>

    {{-- RIGHT: Summary & Controls --}}
    <div class="cc-summary">
        {{-- Total Display --}}
        <div class="cc-total-card">
            <div class="cc-total-label">Saldo Cash Fisik</div>
            <div class="cc-total-amount" id="grand-total">Rp 0</div>
        </div>

        {{-- Account & Target --}}
        <div class="cc-control-card">
            <div class="cc-control-row">
                <div class="cc-control-icon"><i class="fas fa-wallet"></i></div>
                <div class="cc-control-content">
                    <div class="cc-control-label">Akun Kas</div>
                    <select id="account-select" class="cc-select" onchange="onAccountChange()">
                        @foreach($accounts as $account)
                        <option value="{{ $account->id }}" data-balance="{{ $balances[$account->id] ?? 0 }}" {{ $cashAccount && $account->id === $cashAccount->id ? 'selected' : '' }}>
                            {{ $account->name }} ({{ ucfirst($account->type) }})
                        </option>
                        @endforeach
                    </select>
                </div>
            </div>

            @if(!$hasCashAccounts)
            <div class="cc-alert">
                <i class="fas fa-exclamation-triangle"></i> Tidak ada akun cash aktif
            </div>
            @endif

            <div id="account-balance-info" class="cc-balance-info d-none">
                <div class="cc-balance-row">
                    <span>Saldo Sistem</span>
                    <span id="system-balance" class="fw-bold">Rp 0</span>
                </div>
                <div class="cc-balance-row">
                    <span>Uang Fisik</span>
                    <span id="physical-balance" class="fw-bold">Rp 0</span>
                </div>
                <div class="cc-balance-row cc-balance-total">
                    <span>Selisih</span>
                    <span id="diff-balance" class="fw-bold">Rp 0</span>
                </div>
            </div>

            <div class="cc-control-row mt-2">
                <div class="cc-control-icon"><i class="fas fa-play-circle"></i></div>
                <div class="cc-control-content">
                    <div class="cc-control-label">Saldo Cash Sistem</div>
                    <div class="cc-target-input">
                        <span class="cc-target-prefix">Rp</span>
                        <input type="text" id="opening-balance" class="cc-input" readonly value="0">
                    </div>
                </div>
            </div>

            <div id="reconciliation-panel" class="d-none">
                <hr class="my-2" style="border-color:rgba(255,255,255,0.08);">
                <div id="reconciliation-content"></div>
            </div>

            <div id="transactions-panel" class="d-none">
                <hr class="my-2" style="border-color:rgba(255,255,255,0.08);">
                <div id="transactions-content"></div>
            </div>
        </div>

        {{-- Action Buttons --}}
        <div class="cc-actions">
            <button class="cc-btn cc-btn-secondary" onclick="resetCalculator()">
                <i class="fas fa-undo-alt"></i> Reset
            </button>
            <button class="cc-btn cc-btn-success" onclick="openSaveModal()">
                <i class="fas fa-save"></i> Simpan
            </button>
        </div>


    </div>
</div>

{{-- Save Modal --}}
<div class="modal fade" id="saveModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content modal-modern">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" style="font-size:1rem;">Simpan Sesi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Nama / Catatan</label>
                    <input type="text" id="session-title" class="form-control" placeholder="Contoh: Kas Toko Pagi...">
                </div>
                <div class="d-flex justify-content-between mb-1" style="font-size:0.85rem;">
                    <span class="text-muted">Saldo Cash Sistem</span>
                    <strong id="modal-opening-display">Rp 0</strong>
                </div>
                <div class="cc-modal-total">
                    <span>Saldo Cash Fisik</span>
                    <strong id="modal-total-display">Rp 0</strong>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-modern btn-secondary" data-bs-dismiss="modal"><i class="fas fa-times me-1"></i>Batal</button>
                <button type="button" class="btn btn-modern btn-primary" onclick="saveSession()"><i class="fas fa-save me-1"></i>Simpan</button>
            </div>
        </div>
    </div>
</div>

<div id="toast" class="cc-toast d-none"></div>
@endsection

@push('styles')
<style>
/* Layout */
.cc-layout {
    display: grid;
    grid-template-columns: 1fr 380px;
    gap: 1rem;
    align-items: start;
}

/* Denomination Panel */
.cc-denoms { display: flex; flex-direction: column; }

.cc-denom-card {
    background: var(--bg-card);
    border: 1px solid var(--border-subtle);
    border-radius: 12px;
    overflow: hidden;
}

.cc-denom-content { padding: 1rem; }

.cc-denom-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
    gap: 0.75rem;
}

/* Denomination Card */
.cc-denom-item {
    background: var(--bg-card);
    border: 1px solid var(--border-subtle);
    border-radius: 10px;
    padding: 0.75rem;
    border-left: 4px solid var(--item-color, var(--theme-primary));
    transition: all 0.15s;
}

.cc-denom-item:hover { box-shadow: 0 2px 8px rgba(0,0,0,0.06); }

.cc-denom-label {
    font-weight: 700;
    font-size: 0.8rem;
    color: var(--text-primary);
}

.cc-denom-controls {
    display: flex;
    align-items: center;
    gap: 4px;
    margin-top: 0.5rem;
}

.cc-denom-btn {
    width: 28px;
    height: 28px;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 1px solid var(--border-subtle);
    border-radius: 6px;
    background: var(--bg-card);
    color: var(--text-primary);
    font-size: 0.7rem;
    cursor: pointer;
    transition: all 0.1s;
    padding: 0;
}

.cc-denom-btn:hover { background: var(--border-subtle); }
.cc-denom-btn.cc-minus:hover { background: rgba(239,68,68,0.1); color: #ef4444; }
.cc-denom-btn.cc-plus:hover { background: rgba(16,185,129,0.1); color: #10b981; }

.cc-denom-count {
    width: 50px;
    text-align: center;
    border: 1px solid var(--border-subtle);
    border-radius: 6px;
    background: var(--bg-card);
    color: var(--text-primary);
    font-size: 0.85rem;
    font-weight: 700;
    padding: 3px;
    user-select: none;
}

.cc-denom-shortcuts {
    display: flex;
    gap: 3px;
    margin-top: 0.35rem;
}

.cc-shortcut-btn {
    padding: 2px 6px;
    border: 1px solid var(--border-subtle);
    border-radius: 5px;
    background: transparent;
    color: var(--text-muted);
    font-size: 0.6rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.1s;
}

.cc-shortcut-btn:hover { background: var(--theme-primary); color: #fff; border-color: var(--theme-primary); }

.cc-denom-subtotal {
    font-size: 0.8rem;
    font-weight: 700;
    color: var(--text-primary);
    margin-top: 0.35rem;
    text-align: right;
}

/* Summary Panel */
.cc-summary {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
    position: sticky;
    top: 1rem;
}

/* Total Card */
.cc-total-card {
    background: linear-gradient(135deg, var(--theme-primary), color-mix(in srgb, var(--theme-primary) 80%, #000));
    border-radius: 12px;
    padding: 1.25rem;
    text-align: center;
    color: #fff;
}

.cc-total-label {
    font-size: 0.7rem;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    opacity: 0.8;
    font-weight: 600;
}

.cc-total-amount {
    font-size: 1.75rem;
    font-weight: 800;
    letter-spacing: -0.5px;
    margin: 0.25rem 0;
}

/* Control Card */
.cc-control-card {
    background: var(--bg-card);
    border: 1px solid var(--border-subtle);
    border-radius: 12px;
    padding: 0.85rem;
}

.cc-control-row {
    display: flex;
    align-items: center;
    gap: 0.6rem;
}

.cc-control-icon {
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 8px;
    background: rgba(var(--theme-primary-rgb), 0.08);
    color: var(--theme-primary);
    font-size: 0.8rem;
    flex-shrink: 0;
}

.cc-control-content { flex: 1; min-width: 0; }
.cc-control-label { font-size: 0.75rem; font-weight: 600; color: var(--text-muted); margin-bottom: 0.25rem; }

.cc-select {
    width: 100%;
    padding: 0.45rem 0.6rem;
    border: 1px solid var(--border-subtle);
    border-radius: 8px;
    font-size: 0.8rem;
    background: var(--bg-card);
    color: var(--text-primary);
    outline: none;
}

.cc-select:focus { border-color: var(--theme-primary); }

.cc-input {
    width: 100%;
    padding: 0.45rem 0.6rem;
    border: 1px solid var(--border-subtle);
    border-radius: 8px;
    font-size: 0.8rem;
    background: var(--bg-card);
    color: var(--text-primary);
    outline: none;
}

.cc-input:focus { border-color: var(--theme-primary); }

.cc-target-input {
    display: flex;
    gap: 0.35rem;
}

.cc-target-prefix {
    padding: 0.45rem 0.5rem;
    border: 1px solid var(--border-subtle);
    border-radius: 8px;
    font-size: 0.75rem;
    color: var(--text-muted);
    background: var(--bg-card);
}

.cc-target-input .cc-input { flex: 1; }

.cc-target-btn {
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 1px solid var(--border-subtle);
    border-radius: 8px;
    background: var(--bg-card);
    color: var(--text-muted);
    font-size: 0.7rem;
    cursor: pointer;
    transition: all 0.1s;
}

.cc-target-btn:hover { background: var(--theme-primary); color: #fff; border-color: var(--theme-primary); }

/* Balance Info */
.cc-balance-info {
    margin-top: 0.5rem;
    padding: 0.5rem;
    border-radius: 8px;
    background: rgba(var(--theme-primary-rgb), 0.04);
}

.cc-balance-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.2rem 0;
    font-size: 0.8rem;
    color: var(--text-muted);
}

.cc-balance-total {
    border-top: 1px solid var(--border-subtle);
    padding-top: 0.35rem;
    margin-top: 0.2rem;
    font-weight: 600;
    color: var(--text-primary);
}

/* Target Result */
.cc-target-result {
    margin-top: 0.5rem;
    padding: 0.5rem;
    border-radius: 8px;
    background: rgba(var(--theme-primary-rgb), 0.04);
}

/* Adjust Panel */
.cc-adjust-panel {
    margin-top: 0.5rem;
    padding: 0.5rem;
    border-radius: 8px;
    background: rgba(var(--theme-primary-rgb), 0.04);
}

.cc-adjust-label {
    font-size: 0.7rem;
    font-weight: 600;
    color: var(--text-muted);
    margin-bottom: 0.35rem;
    text-transform: uppercase;
    letter-spacing: 0.03em;
}

/* Buttons */
.cc-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.4rem;
    padding: 0.55rem 1rem;
    border: none;
    border-radius: 8px;
    font-size: 0.8rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.15s;
    flex: 1;
}

.cc-btn:hover { filter: brightness(1.05); transform: translateY(-1px); }
.cc-btn-primary { background: var(--theme-primary); color: #fff; }
.cc-btn-success { background: #10b981; color: #fff; }
.cc-btn-danger { background: #ef4444; color: #fff; }
.cc-btn-secondary { background: var(--border-subtle); color: var(--text-primary); }

.cc-actions {
    display: flex;
    gap: 0.5rem;
}

.cc-btn-sm {
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
    padding: 0.3rem 0.6rem;
    border: none;
    border-radius: 6px;
    font-size: 0.7rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.1s;
}

.cc-btn-danger-sm { background: rgba(239,68,68,0.1); color: #ef4444; }
.cc-btn-danger-sm:hover { background: #ef4444; color: #fff; }

.cc-alert {
    padding: 0.5rem;
    border-radius: 8px;
    background: rgba(245,158,11,0.1);
    color: #f59e0b;
    font-size: 0.75rem;
    margin-top: 0.5rem;
    display: flex;
    align-items: center;
    gap: 0.4rem;
}

/* Chart */
/* History */
/* Toast */
.cc-toast {
    position: fixed;
    bottom: 20px;
    left: 50%;
    transform: translateX(-50%);
    background: var(--bg-card);
    color: var(--text-primary);
    padding: 0.6rem 1.2rem;
    border-radius: 10px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.15);
    font-size: 0.85rem;
    font-weight: 600;
    z-index: 9999;
    border: 1px solid var(--border-subtle);
}

/* Modal Total */
.cc-modal-total {
    padding: 0.75rem;
    border-radius: 8px;
    background: var(--border-subtle);
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 0.85rem;
    color: var(--text-muted);
}

/* Responsive */
@media (max-width: 992px) {
    .cc-layout { grid-template-columns: 1fr; }
    .cc-summary { position: static; }
    .cc-denom-grid { grid-template-columns: repeat(auto-fill, minmax(140px, 1fr)); }
}

@media (max-width: 576px) {
    .cc-denom-grid { grid-template-columns: repeat(2, 1fr); }
    .cc-total-amount { font-size: 1.5rem; }
}
</style>
@endpush

@push('scripts')
<script>
const denoms = [
    { key: '100k', value: 100000, label: 'Rp 100.000', color: '#f43f5e' },
    { key: '50k',  value: 50000,  label: 'Rp 50.000',  color: '#3b82f6' },
    { key: '20k',  value: 20000,  label: 'Rp 20.000',  color: '#10b981' },
    { key: '10k',  value: 10000,  label: 'Rp 10.000',  color: '#8b5cf6' },
    { key: '5k',   value: 5000,   label: 'Rp 5.000',   color: '#f59e0b' },
    { key: '2k',   value: 2000,   label: 'Rp 2.000',   color: '#6b7280' },
    { key: '1k',   value: 1000,   label: 'Rp 1.000',   color: '#84cc16' },
    { key: '500',  value: 500,    label: 'Koin Rp 500',  color: '#a855f7' },
];

const DENOM_KEYS = denoms.map(d => d.key);


function getDenomValue(key) {
    const d = denoms.find(x => x.key === key);
    return d ? (d.value || d.key) : 0;
}

function buildCards() {
    const container = document.getElementById('denom-container');
    denoms.forEach(d => {
        const el = document.createElement('div');
        el.className = 'cc-denom-item';
        el.style.setProperty('--item-color', d.color);
        el.innerHTML = `
            <div class="cc-denom-label">${d.label}</div>
            <div class="cc-denom-controls">
                <button type="button" class="cc-denom-btn cc-minus" onclick="adjustCount('${d.key}',-1)"><i class="fas fa-minus"></i></button>
                <span id="count-${d.key}" class="cc-denom-count" data-value="0">0</span>
                <button type="button" class="cc-denom-btn cc-plus" onclick="adjustCount('${d.key}',1)"><i class="fas fa-plus"></i></button>
            </div>
            <div class="cc-denom-shortcuts">
                <button type="button" class="cc-shortcut-btn" onclick="adjustCount('${d.key}',10)">+10</button>
                <button type="button" class="cc-shortcut-btn" onclick="adjustCount('${d.key}',50)">+50</button>
                <button type="button" class="cc-shortcut-btn" onclick="adjustCount('${d.key}',100)">+100</button>
            </div>
            <div class="cc-denom-subtotal" id="subtotal-${d.key}">Rp 0</div>
        `;
        container.appendChild(el);
    });
}

function adjustCount(key, change) {
    const el = document.getElementById('count-' + key);
    let val = parseInt(el.dataset.value) || 0;
    val = Math.max(0, val + change);
    el.dataset.value = val;
    el.textContent = val;
    updateTotal();
}

function getCount(key) {
    const el = document.getElementById('count-' + key);
    return parseInt(el.dataset.value) || 0;
}

function setCount(key, val) {
    const el = document.getElementById('count-' + key);
    val = Math.max(0, val);
    el.dataset.value = val;
    el.textContent = val;
}

function formatRupiah(num) {
    return 'Rp ' + num.toLocaleString('id-ID');
}

function updateTotal() {
    let grandTotal = 0;
    DENOM_KEYS.forEach(key => {
        const count = getCount(key);
        const value = getDenomValue(key);
        const subtotal = count * value;
        document.getElementById('subtotal-' + key).textContent = formatRupiah(subtotal);
        grandTotal += subtotal;
    });

    document.getElementById('grand-total').textContent = formatRupiah(grandTotal);
    document.getElementById('modal-total-display').textContent = formatRupiah(grandTotal);

    const opening = numVal(document.getElementById('opening-balance'));
    document.getElementById('modal-opening-display').textContent = formatRupiah(opening);

    updateReconciliation(grandTotal, opening);
    updateAccountBalanceInfo(grandTotal);
}

function updateReconciliation(grandTotal, opening) {
    const panel = document.getElementById('reconciliation-panel');
    const content = document.getElementById('reconciliation-content');
    const diff = grandTotal - opening;
    const isZero = grandTotal === 0 && opening === 0;

    if (isZero) { panel.classList.add('d-none'); return; }
    panel.classList.remove('d-none');

    let badge, statusText, diffColor;
    if (diff === 0) {
        badge = 'bg-success';
        statusText = 'Seimbang';
        diffColor = 'var(--text-primary)';
    } else if (diff > 0) {
        badge = 'bg-warning text-dark';
        statusText = 'Kelebihan';
        diffColor = '#f59e0b';
    } else {
        badge = 'bg-danger';
        statusText = 'Kekurangan';
        diffColor = '#ef4444';
    }

    content.innerHTML = `
        <div class="cc-balance-row">
            <span>Saldo Cash Sistem</span>
            <span class="fw-bold">${formatRupiah(opening)}</span>
        </div>
        <div class="cc-balance-row">
            <span>Saldo Cash Fisik</span>
            <span class="fw-bold">${formatRupiah(grandTotal)}</span>
        </div>
        <div class="cc-balance-row" style="border-top:1px solid rgba(255,255,255,0.08);padding-top:6px;">
            <span>Selisih</span>
            <span class="fw-bold" style="color:${diffColor}">${diff >= 0 ? '+' : ''}${formatRupiah(diff)}</span>
        </div>
        <div class="mt-2">
            <span class="badge rounded-pill ${badge}" style="font-size:0.75rem;">${statusText}</span>
        </div>
        <button class="cc-btn cc-btn-secondary w-100 mt-2" onclick="toggleTransactions()" style="font-size:0.75rem;">
            <i class="fas fa-list"></i> Lihat Detail Transaksi
        </button>
    `;
}

function toggleTransactions() {
    const panel = document.getElementById('transactions-panel');
    panel.classList.toggle('d-none');
    if (!panel.classList.contains('d-none') && !panel.dataset.loaded) {
        panel.dataset.loaded = '1';
        loadTransactions();
    }
}

function loadTransactions() {
    const accountId = document.getElementById('account-select').value;
    const date = new Date().toISOString().slice(0, 10);
    const content = document.getElementById('transactions-content');
    content.innerHTML = '<div class="text-muted text-center" style="font-size:0.8rem;">Memuat...</div>';

    fetch('{{ route("cash-counter.period-transactions") }}?account_id=' + accountId + '&date=' + date, { headers: { 'Accept': 'application/json' } })
    .then(r => parseJSON(r))
    .then(data => {
        let html = '<div style="font-size:0.8rem;">';
        html += '<div class="fw-bold mb-1" style="color:#10b981;">Pemasukan Hari Ini</div>';
        let hasIncome = false;
        for (const [cat, total] of Object.entries(data.incomes)) {
            if (total > 0) { html += '<div class="d-flex justify-content-between"><span>' + cat + '</span><span>' + formatRupiah(total) + '</span></div>'; hasIncome = true; }
        }
        if (!hasIncome) html += '<div class="text-muted">Tidak ada pemasukan</div>';
        html += '<div class="fw-bold mt-2 mb-1" style="color:#ef4444;">Pengeluaran Hari Ini</div>';
        let hasExpense = false;
        for (const [cat, total] of Object.entries(data.expenses)) {
            if (total > 0) { html += '<div class="d-flex justify-content-between"><span>' + cat + '</span><span>' + formatRupiah(total) + '</span></div>'; hasExpense = true; }
        }
        if (!hasExpense) html += '<div class="text-muted">Tidak ada pengeluaran</div>';
        html += '<hr style="border-color:rgba(255,255,255,0.08);">';
        html += '<div class="d-flex justify-content-between fw-bold"><span>Total Pemasukan</span><span style="color:#10b981;">' + formatRupiah(data.total_income) + '</span></div>';
        html += '<div class="d-flex justify-content-between fw-bold"><span>Total Pengeluaran</span><span style="color:#ef4444;">' + formatRupiah(data.total_expense) + '</span></div>';
        html += '</div>';
        content.innerHTML = html;
    })
    .catch(() => { content.innerHTML = '<div class="text-muted text-center" style="font-size:0.8rem;">Gagal memuat transaksi</div>'; });
}

function onAccountChange() {
    const select = document.getElementById('account-select');
    const infoPanel = document.getElementById('account-balance-info');

    if (!select.value) {
        infoPanel.classList.add('d-none');
        return;
    }

    const balance = parseInt(select.options[select.selectedIndex].dataset.balance) || 0;
    document.getElementById('opening-balance').value = balance;
    infoPanel.classList.remove('d-none');
    updateAccountBalanceInfo(getGrandTotal());
}

function updateAccountBalanceInfo(grandTotal) {
    const select = document.getElementById('account-select');
    if (!select.value) return;

    const option = select.options[select.selectedIndex];
    const balance = parseInt(option.dataset.balance) || 0;
    const diff = grandTotal - balance;

    document.getElementById('system-balance').textContent = formatRupiah(balance);
    document.getElementById('physical-balance').textContent = formatRupiah(grandTotal);

    const diffEl = document.getElementById('diff-balance');
    diffEl.textContent = (diff >= 0 ? '+' : '') + formatRupiah(diff);
    diffEl.style.color = diff === 0 ? 'var(--text-primary)' : (diff > 0 ? '#10b981' : '#ef4444');
}

function getGrandTotal() {
    return parseInt(document.getElementById('grand-total').textContent.replace(/[^\d]/g, '')) || 0;
}

function resetCalculator() {
    confirmAction('Reset semua input?').then(ok => {
        if (!ok) return;
        DENOM_KEYS.forEach(key => { setCount(key, 0); });
        onAccountChange();
        document.getElementById('reconciliation-panel').classList.add('d-none');
        document.getElementById('transactions-panel').classList.add('d-none');
        delete document.getElementById('transactions-panel').dataset.loaded;
        updateTotal();
        showToast('Semua input direset');
    });
}

const saveModal = new bootstrap.Modal(document.getElementById('saveModal'));
function openSaveModal() {
    const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
    const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    const now = new Date();
    const title = days[now.getDay()] + ' ' + now.getDate() + ' ' + months[now.getMonth()] + ' ' + now.getFullYear();
    document.getElementById('session-title').value = title;
    saveModal.show();
}

function saveSession() {
    const title = document.getElementById('session-title').value.trim();
    if (!title) { showToast('Masukkan nama sesi'); return; }

    const denominations = {};
    DENOM_KEYS.forEach(key => { denominations[key] = getCount(key); });

    const select = document.getElementById('account-select');
    const balance = select.value ? parseInt(select.options[select.selectedIndex].dataset.balance) || 0 : 0;
    document.getElementById('opening-balance').value = balance;

    const body = JSON.stringify({
        title, denominations,
        opening_balance: balance,
        total_amount: getGrandTotal(),
        account_id: select.value || null
    });

    fetch('{{ route("cash-counter.save") }}', { method: 'POST', headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }, body })
    .then(r => r.ok ? parseJSON(r) : parseJSON(r).then(e => { throw new Error(e.message || 'Gagal menyimpan'); }))
    .then(s => { saveModal.hide(); showToast('Sesi disimpan'); updateTotal(); })
    .catch(e => showToast(e.message));
}



function parseJSON(r) {
    const ct = r.headers.get('content-type') || '';
    if (ct.includes('application/json')) return r.json();
    return r.text().then(t => { throw new Error('Gagal memuat data'); });
}

function showToast(msg) {
    const el = document.getElementById('toast');
    el.textContent = msg;
    el.classList.remove('d-none');
    setTimeout(() => el.classList.add('d-none'), 2500);
}

document.addEventListener('DOMContentLoaded', function() {
    buildCards();
    onAccountChange();
    updateTotal();
});
</script>
@endpush
