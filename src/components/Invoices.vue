<template>
	<div class="invoices-view">
		<!-- Invoice Modal -->
		<InvoiceModal
			:open="modalOpen"
			:invoice="editingInvoice"
			:clients="clients"
			@close="closeModal"
			@saved="handleInvoiceSaved"
		/>

		<!-- Invoice Payment Modal -->
		<InvoicePaymentModal
			:open="paymentModalOpen"
			:invoice="payingInvoice"
			:clients="clients"
			@close="closePaymentModal"
			@paid="handleInvoicePaid"
		/>

		<!-- Invoice Item Modal -->
		<InvoiceItemModal
			:open="itemModalOpen"
			:invoice="currentInvoice"
			:item="editingItem"
			@close="closeItemModal"
			@saved="handleItemSaved"
		/>

		<!-- Invoice List View -->
		<div v-if="!selectedInvoice" class="invoices-list-view">
			<div class="domaincontrol-actions">
				<button class="button-vue button-vue--primary" @click="showAddModal">
					<MaterialIcon name="add" :size="20" />
					{{ translate('domaincontrol', 'Create Invoice') }}
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
						:class="{ 'button-vue--primary': currentFilter === 'unpaid' }"
						@click="setFilter('unpaid')"
					>
						{{ translate('domaincontrol', 'Unpaid') }}
					</button>
					<button
						class="button-vue button-vue--secondary"
						:class="{ 'button-vue--primary': currentFilter === 'overdue' }"
						@click="setFilter('overdue')"
					>
						{{ translate('domaincontrol', 'Overdue') }}
					</button>
					<button
						class="button-vue button-vue--secondary"
						:class="{ 'button-vue--primary': currentFilter === 'paid' }"
						@click="setFilter('paid')"
					>
						{{ translate('domaincontrol', 'Paid') }}
					</button>
				</div>
				<div class="invoice-search-wrapper">
					<input
						type="text"
						v-model="searchQuery"
						class="invoice-search-input"
						:placeholder="translate('domaincontrol', 'Search invoices...')"
						@input="filterInvoices"
					/>
				</div>
			</div>

			<!-- Empty State -->
			<div v-if="filteredInvoices.length === 0 && !loading" class="empty-content">
				<MaterialIcon name="files" :size="48" color="var(--color-text-maxcontrast)" class="empty-content__icon" />
				<p class="empty-content__text">
					{{ searchQuery ? translate('domaincontrol', 'No invoices found') : translate('domaincontrol', 'No invoices yet') }}
				</p>
				<button v-if="!searchQuery" class="button-vue button-vue--primary" @click="showAddModal">
					{{ translate('domaincontrol', 'Create First Invoice') }}
				</button>
			</div>

			<!-- Loading State -->
			<div v-if="loading" class="loading-content">
				<MaterialIcon name="loading" :size="32" class="loading-icon" />
				<p>{{ translate('domaincontrol', 'Loading invoices...') }}</p>
			</div>

			<!-- Invoices List -->
			<div v-else-if="filteredInvoices.length > 0" class="invoices-list">
				<div
					v-for="invoice in filteredInvoices"
					:key="invoice.id"
					class="list-item invoice-item"
					@click="selectInvoice(invoice)"
				>
					<div class="list-item__avatar">
						<MaterialIcon name="files" :size="24" />
					</div>
					<div class="list-item__content">
						<div class="list-item__title">{{ invoice.invoiceNumber }}</div>
						<div class="list-item__meta">
							<span v-if="getClientName(invoice.clientId)">
								{{ getClientName(invoice.clientId) }}
							</span>
							<span>{{ formatDate(invoice.issueDate) }}</span>
						</div>
					</div>
					<div class="list-item__stats">
						<div class="list-item__stat">
							<div class="list-item__stat-label">{{ translate('domaincontrol', 'Total') }}</div>
							<div class="list-item__stat-value">{{ formatCurrency(invoice.totalAmount, invoice.currency) }}</div>
						</div>
						<div class="list-item__stat">
							<div class="list-item__stat-label">{{ translate('domaincontrol', 'Remaining') }}</div>
							<div class="list-item__stat-value">
								{{ formatCurrency(getRemaining(invoice), invoice.currency) }}
							</div>
						</div>
						<div class="list-item__stat">
							<div class="list-item__stat-label">{{ translate('domaincontrol', 'Status') }}</div>
							<div class="list-item__stat-value">
								<span class="status-badge status-badge--simple" :class="getInvoiceStatusClass(invoice)">
									{{ getInvoiceStatusText(invoice.status) }}
								</span>
							</div>
						</div>
					</div>
					<div class="list-item__actions">
						<div class="popover-menu-wrapper" @click.stop>
							<button
								class="action-button action-button--more"
								@click.stop="togglePopover(invoice.id)"
								:title="translate('domaincontrol', 'More options')"
							>
								<MaterialIcon name="more-vertical" :size="18" />
							</button>
							<div
								v-if="openPopover === invoice.id"
								class="popover-menu"
								@click.stop
							>
								<button
									class="popover-menu-item"
									@click="editInvoice(invoice); closePopover()"
								>
									<MaterialIcon name="edit" :size="16" />
									{{ translate('domaincontrol', 'Edit') }}
								</button>
								<button
									class="popover-menu-item"
									@click="changeStatusQuick(invoice, 'draft'); closePopover()"
									v-if="invoice.status !== 'draft'"
								>
									{{ translate('domaincontrol', 'Set as Draft') }}
								</button>
								<button
									class="popover-menu-item"
									@click="changeStatusQuick(invoice, 'sent'); closePopover()"
									v-if="invoice.status !== 'sent'"
								>
									{{ translate('domaincontrol', 'Set as Sent') }}
								</button>
								<button
									class="popover-menu-item"
									@click="changeStatusQuick(invoice, 'paid'); closePopover()"
									v-if="invoice.status !== 'paid'"
								>
									{{ translate('domaincontrol', 'Set as Paid') }}
								</button>
								<button
									class="popover-menu-item"
									@click="changeStatusQuick(invoice, 'overdue'); closePopover()"
									v-if="invoice.status !== 'overdue'"
								>
									{{ translate('domaincontrol', 'Set as Overdue') }}
								</button>
								<button
									class="popover-menu-item"
									@click="changeStatusQuick(invoice, 'cancelled'); closePopover()"
									v-if="invoice.status !== 'cancelled'"
								>
									{{ translate('domaincontrol', 'Set as Cancelled') }}
								</button>
								<div class="popover-menu-separator"></div>
								<button
									class="popover-menu-item popover-menu-item--danger"
									@click="confirmDelete(invoice); closePopover()"
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

		<!-- Invoice Detail View -->
		<div v-else class="invoice-detail-view">
			<div class="detail-header">
				<button class="button-vue button-vue--tertiary" @click="backToList">
					<MaterialIcon name="arrow-left" :size="20" />
					{{ translate('domaincontrol', 'Back') }}
				</button>
				<h2 class="detail-title">{{ selectedInvoice.invoiceNumber }}</h2>
				<div class="detail-actions">
					<button class="button-vue button-vue--success" @click="showPaymentModal(selectedInvoice)">
						<MaterialIcon name="add_card" :size="20" />
						{{ translate('domaincontrol', 'Add Payment') }}
					</button>
					<button class="button-vue button-vue--info" @click="showItemModal(selectedInvoice)">
						<MaterialIcon name="add" :size="20" />
						{{ translate('domaincontrol', 'Add Item') }}
					</button>
					<button class="button-vue button-vue--secondary" @click="editInvoice(selectedInvoice)">
						<MaterialIcon name="edit" :size="20" />
						{{ translate('domaincontrol', 'Edit') }}
					</button>
					<button class="button-vue button-vue--danger" @click="confirmDelete(selectedInvoice)">
						<MaterialIcon name="delete" :size="20" />
						{{ translate('domaincontrol', 'Delete') }}
					</button>
				</div>
			</div>

			<div class="detail-content">
				<!-- Stats Cards -->
				<div class="detail-stats">
					<div class="stat-card">
						<div class="stat-card__icon">
							<MaterialIcon name="contacts" :size="24" />
						</div>
						<div class="stat-card__content">
							<div class="stat-card__label">{{ translate('domaincontrol', 'Client') }}</div>
							<div class="stat-card__value">
								<a
									href="#"
									@click.prevent="navigateToClient(selectedInvoice.clientId)"
									class="link-primary"
								>
									{{ getClientName(selectedInvoice.clientId) }}
								</a>
							</div>
						</div>
					</div>
					<div class="stat-card">
						<div class="stat-card__icon">
							<MaterialIcon name="payments" :size="24" />
						</div>
						<div class="stat-card__content">
							<div class="stat-card__label">{{ translate('domaincontrol', 'Total') }}</div>
							<div class="stat-card__value">{{ formatCurrency(selectedInvoice.totalAmount, selectedInvoice.currency) }}</div>
						</div>
					</div>
					<div class="stat-card">
						<div class="stat-card__icon">
							<MaterialIcon name="checkmark" :size="24" />
						</div>
						<div class="stat-card__content">
							<div class="stat-card__label">{{ translate('domaincontrol', 'Paid') }}</div>
							<div class="stat-card__value">{{ formatCurrency(selectedInvoice.paidAmount || 0, selectedInvoice.currency) }}</div>
						</div>
					</div>
					<div class="stat-card">
						<div class="stat-card__icon" :class="getRemainingClass(selectedInvoice)">
							<MaterialIcon name="timer" :size="24" />
						</div>
						<div class="stat-card__content">
							<div class="stat-card__label">{{ translate('domaincontrol', 'Remaining') }}</div>
							<div class="stat-card__value" :class="getRemainingClass(selectedInvoice)">
								{{ formatCurrency(getRemaining(selectedInvoice), selectedInvoice.currency) }}
							</div>
						</div>
					</div>
				</div>

				<!-- Payment Progress -->
				<div class="detail-info-card">
					<h3 class="info-card-title">{{ translate('domaincontrol', 'Payment Status') }}</h3>
					<div class="payment-progress">
						<div class="payment-progress__header">
							<span>{{ translate('domaincontrol', 'Payment Progress') }}</span>
							<span><strong>{{ getPaymentPercent(selectedInvoice) }}%</strong> ({{ formatCurrency(selectedInvoice.paidAmount || 0, selectedInvoice.currency) }} / {{ formatCurrency(selectedInvoice.totalAmount, selectedInvoice.currency) }})</span>
						</div>
						<div class="progress-bar">
							<div
								class="progress-bar__fill"
								:style="{ width: getPaymentPercent(selectedInvoice) + '%', backgroundColor: getProgressColor(selectedInvoice) }"
							></div>
						</div>
					</div>
				</div>

				<!-- Info Grid -->
				<div class="detail-info-grid">
					<div class="detail-info-card">
						<h3 class="info-card-title">{{ translate('domaincontrol', 'Invoice Information') }}</h3>
						<table class="detail-table">
							<tbody>
								<tr>
									<td class="table-label">{{ translate('domaincontrol', 'Issue Date') }}</td>
									<td class="table-value">{{ formatDate(selectedInvoice.issueDate) || '-' }}</td>
								</tr>
								<tr>
									<td class="table-label">{{ translate('domaincontrol', 'Due Date') }}</td>
									<td class="table-value">
										{{ formatDate(selectedInvoice.dueDate) || '-' }}
										<span v-if="getDueDays(selectedInvoice) !== null" class="due-days-badge" :class="getDueDaysClass(selectedInvoice)">
											{{ getDueDaysText(selectedInvoice) }}
										</span>
									</td>
								</tr>
								<tr>
									<td class="table-label">{{ translate('domaincontrol', 'Status') }}</td>
									<td class="table-value">
										<span class="status-badge" :class="getInvoiceStatusClass(selectedInvoice)">
											{{ getInvoiceStatusText(selectedInvoice.status) }}
										</span>
									</td>
								</tr>
								<tr>
									<td class="table-label">{{ translate('domaincontrol', 'Currency') }}</td>
									<td class="table-value">{{ selectedInvoice.currency || '-' }}</td>
								</tr>
								<tr v-if="selectedInvoice.notes">
									<td class="table-label">{{ translate('domaincontrol', 'Notes') }}</td>
									<td class="table-value">{{ selectedInvoice.notes }}</td>
								</tr>
							</tbody>
						</table>
					</div>

					<div class="detail-info-card">
						<h3 class="info-card-title">{{ translate('domaincontrol', 'Invoice Items') }}</h3>
						<div class="invoice-items-list">
							<div v-if="invoiceItems.length === 0" class="empty-mini">
								{{ translate('domaincontrol', 'No items yet') }}
							</div>
							<div
								v-for="item in invoiceItems"
								:key="item.id"
								class="invoice-item-row"
							>
								<div class="invoice-item__description">{{ item.description }}</div>
								<div class="invoice-item__details">
									<span>{{ item.quantity }} x {{ formatCurrency(item.unitPrice, selectedInvoice.currency) }}</span>
									<span class="invoice-item__total">{{ formatCurrency(item.totalPrice, selectedInvoice.currency) }}</span>
								</div>
								<button
									class="action-button action-button--delete"
									@click="confirmDeleteItem(item)"
									:title="translate('domaincontrol', 'Delete')"
								>
									<MaterialIcon name="delete" :size="16" />
								</button>
							</div>
						</div>
					</div>
				</div>

				<!-- Payment History -->
				<div class="detail-history-card">
					<h3 class="history-card-title">{{ translate('domaincontrol', 'Payment History') }}</h3>
					<div class="history-list">
						<div v-if="invoicePayments.length === 0" class="empty-state">
							{{ translate('domaincontrol', 'No payments yet') }}
						</div>
						<div
							v-for="payment in invoicePayments"
							:key="payment.id"
							class="history-item"
						>
							<div class="history-date">
								<MaterialIcon name="calendar_month" :size="16" />
								{{ formatDate(payment.paymentDate) }}
							</div>
							<div class="history-content">
								<strong>{{ formatCurrency(payment.amount, payment.currency) }}</strong>
								<span class="history-detail">{{ translate('domaincontrol', payment.paymentMethod) }}</span>
								<span v-if="payment.reference" class="history-note">{{ translate('domaincontrol', 'Reference') }}: {{ payment.reference }}</span>
								<span v-if="payment.notes" class="history-note">{{ payment.notes }}</span>
							</div>
							<button
								class="action-button action-button--delete"
								@click="confirmDeletePayment(payment)"
								:title="translate('domaincontrol', 'Delete')"
							>
								<MaterialIcon name="delete" :size="16" />
							</button>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</template>

