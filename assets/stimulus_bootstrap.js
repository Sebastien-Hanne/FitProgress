import { startStimulusApp } from '@symfony/stimulus-bundle';
import DashboardActionsController from './controllers/dashboard_actions_controller.js';
import DashboardChartController from './controllers/dashboard_chart_controller.js';
import JournalMealsController from './controllers/journal_meals_controller.js';
import JournalEntryController from './controllers/journal_entry_controller.js';

const app = startStimulusApp();
app.register('dashboard-actions', DashboardActionsController);
app.register('dashboard-chart', DashboardChartController);
app.register('journal-meals', JournalMealsController);
app.register('journal-entry', JournalEntryController);
