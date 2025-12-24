<template>
	<div class="reports-view">
		<div class="reports-container">
			<div class="reports-header">
				<h2 class="reports-title">
					<MaterialIcon name="accounting" :size="32" />
					{{ translate('domaincontrol', 'Reports and Analytics') }}
				</h2>
				<p class="reports-subtitle">
					{{ translate('domaincontrol', 'Analyze your business performance and make data-driven decisions') }}
				</p>
			</div>

			<!-- Date Filters -->
			<div class="reports-filters">
				<div class="filter-card">
					<div class="form-row">
						<div class="form-group">
							<label for="report-period" class="form-label">{{ translate('domaincontrol', 'Period') }}</label>
							<select
								id="report-period"
								v-model="currentPeriod"
								class="form-control"
								@change="onPeriodChange"
							>
								<option value="month">{{ translate('domaincontrol', 'This Month') }}</option>
								<option value="quarter">{{ translate('domaincontrol', 'This Quarter') }}</option>
								<option value="year">{{ translate('domaincontrol', 'This Year') }}</option>
								<option value="custom">{{ translate('domaincontrol', 'Custom Date') }}</option>
							</select>
						</div>
						<div v-if="currentPeriod === 'custom'" class="form-group">
							<label for="report-start-date" class="form-label">{{ translate('domaincontrol', 'Start') }}</label>
							<input
								id="report-start-date"
								v-model="startDate"
								type="date"
								class="form-control"
							/>
						</div>
						<div v-if="currentPeriod === 'custom'" class="form-group">
							<label for="report-end-date" class="form-label">{{ translate('domaincontrol', 'End') }}</label>
							<input
								id="report-end-date"
								v-model="endDate"
								type="date"
								class="form-control"
							/>
						</div>
						<div class="form-group">
							<label class="form-label">&nbsp;</label>
							<button class="button-vue button-vue--primary" @click="loadReports">
								<MaterialIcon name="filter" :size="18" />
								{{ translate('domaincontrol', 'Filter') }}
							</button>
						</div>
					</div>
				</div>
			</div>

			<!-- Loading State -->
			<div v-if="loading" class="loading-content">
				<MaterialIcon name="loading" :size="32" class="loading-icon" />
				<p>{{ translate('domaincontrol', 'Loading reports...') }}</p>
			</div>

			<!-- Reports Content -->
			<div v-else>
				<!-- Summary Statistics -->
				<div class="report-section">
					<div class="section-header">
						<h3 class="section-title">
							<MaterialIcon name="accounting" :size="24" />
							{{ translate('domaincontrol', 'Summary Statistics') }}
						</h3>
						<p class="section-description">{{ translate('domaincontrol', 'General business performance indicators') }}</p>
					</div>
					<div class="report-cards">
						<div class="stat-card stat-card--success">
							<div class="stat-card__icon">
								<MaterialIcon name="accounting" :size="32" />
							</div>
							<div class="stat-card__content">
								<div class="stat-card__value">{{ formatCurrency(reportData.totalIncome) }}</div>
								<div class="stat-card__label">{{ translate('domaincontrol', 'Total Income') }}</div>
								<div class="stat-card__subtitle">{{ translate('domaincontrol', 'All time') }}</div>
							</div>
						</div>
						<div class="stat-card stat-card--info">
							<div class="stat-card__icon">
								<MaterialIcon name="calendar" :size="32" />
							</div>
							<div class="stat-card__content">
								<div class="stat-card__value">{{ formatCurrency(reportData.monthlyIncome) }}</div>
								<div class="stat-card__label">{{ translate('domaincontrol', 'Monthly Income') }}</div>
								<div class="stat-card__subtitle">{{ translate('domaincontrol', 'This month') }}</div>
							</div>
						</div>
						<div class="stat-card stat-card--warning">
							<div class="stat-card__icon">
								<MaterialIcon name="calendar" :size="32" />
							</div>
							<div class="stat-card__content">
								<div class="stat-card__value">{{ formatCurrency(reportData.pendingIncome) }}</div>
								<div class="stat-card__label">{{ translate('domaincontrol', 'Pending Payments') }}</div>
								<div class="stat-card__subtitle">{{ translate('domaincontrol', 'Unpaid invoices') }}</div>
							</div>
						</div>
						<div class="stat-card stat-card--primary">
							<div class="stat-card__icon">
								<MaterialIcon name="contacts" :size="32" />
							</div>
							<div class="stat-card__content">
								<div class="stat-card__value">{{ reportData.totalClients }}</div>
								<div class="stat-card__label">{{ translate('domaincontrol', 'Total Clients') }}</div>
								<div class="stat-card__subtitle">{{ reportData.activeClients }} {{ translate('domaincontrol', 'active') }}</div>
							</div>
						</div>
					</div>
				</div>

				<!-- Income Trend -->
				<div class="report-section">
					<div class="section-header">
						<h3 class="section-title">
							<MaterialIcon name="accounting" :size="24" />
							{{ translate('domaincontrol', 'Income Trend') }}
						</h3>
						<p class="section-description">{{ translate('domaincontrol', 'Monthly income trend and comparisons') }}</p>
					</div>
					<div class="report-chart-container">
						<canvas ref="incomeTrendChart"></canvas>
					</div>
				</div>

				<!-- Invoice Status -->
				<div class="report-section">
					<div class="section-header">
						<h3 class="section-title">
							<MaterialIcon name="files" :size="24" />
							{{ translate('domaincontrol', 'Invoice Status') }}
						</h3>
						<p class="section-description">{{ translate('domaincontrol', 'Invoice statuses and payment tracking') }}</p>
					</div>
					<div class="report-cards">
						<div class="stat-card stat-card--primary">
							<div class="stat-card__icon">
								<MaterialIcon name="files" :size="32" />
							</div>
							<div class="stat-card__content">
								<div class="stat-card__value">{{ reportData.totalInvoices }}</div>
								<div class="stat-card__label">{{ translate('domaincontrol', 'Total Invoices') }}</div>
							</div>
						</div>
						<div class="stat-card stat-card--success">
							<div class="stat-card__icon">
								<MaterialIcon name="checkmark" :size="32" />
							</div>
							<div class="stat-card__content">
								<div class="stat-card__value">{{ reportData.paidInvoices }}</div>
								<div class="stat-card__label">{{ translate('domaincontrol', 'Paid') }}</div>
							</div>
						</div>
						<div class="stat-card stat-card--danger">
							<div class="stat-card__icon">
								<MaterialIcon name="warning" :size="32" />
							</div>
							<div class="stat-card__content">
								<div class="stat-card__value">{{ reportData.overdueInvoices }}</div>
								<div class="stat-card__label">{{ translate('domaincontrol', 'Overdue') }}</div>
							</div>
						</div>
						<div class="stat-card stat-card--warning">
							<div class="stat-card__icon">
								<MaterialIcon name="calendar" :size="32" />
							</div>
							<div class="stat-card__content">
								<div class="stat-card__value">{{ reportData.pendingInvoices }}</div>
								<div class="stat-card__label">{{ translate('domaincontrol', 'Pending') }}</div>
							</div>
						</div>
					</div>
					<div class="report-chart-container">
						<canvas ref="invoiceStatusChart"></canvas>
					</div>
				</div>

				<!-- Client Analysis -->
				<div class="report-section">
					<div class="section-header">
						<h3 class="section-title">
							<MaterialIcon name="contacts" :size="24" />
							{{ translate('domaincontrol', 'Client Analysis') }}
						</h3>
						<p class="section-description">{{ translate('domaincontrol', 'Client statistics and top revenue generators') }}</p>
					</div>
					<div class="report-cards">
						<div class="stat-card stat-card--success">
							<div class="stat-card__icon">
								<MaterialIcon name="contacts" :size="32" />
							</div>
							<div class="stat-card__content">
								<div class="stat-card__value">{{ reportData.activeClients }}</div>
								<div class="stat-card__label">{{ translate('domaincontrol', 'Active Clients') }}</div>
							</div>
						</div>
						<div class="stat-card stat-card--info">
							<div class="stat-card__icon">
								<MaterialIcon name="accounting" :size="32" />
							</div>
							<div class="stat-card__content">
								<div class="stat-card__value">{{ formatCurrency(reportData.avgClientIncome) }}</div>
								<div class="stat-card__label">{{ translate('domaincontrol', 'Average Client Income') }}</div>
							</div>
						</div>
					</div>
					<div class="report-table-container">
						<div class="table-header">
							<h4>{{ translate('domaincontrol', 'Top Revenue Generating Clients') }}</h4>
						</div>
						<div class="report-list">
							<div
								v-for="(client, index) in topClients"
								:key="client.id"
								class="report-list-item"
							>
								<div class="report-list-item__rank">{{ index + 1 }}</div>
								<div class="report-list-item__content">
									<div class="report-list-item__title">{{ client.name }}</div>
									<div class="report-list-item__subtitle">{{ formatCurrency(client.totalIncome) }}</div>
								</div>
							</div>
							<div v-if="topClients.length === 0" class="empty-message">
								{{ translate('domaincontrol', 'No client data available') }}
							</div>
						</div>
					</div>
					<div class="report-chart-container">
						<canvas ref="topClientsChart"></canvas>
					</div>
				</div>

				<!-- Project Status -->
				<div class="report-section">
					<div class="section-header">
						<h3 class="section-title">
							<MaterialIcon name="folder" :size="24" />
							{{ translate('domaincontrol', 'Project Status') }}
						</h3>
						<p class="section-description">{{ translate('domaincontrol', 'Project statuses and progress tracking') }}</p>
					</div>
					<div class="report-cards">
						<div class="stat-card stat-card--purple">
							<div class="stat-card__icon">
								<MaterialIcon name="folder" :size="32" />
							</div>
							<div class="stat-card__content">
								<div class="stat-card__value">{{ reportData.activeProjects }}</div>
								<div class="stat-card__label">{{ translate('domaincontrol', 'Active Projects') }}</div>
							</div>
						</div>
						<div class="stat-card stat-card--success">
							<div class="stat-card__icon">
								<MaterialIcon name="checkmark" :size="32" />
							</div>
							<div class="stat-card__content">
								<div class="stat-card__value">{{ reportData.completedProjects }}</div>
								<div class="stat-card__label">{{ translate('domaincontrol', 'Completed') }}</div>
							</div>
						</div>
						<div class="stat-card stat-card--warning">
							<div class="stat-card__icon">
								<MaterialIcon name="pause" :size="32" />
							</div>
							<div class="stat-card__content">
								<div class="stat-card__value">{{ reportData.onHoldProjects }}</div>
								<div class="stat-card__label">{{ translate('domaincontrol', 'On Hold') }}</div>
							</div>
						</div>
					</div>
					<div class="report-chart-container">
						<canvas ref="projectStatusChart"></canvas>
					</div>
				</div>

				<!-- Service Analysis -->
				<div class="report-section">
					<div class="section-header">
						<h3 class="section-title">
							<MaterialIcon name="settings" :size="24" />
							{{ translate('domaincontrol', 'Service Analysis') }}
						</h3>
						<p class="section-description">{{ translate('domaincontrol', 'Service type based income and renewal tracking') }}</p>
					</div>
					<div class="report-cards">
						<div class="stat-card stat-card--info">
							<div class="stat-card__icon">
								<MaterialIcon name="calendar" :size="32" />
							</div>
							<div class="stat-card__content">
								<div class="stat-card__value">{{ reportData.expiringSoon }}</div>
								<div class="stat-card__label">{{ translate('domaincontrol', 'Expiring Soon') }}</div>
								<div class="stat-card__subtitle">{{ translate('domaincontrol', 'Within 30 days') }}</div>
							</div>
						</div>
						<div class="stat-card stat-card--danger">
							<div class="stat-card__icon">
								<MaterialIcon name="warning" :size="32" />
							</div>
							<div class="stat-card__content">
								<div class="stat-card__value">{{ reportData.expiredServices }}</div>
								<div class="stat-card__label">{{ translate('domaincontrol', 'Expired') }}</div>
								<div class="stat-card__subtitle">{{ translate('domaincontrol', 'Immediate action required') }}</div>
							</div>
						</div>
						<div class="stat-card stat-card--success">
							<div class="stat-card__icon">
								<MaterialIcon name="checkmark" :size="32" />
							</div>
							<div class="stat-card__content">
								<div class="stat-card__value">{{ reportData.activeServices }}</div>
								<div class="stat-card__label">{{ translate('domaincontrol', 'Active Services') }}</div>
							</div>
						</div>
					</div>
					<div class="report-table-container">
						<div class="table-header">
							<h4>{{ translate('domaincontrol', 'Services Expiring Soon') }}</h4>
						</div>
						<div class="report-list">
							<div
								v-for="service in expiringServices"
								:key="service.id"
								class="report-list-item"
							>
								<div class="report-list-item__content">
									<div class="report-list-item__title">{{ service.name }}</div>
									<div class="report-list-item__subtitle">
										{{ getClientName(service.clientId) }} • {{ formatDate(service.expirationDate) }}
									</div>
								</div>
							</div>
							<div v-if="expiringServices.length === 0" class="empty-message">
								{{ translate('domaincontrol', 'No expiring services') }}
							</div>
						</div>
					</div>
					<div class="report-chart-container">
						<canvas ref="serviceTypeIncomeChart"></canvas>
					</div>
				</div>

				<!-- Income/Expense Analysis -->
				<div class="report-section">
					<div class="section-header">
						<h3 class="section-title">
							<MaterialIcon name="accounting" :size="24" />
							{{ translate('domaincontrol', 'Income/Expense Analysis') }}
						</h3>
						<p class="section-description">{{ translate('domaincontrol', 'Income and expense comparison, category-based analysis') }}</p>
					</div>
					<div class="report-cards">
						<div class="stat-card stat-card--success">
							<div class="stat-card__icon">
								<MaterialIcon name="add" :size="32" />
							</div>
							<div class="stat-card__content">
								<div class="stat-card__value">{{ formatCurrency(reportData.totalTransactionIncome) }}</div>
								<div class="stat-card__label">{{ translate('domaincontrol', 'Total Income') }}</div>
								<div class="stat-card__subtitle">{{ translate('domaincontrol', 'All transactions') }}</div>
							</div>
						</div>
						<div class="stat-card stat-card--danger">
							<div class="stat-card__icon">
								<MaterialIcon name="delete" :size="32" />
							</div>
							<div class="stat-card__content">
								<div class="stat-card__value">{{ formatCurrency(reportData.totalTransactionExpense) }}</div>
								<div class="stat-card__label">{{ translate('domaincontrol', 'Total Expense') }}</div>
								<div class="stat-card__subtitle">{{ translate('domaincontrol', 'All transactions') }}</div>
							</div>
						</div>
						<div class="stat-card stat-card--primary">
							<div class="stat-card__icon">
								<MaterialIcon name="accounting" :size="32" />
							</div>
							<div class="stat-card__content">
								<div class="stat-card__value" :class="getNetClass(reportData.netTransaction)">
									{{ formatCurrency(reportData.netTransaction) }}
								</div>
								<div class="stat-card__label">{{ translate('domaincontrol', 'Net Profit/Loss') }}</div>
								<div class="stat-card__subtitle">{{ translate('domaincontrol', 'Income - Expense') }}</div>
							</div>
						</div>
					</div>
					<div class="report-chart-container">
						<canvas ref="incomeExpenseChart"></canvas>
					</div>
					<div class="report-chart-container">
						<canvas ref="expenseCategoryChart"></canvas>
					</div>
				</div>

				<!-- Debt/Credit Status -->
				<div class="report-section">
					<div class="section-header">
						<h3 class="section-title">
							<MaterialIcon name="accounting" :size="24" />
							{{ translate('domaincontrol', 'Debt/Credit Status') }}
						</h3>
						<p class="section-description">{{ translate('domaincontrol', 'Total debts, credits and payment status') }}</p>
					</div>
					<div class="report-cards">
						<div class="stat-card stat-card--danger">
							<div class="stat-card__icon">
								<MaterialIcon name="delete" :size="32" />
							</div>
							<div class="stat-card__content">
								<div class="stat-card__value">{{ formatCurrency(reportData.totalDebts) }}</div>
								<div class="stat-card__label">{{ translate('domaincontrol', 'Total Debts') }}</div>
								<div class="stat-card__subtitle">{{ translate('domaincontrol', 'Unpaid debts') }}</div>
							</div>
						</div>
						<div class="stat-card stat-card--success">
							<div class="stat-card__icon">
								<MaterialIcon name="add" :size="32" />
							</div>
							<div class="stat-card__content">
								<div class="stat-card__value">{{ formatCurrency(reportData.totalCredits) }}</div>
								<div class="stat-card__label">{{ translate('domaincontrol', 'Total Credits') }}</div>
								<div class="stat-card__subtitle">{{ translate('domaincontrol', 'Uncollected') }}</div>
							</div>
						</div>
						<div class="stat-card stat-card--warning">
							<div class="stat-card__icon">
								<MaterialIcon name="calendar" :size="32" />
							</div>
							<div class="stat-card__content">
								<div class="stat-card__value">{{ reportData.upcomingDebtPayments }}</div>
								<div class="stat-card__label">{{ translate('domaincontrol', 'Upcoming Payments') }}</div>
								<div class="stat-card__subtitle">{{ translate('domaincontrol', 'Within 30 days') }}</div>
							</div>
						</div>
						<div class="stat-card stat-card--danger">
							<div class="stat-card__icon">
								<MaterialIcon name="warning" :size="32" />
							</div>
							<div class="stat-card__content">
								<div class="stat-card__value">{{ reportData.overdueDebts }}</div>
								<div class="stat-card__label">{{ translate('domaincontrol', 'Overdue Debts') }}</div>
								<div class="stat-card__subtitle">{{ translate('domaincontrol', 'Immediate payment required') }}</div>
							</div>
						</div>
					</div>
					<div class="report-chart-container">
						<canvas ref="debtStatusChart"></canvas>
					</div>
				</div>
			</div>
		</div>
	</div>
