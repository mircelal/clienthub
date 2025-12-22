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

		<!-- Task Modal -->
		<TaskModal
			:open="taskModalOpen"
			:task="editingTask"
			:projects="projects"
			:clients="clients"
			:tasks="projectTasks"
			@close="closeTaskModal"
			@saved="handleTaskSaved"
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

				<!-- Tasks Section -->
				<div class="detail-section">
					<div class="section-header">
						<h3 class="section-title">
							<MaterialIcon name="checkmark" :size="24" />
							{{ translate('domaincontrol', 'Tasks') }}
						</h3>
						<button class="button-vue button-vue--primary" @click="showAddTaskModal">
							<MaterialIcon name="add" :size="18" />
							{{ translate('domaincontrol', 'Add Task') }}
						</button>
					</div>
					<div class="section-content">
						<div v-if="projectTasksLoading" class="loading-content">
							<MaterialIcon name="loading" :size="32" class="loading-icon" />
							<p>{{ translate('domaincontrol', 'Loading tasks...') }}</p>
						</div>
						<div v-else-if="projectTasks.length === 0" class="empty-content">
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
									v-for="task in projectTasks"
									:key="task.id"
									class="project-task-item"
									:class="{ 'task-overdue': isTaskOverdue(task), 'task-cancelled': task.status === 'cancelled' }"
									@click="navigateToTask(task.id)"
								>
									<input
										type="checkbox"
										class="task-checkbox"
										:checked="task.status === 'done'"
										:disabled="task.status === 'cancelled'"
										@click.stop="toggleTaskStatus(task)"
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

				<!-- Time Tracking Section -->
				<div class="detail-section">
					<div class="section-header">
						<h3 class="section-title">
							<MaterialIcon name="monitoring" :size="24" />
							{{ translate('domaincontrol', 'Time Tracking') }}
						</h3>
					</div>
					<div class="section-content">
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
									@click="startTimer"
									:disabled="timerStarting"
								>
									<MaterialIcon name="add" :size="18" />
									{{ translate('domaincontrol', 'Start Timer') }}
								</button>
								<button
									v-else
									class="button-vue button-vue--danger"
									@click="stopTimer"
									:disabled="timerStopping"
								>
									<MaterialIcon name="close" :size="18" />
									{{ translate('domaincontrol', 'Stop Timer') }}
								</button>
							</div>
							<input
								v-model="timerDescription"
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

						<!-- Time Entries List -->
						<div v-if="timeEntriesLoading" class="loading-content">
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
									@click.stop="deleteTimeEntry(entry.id)"
									:title="translate('domaincontrol', 'Delete')"
								>
									<MaterialIcon name="delete" :size="16" />
								</button>
							</div>
						</div>
					</div>
				</div>

				<!-- Financials Section -->
				<div class="detail-section">
					<div class="section-header">
						<h3 class="section-title">
							<MaterialIcon name="accounting" :size="24" />
							{{ translate('domaincontrol', 'Financials') }}
						</h3>
						<button class="button-vue button-vue--primary" @click="createInvoiceFromProject">
							<MaterialIcon name="add" :size="18" />
							{{ translate('domaincontrol', 'Create Invoice') }}
						</button>
					</div>
					<div class="section-content">
						<div class="financial-summary">
							<div class="financial-item">
								<span class="financial-label">{{ translate('domaincontrol', 'Budget') }}:</span>
								<span class="financial-value">{{ formatCurrency(selectedProject.budget, selectedProject.currency) }}</span>
							</div>
							<div v-if="totalInvoiced > 0" class="financial-item">
								<span class="financial-label">{{ translate('domaincontrol', 'Total Invoiced') }}:</span>
								<span class="financial-value">{{ formatCurrency(totalInvoiced, selectedProject.currency) }}</span>
							</div>
							<div v-if="totalPaid > 0" class="financial-item">
								<span class="financial-label">{{ translate('domaincontrol', 'Paid') }}:</span>
								<span class="financial-value financial-value--success">{{ formatCurrency(totalPaid, selectedProject.currency) }}</span>
							</div>
							<div v-if="totalPending > 0" class="financial-item">
								<span class="financial-label">{{ translate('domaincontrol', 'Pending') }}:</span>
								<span class="financial-value financial-value--warning">{{ formatCurrency(totalPending, selectedProject.currency) }}</span>
							</div>
						</div>

						<!-- Project Invoices -->
						<div v-if="projectInvoices.length > 0" class="project-invoices-list">
							<div class="invoices-header">
								<strong>{{ translate('domaincontrol', 'Project Invoices') }}</strong>
								<span>{{ projectInvoices.length }} {{ translate('domaincontrol', 'invoices') }}</span>
							</div>
							<div class="invoices-items">
								<div
									v-for="invoice in projectInvoices"
									:key="invoice.id"
									class="invoice-item"
									@click="navigateToInvoice(invoice.id)"
								>
									<div class="invoice-info">
										<div class="invoice-number">{{ invoice.invoiceNumber || 'Invoice #' + invoice.id }}</div>
										<div class="invoice-date">{{ invoice.issueDate || '-' }}</div>
									</div>
									<span class="status-badge" :class="'status-' + invoice.status">
										{{ getInvoiceStatusText(invoice.status) }}
									</span>
									<div class="invoice-amounts">
										<div class="invoice-total">
											{{ translate('domaincontrol', 'Total') }}: <strong>{{ formatCurrency(invoice.totalAmount, invoice.currency) }}</strong>
										</div>
										<div class="invoice-paid">
											{{ translate('domaincontrol', 'Paid') }}: <strong>{{ formatCurrency(invoice.paidAmount, invoice.currency) }}</strong>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>

				<!-- Linked Items Section -->
				<div class="detail-section">
					<div class="section-header">
						<h3 class="section-title">
							<MaterialIcon name="link" :size="24" />
							{{ translate('domaincontrol', 'Linked Items') }}
						</h3>
						<button class="button-vue button-vue--secondary" @click="showLinkItemModal = true">
							<MaterialIcon name="add" :size="18" />
							{{ translate('domaincontrol', 'Link Item') }}
						</button>
					</div>
					<div class="section-content">
						<div v-if="projectItemsLoading" class="loading-content">
							<MaterialIcon name="loading" :size="32" class="loading-icon" />
							<p>{{ translate('domaincontrol', 'Loading linked items...') }}</p>
						</div>
						<div v-else-if="projectItems.length === 0" class="empty-content">
							<p class="empty-content__text">{{ translate('domaincontrol', 'No linked items yet') }}</p>
						</div>
						<div v-else class="linked-items-list">
							<div
								v-for="item in projectItems"
								:key="item.id"
								class="linked-item"
							>
								<div class="linked-item-content">
									<MaterialIcon :name="getItemTypeIcon(item.itemType)" :size="20" />
									<span class="linked-item-type">{{ getItemTypeLabel(item.itemType) }}</span>
									<span class="linked-item-name">{{ getItemName(item) }}</span>
								</div>
								<button
									class="action-button action-button--delete"
									@click="removeProjectItem(item.id)"
									:title="translate('domaincontrol', 'Remove')"
								>
									<MaterialIcon name="delete" :size="16" />
								</button>
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
import TaskModal from './TaskModal.vue'

