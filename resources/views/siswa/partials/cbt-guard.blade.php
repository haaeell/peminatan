<script>
    class CBTGuard {
        constructor(config) {
            this.examType = config.examType;
            this.logUrl = config.logUrl;
            this.submitUrl = config.submitUrl;
            this.csrf = config.csrf;
            this.maxViolations = config.maxViolations || 3;
            this.warningMessage = config.warningMessage || 'Aktivitas mencurigakan terdeteksi dan dicatat.';
            this.forceFullscreenEnabled = config.forceFullscreen !== false;
            this.violationCount = config.initialViolationCount || 0;
            this.isSubmitting = false;
            this.storageKey = `backup_${this.examType}`;
            this.lastViolationAt = 0;
            this.violationCooldownMs = 1500;
            this.focusMonitor = null;
            this.wasVisible = !document.hidden;
            this.wasFocused = document.hasFocus();
        }

        init() {
            this.forceFullscreen();
            this.blockInput();
            this.listenViolation();
            this.monitorVisibilityFallback();
            this.restoreBackup();
            this.watchConnection();
        }

        forceFullscreen() {
            if (!this.forceFullscreenEnabled) return;

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
            window.addEventListener('pagehide', () => this.report('page_hide'));

            document.addEventListener('freeze', () => this.report('page_freeze'));

            document.addEventListener('visibilitychange', () => {
                if (document.hidden) {
                    this.report('visibility_hidden');
                }
            });

            document.addEventListener('fullscreenchange', () => {
                if (!document.fullscreenElement && !this.isSubmitting) {
                    this.report('exit_fullscreen');
                    this.forceFullscreen();
                }
            });
        }

        monitorVisibilityFallback() {
            if (this.focusMonitor) {
                clearInterval(this.focusMonitor);
            }

            this.focusMonitor = setInterval(() => {
                if (this.isSubmitting) {
                    return;
                }

                const isHidden = document.hidden || document.visibilityState === 'hidden';
                const isFocused = document.hasFocus();

                if (isHidden && !this.wasVisible) {
                    return;
                }

                if (isHidden) {
                    this.report('visibility_hidden');
                }

                this.wasVisible = !isHidden;

                if (!isFocused && this.wasFocused) {
                    this.report('window_blur');
                }

                this.wasFocused = isFocused;
            }, 500);
        }

        report(action) {
            if (this.isSubmitting) return;
            if ($('.swal2-container').length) return;

            const now = Date.now();

            if (now - this.lastViolationAt < this.violationCooldownMs) {
                return;
            }

            this.lastViolationAt = now;

            this.violationCount++;

            $.post(this.logUrl, {
                _token: this.csrf,
                exam_type: this.examType,
                action: action,
                device_info: this.deviceInfo()
            }).done((response) => {
                this.violationCount = Number(response.total_violations || this.violationCount);

                if (response.auto_submit || this.violationCount >= this.maxViolations) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Batas Pelanggaran Tercapai',
                        text: `Ujian akan dikirim otomatis setelah ${this.maxViolations} pelanggaran.`,
                        allowOutsideClick: false,
                        confirmButtonText: 'Saya Mengerti'
                    }).then(() => this.submitExam());

                    return;
                }

                Swal.fire({
                    icon: 'warning',
                    title: 'Peringatan',
                    text: this.warningMessage,
                    allowOutsideClick: false,
                    confirmButtonText: 'Saya Mengerti'
                });
            }).fail(() => {
                Swal.fire({
                    icon: 'warning',
                    title: 'Peringatan',
                    text: this.warningMessage,
                    allowOutsideClick: false,
                    confirmButtonText: 'Saya Mengerti'
                });
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

            if (this.focusMonitor) {
                clearInterval(this.focusMonitor);
                this.focusMonitor = null;
            }

            $.post(this.submitUrl, {
                _token: this.csrf
            }).done(res => {
                localStorage.removeItem(this.storageKey);
                window.location.href = res.redirect_url;
            });
        }
    }
</script>
