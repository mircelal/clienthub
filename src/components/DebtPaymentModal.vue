<template>
	<div v-if="isOpen" class="modal-overlay" @click.self="closeModal">
		<div class="modal-content">
			<div class="modal-header">
				<h3 class="modal-title">
					{{ translate('domaincontrol', 'Add Payment') }}
				</h3>
				<button class="modal-close" @click="closeModal">
					<MaterialIcon name="close" :size="24" />
				</button>
			</div>

			<form @submit.prevent="savePayment" class="modal-body">
				<div v-if="debt" class="debt-info">
					<div class="debt-info-item">
						<strong>{{ translate('domaincontrol', 'Total Amount') }}:</strong>
						<span>{{ formatCurrency(debt.totalAmount, debt.currency) }}</span>
					</div>
					<div class="debt-info-item">
						<strong>{{ translate('domaincontrol', 'Paid') }}:</strong>
						<span>{{ formatCurrency(debt.paidAmount, debt.currency) }}</span>
					</div>
					<div class="debt-info-item">
						<strong>{{ translate('domaincontrol', 'Remaining') }}:</strong>
						<span>{{ formatCurrency(getRemaining(debt), debt.currency) }}</span>
					</div>
				</div>

				<div class="form-row">
					<div class="form-group">
						<label for="payment-amount" class="form-label">
							{{ translate('domaincontrol', 'Payment Amount') }} *
						</label>
						<input
							id="payment-amount"
							v-model="formData.amount"
							type="number"
							step="0.01"
							class="form-control"
							placeholder="0.00"
							required
							:max="getRemaining(debt)"
						/>
						<small class="form-hint">
							{{ translate('domaincontrol', 'Maximum') }}: {{ formatCurrency(getRemaining(debt), debt?.currency) }}
						</small>
					</div>
					<div class="form-group">
						<label for="payment-date" class="form-label">
							{{ translate('domaincontrol', 'Payment Date') }} *
						</label>
						<input
							id="payment-date"
							v-model="formData.paymentDate"
							type="date"
							class="form-control"
							required
						/>
					</div>
				</div>

				<div class="form-group">
					<label for="payment-method" class="form-label">
						{{ translate('domaincontrol', 'Payment Method') }}
					</label>
					<select
						id="payment-method"
						v-model="formData.paymentMethod"
						class="form-control"
					>
						<option value="">{{ translate('domaincontrol', 'Select') }}</option>
						<option value="cash">{{ translate('domaincontrol', 'Cash') }}</option>
						<option value="bank">{{ translate('domaincontrol', 'Bank Transfer') }}</option>
						<option value="credit_card">{{ translate('domaincontrol', 'Credit Card') }}</option>
						<option value="debit_card">{{ translate('domaincontrol', 'Debit Card') }}</option>
						<option value="online">{{ translate('domaincontrol', 'Online Payment') }}</option>
						<option value="other">{{ translate('domaincontrol', 'Other') }}</option>
					</select>
				</div>

				<div class="form-group">
					<label for="payment-reference" class="form-label">
						{{ translate('domaincontrol', 'Reference') }}
					</label>
					<input
						id="payment-reference"
						v-model="formData.reference"
						type="text"
						class="form-control"
						:placeholder="translate('domaincontrol', 'Transaction number, receipt no...')"
					/>
				</div>

				<div class="form-group">
					<label for="payment-notes" class="form-label">
						{{ translate('domaincontrol', 'Notes') }}
					</label>
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
	name: 'DebtPaymentModal',
	components: {
		MaterialIcon,
	},
	props: {
		open: {
			type: Boolean,
			default: false,
		},
		debt: {
			type: Object,
			default: null,
		},
	},
	emits: ['close', 'paid'],
	data() {
		return {
			saving: false,
			formData: {
				amount: '',
				paymentDate: '',
				paymentMethod: '',
				reference: '',
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
				// Set default date to today
				this.formData.paymentDate = new Date().toISOString().split('T')[0]
			}
		},
	},
	methods: {
		resetForm() {
			this.formData = {
				amount: '',
				paymentDate: '',
				paymentMethod: '',
				reference: '',
				notes: '',
			}
		},
		getRemaining(debt) {
			if (!debt) return 0
			return (debt.totalAmount || 0) - (debt.paidAmount || 0)
		},
		async savePayment() {
			if (!this.debt || !this.debt.id) return

			this.saving = true
			try {
				const data = {
					amount: this.formData.amount || '',
					paymentDate: this.formData.paymentDate || '',
					paymentMethod: this.formData.paymentMethod || '',
					reference: this.formData.reference || '',
					notes: this.formData.notes || '',
				}

				await api.debts.addPayment(this.debt.id, data)

				this.$emit('paid')
				this.closeModal()
			} catch (error) {
				console.error('Error saving payment:', error)
				alert(this.translate('domaincontrol', 'Error saving payment'))
			} finally {
				this.saving = false
			}
		},
		closeModal() {
			this.$emit('close')
		},
		formatCurrency(amount, currency = 'USD') {
			if (amount === null || amount === undefined || amount === 0) return '-'
			const formatter = new Intl.NumberFormat('tr-TR', {
				style: 'currency',
				currency: currency || 'USD',
			})
			return formatter.format(amount)
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
				'Total Amount': 'Toplam Tutar',
				'Paid': 'Ödenen',
				'Remaining': 'Kalan',
				'Payment Amount': 'Ödeme Tutarı',
				'Payment Date': 'Ödeme Tarihi',
				'Payment Method': 'Ödeme Yöntemi',
				'Select': 'Seçin',
				'Cash': 'Nakit',
				'Bank Transfer': 'Banka Transferi',
				'Credit Card': 'Kredi Kartı',
				'Debit Card': 'Banka Kartı',
				'Online Payment': 'Online Ödeme',
				'Other': 'Diğer',
				'Reference': 'Referans',
				'Transaction number, receipt no...': 'İşlem numarası, makbuz no...',
				'Notes': 'Notlar',
				'Additional notes...': 'Ek notlar...',
				'Cancel': 'İptal',
				'Saving...': 'Kaydediliyor...',
				'Save': 'Kaydet',
				'Maximum': 'Maksimum',
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

.debt-info {
	background-color: var(--color-background-dark);
	border-radius: var(--border-radius-element);
	padding: 16px;
	border: 1px solid var(--color-border);
	margin-bottom: 8px;
}

.debt-info-item {
	display: flex;
	justify-content: space-between;
	align-items: center;
	margin-bottom: 8px;
	font-size: 14px;
}

.debt-info-item:last-child {
	margin-bottom: 0;
}

.debt-info-item strong {
	color: var(--color-main-text);
}

.debt-info-item span {
	color: var(--color-text-maxcontrast);
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

.form-hint {
	font-size: 12px;
	color: var(--color-text-maxcontrast);
	margin-top: 4px;
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

