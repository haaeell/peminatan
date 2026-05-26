<script>
    class CBTGuard {
        constructor(config) {
            this.examType = config.examType;
            this.logUrl = config.logUrl;
            this.submitUrl = config.submitUrl;
            this.csrf = config.csrf;
            this.violationCount = 0;
            this.isSubmitting = false;
            this.storageKey = `backup_${this.examType}`;
        }

        init() {
            this.forceFullscreen();
            this.blockInput();
            this.listenViolation();
            this.restoreBackup();
            this.watchConnection();
        }

        forceFullscreen() {
            const el = document.documentElement;

            if (el.requestFullscreen) {
                el.requestFullscreen().catch(() => { });
            }
        }

        blockInput() {
            $(document).on('contextmenu copy paste cut', e => {
                e.preventDefault();
                this.report('blocked_input');
            });

            $(document).on('keydown', e => {
                const key = e.key.toLowerCase();

                const blocked =
                    e.key === 'F12' ||
                    (e.ctrlKey && e.shiftKey && ['i', 'j'].includes(key)) ||
                    (e.ctrlKey && ['u', 'c', 'v', 'x'].includes(key));

                if (blocked) {
                    e.preventDefault();
                    this.report('blocked_shortcut_' + key);
                }
            });
        }
        listenViolation() {
            window.addEventListener('blur', () => this.report('window_blur'));

            document.addEventListener('visibilitychange', () => {
                if (document.hidden) this.report('visibility_hidden');
            });

            document.addEventListener('fullscreenchange', () => {
                if (!document.fullscreenElement && !this.isSubmitting) {
                    this.report('exit_fullscreen');
                    this.forceFullscreen();
                }
            });
        }

        report(action) {
            if (this.isSubmitting) return;
            if ($('.swal2-container').length) return;

            this.violationCount++;

            Swal.fire({
                icon: 'warning',
                title: 'Peringatan',
                text: 'Aktivitas mencurigakan terdeteksi dan dicatat.',
                allowOutsideClick: false,
                confirmButtonText: 'Saya Mengerti'
            });

            $.post(this.logUrl, {
                _token: this.csrf,
                exam_type: this.examType,
                action: action,
                violation_count: this.violationCount,
                device_info: this.deviceInfo()
            });
        }

        deviceInfo() {
            return {
                userAgent: navigator.userAgent,
                platform: navigator.platform,
                language: navigator.language,
                screen: screen.width + 'x' + screen.height,
                timezone: Intl.DateTimeFormat().resolvedOptions().timeZone,
                online: navigator.onLine
            };
        }

        backupAnswer(questionId, optionId) {
            const data = JSON.parse(localStorage.getItem(this.storageKey) || '{}');

            data[questionId] = {
                option_id: optionId,
                saved_at: new Date().toISOString()
            };

            localStorage.setItem(this.storageKey, JSON.stringify(data));
        }

        restoreBackup() {
            const data = JSON.parse(localStorage.getItem(this.storageKey) || '{}');

            Object.keys(data).forEach(questionId => {
                const optionId = data[questionId].option_id;
                $(`input[data-question-id="${questionId}"][value="${optionId}"]`).prop('checked', true);
            });
        }

        watchConnection() {
            window.addEventListener('online', () => {
                $('.answer-option:checked').trigger('change');

                Swal.fire({
                    icon: 'success',
                    title: 'Koneksi kembali',
                    timer: 1000,
                    showConfirmButton: false
                });
            });

            window.addEventListener('offline', () => {
                Swal.fire('Offline', 'Jawaban akan disimpan sementara di perangkat.', 'warning');
            });
        }

        submitExam() {
            this.isSubmitting = true;

            $.post(this.submitUrl, {
                _token: this.csrf
            }).done(res => {
                localStorage.removeItem(this.storageKey);
                window.location.href = res.redirect_url;
            });
        }
    }
</script>