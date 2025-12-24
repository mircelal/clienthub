<template>
    <div class="project-overview-container">
        
        <!-- ========================================== -->
        <!-- STATS WIDGETS                              -->
        <!-- ========================================== -->
        <div v-if="project" class="nc-stats-grid">
            <!-- Client -->
            <div class="nc-stat-widget">
                <div class="widget-icon primary-bg">
                    <Account :size="24" />
                </div>
                <div class="widget-content">
                    <span class="widget-label">{{ translate('domaincontrol', 'Client') }}</span>
                    <span class="widget-value">
                        <a
                            v-if="project.clientId"
                            href="#"
                            @click.prevent="$emit('navigate-client', project.clientId)"
                            class="nc-link"
                        >
                            {{ getClientName(project.clientId) }}
                        </a>
                        <span v-else>-</span>
                    </span>
                </div>
            </div>

            <!-- Type -->
            <div class="nc-stat-widget">
                <div class="widget-icon secondary-bg">
                    <Shape :size="24" />
                </div>
                <div class="widget-content">
                    <span class="widget-label">{{ translate('domaincontrol', 'Type') }}</span>
                    <span class="widget-value">
                        {{ getProjectTypeText(project.projectType) || '-' }}
                    </span>
                </div>
            </div>

            <!-- Status -->
            <div class="nc-stat-widget">
                <div class="widget-icon" :class="getStatusColorClass(project.status)">
                    <component :is="getStatusIcon(project.status)" :size="24" />
                </div>
                <div class="widget-content">
                    <span class="widget-label">{{ translate('domaincontrol', 'Status') }}</span>
                    <span class="widget-value status-text" :class="getStatusColorTextClass(project.status)">
                        {{ getProjectStatusText(project.status) }}
                    </span>
                </div>
            </div>

            <!-- Start Date -->
            <div class="nc-stat-widget">
                <div class="widget-icon info-bg">
                    <CalendarStart :size="24" />
                </div>
                <div class="widget-content">
                    <span class="widget-label">{{ translate('domaincontrol', 'Start Date') }}</span>
                    <span class="widget-value">
                        {{ formatDateLocal(project.startDate) || '-' }}
                    </span>
                </div>
            </div>

            <!-- Deadline -->
            <div class="nc-stat-widget">
                <div class="widget-icon" :class="getDeadlineBgClass(project)">
                    <CalendarClock :size="24" />
                </div>
                <div class="widget-content">
                    <span class="widget-label">{{ translate('domaincontrol', 'Deadline') }}</span>
                    <span class="widget-value" :class="getDeadlineTextClass(project)">
                        {{ formatDateLocal(project.deadline) || '-' }}
                    </span>
                </div>
            </div>

            <!-- Budget -->
            <div class="nc-stat-widget">
                <div class="widget-icon success-bg">
                    <Cash :size="24" />
                </div>
                <div class="widget-content">
                    <span class="widget-label">{{ translate('domaincontrol', 'Budget') }}</span>
                    <span class="widget-value font-mono">
                        {{ formatCurrency(project.budget, project.currency) }}
                    </span>
                </div>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- MAIN CONTENT LAYOUT                        -->
        <!-- ========================================== -->
        <div v-if="project" class="nc-overview-layout">
            
            <!-- LEFT COLUMN: Info & Time -->
            <div class="layout-column main">
                
                <!-- Description Panel -->
                <div class="nc-panel">
                    <div class="nc-panel-header">
                        <h3>
                            <FormatText :size="20" class="header-icon" />
                            {{ translate('domaincontrol', 'Description') }}
                        </h3>
                    </div>
                    <div class="nc-panel-body description-text">
                        {{ project.description || translate('domaincontrol', 'No description provided.') }}
                    </div>
                </div>

                <!-- Notes Panel -->
                <div class="nc-panel">
                    <div class="nc-panel-header">
                        <h3>
                            <Notebook :size="20" class="header-icon" />
                            {{ translate('domaincontrol', 'Notes') }}
                        </h3>
                    </div>
                    <div class="nc-panel-body notes-content" v-html="project.notes || translate('domaincontrol', 'No notes.')"></div>
                </div>

                <!-- Time Summary Panel -->
                <div v-if="totalTime > 0" class="nc-panel">
                    <div class="nc-panel-header">
                        <h3>
                            <ClockOutline :size="20" class="header-icon" />
                            {{ translate('domaincontrol', 'Time Summary') }}
                        </h3>
                        <div class="header-badge primary">
                            {{ formatDurationLocal(totalTime) }}
                        </div>
                    </div>
                    
                    <div v-if="!durationByUser || durationByUser.length === 0" class="nc-empty-state small">
                        <p>{{ translate('domaincontrol', 'No time entries yet') }}</p>
                    </div>
                    <div v-else class="nc-list">
                         <div
                            v-for="userTime in durationByUser"
                            :key="userTime.userId || userTime.user_id"
                            class="nc-list-item"
                        >
                            <div class="nc-avatar-icon">
                                <AccountCircle :size="24" />
                            </div>
                            <div class="nc-list-content">
                                <span class="list-title">{{ getUserDisplayNameLocal(userTime.userId || userTime.user_id) }}</span>
                            </div>
                            <div class="nc-list-end font-mono">
                                {{ formatDurationLocal(userTime.duration || userTime.total_duration || 0) }}
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- RIGHT COLUMN: Recent Activity -->
            <div class="layout-column sidebar">
                <div class="nc-panel full-height">
                    <div class="nc-panel-header">
                        <h3>
                            <History :size="20" class="header-icon" />
                            {{ translate('domaincontrol', 'Activity') }}
                        </h3>
                    </div>

                    <div v-if="activitiesLoading" class="nc-empty-state small">
                        <Refresh :size="24" class="spin-animation" />
                        <p>{{ translate('domaincontrol', 'Loading...') }}</p>
                    </div>

                    <div v-else-if="!activities || activities.length === 0" class="nc-empty-state small">
                        <p>{{ translate('domaincontrol', 'No recent activity') }}</p>
                    </div>

                    <div v-else class="nc-activity-stream">
                        <div
                            v-for="activity in activities.slice(0, 15)"
                            :key="activity.id"
                            class="activity-entry"
                        >
                            <div class="activity-icon-col">
                                <div class="activity-dot">
                                    <component :is="getActivityIcon(activity.activityType)" :size="14" />
                                </div>
                                <div class="activity-line"></div>
                            </div>
                            <div class="activity-content-col">
                                <div class="activity-text">
                                    <span class="user-name">{{ getUserDisplayNameLocal(activity.userId) }}</span>
                                    {{ formatActivityDescription(activity) }}
                                </div>
                                <div class="activity-time">
                                    {{ formatDateLocal(activity.createdAt) }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <div v-else class="nc-empty-state">
            <FolderOpen :size="48" class="nc-state-icon" />
            <p>{{ translate('domaincontrol', 'No project selected') }}</p>
        </div>

    </div>
</template>

<script>
// vue-material-design-icons
import Account from 'vue-material-design-icons/Account.vue'
import Shape from 'vue-material-design-icons/Shape.vue'
import PlayCircleOutline from 'vue-material-design-icons/PlayCircleOutline.vue'
import PauseCircleOutline from 'vue-material-design-icons/PauseCircleOutline.vue'
import CheckCircleOutline from 'vue-material-design-icons/CheckCircleOutline.vue'
import CloseCircleOutline from 'vue-material-design-icons/CloseCircleOutline.vue'
import CircleOutline from 'vue-material-design-icons/CircleOutline.vue'
import CalendarStart from 'vue-material-design-icons/CalendarStart.vue'
import CalendarClock from 'vue-material-design-icons/CalendarClock.vue'
import Cash from 'vue-material-design-icons/Cash.vue'
import FormatText from 'vue-material-design-icons/FormatText.vue'
import Notebook from 'vue-material-design-icons/Notebook.vue'
import ClockOutline from 'vue-material-design-icons/ClockOutline.vue'
import AccountCircle from 'vue-material-design-icons/AccountCircle.vue'
import History from 'vue-material-design-icons/History.vue'
import Refresh from 'vue-material-design-icons/Refresh.vue'
import FolderOpen from 'vue-material-design-icons/FolderOpen.vue'
import NotePlus from 'vue-material-design-icons/NotePlus.vue'
import NoteEdit from 'vue-material-design-icons/NoteEdit.vue'
import NoteRemove from 'vue-material-design-icons/NoteRemove.vue'
import Upload from 'vue-material-design-icons/Upload.vue'
import FileMinus from 'vue-material-design-icons/FileMinus.vue'
import CheckboxMarkedCirclePlusOutline from 'vue-material-design-icons/CheckboxMarkedCirclePlusOutline.vue'
import CheckboxMarkedCircleOutline from 'vue-material-design-icons/CheckboxMarkedCircleOutline.vue'
import CheckboxMarkedCircle from 'vue-material-design-icons/CheckboxMarkedCircle.vue'
import Play from 'vue-material-design-icons/Play.vue'
import Stop from 'vue-material-design-icons/Stop.vue'
import ClockEdit from 'vue-material-design-icons/ClockEdit.vue'
import ClockRemove from 'vue-material-design-icons/ClockRemove.vue'
import FolderPlus from 'vue-material-design-icons/FolderPlus.vue'
import FolderEditOutline from 'vue-material-design-icons/FolderEditOutline.vue'
import AccountPlus from 'vue-material-design-icons/AccountPlus.vue'
import AccountMinus from 'vue-material-design-icons/AccountMinus.vue'
import LinkVariant from 'vue-material-design-icons/LinkVariant.vue'
import LinkVariantOff from 'vue-material-design-icons/LinkVariantOff.vue'
import Delete from 'vue-material-design-icons/Delete.vue'

export default {
    name: 'ProjectOverview',
    components: {
        Account,
        Shape,
        PlayCircleOutline,
        PauseCircleOutline,
        CheckCircleOutline,
        CloseCircleOutline,
        CircleOutline,
        CalendarStart,
        CalendarClock,
        Cash,
        TextSubject: FormatText,
        Notebook,
        ClockOutline,
        AccountCircle,
        History,
        Refresh,
        FolderOpen,
        NotePlus,
        NoteEdit,
        NoteRemove,
        FileUpload: Upload,
        FileRemove: FileMinus,
        CheckboxMarkedCirclePlusOutline,
        CheckboxMarkedCircleOutline,
        CheckboxMarkedCircle,
        Play,
        Stop,
        ClockEdit,
        ClockRemove,
        FolderPlus,
        FolderEdit: FolderEditOutline,
        AccountPlus,
        AccountMinus,
        LinkVariant,
        LinkVariantOff,
        Delete,
    },
    props: {
        project: {
            type: Object,
            default: null,
        },
        clients: {
            type: Array,
            default: () => [],
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
        activities: {
            type: Array,
            default: () => [],
        },
        activitiesLoading: {
            type: Boolean,
            default: false,
        },
        getUserDisplayName: {
            type: Function,
            default: null,
        },
        formatDuration: {
            type: Function,
            default: null,
        },
        formatDate: {
            type: Function,
            default: null,
        },
    },
    emits: ['navigate-client'],
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
            
            let result = text
            if (vars && typeof vars === 'object') {
                for (const key in vars) {
                    result = result.replace(new RegExp(`\\{${key}\\}`, 'g'), vars[key])
                }
            }
            return result
        },
        getClientName(clientId) {
            const client = this.clients.find(c => c.id === clientId)
            return client ? client.name : ''
        },
        getStatusColorClass(status) {
            const map = {
                active: 'primary-bg',
                on_hold: 'warning-bg',
                completed: 'success-bg',
                cancelled: 'error-bg'
            }
            return map[status] || 'secondary-bg'
        },
        getStatusColorTextClass(status) {
            const map = {
                active: 'text-primary',
                on_hold: 'text-warning',
                completed: 'text-success',
                cancelled: 'text-error'
            }
            return map[status] || ''
        },
        getStatusIcon(status) {
             const map = {
                active: 'PlayCircleOutline',
                on_hold: 'PauseCircleOutline',
                completed: 'CheckCircleOutline',
                cancelled: 'CloseCircleOutline'
            }
            return map[status] || 'CircleOutline'
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
        formatDateLocal(date) {
            if (this.formatDate && typeof this.formatDate === 'function') return this.formatDate(date)
            if (!date) return ''
            const d = new Date(date)
            return d.toLocaleDateString('tr-TR', { year: 'numeric', month: 'short', day: 'numeric' })
        },
        formatCurrency(amount, currency = 'USD') {
            if (amount === null || amount === undefined) return '-'
            // Handle string numbers
            const val = parseFloat(amount)
            if (isNaN(val) || val === 0) return '-'
            return new Intl.NumberFormat('tr-TR', {
                style: 'currency',
                currency: currency || 'USD',
            }).format(val)
        },
        formatDurationLocal(seconds) {
            if (this.formatDuration && typeof this.formatDuration === 'function') {
                const result = this.formatDuration(seconds)
                if (result && result !== 'NaN' && result !== 'NaNm') return result
            }
            
            // Ensure seconds is a valid number
            const sec = parseInt(seconds) || 0
            if (sec === 0) return '0s'
            
            const h = Math.floor(sec / 3600)
            const m = Math.floor((sec % 3600) / 60)
            return h > 0 ? `${h}h ${m}m` : `${m}m`
        },
        getUserDisplayNameLocal(userId) {
            if (!userId) return 'Unknown'
            
            // Try prop function first
            if (this.getUserDisplayName && typeof this.getUserDisplayName === 'function') {
                const result = this.getUserDisplayName(userId)
                if (result && result !== userId) return result
            }
            
            // Try availableUsers array
            if (this.availableUsers && Array.isArray(this.availableUsers) && this.availableUsers.length > 0) {
                const user = this.availableUsers.find(u => u.userId === userId)
                if (user && user.displayName) {
                    return user.displayName
                }
            }
            
            // Fallback: return userId or 'Unknown'
            return userId || 'Unknown'
        },
        getDeadlineBgClass(project) {
            if (!project || !project.deadline) return 'secondary-bg'
            const deadline = new Date(project.deadline)
            const today = new Date()
            today.setHours(0, 0, 0, 0)
            deadline.setHours(0, 0, 0, 0)
            const diffDays = Math.ceil((deadline - today) / (1000 * 60 * 60 * 24))
            
            if (diffDays < 0) return 'error-bg' // Overdue
            if (diffDays <= 7) return 'warning-bg' // Approaching
            return 'secondary-bg'
        },
        getDeadlineTextClass(project) {
            if (!project || !project.deadline) return ''
            const deadline = new Date(project.deadline)
            const today = new Date()
            today.setHours(0, 0, 0, 0)
            deadline.setHours(0, 0, 0, 0)
            const diffDays = Math.ceil((deadline - today) / (1000 * 60 * 60 * 24))
            
            if (diffDays < 0) return 'text-error font-bold'
            if (diffDays <= 7) return 'text-warning font-bold'
            return ''
        },
        getActivityIcon(type) {
            // Mapping to vue-material-design-icons component names
            const icons = {
                note_created: 'NotePlus',
                note_updated: 'NoteEdit',
                note_deleted: 'NoteRemove',
                file_uploaded: 'FileUpload',
                file_deleted: 'FileRemove',
                task_created: 'CheckboxMarkedCirclePlusOutline',
                task_updated: 'CheckboxMarkedCircleOutline',
                task_completed: 'CheckboxMarkedCircle',
                task_deleted: 'Delete',
                time_started: 'Play',
                time_stopped: 'Stop',
                time_updated: 'ClockEdit',
                time_deleted: 'ClockRemove',
                project_created: 'FolderPlus',
                project_shared: 'AccountPlus',
                project_unshared: 'AccountMinus',
                project_updated: 'FolderEdit',
                item_linked: 'LinkVariant',
                item_unlinked: 'LinkVariantOff',
            }
            return icons[type] || 'History'
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
    },
}
</script>

<style scoped>
/* GLOBAL STYLES */
.project-overview-container {
    color: var(--color-main-text);
    font-family: var(--font-face, sans-serif);
}

/* --- Stats Grid --- */
.nc-stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 16px;
    margin-bottom: 24px;
}

.nc-stat-widget {
    background-color: var(--color-main-background);
    border: 1px solid var(--color-border);
    border-radius: var(--border-radius-large);
    padding: 16px;
    display: flex;
    align-items: center;
    gap: 12px;
    transition: transform 0.2s, border-color 0.2s;
}

.nc-stat-widget:hover {
    border-color: var(--color-primary-element-element-element);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.05);
}

