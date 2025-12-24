<template>
	<div class="projects-list-view">
		<div class="controls">
			<div class="controls__left">
				<NcButton type="primary" @click="$emit('add')">
					<template #icon>
						<MaterialIcon name="add" :size="20" />
					</template>
					{{ translate('domaincontrol', 'Add Project') }}
				</NcButton>
				<div class="filter-buttons">
					<NcButton
						type="tertiary"
						:class="{ 'button-primary': currentFilter === 'all' }"
						@click="$emit('filter', 'all')"
					>
						{{ translate('domaincontrol', 'All') }}
					</NcButton>
					<NcButton
						type="tertiary"
						:class="{ 'button-primary': currentFilter === 'active' }"
						@click="$emit('filter', 'active')"
					>
						{{ translate('domaincontrol', 'Active') }}
					</NcButton>
					<NcButton
						type="tertiary"
						:class="{ 'button-primary': currentFilter === 'completed' }"
						@click="$emit('filter', 'completed')"
					>
						{{ translate('domaincontrol', 'Completed') }}
					</NcButton>
					<NcButton
						type="tertiary"
						:class="{ 'button-primary': currentFilter === 'on_hold' }"
						@click="$emit('filter', 'on_hold')"
					>
						{{ translate('domaincontrol', 'On Hold') }}
					</NcButton>
				</div>
			</div>
			<div class="controls__right">
				<NcTextField
					:model-value="searchQuery"
					:placeholder="translate('domaincontrol', 'Search projects...')"
					class="search-input"
					@update:model-value="handleSearch"
					@input="handleSearchInput"
				/>
			</div>
		</div>

		<!-- Empty State -->
		<div v-if="filteredProjects && filteredProjects.length === 0 && !loading" class="empty-state">
			<MaterialIcon name="folder" :size="48" color="var(--color-text-maxcontrast)" class="empty-state__icon" />
			<p class="empty-state__text">
				{{ searchQuery ? translate('domaincontrol', 'No projects found') : translate('domaincontrol', 'No projects yet') }}
			</p>
			<NcButton v-if="!searchQuery" type="primary" @click="$emit('add')">
				{{ translate('domaincontrol', 'Add First Project') }}
			</NcButton>
		</div>

		<!-- Loading State -->
		<div v-if="loading" class="loading-state">
			<NcLoadingIcon :size="32" />
			<p>{{ translate('domaincontrol', 'Loading projects...') }}</p>
		</div>

		<!-- Projects List -->
		<div v-else-if="filteredProjects && filteredProjects.length > 0" class="projects-list">
			<div
				v-for="project in filteredProjects"
				:key="project.id"
				class="project-item"
				:class="{ 'project-item--active': selectedProject && selectedProject.id === project.id }"
				@click="$emit('select', project)"
			>
				<div class="project-item__icon">
					<MaterialIcon name="folder" :size="24" />
				</div>
				<div class="project-item__content">
					<div class="project-item__title">{{ project.name }}</div>
					<div class="project-item__meta">
						<span v-if="getClientName(project.clientId)" class="project-item__meta-item">
							{{ getClientName(project.clientId) }}
						</span>
						<span v-if="getProjectTypeText(project.projectType)" class="project-item__meta-item">
							{{ getProjectTypeText(project.projectType) }}
						</span>
						<span v-if="project.deadline" class="project-item__meta-item">
							{{ formatDate(project.deadline) }}
						</span>
					</div>
				</div>
				<div class="project-item__stats">
					<div class="project-item__stat">
						<div class="project-item__stat-label">{{ translate('domaincontrol', 'Status') }}</div>
						<div class="project-item__stat-value">
							<span class="status-badge" :class="getProjectStatusClass(project)">
								{{ getProjectStatusText(project.status) }}
							</span>
						</div>
					</div>
					<div class="project-item__stat">
						<div class="project-item__stat-label">{{ translate('domaincontrol', 'Budget') }}</div>
						<div class="project-item__stat-value">
							{{ formatCurrency(project.budget, project.currency) }}
						</div>
					</div>
					<div class="project-item__stat">
						<div class="project-item__stat-label">{{ translate('domaincontrol', 'Deadline') }}</div>
						<div class="project-item__stat-value" :class="getDeadlineClass(project)">
							{{ getDaysUntilDeadline(project.deadline) }}
						</div>
					</div>
				</div>
				<div class="project-item__actions" @click.stop>
					<div class="popover-menu-wrapper">
						<NcButton
							type="tertiary"
							:aria-label="translate('domaincontrol', 'More options')"
							@click.stop="$emit('toggle-popover', project.id)"
						>
							<template #icon>
								<MaterialIcon name="more-vertical" :size="18" />
							</template>
						</NcButton>
						<div
							v-if="openPopover === project.id"
							class="popover-menu"
							@click.stop
						>
							<button
								class="popover-menu-item"
								@click="$emit('edit', project); $emit('close-popover')"
							>
								<MaterialIcon name="edit" :size="16" />
								{{ translate('domaincontrol', 'Edit') }}
							</button>
							<div class="popover-menu-separator"></div>
							<button
								class="popover-menu-item popover-menu-item--danger"
								@click="$emit('delete', project); $emit('close-popover')"
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
</template>

