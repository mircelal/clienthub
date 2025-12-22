<template>
	<div class="services-view">
		<!-- Service Modal -->
		<ServiceModal
			:open="modalOpen"
			:service="editingService"
			:clients="clients"
			:serviceTypes="serviceTypes"
			@close="closeModal"
			@saved="handleServiceSaved"
		/>

		<!-- Service Extend Modal -->
		<ServiceExtendModal
			:open="extendModalOpen"
			:service="extendingService"
			@close="closeExtendModal"
			@extended="handleServiceExtended"
		/>

		<!-- Service Type Modal -->
		<ServiceTypeModal
			:open="serviceTypeModalOpen"
			:serviceType="editingServiceType"
			@close="closeServiceTypeModal"
			@saved="handleServiceTypeSaved"
		/>

		<!-- Service Types View -->
		<div v-if="showServiceTypesView" class="service-types-view">
			<div class="domaincontrol-actions">
				<button class="button-vue button-vue--tertiary" @click="showServiceTypesView = false">
					<MaterialIcon name="arrow-left" :size="20" />
					{{ translate('domaincontrol', 'Back') }}
				</button>
				<button class="button-vue button-vue--primary" @click="showServiceTypeModal()">
					<MaterialIcon name="add" :size="20" />
					{{ translate('domaincontrol', 'Add Service Type') }}
				</button>
				<button class="button-vue button-vue--secondary" @click="initPredefinedTypes">
					<MaterialIcon name="category-office" :size="20" />
					{{ translate('domaincontrol', 'Add Predefined Types') }}
				</button>
			</div>

			<!-- Service Types List -->
			<div v-if="serviceTypes.length === 0 && !serviceTypesLoading" class="empty-content">
				<MaterialIcon name="category-office" :size="48" color="var(--color-text-maxcontrast)" class="empty-content__icon" />
				<p class="empty-content__text">
					{{ translate('domaincontrol', 'No service types yet') }}
				</p>
				<button class="button-vue button-vue--primary" @click="showServiceTypeModal()">
					{{ translate('domaincontrol', 'Add First Service Type') }}
				</button>
			</div>

			<div v-else-if="serviceTypesLoading" class="loading-content">
				<MaterialIcon name="loading" :size="32" class="loading-icon" />
				<p>{{ translate('domaincontrol', 'Loading service types...') }}</p>
			</div>

			<div v-else class="service-types-list">
				<div
					v-for="type in serviceTypes"
					:key="type.id"
					class="list-item service-type-item"
					@click="editServiceType(type)"
				>
					<div class="list-item__avatar">
						<MaterialIcon name="category-office" :size="24" />
					</div>
					<div class="list-item__content">
						<div class="list-item__title">{{ type.name }}</div>
						<div class="list-item__meta">
							<span v-if="type.description">{{ type.description }}</span>
							<span v-if="type.defaultPrice" class="service-type-price">
								{{ formatCurrency(type.defaultPrice, type.defaultCurrency) }}
							</span>
							<span v-if="getRenewalIntervalText(type.renewalInterval)">
								{{ getRenewalIntervalText(type.renewalInterval) }}
							</span>
						</div>
					</div>
					<div class="list-item__stats">
						<div class="list-item__stat">
							<div class="list-item__stat-label">{{ translate('domaincontrol', 'Interval') }}</div>
							<div class="list-item__stat-value">
								{{ getRenewalIntervalText(type.renewalInterval) || '-' }}
							</div>
						</div>
						<div class="list-item__stat">
							<div class="list-item__stat-label">{{ translate('domaincontrol', 'Price') }}</div>
							<div class="list-item__stat-value">
								{{ formatCurrency(type.defaultPrice, type.defaultCurrency) }}
							</div>
						</div>
					</div>
					<div class="list-item__actions">
						<button
							class="action-button action-button--edit"
							@click.stop="editServiceType(type)"
							:title="translate('domaincontrol', 'Edit')"
						>
							<MaterialIcon name="edit" :size="18" />
						</button>
						<button
							class="action-button action-button--delete"
							@click.stop="confirmDeleteServiceType(type)"
							:title="translate('domaincontrol', 'Delete')"
						>
							<MaterialIcon name="delete" :size="18" />
						</button>
					</div>
				</div>
			</div>
		</div>

		<!-- Service List View -->
		<div v-else-if="!selectedService" class="services-list-view">
			<div class="domaincontrol-actions">
				<button class="button-vue button-vue--primary" @click="showAddModal">
					<MaterialIcon name="add" :size="20" />
					{{ translate('domaincontrol', 'Add Service') }}
				</button>
				<button class="button-vue button-vue--secondary" @click="showServiceTypesView = true">
					<MaterialIcon name="category-office" :size="20" />
					{{ translate('domaincontrol', 'Manage Service Types') }}
				</button>
				<div class="service-search-wrapper">
					<input
						type="text"
						v-model="searchQuery"
						class="service-search-input"
						:placeholder="translate('domaincontrol', 'Search services...')"
						@input="filterServices"
					/>
				</div>
			</div>

			<!-- Empty State -->
			<div v-if="filteredServices.length === 0 && !loading" class="empty-content">
				<MaterialIcon name="settings" :size="48" color="var(--color-text-maxcontrast)" class="empty-content__icon" />
				<p class="empty-content__text">
					{{ searchQuery ? translate('domaincontrol', 'No services found') : translate('domaincontrol', 'No services yet') }}
				</p>
				<button v-if="!searchQuery" class="button-vue button-vue--primary" @click="showAddModal">
					{{ translate('domaincontrol', 'Add First Service') }}
				</button>
			</div>

			<!-- Loading State -->
			<div v-if="loading" class="loading-content">
				<MaterialIcon name="loading" :size="32" class="loading-icon" />
				<p>{{ translate('domaincontrol', 'Loading services...') }}</p>
			</div>

			<!-- Services List -->
			<div v-else-if="filteredServices.length > 0" class="services-list">
				<div
					v-for="service in filteredServices"
					:key="service.id"
					class="list-item service-item"
					@click="selectService(service)"
				>
					<div class="list-item__avatar">
						<MaterialIcon name="settings" :size="24" />
					</div>
					<div class="list-item__content">
						<div class="list-item__title">{{ service.name }}</div>
						<div class="list-item__meta">
							<span v-if="getClientName(service.clientId)">
								{{ getClientName(service.clientId) }}
							</span>
							<span v-if="service.expirationDate">
								{{ formatDate(service.expirationDate) }}
							</span>
							<span v-if="getRenewalIntervalText(service.renewalInterval)">
								{{ getRenewalIntervalText(service.renewalInterval) }}
							</span>
						</div>
					</div>
					<div class="list-item__stats">
						<div class="list-item__stat">
							<div class="list-item__stat-label">{{ translate('domaincontrol', 'Price') }}</div>
							<div class="list-item__stat-value">
								{{ formatCurrency(service.price, service.currency) }}
							</div>
						</div>
						<div class="list-item__stat">
							<div class="list-item__stat-label">{{ translate('domaincontrol', 'Status') }}</div>
							<div class="list-item__stat-value">
								<span class="status-badge status-badge--simple" :class="getServiceStatusClass(service)">
									{{ getServiceStatusText(service.status) }}
								</span>
							</div>
						</div>
						<div class="list-item__stat">
							<div class="list-item__stat-label">{{ translate('domaincontrol', 'Expiry') }}</div>
							<div class="list-item__stat-value" :class="getExpiryClass(service)">
								{{ getDaysUntilExpiry(service.expirationDate) }}
							</div>
						</div>
					</div>
					<div class="list-item__actions">
						<div class="popover-menu-wrapper" @click.stop>
							<button
								class="action-button action-button--more"
								@click.stop="togglePopover(service.id)"
								:title="translate('domaincontrol', 'More options')"
							>
								<MaterialIcon name="more-vertical" :size="18" />
							</button>
							<div
								v-if="openPopover === service.id"
								class="popover-menu"
								@click.stop
							>
								<button
									class="popover-menu-item"
									@click="editService(service); closePopover()"
								>
									<MaterialIcon name="edit" :size="16" />
									{{ translate('domaincontrol', 'Edit') }}
								</button>
								<button
									v-if="service.renewalInterval !== 'one-time'"
									class="popover-menu-item"
									@click="showExtendModal(service); closePopover()"
								>
									<MaterialIcon name="calendar" :size="16" />
									{{ translate('domaincontrol', 'Extend') }}
								</button>
								<div class="popover-menu-separator"></div>
								<button
									class="popover-menu-item popover-menu-item--danger"
									@click="confirmDelete(service); closePopover()"
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

		<!-- Service Detail View -->
		<div v-else class="service-detail-view">
			<div class="detail-header">
				<button class="button-vue button-vue--tertiary" @click="backToList">
					<MaterialIcon name="arrow-left" :size="20" />
					{{ translate('domaincontrol', 'Back') }}
				</button>
				<h2 class="detail-title">{{ selectedService.name }}</h2>
				<div class="detail-actions">
					<button
						v-if="selectedService.renewalInterval !== 'one-time'"
						class="button-vue button-vue--success"
						@click="showExtendModal(selectedService)"
					>
						<MaterialIcon name="calendar" :size="20" />
						{{ translate('domaincontrol', 'Extend') }}
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
								@click="editService(selectedService); closeDetailPopover()"
							>
								<MaterialIcon name="edit" :size="16" />
								{{ translate('domaincontrol', 'Edit') }}
							</button>
							<div class="popover-menu-separator"></div>
							<button
								class="popover-menu-item popover-menu-item--danger"
								@click="confirmDelete(selectedService); closeDetailPopover()"
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
							<MaterialIcon name="contacts" :size="24" />
						</div>
						<div class="stat-card__content">
							<div class="stat-card__label">{{ translate('domaincontrol', 'Client') }}</div>
							<div class="stat-card__value">
								<a
									v-if="selectedService.clientId"
									href="#"
									@click.prevent="navigateToClient(selectedService.clientId)"
									class="link-primary"
								>
									{{ getClientName(selectedService.clientId) }}
								</a>
								<span v-else>-</span>
							</div>
						</div>
					</div>
					<div class="stat-card">
						<div class="stat-card__icon">
							<MaterialIcon name="accounting" :size="24" />
						</div>
						<div class="stat-card__content">
							<div class="stat-card__label">{{ translate('domaincontrol', 'Price') }}</div>
							<div class="stat-card__value">
								{{ formatCurrency(selectedService.price, selectedService.currency) }}
							</div>
						</div>
					</div>
					<div class="stat-card">
						<div class="stat-card__icon" :class="getExpiryClass(selectedService)">
							<MaterialIcon name="calendar" :size="24" />
						</div>
						<div class="stat-card__content">
							<div class="stat-card__label">{{ translate('domaincontrol', 'Expiration Date') }}</div>
							<div class="stat-card__value" :class="getExpiryClass(selectedService)">
								{{ formatDate(selectedService.expirationDate) || '-' }}
							</div>
						</div>
					</div>
					<div class="stat-card">
						<div class="stat-card__icon" :class="getServiceStatusClass(selectedService)">
							<MaterialIcon name="checkmark" :size="24" />
						</div>
						<div class="stat-card__content">
							<div class="stat-card__label">{{ translate('domaincontrol', 'Status') }}</div>
							<div class="stat-card__value">
								<span class="status-badge" :class="getServiceStatusClass(selectedService)">
									{{ getServiceStatusText(selectedService.status) }}
								</span>
							</div>
						</div>
					</div>
				</div>

				<!-- Info Grid -->
				<div class="detail-info-grid">
					<div class="detail-info-card">
						<h3 class="info-card-title">{{ translate('domaincontrol', 'Service Information') }}</h3>
						<table class="detail-table">
							<tbody>
								<tr>
									<td class="table-label">{{ translate('domaincontrol', 'Start Date') }}</td>
									<td class="table-value">{{ formatDate(selectedService.startDate) || '-' }}</td>
								</tr>
								<tr>
									<td class="table-label">{{ translate('domaincontrol', 'Renewal Interval') }}</td>
									<td class="table-value">
										{{ getRenewalIntervalText(selectedService.renewalInterval) || '-' }}
									</td>
								</tr>
								<tr>
									<td class="table-label">{{ translate('domaincontrol', 'Service Type') }}</td>
									<td class="table-value">
										{{ getServiceTypeName(selectedService.serviceTypeId) || '-' }}
									</td>
								</tr>
							</tbody>
						</table>
					</div>

					<div class="detail-info-card">
						<h3 class="info-card-title">{{ translate('domaincontrol', 'Notes') }}</h3>
						<div class="detail-notes">
							{{ selectedService.notes || translate('domaincontrol', 'No notes') }}
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
import ServiceModal from './ServiceModal.vue'
import ServiceExtendModal from './ServiceExtendModal.vue'
import ServiceTypeModal from './ServiceTypeModal.vue'

