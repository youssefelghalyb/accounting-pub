{{-- Books grid editor markup. Plain HTML + Alpine (strings come from JS config.i18n so this file has no PHP). --}}
<style>
    .bgx { --bg-line:#e5e7eb; --bg-acc:#4f46e5; }
    .bgx .bg-scroll { position:relative; overflow:auto; height:calc(100vh - 330px); min-height:420px; background:#fff; outline:none; border:1px solid var(--bg-line); border-radius:14px; }
    .bgx .bg-inner { position:relative; min-width:100%; }
    .bgx .bg-head { position:sticky; top:0; z-index:30; display:flex; height:40px; background:#f8fafc; border-bottom:1px solid var(--bg-line); }
    .bgx .bg-hc { position:relative; flex:none; display:flex; align-items:center; padding:0 10px; font-size:11px; font-weight:600; letter-spacing:.02em; color:#475569; border-inline-end:1px solid var(--bg-line); cursor:pointer; user-select:none; background:#f8fafc; white-space:nowrap; overflow:hidden; }
    .bgx .bg-hc:hover { background:#eef2ff; color:#3730a3; }
    .bgx .bg-ht { overflow:hidden; text-overflow:ellipsis; }
    .bgx .bg-req { color:#e11d48; font-style:normal; }
    .bgx .bg-rs { position:absolute; inset-block:0; inset-inline-end:0; width:6px; cursor:col-resize; }
    .bgx .bg-rs:hover { background:#a5b4fc; }
    .bgx .bg-body { position:relative; }
    .bgx .bg-r { --rb:#fff; position:absolute; inset-inline-start:0; display:flex; background:var(--rb); border-bottom:1px solid #f1f5f9; }
    .bgx .bg-r.bg-odd { --rb:#fcfcfd; }
    .bgx .bg-r:hover { --rb:#f5f7ff; }
    .bgx .bg-r.bg-new { --rb:#f0fdf4; } .bgx .bg-r.bg-del { --rb:#fef2f2; } .bgx .bg-r.bg-rsel { --rb:#eef2ff; }
    .bgx .bg-r.bg-del .bg-c { text-decoration:line-through; color:#9ca3af; }
    .bgx .bg-c { flex:none; box-sizing:border-box; padding:0 10px; display:flex; align-items:center; font-size:13px; color:#1f2937; border-inline-end:1px solid #f1f5f9; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; cursor:cell; position:relative; background:inherit; }
    .bgx .bg-c.bg-num { direction:ltr; justify-content:flex-end; font-variant-numeric:tabular-nums; }
    .bgx .bg-c.bg-ro { color:#6b7280; background:#f9fafb; cursor:default; }
    .bgx .bg-c.bg-cc { background:#fffbeb; box-shadow:inset 3px 0 0 #f59e0b; }
    .bgx .bg-r.bg-new .bg-c.bg-cc { background:#ecfdf5; box-shadow:inset 3px 0 0 #10b981; }
    .bgx .bg-c.bg-lk { background-image:repeating-linear-gradient(135deg,transparent 0 6px,rgba(148,163,184,.14) 6px 12px); cursor:not-allowed; }
    .bgx .bg-c.bg-sel { background:#e0e7ff !important; }
    .bgx .bg-c.bg-act { box-shadow:inset 0 0 0 2px var(--bg-acc); background:#fff !important; z-index:2; }
    .bgx .bg-c.bg-er { box-shadow:inset 0 0 0 2px #e11d48; background:#fff1f2 !important; }
    .bgx .bg-st, .bgx .bg-hc.bg-st { position:sticky; z-index:20; background:var(--rb,#f8fafc); border-inline-end:2px solid #d1d5db; }
    .bgx .bg-hc.bg-st { background:#f8fafc; z-index:35; }
    .bgx .bg-g { position:sticky; inset-inline-start:0; z-index:25; background:var(--rb,#f8fafc); gap:6px; padding:0 6px; justify-content:flex-start; cursor:default; border-inline-end:2px solid #d1d5db; }
    .bgx .bg-hc.bg-g { z-index:40; background:#f8fafc; }
    .bgx .bg-n { font-size:11px; color:#94a3b8; min-width:20px; text-align:end; }
    .bgx .bg-b { font-size:9px; padding:1px 4px; border-radius:6px; font-weight:700; letter-spacing:.04em; }
    .bgx .bg-bn { background:#d1fae5; color:#047857; } .bgx .bg-bd { background:#fee2e2; color:#b91c1c; }
    .bgx .bg-bool { width:16px; height:16px; border:1.5px solid #cbd5e1; border-radius:5px; display:inline-flex; align-items:center; justify-content:center; font-size:11px; line-height:1; color:#fff; }
    .bgx .bg-bool.on { background:var(--bg-acc); border-color:var(--bg-acc); }
    .bg-editor { position:fixed; z-index:100; box-sizing:border-box; padding:0 9px; font-size:13px; border:2px solid #4f46e5; border-radius:4px; background:#fff; color:#111827; outline:none; box-shadow:0 8px 24px rgba(79,70,229,.25); }
    .bgx .chip { padding:5px 12px; border-radius:999px; font-size:12px; font-weight:600; border:1px solid #e5e7eb; background:#fff; color:#475569; cursor:pointer; transition:.15s; }
    .bgx .chip:hover { border-color:#a5b4fc; color:#3730a3; }
    .bgx .chip.on { background:#4f46e5; border-color:#4f46e5; color:#fff; }
    .bgx .btn { display:inline-flex; align-items:center; gap:6px; padding:7px 12px; border-radius:10px; font-size:13px; font-weight:600; border:1px solid #e5e7eb; background:#fff; color:#334155; cursor:pointer; transition:.15s; }
    .bgx .btn:hover:not(:disabled) { background:#f8fafc; border-color:#c7d2fe; }
    .bgx .btn:disabled { opacity:.4; cursor:not-allowed; }
    .bgx .btn-pri { background:#4f46e5; border-color:#4f46e5; color:#fff; } .bgx .btn-pri:hover:not(:disabled) { background:#4338ca; border-color:#4338ca; }
    .bgx .btn-red { background:#fff; border-color:#fecaca; color:#b91c1c; } .bgx .btn-red:hover:not(:disabled) { background:#fef2f2; }
    .bgx .inp { padding:7px 12px; border:1px solid #e5e7eb; border-radius:10px; font-size:13px; background:#fff; outline:none; } .bgx .inp:focus { border-color:#6366f1; box-shadow:0 0 0 3px rgba(99,102,241,.15); }
    .bgx .tile { border-radius:14px; padding:14px 16px; border:1px solid; } .bgx .tile b { display:block; font-size:26px; line-height:1.1; }
    .bgx .chg-row td { padding:4px 8px; font-size:12px; vertical-align:top; border-bottom:1px solid #f1f5f9; }
    [x-cloak] { display:none !important; }
</style>

<div class="bgx" x-data="bookGrid(window.__BOOK_GRID_CONFIG)" x-cloak>
    {{-- toolbar --}}
    <div class="mb-3 flex flex-wrap items-center gap-2">
        <button type="button" class="btn btn-pri" x-on:click="addBook()" data-t="add-book">＋ <span x-text="T.add_book"></span></button>
        <button type="button" class="btn" x-on:click="addRows()"><span x-text="T.add_rows"></span></button>
        <button type="button" class="btn btn-red" x-on:click="deleteRows()" data-t="delete-rows"><span x-text="T.delete_rows"></span></button>
        <button type="button" class="btn" x-on:click="restoreRows()"><span x-text="T.restore_rows"></span></button>
        <span class="mx-1 h-6 w-px bg-gray-200"></span>
        <button type="button" class="btn" x-on:click="undo()" x-bind:disabled="!canUndo" data-t="undo">↶ <span x-text="T.undo"></span></button>
        <button type="button" class="btn" x-on:click="redo()" x-bind:disabled="!canRedo">↷ <span x-text="T.redo"></span></button>
        <div class="relative">
            <button type="button" class="btn" x-on:click="colMenu = !colMenu"><span x-text="T.columns"></span> ▾</button>
            <div x-show="colMenu" x-on:click.outside="colMenu = false" class="absolute z-50 mt-1 w-56 rounded-xl border border-gray-200 bg-white p-2 shadow-xl" style="inset-inline-start:0">
                <template x-for="g in groups" x-bind:key="g">
                    <label class="flex cursor-pointer items-center gap-2 rounded-lg px-2 py-1.5 text-sm hover:bg-gray-50">
                        <input type="checkbox" x-bind:checked="!hidden[g]" x-on:change="toggleGroup(g)"> <span x-text="groupLabel(g)"></span>
                    </label>
                </template>
            </div>
        </div>
        <div class="flex items-center gap-2" x-show="warehouses.length">
            <label class="text-xs font-semibold text-gray-500" x-text="T.warehouse"></label>
            <select class="inp" x-model.number="warehouseId" x-on:change="changeWarehouse()">
                <template x-for="w in warehouses" x-bind:key="w.id"><option x-bind:value="w.id" x-text="w.name"></option></template>
            </select>
        </div>
        <div class="flex-1"></div>
        <button type="button" class="btn btn-red" x-on:click="discard()" x-show="stats.dirty"><span x-text="T.discard"></span></button>
        <button type="button" class="btn btn-pri" x-on:click="openReview()" x-bind:disabled="!stats.dirty" data-t="review">
            <span x-text="T.review_save"></span>
            <span class="rounded-full bg-white/25 px-2 text-xs" x-show="stats.dirty" x-text="stats.added + stats.changed + stats.deleted"></span>
        </button>
    </div>

    {{-- filters --}}
    <div class="mb-3 flex flex-wrap items-center gap-2">
        <input type="search" class="inp w-72" x-model.debounce.200ms="q" x-bind:placeholder="T.search" data-t="search">
        <select class="inp" x-model="cat" x-on:change="setCat()"><option value="" x-text="T.all_categories"></option>
            <template x-for="c in lists.categories" x-bind:key="c"><option x-bind:value="c" x-text="c"></option></template>
        </select>
        <template x-for="m in ['all','changed','new','deleted','errors']" x-bind:key="m">
            <button type="button" class="chip" x-bind:class="filterMode === m && 'on'" x-on:click="setFilterMode(m)" x-bind:data-t="'f-' + m">
                <span x-text="T['filter_' + m]"></span>
                <span x-show="m === 'changed'" x-text="'· ' + stats.changed"></span>
                <span x-show="m === 'new'" x-text="'· ' + stats.added"></span>
                <span x-show="m === 'deleted'" x-text="'· ' + stats.deleted"></span>
                <span x-show="m === 'errors'" x-text="'· ' + stats.errors"></span>
            </button>
        </template>
    </div>

    {{-- draft banner --}}
    <div x-show="draft" class="mb-3 flex items-center gap-3 rounded-xl border border-amber-200 bg-amber-50 px-4 py-2.5 text-sm text-amber-900">
        <span x-text="T.draft_found"></span>
        <button type="button" class="btn" x-on:click="restoreDraft()" x-text="T.draft_restore"></button>
        <button type="button" class="btn" x-on:click="dropDraft()" x-text="T.draft_drop"></button>
    </div>

    {{-- grid --}}
    <div class="relative">
        <div class="bg-scroll" x-ref="grid" data-t="grid"></div>
        <div x-show="loading" class="absolute inset-0 flex items-center justify-center rounded-[14px] bg-white/80 text-sm font-medium text-gray-500" x-text="T.loading"></div>
        <div x-show="error" class="absolute inset-0 flex flex-col items-center justify-center gap-3 rounded-[14px] bg-white/90 text-sm text-red-600">
            <span x-text="error"></span><button type="button" class="btn" x-on:click="reload()" x-text="T.retry"></button>
        </div>
    </div>

    {{-- status bar --}}
    <div class="mt-2 flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-gray-500">
        <span><b x-text="stats.total"></b> <span x-text="T.books"></span> · <b x-text="stats.shown"></b> <span x-text="T.shown"></span></span>
        <span x-show="selCount > 1"><b x-text="selCount"></b> <span x-text="T.selected_cells"></span></span>
        <span class="rounded-full bg-amber-100 px-2 py-0.5 font-semibold text-amber-800" x-show="stats.dirty" x-text="T.unsaved"></span>
        <span class="rounded-full bg-red-100 px-2 py-0.5 font-semibold text-red-700" x-show="stats.errors" x-text="stats.errors + ' ' + T.errors"></span>
        <span class="flex-1"></span><span x-text="T.hint"></span>
    </div>

    {{-- toast --}}
    <div x-show="toast" x-transition class="fixed bottom-6 z-[120] max-w-md rounded-xl px-4 py-3 text-sm font-medium text-white shadow-2xl" style="inset-inline-start:24px"
         x-bind:class="toast && toast.type === 'warn' ? 'bg-amber-600' : 'bg-gray-900'" x-text="toast && toast.m"></div>

    {{-- review modal --}}
    <div x-show="review.open" class="fixed inset-0 z-[110] flex items-center justify-center p-4" data-t="review-modal">
        <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" x-on:click="closeReview()"></div>
        <div class="relative flex max-h-[92vh] w-full max-w-4xl flex-col overflow-hidden rounded-2xl bg-white shadow-2xl">
            <div class="flex items-center justify-between border-b border-gray-100 px-6 py-4">
                <h3 class="text-lg font-bold text-gray-900" x-text="review.done ? T.saved_title : T.review_title"></h3>
                <button type="button" class="rounded-lg p-2 text-gray-400 hover:bg-gray-100" x-on:click="closeReview()" x-bind:disabled="review.saving">✕</button>
            </div>

            <div class="overflow-y-auto px-6 py-5">
                <div x-show="review.loading" class="py-10 text-center text-sm text-gray-500" x-text="T.review_loading"></div>
                <div x-show="review.error" class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700" x-text="review.error"></div>

                <template x-if="review.done">
                    <div class="space-y-3 py-6 text-center">
                        <div class="text-5xl">✅</div>
                        <p class="text-gray-700" x-text="T.saved_text"></p>
                        <div class="mx-auto grid max-w-md grid-cols-3 gap-3">
                            <div class="tile border-emerald-200 bg-emerald-50 text-emerald-800"><b x-text="review.result.totals.added"></b><span class="text-xs" x-text="T.added"></span></div>
                            <div class="tile border-amber-200 bg-amber-50 text-amber-800"><b x-text="review.result.totals.changed"></b><span class="text-xs" x-text="T.changed"></span></div>
                            <div class="tile border-red-200 bg-red-50 text-red-800"><b x-text="review.result.totals.deleted"></b><span class="text-xs" x-text="T.deleted"></span></div>
                        </div>
                    </div>
                </template>

                <template x-if="review.plan && !review.done">
                    <div class="space-y-5">
                        <div class="grid grid-cols-3 gap-3">
                            <div class="tile border-emerald-200 bg-emerald-50 text-emerald-800"><b data-t="n-added" x-text="review.plan.totals.added"></b><span class="text-xs font-semibold" x-text="T.books_added"></span></div>
                            <div class="tile border-amber-200 bg-amber-50 text-amber-800"><b data-t="n-changed" x-text="review.plan.totals.changed"></b><span class="text-xs font-semibold" x-text="T.books_changed"></span></div>
                            <div class="tile border-red-200 bg-red-50 text-red-800"><b data-t="n-deleted" x-text="review.plan.totals.deleted"></b><span class="text-xs font-semibold" x-text="T.books_deleted"></span></div>
                        </div>

                        <div x-show="review.fatal" class="rounded-xl border border-red-300 bg-red-50 p-4 text-sm font-semibold text-red-700"><span x-text="T.save_failed"></span>: <span x-text="review.fatal"></span></div>

                        <div x-show="review.plan.errors.length" class="rounded-xl border border-red-200 bg-red-50 p-4" data-t="errors">
                            <h4 class="mb-2 text-sm font-bold text-red-800" x-text="T.fix_errors + ' (' + review.plan.errors.length + ')'"></h4>
                            <ul class="max-h-48 space-y-1 overflow-y-auto text-sm text-red-700">
                                <template x-for="(e, i) in review.plan.errors" x-bind:key="i">
                                    <li class="flex items-start gap-2"><span class="flex-1"><b x-text="e.field ? label(e.field) + ': ' : ''"></b><span x-text="e.message"></span></span>
                                        <button type="button" class="text-xs font-semibold underline" x-show="e.row" x-on:click="goTo(e)" x-text="T.go_to"></button></li>
                                </template>
                            </ul>
                        </div>

                        <div x-show="review.plan.added.length">
                            <h4 class="mb-1 text-sm font-bold text-emerald-800" x-text="T.books_added + ' (' + review.plan.added.length + ')'"></h4>
                            <div class="max-h-44 overflow-y-auto rounded-xl border border-emerald-100">
                                <table class="w-full text-start"><tbody>
                                    <template x-for="a in review.plan.added" x-bind:key="a.key"><tr class="chg-row"><td class="font-semibold" x-text="a.name"></td><td x-text="a.isbn"></td><td x-text="a.contractor || ''"></td><td x-text="a.category || ''"></td><td x-text="T.stock + ': ' + (a.quantity || 0)"></td></tr></template>
                                </tbody></table>
                            </div>
                        </div>

                        <div x-show="review.plan.changed.length">
                            <h4 class="mb-1 text-sm font-bold text-amber-800" x-text="T.books_changed + ' (' + review.plan.changed.length + ')'"></h4>
                            <div class="max-h-72 space-y-1 overflow-y-auto rounded-xl border border-amber-100 p-2">
                                <template x-for="c in review.plan.changed" x-bind:key="c.key">
                                    <div class="rounded-lg bg-amber-50/50 px-3 py-2">
                                        <div class="flex items-center gap-2 text-sm">
                                            <span class="flex-1 font-semibold" x-text="c.name"></span><span class="text-xs text-gray-500" x-text="c.diff.length + ' ✎'"></span>
                                            <button type="button" class="text-xs font-semibold text-indigo-600 underline" x-on:click="review.open2[c.key] = !review.open2[c.key]" x-text="review.open2[c.key] ? T.hide_details : T.show_details"></button>
                                        </div>
                                        <table class="mt-1 w-full text-start" x-show="review.open2[c.key]"><tbody>
                                            <template x-for="d in c.diff" x-bind:key="d.field"><tr class="chg-row"><td class="w-40 font-semibold text-gray-600" x-text="label(d.field)"></td>
                                                <td class="text-red-700 line-through" x-text="fmt(d.old)"></td><td class="text-emerald-700" x-text="fmt(d.new)"></td></tr></template>
                                        </tbody></table>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <div class="grid gap-3 md:grid-cols-2" x-show="review.plan.created.categories.length || review.plan.created.sub_categories.length || review.plan.created.contractors.length || review.plan.contractor_updates.length || review.plan.stock.length">
                            <div x-show="review.plan.created.categories.length || review.plan.created.sub_categories.length" class="rounded-xl border border-indigo-100 bg-indigo-50/40 p-3 text-sm">
                                <b x-text="T.new_categories"></b>
                                <ul class="mt-1 list-inside list-disc text-gray-700"><template x-for="n in review.plan.created.categories.concat(review.plan.created.sub_categories)" x-bind:key="n"><li x-text="n"></li></template></ul>
                            </div>
                            <div x-show="review.plan.created.contractors.length" class="rounded-xl border border-indigo-100 bg-indigo-50/40 p-3 text-sm">
                                <b x-text="T.new_contractors"></b>
                                <ul class="mt-1 list-inside list-disc text-gray-700"><template x-for="n in review.plan.created.contractors" x-bind:key="n"><li x-text="n"></li></template></ul>
                            </div>
                            <div x-show="review.plan.contractor_updates.length" class="rounded-xl border border-indigo-100 bg-indigo-50/40 p-3 text-sm">
                                <b x-text="T.contractor_updates"></b>
                                <ul class="mt-1 text-gray-700"><template x-for="u in review.plan.contractor_updates" x-bind:key="u.id"><li><span x-text="u.name"></span>: <s x-text="fmt(u.old)"></s> → <b x-text="u.new"></b></li></template></ul>
                            </div>
                            <div x-show="review.plan.stock.length" class="rounded-xl border border-indigo-100 bg-indigo-50/40 p-3 text-sm">
                                <b x-text="T.stock_changes + ' (' + review.plan.stock.length + ')'"></b>
                                <ul class="mt-1 max-h-28 overflow-y-auto text-gray-700"><template x-for="s in review.plan.stock" x-bind:key="s.key"><li><span x-text="s.name"></span>: <s x-text="s.old"></s> → <b x-text="s.new"></b></li></template></ul>
                            </div>
                        </div>

                        <div x-show="review.plan.deleted.length" class="rounded-xl border-2 border-red-300 bg-red-50 p-4" data-t="delete-zone">
                            <h4 class="text-sm font-bold text-red-800" x-text="T.delete_title"></h4>
                            <p class="mt-1 text-sm text-red-700" x-text="tx('delete_text', { n: review.plan.deleted.length })"></p>
                            <ul class="mt-2 max-h-32 overflow-y-auto text-sm text-red-900">
                                <template x-for="d in review.plan.deleted" x-bind:key="d.key"><li class="flex gap-2"><span class="flex-1" x-text="d.name + ' · ' + d.isbn"></span><span x-show="d.stock > 0" x-text="T.stock + ': ' + d.stock + ' ' + T.units"></span></li></template>
                            </ul>
                            <label class="mt-3 flex items-start gap-2 text-sm text-red-900" x-show="review.plan.stock_units_destroyed > 0">
                                <input type="checkbox" class="mt-1" x-model="review.ack" data-t="ack"> <span x-text="tx('stock_ack', { units: review.plan.stock_units_destroyed })"></span>
                            </label>
                            <div class="mt-3 flex items-center gap-2 text-sm"><span class="font-semibold text-red-900" x-text="tx('delete_type', { n: review.plan.deleted.length })"></span>
                                <input type="text" class="inp w-24 text-center" x-model="review.confirmText" inputmode="numeric" data-t="confirm-text"></div>
                        </div>
                    </div>
                </template>
            </div>

            <div class="flex items-center justify-end gap-2 border-t border-gray-100 bg-gray-50 px-6 py-3">
                <button type="button" class="btn" x-on:click="closeReview()" x-bind:disabled="review.saving" x-text="review.done ? T.close : T.cancel"></button>
                <button type="button" class="btn btn-pri" x-show="!review.done" x-on:click="save()" x-bind:disabled="!canSave()" data-t="confirm-save"><span x-text="review.saving ? T.saving : T.confirm_save"></span></button>
            </div>
        </div>
    </div>
</div>
