<template>
	<div class="tab-content">
		<div class="detail-section">
			<div class="section-header">
				<h3 class="section-title">
					<MaterialIcon name="note" :size="24" />
					{{ translate('domaincontrol', 'Notes') }}
				</h3>
				<button class="button-vue button-vue--primary" @click="showAddNoteModal">
					<MaterialIcon name="add" :size="18" />
					{{ translate('domaincontrol', 'Add Note') }}
				</button>
			</div>
			<div class="section-content">
				<!-- Category Filter -->
				<div class="category-filter">
					<button
						v-for="cat in categories"
						:key="cat.value"
						class="category-button"
						:class="{ active: selectedCategory === cat.value }"
						@click="selectedCategory = cat.value"
					>
						{{ cat.label }}
					</button>
				</div>

				<div v-if="loading" class="loading-content">
					<MaterialIcon name="loading" :size="32" class="loading-icon" />
					<p>{{ translate('domaincontrol', 'Loading notes...') }}</p>
				</div>
				<div v-else-if="filteredNotes.length === 0" class="empty-content">
					<p class="empty-content__text">{{ translate('domaincontrol', 'No notes yet') }}</p>
				</div>
				<div v-else class="notes-list">
					<div
						v-for="note in filteredNotes"
						:key="note.id"
						class="note-item"
						@click="editNote(note)"
					>
						<div class="note-header">
							<h4 class="note-title">{{ note.title }}</h4>
							<div class="note-actions">
								<button
									class="action-button action-button--edit"
									@click.stop="editNote(note)"
									:title="translate('domaincontrol', 'Edit')"
								>
									<MaterialIcon name="edit" :size="16" />
								</button>
								<button
									class="action-button action-button--delete"
									@click.stop="deleteNote(note.id)"
									:title="translate('domaincontrol', 'Delete')"
								>
									<MaterialIcon name="delete" :size="16" />
								</button>
							</div>
						</div>
						<div class="note-content" v-html="formatContent(note.content)"></div>
						<div class="note-meta">
							<span class="note-category">{{ getCategoryLabel(note.category) }}</span>
							<span class="note-date">{{ formatDate(note.updatedAt || note.createdAt) }}</span>
						</div>
					</div>
				</div>
			</div>
		</div>

		<!-- Note Modal -->
		<div v-if="showNoteModal" class="modal-overlay" @click.self="closeNoteModal">
			<div class="modal-content modal-content--large">
				<div class="modal-header">
					<h2 class="modal-title">{{ editingNote ? translate('domaincontrol', 'Edit Note') : translate('domaincontrol', 'Add Note') }}</h2>
					<button class="modal-close" @click="closeNoteModal">
						<MaterialIcon name="close" :size="24" />
					</button>
				</div>
				<div class="modal-body">
					<div class="form-group">
						<label class="form-label">{{ translate('domaincontrol', 'Category') }}</label>
						<select v-model="noteForm.category" class="form-control">
							<option
								v-for="cat in categories"
								:key="cat.value"
								:value="cat.value"
							>
								{{ cat.label }}
							</option>
						</select>
					</div>
					<div class="form-group">
						<label class="form-label">{{ translate('domaincontrol', 'Title') }}</label>
						<input
							v-model="noteForm.title"
							type="text"
							class="form-control"
							:placeholder="translate('domaincontrol', 'Note title')"
						/>
					</div>
					<div class="form-group">
						<label class="form-label">{{ translate('domaincontrol', 'Content') }}</label>
						<textarea
							v-model="noteForm.content"
							class="form-control"
							rows="10"
							:placeholder="translate('domaincontrol', 'Note content')"
						></textarea>
					</div>
				</div>
				<div class="modal-footer">
					<button class="button-vue button-vue--secondary" @click="closeNoteModal">
						{{ translate('domaincontrol', 'Cancel') }}
					</button>
					<button class="button-vue button-vue--primary" @click="saveNote" :disabled="!noteForm.title">
						{{ translate('domaincontrol', 'Save') }}
					</button>
				</div>
			</div>
		</div>
	</div>
</template>

<script>
import MaterialIcon from '../MaterialIcon.vue'
import api from '../../services/api'

