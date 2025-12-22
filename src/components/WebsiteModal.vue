<template>
	<div v-if="isOpen" class="modal-overlay" @click.self="closeModal">
		<div class="modal-content modal-large">
			<div class="modal-header">
				<h3 class="modal-title">
					{{ website ? translate('domaincontrol', 'Edit Website') : translate('domaincontrol', 'Add Website') }}
				</h3>
				<button class="modal-close" @click="closeModal">
					<MaterialIcon name="close" :size="24" />
				</button>
			</div>

			<form @submit.prevent="saveWebsite" class="modal-body">
				<div class="form-row">
					<div class="form-group">
						<label for="website-name" class="form-label">
							{{ translate('domaincontrol', 'Website Name') }} *
						</label>
						<input
							id="website-name"
							v-model="formData.name"
							type="text"
							class="form-control"
							:placeholder="translate('domaincontrol', 'e.g., Customer Site')"
							required
						/>
					</div>
					<div class="form-group">
						<label for="website-client-id" class="form-label">
							{{ translate('domaincontrol', 'Client') }} *
						</label>
						<select
							id="website-client-id"
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
				</div>

				<div class="form-row">
					<div class="form-group">
						<label for="website-domain-id" class="form-label">
							{{ translate('domaincontrol', 'Domain') }}
						</label>
						<select
							id="website-domain-id"
							v-model="formData.domainId"
							class="form-control"
						>
							<option value="">{{ translate('domaincontrol', 'Select Domain (optional)') }}</option>
							<option
								v-for="domain in domains"
								:key="domain.id"
								:value="domain.id"
							>
								{{ domain.domainName }}
							</option>
						</select>
					</div>
					<div class="form-group">
						<label for="website-hosting-id" class="form-label">
							{{ translate('domaincontrol', 'Hosting') }}
						</label>
						<select
							id="website-hosting-id"
							v-model="formData.hostingId"
							class="form-control"
						>
							<option value="">{{ translate('domaincontrol', 'Select Hosting (optional)') }}</option>
							<option
								v-for="hosting in hostings"
								:key="hosting.id"
								:value="hosting.id"
							>
								{{ hosting.provider }} {{ hosting.plan || '' }}
							</option>
						</select>
					</div>
				</div>

				<div class="form-row">
					<div class="form-group">
						<label for="website-software" class="form-label">
							{{ translate('domaincontrol', 'Software') }}
						</label>
						<input
							id="website-software"
							v-model="formData.software"
							type="text"
							class="form-control"
							:placeholder="translate('domaincontrol', 'WordPress, Laravel, Custom...')"
						/>
					</div>
					<div class="form-group">
						<label for="website-version" class="form-label">
							{{ translate('domaincontrol', 'Version') }}
						</label>
						<input
							id="website-version"
							v-model="formData.version"
							type="text"
							class="form-control"
							:placeholder="translate('domaincontrol', '6.4.2')"
						/>
					</div>
				</div>

				<div class="form-row">
					<div class="form-group">
						<label for="website-status" class="form-label">
							{{ translate('domaincontrol', 'Status') }}
						</label>
						<select
							id="website-status"
							v-model="formData.status"
							class="form-control"
						>
							<option value="active">{{ translate('domaincontrol', 'Active') }}</option>
							<option value="maintenance">{{ translate('domaincontrol', 'Maintenance') }}</option>
							<option value="development">{{ translate('domaincontrol', 'Development') }}</option>
							<option value="inactive">{{ translate('domaincontrol', 'Inactive') }}</option>
						</select>
					</div>
					<div class="form-group">
						<label for="website-installation-date" class="form-label">
							{{ translate('domaincontrol', 'Installation Date') }}
						</label>
						<input
							id="website-installation-date"
							v-model="formData.installationDate"
							type="date"
							class="form-control"
						/>
					</div>
				</div>

				<div class="form-group">
					<label for="website-url" class="form-label">
						{{ translate('domaincontrol', 'Site URL') }}
					</label>
					<input
						id="website-url"
						v-model="formData.url"
						type="text"
						class="form-control"
						:placeholder="translate('domaincontrol', 'https://example.com')"
					/>
				</div>

				<div class="form-group">
					<label for="website-admin-url" class="form-label">
						{{ translate('domaincontrol', 'Admin Panel URL') }}
					</label>
					<input
						id="website-admin-url"
						v-model="formData.adminUrl"
						type="text"
						class="form-control"
						:placeholder="translate('domaincontrol', 'https://example.com/wp-admin')"
					/>
				</div>

				<div class="form-group">
					<label for="website-admin-notes" class="form-label">
						{{ translate('domaincontrol', 'Admin Login Information') }}
					</label>
					<textarea
						id="website-admin-notes"
						v-model="formData.adminNotes"
						class="form-control"
						rows="2"
						:placeholder="translate('domaincontrol', 'User: admin\nPassword: ****')"
					></textarea>
				</div>

				<div class="form-group">
					<label for="website-notes" class="form-label">
						{{ translate('domaincontrol', 'General Notes') }}
					</label>
					<textarea
						id="website-notes"
						v-model="formData.notes"
						class="form-control"
						rows="4"
						:placeholder="translate('domaincontrol', 'Other notes...')"
					></textarea>
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

