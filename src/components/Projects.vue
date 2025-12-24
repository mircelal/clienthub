<template>
	<div class="projects-view">
		<!-- Project List View -->
		<ProjectList
			v-if="!selectedProject"
			:filtered-projects="filteredProjects"
			:loading="loading"
			:current-filter="currentFilter"
			:search-query="searchQuery"
			:clients="clients"
			:open-popover="openPopover"
			:selected-project="selectedProject"
			@select="selectProject"
			@add="showAddModal"
			@edit="editProject"
			@delete="confirmDelete"
			@filter="setFilter"
			@search="searchQuery = $event"
			@toggle-popover="togglePopover"
			@close-popover="closePopover"
		/>

		<!-- Project Detail View -->
		<ProjectDetail
			v-else
			:project="selectedProject"
			:active-tab="activeTab"
			:popover-open="detailPopoverOpen"
			:can-start-timer="canStartTimer"
			:current-running-entry="currentRunningEntry"
			@back="backToList"
			@edit="editProject(selectedProject)"
			@delete="confirmDelete(selectedProject)"
			@toggle-popover="toggleDetailPopover"
			@close-popover="closeDetailPopover"
			@start-timer="startTimer"
			@stop-timer="stopTimer"
			@tab-change="handleTabChange"
			@change-status="changeProjectStatus"
		>
			<div class="detail-content">
				<!-- Overview Tab -->
				<ProjectOverview
					v-if="activeTab === 'overview' && selectedProject"
					:project="selectedProject"
					:clients="clients"
					:total-time="totalTime"
					:duration-by-user="durationByUser"
					:available-users="availableUsers"
					:activities="projectActivities"
					:activities-loading="projectActivitiesLoading"
					:get-user-display-name="getUserDisplayName"
					:format-duration="formatDuration"
					:format-date="formatDate"
					@navigate-client="navigateToClient"
				/>

				<!-- Tasks & Time Tab -->
				<ProjectTasksAndTime
					v-if="activeTab === 'tasks-time' && selectedProject"
					:tasks="projectTasks"
					:tasks-loading="projectTasksLoading"
					:time-entries="timeEntries"
					:time-entries-loading="timeEntriesLoading"
					:current-running-entry="currentRunningEntry"
					:timer-elapsed="timerElapsed"
					:timer-description="timerDescription"
					:timer-starting="timerStarting"
					:timer-stopping="timerStopping"
					:total-time="totalTime"
					:duration-by-user="durationByUser"
					:available-users="availableUsers"
					:get-priority-text="getPriorityText"
					:get-task-status-text="getTaskStatusText"
					:is-task-overdue="isTaskOverdue"
					:format-date="formatDate"
					:format-timer-time="formatTimerTime"
					:format-duration="formatDuration"
					:format-time="formatTime"
					:get-user-display-name="getUserDisplayName"
					@add-task="showAddTaskModal"
					@navigate-task="navigateToTask"
					@toggle-status="toggleTaskStatus"
					@start-timer="startTimer"
					@stop-timer="stopTimer"
					@update:timerDescription="timerDescription = $event"
					@delete-entry="deleteTimeEntry"
				/>

				<!-- Documents Tab -->
				<ProjectDocuments
					v-if="activeTab === 'documents' && selectedProject"
					:project-id="selectedProject.id"
				/>

			<!-- Financials Tab -->
			<ProjectFinancials
				v-if="activeTab === 'financials' && selectedProject"
				@navigate-project="(projectId) => {}"
					:project="selectedProject"
					:invoices="projectInvoices"
					:total-invoiced="totalInvoiced"
					:total-paid="totalPaid"
					:total-pending="totalPending"
					:total-expenses="totalExpenses"
					:expenses="projectExpenses"
					@create-invoice="createInvoiceFromProject"
					@navigate-invoice="navigateToInvoice"
					@add-expense="showAddExpenseModal"
					@expense-deleted="handleExpenseDeleted"
				/>

				<!-- Linked & Sharing Tab -->
				<ProjectLinkedAndSharing
					v-if="activeTab === 'linked-sharing' && selectedProject"
					:items="projectItems"
					:items-loading="projectItemsLoading"
					:shares="projectShares"
					:shares-loading="projectSharesLoading"
					:is-project-owner="isProjectOwner"
					:domains="domains"
					:hostings="hostings"
					:websites="websites"
					:services="services"
					:available-users="availableUsers"
					@link-item="showLinkItemModal = true"
					@remove-item="removeProjectItem"
					@share="showShareProjectModal"
					@unshare="unshareProject"
				/>
			</div>
		</ProjectDetail>

		<!-- Share Project Modal -->
		<div v-if="showShareModal" class="modal-overlay" @click.self="closeShareModal">
			<div class="modal-content modal-content--medium">
				<div class="modal-header">
					<h2 class="modal-title">{{ translate('domaincontrol', 'Share Project') }}</h2>
					<button class="modal-close" @click="closeShareModal">
						<MaterialIcon name="close" :size="24" />
					</button>
				</div>
				<div class="modal-body">
					<div class="form-group">
						<label class="form-label">{{ translate('domaincontrol', 'Select user') }}</label>
						<input
							v-model="shareUserSearch"
							type="text"
							class="form-control"
							:placeholder="translate('domaincontrol', 'Search users...')"
							@input="filteredAvailableUsers"
						/>
						<div v-if="filteredAvailableUsers.length > 0" class="user-select-list">
							<div
								v-for="user in filteredAvailableUsers"
								:key="user.userId"
								class="user-select-item"
								:class="{ 'user-select-item--selected': selectedShareUser && selectedShareUser.userId === user.userId }"
								@click="selectShareUser(user)"
							>
								{{ user.displayName || user.userId }}
							</div>
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<button class="button-vue button-vue--secondary" @click="closeShareModal">
						{{ translate('domaincontrol', 'Cancel') }}
					</button>
					<button class="button-vue button-vue--primary" @click="handleShareProject" :disabled="!selectedShareUser">
						{{ translate('domaincontrol', 'Share') }}
					</button>
				</div>
			</div>
		</div>

		<!-- Link Item Modal -->
		<LinkItemModal
			v-if="showLinkItemModal"
			:open="showLinkItemModal"
			:domains="domains"
			:hostings="hostings"
			:websites="websites"
			:services="services"
			@close="showLinkItemModal = false"
			@link="handleLinkItem"
		/>

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
			:project-id="selectedProject ? selectedProject.id : null"
			:client-id="selectedProject ? (selectedProject.clientId || selectedProject.client_id) : null"
			:clients="clients"
			@close="closeTaskModal"
			@saved="handleTaskSaved"
		/>

		<!-- Expense Modal -->
		<ExpenseModal
			:open="expenseModalOpen"
			:project-id="selectedProject ? selectedProject.id : null"
			:client-id="selectedProject ? (selectedProject.clientId || selectedProject.client_id) : null"
			:currency="selectedProject ? (selectedProject.currency || 'USD') : 'USD'"
			@close="closeExpenseModal"
			@saved="handleExpenseSaved"
		/>
	</div>
