<template>
    <div class="documents-container">
        
        <!-- ========================================== -->
        <!-- FILES SECTION                              -->
        <!-- ========================================== -->
        <div class="nc-section">
            <div class="nc-section-header">
                <h3 class="nc-section-title">
                    <span class="nc-icon-wrapper">
                        <FolderOutline :size="20" />
                    </span>
                    {{ translate('domaincontrol', 'Files') }}
                </h3>
                
                <div class="header-actions">
                    <input
                        ref="fileInput"
                        type="file"
                        style="display: none"
                        @change="handleFileSelect"
                        multiple
                    />
                    <NcButton type="primary" @click="$refs.fileInput.click()" :disabled="uploading">
                        <template #icon>
                            <Refresh v-if="uploading" :size="18" class="spin-animation" />
                            <Upload v-else :size="18" />
                        </template>
                        {{ uploading ? translate('domaincontrol', 'Uploading...') : translate('domaincontrol', 'Upload Files') }}
                    </NcButton>
                </div>
            </div>

            <div class="nc-section-content">
                <!-- Filter Pills -->
                <div class="nc-filter-bar">
                    <button
                        v-for="cat in fileCategories"
                        :key="cat.value"
                        class="nc-pill"
                        :class="{ active: selectedFileCategory === cat.value }"
                        @click="selectedFileCategory = cat.value"
                    >
                        {{ cat.label }}
                    </button>
                </div>

                <!-- Loading / Empty / List -->
                <div v-if="filesLoading" class="nc-empty-state">
                    <Refresh :size="32" class="spin-animation nc-state-icon" />
                    <p>{{ translate('domaincontrol', 'Loading files...') }}</p>
                </div>

                <div v-else-if="filteredFiles.length === 0" class="nc-empty-state">
                    <FolderOpen :size="48" class="nc-state-icon" />
                    <p>{{ translate('domaincontrol', 'No files in this category') }}</p>
                </div>

                <div v-else class="nc-list-container">
                    <div
                        v-for="file in filteredFiles"
                        :key="file.id"
                        class="nc-list-item"
                    >
                        <div class="nc-list-item__icon">
                            <component :is="getFileIcon(file.mimeType)" :size="24" />
                        </div>
                        <div class="nc-list-item__content">
                            <div class="nc-list-item__title">
                                {{ file.fileName }}
                            </div>
                            <div class="nc-list-item__subtitle">
                                <span class="file-meta">{{ formatFileSize(file.fileSize) }}</span>
                                <span class="file-meta-separator">•</span>
                                <span class="file-meta">{{ formatDate(file.createdAt) }}</span>
                                <span v-if="file.category !== 'general'" class="file-category-badge">
                                    {{ getFileCategoryLabel(file.category) }}
                                </span>
                            </div>
                        </div>
                        <div class="nc-list-item__actions">
                            <button
                                class="nc-action-btn"
                                @click.stop="deleteFile(file.id)"
                                :title="translate('domaincontrol', 'Delete')"
                            >
                                <Delete :size="20" />
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- NOTES SECTION                              -->
        <!-- ========================================== -->
        <div class="nc-section">
            <div class="nc-section-header">
                <h3 class="nc-section-title">
                    <span class="nc-icon-wrapper">
                        <Notebook :size="20" />
                    </span>
                    {{ translate('domaincontrol', 'Notes') }}
                </h3>
                <NcButton type="primary" @click="showAddNoteModal">
                    <template #icon>
                        <Plus :size="18" />
                    </template>
                    {{ translate('domaincontrol', 'Add Note') }}
                </NcButton>
            </div>

            <div class="nc-section-content">
                <!-- Filter Pills -->
                <div class="nc-filter-bar">
                    <button
                        v-for="cat in noteCategories"
                        :key="cat.value"
                        class="nc-pill"
                        :class="{ active: selectedNoteCategory === cat.value }"
                        @click="selectedNoteCategory = cat.value"
                    >
                        {{ cat.label }}
                    </button>
                </div>

                <!-- Loading / Empty / Grid -->
                <div v-if="notesLoading" class="nc-empty-state">
                    <Refresh :size="32" class="spin-animation nc-state-icon" />
                    <p>{{ translate('domaincontrol', 'Loading notes...') }}</p>
                </div>

                <div v-else-if="filteredNotes.length === 0" class="nc-empty-state">
                    <NoteText :size="48" class="nc-state-icon" />
                    <p>{{ translate('domaincontrol', 'No notes yet') }}</p>
                </div>

                <div v-else class="nc-list-container">
                    <div
                        v-for="note in filteredNotes"
                        :key="note.id"
                        class="nc-list-item nc-list-item--clickable"
                        @click="editNote(note)"
                    >
                        <div class="nc-list-item__icon">
                            <Notebook :size="24" />
                        </div>
                        <div class="nc-list-item__content">
                            <div class="nc-list-item__title-row">
                                <div class="nc-list-item__title">{{ note.title }}</div>
                                <span class="note-category-badge">{{ getNoteCategoryLabel(note.category) }}</span>
                            </div>
                            <div class="nc-list-item__subtitle">
                                <span class="note-preview">{{ stripHtml(note.content).substring(0, 120) }}{{ stripHtml(note.content).length > 120 ? '...' : '' }}</span>
                            </div>
                            <div class="nc-list-item__meta">
                                <span class="note-date">{{ formatDate(note.updatedAt || note.createdAt) }}</span>
                            </div>
                        </div>
                        <div class="nc-list-item__actions" @click.stop>
                            <button
                                class="nc-action-btn"
                                @click.stop="editNote(note)"
                                :title="translate('domaincontrol', 'Edit')"
                            >
                                <Pencil :size="20" />
                            </button>
                            <button
                                class="nc-action-btn"
                                @click.stop="deleteNote(note.id)"
                                :title="translate('domaincontrol', 'Delete')"
                            >
                                <Delete :size="20" />
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- NOTE MODAL                                 -->
        <!-- ========================================== -->
        <div v-if="showNoteModal" class="nc-modal-backdrop" @click.self="closeNoteModal">
            <div class="nc-modal">
                <div class="nc-modal-header">
                    <h2>{{ editingNote ? translate('domaincontrol', 'Edit Note') : translate('domaincontrol', 'Add Note') }}</h2>
                    <button class="close-btn" @click="closeNoteModal">
                        <Close :size="24" />
                    </button>
                </div>
                
                <div class="nc-modal-body">
                    <div class="nc-form-group">
                        <label>{{ translate('domaincontrol', 'Category') }}</label>
                        <select v-model="noteForm.category" class="nc-select">
                            <option
                                v-for="cat in noteCategories.filter(c => c.value !== 'all')"
                                :key="cat.value"
                                :value="cat.value"
                            >
                                {{ cat.label }}
                            </option>
                        </select>
                    </div>
                    
                    <div class="nc-form-group">
                        <label>{{ translate('domaincontrol', 'Title') }}</label>
                        <input
                            v-model="noteForm.title"
                            type="text"
                            class="nc-input"
                            :placeholder="translate('domaincontrol', 'Note title')"
                            autofocus
                        />
                    </div>
                    
                    <div class="nc-form-group full-height">
                        <label>{{ translate('domaincontrol', 'Content') }}</label>
                        <textarea
                            v-model="noteForm.content"
                            class="nc-textarea"
                            :placeholder="translate('domaincontrol', 'Write your note here...')"
                        ></textarea>
                    </div>
                </div>
                
                <div class="nc-modal-footer">
                    <NcButton type="tertiary" @click="closeNoteModal">
                        {{ translate('domaincontrol', 'Cancel') }}
                    </NcButton>
                    <NcButton type="primary" @click="saveNote" :disabled="!noteForm.title">
                        {{ translate('domaincontrol', 'Save Note') }}
                    </NcButton>
                </div>
            </div>
        </div>

    </div>
