<template>
	<div class="dashboard-view">
		<!-- Welcome Header -->
		<div class="dashboard-header">
			<div>
				<h2 class="dashboard-title">
					{{ translate('domaincontrol', 'Welcome, {name}', { name: currentUserName }) }}
				</h2>
				<p class="dashboard-subtitle">
					{{ translate('domaincontrol', 'Here\'s what\'s happening in your business today.') }}
				</p>
			</div>
			<div class="dashboard-date">
				{{ currentDate }}
			</div>
		</div>

		<!-- Main Stats Cards -->
		<div class="dashboard-stats-grid">
			<StatsCard
				:title="translate('domaincontrol', 'Total Clients')"
				:value="stats.clients"
				icon="icon-contacts"
				color="primary"
			/>
			<StatsCard
				:title="translate('domaincontrol', 'Active Projects')"
				:value="stats.projects"
				icon="icon-folder"
				color="success"
			/>
			<StatsCard
				:title="translate('domaincontrol', 'Pending Tasks')"
				:value="stats.tasks"
				icon="icon-checkmark"
				color="warning"
			/>
			<StatsCard
				:title="translate('domaincontrol', 'Unpaid Invoices')"
				:value="stats.unpaidInvoices"
				icon="icon-files"
				color="danger"
			/>
		</div>

		<div class="dashboard-main-row">
			<!-- Left Column -->
			<div class="dashboard-column">
				<!-- Detailed Stats -->
				<div class="dashboard-card">
					<h3 class="dashboard-card__header">{{ translate('domaincontrol', 'Overview') }}</h3>
					<div class="mini-stats-grid">
						<div class="mini-stat">
							<span class="mini-stat__label">{{ translate('domaincontrol', 'Domains') }}</span>
							<span class="mini-stat__value">{{ stats.domains }}</span>
						</div>
						<div class="mini-stat">
							<span class="mini-stat__label">{{ translate('domaincontrol', 'Hosting') }}</span>
							<span class="mini-stat__value">{{ stats.hostings }}</span>
						</div>
						<div class="mini-stat">
							<span class="mini-stat__label">{{ translate('domaincontrol', 'Websites') }}</span>
							<span class="mini-stat__value">{{ stats.websites }}</span>
						</div>
						<div class="mini-stat">
							<span class="mini-stat__label">{{ translate('domaincontrol', 'Income (This Month)') }}</span>
							<span class="mini-stat__value">{{ formatCurrency(stats.monthlyIncome) }}</span>
						</div>
						<div class="mini-stat">
							<span class="mini-stat__label">{{ translate('domaincontrol', 'Expense (This Month)') }}</span>
							<span class="mini-stat__value">{{ formatCurrency(stats.monthlyExpense) }}</span>
						</div>
						<div class="mini-stat">
							<span class="mini-stat__label">{{ translate('domaincontrol', 'Net Profit/Loss') }}</span>
							<span class="mini-stat__value" :class="{ 'text-error': stats.netProfit < 0, 'text-success': stats.netProfit > 0 }">
								{{ formatCurrency(stats.netProfit) }}
							</span>
						</div>
					</div>
				</div>

				<!-- Quick Actions -->
				<div class="dashboard-card">
					<h3 class="dashboard-card__header">{{ translate('domaincontrol', 'Quick Actions') }}</h3>
					<div class="quick-actions">
						<button class="button-vue button-vue--primary" @click="quickAdd('client')">
							<span class="icon-contacts" />
							{{ translate('domaincontrol', 'New Client') }}
						</button>
						<button class="button-vue button-vue--primary" @click="quickAdd('project')">
							<span class="icon-folder" />
							{{ translate('domaincontrol', 'New Project') }}
						</button>
						<button class="button-vue button-vue--primary" @click="quickAdd('task')">
							<span class="icon-checkmark" />
							{{ translate('domaincontrol', 'New Task') }}
						</button>
						<button class="button-vue button-vue--primary" @click="quickAdd('invoice')">
							<span class="icon-files" />
							{{ translate('domaincontrol', 'Create Invoice') }}
						</button>
					</div>
				</div>

				<!-- Recent Clients -->
				<div class="dashboard-card">
					<div class="dashboard-card__header">
						<h3>{{ translate('domaincontrol', 'Recent Clients') }}</h3>
						<button class="button-vue button-vue--tertiary" @click="switchToTab('clients')">
							{{ translate('domaincontrol', 'View All') }}
						</button>
					</div>
					<div v-if="recentClients.length === 0" class="empty-content">
						<span class="icon-contacts empty-content__icon" />
						<p class="empty-content__text">{{ translate('domaincontrol', 'No clients yet') }}</p>
					</div>
					<ul v-else class="recent-clients-list">
						<li v-for="client in recentClients" :key="client.id" class="recent-client-item">
							<div class="avatar" :style="{ backgroundColor: getAvatarColor(client.name) }">
								{{ getInitials(client.name) }}
							</div>
							<div class="recent-client-info">
								<div class="recent-client-name">{{ client.name }}</div>
								<div v-if="client.email" class="recent-client-email">{{ client.email }}</div>
							</div>
						</li>
					</ul>
				</div>
			</div>

			<!-- Right Column - Alerts -->
			<div class="dashboard-column">
				<!-- Overdue Payments -->
				<div class="dashboard-card alert-card alert-card--error">
					<div class="dashboard-card__header">
						<h3>{{ translate('domaincontrol', 'Overdue Payments') }}</h3>
						<span v-if="alerts.overdue.length > 0" class="counter-bubble">
							{{ alerts.overdue.length }}
						</span>
					</div>
					<div v-if="alerts.overdue.length === 0" class="empty-content">
						<span class="icon-files empty-content__icon" />
						<p class="empty-content__text">{{ translate('domaincontrol', 'No overdue payments') }}</p>
					</div>
					<ul v-else class="alert-list">
						<li v-for="item in alerts.overdue" :key="item.id" class="alert-item">
							<div class="alert-item__content">
								<div class="alert-item__title">{{ item.title }}</div>
								<div class="alert-item__meta">{{ formatDate(item.date) }}</div>
							</div>
							<div class="alert-item__amount">{{ formatCurrency(item.amount) }}</div>
						</li>
					</ul>
				</div>

				<!-- Upcoming Payments -->
				<div class="dashboard-card alert-card alert-card--warning">
					<div class="dashboard-card__header">
						<h3>{{ translate('domaincontrol', 'Upcoming Payments') }}</h3>
						<span v-if="alerts.upcoming.length > 0" class="counter-bubble">
							{{ alerts.upcoming.length }}
						</span>
					</div>
					<div v-if="alerts.upcoming.length === 0" class="empty-content">
						<span class="icon-files empty-content__icon" />
						<p class="empty-content__text">{{ translate('domaincontrol', 'No upcoming payments') }}</p>
					</div>
					<ul v-else class="alert-list">
						<li v-for="item in alerts.upcoming" :key="item.id" class="alert-item">
							<div class="alert-item__content">
								<div class="alert-item__title">{{ item.title }}</div>
								<div class="alert-item__meta">{{ formatDate(item.date) }}</div>
							</div>
							<div class="alert-item__amount">{{ formatCurrency(item.amount) }}</div>
						</li>
					</ul>
				</div>

				<!-- Upcoming Tasks -->
				<div class="dashboard-card alert-card alert-card--info">
					<div class="dashboard-card__header">
						<h3>{{ translate('domaincontrol', 'Upcoming Tasks') }}</h3>
						<span v-if="alerts.tasks.length > 0" class="counter-bubble">
							{{ alerts.tasks.length }}
						</span>
					</div>
					<div v-if="alerts.tasks.length === 0" class="empty-content">
						<span class="icon-checkmark empty-content__icon" />
						<p class="empty-content__text">{{ translate('domaincontrol', 'No upcoming tasks') }}</p>
					</div>
					<ul v-else class="alert-list">
						<li v-for="item in alerts.tasks" :key="item.id" class="alert-item">
							<div class="alert-item__content">
								<div class="alert-item__title">{{ item.title }}</div>
								<div class="alert-item__meta">{{ formatDate(item.date) }}</div>
							</div>
						</li>
					</ul>
				</div>

				<!-- Upcoming Debt Payments -->
				<div class="dashboard-card alert-card alert-card--warning">
					<div class="dashboard-card__header">
						<h3>{{ translate('domaincontrol', 'Upcoming Debt Payments') }}</h3>
						<span v-if="alerts.debts.length > 0" class="counter-bubble">
							{{ alerts.debts.length }}
						</span>
					</div>
					<div v-if="alerts.debts.length === 0" class="empty-content">
						<span class="icon-files empty-content__icon" />
						<p class="empty-content__text">{{ translate('domaincontrol', 'No upcoming debt payments') }}</p>
					</div>
					<ul v-else class="alert-list">
						<li v-for="item in alerts.debts" :key="item.id" class="alert-item">
							<div class="alert-item__content">
								<div class="alert-item__title">{{ item.title }}</div>
								<div class="alert-item__meta">{{ formatDate(item.date) }}</div>
							</div>
							<div class="alert-item__amount">{{ formatCurrency(item.amount) }}</div>
						</li>
					</ul>
				</div>
			</div>
		</div>
	</div>
