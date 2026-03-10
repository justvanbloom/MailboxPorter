<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
        <h3 class="flex items-center gap-2 font-bold text-indigo-600 mb-6 uppercase tracking-wider text-sm italic">
            <span class="p-1 bg-indigo-100 rounded">⬇️</span> Source
        </h3>
        <div class="space-y-4">
            <input type="text" name="s_host" placeholder="Host (es. imap.gmail.com)" class="w-full p-3 bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none transition" required>
            <input type="email" name="s_user" placeholder="Email / Username" class="w-full p-3 bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none transition" required>
            <input type="password" name="s_pass" placeholder="Password" class="w-full p-3 bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none transition" required>
            <button type="button" @click="testConnection('source')" class="text-xs font-bold text-indigo-600 hover:text-indigo-800 underline uppercase tracking-tighter">Test Source</button>
        </div>
    </div>

    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
        <h3 class="flex items-center gap-2 font-bold text-emerald-600 mb-6 uppercase tracking-wider text-sm italic">
            <span class="p-1 bg-emerald-100 rounded">⬆️</span> Destination
        </h3>
        <div class="space-y-4">
            <input type="text" name="d_host" placeholder="Host (es. imap.outlook.com)" class="w-full p-3 bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none transition" required>
            <input type="email" name="d_user" placeholder="Email / Username" class="w-full p-3 bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none transition" required>
            <input type="password" name="d_pass" placeholder="Password" class="w-full p-3 bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none transition" required>
            <button type="button" @click="testConnection('dest')" class="text-xs font-bold text-emerald-600 hover:text-emerald-800 underline uppercase tracking-tighter">Test Destination</button>
        </div>
    </div>
</div>

<div x-data="{ open: false }" class="mt-6">
    <button type="button" @click="open = !open" class="flex items-center gap-2 text-sm font-bold text-slate-500 hover:text-indigo-600 transition outline-none">
        <span class="text-[10px]" x-text="open ? '▲' : '▼'"></span>
        <span>Advanced Options (Filters and Limits)</span>
    </button>

    <div x-show="open" x-cloak class="mt-4 p-6 bg-white border border-slate-200 rounded-2xl grid grid-cols-1 md:grid-cols-2 gap-6 animate-fade-in">
        <div>
            <label class="block text-[10px] font-black uppercase text-slate-400 mb-2 tracking-widest">Last Posts X Days</label>
            <input type="number" name="opt_maxage" placeholder="Es: 30 (lascia vuoto per tutti)" class="w-full p-3 bg-slate-50 border border-slate-200 rounded-lg outline-none focus:ring-2 focus:ring-indigo-500 text-sm">
        </div>
        <div>
            <label class="block text-[10px] font-black uppercase text-slate-400 mb-2 tracking-widest">Exclude Folders (comma separated)</label>
            <input type="text" name="opt_exclude" placeholder="Spam,Trash,Junk" class="w-full p-3 bg-slate-50 border border-slate-200 rounded-lg outline-none focus:ring-2 focus:ring-indigo-500 text-sm">
        </div>
        <div class="md:col-span-2 flex items-center gap-3 bg-indigo-50 p-3 rounded-lg border border-indigo-100">
            <input type="checkbox" name="opt_dryrun" id="dryrun" class="w-4 h-4 text-indigo-600 border-slate-300 rounded focus:ring-indigo-500 cursor-pointer">
            <label for="dryrun" class="text-sm text-indigo-900 font-bold cursor-pointer uppercase tracking-tighter">Dry-run: Not really transferring messages</label>
        </div>
    </div>
</div>