export default {
	name: 'Services',
	components: {
		MaterialIcon,
		ServiceModal,
		ServiceExtendModal,
	},
	data() {
		return {
			services: [],
			clients: [],
			serviceTypes: [],
			selectedService: null,
			loading: false,
			modalOpen: false,
			editingService: null,
			extendModalOpen: false,
			extendingService: null,
			searchQuery: '',
			openPopover: null,
			detailPopoverOpen: false,
		}
	},
	computed: {
		filteredServices() {
			if (!this.searchQuery) return this.services

			const query = this.searchQuery.toLowerCase()
			return this.services.filter(service => {
				const name = (service.name || '').toLowerCase()
				const clientName = this.getClientName(service.clientId) || ''
				return (
					name.includes(query) ||
					clientName.toLowerCase().includes(query)
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
					this.loadServices(),
					this.loadClients(),
					this.loadServiceTypes(),
				])
			} catch (error) {
				console.error('Error loading data:', error)
			} finally {
				this.loading = false
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
		async loadClients() {
			try {
				const response = await api.clients.getAll()
				this.clients = response.data || []
			} catch (error) {
				console.error('Error loading clients:', error)
				this.clients = []
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
		filterServices() {
			// Computed property handles filtering
		},
		selectService(service) {
			this.selectedService = service
		},
		backToList() {
			this.selectedService = null
		},
		showAddModal() {
			this.editingService = null
			this.modalOpen = true
		},
		editService(service) {
			this.editingService = service
			this.modalOpen = true
		},
		closeModal() {
			this.modalOpen = false
			this.editingService = null
		},
		async handleServiceSaved() {
			await this.loadServices()
			if (this.selectedService) {
				const response = await api.services.get(this.selectedService.id)
				this.selectedService = response.data
			}
			this.closeModal()
		},
		showExtendModal(service) {
			this.extendingService = service
			this.extendModalOpen = true
		},
		closeExtendModal() {
			this.extendModalOpen = false
			this.extendingService = null
		},
		async handleServiceExtended() {
			await this.loadServices()
			if (this.selectedService) {
				const response = await api.services.get(this.selectedService.id)
				this.selectedService = response.data
			}
			this.closeExtendModal()
		},
		async confirmDelete(service) {
			if (!confirm(this.translate('domaincontrol', 'Are you sure you want to delete this service?'))) {
				return
			}
			try {
				await api.services.delete(service.id)
				await this.loadServices()
				if (this.selectedService && this.selectedService.id === service.id) {
					this.backToList()
				}
			} catch (error) {
				console.error('Error deleting service:', error)
				alert(this.translate('domaincontrol', 'Error deleting service'))
			}
		},
		getClientName(clientId) {
			const client = this.clients.find(c => c.id === clientId)
			return client ? client.name : ''
		},
		getServiceTypeName(serviceTypeId) {
			const serviceType = this.serviceTypes.find(st => st.id === serviceTypeId)
			return serviceType ? serviceType.name : ''
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
		getServiceStatusClass(service) {
			return `status-${service.status || 'active'}`
		},
		getServiceStatusText(status) {
			const statusTexts = {
				active: this.translate('domaincontrol', 'Active'),
				paused: this.translate('domaincontrol', 'Paused'),
				cancelled: this.translate('domaincontrol', 'Cancelled'),
			}
			return statusTexts[status] || status
		},
		getRenewalIntervalText(interval) {
			const intervals = {
				'one-time': this.translate('domaincontrol', 'One-time'),
				'monthly': this.translate('domaincontrol', 'Monthly'),
				'quarterly': this.translate('domaincontrol', 'Quarterly'),
				'yearly': this.translate('domaincontrol', 'Yearly'),
			}
			return intervals[interval] || interval
		},
		getDaysUntilExpiry(expirationDate) {
			if (!expirationDate) return '-'
			const expiry = new Date(expirationDate)
			const today = new Date()
			today.setHours(0, 0, 0, 0)
			const diffTime = expiry - today
			const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24))
			
			if (diffDays < 0) {
				return `${Math.abs(diffDays)} ${this.translate('domaincontrol', 'days overdue')}`
			} else if (diffDays === 0) {
				return this.translate('domaincontrol', 'Today')
			} else {
				return `${diffDays} ${this.translate('domaincontrol', 'days left')}`
			}
		},
		getExpiryClass(service) {
			if (!service.expirationDate) return ''
			const expiry = new Date(service.expirationDate)
			const today = new Date()
			today.setHours(0, 0, 0, 0)
			const diffTime = expiry - today
			const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24))
			
			if (diffDays < 0) return 'status-critical'
			if (diffDays <= 7) return 'status-warning'
			return 'status-ok'
		},
		togglePopover(serviceId) {
			this.openPopover = this.openPopover === serviceId ? null : serviceId
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
				'Add Service': 'Hizmet Ekle',
				'Search services...': 'Hizmetlerde ara...',
				'No services found': 'Hizmet bulunamadı',
				'No services yet': 'Henüz hizmet yok',
				'Add First Service': 'İlk Hizmeti Ekle',
				'Loading services...': 'Hizmetler yükleniyor...',
				'Price': 'Fiyat',
				'Status': 'Durum',
				'Expiry': 'Bitiş',
				'More options': 'Daha fazla seçenek',
				'Edit': 'Düzenle',
				'Extend': 'Uzat',
				'Delete': 'Sil',
				'Back': 'Geri',
				'Client': 'Müşteri',
				'Expiration Date': 'Bitiş Tarihi',
				'Service Information': 'Hizmet Bilgileri',
				'Start Date': 'Başlangıç Tarihi',
				'Renewal Interval': 'Yenileme Periyodu',
				'Service Type': 'Hizmet Türü',
				'Notes': 'Notlar',
				'No notes': 'Not yok',
				'Active': 'Aktif',
				'Paused': 'Durduruldu',
				'Cancelled': 'İptal',
				'One-time': 'Tek Seferlik',
				'Monthly': 'Aylık',
				'Quarterly': '3 Aylık',
				'Yearly': 'Yıllık',
				'days overdue': 'gün gecikti',
				'Today': 'Bugün',
				'days left': 'gün kaldı',
				'Are you sure you want to delete this service?': 'Bu hizmeti silmek istediğinize emin misiniz?',
				'Error deleting service': 'Hizmet silinirken hata oluştu',
			}

			return translations[text] || text
		},
	},
}
</script>

