<template>
	<div class="debts-view">
		<!-- Debt Modal -->
		<DebtModal
			:open="modalOpen"
			:debt="editingDebt"
			:clients="clients"
			@close="closeModal"
			@saved="handleDebtSaved"
		/>

		<!-- Debt Payment Modal -->
		<DebtPaymentModal
			:open="paymentModalOpen"
			:debt="payingDebt"
			@close="closePaymentModal"
			@paid="handlePaymentAdded"
		/>

		<!-- Debt List View -->
		<div v-if="!selectedDebt" class="debts-list-view">
			<div class="domaincontrol-actions">
				<button class="button-vue button-vue--primary" @click="showAddModal">
					<MaterialIcon name="add" :size="20" />
					{{ translate('domaincontrol', 'Add Debt/Credit') }}
				</button>
				<div class="filter-buttons">
					<button
						class="button-vue button-vue--secondary"
						:class="{ 'button-vue--primary': currentFilter === 'all' }"
						@click="setFilter('all')"
					>
						{{ translate('domaincontrol', 'All') }}
					</button>
					<button
						class="button-vue button-vue--secondary"
						:class="{ 'button-vue--primary': currentFilter === 'debt' }"
						@click="setFilter('debt')"
					>
						{{ translate('domaincontrol', 'Debts') }}
					</button>
					<button
						class="button-vue button-vue--secondary"
						:class="{ 'button-vue--primary': currentFilter === 'credit' }"
						@click="setFilter('credit')"
					>
						{{ translate('domaincontrol', 'Credits') }}
					</button>
					<button
						class="button-vue button-vue--secondary"
						:class="{ 'button-vue--primary': currentFilter === 'upcoming' }"
						@click="setFilter('upcoming')"
					>
						{{ translate('domaincontrol', 'Upcoming Payments') }}
					</button>
					<button
						class="button-vue button-vue--secondary"
						:class="{ 'button-vue--primary': currentFilter === 'overdue' }"
						@click="setFilter('overdue')"
					>
						{{ translate('domaincontrol', 'Overdue') }}
					</button>
				</div>
				<div class="debt-search-wrapper">
					<input
						type="text"
						v-model="searchQuery"
						class="debt-search-input"
						:placeholder="translate('domaincontrol', 'Search debts...')"
						@input="filterDebts"
					/>
				</div>
			</div>

			<!-- Empty State -->
			<div v-if="filteredDebts.length === 0 && !loading" class="empty-content">
				<MaterialIcon name="accounting" :size="48" color="var(--color-text-maxcontrast)" class="empty-content__icon" />
				<p class="empty-content__text">
					{{ searchQuery ? translate('domaincontrol', 'No debts found') : translate('domaincontrol', 'No debts yet') }}
				</p>
				<button v-if="!searchQuery" class="button-vue button-vue--primary" @click="showAddModal">
					{{ translate('domaincontrol', 'Add First Debt/Credit') }}
				</button>
			</div>

			<!-- Loading State -->
			<div v-if="loading" class="loading-content">
				<MaterialIcon name="loading" :size="32" class="loading-icon" />
				<p>{{ translate('domaincontrol', 'Loading debts...') }}</p>
			</div>

			<!-- Debts List -->
			<div v-else-if="filteredDebts.length > 0" class="debts-list">
				<div
					v-for="debt in filteredDebts"
					:key="debt.id"
					class="list-item debt-item"
					:class="{
						'debt-item--debt': debt.type === 'debt',
						'debt-item--credit': debt.type === 'credit',
						'debt-item--overdue': isOverdue(debt),
					}"
					@click="selectDebt(debt)"
				>
					<div class="list-item__avatar" :class="`debt-type--${debt.type}`">
						<MaterialIcon :name="debt.type === 'debt' ? 'delete' : 'add'" :size="24" />
					</div>
					<div class="list-item__content">
						<div class="list-item__title">
							{{ debt.creditorDebtorName || debt.description || translate('domaincontrol', 'Debt/Credit') }}
						</div>
						<div class="list-item__meta">
							<span v-if="getDebtCategoryText(debt.debtType)">
								{{ getDebtCategoryText(debt.debtType) }}
							</span>
							<span v-if="getClientName(debt.clientId)">
								{{ getClientName(debt.clientId) }}
							</span>
							<span v-if="debt.nextPaymentDate">
								{{ translate('domaincontrol', 'Next payment') }}: {{ formatDate(debt.nextPaymentDate) }}
							</span>
						</div>
					</div>
					<div class="list-item__stats">
						<div class="list-item__stat">
							<div class="list-item__stat-label">{{ translate('domaincontrol', 'Total') }}</div>
							<div class="list-item__stat-value">
								{{ formatCurrency(debt.totalAmount, debt.currency) }}
							</div>
						</div>
						<div class="list-item__stat">
							<div class="list-item__stat-label">{{ translate('domaincontrol', 'Paid') }}</div>
							<div class="list-item__stat-value">
								{{ formatCurrency(debt.paidAmount, debt.currency) }}
							</div>
						</div>
						<div class="list-item__stat">
							<div class="list-item__stat-label">{{ translate('domaincontrol', 'Remaining') }}</div>
							<div class="list-item__stat-value" :class="getRemainingClass(debt)">
								{{ formatCurrency(getRemaining(debt), debt.currency) }}
							</div>
						</div>
						<div class="list-item__stat">
							<div class="list-item__stat-label">{{ translate('domaincontrol', 'Status') }}</div>
							<div class="list-item__stat-value">
								<span class="status-badge status-badge--simple" :class="getDebtStatusClass(debt)">
									{{ getDebtStatusText(debt.status) }}
								</span>
							</div>
						</div>
					</div>
					<div class="list-item__actions">
						<div class="popover-menu-wrapper" @click.stop>
							<button
								class="action-button action-button--more"
								@click.stop="togglePopover(debt.id)"
								:title="translate('domaincontrol', 'More options')"
							>
								<MaterialIcon name="more-vertical" :size="18" />
							</button>
							<div
								v-if="openPopover === debt.id"
								class="popover-menu"
								@click.stop
							>
								<button
									class="popover-menu-item"
									@click="editDebt(debt); closePopover()"
								>
									<MaterialIcon name="edit" :size="16" />
									{{ translate('domaincontrol', 'Edit') }}
								</button>
								<div class="popover-menu-separator"></div>
								<button
									class="popover-menu-item popover-menu-item--danger"
									@click="confirmDelete(debt); closePopover()"
								>
									<MaterialIcon name="delete" :size="16" />
									{{ translate('domaincontrol', 'Delete') }}
								</button>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>

		<!-- Debt Detail View -->
		<div v-else class="debt-detail-view">
			<div class="detail-header">
				<button class="button-vue button-vue--tertiary" @click="backToList">
					<MaterialIcon name="arrow-left" :size="20" />
					{{ translate('domaincontrol', 'Back') }}
				</button>
				<h2 class="detail-title">
					{{ selectedDebt.creditorDebtorName || selectedDebt.description || translate('domaincontrol', 'Debt/Credit') }}
				</h2>
				<div class="detail-actions">
					<button
						v-if="selectedDebt.status === 'active' && getRemaining(selectedDebt) > 0"
						class="button-vue button-vue--primary"
						@click="showPaymentModal"
					>
						<MaterialIcon name="add" :size="20" />
						{{ translate('domaincontrol', 'Add Payment') }}
					</button>
					<div class="popover-menu-wrapper" @click.stop>
						<button
							class="button-vue button-vue--secondary action-button--more"
							@click.stop="toggleDetailPopover()"
							:title="translate('domaincontrol', 'More options')"
						>
							<MaterialIcon name="more-vertical" :size="20" />
						</button>
						<div
							v-if="detailPopoverOpen"
							class="popover-menu"
							@click.stop
						>
							<button
								class="popover-menu-item"
								@click="editDebt(selectedDebt); closeDetailPopover()"
							>
								<MaterialIcon name="edit" :size="16" />
								{{ translate('domaincontrol', 'Edit') }}
							</button>
							<div class="popover-menu-separator"></div>
							<button
								class="popover-menu-item popover-menu-item--danger"
								@click="confirmDelete(selectedDebt); closeDetailPopover()"
							>
								<MaterialIcon name="delete" :size="16" />
								{{ translate('domaincontrol', 'Delete') }}
							</button>
						</div>
					</div>
				</div>
			</div>

			<div class="detail-content">
				<!-- Stats Cards -->
				<div class="detail-stats">
					<div class="stat-card">
						<div class="stat-card__icon" :class="getDebtTypeClass(selectedDebt)">
							<MaterialIcon :name="selectedDebt.type === 'debt' ? 'delete' : 'add'" :size="24" />
						</div>
						<div class="stat-card__content">
							<div class="stat-card__label">{{ translate('domaincontrol', 'Type') }}</div>
							<div class="stat-card__value">
								<span class="status-badge" :class="getDebtTypeClass(selectedDebt)">
									{{ getDebtTypeText(selectedDebt.type) }}
								</span>
							</div>
						</div>
					</div>
					<div class="stat-card">
						<div class="stat-card__icon">
							<MaterialIcon name="settings" :size="24" />
						</div>
						<div class="stat-card__content">
							<div class="stat-card__label">{{ translate('domaincontrol', 'Debt Type') }}</div>
							<div class="stat-card__value">
								{{ getDebtCategoryText(selectedDebt.debtType) || '-' }}
							</div>
						</div>
					</div>
					<div class="stat-card">
						<div class="stat-card__icon">
							<MaterialIcon name="accounting" :size="24" />
						</div>
						<div class="stat-card__content">
							<div class="stat-card__label">{{ translate('domaincontrol', 'Total Amount') }}</div>
							<div class="stat-card__value">
								{{ formatCurrency(selectedDebt.totalAmount, selectedDebt.currency) }}
							</div>
						</div>
					</div>
					<div class="stat-card">
						<div class="stat-card__icon">
							<MaterialIcon name="checkmark" :size="24" />
						</div>
						<div class="stat-card__content">
							<div class="stat-card__label">{{ translate('domaincontrol', 'Paid') }}</div>
							<div class="stat-card__value">
								{{ formatCurrency(selectedDebt.paidAmount, selectedDebt.currency) }}
							</div>
						</div>
					</div>
					<div class="stat-card">
						<div class="stat-card__icon" :class="getRemainingClass(selectedDebt)">
							<MaterialIcon name="accounting" :size="24" />
						</div>
						<div class="stat-card__content">
							<div class="stat-card__label">{{ translate('domaincontrol', 'Remaining') }}</div>
							<div class="stat-card__value" :class="getRemainingClass(selectedDebt)">
								{{ formatCurrency(getRemaining(selectedDebt), selectedDebt.currency) }}
							</div>
						</div>
					</div>
					<div class="stat-card">
						<div class="stat-card__icon" :class="getDebtStatusClass(selectedDebt)">
							<MaterialIcon name="checkmark" :size="24" />
						</div>
						<div class="stat-card__content">
							<div class="stat-card__label">{{ translate('domaincontrol', 'Status') }}</div>
							<div class="stat-card__value">
								<span class="status-badge" :class="getDebtStatusClass(selectedDebt)">
									{{ getDebtStatusText(selectedDebt.status) }}
								</span>
							</div>
						</div>
					</div>
				</div>

				<!-- Payment Progress -->
				<div class="detail-info-card">
					<h3 class="info-card-title">{{ translate('domaincontrol', 'Payment Progress') }}</h3>
					<div class="payment-progress">
						<div class="payment-progress-bar">
							<div
								class="payment-progress-fill"
								:style="{ width: getProgressPercentage(selectedDebt) + '%' }"
							></div>
						</div>
						<div class="payment-progress-text">
							{{ getProgressPercentage(selectedDebt).toFixed(1) }}% {{ translate('domaincontrol', 'paid') }}
						</div>
					</div>
				</div>

				<!-- Info Grid -->
				<div class="detail-info-grid">
					<div class="detail-info-card">
						<h3 class="info-card-title">{{ translate('domaincontrol', 'Debt/Credit Information') }}</h3>
						<div class="debt-info-list">
							<div class="debt-info-item">
								<strong>{{ translate('domaincontrol', 'Creditor/Debtor') }}:</strong>
								<span>{{ selectedDebt.creditorDebtorName || '-' }}</span>
							</div>
							<div class="debt-info-item">
								<strong>{{ translate('domaincontrol', 'Client') }}:</strong>
								<span>
									<a
										v-if="selectedDebt.clientId"
										href="#"
										@click.prevent="navigateToClient(selectedDebt.clientId)"
										class="link-primary"
									>
										{{ getClientName(selectedDebt.clientId) }}
									</a>
									<span v-else>-</span>
								</span>
							</div>
							<div class="debt-info-item">
								<strong>{{ translate('domaincontrol', 'Start Date') }}:</strong>
								<span>{{ formatDate(selectedDebt.startDate) || '-' }}</span>
							</div>
							<div class="debt-info-item">
								<strong>{{ translate('domaincontrol', 'Due Date') }}:</strong>
								<span :class="getDueDateClass(selectedDebt)">
									{{ formatDate(selectedDebt.dueDate) || '-' }}
								</span>
							</div>
							<div class="debt-info-item">
								<strong>{{ translate('domaincontrol', 'Next Payment') }}:</strong>
								<span :class="getNextPaymentClass(selectedDebt)">
									{{ formatDate(selectedDebt.nextPaymentDate) || '-' }}
								</span>
							</div>
							<div class="debt-info-item">
								<strong>{{ translate('domaincontrol', 'Payment Frequency') }}:</strong>
								<span>{{ getPaymentFrequencyText(selectedDebt.paymentFrequency) || '-' }}</span>
							</div>
							<div class="debt-info-item">
								<strong>{{ translate('domaincontrol', 'Payment Amount') }}:</strong>
								<span>{{ selectedDebt.paymentAmount ? formatCurrency(selectedDebt.paymentAmount, selectedDebt.currency) : '-' }}</span>
							</div>
							<div class="debt-info-item">
								<strong>{{ translate('domaincontrol', 'Interest Rate') }}:</strong>
								<span>{{ selectedDebt.interestRate ? selectedDebt.interestRate + '%' : '-' }}</span>
							</div>
						</div>
					</div>

					<div class="detail-info-card">
						<h3 class="info-card-title">{{ translate('domaincontrol', 'Description') }}</h3>
						<div class="detail-description">
							{{ selectedDebt.description || translate('domaincontrol', 'No description') }}
						</div>
					</div>
				</div>

				<!-- Payment History -->
				<div class="detail-info-card">
					<h3 class="info-card-title">{{ translate('domaincontrol', 'Payment History') }}</h3>
					<div v-if="payments.length === 0" class="empty-content">
						<p class="empty-content__text">{{ translate('domaincontrol', 'No payments yet') }}</p>
					</div>
					<div v-else class="payments-list">
						<div
							v-for="payment in payments"
							:key="payment.id"
							class="payment-item"
						>
							<div class="payment-item__content">
								<div class="payment-item__amount">
									{{ formatCurrency(payment.amount, selectedDebt.currency) }}
								</div>
								<div class="payment-item__meta">
									<span>{{ formatDate(payment.paymentDate) }}</span>
									<span v-if="getPaymentMethodText(payment.paymentMethod)">
										• {{ getPaymentMethodText(payment.paymentMethod) }}
									</span>
									<span v-if="payment.reference">
										• {{ payment.reference }}
									</span>
								</div>
								<div v-if="payment.notes" class="payment-item__notes">
									{{ payment.notes }}
								</div>
							</div>
							<button
								class="payment-item__delete"
								@click="confirmDeletePayment(payment)"
								:title="translate('domaincontrol', 'Delete')"
							>
								<MaterialIcon name="delete" :size="16" />
							</button>
						</div>
					</div>
				</div>

				<div class="detail-info-card">
					<h3 class="info-card-title">{{ translate('domaincontrol', 'Notes') }}</h3>
					<div class="detail-notes" v-html="selectedDebt.notes || translate('domaincontrol', 'No notes')"></div>
				</div>
			</div>
		</div>
	</div>
