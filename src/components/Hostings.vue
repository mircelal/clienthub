<template>
	<div class="hostings-view">
		<!-- Hosting Modal -->
		<HostingModal
			:open="modalOpen"
			:hosting="editingHosting"
			:clients="clients"
			:packages="hostingPackages"
			@close="closeModal"
			@saved="handleHostingSaved"
		/>

		<!-- Hosting Payment Modal -->
		<HostingPaymentModal
			:open="paymentModalOpen"
			:hosting="payingHosting"
			@close="closePaymentModal"
			@paid="handleHostingPaid"
		/>

		<!-- Hosting Packages View -->
		<div v-if="showPackagesView" class="hosting-packages-view">
			<div class="domaincontrol-actions">
				<button class="button-vue button-vue--tertiary" @click="showPackagesView = false">
					<MaterialIcon name="arrow-left" :size="20" />
					{{ translate('domaincontrol', 'Back') }}
				</button>
				<button class="button-vue button-vue--primary" @click="showPackageModal()">
					<MaterialIcon name="add" :size="20" />
					{{ translate('domaincontrol', 'Add Package') }}
				</button>
			</div>

			<!-- Hosting Packages List -->
			<div v-if="hostingPackages.length === 0 && !packagesLoading" class="empty-content">
				<MaterialIcon name="category-office" :size="48" color="var(--color-text-maxcontrast)" class="empty-content__icon" />
				<p class="empty-content__text">
					{{ translate('domaincontrol', 'No packages yet') }}
				</p>
				<button class="button-vue button-vue--primary" @click="showPackageModal()">
					{{ translate('domaincontrol', 'Add First Package') }}
				</button>
			</div>

			<div v-else-if="packagesLoading" class="loading-content">
				<MaterialIcon name="loading" :size="32" class="loading-icon" />
				<p>{{ translate('domaincontrol', 'Loading packages...') }}</p>
			</div>

			<div v-else class="hosting-packages-list">
				<div
					v-for="pkg in hostingPackages"
					:key="pkg.id"
					class="list-item package-item"
					@click="editPackage(pkg)"
				>
					<div class="list-item__avatar">
						<MaterialIcon name="category-office" :size="24" />
					</div>
					<div class="list-item__content">
						<div class="list-item__title">{{ pkg.name }}</div>
						<div class="list-item__meta">
							<span>{{ pkg.provider }}</span>
							<span v-if="pkg.priceMonthly || pkg.priceYearly" class="package-price">
								<span v-if="pkg.priceMonthly">{{ formatCurrency(pkg.priceMonthly, pkg.currency) }}/{{ translate('domaincontrol', 'Month') }}</span>
								<span v-if="pkg.priceMonthly && pkg.priceYearly"> / </span>
								<span v-if="pkg.priceYearly">{{ formatCurrency(pkg.priceYearly, pkg.currency) }}/{{ translate('domaincontrol', 'Year') }}</span>
							</span>
						</div>
					</div>
					<div class="list-item__stats">
						<div class="list-item__stat">
							<div class="list-item__stat-label">{{ translate('domaincontrol', 'Status') }}</div>
							<div class="list-item__stat-value">
								<span class="status-badge" :class="{ 'package-active': pkg.isActive, 'package-inactive': !pkg.isActive }">
									{{ pkg.isActive ? translate('domaincontrol', 'Active') : translate('domaincontrol', 'Inactive') }}
								</span>
							</div>
						</div>
					</div>
					<div class="list-item__actions">
						<button
							class="action-button action-button--edit"
							@click.stop="editPackage(pkg)"
							:title="translate('domaincontrol', 'Edit')"
						>
							<MaterialIcon name="edit" :size="18" />
						</button>
						<button
							class="action-button action-button--delete"
							@click.stop="confirmDeletePackage(pkg)"
							:title="translate('domaincontrol', 'Delete')"
						>
							<MaterialIcon name="delete" :size="18" />
						</button>
					</div>
				</div>
			</div>
		</div>

		<!-- Hosting List View -->
		<div v-else-if="!selectedHosting" class="hostings-list-view">
			<div class="domaincontrol-actions">
				<button class="button-vue button-vue--primary" @click="showAddModal">
					<MaterialIcon name="add" :size="20" />
					{{ translate('domaincontrol', 'Add Hosting') }}
				</button>
				<button class="button-vue button-vue--secondary" @click="showPackagesView = true">
					<MaterialIcon name="category-office" :size="20" />
					{{ translate('domaincontrol', 'Manage Packages') }}
				</button>
				<div class="hosting-search-wrapper">
					<input
						type="text"
						v-model="searchQuery"
						class="hosting-search-input"
						:placeholder="translate('domaincontrol', 'Search hostings...')"
						@input="filterHostings"
					/>
				</div>
			</div>

			<!-- Empty State -->
			<div v-if="filteredHostings.length === 0 && !loading" class="empty-content">
				<MaterialIcon name="category-office" :size="48" color="var(--color-text-maxcontrast)" class="empty-content__icon" />
				<p class="empty-content__text">
					{{ searchQuery ? translate('domaincontrol', 'No hostings found') : translate('domaincontrol', 'No hostings yet') }}
				</p>
				<button v-if="!searchQuery" class="button-vue button-vue--primary" @click="showAddModal">
					{{ translate('domaincontrol', 'Add First Hosting') }}
				</button>
			</div>

			<!-- Loading State -->
			<div v-if="loading" class="loading-content">
				<MaterialIcon name="loading" :size="32" class="loading-icon" />
				<p>{{ translate('domaincontrol', 'Loading hostings...') }}</p>
			</div>

			<!-- Hostings List -->
			<div v-else-if="filteredHostings.length > 0" class="hostings-list">
				<div
					v-for="hosting in filteredHostings"
					:key="hosting.id"
					class="list-item hosting-item"
					:class="getHostingStatusClass(hosting)"
					@click="selectHosting(hosting)"
				>
					<div class="list-item__avatar">
						<MaterialIcon name="category-office" :size="24" />
					</div>
					<div class="list-item__content">
						<div class="list-item__title">{{ hosting.provider }} {{ hosting.plan ? `- ${hosting.plan}` : '' }}</div>
						<div class="list-item__meta">
							<span v-if="getClientName(hosting.clientId)">
								{{ getClientName(hosting.clientId) }}
							</span>
							<span v-if="hosting.serverIp">
								{{ hosting.serverIp }}
							</span>
						</div>
					</div>
					<div class="list-item__stats">
						<div class="list-item__stat">
							<div class="list-item__stat-label">{{ translate('domaincontrol', 'Next Payment') }}</div>
							<div class="list-item__stat-value">{{ formatDate(hosting.expirationDate) || '-' }}</div>
						</div>
						<div class="list-item__stat">
							<div class="list-item__stat-label">{{ translate('domaincontrol', 'Days Left') }}</div>
							<div class="list-item__stat-value">{{ getDaysUntilExpiry(hosting.expirationDate) }}</div>
						</div>
						<div class="list-item__stat">
							<div class="list-item__stat-label">{{ translate('domaincontrol', 'Status') }}</div>
							<div class="list-item__stat-value">
								<span class="status-badge" :class="getHostingStatusClass(hosting)">
									{{ getHostingStatusText(hosting) }}
								</span>
							</div>
						</div>
					</div>
					<div class="list-item__actions">
						<button
							class="action-button action-button--edit"
							@click.stop="editHosting(hosting)"
							:title="translate('domaincontrol', 'Edit')"
						>
							<MaterialIcon name="edit" :size="18" />
						</button>
						<button
							class="action-button action-button--delete"
							@click.stop="confirmDelete(hosting)"
							:title="translate('domaincontrol', 'Delete')"
						>
							<MaterialIcon name="delete" :size="18" />
						</button>
					</div>
				</div>
			</div>
		</div>

		<!-- Hosting Detail View -->
		<div v-else class="hosting-detail-view">
			<div class="detail-header">
				<button class="button-vue button-vue--tertiary" @click="backToList">
					<MaterialIcon name="arrow-left" :size="20" />
					{{ translate('domaincontrol', 'Back') }}
				</button>
				<h2 class="detail-title">{{ selectedHosting.provider }} {{ selectedHosting.plan ? `- ${selectedHosting.plan}` : '' }}</h2>
				<div class="detail-actions">
					<button class="button-vue button-vue--success" @click="showPaymentModal(selectedHosting)">
						{{ translate('domaincontrol', 'Add Payment') }}
					</button>
					<button class="button-vue button-vue--secondary" @click="editHosting(selectedHosting)">
						{{ translate('domaincontrol', 'Edit') }}
					</button>
					<button class="button-vue button-vue--danger" @click="confirmDelete(selectedHosting)">
						{{ translate('domaincontrol', 'Delete') }}
					</button>
				</div>
			</div>

			<div class="detail-content">
				<!-- Stats Cards -->
				<div class="detail-stats hosting-stats">
					<div class="stat-card hosting-stat-card">
						<div class="stat-card__icon">
							<MaterialIcon name="calendar" :size="24" />
						</div>
						<div class="stat-card__content">
							<div class="stat-card__label">{{ translate('domaincontrol', 'Next Payment') }}</div>
							<div class="stat-card__value">{{ formatDate(selectedHosting.expirationDate) || '-' }}</div>
						</div>
					</div>
					<div class="stat-card hosting-stat-card">
						<div class="stat-card__icon" :class="getHostingStatusClass(selectedHosting)">
							<MaterialIcon name="calendar" :size="24" />
						</div>
						<div class="stat-card__content">
							<div class="stat-card__label">{{ translate('domaincontrol', 'Days Left') }}</div>
							<div class="stat-card__value" :class="getHostingStatusClass(selectedHosting)">
								{{ getDaysUntilExpiry(selectedHosting.expirationDate) }}
							</div>
						</div>
					</div>
					<div class="stat-card hosting-stat-card">
						<div class="stat-card__icon">
							<MaterialIcon name="monitoring" :size="24" />
						</div>
						<div class="stat-card__content">
							<div class="stat-card__label">{{ translate('domaincontrol', 'Status') }}</div>
							<div class="stat-card__value">
								<span class="status-badge" :class="getHostingStatusClass(selectedHosting)">
									{{ getHostingStatusText(selectedHosting) }}
								</span>
							</div>
						</div>
					</div>
					<div class="stat-card hosting-stat-card">
						<div class="stat-card__icon">
							<MaterialIcon name="files" :size="24" />
						</div>
						<div class="stat-card__content">
							<div class="stat-card__label">{{ translate('domaincontrol', 'Price') }}</div>
							<div class="stat-card__value">
								{{ formatCurrency(selectedHosting.price, selectedHosting.currency) || '-' }}
							</div>
						</div>
					</div>
				</div>

				<!-- Info Grid -->
				<div class="detail-info-grid">
					<div class="detail-info-card hosting-info-card">
						<h3 class="hosting-info-title">{{ translate('domaincontrol', 'General Information') }}</h3>
						<table class="detail-table hosting-detail-table">
							<tbody>
								<tr>
									<td class="table-label">{{ translate('domaincontrol', 'Client') }}</td>
									<td class="table-value">
										<a
											href="#"
											@click.prevent="navigateToClient(selectedHosting.clientId)"
											class="link-primary"
										>
											{{ getClientName(selectedHosting.clientId) }}
										</a>
									</td>
								</tr>
								<tr v-if="selectedHosting.plan">
									<td class="table-label">{{ translate('domaincontrol', 'Plan') }}</td>
									<td class="table-value">{{ selectedHosting.plan }}</td>
								</tr>
								<tr v-if="selectedHosting.serverIp">
									<td class="table-label">{{ translate('domaincontrol', 'Server IP') }}</td>
									<td class="table-value">{{ selectedHosting.serverIp }}</td>
								</tr>
								<tr v-if="selectedHosting.serverType">
									<td class="table-label">{{ translate('domaincontrol', 'Server Type') }}</td>
									<td class="table-value">
										{{ selectedHosting.serverType === 'own' ? translate('domaincontrol', 'Own Server') : translate('domaincontrol', 'External Server') }}
									</td>
								</tr>
								<tr v-if="selectedHosting.startDate">
									<td class="table-label">{{ translate('domaincontrol', 'Start Date') }}</td>
									<td class="table-value">{{ formatDate(selectedHosting.startDate) }}</td>
								</tr>
								<tr v-if="selectedHosting.renewalInterval">
									<td class="table-label">{{ translate('domaincontrol', 'Renewal Interval') }}</td>
									<td class="table-value">{{ formatRenewalInterval(selectedHosting.renewalInterval) }}</td>
								</tr>
							</tbody>
						</table>
					</div>

					<div class="detail-info-card hosting-info-card">
						<h3 class="hosting-info-title">{{ translate('domaincontrol', 'Panel Login Info') }}</h3>
						<div v-if="selectedHosting.panelUrl" style="margin-bottom: 8px;">
							<a :href="selectedHosting.panelUrl" target="_blank" class="link-primary">
								{{ selectedHosting.panelUrl }}
							</a>
						</div>
						<pre class="detail-notes hosting-notes">{{ selectedHosting.panelNotes || translate('domaincontrol', 'No panel login info') }}</pre>
					</div>
				</div>

				<div class="detail-info-card hosting-info-card">
					<h3 class="hosting-info-title">{{ translate('domaincontrol', 'Linked Domains') }}</h3>
					<div class="mini-list hosting-mini-list">
						<div v-if="getLinkedDomains(selectedHosting.id).length === 0" class="empty-mini">
							{{ translate('domaincontrol', 'No linked domains') }}
						</div>
						<div
							v-for="domain in getLinkedDomains(selectedHosting.id)"
							:key="domain.id"
							class="mini-item"
							@click="navigateToDomain(domain.id)"
						>
							<span>{{ domain.domainName }}</span>
							<span>{{ formatDate(domain.expirationDate) || '-' }}</span>
						</div>
					</div>
				</div>

				<div class="detail-info-card hosting-info-card">
					<h3 class="hosting-info-title">{{ translate('domaincontrol', 'Linked Websites') }}</h3>
					<div class="mini-list hosting-mini-list">
						<div v-if="getLinkedWebsites(selectedHosting.id).length === 0" class="empty-mini">
							{{ translate('domaincontrol', 'No linked websites') }}
						</div>
						<div
							v-for="website in getLinkedWebsites(selectedHosting.id)"
							:key="website.id"
							class="mini-item"
							@click="navigateToWebsite(website.id)"
						>
							<span>{{ website.name || website.software || 'N/A' }}</span>
							<span>{{ website.url || '-' }}</span>
						</div>
					</div>
				</div>

				<div class="detail-history-card hosting-history-card">
					<h3 class="hosting-history-title">{{ translate('domaincontrol', 'Payment History') }}</h3>
					<div class="history-list hosting-history-list">
						<div v-if="getPaymentHistory(selectedHosting).length === 0" class="empty-state">
							{{ translate('domaincontrol', 'No payment history') }}
						</div>
						<div
							v-for="(entry, index) in getPaymentHistory(selectedHosting)"
							:key="index"
							class="history-item"
						>
							<div class="history-date">
								<MaterialIcon name="calendar" :size="16" /> {{ formatDate(entry.date) }}
							</div>
							<div class="history-content">
								<strong>{{ formatCurrency(entry.amount, entry.currency) }}</strong>
								<span class="history-detail">
									{{ translate('domaincontrol', 'Period') }}: {{ entry.period }} {{ translate('domaincontrol', 'months') }}
								</span>
								<span v-if="entry.note" class="history-note">
									{{ entry.note }}
								</span>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>

		<!-- Hosting Package Modal -->
		<HostingPackageModal
			:open="packageModalOpen"
			:package="editingPackage"
			@close="closePackageModal"
			@saved="handlePackageSaved"
		/>
	</div>
