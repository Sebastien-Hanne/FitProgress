import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['size', 'highContrast', 'darkMode', 'haptics', 'voice', 'captions', 'status'];

    connect() {
        this.sizeTarget.value = localStorage.getItem('fit-accessibility-size') ?? '100';
        this.highContrastTarget.checked = localStorage.getItem('fit-high-contrast') === 'true';
        this.darkModeTarget.checked = localStorage.getItem('fit-dark-mode') === 'true';
        this.hapticsTarget.checked = localStorage.getItem('fit-haptics') === 'true';
        this.voiceTarget.checked = localStorage.getItem('fit-voice-assistance') === 'true';
        this.captionsTarget.checked = localStorage.getItem('fit-video-captions') === 'true';
        this.apply();
    }

    update() {
        localStorage.setItem('fit-accessibility-size', this.sizeTarget.value);
        localStorage.setItem('fit-high-contrast', this.highContrastTarget.checked);
        localStorage.setItem('fit-dark-mode', this.darkModeTarget.checked);
        localStorage.setItem('fit-haptics', this.hapticsTarget.checked);
        localStorage.setItem('fit-voice-assistance', this.voiceTarget.checked);
        localStorage.setItem('fit-video-captions', this.captionsTarget.checked);
        this.apply();

        if (this.hapticsTarget.checked && navigator.vibrate) {
            navigator.vibrate(35);
        }

        this.statusTarget.textContent = 'Préférences d’accessibilité enregistrées.';
    }

    apply() {
        document.documentElement.style.fontSize = `${this.sizeTarget.value}%`;
        document.documentElement.classList.toggle('fit-high-contrast', this.highContrastTarget.checked);
        document.documentElement.classList.toggle('fit-dark-mode', this.darkModeTarget.checked);
        document.documentElement.classList.toggle('fit-voice-assistance', this.voiceTarget.checked);
        document.documentElement.dataset.haptics = String(this.hapticsTarget.checked);
        document.documentElement.dataset.videoCaptions = String(this.captionsTarget.checked);
    }
}