</template>

<script>
import api from '../services/api'
import MaterialIcon from './MaterialIcon.vue'
import DebtModal from './DebtModal.vue'
import DebtPaymentModal from './DebtPaymentModal.vue'

export default {
	name: 'Debts',
	components: {
		MaterialIcon,
		DebtModal,
		DebtPaymentModal,
	},
	data() {
		return {
			debts: [],
			clients: [],
			selectedDebt: null,
			payments: [],
			loading: false,
			modalOpen: false,
			editingDebt: null,
			paymentModalOpen: false,
			payingDebt: null,
			currentFilter: 'all',
			searchQuery: '',
			openPopover: null,
			detailPopoverOpen: false,
		}
	},
	computed: {
		filteredDebts() {
			let filtered = this.debts

			// Apply type filter
			if (this.currentFilter === 'debt' || this.currentFilter === 'credit') {
				filtered = filtered.filter(d => d.type === this.currentFilter)
			} else if (this.currentFilter === 'upcoming') {
				// Filter upcoming payments (next 30 days)
				const today = new Date()
				const thirtyDaysLater = new Date()
				thirtyDaysLater.setDate(today.getDate() + 30)
				filtered = filtered.filter(d => {
					if (!d.nextPaymentDate) return false
					const nextDate = new Date(d.nextPaymentDate)
					return nextDate >= today && nextDate <= thirtyDaysLater
				})
			} else if (this.currentFilter === 'overdue') {
				filtered = filtered.filter(d => this.isOverdue(d))
			}

			// Apply search query
			if (this.searchQuery) {
				const query = this.searchQuery.toLowerCase()
				filtered = filtered.filter(debt => {
					const name = (debt.creditorDebtorName || '').toLowerCase()
					const description = (debt.description || '').toLowerCase()
					const clientName = this.getClientName(debt.clientId) || ''
					return (
						name.includes(query) ||
						description.includes(query) ||
						clientName.toLowerCase().includes(query)
					)
				})
			}

			return filtered
		},
	},
	watch: {
		selectedDebt(newVal) {
			if (newVal && newVal.id) {
				this.loadPayments(newVal.id)
			} else {
				this.payments = []
			}
		},
	},
	mounted() {
		this.loadData()
		document.addEventListener('click', this.handleClickOutside)
	},
	beforeUnmount() {
		document.removeEventListener('click', this.handleClickOutside)
	},
	methods: {
		async loadData() {
			this.loading = true
			try {
				await Promise.all([
					this.loadDebts(),
					this.loadClients(),
				])
			} catch (error) {
				console.error('Error loading data:', error)
			} finally {
				this.loading = false
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
		async loadClients() {
			try {
				const response = await api.clients.getAll()
				this.clients = response.data || []
			} catch (error) {
				console.error('Error loading clients:', error)
				this.clients = []
			}
		},
		async loadPayments(debtId) {
			try {
				const response = await api.debts.get(debtId)
				if (response.data && response.data.payments) {
					this.payments = response.data.payments || []
				} else {
					this.payments = []
				}
			} catch (error) {
				console.error('Error loading payments:', error)
				this.payments = []
			}
		},
		filterDebts() {
			// Computed property handles filtering
		},
		setFilter(filter) {
			this.currentFilter = filter
		},
		selectDebt(debt) {
			this.selectedDebt = debt
		},
		backToList() {
			this.selectedDebt = null
		},
		showAddModal() {
			this.editingDebt = null
			this.modalOpen = true
		},
		editDebt(debt) {
			this.editingDebt = debt
			this.modalOpen = true
		},
		closeModal() {
			this.modalOpen = false
			this.editingDebt = null
		},
		async handleDebtSaved() {
			await this.loadDebts()
			if (this.selectedDebt) {
				const response = await api.debts.get(this.selectedDebt.id)
				this.selectedDebt = response.data
			}
			this.closeModal()
		},
		showPaymentModal() {
			this.payingDebt = this.selectedDebt
			this.paymentModalOpen = true
		},
		closePaymentModal() {
			this.paymentModalOpen = false
			this.payingDebt = null
		},
		async handlePaymentAdded() {
			if (this.selectedDebt) {
				const response = await api.debts.get(this.selectedDebt.id)
				this.selectedDebt = response.data
				if (response.data && response.data.payments) {
					this.payments = response.data.payments || []
				}
			}
			this.closePaymentModal()
		},
		async confirmDelete(debt) {
			if (!confirm(this.translate('domaincontrol', 'Are you sure you want to delete this debt/credit?'))) {
				return
			}
			try {
				await api.debts.delete(debt.id)
				await this.loadDebts()
				if (this.selectedDebt && this.selectedDebt.id === debt.id) {
					this.backToList()
				}
			} catch (error) {
				console.error('Error deleting debt:', error)
				alert(this.translate('domaincontrol', 'Error deleting debt/credit'))
			}
		},
		async confirmDeletePayment(payment) {
			if (!confirm(this.translate('domaincontrol', 'Are you sure you want to delete this payment?'))) {
				return
			}
			try {
				await api.debtPayments.delete(payment.id)
				await this.loadPayments(this.selectedDebt.id)
				const response = await api.debts.get(this.selectedDebt.id)
				this.selectedDebt = response.data
			} catch (error) {
				console.error('Error deleting payment:', error)
				alert(this.translate('domaincontrol', 'Error deleting payment'))
			}
		},
		getClientName(clientId) {
			if (!clientId) return ''
			const client = this.clients.find(c => c.id === clientId)
			return client ? client.name : ''
		},
		navigateToClient(clientId) {
			if (typeof window.DomainControl !== 'undefined' && window.DomainControl.switchTab) {
				window.DomainControl.switchTab('clients')
				setTimeout(() => {
					const event = new CustomEvent('select-client', { detail: { clientId } })
					window.dispatchEvent(event)
				}, 100)
			}
		},
		getRemaining(debt) {
			return (debt.totalAmount || 0) - (debt.paidAmount || 0)
		},
		getRemainingClass(debt) {
			const remaining = this.getRemaining(debt)
			if (remaining <= 0) return 'status-success'
			if (this.isOverdue(debt)) return 'status-critical'
			return ''
		},
		getProgressPercentage(debt) {
			if (!debt.totalAmount || debt.totalAmount === 0) return 0
			return ((debt.paidAmount || 0) / debt.totalAmount) * 100
		},
		isOverdue(debt) {
			if (!debt.nextPaymentDate || debt.status === 'paid') return false
			const nextDate = new Date(debt.nextPaymentDate)
			const today = new Date()
			today.setHours(0, 0, 0, 0)
			return nextDate < today
		},
		getDebtTypeClass(debt) {
			return `debt-type--${debt.type}`
		},
		getDebtTypeText(type) {
			const types = {
				debt: this.translate('domaincontrol', 'Debt'),
				credit: this.translate('domaincontrol', 'Credit'),
			}
			return types[type] || type
		},
		getDebtCategoryText(debtType) {
			const types = {
				credit_card: this.translate('domaincontrol', 'Credit Card'),
				loan: this.translate('domaincontrol', 'Loan'),
				physical: this.translate('domaincontrol', 'Physical Debt'),
				other: this.translate('domaincontrol', 'Other'),
			}
			return types[debtType] || debtType
		},
		getDebtStatusClass(debt) {
			return `status-${debt.status || 'active'}`
		},
		getDebtStatusText(status) {
			const statusTexts = {
				active: this.translate('domaincontrol', 'Active'),
				paid: this.translate('domaincontrol', 'Paid'),
				overdue: this.translate('domaincontrol', 'Overdue'),
				cancelled: this.translate('domaincontrol', 'Cancelled'),
			}
			return statusTexts[status] || status
		},
		getPaymentFrequencyText(frequency) {
			const frequencies = {
				daily: this.translate('domaincontrol', 'Daily'),
				weekly: this.translate('domaincontrol', 'Weekly'),
				monthly: this.translate('domaincontrol', 'Monthly'),
			}
			return frequencies[frequency] || frequency
		},
		getPaymentMethodText(method) {
			const methods = {
				cash: this.translate('domaincontrol', 'Cash'),
				bank: this.translate('domaincontrol', 'Bank Transfer'),
				credit_card: this.translate('domaincontrol', 'Credit Card'),
				debit_card: this.translate('domaincontrol', 'Debit Card'),
				online: this.translate('domaincontrol', 'Online Payment'),
				other: this.translate('domaincontrol', 'Other'),
			}
			return methods[method] || method
		},
		getDueDateClass(debt) {
			if (!debt.dueDate) return ''
			const dueDate = new Date(debt.dueDate)
			const today = new Date()
			today.setHours(0, 0, 0, 0)
			if (dueDate < today && debt.status !== 'paid') return 'status-critical'
			return ''
		},
		getNextPaymentClass(debt) {
			if (!debt.nextPaymentDate) return ''
			const nextDate = new Date(debt.nextPaymentDate)
			const today = new Date()
			today.setHours(0, 0, 0, 0)
			if (nextDate < today) return 'status-critical'
			const diffTime = nextDate - today
			const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24))
			if (diffDays <= 7) return 'status-warning'
			return ''
		},
		togglePopover(debtId) {
			this.openPopover = this.openPopover === debtId ? null : debtId
		},
		closePopover() {
			this.openPopover = null
		},
		toggleDetailPopover() {
			this.detailPopoverOpen = !this.detailPopoverOpen
		},
		closeDetailPopover() {
			this.detailPopoverOpen = false
		},
		handleClickOutside(event) {
			if (this.openPopover && !event.target.closest('.popover-menu-wrapper')) {
				this.closePopover()
			}
			if (this.detailPopoverOpen && !event.target.closest('.popover-menu-wrapper')) {
				this.closeDetailPopover()
			}
		},
		formatDate(date) {
			if (!date) return ''
			const d = new Date(date)
			return d.toLocaleDateString('tr-TR')
		},
		formatCurrency(amount, currency = 'USD') {
			if (amount === null || amount === undefined || amount === 0) return '-'
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
				'Add Debt/Credit': 'Borç/Alacak Ekle',
				'Search debts...': 'Borç/Alacaklarda ara...',
				'No debts found': 'Borç/Alacak bulunamadı',
				'No debts yet': 'Henüz borç/alacak yok',
				'Add First Debt/Credit': 'İlk Borç/Alacağı Ekle',
				'Loading debts...': 'Borç/Alacaklar yükleniyor...',
				'All': 'Tümü',
				'Debts': 'Borçlarım',
				'Credits': 'Alacaklarım',
				'Upcoming Payments': 'Yaklaşan Ödemeler',
				'Overdue': 'Gecikmiş',
				'Total': 'Toplam Tutar',
				'Paid': 'Ödenen',
				'Remaining': 'Kalan',
				'Status': 'Durum',
				'More options': 'Daha fazla seçenek',
				'Edit': 'Düzenle',
				'Delete': 'Sil',
				'Back': 'Geri',
				'Debt/Credit': 'Borç/Alacak',
				'Add Payment': 'Ödeme Ekle',
				'Type': 'Tür',
				'Debt Type': 'Borç Türü',
				'Total Amount': 'Toplam Tutar',
				'Payment Progress': 'Ödeme İlerlemesi',
				'paid': 'ödenen',
				'Debt/Credit Information': 'Borç/Alacak Bilgileri',
				'Creditor/Debtor': 'Alacaklı/Borçlu',
				'Client': 'Müşteri',
				'Start Date': 'Başlangıç Tarihi',
				'Due Date': 'Vade Tarihi',
				'Next Payment': 'Sonraki Ödeme',
				'Payment Frequency': 'Ödeme Sıklığı',
				'Payment Amount': 'Ödeme Tutarı',
				'Interest Rate': 'Faiz Oranı',
				'Description': 'Açıklama',
				'No description': 'Açıklama yok',
				'Payment History': 'Ödeme Geçmişi',
				'No payments yet': 'Henüz ödeme yok',
				'Notes': 'Notlar',
				'No notes': 'Not yok',
				'Debt': 'Borç',
				'Credit': 'Alacak',
				'Credit Card': 'Kredi Kartı',
				'Loan': 'Kredi',
				'Physical Debt': 'Fiziksel Borç',
				'Other': 'Diğer',
				'Active': 'Aktif',
				'Paid': 'Ödendi',
				'Cancelled': 'İptal',
				'Daily': 'Günlük',
				'Weekly': 'Haftalık',
				'Monthly': 'Aylık',
				'Cash': 'Nakit',
				'Bank Transfer': 'Banka Transferi',
				'Credit Card': 'Kredi Kartı',
				'Debit Card': 'Banka Kartı',
				'Online Payment': 'Online Ödeme',
				'Are you sure you want to delete this debt/credit?': 'Bu borç/alacağı silmek istediğinize emin misiniz?',
				'Error deleting debt/credit': 'Borç/Alacak silinirken hata oluştu',
				'Are you sure you want to delete this payment?': 'Bu ödemeyi silmek istediğinize emin misiniz?',
				'Error deleting payment': 'Ödeme silinirken hata oluştu',
			}

			return translations[text] || text
		},
	},
}
</script>