export default {
	name: 'Projects',
	components: {
		MaterialIcon,
		ProjectModal,
		TaskModal,
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
			// Tasks
			projectTasks: [],
			projectTasksLoading: false,
			taskModalOpen: false,
			editingTask: null,
			// Time tracking
			timeEntries: [],
			timeEntriesLoading: false,
			currentRunningEntry: null,
			timerElapsed: 0,
			timerInterval: null,
			timerDescription: '',
			timerStarting: false,
			timerStopping: false,
			totalTime: 0,
			// Financials
			projectInvoices: [],
			totalInvoiced: 0,
			totalPaid: 0,
			totalPending: 0,
			// Linked items
			projectItems: [],
			projectItemsLoading: false,
			showLinkItemModal: false,
			domains: [],
			hostings: [],
			websites: [],
			services: [],
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
		completedTasks() {
			return this.projectTasks.filter(t => t.status === 'done').length
		},
		activeTasks() {
			return this.projectTasks.filter(t => t.status !== 'cancelled').length
		},
		cancelledTasks() {
			return this.projectTasks.filter(t => t.status === 'cancelled').length
		},
		progressPercentage() {
			if (this.activeTasks === 0) return 0
			return Math.round((this.completedTasks / this.activeTasks) * 100)
		},
	},
	watch: {
		selectedProject(newVal) {
			if (newVal) {
				this.loadProjectDetails(newVal.id)
			} else {
				this.cleanup()
			}
		},
	},
	mounted() {
		this.loadData()
		document.addEventListener('click', this.handleClickOutside)
	},
	beforeUnmount() {
		document.removeEventListener('click', this.handleClickOutside)
		this.cleanup()
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
				await this.loadProjectDetails(this.selectedProject.id)
			}
			this.closeModal()
		},
		async loadProjectDetails(projectId) {
			await Promise.all([
				this.loadProjectTasks(projectId),
				this.loadTimeTracking(projectId),
				this.loadProjectFinancials(projectId),
				this.loadProjectItems(projectId),
			])
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
		// Tasks
		async loadProjectTasks(projectId) {
			this.projectTasksLoading = true
			try {
				const response = await api.projects.getTasks(projectId)
				this.projectTasks = response.data || []
			} catch (error) {
				console.error('Error loading project tasks:', error)
				this.projectTasks = []
			} finally {
				this.projectTasksLoading = false
			}
		},
		showAddTaskModal() {
			this.editingTask = null
			this.taskModalOpen = true
		},
		closeTaskModal() {
			this.taskModalOpen = false
			this.editingTask = null
		},
		async handleTaskSaved() {
			if (this.selectedProject) {
				await this.loadProjectTasks(this.selectedProject.id)
			}
			this.closeTaskModal()
		},
		async toggleTaskStatus(task) {
			const newStatus = task.status === 'done' ? 'todo' : 'done'
			try {
				await api.tasks.update(task.id, { status: newStatus })
				task.status = newStatus
				if (this.selectedProject) {
					await this.loadProjectTasks(this.selectedProject.id)
				}
			} catch (error) {
				console.error('Error toggling task status:', error)
				alert(this.translate('domaincontrol', 'Error updating task status'))
			}
		},
		isTaskOverdue(task) {
			if (!task.dueDate || task.status === 'done' || task.status === 'cancelled') return false
			const dueDate = new Date(task.dueDate)
			const today = new Date()
			today.setHours(0, 0, 0, 0)
			return dueDate < today
		},
		navigateToTask(taskId) {
			if (typeof window.DomainControl !== 'undefined' && window.DomainControl.switchTab) {
				window.DomainControl.switchTab('tasks')
				setTimeout(() => {
					const event = new CustomEvent('select-task', { detail: { taskId } })
					window.dispatchEvent(event)
				}, 100)
			}
		},
		getPriorityText(priority) {
			const priorities = {
				low: this.translate('domaincontrol', 'Low'),
				medium: this.translate('domaincontrol', 'Medium'),
				high: this.translate('domaincontrol', 'High'),
			}
			return priorities[priority] || priority
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
		// Time Tracking
		async loadTimeTracking(projectId) {
			this.timeEntriesLoading = true
			try {
				const response = await api.timeEntries.byProject(projectId)
				this.timeEntries = response.data.entries || []
				this.totalTime = response.data.totalDuration || 0

				const runningResponse = await api.timeEntries.getRunning(projectId)
				if (runningResponse.data) {
					this.currentRunningEntry = runningResponse.data
					this.startTimerDisplay()
				} else {
					this.currentRunningEntry = null
					this.stopTimerDisplay()
				}
			} catch (error) {
				console.error('Error loading time tracking:', error)
				this.timeEntries = []
				this.totalTime = 0
			} finally {
				this.timeEntriesLoading = false
			}
		},
		async startTimer() {
			if (this.currentRunningEntry) {
				alert(this.translate('domaincontrol', 'Timer is already running'))
				return
			}
			this.timerStarting = true
			try {
				const response = await api.timeEntries.start(this.selectedProject.id, {
					description: this.timerDescription,
				})
				this.currentRunningEntry = response.data
				this.timerDescription = ''
				this.startTimerDisplay()
				await this.loadTimeTracking(this.selectedProject.id)
			} catch (error) {
				console.error('Error starting timer:', error)
				alert(this.translate('domaincontrol', 'Error starting timer'))
			} finally {
				this.timerStarting = false
			}
		},
		async stopTimer() {
			if (!this.currentRunningEntry) {
				alert(this.translate('domaincontrol', 'No running timer'))
				return
			}
			this.timerStopping = true
			try {
				await api.timeEntries.stop(this.selectedProject.id)
				this.currentRunningEntry = null
				this.stopTimerDisplay()
				await this.loadTimeTracking(this.selectedProject.id)
			} catch (error) {
				console.error('Error stopping timer:', error)
				alert(this.translate('domaincontrol', 'Error stopping timer'))
			} finally {
				this.timerStopping = false
			}
		},
		startTimerDisplay() {
			if (!this.currentRunningEntry) return
			if (this.timerInterval) {
				clearInterval(this.timerInterval)
			}
			const startTime = new Date(this.currentRunningEntry.startTime + ' UTC').getTime()
			const updateTimer = () => {
				const now = Date.now()
				this.timerElapsed = Math.floor((now - startTime) / 1000)
			}
			updateTimer()
			this.timerInterval = setInterval(updateTimer, 1000)
		},
		stopTimerDisplay() {
			if (this.timerInterval) {
				clearInterval(this.timerInterval)
				this.timerInterval = null
			}
			this.timerElapsed = 0
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
		formatTime(dateTime) {
			if (!dateTime) return ''
			const d = new Date(dateTime + ' UTC')
			return d.toLocaleTimeString('tr-TR', { hour: '2-digit', minute: '2-digit' })
		},
		async deleteTimeEntry(entryId) {
			if (!confirm(this.translate('domaincontrol', 'Are you sure you want to delete this time entry?'))) {
				return
			}
			try {
				await api.timeEntries.delete(entryId)
				await this.loadTimeTracking(this.selectedProject.id)
			} catch (error) {
				console.error('Error deleting time entry:', error)
				alert(this.translate('domaincontrol', 'Error deleting time entry'))
			}
		},
		// Financials
		async loadProjectFinancials(projectId) {
			try {
				const invoicesResponse = await api.invoices.getAll()
				const allInvoices = invoicesResponse.data || []
				const project = this.projects.find(p => p.id === projectId)
				if (!project) return

				this.projectInvoices = allInvoices.filter(inv => {
					if (inv.clientId !== project.clientId) return false
					if (inv.notes && inv.notes.includes(`Proje: ${project.name}`)) {
						return true
					}
					return false
				})

				this.totalInvoiced = this.projectInvoices.reduce((sum, inv) => sum + (parseFloat(inv.totalAmount) || 0), 0)
				this.totalPaid = this.projectInvoices.reduce((sum, inv) => sum + (parseFloat(inv.paidAmount) || 0), 0)
				this.totalPending = this.totalInvoiced - this.totalPaid
			} catch (error) {
				console.error('Error loading project financials:', error)
				this.projectInvoices = []
				this.totalInvoiced = 0
				this.totalPaid = 0
				this.totalPending = 0
			}
		},
		async createInvoiceFromProject() {
			if (!this.selectedProject) return
			try {
				const invoiceData = {
					clientId: this.selectedProject.clientId,
					currency: this.selectedProject.currency || 'USD',
					status: 'draft',
					notes: `Proje: ${this.selectedProject.name}`,
				}
				await api.invoices.create(invoiceData)
				await this.loadProjectFinancials(this.selectedProject.id)
				alert(this.translate('domaincontrol', 'Invoice created successfully'))
			} catch (error) {
				console.error('Error creating invoice:', error)
				alert(this.translate('domaincontrol', 'Error creating invoice'))
			}
		},
		getInvoiceStatusText(status) {
			const statusTexts = {
				draft: this.translate('domaincontrol', 'Draft'),
				sent: this.translate('domaincontrol', 'Sent'),
				paid: this.translate('domaincontrol', 'Paid'),
				overdue: this.translate('domaincontrol', 'Overdue'),
				cancelled: this.translate('domaincontrol', 'Cancelled'),
			}
			return statusTexts[status] || status
		},
		navigateToInvoice(invoiceId) {
			if (typeof window.DomainControl !== 'undefined' && window.DomainControl.switchTab) {
				window.DomainControl.switchTab('invoices')
				setTimeout(() => {
					const event = new CustomEvent('select-invoice', { detail: { invoiceId } })
					window.dispatchEvent(event)
				}, 100)
			}
		},
		// Linked Items
		async loadProjectItems(projectId) {
			this.projectItemsLoading = true
			try {
				const response = await api.projects.getItems(projectId)
				this.projectItems = response.data || []
				await Promise.all([
					this.loadDomains(),
					this.loadHostings(),
					this.loadWebsites(),
					this.loadServices(),
				])
			} catch (error) {
				console.error('Error loading project items:', error)
				this.projectItems = []
			} finally {
				this.projectItemsLoading = false
			}
		},
		async loadDomains() {
			try {
				const response = await api.domains.getAll()
				this.domains = response.data || []
			} catch (error) {
				console.error('Error loading domains:', error)
			}
		},
		async loadHostings() {
			try {
				const response = await api.hostings.getAll()
				this.hostings = response.data || []
			} catch (error) {
				console.error('Error loading hostings:', error)
			}
		},
		async loadWebsites() {
			try {
				const response = await api.websites.getAll()
				this.websites = response.data || []
			} catch (error) {
				console.error('Error loading websites:', error)
			}
		},
		async loadServices() {
			try {
				const response = await api.services.getAll()
				this.services = response.data || []
			} catch (error) {
				console.error('Error loading services:', error)
			}
		},
		getItemName(item) {
			let itemData = null
			if (item.itemType === 'domain') {
				itemData = this.domains.find(d => d.id === item.itemId)
				return itemData ? itemData.domainName : 'N/A'
			} else if (item.itemType === 'hosting') {
				itemData = this.hostings.find(h => h.id === item.itemId)
				return itemData ? itemData.provider : 'N/A'
			} else if (item.itemType === 'website') {
				itemData = this.websites.find(w => w.id === item.itemId)
				return itemData ? itemData.name : 'N/A'
			} else if (item.itemType === 'service') {
				itemData = this.services.find(s => s.id === item.itemId)
				return itemData ? itemData.name : 'N/A'
			}
			return 'N/A'
		},
		getItemTypeLabel(type) {
			const labels = {
				domain: this.translate('domaincontrol', 'Domain'),
				hosting: this.translate('domaincontrol', 'Hosting'),
				website: this.translate('domaincontrol', 'Website'),
				service: this.translate('domaincontrol', 'Service'),
			}
			return labels[type] || type
		},
		getItemTypeIcon(type) {
			const icons = {
				domain: 'public',
				hosting: 'category-office',
				website: 'link',
				service: 'settings',
			}
			return icons[type] || 'link'
		},
		async removeProjectItem(itemId) {
			if (!confirm(this.translate('domaincontrol', 'Are you sure you want to remove this item?'))) {
				return
			}
			try {
				await api.projects.removeItem(this.selectedProject.id, itemId)
				await this.loadProjectItems(this.selectedProject.id)
			} catch (error) {
				console.error('Error removing project item:', error)
				alert(this.translate('domaincontrol', 'Error removing item'))
			}
		},
		cleanup() {
			this.stopTimerDisplay()
			this.projectTasks = []
			this.timeEntries = []
			this.projectItems = []
			this.currentRunningEntry = null
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
				// Tasks
				'Tasks': 'Görevler',
				'Add Task': 'Görev Ekle',
				'Loading tasks...': 'Görevler yükleniyor...',
				'No tasks yet': 'Henüz görev yok',
				'Progress': 'İlerleme',
				'tasks completed': 'görev tamamlandı',
				'cancelled': 'iptal',
				'Low': 'Düşük',
				'Medium': 'Orta',
				'High': 'Yüksek',
				'To Do': 'Yapılacak',
				'In Progress': 'Devam Ediyor',
				'Done': 'Tamamlandı',
				'Cancelled': 'İptal Edildi',
				'Completed by': 'Tamamlayan',
				'Error updating task status': 'Görev durumu güncellenirken hata oluştu',
				// Time Tracking
				'Time Tracking': 'Zaman Takibi',
				'Start Timer': 'Zamanlayıcıyı Başlat',
				'Stop Timer': 'Zamanlayıcıyı Durdur',
				'Task description (optional)': 'Görev açıklaması (isteğe bağlı)',
				'Total Time': 'Toplam Süre',
				'Loading time entries...': 'Zaman kayıtları yükleniyor...',
				'No time entries yet': 'Henüz zaman kaydı yok',
				'No description': 'Açıklama yok',
				'Running...': 'Çalışıyor...',
				'Are you sure you want to delete this time entry?': 'Bu zaman kaydını silmek istediğinize emin misiniz?',
				'Error deleting time entry': 'Zaman kaydı silinirken hata oluştu',
				'Timer is already running': 'Zamanlayıcı zaten çalışıyor',
				'Error starting timer': 'Zamanlayıcı başlatılırken hata oluştu',
				'No running timer': 'Çalışan zamanlayıcı yok',
				'Error stopping timer': 'Zamanlayıcı durdurulurken hata oluştu',
				// Financials
				'Financials': 'Finansal Bilgiler',
				'Create Invoice': 'Fatura Oluştur',
				'Total Invoiced': 'Toplam Faturalanan',
				'Paid': 'Ödenen',
				'Pending': 'Bekleyen',
				'Project Invoices': 'Proje Faturaları',
				'invoices': 'fatura',
				'Total': 'Toplam',
				'Invoice created successfully': 'Fatura başarıyla oluşturuldu',
				'Error creating invoice': 'Fatura oluşturulurken hata oluştu',
				'Draft': 'Taslak',
				'Sent': 'Gönderildi',
				'Paid': 'Ödendi',
				'Overdue': 'Gecikmiş',
				// Linked Items
				'Linked Items': 'Bağlı Öğeler',
				'Link Item': 'Öğe Bağla',
				'Loading linked items...': 'Bağlı öğeler yükleniyor...',
				'No linked items yet': 'Henüz bağlı öğe yok',
				'Domain': 'Domain',
				'Hosting': 'Hosting',
				'Website': 'Website',
				'Service': 'Hizmet',
				'Are you sure you want to remove this item?': 'Bu öğeyi kaldırmak istediğinize emin misiniz?',
				'Error removing item': 'Öğe kaldırılırken hata oluştu',
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

/* Detail Sections */
.detail-section {
	background-color: var(--color-background-dark);
	border-radius: var(--border-radius-element);
	padding: 20px;
	border: 1px solid var(--color-border);
	margin-bottom: 20px;
}

.section-header {
	display: flex;
	justify-content: space-between;
	align-items: center;
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

.section-content {
	display: flex;
	flex-direction: column;
	gap: 16px;
}

/* Tasks */
.project-progress {
	margin-bottom: 16px;
}

.progress-header {
	display: flex;
	justify-content: space-between;
	margin-bottom: 8px;
	font-size: 14px;
	color: var(--color-main-text);
}

.progress-bar {
	height: 12px;
	background: var(--color-background-dark);
	border-radius: 6px;
	overflow: hidden;
}

.progress-fill {
	height: 100%;
	background: var(--color-primary-element);
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
	background-color: var(--color-background-hover);
}

.project-task-item.task-overdue {
	border-color: var(--color-error);
}

.project-task-item.task-cancelled {
	opacity: 0.6;
}

.task-checkbox {
	width: 20px;
	height: 20px;
	cursor: pointer;
}

.task-content {
	flex: 1;
	min-width: 0;
}

.task-title {
	font-size: 15px;
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
	flex-wrap: wrap;
}

.priority-badge {
	padding: 2px 8px;
	border-radius: var(--border-radius-pill);
	font-size: 11px;
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

.task-assigned,
.task-completed {
	font-size: 11px;
}

/* Time Tracking */
.timer-controls {
	display: flex;
	flex-direction: column;
	gap: 12px;
	margin-bottom: 16px;
}

.timer-display {
	padding: 12px;
	background-color: var(--color-main-background);
	border-radius: var(--border-radius-element);
	border: 1px solid var(--color-border);
}

.timer-time {
	font-size: 24px;
	font-weight: 600;
	color: var(--color-primary-element);
	margin-bottom: 4px;
}

.timer-description {
	font-size: 13px;
	color: var(--color-text-maxcontrast);
}

.timer-buttons {
	display: flex;
	gap: 8px;
}

.timer-description-input {
	width: 100%;
}

.time-summary {
	padding: 12px;
	background-color: var(--color-main-background);
	border-radius: var(--border-radius-element);
	border: 1px solid var(--color-border);
	margin-bottom: 16px;
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
	min-width: 0;
}

.time-entry-description {
	font-size: 14px;
	font-weight: 500;
	color: var(--color-main-text);
	margin-bottom: 4px;
}

.time-entry-meta {
	display: flex;
	align-items: center;
	gap: 8px;
	font-size: 12px;
	color: var(--color-text-maxcontrast);
}

.time-entry-running {
	color: var(--color-primary-element);
	font-weight: 500;
}

.time-entry-duration {
	font-size: 14px;
	font-weight: 600;
	color: var(--color-main-text);
}

/* Financials */
.financial-summary {
	display: flex;
	flex-direction: column;
	gap: 12px;
	margin-bottom: 16px;
}

.financial-item {
	display: flex;
	justify-content: space-between;
	align-items: center;
	padding: 12px;
	background-color: var(--color-main-background);
	border-radius: var(--border-radius-element);
	border: 1px solid var(--color-border);
}

.financial-label {
	font-size: 14px;
	color: var(--color-text-maxcontrast);
}

.financial-value {
	font-size: 16px;
	font-weight: 600;
	color: var(--color-main-text);
}

.financial-value--success {
	color: var(--color-success);
}

.financial-value--warning {
	color: var(--color-warning);
}

.project-invoices-list {
	margin-top: 16px;
	padding-top: 16px;
	border-top: 1px solid var(--color-border);
}

.invoices-header {
	display: flex;
	justify-content: space-between;
	align-items: center;
	margin-bottom: 12px;
}

.invoices-header strong {
	font-size: 14px;
	color: var(--color-main-text);
}

.invoices-header span {
	font-size: 12px;
	color: var(--color-text-maxcontrast);
}

.invoices-items {
	display: flex;
	flex-direction: column;
	gap: 8px;
	max-height: 300px;
	overflow-y: auto;
}

.invoice-item {
	padding: 12px;
	background-color: var(--color-main-background);
	border: 1px solid var(--color-border);
	border-radius: var(--border-radius-element);
	cursor: pointer;
	transition: background-color 0.2s;
}

.invoice-item:hover {
	background-color: var(--color-background-hover);
}

.invoice-info {
	margin-bottom: 8px;
}

.invoice-number {
	font-weight: 600;
	font-size: 13px;
	color: var(--color-main-text);
	margin-bottom: 4px;
}

.invoice-date {
	font-size: 11px;
	color: var(--color-text-maxcontrast);
}

.invoice-amounts {
	display: flex;
	justify-content: space-between;
	align-items: center;
	margin-top: 8px;
	font-size: 12px;
}

.invoice-total {
	color: var(--color-text-maxcontrast);
}

.invoice-paid {
	color: var(--color-success);
}

/* Linked Items */
.linked-items-list {
	display: flex;
	flex-direction: column;
	gap: 8px;
}

.linked-item {
	display: flex;
	justify-content: space-between;
	align-items: center;
	padding: 12px;
	background-color: var(--color-main-background);
	border: 1px solid var(--color-border);
	border-radius: var(--border-radius-element);
}

.linked-item-content {
	display: flex;
	align-items: center;
	gap: 8px;
	flex: 1;
	min-width: 0;
}

.linked-item-type {
	font-size: 12px;
	color: var(--color-text-maxcontrast);
	min-width: 80px;
}

.linked-item-name {
	font-size: 14px;
	font-weight: 500;
	color: var(--color-main-text);
}
</style>