.widget-icon {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--color-text-maxcontrast);
    background-color: var(--color-background-hover);
}

.primary-bg { background-color: rgba(0, 130, 201, 0.1); color: var(--color-primary-element-element); }
.success-bg { background-color: rgba(70, 186, 97, 0.1); color: var(--color-element-success); }
.warning-bg { background-color: rgba(233, 144, 2, 0.1); color: var(--color-element-warning); }
.error-bg { background-color: rgba(233, 50, 45, 0.1); color: var(--color-element-error); }
.info-bg { background-color: rgba(0, 130, 201, 0.05); color: var(--color-text-maxcontrast); }
.secondary-bg { background-color: var(--color-background-dark); color: var(--color-text-maxcontrast); }

.widget-content {
    display: flex;
    flex-direction: column;
    min-width: 0;
}

.widget-label {
    font-size: 12px;
    color: var(--color-text-maxcontrast);
    margin-bottom: 2px;
}

.widget-value {
    font-size: 15px;
    font-weight: 600;
    color: var(--color-main-text);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.nc-link {
    color: var(--color-primary-element-element);
    text-decoration: none;
}
.nc-link:hover { text-decoration: underline; }

.status-text { text-transform: capitalize; }
.text-primary { color: var(--color-primary-element-element); }
.text-success { color: var(--color-element-success); }
.text-warning { color: var(--color-element-warning); }
.text-error { color: var(--color-element-error); }
.font-bold { font-weight: 700; }
.font-mono { font-family: monospace; }

/* --- Layout --- */
.nc-overview-layout {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 24px;
}

.layout-column {
    display: flex;
    flex-direction: column;
    gap: 24px;
}

/* --- Panels --- */
.nc-panel {
    background-color: var(--color-main-background);
    border: 1px solid var(--color-border);
    border-radius: var(--border-radius-large);
    padding: 20px;
    display: flex;
    flex-direction: column;
}

.nc-panel.full-height {
    height: 100%;
    min-height: 400px;
}

.nc-panel-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 16px;
    border-bottom: 1px solid var(--color-border);
    padding-bottom: 12px;
}

