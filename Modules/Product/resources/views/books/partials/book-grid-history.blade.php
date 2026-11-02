{{-- Books grid history + restore markup. Plain HTML + Alpine; strings come from JS config.i18n. --}}
<style>
    .bgh .btn { display:inline-flex; align-items:center; gap:6px; padding:6px 12px; border-radius:10px; font-size:13px; font-weight:600; border:1px solid #e5e7eb; background:#fff; color:#334155; cursor:pointer; transition:.15s; }
    .bgh .btn:hover:not(:disabled) { background:#f8fafc; border-color:#c7d2fe; } .bgh .btn:disabled { opacity:.4; cursor:not-allowed; }
    .bgh .btn-pri { background:#4f46e5; border-color:#4f46e5; color:#fff; } .bgh .btn-pri:hover:not(:disabled) { background:#4338ca; }
    .bgh .inp { padding:8px 12px; border:1px solid #e5e7eb; border-radius:10px; font-size:13px; background:#fff; outline:none; } .bgh .inp:focus { border-color:#6366f1; box-shadow:0 0 0 3px rgba(99,102,241,.15); }
    .bgh .pill { display:inline-block; padding:2px 9px; border-radius:999px; font-size:11px; font-weight:700; }
    .bgh .cell { padding:10px 12px; border-bottom:1px solid #f1f5f9; font-size:13px; vertical-align:top; }
    [x-cloak] { display:none !important; }
</style>

<div class="bgh" x-data="bookGridHistory(window.__BOOK_GRID_HISTORY)" x-cloak>
    <div class="mb-3 flex flex-wrap items-center gap-2">
        <input type="search" class="inp w-80" x-model.debounce.300ms="term" x-on:input="page = 1; load()" x-bind:placeholder="H.search" data-t="h-search">
        <span class="flex-1"></span>
        <span class="text-xs text-gray-500" x-show="loading" x-text="H.loading"></span>
    </div>

    <div class="overflow-x-auto rounded-2xl border border-gray-200 bg-white">
        <table class="w-full text-start">
            <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wide text-slate-500">
                <tr><th class="cell text-start" x-text="H.when"></th><th class="cell text-start" x-text="H.user"></th><th class="cell text-start" x-text="H.summary"></th><th class="cell text-start" x-text="H.status"></th><th class="cell text-end" x-text="H.actions"></th></tr>
            </thead>
            <tbody data-t="h-rows">
                <template x-for="b in rows" x-bind:key="b.batch_uuid">
                    <tr class="hover:bg-slate-50/60">
                        <td class="cell whitespace-nowrap"><div class="font-semibold text-gray-800" x-text="fmtDate(b.created_at)"></div><div class="text-[11px] text-gray-400" x-text="b.batch_uuid.slice(0, 8)"></div></td>
                        <td class="cell" x-text="b.user_name || H.system"></td>
                        <td class="cell"><div class="flex flex-wrap gap-1.5">
                            <span class="pill bg-emerald-100 text-emerald-800" x-show="b.added > 0" x-text="tx(H.n_added, b.added)"></span>
                            <span class="pill bg-amber-100 text-amber-800" x-show="b.changed > 0" x-text="tx(H.n_changed, b.changed)"></span>
                            <span class="pill bg-red-100 text-red-800" x-show="b.deleted > 0" x-text="tx(H.n_deleted, b.deleted)"></span>
                            <span class="pill bg-indigo-100 text-indigo-800" x-show="b.stock_changes > 0" x-text="tx(H.n_stock, b.stock_changes)"></span>
                        </div></td>
                        <td class="cell"><div class="flex flex-wrap gap-1.5">
                            <span class="pill bg-gray-200 text-gray-700" x-show="b.reverted_by" x-text="H.badge_restored"></span>
                            <span class="pill bg-sky-100 text-sky-800" x-show="b.restore_of" x-text="H.badge_restore"></span>
                        </div></td>
                        <td class="cell text-end whitespace-nowrap">
                            <button type="button" class="btn" x-on:click="openDetails(b)" data-t="h-details" x-text="H.details"></button>
                            <button type="button" class="btn btn-pri" x-on:click="openRestore(b)" x-bind:disabled="!!b.reverted_by" data-t="h-restore" x-text="H.restore"></button>
                        </td>
                    </tr>
                </template>
                <tr x-show="!loading && !rows.length"><td colspan="5" class="cell py-10 text-center text-gray-400" x-text="H.empty"></td></tr>
            </tbody>
        </table>
    </div>
    <div class="mt-3 flex items-center justify-between text-sm text-gray-500" x-show="last > 1">
        <button type="button" class="btn" x-bind:disabled="page <= 1" x-on:click="page--; load()" x-text="H.prev"></button>
        <span x-text="page + ' / ' + last"></span>
        <button type="button" class="btn" x-bind:disabled="page >= last" x-on:click="page++; load()" x-text="H.next"></button>
    </div>

    {{-- details modal --}}
    <div x-show="details.open" class="fixed inset-0 z-[110] flex items-center justify-center p-4" data-t="h-details-modal">
        <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" x-on:click="details.open = false"></div>
        <div class="relative flex max-h-[90vh] w-full max-w-3xl flex-col overflow-hidden rounded-2xl bg-white shadow-2xl">
            <div class="flex items-center justify-between border-b border-gray-100 px-6 py-4">
                <div><h3 class="text-lg font-bold text-gray-900" x-text="H.details_title"></h3>
                    <p class="text-xs text-gray-500" x-text="details.meta ? fmtDate(details.meta.created_at) + ' · ' + (details.meta.user_name || H.system) : ''"></p></div>
                <button type="button" class="rounded-lg p-2 text-gray-400 hover:bg-gray-100" x-on:click="details.open = false">✕</button>
            </div>
            <div class="space-y-4 overflow-y-auto px-6 py-5 text-sm">
                <div x-show="details.loading" class="text-center text-gray-500" x-text="H.loading"></div>
                <template x-if="details.data">
                    <div class="space-y-4">
                        <div x-show="details.data.added.length"><h4 class="mb-1 font-bold text-emerald-800" x-text="UI.books_added + ' (' + details.data.added.length + ')'"></h4>
                            <ul class="rounded-xl border border-emerald-100 p-2"><template x-for="a in details.data.added" x-bind:key="a.book_id"><li class="flex gap-2 py-0.5"><span class="flex-1 font-semibold" x-text="a.name"></span><span x-text="a.isbn"></span><span class="text-xs" x-text="a.exists ? H.still_exists : H.gone"></span></li></template></ul></div>
                        <div x-show="details.data.changed.length"><h4 class="mb-1 font-bold text-amber-800" x-text="UI.books_changed + ' (' + details.data.changed.length + ')'"></h4>
                            <div class="max-h-72 space-y-1 overflow-y-auto rounded-xl border border-amber-100 p-2">
                                <template x-for="c in details.data.changed" x-bind:key="c.book_id"><div class="rounded-lg bg-amber-50/50 px-3 py-2"><div class="font-semibold" x-text="c.name"></div>
                                    <table class="w-full"><tbody><template x-for="d in c.diff" x-bind:key="d.field"><tr><td class="w-40 py-0.5 text-gray-600" x-text="col[d.field] || d.field"></td><td class="text-red-700 line-through" x-text="fmt(d.old)"></td><td class="text-emerald-700" x-text="fmt(d.new)"></td></tr></template></tbody></table></div></template>
                            </div></div>
                        <div x-show="details.data.deleted.length"><h4 class="mb-1 font-bold text-red-800" x-text="UI.books_deleted + ' (' + details.data.deleted.length + ')'"></h4>
                            <ul class="rounded-xl border border-red-100 p-2"><template x-for="d in details.data.deleted" x-bind:key="d.book_id"><li class="flex gap-2 py-0.5"><span class="flex-1 font-semibold" x-text="d.name"></span><span x-text="d.isbn"></span><span x-text="UI.stock + ': ' + d.stock"></span></li></template></ul></div>
                        <div x-show="details.data.created_categories.length"><h4 class="mb-1 font-bold text-indigo-800" x-text="H.categories_created"></h4><p x-text="details.data.created_categories.join('، ')"></p></div>
                        <div x-show="details.data.created_contractors.length"><h4 class="mb-1 font-bold text-indigo-800" x-text="H.contractors_created"></h4><p x-text="details.data.created_contractors.join('، ')"></p></div>
                    </div>
                </template>
            </div>
            <div class="flex justify-end border-t border-gray-100 bg-gray-50 px-6 py-3"><button type="button" class="btn" x-on:click="details.open = false" x-text="H.close"></button></div>
        </div>
    </div>

    {{-- restore modal --}}
    <div x-show="rs.open" class="fixed inset-0 z-[110] flex items-center justify-center p-4" data-t="h-restore-modal">
        <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" x-on:click="closeRestore()"></div>
        <div class="relative flex max-h-[92vh] w-full max-w-3xl flex-col overflow-hidden rounded-2xl bg-white shadow-2xl">
            <div class="flex items-center justify-between border-b border-gray-100 px-6 py-4">
                <h3 class="text-lg font-bold text-gray-900" x-text="rs.done ? UI.saved_title : H.restore_title"></h3>
                <button type="button" class="rounded-lg p-2 text-gray-400 hover:bg-gray-100" x-on:click="closeRestore()" x-bind:disabled="rs.saving">✕</button>
            </div>
            <div class="space-y-4 overflow-y-auto px-6 py-5 text-sm">
                <p class="text-gray-600" x-show="!rs.done" x-text="H.restore_intro"></p>
                <div x-show="rs.loading" class="py-6 text-center text-gray-500" x-text="H.checking"></div>
                <div x-show="rs.error" class="rounded-xl border border-red-200 bg-red-50 p-3 font-semibold text-red-700" x-text="rs.error" data-t="h-restore-error"></div>
                <div x-show="rs.done" class="py-8 text-center"><div class="text-5xl">✅</div><p class="mt-2 text-gray-700" x-text="H.restored_ok"></p></div>

                <template x-if="rs.plan && !rs.done">
                    <div class="space-y-4">
                        <div class="grid grid-cols-3 gap-3 text-center">
                            <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-3 text-emerald-800"><b class="block text-2xl" data-t="r-added" x-text="rs.plan.totals.added"></b><span class="text-xs" x-text="UI.books_added"></span></div>
                            <div class="rounded-xl border border-amber-200 bg-amber-50 p-3 text-amber-800"><b class="block text-2xl" data-t="r-changed" x-text="rs.plan.totals.changed"></b><span class="text-xs" x-text="UI.books_changed"></span></div>
                            <div class="rounded-xl border border-red-200 bg-red-50 p-3 text-red-800"><b class="block text-2xl" data-t="r-deleted" x-text="rs.plan.totals.deleted"></b><span class="text-xs" x-text="UI.books_deleted"></span></div>
                        </div>

                        <div x-show="rs.conflicts.length" class="rounded-xl border border-orange-200 bg-orange-50 p-3" data-t="h-conflicts">
                            <h4 class="mb-1 font-bold text-orange-800" x-text="H.conflicts_title + ' (' + rs.conflicts.length + ')'"></h4>
                            <ul class="max-h-40 space-y-0.5 overflow-y-auto text-orange-900"><template x-for="(c, i) in rs.conflicts" x-bind:key="i"><li><b x-text="(c.name || ('#' + c.book_id)) + (c.field ? ' · ' + (col[c.field] || c.field) : '') + ': '"></b><span x-text="c.message"></span></li></template></ul>
                        </div>
                        <label class="flex items-center gap-2 text-gray-700" x-show="rs.conflicts.length || rs.plan.errors.length"><input type="checkbox" x-model="rs.skip" x-on:change="preview()" data-t="h-skip"> <span x-text="H.skip_conflicts"></span></label>

                        <div x-show="rs.plan.errors.length" class="rounded-xl border border-red-200 bg-red-50 p-3 text-red-700" data-t="h-plan-errors"><ul class="list-inside list-disc"><template x-for="(e, i) in rs.plan.errors" x-bind:key="i"><li x-text="e.message"></li></template></ul></div>

                        <div x-show="rs.plan.changed.length"><h4 class="mb-1 font-bold text-amber-800" x-text="UI.books_changed"></h4>
                            <div class="max-h-52 space-y-1 overflow-y-auto rounded-xl border border-amber-100 p-2"><template x-for="c in rs.plan.changed" x-bind:key="c.key"><div class="rounded-lg bg-amber-50/50 px-3 py-1.5"><div class="font-semibold" x-text="c.name"></div>
                                <table class="w-full"><tbody><template x-for="d in c.diff" x-bind:key="d.field"><tr><td class="w-40 text-gray-600" x-text="col[d.field] || d.field"></td><td class="text-red-700 line-through" x-text="fmt(d.old)"></td><td class="text-emerald-700" x-text="fmt(d.new)"></td></tr></template></tbody></table></div></template></div></div>
                        <div x-show="rs.plan.added.length"><h4 class="mb-1 font-bold text-emerald-800" x-text="UI.books_added"></h4><ul class="rounded-xl border border-emerald-100 p-2"><template x-for="a in rs.plan.added" x-bind:key="a.key"><li x-text="a.name + ' · ' + a.isbn"></li></template></ul></div>

                        <div x-show="rs.plan.deleted.length" class="rounded-xl border-2 border-red-300 bg-red-50 p-3" data-t="h-delete-zone">
                            <h4 class="font-bold text-red-800" x-text="UI.delete_title"></h4>
                            <ul class="mt-1 max-h-28 overflow-y-auto text-red-900"><template x-for="d in rs.plan.deleted" x-bind:key="d.key"><li class="flex gap-2"><span class="flex-1" x-text="d.name + ' · ' + d.isbn"></span><span x-show="d.stock > 0" x-text="UI.stock + ': ' + d.stock"></span></li></template></ul>
                            <label class="mt-2 flex items-start gap-2 text-red-900" x-show="rs.plan.stock_units_destroyed > 0"><input type="checkbox" class="mt-1" x-model="rs.ack" data-t="h-ack"> <span x-text="tx(UI.stock_ack, rs.plan.stock_units_destroyed, 'units')"></span></label>
                            <div class="mt-2 flex items-center gap-2"><span class="font-semibold text-red-900" x-text="tx(UI.delete_type, rs.plan.deleted.length, 'n')"></span><input type="text" class="inp w-24 text-center" x-model="rs.confirmText" data-t="h-confirm-text"></div>
                        </div>

                        <div x-show="rs.notes.length" class="rounded-xl border border-gray-200 bg-gray-50 p-3"><h4 class="mb-1 font-bold text-gray-700" x-text="H.notes_title"></h4><ul class="list-inside list-disc text-gray-600"><template x-for="n in rs.notes" x-bind:key="n"><li x-text="n"></li></template></ul></div>
                        <p class="text-center text-gray-400" x-show="!rs.plan.totals.added && !rs.plan.totals.changed && !rs.plan.totals.deleted && !rs.loading" x-text="H.nothing_to_restore"></p>
                    </div>
                </template>
            </div>
            <div class="flex items-center justify-end gap-2 border-t border-gray-100 bg-gray-50 px-6 py-3">
                <button type="button" class="btn" x-on:click="closeRestore()" x-bind:disabled="rs.saving" x-text="rs.done ? H.close : UI.cancel"></button>
                <button type="button" class="btn btn-pri" x-show="!rs.done" x-on:click="applyRestore()" x-bind:disabled="!canRestore()" data-t="h-restore-now"><span x-text="rs.saving ? H.restoring : H.restore_now"></span></button>
            </div>
        </div>
    </div>
</div>
