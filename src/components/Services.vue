<template>
	<div class="app-content-wrapper">
		<!-- ========================================== -->
		<!-- MODALS                                     -->
		<!-- ========================================== -->
		<ServiceModal
			:open="modalOpen"
			:service="editingService"
			:clients="clients"
			:service-types="serviceTypes"
			@close="closeModal"
			@saved="handleServiceSaved"
		/>

		<ServiceExtendModal
			:open="extendModalOpen"
			:service="extendingService"
			@close="closeExtendModal"
			@extended="handleServiceExtended"
		/>

		<ServiceTypeModal
			:open="serviceTypeModalOpen"
			:service-type="editingServiceType"
			@close="closeServiceTypeModal"
			@saved="handleServiceTypeSaved"
		/>

		<!-- ========================================== -->
		<!-- SERVICE TYPES VIEW (Split-Pane)            -->
		<!-- ========================================== -->
		<template v-if="showServiceTypesView">
			<!-- LEFT PANE: SERVICE TYPES LIST -->
			<div class="app-content-list" :class="{ 'mobile-hidden': isMobile && selectedServiceType }">
				<div class="app-content-list-header">
					<div class="search-wrapper">
						<div class="search-wrapper-inner">
							<Magnify :size="20" class="search-icon" />
							<input
								v-model="typeSearchQuery"
								type="text"
								:placeholder="translate('domaincontrol', 'Hizmet türü ara...')"
								class="search-input"
							>
						</div>
					</div>
					<div class="app-navigation__search">
						<header class="header">
							<div class="import-and-new-contact-buttons">
								<NcButton
									type="secondary"
									:wide="true"
									@click="showServiceTypesView = false"
								>
									<template #icon>
										<ArrowLeft :size="20" />
									</template>
									{{ translate('domaincontrol', 'Geri') }}
								</NcButton>
								<NcButton
									type="secondary"
									:wide="true"
									@click="showServiceTypeModal()"
								>
									<template #icon>
										<Plus :size="20" />
									</template>
									{{ translate('domaincontrol', 'Yeni Tür') }}
								</NcButton>
							</div>
						</header>
					</div>
				</div>

				<div class="app-content-list-wrapper">
					<div v-if="serviceTypesLoading" class="loading-container">
						<Refresh :size="32" class="spin-animation" />
					</div>
					<div v-else-if="filteredServiceTypes.length === 0" class="empty-list">
						<div class="empty-text">
							{{ translate('domaincontrol', 'Hizmet türü bulunamadı') }}
						</div>
					</div>
					<ul v-else class="app-navigation-list">
						<li
							v-for="type in filteredServiceTypes"
							:key="type.id"
							class="app-navigation-entry"
							:class="{ 'active': selectedServiceType && selectedServiceType.id === type.id }"
							@click="selectServiceType(type)"
						>
							<div class="app-navigation-entry-icon">
								<div class="avatar-circle package-avatar" :style="{ backgroundColor: getCategoryColor(type.name) }">
									<Tag :size="20" />
								</div>
							</div>
							<div class="app-navigation-entry-content">
								<div class="app-navigation-entry-name">
									{{ type.name || '-' }}
								</div>
								<div class="app-navigation-entry-details">
									<span v-if="type.defaultPrice">{{ formatCurrency(type.defaultPrice, type.defaultCurrency) }}</span>
									<span v-else>{{ translate('domaincontrol', 'Hizmet Türü') }}</span>
								</div>
							</div>
						</li>
					</ul>
				</div>
			</div>

			<!-- RIGHT PANE: SERVICE TYPE DETAIL -->
			<div class="app-content-details" :class="{ 'mobile-hidden': isMobile && !selectedServiceType }">
				<!-- Empty State -->
				<div v-if="!selectedServiceType" class="empty-content">
					<div class="empty-content-icon">
						<Tag :size="64" />
					</div>
					<h2 class="empty-content-title">
						{{ translate('domaincontrol', 'Bir hizmet türü seçin') }}
					</h2>
					<NcButton
						v-if="serviceTypes.length === 0"
						type="primary"
						@click="initPredefinedTypes"
					>
						{{ translate('domaincontrol', 'Hazır Türleri Yükle') }}
					</NcButton>
				</div>

				<!-- Detail Content -->
				<div v-else class="crm-detail-container">
					<!-- HEADER -->
					<div class="crm-header">
						<div class="crm-header-top">
							<button v-if="isMobile" class="icon-button back-button" @click="selectedServiceType = null">
								<ArrowLeft :size="24" />
							</button>
							<div class="crm-profile-info">
								<div class="avatar-xl package-avatar-xl" :style="{ backgroundColor: getCategoryColor(selectedServiceType.name) }">
									<Tag :size="36" />
								</div>
								<div class="crm-profile-text">
									<h1 class="crm-client-name">
										{{ selectedServiceType.name || '-' }}
									</h1>
									<div class="crm-client-meta">
										<span v-if="selectedServiceType.renewalInterval" class="meta-item">
											<span>{{ getRenewalIntervalText(selectedServiceType.renewalInterval) }}</span>
										</span>
									</div>
								</div>
							</div>
							<div class="crm-header-actions">
								<NcButton @click="editServiceType(selectedServiceType)">
									{{ translate('domaincontrol', 'Düzenle') }}
								</NcButton>
								<button class="icon-button danger" @click="confirmDeleteServiceType(selectedServiceType)">
									<Delete :size="20" />
								</button>
							</div>
						</div>
					</div>

					<!-- CONTENT SCROLL AREA -->
					<div class="crm-content-scroll">
						<!-- Stats Grid -->
						<div class="stats-grid">
							<div class="stat-card">
								<div class="stat-icon" style="background-color: rgba(70, 186, 97, 0.1);">
									<CurrencyUsd :size="24" style="color: var(--color-element-success);" />
								</div>
								<div class="stat-content">
									<div class="stat-label">
										{{ translate('domaincontrol', 'Varsayılan Fiyat') }}
									</div>
									<div class="stat-value">
										{{ selectedServiceType.defaultPrice ? formatCurrency(selectedServiceType.defaultPrice, selectedServiceType.defaultCurrency) : '-' }}
									</div>
								</div>
							</div>

							<div class="stat-card">
								<div class="stat-icon" style="background-color: rgba(0, 130, 201, 0.1);">
									<CogOutline :size="24" style="color: var(--color-primary-element);" />
								</div>
								<div class="stat-content">
									<div class="stat-label">
										{{ translate('domaincontrol', 'Aktif Hizmetler') }}
									</div>
									<div class="stat-value">
										{{ getServicesOfStatus(selectedServiceType.id, 'active').length }}
									</div>
								</div>
							</div>
						</div>

						<!-- Information -->
						<div class="content-box">
							<div class="box-header">
								<h3>{{ translate('domaincontrol', 'Hizmet Türü Bilgileri') }}</h3>
							</div>
							<div class="box-body">
								<div class="info-grid">
									<div class="info-group">
										<div class="info-label">
											{{ translate('domaincontrol', 'Tür Adı') }}
										</div>
										<div class="info-val">
											{{ selectedServiceType.name || '-' }}
										</div>
									</div>
									<div v-if="selectedServiceType.description" class="info-group">
										<div class="info-label">
											{{ translate('domaincontrol', 'Açıklama') }}
										</div>
										<div class="info-val">
											{{ selectedServiceType.description }}
										</div>
									</div>
									<div class="info-group">
										<div class="info-label">
											{{ translate('domaincontrol', 'Yenileme Aralığı') }}
										</div>
										<div class="info-val">
											{{ getRenewalIntervalText(selectedServiceType.renewalInterval) }}
										</div>
									</div>
								</div>
							</div>
						</div>

						<!-- Linked Services -->
						<div class="content-box">
							<div class="box-header">
								<h3>
									{{ translate('domaincontrol', 'Bu Türdeki Hizmetler') }}
									<span v-if="getServicesByType(selectedServiceType.id).length" class="tab-badge">{{ getServicesByType(selectedServiceType.id).length }}</span>
								</h3>
							</div>
							<div class="box-body">
								<div v-if="getServicesByType(selectedServiceType.id).length === 0" class="empty-box">
									{{ translate('domaincontrol', 'Bu türde henüz hizmet yok') }}
								</div>
								<div v-else class="linked-items-list">
									<div
										v-for="service in getServicesByType(selectedServiceType.id)"
										:key="service.id"
										class="linked-item"
										@click="navigateToService(service.id)"
									>
										<div class="linked-item-icon">
											<CogOutline :size="24" />
										</div>
										<div class="linked-item-info">
											<div class="linked-item-name">
												{{ service.name || '-' }}
											</div>
											<div class="linked-item-meta">
												<span v-if="getClientName(service.clientId)">{{ getClientName(service.clientId) }}</span>
												<span v-else>-</span>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</template>

		<!-- ========================================== -->
		<!-- SERVICE LIST & DETAIL VIEW (Split-Pane)   -->
		<!-- ========================================== -->
		<template v-else>
			<!-- LEFT PANE: LIST -->
			<div class="app-content-list" :class="{ 'mobile-hidden': isMobile && selectedService }">
				<div class="app-content-list-header">
					<div class="search-wrapper">
						<div class="search-wrapper-inner">
							<Magnify :size="20" class="search-icon" />
							<input
								v-model="searchQuery"
								type="text"
								:placeholder="translate('domaincontrol', 'Hizmet ara...')"
								class="search-input"
							>
						</div>
					</div>
					<div class="app-navigation__search">
						<header class="header">
							<div class="import-and-new-contact-buttons">
								<NcButton
									type="secondary"
									:wide="true"
									@click="showServiceTypesView = true"
								>
									<template #icon>
										<Tag :size="20" />
									</template>
									{{ translate('domaincontrol', 'Türler') }}
								</NcButton>
								<NcButton
									type="secondary"
									:wide="true"
									@click="showAddModal"
								>
									<template #icon>
										<Plus :size="20" />
									</template>
									{{ translate('domaincontrol', 'Yeni Hizmet') }}
								</NcButton>
							</div>
						</header>
					</div>
				</div>

				<div class="app-content-list-wrapper">
					<div v-if="loading" class="loading-container">
						<Refresh :size="32" class="spin-animation" />
					</div>
					<div v-else-if="filteredServices.length === 0" class="empty-list">
						<div class="empty-text">
							{{ translate('domaincontrol', 'Hizmet bulunamadı') }}
						</div>
					</div>
					<ul v-else class="app-navigation-list">
						<li
							v-for="service in filteredServices"
							:key="service.id"
							class="app-navigation-entry"
							:class="{ 'active': selectedService && selectedService.id === service.id }"
							@click="selectService(service)"
						>
							<div class="app-navigation-entry-icon">
								<div class="avatar-circle hosting-avatar" :style="{ backgroundColor: getCategoryColor(getServiceTypeName(service.serviceTypeId)) }">
									{{ (service.name || 'S').substring(0, 2).toUpperCase() }}
								</div>
							</div>
							<div class="app-navigation-entry-content">
								<div class="app-navigation-entry-name">
									{{ service.name || '-' }}
									<span v-if="service.serviceTypeId" class="plan-tag-small">{{ getServiceTypeName(service.serviceTypeId) }}</span>
								</div>
								<div class="app-navigation-entry-details">
									<span v-if="getClientName(service.clientId)">{{ getClientName(service.clientId) }}</span>
									<span v-else>{{ translate('domaincontrol', 'Hizmet') }}</span>
								</div>
							</div>
							<div class="app-navigation-entry-status">
								<span class="status-dot-small" :class="getServiceStatusClass(service)"></span>
							</div>
						</li>
					</ul>
				</div>
			</div>

			<!-- RIGHT PANE: DETAIL VIEW -->
			<div class="app-content-details" :class="{ 'mobile-hidden': isMobile && !selectedService }">
				<!-- Empty State -->
				<div v-if="!selectedService" class="empty-content">
					<div class="empty-content-icon">
						<CogOutline :size="64" />
					</div>
					<h2 class="empty-content-title">
						{{ translate('domaincontrol', 'Bir hizmet seçin') }}
					</h2>
				</div>

				<!-- Detail Content -->
				<div v-else class="crm-detail-container">
					<!-- HEADER -->
					<div class="crm-header">
						<div class="crm-header-top">
							<button v-if="isMobile" class="icon-button back-button" @click="backToList">
								<ArrowLeft :size="24" />
							</button>
							<div class="crm-profile-info">
								<div class="avatar-xl hosting-avatar-xl" :style="{ backgroundColor: getCategoryColor(getServiceTypeName(selectedService.serviceTypeId)) }">
									{{ (selectedService.name || 'S').substring(0, 1).toUpperCase() }}
								</div>
								<div class="crm-profile-text">
									<h1 class="crm-client-name">
										{{ selectedService.name || '-' }}
									</h1>
									<div class="crm-client-meta">
										<span v-if="selectedService.serviceTypeId" class="meta-item">
											<span>{{ getServiceTypeName(selectedService.serviceTypeId) }}</span>
										</span>
										<span v-if="getClientName(selectedService.clientId)" class="meta-item">
											<Account :size="14" />
											<span>{{ getClientName(selectedService.clientId) }}</span>
										</span>
										<span class="meta-item">
											<span class="status-badge" :class="getServiceStatusBadgeClass(selectedService)">
												{{ getServiceStatusText(selectedService.status) }}
											</span>
										</span>
									</div>
								</div>
							</div>
							<div class="crm-header-actions">
								<NcButton
									v-if="selectedService.renewalInterval !== 'one-time'"
									type="success"
									@click="showExtendModal(selectedService)"
								>
									<template #icon>
										<CalendarSync :size="18" />
									</template>
									{{ translate('domaincontrol', 'Uzat') }}
								</NcButton>
								<NcButton @click="editService(selectedService)">
									{{ translate('domaincontrol', 'Düzenle') }}
								</NcButton>
								<button class="icon-button danger" @click="confirmDelete(selectedService)">
									<Delete :size="20" />
								</button>
							</div>
						</div>

						<!-- TABS -->
						<div class="crm-tabs-scroll">
							<div class="crm-tabs">
								<button class="crm-tab" :class="{ active: activeTab === 'overview' }" @click="activeTab = 'overview'">
									{{ translate('domaincontrol', 'Genel Bakış') }}
								</button>
							</div>
						</div>
					</div>

					<!-- CONTENT SCROLL AREA -->
					<div class="crm-content-scroll">
						<!-- 1. GENEL BAKIŞ -->
						<div v-if="activeTab === 'overview'" class="tab-pane">
							<!-- Stats Grid -->
							<div class="stats-grid">
								<div class="stat-card">
									<div class="stat-icon" style="background-color: rgba(0, 130, 201, 0.1);">
										<CalendarClock :size="24" style="color: var(--color-primary-element);" />
									</div>
									<div class="stat-content">
										<div class="stat-label">
											{{ translate('domaincontrol', 'Süre Sonu') }}
										</div>
										<div class="stat-value">
											{{ formatDate(selectedService.expirationDate) || '-' }}
										</div>
									</div>
								</div>

								<div class="stat-card">
									<div class="stat-icon" :style="{ backgroundColor: getDaysNumber(selectedService.expirationDate) <= 30 ? 'rgba(233, 50, 45, 0.1)' : 'rgba(70, 186, 97, 0.1)' }">
										<Timelapse :size="24" :style="{ color: getDaysNumber(selectedService.expirationDate) <= 30 ? 'var(--color-element-error)' : 'var(--color-element-success)' }" />
									</div>
									<div class="stat-content">
										<div class="stat-label">
											{{ translate('domaincontrol', 'Kalan Gün') }}
										</div>
										<div class="stat-value" :class="getDaysNumber(selectedService.expirationDate) <= 30 ? 'text-error' : 'text-success'">
											{{ getDaysUntilExpiry(selectedService.expirationDate) }}
										</div>
									</div>
								</div>

								<div class="stat-card">
									<div class="stat-icon" style="background-color: rgba(70, 186, 97, 0.1);">
										<CurrencyUsd :size="24" style="color: var(--color-element-success);" />
									</div>
									<div class="stat-content">
										<div class="stat-label">
											{{ translate('domaincontrol', 'Tutar') }}
										</div>
										<div class="stat-value">
											{{ formatCurrency(selectedService.price, selectedService.currency) || '-' }}
										</div>
									</div>
								</div>
							</div>

							<!-- General Information -->
							<div class="content-box">
								<div class="box-header">
									<h3>{{ translate('domaincontrol', 'Hizmet Detayları') }}</h3>
								</div>
								<div class="box-body">
									<div class="info-grid">
										<div class="info-group">
											<div class="info-label">
												{{ translate('domaincontrol', 'Müşteri') }}
											</div>
											<div class="info-val">
												<a
													v-if="selectedService.clientId && getClientName(selectedService.clientId)"
													href="#"
													class="info-link"
													@click.prevent="navigateToClient(selectedService.clientId)"
												>
													<Account :size="14" />
													{{ getClientName(selectedService.clientId) }}
												</a>
												<span v-else>-</span>
											</div>
										</div>
										<div v-if="selectedService.startDate" class="info-group">
											<div class="info-label">
												{{ translate('domaincontrol', 'Başlangıç Tarihi') }}
											</div>
											<div class="info-val">
												{{ formatDate(selectedService.startDate) }}
											</div>
										</div>
										<div v-if="selectedService.renewalInterval" class="info-group">
											<div class="info-label">
												{{ translate('domaincontrol', 'Döngü') }}
											</div>
											<div class="info-val">
												{{ getRenewalIntervalText(selectedService.renewalInterval) }}
											</div>
										</div>
										<div v-if="selectedService.serviceTypeId" class="info-group">
											<div class="info-label">
												{{ translate('domaincontrol', 'Hizmet Türü') }}
											</div>
											<div class="info-val">
												{{ getServiceTypeName(selectedService.serviceTypeId) }}
											</div>
										</div>
									</div>
								</div>
							</div>

							<!-- Notes -->
							<div v-if="selectedService.notes" class="content-box">
								<div class="box-header">
									<h3>
										<NoteText :size="18" class="inline-icon" />
										{{ translate('domaincontrol', 'Notlar') }}
									</h3>
								</div>
								<div class="box-body">
									<div class="detail-notes">
										{{ selectedService.notes }}
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</template>
	</div>