<script>
import api from '../services/api'
import MaterialIcon from './MaterialIcon.vue'
import InvoiceModal from './InvoiceModal.vue'
import InvoicePaymentModal from './InvoicePaymentModal.vue'
import InvoiceItemModal from './InvoiceItemModal.vue'

export default {
	name: 'Invoices',
	components: {
		MaterialIcon,
		InvoiceModal,
		InvoicePaymentModal,
		InvoiceItemModal,
	},
	data() {
		return {
			invoices: [],
			clients: [],
			selectedInvoice: null,
			invoiceItems: [],
			invoicePayments: [],
			loading: false,
			modalOpen: false,
			paymentModalOpen: false,
			itemModalOpen: false,
			editingInvoice: null,
			editingItem: null,
			currentInvoice: null,
			payingInvoice: null,
			searchQuery: '',
			currentFilter: 'all',
			openPopover: null,
			invoiceStatuses: [
				{ value: 'draft', label: 'Draft' },
				{ value: 'sent', label: 'Sent' },
				{ value: 'paid', label: 'Paid' },
				{ value: 'overdue', label: 'Overdue' },
				{ value: 'cancelled', label: 'Cancelled' },
			],
		}
	},
	computed: {
		filteredInvoices() {
			let filtered = this.invoices

			// Apply status filter
			if (this.currentFilter === 'unpaid') {
				filtered = filtered.filter(i => ['draft', 'sent'].includes(i.status))
			} else if (this.currentFilter === 'overdue') {
				filtered = filtered.filter(i => i.status === 'overdue' || (new Date(i.dueDate) < new Date() && i.status !== 'paid'))
			} else if (this.currentFilter === 'paid') {
				filtered = filtered.filter(i => i.status === 'paid')
			}

			// Apply search filter
			if (this.searchQuery) {
				const query = this.searchQuery.toLowerCase()
				filtered = filtered.filter(invoice => {
					const clientName = this.getClientName(invoice.clientId) || ''
					return (
						invoice.invoiceNumber?.toLowerCase().includes(query) ||
						clientName.toLowerCase().includes(query)
					)
				})
			}

			return filtered
		},
	},
	mounted() {
		this.loadData()
		// Close popover when clicking outside
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
					this.loadInvoices(),
					this.loadClients(),
				])
			} finally {
				this.loading = false
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
		async loadClients() {
			try {
				const response = await api.clients.getAll()
				this.clients = response.data || []
			} catch (error) {
				console.error('Error loading clients:', error)
				this.clients = []
			}
		},
		async loadInvoiceDetail(invoiceId) {
			try {
				const [invoiceResponse, itemsResponse, paymentsResponse] = await Promise.all([
					api.invoices.get(invoiceId),
					api.invoices.getItems(invoiceId),
					api.invoices.getPayments(invoiceId),
				])
				this.selectedInvoice = invoiceResponse.data
				this.invoiceItems = itemsResponse.data || []
				this.invoicePayments = paymentsResponse.data || []
			} catch (error) {
				console.error('Error loading invoice detail:', error)
			}
		},
		filterInvoices() {
			// Computed property handles filtering
		},
		setFilter(filter) {
			this.currentFilter = filter
		},
		selectInvoice(invoice) {
			this.selectedInvoice = invoice
			this.loadInvoiceDetail(invoice.id)
		},
		backToList() {
			this.selectedInvoice = null
			this.invoiceItems = []
			this.invoicePayments = []
		},
		showAddModal() {
			this.editingInvoice = null
			this.modalOpen = true
		},
		editInvoice(invoice) {
			this.editingInvoice = invoice
			this.modalOpen = true
		},
		closeModal() {
			this.modalOpen = false
			this.editingInvoice = null
		},
		async handleInvoiceSaved() {
			await this.loadInvoices()
			if (this.selectedInvoice) {
				await this.loadInvoiceDetail(this.selectedInvoice.id)
			}
			this.closeModal()
		},
		showPaymentModal(invoice) {
			this.payingInvoice = invoice
			this.paymentModalOpen = true
		},
		closePaymentModal() {
			this.paymentModalOpen = false
			this.payingInvoice = null
		},
		async handleInvoicePaid() {
			if (this.selectedInvoice) {
				await this.loadInvoiceDetail(this.selectedInvoice.id)
			}
			await this.loadInvoices()
			this.closePaymentModal()
		},
		showItemModal(invoice) {
			this.currentInvoice = invoice
			this.editingItem = null
			this.itemModalOpen = true
		},
		closeItemModal() {
			this.itemModalOpen = false
			this.editingItem = null
			this.currentInvoice = null
		},
		async handleItemSaved() {
			if (this.selectedInvoice) {
				await this.loadInvoiceDetail(this.selectedInvoice.id)
			}
			this.closeItemModal()
		},
		async confirmDelete(invoice) {
			if (!confirm(this.translate('domaincontrol', 'Are you sure you want to delete this invoice?'))) {
				return
			}
			try {
				await api.invoices.delete(invoice.id)
				await this.loadInvoices()
				if (this.selectedInvoice && this.selectedInvoice.id === invoice.id) {
					this.backToList()
				}
			} catch (error) {
				console.error('Error deleting invoice:', error)
				alert(this.translate('domaincontrol', 'Error deleting invoice'))
			}
		},
		async confirmDeleteItem(item) {
			if (!confirm(this.translate('domaincontrol', 'Are you sure you want to delete this item?'))) {
				return
			}
			try {
				await api.invoices.removeItem(this.selectedInvoice.id, item.id)
				await this.loadInvoiceDetail(this.selectedInvoice.id)
			} catch (error) {
				console.error('Error deleting item:', error)
				alert(this.translate('domaincontrol', 'Error deleting item'))
			}
		},
		async confirmDeletePayment(payment) {
			if (!confirm(this.translate('domaincontrol', 'Are you sure you want to delete this payment?'))) {
				return
			}
			try {
				await api.payments.delete(payment.id)
				await this.loadInvoiceDetail(this.selectedInvoice.id)
				await this.loadInvoices()
			} catch (error) {
				console.error('Error deleting payment:', error)
				alert(this.translate('domaincontrol', 'Error deleting payment'))
			}
		},
		async changeStatus(newStatus) {
			try {
				await api.invoices.update(this.selectedInvoice.id, { status: newStatus })
				await this.loadInvoiceDetail(this.selectedInvoice.id)
				await this.loadInvoices()
			} catch (error) {
				console.error('Error changing status:', error)
				alert(this.translate('domaincontrol', 'Error changing status'))
			}
		},
		async changeStatusQuick(invoice, newStatus) {
			try {
				await api.invoices.update(invoice.id, { status: newStatus })
				await this.loadInvoices()
				if (this.selectedInvoice && this.selectedInvoice.id === invoice.id) {
					await this.loadInvoiceDetail(invoice.id)
				}
			} catch (error) {
				console.error('Error changing status:', error)
				alert(this.translate('domaincontrol', 'Error changing status'))
			}
		},
		togglePopover(invoiceId) {
			this.openPopover = this.openPopover === invoiceId ? null : invoiceId
		},
		closePopover() {
			this.openPopover = null
		},
		handleClickOutside(event) {
			if (this.openPopover && !event.target.closest('.popover-menu-wrapper')) {
				this.closePopover()
			}
		},
		getClientName(clientId) {
			const client = this.clients.find(c => c.id === clientId)
			return client ? client.name : ''
		},
		navigateToClient(clientId) {
			if (typeof window.DomainControl !== 'undefined' && window.DomainControl.switchTab) {
				window.DomainControl.switchTab('clients')
				// Trigger client selection in Clients component
				setTimeout(() => {
					const event = new CustomEvent('select-client', { detail: { clientId } })
					window.dispatchEvent(event)
				}, 100)
			}
		},
		getRemaining(invoice) {
			return Math.max(0, (invoice.totalAmount || 0) - (invoice.paidAmount || 0))
		},
		getRemainingClass(invoice) {
			const remaining = this.getRemaining(invoice)
			if (remaining === 0) return 'status-ok'
			if (invoice.status === 'overdue') return 'status-critical'
			return 'status-warning'
		},
		getPaymentPercent(invoice) {
			const total = invoice.totalAmount || 0
			if (total === 0) return 0
			return Math.round(((invoice.paidAmount || 0) / total) * 100)
		},
		getProgressColor(invoice) {
			const percent = this.getPaymentPercent(invoice)
			if (percent === 100) return 'var(--color-success)'
			if (percent >= 50) return 'var(--color-warning)'
			return 'var(--color-primary-element)'
		},
		getDueDays(invoice) {
			if (!invoice.dueDate) return null
			const dueDate = new Date(invoice.dueDate)
			const today = new Date()
			today.setHours(0, 0, 0, 0)
			return Math.ceil((dueDate - today) / (1000 * 60 * 60 * 24))
		},
		getDueDaysClass(invoice) {
			const days = this.getDueDays(invoice)
			if (days === null) return ''
			if (invoice.status === 'paid') return ''
			if (days < 0) return 'status-critical'
			if (days === 0) return 'status-warning'
			if (days <= 7) return 'status-warning'
			return ''
		},
		getDueDaysText(invoice) {
			const days = this.getDueDays(invoice)
			if (days === null) return ''
			if (invoice.status === 'paid') return ''
			if (days < 0) return `${Math.abs(days)} ${this.translate('domaincontrol', 'days overdue')}`
			if (days === 0) return this.translate('domaincontrol', 'Today')
			if (days <= 7) return `${days} ${this.translate('domaincontrol', 'days left')}`
			return ''
		},
		getInvoiceStatusClass(invoice) {
			return `status-${invoice.status || 'draft'}`
		},
		getInvoiceStatusText(status) {
			const statusTexts = {
				draft: this.translate('domaincontrol', 'Draft'),
				sent: this.translate('domaincontrol', 'Sent'),
				paid: this.translate('domaincontrol', 'Paid'),
				overdue: this.translate('domaincontrol', 'Overdue'),
				cancelled: this.translate('domaincontrol', 'Cancelled'),
				partial: this.translate('domaincontrol', 'Partial Payment'),
			}
			return statusTexts[status] || status
		},
		formatDate(date) {
			if (!date) return ''
			const d = new Date(date)
			return d.toLocaleDateString('tr-TR')
		},
		formatCurrency(amount, currency = 'USD') {
			if (amount === null || amount === undefined) return '-'
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
				'Create Invoice': 'Fatura Oluştur',
				'All': 'Tümü',
				'Unpaid': 'Ödenmemiş',
				'Overdue': 'Gecikmiş',
				'Paid': 'Ödendi',
				'Search invoices...': 'Faturalarda ara...',
				'No invoices found': 'Fatura bulunamadı',
				'No invoices yet': 'Henüz fatura yok',
				'Create First Invoice': 'İlk Faturayı Oluştur',
				'Loading invoices...': 'Faturalar yükleniyor...',
				'Total': 'Toplam',
				'Remaining': 'Kalan',
				'Status': 'Durum',
				'Edit': 'Düzenle',
				'Delete': 'Sil',
				'Back': 'Geri',
				'Add Payment': 'Ödeme Ekle',
				'Add Item': 'Kalem Ekle',
				'Client': 'Müşteri',
				'Paid': 'Ödenen',
				'Payment Status': 'Ödeme Durumu',
				'Payment Progress': 'Ödeme İlerlemesi',
				'Change Status': 'Durum Değiştir',
				'Invoice Information': 'Fatura Bilgileri',
				'Issue Date': 'Düzenleme Tarihi',
				'Due Date': 'Vade Tarihi',
				'Currency': 'Para Birimi',
				'Notes': 'Notlar',
				'Invoice Items': 'Fatura Kalemleri',
				'No items yet': 'Henüz kalem yok',
				'Payment History': 'Ödeme Geçmişi',
				'No payments yet': 'Henüz ödeme yok',
				'Reference': 'Referans',
				'Draft': 'Taslak',
				'Sent': 'Gönderildi',
				'Overdue': 'Gecikmiş',
				'Cancelled': 'İptal',
				'Partial Payment': 'Kısmi Ödeme',
				'days overdue': 'gün gecikti',
				'Today': 'Bugün',
				'days left': 'gün kaldı',
				'Are you sure you want to delete this invoice?': 'Bu faturayı silmek istediğinize emin misiniz?',
				'Error deleting invoice': 'Fatura silinirken hata oluştu',
				'Are you sure you want to delete this item?': 'Bu kalemi silmek istediğinize emin misiniz?',
				'Error deleting item': 'Kalem silinirken hata oluştu',
				'Are you sure you want to delete this payment?': 'Bu ödemeyi silmek istediğinize emin misiniz?',
				'Error deleting payment': 'Ödeme silinirken hata oluştu',
				'Error changing status': 'Durum değiştirilirken hata oluştu',
				'More options': 'Daha fazla seçenek',
				'Set as Draft': 'Taslak olarak işaretle',
				'Set as Sent': 'Gönderildi olarak işaretle',
				'Set as Paid': 'Ödendi olarak işaretle',
				'Set as Overdue': 'Gecikmiş olarak işaretle',
				'Set as Cancelled': 'İptal olarak işaretle',
			}

			return translations[text] || text
		},
	},
}
</script>

