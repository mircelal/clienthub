<template>
    <div class="project-tasks-time-container">
        
        <!-- ========================================== -->
        <!-- TASKS SECTION                              -->
        <!-- ========================================== -->
        <div class="nc-section">
            <div class="nc-section-header">
                <h3 class="nc-section-title">
                    <span class="nc-icon-wrapper">
                        <ClipboardCheck :size="20" />
                    </span>
                    {{ translate('domaincontrol', 'Tasks') }}
                </h3>
                <NcButton type="primary" @click="$emit('add-task')">
                    <template #icon>
                        <Plus :size="18" />
                    </template>
                    {{ translate('domaincontrol', 'Add Task') }}
                </NcButton>
            </div>

            <div class="nc-section-content">
                <!-- Loading State -->
                <div v-if="tasksLoading" class="nc-empty-state">
                    <div class="nc-state-icon spin-animation">
                        <Refresh :size="32" />
                    </div>
                    <p>{{ translate('domaincontrol', 'Loading tasks...') }}</p>
                </div>

                <!-- Empty State -->
                <div v-else-if="!tasks || tasks.length === 0" class="nc-empty-state">
                    <div class="nc-state-icon">
                        <FormatListBulleted :size="48" />
                    </div>
                    <p class="nc-empty-text">{{ translate('domaincontrol', 'No tasks yet') }}</p>
                    <p class="nc-empty-subtext">{{ translate('domaincontrol', 'Create a task to get started') }}</p>
                </div>

                <!-- Content -->
                <div v-else class="nc-content-wrapper">
                    <!-- Progress Bar -->
                    <div class="nc-progress-container">
                        <div class="nc-progress-info">
                            <span class="nc-progress-label">
                                {{ translate('domaincontrol', 'Progress') }}
                                <span class="nc-progress-stats">
                                    {{ completedTasks }} / {{ activeTasks }}
                                </span>
                            </span>
                            <span class="nc-progress-percentage">{{ progressPercentage }}%</span>
                        </div>
                        <NcProgressBar :value="progressPercentage" />
                    </div>

                    <!-- Tasks List -->
                    <div class="nc-task-list">
                        <div
                            v-for="task in tasks"
                            :key="task.id"
                            class="nc-list-row"
                            :class="{ 
                                'is-overdue': isTaskOverdue(task), 
                                'is-cancelled': task.status === 'cancelled',
                                'is-done': task.status === 'done'
                            }"
                            @click="$emit('navigate-task', task.id)"
                        >
                            <!-- Custom Checkbox -->
                            <div class="nc-checkbox-wrapper" @click.stop>
                                <label class="nc-custom-checkbox">
                                    <input
                                        type="checkbox"
                                        :checked="task.status === 'done'"
                                        :disabled="task.status === 'cancelled'"
                                        @change="$emit('toggle-status', task)"
                                    />
                                    <span class="nc-checkmark"></span>
                                </label>
                            </div>

                            <div class="nc-row-content">
                                <div class="nc-row-header">
                                    <span class="nc-task-title">{{ task.title }}</span>
                                    <div class="nc-badges">
                                        <!-- Priority Badge -->
                                        <span 
                                            v-if="task.priority !== 'medium'" 
                                            class="nc-badge" 
                                            :class="'badge-priority-' + task.priority"
                                        >
                                            {{ getPriorityText(task.priority) }}
                                        </span>
                                    </div>
                                </div>
                                
                                <!-- Task Description -->
                                <div v-if="task.description" class="nc-task-description">
                                    {{ task.description }}
                                </div>
                                
                                <div class="nc-row-meta">
                                    <span v-if="task.dueDate" class="nc-meta-item" :class="{ 'text-error': isTaskOverdue(task) }">
                                        <Calendar :size="14" class="icon-fix" />
                                        {{ formatDate(task.dueDate) }}
                                    </span>
                                    <span v-if="task.assignedToUserId" class="nc-meta-item nc-user-tag">
                                        <Account :size="14" class="icon-fix" />
                                        {{ task.assignedToUserId }}
                                    </span>
                                    <span v-if="task.status === 'done' && task.completedByUserId" class="nc-meta-item">
                                        <CheckCircle :size="14" class="icon-fix" />
                                        {{ task.completedByUserId }}
                                    </span>
                                </div>
                            </div>

                            <div class="nc-row-action">
                                <ChevronRight :size="20" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- TIME TRACKING SECTION                      -->
        <!-- ========================================== -->
        <div class="nc-section">
            <div class="nc-section-header">
                <h3 class="nc-section-title">
                    <span class="nc-icon-wrapper">
                        <Clock :size="20" />
                    </span>
                    {{ translate('domaincontrol', 'Time Tracking') }}
                </h3>
            </div>

            <div class="nc-section-content">
                <!-- Improved Timer Card -->
                <div class="timer-card" :class="{ 'is-active': !!currentRunningEntry }">
                    <!-- Left: Timer Display -->
                    <div class="timer-left">
                        <div class="timer-clock">
                            {{ formatLocalTimer(timerElapsed) }}
                        </div>
                        <div class="timer-status-indicator">
                            <span class="status-badge" v-if="currentRunningEntry">
                                <span class="blink-dot"></span> {{ translate('domaincontrol', 'Running') }}
                            </span>
                            <span class="status-badge idle" v-else>
                                {{ translate('domaincontrol', 'Idle') }}
                            </span>
                        </div>
                    </div>

                    <!-- Middle: Input -->
                    <div class="timer-middle">
                        <input
                            :value="timerDescription"
                            @input="$emit('update:timerDescription', $event.target.value)"
                            type="text"
                            class="nc-input-clean"
                            :placeholder="translate('domaincontrol', 'What are you working on?')"
                        />
                    </div>

                    <!-- Right: Controls -->
                    <div class="timer-right">
                        <NcButton
                            v-if="!currentRunningEntry"
                            type="primary"
                            @click="$emit('start-timer')"
                            :disabled="timerStarting"
                            class="timer-btn-circle"
                            :title="translate('domaincontrol', 'Start Timer')"
                        >
                            <template #icon>
                                <Play :size="24" />
                            </template>
                        </NcButton>
                        
                        <NcButton
                            v-else
                            type="error"
                            @click="$emit('stop-timer')"
                            :disabled="timerStopping"
                            class="timer-btn-circle"
                            :title="translate('domaincontrol', 'Stop Timer')"
                        >
                            <template #icon>
                                <Stop :size="24" />
                            </template>
                        </NcButton>
                    </div>
                </div>

                <!-- Statistics Grid -->
                <div class="stats-grid">
                    <div class="stat-box total-box">
                        <div class="stat-icon">
                            <ChartPie :size="20" />
                        </div>
                        <div class="stat-content">
                            <span class="stat-label">{{ translate('domaincontrol', 'Total Time') }}</span>
                            <span class="stat-number">{{ formatDuration(totalTime) }}</span>
                        </div>
                    </div>
                    
                    <div v-if="durationByUser && durationByUser.length > 0" class="stat-box users-box">
                         <div class="stat-user-scroll">
                            <div v-for="userTime in durationByUser" :key="userTime.userId" class="mini-user-row">
                                <span class="mini-avatar">
                                    <Account :size="16" />
                                </span>
                                <span class="mini-name">{{ getUserDisplayName(userTime.userId) }}</span>
                                <span class="mini-time">{{ formatDuration(userTime.duration) }}</span>
                            </div>
                         </div>
                    </div>
                </div>

                <!-- Time Entries List -->
                <div class="nc-entry-section">
                    <h4 class="subsection-title">{{ translate('domaincontrol', 'Activity Log') }}</h4>
                    
                    <div v-if="timeEntriesLoading" class="nc-empty-state small-empty">
                        <Refresh :size="24" class="spin-animation" />
                    </div>

                    <div v-else-if="!timeEntries || timeEntries.length === 0" class="nc-empty-state small-empty">
                        <p>{{ translate('domaincontrol', 'No recent activity') }}</p>
                    </div>

                    <div v-else class="nc-clean-list">
                        <div
                            v-for="entry in timeEntries"
                            :key="entry.id"
                            class="clean-list-item"
                        >
                            <div class="item-icon">
                                <History :size="18" />
                            </div>
                            <div class="item-main">
                                <div class="item-desc">
                                    {{ entry.description || translate('domaincontrol', '(No description)') }}
                                </div>
                                <div class="item-meta">
                                    {{ formatDate(entry.startTime) }} • 
                                    <span v-if="entry.endTime">{{ formatTime(entry.startTime) }} - {{ formatTime(entry.endTime) }}</span>
                                    <span v-else class="text-running">{{ translate('domaincontrol', 'Now') }}</span>
                                </div>
                            </div>
                            <div class="item-end">
                                <span class="item-duration">{{ formatDuration(entry.duration || 0) }}</span>
                                <button
                                    class="btn-icon-only"
                                    @click="$emit('delete-entry', entry.id)"
                                    :title="translate('domaincontrol', 'Delete')"
                                >
                                    <Delete :size="18" />
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
import { NcButton, NcProgressBar } from '@nextcloud/vue'
// vue-material-design-icons
import ClipboardCheck from 'vue-material-design-icons/ClipboardCheck.vue'
import Plus from 'vue-material-design-icons/Plus.vue'
import Refresh from 'vue-material-design-icons/Refresh.vue'
import FormatListBulleted from 'vue-material-design-icons/FormatListBulleted.vue'
import Calendar from 'vue-material-design-icons/Calendar.vue'
import Account from 'vue-material-design-icons/Account.vue'
import CheckCircle from 'vue-material-design-icons/CheckCircle.vue'
import ChevronRight from 'vue-material-design-icons/ChevronRight.vue'
import Clock from 'vue-material-design-icons/Clock.vue'
import Play from 'vue-material-design-icons/Play.vue'
import Stop from 'vue-material-design-icons/Stop.vue'
import ChartPie from 'vue-material-design-icons/ChartPie.vue'
import History from 'vue-material-design-icons/History.vue'
import Delete from 'vue-material-design-icons/Delete.vue'

