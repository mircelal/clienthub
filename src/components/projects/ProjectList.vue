<template>
	<div class="projects-list-view">
		<div class="domaincontrol-actions">
			<button class="button-vue button-vue--primary" @click="$emit('add')">
				<MaterialIcon name="add" :size="20" />
				{{ translate('domaincontrol', 'Add Project') }}
			</button>
			<div class="filter-buttons">
				<button
					class="button-vue button-vue--secondary"
					:class="{ 'button-vue--primary': currentFilter === 'all' }"
					@click="$emit('filter', 'all')"
				>
					{{ translate('domaincontrol', 'All') }}
				</button>
				<button
					class="button-vue button-vue--secondary"
					:class="{ 'button-vue--primary': currentFilter === 'active' }"
					@click="$emit('filter', 'active')"
				>
					{{ translate('domaincontrol', 'Active') }}
				</button>
				<button
					class="button-vue button-vue--secondary"
					:class="{ 'button-vue--primary': currentFilter === 'completed' }"
					@click="$emit('filter', 'completed')"
				>
					{{ translate('domaincontrol', 'Completed') }}
				</button>
				<button
					class="button-vue button-vue--secondary"
					:class="{ 'button-vue--primary': currentFilter === 'on_hold' }"
					@click="$emit('filter', 'on_hold')"
				>
					{{ translate('domaincontrol', 'On Hold') }}
				</button>
			</div>
			<div class="project-search-wrapper">
				<input
					type="text"
					:value="searchQuery"
					@input="$emit('search', $event.target.value)"
					class="project-search-input"
					:placeholder="translate('domaincontrol', 'Search projects...')"
				/>
			</div>
		</div>

		<!-- Empty State -->
		<div v-if="filteredProjects && filteredProjects.length === 0 && !loading" class="empty-content">
			<MaterialIcon name="folder" :size="48" color="var(--color-text-maxcontrast)" class="empty-content__icon" />
			<p class="empty-content__text">
				{{ searchQuery ? translate('domaincontrol', 'No projects found') : translate('domaincontrol', 'No projects yet') }}
			</p>
			<button v-if="!searchQuery" class="button-vue button-vue--primary" @click="$emit('add')">
				{{ translate('domaincontrol', 'Add First Project') }}
			</button>
		</div>

		<!-- Loading State -->
		<div v-if="loading" class="loading-content">
			<MaterialIcon name="loading" :size="32" class="loading-icon" />
			<p>{{ translate('domaincontrol', 'Loading projects...') }}</p>
		</div>

		<!-- Projects List -->
		<div v-else-if="filteredProjects && filteredProjects.length > 0" class="projects-list">
			<div
				v-for="project in filteredProjects"
				:key="project.id"
				class="list-item project-item"
				@click="$emit('select', project)"
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
							@click.stop="$emit('toggle-popover', project.id)"
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
import MaterialIcon from '../MaterialIcon.vue'

export default {
	name: 'ProjectList',
	components: {
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
	},
}
</script>

<style scoped>
.projects-list-view {
	width: 100%;
	height: 100%;
}

.domaincontrol-actions {
	display: flex;
	align-items: center;
	gap: 12px;
	margin-bottom: 20px;
	flex-wrap: wrap;
}

.filter-buttons {
	display: flex;
	gap: 8px;
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
	background-color: var(--color-main-background);
	color: var(--color-main-text);
	font-size: 14px;
}

.projects-list {
	display: flex;
	flex-direction: column;
	gap: 8px;
}

.project-item {
	cursor: pointer;
	transition: background-color 0.2s;
}

.project-item:hover {
	background-color: var(--color-background-dark);
}
</style>

