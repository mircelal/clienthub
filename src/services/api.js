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

