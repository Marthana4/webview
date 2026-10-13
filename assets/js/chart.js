/* ═══════════════════════════════════════════════════
   InterActive QRIS Dashboard — chart.js
   Line chart & Bar chart rendering
   ═══════════════════════════════════════════════════ */

'use strict';

// ─── LINE CHART ───────────────────────────────────
function drawLineChart(vals, lbls) {
  const canvas = document.getElementById('lineChart');
  if (!canvas || !vals.length) return;

  const dpr = window.devicePixelRatio || 1;
  const W   = canvas.parentElement.offsetWidth;
  const H   = 140;

  canvas.width        = W * dpr;
  canvas.height       = H * dpr;
  canvas.style.width  = W + 'px';
  canvas.style.height = H + 'px';

  const ctx  = canvas.getContext('2d');
  ctx.scale(dpr, dpr);

  const pad  = { t: 8, b: 22, l: 4, r: 4 };
  const maxC = Math.max(...vals, 1);

  const xp = i => pad.l + (i / (vals.length - 1 || 1)) * (W - pad.l - pad.r);
  const yp = v => pad.t + (1 - v / maxC) * (H - pad.t - pad.b);

  // Y Labels
  const yEl = document.getElementById('chartY');
  if (yEl) {
    yEl.innerHTML = [maxC, maxC * .75, maxC * .5, maxC * .25, 0]
      .map(v => `<span>${fmtShort(v)}</span>`).join('');
  }

  // Grid lines
  for (let i = 0; i <= 4; i++) {
    const y = pad.t + (i / 4) * (H - pad.t - pad.b);
    ctx.beginPath();
    ctx.strokeStyle = '#F0F0F0';
    ctx.lineWidth   = 1;
    ctx.moveTo(pad.l, y);
    ctx.lineTo(W - pad.r, y);
    ctx.stroke();
  }

  // Fill gradient
  const g = ctx.createLinearGradient(0, pad.t, 0, H - pad.b);
  g.addColorStop(0, 'rgba(232,25,44,.13)');
  g.addColorStop(1, 'rgba(232,25,44,0)');

  ctx.beginPath();
  ctx.moveTo(xp(0), yp(vals[0]));
  for (let i = 1; i < vals.length; i++) {
    const cx = (xp(i - 1) + xp(i)) / 2;
    ctx.bezierCurveTo(cx, yp(vals[i - 1]), cx, yp(vals[i]), xp(i), yp(vals[i]));
  }
  ctx.lineTo(xp(vals.length - 1), H - pad.b);
  ctx.lineTo(xp(0), H - pad.b);
  ctx.closePath();
  ctx.fillStyle = g;
  ctx.fill();

  // Line
  ctx.beginPath();
  ctx.moveTo(xp(0), yp(vals[0]));
  for (let i = 1; i < vals.length; i++) {
    const cx = (xp(i - 1) + xp(i)) / 2;
    ctx.bezierCurveTo(cx, yp(vals[i - 1]), cx, yp(vals[i]), xp(i), yp(vals[i]));
  }
  ctx.strokeStyle = '#E8192C';
  ctx.lineWidth   = 2;
  ctx.stroke();

  // Dots
  vals.forEach((v, i) => {
    ctx.beginPath();
    ctx.arc(xp(i), yp(v), 4, 0, Math.PI * 2);
    ctx.fillStyle   = '#E8192C';
    ctx.fill();
    ctx.strokeStyle = 'white';
    ctx.lineWidth   = 2;
    ctx.stroke();
  });

  // X Labels — show every Nth to avoid overlap
  ctx.fillStyle   = '#9CA3AF';
  ctx.font        = '600 10px Plus Jakarta Sans';
  ctx.textAlign   = 'center';
  const step = Math.ceil(lbls.length / 8);
  lbls.forEach((l, i) => {
    if (i % step === 0 || i === lbls.length - 1) {
      ctx.fillText(l, xp(i), H - 4);
    }
  });

  // Hover interaction
  canvas.onmousemove = e => {
    const r  = canvas.getBoundingClientRect();
    const mx = (e.clientX - r.left) * (W / r.width);
    let ci = 0, md = Infinity;
    for (let i = 0; i < vals.length; i++) {
      const d = Math.abs(xp(i) - mx);
      if (d < md) { md = d; ci = i; }
    }
    showTip(e, `Tgl ${lbls[ci]}: ${fmtRp(vals[ci])}`);
  };
  canvas.onmouseleave = hideTip;
  canvas.ontouchmove  = e => {
    e.preventDefault();
    canvas.onmousemove(e.touches[0]);
  };
  canvas.ontouchend    = hideTip;
  canvas.ontouchcancel = hideTip;
}

