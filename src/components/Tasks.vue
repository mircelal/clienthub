<template>
	<div class="tasks-view">
		<!-- Task Modal -->
		<TaskModal
			:open="modalOpen"
			:task="editingTask"
			:projects="projects"
			:clients="clients"
			:tasks="tasks"
			@close="closeModal"
			@saved="handleTaskSaved"
		/>

		<!-- Task List View -->
		<div v-if="!selectedTask" class="tasks-list-view">
			<div class="domaincontrol-actions">
				<button class="button-vue button-vue--primary" @click="showAddModal">
					<MaterialIcon name="add" :size="20" />
					{{ translate('domaincontrol', 'Add Task') }}
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
						:class="{ 'button-vue--primary': currentFilter === 'todo' }"
						@click="setFilter('todo')"
					>
						{{ translate('domaincontrol', 'To Do') }}
					</button>
					<button
						class="button-vue button-vue--secondary"
						:class="{ 'button-vue--primary': currentFilter === 'in_progress' }"
						@click="setFilter('in_progress')"
					>
						{{ translate('domaincontrol', 'In Progress') }}
					</button>
					<button
						class="button-vue button-vue--secondary"
						:class="{ 'button-vue--primary': currentFilter === 'done' }"
						@click="setFilter('done')"
					>
						{{ translate('domaincontrol', 'Done') }}
					</button>
					<button
						class="button-vue button-vue--secondary"
						:class="{ 'button-vue--primary': currentFilter === 'cancelled' }"
						@click="setFilter('cancelled')"
					>
						{{ translate('domaincontrol', 'Cancelled') }}
					</button>
				</div>
				<div class="task-search-wrapper">
					<input
						type="text"
						v-model="searchQuery"
						class="task-search-input"
						:placeholder="translate('domaincontrol', 'Search tasks...')"
						@input="filterTasks"
					/>
				</div>
			</div>

			<!-- Empty State -->
			<div v-if="filteredTasks.length === 0 && !loading" class="empty-content">
				<MaterialIcon name="checkmark" :size="48" color="var(--color-text-maxcontrast)" class="empty-content__icon" />
				<p class="empty-content__text">
					{{ searchQuery ? translate('domaincontrol', 'No tasks found') : translate('domaincontrol', 'No tasks yet') }}
				</p>
				<button v-if="!searchQuery" class="button-vue button-vue--primary" @click="showAddModal">
					{{ translate('domaincontrol', 'Add First Task') }}
				</button>
			</div>

			<!-- Loading State -->
			<div v-if="loading" class="loading-content">
				<MaterialIcon name="loading" :size="32" class="loading-icon" />
				<p>{{ translate('domaincontrol', 'Loading tasks...') }}</p>
			</div>

			<!-- Tasks List -->
			<div v-else-if="filteredTasks.length > 0" class="tasks-list">
				<div
					v-for="task in filteredTasks"
					:key="task.id"
					class="list-item task-item"
					:class="{
						'task-item--done': task.status === 'done',
						'task-item--cancelled': task.status === 'cancelled',
						'task-item--overdue': isOverdue(task),
					}"
					@click="selectTask(task)"
				>
					<div class="task-checkbox-wrapper" @click.stop>
						<input
							type="checkbox"
							class="task-checkbox"
							:checked="task.status === 'done'"
							:disabled="task.status === 'cancelled'"
							@change="toggleTaskStatus(task)"
						/>
					</div>
					<div class="priority-indicator" :class="`priority-${task.priority || 'medium'}`"></div>
					<div class="list-item__content">
						<div class="list-item__title" :class="`task-status--${task.status}`">
							{{ task.title }}
							<span v-if="isToday(task.dueDate)" class="status-badge status-badge--draft" style="margin-left: 8px; font-size: 10px;">
								{{ translate('domaincontrol', 'TODAY') }}
							</span>
						</div>
						<div class="list-item__meta">
							<span v-if="getProjectName(task.projectId)" style="color: var(--color-primary-element); font-weight: 500;">
								{{ getProjectName(task.projectId) }}
							</span>
							<span v-else style="color: var(--color-text-maxcontrast);">
								{{ translate('domaincontrol', 'General') }}
							</span>
							<span v-if="task.dueDate"> • {{ formatDate(task.dueDate) }}</span>
							<span v-if="getSubtasksCount(task) > 0" class="subtask-count">
								• {{ getSubtasksCount(task) }} {{ translate('domaincontrol', 'subtasks') }}
							</span>
						</div>
					</div>
					<div class="list-item__stats">
						<div class="list-item__stat">
							<div class="list-item__stat-label">{{ translate('domaincontrol', 'Priority') }}</div>
							<div class="list-item__stat-value">
								<span class="priority-badge" :class="`priority-badge--${task.priority || 'medium'}`">
									{{ getPriorityText(task.priority) }}
								</span>
							</div>
						</div>
						<div class="list-item__stat">
							<div class="list-item__stat-label">{{ translate('domaincontrol', 'Status') }}</div>
							<div class="list-item__stat-value">
								<span class="status-badge status-badge--simple" :class="getTaskStatusClass(task)">
									{{ getTaskStatusText(task.status) }}
								</span>
							</div>
						</div>
					</div>
					<div class="list-item__actions">
						<div class="popover-menu-wrapper" @click.stop>
							<button
								class="action-button action-button--more"
								@click.stop="togglePopover(task.id)"
								:title="translate('domaincontrol', 'More options')"
							>
								<MaterialIcon name="more-vertical" :size="18" />
							</button>
							<div
								v-if="openPopover === task.id"
								class="popover-menu"
								@click.stop
							>
								<button
									class="popover-menu-item"
									@click="editTask(task); closePopover()"
								>
									<MaterialIcon name="edit" :size="16" />
									{{ translate('domaincontrol', 'Edit') }}
								</button>
								<button
									v-if="task.status !== 'done'"
									class="popover-menu-item"
									@click="toggleTaskStatus(task); closePopover()"
								>
									<MaterialIcon name="checkmark" :size="16" />
									{{ translate('domaincontrol', 'Mark as Done') }}
								</button>
								<button
									v-if="task.status === 'done'"
									class="popover-menu-item"
									@click="toggleTaskStatus(task); closePopover()"
								>
									{{ translate('domaincontrol', 'Mark as To Do') }}
								</button>
								<div class="popover-menu-separator"></div>
								<button
									class="popover-menu-item popover-menu-item--danger"
									@click="confirmDelete(task); closePopover()"
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

		<!-- Task Detail View -->
		<div v-else class="task-detail-view">
			<div class="detail-header">
				<button class="button-vue button-vue--tertiary" @click="backToList">
					<MaterialIcon name="arrow-left" :size="20" />
					{{ translate('domaincontrol', 'Back') }}
				</button>
				<h2 class="detail-title">{{ selectedTask.title }}</h2>
				<div class="detail-actions">
					<button
						v-if="selectedTask.status !== 'done'"
						class="button-vue button-vue--primary"
						@click="toggleTaskStatus(selectedTask)"
					>
						<MaterialIcon name="checkmark" :size="20" />
						{{ translate('domaincontrol', 'Mark as Done') }}
					</button>
					<button
						v-else
						class="button-vue button-vue--secondary"
						@click="toggleTaskStatus(selectedTask)"
					>
						{{ translate('domaincontrol', 'Mark as To Do') }}
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
								@click="editTask(selectedTask); closeDetailPopover()"
							>
								<MaterialIcon name="edit" :size="16" />
								{{ translate('domaincontrol', 'Edit') }}
							</button>
							<div class="popover-menu-separator"></div>
							<button
								class="popover-menu-item popover-menu-item--danger"
								@click="confirmDelete(selectedTask); closeDetailPopover()"
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
							<MaterialIcon name="folder" :size="24" />
						</div>
						<div class="stat-card__content">
							<div class="stat-card__label">{{ translate('domaincontrol', 'Project') }}</div>
							<div class="stat-card__value">
								<a
									v-if="selectedTask.projectId"
									href="#"
									@click.prevent="navigateToProject(selectedTask.projectId)"
									class="link-primary"
								>
									{{ getProjectName(selectedTask.projectId) }}
								</a>
								<span v-else>{{ translate('domaincontrol', 'General') }}</span>
							</div>
						</div>
					</div>
					<div class="stat-card">
						<div class="stat-card__icon" :class="getTaskStatusClass(selectedTask)">
							<MaterialIcon name="checkmark" :size="24" />
						</div>
						<div class="stat-card__content">
							<div class="stat-card__label">{{ translate('domaincontrol', 'Status') }}</div>
							<div class="stat-card__value">
								<span class="status-badge" :class="getTaskStatusClass(selectedTask)">
									{{ getTaskStatusText(selectedTask.status) }}
								</span>
							</div>
						</div>
					</div>
					<div class="stat-card">
						<div class="stat-card__icon" :class="`priority-${selectedTask.priority || 'medium'}`">
							<MaterialIcon name="settings" :size="24" />
						</div>
						<div class="stat-card__content">
							<div class="stat-card__label">{{ translate('domaincontrol', 'Priority') }}</div>
							<div class="stat-card__value">
								<span class="priority-badge" :class="`priority-badge--${selectedTask.priority || 'medium'}`">
									{{ getPriorityText(selectedTask.priority) }}
								</span>
							</div>
						</div>
					</div>
					<div class="stat-card">
						<div class="stat-card__icon" :class="getDueDateClass(selectedTask)">
							<MaterialIcon name="calendar" :size="24" />
						</div>
						<div class="stat-card__content">
							<div class="stat-card__label">{{ translate('domaincontrol', 'Due Date') }}</div>
							<div class="stat-card__value" :class="getDueDateClass(selectedTask)">
								{{ formatDate(selectedTask.dueDate) || '-' }}
							</div>
						</div>
					</div>
				</div>

				<!-- Info Grid -->
				<div class="detail-info-grid">
					<div class="detail-info-card">
						<h3 class="info-card-title">{{ translate('domaincontrol', 'Description') }}</h3>
						<div class="detail-description">
							{{ selectedTask.description || translate('domaincontrol', 'No description') }}
						</div>
					</div>

					<div class="detail-info-card">
						<h3 class="info-card-title">{{ translate('domaincontrol', 'Notes') }}</h3>
						<div class="detail-notes" v-html="selectedTask.notes || translate('domaincontrol', 'No notes')"></div>
					</div>
				</div>

				<!-- Subtasks Section -->
				<div class="detail-info-card subtasks-section">
					<div class="section-header">
						<h3 class="info-card-title">{{ translate('domaincontrol', 'Subtasks') }}</h3>
						<button class="button-vue button-vue--primary" @click="showAddSubtaskModal">
							<MaterialIcon name="add" :size="18" />
							{{ translate('domaincontrol', 'Add Subtask') }}
						</button>
					</div>
					<div v-if="subtasks.length === 0" class="empty-content">
						<p class="empty-content__text">{{ translate('domaincontrol', 'No subtasks yet') }}</p>
					</div>
					<div v-else class="subtasks-list">
						<div
							v-for="subtask in subtasks"
							:key="subtask.id"
							class="subtask-item"
							:class="{
								'subtask-item--done': subtask.status === 'done',
							}"
						>
							<input
								type="checkbox"
								class="subtask-checkbox"
								:checked="subtask.status === 'done'"
								@change="toggleSubtaskStatus(subtask)"
							/>
							<span class="subtask-title" :class="`task-status--${subtask.status}`">
								{{ subtask.title }}
							</span>
							<button
								class="subtask-delete-btn"
								@click="confirmDeleteSubtask(subtask)"
								:title="translate('domaincontrol', 'Delete')"
							>
								<MaterialIcon name="delete" :size="16" />
							</button>
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
import TaskModal from './TaskModal.vue'