</template>

<script>
import { NcButton } from '@nextcloud/vue'
import api from '../../services/api'
// vue-material-design-icons
import FolderOutline from 'vue-material-design-icons/FolderOutline.vue'
import Refresh from 'vue-material-design-icons/Refresh.vue'
import Upload from 'vue-material-design-icons/Upload.vue'
import FolderOpen from 'vue-material-design-icons/FolderOpen.vue'
import FileImage from 'vue-material-design-icons/FileImage.vue'
import FileVideo from 'vue-material-design-icons/FileVideo.vue'
import FileMusic from 'vue-material-design-icons/FileMusic.vue'
import FilePdfBox from 'vue-material-design-icons/FilePdfBox.vue'
import FileWordBox from 'vue-material-design-icons/FileWordBox.vue'
import FileExcelBox from 'vue-material-design-icons/FileExcelBox.vue'
import ZipBox from 'vue-material-design-icons/ZipBox.vue'
import FileCode from 'vue-material-design-icons/FileCode.vue'
import File from 'vue-material-design-icons/File.vue'
import Delete from 'vue-material-design-icons/Delete.vue'
import Notebook from 'vue-material-design-icons/Notebook.vue'
import Plus from 'vue-material-design-icons/Plus.vue'
import NoteText from 'vue-material-design-icons/NoteText.vue'
import Pencil from 'vue-material-design-icons/Pencil.vue'
import Close from 'vue-material-design-icons/Close.vue'