</template>

<script>
import api from '../services/api'
import MaterialIcon from './MaterialIcon.vue'
import HostingModal from './HostingModal.vue'
import HostingPaymentModal from './HostingPaymentModal.vue'
import HostingPackageModal from './HostingPackageModal.vue'

export default {
	name: 'Hostings',
	components: {
		MaterialIcon,
		HostingModal,
		HostingPaymentModal,
		HostingPackageModal,
	},
	data() {
		return {
			hostings: [],
			hostingPackages: [],
			clients: [],
			domains: [],
			websites: [],
			selectedHosting: null,
			searchQuery: '',
			loading: false,
			packagesLoading: false,
			modalOpen: false,
			editingHosting: null,
			paymentModalOpen: false,
			payingHosting: null,
			showPackagesView: false,
			packageModalOpen: false,
			editingPackage: null,
		}
	},
	computed: {
		filteredHostings() {
			if (!this.searchQuery) {
				return this.hostings
			}
			const query = this.searchQuery.toLowerCase()
			return this.hostings.filter(hosting => {
				return (
					hosting.provider?.toLowerCase().includes(query) ||
					hosting.plan?.toLowerCase().includes(query) ||
					this.getClientName(hosting.clientId)?.toLowerCase().includes(query) ||
					hosting.serverIp?.toLowerCase().includes(query)
				)
			})
		},
	},
	mounted() {
		this.loadHostings()
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
				'Add Hosting': 'Hosting Ekle',
				'Manage Packages': 'Paketleri Yönet',
				'Search hostings...': 'Hosting ara...',
				'No hostings found': 'Hosting bulunamadı',
				'No hostings yet': 'Henüz hosting eklenmemiş',
				'Add First Hosting': 'İlk Hostingi Ekle',
				'Loading hostings...': 'Hostingler yükleniyor...',
				'Back': 'Geri',
				'Add Payment': 'Ödeme Ekle',
				'Edit': 'Düzenle',
				'Delete': 'Sil',
				'Next Payment': 'Sonraki Ödeme',
				'Days Left': 'Kalan Gün',
				'Status': 'Durum',
				'Price': 'Fiyat',
				'General Information': 'Genel Bilgiler',
				'Client': 'Müşteri',
				'Plan': 'Paket',
				'Server IP': 'Sunucu IP',
				'Server Type': 'Sunucu Tipi',
				'Own Server': 'Kendi Sunucum',
				'External Server': 'Harici Sunucu',
				'Start Date': 'Başlangıç Tarihi',
				'Renewal Interval': 'Ödeme Periyodu',
				'Panel Login Info': 'Panel Giriş Bilgileri',
				'No panel login info': 'Panel giriş bilgisi eklenmemiş',
				'Linked Domains': 'Bağlı Domainler',
				'No linked domains': 'Bu hosting\'e bağlı domain yok',
				'Linked Websites': 'Bağlı Siteler',
				'No linked websites': 'Bu hosting\'e bağlı site yok',
				'Payment History': 'Ödeme Geçmişi',
				'No payment history': 'Henüz ödeme yapılmamış',
				'Period': 'Süre',
				'months': 'ay',
				'Add Package': 'Paket Ekle',
				'No packages yet': 'Henüz paket eklenmemiş',
				'Add First Package': 'İlk Paketi Ekle',
				'Loading packages...': 'Paketler yükleniyor...',
				'Provider': 'Sağlayıcı',
				'Month': 'Ay',
				'Year': 'Yıl',
				'Active': 'Aktif',
				'Inactive': 'Pasif',
				'Error deleting hosting': 'Hosting silinirken hata oluştu',
				'Hosting deleted successfully': 'Hosting başarıyla silindi',
			}

			return translations[text] || text
		},
		async loadHostings() {
			this.loading = true
			try {
				const response = await api.hostings.getAll()
				this.hostings = response.data || []
				console.log('Hostings loaded:', this.hostings.length)
			} catch (error) {
				console.error('Error loading hostings:', error)
				this.hostings = []
			} finally {
				this.loading = false
			}
		},
		async loadHostingPackages() {
			this.packagesLoading = true
			try {
				const response = await api.hostingPackages.getAll()
				this.hostingPackages = response.data || []
				console.log('Hosting packages loaded:', this.hostingPackages.length)
			} catch (error) {
				console.error('Error loading hosting packages:', error)
				this.hostingPackages = []
			} finally {
				this.packagesLoading = false
			}
		},
		async loadRelatedData() {
			try {
				const [clientsRes, domainsRes, websitesRes] = await Promise.all([
					api.clients.getAll().catch(() => ({ data: [] })),
					api.domains.getAll().catch(() => ({ data: [] })),
					api.websites.getAll().catch(() => ({ data: [] })),
				])
				this.clients = clientsRes.data || []
				this.domains = domainsRes.data || []
				this.websites = websitesRes.data || []
			} catch (error) {
				console.error('Error loading related data:', error)
			}
		},
		filterHostings() {
			// Handled by computed property
		},
		selectHosting(hosting) {
			this.selectedHosting = hosting
		},
		backToList() {
			this.selectedHosting = null
		},
		showAddModal() {
			this.editingHosting = null
			this.modalOpen = true
		},
		editHosting(hosting) {
			this.editingHosting = hosting
			this.modalOpen = true
		},
		closeModal() {
			this.modalOpen = false
			this.editingHosting = null
		},
		handleHostingSaved() {
			this.closeModal()
			this.loadHostings()
			this.loadRelatedData()
		},
		showPaymentModal(hosting) {
			this.payingHosting = hosting
			this.paymentModalOpen = true
		},
		closePaymentModal() {
			this.paymentModalOpen = false
			this.payingHosting = null
		},
		handleHostingPaid() {
			this.closePaymentModal()
			this.loadHostings()
		},
		showPackageModal(pkg = null) {
			this.editingPackage = pkg
			this.packageModalOpen = true
		},
		editPackage(pkg) {
			this.showPackageModal(pkg)
		},
		closePackageModal() {
			this.packageModalOpen = false
			this.editingPackage = null
		},
		handlePackageSaved() {
			this.closePackageModal()
			this.loadHostingPackages()
		},
		async confirmDelete(hosting) {
			if (confirm(this.translate('domaincontrol', `Are you sure you want to delete this hosting?`))) {
				try {
					await api.hostings.delete(hosting.id)
					this.backToList()
					this.loadHostings()
					this.loadRelatedData()
					OC.Notification.show(this.translate('domaincontrol', 'Hosting deleted successfully'))
				} catch (error) {
					console.error('Error deleting hosting:', error)
					OC.Notification.showTemporary(this.translate('domaincontrol', 'Error deleting hosting'))
				}
			}
		},
		async confirmDeletePackage(pkg) {
			if (confirm(this.translate('domaincontrol', `Are you sure you want to delete package ${pkg.name}?`))) {
				try {
					await api.hostingPackages.delete(pkg.id)
					this.loadHostingPackages()
					OC.Notification.show(this.translate('domaincontrol', 'Package deleted successfully'))
				} catch (error) {
					console.error('Error deleting package:', error)
					OC.Notification.showTemporary(this.translate('domaincontrol', 'Error deleting package'))
				}
			}
		},
		formatDate(dateString) {
			if (!dateString) return '-'
			try {
				const options = { year: 'numeric', month: 'long', day: 'numeric' }
				return new Date(dateString).toLocaleDateString(undefined, options)
			} catch (e) {
				return dateString.split(' ')[0]
			}
		},
		getDaysUntilExpiry(expirationDate) {
			if (!expirationDate) return 'N/A'
			const today = new Date()
			today.setHours(0, 0, 0, 0)
			const expiry = new Date(expirationDate)
			expiry.setHours(0, 0, 0, 0)
			const diffTime = expiry.getTime() - today.getTime()
			const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24))
			return diffDays
		},
		getHostingStatusClass(hosting) {
			const daysLeft = this.getDaysUntilExpiry(hosting.expirationDate)
			if (daysLeft <= 0) return 'status-critical'
			if (daysLeft <= 30) return 'status-warning'
			return 'status-ok'
		},
		getHostingStatusText(hosting) {
			const daysLeft = this.getDaysUntilExpiry(hosting.expirationDate)
			if (daysLeft <= 0) return this.translate('domaincontrol', 'EXPIRED')
			if (daysLeft <= 7) return this.translate('domaincontrol', 'CRITICAL')
			if (daysLeft <= 30) return this.translate('domaincontrol', 'UPCOMING')
			return this.translate('domaincontrol', 'ACTIVE')
		},
		getClientName(clientId) {
			const client = this.clients.find(c => c.id == clientId)
			return client ? client.name : this.translate('domaincontrol', 'Unassigned')
		},
		getLinkedDomains(hostingId) {
			return this.domains.filter(d => d.hostingId == hostingId)
		},
		getLinkedWebsites(hostingId) {
			return this.websites.filter(w => w.hostingId == hostingId)
		},
		getPaymentHistory(hosting) {
			try {
				return hosting.paymentHistory ? JSON.parse(hosting.paymentHistory) : []
			} catch (e) {
				console.error('Error parsing payment history:', e)
				return []
			}
		},
		formatCurrency(amount, currency) {
			if (amount === null || amount === undefined) return ''
			const symbol = { USD: '$', EUR: '€', TRY: '₺', AZN: '₼', GBP: '£', RUB: '₽' }[currency] || ''
			return `${symbol}${parseFloat(amount).toFixed(2)}`
		},
		formatRenewalInterval(interval) {
			const intervals = {
				monthly: this.translate('domaincontrol', 'Monthly'),
				quarterly: this.translate('domaincontrol', 'Quarterly'),
				yearly: this.translate('domaincontrol', 'Yearly'),
				biennial: this.translate('domaincontrol', 'Biennial'),
			}
			return intervals[interval] || interval
		},
		navigateToClient(clientId) {
			if (typeof window.DomainControl !== 'undefined' && window.DomainControl.switchTab) {
				window.DomainControl.switchTab('clients')
				console.log(`Navigate to client detail for ID: ${clientId}`)
			}
		},
		navigateToDomain(domainId) {
			if (typeof window.DomainControl !== 'undefined' && window.DomainControl.switchTab) {
				window.DomainControl.switchTab('domains')
				console.log(`Navigate to domain detail for ID: ${domainId}`)
			}
		},
		navigateToWebsite(websiteId) {
			if (typeof window.DomainControl !== 'undefined' && window.DomainControl.switchTab) {
				window.DomainControl.switchTab('websites')
				console.log(`Navigate to website detail for ID: ${websiteId}`)
			}
		},
	},
	watch: {
		showPackagesView(newVal) {
			if (newVal) {
				this.loadHostingPackages()
			}
		},
	},
}
</script>

