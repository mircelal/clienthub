<template>
	<div v-if="isOpen" class="modal-overlay" @click.self="closeModal">
		<div class="modal-content">
			<div class="modal-header">
				<h3 class="modal-title">{{ translate('domaincontrol', 'Add Hosting Payment') }}</h3>
				<button class="modal-close" @click="closeModal">&times;</button>
			</div>
			<div class="modal-body">
				<div v-if="hosting" class="payment-info">
					<p><strong>{{ translate('domaincontrol', 'Hosting') }}:</strong> {{ hosting.provider }} {{ hosting.plan ? `- ${hosting.plan}` : '' }}</p>
					<p><strong>{{ translate('domaincontrol', 'Current Expiry') }}:</strong> {{ formatDate(hosting.expirationDate) || '-' }}</p>
					<p><strong>{{ translate('domaincontrol', 'New Expiry') }}:</strong> <span class="text-success">{{ formatNewExpiry() }}</span></p>
				</div>
				<form @submit.prevent="savePayment">
					<div class="form-row">
						<div class="form-group">
							<label for="hp-amount">{{ translate('domaincontrol', 'Payment Amount') }}</label>
							<input
								type="number"
								id="hp-amount"
								v-model="formData.amount"
								step="0.01"
								class="form-control"
								placeholder="9.99"
							/>
						</div>
						<div class="form-group">
							<label for="hp-currency">{{ translate('domaincontrol', 'Currency') }}</label>
							<select id="hp-currency" v-model="formData.currency" class="form-control">
								<option value="USD">$ USD</option>
								<option value="EUR">€ EUR</option>
								<option value="TRY">₺ TRY</option>
								<option value="AZN">₼ AZN</option>
								<option value="GBP">£ GBP</option>
								<option value="RUB">₽ RUB</option>
							</select>
						</div>
					</div>
					<div class="form-group">
						<label for="hp-period">{{ translate('domaincontrol', 'Payment Period') }}</label>
						<select id="hp-period" v-model="formData.period" class="form-control">
							<option value="1">1 {{ translate('domaincontrol', 'Month') }}</option>
							<option value="3">3 {{ translate('domaincontrol', 'Months') }}</option>
							<option value="6">6 {{ translate('domaincontrol', 'Months') }}</option>
							<option value="12">1 {{ translate('domaincontrol', 'Year') }}</option>
							<option value="24">2 {{ translate('domaincontrol', 'Years') }}</option>
						</select>
					</div>
					<div class="form-group">
						<label for="hp-note">{{ translate('domaincontrol', 'Note') }}</label>
						<textarea
							id="hp-note"
							v-model="formData.note"
							class="form-control"
							rows="2"
							:placeholder="translate('domaincontrol', 'Payment note...')"
						></textarea>
					</div>
					<div class="form-actions">
						<button type="button" class="button-vue button-vue--secondary" @click="closeModal">
							{{ translate('domaincontrol', 'Cancel') }}
						</button>
						<button type="submit" class="button-vue button-vue--success" :disabled="saving">
							{{ saving ? translate('domaincontrol', 'Saving...') : translate('domaincontrol', 'Save Payment') }}
						</button>
					</div>
				</form>
			</div>
		</div>
	</div>
</template>

<script>
import api from '../services/api'