export default {
    name: 'ProjectTasksAndTime',
    components: {
        NcButton,
        NcProgressBar,
        ClipboardCheck,
        Plus,
        Refresh,
        FormatListBulleted,
        Calendar,
        Account,
        CheckCircle,
        ChevronRight,
        Clock,
        Play,
        Stop,
        ChartPie,
        History,
        Delete,
    },
    props: {
        tasks: {
            type: Array,
            default: () => [],
        },
        tasksLoading: {
            type: Boolean,
            default: false,
        },
        timeEntries: {
            type: Array,
            default: () => [],
        },
        timeEntriesLoading: {
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
        getPriorityText: {
            type: Function,
            required: true,
        },
        getTaskStatusText: {
            type: Function,
            required: true,
        },
        isTaskOverdue: {
            type: Function,
            required: true,
        },
        formatDate: {
            type: Function,
            required: true,
        },
        formatTimerTime: {
            type: Function,
            required: true,
        },
        formatDuration: {
            type: Function,
            required: true,
        },
        formatTime: {
            type: Function,
            required: true,
        },
        getUserDisplayName: {
            type: Function,
            required: true,
        },
    },
    emits: ['add-task', 'navigate-task', 'toggle-status', 'start-timer', 'stop-timer', 'update:timerDescription', 'delete-entry'],
    computed: {
        completedTasks() {
            return this.tasks ? this.tasks.filter(t => t.status === 'done').length : 0
        },
        activeTasks() {
            return this.tasks ? this.tasks.filter(t => t.status !== 'cancelled').length : 0
        },
        cancelledTasks() {
            return this.tasks ? this.tasks.filter(t => t.status === 'cancelled').length : 0
        },
        progressPercentage() {
            if (!this.activeTasks) return 0
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
        // TIMEZONE FIX: Calculates duration mathematically
        formatLocalTimer(ms) {
            if (!ms || ms < 0) ms = 0;
            
            const totalSeconds = Math.floor(ms / 1000);
            const hours = Math.floor(totalSeconds / 3600);
            const minutes = Math.floor((totalSeconds % 3600) / 60);
            const seconds = totalSeconds % 60;
            
            const pad = (num) => num.toString().padStart(2, '0');
            
            return `${pad(hours)}:${pad(minutes)}:${pad(seconds)}`;
        }
    },
}
</script>

<style scoped>
/* GLOBAL STYLES */
.project-tasks-time-container {
    color: var(--color-main-text);
    font-family: var(--font-face, sans-serif);
}

.nc-section {
    margin-bottom: 32px;
}

/* --- Headers --- */
.nc-section-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 16px;
    padding-bottom: 12px;
    border-bottom: 1px solid var(--color-border);
}

.nc-section-title {
    display: flex;
    align-items: center;
    gap: 12px;
    margin: 0;
    font-size: 18px;
    font-weight: 600;
    color: var(--color-main-text);
}

.nc-icon-wrapper {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    background-color: var(--color-background-dark);
    border-radius: 50%;
    color: var(--color-text-maxcontrast);
}

/* --- Empty States --- */
.nc-empty-state {
    padding: 30px;
    text-align: center;
    color: var(--color-text-maxcontrast);
    background-color: var(--color-background-hover);
    border-radius: var(--border-radius-large);
}

.nc-state-icon {
    display: inline-flex;
    margin-bottom: 12px;
    opacity: 0.5;
}

.spin-animation {
    animation: spin 1s linear infinite;
}
@keyframes spin { 100% { transform: rotate(360deg); } }

/* --- Progress Bar --- */
.nc-progress-container { margin-bottom: 20px; }
.nc-progress-info { display: flex; justify-content: space-between; font-size: 13px; color: var(--color-text-maxcontrast); margin-bottom: 8px; }

/* --- Task List --- */
.nc-list-row {
    display: flex;
    align-items: center;
    padding: 12px;
    border-bottom: 1px solid var(--color-border);
    background: var(--color-main-background);
    transition: background 0.2s;
    cursor: pointer;
}
.nc-list-row:first-child { border-top: 1px solid var(--color-border); border-top-left-radius: var(--border-radius); border-top-right-radius: var(--border-radius); }
.nc-list-row:last-child { border-bottom-left-radius: var(--border-radius); border-bottom-right-radius: var(--border-radius); border-bottom: none; }
.nc-list-row:hover { background: var(--color-background-hover); }

/* Custom Checkbox */
.nc-checkbox-wrapper { margin-right: 14px; display: flex; align-items: center; }
.nc-custom-checkbox { position: relative; padding-left: 20px; cursor: pointer; }
.nc-custom-checkbox input { position: absolute; opacity: 0; }
.nc-checkmark { position: absolute; top: -9px; left: 0; height: 18px; width: 18px; border: 2px solid var(--color-text-maxcontrast); border-radius: 50%; }
.nc-custom-checkbox input:checked ~ .nc-checkmark { background: var(--color-primary-element-element); border-color: var(--color-primary-element-element); }
.nc-custom-checkbox input:checked ~ .nc-checkmark:after { content: ""; position: absolute; display: block; left: 5px; top: 2px; width: 4px; height: 8px; border: solid white; border-width: 0 2px 2px 0; transform: rotate(45deg); }

/* Task Content */
.nc-row-content { flex: 1; }
.nc-task-title { font-weight: 600; color: var(--color-main-text); margin-right: 8px; }
.nc-row-header { margin-bottom: 4px; display: flex; align-items: center; }
.nc-task-description { 
    font-size: 13px; 
    color: var(--color-text-maxcontrast); 
    margin: 4px 0 6px 0; 
    line-height: 1.4;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    text-overflow: ellipsis;
}
.nc-row-meta { display: flex; gap: 12px; font-size: 12px; color: var(--color-text-maxcontrast); align-items: center; margin-top: 4px; }
.nc-meta-item { display: inline-flex; align-items: center; gap: 4px; }
.icon-fix { display: inline-flex; } /* Ensure icons don't collapse */

/* Badges */
.nc-badge { padding: 2px 8px; border-radius: 10px; font-size: 11px; font-weight: bold; }
.badge-priority-high { color: var(--color-element-error); background: rgba(233, 50, 45, 0.1); }
.badge-priority-low { color: var(--color-element-success); background: rgba(70, 186, 97, 0.1); }

/* --- NEW TIMER DESIGN (Card) --- */
.timer-card {
    display: flex;
    align-items: center;
    background: var(--color-main-background);
    border: 1px solid var(--color-border);
    border-radius: var(--border-radius-large);
    padding: 16px 24px;
    margin-bottom: 24px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.02);
    transition: all 0.3s ease;
    flex-wrap: wrap;
    gap: 16px;
}