export default {
	name: 'WebsiteModal',
	components: {
		MaterialIcon,
	},
	props: {
		open: {
			type: Boolean,
			default: false,
		},
		website: {
			type: Object,
			default: null,
		},
		clients: {
			type: Array,
			default: () => [],
		},
		domains: {
			type: Array,
			default: () => [],
		},
		hostings: {
			type: Array,
			default: () => [],
		},
	},
	data() {
		return {
			saving: false,
			formData: {
				name: '',
				clientId: '',
				domainId: '',
				hostingId: '',
				software: '',
				version: '',
				status: 'active',
				installationDate: '',
				url: '',
				adminUrl: '',
				adminNotes: '',
				notes: '',
			},
		}
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
				if (this.website) {
					this.loadWebsiteData()
				}
			}
		},
	},
	methods: {
		resetForm() {
			this.formData = {
				name: '',
				clientId: '',
				domainId: '',
				hostingId: '',
				software: '',
				version: '',
				status: 'active',
				installationDate: '',
				url: '',
				adminUrl: '',
				adminNotes: '',
				notes: '',
			}
		},
		loadWebsiteData() {
			if (!this.website) return

			this.formData = {
				name: this.website.name || '',
				clientId: this.website.clientId || '',
				domainId: this.website.domainId || '',
				hostingId: this.website.hostingId || '',
				software: this.website.software || '',
				version: this.website.version || '',
				status: this.website.status || 'active',
				installationDate: this.website.installationDate || '',
				url: this.website.url || '',
				adminUrl: this.website.adminUrl || '',
				adminNotes: this.website.adminNotes || '',
				notes: this.website.notes || '',
			}
		},
		async saveWebsite() {
			this.saving = true
			try {
				const data = {
					name: this.formData.name || '',
					clientId: this.formData.clientId || '',
					domainId: this.formData.domainId || '',
					hostingId: this.formData.hostingId || '',
					software: this.formData.software || '',
					version: this.formData.version || '',
					status: this.formData.status || 'active',
					installationDate: this.formData.installationDate || '',
					url: this.formData.url || '',
					adminUrl: this.formData.adminUrl || '',
					adminNotes: this.formData.adminNotes || '',
					notes: this.formData.notes || '',
				}

				if (this.website && this.website.id) {
					await api.websites.update(this.website.id, data)
				} else {
					await api.websites.create(data)
				}

				this.$emit('saved')
				this.closeModal()
			} catch (error) {
				console.error('Error saving website:', error)
				alert(this.translate('domaincontrol', 'Error saving website'))
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
				'Add Website': 'Website Ekle',
				'Edit Website': 'Website Düzenle',
				'Website Name': 'Website Adı',
				'e.g., Customer Site': 'Örn: Müşteri Sitesi',
				'Client': 'Müşteri',
				'Select Client': 'Müşteri Seçin',
				'Domain': 'Domain',
				'Select Domain (optional)': 'Domain Seçin (opsiyonel)',
				'Hosting': 'Hosting',
				'Select Hosting (optional)': 'Hosting Seçin (opsiyonel)',
				'Software': 'Yazılım',
				'WordPress, Laravel, Custom...': 'WordPress, Laravel, Custom...',
				'Version': 'Versiyon',
				'6.4.2': '6.4.2',
				'Status': 'Durum',
				'Active': 'Aktif',
				'Maintenance': 'Bakımda',
				'Development': 'Geliştirmede',
				'Inactive': 'Pasif',
				'Installation Date': 'Kurulum Tarihi',
				'Site URL': 'Site URL',
				'https://example.com': 'https://example.com',
				'Admin Panel URL': 'Admin Panel URL',
				'https://example.com/wp-admin': 'https://example.com/wp-admin',
				'Admin Login Information': 'Admin Giriş Bilgileri',
				'User: admin\nPassword: ****': 'Kullanıcı: admin\nŞifre: ****',
				'General Notes': 'Genel Notlar',
				'Other notes...': 'Diğer notlar...',
				'Cancel': 'İptal',
				'Saving...': 'Kaydediliyor...',
				'Save': 'Kaydet',
				'Error saving website': 'Website kaydedilirken hata oluştu',
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