</template>

<script>
import api from '../services/api'
import MaterialIcon from './MaterialIcon.vue'

export default {
	name: 'Reports',
	components: {
		MaterialIcon,
	},
	data() {
		return {
			loading: false,
			currentPeriod: 'month',
			startDate: '',
			endDate: '',
			reportData: {
				totalIncome: 0,
				monthlyIncome: 0,
				pendingIncome: 0,
				totalClients: 0,
				activeClients: 0,
				totalInvoices: 0,
				paidInvoices: 0,
				overdueInvoices: 0,
				pendingInvoices: 0,
				avgClientIncome: 0,
				activeProjects: 0,
				completedProjects: 0,
				onHoldProjects: 0,
				activeServices: 0,
				expiringSoon: 0,
				expiredServices: 0,
				totalTransactionIncome: 0,
				totalTransactionExpense: 0,
				netTransaction: 0,
				totalDebts: 0,
				totalCredits: 0,
				upcomingDebtPayments: 0,
				overdueDebts: 0,
			},
			clients: [],
			invoices: [],
			payments: [],
			projects: [],
			services: [],
			serviceTypes: [],
			transactions: [],
			debts: [],
			topClients: [],
			expiringServices: [],
			charts: {},
		}
	},
	mounted() {
		this.loadReports()
	},
	beforeUnmount() {
		// Destroy all charts
		Object.values(this.charts).forEach(chart => {
			if (chart && typeof chart.destroy === 'function') {
				chart.destroy()
			}
		})
	},
	methods: {
		onPeriodChange() {
			if (this.currentPeriod !== 'custom') {
				this.startDate = ''
				this.endDate = ''
			}
		},
		async loadReports() {
			this.loading = true
			try {
				await Promise.all([
					this.loadClients(),
					this.loadInvoices(),
					this.loadPayments(),
					this.loadProjects(),
					this.loadServices(),
					this.loadServiceTypes(),
					this.loadTransactions(),
					this.loadDebts(),
				])
				this.calculateReports()
				this.$nextTick(() => {
					this.renderCharts()
				})
			} catch (error) {
				console.error('Error loading reports:', error)
			} finally {
				this.loading = false
			}
		},
		async loadClients() {
			try {
				const response = await api.clients.getAll()
				this.clients = response.data || []
			} catch (error) {
				console.error('Error loading clients:', error)
				this.clients = []
			}
		},
		async loadInvoices() {
			try {
				const response = await api.invoices.getAll()
				this.invoices = response.data || []
			} catch (error) {
				console.error('Error loading invoices:', error)
				this.invoices = []
			}
		},
		async loadPayments() {
			try {
				// Payments are loaded with invoices
				this.payments = []
				this.invoices.forEach(invoice => {
					if (invoice.payments && Array.isArray(invoice.payments)) {
						this.payments.push(...invoice.payments)
					}
				})
			} catch (error) {
				console.error('Error loading payments:', error)
				this.payments = []
			}
		},
		async loadProjects() {
			try {
				const response = await api.projects.getAll()
				this.projects = response.data || []
			} catch (error) {
				console.error('Error loading projects:', error)
				this.projects = []
			}
		},
		async loadServices() {
			try {
				const response = await api.services.getAll()
				this.services = response.data || []
			} catch (error) {
				console.error('Error loading services:', error)
				this.services = []
			}
		},
		async loadServiceTypes() {
			try {
				const response = await api.serviceTypes.getAll()
				this.serviceTypes = response.data || []
			} catch (error) {
				console.error('Error loading service types:', error)
				this.serviceTypes = []
			}
		},
		async loadTransactions() {
			try {
				const response = await api.transactions.getAll()
				this.transactions = response.data || []
			} catch (error) {
				console.error('Error loading transactions:', error)
				this.transactions = []
			}
		},
		async loadDebts() {
			try {
				const response = await api.debts.getAll()
				this.debts = response.data || []
			} catch (error) {
				console.error('Error loading debts:', error)
				this.debts = []
			}
		},
		calculateReports() {
			// Filter data by period
			const filteredPayments = this.filterByPeriod(this.payments, 'paymentDate')
			const filteredInvoices = this.filterByPeriod(this.invoices, 'issueDate')
			const filteredTransactions = this.filterByPeriod(this.transactions, 'transactionDate')

			// Calculate total income
			const totalIncome = this.payments.reduce((sum, p) => sum + (parseFloat(p.amount) || 0), 0)

			// Calculate monthly income
			const now = new Date()
			const currentMonth = now.getMonth()
			const currentYear = now.getFullYear()
			const monthlyIncome = this.payments.reduce((sum, p) => {
				if (!p.paymentDate) return sum
				const paymentDate = new Date(p.paymentDate)
				if (paymentDate.getMonth() === currentMonth && paymentDate.getFullYear() === currentYear) {
					return sum + (parseFloat(p.amount) || 0)
				}
				return sum
			}, 0)

			// Calculate pending income
			const pendingIncome = this.invoices.reduce((sum, i) => {
				if (['draft', 'sent', 'overdue'].includes(i.status)) {
					const total = parseFloat(i.totalAmount) || 0
					const paid = parseFloat(i.paidAmount) || 0
					return sum + (total - paid)
				}
				return sum
			}, 0)

			// Invoice stats
			this.reportData.totalInvoices = this.invoices.length
			this.reportData.paidInvoices = this.invoices.filter(i => i.status === 'paid').length
			this.reportData.overdueInvoices = this.invoices.filter(i => i.status === 'overdue').length
			this.reportData.pendingInvoices = this.invoices.filter(i => ['draft', 'sent'].includes(i.status)).length

			// Client stats
			this.reportData.totalClients = this.clients.length
			this.reportData.activeClients = this.clients.filter(c => {
				const hasActiveItems = this.domains?.some(d => d.clientId === c.id) ||
									   this.hostings?.some(h => h.clientId === c.id) ||
									   this.websites?.some(w => w.clientId === c.id) ||
									   this.services.some(s => s.clientId === c.id && s.status === 'active')
				return hasActiveItems
			}).length

			// Average client income
			this.reportData.avgClientIncome = this.reportData.totalClients > 0 ? totalIncome / this.reportData.totalClients : 0

			// Top clients by income
			this.calculateTopClients()

			// Project stats
			this.reportData.activeProjects = this.projects.filter(p => p.status === 'active').length
			this.reportData.completedProjects = this.projects.filter(p => p.status === 'completed').length
			this.reportData.onHoldProjects = this.projects.filter(p => p.status === 'on_hold').length

			// Service stats
			this.reportData.activeServices = this.services.filter(s => s.status === 'active').length

			// Service expiration stats
			const nowDate = new Date()
			const thirtyDaysLater = new Date(nowDate.getTime() + 30 * 24 * 60 * 60 * 1000)
			this.reportData.expiringSoon = this.services.filter(s => {
				if (!s.expirationDate || s.renewalInterval === 'one-time') return false
				const expDate = new Date(s.expirationDate)
				return expDate >= nowDate && expDate <= thirtyDaysLater
			}).length

			this.reportData.expiredServices = this.services.filter(s => {
				if (!s.expirationDate || s.renewalInterval === 'one-time') return false
				const expDate = new Date(s.expirationDate)
				return expDate < nowDate
			}).length

			// Expiring services list
			this.expiringServices = this.services.filter(s => {
				if (!s.expirationDate || s.renewalInterval === 'one-time') return false
				const expDate = new Date(s.expirationDate)
				return expDate >= nowDate && expDate <= thirtyDaysLater
			}).slice(0, 10)

			// Transaction stats
			const transactionIncome = this.transactions.filter(t => t.type === 'income').reduce((sum, t) => sum + (parseFloat(t.amount) || 0), 0)
			const transactionExpense = this.transactions.filter(t => t.type === 'expense').reduce((sum, t) => sum + (parseFloat(t.amount) || 0), 0)
			this.reportData.netTransaction = transactionIncome - transactionExpense

			// Debt stats
			const totalDebts = this.debts.filter(d => d.type === 'debt' && d.status === 'active').reduce((sum, d) => {
				const remaining = parseFloat(d.totalAmount || 0) - parseFloat(d.paidAmount || 0)
				return sum + remaining
			}, 0)
			const totalCredits = this.debts.filter(d => d.type === 'credit' && d.status === 'active').reduce((sum, d) => {
				const remaining = parseFloat(d.totalAmount || 0) - parseFloat(d.paidAmount || 0)
				return sum + remaining
			}, 0)

			const today = new Date()
			today.setHours(0, 0, 0, 0)
			const thirtyDaysFromToday = new Date(today)
			thirtyDaysFromToday.setDate(thirtyDaysFromToday.getDate() + 30)

			this.reportData.upcomingDebtPayments = this.debts.filter(d => {
				if (d.status !== 'active' || !d.nextPaymentDate) return false
				const nextDate = new Date(d.nextPaymentDate)
				return nextDate >= today && nextDate <= thirtyDaysFromToday
			}).length

			this.reportData.overdueDebts = this.debts.filter(d => {
				if (d.status !== 'active') return false
				if (d.nextPaymentDate) {
					const nextDate = new Date(d.nextPaymentDate)
					return nextDate < today
				}
				if (d.dueDate) {
					const dueDate = new Date(d.dueDate)
					return dueDate < today
				}
				return false
			}).length

			// Update report data
			this.reportData.totalIncome = totalIncome
			this.reportData.monthlyIncome = monthlyIncome
			this.reportData.pendingIncome = pendingIncome
			this.reportData.totalTransactionIncome = transactionIncome
			this.reportData.totalTransactionExpense = transactionExpense
			this.reportData.totalDebts = totalDebts
			this.reportData.totalCredits = totalCredits
		},
		calculateTopClients() {
			const clientIncome = {}
			this.payments.forEach(payment => {
				if (!payment.invoiceId) return
				const invoice = this.invoices.find(i => i.id === payment.invoiceId)
				if (!invoice || !invoice.clientId) return
				if (!clientIncome[invoice.clientId]) {
					clientIncome[invoice.clientId] = 0
				}
				clientIncome[invoice.clientId] += parseFloat(payment.amount) || 0
			})

			this.topClients = Object.entries(clientIncome)
				.map(([clientId, totalIncome]) => {
					const client = this.clients.find(c => c.id === parseInt(clientId))
					return {
						id: parseInt(clientId),
						name: client ? client.name : `Client ${clientId}`,
						totalIncome,
					}
				})
				.sort((a, b) => b.totalIncome - a.totalIncome)
				.slice(0, 10)
		},
		filterByPeriod(data, dateField) {
			if (this.currentPeriod === 'custom' && this.startDate && this.endDate) {
				const start = new Date(this.startDate)
				const end = new Date(this.endDate)
				return data.filter(item => {
					if (!item[dateField]) return false
					const itemDate = new Date(item[dateField])
					return itemDate >= start && itemDate <= end
				})
			} else if (this.currentPeriod === 'month') {
				const now = new Date()
				const start = new Date(now.getFullYear(), now.getMonth(), 1)
				const end = new Date(now.getFullYear(), now.getMonth() + 1, 0)
				return data.filter(item => {
					if (!item[dateField]) return false
					const itemDate = new Date(item[dateField])
					return itemDate >= start && itemDate <= end
				})
			} else if (this.currentPeriod === 'quarter') {
				const now = new Date()
				const quarter = Math.floor(now.getMonth() / 3)
				const start = new Date(now.getFullYear(), quarter * 3, 1)
				const end = new Date(now.getFullYear(), (quarter + 1) * 3, 0)
				return data.filter(item => {
					if (!item[dateField]) return false
					const itemDate = new Date(item[dateField])
					return itemDate >= start && itemDate <= end
				})
			} else if (this.currentPeriod === 'year') {
				const now = new Date()
				const start = new Date(now.getFullYear(), 0, 1)
				const end = new Date(now.getFullYear(), 11, 31)
				return data.filter(item => {
					if (!item[dateField]) return false
					const itemDate = new Date(item[dateField])
					return itemDate >= start && itemDate <= end
				})
			}
			return data
		},
		renderCharts() {
			if (typeof Chart === 'undefined') {
				console.warn('Chart.js is not loaded')
				return
			}

			this.renderIncomeTrendChart()
			this.renderInvoiceStatusChart()
			this.renderTopClientsChart()
			this.renderProjectStatusChart()
			this.renderServiceTypeIncomeChart()
			this.renderIncomeExpenseChart()
			this.renderExpenseCategoryChart()
			this.renderDebtStatusChart()
		},
		renderIncomeTrendChart() {
			const ctx = this.$refs.incomeTrendChart
			if (!ctx) return

			if (this.charts.incomeTrend) {
				this.charts.incomeTrend.destroy()
			}

			const monthlyData = {}
			this.payments.forEach(p => {
				if (!p.paymentDate) return
				const date = new Date(p.paymentDate)
				const monthKey = `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}`
				if (!monthlyData[monthKey]) {
					monthlyData[monthKey] = 0
				}
				monthlyData[monthKey] += parseFloat(p.amount) || 0
			})

			const sortedMonths = Object.keys(monthlyData).sort().slice(-12)
			const labels = sortedMonths.map(m => {
				const [year, month] = m.split('-')
				return `${month}/${year}`
			})
			const data = sortedMonths.map(m => monthlyData[m])

			this.charts.incomeTrend = new Chart(ctx, {
				type: 'line',
				data: {
					labels: labels,
					datasets: [{
						label: this.translate('domaincontrol', 'Monthly Income'),
						data: data,
						borderColor: 'rgb(75, 192, 192)',
						backgroundColor: 'rgba(75, 192, 192, 0.1)',
						tension: 0.4,
						fill: true,
					}],
				},
				options: {
					responsive: true,
					maintainAspectRatio: false,
					plugins: {
						legend: {
							display: true,
							position: 'top',
						},
					},
					scales: {
						y: {
							beginAtZero: true,
							ticks: {
								callback: (value) => this.formatCurrency(value),
							},
						},
					},
				},
			})
		},
		renderInvoiceStatusChart() {
			const ctx = this.$refs.invoiceStatusChart
			if (!ctx) return

			if (this.charts.invoiceStatus) {
				this.charts.invoiceStatus.destroy()
			}

			const paid = this.reportData.paidInvoices
			const overdue = this.reportData.overdueInvoices
			const pending = this.reportData.pendingInvoices

			this.charts.invoiceStatus = new Chart(ctx, {
				type: 'doughnut',
				data: {
					labels: [
						this.translate('domaincontrol', 'Paid'),
						this.translate('domaincontrol', 'Overdue'),
						this.translate('domaincontrol', 'Pending'),
					],
					datasets: [{
						data: [paid, overdue, pending],
						backgroundColor: [
							'rgba(75, 192, 192, 0.8)',
							'rgba(255, 99, 132, 0.8)',
							'rgba(255, 206, 86, 0.8)',
						],
					}],
				},
				options: {
					responsive: true,
					maintainAspectRatio: false,
					plugins: {
						legend: {
							position: 'bottom',
						},
					},
				},
			})
		},
		renderTopClientsChart() {
			const ctx = this.$refs.topClientsChart
			if (!ctx || this.topClients.length === 0) return

			if (this.charts.topClients) {
				this.charts.topClients.destroy()
			}

			const labels = this.topClients.map(c => c.name)
			const data = this.topClients.map(c => c.totalIncome)

			this.charts.topClients = new Chart(ctx, {
				type: 'bar',
				data: {
					labels: labels,
					datasets: [{
						label: this.translate('domaincontrol', 'Income'),
						data: data,
						backgroundColor: 'rgba(54, 162, 235, 0.8)',
					}],
				},
				options: {
					responsive: true,
					maintainAspectRatio: false,
					plugins: {
						legend: {
							display: false,
						},
					},
					scales: {
						y: {
							beginAtZero: true,
							ticks: {
								callback: (value) => this.formatCurrency(value),
							},
						},
					},
				},
			})
		},
		renderProjectStatusChart() {
			const ctx = this.$refs.projectStatusChart
			if (!ctx) return

			if (this.charts.projectStatus) {
				this.charts.projectStatus.destroy()
			}

			const active = this.reportData.activeProjects
			const completed = this.reportData.completedProjects
			const onHold = this.reportData.onHoldProjects

			this.charts.projectStatus = new Chart(ctx, {
				type: 'pie',
				data: {
					labels: [
						this.translate('domaincontrol', 'Active'),
						this.translate('domaincontrol', 'Completed'),
						this.translate('domaincontrol', 'On Hold'),
					],
					datasets: [{
						data: [active, completed, onHold],
						backgroundColor: [
							'rgba(153, 102, 255, 0.8)',
							'rgba(75, 192, 192, 0.8)',
							'rgba(255, 206, 86, 0.8)',
						],
					}],
				},
				options: {
					responsive: true,
					maintainAspectRatio: false,
					plugins: {
						legend: {
							position: 'bottom',
						},
					},
				},
			})
		},
		renderServiceTypeIncomeChart() {
			const ctx = this.$refs.serviceTypeIncomeChart
			if (!ctx) return

			if (this.charts.serviceTypeIncome) {
				this.charts.serviceTypeIncome.destroy()
			}

			const typeCount = {}
			this.services.forEach(service => {
				if (!service.serviceTypeId) return
				const type = this.serviceTypes.find(t => t.id === service.serviceTypeId)
				if (!type) return

				if (!typeCount[type.name]) {
					typeCount[type.name] = 0
				}
				typeCount[type.name]++
			})

			const labels = Object.keys(typeCount)
			const data = Object.values(typeCount)

			if (labels.length === 0) return

			this.charts.serviceTypeIncome = new Chart(ctx, {
				type: 'bar',
				data: {
					labels: labels,
					datasets: [{
						label: this.translate('domaincontrol', 'Service Count'),
						data: data,
						backgroundColor: 'rgba(255, 159, 64, 0.8)',
					}],
				},
				options: {
					responsive: true,
					maintainAspectRatio: false,
					plugins: {
						legend: {
							display: false,
						},
					},
					scales: {
						y: {
							beginAtZero: true,
							ticks: {
								stepSize: 1,
							},
						},
					},
				},
			})
		},
		renderIncomeExpenseChart() {
			const ctx = this.$refs.incomeExpenseChart
			if (!ctx) return

			if (this.charts.incomeExpense) {
				this.charts.incomeExpense.destroy()
			}

			const monthlyData = {}
			this.transactions.forEach(t => {
				if (!t.transactionDate) return
				const date = new Date(t.transactionDate)
				const monthKey = `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}`
				if (!monthlyData[monthKey]) {
					monthlyData[monthKey] = { income: 0, expense: 0 }
				}
				if (t.type === 'income') {
					monthlyData[monthKey].income += parseFloat(t.amount) || 0
				} else {
					monthlyData[monthKey].expense += parseFloat(t.amount) || 0
				}
			})

			const sortedMonths = Object.keys(monthlyData).sort().slice(-12)
			const labels = sortedMonths.map(m => {
				const [year, month] = m.split('-')
				return `${month}/${year}`
			})
			const incomeData = sortedMonths.map(m => monthlyData[m].income)
			const expenseData = sortedMonths.map(m => monthlyData[m].expense)

			this.charts.incomeExpense = new Chart(ctx, {
				type: 'bar',
				data: {
					labels: labels,
					datasets: [
						{
							label: this.translate('domaincontrol', 'Income'),
							data: incomeData,
							backgroundColor: 'rgba(75, 192, 192, 0.8)',
						},
						{
							label: this.translate('domaincontrol', 'Expense'),
							data: expenseData,
							backgroundColor: 'rgba(255, 99, 132, 0.8)',
						},
					],
				},
				options: {
					responsive: true,
					maintainAspectRatio: false,
					plugins: {
						legend: {
							position: 'top',
						},
					},
					scales: {
						y: {
							beginAtZero: true,
							ticks: {
								callback: (value) => this.formatCurrency(value),
							},
						},
					},
				},
			})
		},
		renderExpenseCategoryChart() {
			const ctx = this.$refs.expenseCategoryChart
			if (!ctx) return

			if (this.charts.expenseCategory) {
				this.charts.expenseCategory.destroy()
			}

			const categoryData = {}
			this.transactions.filter(t => t.type === 'expense').forEach(t => {
				const categoryName = t.categoryId ? `Category ${t.categoryId}` : this.translate('domaincontrol', 'Uncategorized')
				if (!categoryData[categoryName]) {
					categoryData[categoryName] = 0
				}
				categoryData[categoryName] += parseFloat(t.amount) || 0
			})

			const labels = Object.keys(categoryData)
			const data = Object.values(categoryData)

			if (labels.length === 0) return

			this.charts.expenseCategory = new Chart(ctx, {
				type: 'pie',
				data: {
					labels: labels,
					datasets: [{
						data: data,
						backgroundColor: [
							'rgba(255, 99, 132, 0.8)',
							'rgba(54, 162, 235, 0.8)',
							'rgba(255, 206, 86, 0.8)',
							'rgba(75, 192, 192, 0.8)',
							'rgba(153, 102, 255, 0.8)',
							'rgba(255, 159, 64, 0.8)',
						],
					}],
				},
				options: {
					responsive: true,
					maintainAspectRatio: false,
					plugins: {
						legend: {
							position: 'bottom',
						},
					},
				},
			})
		},
		renderDebtStatusChart() {
			const ctx = this.$refs.debtStatusChart
			if (!ctx) return

			if (this.charts.debtStatus) {
				this.charts.debtStatus.destroy()
			}

			const debts = this.reportData.totalDebts
			const credits = this.reportData.totalCredits

			this.charts.debtStatus = new Chart(ctx, {
				type: 'doughnut',
				data: {
					labels: [
						this.translate('domaincontrol', 'Debts'),
						this.translate('domaincontrol', 'Credits'),
					],
					datasets: [{
						data: [debts, credits],
						backgroundColor: [
							'rgba(255, 99, 132, 0.8)',
							'rgba(75, 192, 192, 0.8)',
						],
					}],
				},
				options: {
					responsive: true,
					maintainAspectRatio: false,
					plugins: {
						legend: {
							position: 'bottom',
						},
					},
				},
			})
		},
		getClientName(clientId) {
			if (!clientId) return ''
			const client = this.clients.find(c => c.id === clientId)
			return client ? client.name : ''
		},
		getNetClass(net) {
			if (net < 0) return 'text-error'
			if (net > 0) return 'text-success'
			return ''
		},
		formatDate(date) {
			if (!date) return ''
			const d = new Date(date)
			return d.toLocaleDateString('tr-TR')
		},
		formatCurrency(amount, currency = 'USD') {
			if (amount === null || amount === undefined || amount === 0) return '0.00'
			const formatter = new Intl.NumberFormat('tr-TR', {
				style: 'currency',
				currency: currency || 'USD',
			})
			return formatter.format(amount)
		},
		translate(appId, text, vars) {
			try {
				if (typeof window !== 'undefined') {
					if (typeof OC !== 'undefined' && OC.L10n && typeof OC.L10n.translate === 'function') {
						const translated = OC.L10n.translate(appId, text, vars || {})
						if (translated && translated !== text) {
							return translated
						}
					}
					if (typeof window.t === 'function') {
						const translated = window.t(appId, text, vars || {})
						if (translated && translated !== text) {
							return translated
						}
					}
				}
			} catch (e) {
				console.warn('Translation error:', e)
			}

			const translations = {
				'Reports and Analytics': 'Raporlar ve Analitik',
				'Analyze your business performance and make data-driven decisions': 'İş performansınızı analiz edin ve kararlarınızı veriye dayalı alın',
				'Period': 'Dönem',
				'This Month': 'Bu Ay',
				'This Quarter': 'Bu Çeyrek',
				'This Year': 'Bu Yıl',
				'Custom Date': 'Özel Tarih',
				'Start': 'Başlangıç',
				'End': 'Bitiş',
				'Filter': 'Filtrele',
				'Loading reports...': 'Raporlar yükleniyor...',
				'Summary Statistics': 'Özet İstatistikler',
				'General business performance indicators': 'Genel iş performansı göstergeleri',
				'Total Income': 'Toplam Gelir',
				'All time': 'Tüm zamanlar',
				'Monthly Income': 'Aylık Gelir',
				'This month': 'Bu ay',
				'Pending Payments': 'Bekleyen Ödemeler',
				'Unpaid invoices': 'Ödenmemiş faturalar',
				'Total Clients': 'Toplam Müşteri',
				'active': 'aktif',
				'Income Trend': 'Gelir Trendi',
				'Monthly income trend and comparisons': 'Aylık gelir trendi ve karşılaştırmalar',
				'Invoice Status': 'Fatura Durumu',
				'Invoice statuses and payment tracking': 'Fatura durumları ve ödeme takibi',
				'Total Invoices': 'Toplam Fatura',
				'Paid': 'Ödenen',
				'Overdue': 'Gecikmiş',
				'Pending': 'Bekleyen',
				'Client Analysis': 'Müşteri Analizi',
				'Client statistics and top revenue generators': 'Müşteri istatistikleri ve en çok gelir getirenler',
				'Active Clients': 'Aktif Müşteriler',
				'Average Client Income': 'Ortalama Müşteri Geliri',
				'Top Revenue Generating Clients': 'En Çok Gelir Getiren Müşteriler',
				'No client data available': 'Müşteri verisi yok',
				'Project Status': 'Proje Durumu',
				'Project statuses and progress tracking': 'Proje durumları ve ilerleme takibi',
				'Active Projects': 'Aktif Projeler',
				'Completed': 'Tamamlanan',
				'On Hold': 'Beklemede',
				'Service Analysis': 'Hizmet Analizi',
				'Service type based income and renewal tracking': 'Hizmet türü bazlı gelir ve yenileme takibi',
				'Expiring Soon': 'Yakında Bitecek',
				'Within 30 days': '30 gün içinde',
				'Expired': 'Süresi Dolmuş',
				'Immediate action required': 'Acil müdahale gerekli',
				'Active Services': 'Aktif Hizmetler',
				'Services Expiring Soon': 'Yakında Bitecek Hizmetler',
				'No expiring services': 'Yakında bitecek hizmet yok',
				'Income/Expense Analysis': 'Gelir/Gider Analizi',
				'Income and expense comparison, category-based analysis': 'Gelir ve gider karşılaştırması, kategori bazlı analiz',
				'Total Expense': 'Toplam Gider',
				'All transactions': 'Tüm işlemler',
				'Net Profit/Loss': 'Net Kar/Zarar',
				'Income - Expense': 'Gelir - Gider',
				'Debt/Credit Status': 'Borç/Alacak Durumu',
				'Total debts, credits and payment status': 'Toplam borçlar, alacaklar ve ödeme durumu',
				'Total Debts': 'Toplam Borçlar',
				'Unpaid debts': 'Ödenmemiş borçlar',
				'Total Credits': 'Toplam Alacaklar',
				'Uncollected': 'Tahsil edilmemiş',
				'Upcoming Payments': 'Yaklaşan Ödemeler',
				'Overdue Debts': 'Gecikmiş Borçlar',
				'Immediate payment required': 'Acil ödeme gerekli',
				'Monthly Income': 'Aylık Gelir',
				'Income': 'Gelir',
				'Expense': 'Gider',
				'Service Count': 'Hizmet Sayısı',
				'Debts': 'Borçlar',
				'Credits': 'Alacaklar',
				'Uncategorized': 'Kategorisiz',
			}

			return translations[text] || text
		},
	},
}
</script>

