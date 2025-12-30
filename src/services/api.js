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
		testCreateNextcloudUser: (data) => axios.post(`${apiBase}/clients/test-create-nc-user`, data, {
			headers: {
				'Content-Type': 'application/json',
			},
		}),
		getNextcloudGroups: () => axios.get(`${apiBase}/clients/nextcloud-groups`),
		byClient: {
			projects: (clientId) => axios.get(`${apiBase}/clients/${clientId}/projects`),
			tasks: (clientId) => axios.get(`${apiBase}/clients/${clientId}/tasks`),
			payments: (clientId) => axios.get(`${apiBase}/clients/${clientId}/payments`),
			invoices: (clientId) => axios.get(`${apiBase}/clients/${clientId}/invoices`),
			notes: {
				getAll: (clientId) => axios.get(`${apiBase}/clients/${clientId}/notes`),
				get: (clientId, id) => axios.get(`${apiBase}/clients/${clientId}/notes/${id}`),
				create: (clientId, data) => axios.post(`${apiBase}/clients/${clientId}/notes`, toFormData(data), {
					headers: {
						'Content-Type': 'application/x-www-form-urlencoded',
					},
				}),
				update: (clientId, id, data) => axios.put(`${apiBase}/clients/${clientId}/notes/${id}`, toFormData(data), {
					headers: {
						'Content-Type': 'application/x-www-form-urlencoded',
					},
				}),
				delete: (clientId, id) => axios.delete(`${apiBase}/clients/${clientId}/notes/${id}`),
			},
			files: {
				getAll: (clientId) => axios.get(`${apiBase}/clients/${clientId}/files`),
				upload: (clientId, file) => {
					const formData = new FormData()
					formData.append('file', file)
					return axios.post(`${apiBase}/clients/${clientId}/files`, formData, {
						headers: {
							'Content-Type': 'multipart/form-data',
						},
					})
				},
				download: (clientId, fileName) => axios.get(`${apiBase}/clients/${clientId}/files/${encodeURIComponent(fileName)}`, {
					responseType: 'blob',
				}),
				delete: (clientId, fileName) => axios.delete(`${apiBase}/clients/${clientId}/files/${encodeURIComponent(fileName)}`),
			},
		},
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
		byClient: (clientId) => axios.get(`${apiBase}/clients/${clientId}/projects`),
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
		getTasks: (id) => axios.get(`${apiBase}/projects/${id}/tasks`),
		getInvoices: (id) => axios.get(`${apiBase}/projects/${id}/invoices`),
		getShares: (id) => axios.get(`${apiBase}/projects/${id}/shares`),
		share: (id, data) => axios.post(`${apiBase}/projects/${id}/shares`, toFormData(data), {
			headers: {
				'Content-Type': 'application/x-www-form-urlencoded',
			},
		}),
		unshare: (id, sharedWithUserId) => axios.delete(`${apiBase}/projects/${id}/shares/${sharedWithUserId}`),
	},
	timeEntries: {
		byProject: (projectId) => axios.get(`${apiBase}/projects/${projectId}/time-entries`),
		getRunning: (projectId) => axios.get(`${apiBase}/projects/${projectId}/time-entries/running`),
		start: (projectId, data) => axios.post(`${apiBase}/projects/${projectId}/time-entries/start`, toFormData(data), {
			headers: {
				'Content-Type': 'application/x-www-form-urlencoded',
			},
		}),
		stop: (projectId) => axios.post(`${apiBase}/projects/${projectId}/time-entries/stop`, {}, {
			headers: {
				'Content-Type': 'application/x-www-form-urlencoded',
			},
		}),
		update: (id, data) => axios.put(`${apiBase}/time-entries/${id}`, toFormData(data), {
			headers: {
				'Content-Type': 'application/x-www-form-urlencoded',
			},
		}),
		delete: (id) => axios.delete(`${apiBase}/time-entries/${id}`),
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
		byClient: (clientId) => axios.get(`${apiBase}/clients/${clientId}/invoices`),
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
		duplicate: (id) => axios.post(`${apiBase}/invoices/${id}/duplicate`),
		downloadPdf: (id) => axios.get(`${apiBase}/invoices/${id}/download-pdf`, { responseType: 'blob' }),
		sendEmail: (id, data) => axios.post(`${apiBase}/invoices/${id}/send-email`, toFormData(data), {
			headers: {
				'Content-Type': 'application/x-www-form-urlencoded',
			},
		}),
		exportReport: () => axios.get(`${apiBase}/invoices/export`),
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
		byClient: (clientId) => axios.get(`${apiBase}/clients/${clientId}/payments`),
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
		get: (id) => axios.get(`${apiBase}/transactions/${id}`),
		byType: (type) => axios.get(`${apiBase}/transactions/type/${type}`),
		byCategory: (categoryId) => axios.get(`${apiBase}/transactions/category/${categoryId}`),
		byProject: (projectId) => axios.get(`${apiBase}/projects/${projectId}/transactions`),
		byClient: (clientId) => axios.get(`${apiBase}/clients/${clientId}/transactions`),
		monthlySummary: (yearMonth) => axios.get(`${apiBase}/transactions/monthly-summary`, {
			params: { yearMonth },
		}),
		summaryByCategory: (yearMonth, type) => axios.get(`${apiBase}/transactions/summary-by-category`, {
			params: { yearMonth, type },
		}),
		create: (data) => axios.post(`${apiBase}/transactions`, toFormData(data), {
			headers: {
				'Content-Type': 'application/x-www-form-urlencoded',
			},
		}),
		update: (id, data) => axios.put(`${apiBase}/transactions/${id}`, toFormData(data), {
			headers: {
				'Content-Type': 'application/x-www-form-urlencoded',
			},
		}),
		delete: (id) => axios.delete(`${apiBase}/transactions/${id}`),
	},
	transactionCategories: {
		getAll: () => axios.get(`${apiBase}/transaction-categories`),
		byType: (type) => axios.get(`${apiBase}/transaction-categories/type/${type}`),
		create: (data) => axios.post(`${apiBase}/transaction-categories`, toFormData(data), {
			headers: {
				'Content-Type': 'application/x-www-form-urlencoded',
			},
		}),
		update: (id, data) => axios.put(`${apiBase}/transaction-categories/${id}`, toFormData(data), {
			headers: {
				'Content-Type': 'application/x-www-form-urlencoded',
			},
		}),
		delete: (id) => axios.delete(`${apiBase}/transaction-categories/${id}`),
	},
	debts: {
		getAll: () => axios.get(`${apiBase}/debts`),
		get: (id) => axios.get(`${apiBase}/debts/${id}`),
		byType: (type) => axios.get(`${apiBase}/debts/type/${type}`),
		upcomingPayments: (days) => axios.get(`${apiBase}/debts/upcoming-payments`, {
			params: { days },
		}),
		overdue: () => axios.get(`${apiBase}/debts/overdue`),
		create: (data) => axios.post(`${apiBase}/debts`, toFormData(data), {
			headers: {
				'Content-Type': 'application/x-www-form-urlencoded',
			},
		}),
		update: (id, data) => axios.put(`${apiBase}/debts/${id}`, toFormData(data), {
			headers: {
				'Content-Type': 'application/x-www-form-urlencoded',
			},
		}),
		delete: (id) => axios.delete(`${apiBase}/debts/${id}`),
		addPayment: (id, data) => axios.post(`${apiBase}/debts/${id}/payments`, toFormData(data), {
			headers: {
				'Content-Type': 'application/x-www-form-urlencoded',
			},
		}),
	},
	debtPayments: {
		delete: (id) => axios.delete(`${apiBase}/debt-payments/${id}`),
	},
	inventory: {
		getAll: () => axios.get(`${apiBase}/inventory`),
		get: (id) => axios.get(`${apiBase}/inventory/${id}`),
		byCategory: (categoryId) => axios.get(`${apiBase}/inventory/category/${categoryId}`),
		byWarehouse: (warehouseId) => axios.get(`${apiBase}/inventory/warehouse/${warehouseId}`),
		create: (data) => axios.post(`${apiBase}/inventory`, toFormData(data), {
			headers: {
				'Content-Type': 'application/x-www-form-urlencoded',
			},
		}),
		update: (id, data) => axios.put(`${apiBase}/inventory/${id}`, toFormData(data), {
			headers: {
				'Content-Type': 'application/x-www-form-urlencoded',
			},
		}),
		delete: (id) => axios.delete(`${apiBase}/inventory/${id}`),
	},
	inventoryImages: {
		getAll: (inventoryId) => axios.get(`${apiBase}/inventory/${inventoryId}/images`),
		upload: (inventoryId, file) => {
			const formData = new FormData()
			formData.append('file', file)
			return axios.post(`${apiBase}/inventory/${inventoryId}/images`, formData)
		},
		setPrimary: (id) => axios.put(`${apiBase}/inventory/images/${id}/primary`),
		updateOrder: (inventoryId, order) => axios.put(`${apiBase}/inventory/${inventoryId}/images/order`, toFormData({ order: JSON.stringify(order) }), {
			headers: {
				'Content-Type': 'application/x-www-form-urlencoded',
			},
		}),
		delete: (id) => axios.delete(`${apiBase}/inventory/images/${id}`),
	},
	inventoryCategories: {
		getAll: () => axios.get(`${apiBase}/categories`),
		create: (data) => axios.post(`${apiBase}/categories`, toFormData(data), {
			headers: {
				'Content-Type': 'application/x-www-form-urlencoded',
			},
		}),
		update: (id, data) => axios.put(`${apiBase}/categories/${id}`, toFormData(data), {
			headers: {
				'Content-Type': 'application/x-www-form-urlencoded',
			},
		}),
		delete: (id) => axios.delete(`${apiBase}/categories/${id}`),
	},
	inventoryWarehouses: {
		getAll: () => axios.get(`${apiBase}/warehouses`),
		create: (data) => axios.post(`${apiBase}/warehouses`, toFormData(data), {
			headers: {
				'Content-Type': 'application/x-www-form-urlencoded',
			},
		}),
		update: (id, data) => axios.put(`${apiBase}/warehouses/${id}`, toFormData(data), {
			headers: {
				'Content-Type': 'application/x-www-form-urlencoded',
			},
		}),
		delete: (id) => axios.delete(`${apiBase}/warehouses/${id}`),
	},
	pos: {
		createTransaction: (data) => axios.post(`${apiBase}/pos/transactions`, toFormData(data), {
			headers: {
				'Content-Type': 'application/x-www-form-urlencoded',
			},
		}),
		getRecentSales: () => axios.get(`${apiBase}/pos/sales`),
		getActiveRentals: () => axios.get(`${apiBase}/pos/rentals`),
		getReturns: () => axios.get(`${apiBase}/pos/returns`),
		returnRental: (id) => axios.post(`${apiBase}/pos/rentals/${id}/return`),
		returnSale: (id) => axios.post(`${apiBase}/pos/sales/${id}/return`),
		getMovementsByInventory: (inventoryId) => axios.get(`${apiBase}/pos/inventory/${inventoryId}/movements`),
	},
	settings: {
		get: () => axios.get(`${apiBase}/settings`),
		update: (data) => axios.put(`${apiBase}/settings`, toFormData(data), {
			headers: {
				'Content-Type': 'application/x-www-form-urlencoded',
			},
		}),
	},
	users: {
		getAll: () => axios.get(`${apiBase}/users`),
	},
	projectShares: {
		index: (projectId) => axios.get(`${apiBase}/projects/${projectId}/shares`),
		share: (projectId, data) => axios.post(`${apiBase}/projects/${projectId}/shares`, toFormData(data), {
			headers: {
				'Content-Type': 'application/x-www-form-urlencoded',
			},
		}),
		unshare: (projectId, sharedWithUserId) => axios.delete(`${apiBase}/projects/${projectId}/shares/${sharedWithUserId}`),
	},
	projectNotes: {
		getAll: (projectId, category = null) => {
			let url = `${apiBase}/projects/${projectId}/notes`
			if (category) {
				url += `?category=${encodeURIComponent(category)}`
			}
			return axios.get(url)
		},
		get: (projectId, id) => axios.get(`${apiBase}/projects/${projectId}/notes/${id}`),
		create: (projectId, data) => axios.post(`${apiBase}/projects/${projectId}/notes`, toFormData(data), {
			headers: {
				'Content-Type': 'application/x-www-form-urlencoded',
			},
		}),
		update: (projectId, id, data) => axios.put(`${apiBase}/projects/${projectId}/notes/${id}`, toFormData(data), {
			headers: {
				'Content-Type': 'application/x-www-form-urlencoded',
			},
		}),
		delete: (projectId, id) => axios.delete(`${apiBase}/projects/${projectId}/notes/${id}`),
	},
	projectFiles: {
		getAll: (projectId, category = null) => {
			let url = `${apiBase}/projects/${projectId}/files`
			if (category) {
				url += `?category=${encodeURIComponent(category)}`
			}
			return axios.get(url)
		},
		get: (projectId, id) => axios.get(`${apiBase}/projects/${projectId}/files/${id}`),
		upload: (projectId, file, category = 'general', description = '') => {
			const formData = new FormData()
			formData.append('file', file)
			formData.append('category', category)
			formData.append('description', description)
			return axios.post(`${apiBase}/projects/${projectId}/files`, formData)
		},
		delete: (projectId, id) => axios.delete(`${apiBase}/projects/${projectId}/files/${id}`),
	},
	projectActivities: {
		getAll: (projectId, type = null) => {
			let url = `${apiBase}/projects/${projectId}/activities`
			if (type) {
				url += `?type=${encodeURIComponent(type)}`
			}
			return axios.get(url)
		},
	},
}

