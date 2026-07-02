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
            <div class="cc-total-label">Total Uang Dihitung</div>
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
                <div class="cc-control-icon"><i class="fas fa-bullseye"></i></div>
                <div class="cc-control-content">
                    <div class="cc-control-label">Target Kas</div>
                    <div class="cc-target-input">
                        <span class="cc-target-prefix">Rp</span>
                        <input type="number" id="target-amount" class="cc-input" min="0" placeholder="0" oninput="updateTotal()">
                        <button type="button" class="cc-target-btn" onclick="fillTargetFromBalance()" title="Isi dari saldo sistem">
                            <i class="fas fa-arrow-down"></i>
                        </button>
                    </div>
                </div>
            </div>

            <div id="target-result-panel" class="cc-target-result d-none">
                <div class="cc-balance-row">
                    <span>Status</span>
                    <span id="target-status" class="badge rounded-pill">Sesuai</span>
                </div>
                <div class="cc-balance-row">
                    <span>Selisih</span>
                    <span id="target-diff" class="fw-bold">Rp 0</span>
                </div>
            </div>

            <div id="adjust-panel" class="cc-adjust-panel d-none">
                <div class="cc-adjust-label">Penyesuaian Kas <span id="adjust-count-badge" class="badge bg-secondary ms-1" style="font-size:0.7rem;"></span></div>
                <div id="adjust-max-msg" class="text-center text-muted d-none" style="font-size:0.8rem;padding:4px 0;">Maksimal 2x penyesuaian tercapai</div>
                <button id="btn-adjust-income" class="cc-btn cc-btn-success w-100 d-none" onclick="createAdjustment('income')">
                    <i class="fas fa-plus"></i> <span id="adjust-income-text">Pendapatan Penyesuaian</span>
                </button>
                <button id="btn-adjust-expense" class="cc-btn cc-btn-danger w-100 d-none" onclick="createAdjustment('expense')">
                    <i class="fas fa-minus"></i> <span id="adjust-expense-text">Pengeluaran Penyesuaian</span>
                </button>
                <button id="btn-delete-adjustment" class="cc-btn w-100 d-none mt-1" style="background:#64748b;color:#fff;" onclick="deleteAdjustment()">
                    <i class="fas fa-undo"></i> Hapus Penyesuaian
                </button>
            </div>
        </div>

        {{-- Distribution Chart --}}
        <div class="cc-control-card">
            <div class="cc-control-row mb-2">
                <div class="cc-control-icon"><i class="fas fa-chart-pie"></i></div>
                <div class="cc-control-content">
                    <div class="cc-control-label">Distribusi Denominasi</div>
                </div>
            </div>
            <div class="cc-chart-wrap">
                <canvas id="distribution-chart"></canvas>
                <div id="chart-placeholder" class="cc-chart-placeholder">
                    <i class="fas fa-info-circle"></i>
                    <span>Masukkan jumlah untuk melihat distribusi</span>
                </div>
            </div>
        </div>

        {{-- Action Buttons --}}
        <div class="cc-actions">
            <button class="cc-btn cc-btn-secondary" onclick="resetCalculator()">
                <i class="fas fa-undo-alt"></i> Reset
            </button>
            <button class="cc-btn cc-btn-primary" onclick="copySummary()">
                <i class="fas fa-copy"></i> Salin
            </button>
            <button class="cc-btn cc-btn-success" onclick="openSaveModal()">
                <i class="fas fa-save"></i> Simpan
            </button>
        </div>

        {{-- History --}}
        <div class="cc-history-card">
            <div class="cc-history-header">
                <div class="cc-control-row">
                    <div class="cc-control-icon"><i class="fas fa-history"></i></div>
                    <div class="cc-control-content">
                        <div class="cc-control-label">Riwayat Sesi</div>
                    </div>
                </div>
                <button class="cc-btn-sm cc-btn-danger-sm" onclick="clearHistory()">
                    <i class="fas fa-trash"></i> Hapus Semua
                </button>
            </div>
            <div id="history-list-container" class="cc-history-list">
                <div class="cc-history-empty">Belum ada sesi tersimpan</div>
            </div>
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
                <div class="cc-modal-total">
                    <span>Total</span>
                    <strong id="modal-total-display">Rp 0</strong>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-modern btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-modern btn-primary" onclick="saveSession()">Simpan</button>
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
.cc-chart-wrap {
    position: relative;
    height: 160px;
}