export default {
    name: 'ProjectDocuments',
    components: {
        NcButton,
        FolderOutline,
        Refresh,
        Upload,
        FolderOpen,
        FileImage,
        FileVideo,
        FileMusic,
        FilePdfBox,
        FileWordBox,
        FileExcelBox,
        ZipBox,
        FileCode,
        File,
        Delete,
        Notebook,
        Plus,
        NoteText,
        Pencil,
        Close,
    },
    props: {
        projectId: {
            type: Number,
            required: true,
        },
    },
    data() {
        return {
            files: [],
            filesLoading: false,
            selectedFileCategory: 'all',
            uploading: false,
            // Categories
            fileCategories: [
                { value: 'all', label: this.translate('domaincontrol', 'All') },
                { value: 'general', label: this.translate('domaincontrol', 'General') },
                { value: 'documentation', label: this.translate('domaincontrol', 'Docs') },
                { value: 'design', label: this.translate('domaincontrol', 'Design') },
                { value: 'code', label: this.translate('domaincontrol', 'Code') },
                { value: 'other', label: this.translate('domaincontrol', 'Other') },
            ],
            notes: [],
            notesLoading: false,
            selectedNoteCategory: 'all',
            showNoteModal: false,
            editingNote: null,
            noteForm: {
                category: 'general',
                title: '',
                content: '',
            },
            noteCategories: [
                { value: 'all', label: this.translate('domaincontrol', 'All') },
                { value: 'general', label: this.translate('domaincontrol', 'General') },
                { value: 'requirements', label: this.translate('domaincontrol', 'Requirements') },
                { value: 'challenges', label: this.translate('domaincontrol', 'Challenges') },
                { value: 'research', label: this.translate('domaincontrol', 'Research') },
            ],
        }
    },
    computed: {
        filteredFiles() {
            if (this.selectedFileCategory === 'all') return this.files
            return this.files.filter(f => f.category === this.selectedFileCategory)
        },
        filteredNotes() {
            if (!Array.isArray(this.notes)) return []
            if (this.selectedNoteCategory === 'all') return this.notes
            return this.notes.filter(n => n.category === this.selectedNoteCategory)
        },
    },
    mounted() {
        if (this.projectId) {
            this.loadFiles()
            this.loadNotes()
        }
    },
    watch: {
        projectId(newVal) {
            if (newVal) {
                this.loadFiles()
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
                        if (translated && translated !== text) return translated
                    }
                    if (typeof window.t === 'function') {
                        const translated = window.t(appId, text, vars || {})
                        if (translated && translated !== text) return translated
                    }
                }
            } catch (e) { console.warn('Translation error:', e) }
            return text
        },
        async loadFiles() {
            this.filesLoading = true
            try {
                const response = await api.projectFiles.getAll(this.projectId)
                this.files = response.data || []
            } catch (error) {
                console.error('Error loading files:', error)
            } finally {
                this.filesLoading = false
            }
        },
        async handleFileSelect(event) {
            const selectedFiles = Array.from(event.target.files)
            if (selectedFiles.length === 0) return
            
            this.uploading = true
            try {
                for (const file of selectedFiles) {
                    await api.projectFiles.upload(this.projectId, file, this.selectedFileCategory !== 'all' ? this.selectedFileCategory : 'general')
                }
                await this.loadFiles()
                event.target.value = ''
            } catch (error) {
                console.error('Error uploading file:', error)
            } finally {
                this.uploading = false
            }
        },
        async deleteFile(id) {
            if (!confirm(this.translate('domaincontrol', 'Are you sure?'))) return
            try {
                await api.projectFiles.delete(this.projectId, id)
                await this.loadFiles()
            } catch (error) {
                console.error('Error deleting file:', error)
            }
        },
        async loadNotes() {
            this.notesLoading = true
            try {
                const response = await api.projectNotes.getAll(this.projectId)
                this.notes = Array.isArray(response.data) ? response.data : []
            } catch (error) {
                this.notes = []
            } finally {
                this.notesLoading = false
            }
        },
        showAddNoteModal() {
            this.editingNote = null
            this.noteForm = {
                category: this.selectedNoteCategory !== 'all' ? this.selectedNoteCategory : 'general',
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
            } catch (error) {
                console.error('Error saving note:', error)
            }
        },
        async deleteNote(id) {
            if (!confirm(this.translate('domaincontrol', 'Delete this note?'))) return
            try {
                await api.projectNotes.delete(this.projectId, id)
                await this.loadNotes()
            } catch (error) {
                console.error('Error deleting note:', error)
            }
        },
        // vue-material-design-icons component mapping
        getFileIcon(mimeType) {
            if (!mimeType) return 'File'
            if (mimeType.startsWith('image/')) return 'FileImage'
            if (mimeType.startsWith('video/')) return 'FileVideo'
            if (mimeType.startsWith('audio/')) return 'FileMusic'
            if (mimeType.includes('pdf')) return 'FilePdfBox'
            if (mimeType.includes('word') || mimeType.includes('document')) return 'FileWordBox'
            if (mimeType.includes('spreadsheet') || mimeType.includes('excel')) return 'FileExcelBox'
            if (mimeType.includes('zip') || mimeType.includes('archive')) return 'ZipBox'
            if (mimeType.includes('text') || mimeType.includes('code')) return 'FileCode'
            return 'File'
        },
        getFileCategoryLabel(category) {
            const cat = this.fileCategories.find(c => c.value === category)
            return cat ? cat.label : category
        },
        getNoteCategoryLabel(category) {
            const cat = this.noteCategories.find(c => c.value === category)
            return cat ? cat.label : category
        },
        stripHtml(html) {
            if (!html) return ''
            let tmp = document.createElement("DIV")
            tmp.innerHTML = html
            return tmp.textContent || tmp.innerText || ""
        },
        formatFileSize(bytes) {
            if (!bytes) return '-'
            const sizes = ['B', 'KB', 'MB', 'GB']
            if (bytes === 0) return '0 B'
            const i = Math.floor(Math.log(bytes) / Math.log(1024))
            return Math.round(bytes / Math.pow(1024, i) * 100) / 100 + ' ' + sizes[i]
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
/* GLOBAL & LAYOUT */
.documents-container {
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
    padding-bottom: 8px;
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
    background-color: var(--color-background-hover);
    border-radius: 50%;
    color: var(--color-text-maxcontrast);
}

/* --- Filter Pills --- */
.nc-filter-bar {
    display: flex;
    gap: 8px;
    margin-bottom: 20px;
    flex-wrap: wrap;
}

.nc-pill {
    padding: 6px 16px;
    border: 1px solid var(--color-border);
    border-radius: var(--border-radius-pill);
    background-color: var(--color-main-background);
    color: var(--color-text-maxcontrast);
    font-size: 13px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s ease;
}

.nc-pill:hover {
    background-color: var(--color-background-hover);
    color: var(--color-main-text);
}

.nc-pill.active {
    background-color: var(--color-primary-element-element-element);
    color: var(--color-primary-element-element-element-text);
    border-color: var(--color-primary-element-element-element);
}

/* --- Empty States --- */
.nc-empty-state {
    padding: 40px;
    text-align: center;
    color: var(--color-text-maxcontrast);
    background-color: var(--color-background-hover);
    border-radius: var(--border-radius-large);
    display: flex;
    flex-direction: column;
    align-items: center;
}

.nc-state-icon {
    margin-bottom: 12px;
    opacity: 0.5;
}

.spin-animation {
    animation: spin 1s linear infinite;
}
@keyframes spin { 100% { transform: rotate(360deg); } }


/* --- NEXTCLOUD STANDARD LIST CONTAINER --- */
.nc-list-container {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

/* --- NEXTCLOUD STANDARD LIST ITEM --- */
.nc-list-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 16px;
    background-color: var(--color-main-background);
    border: 1px solid var(--color-border);
    border-radius: var(--border-radius);
    transition: all 0.15s ease;
    min-height: 60px;
}

.nc-list-item:hover {
    background-color: var(--color-background-hover);
    border-color: var(--color-primary-element-element-element);
}

.nc-list-item--clickable {
    cursor: pointer;
}

.nc-list-item__icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
    flex-shrink: 0;
    color: var(--color-text-maxcontrast);
    background-color: var(--color-background-dark);
    border-radius: var(--border-radius);
}