// ─── BAR CHART ────────────────────────────────────
function drawBarChart(vals, lbls) {
  const wrap = document.getElementById('barWrap');
  if (!wrap) return;

  const max = Math.max(...vals, 1);

  wrap.innerHTML = vals.map((v, i) => `
    <div class="bar-item">
      <div class="bar-fill ${v < max * .2 ? 'dim' : ''}"
        data-v="${v}"
        style="height:0px"
        onmouseenter="showTip(event, '${lbls[i] ? lbls[i] + ': ' : ''}${v}')"
        ontouchstart="showTip(event, '${lbls[i] ? lbls[i] + ': ' : ''}${v}')"
        onmouseleave="hideTip()"
        ontouchend="hideTip()">
      </div>
      ${lbls[i] ? `<div class="bar-xlbl">${lbls[i]}</div>` : ''}
    </div>`).join('');

  // Animate bars in
  setTimeout(() => {
    wrap.querySelectorAll('.bar-fill').forEach(el => {
      el.style.height = Math.max((+el.dataset.v / max) * 72, 3) + 'px';
    });
  }, 100);
}

function dotClick(i) {
  document.querySelectorAll('.dot').forEach((d, j) => d.classList.toggle('on', j === i));
  const wrap = document.getElementById('barWrap');
  if (wrap) wrap.scrollTo({ left: i * (wrap.scrollWidth / 5), behavior: 'smooth' });
}

// ─── PERIOD SHEET ─────────────────────────────────
const DAY_N = ['Min','Sen','Sel','Rab','Kam','Jum','Sab'];
const MON_N = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Ags','Sep','Okt','Nov','Des'];

function fmtD(d) {
  return `${DAY_N[d.getDay()]}. ${d.getDate()} ${MON_N[d.getMonth()]} ${d.getFullYear()}`;
}

function addDays(n) {
  const d = new Date();
  d.setDate(d.getDate() + n);
  return d;
}

function initPeriodLabels(activePeriod) {
  const today      = new Date();
  const startMonth = new Date(today.getFullYear(), today.getMonth(), 1);

  const map = {
    kemarin: `Min. ${fmtD(addDays(-1))}`,
    bulan:   `${fmtD(startMonth)} – ${fmtD(today)}`,
    7:       `${fmtD(addDays(-7))} – ${fmtD(today)}`,
    30:      `${fmtD(addDays(-30))} – ${fmtD(today)}`,
  };

  Object.entries(map).forEach(([k, v]) => {
    const el = document.getElementById('plbl-' + k);
    if (el) el.textContent = v;
  });

  updateRadios(activePeriod || '30');
}

function updateRadios(v) {
  ['kemarin', 'bulan', '7', '30', 'atur'].forEach(k => {
    const r = document.getElementById('pradio-' + k);
    if (r) r.classList.toggle('checked', k === v);
  });
  const aturRow = document.getElementById('aturInputs');
  if (aturRow) aturRow.style.display = v === 'atur' ? 'flex' : 'none';
}

const PERIOD_LBLS = {
  kemarin: 'Kemarin',
  bulan:   'Bulan Ini',
  7:       '7 Hari Terakhir',
  30:      '30 Hari Terakhir',
  atur:    'Atur Tanggal',
};

// curPeriod & applyPeriod harus di-definisikan di page-specific JS
// karena tiap halaman mungkin punya behavior berbeda