<style scoped>
.reports-view {
	width: 100%;
	height: 100%;
	padding: 20px;
	padding-bottom: 40px;
}

.reports-container {
	max-width: 1400px;
	margin: 0 auto;
}

.reports-header {
	margin-bottom: 32px;
}

.reports-title {
	display: flex;
	align-items: center;
	gap: 12px;
	margin: 0 0 8px 0;
	font-size: 28px;
	font-weight: 600;
	color: var(--color-main-text);
}

.reports-subtitle {
	margin: 0;
	font-size: 16px;
	color: var(--color-text-maxcontrast);
}

.reports-filters {
	margin-bottom: 32px;
}

.filter-card {
	background-color: var(--color-background-dark);
	border-radius: var(--border-radius-element);
	padding: 20px;
	border: 1px solid var(--color-border);
}

.form-row {
	display: grid;
	grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
	gap: 16px;
	align-items: end;
}

.form-group {
	display: flex;
	flex-direction: column;
	gap: 8px;
}

.form-label {
	font-size: 14px;
	font-weight: 500;
	color: var(--color-main-text);
}

.form-control {
	padding: 8px 12px;
	border: 1px solid var(--color-border);
	border-radius: var(--border-radius-element);
	background-color: var(--color-main-background);
	color: var(--color-main-text);
	font-size: 14px;
	font-family: inherit;
}

