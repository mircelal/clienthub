<template>
	<div class="tab-content">
		<div class="detail-section">
			<div class="section-header">
				<h3 class="section-title">
					<MaterialIcon name="checkmark" :size="24" />
					{{ translate('domaincontrol', 'Tasks') }}
				</h3>
				<button class="button-vue button-vue--primary" @click="$emit('add-task')">
					<MaterialIcon name="add" :size="18" />
					{{ translate('domaincontrol', 'Add Task') }}
				</button>
			</div>
			<div class="section-content">
				<!-- Debug Info -->
				<div v-if="false" style="padding: 10px; background: #f0f0f0; margin-bottom: 10px; font-size: 12px;">
					Debug: loading={{ loading }}, tasks.length={{ tasks ? tasks.length : 'null' }}, tasks={{ tasks }}
				</div>
				<div v-if="loading" class="loading-content">
					<MaterialIcon name="loading" :size="32" class="loading-icon" />
					<p>{{ translate('domaincontrol', 'Loading tasks...') }}</p>
				</div>
				<div v-else-if="!tasks || tasks.length === 0" class="empty-content">
					<MaterialIcon name="checkmark" :size="48" color="var(--color-text-maxcontrast)" class="empty-content__icon" />
					<p class="empty-content__text">{{ translate('domaincontrol', 'No tasks yet') }}</p>
				</div>
				<div v-else>
					<!-- Progress Bar -->
					<div class="project-progress">
						<div class="progress-header">
							<span>
								<strong>{{ translate('domaincontrol', 'Progress') }}:</strong>
								{{ completedTasks }}/{{ activeTasks }} {{ translate('domaincontrol', 'tasks completed') }}
								<span v-if="cancelledTasks > 0">({{ cancelledTasks }} {{ translate('domaincontrol', 'cancelled') }})</span>
							</span>
							<span><strong>{{ progressPercentage }}%</strong></span>
						</div>
						<div class="progress-bar">
							<div class="progress-fill" :style="{ width: progressPercentage + '%' }"></div>
						</div>
					</div>

					<!-- Tasks List -->
					<div class="project-tasks-list">
						<div
							v-for="task in tasks"
							:key="task.id"
							class="project-task-item"
							:class="{ 'task-overdue': isTaskOverdue(task), 'task-cancelled': task.status === 'cancelled' }"
							@click="$emit('navigate-task', task.id)"
						>
							<input
								type="checkbox"
								class="task-checkbox"
								:checked="task.status === 'done'"
								:disabled="task.status === 'cancelled'"
								@click.stop="$emit('toggle-status', task)"
							/>
							<div class="task-content">
								<div class="task-title" :class="'task-status--' + task.status">
									{{ task.title }}
								</div>
								<div class="task-meta">
									<span class="priority-badge" :class="'priority-badge--' + task.priority">
										{{ getPriorityText(task.priority) }}
									</span>
									<span v-if="task.dueDate">{{ formatDate(task.dueDate) }}</span>
									<span v-if="task.assignedToUserId" class="task-assigned">
										👤 {{ task.assignedToUserId }}
									</span>
									<span v-if="task.status === 'done' && task.completedByUserId" class="task-completed">
										✓ {{ translate('domaincontrol', 'Completed by') }} {{ task.completedByUserId }}
									</span>
								</div>
							</div>
							<span class="status-badge" :class="'task-status--' + task.status">
								{{ getTaskStatusText(task.status) }}
							</span>
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
	name: 'ProjectTasks',
	components: {
		MaterialIcon,
	},
	props: {
		tasks: {
			type: Array,
			default: () => [],
		},
		loading: {
			type: Boolean,
			default: false,
		},
	},
	emits: ['add-task', 'navigate-task', 'toggle-status'],
	computed: {
		activeTasks() {
			return this.tasks.filter(t => t.status !== 'cancelled').length
		},
		completedTasks() {
			return this.tasks.filter(t => t.status === 'done').length
		},
		cancelledTasks() {
			return this.tasks.filter(t => t.status === 'cancelled').length
		},
		progressPercentage() {
			if (this.activeTasks === 0) return 0
			return Math.round((this.completedTasks / this.activeTasks) * 100)
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
		isTaskOverdue(task) {
			if (!task.dueDate || task.status === 'done' || task.status === 'cancelled') return false
			const dueDate = new Date(task.dueDate)
			const today = new Date()
			today.setHours(0, 0, 0, 0)
			dueDate.setHours(0, 0, 0, 0)
			return dueDate < today
		},
		getTaskStatusText(status) {
			const statusTexts = {
				todo: this.translate('domaincontrol', 'To Do'),
				in_progress: this.translate('domaincontrol', 'In Progress'),
				done: this.translate('domaincontrol', 'Done'),
				cancelled: this.translate('domaincontrol', 'Cancelled'),
			}
			return statusTexts[status] || status
		},
		getPriorityText(priority) {
			const priorities = {
				low: this.translate('domaincontrol', 'Low'),
				medium: this.translate('domaincontrol', 'Medium'),
				high: this.translate('domaincontrol', 'High'),
			}
			return priorities[priority] || priority
		},
		formatDate(date) {
			if (!date) return ''
			const d = new Date(date)
			return d.toLocaleDateString('tr-TR')
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

.project-progress {
	margin-bottom: 20px;
	padding: 16px;
	background-color: var(--color-main-background);
	border: 1px solid var(--color-border);
	border-radius: var(--border-radius-element);
}

.progress-header {
	display: flex;
	justify-content: space-between;
	align-items: center;
	margin-bottom: 8px;
	font-size: 14px;
	color: var(--color-main-text);
}

.progress-bar {
	width: 100%;
	height: 8px;
	background-color: var(--color-background-dark);
	border-radius: 4px;
	overflow: hidden;
}

.progress-fill {
	height: 100%;
	background-color: var(--color-success);
	transition: width 0.3s;
}

.project-tasks-list {
	display: flex;
	flex-direction: column;
	gap: 8px;
}

.project-task-item {
	display: flex;
	align-items: center;
	gap: 12px;
	padding: 12px;
	background-color: var(--color-main-background);
	border: 1px solid var(--color-border);
	border-radius: var(--border-radius-element);
	cursor: pointer;
	transition: background-color 0.2s;
}

.project-task-item:hover {
	background-color: var(--color-background-dark);
}

.task-overdue {
	border-left: 3px solid var(--color-error);
}

.task-cancelled {
	opacity: 0.6;
}

.task-checkbox {
	width: 18px;
	height: 18px;
	cursor: pointer;
}

.task-content {
	flex: 1;
}

.task-title {
	font-size: 14px;
	font-weight: 500;
	color: var(--color-main-text);
	margin-bottom: 4px;
}

.task-meta {
	display: flex;
	align-items: center;
	gap: 8px;
	font-size: 12px;
	color: var(--color-text-maxcontrast);
}

.priority-badge {
	padding: 2px 8px;
	border-radius: 12px;
	font-size: 11px;
	font-weight: 500;
}

.priority-badge--low {
	background-color: var(--color-success);
	color: white;
}

.priority-badge--medium {
	background-color: var(--color-warning);
	color: white;
}

.priority-badge--high {
	background-color: var(--color-error);
	color: white;
}

.status-badge {
	padding: 4px 8px;
	border-radius: 12px;
	font-size: 11px;
	font-weight: 500;
}

.task-status--todo {
	background-color: var(--color-background-dark);
	color: var(--color-main-text);
}

.task-status--in_progress {
	background-color: var(--color-primary-element-light);
	color: var(--color-primary-element-text-dark);
}

.task-status--done {
	background-color: var(--color-success);
	color: white;
}

.task-status--cancelled {
	background-color: var(--color-text-maxcontrast);
	color: white;
}
</style>

