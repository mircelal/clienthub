<template>
	<div class="domains-view">
		<!-- Domain Modal -->
		<DomainModal
			:open="modalOpen"
			:domain="editingDomain"
			:clients="clients"
			@close="closeModal"
			@saved="handleDomainSaved"
		/>

		<!-- Domain Extend Modal -->
		<DomainExtendModal
			:open="extendModalOpen"
			:domain="extendingDomain"
			@close="closeExtendModal"
			@extended="handleDomainExtended"
		/>

		<!-- Domain List View -->
		<div v-if="!selectedDomain" class="domains-list-view">
			<div class="domaincontrol-actions">
				<button class="button-vue button-vue--primary" @click="showAddModal">
					<MaterialIcon name="add" :size="20" />
					{{ translate('domaincontrol', 'Add Domain') }}
				</button>
				<button class="button-vue button-vue--secondary" @click="testEmail" title="Süresi yaklaşan domainler için test e-postası gönder">
					<MaterialIcon name="mail" :size="20" />
					{{ translate('domaincontrol', 'Test Email') }}
				</button>
			</div>

			<!-- Empty State -->
			<div v-if="filteredDomains.length === 0 && !loading" class="empty-content">
				<MaterialIcon name="public" :size="48" color="var(--color-text-maxcontrast)" class="empty-content__icon" />
				<p class="empty-content__text">
					{{ translate('domaincontrol', 'No domains yet') }}
				</p>
				<button class="button-vue button-vue--primary" @click="showAddModal">
					{{ translate('domaincontrol', 'Add First Domain') }}
				</button>
			</div>

			<!-- Loading State -->
			<div v-if="loading" class="loading-content">
				<span class="icon-loading"></span>
				<p>{{ translate('domaincontrol', 'Loading domains...') }}</p>
			</div>

			<!-- Domains List -->
			<div v-else-if="filteredDomains.length > 0" class="domains-list">
				<div
					v-for="domain in filteredDomains"
					:key="domain.id"
					class="list-item domain-item"
					:class="getDomainStatusClass(domain)"
					@click="selectDomain(domain)"
				>
					<div class="list-item__avatar">
						<MaterialIcon name="public" :size="24" />
					</div>
					<div class="list-item__content">
						<div class="list-item__title">{{ domain.domainName }}</div>
						<div class="list-item__meta">
							<span v-if="getClientName(domain.clientId)">
								{{ getClientName(domain.clientId) }}
							</span>
							<span v-if="domain.registrar">
								{{ domain.registrar }}
							</span>
						</div>
					</div>
					<div class="list-item__stats">
						<div class="list-item__stat">
							<div class="list-item__stat-label">{{ translate('domaincontrol', 'Expiry') }}</div>
							<div class="list-item__stat-value">{{ formatDate(domain.expirationDate) || '-' }}</div>
						</div>
						<div class="list-item__stat">
							<div class="list-item__stat-label">{{ translate('domaincontrol', 'Days Left') }}</div>
							<div class="list-item__stat-value">{{ getDaysUntilExpiry(domain.expirationDate) }}</div>
						</div>
						<div class="list-item__stat">
							<div class="list-item__stat-label">{{ translate('domaincontrol', 'Status') }}</div>
							<div class="list-item__stat-value">
								<span class="status-badge" :class="getDomainStatusClass(domain)">
									{{ getDomainStatusText(domain) }}
								</span>
							</div>
						</div>
					</div>
					<div class="list-item__actions">
						<button
							class="btn-extend extend-domain-btn"
							:data-id="domain.id"
							:title="translate('domaincontrol', 'Extend')"
							@click.stop="showExtendModal(domain)"
						>
							+{{ domain.renewalInterval || 1 }}Y
						</button>
						<button
							class="action-button action-button--edit"
							@click.stop="editDomain(domain.id)"
							:title="translate('domaincontrol', 'Edit')"
						>
							<MaterialIcon name="edit" :size="18" />
						</button>
						<button
							class="action-button action-button--delete"
							@click.stop="confirmDelete(domain)"
							:title="translate('domaincontrol', 'Delete')"
						>
							<MaterialIcon name="delete" :size="18" />
						</button>
					</div>
				</div>
			</div>
		</div>

		<!-- Domain Detail View -->
		<div v-else class="domain-detail-view">
			<div class="detail-header">
				<button class="button-vue button-vue--tertiary" @click="backToList">
					<MaterialIcon name="arrow-left" :size="20" />
					{{ translate('domaincontrol', 'Back') }}
				</button>
				<h2 class="detail-title">{{ selectedDomain.domainName }}</h2>
				<div class="detail-actions">
					<button class="button-vue button-vue--success" @click="showExtendModal(selectedDomain)">
						{{ translate('domaincontrol', 'Extend') }}
					</button>
					<button class="button-vue button-vue--secondary" @click="editDomain(selectedDomain.id)">
						{{ translate('domaincontrol', 'Edit') }}
					</button>
					<button class="button-vue button-vue--danger" @click="confirmDelete(selectedDomain)">
						{{ translate('domaincontrol', 'Delete') }}
					</button>
				</div>
			</div>

			<div class="detail-content">
				<!-- Stats Cards -->
				<div class="detail-stats">
					<div class="stat-card">
						<div class="stat-card__icon">
							<MaterialIcon name="calendar" :size="24" />
						</div>
						<div class="stat-card__content">
							<div class="stat-card__label">{{ translate('domaincontrol', 'Expiry Date') }}</div>
							<div class="stat-card__value">{{ formatDate(selectedDomain.expirationDate) || '-' }}</div>
						</div>
					</div>
					<div class="stat-card">
						<div class="stat-card__icon" :class="getDomainStatusClass(selectedDomain)">
							<MaterialIcon name="calendar" :size="24" />
						</div>
						<div class="stat-card__content">
							<div class="stat-card__label">{{ translate('domaincontrol', 'Days Left') }}</div>
							<div class="stat-card__value" :class="getDomainStatusClass(selectedDomain)">
								{{ getDaysUntilExpiry(selectedDomain.expirationDate) }}
							</div>
						</div>
					</div>
					<div class="stat-card">
						<div class="stat-card__icon">
							<MaterialIcon name="monitoring" :size="24" />
						</div>
						<div class="stat-card__content">
							<div class="stat-card__label">{{ translate('domaincontrol', 'Status') }}</div>
							<div class="stat-card__value">
								<span class="status-badge" :class="getDomainStatusClass(selectedDomain)">
									{{ getDomainStatusText(selectedDomain) }}
								</span>
							</div>
						</div>
					</div>
					<div class="stat-card">
						<div class="stat-card__icon">
							<MaterialIcon name="files" :size="24" />
						</div>
						<div class="stat-card__content">
							<div class="stat-card__label">{{ translate('domaincontrol', 'Price') }}</div>
							<div class="stat-card__value">
								{{ formatCurrency(selectedDomain.price, selectedDomain.currency) || '-' }}
							</div>
						</div>
					</div>
				</div>

				<!-- Info Grid -->
				<div class="detail-info-grid">
					<div class="detail-info-card">
						<h3>{{ translate('domaincontrol', 'General Information') }}</h3>
						<table class="detail-table">
							<tr>
								<td>{{ translate('domaincontrol', 'Client') }}</td>
								<td>{{ getClientName(selectedDomain.clientId) || '-' }}</td>
							</tr>
							<tr>
								<td>{{ translate('domaincontrol', 'Registrar') }}</td>
								<td>{{ selectedDomain.registrar || '-' }}</td>
							</tr>
							<tr>
								<td>{{ translate('domaincontrol', 'Registration Date') }}</td>
								<td>{{ formatDate(selectedDomain.registrationDate) || '-' }}</td>
							</tr>
							<tr>
								<td>{{ translate('domaincontrol', 'Interval') }}</td>
								<td>{{ (selectedDomain.renewalInterval || 1) }} {{ translate('domaincontrol', 'Year') }}</td>
							</tr>
							<tr>
								<td>{{ translate('domaincontrol', 'Hosting') }}</td>
								<td>
									<span v-if="getHostingName(selectedDomain.hostingId)">
										{{ getHostingName(selectedDomain.hostingId) }}
									</span>
									<span v-else>-</span>
								</td>
							</tr>
						</table>
					</div>

					<div class="detail-info-card">
						<h3>{{ translate('domaincontrol', 'Panel Access Information') }}</h3>
						<pre class="detail-notes">{{ selectedDomain.panelNotes || translate('domaincontrol', 'No panel information') }}</pre>
					</div>
				</div>

				<!-- Linked Websites -->
				<div class="detail-info-card">
					<h3>{{ translate('domaincontrol', 'Linked Websites') }}</h3>
					<div class="mini-list">
						<div v-if="getDomainWebsites(selectedDomain.id).length === 0" class="empty-mini">
							{{ translate('domaincontrol', 'No websites linked to this domain') }}
						</div>
						<div
							v-for="website in getDomainWebsites(selectedDomain.id)"
							:key="website.id"
							class="mini-item"
							@click="navigateToWebsite(website.id)"
						>
							<span>{{ website.name || website.software || 'N/A' }}</span>
						</div>
					</div>
				</div>

				<!-- Renewal History -->
				<div class="detail-history-card">
					<h3>{{ translate('domaincontrol', 'Renewal History') }}</h3>
					<div class="history-list">
						<div v-if="getRenewalHistory(selectedDomain).length === 0" class="empty-state">
							{{ translate('domaincontrol', 'No renewals yet') }}
						</div>
						<div
							v-for="(entry, index) in getRenewalHistory(selectedDomain)"
							:key="index"
							class="history-item"
						>
							<div class="history-date">{{ formatDate(entry.date) }}</div>
							<div class="history-content">
								<strong>{{ entry.years }} {{ translate('domaincontrol', 'year(s) extended') }}</strong>
								<span class="history-detail">{{ translate('domaincontrol', 'New expiry') }}: {{ formatDate(entry.newExpiry) }}</span>
								<span v-if="entry.price" class="history-detail">{{ translate('domaincontrol', 'Price') }}: {{ entry.price }}</span>
								<span v-if="entry.note" class="history-note">{{ entry.note }}</span>
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
import DomainModal from './DomainModal.vue'
import DomainExtendModal from './DomainExtendModal.vue'
import MaterialIcon from './MaterialIcon.vue'

