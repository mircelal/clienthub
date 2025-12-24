<template>
	<div v-if="isOpen" class="modal-overlay" @click.self="closeModal">
		<div class="modal-content modal-large">
			<div class="modal-header">
				<h3 class="modal-title">
					{{ debt && debt.id ? translate('domaincontrol', 'Edit Debt/Credit') : translate('domaincontrol', 'Add Debt/Credit') }}
				</h3>
				<button class="modal-close" @click="closeModal">
					<MaterialIcon name="close" :size="24" />
				</button>
			</div>

			<form @submit.prevent="saveDebt" class="modal-body">
				<div class="form-row">
					<div class="form-group">
						<label for="debt-type" class="form-label">
							{{ translate('domaincontrol', 'Type') }} *
						</label>
						<select
							id="debt-type"
							v-model="formData.type"
							class="form-control"
							required
						>
							<option value="">{{ translate('domaincontrol', 'Select') }}</option>
							<option value="debt">{{ translate('domaincontrol', 'Debt (I owe)') }}</option>
							<option value="credit">{{ translate('domaincontrol', 'Credit (I am owed)') }}</option>
						</select>
					</div>
					<div class="form-group">
						<label for="debt-debt-type" class="form-label">
							{{ translate('domaincontrol', 'Debt Type') }} *
						</label>
						<select
							id="debt-debt-type"
							v-model="formData.debtType"
							class="form-control"
							required
						>
							<option value="">{{ translate('domaincontrol', 'Select') }}</option>
							<option value="credit_card">{{ translate('domaincontrol', 'Credit Card') }}</option>
							<option value="loan">{{ translate('domaincontrol', 'Loan') }}</option>
							<option value="physical">{{ translate('domaincontrol', 'Physical Debt') }}</option>
							<option value="other">{{ translate('domaincontrol', 'Other') }}</option>
						</select>
					</div>
				</div>

				<div class="form-row">
					<div class="form-group">
						<label for="debt-creditor-debtor-name" class="form-label">
							{{ translate('domaincontrol', 'Creditor/Debtor Name') }} *
						</label>
						<input
							id="debt-creditor-debtor-name"
							v-model="formData.creditorDebtorName"
							type="text"
							class="form-control"
							:placeholder="translate('domaincontrol', 'Bank, person, institution name...')"
							required
						/>
					</div>
					<div class="form-group">
						<label for="debt-client-id" class="form-label">
							{{ translate('domaincontrol', 'Client (Optional)') }}
						</label>
						<select
							id="debt-client-id"
							v-model="formData.clientId"
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
				</div>

				<div class="form-row">
					<div class="form-group">
						<label for="debt-total-amount" class="form-label">
							{{ translate('domaincontrol', 'Total Amount') }} *
						</label>
						<input
							id="debt-total-amount"
							v-model="formData.totalAmount"
							type="number"
							step="0.01"
							class="form-control"
							placeholder="0.00"
							required
						/>
					</div>
					<div class="form-group">
						<label for="debt-currency" class="form-label">
							{{ translate('domaincontrol', 'Currency') }}
						</label>
						<select
							id="debt-currency"
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
				</div>

				<div class="form-row">
					<div class="form-group">
						<label for="debt-start-date" class="form-label">
							{{ translate('domaincontrol', 'Start Date') }}
						</label>
						<input
							id="debt-start-date"
							v-model="formData.startDate"
							type="date"
							class="form-control"
						/>
					</div>
					<div class="form-group">
						<label for="debt-due-date" class="form-label">
							{{ translate('domaincontrol', 'Due Date') }}
						</label>
						<input
							id="debt-due-date"
							v-model="formData.dueDate"
							type="date"
							class="form-control"
						/>
					</div>
				</div>

				<div class="form-row">
					<div class="form-group">
						<label for="debt-next-payment-date" class="form-label">
							{{ translate('domaincontrol', 'Next Payment Date') }}
						</label>
						<input
							id="debt-next-payment-date"
							v-model="formData.nextPaymentDate"
							type="date"
							class="form-control"
						/>
					</div>
					<div class="form-group">
						<label for="debt-payment-frequency" class="form-label">
							{{ translate('domaincontrol', 'Payment Frequency') }}
						</label>
						<select
							id="debt-payment-frequency"
							v-model="formData.paymentFrequency"
							class="form-control"
						>
							<option value="">{{ translate('domaincontrol', 'One-time') }}</option>
							<option value="daily">{{ translate('domaincontrol', 'Daily') }}</option>
							<option value="weekly">{{ translate('domaincontrol', 'Weekly') }}</option>
							<option value="monthly">{{ translate('domaincontrol', 'Monthly') }}</option>
						</select>
					</div>
				</div>

				<div class="form-row">
					<div class="form-group">
						<label for="debt-payment-amount" class="form-label">
							{{ translate('domaincontrol', 'Payment Amount (Installment)') }}
						</label>
						<input
							id="debt-payment-amount"
							v-model="formData.paymentAmount"
							type="number"
							step="0.01"
							class="form-control"
							placeholder="0.00"
						/>
					</div>
					<div class="form-group">
						<label for="debt-interest-rate" class="form-label">
							{{ translate('domaincontrol', 'Interest Rate (%)') }}
						</label>
						<input
							id="debt-interest-rate"
							v-model="formData.interestRate"
							type="number"
							step="0.01"
							class="form-control"
							placeholder="0.00"
						/>
					</div>
				</div>

				<div class="form-group">
					<label for="debt-description" class="form-label">
						{{ translate('domaincontrol', 'Description') }}
					</label>
					<textarea
						id="debt-description"
						v-model="formData.description"
						class="form-control"
						rows="3"
						:placeholder="translate('domaincontrol', 'Debt/credit description...')"
					></textarea>
				</div>

				<div class="form-group">
					<label for="debt-status" class="form-label">
						{{ translate('domaincontrol', 'Status') }}
					</label>
					<select
						id="debt-status"
						v-model="formData.status"
						class="form-control"
					>
						<option value="active">{{ translate('domaincontrol', 'Active') }}</option>
						<option value="paid">{{ translate('domaincontrol', 'Paid') }}</option>
						<option value="overdue">{{ translate('domaincontrol', 'Overdue') }}</option>
						<option value="cancelled">{{ translate('domaincontrol', 'Cancelled') }}</option>
					</select>
				</div>

				<div class="form-group">
					<label for="debt-notes" class="form-label">
						{{ translate('domaincontrol', 'Notes') }}
					</label>
					<RichTextEditor
						v-model="formData.notes"
						:placeholder="translate('domaincontrol', 'Additional notes...')"
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
	name: 'DebtModal',
	components: {
		MaterialIcon,
		RichTextEditor,
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
		clients: {
			type: Array,
			default: () => [],
		},
	},
	emits: ['close', 'saved'],
	data() {
		return {
			saving: false,
			formData: {
				type: '',
				debtType: '',
				creditorDebtorName: '',
				clientId: '',
				totalAmount: '',
				paidAmount: '',
				currency: 'USD',
				interestRate: '',
				startDate: '',
				dueDate: '',
				nextPaymentDate: '',
				paymentFrequency: '',
				paymentAmount: '',
				description: '',
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
				if (this.debt && this.debt.id) {
					this.loadDebtData()
				} else {
					// Set default date to today
					this.formData.startDate = new Date().toISOString().split('T')[0]
				}
			}
		},
	},
	methods: {
		resetForm() {
			this.formData = {
				type: '',
				debtType: '',
				creditorDebtorName: '',
				clientId: '',
				totalAmount: '',
				paidAmount: '',
				currency: 'USD',
				interestRate: '',
				startDate: '',
				dueDate: '',
				nextPaymentDate: '',
				paymentFrequency: '',
				paymentAmount: '',
				description: '',
				status: 'active',
				notes: '',
			}
		},
		loadDebtData() {
			if (!this.debt || !this.debt.id) return

			// Helper function to format date for input field (YYYY-MM-DD)
			const formatDateForInput = (dateString) => {
				if (!dateString) return ''
				// If it's already in YYYY-MM-DD format, return as is
				if (dateString.match(/^\d{4}-\d{2}-\d{2}$/)) {
					return dateString
				}
				// If it's a datetime string, extract date part
				if (dateString.includes(' ')) {
					return dateString.split(' ')[0]
				}
				// Try to parse and format
				try {
					const date = new Date(dateString)
					if (!isNaN(date.getTime())) {
						return date.toISOString().split('T')[0]
					}
				} catch (e) {
					console.warn('Date parsing error:', e)
				}
				return ''
			}

			this.formData = {
				type: this.debt.type || '',
				debtType: this.debt.debtType || '',
				creditorDebtorName: this.debt.creditorDebtorName || '',
				clientId: this.debt.clientId || '',
				totalAmount: this.debt.totalAmount || '',
				paidAmount: this.debt.paidAmount || '',
				currency: this.debt.currency || 'USD',
				interestRate: this.debt.interestRate || '',
				startDate: formatDateForInput(this.debt.startDate),
				dueDate: formatDateForInput(this.debt.dueDate),
				nextPaymentDate: formatDateForInput(this.debt.nextPaymentDate),
				paymentFrequency: this.debt.paymentFrequency || '',
				paymentAmount: this.debt.paymentAmount || '',
				description: this.debt.description || '',
				status: this.debt.status || 'active',
				notes: this.debt.notes || '',
			}
		},
		async saveDebt() {
			this.saving = true
			try {
				const data = {
					type: this.formData.type || '',
					debtType: this.formData.debtType || '',
					creditorDebtorName: this.formData.creditorDebtorName || '',
					clientId: this.formData.clientId || '',
					totalAmount: this.formData.totalAmount || '',
					paidAmount: this.formData.paidAmount || '',
					currency: this.formData.currency || 'USD',
					interestRate: this.formData.interestRate || '',
					startDate: this.formData.startDate || '',
					dueDate: this.formData.dueDate || '',
					nextPaymentDate: this.formData.nextPaymentDate || '',
					paymentFrequency: this.formData.paymentFrequency || '',
					paymentAmount: this.formData.paymentAmount || '',
					description: this.formData.description || '',
					status: this.formData.status || 'active',
					notes: this.formData.notes || '',
				}

				// Convert empty strings to null for optional fields
				if (!data.clientId) data.clientId = null
				if (!data.interestRate) data.interestRate = null
				if (!data.paymentAmount) data.paymentAmount = null
				if (!data.startDate) data.startDate = null
				if (!data.dueDate) data.dueDate = null
				if (!data.nextPaymentDate) data.nextPaymentDate = null
				if (!data.paymentFrequency) data.paymentFrequency = null
				if (!data.description) data.description = null

				if (this.debt && this.debt.id) {
					await api.debts.update(this.debt.id, data)
				} else {
					await api.debts.create(data)
				}

				this.$emit('saved')
				this.closeModal()
			} catch (error) {
				console.error('Error saving debt:', error)
				alert(this.translate('domaincontrol', 'Error saving debt/credit'))
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
				'Add Debt/Credit': 'Borç/Alacak Ekle',
				'Edit Debt/Credit': 'Borç/Alacak Düzenle',
				'Type': 'Tür',
				'Select': 'Seçin',
				'Debt (I owe)': 'Borç (Aldığım)',
				'Credit (I am owed)': 'Alacak (Verdiğim)',
				'Debt Type': 'Borç Türü',
				'Credit Card': 'Kredi Kartı',
				'Loan': 'Kredi',
				'Physical Debt': 'Fiziksel Borç',
				'Other': 'Diğer',
				'Creditor/Debtor Name': 'Alacaklı/Borçlu Adı',
				'Bank, person, institution name...': 'Banka, kişi, kurum adı...',
				'Client (Optional)': 'Müşteri (Opsiyonel)',
				'Select Client': 'Müşteri Seçin',
				'Total Amount': 'Toplam Tutar',
				'Currency': 'Para Birimi',
				'Start Date': 'Başlangıç Tarihi',
				'Due Date': 'Vade Tarihi',
				'Next Payment Date': 'Sonraki Ödeme Tarihi',
				'Payment Frequency': 'Ödeme Sıklığı',
				'One-time': 'Tek Seferlik',
				'Daily': 'Günlük',
				'Weekly': 'Haftalık',
				'Monthly': 'Aylık',
				'Payment Amount (Installment)': 'Ödeme Tutarı (Taksit)',
				'Interest Rate (%)': 'Faiz Oranı (%)',
				'Description': 'Açıklama',
				'Debt/credit description...': 'Borç/alacak açıklaması...',
				'Status': 'Durum',
				'Active': 'Aktif',
				'Paid': 'Ödendi',
				'Overdue': 'Gecikmiş',
				'Cancelled': 'İptal',
				'Notes': 'Notlar',
				'Additional notes...': 'Ek notlar...',
				'Cancel': 'İptal',
				'Saving...': 'Kaydediliyor...',
				'Save': 'Kaydet',
				'Error saving debt/credit': 'Borç/Alacak kaydedilirken hata oluştu',
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
	border-color: var(--color-primary-element-element-element);
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