.cc-chart-placeholder {
    position: absolute;
    inset: 0;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    color: var(--text-muted);
    font-size: 0.75rem;
    gap: 0.3rem;
}

.cc-chart-placeholder i { font-size: 1rem; opacity: 0.5; }

/* History */
.cc-history-card {
    background: var(--bg-card);
    border: 1px solid var(--border-subtle);
    border-radius: 12px;
    overflow: hidden;
}

.cc-history-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0.75rem;
    border-bottom: 1px solid var(--border-subtle);
}

.cc-history-list {
    max-height: 200px;
    overflow-y: auto;
}

.cc-history-empty {
    text-align: center;
    padding: 1.5rem;
    color: var(--text-muted);
    font-size: 0.8rem;
}

.cc-history-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0.6rem 0.75rem;
    border-bottom: 1px solid var(--border-subtle);
    transition: background 0.1s;
}

.cc-history-item:hover { background: rgba(var(--theme-primary-rgb), 0.03); }
.cc-history-item:last-child { border-bottom: none; }

.cc-history-info { flex: 1; min-width: 0; }
.cc-history-title { font-weight: 600; font-size: 0.8rem; color: var(--text-primary); }
.cc-history-meta { font-size: 0.65rem; color: var(--text-muted); margin-top: 0.15rem; }

.cc-history-actions { display: flex; gap: 0.25rem; }

.cc-history-btn {
    width: 26px;
    height: 26px;
    display: flex;
    align-items: center;
    justify-content: center;
    border: none;
    border-radius: 6px;
    font-size: 0.65rem;
    cursor: pointer;
    transition: all 0.1s;
}

