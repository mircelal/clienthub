<template>
	<div v-if="isOpen" class="modal-overlay" @click.self="closeModal">
		<div class="modal-content modal-large">
			<div class="modal-header">
				<h3 class="modal-title">
					{{ task && task.id ? translate('domaincontrol', 'Edit Task') : translate('domaincontrol', 'Add Task') }}
				</h3>
				<button class="modal-close" @click="closeModal">
					<MaterialIcon name="close" :size="24" />
				</button>
			</div>

			<form @submit.prevent="saveTask" class="modal-body">
				<div class="form-row">
					<div class="form-group">
						<label for="task-project-id" class="form-label">
							{{ translate('domaincontrol', 'Project') }}
						</label>
						<select
							id="task-project-id"
							v-model="formData.projectId"
							class="form-control"
							@change="onProjectChange"
						>
							<option value="">{{ translate('domaincontrol', 'Select Project (optional)') }}</option>
							<option
								v-for="project in projects"
								:key="project.id"
								:value="project.id"
							>
								{{ project.name }}
							</option>
						</select>
					</div>
					<div class="form-group">
						<label for="task-client-id" class="form-label">
							{{ translate('domaincontrol', 'Client') }}
						</label>
						<select
							id="task-client-id"
							v-model="formData.clientId"
							class="form-control"
						>
							<option value="">{{ translate('domaincontrol', 'Select Client (optional)') }}</option>
							<option
								v-for="client in clients"
								:key="client.id"
								:value="client.id"
							>
								{{ client.name }}
							</option>
						</select>
					</div>
				</div>

				<div class="form-group">
					<label for="task-title" class="form-label">
						{{ translate('domaincontrol', 'Title') }} *
					</label>
					<input
						id="task-title"
						v-model="formData.title"
						type="text"
						class="form-control"
						required
					/>
				</div>

				<div class="form-group">
					<label for="task-description" class="form-label">
						{{ translate('domaincontrol', 'Description') }}
					</label>
					<textarea
						id="task-description"
						v-model="formData.description"
						class="form-control"
						rows="4"
					></textarea>
				</div>

				<div class="form-row">
					<div class="form-group">
						<label for="task-status" class="form-label">
							{{ translate('domaincontrol', 'Status') }}
						</label>
						<select
							id="task-status"
							v-model="formData.status"
							class="form-control"
						>
							<option value="todo">{{ translate('domaincontrol', 'To Do') }}</option>
							<option value="in_progress">{{ translate('domaincontrol', 'In Progress') }}</option>
							<option value="done">{{ translate('domaincontrol', 'Done') }}</option>
							<option value="cancelled">{{ translate('domaincontrol', 'Cancelled') }}</option>
						</select>
					</div>
					<div class="form-group">
						<label for="task-priority" class="form-label">
							{{ translate('domaincontrol', 'Priority') }}
						</label>
						<select
							id="task-priority"
							v-model="formData.priority"
							class="form-control"
						>
							<option value="low">{{ translate('domaincontrol', 'Low') }}</option>
							<option value="medium">{{ translate('domaincontrol', 'Medium') }}</option>
							<option value="high">{{ translate('domaincontrol', 'High') }}</option>
						</select>
					</div>
				</div>

				<div class="form-group" v-if="!formData.parentId">
					<label for="task-parent-id" class="form-label">
						{{ translate('domaincontrol', 'Parent Task') }}
					</label>
					<select
						id="task-parent-id"
						v-model="formData.parentId"
						class="form-control"
					>
						<option value="">{{ translate('domaincontrol', 'No Parent Task') }}</option>
						<option
							v-for="parentTask in availableParentTasks"
							:key="parentTask.id"
							:value="parentTask.id"
						>
							{{ parentTask.title }}
						</option>
					</select>
				</div>

				<div class="form-row">
					<div class="form-group">
						<label for="task-due-date" class="form-label">
							{{ translate('domaincontrol', 'Due Date') }}
						</label>
						<input
							id="task-due-date"
							v-model="formData.dueDate"
							type="date"
							class="form-control"
						/>
					</div>
				</div>

				<div class="form-group" v-if="formData.projectId">
					<label for="task-assigned-to" class="form-label">
						{{ translate('domaincontrol', 'Assigned To') }}
					</label>
					<select
						id="task-assigned-to"
						v-model="formData.assignedToUserId"
						class="form-control"
					>
						<option value="">{{ translate('domaincontrol', 'Unassigned') }}</option>
						<!-- TODO: Load users from project shares -->
					</select>
					<small class="form-hint">
						{{ translate('domaincontrol', 'Assign task to a user (active when project is selected)') }}
					</small>
				</div>

				<div class="form-group">
					<label for="task-notes" class="form-label">
						{{ translate('domaincontrol', 'Notes (Internal use)') }}
					</label>
					<RichTextEditor
						v-model="formData.notes"
						:placeholder="translate('domaincontrol', 'Technical details or special notes for the task...')"
					/>
				</div>

				<div class="form-actions">
					<button type="button" class="button-vue button-vue--secondary" @click="closeModal">
						{{ translate('domaincontrol', 'Cancel') }}
					</button>
					<button type="submit" class="button-vue button-vue--primary" :disabled="saving">
						<MaterialIcon v-if="saving" name="loading" :size="18" class="loading-icon" />
						{{ saving ? translate('domaincontrol', 'Saving...') : translate('domaincontrol', 'Save') }}
					</button>
				</div>
			</form>
		</div>
	</div>