<style scoped>
/* General View Layout */
.hostings-list-view,
.hosting-detail-view,
.hosting-packages-view {
	padding: 20px;
	max-width: 1200px;
	margin: 0 auto;
}

.hosting-search-wrapper {
	flex-grow: 1;
	min-width: 200px;
	position: relative;
}

.hosting-search-input {
	width: 100%;
	padding: 8px 12px 8px 40px;
	border: 1px solid var(--color-border);
	border-radius: var(--border-radius-element);
	background-color: var(--color-background-dark);
	color: var(--color-main-text);
	font-size: 14px;
	box-sizing: border-box;
	background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>');
	background-repeat: no-repeat;
	background-position: 12px center;
	background-size: 16px;
}

.hosting-search-input:focus {
	outline: none;
	border-color: var(--color-primary);
}

/* Hosting Packages List - Nextcloud style */
.hosting-packages-list {
	display: grid;
	gap: 12px;
	margin-top: 20px;
}

.package-item {
	cursor: pointer;
}

.package-item .list-item__avatar .material-icon {
	filter: brightness(0) invert(1);
}

.package-price {
	color: var(--color-primary-element);
	font-weight: 500;
}

.package-active {
	background-color: var(--color-success);
	color: var(--color-success-text);
}

.package-inactive {
	background-color: var(--color-text-maxcontrast);
	color: var(--color-main-background);
}

