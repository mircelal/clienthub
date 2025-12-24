<template>
	<div v-if="open" class="modal-overlay" @click.self="$emit('close')">
		<div class="modal-content">
			<div class="modal-header">
				<h3 class="modal-title">{{ translate('domaincontrol', 'Add Payment') }}</h3>
				<button class="modal-close" @click="$emit('close')">
					<Close :size="24" />
				</button>
			</div>
			<div class="modal-body">
				<form @submit.prevent="savePayment">
					<input type="hidden" v-model="formData.invoiceId" />
					<div class="form-group">
						<label for="payment-client-id">{{ translate('domaincontrol', 'Client') }} *</label>
						<select
							id="payment-client-id"
							v-model="formData.clientId"
							required
							class="form-control"
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
					<div class="form-row">
						<div class="form-group">
							<label for="payment-amount">{{ translate('domaincontrol', 'Amount') }} *</label>
							<input
								type="number"
								id="payment-amount"
								v-model="formData.amount"
								step="0.01"
								required
								class="form-control"
								placeholder="0.00"
							/>
						</div>
						<div class="form-group">
							<label for="payment-currency">{{ translate('domaincontrol', 'Currency') }}</label>
							<select id="payment-currency" v-model="formData.currency" class="form-control">
								<option value="USD">$ USD</option>
								<option value="EUR">€ EUR</option>
								<option value="TRY">₺ TRY</option>
								<option value="AZN">₼ AZN</option>
								<option value="GBP">£ GBP</option>
								<option value="RUB">₽ RUB</option>
							</select>
						</div>
					</div>
					<div class="form-row">
						<div class="form-group">
							<label for="payment-date">{{ translate('domaincontrol', 'Payment Date') }}</label>
							<input
								type="date"
								id="payment-date"
								v-model="formData.paymentDate"
								class="form-control"
							/>
						</div>
						<div class="form-group">
							<label for="payment-method">{{ translate('domaincontrol', 'Payment Method') }}</label>
							<select id="payment-method" v-model="formData.paymentMethod" class="form-control">
								<option value="cash">{{ translate('domaincontrol', 'Cash') }}</option>
								<option value="bank">{{ translate('domaincontrol', 'Bank Transfer') }}</option>
								<option value="card">{{ translate('domaincontrol', 'Card') }}</option>
								<option value="other">{{ translate('domaincontrol', 'Other') }}</option>
							</select>
						</div>
					</div>
					<div class="form-group">
						<label for="payment-reference">{{ translate('domaincontrol', 'Reference Number') }}</label>
						<input
							type="text"
							id="payment-reference"
							v-model="formData.reference"
							class="form-control"
							:placeholder="translate('domaincontrol', 'Receipt/Reference number')"
						/>
					</div>
					<div class="form-group">
						<label for="payment-notes">{{ translate('domaincontrol', 'Notes') }}</label>
						<textarea
							id="payment-notes"
							v-model="formData.notes"
							class="form-control"
							rows="2"
							:placeholder="translate('domaincontrol', 'Additional notes...')"
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
import Close from 'vue-material-design-icons/Close.vue'

export default {
	name: 'InvoicePaymentModal',
	components: {
		Close,
	},
	props: {
		open: {
			type: Boolean,
			default: false,
		},
		invoice: {
			type: Object,
			default: null,
		},
		clients: {
			type: Array,
			default: () => [],
		},
	},
	emits: ['close', 'paid'],
	data() {
		return {
			saving: false,
			formData: {
				invoiceId: null,
				clientId: '',
				amount: '',
				currency: 'USD',
				paymentDate: '',
				paymentMethod: 'cash',
				reference: '',
				notes: '',
			},
		}
	},
	watch: {
		open(newVal) {
			if (newVal) {
				this.resetForm()
				if (this.invoice) {
					this.loadInvoice()
				}
			}
		},
	},
	methods: {
		resetForm() {
			const today = new Date().toISOString().split('T')[0]
			this.formData = {
				invoiceId: null,
				clientId: '',
				amount: '',
				currency: 'USD',
				paymentDate: today,
				paymentMethod: 'cash',
				reference: '',
				notes: '',
			}
		},
		loadInvoice() {
			if (!this.invoice) return
			this.formData.invoiceId = this.invoice.id
			this.formData.clientId = this.invoice.clientId || ''
			this.formData.currency = this.invoice.currency || 'USD'
		},
		async savePayment() {
			this.saving = true
			try {
				const data = {
					...this.formData,
					invoiceId: this.formData.invoiceId || '',
					clientId: this.formData.clientId || '',
					amount: this.formData.amount || '0',
					currency: this.formData.currency || 'USD',
					paymentDate: this.formData.paymentDate || '',
					paymentMethod: this.formData.paymentMethod || 'cash',
					reference: this.formData.reference || '',
					notes: this.formData.notes || '',
				}

				await api.payments.create(data)
				this.$emit('paid')
				this.$emit('close')
			} catch (error) {
				console.error('Error saving payment:', error)
				alert(this.translate('domaincontrol', 'Error saving payment'))
			} finally {
				this.saving = false
			}
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
				'Add Payment': 'Ödeme Ekle',
				'Client': 'Müşteri',
				'Select Client': 'Müşteri Seçin',
				'Amount': 'Tutar',
				'Currency': 'Para Birimi',
				'Payment Date': 'Ödeme Tarihi',
				'Payment Method': 'Ödeme Yöntemi',
				'Reference Number': 'Referans No',
				'Notes': 'Notlar',
				'Cancel': 'İptal',
				'Saving...': 'Kaydediliyor...',
				'Save Payment': 'Ödemeyi Kaydet',
				'Cash': 'Nakit',
				'Bank Transfer': 'Banka Havalesi',
				'Card': 'Kart',
				'Other': 'Diğer',
				'Receipt/Reference number': 'Dekont/Fiş no',
				'Additional notes...': 'Ek notlar...',
				'Error saving payment': 'Ödeme kaydedilirken hata oluştu',
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

.button-vue--success {
	background-color: var(--color-element-success);
	color: var(--color-element-success-text);
}

.button-vue--success:hover:not(:disabled) {
	opacity: 0.9;
}

.button-vue--secondary {
	background-color: transparent;
	color: var(--color-main-text);
	border-color: var(--color-border);
}

.button-vue--secondary:hover:not(:disabled) {
	background-color: var(--color-background-hover);
}
</style>

