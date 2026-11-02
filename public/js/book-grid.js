/*
 * Books grid editor — spreadsheet-style editing for books/contracts/stock.
 * Plain JS engine (virtualised rows, keyboard, paste from Excel, undo/redo, draft autosave)
 * + a small Alpine component (window.bookGrid) for toolbar / review modal.
 * Nothing is written by this file: it only builds a payload that the server previews and applies.
 */
(function () {
    'use strict';

    var ROW_H = 34, HEAD_H = 40, GUTTER_W = 92, OVERSCAN = 8, DEFAULT_PCT = 10;
    var MAXLEN = { name: 255, isbn: 50, authors: 500, contractor: 255, nationality: 150, category: 255, sub_category: 255, language: 100,
        translated_from: 100, translated_to: 100, translator_name: 255, supervisor: 255, introduction_by: 255, deposit_number: 50,
        deposit_status: 50, sku: 100 };

    function esc(s) {
        return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
        });
    }
    function blank(v) { return v === null || v === undefined || v === ''; }
    function same(a, b) {
        if (blank(a) && blank(b)) return true;
        if (blank(a) || blank(b)) return false;
        if (typeof a === 'boolean' || typeof b === 'boolean') return !!a === !!b;
        if (!isNaN(a) && !isNaN(b) && a !== '' && b !== '') return Math.abs(Number(a) - Number(b)) < 0.0001;
        return String(a) === String(b);
    }
    function arabicDigits(s) {
        return String(s).replace(/[٠-٩]/g, function (d) { return d.charCodeAt(0) - 0x660; })
            .replace(/[۰-۹]/g, function (d) { return d.charCodeAt(0) - 0x6F0; });
    }
    function clone(o) { return JSON.parse(JSON.stringify(o)); }

    /* ───────────────────────────── Engine ───────────────────────────── */
    function Engine(host, config, hooks) {
        this.host = host; this.cfg = config; this.T = config.i18n.ui; this.hooks = hooks || {};
        this.cols = []; this.rows = []; this.view = []; this.lists = {};
        this.hiddenGroups = {}; this.sort = null; this.filter = { mode: 'all', q: '', cat: '' };
        this.active = null; this.sel = null; this.editor = null; this.undoStack = []; this.redoStack = [];
        this.seq = 0; this.first = -1; this.last = -1; this.dirtyRender = true; this.warehouseId = null;
        this.build();
    }
    var P = Engine.prototype;

    P.t = function (k, p) {
        var s = this.T[k] || k;
        if (p) for (var x in p) s = s.replace(':' + x, p[x]);
        return s;
    };

    P.build = function () {
        var self = this;
        this.host.innerHTML = '<div class="bg-inner"><div class="bg-head"></div><div class="bg-body"></div></div>';
        this.head = this.host.querySelector('.bg-head');
        this.body = this.host.querySelector('.bg-body');
        this.host.setAttribute('tabindex', '0');
        this.host.addEventListener('scroll', function () { self.schedule(); });
        this.host.addEventListener('mousedown', function (e) { self.onMouseDown(e); });
        this.host.addEventListener('dblclick', function (e) { self.onDblClick(e); });
        this.host.addEventListener('keydown', function (e) { self.onKey(e); });
        this.host.addEventListener('paste', function (e) { self.onPaste(e); });
        this.host.addEventListener('copy', function (e) { self.onCopy(e, false); });
        this.host.addEventListener('cut', function (e) { self.onCopy(e, true); });
        this.head.addEventListener('click', function (e) { self.onHeadClick(e); });
        this.head.addEventListener('mousedown', function (e) { self.onResizeStart(e); });
        window.addEventListener('mouseup', function () { self.dragging = false; self.resizing = null; });
        window.addEventListener('mousemove', function (e) { self.onMouseMove(e); });
        window.addEventListener('resize', function () { self.schedule(); });
    };

    /* ── data ── */
    P.load = function (snapshot) {
        var self = this;
        this.cols = snapshot.columns; this.lists = snapshot.lists; this.warehouseId = snapshot.warehouse_id;
        this.byKey = {}; this.cols.forEach(function (c) { self.byKey[c.key] = c; });
        this.rows = snapshot.rows.map(function (s) { return self.makeRow(s); });
        this.undoStack = []; this.redoStack = []; this.active = null; this.sel = null;
        this.subMap = {};
        (snapshot.lists.sub_categories || []).forEach(function (s) { (self.subMap[s.parent] = self.subMap[s.parent] || []).push(s.name); });
        this.contractorInfo = {};
        (snapshot.lists.contractors || []).forEach(function (c) { self.contractorInfo[c.name.toLowerCase()] = c; });
        this.renderHeader(); this.rebuildView(); this.stats();
    };

    P.makeRow = function (s) {
        var vals = {};
        this.cols.forEach(function (c) { vals[c.key] = s[c.key] === undefined ? null : s[c.key]; });
        return { key: s.key, book_id: s.book_id, v: s.v, q0: s.quantity, vals: vals, orig: clone(vals), locks: s.locks || {},
            deleted: false, isNew: false, errors: {}, srv: {}, sel: false };
    };

    P.newRow = function () {
        var vals = {}, key = 'n' + (++this.seq) + '_' + Date.now().toString(36);
        this.cols.forEach(function (c) { vals[c.key] = null; });
        vals.status = 'active'; vals.cover_type = 'soft'; vals.contract_status = 'active'; vals.quantity = 0; vals.is_translated = false;
        return { key: key, book_id: null, v: null, q0: 0, vals: vals, orig: null, locks: {}, deleted: false, isNew: true, errors: {}, srv: {}, sel: false };
    };

    P.editableCols = function () { return this.cols.filter(function (c) { return !c.readonly; }); };
    P.visCols = function () { var h = this.hiddenGroups; return this.cols.filter(function (c) { return !(c.group && h[c.group]); }); };

    P.dirtyKeys = function (row) {
        if (row.isNew) return this.editableCols().map(function (c) { return c.key; });
        var o = row.orig, v = row.vals;
        return this.editableCols().filter(function (c) { return !same(v[c.key], o[c.key]); }).map(function (c) { return c.key; });
    };
    P.isBlankNew = function (row) {
        if (!row.isNew) return false;
        var v = row.vals;
        return this.editableCols().every(function (c) {
            return blank(v[c.key]) || (['status', 'cover_type', 'contract_status'].indexOf(c.key) >= 0) || (c.key === 'quantity' && !Number(v[c.key])) || (c.key === 'is_translated' && !v[c.key]);
        });
    };
    P.isChanged = function (row) { return !row.isNew && !row.deleted && this.dirtyKeys(row).length > 0; };

    /* ── validation (light, instant; the server re-validates strictly) ── */
    P.validateAll = function () {
        var self = this, isbnCount = {};
        this.rows.forEach(function (r) {
            if (r.deleted || self.isBlankNew(r) || !(r.isNew || self.dirtyKeys(r).length)) return;
            var i = String(r.vals.isbn || '').replace(/[\s-]/g, '').toUpperCase();
            if (i) isbnCount[i] = (isbnCount[i] || 0) + 1;
        });
        this.rows.forEach(function (r) {
            r.errors = {};
            if (r.deleted || self.isBlankNew(r) || !(r.isNew || self.dirtyKeys(r).length)) return;
            var v = r.vals, e = r.errors, T = self.T;
            self.editableCols().forEach(function (c) {
                var x = v[c.key], k = c.key;
                if (blank(x)) { if (['name', 'isbn', 'base_price'].indexOf(k) >= 0) e[k] = T.req || 'Required'; return; }
                if (c.type === 'number') { if (isNaN(x) || Number(x) < 0 || (k === 'profit_percentage' && Number(x) > 100)) e[k] = '0–' + (k === 'profit_percentage' ? '100' : '∞'); }
                else if (c.type === 'int') {
                    if (isNaN(x) || Math.floor(Number(x)) !== Number(x) || Number(x) < 0) e[k] = '#';
                    else if (k === 'num_of_pages' && Number(x) < 1) e[k] = '≥ 1';
                    else if (k === 'deposit_year' && (Number(x) < 1900 || Number(x) > 2100)) e[k] = '1900–2100';
                } else if (c.type === 'date') {
                    if (!/^\d{4}-\d{2}-\d{2}$/.test(x) || isNaN(Date.parse(x))) e[k] = 'YYYY-MM-DD';
                } else if (c.type === 'select') {
                    if (c.options.indexOf(x) < 0) e[k] = '?';
                }
                if (MAXLEN[k] && String(x).length > MAXLEN[k]) e[k] = '≤ ' + MAXLEN[k];
            });
            if (!blank(v.isbn) && isbnCount[String(v.isbn).replace(/[\s-]/g, '').toUpperCase()] > 1) e.isbn = 'ISBN ×2';
            if (!blank(v.contractor) && blank(v.profit_percentage)) e.profit_percentage = '%';
            if (!blank(v.sub_category) && blank(v.category)) e.sub_category = '?';
            Object.keys(r.srv).forEach(function (k) { if (!e[k]) e[k] = r.srv[k]; });
        });
    };

    P.stats = function () {
        var self = this, s = { added: 0, changed: 0, deleted: 0, errors: 0, stockDirty: false };
        this.validateAll();
        this.rows.forEach(function (r) {
            if (r.isNew) { if (!self.isBlankNew(r) && !r.deleted) s.added++; }
            else if (r.deleted) s.deleted++;
            else { var d = self.dirtyKeys(r); if (d.length) s.changed++; if (d.indexOf('quantity') >= 0) s.stockDirty = true; }
            if (Object.keys(r.errors).length) s.errors++;
            if (r.isNew && Number(r.vals.quantity) > 0 && !r.deleted) s.stockDirty = true;
        });
        s.total = this.rows.length; s.shown = this.view.length; s.dirty = s.added + s.changed + s.deleted > 0;
        this.lastStats = s;
        if (this.hooks.onStats) this.hooks.onStats(s);
        this.saveDraftSoon();
        return s;
    };

    /* ── view: filter / sort ── */
    P.rebuildView = function () {
        var self = this, f = this.filter, q = (f.q || '').toLowerCase().trim(), cat = f.cat;
        var list = this.rows.filter(function (r) {
            if (f.mode === 'changed' && !self.isChanged(r)) return false;
            if (f.mode === 'new' && !(r.isNew && !self.isBlankNew(r))) return false;
            if (f.mode === 'deleted' && !(r.deleted && !r.isNew)) return false;
            if (f.mode === 'errors' && !Object.keys(r.errors).length) return false;
            if (cat && r.vals.category !== cat) return false;
            if (q) {
                var v = r.vals, hay = [v.name, v.isbn, v.authors, v.contractor, v.sku, v.book_id].join(' ').toLowerCase();
                if (hay.indexOf(q) < 0) return false;
            }
            return true;
        });
        var news = list.filter(function (r) { return r.isNew; }), olds = list.filter(function (r) { return !r.isNew; });
        if (this.sort) {
            var k = this.sort.key, dir = this.sort.dir, col = this.byKey[k], num = col && (col.type === 'number' || col.type === 'int' || col.type === 'readonly');
            olds.sort(function (a, b) {
                var x = a.vals[k], y = b.vals[k];
                if (blank(x) && blank(y)) return 0; if (blank(x)) return 1; if (blank(y)) return -1;
                var c = num && !isNaN(x) && !isNaN(y) ? Number(x) - Number(y) : String(x).localeCompare(String(y), undefined, { numeric: true });
                return c * dir;
            });
        }
        this.view = news.concat(olds);
        this.body.style.height = (this.view.length * ROW_H) + 'px';
        this.first = this.last = -1; this.sel = this.active ? this.sel : null;
        if (this.active && this.active.r >= this.view.length) this.active = null;
        this.schedule(true);
    };

    /* ── rendering ── */
    P.totalWidth = function () { return GUTTER_W + this.visCols().reduce(function (a, c) { return a + (c.width || 120); }, 0); };

    P.renderHeader = function () {
        var cols = this.visCols(), left = GUTTER_W, h = '<div class="bg-hc bg-g" style="width:' + GUTTER_W + 'px"><input type="checkbox" class="bg-all" aria-label="select all"></div>';
        for (var i = 0; i < cols.length; i++) {
            var c = cols[i], arrow = this.sort && this.sort.key === c.key ? (this.sort.dir > 0 ? ' ▲' : ' ▼') : '';
            h += '<div class="bg-hc' + (c.sticky ? ' bg-st' : '') + '" data-k="' + c.key + '" style="width:' + (c.width || 120) + 'px;' + (c.sticky ? 'inset-inline-start:' + GUTTER_W + 'px' : '') + '" title="' + esc(c.label) + '">'
                + '<span class="bg-ht">' + esc(c.label) + (c.required ? ' <i class="bg-req">*</i>' : '') + arrow + '</span><span class="bg-rs" data-k="' + c.key + '"></span></div>';
        }
        this.head.style.width = this.totalWidth() + 'px'; this.body.style.width = this.totalWidth() + 'px';
        this.head.innerHTML = h;
        var all = this.head.querySelector('.bg-all'), self = this;
        all.addEventListener('change', function () { self.view.forEach(function (r) { r.sel = all.checked; }); self.schedule(true); });
    };

    P.schedule = function (force) {
        var self = this; if (force) this.dirtyRender = true;
        if (this.raf) return;
        this.raf = requestAnimationFrame(function () { self.raf = null; self.render(); });
    };

    /* render right now (typing right after a move/add must find the cell in the DOM) */
    P.flush = function () {
        if (this.raf) { cancelAnimationFrame(this.raf); this.raf = null; }
        this.dirtyRender = true; this.render();
    };

    P.render = function () {
        var h = this.host.clientHeight || 600, top = this.host.scrollTop;
        var start = Math.max(0, Math.floor(top / ROW_H) - OVERSCAN), end = Math.min(this.view.length - 1, Math.ceil((top + h) / ROW_H) + OVERSCAN);
        if (!this.dirtyRender && start === this.first && end === this.last) return;
        this.first = start; this.last = end; this.dirtyRender = false;
        var cols = this.visCols(), out = '', i, j;
        for (i = start; i <= end; i++) out += this.rowHtml(i, cols);
        this.body.innerHTML = out;
        if (this.editor) this.positionEditor();
    };

    P.rowHtml = function (i, cols) {
        var r = this.view[i], v = r.vals, s = this.sel, a = this.active, w = this.totalWidth();
        var cls = 'bg-r' + (r.isNew ? ' bg-new' : '') + (r.deleted ? ' bg-del' : '') + (!r.isNew && !r.deleted && this.isChanged(r) ? ' bg-chg' : '') + (Object.keys(r.errors).length ? ' bg-bad' : '') + (r.sel ? ' bg-rsel' : '') + (i % 2 ? ' bg-odd' : '');
        var badge = r.isNew ? '<b class="bg-b bg-bn">' + esc(this.t('row_new')) + '</b>' : r.deleted ? '<b class="bg-b bg-bd">' + esc(this.t('row_deleted')) + '</b>' : '';
        var out = '<div class="' + cls + '" data-r="' + i + '" style="top:' + (i * ROW_H) + 'px;width:' + w + 'px;height:' + ROW_H + 'px">'
            + '<div class="bg-c bg-g" data-g="1" style="width:' + GUTTER_W + 'px"><input type="checkbox" class="bg-chk"' + (r.sel ? ' checked' : '') + '><span class="bg-n">' + (i + 1) + '</span>' + badge + '</div>';
        var dirty = r.isNew ? null : this.dirtyKeys(r);
        for (var j = 0; j < cols.length; j++) {
            var c = cols[j], k = c.key, x = v[k], c2 = 'bg-c';
            if (c.sticky) c2 += ' bg-st';
            if (c.readonly) c2 += ' bg-ro';
            if (c.type === 'number' || c.type === 'int' || c.type === 'readonly') c2 += ' bg-num';
            if (s && i >= s.r1 && i <= s.r2 && j >= s.c1 && j <= s.c2) c2 += ' bg-sel';
            if (a && a.r === i && a.c === j) c2 += ' bg-act';
            if (dirty ? dirty.indexOf(k) >= 0 : !blank(x)) c2 += ' bg-cc';
            var lockMsg = this.lockReason(r, c);
            if (lockMsg && !c.readonly) c2 += ' bg-lk';
            var err = r.errors[k]; if (err) c2 += ' bg-er';
            out += '<div class="' + c2 + '" data-c="' + j + '" style="width:' + (c.width || 120) + 'px;' + (c.sticky ? 'inset-inline-start:' + GUTTER_W + 'px' : '') + '"'
                + (err ? ' title="' + esc(err) + '"' : lockMsg ? ' title="' + esc(lockMsg) + '"' : '') + '>' + this.cellHtml(c, x) + '</div>';
        }
        return out + '</div>';
    };

    P.cellHtml = function (c, x) {
        if (blank(x)) return c.type === 'bool' ? '<span class="bg-bool"></span>' : '';
        if (c.type === 'bool') return '<span class="bg-bool on">✓</span>';
        if (c.type === 'select') { var o = (this.cfg.i18n.opt[c.key] || {})[x]; return esc(o || x); }
        if (c.type === 'number' && !isNaN(x)) return esc(Number(x).toLocaleString('en', { maximumFractionDigits: 2 }));
        return esc(x);
    };

    P.lockReason = function (r, c) {
        if (r.deleted) return this.t('locked_deleted');
        if (c.readonly) return this.t('locked_readonly');
        if (r.isNew) return null;
        if (c.key === 'sku' && r.locks.sku) return this.t('locked_sku');
        if (c.key === 'contractor' && r.locks.contractor) return this.t('locked_contractor');
        return null;
    };

    /* ── selection / mouse ── */
    P.cellAt = function (e) {
        var el = e.target.closest && e.target.closest('.bg-c'); if (!el) return null;
        var rowEl = el.parentNode; var r = Number(rowEl.getAttribute('data-r'));
        if (el.hasAttribute('data-g')) return { r: r, gutter: true, el: el };
        return { r: r, c: Number(el.getAttribute('data-c')), el: el };
    };
    P.setActive = function (r, c, extend) {
        var max = this.view.length - 1, cmax = this.visCols().length - 1;
        r = Math.max(0, Math.min(max, r)); c = Math.max(0, Math.min(cmax, c));
        if (extend && this.active) {
            this.sel = { r1: Math.min(this.active.r, r), r2: Math.max(this.active.r, r), c1: Math.min(this.active.c, c), c2: Math.max(this.active.c, c) };
            this.focusCell = { r: r, c: c };
        } else { this.active = { r: r, c: c }; this.sel = { r1: r, r2: r, c1: c, c2: c }; this.focusCell = { r: r, c: c }; }
        this.scrollTo(this.focusCell.r, this.focusCell.c); this.schedule(true);
        if (this.hooks.onSelection) { var n = (this.sel.r2 - this.sel.r1 + 1) * (this.sel.c2 - this.sel.c1 + 1); this.hooks.onSelection(n); }
    };
    P.scrollTo = function (r, c) {
        var top = r * ROW_H, h = this.host.clientHeight - HEAD_H, st = this.host.scrollTop;
        if (top < st) this.host.scrollTop = top; else if (top + ROW_H > st + h) this.host.scrollTop = top + ROW_H - h;
        var cols = this.visCols(), left = 0; for (var i = 0; i < c; i++) left += cols[i].width || 120;
        var w = cols[c] ? (cols[c].width || 120) : 0, view = this.host.clientWidth - GUTTER_W;
        var sl = Math.abs(this.host.scrollLeft), rtl = getComputedStyle(this.host).direction === 'rtl';
        if (!cols[c] || cols[c].sticky) return;
        if (left < sl) this.host.scrollLeft = rtl ? -left : left; else if (left + w > sl + view) { var nl = left + w - view; this.host.scrollLeft = rtl ? -nl : nl; }
    };
    P.onMouseDown = function (e) {
        if (e.target.closest('.bg-editor')) return;
        var t = this.cellAt(e); if (!t) return;
        if (this.editor) this.commit(false);
        if (t.gutter) {
            var row = this.view[t.r];
            if (e.target.classList.contains('bg-chk')) { row.sel = e.target.checked; this.schedule(true); return; }
            if (e.shiftKey && this.lastGutter != null) { var a = Math.min(this.lastGutter, t.r), b = Math.max(this.lastGutter, t.r); for (var i = a; i <= b; i++) this.view[i].sel = true; }
            else { row.sel = !row.sel; this.lastGutter = t.r; }
            this.schedule(true); return;
        }
        this.dragging = true; this.host.focus();
        this.setActive(t.r, t.c, e.shiftKey && !!this.active);
    };
    P.onMouseMove = function (e) {
        if (this.resizing) { this.doResize(e); return; }
        if (!this.dragging) return;
        var el = document.elementFromPoint(e.clientX, e.clientY); if (!el) return;
        var cell = el.closest && el.closest('.bg-c'); if (!cell || cell.hasAttribute('data-g') || !this.host.contains(cell)) return;
        var r = Number(cell.parentNode.getAttribute('data-r')), c = Number(cell.getAttribute('data-c'));
        if (!this.focusCell || this.focusCell.r !== r || this.focusCell.c !== c) this.setActive(r, c, true);
    };
    P.onDblClick = function (e) { var t = this.cellAt(e); if (t && !t.gutter) this.startEdit(); };

    P.onHeadClick = function (e) {
        if (e.target.classList.contains('bg-rs') || e.target.classList.contains('bg-all')) return;
        var h = e.target.closest('.bg-hc'); if (!h || !h.getAttribute('data-k')) return;
        var k = h.getAttribute('data-k');
        this.sort = !this.sort || this.sort.key !== k ? { key: k, dir: 1 } : (this.sort.dir === 1 ? { key: k, dir: -1 } : null);
        this.renderHeader(); this.rebuildView();
    };
    P.onResizeStart = function (e) {
        if (!e.target.classList.contains('bg-rs')) return;
        e.preventDefault(); var k = e.target.getAttribute('data-k'), col = this.byKey[k];
        this.resizing = { col: col, x: e.clientX, w: col.width || 120, rtl: getComputedStyle(this.host).direction === 'rtl' };
    };
    P.doResize = function (e) {
        var d = (e.clientX - this.resizing.x) * (this.resizing.rtl ? -1 : 1);
        this.resizing.col.width = Math.max(50, Math.min(700, this.resizing.w + d));
        this.renderHeader(); this.body.style.width = this.totalWidth() + 'px'; this.schedule(true);
    };

    /* ── cell access / editing ── */
    P.cellRef = function (r, c) {
        var row = this.view[r], col = this.visCols()[c]; return row && col ? { row: row, col: col } : null;
    };
    P.canEdit = function (row, col, quiet) {
        var why = this.lockReason(row, col);
        if (why) { if (!quiet && this.hooks.onToast) this.hooks.onToast(why, 'warn'); return false; }
        return true;
    };

    P.startEdit = function (initial) {
        if (!this.active) return;
        this.flush();
        var ref = this.cellRef(this.active.r, this.active.c); if (!ref || !this.canEdit(ref.row, ref.col)) return;
        var row = ref.row, col = ref.col;
        if (col.type === 'bool') { this.setCell(row, col.key, !row.vals[col.key]); this.afterChange(); return; }
        var inp, self = this;
        if (col.type === 'select') {
            inp = document.createElement('select');
            col.options.forEach(function (o) { var op = document.createElement('option'); op.value = o; op.textContent = (self.cfg.i18n.opt[col.key] || {})[o] || o; inp.appendChild(op); });
            inp.value = row.vals[col.key] == null ? col.options[0] : row.vals[col.key];
        } else {
            inp = document.createElement('input'); inp.type = 'text'; inp.autocomplete = 'off';
            inp.value = initial !== undefined ? initial : (blank(row.vals[col.key]) ? '' : String(row.vals[col.key]));
            if (col.type === 'combo') this.attachList(inp, row, col);
        }
        inp.className = 'bg-editor'; inp.setAttribute('dir', 'auto');
        document.body.appendChild(inp);
        this.editor = { el: inp, row: row, col: col, r: this.active.r, c: this.active.c };
        this.positionEditor(); inp.focus(); if (inp.select && initial === undefined) { try { inp.select(); } catch (x) { /* select */ } }
        inp.addEventListener('keydown', function (e) { self.onEditorKey(e); });
        inp.addEventListener('blur', function () { if (self.editor && self.editor.el === inp) self.commit(false); });
    };
    P.attachList = function (inp, row, col) {
        var id = 'bg-dl-' + col.key, dl = document.getElementById(id); if (!dl) { dl = document.createElement('datalist'); dl.id = id; document.body.appendChild(dl); }
        var items = [];
        if (col.list === 'categories') items = this.lists.categories || [];
        else if (col.list === 'contractors') items = (this.lists.contractors || []).map(function (c) { return c.name; });
        else if (col.list === 'sub_categories') items = row.vals.category && this.subMap[row.vals.category] ? this.subMap[row.vals.category] : (this.lists.sub_categories || []).map(function (s) { return s.name; });
        dl.innerHTML = items.map(function (n) { return '<option value="' + esc(n) + '">'; }).join('');
        inp.setAttribute('list', id);
    };
    P.positionEditor = function () {
        var ed = this.editor; if (!ed) return;
        var cell = this.body.querySelector('[data-r="' + ed.r + '"] [data-c="' + ed.c + '"]');
        if (!cell) { this.commit(false); return; }
        var b = cell.getBoundingClientRect();
        ed.el.style.left = b.left + 'px'; ed.el.style.top = b.top + 'px'; ed.el.style.width = Math.max(b.width, 90) + 'px'; ed.el.style.height = b.height + 'px';
    };
    P.onEditorKey = function (e) {
        e.stopPropagation();
        if (e.key === 'Enter') { e.preventDefault(); this.commit(true, 1, 0); }
        else if (e.key === 'Tab') { e.preventDefault(); this.commit(true, 0, e.shiftKey ? -1 : 1); }
        else if (e.key === 'Escape') { e.preventDefault(); this.cancelEdit(); }
    };
    P.cancelEdit = function () { var ed = this.editor; if (!ed) return; this.editor = null; ed.el.remove(); this.host.focus(); };
    P.commit = function (move, dr, dc) {
        var ed = this.editor; if (!ed) return; this.editor = null;
        var val = ed.el.value; ed.el.remove();
        this.setCell(ed.row, ed.col.key, this.coerce(ed.col, val));
        this.afterChange();
        this.host.focus();
        if (move && this.active) this.setActive(this.active.r + (dr || 0), this.active.c + (dc || 0));
    };

    /* convert typed / pasted text to the column's value */
    P.coerce = function (col, text) {
        if (text === null || text === undefined) return null;
        var s = String(text).replace(/[ ]/g, ' ');
        if (col.type !== 'longtext') s = s.replace(/\s+/g, ' ');
        s = s.trim();
        if (s === '') return col.type === 'bool' ? false : null;
        switch (col.type) {
            case 'int': case 'number': s = arabicDigits(s).replace(/\s/g, ''); if (col.type === 'number') s = s.replace(/٫/g, '.').replace(/,(\d{1,2})$/, '.$1').replace(/,/g, ''); return isNaN(s) ? s : Number(s);
            case 'date':
                s = arabicDigits(s).replace(/[\/.]/g, '-'); if (/^\d{4}$/.test(s)) return s + '-01-01';
                var m = s.match(/^(\d{1,2})-(\d{1,2})-(\d{4})$/); if (m) return m[3] + '-' + ('0' + m[2]).slice(-2) + '-' + ('0' + m[1]).slice(-2);
                m = s.match(/^(\d{4})-(\d{1,2})-(\d{1,2})$/); return m ? m[1] + '-' + ('0' + m[2]).slice(-2) + '-' + ('0' + m[3]).slice(-2) : s;
            case 'bool': return /^(1|true|yes|y|نعم|✓|x)$/i.test(s);
            case 'select':
                var low = s.toLowerCase(), lab = this.cfg.i18n.opt[col.key] || {}, hit = col.options.filter(function (o) { return o.toLowerCase() === low || String(lab[o] || '').toLowerCase() === low; });
                return hit.length ? hit[0] : s;
            default: return s;
        }
    };

    /* set a value (records undo, applies side effects) */
    P.setCell = function (row, key, value, group) {
        var col = this.byKey[key]; if (!col || col.readonly) return false;
        var old = row.vals[key]; if (same(old, value) && typeof old === typeof value) return false;
        if (same(old, value)) return false;
        var changes = group || []; var top = !group;
        this.applyVal(row, key, value, changes);
        if (key === 'contractor') {
            var info = value ? this.contractorInfo[String(value).toLowerCase()] : null;
            if (!value) { this.applyVal(row, 'nationality', null, changes); this.applyVal(row, 'profit_percentage', null, changes); this.applyVal(row, 'percentage_basis', null, changes); this.applyVal(row, 'gift_copies_given', null, changes); }
            else {
                if (info && info.nationality) this.applyVal(row, 'nationality', info.nationality, changes);
                if (blank(row.vals.profit_percentage)) this.applyVal(row, 'profit_percentage', DEFAULT_PCT, changes);
                if (blank(row.vals.percentage_basis)) this.applyVal(row, 'percentage_basis', 'sale_price', changes);
                if (blank(row.vals.gift_copies_given)) this.applyVal(row, 'gift_copies_given', 0, changes);
            }
        }
        if (key === 'category' && !blank(row.vals.sub_category)) {
            var ok = value && (this.subMap[value] || []).indexOf(row.vals.sub_category) >= 0;
            if (!ok) this.applyVal(row, 'sub_category', null, changes);
        }
        if (top) { this.undoStack.push(changes); if (this.undoStack.length > 300) this.undoStack.shift(); this.redoStack = []; }
        delete row.srv[key];
        return true;
    };
    P.applyVal = function (row, key, value, changes) {
        var old = row.vals[key]; if (same(old, value) && typeof old === typeof value) return;
        row.vals[key] = value; changes.push({ row: row, key: key, old: old, nv: value });
    };
    P.afterChange = function () { this.stats(); if (this.filter.mode !== 'all' || this.sort) this.rebuildView(); else this.schedule(true); };

    /* ── undo / redo ── */
    P.undo = function () {
        var g = this.undoStack.pop(); if (!g) return; var self = this;
        if (g.op) { g.undo(); } else g.slice().reverse().forEach(function (c) { c.row.vals[c.key] = c.old; });
        this.redoStack.push(g); this.rebuildView(); this.stats();
    };
    P.redo = function () {
        var g = this.redoStack.pop(); if (!g) return;
        if (g.op) { g.redo(); } else g.forEach(function (c) { c.row.vals[c.key] = c.nv; });
        this.undoStack.push(g); this.rebuildView(); this.stats();
    };
    P.canUndo = function () { return this.undoStack.length > 0; };
    P.canRedo = function () { return this.redoStack.length > 0; };

    /* ── keyboard ── */
    P.onKey = function (e) {
        if (this.editor) return;
        var k = e.key, ctrl = e.ctrlKey || e.metaKey;
        if (ctrl && !e.shiftKey && k.toLowerCase() === 'z') { e.preventDefault(); this.undo(); return; }
        if (ctrl && (k.toLowerCase() === 'y' || (e.shiftKey && k.toLowerCase() === 'z'))) { e.preventDefault(); this.redo(); return; }
        if (ctrl && k.toLowerCase() === 'd') { e.preventDefault(); this.fillDown(); return; }
        if (ctrl && k.toLowerCase() === 'a') { e.preventDefault(); this.sel = { r1: 0, r2: this.view.length - 1, c1: 0, c2: this.visCols().length - 1 }; if (!this.active) this.active = { r: 0, c: 0 }; this.schedule(true); return; }
        if (ctrl && ['c', 'v', 'x'].indexOf(k.toLowerCase()) >= 0) return; // handled by clipboard events
        if (!this.active) { if (this.view.length && ['ArrowDown', 'ArrowUp', 'ArrowLeft', 'ArrowRight', 'Enter'].indexOf(k) >= 0) { e.preventDefault(); this.setActive(0, 0); } return; }
        var a = this.focusCell || this.active, rtl = getComputedStyle(this.host).direction === 'rtl';
        var map = { ArrowDown: [1, 0], ArrowUp: [-1, 0], ArrowRight: [0, rtl ? -1 : 1], ArrowLeft: [0, rtl ? 1 : -1] };
        if (map[k]) { e.preventDefault(); this.setActive(a.r + map[k][0], a.c + map[k][1], e.shiftKey); return; }
        if (k === 'Tab') { e.preventDefault(); this.setActive(this.active.r, this.active.c + (e.shiftKey ? -1 : 1)); return; }
        if (k === 'PageDown') { e.preventDefault(); this.setActive(a.r + 15, a.c, e.shiftKey); return; }
        if (k === 'PageUp') { e.preventDefault(); this.setActive(a.r - 15, a.c, e.shiftKey); return; }
        if (k === 'Home') { e.preventDefault(); this.setActive(ctrl ? 0 : a.r, 0, e.shiftKey); return; }
        if (k === 'End') { e.preventDefault(); this.setActive(ctrl ? this.view.length - 1 : a.r, this.visCols().length - 1, e.shiftKey); return; }
        if (k === 'Enter' || k === 'F2') { e.preventDefault(); this.startEdit(); return; }
        if (k === ' ') { var ref = this.cellRef(this.active.r, this.active.c); if (ref && ref.col.type === 'bool') { e.preventDefault(); this.startEdit(); return; } }
        if (k === 'Delete' || k === 'Backspace') { e.preventDefault(); this.clearSelection(); return; }
        if (!ctrl && !e.altKey && k.length === 1) { e.preventDefault(); this.startEdit(k); }
    };

    P.eachSelected = function (fn) {
        var s = this.sel; if (!s) return; var cols = this.visCols();
        for (var r = s.r1; r <= s.r2; r++) for (var c = s.c1; c <= s.c2; c++) { var row = this.view[r], col = cols[c]; if (row && col) fn(row, col, r, c); }
    };
    P.clearSelection = function () {
        var group = [], self = this, blocked = false;
        this.eachSelected(function (row, col) {
            if (col.readonly || !self.canEdit(row, col, true)) { blocked = true; return; }
            self.setCell(row, col.key, col.type === 'bool' ? false : null, group);
        });
        if (group.length) { this.undoStack.push(group); this.redoStack = []; }
        if (blocked && this.hooks.onToast) this.hooks.onToast(this.t('locked_readonly'), 'warn');
        this.afterChange();
    };
    P.fillDown = function () {
        var s = this.sel; if (!s || s.r1 === s.r2) return; var group = [], self = this;
        for (var c = s.c1; c <= s.c2; c++) {
            var col = this.visCols()[c], src = this.view[s.r1]; if (!col || col.readonly) continue;
            for (var r = s.r1 + 1; r <= s.r2; r++) { var row = this.view[r]; if (this.canEdit(row, col, true)) this.setCell(row, col.key, src.vals[col.key], group); }
        }
        if (group.length) { this.undoStack.push(group); this.redoStack = []; }
        this.afterChange();
    };

    /* ── clipboard ── */
    P.onCopy = function (e, cut) {
        if (this.editor || !this.sel) return;
        var lines = [], s = this.sel, cols = this.visCols();
        for (var r = s.r1; r <= s.r2; r++) {
            var cells = [];
            for (var c = s.c1; c <= s.c2; c++) {
                var x = this.view[r].vals[cols[c].key];
                if (cols[c].type === 'bool') x = x ? 'Yes' : 'No';
                x = blank(x) ? '' : String(x);
                cells.push(/[\t\n"]/.test(x) ? '"' + x.replace(/"/g, '""') + '"' : x);
            }
            lines.push(cells.join('\t'));
        }
        e.clipboardData.setData('text/plain', lines.join('\n')); e.preventDefault();
        if (cut) this.clearSelection();
    };
    P.parseTsv = function (text) {
        var rows = [], row = [], cell = '', q = false, i, ch;
        text = text.replace(/\r\n?/g, '\n');
        for (i = 0; i < text.length; i++) {
            ch = text[i];
            if (q) { if (ch === '"') { if (text[i + 1] === '"') { cell += '"'; i++; } else q = false; } else cell += ch; }
            else if (ch === '"' && cell === '') q = true;
            else if (ch === '\t') { row.push(cell); cell = ''; }
            else if (ch === '\n') { row.push(cell); rows.push(row); row = []; cell = ''; }
            else cell += ch;
        }
        if (cell !== '' || row.length) { row.push(cell); rows.push(row); }
        return rows;
    };
    P.onPaste = function (e) {
        if (this.editor || !this.active) return;
        e.preventDefault();
        var text = (e.clipboardData || window.clipboardData).getData('text'); if (!text) return;
        var grid = this.parseTsv(text), cols = this.visCols(), group = [], self = this, n = 0, over = 0;
        var s = this.sel, single = grid.length === 1 && grid[0].length === 1;
        if (single && s && (s.r1 !== s.r2 || s.c1 !== s.c2)) {
            this.eachSelected(function (row, col) { if (!col.readonly && self.canEdit(row, col, true)) { if (self.setCell(row, col.key, self.coerce(col, grid[0][0]), group)) n++; } });
        } else {
            for (var i = 0; i < grid.length; i++) {
                var r = this.active.r + i;
                if (r >= this.view.length) { over++; continue; }
                for (var j = 0; j < grid[i].length; j++) {
                    var c = this.active.c + j; if (c >= cols.length) break;
                    var row = this.view[r], col = cols[c];
                    if (col.readonly || !this.canEdit(row, col, true)) continue;
                    if (this.setCell(row, col.key, this.coerce(col, grid[i][j]), group)) n++;
                }
            }
        }
        if (group.length) { this.undoStack.push(group); this.redoStack = []; }
        this.afterChange();
        if (this.hooks.onToast) { this.hooks.onToast(this.t('pasted', { n: n }) + (over ? ' ' + this.t('paste_overflow', { n: over }) : ''), over ? 'warn' : 'ok'); }
    };

    /* ── row operations ── */
    P.addRows = function (n) {
        n = Math.max(1, Math.min(200, n | 0)); var self = this, added = [];
        for (var i = 0; i < n; i++) added.push(this.newRow());
        this.rows = added.concat(this.rows);
        this.undoStack.push({ op: true, undo: function () { self.rows = self.rows.filter(function (r) { return added.indexOf(r) < 0; }); }, redo: function () { self.rows = added.concat(self.rows); } });
        this.redoStack = []; this.filter.mode = 'all'; this.filter.q = ''; this.filter.cat = '';
        this.rebuildView(); this.stats(); this.host.scrollTop = 0; this.host.focus();
        var nameIdx = this.visCols().findIndex(function (c) { return c.key === 'name'; });
        this.setActive(0, Math.max(0, nameIdx));
        if (this.hooks.onFilterReset) this.hooks.onFilterReset();
    };
    P.selectedRows = function () {
        var picked = this.view.filter(function (r) { return r.sel; });
        if (picked.length) return picked;
        if (this.sel) { var out = []; for (var r = this.sel.r1; r <= this.sel.r2; r++) if (this.view[r]) out.push(this.view[r]); return out; }
        return [];
    };
    P.deleteSelected = function () {
        var self = this, rows = this.selectedRows(), refused = 0, doRows = [];
        rows.forEach(function (r) { if (!r.isNew && r.locks.delete === 'used') refused++; else if (!r.deleted) doRows.push(r); });
        if (!doRows.length) { if (refused && this.hooks.onToast) this.hooks.onToast(this.t('delete_used', { n: refused }), 'warn'); return; }
        var removedNew = doRows.filter(function (r) { return r.isNew; }), marked = doRows.filter(function (r) { return !r.isNew; });
        marked.forEach(function (r) { r.deleted = true; });
        this.rows = this.rows.filter(function (r) { return removedNew.indexOf(r) < 0; });
        this.undoStack.push({ op: true,
            undo: function () { marked.forEach(function (r) { r.deleted = false; }); self.rows = removedNew.concat(self.rows); },
            redo: function () { marked.forEach(function (r) { r.deleted = true; }); self.rows = self.rows.filter(function (r) { return removedNew.indexOf(r) < 0; }); } });
        this.redoStack = []; this.view.forEach(function (r) { r.sel = false; });
        this.rebuildView(); this.stats();
        if (refused && this.hooks.onToast) this.hooks.onToast(this.t('delete_used', { n: refused }), 'warn');
    };
    P.restoreSelected = function () {
        var self = this, rows = this.selectedRows().filter(function (r) { return r.deleted; }); if (!rows.length) return;
        rows.forEach(function (r) { r.deleted = false; });
        this.undoStack.push({ op: true, undo: function () { rows.forEach(function (r) { r.deleted = true; }); }, redo: function () { rows.forEach(function (r) { r.deleted = false; }); } });
        this.redoStack = []; this.view.forEach(function (r) { r.sel = false; }); this.rebuildView(); this.stats();
    };
    P.discardAll = function () {
        var self = this;
        this.rows = this.rows.filter(function (r) { return !r.isNew; });
        this.rows.forEach(function (r) { r.vals = clone(r.orig); r.deleted = false; r.srv = {}; r.sel = false; });
        this.undoStack = []; this.redoStack = []; this.rebuildView(); this.stats(); this.clearDraft();
    };

    P.setHiddenGroups = function (g) { this.hiddenGroups = g; this.active = null; this.sel = null; this.renderHeader(); this.rebuildView(); };
    P.setFilter = function (f) { this.filter = Object.assign({}, this.filter, f); this.active = null; this.sel = null; this.rebuildView(); };

    P.hasStockEdits = function () { return !!(this.lastStats && this.lastStats.stockDirty); };

    /* ── save payload & server error mapping ── */
    P.buildPayload = function () {
        var self = this, rows = [], deleted = [], edit = this.editableCols();
        this.rows.forEach(function (r) {
            if (r.isNew) { if (r.deleted || self.isBlankNew(r)) return; }
            else if (r.deleted) { deleted.push(r.book_id); return; }
            else if (!self.dirtyKeys(r).length) return;
            var fields = {}; edit.forEach(function (c) { fields[c.key] = r.vals[c.key]; });
            rows.push({ key: r.key, book_id: r.book_id, v: r.v, q0: r.q0, fields: fields });
        });
        return { warehouse_id: this.warehouseId, rows: rows, deleted: deleted };
    };
    P.applyServerErrors = function (errors) {
        var byKey = {}; this.rows.forEach(function (r) { byKey[r.key] = r; r.srv = {}; });
        (errors || []).forEach(function (e) { var r = byKey[e.row]; if (r) r.srv[e.field || 'name'] = e.message; });
        this.stats(); this.schedule(true);
    };
    P.goTo = function (rowKey, field) {
        var self = this, idx = this.view.findIndex(function (r) { return r.key === rowKey; });
        if (idx < 0) { this.filter = { mode: 'all', q: '', cat: '' }; if (this.hooks.onFilterReset) this.hooks.onFilterReset(); this.rebuildView(); idx = this.view.findIndex(function (r) { return r.key === rowKey; }); }
        if (idx < 0) return;
        var c = this.visCols().findIndex(function (x) { return x.key === (field || 'name'); }); if (c < 0) c = 0;
        this.host.focus(); this.setActive(idx, c);
    };
    P.findRow = function (key) { return this.rows.filter(function (r) { return r.key === key; })[0]; };

    /* ── draft autosave (survives refresh) ── */
    P.draftKey = function () { return 'bookgrid:draft:v1:' + this.cfg.userKey; };
    P.saveDraftSoon = function () {
        var self = this; clearTimeout(this.dt);
        this.dt = setTimeout(function () {
            try {
                var s = self.lastStats; if (!s || !s.dirty) { localStorage.removeItem(self.draftKey()); return; }
                var edits = [], news = [], deleted = [];
                self.rows.forEach(function (r) {
                    if (r.isNew) { if (!self.isBlankNew(r) && !r.deleted) news.push({ vals: r.vals }); return; }
                    if (r.deleted) { deleted.push(r.key); return; }
                    var d = self.dirtyKeys(r); if (d.length) { var f = {}; d.forEach(function (k) { f[k] = r.vals[k]; }); edits.push({ key: r.key, v: r.v, f: f }); }
                });
                localStorage.setItem(self.draftKey(), JSON.stringify({ t: Date.now(), wh: self.warehouseId, edits: edits, news: news, deleted: deleted }));
            } catch (x) { /* storage unavailable */ }
        }, 600);
    };
    P.readDraft = function () { try { var d = JSON.parse(localStorage.getItem(this.draftKey()) || 'null'); return d && (d.edits.length + d.news.length + d.deleted.length) ? d : null; } catch (x) { return null; } };
    P.clearDraft = function () { try { localStorage.removeItem(this.draftKey()); } catch (x) { /* storage unavailable */ } };
    P.restoreDraft = function (d) {
        var self = this, ok = 0, skipped = 0;
        d.edits.forEach(function (e) { var r = self.findRow(e.key); if (!r || r.v !== e.v) { skipped++; return; } Object.keys(e.f).forEach(function (k) { r.vals[k] = e.f[k]; }); ok++; });
        d.deleted.forEach(function (key) { var r = self.findRow(key); if (r && r.locks.delete !== 'used') { r.deleted = true; ok++; } else skipped++; });
        var news = d.news.map(function (n) { var r = self.newRow(); Object.keys(n.vals).forEach(function (k) { if (k in r.vals) r.vals[k] = n.vals[k]; }); ok++; return r; });
        this.rows = news.concat(this.rows); this.rebuildView(); this.stats();
        return { ok: ok, skipped: skipped };
    };

    /* ───────────────────────── Alpine component ───────────────────────── */
    window.bookGrid = function (config) {
        var engine = null; // kept out of Alpine's reactive proxy on purpose
        var HIDE_KEY = 'bookgrid:groups:v1';
        return {
            T: config.i18n.ui, G: config.i18n.groups, loading: true, error: null, ready: false,
            stats: { added: 0, changed: 0, deleted: 0, errors: 0, total: 0, shown: 0, dirty: false, stockDirty: false },
            filterMode: 'all', q: '', cat: '', warehouseId: null, warehouses: [], lists: { categories: [] }, groups: [], hidden: {}, colMenu: false,
            canUndo: false, canRedo: false, selCount: 0, toast: null, draft: null,
            review: { open: false, loading: false, error: null, plan: null, token: null, confirmText: '', ack: false, saving: false, done: false, fatal: null, open2: {} },

            init: function () {
                var self = this;
                try { this.hidden = JSON.parse(localStorage.getItem(HIDE_KEY)) || { system: true, translation: true }; } catch (e) { this.hidden = { system: true, translation: true }; }
                engine = new Engine(this.$refs.grid, config, {
                    onStats: function (s) { self.stats = s; self.canUndo = engine.canUndo(); self.canRedo = engine.canRedo(); },
                    onSelection: function (n) { self.selCount = n; },
                    onToast: function (m, t) { self.say(m, t); },
                    onFilterReset: function () { self.filterMode = 'all'; self.q = ''; self.cat = ''; },
                });
                engine.hiddenGroups = this.hidden;
                window.addEventListener('beforeunload', function (e) { if (engine && engine.lastStats && engine.lastStats.dirty && !self.review.done) { e.preventDefault(); e.returnValue = self.T.leave_warning; } });
                this.$watch('q', function (v) { engine.setFilter({ q: v }); self.stats = engine.stats(); });
                this.load();
                window.__bookGrid = { engine: function () { return engine; } };
            },
            say: function (m, type) { var self = this; this.toast = { m: m, type: type || 'ok' }; clearTimeout(this._tt); this._tt = setTimeout(function () { self.toast = null; }, 4500); },

            load: function (warehouseId) {
                var self = this; this.loading = true; this.error = null;
                var url = config.dataUrl + (warehouseId ? (config.dataUrl.indexOf('?') < 0 ? '?' : '&') + 'warehouse_id=' + warehouseId : '');
                return fetch(url, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' })
                    .then(function (r) { if (!r.ok) throw new Error('HTTP ' + r.status); return r.json(); })
                    .then(function (snap) {
                        self.warehouses = snap.warehouses; self.warehouseId = snap.warehouse_id; self.lists = snap.lists;
                        var seen = {}; self.groups = []; snap.columns.forEach(function (c) { if (c.group && !seen[c.group]) { seen[c.group] = 1; self.groups.push(c.group); } });
                        engine.hiddenGroups = self.hidden; engine.load(snap); self.loading = false; self.ready = true;
                        self.draft = engine.readDraft();
                    })
                    .catch(function (e) { self.loading = false; self.error = self.T.load_failed + ' (' + e.message + ')'; });
            },
            reload: function () { this.load(this.warehouseId); },

            setFilterMode: function (m) { this.filterMode = m; engine.setFilter({ mode: m }); this.stats = engine.stats(); },
            setCat: function () { engine.setFilter({ cat: this.cat }); this.stats = engine.stats(); },
            changeWarehouse: function () {
                if (engine.hasStockEdits()) { this.say(this.T.warehouse_locked, 'warn'); this.warehouseId = engine.warehouseId; return; }
                this.load(this.warehouseId);
            },
            toggleGroup: function (g) { var h = Object.assign({}, this.hidden); h[g] = !h[g]; this.hidden = h; try { localStorage.setItem(HIDE_KEY, JSON.stringify(h)); } catch (e) { /* storage unavailable */ } engine.setHiddenGroups(h); },

            addBook: function () { engine.addRows(1); },
            addRows: function () { var n = parseInt(window.prompt(this.T.add_rows_prompt, '10'), 10); if (n > 0) engine.addRows(n); },
            deleteRows: function () { engine.deleteSelected(); },
            restoreRows: function () { engine.restoreSelected(); },
            undo: function () { engine.undo(); }, redo: function () { engine.redo(); },
            discard: function () { if (window.confirm(this.T.confirm_discard)) { engine.discardAll(); this.draft = null; } },
            restoreDraft: function () { var r = engine.restoreDraft(this.draft); this.draft = null; this.say(this.T.draft_restored.replace(':n', r.ok).replace(':skipped', r.skipped), r.skipped ? 'warn' : 'ok'); },
            dropDraft: function () { engine.clearDraft(); this.draft = null; },

            /* ── review & save ── */
            openReview: function () {
                var self = this, rv = this.review; var payload = engine.buildPayload();
                Object.assign(rv, { open: true, loading: true, error: null, plan: null, token: null, confirmText: '', ack: false, saving: false, done: false, fatal: null, open2: {} });
                this.post(config.previewUrl, payload).then(function (res) {
                    rv.loading = false;
                    if (!res.ok && !res.json) { rv.error = self.T.review_failed; return; }
                    rv.plan = res.json; rv.token = res.json.token; engine.applyServerErrors(res.json.errors);
                }).catch(function (e) { rv.loading = false; rv.error = self.T.review_failed + ' ' + e.message; });
            },
            closeReview: function () { if (!this.review.saving) this.review.open = false; },
            canSave: function () {
                var rv = this.review, p = rv.plan; if (!p || rv.loading || rv.saving || !p.ok) return false;
                if (p.totals.added + p.totals.changed + p.totals.deleted === 0) return false;
                if (p.totals.deleted > 0 && String(rv.confirmText).trim() !== String(p.totals.deleted)) return false;
                if (p.stock_units_destroyed > 0 && !rv.ack) return false;
                return true;
            },
            save: function () {
                var self = this, rv = this.review; if (!this.canSave()) return; rv.saving = true; rv.fatal = null;
                this.post(config.applyUrl, { token: rv.token, ack_stock: rv.ack, confirm_delete_count: rv.plan.totals.deleted ? parseInt(rv.confirmText, 10) : null })
                    .then(function (res) {
                        rv.saving = false;
                        if (res.ok) { rv.done = true; rv.result = res.json; engine.clearDraft(); self.reload(); return; }
                        var j = res.json || {};
                        rv.fatal = j.fatal || j.message || self.T.save_failed;
                        if (j.errors) { rv.plan = Object.assign({}, rv.plan, { ok: false, errors: j.errors }); engine.applyServerErrors(j.errors); }
                    }).catch(function (e) { rv.saving = false; rv.fatal = e.message; });
            },
            post: function (url, body) {
                var tok = document.querySelector('meta[name="csrf-token"]');
                return fetch(url, { method: 'POST', credentials: 'same-origin',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': tok ? tok.content : '' },
                    body: JSON.stringify(body) })
                    .then(function (r) { return r.json().catch(function () { return null; }).then(function (j) { return { ok: r.ok, status: r.status, json: j }; }); });
            },
            goTo: function (e) { this.review.open = false; var self = this; setTimeout(function () { engine.goTo(e.row, e.field); }, 50); },
            fmt: function (v) { return v === null || v === undefined || v === '' ? this.T.empty_value : (v === true ? '✓' : v === false ? '✗' : String(v)); },
            label: function (k) { var c = engine && engine.byKey ? engine.byKey[k] : null; return c ? c.label : k; },
            opt: function (k, v) { return ((config.i18n.opt || {})[k] || {})[v] || v; },
            groupLabel: function (g) { return this.G[g] || g; },
            tx: function (key, p) { var s = this.T[key] || key; for (var x in (p || {})) s = s.replace(':' + x, p[x]); return s; },
        };
    };
    window.BookGridEngine = Engine;
})();