<style scoped>
.debts-view {
	width: 100%;
	height: 100%;
}

.debts-list-view {
	padding: 20px;
	padding-bottom: 40px;
}

.domaincontrol-actions {
	display: flex;
	gap: 12px;
	align-items: center;
	margin-bottom: 20px;
	flex-wrap: wrap;
}

.filter-buttons {
	display: flex;
	gap: 8px;
	flex-wrap: wrap;
}

.debt-search-wrapper {
	flex: 1;
	min-width: 200px;
}

.debt-search-input {
	width: 100%;
	padding: 8px 12px;
	border: 1px solid var(--color-border);
	border-radius: var(--border-radius-element);
	background-color: var(--color-background-dark);
	color: var(--color-main-text);
	font-size: 14px;
}

.debt-search-input:focus {
	outline: none;
	border-color: var(--color-primary-element);
}

.debts-list {
	display: grid;
	gap: 12px;
}

.debt-item {
	cursor: pointer;
	background-color: var(--color-main-background);
	border: 1px solid var(--color-border);
	border-radius: var(--border-radius-element);
	padding: 12px;
	display: flex;
	align-items: center;
	gap: 16px;
	transition: background-color 0.2s ease;
}

.debt-item:hover {
	background-color: var(--color-background-hover);
}

.debt-item--overdue {
	border-left: 4px solid var(--color-error);
}

