import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['water', 'waterProgress', 'weight', 'weightWheel'];
    static values = { hydrationGoal: Number };

    connect() {
        this.updateWaterProgress();
        this.updateWeightWheel();
    }

    incrementWater() {
        this.changeWater(100);
    }

    decrementWater() {
        this.changeWater(-100);
    }

    changeWater(amount) {
        const value = Number.parseInt(this.waterTarget.value, 10) || 0;
        this.waterTarget.value = Math.max(0, value + amount);
        this.waterTarget.dispatchEvent(new Event('input', { bubbles: true }));
        this.updateWaterProgress();
    }

    incrementWeight() {
        this.changeWeight(0.1);
    }

    decrementWeight() {
        this.changeWeight(-0.1);
    }

    startWeightDrag(event) {
        this.dragStartX = event.clientX;
        this.dragStartWeight = Number.parseFloat(this.weightTarget.value) || 0;
        event.currentTarget.setPointerCapture(event.pointerId);
    }

    moveWeightDrag(event) {
        if (this.dragStartX === undefined) return;

        const steps = Math.trunc((event.clientX - this.dragStartX) / 12);
        this.setWeight(this.dragStartWeight - steps * 0.1);
    }

    stopWeightDrag() {
        this.dragStartX = undefined;
        this.dragStartWeight = undefined;
    }

    adjustWeight(event) {
        event.preventDefault();
        const increase = Math.abs(event.deltaX) > Math.abs(event.deltaY)
            ? event.deltaX < 0
            : event.deltaY < 0;
        this.changeWeight(increase ? 0.1 : -0.1);
    }

    changeWeight(amount) {
        const value = Number.parseFloat(this.weightTarget.value) || 0;
        this.setWeight(value + amount);
    }

    setWeight(value) {
        this.weightTarget.value = Math.max(0, Math.min(500, value)).toFixed(1);
        this.weightTarget.dispatchEvent(new Event('input', { bubbles: true }));
        this.updateWeightWheel();
    }

    updateWaterProgress() {
        if (!this.hasWaterProgressTarget || !this.hasWaterTarget) return;

        const value = Number.parseInt(this.waterTarget.value, 10) || 0;
        const goal = this.hydrationGoalValue || 2000;
        this.waterProgressTarget.style.width = `${Math.min(100, value / goal * 100)}%`;
    }

    updateWeightWheel() {
        if (!this.hasWeightWheelTarget || !this.hasWeightTarget) return;

        const value = Number.parseFloat(this.weightTarget.value) || 0;
        this.weightWheelTarget.style.backgroundPositionX = `${value * 10}px`;
    }
}
