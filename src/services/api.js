/**
 * API Service for Vue.js components
 * Uses Nextcloud's axios instance
 * Backend expects application/x-www-form-urlencoded format
 */

import axios from '@nextcloud/axios'
import { generateUrl } from '@nextcloud/router'

const apiBase = generateUrl('/apps/domaincontrol/api')

// Helper function to convert object to URLSearchParams
function toFormData(data) {
	const params = new URLSearchParams()
	for (const key in data) {
		if (data[key] !== null && data[key] !== undefined) {
			params.append(key, data[key])
		}
	}
	return params.toString()
}

export default {
	clients: {
		getAll: () => axios.get(`${apiBase}/clients`),
		get: (id) => axios.get(`${apiBase}/clients/${id}`),
		create: (data) => axios.post(`${apiBase}/clients`, toFormData(data), {
			headers: {
				'Content-Type': 'application/x-www-form-urlencoded',
			},
		}),
		update: (id, data) => axios.put(`${apiBase}/clients/${id}`, toFormData(data), {
			headers: {
				'Content-Type': 'application/x-www-form-urlencoded',
			},
		}),
		delete: (id) => axios.delete(`${apiBase}/clients/${id}`),
	},
	domains: {
		getAll: () => axios.get(`${apiBase}/domains`),
		get: (id) => axios.get(`${apiBase}/domains/${id}`),
		create: (data) => axios.post(`${apiBase}/domains`, toFormData(data), {
			headers: {
				'Content-Type': 'application/x-www-form-urlencoded',
			},
		}),
		update: (id, data) => axios.put(`${apiBase}/domains/${id}`, toFormData(data), {
			headers: {
				'Content-Type': 'application/x-www-form-urlencoded',
			},
		}),
		delete: (id) => axios.delete(`${apiBase}/domains/${id}`),
		extend: (id, data) => axios.post(`${apiBase}/domains/${id}/extend`, toFormData(data), {
			headers: {
				'Content-Type': 'application/x-www-form-urlencoded',
			},
		}),
	},
	hostings: {
		getAll: () => axios.get(`${apiBase}/hostings`),
		get: (id) => axios.get(`${apiBase}/hostings/${id}`),
		create: (data) => axios.post(`${apiBase}/hostings`, toFormData(data), {
			headers: {
				'Content-Type': 'application/x-www-form-urlencoded',
			},
		}),
		update: (id, data) => axios.put(`${apiBase}/hostings/${id}`, toFormData(data), {
			headers: {
				'Content-Type': 'application/x-www-form-urlencoded',
			},
		}),
		delete: (id) => axios.delete(`${apiBase}/hostings/${id}`),
		addPayment: (id, data) => axios.post(`${apiBase}/hostings/${id}/payment`, toFormData(data), {
			headers: {
				'Content-Type': 'application/x-www-form-urlencoded',
			},
		}),
	},
	hostingPackages: {
		getAll: () => axios.get(`${apiBase}/hosting-packages`),
		get: (id) => axios.get(`${apiBase}/hosting-packages/${id}`),
		create: (data) => axios.post(`${apiBase}/hosting-packages`, toFormData(data), {
			headers: {
				'Content-Type': 'application/x-www-form-urlencoded',
			},
		}),
		update: (id, data) => axios.put(`${apiBase}/hosting-packages/${id}`, toFormData(data), {
			headers: {
				'Content-Type': 'application/x-www-form-urlencoded',
			},
		}),
		delete: (id) => axios.delete(`${apiBase}/hosting-packages/${id}`),
	},
	websites: {
		getAll: () => axios.get(`${apiBase}/websites`),
		get: (id) => axios.get(`${apiBase}/websites/${id}`),
		create: (data) => axios.post(`${apiBase}/websites`, toFormData(data), {
			headers: {
				'Content-Type': 'application/x-www-form-urlencoded',
			},
		}),
		update: (id, data) => axios.put(`${apiBase}/websites/${id}`, toFormData(data), {
			headers: {
				'Content-Type': 'application/x-www-form-urlencoded',
			},
		}),
		delete: (id) => axios.delete(`${apiBase}/websites/${id}`),
	},
	services: {
		getAll: () => axios.get(`${apiBase}/services`),
		get: (id) => axios.get(`${apiBase}/services/${id}`),
		create: (data) => axios.post(`${apiBase}/services`, toFormData(data), {
			headers: {
				'Content-Type': 'application/x-www-form-urlencoded',
			},
		}),
		update: (id, data) => axios.put(`${apiBase}/services/${id}`, toFormData(data), {
			headers: {
				'Content-Type': 'application/x-www-form-urlencoded',
			},
		}),
		delete: (id) => axios.delete(`${apiBase}/services/${id}`),
		extend: (id, data) => axios.post(`${apiBase}/services/${id}/extend`, toFormData(data), {
			headers: {
				'Content-Type': 'application/x-www-form-urlencoded',
			},
		}),
		expiringSoon: () => axios.get(`${apiBase}/services/expiring-soon`),
	},
	serviceTypes: {
		getAll: () => axios.get(`${apiBase}/service-types`),
		get: (id) => axios.get(`${apiBase}/service-types/${id}`),
		create: (data) => axios.post(`${apiBase}/service-types`, toFormData(data), {
			headers: {
				'Content-Type': 'application/x-www-form-urlencoded',
			},
		}),
		update: (id, data) => axios.put(`${apiBase}/service-types/${id}`, toFormData(data), {
			headers: {
				'Content-Type': 'application/x-www-form-urlencoded',
			},
		}),
		delete: (id) => axios.delete(`${apiBase}/service-types/${id}`),
		initPredefined: () => axios.post(`${apiBase}/service-types/init`, {}, {
			headers: {
				'Content-Type': 'application/x-www-form-urlencoded',
			},
		}),
	},
	projects: {
		getAll: () => axios.get(`${apiBase}/projects`),
		get: (id) => axios.get(`${apiBase}/projects/${id}`),
		getActive: () => axios.get(`${apiBase}/projects/active`),
		approachingDeadline: () => axios.get(`${apiBase}/projects/approaching-deadline`),
		create: (data) => axios.post(`${apiBase}/projects`, toFormData(data), {
			headers: {
				'Content-Type': 'application/x-www-form-urlencoded',
			},
		}),
		update: (id, data) => axios.put(`${apiBase}/projects/${id}`, toFormData(data), {
			headers: {
				'Content-Type': 'application/x-www-form-urlencoded',
			},
		}),
		delete: (id) => axios.delete(`${apiBase}/projects/${id}`),
		getItems: (id) => axios.get(`${apiBase}/projects/${id}/items`),
		addItem: (id, data) => axios.post(`${apiBase}/projects/${id}/items`, toFormData(data), {
			headers: {
				'Content-Type': 'application/x-www-form-urlencoded',
			},
		}),
		removeItem: (id, itemId) => axios.delete(`${apiBase}/projects/${id}/items/${itemId}`),
	},
	tasks: {
		getAll: () => axios.get(`${apiBase}/tasks`),
		get: (id) => axios.get(`${apiBase}/tasks/${id}`),
		getPending: () => axios.get(`${apiBase}/tasks/pending`),
		approachingDeadline: () => axios.get(`${apiBase}/tasks/approaching-deadline`),
		overdue: () => axios.get(`${apiBase}/tasks/overdue`),
		byProject: (projectId) => axios.get(`${apiBase}/projects/${projectId}/tasks`),
		byClient: (clientId) => axios.get(`${apiBase}/clients/${clientId}/tasks`),
		getSubtasks: (id) => axios.get(`${apiBase}/tasks/${id}/subtasks`),
		create: (data) => axios.post(`${apiBase}/tasks`, toFormData(data), {
			headers: {
				'Content-Type': 'application/x-www-form-urlencoded',
			},
		}),
		update: (id, data) => axios.put(`${apiBase}/tasks/${id}`, toFormData(data), {
			headers: {
				'Content-Type': 'application/x-www-form-urlencoded',
			},
		}),
		delete: (id) => axios.delete(`${apiBase}/tasks/${id}`),
		toggleStatus: (id) => axios.post(`${apiBase}/tasks/${id}/toggle`),
	},
	invoices: {
		getAll: () => axios.get(`${apiBase}/invoices`),
		get: (id) => axios.get(`${apiBase}/invoices/${id}`),
		getUnpaid: () => axios.get(`${apiBase}/invoices/unpaid`),
		getOverdue: () => axios.get(`${apiBase}/invoices/overdue`),
		getUpcoming: () => axios.get(`${apiBase}/invoices/upcoming`),
		create: (data) => axios.post(`${apiBase}/invoices`, toFormData(data), {
			headers: {
				'Content-Type': 'application/x-www-form-urlencoded',
			},
		}),
		update: (id, data) => axios.put(`${apiBase}/invoices/${id}`, toFormData(data), {
			headers: {
				'Content-Type': 'application/x-www-form-urlencoded',
			},
		}),
		delete: (id) => axios.delete(`${apiBase}/invoices/${id}`),
		getItems: (id) => axios.get(`${apiBase}/invoices/${id}/items`),
		addItem: (id, data) => axios.post(`${apiBase}/invoices/${id}/items`, toFormData(data), {
			headers: {
				'Content-Type': 'application/x-www-form-urlencoded',
			},
		}),
		removeItem: (id, itemId) => axios.delete(`${apiBase}/invoices/${id}/items/${itemId}`),
		getPayments: (id) => axios.get(`${apiBase}/invoices/${id}/payments`),
	},
	payments: {
		getAll: () => axios.get(`${apiBase}/payments`),
		get: (id) => axios.get(`${apiBase}/payments/${id}`),
		getMonthlyTotal: () => axios.get(`${apiBase}/payments/monthly-total`),
		create: (data) => axios.post(`${apiBase}/payments`, toFormData(data), {
			headers: {
				'Content-Type': 'application/x-www-form-urlencoded',
			},
		}),
		update: (id, data) => axios.put(`${apiBase}/payments/${id}`, toFormData(data), {
			headers: {
				'Content-Type': 'application/x-www-form-urlencoded',
			},
		}),
		delete: (id) => axios.delete(`${apiBase}/payments/${id}`),
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

