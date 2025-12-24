<template>
    <div class="tasks-view-container">
        <!-- ========================================== -->
        <!-- MODALS                                     -->
        <!-- ========================================== -->
        <TaskModal
            :open="modalOpen"
            :task="editingTask"
            :projects="projects"
            :clients="clients"
            :tasks="tasks"
            @close="closeModal"
            @saved="handleTaskSaved"
        />

        <!-- ========================================== -->
        <!-- LIST VIEW                                  -->
        <!-- ========================================== -->
        <div v-if="!selectedTask" class="nc-main-view">
            <!-- Header & Actions -->
            <div class="nc-section-header">
                <div class="header-left">
                    <h2 class="nc-app-title">
                        <CheckboxMarkedCircleOutline :size="24" class="header-icon" />
                        {{ translate('domaincontrol', 'Tasks') }}
                    </h2>
                </div>
                <div class="header-actions">
                    <NcButton type="primary" @click="showAddModal">
                        <template #icon><Plus :size="20" /></template>
                        {{ translate('domaincontrol', 'Add Task') }}
                    </NcButton>
                </div>
            </div>

            <!-- Filter Bar & Search -->
            <div class="nc-filter-bar">
                <div class="filter-tabs">
                    <button 
                        v-for="filter in ['all', 'todo', 'in_progress', 'done', 'cancelled']" 
                        :key="filter"
                        class="filter-tab"
                        :class="{ active: currentFilter === filter }"
                        @click="setFilter(filter)"
                    >
                        {{ getFilterLabel(filter) }}
                    </button>
                </div>
                <div class="search-wrapper">
                    <Magnify :size="18" class="search-icon" />
                    <input
                        type="text"
                        v-model="searchQuery"
                        class="nc-input search-input"
                        :placeholder="translate('domaincontrol', 'Search tasks...')"
                    />
                </div>
            </div>

            <!-- Loading State -->
            <div v-if="loading" class="nc-loading-state">
                <Refresh :size="48" class="spin-animation nc-state-icon" />
                <p>{{ translate('domaincontrol', 'Loading tasks...') }}</p>
            </div>

            <!-- Empty State -->
            <div v-else-if="filteredTasks.length === 0" class="nc-empty-state">
                <CheckboxBlankOutline :size="64" class="nc-state-icon" />
                <h3>{{ searchQuery ? translate('domaincontrol', 'No tasks found') : translate('domaincontrol', 'No tasks yet') }}</h3>
                <NcButton v-if="!searchQuery" type="primary" @click="showAddModal" class="mt-4">
                    {{ translate('domaincontrol', 'Add First Task') }}
                </NcButton>
            </div>

            <!-- Tasks List -->
            <div v-else class="nc-list-container">
                <div 
                    v-for="task in filteredTasks" 
                    :key="task.id" 
                    class="nc-list-item task-row"
                    :class="[
                        getPriorityClass(task.priority),
                        { 'is-completed': task.status === 'done', 'is-cancelled': task.status === 'cancelled' }
                    ]"
                    @click="selectTask(task)"
                >
                    <!-- Checkbox Area -->
                    <div class="task-checkbox-area" @click.stop>
                        <div class="custom-checkbox" :class="{ checked: task.status === 'done' }" @click="toggleTaskStatus(task)">
                            <Check v-if="task.status === 'done'" :size="14" />
                        </div>
                    </div>

                    <!-- Main Content -->
                    <div class="item-content">
                        <div class="task-header">
                            <span class="task-title">{{ task.title }}</span>
                            <span v-if="isToday(task.dueDate)" class="today-badge">{{ translate('domaincontrol', 'TODAY') }}</span>
                        </div>
                        <div class="task-meta">
                            <span v-if="getProjectName(task.projectId)" class="meta-tag project-tag">
                                <Folder :size="12" class="inline-icon" /> {{ getProjectName(task.projectId) }}
                            </span>
                            <span v-else class="meta-tag">
                                {{ translate('domaincontrol', 'General') }}
                            </span>
                            
                            <span v-if="task.dueDate" class="meta-tag" :class="getDueDateClass(task)">
                                <Calendar :size="12" class="inline-icon" /> {{ formatDate(task.dueDate) }}
                            </span>
                            
                            <span v-if="getSubtasksCount(task) > 0" class="meta-tag">
                                <FormatListBulleted :size="12" class="inline-icon" /> {{ getSubtasksCount(task) }}
                            </span>
                        </div>
                    </div>

                    <!-- Status Badge (Desktop) -->
                    <div class="item-status desktop-only">
                        <span class="nc-badge" :class="getStatusBadgeClass(task.status)">
                            {{ getTaskStatusText(task.status) }}
                        </span>
                    </div>

                    <!-- Actions -->
                    <div class="item-actions">
                        <div class="popover-wrapper" @click.stop>
                            <button class="action-btn" @click="togglePopover(task.id)">
                                <DotsVertical :size="18" />
                            </button>
                            <!-- Dropdown Menu -->
                            <div v-if="openPopover === task.id" class="nc-popover-menu">
                                <div class="popover-item" @click="editTask(task); closePopover()">
                                    <Pencil :size="16" /> {{ translate('domaincontrol', 'Edit') }}
                                </div>
                                <div v-if="task.status !== 'done'" class="popover-item" @click="toggleTaskStatus(task); closePopover()">
                                    <Check :size="16" /> {{ translate('domaincontrol', 'Mark as Done') }}
                                </div>
                                <div v-else class="popover-item" @click="toggleTaskStatus(task); closePopover()">
                                    <Refresh :size="16" /> {{ translate('domaincontrol', 'Mark as To Do') }}
                                </div>
                                <div class="popover-separator"></div>
                                <div class="popover-item danger" @click="confirmDelete(task); closePopover()">
                                    <Delete :size="16" /> {{ translate('domaincontrol', 'Delete') }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- DETAIL VIEW                                -->
        <!-- ========================================== -->
        <div v-else class="nc-detail-view">
            <!-- Header -->
            <div class="nc-detail-header">
                <div class="header-left">
                    <NcButton type="tertiary" @click="backToList">
                        <template #icon><ArrowLeft :size="20" /></template>
                        {{ translate('domaincontrol', 'Back') }}
                    </NcButton>
                    <div class="detail-title-wrapper">
                         <!-- Large Checkbox in Header -->
                        <div class="custom-checkbox large" :class="{ checked: selectedTask.status === 'done' }" @click="toggleTaskStatus(selectedTask)">
                            <Check v-if="selectedTask.status === 'done'" :size="18" />
                        </div>
                        <h2 class="detail-title" :class="{ 'text-strike': selectedTask.status === 'done' }">{{ selectedTask.title }}</h2>
                    </div>
                </div>
                <div class="header-actions">
                     <div class="popover-wrapper" @click.stop>
                        <NcButton type="secondary" @click="toggleDetailPopover">
                            <template #icon><DotsVertical :size="20" /></template>
                        </NcButton>
                        <div v-if="detailPopoverOpen" class="nc-popover-menu right-aligned">
                            <div class="popover-item" @click="editTask(selectedTask); closeDetailPopover()">
                                <Pencil :size="16" /> {{ translate('domaincontrol', 'Edit') }}
                            </div>
                            <div class="popover-separator"></div>
                            <div class="popover-item danger" @click="confirmDelete(selectedTask); closeDetailPopover()">
                                <Delete :size="16" /> {{ translate('domaincontrol', 'Delete') }}
                            </div>
                        </div>
                     </div>
                </div>
            </div>

            <div class="nc-detail-content">
                <!-- Left Column -->
                <div class="detail-column main">
                    <!-- Stats Grid -->
                    <div class="stats-grid">
                        <div class="stat-widget">
                            <div class="widget-icon">
                                <Folder :size="20" />
                            </div>
                            <div class="widget-info">
                                <span class="label">{{ translate('domaincontrol', 'Project') }}</span>
                                <span class="value link" @click="navigateToProject(selectedTask.projectId)">
                                    {{ getProjectName(selectedTask.projectId) || translate('domaincontrol', 'General') }}
                                </span>
                            </div>
                        </div>
                        <div class="stat-widget">
                            <div class="widget-icon">
                                <ListStatus :size="20" />
                            </div>
                            <div class="widget-info">
                                <span class="label">{{ translate('domaincontrol', 'Status') }}</span>
                                <span class="nc-badge" :class="getStatusBadgeClass(selectedTask.status)">
                                    {{ getTaskStatusText(selectedTask.status) }}
                                </span>
                            </div>
                        </div>
                        <div class="stat-widget">
                            <div class="widget-icon">
                                <Flag :size="20" />
                            </div>
                            <div class="widget-info">
                                <span class="label">{{ translate('domaincontrol', 'Priority') }}</span>
                                <span class="nc-badge" :class="`badge-priority-${selectedTask.priority || 'medium'}`">
                                    {{ getPriorityText(selectedTask.priority) }}
                                </span>
                            </div>
                        </div>
                        <div class="stat-widget">
                            <div class="widget-icon">
                                <Calendar :size="20" />
                            </div>
                            <div class="widget-info">
                                <span class="label">{{ translate('domaincontrol', 'Due Date') }}</span>
                                <span class="value" :class="getDueDateClass(selectedTask)">
                                    {{ formatDate(selectedTask.dueDate) || '-' }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Description -->
                    <div class="nc-panel">
                        <div class="panel-header">
                            <h3><TextBox :size="18" class="inline-icon"/> {{ translate('domaincontrol', 'Description') }}</h3>
                        </div>
                        <div class="panel-body">
                            <div class="text-content">
                                {{ selectedTask.description || translate('domaincontrol', 'No description') }}
                            </div>
                        </div>
                    </div>

                    <!-- Notes -->
                    <div class="nc-panel">
                        <div class="panel-header">
                            <h3><StickerTextOutline :size="18" class="inline-icon"/> {{ translate('domaincontrol', 'Notes') }}</h3>
                        </div>
                        <div class="panel-body">
                            <div class="text-content" v-html="selectedTask.notes || translate('domaincontrol', 'No notes')"></div>
                        </div>
                    </div>

                </div>

                <!-- Right Column (Subtasks) -->
                <div class="detail-column sidebar">
                    <div class="nc-panel">
                        <div class="panel-header">
                            <h3>
                                <FormatListChecks :size="18" class="inline-icon" />
                                {{ translate('domaincontrol', 'Subtasks') }}
                            </h3>
                            <NcButton type="tertiary" size="small" @click="showAddSubtaskModal">
                                <template #icon><Plus :size="16" /></template>
                            </NcButton>
                        </div>
                        
                        <div v-if="subtasks.length === 0" class="empty-mini">
                            {{ translate('domaincontrol', 'No subtasks yet') }}
                        </div>
                        
                        <div v-else class="nc-subtask-list">
                            <div 
                                v-for="subtask in subtasks" 
                                :key="subtask.id" 
                                class="subtask-row"
                                :class="{ 'is-completed': subtask.status === 'done' }"
                            >
                                <div class="custom-checkbox small" :class="{ checked: subtask.status === 'done' }" @click="toggleSubtaskStatus(subtask)">
                                    <Check v-if="subtask.status === 'done'" :size="12" />
                                </div>
                                <span class="subtask-text">{{ subtask.title }}</span>
                                <button class="delete-icon-btn" @click="confirmDeleteSubtask(subtask)">
                                    <Close :size="16" />
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</template>

<script>
import { NcButton } from '@nextcloud/vue'
import api from '../services/api'
import TaskModal from './TaskModal.vue'

// Icons
import CheckboxMarkedCircleOutline from 'vue-material-design-icons/CheckboxMarkedCircleOutline.vue'
import CheckboxBlankOutline from 'vue-material-design-icons/CheckboxBlankOutline.vue'
import Plus from 'vue-material-design-icons/Plus.vue'
import Magnify from 'vue-material-design-icons/Magnify.vue'
import Refresh from 'vue-material-design-icons/Refresh.vue'
import Check from 'vue-material-design-icons/Check.vue'
import Folder from 'vue-material-design-icons/Folder.vue'
import Calendar from 'vue-material-design-icons/Calendar.vue'
import FormatListBulleted from 'vue-material-design-icons/FormatListBulleted.vue'
import DotsVertical from 'vue-material-design-icons/DotsVertical.vue'
import Pencil from 'vue-material-design-icons/Pencil.vue'
import Delete from 'vue-material-design-icons/Delete.vue'
import ArrowLeft from 'vue-material-design-icons/ArrowLeft.vue'
import ListStatus from 'vue-material-design-icons/ListStatus.vue'
import Flag from 'vue-material-design-icons/Flag.vue'
import TextBox from 'vue-material-design-icons/TextBox.vue'
import StickerTextOutline from 'vue-material-design-icons/StickerTextOutline.vue'
import FormatListChecks from 'vue-material-design-icons/FormatListChecks.vue'
import Close from 'vue-material-design-icons/Close.vue'

export default {
    name: 'Tasks',
    components: {
        NcButton,
        TaskModal,
        // Icons
        CheckboxMarkedCircleOutline, CheckboxBlankOutline, Plus, Magnify, Refresh,
        Check, Folder, Calendar, FormatListBulleted, DotsVertical, Pencil, Delete,
        ArrowLeft, ListStatus, Flag, TextBox, StickerTextOutline, FormatListChecks, Close
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
            let filtered = this.tasks.filter(t => !t.parentId) // Only top-level

            if (this.currentFilter !== 'all') {
                filtered = filtered.filter(t => t.status === this.currentFilter)
            }

            if (this.searchQuery) {
                const query = this.searchQuery.toLowerCase()
                filtered = filtered.filter(task => {
                    const title = (task.title || '').toLowerCase()
                    const projectName = this.getProjectName(task.projectId) || ''
                    return title.includes(query) || projectName.toLowerCase().includes(query)
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
        translate(appId, text, vars) {
             try {
                if (typeof window !== 'undefined') {
                    if (typeof OC !== 'undefined' && OC.L10n && typeof OC.L10n.translate === 'function') {
                        const translated = OC.L10n.translate(appId, text, vars || {})
                        if (translated && translated !== text) return translated
                    }
                    if (typeof window.t === 'function') {
                        const translated = window.t(appId, text, vars || {})
                        if (translated && translated !== text) return translated
                    }
                }
            } catch (e) { console.warn('Translation error:', e) }
            
            // Fallback dictionary for demo
            const dict = {
                'All': 'Tümü', 'To Do': 'Yapılacak', 'In Progress': 'Devam Ediyor', 'Done': 'Tamamlandı', 'Cancelled': 'İptal',
                'General': 'Genel', 'TODAY': 'BUGÜN'
            }
            return dict[text] || text
        },
        async loadData() {
            this.loading = true
            try {
                await Promise.all([
                    this.loadTasks(),
                    this.loadProjects(),
                    this.loadClients(),
                ])
            } catch (error) {
                console.error(error)
            } finally {
                this.loading = false
            }
        },
        async loadTasks() {
            try {
                const response = await api.tasks.getAll()
                this.tasks = response.data || []
            } catch (error) { this.tasks = [] }
        },
        async loadProjects() {
            try {
                const response = await api.projects.getAll()
                this.projects = response.data || []
            } catch (error) { this.projects = [] }
        },
        async loadClients() {
            try {
                const response = await api.clients.getAll()
                this.clients = response.data || []
            } catch (error) { this.clients = [] }
        },
        async loadSubtasks(taskId) {
            try {
                const response = await api.tasks.getSubtasks(taskId)
                this.subtasks = response.data || []
            } catch (error) { this.subtasks = [] }
        },
        getFilterLabel(filter) {
            const labels = {
                all: this.translate('domaincontrol', 'All'),
                todo: this.translate('domaincontrol', 'To Do'),
                in_progress: this.translate('domaincontrol', 'In Progress'),
                done: this.translate('domaincontrol', 'Done'),
                cancelled: this.translate('domaincontrol', 'Cancelled')
            }
            return labels[filter] || filter
        },
        setFilter(filter) { this.currentFilter = filter },
        selectTask(task) { this.selectedTask = task },
        backToList() { this.selectedTask = null },
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
                // Refresh selected task if it was edited
                const response = await api.tasks.get(this.selectedTask.id).catch(() => ({ data: this.selectedTask }))
                this.selectedTask = response.data
                // Also refresh subtasks
                this.loadSubtasks(this.selectedTask.id)
            }
            this.closeModal()
        },
        async toggleTaskStatus(task) {
            // Optimistic update
            const oldStatus = task.status
            const newStatus = oldStatus === 'done' ? 'todo' : 'done'
            task.status = newStatus
            
            try {
                const response = await api.tasks.toggleStatus(task.id)
                // Sync with server response
                Object.assign(task, response.data)
            } catch (error) {
                task.status = oldStatus // Revert
                console.error(error)
            }
        },
        async toggleSubtaskStatus(subtask) {
            try {
                await api.tasks.toggleStatus(subtask.id)
                await this.loadSubtasks(this.selectedTask.id)
            } catch (error) { console.error(error) }
        },
        async confirmDelete(task) {
            if (!confirm(this.translate('domaincontrol', 'Are you sure you want to delete this task?'))) return
            try {
                await api.tasks.delete(task.id)
                await this.loadTasks()
                if (this.selectedTask && this.selectedTask.id === task.id) {
                    this.backToList()
                }
            } catch (error) { alert('Error deleting task') }
        },
        async confirmDeleteSubtask(subtask) {
             if (!confirm(this.translate('domaincontrol', 'Are you sure you want to delete this subtask?'))) return
             try {
                await api.tasks.delete(subtask.id)
                await this.loadSubtasks(this.selectedTask.id)
             } catch(e) { alert('Error') }
        },
        
        // Helpers
        getProjectName(id) { return this.projects.find(p => p.id === id)?.name || '' },
        navigateToProject(id) {
            if (window.DomainControl?.switchTab) {
                 window.DomainControl.switchTab('projects')
                 setTimeout(() => window.dispatchEvent(new CustomEvent('select-project', { detail: { projectId: id } })), 100)
            }
        },
        getSubtasksCount(task) { return this.tasks.filter(t => t.parentId === task.id).length },
        
        // CSS Class Helpers
        getPriorityClass(priority) { return `priority-${priority || 'medium'}` },
        getPriorityText(p) { return p ? p.charAt(0).toUpperCase() + p.slice(1) : 'Medium' },
        getTaskStatusText(s) { 
            const map = { todo: 'To Do', in_progress: 'In Progress', done: 'Done', cancelled: 'Cancelled' }
            return this.translate('domaincontrol', map[s] || s)
        },
        getStatusBadgeClass(status) {
            const map = {
                todo: 'badge-neutral',
                in_progress: 'badge-primary',
                done: 'badge-success',
                cancelled: 'badge-neutral'
            }
            return map[status] || 'badge-neutral'
        },
        isOverdue(task) {
            if (!task.dueDate || task.status === 'done' || task.status === 'cancelled') return false
            return new Date(task.dueDate) < new Date().setHours(0,0,0,0)
        },
        isToday(date) {
            if (!date) return false
            return new Date(date).toDateString() === new Date().toDateString()
        },
        getDueDateClass(task) {
             if (!task.dueDate || task.status === 'done' || task.status === 'cancelled') return ''
             if (this.isOverdue(task)) return 'text-error'
             return ''
        },
        formatDate(d) { return d ? new Date(d).toLocaleDateString() : '' },
        
        // Popover
        togglePopover(id) { this.openPopover = this.openPopover === id ? null : id },
        closePopover() { this.openPopover = null },
        toggleDetailPopover() { this.detailPopoverOpen = !this.detailPopoverOpen },
        closeDetailPopover() { this.detailPopoverOpen = false },
        handleClickOutside(e) {
            if (this.openPopover && !e.target.closest('.popover-wrapper')) this.closePopover()
            if (this.detailPopoverOpen && !e.target.closest('.popover-wrapper')) this.closeDetailPopover()
        }
    }
}
</script>

<style scoped>
/* GLOBAL LAYOUT */
.tasks-view-container {
    padding: 20px;
    height: 100%;
    color: var(--color-main-text);
    font-family: var(--font-face, sans-serif);
}

.nc-main-view, .nc-detail-view { display: flex; flex-direction: column; gap: 20px; }

/* HEADER */
.nc-section-header {
    display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; flex-wrap: wrap; gap: 16px;
}
.header-left { display: flex; align-items: center; gap: 12px; }
.nc-app-title { margin: 0; font-size: 24px; font-weight: bold; display: flex; align-items: center; gap: 12px; }
.header-icon { opacity: 0.8; color: var(--color-text-maxcontrast); }
.header-actions { display: flex; align-items: center; gap: 12px; }

/* FILTER BAR */
.nc-filter-bar {
    display: flex; justify-content: space-between; align-items: center; gap: 16px; flex-wrap: wrap;
    background: var(--color-background-hover); padding: 6px; border-radius: var(--border-radius-large);
}
.filter-tabs { display: flex; gap: 4px; }
.filter-tab {
    background: transparent; border: none; padding: 6px 12px; border-radius: var(--border-radius-pill);
    color: var(--color-text-maxcontrast); font-weight: 500; cursor: pointer; transition: all 0.2s;
    font-size: 13px;
}
.filter-tab:hover { background: var(--color-background-dark); color: var(--color-main-text); }
.filter-tab.active { background: var(--color-main-background); color: var(--color-primary); box-shadow: 0 1px 2px rgba(0,0,0,0.1); }

/* SEARCH */
.search-wrapper { position: relative; width: 200px; }
.search-icon { position: absolute; left: 10px; top: 50%; transform: translateY(-50%); color: var(--color-text-maxcontrast); opacity: 0.7; }
.search-input { width: 100%; padding: 6px 12px 6px 32px; border-radius: var(--border-radius-pill); border: 1px solid transparent; background: var(--color-main-background); color: var(--color-main-text); font-size: 13px; }
.search-input:focus { border-color: var(--color-primary); outline: none; }

/* TASK LIST */
.nc-list-container {
    display: flex; flex-direction: column; background: var(--color-main-background);
    border: 1px solid var(--color-border); border-radius: var(--border-radius-large); overflow: hidden;
}

.nc-list-item {
    display: flex; align-items: center; padding: 12px 16px; border-bottom: 1px solid var(--color-border);
    cursor: pointer; transition: background 0.1s ease; border-left: 4px solid transparent; /* For priority */
}
.nc-list-item:last-child { border-bottom: none; }
.nc-list-item:hover { background-color: var(--color-background-hover); }

/* Priority Colors (Left Border) */
.priority-high { border-left-color: var(--color-error-element); }
.priority-medium { border-left-color: var(--color-warning-element); }
.priority-low { border-left-color: var(--color-success-element); }

/* Task Row Content */
.task-checkbox-area { margin-right: 16px; display: flex; align-items: center; }
.custom-checkbox {
    width: 20px; height: 20px; border: 2px solid var(--color-text-maxcontrast); border-radius: 50%;
    display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.2s;
    color: #fff;
}
.custom-checkbox:hover { border-color: var(--color-primary); }
.custom-checkbox.checked { background-color: var(--color-primary); border-color: var(--color-primary); }

.item-content { flex: 1; display: flex; flex-direction: column; gap: 4px; }
.task-header { display: flex; align-items: center; gap: 8px; }
.task-title { font-weight: 600; font-size: 15px; color: var(--color-main-text); }
.is-completed .task-title { text-decoration: line-through; opacity: 0.6; }
.is-cancelled { opacity: 0.5; }

.today-badge {
    font-size: 10px; background: var(--color-error-element); color: #fff; padding: 1px 6px; border-radius: 4px; font-weight: bold;
}

.task-meta { display: flex; gap: 12px; font-size: 12px; color: var(--color-text-maxcontrast); align-items: center; }
.meta-tag { display: flex; align-items: center; gap: 4px; }
.inline-icon { opacity: 0.7; }
.text-error { color: var(--color-error-element); font-weight: bold; }

.item-status { margin-right: 16px; }
.nc-badge { font-size: 11px; padding: 2px 8px; border-radius: 10px; font-weight: 600; text-transform: uppercase; }
.badge-primary { background: rgba(0, 130, 201, 0.15); color: var(--color-primary); }
.badge-success { background: rgba(70, 186, 97, 0.15); color: var(--color-success-element); }
.badge-neutral { background: var(--color-background-dark); color: var(--color-text-maxcontrast); }
.badge-priority-high { color: var(--color-error-element); background: rgba(233, 50, 45, 0.1); }
.badge-priority-medium { color: var(--color-warning-element); background: rgba(233, 144, 2, 0.1); }

/* POPOVER ACTIONS */
.action-btn { background: none; border: none; padding: 6px; color: var(--color-text-maxcontrast); cursor: pointer; border-radius: 4px; }
.action-btn:hover { background: var(--color-background-dark); color: var(--color-main-text); }
.popover-wrapper { position: relative; }
.nc-popover-menu {
    position: absolute; right: 0; top: 100%; width: 180px; background: var(--color-main-background);
    border: 1px solid var(--color-border); border-radius: var(--border-radius-large); box-shadow: 0 5px 15px rgba(0,0,0,0.15);
    z-index: 100; padding: 4px 0;
}
.popover-item {
    padding: 8px 16px; display: flex; align-items: center; gap: 8px; cursor: pointer; font-size: 13px; color: var(--color-main-text);
}
.popover-item:hover { background: var(--color-background-hover); }
.popover-item.danger { color: var(--color-error-element); }
.popover-item.danger:hover { background: rgba(233, 50, 45, 0.05); }
.popover-separator { height: 1px; background: var(--color-border); margin: 4px 0; }

/* DETAIL VIEW */
.nc-detail-header {
    display: flex; justify-content: space-between; align-items: center; padding-bottom: 20px; border-bottom: 1px solid var(--color-border);
}
.detail-title-wrapper { display: flex; align-items: center; gap: 12px; margin-left: 12px; }
.custom-checkbox.large { width: 28px; height: 28px; border-width: 3px; }
.detail-title { margin: 0; font-size: 24px; font-weight: bold; }
.text-strike { text-decoration: line-through; opacity: 0.6; }

.nc-detail-content { display: grid; grid-template-columns: 2fr 1fr; gap: 24px; }
.detail-column { display: flex; flex-direction: column; gap: 24px; }

/* Stats Grid */
.stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; }
.stat-widget {
    background: var(--color-main-background); border: 1px solid var(--color-border); border-radius: var(--border-radius-large);
    padding: 12px; display: flex; align-items: center; gap: 10px;
}
.widget-icon {
    width: 36px; height: 36px; border-radius: 8px; display: flex; align-items: center; justify-content: center;
    color: var(--color-text-maxcontrast); background: var(--color-background-hover);
}
.widget-info { display: flex; flex-direction: column; }
.widget-info .label { font-size: 11px; color: var(--color-text-maxcontrast); }
.widget-info .value { font-size: 14px; font-weight: 600; color: var(--color-main-text); }
.widget-info .value.link { color: var(--color-primary); cursor: pointer; }
.widget-info .value.link:hover { text-decoration: underline; }

/* Panels */
.nc-panel { background: var(--color-main-background); border: 1px solid var(--color-border); border-radius: var(--border-radius-large); overflow: hidden; }
.panel-header {
    padding: 10px 16px; background: var(--color-background-hover); border-bottom: 1px solid var(--color-border);
    display: flex; justify-content: space-between; align-items: center;
}
.panel-header h3 { margin: 0; font-size: 14px; font-weight: 600; display: flex; align-items: center; gap: 8px; }
.panel-body { padding: 16px; font-size: 14px; line-height: 1.5; color: var(--color-main-text); }
.text-content { white-space: pre-wrap; }

/* Subtasks */
.empty-mini { padding: 16px; text-align: center; color: var(--color-text-maxcontrast); font-style: italic; font-size: 13px; }
.nc-subtask-list { display: flex; flex-direction: column; }
.subtask-row {
    display: flex; align-items: center; padding: 10px 16px; border-bottom: 1px solid var(--color-border);
    gap: 12px; transition: background 0.1s;
}
.subtask-row:last-child { border-bottom: none; }
.subtask-row.is-completed .subtask-text { text-decoration: line-through; opacity: 0.6; }
.subtask-text { flex: 1; font-size: 14px; }
.custom-checkbox.small { width: 16px; height: 16px; }
.delete-icon-btn {
    background: none; border: none; color: var(--color-text-maxcontrast); cursor: pointer; opacity: 0.5;
}
.delete-icon-btn:hover { color: var(--color-error-element); opacity: 1; }

/* Responsive */
@media (max-width: 900px) {
    .nc-detail-content { grid-template-columns: 1fr; }
    .desktop-only { display: none; }
    .stats-grid { grid-template-columns: 1fr 1fr; }
    .nc-filter-bar { flex-direction: column-reverse; align-items: stretch; }
    .filter-tabs { overflow-x: auto; padding-bottom: 4px; }
    .search-wrapper { width: 100%; }
}
/* Loading & Empty */
.nc-empty-state, .nc-loading-state {
    padding: 60px; text-align: center; display: flex; flex-direction: column; align-items: center;
    color: var(--color-text-maxcontrast);
}
.nc-state-icon { opacity: 0.5; margin-bottom: 16px; }
.spin-animation { animation: spin 1s linear infinite; }
.mt-4 { margin-top: 16px; }
</style>