.form-control:focus {
	outline: none;
	border-color: var(--color-primary-element-element-element);
}

.report-section {
	margin-bottom: 48px;
}

.section-header {
	margin-bottom: 24px;
}

.section-title {
	display: flex;
	align-items: center;
	gap: 8px;
	margin: 0 0 8px 0;
	font-size: 22px;
	font-weight: 600;
	color: var(--color-main-text);
}

.section-description {
	margin: 0;
	font-size: 14px;
	color: var(--color-text-maxcontrast);
}

.report-cards {
	display: grid;
	grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
	gap: 16px;
	margin-bottom: 24px;
}

.stat-card {
	display: flex;
	align-items: center;
	gap: 16px;
	padding: 20px;
	background-color: var(--color-background-dark);
	border-radius: var(--border-radius-element);
	border: 1px solid var(--color-border);
}

.stat-card__icon {
	width: 56px;
	height: 56px;
	border-radius: 50%;
	display: flex;
	align-items: center;
	justify-content: center;
	flex-shrink: 0;
}

.stat-card--success .stat-card__icon {
	background-color: var(--color-element-success);
	color: var(--color-element-success-text);
}

.stat-card--info .stat-card__icon {
	background-color: var(--color-primary-element-element-element);
	color: var(--color-primary-element-element-element-text);
}

