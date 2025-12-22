<template>
	<div class="transactions-view">
		<!-- Transaction Modal -->
		<TransactionModal
			:open="modalOpen"
			:transaction="editingTransaction"
			:clients="clients"
			:projects="projects"
			:categories="categories"
			@close="closeModal"
			@saved="handleTransactionSaved"
		/>

		<!-- Transaction List View -->
		<div v-if="!selectedTransaction" class="transactions-list-view">
			<div class="domaincontrol-actions">
				<button class="button-vue button-vue--primary" @click="showAddModal">
					<MaterialIcon name="add" :size="20" />
					{{ translate('domaincontrol', 'Add Transaction') }}
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
						:class="{ 'button-vue--primary': currentFilter === 'income' }"
						@click="setFilter('income')"
					>
						{{ translate('domaincontrol', 'Income') }}
					</button>
					<button
						class="button-vue button-vue--secondary"
						:class="{ 'button-vue--primary': currentFilter === 'expense' }"
						@click="setFilter('expense')"
					>
						{{ translate('domaincontrol', 'Expense') }}
					</button>
				</div>
				<div class="transaction-search-wrapper">
					<input
						type="text"
						v-model="searchQuery"
						class="transaction-search-input"
						:placeholder="translate('domaincontrol', 'Search transactions...')"
						@input="filterTransactions"
					/>
				</div>
			</div>

			<!-- Empty State -->
			<div v-if="filteredTransactions.length === 0 && !loading" class="empty-content">
				<MaterialIcon name="accounting" :size="48" color="var(--color-text-maxcontrast)" class="empty-content__icon" />
				<p class="empty-content__text">
					{{ searchQuery ? translate('domaincontrol', 'No transactions found') : translate('domaincontrol', 'No transactions yet') }}
				</p>
				<button v-if="!searchQuery" class="button-vue button-vue--primary" @click="showAddModal">
					{{ translate('domaincontrol', 'Add First Transaction') }}
				</button>
			</div>

			<!-- Loading State -->
			<div v-if="loading" class="loading-content">
				<MaterialIcon name="loading" :size="32" class="loading-icon" />
				<p>{{ translate('domaincontrol', 'Loading transactions...') }}</p>
			</div>

			<!-- Transactions List -->
			<div v-else-if="filteredTransactions.length > 0" class="transactions-list">
				<div
					v-for="transaction in filteredTransactions"
					:key="transaction.id"
					class="list-item transaction-item"
					:class="{
						'transaction-item--income': transaction.type === 'income',
						'transaction-item--expense': transaction.type === 'expense',
					}"
					@click="selectTransaction(transaction)"
				>
					<div class="list-item__avatar" :class="`transaction-type--${transaction.type}`">
						<MaterialIcon :name="transaction.type === 'income' ? 'add' : 'delete'" :size="24" />
					</div>
					<div class="list-item__content">
						<div class="list-item__title">
							{{ transaction.description || translate('domaincontrol', 'Transaction') }}
						</div>
						<div class="list-item__meta">
							<span v-if="getCategoryName(transaction.categoryId)">
								{{ getCategoryName(transaction.categoryId) }}
							</span>
							<span v-if="getClientName(transaction.clientId)">
								{{ getClientName(transaction.clientId) }}
							</span>
							<span v-if="getProjectName(transaction.projectId)">
								{{ getProjectName(transaction.projectId) }}
							</span>
							<span v-if="transaction.transactionDate">
								{{ formatDate(transaction.transactionDate) }}
							</span>
						</div>
					</div>
					<div class="list-item__stats">
						<div class="list-item__stat">
							<div class="list-item__stat-label">{{ translate('domaincontrol', 'Amount') }}</div>
							<div class="list-item__stat-value" :class="`transaction-amount--${transaction.type}`">
								{{ transaction.type === 'income' ? '+' : '-' }}{{ formatCurrency(transaction.amount, transaction.currency) }}
							</div>
						</div>
						<div class="list-item__stat">
							<div class="list-item__stat-label">{{ translate('domaincontrol', 'Type') }}</div>
							<div class="list-item__stat-value">
								<span class="status-badge status-badge--simple" :class="getTransactionTypeClass(transaction)">
									{{ getTransactionTypeText(transaction.type) }}
								</span>
							</div>
						</div>
						<div class="list-item__stat">
							<div class="list-item__stat-label">{{ translate('domaincontrol', 'Payment Method') }}</div>
							<div class="list-item__stat-value">
								{{ getPaymentMethodText(transaction.paymentMethod) || '-' }}
							</div>
						</div>
					</div>
					<div class="list-item__actions">
						<div class="popover-menu-wrapper" @click.stop>
							<button
								class="action-button action-button--more"
								@click.stop="togglePopover(transaction.id)"
								:title="translate('domaincontrol', 'More options')"
							>
								<MaterialIcon name="more-vertical" :size="18" />
							</button>
							<div
								v-if="openPopover === transaction.id"
								class="popover-menu"
								@click.stop
							>
								<button
									class="popover-menu-item"
									@click="editTransaction(transaction); closePopover()"
								>
									<MaterialIcon name="edit" :size="16" />
									{{ translate('domaincontrol', 'Edit') }}
								</button>
								<div class="popover-menu-separator"></div>
								<button
									class="popover-menu-item popover-menu-item--danger"
									@click="confirmDelete(transaction); closePopover()"
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

		<!-- Transaction Detail View -->
		<div v-else class="transaction-detail-view">
			<div class="detail-header">
				<button class="button-vue button-vue--tertiary" @click="backToList">
					<MaterialIcon name="arrow-left" :size="20" />
					{{ translate('domaincontrol', 'Back') }}
				</button>
				<h2 class="detail-title">
					{{ selectedTransaction.description || translate('domaincontrol', 'Transaction') }}
				</h2>
				<div class="detail-actions">
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
								@click="editTransaction(selectedTransaction); closeDetailPopover()"
							>
								<MaterialIcon name="edit" :size="16" />
								{{ translate('domaincontrol', 'Edit') }}
							</button>
							<div class="popover-menu-separator"></div>
							<button
								class="popover-menu-item popover-menu-item--danger"
								@click="confirmDelete(selectedTransaction); closeDetailPopover()"
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
						<div class="stat-card__icon" :class="getTransactionTypeClass(selectedTransaction)">
							<MaterialIcon :name="selectedTransaction.type === 'income' ? 'add' : 'delete'" :size="24" />
						</div>
						<div class="stat-card__content">
							<div class="stat-card__label">{{ translate('domaincontrol', 'Type') }}</div>
							<div class="stat-card__value">
								<span class="status-badge" :class="getTransactionTypeClass(selectedTransaction)">
									{{ getTransactionTypeText(selectedTransaction.type) }}
								</span>
							</div>
						</div>
					</div>
					<div class="stat-card">
						<div class="stat-card__icon">
							<MaterialIcon name="settings" :size="24" />
						</div>
						<div class="stat-card__content">
							<div class="stat-card__label">{{ translate('domaincontrol', 'Category') }}</div>
							<div class="stat-card__value">
								{{ getCategoryName(selectedTransaction.categoryId) || '-' }}
							</div>
						</div>
					</div>
					<div class="stat-card">
						<div class="stat-card__icon" :class="`transaction-amount--${selectedTransaction.type}`">
							<MaterialIcon name="accounting" :size="24" />
						</div>
						<div class="stat-card__content">
							<div class="stat-card__label">{{ translate('domaincontrol', 'Amount') }}</div>
							<div class="stat-card__value" :class="`transaction-amount--${selectedTransaction.type}`">
								{{ selectedTransaction.type === 'income' ? '+' : '-' }}{{ formatCurrency(selectedTransaction.amount, selectedTransaction.currency) }}
							</div>
						</div>
					</div>
					<div class="stat-card">
						<div class="stat-card__icon">
							<MaterialIcon name="calendar" :size="24" />
						</div>
						<div class="stat-card__content">
							<div class="stat-card__label">{{ translate('domaincontrol', 'Date') }}</div>
							<div class="stat-card__value">
								{{ formatDate(selectedTransaction.transactionDate) || '-' }}
							</div>
						</div>
					</div>
					<div class="stat-card">
						<div class="stat-card__icon">
							<MaterialIcon name="settings" :size="24" />
						</div>
						<div class="stat-card__content">
							<div class="stat-card__label">{{ translate('domaincontrol', 'Payment Method') }}</div>
							<div class="stat-card__value">
								{{ getPaymentMethodText(selectedTransaction.paymentMethod) || '-' }}
							</div>
						</div>
					</div>
					<div class="stat-card">
						<div class="stat-card__icon">
							<MaterialIcon name="contacts" :size="24" />
						</div>
						<div class="stat-card__content">
							<div class="stat-card__label">{{ translate('domaincontrol', 'Client') }}</div>
							<div class="stat-card__value">
								<a
									v-if="selectedTransaction.clientId"
									href="#"
									@click.prevent="navigateToClient(selectedTransaction.clientId)"
									class="link-primary"
								>
									{{ getClientName(selectedTransaction.clientId) }}
								</a>
								<span v-else>-</span>
							</div>
						</div>
					</div>
				</div>

				<!-- Info Grid -->
				<div class="detail-info-grid">
					<div class="detail-info-card">
						<h3 class="info-card-title">{{ translate('domaincontrol', 'Description') }}</h3>
						<div class="detail-description">
							{{ selectedTransaction.description || translate('domaincontrol', 'No description') }}
						</div>
					</div>

					<div class="detail-info-card">
						<h3 class="info-card-title">{{ translate('domaincontrol', 'Project') }}</h3>
						<div class="detail-description">
							<a
								v-if="selectedTransaction.projectId"
								href="#"
								@click.prevent="navigateToProject(selectedTransaction.projectId)"
								class="link-primary"
							>
								{{ getProjectName(selectedTransaction.projectId) }}
							</a>
							<span v-else>{{ translate('domaincontrol', 'No project') }}</span>
						</div>
					</div>
				</div>

				<div class="detail-info-card">
					<h3 class="info-card-title">{{ translate('domaincontrol', 'Notes') }}</h3>
					<div class="detail-notes" v-html="selectedTransaction.notes || translate('domaincontrol', 'No notes')"></div>
				</div>

				<div v-if="selectedTransaction.reference" class="detail-info-card">
					<h3 class="info-card-title">{{ translate('domaincontrol', 'Reference / Invoice Number') }}</h3>
					<div class="detail-description">
						{{ selectedTransaction.reference }}
					</div>
				</div>
			</div>
		</div>
	</div>