.debt-item .list-item__avatar {
	width: 48px;
	height: 48px;
	border-radius: 50%;
	display: flex;
	align-items: center;
	justify-content: center;
	flex-shrink: 0;
}

.debt-type--debt {
	background-color: var(--color-error);
	color: var(--color-error-text);
}

.debt-type--credit {
	background-color: var(--color-success);
	color: var(--color-success-text);
}

.debt-item .list-item__content {
	flex: 1;
	min-width: 0;
}

.debt-item .list-item__title {
	font-size: 16px;
	font-weight: 500;
	color: var(--color-main-text);
	margin-bottom: 4px;
}

.debt-item .list-item__meta {
	display: flex;
	align-items: center;
	gap: 12px;
	font-size: 13px;
	color: var(--color-text-maxcontrast);
	flex-wrap: wrap;
}

.debt-item .list-item__stats {
	display: flex;
	gap: 24px;
	align-items: center;
}

.debt-item .list-item__stat {
	display: flex;
	flex-direction: column;
	gap: 4px;
	min-width: 100px;
}

.debt-item .list-item__stat-label {
	font-size: 12px;
	color: var(--color-text-maxcontrast);
}

.debt-item .list-item__stat-value {
	font-size: 14px;
	font-weight: 500;
	color: var(--color-main-text);
}

