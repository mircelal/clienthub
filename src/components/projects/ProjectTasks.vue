<template>
    <div class="tab-content">
        <div class="detail-section">
            <!-- Header Section -->
            <div class="section-header">
                <h3 class="section-title">
                    <div class="icon-container">
                        <ClipboardCheck :size="20" />
                    </div>
                    {{ translate('domaincontrol', 'Tasks') }}
                </h3>
                <button class="nc-button nc-button-primary" @click="$emit('add-task')">
                    <span class="icon-wrapper">
                        <Plus :size="18" />
                    </span>
                    {{ translate('domaincontrol', 'Add Task') }}
                </button>
            </div>

            <div class="section-content">
                <!-- Debug Info (Hidden) -->
                <div v-if="false" style="padding: 10px; background: #f0f0f0; margin-bottom: 10px; font-size: 12px;">
                    Debug: loading={{ loading }}, tasks.length={{ tasks ? tasks.length : 'null' }}, tasks={{ tasks }}
                </div>

                <!-- Loading State -->
                <div v-if="loading" class="state-container">
                    <div class="loading-spinner">
                        <Refresh :size="32" class="spin-animation" />
                    </div>
                    <p class="state-text">{{ translate('domaincontrol', 'Loading tasks...') }}</p>
                </div>

                <!-- Empty State -->
                <div v-else-if="!tasks || tasks.length === 0" class="state-container empty-state">
                    <div class="empty-icon-wrapper">
                        <FormatListBulleted :size="48" />
                    </div>
                    <p class="state-text">{{ translate('domaincontrol', 'No tasks yet') }}</p>
                    <p class="state-subtext">{{ translate('domaincontrol', 'Create a task to get started') }}</p>
                </div>

                <!-- Content -->
                <div v-else class="tasks-container">
                    <!-- Progress Section -->
                    <div class="progress-section">
                        <div class="progress-info">
                            <span class="progress-label">
                                <strong>{{ translate('domaincontrol', 'Progress') }}</strong>
                                <span class="progress-stats">
                                    {{ completedTasks }} / {{ activeTasks }}
                                    <span v-if="cancelledTasks > 0" class="cancelled-stats">
                                        ({{ cancelledTasks }} {{ translate('domaincontrol', 'cancelled') }})
                                    </span>
                                </span>
                            </span>
                            <span class="progress-percentage">{{ progressPercentage }}%</span>
                        </div>
                        <div class="progress-track">
                            <div class="progress-fill" :style="{ width: progressPercentage + '%' }"></div>
                        </div>
                    </div>

                    <!-- Tasks List -->
                    <div class="task-list">
                        <div
                            v-for="task in tasks"
                            :key="task.id"
                            class="task-row"
                            :class="{ 
                                'task-overdue': isTaskOverdue(task), 
                                'task-cancelled': task.status === 'cancelled',
                                'task-done': task.status === 'done'
                            }"
                            @click="$emit('navigate-task', task.id)"
                        >
                            <!-- Checkbox Area -->
                            <div class="task-checkbox-wrapper" @click.stop>
                                <label class="custom-checkbox-container">
                                    <input
                                        type="checkbox"
                                        :checked="task.status === 'done'"
                                        :disabled="task.status === 'cancelled'"
                                        @change="$emit('toggle-status', task)"
                                    />
                                    <span class="checkmark"></span>
                                </label>
                            </div>

                            <!-- Main Content -->
                            <div class="task-main">
                                <div class="task-header">
                                    <span class="task-title">{{ task.title }}</span>
                                    
                                    <!-- Badges Row -->
                                    <div class="task-badges">
                                        <span class="nc-badge status-badge" :class="'status-' + task.status">
                                            {{ getTaskStatusText(task.status) }}
                                        </span>
                                        <span 
                                            v-if="task.priority !== 'medium'" 
                                            class="nc-badge priority-badge" 
                                            :class="'priority-' + task.priority"
                                        >
                                            <component 
                                                :is="task.priority === 'high' ? PriorityHigh : LowPriority" 
                                                :size="14" 
                                                style="margin-right: 2px; vertical-align: text-bottom;"
                                            />
                                            {{ getPriorityText(task.priority) }}
                                        </span>
                                    </div>
                                </div>

                                <!-- Metadata Row -->
                                <div class="task-meta-row">
                                    <span v-if="task.dueDate" class="meta-item" :class="{ 'text-error': isTaskOverdue(task) }">
                                        <Calendar :size="14" class="meta-icon" />
                                        {{ formatDate(task.dueDate) }}
                                    </span>

                                    <span v-if="task.assignedToUserId" class="meta-item user-tag">
                                        <span class="user-avatar-xs">
                                            <Account :size="12" />
                                        </span>
                                        {{ task.assignedToUserId }}
                                    </span>

                                    <span v-if="task.status === 'done' && task.completedByUserId" class="meta-item completed-by">
                                        <CheckCircle :size="14" class="meta-icon" />
                                        {{ task.completedByUserId }}
                                    </span>
                                </div>
                            </div>

                            <!-- Chevron/Action Indicator -->
                            <div class="task-action">
                                <ChevronRight :size="20" class="action-icon" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
