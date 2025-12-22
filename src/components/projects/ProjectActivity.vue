<template>
	<div class="tab-content">
		<div class="detail-section">
			<div class="section-header">
				<h3 class="section-title">
					<MaterialIcon name="history" :size="24" />
					{{ translate('domaincontrol', 'Activity') }}
				</h3>
			</div>
			<div class="section-content">
				<div v-if="loading" class="loading-content">
					<MaterialIcon name="loading" :size="32" class="loading-icon" />
					<p>{{ translate('domaincontrol', 'Loading activities...') }}</p>
				</div>
				<div v-else-if="activities.length === 0" class="empty-content">
					<p class="empty-content__text">{{ translate('domaincontrol', 'No activities yet') }}</p>
				</div>
				<div v-else class="activity-list">
					<div
						v-for="activity in activities"
						:key="activity.id"
						class="activity-item"
					>
						<div class="activity-icon">
							<MaterialIcon :name="getActivityIcon(activity.activityType)" :size="20" />
						</div>
						<div class="activity-content">
							<div class="activity-description">{{ formatActivityDescription(activity) }}</div>
							<div class="activity-meta">
								<span class="activity-user">{{ getUserDisplayName(activity.userId) }}</span>
								<span class="activity-time">{{ formatTime(activity.createdAt) }}</span>
							</div>
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
	name: 'ProjectActivity',
	components: {
		MaterialIcon,
	},
	props: {
		activities: {
			type: Array,
			default: () => [],
		},
		loading: {
			type: Boolean,
			default: false,
		},
		availableUsers: {
			type: Array,
			default: () => [],
		},
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
			return text
		},
		getActivityIcon(type) {
			const icons = {
				note_created: 'note',
				note_updated: 'edit',
				note_deleted: 'delete',
				file_uploaded: 'upload',
				file_deleted: 'delete',
				task_created: 'checkmark',
				task_updated: 'edit',
				task_completed: 'checkmark-circle',
				task_deleted: 'delete',
				time_started: 'play',
				time_stopped: 'stop',
				time_updated: 'edit',
				time_deleted: 'delete',
				project_created: 'add',
				project_shared: 'share',
				project_unshared: 'share',
				project_updated: 'edit',
				item_linked: 'link',
				item_unlinked: 'link-off',
			}
			return icons[type] || 'history'
		},
		formatActivityDescription(activity) {
			const metadata = activity.metadata || {}
			const type = activity.activityType
			
			const descriptions = {
				note_created: this.translate('domaincontrol', 'Created note: {title}', { title: metadata.title || '' }),
				note_updated: this.translate('domaincontrol', 'Updated note: {title}', { title: metadata.title || '' }),
				note_deleted: this.translate('domaincontrol', 'Deleted note'),
				file_uploaded: this.translate('domaincontrol', 'Uploaded file: {fileName}', { fileName: metadata.fileName || '' }),
				file_deleted: this.translate('domaincontrol', 'Deleted file: {fileName}', { fileName: metadata.fileName || '' }),
				task_created: this.translate('domaincontrol', 'Created task'),
				task_updated: this.translate('domaincontrol', 'Updated task'),
				task_completed: this.translate('domaincontrol', 'Completed task'),
				task_deleted: this.translate('domaincontrol', 'Deleted task'),
				time_started: this.translate('domaincontrol', 'Started time tracking'),
				time_stopped: this.translate('domaincontrol', 'Stopped time tracking'),
				time_updated: this.translate('domaincontrol', 'Updated time entry'),
				time_deleted: this.translate('domaincontrol', 'Deleted time entry'),
				project_created: this.translate('domaincontrol', 'Created project'),
				project_shared: this.translate('domaincontrol', 'Shared project with user'),
				project_unshared: this.translate('domaincontrol', 'Unshared project with user'),
				project_updated: this.translate('domaincontrol', 'Updated project'),
				item_linked: this.translate('domaincontrol', 'Linked {itemType} to project', { itemType: metadata.itemType || '' }),
				item_unlinked: this.translate('domaincontrol', 'Unlinked {itemType} from project', { itemType: metadata.itemType || '' }),
			}
			
			return descriptions[type] || activity.description || type
		},
		getUserDisplayName(userId) {
			const user = this.availableUsers.find(u => u.userId === userId)
			if (user) {
				return user.displayName || userId
			}
			return userId
		},
		formatTime(dateTime) {
			if (!dateTime) return ''
			const d = new Date(dateTime)
			const now = new Date()
			const diffMs = now - d
			const diffMins = Math.floor(diffMs / 60000)
			const diffHours = Math.floor(diffMs / 3600000)
			const diffDays = Math.floor(diffMs / 86400000)
			
			if (diffMins < 1) {
				return this.translate('domaincontrol', 'Just now')
			} else if (diffMins < 60) {
				return this.translate('domaincontrol', '{minutes} minutes ago', { minutes: diffMins })
			} else if (diffHours < 24) {
				return this.translate('domaincontrol', '{hours} hours ago', { hours: diffHours })
			} else if (diffDays < 7) {
				return this.translate('domaincontrol', '{days} days ago', { days: diffDays })
			} else {
				return d.toLocaleDateString('tr-TR', { year: 'numeric', month: 'short', day: 'numeric' })
			}
		},
	},
}
</script>

<style scoped>
.detail-section {
	margin-bottom: 24px;
}

.section-header {
	display: flex;
	align-items: center;
	justify-content: space-between;
	margin-bottom: 16px;
}

.section-title {
	display: flex;
	align-items: center;
	gap: 8px;
	margin: 0;
	font-size: 18px;
	font-weight: 600;
	color: var(--color-main-text);
}

.activity-list {
	display: flex;
	flex-direction: column;
	gap: 12px;
}

.activity-item {
	display: flex;
	align-items: flex-start;
	gap: 12px;
	padding: 12px;
	background-color: var(--color-main-background);
	border: 1px solid var(--color-border);
	border-radius: var(--border-radius-element);
}

.activity-icon {
	display: flex;
	align-items: center;
	justify-content: center;
	width: 32px;
	height: 32px;
	border-radius: 50%;
	background-color: var(--color-background-dark);
	color: var(--color-text-maxcontrast);
	flex-shrink: 0;
}

.activity-content {
	flex: 1;
}

.activity-description {
	font-size: 14px;
	color: var(--color-main-text);
	margin-bottom: 4px;
}

.activity-meta {
	display: flex;
	align-items: center;
	gap: 8px;
	font-size: 12px;
	color: var(--color-text-maxcontrast);
}

.activity-user {
	font-weight: 500;
}

.activity-time {
	color: var(--color-text-maxcontrast);
}
</style>