</template>

<script>
import { NcButton } from '@nextcloud/vue'
import api from '../services/api'
import ServiceModal from './ServiceModal.vue'
import ServiceExtendModal from './ServiceExtendModal.vue'
import ServiceTypeModal from './ServiceTypeModal.vue'

// Icons
import CogOutline from 'vue-material-design-icons/CogOutline.vue'
import Magnify from 'vue-material-design-icons/Magnify.vue'
import Plus from 'vue-material-design-icons/Plus.vue'
import Refresh from 'vue-material-design-icons/Refresh.vue'
import ArrowLeft from 'vue-material-design-icons/ArrowLeft.vue'
import Pencil from 'vue-material-design-icons/Pencil.vue'
import Delete from 'vue-material-design-icons/Delete.vue'
import Tag from 'vue-material-design-icons/Tag.vue'
import Account from 'vue-material-design-icons/Account.vue'
import CalendarClock from 'vue-material-design-icons/CalendarClock.vue'
import Timelapse from 'vue-material-design-icons/Timelapse.vue'
import CurrencyUsd from 'vue-material-design-icons/CurrencyUsd.vue'
import CalendarSync from 'vue-material-design-icons/CalendarSync.vue'
import NoteText from 'vue-material-design-icons/NoteText.vue'

export default {
	name: 'Services',
	components: {
		NcButton,
		ServiceModal,
		ServiceExtendModal,
		ServiceTypeModal,
		CogOutline, Magnify, Plus, Refresh, ArrowLeft, Pencil, Delete,
		Tag, Account, CalendarClock, Timelapse,
		CurrencyUsd, CalendarSync, NoteText,
	},
	data() {
		return {
			services: [],
			serviceTypes: [],
			clients: [],
			selectedService: null,
			selectedServiceType: null,
			searchQuery: '',
			typeSearchQuery: '',
			loading: false,
			serviceTypesLoading: false,
			isMobile: window.innerWidth < 768,
			activeTab: 'overview',
			modalOpen: false,
			editingService: null,
			extendModalOpen: false,
			extendingService: null,
			showServiceTypesView: false,
			serviceTypeModalOpen: false,
			editingServiceType: null,
		}
	},
	computed: {
		filteredServices() {
			if (!this.searchQuery) return this.services
			const query = this.searchQuery.toLowerCase()
			return this.services.filter(service => {
				const name = (service.name || '').toLowerCase()
				const clientName = (this.getClientName(service.clientId) || '').toLowerCase()
				const typeName = (this.getServiceTypeName(service.serviceTypeId) || '').toLowerCase()
				return name.includes(query) || clientName.includes(query) || typeName.includes(query)
			})
		},
		filteredServiceTypes() {
			if (!this.typeSearchQuery) return this.serviceTypes
			const query = this.typeSearchQuery.toLowerCase()
			return this.serviceTypes.filter(type => {
				return (type.name || '').toLowerCase().includes(query) ||
                       (type.description || '').toLowerCase().includes(query)
			})
		},
	},
	watch: {
		showServiceTypesView(newVal) {
			if (newVal) {
				this.loadServiceTypes()
				this.selectedServiceType = null
			} else {
				this.selectedService = null
			}
		},
	},
	mounted() {
		this.loadData()
		window.addEventListener('resize', this.handleResize)
	},
	beforeUnmount() {
		window.removeEventListener('resize', this.handleResize)
	},
	methods: {
		handleResize() {
			this.isMobile = window.innerWidth < 768
		},
		translate(appId, text, vars) {
			try {
				if (typeof window !== 'undefined') {
					if (typeof OC !== 'undefined' && OC.L10n && typeof OC.L10n.translate === 'function') {
						const translated = OC.L10n.translate(appId, text, vars || {})
						if (translated && translated !== text) return translated
					}
					if (typeof window.t === 'function') {
						const translated = window.t(appId, text, vars || {})
						if (translated && translated !== text) return translated
					}
				}
			} catch (e) { console.warn('Translation error:', e) }
			return text
		},
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
			this.serviceTypesLoading = true
			try {
				const response = await api.serviceTypes.getAll()
				this.serviceTypes = response.data || []
			} catch (error) {
				console.error('Error loading service types:', error)
				this.serviceTypes = []
			} finally {
				this.serviceTypesLoading = false
			}
		},
		selectService(service) {
			this.selectedService = service
			this.activeTab = 'overview'
		},
		backToList() {
			this.selectedService = null
		},
		selectServiceType(type) {
			this.selectedServiceType = type
		},
		navigateToService(serviceId) {
			this.showServiceTypesView = false
			this.$nextTick(() => {
				const service = this.services.find(s => s.id === serviceId)
				if (service) {
					this.selectService(service)
				}
			})
		},
		getServicesByType(typeId) {
			return this.services.filter(s => s.serviceTypeId == typeId || s.service_type_id == typeId)
		},
		getServicesOfStatus(typeId, status) {
			return this.getServicesByType(typeId).filter(s => (s.status || 'active') === status)
		},
		getCategoryColor(name) {
			if (!name) return '#0082c9'
			const colors = ['#0082c9', '#46ba61', '#f0ad4e', '#e3322d', '#5bc0de', '#9b59b6', '#e67e22', '#3498db']
			let hash = 0
			for (let i = 0; i < name.length; i++) {
				hash = name.charCodeAt(i) + ((hash << 5) - hash)
			}
			return colors[Math.abs(hash) % colors.length]
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
			if (!confirm(this.translate('domaincontrol', 'Bu hizmeti silmek istediğinizden emin misiniz?'))) {
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
			}
		},
		showServiceTypeModal() {
			this.editingServiceType = null
			this.serviceTypeModalOpen = true
		},
		editServiceType(type) {
			this.editingServiceType = type
			this.serviceTypeModalOpen = true
		},
		closeServiceTypeModal() {
			this.serviceTypeModalOpen = false
			this.editingServiceType = null
		},
		async handleServiceTypeSaved() {
			await this.loadServiceTypes()
			if (this.selectedServiceType) {
				const response = await api.serviceTypes.get(this.selectedServiceType.id)
				this.selectedServiceType = response.data
			}
			this.closeServiceTypeModal()
		},
		async confirmDeleteServiceType(type) {
			if (!confirm(this.translate('domaincontrol', 'Bu hizmet türünü silmek istediğinizden emin misiniz?'))) {
				return
			}
			try {
				await api.serviceTypes.delete(type.id)
				await this.loadServiceTypes()
				this.selectedServiceType = null
			} catch (error) {
				console.error('Error deleting service type:', error)
			}
		},
		async initPredefinedTypes() {
			try {
				await api.serviceTypes.initPredefined()
				await this.loadServiceTypes()
			} catch (error) {
				console.error('Error initializing predefined types:', error)
			}
		},
		getClientName(clientId) {
			const client = this.clients.find(c => c.id == clientId)
			return client ? client.name : ''
		},
		getServiceTypeName(typeId) {
			const type = this.serviceTypes.find(t => t.id == typeId)
			return type ? type.name : ''
		},
		formatDate(date) {
			if (!date) return ''
			return new Date(date).toLocaleDateString('tr-TR')
		},
		formatCurrency(amount, currency = 'USD') {
			if (amount === null || amount === undefined) return '-'
			return new Intl.NumberFormat('tr-TR', {
				style: 'currency',
				currency: currency || 'USD',
			}).format(amount)
		},
		getRenewalIntervalText(interval) {
			const intervals = {
				'one-time': this.translate('domaincontrol', 'Tek Seferlik'),
				'monthly': this.translate('domaincontrol', 'Aylık'),
				'quarterly': this.translate('domaincontrol', '3 Aylık'),
				'yearly': this.translate('domaincontrol', 'Yıllık'),
			}
			return intervals[interval] || interval
		},
		getDaysNumber(date) {
			if (!date) return 999
			const diff = new Date(date) - new Date()
			return Math.ceil(diff / (1000 * 60 * 60 * 24))
		},
		getDaysUntilExpiry(date) {
			const days = this.getDaysNumber(date)
			if (days === 999) return '-'
			if (days < 0) return `${Math.abs(days)} ${this.translate('domaincontrol', 'gün geçti')}`
			if (days === 0) return this.translate('domaincontrol', 'Bugün')
			return `${days} ${this.translate('domaincontrol', 'gün kaldı')}`
		},
		getServiceStatusClass(service) {
			const days = this.getDaysNumber(service.expirationDate)
			if ((service.status || 'active') !== 'active') return 'status-expired'
			if (days < 0) return 'status-expired'
			if (days <= 7) return 'status-warning'
			return 'status-ok'
		},
		getServiceStatusBadgeClass(service) {
			const days = this.getDaysNumber(service.expirationDate)
			if ((service.status || 'active') !== 'active') return 'badge-neutral'
			if (days < 0) return 'badge-neutral'
			return 'badge-success'
		},
		getServiceStatusText(status) {
			const statusTexts = {
				active: this.translate('domaincontrol', 'Aktif'),
				paused: this.translate('domaincontrol', 'Duraklatıldı'),
				cancelled: this.translate('domaincontrol', 'İptal Edildi'),
			}
			return statusTexts[status] || status
		},
		navigateToClient(clientId) {
			if (typeof window.DomainControl !== 'undefined' && window.DomainControl.switchTab) {
				window.DomainControl.switchTab('clients')
				setTimeout(() => {
					window.dispatchEvent(new CustomEvent('select-client', { detail: { clientId } }))
				}, 100)
			}
		},
	},
}
</script>