/* List Item Styling - Similar to Domains */
.hostings-list {
	display: grid;
	gap: 12px;
}

.list-item {
	display: flex;
	align-items: center;
	background-color: var(--color-background-dark);
	border-radius: var(--border-radius-element);
	padding: 12px 16px;
	transition: background-color 0.2s ease;
	border: 1px solid var(--color-border);
}

.list-item:hover {
	background-color: var(--color-background-hover);
}

.list-item__avatar {
	width: 40px;
	height: 40px;
	border-radius: 50%;
	background-color: var(--color-primary-element);
	display: flex;
	align-items: center;
	justify-content: center;
	font-size: 1.2em;
	color: var(--color-primary-element-text);
	margin-right: 16px;
	flex-shrink: 0;
}

.list-item__avatar .material-icon {
	filter: brightness(0) invert(1);
}

.list-item__content {
	flex-grow: 1;
	min-width: 0;
}

.list-item__title {
	font-weight: 600;
	color: var(--color-main-text);
	font-size: 16px;
	margin-bottom: 4px;
	white-space: nowrap;
	overflow: hidden;
	text-overflow: ellipsis;
}

.list-item__meta {
	font-size: 13px;
	color: var(--color-text-maxcontrast);
	display: flex;
	align-items: center;
	gap: 8px;
	white-space: nowrap;
	overflow: hidden;
	text-overflow: ellipsis;
}