.debt-item .list-item__actions {
	display: flex;
	align-items: center;
	gap: 8px;
	flex-shrink: 0;
}

.debt-detail-view {
	padding: 20px;
	padding-bottom: 40px;
}

.detail-header {
	display: flex;
	align-items: center;
	gap: 16px;
	margin-bottom: 24px;
	flex-wrap: wrap;
}

.detail-title {
	margin: 0;
	flex: 1;
	font-size: 24px;
	font-weight: 600;
	color: var(--color-main-text);
}

.detail-actions {
	display: flex;
	gap: 8px;
	flex-wrap: wrap;
}

.detail-content {
	display: flex;
	flex-direction: column;
	gap: 20px;
}

.detail-stats {
	display: grid;
	grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
	gap: 16px;
	margin-bottom: 20px;
}

.stat-card {
	display: flex;
	align-items: center;
	gap: 12px;
	padding: 16px;
	background-color: var(--color-background-dark);
	border-radius: var(--border-radius-element);
	border: 1px solid var(--color-border);
}

.stat-card__icon {
	width: 48px;
	height: 48px;
	border-radius: 50%;
	background-color: var(--color-primary-element);
	display: flex;
	align-items: center;
	justify-content: center;
	color: var(--color-primary-element-text);
	flex-shrink: 0;
}

