import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['alert', 'dialog', 'dialogTitle', 'dialogContent', 'notificationDot'];

    connect() {
        if (this.hasAlertTarget && sessionStorage.getItem('fitprogress-weight-alert-dismissed') === 'true') {
            this.alertTarget.remove();
        }
    }

    dismissAlert() {
        sessionStorage.setItem('fitprogress-weight-alert-dismissed', 'true');
        this.alertTarget.remove();
    }

    open(event) {
        const { title, content } = event.currentTarget.dataset;

        this.dialogTitleTarget.textContent = title;
        this.dialogContentTarget.textContent = content;
        this.dialogTarget.showModal();

        if (event.currentTarget.dataset.actionName === 'notifications' && this.hasNotificationDotTarget) {
            this.notificationDotTarget.classList.add('hidden');
        }
    }

    close() {
        this.dialogTarget.close();
    }

    closeOnBackdrop(event) {
        if (event.target === this.dialogTarget) {
            this.close();
        }
    }

    exportCsv() {
        const rows = [
            ['Date', 'Poids (kg)'],
            ...JSON.parse(this.element.dataset.labels).map((label, index) => [
                label,
                JSON.parse(this.element.dataset.weights)[index],
            ]),
        ];
        const csv = rows.map((row) => row.join(';')).join('\n');
        const url = URL.createObjectURL(new Blob([`\uFEFF${csv}`], { type: 'text/csv;charset=utf-8' }));
        const link = document.createElement('a');

        link.href = url;
        link.download = 'suivi-poids-fitprogress.csv';
        link.click();
        URL.revokeObjectURL(url);
    }
}
