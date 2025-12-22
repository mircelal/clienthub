<template>
	<div class="projects-view">
		<!-- Project Modal -->
		<ProjectModal
			:open="modalOpen"
			:project="editingProject"
			:clients="clients"
			@close="closeModal"
			@saved="handleProjectSaved"
		/>

		<!-- Project List View -->
		<div v-if="!selectedProject" class="projects-list-view">
			<div class="domaincontrol-actions">
				<button class="button-vue button-vue--primary" @click="showAddModal">
					<MaterialIcon name="add" :size="20" />
					{{ translate('domaincontrol', 'Add Project') }}
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
						:class="{ 'button-vue--primary': currentFilter === 'active' }"
						@click="setFilter('active')"
					>
						{{ translate('domaincontrol', 'Active') }}
					</button>
					<button
						class="button-vue button-vue--secondary"
						:class="{ 'button-vue--primary': currentFilter === 'completed' }"
						@click="setFilter('completed')"
					>
						{{ translate('domaincontrol', 'Completed') }}
					</button>
					<button
						class="button-vue button-vue--secondary"
						:class="{ 'button-vue--primary': currentFilter === 'on_hold' }"
						@click="setFilter('on_hold')"
					>
						{{ translate('domaincontrol', 'On Hold') }}
					</button>
				</div>
				<div class="project-search-wrapper">
					<input
						type="text"
						v-model="searchQuery"
						class="project-search-input"
						:placeholder="translate('domaincontrol', 'Search projects...')"
						@input="filterProjects"
					/>
				</div>
			</div>

			<!-- Empty State -->
			<div v-if="filteredProjects.length === 0 && !loading" class="empty-content">
				<MaterialIcon name="folder" :size="48" color="var(--color-text-maxcontrast)" class="empty-content__icon" />
				<p class="empty-content__text">
					{{ searchQuery ? translate('domaincontrol', 'No projects found') : translate('domaincontrol', 'No projects yet') }}
				</p>
				<button v-if="!searchQuery" class="button-vue button-vue--primary" @click="showAddModal">
					{{ translate('domaincontrol', 'Add First Project') }}
				</button>
			</div>

			<!-- Loading State -->
			<div v-if="loading" class="loading-content">
				<MaterialIcon name="loading" :size="32" class="loading-icon" />
				<p>{{ translate('domaincontrol', 'Loading projects...') }}</p>
			</div>

			<!-- Projects List -->
			<div v-else-if="filteredProjects.length > 0" class="projects-list">
				<div
					v-for="project in filteredProjects"
					:key="project.id"
					class="list-item project-item"
					@click="selectProject(project)"
				>
					<div class="list-item__avatar">
						<MaterialIcon name="folder" :size="24" />
					</div>
					<div class="list-item__content">
						<div class="list-item__title">{{ project.name }}</div>
						<div class="list-item__meta">
							<span v-if="getClientName(project.clientId)">
								{{ getClientName(project.clientId) }}
							</span>
							<span v-if="getProjectTypeText(project.projectType)">
								{{ getProjectTypeText(project.projectType) }}
							</span>
							<span v-if="project.deadline">
								{{ formatDate(project.deadline) }}
							</span>
						</div>
					</div>
					<div class="list-item__stats">
						<div class="list-item__stat">
							<div class="list-item__stat-label">{{ translate('domaincontrol', 'Status') }}</div>
							<div class="list-item__stat-value">
								<span class="status-badge status-badge--simple" :class="getProjectStatusClass(project)">
									{{ getProjectStatusText(project.status) }}
								</span>
							</div>
						</div>
						<div class="list-item__stat">
							<div class="list-item__stat-label">{{ translate('domaincontrol', 'Budget') }}</div>
							<div class="list-item__stat-value">
								{{ formatCurrency(project.budget, project.currency) }}
							</div>
						</div>
						<div class="list-item__stat">
							<div class="list-item__stat-label">{{ translate('domaincontrol', 'Deadline') }}</div>
							<div class="list-item__stat-value" :class="getDeadlineClass(project)">
								{{ getDaysUntilDeadline(project.deadline) }}
							</div>
						</div>
					</div>
					<div class="list-item__actions">
						<div class="popover-menu-wrapper" @click.stop>
							<button
								class="action-button action-button--more"
								@click.stop="togglePopover(project.id)"
								:title="translate('domaincontrol', 'More options')"
							>
								<MaterialIcon name="more-vertical" :size="18" />
							</button>
							<div
								v-if="openPopover === project.id"
								class="popover-menu"
								@click.stop
							>
								<button
									class="popover-menu-item"
									@click="editProject(project); closePopover()"
								>
									<MaterialIcon name="edit" :size="16" />
									{{ translate('domaincontrol', 'Edit') }}
								</button>
								<div class="popover-menu-separator"></div>
								<button
									class="popover-menu-item popover-menu-item--danger"
									@click="confirmDelete(project); closePopover()"
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

		<!-- Project Detail View -->
		<div v-else class="project-detail-view">
			<div class="detail-header">
				<button class="button-vue button-vue--tertiary" @click="backToList">
					<MaterialIcon name="arrow-left" :size="20" />
					{{ translate('domaincontrol', 'Back') }}
				</button>
				<h2 class="detail-title">{{ selectedProject.name }}</h2>
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
								@click="editProject(selectedProject); closeDetailPopover()"
							>
								<MaterialIcon name="edit" :size="16" />
								{{ translate('domaincontrol', 'Edit') }}
							</button>
							<div class="popover-menu-separator"></div>
							<button
								class="popover-menu-item popover-menu-item--danger"
								@click="confirmDelete(selectedProject); closeDetailPopover()"
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
									v-if="selectedProject.clientId"
									href="#"
									@click.prevent="navigateToClient(selectedProject.clientId)"
									class="link-primary"
								>
									{{ getClientName(selectedProject.clientId) }}
								</a>
								<span v-else>-</span>
							</div>
						</div>
					</div>
					<div class="stat-card">
						<div class="stat-card__icon">
							<MaterialIcon name="settings" :size="24" />
						</div>
						<div class="stat-card__content">
							<div class="stat-card__label">{{ translate('domaincontrol', 'Project Type') }}</div>
							<div class="stat-card__value">
								{{ getProjectTypeText(selectedProject.projectType) || '-' }}
							</div>
						</div>
					</div>
					<div class="stat-card">
						<div class="stat-card__icon" :class="getProjectStatusClass(selectedProject)">
							<MaterialIcon name="checkmark" :size="24" />
						</div>
						<div class="stat-card__content">
							<div class="stat-card__label">{{ translate('domaincontrol', 'Status') }}</div>
							<div class="stat-card__value">
								<span class="status-badge" :class="getProjectStatusClass(selectedProject)">
									{{ getProjectStatusText(selectedProject.status) }}
								</span>
							</div>
						</div>
					</div>
					<div class="stat-card">
						<div class="stat-card__icon">
							<MaterialIcon name="calendar" :size="24" />
						</div>
						<div class="stat-card__content">
							<div class="stat-card__label">{{ translate('domaincontrol', 'Start Date') }}</div>
							<div class="stat-card__value">
								{{ formatDate(selectedProject.startDate) || '-' }}
							</div>
						</div>
					</div>
					<div class="stat-card">
						<div class="stat-card__icon" :class="getDeadlineClass(selectedProject)">
							<MaterialIcon name="calendar" :size="24" />
						</div>
						<div class="stat-card__content">
							<div class="stat-card__label">{{ translate('domaincontrol', 'Deadline') }}</div>
							<div class="stat-card__value" :class="getDeadlineClass(selectedProject)">
								{{ formatDate(selectedProject.deadline) || '-' }}
							</div>
						</div>
					</div>
					<div class="stat-card">
						<div class="stat-card__icon">
							<MaterialIcon name="accounting" :size="24" />
						</div>
						<div class="stat-card__content">
							<div class="stat-card__label">{{ translate('domaincontrol', 'Budget') }}</div>
							<div class="stat-card__value">
								{{ formatCurrency(selectedProject.budget, selectedProject.currency) }}
							</div>
						</div>
					</div>
				</div>

				<!-- Info Grid -->
				<div class="detail-info-grid">
					<div class="detail-info-card">
						<h3 class="info-card-title">{{ translate('domaincontrol', 'Project Description') }}</h3>
						<div class="detail-description">
							{{ selectedProject.description || translate('domaincontrol', 'No description') }}
						</div>
					</div>

					<div class="detail-info-card">
						<h3 class="info-card-title">{{ translate('domaincontrol', 'Notes') }}</h3>
						<div class="detail-notes" v-html="selectedProject.notes || translate('domaincontrol', 'No notes')"></div>
					</div>
				</div>
			</div>
		</div>
	</div>