.stat-card__content {
	flex: 1;
	min-width: 0;
}

.stat-card__label {
	font-size: 12px;
	color: var(--color-text-maxcontrast);
	margin-bottom: 4px;
}

.stat-card__value {
	font-size: 18px;
	font-weight: 600;
	color: var(--color-main-text);
}

.detail-info-card {
	background-color: var(--color-background-dark);
	border-radius: var(--border-radius-element);
	padding: 20px;
	border: 1px solid var(--color-border);
}

.info-card-title {
	margin: 0 0 16px 0;
	font-size: 18px;
	font-weight: 600;
	color: var(--color-main-text);
}

.detail-info-grid {
	display: grid;
	grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
	gap: 20px;
}

.detail-description {
	white-space: pre-wrap;
	font-size: 14px;
	color: var(--color-main-text);
	line-height: 1.6;
}

.detail-notes {
	white-space: pre-wrap;
	font-family: inherit;
	font-size: 14px;
	color: var(--color-main-text);
	line-height: 1.6;
}

.payment-progress {
	margin-top: 12px;
}

.payment-progress-bar {
	width: 100%;
	height: 24px;
	background-color: var(--color-background-hover);
	border-radius: var(--border-radius-pill);
	overflow: hidden;
	margin-bottom: 8px;
}

