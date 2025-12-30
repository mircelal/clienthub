<template>
    <transition name="fade">
        <div v-if="isOpen" class="nc-modal-overlay" @click.self="closeModal" @keydown.esc="closeModal" tabindex="0">
            <div class="nc-modal-container">
                
                <!-- HEADER -->
                <div class="nc-modal-header">
                    <h3 class="nc-modal-title">
                        {{ task && task.id ? translate('domaincontrol', 'Edit Task') : translate('domaincontrol', 'Add Task') }}
                    </h3>
                    <button class="nc-modal-close-btn" @click="closeModal" :aria-label="translate('domaincontrol', 'Close')">
                        <Close :size="20" />
                    </button>
                </div>

                <!-- BODY -->
                <form @submit.prevent="saveTask" id="taskForm" class="nc-modal-body">
                    
                    <!-- Context Section (Project / Client) -->
                    <div class="form-section" v-if="!hasProjectId">
                        <div class="form-row">
                            <div class="form-group half">
                                <label for="task-project-id">{{ translate('domaincontrol', 'Project') }}</label>
                                <div class="input-wrapper">
                                    <BriefcaseOutline :size="16" class="input-icon" />
                                    <select id="task-project-id" v-model="formData.projectId" class="nc-input with-icon" @change="onProjectChange">
                                        <option value="">{{ translate('domaincontrol', 'Select Project (optional)') }}</option>
                                        <option v-for="project in projects" :key="project.id" :value="project.id">
                                            {{ project.name }}
                                        </option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group half">
                                <label for="task-client-id">{{ translate('domaincontrol', 'Client') }}</label>
                                <div class="input-wrapper">
                                    <AccountTie :size="16" class="input-icon" />
                                    <select id="task-client-id" v-model="formData.clientId" class="nc-input with-icon">
                                        <option value="">{{ translate('domaincontrol', 'Select Client (optional)') }}</option>
                                        <option v-for="client in clients" :key="client.id" :value="client.id">
                                            {{ client.name }}
                                        </option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Main Info Section -->
                    <div class="form-group">
                        <label for="task-title">{{ translate('domaincontrol', 'Title') }} *</label>
                        <div class="input-wrapper">
                            <FormatTitle :size="16" class="input-icon" />
                            <input id="task-title" v-model="formData.title" type="text" class="nc-input with-icon" required :placeholder="translate('domaincontrol', 'Task title...')" />
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="task-description">{{ translate('domaincontrol', 'Description') }}</label>
                        <textarea id="task-description" v-model="formData.description" class="nc-input textarea" rows="3" :placeholder="translate('domaincontrol', 'Task details...')"></textarea>
                    </div>

                    <!-- Meta Data Row (Status, Priority, Date) -->
                    <div class="form-row three-col">
                        <div class="form-group">
                            <label for="task-status">{{ translate('domaincontrol', 'Status') }}</label>
                            <div class="input-wrapper">
                                <ListStatus :size="16" class="input-icon" />
                                <select id="task-status" v-model="formData.status" class="nc-input with-icon">
                                    <option value="todo">{{ translate('domaincontrol', 'To Do') }}</option>
                                    <option value="in_progress">{{ translate('domaincontrol', 'In Progress') }}</option>
                                    <option value="done">{{ translate('domaincontrol', 'Done') }}</option>
                                    <option value="cancelled">{{ translate('domaincontrol', 'Cancelled') }}</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="task-priority">{{ translate('domaincontrol', 'Priority') }}</label>
                            <div class="input-wrapper">
                                <Flag :size="16" class="input-icon" />
                                <select id="task-priority" v-model="formData.priority" class="nc-input with-icon">
                                    <option value="low">{{ translate('domaincontrol', 'Low') }}</option>
                                    <option value="medium">{{ translate('domaincontrol', 'Medium') }}</option>
                                    <option value="high">{{ translate('domaincontrol', 'High') }}</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="task-due-date">{{ translate('domaincontrol', 'Due Date') }}</label>
                            <div class="input-wrapper">
                                <Calendar :size="16" class="input-icon" />
                                <input id="task-due-date" v-model="formData.dueDate" type="date" class="nc-input with-icon" />
                            </div>
                        </div>
                    </div>

                    <!-- Assignment & Hierarchy -->
                    <div class="form-row">
                        <div class="form-group half" v-if="!formData.parentId">
                            <label for="task-parent-id">{{ translate('domaincontrol', 'Parent Task') }}</label>
                            <div class="input-wrapper">
                                <SourceBranch :size="16" class="input-icon" />
                                <select id="task-parent-id" v-model="formData.parentId" class="nc-input with-icon">
                                    <option value="">{{ translate('domaincontrol', 'No Parent Task') }}</option>
                                    <option v-for="parentTask in availableParentTasks" :key="parentTask.id" :value="parentTask.id">
                                        {{ parentTask.title }}
                                    </option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group half" v-if="formData.projectId">
                            <label for="task-assigned-to">{{ translate('domaincontrol', 'Assigned To') }}</label>
                            <div class="input-wrapper">
                                <AccountOutline :size="16" class="input-icon" />
                                <select id="task-assigned-to" v-model="formData.assignedToUserId" class="nc-input with-icon">
                                    <option value="">{{ translate('domaincontrol', 'Unassigned') }}</option>
                                    <!-- User list would be populated here -->
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Notes -->
                    <div class="form-group">
                        <label for="task-notes">{{ translate('domaincontrol', 'Notes (Internal use)') }}</label>
                        <RichTextEditor
                            v-model="formData.notes"
                            :placeholder="translate('domaincontrol', 'Technical details or special notes for the task...')"
                            class="nc-richtext"
                        />
                    </div>

                </form>

                <!-- FOOTER -->
                <div class="nc-modal-footer">
                    <button type="button" class="nc-button secondary" @click="closeModal">
                        {{ translate('domaincontrol', 'Cancel') }}
                    </button>
                    <button type="submit" form="taskForm" class="nc-button primary" :disabled="saving">
                        <span v-if="saving" class="spin-loader"></span>
                        {{ saving ? translate('domaincontrol', 'Saving...') : translate('domaincontrol', 'Save') }}
                    </button>
                </div>

            </div>
        </div>
    </transition>