<style scoped>
.services-view {
	width: 100%;
	height: 100%;
}

.services-list-view {
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

.service-search-wrapper {
	flex: 1;
	min-width: 200px;
}

.service-search-input {
	width: 100%;
	padding: 8px 12px;
	border: 1px solid var(--color-border);
	border-radius: var(--border-radius-element);
	background-color: var(--color-background-dark);
	color: var(--color-main-text);
	font-size: 14px;
}

.service-search-input:focus {
	outline: none;
	border-color: var(--color-primary-element);
}

.services-list {
	display: grid;
	gap: 12px;
}

.service-item {
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

.service-item:hover {
	background-color: var(--color-background-hover);
}

.service-item .list-item__avatar {
	width: 48px;
	height: 48px;
	border-radius: 50%;
	background-color: var(--color-background-dark);
	display: flex;
	align-items: center;
	justify-content: center;
	flex-shrink: 0;
}

.service-item .list-item__avatar .material-icon {
	color: var(--color-text-maxcontrast);
}

.service-item .list-item__content {
	flex: 1;
	min-width: 0;
}

.service-item .list-item__title {
	font-size: 16px;
	font-weight: 500;
	color: var(--color-main-text);
	margin-bottom: 4px;
}

.service-item .list-item__meta {
	display: flex;
	align-items: center;
	gap: 12px;
	font-size: 13px;
	color: var(--color-text-maxcontrast);
	flex-wrap: wrap;
}

.service-item .list-item__stats {
	display: flex;
	gap: 24px;
	align-items: center;
}

.service-item .list-item__stat {
	display: flex;
	flex-direction: column;
	gap: 4px;
	min-width: 100px;
}

.service-item .list-item__stat-label {
	font-size: 12px;
	color: var(--color-text-maxcontrast);
}

.service-item .list-item__stat-value {
	font-size: 14px;
	font-weight: 500;
	color: var(--color-main-text);
}

.service-item .list-item__actions {
	display: flex;
	align-items: center;
	gap: 8px;
	flex-shrink: 0;
}

.service-detail-view {
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

.status-active {
	background-color: var(--color-success);
	color: var(--color-success-text);
}

.status-paused {
	background-color: var(--color-warning);
	color: var(--color-warning-text);
}

.status-cancelled {
	background-color: var(--color-text-maxcontrast);
	color: var(--color-main-background);
	opacity: 0.6;
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