.payment-progress-fill {
	height: 100%;
	background-color: var(--color-success);
	transition: width 0.3s ease;
}

.payment-progress-text {
	font-size: 14px;
	color: var(--color-text-maxcontrast);
	text-align: center;
}

.debt-info-list {
	display: flex;
	flex-direction: column;
	gap: 12px;
}

.debt-info-item {
	display: flex;
	align-items: flex-start;
	gap: 8px;
	font-size: 14px;
	line-height: 1.6;
}

.debt-info-item strong {
	color: var(--color-main-text);
	min-width: 140px;
}

.debt-info-item span {
	color: var(--color-text-maxcontrast);
	flex: 1;
}

.payments-list {
	display: flex;
	flex-direction: column;
	gap: 12px;
}

.payment-item {
	display: flex;
	align-items: center;
	justify-content: space-between;
	gap: 16px;
	padding: 12px;
	background-color: var(--color-main-background);
	border: 1px solid var(--color-border);
	border-radius: var(--border-radius-element);
}

.payment-item__content {
	flex: 1;
	min-width: 0;
}

.payment-item__amount {
	font-size: 16px;
	font-weight: 600;
	color: var(--color-main-text);
	margin-bottom: 4px;
}

.payment-item__meta {
	display: flex;
	align-items: center;
	gap: 8px;
	font-size: 13px;
	color: var(--color-text-maxcontrast);
	flex-wrap: wrap;
}

