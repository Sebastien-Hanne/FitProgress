const initializedJournals = new WeakSet();

const initializeJournal = () => {
    const journal = document.querySelector('[data-journal-page]');
    if (!journal || initializedJournals.has(journal)) return;
    initializedJournals.add(journal);
    const dayStrip = document.querySelector('[data-journal-day-strip]');
    const selectedDay = dayStrip?.querySelector('[data-journal-selected-day]');
    if (dayStrip && selectedDay && dayStrip.scrollWidth > dayStrip.clientWidth) {
        dayStrip.scrollLeft = selectedDay.offsetLeft - (dayStrip.clientWidth - selectedDay.clientWidth) / 2;
    }
    const mealContainer = journal.querySelector('[data-journal-meals-target="container"]');
    const calorieTotal = journal.querySelector('[data-journal-meals-target="total"]');
    let mealIndex = Number.parseInt(journal.dataset.journalMealsIndexValue, 10) || 0;

    const updateCalories = () => {
        if (!mealContainer || !calorieTotal) return;
        const total = Array.from(mealContainer.querySelectorAll('input[name$="[calories]"]'))
            .reduce((sum, field) => sum + (Number.parseInt(field.value, 10) || 0), 0);
        calorieTotal.textContent = total.toLocaleString('fr-FR');
    };

    journal.querySelector('[data-journal-add-meal]')?.addEventListener('click', () => {
        if (!mealContainer) return;
        mealContainer.insertAdjacentHTML('beforeend', mealContainer.dataset.prototype.replace(/__name__/g, mealIndex));
        mealIndex++;
        updateCalories();
    });

    mealContainer?.addEventListener('click', (event) => {
        const removeButton = event.target.closest('[data-journal-remove-meal]');
        if (!removeButton) return;
        removeButton.closest('[data-meal-item]')?.remove();
        updateCalories();
    });
    mealContainer?.addEventListener('input', updateCalories);
    const mealIcons = { breakfast: '🍞', lunch: '🍔', dinner: '🍽️', snack: '🍎' };
    mealContainer?.addEventListener('change', (event) => {
        if (!event.target.matches('[data-meal-type-select]')) return;
        const icon = event.target.closest('[data-meal-item]')?.querySelector('[data-meal-type-icon]');
        if (icon) icon.textContent = mealIcons[event.target.value] || '🍽️';
    });

    const waterInput = journal.querySelector('[data-journal-water]');
    const waterProgress = journal.querySelector('[data-journal-water-progress]');
    const hydrationGoal = Number.parseInt(journal.dataset.hydrationGoal, 10) || 2000;
    const updateWater = () => {
        if (!waterInput || !waterProgress) return;
        const value = Number.parseInt(waterInput.value, 10) || 0;
        waterProgress.style.width = `${Math.min(100, value / hydrationGoal * 100)}%`;
    };
    journal.querySelectorAll('[data-water-change]').forEach((button) => {
        button.addEventListener('click', () => {
            const value = Number.parseInt(waterInput?.value, 10) || 0;
            if (waterInput) waterInput.value = Math.max(0, value + Number.parseInt(button.dataset.waterChange, 10));
            updateWater();
        });
    });
    waterInput?.addEventListener('input', updateWater);

    const weightInput = journal.querySelector('[data-journal-weight]');
    const weightWheel = journal.querySelector('[data-journal-weight-wheel]');
    let dragStartX;
    let dragStartWeight;
    const setWeight = (value) => {
        if (!weightInput) return;
        weightInput.value = Math.max(0, Math.min(500, value)).toFixed(1);
        if (weightWheel) weightWheel.style.backgroundPositionX = `${value * 10}px`;
    };
    journal.querySelectorAll('[data-weight-change]').forEach((button) => {
        button.addEventListener('click', () => setWeight((Number.parseFloat(weightInput?.value) || 0) + Number.parseFloat(button.dataset.weightChange)));
    });
    weightWheel?.addEventListener('pointerdown', (event) => {
        dragStartX = event.clientX;
        dragStartWeight = Number.parseFloat(weightInput?.value) || 0;
        weightWheel.setPointerCapture(event.pointerId);
    });
    weightWheel?.addEventListener('pointermove', (event) => {
        if (dragStartX === undefined) return;
        // Convention FitProgress : droite = augmentation, gauche = diminution.
        setWeight(dragStartWeight + Math.trunc((event.clientX - dragStartX) / 12) * 0.1);
    });
    const stopDragging = () => { dragStartX = undefined; dragStartWeight = undefined; };
    weightWheel?.addEventListener('pointerup', stopDragging);
    weightWheel?.addEventListener('pointercancel', stopDragging);
    weightWheel?.addEventListener('wheel', (event) => {
        event.preventDefault();
        const increase = Math.abs(event.deltaX) > Math.abs(event.deltaY)
            ? event.deltaX > 0
            : event.deltaY < 0;
        setWeight((Number.parseFloat(weightInput?.value) || 0) + (increase ? 0.1 : -0.1));
    }, { passive: false });
    weightInput?.addEventListener('input', () => {
        if (weightWheel) weightWheel.style.backgroundPositionX = `${(Number.parseFloat(weightInput.value) || 0) * 10}px`;
    });

    const energyLabels = ['Très faible', 'Faible', 'Stable', 'Concentré', 'Dynamique'];
    const energyStatus = journal.querySelector('[data-energy-status]');
    const updateEnergy = () => {
        const selected = journal.querySelector('input[name$="[energyLevel]"]:checked');
        if (energyStatus) energyStatus.textContent = selected ? energyLabels[Number.parseInt(selected.value, 10) - 1] : 'Sélectionnez votre énergie';
    };
    journal.querySelectorAll('input[name$="[energyLevel]"]').forEach((input) => input.addEventListener('change', updateEnergy));

    const activityInput = journal.querySelector('[data-journal-activity]');
    const activityRing = journal.querySelector('[data-journal-activity-ring]');
    const updateActivity = () => {
        const minutes = Number.parseInt(activityInput?.value, 10) || 0;
        activityRing?.style.setProperty('--activity-progress', `${Math.min(100, minutes / 60 * 100)}%`);
    };
    activityInput?.addEventListener('input', updateActivity);

    const sleepQuality = journal.querySelector('[data-sleep-quality]');
    const updateSleepQuality = () => {
        const selected = sleepQuality?.querySelector('input:checked');
        const selectedValue = Number.parseInt(selected?.value, 10) || 0;
        sleepQuality?.querySelectorAll('label').forEach((label, index) => {
            label.classList.toggle('is-filled', index < selectedValue);
        });
    };
    sleepQuality?.querySelectorAll('input').forEach((input) => input.addEventListener('change', updateSleepQuality));

    updateCalories();
    updateWater();
    updateEnergy();
    updateActivity();
    updateSleepQuality();
    if (weightWheel) weightWheel.style.backgroundPositionX = `${(Number.parseFloat(weightInput?.value) || 0) * 10}px`;
};

document.addEventListener('turbo:load', initializeJournal);
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeJournal, { once: true });
} else {
    initializeJournal();
}