.nc-list-item__content {
    flex: 1;
    min-width: 0;
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.nc-list-item__title-row {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
}

.nc-list-item__title {
    font-size: 15px;
    font-weight: 500;
    color: var(--color-main-text);
    line-height: 1.4;
    word-break: break-word;
}

.nc-list-item__subtitle {
    font-size: 13px;
    color: var(--color-text-maxcontrast);
    line-height: 1.4;
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
}

.nc-list-item__meta {
    font-size: 12px;
    color: var(--color-text-maxcontrast);
    margin-top: 2px;
}

.nc-list-item__actions {
    display: flex;
    align-items: center;
    gap: 4px;
    opacity: 0;
    transition: opacity 0.15s ease;
    flex-shrink: 0;
}

.nc-list-item:hover .nc-list-item__actions {
    opacity: 1;
}

.nc-action-btn {
    background: transparent;
    border: none;
    cursor: pointer;
    padding: 8px;
    border-radius: var(--border-radius);
    color: var(--color-text-maxcontrast);
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.15s ease;
    width: 36px;
    height: 36px;
}

.nc-action-btn:hover {
    background-color: var(--color-background-dark);
    color: var(--color-main-text);
}

.nc-action-btn:active {
    background-color: var(--color-background-hover);
}

/* File specific styles */
.file-meta {
    font-size: 13px;
    color: var(--color-text-maxcontrast);
}

.file-meta-separator {
    color: var(--color-text-maxcontrast);
    opacity: 0.5;
    margin: 0 4px;
}

.file-category-badge {
    font-size: 11px;
    padding: 2px 8px;
    background-color: var(--color-background-dark);
    border-radius: 12px;
    color: var(--color-text-maxcontrast);
    font-weight: 500;
    white-space: nowrap;
}

/* Note specific styles */
.note-preview {
    color: var(--color-text-maxcontrast);
    line-height: 1.5;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.note-category-badge {
    font-size: 11px;
    padding: 2px 8px;
    background-color: var(--color-primary-element-element-element);
    color: var(--color-primary-element-element-element-text);
    border-radius: 12px;
    font-weight: 500;
    white-space: nowrap;
}

.note-date {
    color: var(--color-text-maxcontrast);
    font-size: 12px;
}

/* --- MODAL --- */
.nc-modal-backdrop {
    position: fixed;
    top: 0; left: 0; width: 100%; height: 100%;
    background: rgba(0,0,0,0.4);
    backdrop-filter: blur(4px);
    z-index: 10000;
    display: flex;
    align-items: center;
    justify-content: center;
}

.nc-modal {
    background: var(--color-main-background);
    border-radius: var(--border-radius-large);
    width: 90%;
    max-width: 600px;
    box-shadow: 0 20px 50px rgba(0,0,0,0.3);
    display: flex;
    flex-direction: column;
    max-height: 85vh;
}

.nc-modal-header {
    padding: 20px;
    border-bottom: 1px solid var(--color-border);
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.nc-modal-header h2 { margin: 0; font-size: 20px; font-weight: bold; }

.close-btn {
    background: none; border: none; cursor: pointer; color: var(--color-text-maxcontrast);
}
.close-btn:hover { color: var(--color-main-text); }

.nc-modal-body {
    padding: 20px;
    overflow-y: auto;
}

.nc-form-group { margin-bottom: 16px; }
.nc-form-group.full-height { flex: 1; display: flex; flex-direction: column; }
.nc-form-group label { display: block; margin-bottom: 6px; font-weight: 600; font-size: 13px; }

.nc-input, .nc-select, .nc-textarea {
    width: 100%;
    padding: 10px;
    border: 1px solid var(--color-border);
    border-radius: var(--border-radius);
    background: var(--color-main-background);
    color: var(--color-main-text);
    font-family: inherit;
    box-sizing: border-box;
}
.nc-input:focus, .nc-select:focus, .nc-textarea:focus {
    border-color: var(--color-primary-element-element);
    outline: none;
}
.nc-textarea { min-height: 150px; resize: vertical; }

.nc-modal-footer {
    padding: 20px;
    border-top: 1px solid var(--color-border);
    display: flex;
    justify-content: flex-end;
    gap: 12px;
}

/* Mobile */
@media (max-width: 768px) {
    .nc-list-item {
        padding: 10px 12px;
        min-height: 56px;
    }
    
    .nc-list-item__icon {
        width: 36px;
        height: 36px;
    }
    
    .nc-list-item__title {
        font-size: 14px;
    }
    
    .nc-list-item__subtitle {
        font-size: 12px;
    }
    
    .nc-list-item__actions {
        opacity: 1;
    }
}
</style>