.payment-item__notes {
	font-size: 13px;
	color: var(--color-text-maxcontrast);
	margin-top: 4px;
	font-style: italic;
}

.payment-item__delete {
	background: none;
	border: none;
	color: var(--color-text-error);
	cursor: pointer;
	padding: 4px;
	border-radius: var(--border-radius-small);
	transition: background-color 0.2s;
	display: flex;
	align-items: center;
	justify-content: center;
	flex-shrink: 0;
}

.payment-item__delete:hover {
	background-color: var(--color-error);
	color: var(--color-error-text);
}

.link-primary {
	color: var(--color-primary-element);
	text-decoration: none;
}

.link-primary:hover {
	text-decoration: underline;
}

.status-badge--simple {
	padding: 4px 8px;
	border-radius: var(--border-radius-pill);
	font-size: 12px;
	font-weight: 500;
	background-color: var(--color-background-dark);
	color: var(--color-main-text);
	border: 1px solid var(--color-border);
}

.status-active {
	background-color: var(--color-primary-element);
	color: var(--color-primary-element-text);
}

.status-paid {
	background-color: var(--color-success);
	color: var(--color-success-text);
}

.status-overdue {
	background-color: var(--color-error);
	color: var(--color-error-text);
}

.status-cancelled {
	background-color: var(--color-text-maxcontrast);
	color: var(--color-main-background);
	opacity: 0.6;
}

.status-success {
	color: var(--color-text-success);
}

.status-warning {
	color: var(--color-text-error);
}

.status-critical {
	color: var(--color-text-error);
}

.popover-menu-wrapper {
	position: relative;
}

.popover-menu {
	position: absolute;
	right: 0;
	top: 100%;
	margin-top: 4px;
	background-color: var(--color-main-background);
	border: 1px solid var(--color-border);
	border-radius: var(--border-radius-container);
	box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
	min-width: 180px;
	z-index: 1000;
	overflow: hidden;
}

.popover-menu-item {
	display: flex;
	align-items: center;
	gap: 8px;
	width: 100%;
	padding: 10px 16px;
	border: none;
	background: none;
	color: var(--color-main-text);
	font-size: 14px;
	text-align: left;
	cursor: pointer;
	transition: background-color 0.2s;
}

.popover-menu-item:hover {
	background-color: var(--color-background-hover);
}

.popover-menu-item--danger {
	color: var(--color-text-error);
}

.popover-menu-item--danger:hover {
	background-color: var(--color-error);
	color: var(--color-error-text);
}

.popover-menu-separator {
	height: 1px;
	background-color: var(--color-border);
	margin: 4px 0;
}

.action-button--more {
	opacity: 0.7;
}

.action-button--more:hover {
	opacity: 1;
}
</style>