</template>

<script>
import StatsCard from './StatsCard.vue'
import api from '../services/api'

export default {
	name: 'Dashboard',
	components: {
		StatsCard,
	},
	data() {
		return {
			currentUserName: OC.getCurrentUser().uid || 'User',
			currentDate: '',
			stats: {
				clients: 0,
				domains: 0,
				hostings: 0,
				websites: 0,
				projects: 0,
				tasks: 0,
				unpaidInvoices: 0,
				monthlyIncome: 0,
				monthlyExpense: 0,
				netProfit: 0,
			},
			recentClients: [],
			alerts: {
				overdue: [],
				upcoming: [],
				tasks: [],
				debts: [],
			},
			allData: {
				clients: [],
				domains: [],
				hostings: [],
				websites: [],
				projects: [],
				tasks: [],
				invoices: [],
				payments: [],
				transactions: [],
				debts: [],
			},
		}
	},
	mounted() {
		this.updateDate()
		this.loadDashboardData()
	},
	methods: {
		translate(appId, text, vars) {
			// Use global t() function from Nextcloud
			// Access via window to avoid webpack bundling issues
			if (typeof window !== 'undefined' && typeof window.t === 'function') {
				return window.t(appId, text, vars)
			}
			// Fallback - return original text
			return text
		},
		updateDate() {
			const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }
			this.currentDate = new Date().toLocaleDateString('tr-TR', options)
		},
		async loadDashboardData() {
			try {
				// Load all necessary data
				const [clients, domains, hostings, websites, projects, tasks, invoices, payments, transactions, debts] = await Promise.all([
					api.clients.getAll().catch(() => []),
					api.domains.getAll().catch(() => []),
					api.hostings.getAll().catch(() => []),
					api.websites.getAll().catch(() => []),
					api.projects.getActive().catch(() => []),
					api.tasks.getPending().catch(() => []),
					api.invoices.getAll().catch(() => []),
					api.payments.getAll().catch(() => []),
					api.transactions.getAll().catch(() => []),
					api.debts.getUpcomingPayments().catch(() => []),
				])

				this.allData = {
					clients: clients.data || [],
					domains: domains.data || [],
					hostings: hostings.data || [],
					websites: websites.data || [],
					projects: projects.data || [],
					tasks: tasks.data || [],
					invoices: invoices.data || [],
					payments: payments.data || [],
					transactions: transactions.data || [],
					debts: debts.data || [],
				}

				this.calculateStats()
				this.updateRecentClients()
				this.updateAlerts()
			} catch (error) {
				console.error('Error loading dashboard data:', error)
			}
		},
		calculateStats() {
			this.stats.clients = this.allData.clients.length
			this.stats.domains = this.allData.domains.length
			this.stats.hostings = this.allData.hostings.length
			this.stats.websites = this.allData.websites.length
			this.stats.projects = this.allData.projects.length
			this.stats.tasks = this.allData.tasks.length
			this.stats.unpaidInvoices = (this.allData.invoices || []).filter(i => ['draft', 'sent', 'overdue'].includes(i.status)).length

			// Calculate monthly income/expense
			const now = new Date()
			const currentMonth = now.getMonth()
			const currentYear = now.getFullYear()

			// Monthly income from payments
			this.stats.monthlyIncome = (this.allData.payments || []).reduce((sum, p) => {
				if (!p.paymentDate) return sum
				const date = new Date(p.paymentDate)
				if (date.getMonth() === currentMonth && date.getFullYear() === currentYear) {
					return sum + (parseFloat(p.amount) || 0)
				}
				return sum
			}, 0)

			// Monthly expense from transactions (type: expense)
			this.stats.monthlyExpense = (this.allData.transactions || []).reduce((sum, t) => {
				if (t.type !== 'expense') return sum
				if (!t.date) return sum
				const date = new Date(t.date)
				if (date.getMonth() === currentMonth && date.getFullYear() === currentYear) {
					return sum + (parseFloat(t.amount) || 0)
				}
				return sum
			}, 0)

			this.stats.netProfit = this.stats.monthlyIncome - this.stats.monthlyExpense
		},
		updateRecentClients() {
			this.recentClients = this.allData.clients.slice(0, 5)
		},
		updateAlerts() {
			// Overdue invoices
			this.alerts.overdue = (this.allData.invoices || [])
				.filter(i => i.status === 'overdue')
				.slice(0, 5)
				.map(i => ({
					id: i.id,
					title: `Invoice #${i.invoiceNumber || i.id}`,
					date: i.dueDate,
					amount: parseFloat(i.totalAmount || 0) - parseFloat(i.paidAmount || 0),
				}))

			// Upcoming payments (invoices due in next 7 days)
			const nextWeek = new Date()
			nextWeek.setDate(nextWeek.getDate() + 7)
			this.alerts.upcoming = (this.allData.invoices || [])
				.filter(i => {
					if (!i.dueDate || ['paid', 'cancelled'].includes(i.status)) return false
					const dueDate = new Date(i.dueDate)
					return dueDate <= nextWeek && dueDate >= new Date()
				})
				.slice(0, 5)
				.map(i => ({
					id: i.id,
					title: `Invoice #${i.invoiceNumber || i.id}`,
					date: i.dueDate,
					amount: parseFloat(i.totalAmount || 0) - parseFloat(i.paidAmount || 0),
				}))

			// Upcoming tasks
			this.alerts.tasks = (this.allData.tasks || [])
				.filter(t => {
					if (!t.dueDate || t.status === 'done') return false
					const dueDate = new Date(t.dueDate)
					return dueDate >= new Date()
				})
				.slice(0, 5)
				.map(t => ({
					id: t.id,
					title: t.title,
					date: t.dueDate,
				}))

			// Upcoming debt payments
			this.alerts.debts = (this.allData.debts || [])
				.filter(d => {
					if (!d.nextPaymentDate) return false
					const paymentDate = new Date(d.nextPaymentDate)
					return paymentDate >= new Date()
				})
				.slice(0, 5)
				.map(d => ({
					id: d.id,
					title: d.description || `Debt #${d.id}`,
					date: d.nextPaymentDate,
					amount: parseFloat(d.amount || 0),
				}))
		},
		formatCurrency(amount) {
			if (typeof amount !== 'number') return '0 ₼'
			return new Intl.NumberFormat('tr-TR', { style: 'currency', currency: 'AZN' }).format(amount)
		},
		formatDate(dateString) {
			if (!dateString) return '-'
			try {
				const date = new Date(dateString)
				return new Intl.DateTimeFormat('tr-TR', { day: 'numeric', month: 'short', year: 'numeric' }).format(date)
			} catch (e) {
				return dateString
			}
		},
		quickAdd(type) {
			// Emit event to parent or call global function
			if (typeof window.DomainControl !== 'undefined') {
				window.DomainControl.switchTab(type + 's')
				if (type === 'client') window.DomainControl.showClientModal()
				else if (type === 'project') window.DomainControl.showProjectModal()
				else if (type === 'task') window.DomainControl.showTaskModal()
				else if (type === 'invoice') window.DomainControl.showInvoiceModal()
			}
		},
		switchToTab(tab) {
			if (typeof window.DomainControl !== 'undefined') {
				window.DomainControl.switchTab(tab)
			}
		},
		getInitials(name) {
			if (!name) return '?'
			const parts = name.trim().split(' ')
			if (parts.length >= 2) {
				return (parts[0][0] + parts[parts.length - 1][0]).toUpperCase()
			}
			return name.substring(0, 2).toUpperCase()
		},
		getAvatarColor(name) {
			if (!name) return '#999'
			let hash = 0
			for (let i = 0; i < name.length; i++) {
				hash = name.charCodeAt(i) + ((hash << 5) - hash)
			}
			const hue = hash % 360
			return `hsl(${hue}, 70%, 50%)`
		},
	},
}
</script>

