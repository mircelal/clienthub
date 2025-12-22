<template>
	<div class="websites-view">
		<!-- Website Modal -->
		<WebsiteModal
			:open="modalOpen"
			:website="editingWebsite"
			:clients="clients"
			:domains="domains"
			:hostings="hostings"
			@close="closeModal"
			@saved="handleWebsiteSaved"
		/>

		<!-- Website List View -->
		<div v-if="!selectedWebsite" class="websites-list-view">
			<div class="domaincontrol-actions">
				<button class="button-vue button-vue--primary" @click="showAddModal">
					<MaterialIcon name="add" :size="20" />
					{{ translate('domaincontrol', 'Add Website') }}
				</button>
				<div class="website-search-wrapper">
					<input
						type="text"
						v-model="searchQuery"
						class="website-search-input"
						:placeholder="translate('domaincontrol', 'Search websites...')"
						@input="filterWebsites"
					/>
				</div>
			</div>

			<!-- Empty State -->
			<div v-if="filteredWebsites.length === 0 && !loading" class="empty-content">
				<MaterialIcon name="link" :size="48" color="var(--color-text-maxcontrast)" class="empty-content__icon" />
				<p class="empty-content__text">
					{{ searchQuery ? translate('domaincontrol', 'No websites found') : translate('domaincontrol', 'No websites yet') }}
				</p>
				<button v-if="!searchQuery" class="button-vue button-vue--primary" @click="showAddModal">
					{{ translate('domaincontrol', 'Add First Website') }}
				</button>
			</div>

			<!-- Loading State -->
			<div v-if="loading" class="loading-content">
				<MaterialIcon name="loading" :size="32" class="loading-icon" />
				<p>{{ translate('domaincontrol', 'Loading websites...') }}</p>
			</div>

			<!-- Websites List -->
			<div v-else-if="filteredWebsites.length > 0" class="websites-list">
				<div
					v-for="website in filteredWebsites"
					:key="website.id"
					class="list-item website-item"
					@click="selectWebsite(website)"
				>
					<div class="list-item__avatar">
						<MaterialIcon name="link" :size="24" />
					</div>
					<div class="list-item__content">
						<div class="list-item__title">{{ website.name || website.software || translate('domaincontrol', 'Website') }}</div>
						<div class="list-item__meta">
							<span v-if="getClientName(website.clientId)">
								{{ getClientName(website.clientId) }}
							</span>
							<span v-if="getDomainName(website.domainId)">
								{{ getDomainName(website.domainId) }}
							</span>
							<span v-if="website.software">
								{{ website.software }}
							</span>
						</div>
					</div>
					<div class="list-item__stats">
						<div class="list-item__stat">
							<div class="list-item__stat-label">{{ translate('domaincontrol', 'Status') }}</div>
							<div class="list-item__stat-value">
								<span class="status-badge status-badge--simple" :class="getWebsiteStatusClass(website)">
									{{ getWebsiteStatusText(website.status) }}
								</span>
							</div>
						</div>
						<div class="list-item__stat">
							<div class="list-item__stat-label">{{ translate('domaincontrol', 'Hosting') }}</div>
							<div class="list-item__stat-value">
								{{ getHostingName(website.hostingId) || '-' }}
							</div>
						</div>
						<div class="list-item__stat">
							<div class="list-item__stat-label">{{ translate('domaincontrol', 'Software') }}</div>
							<div class="list-item__stat-value">
								{{ website.software || '-' }}
							</div>
						</div>
					</div>
					<div class="list-item__actions">
						<div class="popover-menu-wrapper" @click.stop>
							<button
								class="action-button action-button--more"
								@click.stop="togglePopover(website.id)"
								:title="translate('domaincontrol', 'More options')"
							>
								<MaterialIcon name="more-vertical" :size="18" />
							</button>
							<div
								v-if="openPopover === website.id"
								class="popover-menu"
								@click.stop
							>
								<button
									class="popover-menu-item"
									@click="editWebsite(website); closePopover()"
								>
									<MaterialIcon name="edit" :size="16" />
									{{ translate('domaincontrol', 'Edit') }}
								</button>
								<div class="popover-menu-separator"></div>
								<button
									class="popover-menu-item popover-menu-item--danger"
									@click="confirmDelete(website); closePopover()"
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

		<!-- Website Detail View -->
		<div v-else class="website-detail-view">
			<div class="detail-header">
				<button class="button-vue button-vue--tertiary" @click="backToList">
					<MaterialIcon name="arrow-left" :size="20" />
					{{ translate('domaincontrol', 'Back') }}
				</button>
				<h2 class="detail-title">{{ selectedWebsite.name || selectedWebsite.software || translate('domaincontrol', 'Website') }}</h2>
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
								@click="editWebsite(selectedWebsite); closeDetailPopover()"
							>
								<MaterialIcon name="edit" :size="16" />
								{{ translate('domaincontrol', 'Edit') }}
							</button>
							<div class="popover-menu-separator"></div>
							<button
								class="popover-menu-item popover-menu-item--danger"
								@click="confirmDelete(selectedWebsite); closeDetailPopover()"
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
						<div class="stat-card__icon">
							<MaterialIcon name="link" :size="24" />
						</div>
						<div class="stat-card__content">
							<div class="stat-card__label">{{ translate('domaincontrol', 'Software') }}</div>
							<div class="stat-card__value">{{ selectedWebsite.software || '-' }}</div>
						</div>
					</div>
					<div class="stat-card">
						<div class="stat-card__icon">
							<MaterialIcon name="settings" :size="24" />
						</div>
						<div class="stat-card__content">
							<div class="stat-card__label">{{ translate('domaincontrol', 'Version') }}</div>
							<div class="stat-card__value">{{ selectedWebsite.version || '-' }}</div>
						</div>
					</div>
					<div class="stat-card">
						<div class="stat-card__icon" :class="getWebsiteStatusClass(selectedWebsite)">
							<MaterialIcon name="checkmark" :size="24" />
						</div>
						<div class="stat-card__content">
							<div class="stat-card__label">{{ translate('domaincontrol', 'Status') }}</div>
							<div class="stat-card__value">
								<span class="status-badge" :class="getWebsiteStatusClass(selectedWebsite)">
									{{ getWebsiteStatusText(selectedWebsite.status) }}
								</span>
							</div>
						</div>
					</div>
					<div class="stat-card">
						<div class="stat-card__icon">
							<MaterialIcon name="calendar" :size="24" />
						</div>
						<div class="stat-card__content">
							<div class="stat-card__label">{{ translate('domaincontrol', 'Installation Date') }}</div>
							<div class="stat-card__value">{{ formatDate(selectedWebsite.installationDate) || '-' }}</div>
						</div>
					</div>
				</div>

				<!-- Info Grid -->
				<div class="detail-info-grid">
					<div class="detail-info-card">
						<h3 class="info-card-title">{{ translate('domaincontrol', 'General Information') }}</h3>
						<table class="detail-table">
							<tbody>
								<tr>
									<td class="table-label">{{ translate('domaincontrol', 'Client') }}</td>
									<td class="table-value">
										<a
											v-if="selectedWebsite.clientId"
											href="#"
											@click.prevent="navigateToClient(selectedWebsite.clientId)"
											class="link-primary"
										>
											{{ getClientName(selectedWebsite.clientId) }}
										</a>
										<span v-else>-</span>
									</td>
								</tr>
								<tr>
									<td class="table-label">{{ translate('domaincontrol', 'Domain') }}</td>
									<td class="table-value">
										<a
											v-if="selectedWebsite.domainId"
											href="#"
											@click.prevent="navigateToDomain(selectedWebsite.domainId)"
											class="link-primary"
										>
											{{ getDomainName(selectedWebsite.domainId) }}
										</a>
										<span v-else>-</span>
									</td>
								</tr>
								<tr>
									<td class="table-label">{{ translate('domaincontrol', 'Hosting') }}</td>
									<td class="table-value">
										<a
											v-if="selectedWebsite.hostingId"
											href="#"
											@click.prevent="navigateToHosting(selectedWebsite.hostingId)"
											class="link-primary"
										>
											{{ getHostingName(selectedWebsite.hostingId) }}
										</a>
										<span v-else>-</span>
									</td>
								</tr>
								<tr>
									<td class="table-label">{{ translate('domaincontrol', 'URL') }}</td>
									<td class="table-value">
										<a
											v-if="selectedWebsite.url"
											:href="selectedWebsite.url"
											target="_blank"
											rel="noopener noreferrer"
											class="link-primary"
										>
											<MaterialIcon name="link" :size="16" />
											{{ selectedWebsite.url }}
										</a>
										<span v-else>-</span>
									</td>
								</tr>
							</tbody>
						</table>
					</div>

					<div class="detail-info-card">
						<h3 class="info-card-title">{{ translate('domaincontrol', 'Admin Panel Information') }}</h3>
						<p v-if="selectedWebsite.adminUrl" class="admin-url">
							<a :href="selectedWebsite.adminUrl" target="_blank" rel="noopener noreferrer" class="link-primary">
								<MaterialIcon name="link" :size="16" />
								{{ selectedWebsite.adminUrl }}
							</a>
						</p>
						<pre class="detail-notes">{{ selectedWebsite.adminNotes || translate('domaincontrol', 'No admin panel info') }}</pre>
					</div>
				</div>

				<div class="detail-info-card" v-if="selectedWebsite.notes">
					<h3 class="info-card-title">{{ translate('domaincontrol', 'Notes') }}</h3>
					<div class="rich-text-content" v-html="selectedWebsite.notes"></div>
				</div>
			</div>
		</div>
	</div>