</template>

<script>
import api from '../services/api'
import RichTextEditor from './RichTextEditor.vue'

// Modern Icons
import Close from 'vue-material-design-icons/Close.vue'
import BriefcaseOutline from 'vue-material-design-icons/BriefcaseOutline.vue'
import AccountTie from 'vue-material-design-icons/AccountTie.vue'
import FormatTitle from 'vue-material-design-icons/FormatTitle.vue'
import ListStatus from 'vue-material-design-icons/ListStatus.vue'
import Flag from 'vue-material-design-icons/Flag.vue'
import Calendar from 'vue-material-design-icons/Calendar.vue'
import SourceBranch from 'vue-material-design-icons/SourceBranch.vue'
import AccountOutline from 'vue-material-design-icons/AccountOutline.vue'

export default {
    name: 'TaskModal',
    components: {
        RichTextEditor,
        Close, BriefcaseOutline, AccountTie, FormatTitle, ListStatus, Flag, Calendar, SourceBranch, AccountOutline
    },
    props: {
        open: {
            type: Boolean,
            default: false,
        },
        task: {
            type: Object,
            default: null,
        },
        projects: {
            type: Array,
            default: () => [],
        },
        clients: {
            type: Array,
            default: () => [],
        },
        tasks: {
            type: Array,
            default: () => [],
        },
        presetClientId: {
            type: [Number, String],
            default: null,
        },
        projectId: {
            type: [Number, String],
            default: null,
        },
        clientId: {
            type: [Number, String],
            default: null,
        },
    },
    emits: ['close', 'saved'],
    data() {
        return {
            saving: false,
            formData: {
                title: '',
                projectId: '',
                clientId: '',
                parentId: '',
                description: '',
                status: 'todo',
                priority: 'medium',
                dueDate: '',
                assignedToUserId: '',
                notes: '',
            },
        }
    },
    computed: {
        isOpen() {
            return this.open
        },
        hasProjectId() {
            return this.projectId !== null && this.projectId !== undefined && this.projectId !== ''
        },
        availableParentTasks() {
            let filtered = this.tasks || []
            
            // Filter out tasks that already have a parent (they can't be parents)
            filtered = filtered.filter(t => !t.parentId)
            
            // Filter out the current task if editing
            if (this.task && this.task.id) {
                filtered = filtered.filter(t => t.id !== this.task.id)
            }
            
            // If clientId is set, filter by client
            if (this.formData.clientId) {
                filtered = filtered.filter(t => 
                    (t.clientId == this.formData.clientId || t.client_id == this.formData.clientId)
                )
            }
            
            // If projectId is set, filter by project
            if (this.formData.projectId) {
                filtered = filtered.filter(t => 
                    (t.projectId == this.formData.projectId || t.project_id == this.formData.projectId)
                )
            }
            
            return filtered
        },
    },
    watch: {
        open(newVal) {
            if (newVal) {
                this.resetForm()
                if (this.task && this.task.id) {
                    this.loadTaskData()
                } else if (this.task) {
                    // New task with pre-filled data (e.g., for subtask)
                    this.formData = {
                        title: '',
                        projectId: this.task.projectId || '',
                        clientId: this.task.clientId || '',
                        parentId: this.task.parentId || '',
                        description: '',
                        status: 'todo',
                        priority: 'medium',
                        dueDate: '',
                        assignedToUserId: '',
                        notes: '',
                    }
                } else {
                    // Defaults based on props
                    if (this.projectId) this.formData.projectId = this.projectId
                    if (this.clientId) this.formData.clientId = this.clientId
                    
                    if (this.projectId && !this.clientId && this.presetClientId) {
                        this.formData.clientId = this.presetClientId
                    }
                    if (!this.projectId && this.presetClientId) {
                        this.formData.clientId = this.presetClientId
                    }
                }
            }
        },
    },
    methods: {
        resetForm() {
            this.formData = {
                title: '',
                projectId: '',
                clientId: '',
                parentId: '',
                description: '',
                status: 'todo',
                priority: 'medium',
                dueDate: '',
                assignedToUserId: '',
                notes: '',
            }
        },
        loadTaskData() {
            if (!this.task || !this.task.id) return

            this.formData = {
                title: this.task.title || '',
                projectId: this.task.projectId || '',
                clientId: this.task.clientId || '',
                parentId: this.task.parentId || '',
                description: this.task.description || '',
                status: this.task.status || 'todo',
                priority: this.task.priority || 'medium',
                dueDate: this.task.dueDate ? this.task.dueDate.split(' ')[0] : '',
                assignedToUserId: this.task.assignedToUserId || '',
                notes: this.task.notes || '',
            }
        },
        onProjectChange() {
            // Logic preserved from original
        },
        async saveTask() {
            this.saving = true
            try {
                const data = {
                    title: this.formData.title || '',
                    projectId: this.formData.projectId || '',
                    clientId: this.formData.clientId || '',
                    parentId: this.formData.parentId || '',
                    description: this.formData.description || '',
                    status: this.formData.status || 'todo',
                    priority: this.formData.priority || 'medium',
                    dueDate: this.formData.dueDate || '',
                    assignedToUserId: this.formData.assignedToUserId || '',
                    notes: this.formData.notes || '',
                }

                if (this.projectId) data.projectId = this.projectId
                if (this.clientId) data.clientId = this.clientId

                // Convert empty strings to null
                if (!data.projectId) data.projectId = null
                if (!data.clientId) data.clientId = null
                if (!data.parentId) data.parentId = null
                if (!data.assignedToUserId) data.assignedToUserId = null

                if (this.task && this.task.id) {
                    await api.tasks.update(this.task.id, data)
                } else {
                    await api.tasks.create(data)
                }

                this.$emit('saved')
                this.closeModal()
            } catch (error) {
                console.error('Error saving task:', error)
                alert(this.translate('domaincontrol', 'Error saving task'))
            } finally {
                this.saving = false
            }
        },
        closeModal() {
            this.$emit('close')
        },
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

            const translations = {
                'Add Task': 'Görev Ekle',
                'Edit Task': 'Görev Düzenle',
                'Project': 'Proje',
                'Select Project (optional)': 'Proje Seçin (opsiyonel)',
                'Client': 'Müşteri',
                'Select Client (optional)': 'Müşteri Seçin (opsiyonel)',
                'Title': 'Başlık',
                'Task title...': 'Görev başlığı...',
                'Description': 'Açıklama',
                'Task details...': 'Görev detayları...',
                'Status': 'Durum',
                'To Do': 'Yapılacak',
                'In Progress': 'Devam Ediyor',
                'Done': 'Tamamlandı',
                'Cancelled': 'İptal Edildi',
                'Priority': 'Öncelik',
                'Low': 'Düşük',
                'Medium': 'Orta',
                'High': 'Yüksek',
                'Parent Task': 'Üst Görev',
                'No Parent Task': 'Üst Görev Yok',
                'Due Date': 'Bitiş Tarihi',
                'Assigned To': 'Atanan Kişi',
                'Unassigned': 'Atanmamış',
                'Notes (Internal use)': 'Notlar (Dahili kullanım)',
                'Technical details or special notes for the task...': 'Teknik detaylar veya özel notlar...',
                'Cancel': 'İptal',
                'Saving...': 'Kaydediliyor...',
                'Save': 'Kaydet',
                'Error saving task': 'Görev kaydedilirken hata oluştu',
                'Close': 'Kapat'
            }
            return translations[text] || text
        },
    },
}
</script>