<style scoped>
/* ========================================== */
/* CORE LAYOUT (Split-Pane)                   */
/* ========================================== */
.app-content-wrapper {
    display: flex;
    height: 100%;
    width: 100%;
    overflow: hidden;
    background-color: var(--color-main-background);
}

.app-content-list {
    width: 350px;
    flex-shrink: 0;
    border-right: 1px solid var(--color-border);
    display: flex;
    flex-direction: column;
    background-color: var(--color-main-background);
    z-index: 10;
}

.app-content-details {
    flex: 1;
    min-width: 0;
    display: flex;
    flex-direction: column;
    background-color: var(--color-background-hover);
    position: relative;
}

/* ========================================== */
/* LIST PANE STYLES                           */
/* ========================================== */
.app-content-list-header {
    padding: 0;
    display: flex;
    flex-direction: column;
    border-bottom: 1px solid var(--color-border);
}

.search-wrapper {
    position: relative;
    padding: 12px 16px;
    border-bottom: 1px solid var(--color-border);
}

.search-wrapper-inner {
    margin-left: 24px;
}

.search-icon {
    position: absolute;
    left: 45px;
    top: 50%;
    transform: translateY(-50%);
    color: var(--color-text-maxcontrast);
    pointer-events: none;
    opacity: 0.5;
}