// vue-material-design-icons
import ClipboardCheck from 'vue-material-design-icons/ClipboardCheck.vue'
import Plus from 'vue-material-design-icons/Plus.vue'
import Refresh from 'vue-material-design-icons/Refresh.vue'
import FormatListBulleted from 'vue-material-design-icons/FormatListBulleted.vue'
import Calendar from 'vue-material-design-icons/Calendar.vue'
import Account from 'vue-material-design-icons/Account.vue'
import CheckCircle from 'vue-material-design-icons/CheckCircle.vue'
import ChevronRight from 'vue-material-design-icons/ChevronRight.vue'
import PriorityHigh from 'vue-material-design-icons/PriorityHigh.vue'
import LowPriority from 'vue-material-design-icons/LowPriority.vue'

export default {
    name: 'ProjectTasks',
    components: {
        ClipboardCheck,
        Plus,
        Refresh,
        FormatListBulleted,
        Calendar,
        Account,
        CheckCircle,
        ChevronRight,
        PriorityHigh,
        LowPriority,
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
/* Reset & Base Variables fallback */
.tab-content {
    /* Nextcloud Standard Variables Fallbacks */
    --color-main-text: #222;
    --color-main-background: #fff;
    --color-background-hover: #f5f5f5;
    --color-primary-element-element: #0082c9;
    --color-primary-element-element-text: #ffffff;
    --color-border: #ededed;
    --color-border-dark: #dbdbdb;
    --color-text-maxcontrast: #767676;
    --color-text-light: #888;
    --color-element-success: #46ba61;
    --color-element-warning: #e99002;
    --color-element-error: #e9322d;
    --border-radius: 4px;
    --border-radius-large: 12px;
    
    padding: 0;
    color: var(--color-main-text);
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
}

/* Header Styling */
.section-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 0 20px 0;
    margin-bottom: 10px;
    border-bottom: 1px solid transparent;
}

.section-title {
    display: flex;
    align-items: center;
    gap: 12px;
    margin: 0;
    font-size: 20px;
    font-weight: 600;
    color: var(--color-main-text);
}

.icon-container {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    background-color: var(--color-background-hover);
    border-radius: 50%;
    color: var(--color-text-maxcontrast);
}

/* Button Styling (Nextcloud Native Look) */
.nc-button {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 8px 16px;
    border: none;
    border-radius: var(--border-radius-large); /* Yuvarlak buton */
    font-weight: 500;
    font-size: 14px;
    cursor: pointer;
    transition: background-color 0.2s, opacity 0.2s;
}

.nc-button-primary {
    background-color: var(--color-primary-element-element);
    color: var(--color-primary-element-element-text);
    box-shadow: 0 1px 2px rgba(0,0,0,0.1);
}

.nc-button-primary:hover {
    opacity: 0.9;
}

.icon-wrapper {
    display: flex;
    align-items: center;
}

/* States (Loading, Empty) */
.state-container {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 60px 20px;
    text-align: center;
    background-color: var(--color-main-background);
    border-radius: var(--border-radius-large);
}

.empty-icon-wrapper {
    color: var(--color-border-dark);
    margin-bottom: 16px;
    opacity: 0.5;
}

.state-text {
    font-size: 16px;
    font-weight: 600;
    color: var(--color-main-text);
    margin: 0 0 8px 0;
}

.state-subtext {
    font-size: 14px;
    color: var(--color-text-maxcontrast);
    margin: 0;
}

.spin-animation {
    animation: spin 1s linear infinite;
    color: var(--color-primary-element-element);
}

@keyframes spin {
    100% { transform: rotate(360deg); }
}

/* Progress Bar Section */
.progress-section {
    margin-bottom: 24px;
    padding: 16px;
    background-color: var(--color-background-hover);
    border-radius: var(--border-radius-large);
}

.progress-info {
    display: flex;
    justify-content: space-between;
    align-items: baseline;
    margin-bottom: 8px;
    font-size: 14px;
}

.progress-label {
    color: var(--color-main-text);
}

.progress-stats {
    margin-left: 8px;
    font-weight: normal;
    color: var(--color-text-maxcontrast);
}

.cancelled-stats {
    font-size: 12px;
    margin-left: 4px;
}

.progress-percentage {
    font-weight: 700;
    color: var(--color-primary-element-element);
}

.progress-track {
    width: 100%;
    height: 6px;
    background-color: rgba(0,0,0,0.05);
    border-radius: 99px;
    overflow: hidden;
}

.progress-fill {
    height: 100%;
    background-color: var(--color-element-success);
    border-radius: 99px;
    transition: width 0.4s ease-out;
}

/* Task List */
.task-list {
    display: flex;
    flex-direction: column;
    gap: 1px; /* Divider effect */
}

.task-row {
    position: relative;
    display: flex;
    align-items: flex-start;
    padding: 16px 12px;
    background-color: var(--color-main-background);
    border-bottom: 1px solid var(--color-border);
    cursor: pointer;
    transition: all 0.2s ease;
}

.task-row:first-child {
    border-top: 1px solid var(--color-border);
    border-top-left-radius: var(--border-radius);
    border-top-right-radius: var(--border-radius);
}

.task-row:last-child {
    border-bottom: none;
    border-bottom-left-radius: var(--border-radius);
    border-bottom-right-radius: var(--border-radius);
}

.task-row:hover {
    background-color: var(--color-background-hover);
    z-index: 1;
}

/* Checkbox Styling */
.task-checkbox-wrapper {
    padding-top: 2px;
    margin-right: 16px;
}

.custom-checkbox-container {
    display: block;
    position: relative;
    padding-left: 20px;
    cursor: pointer;
    user-select: none;
}

.custom-checkbox-container input {
    position: absolute;
    opacity: 0;
    cursor: pointer;
    height: 0;
    width: 0;
}

.checkmark {
    position: absolute;
    top: 0;
    left: 0;
    height: 18px;
    width: 18px;
    background-color: #fff;
    border: 2px solid var(--color-text-maxcontrast);
    border-radius: 50%; /* Circle style like Todo apps */
    transition: all 0.2s;
}

.custom-checkbox-container:hover input ~ .checkmark {
    border-color: var(--color-primary-element-element);
}

.custom-checkbox-container input:checked ~ .checkmark {
    background-color: var(--color-primary-element-element);
    border-color: var(--color-primary-element-element);
}

.checkmark:after {
    content: "";
    position: absolute;
    display: none;
}

.custom-checkbox-container input:checked ~ .checkmark:after {
    display: block;
}

.custom-checkbox-container .checkmark:after {
    left: 5px;
    top: 2px;
    width: 4px;
    height: 8px;
    border: solid white;
    border-width: 0 2px 2px 0;
    transform: rotate(45deg);
}

/* Task Content */
.task-main {
    flex: 1;
    min-width: 0;
}

.task-header {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 8px;
    margin-bottom: 6px;
}

.task-title {
    font-size: 15px;
    font-weight: 600;
    color: var(--color-main-text);
    line-height: 1.4;
}

/* Badges */
.nc-badge {
    display: inline-flex;
    align-items: center;
    padding: 2px 8px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 600;
    letter-spacing: 0.3px;
    line-height: 1.4;
}

.status-badge {
    background-color: var(--color-background-hover);
    color: var(--color-text-maxcontrast);
    border: 1px solid var(--color-border-dark);
}

.status-in_progress {
    background-color: #e3f2fd;
    color: #0d47a1;
    border-color: #bbdefb;
}

.status-done {
    background-color: #e8f5e9;
    color: #1b5e20;
    border-color: #c8e6c9;
}

.priority-high {
    background-color: #ffebee;
    color: #c62828;
    border-color: #ffcdd2;
}

.priority-low {
    background-color: #f1f8e9;
    color: #33691e;
    border-color: #dcedc8;
}

/* Meta Data */
.task-meta-row {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 16px;
    font-size: 12px;
    color: var(--color-text-maxcontrast);
}

.meta-item {
    display: inline-flex;
    align-items: center;
    gap: 4px;
}

.meta-icon {
    opacity: 0.7;
}

.text-error {
    color: var(--color-element-error);
    font-weight: 600;
}

.user-tag {
    background-color: var(--color-background-hover);
    padding: 2px 8px 2px 4px;
    border-radius: 12px;
    color: var(--color-main-text);
}

.user-avatar-xs {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 20px;
    height: 20px;
    border-radius: 50%;
    background-color: var(--color-border);
    color: var(--color-text-maxcontrast);
    margin-right: 4px;
}

/* Action Icon (Chevron) */
.task-action {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    margin-left: 8px;
    color: var(--color-border-dark);
    transition: color 0.2s;
}

.task-row:hover .task-action {
    color: var(--color-text-maxcontrast);
}

/* Done / Cancelled Modifiers */
.task-done .task-title {
    text-decoration: line-through;
    color: var(--color-text-maxcontrast);
}

.task-done .task-meta-row {
    opacity: 0.7;
}

.task-cancelled {
    opacity: 0.6;
    background-color: #fafafa;
}

.task-cancelled .task-title {
    text-decoration: line-through;
}

/* Overdue Modifier */
.task-overdue::before {
    content: '';
    position: absolute;
    left: 0;
    top: 0;
    bottom: 0;
    width: 3px;
    background-color: var(--color-element-error);
    border-top-left-radius: var(--border-radius);
    border-bottom-left-radius: var(--border-radius);
}
</style>