</template>

<script>
import api from '../services/api'
import MaterialIcon from './MaterialIcon.vue'
import WebsiteModal from './WebsiteModal.vue'

export default {
	name: 'Websites',
	components: {
		MaterialIcon,
		WebsiteModal,
	},
	data() {
		return {
			websites: [],
			clients: [],
			domains: [],
			hostings: [],
			selectedWebsite: null,
			loading: false,
			modalOpen: false,
			editingWebsite: null,
			searchQuery: '',
			openPopover: null,
			detailPopoverOpen: false,
		}
	},
	computed: {
		filteredWebsites() {
			if (!this.searchQuery) return this.websites

			const query = this.searchQuery.toLowerCase()
			return this.websites.filter(website => {
				const name = (website.name || website.software || '').toLowerCase()
				const clientName = this.getClientName(website.clientId) || ''
				const domainName = this.getDomainName(website.domainId) || ''
				const software = (website.software || '').toLowerCase()
				return (
					name.includes(query) ||
					clientName.toLowerCase().includes(query) ||
					domainName.toLowerCase().includes(query) ||
					software.includes(query)
				)
			})
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
					this.loadWebsites(),
					this.loadClients(),
					this.loadDomains(),
					this.loadHostings(),
				])
			} catch (error) {
				console.error('Error loading data:', error)
			} finally {
				this.loading = false
			}
		},
		async loadWebsites() {
			try {
				const response = await api.websites.getAll()
				this.websites = response.data || []
			} catch (error) {
				console.error('Error loading websites:', error)
				this.websites = []
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
		async loadDomains() {
			try {
				const response = await api.domains.getAll()
				this.domains = response.data || []
			} catch (error) {
				console.error('Error loading domains:', error)
				this.domains = []
			}
		},
		async loadHostings() {
			try {
				const response = await api.hostings.getAll()
				this.hostings = response.data || []
			} catch (error) {
				console.error('Error loading hostings:', error)
				this.hostings = []
			}
		},
		filterWebsites() {
			// Computed property handles filtering
		},
		selectWebsite(website) {
			this.selectedWebsite = website
		},
		backToList() {
			this.selectedWebsite = null
		},
		showAddModal() {
			this.editingWebsite = null
			this.modalOpen = true
		},
		editWebsite(website) {
			this.editingWebsite = website
			this.modalOpen = true
		},
		closeModal() {
			this.modalOpen = false
			this.editingWebsite = null
		},
		async handleWebsiteSaved() {
			await this.loadWebsites()
			if (this.selectedWebsite) {
				const response = await api.websites.get(this.selectedWebsite.id)
				this.selectedWebsite = response.data
			}
			this.closeModal()
		},
		async confirmDelete(website) {
			if (!confirm(this.translate('domaincontrol', 'Are you sure you want to delete this website?'))) {
				return
			}
			try {
				await api.websites.delete(website.id)
				await this.loadWebsites()
				if (this.selectedWebsite && this.selectedWebsite.id === website.id) {
					this.backToList()
				}
			} catch (error) {
				console.error('Error deleting website:', error)
				alert(this.translate('domaincontrol', 'Error deleting website'))
			}
		},
		getClientName(clientId) {
			const client = this.clients.find(c => c.id === clientId)
			return client ? client.name : ''
		},
		getDomainName(domainId) {
			const domain = this.domains.find(d => d.id === domainId)
			return domain ? domain.domainName : ''
		},
		getHostingName(hostingId) {
			const hosting = this.hostings.find(h => h.id === hostingId)
			return hosting ? `${hosting.provider} ${hosting.plan || ''}`.trim() : ''
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
		navigateToDomain(domainId) {
			if (typeof window.DomainControl !== 'undefined' && window.DomainControl.switchTab) {
				window.DomainControl.switchTab('domains')
				setTimeout(() => {
					const event = new CustomEvent('select-domain', { detail: { domainId } })
					window.dispatchEvent(event)
				}, 100)
			}
		},
		navigateToHosting(hostingId) {
			if (typeof window.DomainControl !== 'undefined' && window.DomainControl.switchTab) {
				window.DomainControl.switchTab('hostings')
				setTimeout(() => {
					const event = new CustomEvent('select-hosting', { detail: { hostingId } })
					window.dispatchEvent(event)
				}, 100)
			}
		},
		getWebsiteStatusClass(website) {
			return `status-${website.status || 'active'}`
		},
		getWebsiteStatusText(status) {
			const statusTexts = {
				active: this.translate('domaincontrol', 'Active'),
				maintenance: this.translate('domaincontrol', 'Maintenance'),
				development: this.translate('domaincontrol', 'Development'),
				inactive: this.translate('domaincontrol', 'Inactive'),
			}
			return statusTexts[status] || status
		},
		togglePopover(websiteId) {
			this.openPopover = this.openPopover === websiteId ? null : websiteId
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
				'Add Website': 'Website Ekle',
				'Search websites...': 'Websitelerde ara...',
				'No websites found': 'Website bulunamadı',
				'No websites yet': 'Henüz website yok',
				'Add First Website': 'İlk Websiteyi Ekle',
				'Loading websites...': 'Websiteler yükleniyor...',
				'Status': 'Durum',
				'Hosting': 'Hosting',
				'More options': 'Daha fazla seçenek',
				'Edit': 'Düzenle',
				'Delete': 'Sil',
				'Back': 'Geri',
				'Software': 'Yazılım',
				'Version': 'Versiyon',
				'Installation Date': 'Kurulum Tarihi',
				'General Information': 'Genel Bilgiler',
				'Client': 'Müşteri',
				'Domain': 'Domain',
				'URL': 'URL',
				'Admin Panel Information': 'Admin Panel Bilgileri',
				'No admin panel info': 'Admin panel bilgisi yok',
				'Notes': 'Notlar',
				'Active': 'Aktif',
				'Maintenance': 'Bakımda',
				'Development': 'Geliştirmede',
				'Inactive': 'Pasif',
				'Are you sure you want to delete this website?': 'Bu websiteyi silmek istediğinize emin misiniz?',
				'Error deleting website': 'Website silinirken hata oluştu',
				'Website': 'Website',
			}

			return translations[text] || text
		},
	},
}
</script>