.list-item__stats {
	display: flex;
	gap: 20px;
	margin-left: 20px;
	flex-shrink: 0;
}

.list-item__stat {
	text-align: right;
}

.list-item__stat-label {
	font-size: 12px;
	color: var(--color-text-maxcontrast);
	margin-bottom: 2px;
}

.list-item__stat-value {
	font-weight: 500;
	color: var(--color-main-text);
	font-size: 14px;
}

.list-item__actions {
	display: flex;
	gap: 8px;
	margin-left: 20px;
	flex-shrink: 0;
}

/* Status Badges */
.status-badge {
	padding: 4px 8px;
	border-radius: var(--border-radius-pill);
	font-size: 11px;
	font-weight: 600;
	text-transform: uppercase;
	display: inline-block;
}

.status-ok {
	background-color: var(--color-success);
	color: var(--color-success-text);
}

.status-warning {
	background-color: var(--color-warning);
	color: var(--color-warning-text);
}

.status-critical {
	background-color: var(--color-error);
	color: var(--color-error-text);
}

/* Detail View Styling */
.detail-header {
	display: flex;
	align-items: center;
	gap: 20px;
	margin-bottom: 20px;
	flex-wrap: wrap;
}

.detail-title {
	margin: 0;
	font-size: 24px;
	color: var(--color-main-text);
	flex-grow: 1;
}