<script>
import { NcButton, NcTextField, NcLoadingIcon } from '@nextcloud/vue'
import MaterialIcon from '../MaterialIcon.vue'

export default {
	name: 'ProjectList',
	components: {
		NcButton,
		NcTextField,
		NcLoadingIcon,
		MaterialIcon,
	},
	props: {
		projects: {
			type: Array,
			default: () => [],
		},
		clients: {
			type: Array,
			default: () => [],
		},
		loading: {
			type: Boolean,
			default: false,
		},
		currentFilter: {
			type: String,
			default: 'all',
		},
		searchQuery: {
			type: String,
			default: '',
		},
		filteredProjects: {
			type: Array,
			default: () => [],
		},
		openPopover: {
			type: [String, Number],
			default: null,
		},
		selectedProject: {
			type: Object,
			default: null,
		},
	},
	emits: ['add', 'filter', 'search', 'select', 'edit', 'delete', 'toggle-popover', 'close-popover'],
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
			return text
		},
		getClientName(clientId) {
			const client = this.clients.find(c => c.id === clientId)
			return client ? client.name : ''
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
		getDeadlineClass(project) {
			if (!project.deadline) return ''
			const deadline = new Date(project.deadline)
			const today = new Date()
			today.setHours(0, 0, 0, 0)
			deadline.setHours(0, 0, 0, 0)
			const diffDays = Math.ceil((deadline - today) / (1000 * 60 * 60 * 24))
			
			if (diffDays < 0) return 'status-critical'
			if (diffDays <= 7) return 'status-warning'
			return 'status-ok'
		},
		getDaysUntilDeadline(deadline) {
			if (!deadline) return '-'
			const deadlineDate = new Date(deadline)
			const today = new Date()
			today.setHours(0, 0, 0, 0)
			deadlineDate.setHours(0, 0, 0, 0)
			const diffDays = Math.ceil((deadlineDate - today) / (1000 * 60 * 60 * 24))
			
			if (diffDays < 0) {
				return `${Math.abs(diffDays)} ${this.translate('domaincontrol', 'days overdue')}`
			} else if (diffDays === 0) {
				return this.translate('domaincontrol', 'Today')
			} else {
				return `${diffDays} ${this.translate('domaincontrol', 'days left')}`
			}
		},
		handleSearch(value) {
			console.log('ProjectList handleSearch called with:', value)
			this.$emit('search', value)
		},
		handleSearchInput(event) {
			const value = event?.target?.value || event
			console.log('ProjectList handleSearchInput called with:', value)
			this.$emit('search', value)
		},
	},
}
</script>

<style scoped>
.projects-list-view {
	width: 100%;
	height: 100%;
	padding: 7px;
	/* Nextcloud NcAppContent handles padding automatically */
}

/* Controls - Nextcloud Standard */
.controls {
	display: flex;
	align-items: center;
	justify-content: space-between;
	gap: 16px;
	margin-bottom: 24px;
	margin-left: 40px;
	flex-wrap: wrap;
}

.controls__left {
	display: flex;
	align-items: center;
	gap: 12px;
	flex-wrap: wrap;
}

.controls__right {
	display: flex;
	align-items: center;
	gap: 8px;
	flex: 1;
	justify-content: flex-end;
}

.search-input {
	min-width: 300px;
	max-width: 500px;
	width: 100%;
}

.search-input :deep(input) {
	width: 100%;
}