.search-input {
    width: 100%;
    padding: 8px 12px 8px 34px !important;
    border: 1px solid transparent !important;
    border-radius: 8px !important;
    background-color: var(--color-background-dark) !important;
    font-size: 14px;
    transition: all 0.2s ease;
}

.search-input:focus {
    background-color: var(--color-main-background) !important;
    border-color: var(--color-primary-element) !important;
    outline: none;
    box-shadow: 0 0 0 2px var(--color-primary-element-light);
}

.header {
    padding: 12px 16px;
}

.app-navigation-list {
    list-style: none;
    padding: 0;
    margin: 0;
    overflow-y: auto;
    flex: 1;
}

.app-navigation-entry {
    display: flex;
    align-items: center;
    padding: 12px 16px;
    cursor: pointer;
    border-bottom: 1px solid var(--color-border-hover);
    transition: background-color 0.1s ease;
}

.app-navigation-entry:hover {
    background-color: var(--color-background-hover);
}

.app-navigation-entry.active {
    background-color: var(--color-background-hover);
    border-left: 4px solid var(--color-primary-element);
    padding-left: 12px;
}

.app-navigation-entry-icon {
    margin-right: 12px;
    flex-shrink: 0;
}

.avatar-circle {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-weight: 600;
    font-size: 14px;
}

