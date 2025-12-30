<template>
	<div class="projects-navigation">
		<div class="app-content-list-header">
			<div class="search-wrapper">
				<div class="search-wrapper-inner">
					<Magnify :size="20" class="search-icon" />
					<input
						type="text"
						:value="searchQuery"
						:placeholder="translate('domaincontrol', 'Proje ara...')"
						class="search-input"
						@input="handleSearchInput"
					/>
				</div>
			</div>
			
			<div class="header-actions">
				<NcButton 
					type="secondary" 
					:wide="true"
					@click="$emit('add')">
					<template #icon>
						<Plus :size="20" />
					</template>
					{{ translate('domaincontrol', 'Yeni Proje') }}
				</NcButton>
			</div>

			<div class="filter-tabs">
				<button 
					class="filter-tab" 
					:class="{ active: currentFilter === 'all' }"
					@click="$emit('filter', 'all')"
				>
					{{ translate('domaincontrol', 'Hepsi') }}
				</button>
				<button 
					class="filter-tab" 
					:class="{ active: currentFilter === 'active' }"
					@click="$emit('filter', 'active')"
				>
					{{ translate('domaincontrol', 'Aktif') }}
				</button>
				<button 
					class="filter-tab" 
					:class="{ active: currentFilter === 'completed' }"
					@click="$emit('filter', 'completed')"
				>
					{{ translate('domaincontrol', 'Tamamlanan') }}
				</button>
			</div>
		</div>

		<div class="app-content-list-wrapper">
			<div v-if="loading" class="loading-container">
				<Refresh :size="32" class="spin-animation" />
			</div>
			
			<div v-else-if="filteredProjects.length === 0" class="empty-list">
				<div class="empty-text">{{ translate('domaincontrol', 'Proje bulunamadı') }}</div>
			</div>

			<ul v-else class="app-navigation-list">
				<li 
					v-for="project in filteredProjects" 
					:key="project.id" 
					class="app-navigation-entry" 
					:class="{ 'active': selectedProject && selectedProject.id === project.id }" 
					@click="$emit('select', project)"
				>
					<div class="app-navigation-entry-icon">
						<div class="avatar-circle project-avatar" :style="{ backgroundColor: getProjectColor(project) }">
							<Folder :size="20" />
						</div>
					</div>
					<div class="app-navigation-entry-content">
						<div class="app-navigation-entry-name">{{ project.name }}</div>
						<div class="app-navigation-entry-details">
							<span v-if="getClientName(project.clientId)">{{ getClientName(project.clientId) }}</span>
							<span v-else>{{ getProjectTypeText(project.projectType) }}</span>
						</div>
					</div>
					<div class="app-navigation-entry-status">
						<span class="status-dot-small" :class="getProjectStatusDotClass(project)"></span>
					</div>
				</li>
			</ul>
		</div>
	</div>
</template>

<script>
import { NcButton } from '@nextcloud/vue'
import Folder from 'vue-material-design-icons/Folder.vue'
import Plus from 'vue-material-design-icons/Plus.vue'
import Magnify from 'vue-material-design-icons/Magnify.vue'
import Refresh from 'vue-material-design-icons/Refresh.vue'

export default {
	name: 'ProjectList',
	components: {
		NcButton,
		Folder,
		Plus,
		Magnify,
		Refresh,
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
	data() {
		return {
			// No local state needed for now
		}
	},
    // Mounted hook removed as loadSettings was undefined and unused
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
		getProjectColor(project) {
			if (!project.name) return '#0082c9'
			const colors = ['#0082c9', '#46ba61', '#f0ad4e', '#e3322d', '#5bc0de', '#9b59b6', '#e67e22', '#3498db']
			let hash = 0
			for (let i = 0; i < project.name.length; i++) {
				hash = project.name.charCodeAt(i) + ((hash << 5) - hash)
			}
			return colors[Math.abs(hash) % colors.length]
		},
		getProjectStatusDotClass(project) {
			const status = project.status || 'active'
			if (status === 'active') return 'status-ok'
			if (status === 'completed') return 'status-completed'
			if (status === 'on_hold') return 'status-warning'
			if (status === 'cancelled') return 'status-expired'
			return 'status-ok'
		},
		handleSearchInput(event) {
			this.$emit('search', event.target.value)
		},
	},
}
</script>

