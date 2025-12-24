<template>
	<div class="tab-content">
		<div class="detail-section">
			<div class="section-header">
				<h3 class="section-title">
					<MaterialIcon name="monitoring" :size="24" />
					{{ translate('domaincontrol', 'Time Tracking') }}
				</h3>
			</div>
			<div class="section-content">
				<!-- Debug Info -->
				<div v-if="false" style="padding: 10px; background: #f0f0f0; margin-bottom: 10px; font-size: 12px;">
					Debug: loading={{ loading }}, timeEntries.length={{ timeEntries ? timeEntries.length : 'null' }}, totalTime={{ totalTime }}
				</div>
				<!-- Timer Controls -->
				<div class="timer-controls">
					<div class="timer-display" v-if="currentRunningEntry">
						<div class="timer-time">{{ formatTimerTime(timerElapsed) }}</div>
						<div class="timer-description">{{ currentRunningEntry.description || translate('domaincontrol', 'No description') }}</div>
					</div>
					<div class="timer-buttons">
						<button
							v-if="!currentRunningEntry"
							class="button-vue button-vue--primary"
							@click="$emit('start-timer')"
							:disabled="timerStarting"
						>
							<MaterialIcon name="add" :size="18" />
							{{ translate('domaincontrol', 'Start Timer') }}
						</button>
						<button
							v-else
							class="button-vue button-vue--danger"
							@click="$emit('stop-timer')"
							:disabled="timerStopping"
						>
							<MaterialIcon name="close" :size="18" />
							{{ translate('domaincontrol', 'Stop Timer') }}
						</button>
					</div>
					<input
						:value="timerDescription"
						@input="$emit('update:timerDescription', $event.target.value)"
						type="text"
						class="form-control timer-description-input"
						:placeholder="translate('domaincontrol', 'Task description (optional)')"
					/>
				</div>

				<!-- Total Time -->
				<div class="time-summary">
					<div class="time-summary-item">
						<span class="time-label">{{ translate('domaincontrol', 'Total Time') }}:</span>
						<span class="time-value">{{ formatDuration(totalTime) }}</span>
					</div>
				</div>

				<!-- Time by User -->
				<div v-if="durationByUser.length > 0" class="user-time-summary">
					<div class="user-time-header">
						<strong>{{ translate('domaincontrol', 'Time by User') }}</strong>
					</div>
					<div class="user-time-list">
						<div
							v-for="userTime in durationByUser"
							:key="userTime.user_id"
							class="user-time-item"
						>
							<div class="user-time-info">
								<span class="user-time-name">{{ getUserDisplayName(userTime.user_id) }}</span>
								<span class="user-time-count">{{ userTime.entry_count }} {{ translate('domaincontrol', 'entries') }}</span>
							</div>
							<span class="user-time-duration">{{ formatDuration(userTime.total_duration) }}</span>
						</div>
					</div>
				</div>

				<!-- Time Entries List -->
				<div v-if="loading" class="loading-content">
					<MaterialIcon name="loading" :size="32" class="loading-icon" />
					<p>{{ translate('domaincontrol', 'Loading time entries...') }}</p>
				</div>
				<div v-else-if="timeEntries.length === 0" class="empty-content">
					<p class="empty-content__text">{{ translate('domaincontrol', 'No time entries yet') }}</p>
				</div>
				<div v-else class="time-entries-list">
					<div
						v-for="entry in timeEntries"
						:key="entry.id"
						class="time-entry-item"
					>
						<div class="time-entry-content">
							<div class="time-entry-description">{{ entry.description || translate('domaincontrol', 'No description') }}</div>
							<div class="time-entry-meta">
								<span>{{ formatDate(entry.startTime) }}</span>
								<span v-if="entry.endTime">{{ formatTime(entry.startTime) }} - {{ formatTime(entry.endTime) }}</span>
								<span v-else class="time-entry-running">{{ translate('domaincontrol', 'Running...') }}</span>
							</div>
						</div>
						<div class="time-entry-duration">{{ formatDuration(entry.duration) }}</div>
						<button
							class="action-button action-button--delete"
							@click.stop="$emit('delete-entry', entry.id)"
							:title="translate('domaincontrol', 'Delete')"
						>
							<MaterialIcon name="delete" :size="16" />
						</button>
					</div>
				</div>
			</div>
		</div>
	</div>
</template>

<script>
import MaterialIcon from '../MaterialIcon.vue'

