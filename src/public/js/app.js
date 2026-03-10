function migrationApp() {
    return {
        migrating: false,
        logOutput: '',
        jobId: null,
        statusText: 'READY',
        currentFolder: 'Waiting for start...',
        progressPercent: 0,
        statsSummary: 'Messages: 0 / 0',
        showSummary: false,
        summaryData: {
            transferred: 0,
            skipped: 0,
            errors: 0,
            size: '0 MB',
            time: '0s'
        },
        pollInterval: null,

        init() {
            const savedJob = localStorage.getItem('active_job_id');
            if (savedJob) {
                this.jobId = savedJob;
                this.migrating = true;
                this.statusText = 'RECOVERY';
                this.startPolling();
            }
        },

        async testConnection(type) {
            Swal.fire({ title: 'Verifying...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
            const formData = new FormData(document.getElementById('mig-form'));
            formData.append('test_type', type);

            try {
                const res = await fetch('api/test_connection.php', { method: 'POST', body: formData });
                const data = await res.json();
                if(data.success) {
                    Swal.fire('Connected!', data.message, 'success');
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        html: `<pre class="text-left text-[10px] bg-slate-100 p-2 overflow-auto max-h-40 font-mono">${data.debug || data.message}</pre>`
                    });
                }
            } catch (e) { Swal.fire('Error', 'API Error.', 'error'); }
        },

        async startMigration() {
            // 1. STATE RESET (For new migrations after the first one)
            this.showSummary = false;
            this.logOutput = '> Initializing...\n';
            this.progressPercent = 0;
            this.currentFolder = 'Starting...';
            this.statsSummary = 'Messages: 0 / 0';
            this.statusText = 'STARTING';

            this.migrating = true;
            const formData = new FormData(document.getElementById('mig-form'));

            try {
                const response = await fetch('api/start_migration.php', { method: 'POST', body: formData });
                const data = await response.json();
                if (data.success) {
                    this.jobId = data.job_id;
                    localStorage.setItem('active_job_id', data.job_id);
                    this.statusText = 'IN PROGRESS';
                    this.startPolling();
                } else { throw new Error(data.message); }
            } catch (e) {
                this.logOutput += `> ERROR: ${e.message}\n`;
                this.migrating = false;
            }
        },

        startPolling() {
            this.pollInterval = setInterval(async () => {
                if (!this.jobId) return;
                try {
                    const res = await fetch(`api/get_log.php?job_id=${this.jobId}`);
                    const data = await res.json();
                    if (data.success) {
                        const lines = data.content.split('\n');
                        this.logOutput = lines.filter(l => !l.match(/Modules|kill|PID|Perl|RCSfile|Load|Effective|Authen|IO|Mail|Net/)).join('\n');

                        this.$nextTick(() => {
                            const consoleDiv = document.getElementById('console-log');
                            if (consoleDiv) consoleDiv.scrollTop = consoleDiv.scrollHeight;
                        });

                        const folderMatch = this.logOutput.match(/Syncing folder "([^"]+)"/g);
                        if (folderMatch) {
                            const lastFolderLine = folderMatch[folderMatch.length - 1];
                            this.currentFolder = lastFolderLine.match(/"([^"]+)"/)[1];
                        }

                        const msgMatch = this.logOutput.match(/(\d+)\/(\d+) identified messages/);
                        if (msgMatch) {
                            const current = parseInt(msgMatch[1]);
                            const total = parseInt(msgMatch[2]);
                            this.progressPercent = Math.round((current / total) * 100);
                            this.statsSummary = `MESSAGES: ${current} / ${total}`;
                        }

                        if (data.content.includes('Exiting with return value') || data.content.includes('Removing pidfile')) {
                            clearInterval(this.pollInterval);
                            this.migrating = false;
                            this.statusText = 'COMPLETED';
                            this.currentFolder = 'Synchronization Complete! ✅';
                            this.progressPercent = 100;
                            this.parseFinalStats(data.content);
                            localStorage.removeItem('active_job_id');
                            Swal.fire('Done!', 'Migration completed.', 'success');
                        }

                        if (data.content.includes('failed login') || data.content.includes('AUTHENTICATION_FAILURE')) {
                            clearInterval(this.pollInterval);
                            this.migrating = false;
                            this.statusText = 'ERROR';
                            this.currentFolder = 'Authentication failure ❌';
                            localStorage.removeItem('active_job_id');
                            Swal.fire('Error', 'Authentication failed.', 'error');
                        }
                    }
                } catch (e) { console.error(e); }
            }, 2000);
        },

        parseFinalStats(content) {
            const tr = content.match(/Messages transferred\s+:\s+(\d+)/);
            const sk = content.match(/Messages skipped\s+:\s+(\d+)/);
            const er = content.match(/Detected\s+(\d+)\s+errors/);
            const sz = content.match(/Total bytes transferred\s+:\s+(\d+)/); // Only the bytes
            const tm = content.match(/Transfer time\s+:\s+([\d\.]+)\s+sec/); // Only the number and sec

            // Byte to MB conversion with 2 decimals
            let sizeMb = '0 MB';
            if (sz && sz[1]) {
                const bytes = parseInt(sz[1]);
                sizeMb = (bytes / (1024 * 1024)).toFixed(2) + ' MB';
            }

            this.summaryData = {
                transferred: tr ? tr[1] : 0,
                skipped: sk ? sk[1] : 0,
                errors: er ? er[1] : 0,
                size: sizeMb,
                time: tm ? tm[1] + ' sec.' : '0 sec.'
            };
            this.showSummary = true;
        }
    }
}