</template>

<script>
import MaterialIcon from './MaterialIcon.vue'
import ProjectList from './projects/ProjectList.vue'
import ProjectDetail from './projects/ProjectDetail.vue'
import ProjectHeader from './projects/ProjectHeader.vue'
import ProjectOverview from './projects/ProjectOverview.vue'
import ProjectTasksAndTime from './projects/ProjectTasksAndTime.vue'
import ProjectFinancials from './projects/ProjectFinancials.vue'
import ProjectDocuments from './projects/ProjectDocuments.vue'
import ProjectLinkedAndSharing from './projects/ProjectLinkedAndSharing.vue'
import LinkItemModal from './projects/modals/LinkItemModal.vue'
import ExpenseModal from './projects/modals/ExpenseModal.vue'
import ProjectModal from './ProjectModal.vue'
import TaskModal from './TaskModal.vue'
import api from '../services/api'

export default {
	name: 'Projects',
	components: {
		MaterialIcon,
		ProjectList,
		ProjectDetail,
		ProjectHeader,
		ExpenseModal,
		ProjectOverview,
		ProjectTasksAndTime,
		ProjectFinancials,
		ProjectDocuments,
		ProjectLinkedAndSharing,
		LinkItemModal,
		ProjectModal,
		TaskModal,
	},
	data() {
		return {
			projects: [],
			clients: [],
			domains: [],
			hostings: [],
			websites: [],
			services: [],
			selectedProject: null,
			editingProject: null,
			modalOpen: false,
			loading: false,
			currentFilter: 'all',
			searchQuery: '',
			openPopover: null,
			detailPopoverOpen: false,
			activeTab: 'overview',
			// Project detail data
			projectTasks: [],
			projectTasksLoading: false,
			timeEntries: [],
			timeEntriesLoading: false,
			currentRunningEntry: null,
			timerElapsed: 0,
			timerDescription: '',
			timerStarting: false,
			timerStopping: false,
			totalTime: 0,
			durationByUser: [],
			projectInvoices: [],
			totalInvoiced: 0,
			totalPaid: 0,
			totalPending: 0,
			projectExpenses: [],
			totalExpenses: 0,
			projectItems: [],
			projectItemsLoading: false,
			projectShares: [],
			projectSharesLoading: false,
			projectActivities: [],
			projectActivitiesLoading: false,
			expenseModalOpen: false,
			availableUsers: [],
			// Modals
			showShareModal: false,
			showLinkItemModal: false,
			shareUserSearch: '',
			selectedShareUser: null,
			taskModalOpen: false,
			editingTask: null,
		}
	},
	computed: {
		filteredProjects() {
			let filtered = this.projects

			// Filter by status
			if (this.currentFilter !== 'all') {
				filtered = filtered.filter(p => p.status === this.currentFilter)
			}

			// Filter by search query
			if (this.searchQuery) {
				const query = this.searchQuery.toLowerCase()
				filtered = filtered.filter(p =>
					p.name.toLowerCase().includes(query) ||
					(p.description && p.description.toLowerCase().includes(query))
				)
			}

			return filtered
		},
		isProjectOwner() {
			return this.selectedProject && this.selectedProject.userId === (window.OC?.currentUser || 'admin')
		},
		canStartTimer() {
			return this.selectedProject && !this.currentRunningEntry
		},
		filteredAvailableUsers() {
			if (!this.shareUserSearch) {
				return this.availableUsers.filter(u => u.userId !== (window.OC?.currentUser || 'admin'))
			}
			const query = this.shareUserSearch.toLowerCase()
			return this.availableUsers.filter(u =>
				u.userId !== (window.OC?.currentUser || 'admin') &&
				(u.userId.toLowerCase().includes(query) ||
					(u.displayName && u.displayName.toLowerCase().includes(query)) ||
					(u.email && u.email.toLowerCase().includes(query)))
			)
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
		activeTab(newTab) {
			// Load data when tab changes
			if (this.selectedProject) {
				this.loadTabData(newTab, this.selectedProject.id)
			}
		},
	},
	mounted() {
		this.loadProjects()
		this.loadClients()
		this.loadDomains()
		this.loadHostings()
		this.loadWebsites()
		this.loadServices()
		this.loadUsers()
		document.addEventListener('click', this.handleClickOutside)
	},
	beforeUnmount() {
		document.removeEventListener('click', this.handleClickOutside)
		if (this.timerInterval) {
			clearInterval(this.timerInterval)
		}
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
		async loadProjects() {
			this.loading = true
			try {
				const response = await api.projects.getAll()
				this.projects = response.data || []
			} catch (error) {
				console.error('Error loading projects:', error)
				this.projects = []
			} finally {
				this.loading = false
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
		async loadDomains() {
			try {
				const response = await api.domains.getAll()
				this.domains = response.data || []
			} catch (error) {
				console.error('Error loading domains:', error)
				this.domains = []
			}
		},
		async loadHostings() {
			try {
				const response = await api.hostings.getAll()
				this.hostings = response.data || []
			} catch (error) {
				console.error('Error loading hostings:', error)
				this.hostings = []
			}
		},
		async loadWebsites() {
			try {
				const response = await api.websites.getAll()
				this.websites = response.data || []
			} catch (error) {
				console.error('Error loading websites:', error)
				this.websites = []
			}
		},
		async loadServices() {
			try {
				const response = await api.services.getAll()
				this.services = response.data || []
			} catch (error) {
				console.error('Error loading services:', error)
				this.services = []
			}
		},
		async loadUsers() {
			try {
				const response = await api.users.getAll()
				this.availableUsers = response.data || []
			} catch (error) {
				console.error('Error loading users:', error)
				this.availableUsers = []
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
			this.activeTab = 'overview'
		},
		backToList() {
			this.selectedProject = null
			this.activeTab = 'overview'
		},
		handleTabChange(tab) {
			this.activeTab = tab
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
		async changeProjectStatus(newStatus) {
			if (!this.selectedProject) return
			
			try {
				await api.projects.update(this.selectedProject.id, { status: newStatus })
				// Update local project object
				this.selectedProject.status = newStatus
				// Reload project details to get fresh data
				await this.loadProjectDetails(this.selectedProject.id)
				// Reload projects list to update status in list view
				await this.loadProjects()
			} catch (error) {
				console.error('Error changing project status:', error)
				alert(this.translate('domaincontrol', 'Error changing project status'))
			}
		},
		async loadProjectDetails(projectId) {
			// Load all data when project is selected
			console.log('Loading project details for projectId:', projectId)
			try {
				await Promise.all([
					this.loadProjectTasks(projectId),
					this.loadTimeTracking(projectId),
					this.loadProjectFinancials(projectId),
					this.loadProjectItems(projectId),
					this.loadProjectShares(projectId),
					this.loadProjectActivities(projectId),
				])
				console.log('Project details loaded:', {
					tasks: this.projectTasks.length,
					timeEntries: this.timeEntries.length,
					invoices: this.projectInvoices.length,
					items: this.projectItems.length,
					shares: this.projectShares.length,
					activities: this.projectActivities.length,
				})
			} catch (error) {
				console.error('Error loading project details:', error)
			}
		},
		async loadTabData(tab, projectId) {
			// Load data for specific tab when it becomes active
			if (!projectId) return
			
			switch (tab) {
				case 'tasks':
					await this.loadProjectTasks(projectId)
					break
				case 'time':
					await this.loadTimeTracking(projectId)
					break
				case 'financials':
					await this.loadProjectFinancials(projectId)
					break
				case 'linked':
					await this.loadProjectItems(projectId)
					break
				case 'sharing':
					await this.loadProjectShares(projectId)
					break
				case 'activity':
					await this.loadProjectActivities(projectId)
					break
				// Files and Notes load their own data via watch on projectId
			}
		},
		async loadProjectActivities(projectId) {
			this.projectActivitiesLoading = true
			try {
				const response = await api.projectActivities.getAll(projectId)
				this.projectActivities = response.data || []
			} catch (error) {
				console.error('Error loading project activities:', error)
				this.projectActivities = []
			} finally {
				this.projectActivitiesLoading = false
			}
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
				console.log('Loading tasks for projectId:', projectId)
				const response = await api.projects.getTasks(projectId)
				this.projectTasks = response.data || []
				console.log('Tasks loaded:', this.projectTasks.length)
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
		formatTimerTime(seconds) {
			const hours = Math.floor(seconds / 3600)
			const minutes = Math.floor((seconds % 3600) / 60)
			const secs = seconds % 60
			return `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}:${String(secs).padStart(2, '0')}`
		},
		formatTime(dateTime) {
			if (!dateTime) return ''
			const d = new Date(dateTime)
			return d.toLocaleTimeString('tr-TR', { hour: '2-digit', minute: '2-digit' })
		},
		formatDuration(seconds) {
			const hours = Math.floor(seconds / 3600)
			const minutes = Math.floor((seconds % 3600) / 60)
			if (hours > 0) {
				return `${hours}h ${minutes}m`
			}
			return `${minutes}m`
		},
		getUserDisplayName(userId) {
			const user = this.availableUsers.find(u => u.userId === userId)
			if (user) {
				return user.displayName || userId
			}
			return userId
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
		// Time Tracking
		async loadTimeTracking(projectId) {
			this.timeEntriesLoading = true
			// Clear existing timer interval before loading new data
			if (this.timerInterval) {
				clearInterval(this.timerInterval)
				this.timerInterval = null
			}
			
			try {
				const response = await api.timeEntries.byProject(projectId)
				this.timeEntries = response.data.entries || []
				this.totalTime = response.data.totalDuration || 0
				this.durationByUser = response.data.durationByUser || []
				
				// Get running entry
				const runningResponse = await api.timeEntries.getRunning(projectId)
				this.currentRunningEntry = runningResponse.data
				
				if (this.currentRunningEntry) {
					// Calculate elapsed time immediately
					const startTime = new Date(this.currentRunningEntry.startTime).getTime()
					const now = Date.now()
					this.timerElapsed = Math.floor((now - startTime) / 1000)
					// Start timer interval
					this.startTimerInterval()
				} else {
					this.timerElapsed = 0
				}
			} catch (error) {
				console.error('Error loading time tracking:', error)
				this.timeEntries = []
				this.totalTime = 0
				this.durationByUser = []
				this.currentRunningEntry = null
				this.timerElapsed = 0
			} finally {
				this.timeEntriesLoading = false
			}
		},
		async startTimer() {
			if (!this.selectedProject) return
			
			this.timerStarting = true
			try {
				const response = await api.timeEntries.start(this.selectedProject.id, {
					description: this.timerDescription,
				})
				this.currentRunningEntry = response.data
				this.timerDescription = ''
				this.startTimerInterval()
				await this.loadTimeTracking(this.selectedProject.id)
			} catch (error) {
				console.error('Error starting timer:', error)
				alert(this.translate('domaincontrol', 'Error starting timer'))
			} finally {
				this.timerStarting = false
			}
		},
		async stopTimer() {
			if (!this.selectedProject) return
			
			this.timerStopping = true
			try {
				await api.timeEntries.stop(this.selectedProject.id)
				this.currentRunningEntry = null
				this.timerElapsed = 0
				if (this.timerInterval) {
					clearInterval(this.timerInterval)
					this.timerInterval = null
				}
				await this.loadTimeTracking(this.selectedProject.id)
			} catch (error) {
				console.error('Error stopping timer:', error)
				alert(this.translate('domaincontrol', 'Error stopping timer'))
			} finally {
				this.timerStopping = false
			}
		},
		startTimerInterval() {
			if (this.timerInterval) {
				clearInterval(this.timerInterval)
			}
			
			if (this.currentRunningEntry) {
				const startTime = new Date(this.currentRunningEntry.startTime).getTime()
				this.timerInterval = setInterval(() => {
					const now = Date.now()
					this.timerElapsed = Math.floor((now - startTime) / 1000)
				}, 1000)
			}
		},
		async deleteTimeEntry(entryId) {
			if (!this.selectedProject) return
			
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
				// Get all invoices and filter by projectId if invoice has projectId field
				// For now, we'll get all invoices and filter client-side
				// TODO: Add backend endpoint /api/projects/{id}/invoices
				const response = await api.invoices.getAll()
				const allInvoices = response.data || []
				// Filter invoices that belong to this project's client
				// Note: This is a workaround until backend endpoint is added
				if (this.selectedProject && this.selectedProject.clientId) {
					this.projectInvoices = allInvoices.filter(inv => inv.clientId === this.selectedProject.clientId)
				} else {
					this.projectInvoices = []
				}
				
				// Calculate totals
				this.totalInvoiced = this.projectInvoices.reduce((sum, inv) => sum + (parseFloat(inv.totalAmount || inv.total || 0) || 0), 0)
				this.totalPaid = this.projectInvoices
					.filter(inv => inv.status === 'paid')
					.reduce((sum, inv) => sum + (parseFloat(inv.paidAmount || inv.total || 0) || 0), 0)
				this.totalPending = this.totalInvoiced - this.totalPaid

				// Load expenses (transactions with type='expense' and projectId)
				await this.loadProjectExpenses(projectId)
			} catch (error) {
				console.error('Error loading project financials:', error)
				this.projectInvoices = []
				this.totalInvoiced = 0
				this.totalPaid = 0
				this.totalPending = 0
				this.projectExpenses = []
				this.totalExpenses = 0
			}
		},
		async loadProjectExpenses(projectId) {
			try {
				const response = await api.transactions.byProject(projectId)
				const allTransactions = response.data || []
				// Filter only expense transactions (type='expense')
				const expenses = allTransactions.filter(t => t.type === 'expense')
				
				// Load categories to get category names
				const categoriesResponse = await api.transactionCategories.getAll()
				const categories = categoriesResponse.data || []
				const categoryMap = {}
				categories.forEach(cat => {
					categoryMap[cat.id] = cat.name
				})
				
				// Add category name to each expense
				this.projectExpenses = expenses.map(exp => ({
					...exp,
					categoryName: exp.categoryId ? (categoryMap[exp.categoryId] || '-') : '-'
				}))
				
				// Calculate total expenses
				this.totalExpenses = this.projectExpenses.reduce((sum, exp) => {
					return sum + (parseFloat(exp.amount || 0) || 0)
				}, 0)
			} catch (error) {
				console.error('Error loading project expenses:', error)
				this.projectExpenses = []
				this.totalExpenses = 0
			}
		},
		showAddExpenseModal() {
			this.expenseModalOpen = true
		},
		closeExpenseModal() {
			this.expenseModalOpen = false
		},
		async handleExpenseSaved() {
			if (this.selectedProject) {
				await this.loadProjectExpenses(this.selectedProject.id)
			}
			this.closeExpenseModal()
		},
		async handleExpenseDeleted() {
			if (this.selectedProject) {
				await this.loadProjectExpenses(this.selectedProject.id)
			}
		},
		async createInvoiceFromProject() {
			if (!this.selectedProject || !this.selectedProject.clientId) {
				alert(this.translate('domaincontrol', 'Project must have a client assigned'))
				return
			}

			try {
				const invoiceData = {
					clientId: this.selectedProject.clientId,
					currency: this.selectedProject.currency || 'USD',
					status: 'draft',
					notes: `${this.translate('domaincontrol', 'Project')}: ${this.selectedProject.name}`,
				}

				const response = await api.invoices.create(invoiceData)
				if (response.data && response.data.id) {
					// Reload project financials to show the new invoice
					await this.loadProjectFinancials(this.selectedProject.id)
					// Show success message
					alert(this.translate('domaincontrol', 'Invoice created successfully'))
				} else {
					throw new Error('Invalid response from server')
				}
			} catch (error) {
				console.error('Error creating invoice:', error)
				alert(this.translate('domaincontrol', 'Error creating invoice') + ': ' + (error.response?.data?.error || error.message))
			}
		},
		navigateToInvoice(invoiceId) {
			// Dispatch event to App.vue to switch tab and select invoice
			const event = new CustomEvent('navigate-to-invoice', { detail: invoiceId })
			window.dispatchEvent(event)
		},
		// Linked Items
		async loadProjectItems(projectId) {
			this.projectItemsLoading = true
			try {
				const response = await api.projects.getItems(projectId)
				this.projectItems = response.data || []
			} catch (error) {
				console.error('Error loading project items:', error)
				this.projectItems = []
			} finally {
				this.projectItemsLoading = false
			}
		},
		async handleLinkItem(data) {
			if (!this.selectedProject) return
			
			try {
				await api.projects.addItem(this.selectedProject.id, {
					itemType: data.itemType,
					itemId: data.itemId,
				})
				await this.loadProjectItems(this.selectedProject.id)
				this.showLinkItemModal = false
			} catch (error) {
				console.error('Error linking item:', error)
				alert(this.translate('domaincontrol', 'Error linking item'))
			}
		},
		async removeProjectItem(itemId) {
			if (!this.selectedProject) return
			
			if (!confirm(this.translate('domaincontrol', 'Are you sure you want to remove this item?'))) {
				return
			}
			try {
				await api.projects.removeItem(this.selectedProject.id, itemId)
				await this.loadProjectItems(this.selectedProject.id)
			} catch (error) {
				console.error('Error removing item:', error)
				alert(this.translate('domaincontrol', 'Error removing item'))
			}
		},
		// Sharing
		async loadProjectShares(projectId) {
			this.projectSharesLoading = true
			try {
				const response = await api.projectShares.index(projectId)
				this.projectShares = response.data || []
			} catch (error) {
				console.error('Error loading project shares:', error)
				this.projectShares = []
			} finally {
				this.projectSharesLoading = false
			}
		},
		showShareProjectModal() {
			this.shareUserSearch = ''
			this.selectedShareUser = null
			this.showShareModal = true
		},
		closeShareModal() {
			this.showShareModal = false
			this.shareUserSearch = ''
			this.selectedShareUser = null
		},
		selectShareUser(user) {
			this.selectedShareUser = user
		},
		async handleShareProject() {
			if (!this.selectedProject || !this.selectedShareUser) return
			
			try {
				await api.projectShares.share(this.selectedProject.id, {
					sharedWithUserId: this.selectedShareUser.userId,
					permissionLevel: 'read',
				})
				await this.loadProjectShares(this.selectedProject.id)
				this.closeShareModal()
			} catch (error) {
				console.error('Error sharing project:', error)
				alert(this.translate('domaincontrol', 'Error sharing project'))
			}
		},
		async unshareProject(sharedWithUserId) {
			if (!this.selectedProject) return
			
			if (!confirm(this.translate('domaincontrol', 'Are you sure you want to unshare this project?'))) {
				return
			}
			try {
				await api.projectShares.unshare(this.selectedProject.id, sharedWithUserId)
				await this.loadProjectShares(this.selectedProject.id)
			} catch (error) {
				console.error('Error unsharing project:', error)
				alert(this.translate('domaincontrol', 'Error unsharing project'))
			}
		},
		cleanup() {
			// Clear all project detail data
			this.projectTasks = []
			this.timeEntries = []
			this.currentRunningEntry = null
			this.timerElapsed = 0
			this.projectInvoices = []
			this.projectItems = []
			this.projectShares = []
			this.projectActivities = []
			if (this.timerInterval) {
				clearInterval(this.timerInterval)
				this.timerInterval = null
			}
		},
	},
}
</script>

<style scoped>
/* Nextcloud Files app content structure */
/* Following Nextcloud Files app pattern */
/* NcAppContent handles all padding and layout automatically */
.projects-view {
	width: 100%;
	height: 100%;
}

/* Share Project Modal - Center on screen */
.modal-overlay {
	position: fixed;
	top: 0;
	left: 0;
	right: 0;
	bottom: 0;
	background-color: rgba(0, 0, 0, 0.5);
	display: flex;
	align-items: center;
	justify-content: center;
	z-index: 10000;
	overflow-y: auto;
	padding: 20px;
}

.modal-content {
	background-color: var(--color-main-background);
	border-radius: var(--border-radius-large);
	box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
	max-width: 500px;
	width: 90%;
	max-height: 90vh;
	overflow-y: auto;
	margin: auto;
}

.modal-content--medium {
	max-width: 500px;
}

.modal-header {
	display: flex;
	align-items: center;
	justify-content: space-between;
	padding: 20px;
	border-bottom: 1px solid var(--color-border);
}

.modal-title {
	margin: 0;
	font-size: 20px;
	font-weight: 600;
	color: var(--color-main-text);
}

.modal-close {
	background: transparent;
	border: none;
	cursor: pointer;
	color: var(--color-text-maxcontrast);
	padding: 4px;
	display: flex;
	align-items: center;
	justify-content: center;
}

.modal-close:hover {
	color: var(--color-main-text);
}

.modal-body {
	padding: 20px;
}

.modal-footer {
	display: flex;
	justify-content: flex-end;
	gap: 8px;
	padding: 20px;
	border-top: 1px solid var(--color-border);
}

.form-group {
	margin-bottom: 16px;
}

.form-label {
	display: block;
	margin-bottom: 8px;
	font-size: 14px;
	font-weight: 500;
	color: var(--color-main-text);
}

.form-control {
	width: 100%;
	padding: 8px 12px;
	border: 1px solid var(--color-border);
	border-radius: var(--border-radius-element);
	background-color: var(--color-main-background);
	color: var(--color-main-text);
	font-size: 14px;
	box-sizing: border-box;
}

.user-select-list {
	max-height: 200px;
	overflow-y: auto;
	margin-top: 8px;
	border: 1px solid var(--color-border);
	border-radius: var(--border-radius);
}

.user-select-item {
	padding: 10px 12px;
	cursor: pointer;
	transition: background-color 0.2s;
	border-bottom: 1px solid var(--color-border);
}

.user-select-item:last-child {
	border-bottom: none;
}

.user-select-item:hover {
	background-color: var(--color-background-hover);
}

.user-select-item--selected {
	background-color: var(--color-primary-element-element-element-light);
	color: var(--color-primary-element-element-element);
}
</style>