export default {
	name: 'Tasks',
	components: {
		MaterialIcon,
		TaskModal,
	},
	data() {
		return {
			tasks: [],
			projects: [],
			clients: [],
			selectedTask: null,
			subtasks: [],
			loading: false,
			modalOpen: false,
			editingTask: null,
			currentFilter: 'all',
			searchQuery: '',
			openPopover: null,
			detailPopoverOpen: false,
		}
	},
	computed: {
		filteredTasks() {
			let filtered = this.tasks.filter(t => !t.parentId) // Only show top-level tasks

			// Apply status filter
			if (this.currentFilter !== 'all') {
				filtered = filtered.filter(t => t.status === this.currentFilter)
			}

			// Apply search query
			if (this.searchQuery) {
				const query = this.searchQuery.toLowerCase()
				filtered = filtered.filter(task => {
					const title = (task.title || '').toLowerCase()
					const projectName = this.getProjectName(task.projectId) || ''
					return (
						title.includes(query) ||
						projectName.toLowerCase().includes(query)
					)
				})
			}

			return filtered
		},
	},
	watch: {
		selectedTask(newVal) {
			if (newVal && newVal.id) {
				this.loadSubtasks(newVal.id)
			} else {
				this.subtasks = []
			}
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
					this.loadTasks(),
					this.loadProjects(),
					this.loadClients(),
				])
			} catch (error) {
				console.error('Error loading data:', error)
			} finally {
				this.loading = false
			}
		},
		async loadTasks() {
			try {
				const response = await api.tasks.getAll()
				this.tasks = response.data || []
			} catch (error) {
				console.error('Error loading tasks:', error)
				this.tasks = []
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
		async loadSubtasks(taskId) {
			try {
				const response = await api.tasks.getSubtasks(taskId)
				this.subtasks = response.data || []
			} catch (error) {
				console.error('Error loading subtasks:', error)
				this.subtasks = []
			}
		},
		filterTasks() {
			// Computed property handles filtering
		},
		setFilter(filter) {
			this.currentFilter = filter
		},
		selectTask(task) {
			this.selectedTask = task
		},
		backToList() {
			this.selectedTask = null
		},
		showAddModal() {
			this.editingTask = null
			this.modalOpen = true
		},
		showAddSubtaskModal() {
			if (!this.selectedTask) return
			this.editingTask = {
				parentId: this.selectedTask.id,
				projectId: this.selectedTask.projectId,
				clientId: this.selectedTask.clientId,
			}
			this.modalOpen = true
		},
		editTask(task) {
			this.editingTask = task
			this.modalOpen = true
		},
		closeModal() {
			this.modalOpen = false
			this.editingTask = null
		},
		async handleTaskSaved() {
			await this.loadTasks()
			if (this.selectedTask) {
				const response = await api.tasks.get(this.selectedTask.id)
				this.selectedTask = response.data
			}
			this.closeModal()
		},
		async toggleTaskStatus(task) {
			try {
				const response = await api.tasks.toggleStatus(task.id)
				await this.loadTasks()
				if (this.selectedTask && this.selectedTask.id === task.id) {
					this.selectedTask = response.data
				}
			} catch (error) {
				console.error('Error toggling task status:', error)
			}
		},
		async toggleSubtaskStatus(subtask) {
			try {
				await api.tasks.toggleStatus(subtask.id)
				await this.loadSubtasks(this.selectedTask.id)
			} catch (error) {
				console.error('Error toggling subtask status:', error)
			}
		},
		async confirmDelete(task) {
			if (!confirm(this.translate('domaincontrol', 'Are you sure you want to delete this task?'))) {
				return
			}
			try {
				await api.tasks.delete(task.id)
				await this.loadTasks()
				if (this.selectedTask && this.selectedTask.id === task.id) {
					this.backToList()
				}
			} catch (error) {
				console.error('Error deleting task:', error)
				alert(this.translate('domaincontrol', 'Error deleting task'))
			}
		},
		async confirmDeleteSubtask(subtask) {
			if (!confirm(this.translate('domaincontrol', 'Are you sure you want to delete this subtask?'))) {
				return
			}
			try {
				await api.tasks.delete(subtask.id)
				await this.loadSubtasks(this.selectedTask.id)
			} catch (error) {
				console.error('Error deleting subtask:', error)
				alert(this.translate('domaincontrol', 'Error deleting subtask'))
			}
		},
		getProjectName(projectId) {
			if (!projectId) return ''
			const project = this.projects.find(p => p.id === projectId)
			return project ? project.name : ''
		},
		navigateToProject(projectId) {
			if (typeof window.DomainControl !== 'undefined' && window.DomainControl.switchTab) {
				window.DomainControl.switchTab('projects')
				setTimeout(() => {
					const event = new CustomEvent('select-project', { detail: { projectId } })
					window.dispatchEvent(event)
				}, 100)
			}
		},
		getSubtasksCount(task) {
			return this.tasks.filter(t => t.parentId === task.id).length
		},
		getTaskStatusClass(task) {
			return `status-${task.status || 'todo'}`
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
		isOverdue(task) {
			if (!task.dueDate || task.status === 'done' || task.status === 'cancelled') return false
			const dueDate = new Date(task.dueDate)
			const today = new Date()
			today.setHours(0, 0, 0, 0)
			return dueDate < today
		},
		isToday(date) {
			if (!date) return false
			const taskDate = new Date(date)
			const today = new Date()
			today.setHours(0, 0, 0, 0)
			taskDate.setHours(0, 0, 0, 0)
			return taskDate.getTime() === today.getTime()
		},
		getDueDateClass(task) {
			if (!task.dueDate) return ''
			if (task.status === 'done' || task.status === 'cancelled') return ''
			const dueDate = new Date(task.dueDate)
			const today = new Date()
			today.setHours(0, 0, 0, 0)
			if (dueDate < today) return 'status-critical'
			const diffTime = dueDate - today
			const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24))
			if (diffDays <= 3) return 'status-warning'
			return 'status-ok'
		},
		togglePopover(taskId) {
			this.openPopover = this.openPopover === taskId ? null : taskId
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
				'Add Task': 'Görev Ekle',
				'Search tasks...': 'Görevlerde ara...',
				'No tasks found': 'Görev bulunamadı',
				'No tasks yet': 'Henüz görev yok',
				'Add First Task': 'İlk Görevi Ekle',
				'Loading tasks...': 'Görevler yükleniyor...',
				'All': 'Tümü',
				'To Do': 'Yapılacak',
				'In Progress': 'Devam Ediyor',
				'Done': 'Tamamlandı',
				'Cancelled': 'İptal Edildi',
				'Priority': 'Öncelik',
				'Status': 'Durum',
				'More options': 'Daha fazla seçenek',
				'Edit': 'Düzenle',
				'Mark as Done': 'Tamamlandı Olarak İşaretle',
				'Mark as To Do': 'Yapılacak Olarak İşaretle',
				'Delete': 'Sil',
				'Back': 'Geri',
				'Project': 'Proje',
				'General': 'Genel',
				'Due Date': 'Bitiş Tarihi',
				'Description': 'Açıklama',
				'No description': 'Açıklama yok',
				'Notes': 'Notlar',
				'No notes': 'Not yok',
				'Subtasks': 'Alt Görevler',
				'Add Subtask': 'Alt Görev Ekle',
				'No subtasks yet': 'Henüz alt görev yok',
				'Low': 'Düşük',
				'Medium': 'Orta',
				'High': 'Yüksek',
				'TODAY': 'BUGÜN',
				'subtasks': 'alt görev',
				'Are you sure you want to delete this task?': 'Bu görevi silmek istediğinize emin misiniz?',
				'Error deleting task': 'Görev silinirken hata oluştu',
				'Are you sure you want to delete this subtask?': 'Bu alt görevi silmek istediğinize emin misiniz?',
				'Error deleting subtask': 'Alt görev silinirken hata oluştu',
			}

			return translations[text] || text
		},
	},
}
</script>

