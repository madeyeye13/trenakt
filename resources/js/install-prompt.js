document.addEventListener('alpine:init', () => {
    Alpine.data('installPrompt', () => ({
        visible: false,
        deferredPrompt: null,
        isIOS: false,

        init() {
            const dismissed = localStorage.getItem('trenakt_install_dismissed');
            if (dismissed) return;

            this.isIOS = /iphone|ipad|ipod/i.test(navigator.userAgent) && !window.navigator.standalone;

            if (this.isIOS) {
                this.visible = true;
                return;
            }

            window.addEventListener('beforeinstallprompt', (e) => {
                e.preventDefault();
                this.deferredPrompt = e;
                this.visible = true;
            });
        },

        async install() {
            if (!this.deferredPrompt) return;
            this.deferredPrompt.prompt();
            await this.deferredPrompt.userChoice;
            this.deferredPrompt = null;
            this.visible = false;
        },

        dismiss() {
            this.visible = false;
            localStorage.setItem('trenakt_install_dismissed', '1');
        },
    }));
});