<style scoped>
/* NEXTCLOUD MODAL STYLES (HUB Style) */
.nc-modal-overlay {
    position: fixed;
    top: 0; left: 0; right: 0; bottom: 0;
    background-color: rgba(0, 0, 0, 0.4);
    backdrop-filter: blur(4px); /* Modern Hub Blur */
    z-index: 10000;
    display: flex;
    align-items: center;
    justify-content: center;
    animation: fadeIn 0.2s ease-out;
}

.nc-modal-container {
    background-color: var(--color-main-background);
    color: var(--color-main-text);
    width: 100%;
    max-width: 650px;
    max-height: 90vh;
    border-radius: var(--border-radius-large);
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
    display: flex;
    flex-direction: column;
    overflow: hidden;
    animation: slideUp 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    position: relative;
    border: 1px solid var(--color-border);
}

/* HEADER */
.nc-modal-header {
    padding: 18px 24px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid var(--color-border);
    flex-shrink: 0;
    background-color: var(--color-main-background);
}
.nc-modal-title {
    margin: 0;
    font-size: 18px;
    font-weight: 700;
    color: var(--color-main-text);
}
.nc-modal-close-btn {
    background: none;
    border: none;
    color: var(--color-text-maxcontrast);
    cursor: pointer;
    border-radius: 50%;
    width: 36px; height: 36px;
    display: flex; align-items: center; justify-content: center;
    transition: background 0.15s;
}
.nc-modal-close-btn:hover { background-color: var(--color-background-hover); }

