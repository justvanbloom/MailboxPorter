<?php
$command = '/usr/bin/imapsync --version 2>&1';
$imapsync_version = shell_exec($command);
$is_installed = !empty($imapsync_version) && preg_match('/\d+\.\d+/', $imapsync_version);
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MailboxPorter | The modern, simple, and Dockerized GUI for imapsync.</title>

    <link rel="icon" href="https://fav.farm/🧳">

    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        [x-cloak] { display: none !important; }
        .console-box::-webkit-scrollbar { width: 8px; }
        .console-box::-webkit-scrollbar-thumb { background: #334155; border-radius: 4px; }
        .progress-transition { transition: width 0.7s ease-in-out; }
    </style>
</head>
<body class="bg-slate-50 font-sans antialiased text-slate-900" x-data="migrationApp()">

<div class="max-w-6xl mx-auto py-10 px-4">
    <header class="text-center mb-10">
        <div class="inline-block p-3 bg-indigo-600 rounded-2xl shadow-lg mb-4 text-white text-3xl shadow-indigo-200">🧳</div>
        <h1 class="text-4xl font-extrabold tracking-tight text-slate-900">MailboxPorter</h1>
        <p class="mt-2 text-slate-500 text-lg">The modern, simple, and Dockerized GUI for imapsync.</p>
    </header>

    <div class="mb-8 flex justify-center">
        <?php if ($is_installed): ?>
            <div class="flex items-center gap-3 bg-white border border-emerald-100 text-emerald-700 px-5 py-2 rounded-full shadow-sm">
                <div class="h-2 w-2 bg-emerald-500 rounded-full animate-pulse"></div>
                <span class="text-xs font-bold uppercase tracking-wider">Engine: imapsync v<?php echo trim($imapsync_version); ?></span>
            </div>
        <?php else: ?>
            <div class="bg-red-50 border border-red-200 text-red-700 px-5 py-2 rounded-full text-xs font-bold">ERRORE: imapsync non installato</div>
        <?php endif; ?>
    </div>

    <form id="mig-form" @submit.prevent="startMigration" class="space-y-8">
        <?php include 'partials/form_fields.php'; ?>

        <button type="submit" :disabled="migrating"
                class="w-full py-5 bg-slate-900 text-white font-black rounded-2xl hover:bg-indigo-600 transition-all duration-300 shadow-xl disabled:opacity-50 flex items-center justify-center gap-4 text-lg">
            <span x-show="!migrating" x-cloak>start sync now</span>
            <span x-show="migrating" x-cloak class="flex items-center gap-3">
                <svg class="animate-spin h-6 w-6 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                <span x-text="statusText === 'RECOVERY' ? 'reconnect...' : 'syncing...'"></span>
            </span>
        </button>
    </form>

    <div x-show="migrating || logOutput" x-cloak class="mt-12">

        <template x-if="showSummary">
            <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-8">
                <div class="bg-indigo-600 p-5 rounded-2xl text-white shadow-lg">
                    <p class="text-[10px] uppercase font-bold opacity-70">Transferred</p>
                    <p class="text-3xl font-black" x-text="summaryData.transferred"></p>
                </div>
                <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm text-center">
                    <p class="text-[10px] uppercase font-bold text-slate-400">Skipped</p>
                    <p class="text-3xl font-black text-slate-800" x-text="summaryData.skipped"></p>
                </div>
                <div class="p-5 rounded-2xl text-white shadow-lg text-center transition" :class="summaryData.errors > 0 ? 'bg-red-500' : 'bg-slate-800'">
                    <p class="text-[10px] uppercase font-bold opacity-70">Errors</p>
                    <p class="text-3xl font-black" x-text="summaryData.errors"></p>
                </div>
                <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm text-center">
                    <p class="text-[10px] uppercase font-bold text-slate-400">Size</p>
                    <p class="text-xl font-black text-slate-800 mt-2" x-text="summaryData.size"></p>
                </div>
                <div class="bg-emerald-500 p-5 rounded-2xl text-white shadow-lg text-center">
                    <p class="text-[10px] uppercase font-bold opacity-70">Time</p>
                    <p class="text-xl font-black mt-2" x-text="summaryData.time"></p>
                </div>
            </div>
        </template>

        <div class="bg-slate-900 rounded-[2rem] overflow-hidden shadow-2xl border border-slate-800">
            <div class="px-6 py-4 bg-slate-800/50 flex justify-between items-center border-b border-slate-800">
                <div class="flex items-center gap-3">
                    <div class="flex gap-1.5">
                        <div class="w-3 h-3 rounded-full bg-slate-700"></div>
                        <div class="w-3 h-3 rounded-full bg-slate-700"></div>
                        <div class="w-3 h-3 rounded-full bg-slate-700"></div>
                    </div>
                    <span class="text-[10px] font-mono text-slate-500 uppercase tracking-widest ml-2">Terminal Output</span>
                </div>
                <span class="text-xs font-mono text-emerald-500 font-bold" x-text="statusText"></span>
            </div>

            <div x-show="migrating && !showSummary" class="px-6 py-6 bg-slate-800/30 border-b border-slate-800">
                <div class="flex flex-wrap justify-between items-end gap-4 mb-4">
                    <div class="flex-1">
                        <p class="text-[10px] text-slate-500 uppercase font-black mb-1">Folder</p>
                        <p class="text-lg text-white font-mono truncate" x-text="currentFolder"></p>
                    </div>
                    <div class="text-right">
                        <p class="text-[10px] text-slate-500 uppercase font-black mb-1" x-text="statsSummary"></p>
                        <p class="text-3xl text-emerald-400 font-black" x-text="progressPercent + '%'"></p>
                    </div>
                </div>
                <div class="w-full bg-slate-950 rounded-full h-3 overflow-hidden border border-slate-800">
                    <div class="bg-gradient-to-r from-indigo-500 to-emerald-500 h-full progress-transition"
                         :style="`width: ${progressPercent}%`"
                         style="box-shadow: 0 0 10px rgba(16,185,129,0.4)"></div>
                </div>
            </div>

            <div id="console-log" class="p-8 h-96 overflow-y-auto font-mono text-[11px] leading-relaxed text-slate-400 bg-black/20 console-box">
                <pre x-text="logOutput" class="whitespace-pre-wrap"></pre>
            </div>
        </div>
    </div>
</div>

<script src="js/app.js"></script>
</body>
</html>