<style scoped>
.invoices-view {
	width: 100%;
	height: 100%;
}

.invoices-list-view {
	padding: 20px;
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
}

.invoice-search-wrapper {
	flex: 1;
	min-width: 200px;
}

.invoice-search-input {
	width: 100%;
	padding: 8px 12px;
	border: 1px solid var(--color-border);
	border-radius: var(--border-radius-element);
	background-color: var(--color-background-dark);
	color: var(--color-main-text);
	font-size: 14px;
}

.invoice-search-input:focus {
	outline: none;
	border-color: var(--color-primary-element);
}

.invoices-list {
	display: grid;
	gap: 12px;
}

.invoice-item {
	cursor: pointer;
	background-color: var(--color-main-background);
	border: 1px solid var(--color-border);
}

.invoice-item:hover {
	background-color: var(--color-background-hover);
}

.invoice-item .list-item__avatar {
	background-color: var(--color-background-dark);
}

.invoice-item .list-item__avatar .material-icon {
	color: var(--color-text-maxcontrast);
}

.list-item__stat-value {
	color: var(--color-main-text);
	font-weight: 500;
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

.invoice-detail-view {
	padding: 20px;
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

.payment-progress {
	margin-top: 12px;
}

.payment-progress__header {
	display: flex;
	justify-content: space-between;
	margin-bottom: 8px;
	font-size: 14px;
	color: var(--color-main-text);
}

.progress-bar {
	height: 12px;
	background: var(--color-background-dark);
	border-radius: 6px;
	overflow: hidden;
}

.progress-bar__fill {
	height: 100%;
	transition: width 0.3s;
}

.status-actions {
	display: flex;
	gap: 8px;
	flex-wrap: wrap;
}

.detail-info-grid {
	display: grid;
	grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
	gap: 20px;
}

.detail-table {
	width: 100%;
	border-collapse: collapse;
}

.detail-table td {
	padding: 8px 0;
	border-bottom: 1px solid var(--color-border);
}

.detail-table tr:last-child td {
	border-bottom: none;
}

.table-label {
	font-weight: 500;
	color: var(--color-text-maxcontrast);
	width: 40%;
}

.table-value {
	color: var(--color-main-text);
}

.due-days-badge {
	margin-left: 8px;
	padding: 2px 8px;
	border-radius: var(--border-radius-pill);
	font-size: 12px;
	font-weight: 500;
}

.invoice-items-list {
	display: flex;
	flex-direction: column;
	gap: 12px;
}

.invoice-item-row {
	display: flex;
	align-items: center;
	gap: 12px;
	padding: 12px;
	background-color: var(--color-main-background);
	border-radius: var(--border-radius-element);
	border: 1px solid var(--color-border);
}

.invoice-item__description {
	flex: 1;
	font-weight: 500;
	color: var(--color-main-text);
}

.invoice-item__details {
	display: flex;
	align-items: center;
	gap: 12px;
	font-size: 14px;
	color: var(--color-text-maxcontrast);
}

.invoice-item__total {
	font-weight: 600;
	color: var(--color-main-text);
}

.detail-history-card {
	background-color: var(--color-background-dark);
	border-radius: var(--border-radius-element);
	padding: 20px;
	border: 1px solid var(--color-border);
}

.history-card-title {
	margin: 0 0 16px 0;
	font-size: 18px;
	font-weight: 600;
	color: var(--color-main-text);
}

.history-list {
	display: flex;
	flex-direction: column;
	gap: 12px;
}

.history-item {
	display: flex;
	align-items: center;
	gap: 12px;
	padding: 12px;
	background-color: var(--color-main-background);
	border-radius: var(--border-radius-element);
	border: 1px solid var(--color-border);
}

.history-date {
	display: flex;
	align-items: center;
	gap: 8px;
	font-size: 14px;
	color: var(--color-text-maxcontrast);
	min-width: 120px;
}

.history-content {
	flex: 1;
	display: flex;
	flex-direction: column;
	gap: 4px;
}

.history-detail {
	font-size: 13px;
	color: var(--color-text-maxcontrast);
}

.history-note {
	font-size: 12px;
	color: var(--color-text-maxcontrast);
	font-style: italic;
}

.empty-mini {
	padding: 20px;
	text-align: center;
	color: var(--color-text-maxcontrast);
	font-size: 14px;
}

.empty-state {
	padding: 20px;
	text-align: center;
	color: var(--color-text-maxcontrast);
	font-size: 14px;
}

.link-primary {
	color: var(--color-primary-element);
	text-decoration: none;
}

.link-primary:hover {
	text-decoration: underline;
}

.status-ok {
	color: var(--color-text-success);
}

.status-warning {
	color: var(--color-text-error);
}

.status-critical {
	color: var(--color-text-error);
}

.status-draft {
	background-color: var(--color-background-dark);
	color: var(--color-text-maxcontrast);
}

.status-sent {
	background-color: var(--color-primary-element-light);
	color: var(--color-primary-element-light-text);
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
	background-color: var(--color-background-dark);
	color: var(--color-text-maxcontrast);
	opacity: 0.6;
}

.button-vue--info {
	background-color: var(--color-primary-element);
	color: var(--color-primary-element-text);
}

.button-vue--info:hover:not(:disabled) {
	opacity: 0.9;
}

.button-vue--tertiary {
	background-color: transparent;
	color: var(--color-main-text);
	border: 1px solid var(--color-border);
}

.button-vue--tertiary:hover:not(:disabled) {
	background-color: var(--color-background-hover);
}
</style>