.timer-card.is-active {
    border-color: var(--color-primary-element-element);
    background: linear-gradient(to right, var(--color-main-background), rgba(0, 130, 201, 0.03));
}

.timer-left {
    display: flex;
    flex-direction: column;
    justify-content: center;
    min-width: 120px;
    border-right: 1px solid var(--color-border);
    padding-right: 20px;
}

.timer-clock {
    font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', monospace;
    font-size: 28px;
    font-weight: 500;
    color: var(--color-main-text);
    line-height: 1.2;
}

.timer-status-indicator {
    margin-top: 4px;
}

.status-badge {
    font-size: 11px;
    text-transform: uppercase;
    font-weight: 700;
    color: var(--color-element-success);
    display: inline-flex;
    align-items: center;
    gap: 6px;
}

.status-badge.idle { color: var(--color-text-maxcontrast); }

.blink-dot {
    width: 8px;
    height: 8px;
    background: var(--color-element-success);
    border-radius: 50%;
    animation: blink 1s infinite;
}
@keyframes blink { 50% { opacity: 0.3; } }

.timer-middle {
    flex: 1;
    min-width: 200px;
}

.nc-input-clean {
    width: 100%;
    border: none;
    background: transparent;
    font-size: 16px;
    color: var(--color-main-text);
    padding: 8px 0;
}
.nc-input-clean:focus { outline: none; border-bottom: 2px solid var(--color-primary-element-element); }
.nc-input-clean::placeholder { color: var(--color-text-maxcontrast); opacity: 0.6; }

