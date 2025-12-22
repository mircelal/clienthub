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
			@back="backToList"
			@edit="editProject(selectedProject)"
			@delete="confirmDelete(selectedProject)"
			@toggle-popover="toggleDetailPopover"
			@close-popover="closeDetailPopover"
			@start-timer="startTimer"
			@tab-change="handleTabChange"
		>
			<div class="detail-content">
				<!-- Overview Tab -->
				<ProjectOverview
					v-if="activeTab === 'overview' && selectedProject"
					:project="selectedProject"
					:clients="clients"
					@navigate-client="navigateToClient"
				/>

				<!-- Tasks Tab -->
				<ProjectTasks
					v-if="activeTab === 'tasks' && selectedProject"
					:tasks="projectTasks"
					:loading="projectTasksLoading"
					:get-priority-text="getPriorityText"
					:get-task-status-text="getTaskStatusText"
					:is-task-overdue="isTaskOverdue"
					:format-date="formatDate"
					@add-task="showAddTaskModal"
					@navigate-task="navigateToTask"
					@toggle-status="toggleTaskStatus"
				/>

				<!-- Time Tracking Tab -->
				<ProjectTimeTracking
					v-if="activeTab === 'time' && selectedProject"
					:time-entries="timeEntries"
					:loading="timeEntriesLoading"
					:current-running-entry="currentRunningEntry"
					:timer-elapsed="timerElapsed"
					:timer-description="timerDescription"
					:timer-starting="timerStarting"
					:timer-stopping="timerStopping"
					:total-time="totalTime"
					:duration-by-user="durationByUser"
					:available-users="availableUsers"
					@start-timer="startTimer"
					@stop-timer="stopTimer"
					@update:timerDescription="timerDescription = $event"
					@delete-entry="deleteTimeEntry"
				/>

				<!-- Financials Tab -->
				<ProjectFinancials
					v-if="activeTab === 'financials' && selectedProject"
					:project="selectedProject"
					:invoices="projectInvoices"
					:total-invoiced="totalInvoiced"
					:total-paid="totalPaid"
					:total-pending="totalPending"
					@create-invoice="createInvoiceFromProject"
					@navigate-invoice="navigateToInvoice"
				/>

				<!-- Linked Items Tab -->
				<ProjectLinkedItems
					v-if="activeTab === 'linked' && selectedProject"
					:items="projectItems"
					:loading="projectItemsLoading"
					:domains="domains"
					:hostings="hostings"
					:websites="websites"
					:services="services"
					@link-item="showLinkItemModal = true"
					@remove-item="removeProjectItem"
				/>

				<!-- Sharing Tab -->
				<ProjectSharing
					v-if="activeTab === 'sharing' && selectedProject && isProjectOwner"
					:shares="projectShares"
					:loading="projectSharesLoading"
					:available-users="availableUsers"
					@share="showShareProjectModal"
					@unshare="unshareProject"
				/>

				<!-- Activity Tab -->
				<ProjectActivity
					v-if="activeTab === 'activity' && selectedProject"
					:activities="projectActivities"
					:loading="projectActivitiesLoading"
					:available-users="availableUsers"
				/>

				<!-- Files Tab -->
				<ProjectFiles
					v-if="activeTab === 'files' && selectedProject"
					:project-id="selectedProject.id"
				/>

				<!-- Notes Tab -->
				<ProjectNotes
					v-if="activeTab === 'notes' && selectedProject"
					:project-id="selectedProject.id"
				/>

				<!-- Requirements Tab (Notes with category filter) -->
				<ProjectNotes
					v-if="activeTab === 'requirements' && selectedProject"
					:project-id="selectedProject.id"
					initial-category="requirements"
				/>

				<!-- Challenges Tab (Notes with category filter) -->
				<ProjectNotes
					v-if="activeTab === 'challenges' && selectedProject"
					:project-id="selectedProject.id"
					initial-category="challenges"
				/>

				<!-- Research Tab (Notes with category filter) -->
				<ProjectNotes
					v-if="activeTab === 'research' && selectedProject"
					:project-id="selectedProject.id"
					initial-category="research"
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
			:show-modal="showLinkItemModal"
			:domains="domains"
			:hostings="hostings"
			:websites="websites"
			:services="services"
			@close="showLinkItemModal = false"
			@link-item="handleLinkItem"
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
			:clients="clients"
			@close="closeTaskModal"
			@saved="handleTaskSaved"
		/>
	</div>
</template>

<script>
import MaterialIcon from './MaterialIcon.vue'
import ProjectList from './projects/ProjectList.vue'
import ProjectDetail from './projects/ProjectDetail.vue'
import ProjectHeader from './projects/ProjectHeader.vue'
import ProjectOverview from './projects/ProjectOverview.vue'
import ProjectTasks from './projects/ProjectTasks.vue'
import ProjectTimeTracking from './projects/ProjectTimeTracking.vue'
import ProjectFinancials from './projects/ProjectFinancials.vue'
import ProjectLinkedItems from './projects/ProjectLinkedItems.vue'
import ProjectSharing from './projects/ProjectSharing.vue'
import ProjectActivity from './projects/ProjectActivity.vue'
import ProjectFiles from './projects/ProjectFiles.vue'
import ProjectNotes from './projects/ProjectNotes.vue'
import LinkItemModal from './projects/modals/LinkItemModal.vue'
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
		ProjectOverview,
		ProjectTasks,
		ProjectTimeTracking,
		ProjectFinancials,
		ProjectLinkedItems,
		ProjectSharing,
		ProjectActivity,
		ProjectFiles,
		ProjectNotes,
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
			projectItems: [],
			projectItemsLoading: false,
			projectShares: [],
			projectSharesLoading: false,
			projectActivities: [],
			projectActivitiesLoading: false,
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
			try {
				const response = await api.timeEntries.byProject(projectId)
				this.timeEntries = response.data.entries || []
				this.totalTime = response.data.totalDuration || 0
				this.durationByUser = response.data.durationByUser || []
				
				// Get running entry
				const runningResponse = await api.timeEntries.getRunning(projectId)
				this.currentRunningEntry = runningResponse.data
				
				if (this.currentRunningEntry) {
					this.startTimerInterval()
				}
			} catch (error) {
				console.error('Error loading time tracking:', error)
				this.timeEntries = []
				this.totalTime = 0
				this.durationByUser = []
				this.currentRunningEntry = null
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
			} catch (error) {
				console.error('Error loading project financials:', error)
				this.projectInvoices = []
				this.totalInvoiced = 0
				this.totalPaid = 0
				this.totalPending = 0
			}
		},
		createInvoiceFromProject() {
			if (typeof window.DomainControl !== 'undefined' && window.DomainControl.switchTab) {
				window.DomainControl.switchTab('invoices')
				setTimeout(() => {
					const event = new CustomEvent('create-invoice-from-project', {
						detail: { projectId: this.selectedProject.id },
					})
					window.dispatchEvent(event)
				}, 100)
			}
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
			} catch (error) {
				console.error('Error loading project items:', error)
				this.projectItems = []
			} finally {
				this.projectItemsLoading = false
			}
		},
		async handleLinkItem(itemType, itemId) {
			if (!this.selectedProject) return
			
			try {
				await api.projects.addItem(this.selectedProject.id, {
					itemType,
					itemId,
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
.projects-view {
	width: 100%;
	height: 100%;
}

.detail-content {
	padding: 20px;
}
</style>