</template>

<script>
import api from '../services/api'
import MaterialIcon from './MaterialIcon.vue'
import ProjectModal from './ProjectModal.vue'

export default {
	name: 'Projects',
	components: {
		MaterialIcon,
		ProjectModal,
	},
	data() {
		return {
			projects: [],
			clients: [],
			selectedProject: null,
			loading: false,
			modalOpen: false,
			editingProject: null,
			currentFilter: 'all',
			searchQuery: '',
			openPopover: null,
			detailPopoverOpen: false,
		}
	},
	computed: {
		filteredProjects() {
			let filtered = this.projects

			// Apply status filter
			if (this.currentFilter !== 'all') {
				filtered = filtered.filter(p => p.status === this.currentFilter)
			}

			// Apply search query
			if (this.searchQuery) {
				const query = this.searchQuery.toLowerCase()
				filtered = filtered.filter(project => {
					const name = (project.name || '').toLowerCase()
					const clientName = this.getClientName(project.clientId) || ''
					return (
						name.includes(query) ||
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
					this.loadProjects(),
					this.loadClients(),
				])
			} catch (error) {
				console.error('Error loading data:', error)
			} finally {
				this.loading = false
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
		async loadClients() {
			try {
				const response = await api.clients.getAll()
				this.clients = response.data || []
			} catch (error) {
				console.error('Error loading clients:', error)
				this.clients = []
			}
		},
		filterProjects() {
			// Computed property handles filtering
		},
		setFilter(filter) {
			this.currentFilter = filter
		},
		selectProject(project) {
			this.selectedProject = project
		},
		backToList() {
			this.selectedProject = null
		},
		showAddModal() {
			this.editingProject = null
			this.modalOpen = true
		},
		editProject(project) {
			this.editingProject = project
			this.modalOpen = true
		},
		closeModal() {
			this.modalOpen = false
			this.editingProject = null
		},
		async handleProjectSaved() {
			await this.loadProjects()
			if (this.selectedProject) {
				const response = await api.projects.get(this.selectedProject.id)
				this.selectedProject = response.data
			}
			this.closeModal()
		},
		async confirmDelete(project) {
			if (!confirm(this.translate('domaincontrol', 'Are you sure you want to delete this project?'))) {
				return
			}
			try {
				await api.projects.delete(project.id)
				await this.loadProjects()
				if (this.selectedProject && this.selectedProject.id === project.id) {
					this.backToList()
				}
			} catch (error) {
				console.error('Error deleting project:', error)
				alert(this.translate('domaincontrol', 'Error deleting project'))
			}
		},
		getClientName(clientId) {
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
		getProjectStatusClass(project) {
			return `status-${project.status || 'active'}`
		},
		getProjectStatusText(status) {
			const statusTexts = {
				active: this.translate('domaincontrol', 'Active'),
				on_hold: this.translate('domaincontrol', 'On Hold'),
				completed: this.translate('domaincontrol', 'Completed'),
				cancelled: this.translate('domaincontrol', 'Cancelled'),
			}
			return statusTexts[status] || status
		},
		getProjectTypeText(type) {
			const types = {
				website: this.translate('domaincontrol', 'Website'),
				ecommerce: this.translate('domaincontrol', 'E-commerce'),
				webapp: this.translate('domaincontrol', 'Web App'),
				theme: this.translate('domaincontrol', 'Theme/Module'),
				design: this.translate('domaincontrol', 'Graphic Design'),
				server: this.translate('domaincontrol', 'Server Setup'),
				email: this.translate('domaincontrol', 'Email Setup'),
				hosting: this.translate('domaincontrol', 'Hosting'),
				device: this.translate('domaincontrol', 'Device Setup'),
				support: this.translate('domaincontrol', 'Technical Support'),
				seo: this.translate('domaincontrol', 'SEO/Marketing'),
				other: this.translate('domaincontrol', 'Other'),
			}
			return types[type] || type
		},
		getDaysUntilDeadline(deadline) {
			if (!deadline) return '-'
			const deadlineDate = new Date(deadline)
			const today = new Date()
			today.setHours(0, 0, 0, 0)
			const diffTime = deadlineDate - today
			const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24))
			
			if (diffDays < 0) {
				return `${Math.abs(diffDays)} ${this.translate('domaincontrol', 'days overdue')}`
			} else if (diffDays === 0) {
				return this.translate('domaincontrol', 'Today')
			} else {
				return `${diffDays} ${this.translate('domaincontrol', 'days left')}`
			}
		},
		getDeadlineClass(project) {
			if (!project.deadline) return ''
			const deadline = new Date(project.deadline)
			const today = new Date()
			today.setHours(0, 0, 0, 0)
			const diffTime = deadline - today
			const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24))
			
			if (diffDays < 0) return 'status-critical'
			if (diffDays <= 7) return 'status-warning'
			return 'status-ok'
		},
		togglePopover(projectId) {
			this.openPopover = this.openPopover === projectId ? null : projectId
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
				'Add Project': 'Proje Ekle',
				'Search projects...': 'Projelerde ara...',
				'No projects found': 'Proje bulunamadı',
				'No projects yet': 'Henüz proje yok',
				'Add First Project': 'İlk Projeyi Ekle',
				'Loading projects...': 'Projeler yükleniyor...',
				'All': 'Tümü',
				'Active': 'Aktif',
				'Completed': 'Tamamlandı',
				'On Hold': 'Beklemede',
				'Status': 'Durum',
				'Budget': 'Bütçe',
				'Deadline': 'Bitiş Tarihi',
				'More options': 'Daha fazla seçenek',
				'Edit': 'Düzenle',
				'Delete': 'Sil',
				'Back': 'Geri',
				'Client': 'Müşteri',
				'Project Type': 'Proje Türü',
				'Start Date': 'Başlangıç Tarihi',
				'Project Description': 'Proje Açıklaması',
				'Notes': 'Notlar',
				'No description': 'Açıklama yok',
				'No notes': 'Not yok',
				'Website': 'Web Sitesi',
				'E-commerce': 'E-Ticaret',
				'Web App': 'Web Uygulaması',
				'Theme/Module': 'Tema/Modül',
				'Graphic Design': 'Grafik Tasarım',
				'Server Setup': 'Sunucu Kurulumu',
				'Email Setup': 'Mail Kurulumu',
				'Hosting': 'Hosting',
				'Device Setup': 'Cihaz Kurulumu',
				'Technical Support': 'Teknik Destek',
				'SEO/Marketing': 'SEO/Pazarlama',
				'Other': 'Diğer',
				'days overdue': 'gün gecikti',
				'Today': 'Bugün',
				'days left': 'gün kaldı',
				'Are you sure you want to delete this project?': 'Bu projeyi silmek istediğinize emin misiniz?',
				'Error deleting project': 'Proje silinirken hata oluştu',
			}

			return translations[text] || text
		},
	},
}
</script>