.nc-panel-header h3 {
    margin: 0;
    font-size: 16px;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 8px;
    color: var(--color-main-text);
}

.header-icon { opacity: 0.7; color: var(--color-text-maxcontrast); }

.nc-panel-body {
    font-size: 14px;
    line-height: 1.6;
    color: var(--color-main-text);
}

.description-text, .notes-content {
    white-space: pre-wrap;
}

.header-badge {
    font-size: 12px;
    padding: 2px 8px;
    border-radius: 10px;
    font-weight: bold;
}
.header-badge.primary { background-color: var(--color-background-dark); color: var(--color-primary-element-element); }

/* --- Lists (Time Summary) --- */
.nc-list {
    display: flex;
    flex-direction: column;
}

.nc-list-item {
    display: flex;
    align-items: center;
    padding: 8px 0;
    border-bottom: 1px solid var(--color-border);
}
.nc-list-item:last-child { border-bottom: none; }

.nc-avatar-icon {
    color: var(--color-text-maxcontrast);
    opacity: 0.5;
    margin-right: 12px;
    display: flex;
}

.nc-list-content { flex: 1; }
.list-title { font-weight: 500; font-size: 14px; }
.nc-list-end { font-weight: 600; font-size: 14px; color: var(--color-text-maxcontrast); }

