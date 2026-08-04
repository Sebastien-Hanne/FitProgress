import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['container', 'total'];
    static values = { index: Number };

    connect() {
        this.updateTotal();
    }

    add() {
        const html = this.containerTarget.dataset.prototype.replace(/__name__/g, this.indexValue);
        this.containerTarget.insertAdjacentHTML('beforeend', html);
        this.indexValue++;
        this.updateTotal();
    }

    remove(event) {
        event.currentTarget.closest('[data-meal-item]')?.remove();
        this.updateTotal();
    }

    updateTotal() {
        const total = Array.from(this.containerTarget.querySelectorAll('input[name$="[calories]"]'))
            .reduce((sum, input) => sum + (Number.parseInt(input.value, 10) || 0), 0);

        this.totalTarget.textContent = total.toLocaleString('fr-FR');
    }
}
