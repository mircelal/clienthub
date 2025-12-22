<template>
	<div class="clients-view">
		<!-- Client List View -->
		<div v-if="!selectedClient" class="clients-list-view">
			<div class="domaincontrol-actions">
				<button class="button-vue button-vue--primary" @click="showAddModal">
					<span class="icon-add"></span>
					{{ translate('domaincontrol', 'Add Client') }}
				</button>
				<div class="client-search-wrapper">
					<input
						type="text"
						v-model="searchQuery"
						class="client-search-input"
						:placeholder="translate('domaincontrol', 'Search clients...')"
						@input="filterClients"
					/>
				</div>
			</div>

			<!-- Empty State -->
			<div v-if="filteredClients.length === 0 && !loading" class="empty-content">
				<span class="icon-contacts empty-content__icon" />
				<p class="empty-content__text">
					{{ searchQuery ? translate('domaincontrol', 'No clients found') : translate('domaincontrol', 'No clients yet') }}
				</p>
				<button v-if="!searchQuery" class="button-vue button-vue--primary" @click="showAddModal">
					{{ translate('domaincontrol', 'Add First Client') }}
				</button>
			</div>

			<!-- Loading State -->
			<div v-if="loading" class="loading-content">
				<span class="icon-loading"></span>
				<p>{{ translate('domaincontrol', 'Loading clients...') }}</p>
			</div>

			<!-- Clients List -->
			<div v-else-if="filteredClients.length > 0" class="clients-list">
				<div
					v-for="client in filteredClients"
					:key="client.id"
					class="list-item client-item"
					@click="selectClient(client)"
				>
					<div class="list-item__avatar">
						<div class="avatar" :style="{ backgroundColor: getAvatarColor(client.name) }">
							{{ getInitials(client.name) }}
						</div>
					</div>
					<div class="list-item__content">
						<div class="list-item__title">{{ client.name }}</div>
						<div v-if="client.email" class="list-item__subtitle">{{ client.email }}</div>
						<div v-if="client.phone" class="list-item__meta">{{ client.phone }}</div>
					</div>
					<div class="list-item__stats">
						<div class="client-stat-badge">
							<span class="icon-public"></span>
							{{ getClientDomainCount(client.id) }}
						</div>
						<div class="client-stat-badge">
							<span class="icon-category-office"></span>
							{{ getClientHostingCount(client.id) }}
						</div>
						<div class="client-stat-badge">
							<span class="icon-link"></span>
							{{ getClientWebsiteCount(client.id) }}
						</div>
					</div>
					<div class="list-item__actions">
						<button
							class="icon-edit action-button"
							@click.stop="editClient(client.id)"
							:title="translate('domaincontrol', 'Edit')"
						></button>
						<button
							class="icon-delete action-button"
							@click.stop="confirmDelete(client)"
							:title="translate('domaincontrol', 'Delete')"
						></button>
					</div>
				</div>
			</div>
		</div>

		<!-- Client Detail View -->
		<div v-else class="client-detail-view">
			<div class="detail-header">
				<button class="button-vue button-vue--tertiary" @click="backToList">
					<span class="icon-arrow-left"></span>
					{{ translate('domaincontrol', 'Back') }}
				</button>
				<h2 class="detail-title">{{ selectedClient.name }}</h2>
				<div class="detail-actions">
					<button class="button-vue button-vue--secondary" @click="editClient(selectedClient.id)">
						{{ translate('domaincontrol', 'Edit') }}
					</button>
					<button class="button-vue button-vue--danger" @click="confirmDelete(selectedClient)">
						{{ translate('domaincontrol', 'Delete') }}
					</button>
				</div>
			</div>

			<div class="detail-content">
				<!-- Stats Cards -->
				<div class="detail-stats client-stats">
					<div class="stat-card client-stat-card">
						<div class="stat-card__icon client-stat-icon" style="background: rgba(59, 130, 246, 0.08);">
							<span class="icon-public"></span>
						</div>
						<div class="stat-card__content">
							<div class="stat-card__label">{{ translate('domaincontrol', 'Domains') }}</div>
							<div class="stat-card__value">{{ getClientDomainCount(selectedClient.id) }}</div>
						</div>
					</div>
					<div class="stat-card client-stat-card">
						<div class="stat-card__icon client-stat-icon" style="background: rgba(16, 185, 129, 0.08);">
							<span class="icon-category-office"></span>
						</div>
						<div class="stat-card__content">
							<div class="stat-card__label">{{ translate('domaincontrol', 'Hosting') }}</div>
							<div class="stat-card__value">{{ getClientHostingCount(selectedClient.id) }}</div>
						</div>
					</div>
					<div class="stat-card client-stat-card">
						<div class="stat-card__icon client-stat-icon" style="background: rgba(139, 92, 246, 0.08);">
							<span class="icon-link"></span>
						</div>
						<div class="stat-card__content">
							<div class="stat-card__label">{{ translate('domaincontrol', 'Websites') }}</div>
							<div class="stat-card__value">{{ getClientWebsiteCount(selectedClient.id) }}</div>
						</div>
					</div>
				</div>

				<!-- Info Grid -->
				<div class="detail-info-grid">
					<div class="detail-info-card client-info-card">
						<h3 class="client-info-title">{{ translate('domaincontrol', 'Contact Information') }}</h3>
						<table class="detail-table client-detail-table">
							<tr v-if="selectedClient.email">
								<td class="table-label">{{ translate('domaincontrol', 'Email') }}</td>
								<td class="table-value">
									<a :href="`mailto:${selectedClient.email}`" class="link-primary">{{ selectedClient.email }}</a>
								</td>
							</tr>
							<tr v-if="selectedClient.phone">
								<td class="table-label">{{ translate('domaincontrol', 'Phone') }}</td>
								<td class="table-value">
									<a :href="`tel:${selectedClient.phone}`" class="link-primary">{{ selectedClient.phone }}</a>
								</td>
							</tr>
							<tr v-if="selectedClient.createdAt">
								<td class="table-label">{{ translate('domaincontrol', 'Created Date') }}</td>
								<td class="table-value">{{ formatDate(selectedClient.createdAt) }}</td>
							</tr>
						</table>
					</div>

					<div class="detail-info-card client-info-card">
						<h3 class="client-info-title">{{ translate('domaincontrol', 'Notes') }}</h3>
						<div class="detail-notes client-notes">
							{{ selectedClient.notes || translate('domaincontrol', 'No notes') }}
						</div>
					</div>
				</div>

				<!-- Related Items Grid -->
				<div class="detail-services client-services-grid">
					<!-- Domains -->
					<div class="detail-service-card client-service-card">
						<div class="service-card-header">
							<h3 class="service-card-title">
								<span class="icon-public"></span>
								{{ translate('domaincontrol', 'Domains') }}
							</h3>
							<span class="service-card-count">{{ getClientDomainCount(selectedClient.id) }}</span>
						</div>
						<div class="mini-list client-mini-list">
							<div v-if="getClientDomains(selectedClient.id).length === 0" class="empty-mini">
								{{ translate('domaincontrol', 'No domains') }}
							</div>
							<div
								v-for="domain in getClientDomains(selectedClient.id)"
								:key="domain.id"
								class="mini-item"
								:class="getDomainStatusClass(domain)"
								@click="navigateToDomain(domain.id)"
							>
								<span>{{ domain.domainName }}</span>
								<span>{{ formatDate(domain.expirationDate) }}</span>
							</div>
						</div>
					</div>

					<!-- Hostings -->
					<div class="detail-service-card client-service-card">
						<div class="service-card-header">
							<h3 class="service-card-title">
								<span class="icon-category-office"></span>
								{{ translate('domaincontrol', 'Hosting') }}
							</h3>
							<span class="service-card-count">{{ getClientHostingCount(selectedClient.id) }}</span>
						</div>
						<div class="mini-list client-mini-list">
							<div v-if="getClientHostings(selectedClient.id).length === 0" class="empty-mini">
								{{ translate('domaincontrol', 'No hostings') }}
							</div>
							<div
								v-for="hosting in getClientHostings(selectedClient.id)"
								:key="hosting.id"
								class="mini-item"
								:class="getHostingStatusClass(hosting)"
								@click="navigateToHosting(hosting.id)"
							>
								<span>{{ hosting.provider || 'N/A' }}</span>
								<span>{{ formatDate(hosting.expirationDate) }}</span>
							</div>
						</div>
					</div>

					<!-- Websites -->
					<div class="detail-service-card client-service-card">
						<div class="service-card-header">
							<h3 class="service-card-title">
								<span class="icon-link"></span>
								{{ translate('domaincontrol', 'Websites') }}
							</h3>
							<span class="service-card-count">{{ getClientWebsiteCount(selectedClient.id) }}</span>
						</div>
						<div class="mini-list client-mini-list">
							<div v-if="getClientWebsites(selectedClient.id).length === 0" class="empty-mini">
								{{ translate('domaincontrol', 'No websites') }}
							</div>
							<div
								v-for="website in getClientWebsites(selectedClient.id)"
								:key="website.id"
								class="mini-item"
								@click="navigateToWebsite(website.id)"
							>
								<span>{{ website.name || 'N/A' }}</span>
								<span>{{ website.url || '-' }}</span>
							</div>
						</div>
					</div>

					<!-- Services -->
					<div class="detail-service-card client-service-card">
						<div class="service-card-header">
							<h3 class="service-card-title">
								<span class="icon-settings"></span>
								{{ translate('domaincontrol', 'Services') }}
							</h3>
							<span class="service-card-count">{{ getClientServiceCount(selectedClient.id) }}</span>
						</div>
						<div class="mini-list client-mini-list">
							<div v-if="getClientServices(selectedClient.id).length === 0" class="empty-mini">
								{{ translate('domaincontrol', 'No services') }}
							</div>
							<div
								v-for="service in getClientServices(selectedClient.id)"
								:key="service.id"
								class="mini-item"
								:class="service.status === 'active' ? 'status-ok' : 'status-warning'"
								@click="navigateToService(service.id)"
							>
								<span>{{ service.name }}</span>
								<span>{{ formatCurrency(service.price) }} {{ service.currency }}</span>
							</div>
						</div>
					</div>

					<!-- Invoices -->
					<div class="detail-service-card client-service-card">
						<div class="service-card-header">
							<h3 class="service-card-title">
								<span class="icon-files"></span>
								{{ translate('domaincontrol', 'Invoices') }}
							</h3>
							<span class="service-card-count">{{ getClientInvoiceCount(selectedClient.id) }}</span>
						</div>
						<div class="mini-list client-mini-list">
							<div v-if="getClientInvoices(selectedClient.id).length === 0" class="empty-mini">
								{{ translate('domaincontrol', 'No invoices') }}
							</div>
							<div
								v-for="invoice in getClientInvoices(selectedClient.id)"
								:key="invoice.id"
								class="mini-item"
								:class="getInvoiceStatusClass(invoice)"
								@click="navigateToInvoice(invoice.id)"
							>
								<span>{{ invoice.invoiceNumber || `#${invoice.id}` }}</span>
								<span>{{ formatCurrency(invoice.totalAmount) }} {{ invoice.currency }}</span>
							</div>
						</div>
					</div>

					<!-- Payments -->
					<div class="detail-service-card client-service-card">
						<div class="service-card-header">
							<h3 class="service-card-title">
								<span class="icon-files"></span>
								{{ translate('domaincontrol', 'Payments') }}
							</h3>
							<span class="service-card-count">{{ getClientPaymentCount(selectedClient.id) }}</span>
						</div>
						<div class="mini-list client-mini-list">
							<div v-if="getClientPayments(selectedClient.id).length === 0" class="empty-mini">
								{{ translate('domaincontrol', 'No payments') }}
							</div>
							<div
								v-for="payment in getClientPayments(selectedClient.id).slice(0, 5)"
								:key="payment.id"
								class="mini-item status-ok"
							>
								<span>{{ formatDate(payment.paymentDate) }}</span>
								<span>{{ formatCurrency(payment.amount) }} {{ payment.currency }}</span>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</template>

