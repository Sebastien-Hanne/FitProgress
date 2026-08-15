import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['dialog'];

    open(event) {
        event.preventDefault();
        this.dialogTarget.showModal();
        document.body.style.overflow = 'hidden';
    }

    close() {
        if (this.dialogTarget.open) this.dialogTarget.close();
        this.unlockScroll();
    }

    backdrop(event) {
        if (event.target === this.dialogTarget) this.close();
    }

    disconnect() {
        this.unlockScroll();
    }

    unlockScroll() {
        document.body.style.removeProperty('overflow');
        document.documentElement.style.removeProperty('overflow');
    }
}