.app-navigation-entry-content {
    flex: 1;
    min-width: 0;
}

.app-navigation-entry-name {
    font-weight: 600;
    font-size: 14px;
    color: var(--color-main-text);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    display: flex;
    align-items: center;
    gap: 6px;
}

.plan-tag-small {
    font-size: 10px;
    background: var(--color-background-dark);
    padding: 1px 6px;
    border-radius: 4px;
    font-weight: normal;
    color: var(--color-text-maxcontrast);
}

.app-navigation-entry-details {
    font-size: 12px;
    color: var(--color-text-maxcontrast);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.status-dot-small {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    display: inline-block;
}

.status-ok { background-color: var(--color-element-success); }
.status-warning { background-color: var(--color-element-warning); }
.status-expired { background-color: var(--color-element-error); }

/* ========================================== */
/* DETAIL PANE STYLES                         */
/* ========================================== */
.empty-content {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    height: 100%;
    color: var(--color-text-maxcontrast);
    padding: 40px;
    text-align: center;
}

.empty-content-icon {
    margin-bottom: 20px;
    opacity: 0.3;
}

.empty-content-title {
    font-size: 20px;
    font-weight: 600;
    margin-bottom: 20px;
}

.crm-detail-container {
    display: flex;
    flex-direction: column;
    height: 100%;
    overflow: hidden;
}

.crm-header {
    padding: 24px 32px 0 32px;
    border-bottom: 1px solid var(--color-border);
    background-color: var(--color-main-background);
    flex-shrink: 0;
}

.crm-header-top {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    margin-bottom: 24px;
    gap: 16px;
}

.crm-profile-info {
    display: flex;
    align-items: center;
    gap: 20px;
    flex: 1;
    min-width: 0;
}

.avatar-xl {
    width: 80px;
    height: 80px;
    border-radius: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-size: 32px;
    font-weight: 700;
    flex-shrink: 0;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.crm-profile-text {
    flex: 1;
    min-width: 0;
}

.crm-client-name {
    font-size: 28px;
    font-weight: 700;
    margin: 0 0 8px 0;
    color: var(--color-main-text);
    line-height: 1.2;
}

.crm-client-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    align-items: center;
}

.meta-item {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 14px;
    color: var(--color-text-maxcontrast);
}

.crm-header-actions {
    display: flex;
    gap: 8px;
    align-items: center;
}

.icon-button {
    width: 36px;
    height: 36px;
    border-radius: 10px;
    border: 1px solid var(--color-border);
    background: var(--color-main-background);
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    color: var(--color-text-maxcontrast);
    transition: all 0.2s;
}

.icon-button:hover {
    background: var(--color-background-hover);
    color: var(--color-main-text);
}

.icon-button.danger:hover {
    background: var(--color-element-error);
    color: #fff;
    border-color: var(--color-element-error);
}

/* Tabs */
.crm-tabs-scroll {
    overflow-x: auto;
    scrollbar-width: none;
}

.crm-tabs {
    display: flex;
    gap: 32px;
}

.crm-tab {
    padding: 12px 4px;
    background: none;
    border: none;
    border-bottom: 3px solid transparent;
    font-size: 14px;
    font-weight: 600;
    color: var(--color-text-maxcontrast);
    cursor: pointer;
    white-space: nowrap;
    transition: all 0.2s;
}

.crm-tab:hover {
    color: var(--color-main-text);
}

.crm-tab.active {
    color: var(--color-primary-element);
    border-bottom-color: var(--color-primary-element);
}

.tab-badge {
    background: var(--color-background-dark);
    color: var(--color-text-maxcontrast);
    padding: 1px 6px;
    border-radius: 10px;
    font-size: 11px;
    margin-left: 4px;
}

/* Content Area */
.crm-content-scroll {
    flex: 1;
    overflow-y: auto;
    padding: 24px;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 16px;
    margin-bottom: 24px;
}

.stat-card {
    background: var(--color-main-background);
    border: 1px solid var(--color-border);
    border-radius: var(--border-radius-large);
    padding: 16px;
    display: flex;
    align-items: center;
    gap: 12px;
}

.stat-icon {
    width: 48px;
    height: 48px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.stat-label {
    font-size: 12px;
    color: var(--color-text-maxcontrast);
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 4px;
}

.stat-value {
    font-size: 18px;
    font-weight: 700;
    color: var(--color-main-text);
}

/* Content Boxes */
.content-box {
    background: var(--color-main-background);
    border: 1px solid var(--color-border);
    border-radius: var(--border-radius-large);
    overflow: hidden;
}
.box-header {
    padding: 12px 20px;
    background: var(--color-background-hover);
    border-bottom: 1px solid var(--color-border);
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.box-header h3 {
    margin: 0;
    font-size: 16px;
    font-weight: 700;
}

.box-body {
    padding: 24px;
}

.info-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 24px;
}

.info-label {
    font-size: 12px;
    color: var(--color-text-maxcontrast);
    margin-bottom: 6px;
}

.info-val {
    font-size: 15px;
    color: var(--color-main-text);
    font-weight: 500;
}

.info-link {
    color: var(--color-primary-element);
    text-decoration: none;
    display: flex;
    align-items: center;
    gap: 6px;
}

.info-link:hover {
    text-decoration: underline;
}

.detail-notes {
    white-space: pre-wrap;
    font-size: 14px;
    color: var(--color-main-text);
    line-height: 1.6;
}

/* Linked Items */
.linked-items-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.linked-item {
    background: var(--color-main-background);
    border: 1px solid var(--color-border);
    border-radius: var(--border-radius-large);
    padding: 16px;
    display: flex;
    align-items: center;
    gap: 16px;
    cursor: pointer;
    transition: all 0.15s ease;
}

.linked-item:hover {
    background: var(--color-background-hover);
    border-color: var(--color-primary-element);
}

.linked-item-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    background: var(--color-background-dark);
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--color-primary-element);
}