/* BODY */
.nc-modal-body {
    padding: 24px;
    overflow-y: auto;
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 20px;
}

/* FOOTER */
.nc-modal-footer {
    padding: 16px 24px;
    border-top: 1px solid var(--color-border);
    display: flex;
    justify-content: flex-end;
    gap: 12px;
    background-color: var(--color-background-dark);
    flex-shrink: 0;
}

/* --- FORM STYLES --- */
.form-group {
    display: flex; flex-direction: column; gap: 6px;
}
.form-group label {
    font-size: 13px; font-weight: 600; color: var(--color-text-maxcontrast);
}

/* Layout Grid System */
.form-row { display: flex; gap: 20px; }
.half { flex: 1; }
.three-col { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 15px; }

/* Input Styling */
.input-wrapper { position: relative; }
.input-icon {
    position: absolute; left: 10px; top: 50%;
    transform: translateY(-50%);
    color: var(--color-text-maxcontrast); opacity: 0.7;
    pointer-events: none;
}

.nc-input {
    width: 100%;
    padding: 4px 12px;
    border: 1px solid var(--color-border);
    border-radius: var(--border-radius);
    background-color: var(--color-main-background);
    color: var(--color-main-text);
    font-size: 14px;
    box-sizing: border-box;
    transition: border-color 0.15s, box-shadow 0.15s;
    appearance: none;
}
.nc-input.with-icon { padding-left: 36px; }
.nc-input.textarea { resize: vertical; min-height: 80px; font-family: inherit; }

.nc-input:focus {
    border-color: var(--color-primary-element);
    box-shadow: 0 0 0 2px var(--color-primary-element-light);
    outline: none;
}

/* Select specific styling (to show chevron) */
select.nc-input {
    background-image: url("data:image/svg+xml;charset=utf-8,%3Csvg xmlns='http://www.w3.org/2000/svg' height='24' viewBox='0 0 24 24' width='24'%3E%3Cpath d='M7 10l5 5 5-5z' fill='%23666'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 8px center;
    padding-right: 30px;
}

/* --- BUTTONS --- */
.nc-button {
    padding: 10px 24px;
    border-radius: var(--border-radius-pill);
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    border: none;
    display: flex; align-items: center; justify-content: center; gap: 8px;
    transition: all 0.2s;
}
.nc-button.primary {
    background-color: var(--color-primary-element);
    color: var(--color-primary-element-text);
}
.nc-button.primary:hover:not(:disabled) { opacity: 0.9; }
.nc-button.primary:disabled { opacity: 0.6; cursor: not-allowed; }

.nc-button.secondary {
    background-color: transparent;
    border: 1px solid var(--color-border);
    color: var(--color-main-text);
}
.nc-button.secondary:hover { background-color: var(--color-background-hover); }

/* Loader */
.spin-loader {
    width: 14px; height: 14px; border: 2px solid rgba(255,255,255,0.3);
    border-top-color: white; border-radius: 50%; animation: spin 1s linear infinite;
}

@keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
@keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
@keyframes slideUp { from { opacity: 0; transform: translateY(20px) scale(0.95); } to { opacity: 1; transform: translateY(0) scale(1); } }
</style>