</template>

<script>
import api from '../services/api'
import MaterialIcon from './MaterialIcon.vue'
import RichTextEditor from './RichTextEditor.vue'

export default {
	name: 'TaskModal',
	components: {
		MaterialIcon,
		RichTextEditor,
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
		availableParentTasks() {
			// Filter out the current task and its subtasks
			if (this.task && this.task.id) {
				return this.tasks.filter(t => 
					!t.parentId && 
					t.id !== this.task.id &&
					(!this.task.parentId || t.id !== this.task.parentId)
				)
			}
			return this.tasks.filter(t => !t.parentId)
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
			// When project changes, we might want to update client or other fields
			// For now, we just keep the current implementation
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

				// Convert empty strings to null for optional fields
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
				'Edit Task': 'Görev Düzenle',
				'Project': 'Proje',
				'Select Project (optional)': 'Proje Seçin (opsiyonel)',
				'Client': 'Müşteri',
				'Select Client (optional)': 'Müşteri Seçin (opsiyonel)',
				'Title': 'Başlık',
				'Description': 'Açıklama',
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
				'Assign task to a user (active when project is selected)': 'Görevi bir kullanıcıya atayın (proje seçildiğinde aktif)',
				'Notes (Internal use)': 'Notlar (Dahili kullanım için)',
				'Technical details or special notes for the task...': 'Görevin teknik detayları veya özel notlar...',
				'Cancel': 'İptal',
				'Saving...': 'Kaydediliyor...',
				'Save': 'Kaydet',
				'Error saving task': 'Görev kaydedilirken hata oluştu',
			}

			return translations[text] || text
		},
	},
}
</script>

<style scoped>
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
	padding: 20px;
}

.modal-content {
	background-color: var(--color-main-background);
	border-radius: var(--border-radius-container);
	box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
	max-width: 600px;
	width: 100%;
	max-height: 90vh;
	overflow-y: auto;
	display: flex;
	flex-direction: column;
}

.modal-large {
	max-width: 800px;
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
	background: none;
	border: none;
	color: var(--color-text-maxcontrast);
	cursor: pointer;
	padding: 4px;
	border-radius: var(--border-radius-small);
	transition: background-color 0.2s;
	display: flex;
	align-items: center;
	justify-content: center;
}

.modal-close:hover {
	background-color: var(--color-background-hover);
}

.modal-body {
	padding: 20px;
	display: flex;
	flex-direction: column;
	gap: 16px;
}

.form-row {
	display: grid;
	grid-template-columns: 1fr 1fr;
	gap: 16px;
}

.form-group {
	display: flex;
	flex-direction: column;
	gap: 8px;
}

.form-label {
	font-size: 14px;
	font-weight: 500;
	color: var(--color-main-text);
}

.form-control {
	padding: 8px 12px;
	border: 1px solid var(--color-border);
	border-radius: var(--border-radius-element);
	background-color: var(--color-background-dark);
	color: var(--color-main-text);
	font-size: 14px;
	font-family: inherit;
}

.form-control:focus {
	outline: none;
	border-color: var(--color-primary-element);
}

.form-control::placeholder {
	color: var(--color-text-maxcontrast);
}

textarea.form-control {
	resize: vertical;
	min-height: 80px;
}

.form-hint {
	font-size: 11px;
	color: var(--color-text-maxcontrast);
	margin-top: 4px;
}

.form-actions {
	display: flex;
	justify-content: flex-end;
	gap: 12px;
	margin-top: 8px;
	padding-top: 16px;
	border-top: 1px solid var(--color-border);
}

.loading-icon {
	animation: spin 1s linear infinite;
}

@keyframes spin {
	from {
		transform: rotate(0deg);
	}
	to {
		transform: rotate(360deg);
	}
}
</style>