export default {
	name: 'ProjectTimeTracking',
	components: {
		MaterialIcon,
	},
	props: {
		timeEntries: {
			type: Array,
			default: () => [],
		},
		loading: {
			type: Boolean,
			default: false,
		},
		currentRunningEntry: {
			type: Object,
			default: null,
		},
		timerElapsed: {
			type: Number,
			default: 0,
		},
		timerDescription: {
			type: String,
			default: '',
		},
		timerStarting: {
			type: Boolean,
			default: false,
		},
		timerStopping: {
			type: Boolean,
			default: false,
		},
		totalTime: {
			type: Number,
			default: 0,
		},
		durationByUser: {
			type: Array,
			default: () => [],
		},
		availableUsers: {
			type: Array,
			default: () => [],
		},
	},
	emits: ['start-timer', 'stop-timer', 'update:timerDescription', 'delete-entry'],
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
		formatTimerTime(seconds) {
			const hours = Math.floor(seconds / 3600)
			const minutes = Math.floor((seconds % 3600) / 60)
			const secs = seconds % 60
			return `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}:${String(secs).padStart(2, '0')}`
		},
		formatDuration(seconds) {
			const hours = Math.floor(seconds / 3600)
			const minutes = Math.floor((seconds % 3600) / 60)
			if (hours > 0) {
				return `${hours}h ${minutes}m`
			}
			return `${minutes}m`
		},
		formatDate(date) {
			if (!date) return ''
			const d = new Date(date + ' UTC')
			return d.toLocaleDateString('tr-TR')
		},
		formatTime(dateTime) {
			if (!dateTime) return ''
			const d = new Date(dateTime + ' UTC')
			return d.toLocaleTimeString('tr-TR', { hour: '2-digit', minute: '2-digit' })
		},
		getUserDisplayName(userId) {
			const user = this.availableUsers.find(u => u.userId === userId)
			if (user) {
				return user.displayName || userId
			}
			return userId
		},
	},
}
</script>

<style scoped>
.tab-content {
	padding: 20px;
}

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

.timer-controls {
	padding: 16px;
	background-color: var(--color-main-background);
	border: 1px solid var(--color-border);
	border-radius: var(--border-radius-element);
	margin-bottom: 20px;
}

.timer-display {
	margin-bottom: 12px;
}

.timer-time {
	font-size: 32px;
	font-weight: 600;
	color: var(--color-primary-element-element-element);
	margin-bottom: 4px;
}

.timer-description {
	font-size: 14px;
	color: var(--color-text-maxcontrast);
}

.timer-buttons {
	margin-bottom: 12px;
}

.timer-description-input {
	width: 100%;
}

.time-summary {
	padding: 16px;
	background-color: var(--color-main-background);
	border: 1px solid var(--color-border);
	border-radius: var(--border-radius-element);
	margin-bottom: 20px;
}

.time-summary-item {
	display: flex;
	justify-content: space-between;
	align-items: center;
}

.time-label {
	font-size: 14px;
	color: var(--color-text-maxcontrast);
}

.time-value {
	font-size: 18px;
	font-weight: 600;
	color: var(--color-main-text);
}

.user-time-summary {
	margin-bottom: 20px;
}

.user-time-header {
	margin-bottom: 12px;
	font-size: 14px;
	color: var(--color-main-text);
}

.user-time-list {
	display: flex;
	flex-direction: column;
	gap: 8px;
}

.user-time-item {
	display: flex;
	justify-content: space-between;
	align-items: center;
	padding: 12px;
	background-color: var(--color-main-background);
	border: 1px solid var(--color-border);
	border-radius: var(--border-radius-element);
}

.user-time-info {
	display: flex;
	flex-direction: column;
	gap: 4px;
}

.user-time-name {
	font-size: 14px;
	font-weight: 500;
	color: var(--color-main-text);
}

.user-time-count {
	font-size: 12px;
	color: var(--color-text-maxcontrast);
}

.user-time-duration {
	font-size: 16px;
	font-weight: 600;
	color: var(--color-primary-element-element-element);
}

.time-entries-list {
	display: flex;
	flex-direction: column;
	gap: 8px;
}

.time-entry-item {
	display: flex;
	align-items: center;
	gap: 12px;
	padding: 12px;
	background-color: var(--color-main-background);
	border: 1px solid var(--color-border);
	border-radius: var(--border-radius-element);
}

.time-entry-content {
	flex: 1;
}

.time-entry-description {
	font-size: 14px;
	font-weight: 500;
	color: var(--color-main-text);
	margin-bottom: 4px;
}

.time-entry-meta {
	display: flex;
	gap: 8px;
	font-size: 12px;
	color: var(--color-text-maxcontrast);
}

.time-entry-running {
	color: var(--color-element-success);
	font-weight: 500;
}

.time-entry-duration {
	font-size: 14px;
	font-weight: 600;
	color: var(--color-primary-element-element-element);
}
</style>

