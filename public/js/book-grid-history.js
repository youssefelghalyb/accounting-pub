/* Books grid history: list of saved batches, details, and restore (revert) with preview + strict confirmations. */
(function () {
    'use strict';
    window.bookGridHistory = function (config) {
        var blankRs = function () {
            return { open: false, batch: null, loading: false, error: null, plan: null, conflicts: [], notes: [], token: null, skip: false,
                confirmText: '', ack: false, saving: false, done: false };
        };
        return {
            UI: config.i18n.ui, H: config.i18n.h, col: config.i18n.col,
            rows: [], page: 1, last: 1, term: '', loading: false,
            details: { open: false, loading: false, meta: null, data: null },
            rs: blankRs(),

            init: function () { this.load(); },
            tx: function (s, n, key) { return String(s).replace(':' + (key || 'n'), n); },
            fmt: function (v) { return v === null || v === undefined || v === '' ? this.UI.empty_value : String(v); },
            fmtDate: function (s) { if (!s) return ''; var d = new Date(String(s).replace(' ', 'T') + 'Z'); return isNaN(d) ? s : d.toLocaleString(); },
            req: function (url, opts) {
                var tok = document.querySelector('meta[name="csrf-token"]');
                opts = Object.assign({ credentials: 'same-origin', headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': tok ? tok.content : '' } }, opts || {});
                return fetch(url, opts).then(function (r) { return r.json().catch(function () { return null; }).then(function (j) { return { ok: r.ok, status: r.status, json: j }; }); });
            },

            load: function () {
                var self = this; this.loading = true;
                var u = config.listUrl + '&page=' + this.page + (this.term ? '&book=' + encodeURIComponent(this.term) : '');
                this.req(u).then(function (r) { self.loading = false; if (r.ok) { self.rows = r.json.data; self.last = r.json.last_page || 1; } });
            },

            openDetails: function (b) {
                var self = this; this.details = { open: true, loading: true, meta: b, data: null };
                this.req(config.showUrl.replace('__ID__', b.batch_uuid)).then(function (r) { self.details.loading = false; if (r.ok) self.details.data = r.json; });
            },

            openRestore: function (b) { this.rs = Object.assign(blankRs(), { open: true, batch: b }); this.preview(); },
            closeRestore: function () { if (this.rs.saving) return; var done = this.rs.done; this.rs.open = false; if (done) this.load(); },
            preview: function () {
                var self = this, rs = this.rs; rs.loading = true; rs.error = null; rs.confirmText = ''; rs.ack = false;
                this.req(config.previewUrl.replace('__ID__', rs.batch.batch_uuid), { method: 'POST', body: JSON.stringify({ skip_conflicts: rs.skip }) }).then(function (r) {
                    rs.loading = false;
                    if (!r.ok && (!r.json || !r.json.totals)) { rs.error = (r.json && (r.json.fatal || r.json.message)) || self.H.not_found; rs.plan = null; return; }
                    rs.plan = r.json; rs.token = r.json.token; rs.conflicts = r.json.conflicts || []; rs.notes = r.json.notes || [];
                });
            },
            canRestore: function () {
                var rs = this.rs, p = rs.plan; if (!p || rs.loading || rs.saving || rs.done || p.errors.length) return false;
                if (rs.conflicts.length && !rs.skip) return false; // all-or-nothing unless the user chose to skip the conflicts
                if (p.totals.added + p.totals.changed + p.totals.deleted === 0) return false;
                if (p.totals.deleted > 0 && String(rs.confirmText).trim() !== String(p.totals.deleted)) return false;
                if (p.stock_units_destroyed > 0 && !rs.ack) return false;
                return true;
            },
            applyRestore: function () {
                var self = this, rs = this.rs; if (!this.canRestore()) return; rs.saving = true; rs.error = null;
                this.req(config.applyUrl, { method: 'POST', body: JSON.stringify({ token: rs.token, ack_stock: rs.ack, confirm_delete_count: rs.plan.totals.deleted ? parseInt(rs.confirmText, 10) : null }) }).then(function (r) {
                    rs.saving = false;
                    if (r.ok) { rs.done = true; return; }
                    rs.error = (r.json && (r.json.fatal || r.json.message)) || self.UI.save_failed;
                });
            },
        };
    };
})();