.filter-buttons {
	display: flex;
	gap: 8px;
	flex-wrap: wrap;
}

.button-primary {
	background-color: var(--color-primary-element-element-element) !important;
	color: var(--color-primary-element-element-element-text) !important;
}

.empty-state {
	display: flex;
	flex-direction: column;
	align-items: center;
	justify-content: center;
	text-align: center;
}

.empty-state__icon {
	margin-bottom: 16px;
	opacity: 0.5;
}

.empty-state__text {
	color: var(--color-text-maxcontrast);
	font-size: var(--default-font-size);
	margin-bottom: 20px;
}

.loading-state {
	display: flex;
	flex-direction: column;
	align-items: center;
	justify-content: center;
	text-align: center;
	color: var(--color-text-maxcontrast);
}

.projects-list {
	display: flex;
	flex-direction: column;
	gap: 4px;
}

.project-item {
	display: flex;
	align-items: center;
	gap: 16px;
	padding: 12px 16px;
	background-color: var(--color-main-background);
	border: 1px solid var(--color-border);
	border-radius: var(--border-radius);
	cursor: pointer;
	transition: all 0.2s ease;
	position: relative;
}

.project-item:hover {
	background-color: var(--color-background-hover);
	border-color: var(--color-primary-element-element-element);
}

.project-item--active {
	border-left: 4px solid var(--color-primary-element-element-element);
	padding-left: 12px;
}

.project-item__icon {
	display: flex;
	align-items: center;
	justify-content: center;
	width: 40px;
	height: 40px;
	border-radius: var(--border-radius);
	background-color: var(--color-background-dark);
	color: var(--color-main-text);
	flex-shrink: 0;
}

.project-item__content {
	flex: 1;
	min-width: 0;
}

.project-item__title {
	font-size: var(--default-font-size);
	font-weight: 600;
	color: var(--color-main-text);
	margin-bottom: 4px;
	overflow: hidden;
	text-overflow: ellipsis;
	white-space: nowrap;
}

.project-item__meta {
	display: flex;
	gap: 12px;
	flex-wrap: wrap;
	font-size: var(--default-font-size);
	color: var(--color-text-maxcontrast);
}

.project-item__meta-item {
	display: flex;
	align-items: center;
}

.project-item__meta-item:not(:last-child)::after {
	content: '•';
	margin-left: 12px;
	color: var(--color-text-maxcontrast);
	opacity: 0.5;
}

.project-item__stats {
	display: none; /* Hide stats on mobile, show on larger screens */
	flex-shrink: 0;
}

@media (min-width: 1024px) {
	.project-item__stats {
		display: flex;
		gap: 24px;
	}
}

.project-item__stat {
	display: flex;
	flex-direction: column;
	gap: 4px;
	min-width: 80px;
}

.project-item__stat-label {
	font-size: 12px;
	color: var(--color-text-maxcontrast);
	text-transform: uppercase;
	letter-spacing: 0.5px;
}

.project-item__stat-value {
	font-size: var(--default-font-size);
	color: var(--color-main-text);
	font-weight: 500;
}

.project-item__actions {
	flex-shrink: 0;
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
	color: var(--color-element-error-element);
}

.popover-menu-item--danger:hover {
	background-color: var(--color-element-error-background);
	color: var(--color-element-error-element);
}

.popover-menu-separator {
	height: 1px;
	background-color: var(--color-border);
	margin: 4px 0;
}

.status-badge {
	display: inline-block;
	padding: 4px 12px;
	border-radius: var(--border-radius-pill);
	font-size: 12px;
	font-weight: 500;
	text-transform: uppercase;
	letter-spacing: 0.5px;
}

.status-badge.status-active {
	background-color: var(--color-element-success);
	color: var(--color-element-success-text);
}

.status-badge.status-on_hold {
	background-color: var(--color-element-warning);
	color: var(--color-element-warning-text);
}

.status-badge.status-completed {
	background-color: var(--color-primary-element-element-element);
	color: var(--color-primary-element-element-element-text);
}

.status-badge.status-cancelled {
	background-color: var(--color-element-error);
	color: var(--color-element-error-text);
}

.status-ok {
	color: var(--color-element-success);
}

.status-warning {
	color: var(--color-element-warning);
}

.status-critical {
	color: var(--color-element-error);
}
</style>