<style scoped>
.dashboard-view {
	padding: 20px;
}

.dashboard-header {
	display: flex;
	justify-content: space-between;
	align-items: flex-start;
	margin-bottom: 20px;
}

.dashboard-title {
	font-size: 2.2em;
	font-weight: bold;
	color: var(--color-main-text);
	margin-bottom: 5px;
}

.dashboard-subtitle {
	font-size: 1.1em;
	color: var(--color-text-maxcontrast);
}

.dashboard-date {
	font-size: 1.1em;
	color: var(--color-text-maxcontrast);
	white-space: nowrap;
}

.dashboard-stats-grid {
	display: grid;
	grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
	gap: 20px;
	margin-bottom: 30px;
}

.dashboard-main-row {
	display: grid;
	grid-template-columns: 2fr 1fr;
	gap: 20px;
}

.dashboard-column {
	display: flex;
	flex-direction: column;
	gap: 20px;
}

.dashboard-card {
	background-color: var(--color-background-dark);
	border-radius: var(--border-radius-element);
	padding: 20px;
	box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.dashboard-card__header {
	display: flex;
	justify-content: space-between;
	align-items: center;
	margin-bottom: 15px;
	font-size: 1.2em;
	color: var(--color-main-text);
}

.mini-stats-grid {
	display: grid;
	grid-template-columns: repeat(2, 1fr);
	gap: 15px;
}

.mini-stat {
	background-color: var(--color-background-hover);
	border-radius: var(--border-radius-small);
	padding: 15px;
	display: flex;
	flex-direction: column;
	align-items: flex-start;
	border: 1px solid var(--color-border);
}

.mini-stat__label {
	font-size: 0.85em;
	color: var(--color-text-maxcontrast);
	margin-bottom: 5px;
}

.mini-stat__value {
	font-size: 1.4em;
	font-weight: bold;
	color: var(--color-main-text);
}

.text-error {
	color: var(--color-error);
}

.text-success {
	color: var(--color-success);
}

.quick-actions {
	display: grid;
	grid-template-columns: repeat(2, 1fr);
	gap: 10px;
}

.quick-actions .button-vue {
	width: 100%;
	display: flex;
	align-items: center;
	justify-content: flex-start;
	padding: 10px 15px;
	font-size: 1em;
	border: none;
	border-radius: var(--border-radius-element);
	cursor: pointer;
	transition: all 0.2s ease;
}

.button-vue--primary {
	background-color: var(--color-primary);
	color: var(--color-primary-text);
}

.button-vue--primary:hover {
	background-color: var(--color-primary-hover);
}

.button-vue--tertiary {
	background-color: transparent;
	color: var(--color-primary);
	border: 1px solid var(--color-primary);
}

.button-vue--tertiary:hover {
	background-color: var(--color-primary-light);
}

.button-vue .icon-contacts,
.button-vue .icon-folder,
.button-vue .icon-checkmark,
.button-vue .icon-files {
	margin-right: 8px;
}

.recent-clients-list {
	list-style: none;
	padding: 0;
	margin: 0;
}

.recent-client-item {
	display: flex;
	align-items: center;
	padding: 10px 0;
	border-bottom: 1px solid var(--color-border);
}

.recent-client-item:last-child {
	border-bottom: none;
}

.avatar {
	width: 40px;
	height: 40px;
	border-radius: 50%;
	display: flex;
	align-items: center;
	justify-content: center;
	color: white;
	font-weight: bold;
	font-size: 0.9em;
	flex-shrink: 0;
}

.avatar {
	width: 40px;
	height: 40px;
	border-radius: 50%;
	display: flex;
	align-items: center;
	justify-content: center;
	color: white;
	font-weight: bold;
	font-size: 0.9em;
	flex-shrink: 0;
}

.recent-client-info {
	margin-left: 10px;
}

.recent-client-name {
	font-weight: bold;
	color: var(--color-main-text);
}

.recent-client-email {
	font-size: 0.9em;
	color: var(--color-text-maxcontrast);
}

.empty-content {
	display: flex;
	flex-direction: column;
	align-items: center;
	justify-content: center;
	padding: 40px 20px;
	text-align: center;
}

.empty-content__icon {
	font-size: 3em;
	color: var(--color-text-maxcontrast);
	margin-bottom: 10px;
	opacity: 0.5;
}

.empty-content__text {
	color: var(--color-text-maxcontrast);
	font-size: 1em;
}

.counter-bubble {
	display: inline-flex;
	align-items: center;
	justify-content: center;
	min-width: 24px;
	height: 24px;
	padding: 0 8px;
	background-color: var(--color-primary);
	color: var(--color-primary-text);
	border-radius: 12px;
	font-size: 0.85em;
	font-weight: bold;
}

.alert-card--error {
	background-color: var(--color-error);
	color: var(--color-error-text);
}

.alert-card--warning {
	background-color: var(--color-warning);
	color: var(--color-warning-text);
}

.alert-card--info {
	background-color: var(--color-info);
	color: var(--color-info-text);
}

.alert-card .dashboard-card__header h3 {
	color: inherit;
}

.alert-list {
	list-style: none;
	padding: 0;
	margin: 0;
}

.alert-item {
	display: flex;
	justify-content: space-between;
	align-items: center;
	padding: 10px 0;
	border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.alert-item:last-child {
	border-bottom: none;
}

.alert-item__title {
	font-weight: bold;
}

.alert-item__meta {
	font-size: 0.9em;
	opacity: 0.8;
}

.alert-item__amount {
	font-weight: bold;
	white-space: nowrap;
}

.empty-content {
	display: flex;
	flex-direction: column;
	align-items: center;
	justify-content: center;
	padding: 40px 20px;
	text-align: center;
}

.empty-content__icon {
	font-size: 3em;
	color: var(--color-text-maxcontrast);
	margin-bottom: 10px;
	opacity: 0.5;
}

.empty-content__text {
	color: var(--color-text-maxcontrast);
	font-size: 1em;
}

.counter-bubble {
	display: inline-flex;
	align-items: center;
	justify-content: center;
	min-width: 24px;
	height: 24px;
	padding: 0 8px;
	background-color: var(--color-primary);
	color: var(--color-primary-text);
	border-radius: 12px;
	font-size: 0.85em;
	font-weight: bold;
}

@media screen and (max-width: 1024px) {
	.dashboard-main-row {
		grid-template-columns: 1fr;
	}
}

@media screen and (max-width: 768px) {
	.dashboard-stats-grid {
		grid-template-columns: 1fr;
	}
	.mini-stats-grid {
		grid-template-columns: 1fr;
	}
	.quick-actions {
		grid-template-columns: 1fr;
	}
	.dashboard-header {
		flex-direction: column;
		align-items: flex-start;
	}
	.dashboard-date {
		margin-top: 10px;
	}
}
</style>