.cc-history-btn-primary { background: rgba(var(--theme-primary-rgb), 0.1); color: var(--theme-primary); }
.cc-history-btn-primary:hover { background: var(--theme-primary); color: #fff; }
.cc-history-btn-danger { background: rgba(239,68,68,0.1); color: #ef4444; }
.cc-history-btn-danger:hover { background: #ef4444; color: #fff; }

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
    { key: 100000, label: 'Rp 100.000', color: '#f43f5e' },
    { key: 50000,  label: 'Rp 50.000',  color: '#3b82f6' },
    { key: 20000,  label: 'Rp 20.000',  color: '#10b981' },
    { key: 10000,  label: 'Rp 10.000',  color: '#8b5cf6' },
    { key: 5000,   label: 'Rp 5.000',   color: '#f59e0b' },
    { key: 2000,   label: 'Rp 2.000',   color: '#6b7280' },
    { key: 1000,   label: 'Rp 1.000',   color: '#84cc16' },
    { key: 'c500',  value: 500,  label: 'Koin Rp 500',   color: '#a855f7' },
];

let chartInstance = null;
const DENOM_KEYS = denoms.map(d => d.key);
let currentSessionId = null;
let savedAccountId = null;
let adjustmentCount = 0;

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

    updateTargetCash(grandTotal);
    updateAccountBalanceInfo(grandTotal);
    updateAdjustPanel(grandTotal);
    updateChart(grandTotal);
}

function updateTargetCash(grandTotal) {
    const targetInput = document.getElementById('target-amount');
    const target = parseInt(targetInput.value) || 0;
    const panel = document.getElementById('target-result-panel');
    if (target <= 0) { panel.classList.add('d-none'); return; }
    panel.classList.remove('d-none');

    const statusEl = document.getElementById('target-status');
    const diffEl = document.getElementById('target-diff');
    const diff = grandTotal - target;

    if (grandTotal === target) {
        statusEl.textContent = 'Pas';
        statusEl.className = 'badge rounded-pill bg-success';
        diffEl.textContent = 'Rp 0';
        diffEl.style.color = 'var(--text-primary)';
    } else if (grandTotal > target) {
        statusEl.textContent = 'Lebih';
        statusEl.className = 'badge rounded-pill bg-warning text-dark';
        diffEl.textContent = formatRupiah(diff);
        diffEl.style.color = '#f59e0b';
    } else {
        statusEl.textContent = 'Kurang';
        statusEl.className = 'badge rounded-pill bg-danger';
        diffEl.textContent = formatRupiah(Math.abs(diff));
        diffEl.style.color = '#ef4444';
    }
}

function onAccountChange() {
    const select = document.getElementById('account-select');
    const infoPanel = document.getElementById('account-balance-info');

    if (!select.value) {
        infoPanel.classList.add('d-none');
        document.getElementById('adjust-panel').classList.add('d-none');
        return;
    }

    infoPanel.classList.remove('d-none');
    updateAccountBalanceInfo(getGrandTotal());
    updateAdjustPanel(getGrandTotal());
}

function fillTargetFromBalance() {
    const select = document.getElementById('account-select');
    if (!select.value) { showToast('Pilih akun terlebih dahulu'); return; }
    const option = select.options[select.selectedIndex];
    const balance = parseInt(option.dataset.balance) || 0;
    document.getElementById('target-amount').value = balance;
    updateTotal();
    showToast('Target diisi dari saldo sistem');
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

function updateAdjustPanel(grandTotal) {
    const select = document.getElementById('account-select');
    const badge = document.getElementById('adjust-count-badge');
    const btnDelete = document.getElementById('btn-delete-adjustment');

    if (!select.value || !currentSessionId) {
        document.getElementById('adjust-panel').classList.add('d-none');
        return;
    }

    btnDelete.classList.toggle('d-none', adjustmentCount === 0);

    if (adjustmentCount >= 2) {
        badge.textContent = '2/2';
        document.getElementById('adjust-panel').classList.remove('d-none');
        document.getElementById('adjust-max-msg').classList.remove('d-none');
        document.getElementById('btn-adjust-income').classList.add('d-none');
        document.getElementById('btn-adjust-expense').classList.add('d-none');
        return;
    }

    const option = select.options[select.selectedIndex];
    const balance = parseInt(option.dataset.balance) || 0;
    const diff = grandTotal - balance;

    if (diff === 0) {
        document.getElementById('adjust-panel').classList.add('d-none');
        return;
    }

    document.getElementById('adjust-panel').classList.remove('d-none');
    document.getElementById('adjust-max-msg').classList.add('d-none');
    badge.textContent = adjustmentCount + '/2';
    const absDiff = Math.abs(diff);

    if (diff > 0) {
        document.getElementById('btn-adjust-income').classList.remove('d-none');
        document.getElementById('btn-adjust-expense').classList.add('d-none');
        document.getElementById('adjust-income-text').textContent = 'Pendapatan +' + formatRupiah(absDiff);
    } else {
        document.getElementById('btn-adjust-income').classList.add('d-none');
        document.getElementById('btn-adjust-expense').classList.remove('d-none');
        document.getElementById('adjust-expense-text').textContent = 'Pengeluaran -' + formatRupiah(absDiff);
    }
}

function getGrandTotal() {
    return parseInt(document.getElementById('grand-total').textContent.replace(/[^\d]/g, '')) || 0;
}

function createAdjustment(type) {
    if (!currentSessionId) {
        showToast('Simpan sesi terlebih dahulu');
        return;
    }

    // Disable tombol biar gak diklik 2x
    const btnIncome = document.getElementById('btn-adjust-income');
    const btnExpense = document.getElementById('btn-adjust-expense');
    btnIncome.disabled = true;
    btnExpense.disabled = true;

    const grandTotal = getGrandTotal();
    const balance = parseInt(document.getElementById('account-select').options[document.getElementById('account-select').selectedIndex].dataset.balance) || 0;
    const diff = Math.abs(grandTotal - balance);

    confirmAction('Buat ' + (type === 'income' ? 'pendapatan' : 'pengeluaran') + ' penyesuaian ' + formatRupiah(diff) + '?').then(ok => {
        if (!ok) {
            btnIncome.disabled = false;
            btnExpense.disabled = false;
            return;
        }

        fetch('{{ url("cash-counter/sessions") }}/' + currentSessionId + '/adjust', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify({ type, amount: diff, account_id: document.getElementById('account-select').value })
        })
        .then(r => r.ok ? r.json() : r.json().then(e => { throw new Error(e.message); }))
        .then(res => {
            showToast(res.message);
            adjustmentCount = res.adjustment_count || adjustmentCount + 1;
            updateAdjustPanel(getGrandTotal());
            btnIncome.disabled = false;
            btnExpense.disabled = false;
        })
        .catch(e => {
            showToast(e.message);
            btnIncome.disabled = false;
            btnExpense.disabled = false;
        });
    });
}

function deleteAdjustment() {
    if (!currentSessionId) {
        showToast('Simpan sesi terlebih dahulu');
        return;
    }

    confirmAction('Hapus semua penyesuaian untuk sesi ini?').then(ok => {
        if (!ok) return;

        const btn = document.getElementById('btn-delete-adjustment');
        btn.disabled = true;

        fetch('{{ url("cash-counter/sessions") }}/' + currentSessionId + '/adjust', {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
        })
        .then(r => r.ok ? r.json() : r.json().then(e => { throw new Error(e.message); }))
        .then(res => {
            showToast(res.message);
            adjustmentCount = 0;
            updateAdjustPanel(getGrandTotal());
            btn.disabled = false;
        })
        .catch(e => {
            showToast(e.message);
            btn.disabled = false;
        });
    });
}

function updateChart() {
    const labels = [], data = [], colors = [];
    const placeholder = document.getElementById('chart-placeholder');

    denoms.forEach(d => {
        const count = getCount(d.key);
        const subtotal = count * getDenomValue(d.key);
        if (subtotal > 0) { labels.push(d.label); data.push(subtotal); colors.push(d.color); }
    });

    if (data.length === 0) {
        placeholder.style.display = 'flex';
        if (chartInstance) { chartInstance.destroy(); chartInstance = null; }
        return;
    }
    placeholder.style.display = 'none';

    const ctx = document.getElementById('distribution-chart').getContext('2d');
    if (chartInstance) chartInstance.destroy();

    chartInstance = new Chart(ctx, {
        type: 'doughnut',
        data: { labels, datasets: [{ data, backgroundColor: colors, borderWidth: 0 }] },
        options: {
            responsive: true, maintainAspectRatio: false, cutout: '65%',
            plugins: { legend: { position: 'right', labels: { boxWidth: 10, padding: 6, font: { size: 9 }, color: getComputedStyle(document.body).getPropertyValue('--text-primary').trim() || '#1e293b' } } }
        }
    });
}

function resetCalculator() {
    confirmAction('Reset semua input?').then(ok => {
        if (!ok) return;
        DENOM_KEYS.forEach(key => { setCount(key, 0); });
        document.getElementById('target-amount').value = '';
        document.getElementById('target-result-panel').classList.add('d-none');
        document.getElementById('adjust-panel').classList.add('d-none');
        currentSessionId = null;
        adjustmentCount = 0;
        updateTotal();
        showToast('Semua input direset');
    });
}

function copySummary() {
    let text = '=== RINGKASAN KAS ===\n\n';
    denoms.forEach(d => {
        const count = getCount(d.key);
        if (count > 0) {
            const value = getDenomValue(d.key);
            text += d.label + ' : ' + count + ' x ' + formatRupiah(value) + ' = ' + formatRupiah(count * value) + '\n';
        }
    });
    text += '\nTOTAL: ' + document.getElementById('grand-total').textContent;

    const target = parseInt(document.getElementById('target-amount').value) || 0;
    if (target > 0) {
        const diff = getGrandTotal() - target;
        text += '\nTarget: ' + formatRupiah(target);
        text += '\nSelisih: ' + formatRupiah(Math.abs(diff)) + (diff >= 0 ? ' (Lebih)' : ' (Kurang)');
    }

    const select = document.getElementById('account-select');
    if (select.value) text += '\nAkun: ' + select.options[select.selectedIndex].text;

    navigator.clipboard.writeText(text).then(() => showToast('Ringkasan disalin'));
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

    const body = JSON.stringify({
        title, denominations,
        target_amount: parseInt(document.getElementById('target-amount').value) || null,
        total_amount: getGrandTotal(),
        account_id: document.getElementById('account-select').value || null
    });

    const url = currentSessionId ? '{{ url("cash-counter/sessions") }}/' + currentSessionId : '{{ route("cash-counter.sessions.store") }}';
    const method = currentSessionId ? 'PUT' : 'POST';

    fetch(url, { method, headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }, body })
    .then(r => r.ok ? r.json() : r.json().then(e => { throw new Error(e.message || 'Gagal'); }))
    .then(s => { currentSessionId = s.id; adjustmentCount = 0; saveModal.hide(); showToast('Sesi disimpan'); loadHistory(); updateAdjustPanel(getGrandTotal()); })
    .catch(e => showToast(e.message));
}

