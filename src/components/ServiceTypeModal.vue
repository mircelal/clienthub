<template>
	<div v-if="isOpen" class="modal-overlay" @click.self="closeModal">
		<div class="modal-content modal-large">
			<div class="modal-header">
				<h3 class="modal-title">
					{{ serviceType ? translate('domaincontrol', 'Edit Service Type') : translate('domaincontrol', 'Add Service Type') }}
				</h3>
				<button class="modal-close" @click="closeModal">
					<MaterialIcon name="close" :size="24" />
				</button>
			</div>

			<form @submit.prevent="saveServiceType" class="modal-body">
				<div class="form-group">
					<label for="service-type-name" class="form-label">
						{{ translate('domaincontrol', 'Service Name') }} *
					</label>
					<input
						id="service-type-name"
						v-model="formData.name"
						type="text"
						class="form-control"
						required
						:placeholder="translate('domaincontrol', 'e.g., Maintenance, SEO')"
					/>
				</div>

				<div class="form-group">
					<label for="service-type-description" class="form-label">
						{{ translate('domaincontrol', 'Description') }}
					</label>
					<textarea
						id="service-type-description"
						v-model="formData.description"
						class="form-control"
						rows="2"
						:placeholder="translate('domaincontrol', 'Service description...')"
					></textarea>
				</div>

				<div class="form-row">
					<div class="form-group">
						<label for="service-type-price" class="form-label">
							{{ translate('domaincontrol', 'Default Price') }}
						</label>
						<input
							id="service-type-price"
							v-model="formData.defaultPrice"
							type="number"
							step="0.01"
							class="form-control"
						/>
					</div>
					<div class="form-group">
						<label for="service-type-currency" class="form-label">
							{{ translate('domaincontrol', 'Currency') }}
						</label>
						<select
							id="service-type-currency"
							v-model="formData.defaultCurrency"
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
						<label for="service-type-interval" class="form-label">
							{{ translate('domaincontrol', 'Renewal Interval') }}
						</label>
						<select
							id="service-type-interval"
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
	name: 'ServiceTypeModal',
	components: {
		MaterialIcon,
	},
	props: {
		open: {
			type: Boolean,
			default: false,
		},
		serviceType: {
			type: Object,
			default: null,
		},
	},
	emits: ['close', 'saved'],
	data() {
		return {
			saving: false,
			formData: {
				name: '',
				description: '',
				defaultPrice: '',
				defaultCurrency: 'USD',
				renewalInterval: 'monthly',
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
				if (this.serviceType) {
					this.loadServiceTypeData()
				}
			}
		},
	},
	methods: {
		resetForm() {
			this.formData = {
				name: '',
				description: '',
				defaultPrice: '',
				defaultCurrency: 'USD',
				renewalInterval: 'monthly',
			}
		},
		loadServiceTypeData() {
			if (!this.serviceType) return

			this.formData = {
				name: this.serviceType.name || '',
				description: this.serviceType.description || '',
				defaultPrice: this.serviceType.defaultPrice || '',
				defaultCurrency: this.serviceType.defaultCurrency || 'USD',
				renewalInterval: this.serviceType.renewalInterval || 'monthly',
			}
		},
		async saveServiceType() {
			this.saving = true
			try {
				const data = {
					name: this.formData.name || '',
					description: this.formData.description || '',
					defaultPrice: this.formData.defaultPrice || '',
					defaultCurrency: this.formData.defaultCurrency || 'USD',
					renewalInterval: this.formData.renewalInterval || 'monthly',
				}

				if (this.serviceType && this.serviceType.id) {
					await api.serviceTypes.update(this.serviceType.id, data)
				} else {
					await api.serviceTypes.create(data)
				}

				this.$emit('saved')
				this.closeModal()
			} catch (error) {
				console.error('Error saving service type:', error)
				alert(this.translate('domaincontrol', 'Error saving service type'))
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
				'Add Service Type': 'Hizmet Türü Ekle',
				'Edit Service Type': 'Hizmet Türü Düzenle',
				'Service Name': 'Hizmet Adı',
				'e.g., Maintenance, SEO': 'Örn: Bakım, SEO',
				'Description': 'Açıklama',
				'Service description...': 'Hizmet açıklaması...',
				'Default Price': 'Varsayılan Fiyat',
				'Currency': 'Para Birimi',
				'Renewal Interval': 'Yenileme Periyodu',
				'One-time': 'Tek Seferlik',
				'Monthly': 'Aylık',
				'Quarterly': '3 Aylık',
				'Yearly': 'Yıllık',
				'Cancel': 'İptal',
				'Saving...': 'Kaydediliyor...',
				'Save': 'Kaydet',
				'Error saving service type': 'Hizmet türü kaydedilirken hata oluştu',
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
	min-height: 60px;
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

