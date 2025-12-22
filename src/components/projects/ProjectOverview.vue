<template>
	<div class="tab-content">
		<!-- Debug Info -->
		<div v-if="false" style="padding: 10px; background: #f0f0f0; margin-bottom: 10px; font-size: 12px;">
			Debug: project={{ project ? project.id : 'null' }}, clients.length={{ clients ? clients.length : 'null' }}
		</div>
		<!-- Stats Cards -->
		<div v-if="project" class="detail-stats">
			<div class="stat-card">
				<div class="stat-card__icon">
					<MaterialIcon name="contacts" :size="24" />
				</div>
				<div class="stat-card__content">
					<div class="stat-card__label">{{ translate('domaincontrol', 'Client') }}</div>
					<div class="stat-card__value">
						<a
							v-if="project && project.clientId"
							href="#"
							@click.prevent="$emit('navigate-client', project.clientId)"
							class="link-primary"
						>
							{{ getClientName(project.clientId) }}
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
						{{ project ? getProjectTypeText(project.projectType) || '-' : '-' }}
					</div>
				</div>
			</div>
			<div class="stat-card">
				<div class="stat-card__icon" :class="project ? getProjectStatusClass(project) : ''">
					<MaterialIcon name="checkmark" :size="24" />
				</div>
				<div class="stat-card__content">
					<div class="stat-card__label">{{ translate('domaincontrol', 'Status') }}</div>
					<div class="stat-card__value">
						<span class="status-badge" :class="project ? getProjectStatusClass(project) : ''">
							{{ project ? getProjectStatusText(project.status) : '-' }}
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
						{{ project ? formatDate(project.startDate) || '-' : '-' }}
					</div>
				</div>
			</div>
			<div class="stat-card">
				<div class="stat-card__icon" :class="project ? getDeadlineClass(project) : ''">
					<MaterialIcon name="calendar" :size="24" />
				</div>
				<div class="stat-card__content">
					<div class="stat-card__label">{{ translate('domaincontrol', 'Deadline') }}</div>
					<div class="stat-card__value" :class="project ? getDeadlineClass(project) : ''">
						{{ project ? formatDate(project.deadline) || '-' : '-' }}
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
						{{ project ? formatCurrency(project.budget, project.currency) : '-' }}
					</div>
				</div>
			</div>
		</div>

		<!-- Info Grid -->
		<div v-if="project" class="detail-info-grid">
			<div class="detail-info-card">
				<h3 class="info-card-title">{{ translate('domaincontrol', 'Project Description') }}</h3>
				<div class="detail-description">
					{{ project.description || translate('domaincontrol', 'No description') }}
				</div>
			</div>

			<div class="detail-info-card">
				<h3 class="info-card-title">{{ translate('domaincontrol', 'Notes') }}</h3>
				<div class="detail-notes" v-html="project.notes || translate('domaincontrol', 'No notes')"></div>
			</div>
		</div>
		<div v-else class="empty-content">
			<p class="empty-content__text">{{ translate('domaincontrol', 'No project selected') }}</p>
		</div>
	</div>
</template>

<script>
import MaterialIcon from '../MaterialIcon.vue'

export default {
	name: 'ProjectOverview',
	components: {
		MaterialIcon,
	},
	props: {
		project: {
			type: Object,
			default: null,
		},
		clients: {
			type: Array,
			default: () => [],
		},
	},
	emits: ['navigate-client'],
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
			if (!project) return ''
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
			if (!project || !project.deadline) return ''
			const deadline = new Date(project.deadline)
			const today = new Date()
			today.setHours(0, 0, 0, 0)
			deadline.setHours(0, 0, 0, 0)
			const diffDays = Math.ceil((deadline - today) / (1000 * 60 * 60 * 24))
			
			if (diffDays < 0) return 'status-critical'
			if (diffDays <= 7) return 'status-warning'
			return 'status-ok'
		},
	},
}
</script>

<style scoped>
.tab-content {
	padding: 20px;
}

.detail-stats {
	display: grid;
	grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
	gap: 16px;
	margin-bottom: 24px;
}

.stat-card {
	display: flex;
	align-items: center;
	gap: 12px;
	padding: 16px;
	background-color: var(--color-main-background);
	border: 1px solid var(--color-border);
	border-radius: var(--border-radius-element);
}

.stat-card__icon {
	display: flex;
	align-items: center;
	justify-content: center;
	width: 40px;
	height: 40px;
	border-radius: var(--border-radius-element);
	background-color: var(--color-background-dark);
	color: var(--color-main-text);
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
	font-size: 14px;
	font-weight: 500;
	color: var(--color-main-text);
}

.detail-info-grid {
	display: grid;
	grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
	gap: 20px;
}

.detail-info-card {
	padding: 20px;
	background-color: var(--color-main-background);
	border: 1px solid var(--color-border);
	border-radius: var(--border-radius-element);
}

.info-card-title {
	margin: 0 0 12px 0;
	font-size: 16px;
	font-weight: 600;
	color: var(--color-main-text);
}

.detail-description,
.detail-notes {
	font-size: 14px;
	color: var(--color-main-text);
	line-height: 1.5;
}

.link-primary {
	color: var(--color-primary-element);
	text-decoration: none;
}

.link-primary:hover {
	text-decoration: underline;
}

.empty-content {
	text-align: center;
	padding: 40px 20px;
}

.empty-content__text {
	color: var(--color-text-maxcontrast);
	font-size: 14px;
}
</style>
