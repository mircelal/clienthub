/**
 * API Service for Vue.js components
 * Uses Nextcloud's axios instance
 */

import axios from '@nextcloud/axios'
import { generateUrl } from '@nextcloud/router'

const apiBase = generateUrl('/apps/domaincontrol/api')

export default {
	clients: {
		getAll: () => axios.get(`${apiBase}/clients`),
		get: (id) => axios.get(`${apiBase}/clients/${id}`),
		create: (data) => axios.post(`${apiBase}/clients`, data),
		update: (id, data) => axios.put(`${apiBase}/clients/${id}`, data),
		delete: (id) => axios.delete(`${apiBase}/clients/${id}`),
	},
	domains: {
		getAll: () => axios.get(`${apiBase}/domains`),
		get: (id) => axios.get(`${apiBase}/domains/${id}`),
	},
	hostings: {
		getAll: () => axios.get(`${apiBase}/hostings`),
		get: (id) => axios.get(`${apiBase}/hostings/${id}`),
	},
	websites: {
		getAll: () => axios.get(`${apiBase}/websites`),
		get: (id) => axios.get(`${apiBase}/websites/${id}`),
	},
	services: {
		getAll: () => axios.get(`${apiBase}/services`),
		get: (id) => axios.get(`${apiBase}/services/${id}`),
	},
	projects: {
		getAll: () => axios.get(`${apiBase}/projects`),
		getActive: () => axios.get(`${apiBase}/projects/active`),
	},
	tasks: {
		getAll: () => axios.get(`${apiBase}/tasks`),
		getPending: () => axios.get(`${apiBase}/tasks/pending`),
	},
	invoices: {
		getAll: () => axios.get(`${apiBase}/invoices`),
		getUnpaid: () => axios.get(`${apiBase}/invoices/unpaid`),
		getOverdue: () => axios.get(`${apiBase}/invoices/overdue`),
		getUpcoming: () => axios.get(`${apiBase}/invoices/upcoming`),
	},
	payments: {
		getAll: () => axios.get(`${apiBase}/payments`),
		getMonthlyTotal: () => axios.get(`${apiBase}/payments/monthly-total`),
	},
	transactions: {
		getAll: () => axios.get(`${apiBase}/transactions`),
		getMonthlySummary: () => axios.get(`${apiBase}/transactions/monthly-summary`),
	},
	debts: {
		getAll: () => axios.get(`${apiBase}/debts`),
		getUpcomingPayments: () => axios.get(`${apiBase}/debts/upcoming-payments`),
	},
}

