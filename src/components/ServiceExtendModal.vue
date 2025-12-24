<template>
	<div v-if="isOpen" class="modal-overlay" @click.self="closeModal">
		<div class="modal-content">
			<div class="modal-header">
				<h3 class="modal-title">
					{{ translate('domaincontrol', 'Extend Service') }}
				</h3>
				<button class="modal-close" @click="closeModal">
					<MaterialIcon name="close" :size="24" />
				</button>
			</div>

			<form @submit.prevent="extendService" class="modal-body">
				<div class="form-group">
					<label class="form-label">
						{{ translate('domaincontrol', 'Current Expiration Date') }}
					</label>
					<div class="form-control-static">
						{{ formatDate(service?.expirationDate) || translate('domaincontrol', 'Not set') }}
					</div>
				</div>

				<div class="form-group">
					<label for="extend-months" class="form-label">
						{{ translate('domaincontrol', 'Extend by (months)') }} *
					</label>
					<input
						id="extend-months"
						v-model.number="formData.months"
						type="number"
						min="1"
						class="form-control"
						required
					/>
				</div>

				<div class="form-group">
					<label class="form-label">
						{{ translate('domaincontrol', 'New Expiration Date') }}
					</label>
					<div class="form-control-static new-expiry">
						{{ newExpiryDate || '-' }}
					</div>
				</div>

				<div class="form-actions">
					<button type="button" class="button-vue button-vue--secondary" @click="closeModal">
						{{ translate('domaincontrol', 'Cancel') }}
					</button>
					<button type="submit" class="button-vue button-vue--primary" :disabled="saving">
						<MaterialIcon v-if="saving" name="loading" :size="18" class="loading-icon" />
						{{ saving ? translate('domaincontrol', 'Extending...') : translate('domaincontrol', 'Extend') }}
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
	name: 'ServiceExtendModal',
	components: {
		MaterialIcon,
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
	},
	data() {
		return {
			saving: false,
			formData: {
				months: 1,
			},
		}
	},
	computed: {
		isOpen() {
			return this.open
		},
		newExpiryDate() {
			if (!this.service || !this.formData.months) return ''

			const baseDate = this.service.expirationDate || new Date().toISOString().split('T')[0]
			const date = new Date(baseDate)
			date.setMonth(date.getMonth() + this.formData.months)
			return this.formatDate(date.toISOString().split('T')[0])
		},
	},
	watch: {
		open(newVal) {
			if (newVal) {
				this.formData.months = 1
			}
		},
	},
	methods: {
		async extendService() {
			this.saving = true
			try {
				await api.services.extend(this.service.id, {
					months: this.formData.months,
				})
				this.$emit('extended')
				this.closeModal()
			} catch (error) {
				console.error('Error extending service:', error)
				alert(this.translate('domaincontrol', 'Error extending service'))
			} finally {
				this.saving = false
			}
		},
		closeModal() {
			this.$emit('close')
		},
		formatDate(date) {
			if (!date) return ''
			const d = new Date(date)
			return d.toLocaleDateString('tr-TR')
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
				'Extend Service': 'Hizmet Süresini Uzat',
				'Current Expiration Date': 'Mevcut Bitiş Tarihi',
				'Not set': 'Belirlenmemiş',
				'Extend by (months)': 'Uzat (ay)',
				'New Expiration Date': 'Yeni Bitiş Tarihi',
				'Cancel': 'İptal',
				'Extending...': 'Uzatılıyor...',
				'Extend': 'Uzat',
				'Error extending service': 'Hizmet uzatılırken hata oluştu',
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
	max-width: 500px;
	width: 100%;
	max-height: 90vh;
	overflow-y: auto;
	display: flex;
	flex-direction: column;
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
	border-color: var(--color-primary-element-element-element);
}

.form-control-static {
	padding: 8px 12px;
	border: 1px solid var(--color-border);
	border-radius: var(--border-radius-element);
	background-color: var(--color-background-dark);
	color: var(--color-main-text);
	font-size: 14px;
}

.new-expiry {
	font-weight: 600;
	color: var(--color-primary-element-element-element);
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