.timer-right {
    display: flex;
    align-items: center;
}

.timer-btn-circle {
    border-radius: 50% !important;
    padding: 0 !important;
    display: flex !important;
    align-items: center;
    justify-content: center;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}

/* --- Stats Grid --- */
.stats-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    margin-bottom: 30px;
}

.stat-box {
    background: var(--color-main-background);
    border: 1px solid var(--color-border);
    border-radius: var(--border-radius-large);
    padding: 16px;
    display: flex;
    align-items: center;
    gap: 16px;
}

.stat-icon {
    width: 40px;
    height: 40px;
    background: var(--color-background-hover);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--color-primary-element-element);
}

.stat-content { display: flex; flex-direction: column; }
.stat-label { font-size: 12px; text-transform: uppercase; color: var(--color-text-maxcontrast); }
.stat-number { font-size: 20px; font-weight: 600; color: var(--color-main-text); }

.stat-user-scroll { width: 100%; max-height: 80px; overflow-y: auto; }
.mini-user-row { display: flex; align-items: center; justify-content: space-between; font-size: 13px; padding: 4px 0; border-bottom: 1px solid var(--color-border); }
.mini-user-row:last-child { border: none; }
.mini-avatar { display: inline-flex; color: var(--color-text-maxcontrast); margin-right: 8px; }
.mini-name { flex: 1; color: var(--color-main-text); }
.mini-time { font-weight: 600; color: var(--color-text-maxcontrast); }