<style scoped>
.projects-navigation {
	height: 100%;
	display: flex;
	flex-direction: column;
	background-color: var(--color-main-background);
}

.app-content-list-header {
	padding: 0;
	display: flex;
	flex-direction: column;
	border-bottom: 1px solid var(--color-border);
}

.search-wrapper {
	position: relative;
	padding: 11px 19px;
	border-bottom: 1px solid var(--color-border);
}

.search-wrapper-inner {
	margin-left: 25px;
}

.search-icon {
	position: absolute;
	left: 50px;
	top: 50%;
	transform: translateY(-50%);
	opacity: 0.5;
	pointer-events: none;
	color: var(--color-text-maxcontrast);
}

.search-input {
	width: 100%;
	padding: 8px 12px 8px 34px !important;
	border: 1px solid transparent !important;
	border-radius: 8px !important;
	background-color: var(--color-background-dark) !important;
	color: var(--color-main-text);
	box-sizing: border-box;
	transition: all 0.2s ease;
}

.search-input:focus {
	background-color: var(--color-main-background) !important;
	border-color: var(--color-primary-element) !important;
	outline: none;
	box-shadow: 0 0 0 2px var(--color-primary-element-light);
}

.header-actions {
	padding: 12px 16px;
	border-bottom: 1px solid var(--color-border);
}

/* Filter Tabs */
.filter-tabs {
	display: flex;
	padding: 0 16px;
	gap: 16px;
	background: var(--color-background-dark);
}

.filter-tab {
	padding: 10px 4px;
	background: none;
	border: none;
	font-size: 13px;
	font-weight: 600;
	color: var(--color-text-maxcontrast);
	cursor: pointer;
	transition: all 0.2s;
}

.filter-tab:hover {
	color: var(--color-main-text);
}

.filter-tab.active {
	color: var(--color-primary-element);
	border-bottom-color: var(--color-primary-element);
}

.app-content-list-wrapper {
	flex: 1;
	overflow-y: auto;
}

.app-navigation-list { 
	list-style: none; 
	padding: 0; 
	margin: 0; 
}

.app-navigation-entry {
	display: flex;
	align-items: center;
	padding: 12px 15px;
	cursor: pointer;
	border-bottom: 1px solid var(--color-border);
	transition: background-color 0.15s ease;
}

.app-navigation-entry:hover { 
	background-color: var(--color-background-hover); 
}

.app-navigation-entry.active { 
	background-color: var(--color-primary-element-light); 
	border-left: 3px solid var(--color-primary-element); 
}

.avatar-circle {
	width: 36px; 
	height: 36px;
	border-radius: 50%;
	color: white;
	display: flex; 
	align-items: center; 
	justify-content: center;
}

.app-navigation-entry-content { 
	margin-left: 12px; 
	flex: 1; 
	min-width: 0; 
}

.app-navigation-entry-name { 
	font-weight: 600; 
	white-space: nowrap; 
	overflow: hidden; 
	text-overflow: ellipsis; 
	color: var(--color-main-text); 
	font-size: 14px;
}

.app-navigation-entry-details { 
	font-size: 12px; 
	color: var(--color-text-maxcontrast); 
	opacity: 0.7; 
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
.status-completed { background-color: var(--color-primary-element); }
.status-expired { background-color: var(--color-text-maxcontrast); }

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

.empty-list {
	padding: 40px 20px;
	text-align: center;
}

.empty-text {
	color: var(--color-text-maxcontrast);
	font-style: italic;
}
</style>