export default {
	name: 'HostingPaymentModal',
	props: {
		open: {
			type: Boolean,
			default: false,
		},
		hosting: {
			type: Object,
			default: null,
		},
	},
	emits: ['close', 'paid'],
	data() {
		return {
			isOpen: false,
			formData: {
				amount: null,
				currency: 'USD',
				period: '12',
				note: '',
			},
			saving: false,
		}
	},
	watch: {
		open(newVal) {
			this.isOpen = newVal
			if (newVal) {
				this.resetForm()
				if (this.hosting && this.hosting.currency) {
					this.formData.currency = this.hosting.currency
				}
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

			const translations = {
				'Add Hosting Payment': 'Hosting Ödeme Ekle',
				'Hosting': 'Hosting',
				'Current Expiry': 'Mevcut Bitiş',
				'New Expiry': 'Yeni Bitiş',
				'Payment Amount': 'Ödeme Tutarı',
				'Currency': 'Para Birimi',
				'Payment Period': 'Ödeme Periyodu',
				'Month': 'Ay',
				'Months': 'Ay',
				'Year': 'Yıl',
				'Years': 'Yıl',
				'Note': 'Not',
				'Payment note...': 'Ödeme notu...',
				'Cancel': 'İptal',
				'Saving...': 'Kaydediliyor...',
				'Save Payment': 'Ödemeyi Kaydet',
				'Error saving payment': 'Ödeme kaydedilirken hata oluştu',
			}

			return translations[text] || text
		},
		resetForm() {
			this.formData = {
				amount: null,
				currency: this.hosting?.currency || 'USD',
				period: '12',
				note: '',
			}
		},
		closeModal() {
			this.$emit('close')
			this.resetForm()
		},
		formatDate(dateString) {
			if (!dateString) return '-'
			try {
				const options = { year: 'numeric', month: 'long', day: 'numeric' }
				return new Date(dateString).toLocaleDateString(undefined, options)
			} catch (e) {
				return dateString.split(' ')[0]
			}
		},
		formatNewExpiry() {
			if (!this.hosting || !this.hosting.expirationDate) return '-'
			try {
				const currentDate = new Date(this.hosting.expirationDate)
				const months = parseInt(this.formData.period) || 12
				const newDate = new Date(currentDate)
				newDate.setMonth(newDate.getMonth() + months)
				const options = { year: 'numeric', month: 'long', day: 'numeric' }
				return newDate.toLocaleDateString(undefined, options)
			} catch (e) {
				return '-'
			}
		},
		async savePayment() {
			if (!this.hosting) return

			this.saving = true
			try {
				const data = {
					amount: this.formData.amount || null,
					currency: this.formData.currency,
					period: this.formData.period,
					note: this.formData.note || '',
				}

				await api.hostings.addPayment(this.hosting.id, data)

				this.closeModal()
				this.$emit('paid')
			} catch (error) {
				console.error('Error saving payment:', error)
				alert(this.translate('domaincontrol', 'Error saving payment'))
			} finally {
				this.saving = false
			}
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
	max-height: 85vh;
	display: flex;
	flex-direction: column;
	overflow: hidden;
}

.modal-header {
	display: flex;
	justify-content: space-between;
	align-items: center;
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
	font-size: 28px;
	color: var(--color-text-maxcontrast);
	cursor: pointer;
	padding: 0;
	width: 32px;
	height: 32px;
	display: flex;
	align-items: center;
	justify-content: center;
	border-radius: var(--border-radius-small);
	transition: background-color 0.2s;
}

.modal-close:hover {
	background-color: var(--color-background-hover);
}

.modal-body {
	padding: 20px;
	overflow-y: auto;
	flex: 1;
	min-height: 0;
}

.payment-info {
	background-color: var(--color-background-hover);
	border-radius: var(--border-radius-small);
	padding: 16px;
	margin-bottom: 20px;
	border: 1px solid var(--color-border);
}

.payment-info p {
	margin: 8px 0;
	color: var(--color-main-text);
}

.text-success {
	color: var(--color-element-success);
	font-weight: 600;
}

.form-row {
	display: grid;
	grid-template-columns: repeat(2, 1fr);
	gap: 16px;
	margin-bottom: 16px;
}

.form-group {
	margin-bottom: 16px;
}

.form-group label {
	display: block;
	margin-bottom: 6px;
	font-weight: 500;
	color: var(--color-main-text);
}

.form-control {
	width: 100%;
	padding: 8px 12px;
	border: 1px solid var(--color-border);
	border-radius: var(--border-radius-element);
	background-color: var(--color-background-dark);
	color: var(--color-main-text);
	font-size: 14px;
	box-sizing: border-box;
}

.form-control:focus {
	outline: none;
	border-color: var(--color-primary-element-element);
}

.form-control::placeholder {
	color: var(--color-text-maxcontrast);
}

.form-actions {
	display: flex;
	justify-content: flex-end;
	gap: 12px;
	margin-top: 24px;
	padding-top: 20px;
	border-top: 1px solid var(--color-border);
	position: sticky;
	bottom: 0;
	background-color: var(--color-main-background);
	z-index: 10;
}

.button-vue {
	display: inline-flex;
	align-items: center;
	justify-content: center;
	padding: 8px 16px;
	border-radius: var(--border-radius-small);
	font-size: 14px;
	cursor: pointer;
	transition: all 0.2s ease;
	text-decoration: none;
	white-space: nowrap;
	box-sizing: border-box;
	border: 1px solid transparent;
}

.button-vue:disabled {
	opacity: 0.6;
	cursor: not-allowed;
}

.button-vue--primary {
	background-color: var(--color-primary-element-element);
	color: var(--color-primary-element-element-text);
}

.button-vue--primary:hover:not(:disabled) {
	background-color: var(--color-primary-element-element-hover);
}

.button-vue--secondary {
	background-color: transparent;
	color: var(--color-main-text);
	border-color: var(--color-border);
}

.button-vue--secondary:hover:not(:disabled) {
	background-color: var(--color-background-hover);
}

.button-vue--success {
	background-color: var(--color-element-success);
	color: var(--color-element-success-text);
}

.button-vue--success:hover:not(:disabled) {
	background-color: var(--color-element-success-hover);
}
</style>

