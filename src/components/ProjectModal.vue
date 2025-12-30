<template>
	<div v-if="isOpen" class="modal-overlay" @click.self="closeModal">
		<div class="modal-content modal-large">
			<div class="modal-header">
				<h3 class="modal-title">
					{{ project ? translate('domaincontrol', 'Edit Project') : translate('domaincontrol', 'Add Project') }}
				</h3>
				<button class="modal-close" @click="closeModal">
					<MaterialIcon name="close" :size="24" />
				</button>
			</div>

			<form @submit.prevent="saveProject" class="modal-body">
				<div class="form-row">
					<div class="form-group">
						<label for="project-client-id" class="form-label">
							{{ translate('domaincontrol', 'Client') }} *
						</label>
						<select
							id="project-client-id"
							v-model="formData.clientId"
							class="form-control"
							required
						>
							<option value="">{{ translate('domaincontrol', 'Select Client') }}</option>
							<option
								v-for="client in clients"
								:key="client.id"
								:value="client.id"
							>
								{{ client.name }}
							</option>
						</select>
					</div>
					<div class="form-group">
						<label for="project-type" class="form-label">
							{{ translate('domaincontrol', 'Project Type') }}
						</label>
						<select
							id="project-type"
							v-model="formData.projectType"
							class="form-control"
						>
							<option value="">{{ translate('domaincontrol', 'Select') }}</option>
							<option value="website">{{ translate('domaincontrol', 'Website') }}</option>
							<option value="ecommerce">{{ translate('domaincontrol', 'E-commerce') }}</option>
							<option value="webapp">{{ translate('domaincontrol', 'Web App') }}</option>
							<option value="theme">{{ translate('domaincontrol', 'Theme/Module') }}</option>
							<option value="design">{{ translate('domaincontrol', 'Graphic Design') }}</option>
							<option value="server">{{ translate('domaincontrol', 'Server Setup') }}</option>
							<option value="email">{{ translate('domaincontrol', 'Email Setup') }}</option>
							<option value="hosting">{{ translate('domaincontrol', 'Hosting') }}</option>
							<option value="device">{{ translate('domaincontrol', 'Device Setup') }}</option>
							<option value="support">{{ translate('domaincontrol', 'Technical Support') }}</option>
							<option value="seo">{{ translate('domaincontrol', 'SEO/Marketing') }}</option>
							<option value="other">{{ translate('domaincontrol', 'Other') }}</option>
						</select>
					</div>
				</div>

				<div class="form-row">
					<div class="form-group">
						<label for="project-name" class="form-label">
							{{ translate('domaincontrol', 'Project Name') }} *
						</label>
						<input
							id="project-name"
							v-model="formData.name"
							type="text"
							class="form-control"
							required
						/>
					</div>
					<div class="form-group">
						<label for="project-status" class="form-label">
							{{ translate('domaincontrol', 'Status') }}
						</label>
						<select
							id="project-status"
							v-model="formData.status"
							class="form-control"
						>
							<option value="active">{{ translate('domaincontrol', 'Active') }}</option>
							<option value="on_hold">{{ translate('domaincontrol', 'On Hold') }}</option>
							<option value="completed">{{ translate('domaincontrol', 'Completed') }}</option>
							<option value="cancelled">{{ translate('domaincontrol', 'Cancelled') }}</option>
						</select>
					</div>
				</div>

				<div class="form-group">
					<label for="project-description" class="form-label">
						{{ translate('domaincontrol', 'Description') }}
					</label>
					<textarea
						id="project-description"
						v-model="formData.description"
						class="form-control"
						rows="4"
						:placeholder="translate('domaincontrol', 'Project details, requirements, special notes...')"
					></textarea>
				</div>

				<div class="form-row">
					<div class="form-group">
						<label for="project-start-date" class="form-label">
							{{ translate('domaincontrol', 'Start Date') }}
						</label>
						<input
							id="project-start-date"
							v-model="formData.startDate"
							type="date"
							class="form-control"
						/>
					</div>
					<div class="form-group">
						<label for="project-deadline" class="form-label">
							{{ translate('domaincontrol', 'Deadline') }}
						</label>
						<input
							id="project-deadline"
							v-model="formData.deadline"
							type="date"
							class="form-control"
						/>
					</div>
				</div>

				<div class="form-row">
					<div class="form-group">
						<label for="project-budget" class="form-label">
							{{ translate('domaincontrol', 'Budget') }}
						</label>
						<input
							id="project-budget"
							v-model="formData.budget"
							type="number"
							step="0.01"
							class="form-control"
							placeholder="0.00"
						/>
					</div>
				</div>

				<div class="form-group">
					<label for="project-notes" class="form-label">
						{{ translate('domaincontrol', 'Notes') }}
					</label>
					<RichTextEditor
						v-model="formData.notes"
						:placeholder="translate('domaincontrol', 'Additional information, agreement details...')"
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
	name: 'ProjectModal',
	components: {
		MaterialIcon,
		RichTextEditor,
	},
	props: {
		open: {
			type: Boolean,
			default: false,
		},
		project: {
			type: Object,
			default: null,
		},
		clients: {
			type: Array,
			default: () => [],
		},
		presetClientId: {
			type: [Number, String],
			default: null,
		},
	},
	emits: ['close', 'saved'],
		data() {
		return {
			saving: false,
			defaultCurrency: 'USD',
			currencies: [],
			formData: {
				name: '',
				clientId: '',
				projectType: '',
				description: '',
				status: 'active',
				startDate: '',
				deadline: '',
				budget: '',
				currency: this.defaultCurrency || 'USD',
				notes: '',
			},
		}
	},
	mounted() {
		// loadSettings removed - not needed for this modal
	},
	computed: {
		isOpen() {
			return this.open
		},
	},
	watch: {
		open(newVal) {
			if (newVal) {
				this.resetForm()
				if (this.project) {
					this.loadProjectData()
				} else {
					// Set default start date to today
					this.formData.startDate = new Date().toISOString().split('T')[0]
					// Set preset client ID if provided
					if (this.presetClientId) {
						this.formData.clientId = this.presetClientId
					}
					// Set default currency
				}
			}
		},
	},
	methods: {
		resetForm() {
			this.formData = {
				name: '',
				clientId: '',
				projectType: '',
				description: '',
				status: 'active',
				startDate: '',
				deadline: '',
				budget: '',
				notes: '',
			}
		},
		loadProjectData() {
			if (!this.project) return

			this.formData = {
				name: this.project.name || '',
				clientId: this.project.clientId || '',
				projectType: this.project.projectType || '',
				description: this.project.description || '',
				status: this.project.status || 'active',
				startDate: this.project.startDate ? this.project.startDate.split(' ')[0] : '',
				deadline: this.project.deadline ? this.project.deadline.split(' ')[0] : '',
				budget: this.project.budget || '',
				notes: this.project.notes || '',
			}
		},
		async saveProject() {
			this.saving = true
			try {
				const data = {
					name: this.formData.name || '',
					clientId: this.formData.clientId || '',
					projectType: this.formData.projectType || '',
					description: this.formData.description || '',
					status: this.formData.status || 'active',
					startDate: this.formData.startDate || '',
					deadline: this.formData.deadline || '',
					budget: this.formData.budget || '',
					currency: this.defaultCurrency || 'USD',
					notes: this.formData.notes || '',
				}

				if (this.project && this.project.id) {
					await api.projects.update(this.project.id, data)
				} else {
					await api.projects.create(data)
				}

				this.$emit('saved')
				this.closeModal()
			} catch (error) {
				console.error('Error saving project:', error)
				alert(this.translate('domaincontrol', 'Error saving project'))
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
				'Add Project': 'Proje Ekle',
				'Edit Project': 'Proje Düzenle',
				'Client': 'Müşteri',
				'Select Client': 'Müşteri Seçin',
				'Project Type': 'Proje Türü',
				'Select': 'Seçin',
				'Project Name': 'Proje Adı',
				'Status': 'Durum',
				'Active': 'Aktif',
				'On Hold': 'Beklemede',
				'Completed': 'Tamamlandı',
				'Cancelled': 'İptal',
				'Description': 'Açıklama',
				'Project details, requirements, special notes...': 'Proje detayları, gereksinimler, özel notlar...',
				'Start Date': 'Başlangıç Tarihi',
				'Deadline': 'Bitiş Tarihi',
				'Budget': 'Bütçe',
				'Currency': 'Para Birimi',
				'Notes': 'Notlar',
				'Additional information, agreement details...': 'Ek bilgiler, anlaşma detayları...',
				'Cancel': 'İptal',
				'Saving...': 'Kaydediliyor...',
				'Save': 'Kaydet',
				'Error saving project': 'Proje kaydedilirken hata oluştu',
				'Website': 'Web Sitesi',
				'E-commerce': 'E-Ticaret',
				'Web App': 'Web Uygulaması',
				'Theme/Module': 'Tema/Modül',
				'Graphic Design': 'Grafik Tasarım',
				'Server Setup': 'Sunucu Kurulumu',
				'Email Setup': 'Mail Kurulumu',
				'Hosting': 'Hosting',
				'Device Setup': 'Cihaz Kurulumu',
				'Technical Support': 'Teknik Destek',
				'SEO/Marketing': 'SEO/Pazarlama',
				'Other': 'Diğer',
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
	border-color: var(--color-primary-element-element);
}

.form-control::placeholder {
	color: var(--color-text-maxcontrast);
}

textarea.form-control {
	resize: vertical;
	min-height: 80px;
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