<style scoped>
.websites-view {
	width: 100%;
	height: 100%;
}

.websites-list-view {
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

.website-search-wrapper {
	flex: 1;
	min-width: 200px;
}

.website-search-input {
	width: 100%;
	padding: 8px 12px;
	border: 1px solid var(--color-border);
	border-radius: var(--border-radius-element);
	background-color: var(--color-background-dark);
	color: var(--color-main-text);
	font-size: 14px;
}

.website-search-input:focus {
	outline: none;
	border-color: var(--color-primary-element);
}

.websites-list {
	display: grid;
	gap: 12px;
}

.website-item {
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

.website-item:hover {
	background-color: var(--color-background-hover);
}

.website-item .list-item__avatar {
	width: 48px;
	height: 48px;
	border-radius: 50%;
	background-color: var(--color-background-dark);
	display: flex;
	align-items: center;
	justify-content: center;
	flex-shrink: 0;
}

.website-item .list-item__avatar .material-icon {
	color: var(--color-text-maxcontrast);
}

.website-item .list-item__content {
	flex: 1;
	min-width: 0;
}

.website-item .list-item__title {
	font-size: 16px;
	font-weight: 500;
	color: var(--color-main-text);
	margin-bottom: 4px;
}

.website-item .list-item__meta {
	display: flex;
	align-items: center;
	gap: 12px;
	font-size: 13px;
	color: var(--color-text-maxcontrast);
	flex-wrap: wrap;
}

.website-item .list-item__stats {
	display: flex;
	gap: 24px;
	align-items: center;
}

.website-item .list-item__stat {
	display: flex;
	flex-direction: column;
	gap: 4px;
	min-width: 100px;
}

.website-item .list-item__stat-label {
	font-size: 12px;
	color: var(--color-text-maxcontrast);
}

.website-item .list-item__stat-value {
	font-size: 14px;
	font-weight: 500;
	color: var(--color-main-text);
}

.website-item .list-item__actions {
	display: flex;
	align-items: center;
	gap: 8px;
	flex-shrink: 0;
}

.website-detail-view {
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

.detail-table {
	width: 100%;
	border-collapse: collapse;
}

.detail-table td {
	padding: 8px 0;
	border-bottom: 1px solid var(--color-border);
}

.detail-table tbody tr:last-child td {
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

.admin-url {
	margin-bottom: 12px;
}

.detail-notes {
	white-space: pre-wrap;
	font-family: inherit;
	font-size: 14px;
	color: var(--color-main-text);
	background-color: var(--color-main-background);
	padding: 12px;
	border-radius: var(--border-radius-element);
	border: 1px solid var(--color-border);
	margin: 0;
}

.rich-text-content {
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

.status-active {
	background-color: var(--color-success);
	color: var(--color-success-text);
}

.status-maintenance {
	background-color: var(--color-warning);
	color: var(--color-warning-text);
}

.status-development {
	background-color: var(--color-primary-element-light);
	color: var(--color-primary-element-light-text);
}

.status-inactive {
	background-color: var(--color-text-maxcontrast);
	color: var(--color-main-background);
	opacity: 0.6;
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