function loadHistory() {
    fetch('{{ route("cash-counter.history") }}')
    .then(r => r.json())
    .then(sessions => {
        const container = document.getElementById('history-list-container');
        if (sessions.length === 0) {
            container.innerHTML = '<div class="cc-history-empty">Belum ada sesi tersimpan</div>';
            return;
        }
        container.innerHTML = sessions.map(s => `
            <div class="cc-history-item">
                <div class="cc-history-info">
                    <div class="cc-history-title">${escapeHtml(s.title)}</div>
                    <div class="cc-history-meta">${formatRupiah(s.total_amount)}${s.account ? ' &middot; ' + escapeHtml(s.account.name) : ''} &middot; ${new Date(s.created_at).toLocaleDateString('id-ID')}</div>
                </div>
                <div class="cc-history-actions">
                    <button class="cc-history-btn cc-history-btn-primary" onclick="loadSession(${s.id})" title="Muat"><i class="fas fa-folder-open"></i></button>
                    <button class="cc-history-btn cc-history-btn-danger" onclick="deleteSession(${s.id})" title="Hapus"><i class="fas fa-trash"></i></button>
                </div>
            </div>
        `).join('');
    });
}

function loadSession(id) {
    fetch(`{{ url("cash-counter/sessions") }}/${id}`)
    .then(r => r.json())
    .then(s => {
        const data = s.denominations || {};
        DENOM_KEYS.forEach(key => { setCount(key, data[key] || 0); });
        if (s.target_amount) document.getElementById('target-amount').value = s.target_amount;
        if (s.account_id) { document.getElementById('account-select').value = s.account_id; onAccountChange(); }
        currentSessionId = s.id;
        adjustmentCount = s.adjustment_count || 0;
        updateTotal();
        showToast('Sesi "' + escapeHtml(s.title) + '" dimuat');
    });
}