export default {
	name: 'Domains',
	components: {
		DomainModal,
		DomainExtendModal,
		MaterialIcon,
	},
	data() {
		return {
			domains: [],
			clients: [],
			hostings: [],
			websites: [],
			selectedDomain: null,
			loading: false,
			modalOpen: false,
			extendModalOpen: false,
			editingDomain: null,
			extendingDomain: null,
		}
	},
	computed: {
		filteredDomains() {
			return this.domains
		},
	},
	mounted() {
		this.loadDomains()
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
				'Add Domain': 'Domain Ekle',
				'Test Email': 'E-posta Test Et',
				'No domains yet': 'Henüz domain eklenmemiş',
				'Add First Domain': 'İlk Domaini Ekle',
				'Loading domains...': 'Domainler yükleniyor...',
				'Back': 'Geri',
				'Edit': 'Düzenle',
				'Delete': 'Sil',
				'Extend': 'Süreyi Uzat',
				'Expiry': 'Bitiş',
				'Days Left': 'Kalan Gün',
				'Status': 'Durum',
				'Expiry Date': 'Bitiş Tarihi',
				'General Information': 'Genel Bilgiler',
				'Client': 'Müşteri',
				'Registrar': 'Kayıtçı',
				'Registration Date': 'Kayıt Tarihi',
				'Interval': 'Süre',
				'Year': 'Yıl',
				'Hosting': 'Hosting',
				'Panel Access Information': 'Panel Giriş Bilgileri',
				'No panel information': 'Panel giriş bilgisi eklenmemiş',
				'Linked Websites': 'Bağlı Siteler',
				'No websites linked to this domain': 'Bu domain\'e bağlı site yok',
				'Renewal History': 'Uzatma Geçmişi',
				'No renewals yet': 'Henüz uzatma yapılmamış',
				'year(s) extended': 'yıl uzatıldı',
				'New expiry': 'Yeni bitiş',
				'Price': 'Ücret',
			}

			return translations[text] || text
		},
		async loadDomains() {
			this.loading = true
			try {
				const response = await api.domains.getAll()
				this.domains = response.data || []
				console.log('Domains loaded:', this.domains.length)
			} catch (error) {
				console.error('Error loading domains:', error)
				this.domains = []
			} finally {
				this.loading = false
			}
		},
		async loadRelatedData() {
			try {
				const [clientsRes, hostingsRes, websitesRes] = await Promise.all([
					api.clients.getAll().catch(() => ({ data: [] })),
					api.hostings.getAll().catch(() => ({ data: [] })),
					api.websites.getAll().catch(() => ({ data: [] })),
				])
				this.clients = clientsRes.data || []
				this.hostings = hostingsRes.data || []
				this.websites = websitesRes.data || []
			} catch (error) {
				console.error('Error loading related data:', error)
			}
		},
		selectDomain(domain) {
			this.selectedDomain = domain
		},
		backToList() {
			this.selectedDomain = null
		},
		showAddModal() {
			this.editingDomain = null
			this.modalOpen = true
		},
		editDomain(id) {
			const domain = this.domains.find(d => d.id === id)
			if (domain) {
				this.editingDomain = domain
				this.modalOpen = true
			}
		},
		showExtendModal(domain) {
			this.extendingDomain = domain
			this.extendModalOpen = true
		},
		closeModal() {
			this.modalOpen = false
			this.editingDomain = null
		},
		closeExtendModal() {
			this.extendModalOpen = false
			this.extendingDomain = null
		},
		async handleDomainSaved() {
			await this.loadDomains()
			this.closeModal()
			// If detail view is open, refresh it
			if (this.selectedDomain) {
				const updated = this.domains.find(d => d.id === this.selectedDomain.id)
				if (updated) {
					this.selectedDomain = updated
				}
			}
		},
		async handleDomainExtended() {
			await this.loadDomains()
			this.closeExtendModal()
			// If detail view is open, refresh it
			if (this.selectedDomain) {
				const updated = this.domains.find(d => d.id === this.selectedDomain.id)
				if (updated) {
					this.selectedDomain = updated
				}
			}
		},
		confirmDelete(domain) {
			if (confirm(this.translate('domaincontrol', 'Are you sure you want to delete {name}?', { name: domain.domainName }))) {
				this.deleteDomain(domain.id)
			}
		},
		async deleteDomain(id) {
			try {
				await api.domains.delete(id)
				await this.loadDomains()
				if (this.selectedDomain && this.selectedDomain.id === id) {
					this.selectedDomain = null
				}
			} catch (error) {
				console.error('Error deleting domain:', error)
				alert(this.translate('domaincontrol', 'Error deleting domain'))
			}
		},
		getClientName(clientId) {
			const client = this.clients.find(c => c.id === clientId)
			return client ? client.name : null
		},
		getHostingName(hostingId) {
			const hosting = this.hostings.find(h => h.id === hostingId)
			return hosting ? `${hosting.provider}${hosting.plan ? ' - ' + hosting.plan : ''}` : null
		},
		getDomainWebsites(domainId) {
			return (this.websites || []).filter(w => w.domainId === domainId)
		},
		getRenewalHistory(domain) {
			if (!domain.renewalHistory) return []
			try {
				const history = JSON.parse(domain.renewalHistory)
				return Array.isArray(history) ? history.slice().reverse() : []
			} catch (e) {
				return []
			}
		},
		getDaysUntilExpiry(expirationDate) {
			if (!expirationDate) return '-'
			const today = new Date()
			today.setHours(0, 0, 0, 0)
			const expiry = new Date(expirationDate)
			expiry.setHours(0, 0, 0, 0)
			const diff = Math.ceil((expiry - today) / (1000 * 60 * 60 * 24))
			if (diff < 0) return this.translate('domaincontrol', 'Expired')
			if (diff === 0) return this.translate('domaincontrol', 'Today')
			return `${diff} ${this.translate('domaincontrol', 'days')}`
		},
		getDomainStatusClass(domain) {
			const daysLeft = this.getDaysUntilExpiryNumber(domain.expirationDate)
			if (daysLeft <= 0) return 'status-critical'
			if (daysLeft <= 7) return 'status-critical'
			if (daysLeft <= 30) return 'status-warning'
			return 'status-ok'
		},
		getDaysUntilExpiryNumber(expirationDate) {
			if (!expirationDate) return 999
			const today = new Date()
			today.setHours(0, 0, 0, 0)
			const expiry = new Date(expirationDate)
			expiry.setHours(0, 0, 0, 0)
			return Math.ceil((expiry - today) / (1000 * 60 * 60 * 24))
		},
		getDomainStatusText(domain) {
			const daysLeft = this.getDaysUntilExpiryNumber(domain.expirationDate)
			if (daysLeft <= 0) return this.translate('domaincontrol', 'EXPIRED')
			if (daysLeft <= 7) return this.translate('domaincontrol', 'CRITICAL')
			if (daysLeft <= 30) return this.translate('domaincontrol', 'UPCOMING')
			return this.translate('domaincontrol', 'ACTIVE')
		},
		formatDate(date) {
			if (!date) return '-'
			try {
				const d = new Date(date)
				return d.toLocaleDateString('tr-TR', { year: 'numeric', month: 'long', day: 'numeric' })
			} catch (e) {
				return date
			}
		},
		formatCurrency(amount, currency = 'USD') {
			if (!amount) return '-'
			const symbols = { USD: '$', EUR: '€', TRY: '₺', AZN: '₼', GBP: '£', RUB: '₽' }
			const symbol = symbols[currency] || currency
			return `${symbol}${parseFloat(amount).toFixed(2)} ${currency}`
		},
		navigateToWebsite(websiteId) {
			if (typeof window.DomainControl !== 'undefined' && window.DomainControl.switchTab) {
				window.DomainControl.switchTab('websites')
				setTimeout(() => {
					if (window.DomainControl.showWebsiteDetail) {
						window.DomainControl.showWebsiteDetail(websiteId)
					}
				}, 100)
			}
		},
		testEmail() {
			// TODO: Implement test email functionality
			alert(this.translate('domaincontrol', 'Test email functionality coming soon'))
		},
	},
}
</script>