</template>

<script>
import api from '../services/api'
import MaterialIcon from './MaterialIcon.vue'
import TransactionModal from './TransactionModal.vue'

export default {
	name: 'Transactions',
	components: {
		MaterialIcon,
		TransactionModal,
	},
	data() {
		return {
			transactions: [],
			clients: [],
			projects: [],
			categories: [],
			selectedTransaction: null,
			loading: false,
			modalOpen: false,
			editingTransaction: null,
			currentFilter: 'all',
			searchQuery: '',
			openPopover: null,
			detailPopoverOpen: false,
		}
	},
	computed: {
		filteredTransactions() {
			let filtered = this.transactions

			// Apply type filter
			if (this.currentFilter !== 'all') {
				filtered = filtered.filter(t => t.type === this.currentFilter)
			}

			// Apply search query
			if (this.searchQuery) {
				const query = this.searchQuery.toLowerCase()
				filtered = filtered.filter(transaction => {
					const description = (transaction.description || '').toLowerCase()
					const categoryName = this.getCategoryName(transaction.categoryId) || ''
					const clientName = this.getClientName(transaction.clientId) || ''
					return (
						description.includes(query) ||
						categoryName.toLowerCase().includes(query) ||
						clientName.toLowerCase().includes(query)
					)
				})
			}

			return filtered
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
					this.loadTransactions(),
					this.loadClients(),
					this.loadProjects(),
					this.loadCategories(),
				])
			} catch (error) {
				console.error('Error loading data:', error)
			} finally {
				this.loading = false
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
		async loadClients() {
			try {
				const response = await api.clients.getAll()
				this.clients = response.data || []
			} catch (error) {
				console.error('Error loading clients:', error)
				this.clients = []
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
		async loadCategories() {
			try {
				const response = await api.transactionCategories.getAll()
				this.categories = response.data || []
			} catch (error) {
				console.error('Error loading categories:', error)
				this.categories = []
			}
		},
		filterTransactions() {
			// Computed property handles filtering
		},
		setFilter(filter) {
			this.currentFilter = filter
		},
		selectTransaction(transaction) {
			this.selectedTransaction = transaction
		},
		backToList() {
			this.selectedTransaction = null
		},
		showAddModal() {
			this.editingTransaction = null
			this.modalOpen = true
		},
		editTransaction(transaction) {
			this.editingTransaction = transaction
			this.modalOpen = true
		},
		closeModal() {
			this.modalOpen = false
			this.editingTransaction = null
		},
		async handleTransactionSaved() {
			await this.loadTransactions()
			if (this.selectedTransaction) {
				const response = await api.transactions.get(this.selectedTransaction.id)
				this.selectedTransaction = response.data
			}
			this.closeModal()
		},
		async confirmDelete(transaction) {
			if (!confirm(this.translate('domaincontrol', 'Are you sure you want to delete this transaction?'))) {
				return
			}
			try {
				await api.transactions.delete(transaction.id)
				await this.loadTransactions()
				if (this.selectedTransaction && this.selectedTransaction.id === transaction.id) {
					this.backToList()
				}
			} catch (error) {
				console.error('Error deleting transaction:', error)
				alert(this.translate('domaincontrol', 'Error deleting transaction'))
			}
		},
		getClientName(clientId) {
			if (!clientId) return ''
			const client = this.clients.find(c => c.id === clientId)
			return client ? client.name : ''
		},
		getProjectName(projectId) {
			if (!projectId) return ''
			const project = this.projects.find(p => p.id === projectId)
			return project ? project.name : ''
		},
		getCategoryName(categoryId) {
			if (!categoryId) return ''
			const category = this.categories.find(c => c.id === categoryId)
			return category ? category.name : ''
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
		navigateToProject(projectId) {
			if (typeof window.DomainControl !== 'undefined' && window.DomainControl.switchTab) {
				window.DomainControl.switchTab('projects')
				setTimeout(() => {
					const event = new CustomEvent('select-project', { detail: { projectId } })
					window.dispatchEvent(event)
				}, 100)
			}
		},
		getTransactionTypeClass(transaction) {
			return `transaction-type--${transaction.type}`
		},
		getTransactionTypeText(type) {
			const types = {
				income: this.translate('domaincontrol', 'Income'),
				expense: this.translate('domaincontrol', 'Expense'),
			}
			return types[type] || type
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
		togglePopover(transactionId) {
			this.openPopover = this.openPopover === transactionId ? null : transactionId
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
				'Add Transaction': 'İşlem Ekle',
				'Search transactions...': 'İşlemlerde ara...',
				'No transactions found': 'İşlem bulunamadı',
				'No transactions yet': 'Henüz işlem yok',
				'Add First Transaction': 'İlk İşlemi Ekle',
				'Loading transactions...': 'İşlemler yükleniyor...',
				'All': 'Tümü',
				'Income': 'Gelir',
				'Expense': 'Gider',
				'Amount': 'Tutar',
				'Type': 'Tür',
				'Payment Method': 'Ödeme Yöntemi',
				'More options': 'Daha fazla seçenek',
				'Edit': 'Düzenle',
				'Delete': 'Sil',
				'Back': 'Geri',
				'Transaction': 'İşlem',
				'Category': 'Kategori',
				'Date': 'Tarih',
				'Client': 'Müşteri',
				'Description': 'Açıklama',
				'No description': 'Açıklama yok',
				'Project': 'Proje',
				'No project': 'Proje yok',
				'Notes': 'Notlar',
				'No notes': 'Not yok',
				'Reference / Invoice Number': 'Referans / Fatura No',
				'Cash': 'Nakit',
				'Bank Transfer': 'Banka Transferi',
				'Credit Card': 'Kredi Kartı',
				'Debit Card': 'Banka Kartı',
				'Online Payment': 'Online Ödeme',
				'Other': 'Diğer',
				'Are you sure you want to delete this transaction?': 'Bu işlemi silmek istediğinize emin misiniz?',
				'Error deleting transaction': 'İşlem silinirken hata oluştu',
			}

			return translations[text] || text
		},
	},
}
</script>

<style scoped>
.transactions-view {
	width: 100%;
	height: 100%;
}

.transactions-list-view {
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

.transaction-search-wrapper {
	flex: 1;
	min-width: 200px;
}

.transaction-search-input {
	width: 100%;
	padding: 8px 12px;
	border: 1px solid var(--color-border);
	border-radius: var(--border-radius-element);
	background-color: var(--color-background-dark);
	color: var(--color-main-text);
	font-size: 14px;
}

.transaction-search-input:focus {
	outline: none;
	border-color: var(--color-primary-element);
}

.transactions-list {
	display: grid;
	gap: 12px;
}

.transaction-item {
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

.transaction-item:hover {
	background-color: var(--color-background-hover);
}

.transaction-item .list-item__avatar {
	width: 48px;
	height: 48px;
	border-radius: 50%;
	display: flex;
	align-items: center;
	justify-content: center;
	flex-shrink: 0;
}

.transaction-type--income {
	background-color: var(--color-success);
	color: var(--color-success-text);
}

.transaction-type--expense {
	background-color: var(--color-error);
	color: var(--color-error-text);
}

.transaction-item .list-item__content {
	flex: 1;
	min-width: 0;
}

.transaction-item .list-item__title {
	font-size: 16px;
	font-weight: 500;
	color: var(--color-main-text);
	margin-bottom: 4px;
}

.transaction-item .list-item__meta {
	display: flex;
	align-items: center;
	gap: 12px;
	font-size: 13px;
	color: var(--color-text-maxcontrast);
	flex-wrap: wrap;
}

.transaction-item .list-item__stats {
	display: flex;
	gap: 24px;
	align-items: center;
}

.transaction-item .list-item__stat {
	display: flex;
	flex-direction: column;
	gap: 4px;
	min-width: 100px;
}

.transaction-item .list-item__stat-label {
	font-size: 12px;
	color: var(--color-text-maxcontrast);
}

.transaction-item .list-item__stat-value {
	font-size: 14px;
	font-weight: 500;
	color: var(--color-main-text);
}

.transaction-amount--income {
	color: var(--color-success);
}

.transaction-amount--expense {
	color: var(--color-error);
}

.transaction-item .list-item__actions {
	display: flex;
	align-items: center;
	gap: 8px;
	flex-shrink: 0;
}

.transaction-detail-view {
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
