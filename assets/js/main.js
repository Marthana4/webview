/* ═══════════════════════════════════════════════════
   InterActive QRIS Dashboard — main.js
   Shared utilities & UI components
   ═══════════════════════════════════════════════════ */

'use strict';

// ─── UTILS ────────────────────────────────────────
const fmtRp = v => 'Rp ' + parseInt(v).toLocaleString('id-ID');

const fmtShort = v => {
  v = parseInt(v);
  if (v >= 1e9) return (v / 1e9).toFixed(1).replace('.0', '') + 'M';
  if (v >= 1e6) return Math.round(v / 1e6) + 'jt';
  if (v >= 1e3) return Math.round(v / 1e3) + 'rb';
  return v;
};

// ─── TOOLTIP ─────────────────────────────────────
const tipEl = document.getElementById('tip');

function showTip(e, text) {
  if (!tipEl) return;
  tipEl.textContent = text;
  tipEl.style.opacity = '1';
  tipEl.style.left = e.clientX + 'px';
  tipEl.style.top  = (e.clientY - 36) + 'px';
}

function hideTip() {
  if (tipEl) tipEl.style.opacity = '0';
}

// ─── TOAST ───────────────────────────────────────
function toast(msg, isError = false) {
  const el = document.getElementById('toast');
  if (!el) return;
  el.textContent = msg;
  el.style.background = isError ? '#E8192C' : '#111827';
  el.classList.add('show');
  setTimeout(() => el.classList.remove('show'), 2800);
}

// ─── OVERLAY / SHEETS ─────────────────────────────
function openOv(id) {
  const el = document.getElementById(id);
  if (!el) return;
  el.classList.add('on');
  if (id === 'ovLokasi') {
    const input = document.getElementById('locSearch');
    if (input) { input.value = ''; }
    renderLocs('');
  }
}

function closeOv(e, id) {
  if (e.target === document.getElementById(id)) closeOvById(id);
}

function closeOvById(id) {
  const el = document.getElementById(id);
  if (el) el.classList.remove('on');
}

// ─── LOKASI SHEET ─────────────────────────────────
// ALL_TOKO dan selToko harus di-set di page-specific JS

function renderLocs(filter) {
  const list = document.getElementById('locList');
  if (!list || typeof ALL_TOKO === 'undefined') return;

  const f = (filter || '').toLowerCase();
  list.innerHTML = ALL_TOKO
    .filter(l =>
      l.nama.toLowerCase().includes(f) ||
      (l.alamat || '').toLowerCase().includes(f)
    )
    .map(l => `
      <div class="loc-item" onclick="pickLoc(${l.id}, '${l.nama.replace(/'/g,"\\'")}', '${(l.alamat||'').replace(/'/g,"\\'")}')">
        <div>
          <div class="loc-name">${l.nama}</div>
          <div class="loc-addr2">
            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/>
            </svg>
            ${l.alamat || ''}
          </div>
        </div>
        ${typeof selToko !== 'undefined' && selToko === parseInt(l.id)
          ? `<div class="loc-check">
               <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                 <polyline points="20 6 9 17 4 12"/>
               </svg>
             </div>`
          : ''}
      </div>`).join('');
}

function filterLoc(val) {
  renderLocs(val);
}