.detail-actions {
	display: flex;
	gap: 12px;
}

.detail-content {
	display: grid;
	gap: 20px;
}

.detail-stats {
	display: grid;
	grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
	gap: 16px;
}

.stat-card {
	background-color: var(--color-background-dark);
	border-radius: var(--border-radius-element);
	padding: 16px;
	display: flex;
	align-items: center;
	gap: 12px;
	border: 1px solid var(--color-border);
}

.stat-card__icon {
	width: 40px;
	height: 40px;
	border-radius: 50%;
	background-color: var(--color-background-hover);
	display: flex;
	align-items: center;
	justify-content: center;
	flex-shrink: 0;
}

.stat-card__icon .material-icon {
	filter: brightness(0) invert(1) opacity(0.7);
}

.stat-card__content {
	flex-grow: 1;
}

.stat-card__label {
	font-size: 13px;
	color: var(--color-text-maxcontrast);
	margin-bottom: 4px;
}

.stat-card__value {
	font-size: 18px;
	font-weight: 600;
	color: var(--color-main-text);
}

.detail-info-grid {
	display: grid;
	grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
	gap: 20px;
}

.detail-info-card,
.detail-history-card {
	background-color: var(--color-background-dark);
	border-radius: var(--border-radius-element);
	padding: 20px;
	border: 1px solid var(--color-border);
}

