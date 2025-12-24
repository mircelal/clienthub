<template>
	<div v-if="isOpen" class="modal-overlay" @click.self="closeModal">
		<div class="modal-content modal-large">
			<div class="modal-header">
				<h3 class="modal-title">
					{{ editingInvoice ? translate('domaincontrol', 'Edit Invoice') : translate('domaincontrol', 'Create Invoice') }}
				</h3>
				<button class="modal-close" @click="closeModal">
					<MaterialIcon name="close" :size="24" />
				</button>
			</div>
			<div class="modal-body">
				<form @submit.prevent="saveInvoice">
					<input type="hidden" v-model="formData.id" />
					<div class="form-row">
						<div class="form-group">
							<label for="invoice-client-id">{{ translate('domaincontrol', 'Client') }} *</label>
							<select
								id="invoice-client-id"
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
						<div class="form-group">
							<label for="invoice-number">{{ translate('domaincontrol', 'Invoice Number') }}</label>
							<input
								type="text"
								id="invoice-number"
								v-model="formData.invoiceNumber"
								class="form-control"
								:placeholder="translate('domaincontrol', 'Auto-generated if empty')"
								autocomplete="off"
							/>
						</div>
					</div>
					<div class="form-row">
						<div class="form-group">
							<label for="invoice-issue-date">{{ translate('domaincontrol', 'Issue Date') }}</label>
							<input
								type="date"
								id="invoice-issue-date"
								v-model="formData.issueDate"
								class="form-control"
							/>
						</div>
						<div class="form-group">
							<label for="invoice-due-date">{{ translate('domaincontrol', 'Due Date') }}</label>
							<input
								type="date"
								id="invoice-due-date"
								v-model="formData.dueDate"
								class="form-control"
							/>
						</div>
					</div>
					<div class="form-row">
						<div class="form-group">
							<label for="invoice-total">{{ translate('domaincontrol', 'Total Amount') }}</label>
							<input
								type="number"
								id="invoice-total"
								v-model="formData.totalAmount"
								step="0.01"
								class="form-control"
								placeholder="0.00"
							/>
						</div>
						<div class="form-group">
							<label for="invoice-currency">{{ translate('domaincontrol', 'Currency') }}</label>
							<select id="invoice-currency" v-model="formData.currency" class="form-control">
								<option value="USD">$ USD</option>
								<option value="EUR">€ EUR</option>
								<option value="TRY">₺ TRY</option>
								<option value="AZN">₼ AZN</option>
								<option value="GBP">£ GBP</option>
								<option value="RUB">₽ RUB</option>
							</select>
						</div>
						<div class="form-group">
							<label for="invoice-status">{{ translate('domaincontrol', 'Status') }}</label>
							<select id="invoice-status" v-model="formData.status" class="form-control">
								<option value="draft">{{ translate('domaincontrol', 'Draft') }}</option>
								<option value="sent">{{ translate('domaincontrol', 'Sent') }}</option>
								<option value="paid">{{ translate('domaincontrol', 'Paid') }}</option>
								<option value="overdue">{{ translate('domaincontrol', 'Overdue') }}</option>
								<option value="cancelled">{{ translate('domaincontrol', 'Cancelled') }}</option>
							</select>
						</div>
					</div>
					<div class="form-group">
						<label for="invoice-notes">{{ translate('domaincontrol', 'Notes') }}</label>
						<textarea
							id="invoice-notes"
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
							{{ saving ? translate('domaincontrol', 'Saving...') : translate('domaincontrol', 'Save') }}
						</button>
					</div>
				</form>
			</div>
		</div>
	</div>
</template>

<script>
import api from '../services/api'
import MaterialIcon from './MaterialIcon.vue'
import RichTextEditor from './RichTextEditor.vue'

export default {
	name: 'InvoiceModal',
	components: {
		MaterialIcon,
		RichTextEditor,
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
	emits: ['close', 'saved'],
	data() {
		return {
			isOpen: false,
			saving: false,
			formData: {
				id: null,
				clientId: '',
				invoiceNumber: '',
				issueDate: '',
				dueDate: '',
				totalAmount: '',
				currency: 'USD',
				status: 'draft',
				notes: '',
			},
		}
	},
	watch: {
		open(newVal) {
			this.isOpen = newVal
			if (newVal) {
				this.resetForm()
				if (this.invoice) {
					this.loadInvoice()
				} else {
					// Set default dates
					const today = new Date()
					this.formData.issueDate = today.toISOString().split('T')[0]
					const dueDate = new Date(today)
					dueDate.setDate(dueDate.getDate() + 30)
					this.formData.dueDate = dueDate.toISOString().split('T')[0]
				}
			}
		},
	},
	methods: {
		resetForm() {
			this.formData = {
				id: null,
				clientId: '',
				invoiceNumber: '',
				issueDate: '',
				dueDate: '',
				totalAmount: '',
				currency: 'USD',
				status: 'draft',
				notes: '',
			}
		},
		loadInvoice() {
			if (!this.invoice) return
			this.formData = {
				id: this.invoice.id || null,
				clientId: this.invoice.clientId || '',
				invoiceNumber: this.invoice.invoiceNumber || '',
				issueDate: this.invoice.issueDate || '',
				dueDate: this.invoice.dueDate || '',
				totalAmount: this.invoice.totalAmount || '',
				currency: this.invoice.currency || 'USD',
				status: this.invoice.status || 'draft',
				notes: this.invoice.notes || '',
			}
		},
		async saveInvoice() {
			this.saving = true
			try {
				const data = {
					...this.formData,
					clientId: this.formData.clientId || '',
					invoiceNumber: this.formData.invoiceNumber || '',
					issueDate: this.formData.issueDate || '',
					dueDate: this.formData.dueDate || '',
					totalAmount: this.formData.totalAmount || '0',
					currency: this.formData.currency || 'USD',
					status: this.formData.status || 'draft',
					notes: this.formData.notes || '',
				}

				if (this.formData.id) {
					await api.invoices.update(this.formData.id, data)
				} else {
					await api.invoices.create(data)
				}

				this.$emit('saved')
				this.closeModal()
			} catch (error) {
				console.error('Error saving invoice:', error)
				alert(this.translate('domaincontrol', 'Error saving invoice'))
			} finally {
				this.saving = false
			}
		},
		closeModal() {
			this.isOpen = false
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
				'Edit Invoice': 'Fatura Düzenle',
				'Create Invoice': 'Fatura Oluştur',
				'Client': 'Müşteri',
				'Select Client': 'Müşteri Seçin',
				'Invoice Number': 'Fatura No',
				'Auto-generated if empty': 'Boş bırakılırsa otomatik oluşturulur',
				'Issue Date': 'Düzenleme Tarihi',
				'Due Date': 'Vade Tarihi',
				'Total Amount': 'Toplam Tutar',
				'Currency': 'Para Birimi',
				'Status': 'Durum',
				'Notes': 'Notlar',
				'Additional notes...': 'Ek notlar...',
				'Cancel': 'İptal',
				'Saving...': 'Kaydediliyor...',
				'Save': 'Kaydet',
				'Draft': 'Taslak',
				'Sent': 'Gönderildi',
				'Paid': 'Ödendi',
				'Overdue': 'Gecikmiş',
				'Cancelled': 'İptal',
				'Error saving invoice': 'Fatura kaydedilirken hata oluştu',
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

.modal-content.modal-large {
	max-width: 700px;
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

.form-row:has(.form-group:nth-child(3)) {
	grid-template-columns: repeat(3, 1fr);
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
</style>