.stat-card--warning .stat-card__icon {
	background-color: var(--color-element-warning);
	color: var(--color-element-warning-text);
}

.stat-card--primary .stat-card__icon {
	background-color: var(--color-primary-element-element-element);
	color: var(--color-primary-element-element-element-text);
}

.stat-card--danger .stat-card__icon {
	background-color: var(--color-element-error);
	color: var(--color-element-error-text);
}

.stat-card--purple .stat-card__icon {
	background-color: #9b59b6;
	color: white;
}

.stat-card__content {
	flex: 1;
	min-width: 0;
}

.stat-card__value {
	font-size: 24px;
	font-weight: 600;
	color: var(--color-main-text);
	margin-bottom: 4px;
}

.stat-card__label {
	font-size: 14px;
	font-weight: 500;
	color: var(--color-main-text);
	margin-bottom: 2px;
}

.stat-card__subtitle {
	font-size: 12px;
	color: var(--color-text-maxcontrast);
}

.report-chart-container {
	position: relative;
	height: 300px;
	margin-top: 24px;
	background-color: var(--color-background-dark);
	border-radius: var(--border-radius-element);
	padding: 20px;
	border: 1px solid var(--color-border);
}

.report-table-container {
	background-color: var(--color-background-dark);
	border-radius: var(--border-radius-element);
	padding: 20px;
	border: 1px solid var(--color-border);
	margin-bottom: 24px;
}