<style scoped>
.domains-view {
	padding: 20px;
	padding-bottom: 40px;
}

.domains-list-view {
	width: 100%;
}

.domain-item {
	cursor: pointer;
}

.domain-item:hover {
	background-color: var(--color-background-hover);
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
	margin: 0;
	flex: 1;
}

.detail-actions {
	display: flex;
	gap: 8px;
}

.detail-content {
	display: flex;
	flex-direction: column;
	gap: 24px;
}

.detail-stats {
	display: grid;
	grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
	gap: 16px;
}

.stat-card {
	display: flex;
	align-items: center;
	gap: 16px;
	padding: 16px;
	background: var(--color-main-background);
	border: 1px solid var(--color-border);
	border-radius: var(--border-radius-large);
}

.stat-card__icon {
	width: 48px;
	height: 48px;
	display: flex;
	align-items: center;
	justify-content: center;
	border-radius: var(--border-radius);
	background: var(--color-background-hover);
}

.stat-card__content {
	flex: 1;
}

.stat-card__label {
	font-size: var(--font-size-small);
	color: var(--color-text-maxcontrast);
	margin-bottom: 4px;
}

.stat-card__value {
	font-size: 18px;
	font-weight: 500;
	color: var(--color-main-text);
}

.detail-info-grid {
	display: grid;
	grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
	gap: 16px;
}

