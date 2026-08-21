import { startStimulusApp } from '@symfony/stimulus-bundle';

const app = startStimulusApp();
const registeredControllers = new Set();

const lazyControllers = {
    'dashboard-actions': () => import('./controllers/dashboard_actions_controller.js'),
    'dashboard-chart': () => import('./controllers/dashboard_chart_controller.js'),
    'journal-meals': () => import('./controllers/journal_meals_controller.js'),
    accessibility: () => import('./controllers/accessibility_controller.js'),
    'coach-contact': () => import('./controllers/coach_contact_controller.js'),
};

const registerPageControllers = () => {
    const requestedControllers = new Set();

    document.querySelectorAll('[data-controller]').forEach((element) => {
        element.dataset.controller.split(/\s+/).forEach((name) => requestedControllers.add(name));
    });

    requestedControllers.forEach((name) => {
        if (!lazyControllers[name] || registeredControllers.has(name)) return;

        registeredControllers.add(name);
        lazyControllers[name]().then(({ default: controller }) => app.register(name, controller));
    });
};

registerPageControllers();
document.addEventListener('turbo:load', registerPageControllers);