/* --- Activity Stream --- */
.nc-activity-stream {
    display: flex;
    flex-direction: column;
}

.activity-entry {
    display: flex;
    gap: 12px;
    padding-bottom: 16px;
}

.activity-icon-col {
    display: flex;
    flex-direction: column;
    align-items: center;
    width: 24px;
}

.activity-dot {
    width: 24px;
    height: 24px;
    border-radius: 50%;
    background-color: var(--color-background-hover);
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--color-text-maxcontrast);
    z-index: 1;
}

.activity-line {
    width: 2px;
    flex: 1;
    background-color: var(--color-border);
    margin-top: 4px;
    min-height: 10px;
}
.activity-entry:last-child .activity-line { display: none; }

.activity-content-col {
    flex: 1;
    padding-top: 2px;
}

.activity-text {
    font-size: 13px;
    line-height: 1.4;
    color: var(--color-main-text);
}

.user-name {
    font-weight: 600;
    margin-right: 4px;
}

.activity-time {
    font-size: 11px;
    color: var(--color-text-maxcontrast);
    margin-top: 4px;
}

/* --- Empty State --- */
.nc-empty-state {
    padding: 30px;
    text-align: center;
    color: var(--color-text-maxcontrast);
    background-color: var(--color-background-hover);
    border-radius: var(--border-radius-large);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
}

.nc-empty-state.small {
    padding: 15px;
    background: transparent;
    border: none;
}

.nc-state-icon { margin-bottom: 12px; opacity: 0.5; }
.spin-animation { animation: spin 1s linear infinite; }

@keyframes spin { 100% { transform: rotate(360deg); } }

/* Mobile */
@media (max-width: 900px) {
    .nc-overview-layout { grid-template-columns: 1fr; }
}
</style>