<script>
import api from '../services/api'

export default {
	name: 'Clients',
	data() {
		return {
			clients: [],
			domains: [],
			hostings: [],
			websites: [],
			services: [],
			invoices: [],
			payments: [],
			selectedClient: null,
			searchQuery: '',
			loading: false,
		}
	},
	computed: {
		filteredClients() {
			if (!this.searchQuery) {
				return this.clients
			}
			const query = this.searchQuery.toLowerCase()
			return this.clients.filter(client => {
				return (
					client.name?.toLowerCase().includes(query) ||
					client.email?.toLowerCase().includes(query) ||
					client.phone?.toLowerCase().includes(query)
				)
			})
		},
	},
	mounted() {
		this.loadClients()
		this.loadRelatedData()
	},
	methods: {
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
				'Add Client': 'Müşteri Ekle',
				'Search clients...': 'Müşteri ara...',
				'No clients found': 'Müşteri bulunamadı',
				'No clients yet': 'Henüz müşteri eklenmemiş',
				'Add First Client': 'İlk Müşteriyi Ekle',
				'Loading clients...': 'Müşteriler yükleniyor...',
				'Back': 'Geri',
				'Edit': 'Düzenle',
				'Delete': 'Sil',
				'Contact Information': 'İletişim Bilgileri',
				'Email': 'E-posta',
				'Phone': 'Telefon',
				'Created Date': 'Kayıt Tarihi',
				'Notes': 'Notlar',
				'No notes': 'Not bulunmuyor',
			}

			return translations[text] || text
		},
		async loadClients() {
			this.loading = true
			try {
				const response = await api.clients.getAll()
				this.clients = response.data || []
				console.log('Clients loaded:', this.clients.length)
			} catch (error) {
				console.error('Error loading clients:', error)
				this.clients = []
			} finally {
				this.loading = false
			}
		},
		async loadRelatedData() {
			try {
				const [domainsRes, hostingsRes, websitesRes, servicesRes, invoicesRes, paymentsRes] = await Promise.all([
					api.domains.getAll().catch(() => ({ data: [] })),
					api.hostings.getAll().catch(() => ({ data: [] })),
					api.websites.getAll().catch(() => ({ data: [] })),
					api.services.getAll().catch(() => ({ data: [] })),
					api.invoices.getAll().catch(() => ({ data: [] })),
					api.payments.getAll().catch(() => ({ data: [] })),
				])
				this.domains = domainsRes.data || []
				this.hostings = hostingsRes.data || []
				this.websites = websitesRes.data || []
				this.services = servicesRes.data || []
				this.invoices = invoicesRes.data || []
				this.payments = paymentsRes.data || []
			} catch (error) {
				console.error('Error loading related data:', error)
			}
		},
		filterClients() {
			// Filtering is handled by computed property
		},
		selectClient(client) {
			this.selectedClient = client
		},
		backToList() {
			this.selectedClient = null
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
		getClientDomainCount(clientId) {
			return (this.domains || []).filter(d => d.clientId == clientId).length
		},
		getClientHostingCount(clientId) {
			return (this.hostings || []).filter(h => h.clientId == clientId).length
		},
		getClientWebsiteCount(clientId) {
			return (this.websites || []).filter(w => w.clientId == clientId).length
		},
		getClientServiceCount(clientId) {
			return (this.services || []).filter(s => s.clientId == clientId).length
		},
		getClientInvoiceCount(clientId) {
			return (this.invoices || []).filter(i => i.clientId == clientId).length
		},
		getClientPaymentCount(clientId) {
			return (this.payments || []).filter(p => p.clientId == clientId).length
		},
		getClientDomains(clientId) {
			return (this.domains || []).filter(d => d.clientId == clientId)
		},
		getClientHostings(clientId) {
			return (this.hostings || []).filter(h => h.clientId == clientId)
		},
		getClientWebsites(clientId) {
			return (this.websites || []).filter(w => w.clientId == clientId)
		},
		getClientServices(clientId) {
			return (this.services || []).filter(s => s.clientId == clientId)
		},
		getClientInvoices(clientId) {
			return (this.invoices || []).filter(i => i.clientId == clientId)
		},
		getClientPayments(clientId) {
			return (this.payments || []).filter(p => p.clientId == clientId)
		},
		getDomainStatusClass(domain) {
			if (!domain.expirationDate) return 'status-ok'
			const daysLeft = this.getDaysUntilExpiry(domain.expirationDate)
			if (daysLeft <= 7) return 'status-critical'
			if (daysLeft <= 30) return 'status-warning'
			return 'status-ok'
		},
		getHostingStatusClass(hosting) {
			if (!hosting.expirationDate) return 'status-ok'
			const daysLeft = this.getDaysUntilExpiry(hosting.expirationDate)
			if (daysLeft <= 7) return 'status-critical'
			if (daysLeft <= 30) return 'status-warning'
			return 'status-ok'
		},
		getInvoiceStatusClass(invoice) {
			if (invoice.status === 'paid') return 'status-ok'
			if (invoice.status === 'overdue') return 'status-critical'
			return 'status-warning'
		},
		getDaysUntilExpiry(dateString) {
			if (!dateString) return Infinity
			try {
				const expiryDate = new Date(dateString)
				const today = new Date()
				today.setHours(0, 0, 0, 0)
				expiryDate.setHours(0, 0, 0, 0)
				const diffTime = expiryDate - today
				const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24))
				return diffDays
			} catch (e) {
				return Infinity
			}
		},
		formatDate(dateString) {
			if (!dateString) return '-'
			try {
				const date = new Date(dateString)
				return new Intl.DateTimeFormat('tr-TR', {
					day: 'numeric',
					month: 'short',
					year: 'numeric',
				}).format(date)
			} catch (e) {
				return dateString
			}
		},
		formatCurrency(amount) {
			if (typeof amount !== 'number') return '0'
			return new Intl.NumberFormat('tr-TR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(amount)
		},
		navigateToDomain(id) {
			if (typeof window.DomainControl !== 'undefined' && window.DomainControl.switchTab) {
				this.backToList()
				window.DomainControl.switchTab('domains')
				setTimeout(() => {
					if (window.DomainControl.showDomainDetail) {
						window.DomainControl.showDomainDetail(id)
					}
				}, 100)
			}
		},
		navigateToHosting(id) {
			if (typeof window.DomainControl !== 'undefined' && window.DomainControl.switchTab) {
				this.backToList()
				window.DomainControl.switchTab('hostings')
				setTimeout(() => {
					if (window.DomainControl.showHostingDetail) {
						window.DomainControl.showHostingDetail(id)
					}
				}, 100)
			}
		},
		navigateToWebsite(id) {
			if (typeof window.DomainControl !== 'undefined' && window.DomainControl.switchTab) {
				this.backToList()
				window.DomainControl.switchTab('websites')
				setTimeout(() => {
					if (window.DomainControl.showWebsiteDetail) {
						window.DomainControl.showWebsiteDetail(id)
					}
				}, 100)
			}
		},
		navigateToService(id) {
			if (typeof window.DomainControl !== 'undefined' && window.DomainControl.switchTab) {
				this.backToList()
				window.DomainControl.switchTab('services')
				setTimeout(() => {
					if (window.DomainControl.showServiceDetail) {
						window.DomainControl.showServiceDetail(id)
					}
				}, 100)
			}
		},
		navigateToInvoice(id) {
			if (typeof window.DomainControl !== 'undefined' && window.DomainControl.switchTab) {
				this.backToList()
				window.DomainControl.switchTab('invoices')
				setTimeout(() => {
					if (window.DomainControl.showInvoiceDetail) {
						window.DomainControl.showInvoiceDetail(id)
					}
				}, 100)
			}
		},
		showAddModal() {
			if (typeof window.DomainControl !== 'undefined' && window.DomainControl.showClientModal) {
				window.DomainControl.showClientModal()
			}
		},
		editClient(id) {
			if (typeof window.DomainControl !== 'undefined' && window.DomainControl.showClientModal) {
				window.DomainControl.showClientModal(id)
			}
		},
		confirmDelete(client) {
			const clientObj = typeof client === 'object' ? client : this.clients.find(c => c.id === client)
			if (!clientObj) return

			if (confirm(this.translate('domaincontrol', 'Are you sure you want to delete {name}?', { name: clientObj.name }))) {
				this.deleteClient(clientObj.id)
			}
		},
		async deleteClient(id) {
			try {
				await api.clients.delete(id)
				this.clients = this.clients.filter(c => c.id !== id)
				if (this.selectedClient && this.selectedClient.id === id) {
					this.selectedClient = null
				}
				// Reload related data to update counts
				this.loadRelatedData()
				// Refresh global DomainControl data
				if (typeof window.DomainControl !== 'undefined' && window.DomainControl.loadClients) {
					window.DomainControl.loadClients()
				}
			} catch (error) {
				console.error('Error deleting client:', error)
				alert(this.translate('domaincontrol', 'Error deleting client'))
			}
		},
	},
	watch: {
		// Watch for client updates from global DomainControl
		'$data': {
			handler() {
				// This will be called when data changes
			},
			deep: true,
		},
	},
}
</script>

