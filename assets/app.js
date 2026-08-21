import './stimulus_bootstrap.js';
/*
 * Welcome to your app's main JavaScript file!
 *
 * This file will be included onto the page via the importmap() Twig function,
 * which should already be in your base.html.twig.
 */
import './styles/app.css';

const applyAccessibilityPreferences = () => {
    const size = localStorage.getItem('fit-accessibility-size');
    document.documentElement.style.fontSize = size ? `${size}%` : '';
    document.documentElement.classList.toggle('fit-high-contrast', localStorage.getItem('fit-high-contrast') === 'true');
    document.documentElement.classList.toggle('fit-dark-mode', localStorage.getItem('fit-dark-mode') === 'true');
    document.documentElement.classList.toggle('fit-voice-assistance', localStorage.getItem('fit-voice-assistance') === 'true');
    document.documentElement.dataset.haptics = localStorage.getItem('fit-haptics') === 'true' ? 'true' : 'false';
    document.documentElement.dataset.videoCaptions = localStorage.getItem('fit-video-captions') === 'true' ? 'true' : 'false';
    const reducedMotion = localStorage.getItem('fit-reduced-motion');
    document.documentElement.classList.toggle('fit-reduced-motion', reducedMotion === 'true' || (reducedMotion === null && window.matchMedia('(prefers-reduced-motion: reduce)').matches));
};

applyAccessibilityPreferences();
document.addEventListener('turbo:load', applyAccessibilityPreferences);

document.addEventListener('click', () => {
    if (document.documentElement.dataset.haptics === 'true' && navigator.vibrate) {
        navigator.vibrate(20);
    }
});

let accessibilitySpeechTimer;

const speakAccessibilityText = (text) => {
    if (!text || !('speechSynthesis' in window)) return;

    const readableText = text.replace(/\s+/g, ' ').trim();
    if (!readableText) return;

    window.clearTimeout(accessibilitySpeechTimer);
    accessibilitySpeechTimer = window.setTimeout(() => {
        const voices = window.speechSynthesis.getVoices();
        const frenchVoice = voices.find((voice) => voice.lang.toLowerCase() === 'fr-fr')
            || voices.find((voice) => voice.lang.toLowerCase().startsWith('fr'));
        const message = new SpeechSynthesisUtterance(readableText);
        message.lang = frenchVoice?.lang || 'fr-FR';
        message.voice = frenchVoice || null;
        message.rate = 0.8;
        message.pitch = 1;
        message.volume = 1;

        window.speechSynthesis.cancel();
        window.speechSynthesis.speak(message);
    }, 220);
};

window.fitSpeak = speakAccessibilityText;

document.addEventListener('focusin', (event) => {
    if (localStorage.getItem('fit-voice-assistance') !== 'true') return;

    const element = event.target.closest('a, button, input, select, textarea, [tabindex]');
    if (!element) return;

    const label = element.getAttribute('aria-label')
        || (element.id ? document.querySelector(`label[for="${CSS.escape(element.id)}"]`)?.textContent : '')
        || element.closest('label')?.querySelector('.sr-only')?.textContent
        || element.textContent
        || element.getAttribute('name');

    speakAccessibilityText(label);
});
