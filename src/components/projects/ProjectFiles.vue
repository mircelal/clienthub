<template>
	<div class="tab-content">
		<div class="detail-section">
			<div class="section-header">
				<h3 class="section-title">
					<MaterialIcon name="folder" :size="24" />
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
					<button class="button-vue button-vue--primary" @click="$refs.fileInput.click()">
						<MaterialIcon name="upload" :size="18" />
						{{ translate('domaincontrol', 'Upload Files') }}
					</button>
				</div>
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
					<p>{{ translate('domaincontrol', 'Loading files...') }}</p>
				</div>
				<div v-else-if="filteredFiles.length === 0" class="empty-content">
					<p class="empty-content__text">{{ translate('domaincontrol', 'No files yet') }}</p>
				</div>
				<div v-else class="files-list">
					<div
						v-for="file in filteredFiles"
						:key="file.id"
						class="file-item"
					>
						<div class="file-icon">
							<MaterialIcon :name="getFileIcon(file.mimeType)" :size="24" />
						</div>
						<div class="file-info">
							<div class="file-name">{{ file.fileName }}</div>
							<div class="file-meta">
								<span>{{ formatFileSize(file.fileSize) }}</span>
								<span v-if="file.category">{{ getCategoryLabel(file.category) }}</span>
								<span>{{ formatDate(file.createdAt) }}</span>
							</div>
							<div v-if="file.description" class="file-description">{{ file.description }}</div>
						</div>
						<div class="file-actions">
							<button
								class="action-button action-button--delete"
								@click="deleteFile(file.id)"
								:title="translate('domaincontrol', 'Delete')"
							>
								<MaterialIcon name="delete" :size="16" />
							</button>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</template>

<script>
import MaterialIcon from '../MaterialIcon.vue'
import api from '../../services/api'

export default {
	name: 'ProjectFiles',
	components: {
		MaterialIcon,
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
			loading: false,
			selectedCategory: 'all',
			uploading: false,
			categories: [
				{ value: 'all', label: this.translate('domaincontrol', 'All') },
				{ value: 'general', label: this.translate('domaincontrol', 'General') },
				{ value: 'documentation', label: this.translate('domaincontrol', 'Documentation') },
				{ value: 'design', label: this.translate('domaincontrol', 'Design') },
				{ value: 'code', label: this.translate('domaincontrol', 'Code') },
				{ value: 'other', label: this.translate('domaincontrol', 'Other') },
			],
		}
	},
	computed: {
		filteredFiles() {
			if (this.selectedCategory === 'all') {
				return this.files
			}
			return this.files.filter(f => f.category === this.selectedCategory)
		},
	},
	mounted() {
		if (this.projectId) {
			this.loadFiles()
		}
	},
	watch: {
		projectId(newVal) {
			if (newVal) {
				this.loadFiles()
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
		async loadFiles() {
			this.loading = true
			try {
				const response = await api.projectFiles.getAll(this.projectId)
				this.files = response.data || []
			} catch (error) {
				console.error('Error loading files:', error)
			} finally {
				this.loading = false
			}
		},
		async handleFileSelect(event) {
			const selectedFiles = Array.from(event.target.files)
			if (selectedFiles.length === 0) return
			
			this.uploading = true
			try {
				for (const file of selectedFiles) {
					await api.projectFiles.upload(this.projectId, file, this.selectedCategory !== 'all' ? this.selectedCategory : 'general')
				}
				await this.loadFiles()
				// Reset file input
				event.target.value = ''
			} catch (error) {
				console.error('Error uploading file:', error)
				alert(this.translate('domaincontrol', 'Error uploading file'))
			} finally {
				this.uploading = false
			}
		},
		async deleteFile(id) {
			if (!confirm(this.translate('domaincontrol', 'Are you sure you want to delete this file?'))) {
				return
			}
			
			try {
				await api.projectFiles.delete(this.projectId, id)
				await this.loadFiles()
			} catch (error) {
				console.error('Error deleting file:', error)
				alert(this.translate('domaincontrol', 'Error deleting file'))
			}
		},
		getFileIcon(mimeType) {
			if (!mimeType) return 'file'
			
			if (mimeType.startsWith('image/')) return 'image'
			if (mimeType.startsWith('video/')) return 'video'
			if (mimeType.startsWith('audio/')) return 'audio'
			if (mimeType.includes('pdf')) return 'picture-as-pdf'
			if (mimeType.includes('word') || mimeType.includes('document')) return 'description'
			if (mimeType.includes('spreadsheet') || mimeType.includes('excel')) return 'table-chart'
			if (mimeType.includes('zip') || mimeType.includes('archive')) return 'archive'
			if (mimeType.includes('text') || mimeType.includes('code')) return 'code'
			
			return 'file'
		},
		getCategoryLabel(category) {
			const cat = this.categories.find(c => c.value === category)
			return cat ? cat.label : category
		},
		formatFileSize(bytes) {
			if (!bytes) return '-'
			const sizes = ['Bytes', 'KB', 'MB', 'GB']
			if (bytes === 0) return '0 Bytes'
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

.header-actions {
	display: flex;
	gap: 8px;
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

.files-list {
	display: flex;
	flex-direction: column;
	gap: 12px;
}

.file-item {
	display: flex;
	align-items: center;
	gap: 12px;
	padding: 12px;
	background-color: var(--color-main-background);
	border: 1px solid var(--color-border);
	border-radius: var(--border-radius-element);
}

.file-icon {
	display: flex;
	align-items: center;
	justify-content: center;
	width: 40px;
	height: 40px;
	border-radius: var(--border-radius-element);
	background-color: var(--color-background-dark);
	color: var(--color-text-maxcontrast);
	flex-shrink: 0;
}

.file-info {
	flex: 1;
}

.file-name {
	font-size: 14px;
	font-weight: 500;
	color: var(--color-main-text);
	margin-bottom: 4px;
}

.file-meta {
	display: flex;
	gap: 8px;
	font-size: 12px;
	color: var(--color-text-maxcontrast);
}

.file-description {
	font-size: 12px;
	color: var(--color-text-maxcontrast);
	margin-top: 4px;
}

.file-actions {
	display: flex;
	gap: 4px;
}
</style>