export default {
	name: 'ProjectNotes',
	components: {
		MaterialIcon,
	},
	props: {
		projectId: {
			type: Number,
			required: true,
		},
		initialCategory: {
			type: String,
			default: null,
		},
	},
	emits: ['note-saved', 'note-deleted'],
	data() {
		return {
			notes: [],
			loading: false,
			selectedCategory: this.initialCategory || 'all',
			showNoteModal: false,
			editingNote: null,
			noteForm: {
				category: 'general',
				title: '',
				content: '',
			},
			categories: [
				{ value: 'all', label: this.translate('domaincontrol', 'All') },
				{ value: 'general', label: this.translate('domaincontrol', 'General') },
				{ value: 'requirements', label: this.translate('domaincontrol', 'Requirements') },
				{ value: 'challenges', label: this.translate('domaincontrol', 'Challenges') },
				{ value: 'research', label: this.translate('domaincontrol', 'Research') },
			],
		}
	},
	computed: {
		filteredNotes() {
			if (!Array.isArray(this.notes)) {
				return []
			}
			if (this.selectedCategory === 'all') {
				return this.notes
			}
			return this.notes.filter(n => n.category === this.selectedCategory)
		},
	},
	watch: {
		initialCategory(newVal) {
			if (newVal) {
				this.selectedCategory = newVal
			}
		},
	},
	mounted() {
		if (this.projectId) {
			this.loadNotes()
		}
	},
	watch: {
		projectId(newVal) {
			if (newVal) {
				this.loadNotes()
			}
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
		async loadNotes() {
			this.loading = true
			try {
				const response = await api.projectNotes.getAll(this.projectId)
				this.notes = Array.isArray(response.data) ? response.data : []
			} catch (error) {
				console.error('Error loading notes:', error)
				this.notes = []
			} finally {
				this.loading = false
			}
		},
		showAddNoteModal() {
			this.editingNote = null
			this.noteForm = {
				category: this.selectedCategory !== 'all' ? this.selectedCategory : 'general',
				title: '',
				content: '',
			}
			this.showNoteModal = true
		},
		editNote(note) {
			this.editingNote = note
			this.noteForm = {
				category: note.category,
				title: note.title,
				content: note.content || '',
			}
			this.showNoteModal = true
		},
		closeNoteModal() {
			this.showNoteModal = false
			this.editingNote = null
			this.noteForm = {
				category: 'general',
				title: '',
				content: '',
			}
		},
		async saveNote() {
			if (!this.noteForm.title) return
			
			try {
				if (this.editingNote) {
					await api.projectNotes.update(this.projectId, this.editingNote.id, this.noteForm)
				} else {
					await api.projectNotes.create(this.projectId, this.noteForm)
				}
				await this.loadNotes()
				this.closeNoteModal()
				this.$emit('note-saved')
			} catch (error) {
				console.error('Error saving note:', error)
				alert(this.translate('domaincontrol', 'Error saving note'))
			}
		},
		async deleteNote(id) {
			if (!confirm(this.translate('domaincontrol', 'Are you sure you want to delete this note?'))) {
				return
			}
			
			try {
				await api.projectNotes.delete(this.projectId, id)
				await this.loadNotes()
				this.$emit('note-deleted')
			} catch (error) {
				console.error('Error deleting note:', error)
				alert(this.translate('domaincontrol', 'Error deleting note'))
			}
		},
		getCategoryLabel(category) {
			const cat = this.categories.find(c => c.value === category)
			return cat ? cat.label : category
		},
		formatContent(content) {
			if (!content) return ''
			// Simple markdown-like formatting
			return content
				.replace(/\n/g, '<br>')
				.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
				.replace(/\*(.*?)\*/g, '<em>$1</em>')
		},
		formatDate(date) {
			if (!date) return ''
			const d = new Date(date)
			return d.toLocaleDateString('tr-TR', { year: 'numeric', month: 'short', day: 'numeric' })
		},
	},
}
</script>

<style scoped>
.detail-section {
	margin-bottom: 24px;
}

.section-header {
	display: flex;
	align-items: center;
	justify-content: space-between;
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

.category-filter {
	display: flex;
	gap: 8px;
	margin-bottom: 16px;
	flex-wrap: wrap;
}

.category-button {
	padding: 6px 12px;
	border: 1px solid var(--color-border);
	border-radius: var(--border-radius-element);
	background-color: var(--color-main-background);
	color: var(--color-main-text);
	font-size: 13px;
	cursor: pointer;
	transition: all 0.2s;
}

.category-button:hover {
	background-color: var(--color-background-hover);
}

.category-button.active {
	background-color: var(--color-primary-element-element-element);
	color: var(--color-primary-element-element-element-text-dark);
	border-color: var(--color-primary-element-element-element);
}

.notes-list {
	display: flex;
	flex-direction: column;
	gap: 12px;
}

.note-item {
	padding: 16px;
	background-color: var(--color-main-background);
	border: 1px solid var(--color-border);
	border-radius: var(--border-radius-element);
	cursor: pointer;
	transition: background-color 0.2s;
}

.note-item:hover {
	background-color: var(--color-background-hover);
}

.note-header {
	display: flex;
	justify-content: space-between;
	align-items: flex-start;
	margin-bottom: 8px;
}

.note-title {
	margin: 0;
	font-size: 16px;
	font-weight: 600;
	color: var(--color-main-text);
}

.note-actions {
	display: flex;
	gap: 4px;
}

.note-content {
	font-size: 14px;
	color: var(--color-main-text);
	margin-bottom: 8px;
	line-height: 1.5;
}

.note-meta {
	display: flex;
	justify-content: space-between;
	align-items: center;
	font-size: 12px;
	color: var(--color-text-maxcontrast);
}

.note-category {
	padding: 2px 8px;
	background-color: var(--color-background-dark);
	border-radius: 12px;
}

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
	z-index: 1000;
}

.modal-content {
	background-color: var(--color-main-background);
	border-radius: var(--border-radius-large);
	box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
	max-width: 600px;
	width: 90%;
	max-height: 90vh;
	overflow-y: auto;
}

.modal-content--large {
	/* max-width: 800px; */
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
}

.form-control:focus {
	outline: none;
	border-color: var(--color-primary-element-element-element);
}
</style>