/* --- Activity List --- */
.subsection-title { font-size: 14px; font-weight: 600; color: var(--color-text-maxcontrast); margin-bottom: 12px; text-transform: uppercase; letter-spacing: 0.5px; }

.nc-clean-list { display: flex; flex-direction: column; border: 1px solid var(--color-border); border-radius: var(--border-radius); overflow: hidden; }

.clean-list-item {
    display: flex;
    align-items: center;
    padding: 12px 16px;
    background: var(--color-main-background);
    border-bottom: 1px solid var(--color-border);
    transition: background 0.1s;
}
.clean-list-item:last-child { border-bottom: none; }
.clean-list-item:hover { background: var(--color-background-hover); }

.item-icon { color: var(--color-text-maxcontrast); margin-right: 16px; opacity: 0.6; display: inline-flex; }
.item-main { flex: 1; }
.item-desc { font-size: 14px; font-weight: 500; color: var(--color-main-text); margin-bottom: 2px; }
.item-meta { font-size: 12px; color: var(--color-text-maxcontrast); }
.item-end { display: flex; align-items: center; gap: 12px; }
.item-duration { font-family: monospace; font-weight: 600; color: var(--color-main-text); }

.btn-icon-only {
    background: none; border: none; cursor: pointer; color: var(--color-text-maxcontrast); opacity: 0; transition: all 0.2s; padding: 4px; border-radius: 50%;
}
.clean-list-item:hover .btn-icon-only { opacity: 1; }
.btn-icon-only:hover { background: var(--color-background-dark); color: var(--color-element-error); }
.text-running { color: var(--color-element-success); font-weight: bold; }

/* Mobile */
@media (max-width: 650px) {
    .timer-card { flex-direction: column; align-items: stretch; text-align: center; }
    .timer-left { border-right: none; border-bottom: 1px solid var(--color-border); padding-right: 0; padding-bottom: 12px; margin-bottom: 12px; }
    .timer-right { justify-content: center; }
    .stats-grid { grid-template-columns: 1fr; }
}
</style>