<style scoped>
.clients-view {
	padding: 20px;
}

.domaincontrol-actions {
	margin-bottom: 24px;
	display: flex;
	gap: 12px;
	align-items: center;
	flex-wrap: wrap;
}

.client-search-wrapper {
	flex: 1;
	min-width: 200px;
	max-width: 400px;
	position: relative;
}

.client-search-input {
	width: 100%;
	padding: 8px 12px;
	border: 1px solid var(--color-border);
	border-radius: var(--border-radius-element);
	background-color: var(--color-background-dark);
	color: var(--color-main-text);
	font-size: 14px;
}

.client-search-input:focus {
	outline: none;
	border-color: var(--color-primary);
}

.clients-list {
	display: flex;
	flex-direction: column;
	gap: 8px;
}

.client-item {
	display: flex;
	align-items: center;
	padding: 12px;
	background-color: var(--color-background-dark);
	border: 1px solid var(--color-border);
	border-radius: var(--border-radius-element);
	cursor: pointer;
	transition: all 0.2s ease;
}

.client-item:hover {
	background-color: var(--color-background-hover);
	border-color: var(--color-primary);
	transform: translateY(-1px);
	box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.list-item__avatar {
	margin-right: 12px;
}

.list-item__content {
	flex: 1;
	min-width: 0;
}

.list-item__title {
	font-weight: 600;
	font-size: 16px;
	color: var(--color-main-text);
	margin-bottom: 4px;
}

.list-item__subtitle {
	font-size: 14px;
	color: var(--color-text-maxcontrast);
	margin-bottom: 2px;
}

.list-item__meta {
	font-size: 12px;
	color: var(--color-text-maxcontrast);
}

.list-item__stats {
	display: flex;
	gap: 8px;
	margin-right: 12px;
}

.client-stat-badge {
	display: flex;
	align-items: center;
	gap: 4px;
	padding: 4px 8px;
	background-color: var(--color-background-hover);
	border-radius: var(--border-radius-small);
	font-size: 12px;
	color: var(--color-text-maxcontrast);
}

.list-item__actions {
	display: flex;
	gap: 4px;
}

.action-button {
	width: 32px;
	height: 32px;
	border: none;
	background: transparent;
	border-radius: var(--border-radius-small);
	cursor: pointer;
	color: var(--color-text-maxcontrast);
	transition: all 0.2s ease;
}

.action-button:hover {
	background-color: var(--color-background-hover);
	color: var(--color-main-text);
}

.client-detail-view {
	padding: 20px;
}

.detail-header {
	display: flex;
	align-items: center;
	gap: 16px;
	margin-bottom: 24px;
	padding-bottom: 16px;
	border-bottom: 1px solid var(--color-border);
}

.detail-title {
	flex: 1;
	font-size: 24px;
	font-weight: bold;
	color: var(--color-main-text);
	margin: 0;
}

.detail-actions {
	display: flex;
	gap: 8px;
}

.detail-stats {
	display: grid;
	grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
	gap: 16px;
	margin-bottom: 24px;
}

.client-stat-card {
	display: flex;
	align-items: center;
	padding: 16px;
	background-color: var(--color-background-dark);
	border-radius: var(--border-radius-element);
	border: 1px solid var(--color-border);
}

.stat-card__icon {
	width: 48px;
	height: 48px;
	border-radius: var(--border-radius-element);
	display: flex;
	align-items: center;
	justify-content: center;
	margin-right: 12px;
	font-size: 24px;
}

.stat-card__content {
	flex: 1;
}

.stat-card__label {
	font-size: 12px;
	color: var(--color-text-maxcontrast);
	margin-bottom: 4px;
}

.stat-card__value {
	font-size: 20px;
	font-weight: bold;
	color: var(--color-main-text);
}

.detail-info-grid {
	display: grid;
	grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
	gap: 16px;
	margin-bottom: 24px;
}

.client-info-card {
	padding: 16px;
	background-color: var(--color-background-dark);
	border-radius: var(--border-radius-element);
	border: 1px solid var(--color-border);
}

.client-info-title {
	font-size: 16px;
	font-weight: 600;
	color: var(--color-main-text);
	margin-bottom: 12px;
}

.detail-table {
	width: 100%;
	border-collapse: collapse;
}

.detail-table tr {
	border-bottom: 1px solid var(--color-border);
}

.detail-table tr:last-child {
	border-bottom: none;
}

.table-label {
	padding: 8px 0;
	font-weight: 500;
	color: var(--color-text-maxcontrast);
	width: 40%;
}

.table-value {
	padding: 8px 0;
	color: var(--color-main-text);
}

.detail-notes {
	padding: 12px;
	background-color: var(--color-background-hover);
	border-radius: var(--border-radius-small);
	color: var(--color-main-text);
	min-height: 60px;
	white-space: pre-wrap;
}

.loading-content {
	display: flex;
	flex-direction: column;
	align-items: center;
	justify-content: center;
	padding: 60px 20px;
	text-align: center;
}

.loading-content .icon-loading {
	font-size: 3em;
	color: var(--color-primary);
	animation: spin 1s linear infinite;
	margin-bottom: 16px;
}

@keyframes spin {
	from {
		transform: rotate(0deg);
	}
	to {
		transform: rotate(360deg);
	}
}

.empty-content {
	display: flex;
	flex-direction: column;
	align-items: center;
	justify-content: center;
	padding: 60px 20px;
	text-align: center;
}

.empty-content__icon {
	font-size: 4em;
	color: var(--color-text-maxcontrast);
	margin-bottom: 16px;
	opacity: 0.5;
}

.empty-content__text {
	color: var(--color-text-maxcontrast);
	font-size: 16px;
	margin-bottom: 16px;
}

@media screen and (max-width: 768px) {
	.detail-header {
		flex-direction: column;
		align-items: flex-start;
	}

	.detail-actions {
		width: 100%;
	}

	.detail-actions .button-vue {
		flex: 1;
	}
}
</style>