.table-header {
	margin-bottom: 16px;
}

.table-header h4 {
	margin: 0;
	font-size: 18px;
	font-weight: 600;
	color: var(--color-main-text);
}

.report-list {
	display: flex;
	flex-direction: column;
	gap: 12px;
}

.report-list-item {
	display: flex;
	align-items: center;
	gap: 16px;
	padding: 12px;
	background-color: var(--color-main-background);
	border-radius: var(--border-radius-element);
	border: 1px solid var(--color-border);
}

.report-list-item__rank {
	width: 32px;
	height: 32px;
	border-radius: 50%;
	background-color: var(--color-primary-element-element-element);
	color: var(--color-primary-element-element-element-text);
	display: flex;
	align-items: center;
	justify-content: center;
	font-weight: 600;
	font-size: 14px;
	flex-shrink: 0;
}

.report-list-item__content {
	flex: 1;
	min-width: 0;
}

.report-list-item__title {
	font-size: 16px;
	font-weight: 500;
	color: var(--color-main-text);
	margin-bottom: 4px;
}

.report-list-item__subtitle {
	font-size: 14px;
	color: var(--color-text-maxcontrast);
}

.empty-message {
	padding: 20px;
	text-align: center;
	color: var(--color-text-maxcontrast);
	font-size: 14px;
}

.text-error {
	color: var(--color-text-error);
}

.text-success {
	color: var(--color-text-success);
}

.loading-content {
	display: flex;
	flex-direction: column;
	align-items: center;
	justify-content: center;
	padding: 60px 20px;
	gap: 16px;
}

.loading-icon {
	animation: spin 1s linear infinite;
}

@keyframes spin {
	from {
		transform: rotate(0deg);
	}
	to {
		transform: rotate(360deg);
	}
}
</style>