.linked-item-info {
    flex: 1;
    min-width: 0;
}

.linked-item-name {
    font-weight: 600;
    font-size: 15px;
    color: var(--color-main-text);
    margin-bottom: 4px;
}

.linked-item-meta {
    font-size: 13px;
    color: var(--color-text-maxcontrast);
}

.empty-box {
    padding: 40px 20px;
    text-align: center;
    color: var(--color-text-maxcontrast);
    font-style: italic;
    background: var(--color-main-background);
    border: 1px solid var(--color-border);
    border-radius: var(--border-radius-large);
}

/* Status Badges */
.status-badge {
    padding: 4px 10px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    display: inline-block;
}

.badge-success {
    background-color: rgba(70, 186, 97, 0.15);
    color: var(--color-element-success);
}

.badge-neutral {
    background-color: var(--color-background-dark);
    color: var(--color-text-maxcontrast);
}

.text-error { color: var(--color-element-error); }
.text-success { color: var(--color-element-success); }

.inline-icon {
    margin-right: 6px;
    vertical-align: middle;
}

/* Mobile Helpers */
@media (max-width: 768px) {
    .app-content-list { width: 100%; border: none; }
    .mobile-hidden { display: none !important; }
    .stats-grid { grid-template-columns: 1fr; }
    .info-grid { grid-template-columns: 1fr; }
}

/* Loading */
.loading-container {
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 40px;
}

.spin-animation {
    animation: spin 1s linear infinite;
}

@keyframes spin {
    100% { transform: rotate(360deg); }
}

.import-and-new-contact-buttons {
    display: flex;
    gap: 8px;
}
</style>