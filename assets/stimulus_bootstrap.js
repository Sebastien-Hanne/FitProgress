import { startStimulusApp } from '@symfony/stimulus-bundle';
import DashboardActionsController from './controllers/dashboard_actions_controller.js';
import DashboardChartController from './controllers/dashboard_chart_controller.js';

const app = startStimulusApp();
app.register('dashboard-actions', DashboardActionsController);
app.register('dashboard-chart', DashboardChartController);