.hosting-info-title,
.hosting-history-title {
	margin-top: 0;
	margin-bottom: 15px;
	font-size: 18px;
	color: var(--color-main-text);
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
	width: 120px;
}

.table-value {
	color: var(--color-main-text);
}

.detail-notes {
	background-color: var(--color-background-hover);
	border: 1px solid var(--color-border);
	border-radius: var(--border-radius-small);
	padding: 10px;
	white-space: pre-wrap;
	font-family: var(--font-face);
	font-size: 14px;
	color: var(--color-main-text);
}

.mini-list {
	display: grid;
	gap: 8px;
}

.mini-item {
	background-color: var(--color-background-hover);
	border-radius: var(--border-radius-small);
	padding: 8px 12px;
	display: flex;
	justify-content: space-between;
	align-items: center;
	font-size: 14px;
	color: var(--color-main-text);
	border: 1px solid var(--color-border);
	cursor: pointer;
	transition: background-color 0.2s ease;
}

.mini-item:hover {
	background-color: var(--color-background-darker);
}

.empty-mini {
	color: var(--color-text-maxcontrast);
	font-style: italic;
	padding: 10px;
	text-align: center;
}

.history-list {
	display: grid;
	gap: 12px;
}

.history-item {
	background-color: var(--color-background-hover);
	border-radius: var(--border-radius-small);
	padding: 12px;
	border: 1px solid var(--color-border);
}