.detail-info-card {
	padding: 20px;
	background: var(--color-main-background);
	border: 1px solid var(--color-border);
	border-radius: var(--border-radius-large);
}

.detail-info-card h3 {
	margin: 0 0 16px 0;
	font-size: 16px;
	font-weight: 500;
}

.detail-table {
	width: 100%;
	border-collapse: collapse;
}

.detail-table td {
	padding: 8px 0;
	border-bottom: 1px solid var(--color-border);
}

.detail-table td:first-child {
	color: var(--color-text-maxcontrast);
	width: 40%;
}

.detail-notes {
	white-space: pre-wrap;
	font-family: monospace;
	font-size: 13px;
	color: var(--color-main-text);
	background: var(--color-background-dark);
	padding: 12px;
	border-radius: var(--border-radius);
}

.mini-list {
	display: flex;
	flex-direction: column;
	gap: 8px;
}

.mini-item {
	padding: 12px;
	background: var(--color-background-hover);
	border-radius: var(--border-radius);
	cursor: pointer;
	transition: background-color 0.2s;
}

.mini-item:hover {
	background: var(--color-background-dark);
}

.empty-mini {
	padding: 16px;
	text-align: center;
	color: var(--color-text-maxcontrast);
	font-style: italic;
}

.detail-history-card {
	padding: 20px;
	background: var(--color-main-background);
	border: 1px solid var(--color-border);
	border-radius: var(--border-radius-large);
}

.detail-history-card h3 {
	margin: 0 0 16px 0;
	font-size: 16px;
	font-weight: 500;
}

.history-list {
	display: flex;
	flex-direction: column;
	gap: 12px;
}

.history-item {
	display: flex;
	gap: 16px;
	padding: 12px;
	background: var(--color-background-hover);
	border-radius: var(--border-radius);
}

.history-date {
	font-weight: 500;
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
	font-size: var(--font-size-small);
	color: var(--color-text-maxcontrast);
}

.history-note {
	font-size: var(--font-size-small);
	color: var(--color-text-maxcontrast);
	font-style: italic;
	margin-top: 4px;
}

.status-badge {
	padding: 4px 8px;
	border-radius: var(--border-radius);
	font-size: var(--font-size-small);
	font-weight: 500;
}

.status-ok {
	background: var(--color-success);
	color: var(--color-success-text);
}

.status-warning {
	background: var(--color-warning);
	color: var(--color-warning-text);
}

.status-critical {
	background: var(--color-error);
	color: var(--color-error-text);
}

.empty-state {
	padding: 40px;
	text-align: center;
	color: var(--color-text-maxcontrast);
}
</style>