<style scoped>
.projects-view {
	width: 100%;
	height: 100%;
}

.projects-list-view {
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

.project-search-wrapper {
	flex: 1;
	min-width: 200px;
}

.project-search-input {
	width: 100%;
	padding: 8px 12px;
	border: 1px solid var(--color-border);
	border-radius: var(--border-radius-element);
	background-color: var(--color-background-dark);
	color: var(--color-main-text);
	font-size: 14px;
}

.project-search-input:focus {
	outline: none;
	border-color: var(--color-primary-element);
}

.projects-list {
	display: grid;
	gap: 12px;
}

.project-item {
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

.project-item:hover {
	background-color: var(--color-background-hover);
}

.project-item .list-item__avatar {
	width: 48px;
	height: 48px;
	border-radius: 50%;
	background-color: var(--color-background-dark);
	display: flex;
	align-items: center;
	justify-content: center;
	flex-shrink: 0;
}

.project-item .list-item__avatar .material-icon {
	color: var(--color-text-maxcontrast);
}

.project-item .list-item__content {
	flex: 1;
	min-width: 0;
}

.project-item .list-item__title {
	font-size: 16px;
	font-weight: 500;
	color: var(--color-main-text);
	margin-bottom: 4px;
}

.project-item .list-item__meta {
	display: flex;
	align-items: center;
	gap: 12px;
	font-size: 13px;
	color: var(--color-text-maxcontrast);
	flex-wrap: wrap;
}

.project-item .list-item__stats {
	display: flex;
	gap: 24px;
	align-items: center;
}

.project-item .list-item__stat {
	display: flex;
	flex-direction: column;
	gap: 4px;
	min-width: 100px;
}

.project-item .list-item__stat-label {
	font-size: 12px;
	color: var(--color-text-maxcontrast);
}

.project-item .list-item__stat-value {
	font-size: 14px;
	font-weight: 500;
	color: var(--color-main-text);
}

.project-item .list-item__actions {
	display: flex;
	align-items: center;
	gap: 8px;
	flex-shrink: 0;
}

.project-detail-view {
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

.status-active {
	background-color: var(--color-success);
	color: var(--color-success-text);
}

.status-on_hold {
	background-color: var(--color-warning);
	color: var(--color-warning-text);
}

.status-completed {
	background-color: var(--color-text-maxcontrast);
	color: var(--color-main-background);
	opacity: 0.7;
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