.history-date {
	font-size: 13px;
	color: var(--color-text-maxcontrast);
	margin-bottom: 8px;
	display: flex;
	align-items: center;
	gap: 5px;
}

.history-content {
	font-size: 14px;
	color: var(--color-main-text);
}

.history-detail {
	display: block;
	color: var(--color-text-maxcontrast);
	margin-top: 4px;
}

.history-note {
	display: block;
	font-style: italic;
	color: var(--color-text-maxcontrast);
	margin-top: 8px;
}

.link-primary {
	color: var(--color-primary-element);
	text-decoration: none;
}

.link-primary:hover {
	text-decoration: underline;
}

/* Responsive adjustments */
@media (max-width: 768px) {
	.domaincontrol-actions {
		flex-direction: column;
		align-items: stretch;
	}

	.hosting-search-wrapper {
		width: 100%;
	}

	.detail-header {
		flex-direction: column;
		align-items: flex-start;
	}

	.detail-actions {
		width: 100%;
		justify-content: stretch;
	}

	.detail-actions .button-vue {
		flex-grow: 1;
	}

	.detail-stats,
	.detail-info-grid {
		grid-template-columns: 1fr;
	}

	.hosting-packages-grid {
		grid-template-columns: 1fr;
	}

	.list-item {
		flex-wrap: wrap;
	}

	.list-item__content {
		flex-basis: 100%;
		margin-bottom: 10px;
	}

	.list-item__stats {
		flex-basis: 100%;
		justify-content: space-around;
		margin-left: 0;
		margin-top: 10px;
	}

	.list-item__actions {
		flex-basis: 100%;
		justify-content: flex-end;
		margin-left: 0;
		margin-top: 10px;
	}
}
</style>