function deleteSession(id) {
    confirmDelete('Hapus sesi ini?').then(ok => {
        if (!ok) return;
        fetch(`{{ url("cash-counter/sessions") }}/${id}`, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' } })
        .then(r => r.json())
        .then(() => { showToast('Sesi dihapus'); loadHistory(); });
    });
}

function clearHistory() {
    confirmDelete('Hapus semua sesi?').then(ok => {
        if (!ok) return;
        fetch('{{ route("cash-counter.history") }}')
        .then(r => r.json())
        .then(sessions => {
            if (sessions.length === 0) { showToast('Tidak ada sesi'); return; }
            Promise.all(sessions.map(s =>
                fetch(`{{ url("cash-counter/sessions") }}/${s.id}`, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' } })
            )).then(() => { showToast('Semua sesi dihapus'); loadHistory(); })
            .catch(() => showToast('Gagal menghapus beberapa sesi'));
        });
    });
}

function escapeHtml(str) {
    const div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
}

function showToast(msg) {
    const el = document.getElementById('toast');
    el.textContent = msg;
    el.classList.remove('d-none');
    setTimeout(() => el.classList.add('d-none'), 2500);
}

document.addEventListener('DOMContentLoaded', function() {
    buildCards();
    updateTotal();
    loadHistory();

    const accountSelect = document.getElementById('account-select');
    if (accountSelect && accountSelect.value) {
        onAccountChange();
        const option = accountSelect.options[accountSelect.selectedIndex];
        const balance = parseInt(option.dataset.balance) || 0;
        document.getElementById('target-amount').value = balance;
        updateTotal();
    }
});
</script>
@endpush