<style scoped>
.tasks-view {
	width: 100%;
	height: 100%;
}

.tasks-list-view {
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

.task-search-wrapper {
	flex: 1;
	min-width: 200px;
}

.task-search-input {
	width: 100%;
	padding: 8px 12px;
	border: 1px solid var(--color-border);
	border-radius: var(--border-radius-element);
	background-color: var(--color-background-dark);
	color: var(--color-main-text);
	font-size: 14px;
}

.task-search-input:focus {
	outline: none;
	border-color: var(--color-primary-element);
}

.tasks-list {
	display: grid;
	gap: 12px;
}

.task-item {
	cursor: pointer;
	background-color: var(--color-main-background);
	border: 1px solid var(--color-border);
	border-radius: var(--border-radius-element);
	padding: 12px;
	display: flex;
	align-items: center;
	gap: 12px;
	transition: background-color 0.2s ease;
	position: relative;
}

.task-item:hover {
	background-color: var(--color-background-hover);
}

.task-item--done {
	opacity: 0.7;
}

.task-item--cancelled {
	opacity: 0.5;
	text-decoration: line-through;
}

.task-item--overdue {
	border-left: 4px solid var(--color-error);
}

.task-checkbox-wrapper {
	display: flex;
	align-items: center;
	justify-content: center;
	flex-shrink: 0;
}

.task-checkbox {
	width: 22px;
	height: 22px;
	cursor: pointer;
}

.priority-indicator {
	width: 4px;
	height: 100%;
	border-radius: 2px;
	flex-shrink: 0;
}

.priority-low {
	background-color: var(--color-success);
}

.priority-medium {
	background-color: var(--color-warning);
}

.priority-high {
	background-color: var(--color-error);
}

.task-item .list-item__content {
	flex: 1;
	min-width: 0;
}

.task-item .list-item__title {
	font-size: 16px;
	font-weight: 500;
	color: var(--color-main-text);
	margin-bottom: 4px;
}

.task-item .list-item__title.task-status--done {
	text-decoration: line-through;
	opacity: 0.7;
}

.task-item .list-item__meta {
	display: flex;
	align-items: center;
	gap: 12px;
	font-size: 13px;
	color: var(--color-text-maxcontrast);
	flex-wrap: wrap;
}

.task-item .list-item__stats {
	display: flex;
	gap: 24px;
	align-items: center;
}

.task-item .list-item__stat {
	display: flex;
	flex-direction: column;
	gap: 4px;
	min-width: 100px;
}

.task-item .list-item__stat-label {
	font-size: 12px;
	color: var(--color-text-maxcontrast);
}

.task-item .list-item__stat-value {
	font-size: 14px;
	font-weight: 500;
	color: var(--color-main-text);
}

.task-item .list-item__actions {
	display: flex;
	align-items: center;
	gap: 8px;
	flex-shrink: 0;
}

.subtask-count {
	color: var(--color-text-maxcontrast);
	font-size: 12px;
}

.priority-badge {
	padding: 4px 8px;
	border-radius: var(--border-radius-pill);
	font-size: 12px;
	font-weight: 500;
}

.priority-badge--low {
	background-color: var(--color-success);
	color: var(--color-success-text);
}

.priority-badge--medium {
	background-color: var(--color-warning);
	color: var(--color-warning-text);
}

.priority-badge--high {
	background-color: var(--color-error);
	color: var(--color-error-text);
}

.task-detail-view {
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

.subtasks-section {
	margin-top: 20px;
}

.section-header {
	display: flex;
	justify-content: space-between;
	align-items: center;
	margin-bottom: 16px;
}

.subtasks-list {
	display: flex;
	flex-direction: column;
	gap: 8px;
}

.subtask-item {
	display: flex;
	align-items: center;
	gap: 12px;
	padding: 12px;
	background-color: var(--color-main-background);
	border: 1px solid var(--color-border);
	border-radius: var(--border-radius-element);
}

.subtask-item--done {
	opacity: 0.7;
}

.subtask-checkbox {
	width: 20px;
	height: 20px;
	cursor: pointer;
}

.subtask-title {
	flex: 1;
	font-size: 14px;
	color: var(--color-main-text);
}

.subtask-title.task-status--done {
	text-decoration: line-through;
	opacity: 0.7;
}

.subtask-delete-btn {
	background: none;
	border: none;
	color: var(--color-text-error);
	cursor: pointer;
	padding: 4px;
	border-radius: var(--border-radius-small);
	transition: background-color 0.2s;
	display: flex;
	align-items: center;
	justify-content: center;
}

.subtask-delete-btn:hover {
	background-color: var(--color-error);
	color: var(--color-error-text);
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

.status-todo {
	background-color: var(--color-text-maxcontrast);
	color: var(--color-main-background);
	opacity: 0.7;
}

.status-in_progress {
	background-color: var(--color-primary-element);
	color: var(--color-primary-element-text);
}

.status-done {
	background-color: var(--color-success);
	color: var(--color-success-text);
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
