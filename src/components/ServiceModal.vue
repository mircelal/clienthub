<template>
	<div v-if="isOpen" class="modal-overlay" @click.self="closeModal">
		<div class="modal-content modal-large">
			<div class="modal-header">
				<h3 class="modal-title">
					{{ service ? translate('domaincontrol', 'Edit Service') : translate('domaincontrol', 'Add Service') }}
				</h3>
				<button class="modal-close" @click="closeModal">
					<MaterialIcon name="close" :size="24" />
				</button>
			</div>

			<form @submit.prevent="saveService" class="modal-body">
				<div class="form-row">
					<div class="form-group">
						<label for="service-client-id" class="form-label">
							{{ translate('domaincontrol', 'Client') }} *
						</label>
						<select
							id="service-client-id"
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
						<label for="service-type-id" class="form-label">
							{{ translate('domaincontrol', 'Service Type') }}
						</label>
						<select
							id="service-type-id"
							v-model="formData.serviceTypeId"
							class="form-control"
						>
							<option value="">{{ translate('domaincontrol', 'Select or enter custom') }}</option>
							<option
								v-for="serviceType in serviceTypes"
								:key="serviceType.id"
								:value="serviceType.id"
							>
								{{ serviceType.name }}
							</option>
						</select>
					</div>
				</div>

				<div class="form-group">
					<label for="service-name" class="form-label">
						{{ translate('domaincontrol', 'Service Name') }} *
					</label>
					<input
						id="service-name"
						v-model="formData.name"
						type="text"
						class="form-control"
						required
					/>
				</div>

				<div class="form-row">
					<div class="form-group">
						<label for="service-price" class="form-label">
							{{ translate('domaincontrol', 'Price') }}
						</label>
						<input
							id="service-price"
							v-model="formData.price"
							type="number"
							step="0.01"
							class="form-control"
						/>
					</div>
					<div class="form-group">
						<label for="service-currency" class="form-label">
							{{ translate('domaincontrol', 'Currency') }}
						</label>
						<select
							id="service-currency"
							v-model="formData.currency"
							class="form-control"
						>
							<option value="USD">$ USD</option>
							<option value="EUR">€ EUR</option>
							<option value="TRY">₺ TRY</option>
							<option value="AZN">₼ AZN</option>
							<option value="GBP">£ GBP</option>
							<option value="RUB">₽ RUB</option>
						</select>
					</div>
					<div class="form-group">
						<label for="service-interval" class="form-label">
							{{ translate('domaincontrol', 'Renewal Interval') }}
						</label>
						<select
							id="service-interval"
							v-model="formData.renewalInterval"
							class="form-control"
						>
							<option value="one-time">{{ translate('domaincontrol', 'One-time') }}</option>
							<option value="monthly">{{ translate('domaincontrol', 'Monthly') }}</option>
							<option value="quarterly">{{ translate('domaincontrol', 'Quarterly') }}</option>
							<option value="yearly">{{ translate('domaincontrol', 'Yearly') }}</option>
						</select>
					</div>
				</div>

				<div class="form-row">
					<div class="form-group">
						<label for="service-start-date" class="form-label">
							{{ translate('domaincontrol', 'Start Date') }}
						</label>
						<input
							id="service-start-date"
							v-model="formData.startDate"
							type="date"
							class="form-control"
						/>
					</div>
					<div class="form-group">
						<label for="service-expiration-date" class="form-label">
							{{ translate('domaincontrol', 'Expiration Date') }}
						</label>
						<input
							id="service-expiration-date"
							v-model="formData.expirationDate"
							type="date"
							class="form-control"
						/>
					</div>
					<div class="form-group">
						<label for="service-status" class="form-label">
							{{ translate('domaincontrol', 'Status') }}
						</label>
						<select
							id="service-status"
							v-model="formData.status"
							class="form-control"
						>
							<option value="active">{{ translate('domaincontrol', 'Active') }}</option>
							<option value="paused">{{ translate('domaincontrol', 'Paused') }}</option>
							<option value="cancelled">{{ translate('domaincontrol', 'Cancelled') }}</option>
						</select>
					</div>
				</div>

				<div class="form-group">
					<label for="service-notes" class="form-label">
						{{ translate('domaincontrol', 'Notes') }}
					</label>
					<RichTextEditor
						v-model="formData.notes"
						:placeholder="translate('domaincontrol', 'Other notes...')"
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
	name: 'ServiceModal',
	components: {
		MaterialIcon,
		RichTextEditor,
	},
	props: {
		open: {
			type: Boolean,
			default: false,
		},
		service: {
			type: Object,
			default: null,
		},
		clients: {
			type: Array,
			default: () => [],
		},
		serviceTypes: {
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
				serviceTypeId: '',
				price: '',
				currency: 'USD',
				renewalInterval: 'monthly',
				startDate: '',
				expirationDate: '',
				status: 'active',
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
				if (this.service) {
					this.loadServiceData()
				}
			}
		},
	},
	methods: {
		resetForm() {
			this.formData = {
				name: '',
				clientId: '',
				serviceTypeId: '',
				price: '',
				currency: 'USD',
				renewalInterval: 'monthly',
				startDate: '',
				expirationDate: '',
				status: 'active',
				notes: '',
			}
		},
		loadServiceData() {
			if (!this.service) return

			this.formData = {
				name: this.service.name || '',
				clientId: this.service.clientId || '',
				serviceTypeId: this.service.serviceTypeId || '',
				price: this.service.price || '',
				currency: this.service.currency || 'USD',
				renewalInterval: this.service.renewalInterval || 'monthly',
				startDate: this.service.startDate || '',
				expirationDate: this.service.expirationDate || '',
				status: this.service.status || 'active',
				notes: this.service.notes || '',
			}
		},
		async saveService() {
			this.saving = true
			try {
				const data = {
					name: this.formData.name || '',
					clientId: this.formData.clientId || '',
					serviceTypeId: this.formData.serviceTypeId || '',
					price: this.formData.price || '',
					currency: this.formData.currency || 'USD',
					renewalInterval: this.formData.renewalInterval || 'monthly',
					startDate: this.formData.startDate || '',
					expirationDate: this.formData.expirationDate || '',
					status: this.formData.status || 'active',
					notes: this.formData.notes || '',
				}

				if (this.service && this.service.id) {
					await api.services.update(this.service.id, data)
				} else {
					await api.services.create(data)
				}

				this.$emit('saved')
				this.closeModal()
			} catch (error) {
				console.error('Error saving service:', error)
				alert(this.translate('domaincontrol', 'Error saving service'))
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
				'Add Service': 'Hizmet Ekle',
				'Edit Service': 'Hizmet Düzenle',
				'Client': 'Müşteri',
				'Select Client': 'Müşteri Seçin',
				'Service Type': 'Hizmet Türü',
				'Select or enter custom': 'Seçin veya özel girin',
				'Service Name': 'Hizmet Adı',
				'Price': 'Fiyat',
				'Currency': 'Para Birimi',
				'Renewal Interval': 'Yenileme Periyodu',
				'One-time': 'Tek Seferlik',
				'Monthly': 'Aylık',
				'Quarterly': '3 Aylık',
				'Yearly': 'Yıllık',
				'Start Date': 'Başlangıç Tarihi',
				'Expiration Date': 'Bitiş Tarihi',
				'Status': 'Durum',
				'Active': 'Aktif',
				'Paused': 'Durduruldu',
				'Cancelled': 'İptal',
				'Notes': 'Notlar',
				'Other notes...': 'Diğer notlar...',
				'Cancel': 'İptal',
				'Saving...': 'Kaydediliyor...',
				'Save': 'Kaydet',
				'Error saving service': 'Hizmet kaydedilirken hata oluştu',
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

.form-row:has(.form-group:nth-child(3)) {
	grid-template-columns: 1fr 1fr 1fr;
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

