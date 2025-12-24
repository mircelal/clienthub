<template>
	<div v-if="isOpen" class="modal-overlay" @click.self="closeModal">
		<div class="modal-content modal-large">
			<div class="modal-header">
				<h3 class="modal-title">
					{{ transaction && transaction.id ? translate('domaincontrol', 'Edit Transaction') : translate('domaincontrol', 'Add Transaction') }}
				</h3>
				<button class="modal-close" @click="closeModal">
					<MaterialIcon name="close" :size="24" />
				</button>
			</div>

			<form @submit.prevent="saveTransaction" class="modal-body">
				<div class="form-row">
					<div class="form-group">
						<label for="transaction-type" class="form-label">
							{{ translate('domaincontrol', 'Type') }} *
						</label>
						<select
							id="transaction-type"
							v-model="formData.type"
							class="form-control"
							required
							@change="onTypeChange"
						>
							<option value="">{{ translate('domaincontrol', 'Select') }}</option>
							<option value="income">{{ translate('domaincontrol', 'Income') }}</option>
							<option value="expense">{{ translate('domaincontrol', 'Expense') }}</option>
						</select>
					</div>
					<div class="form-group">
						<label for="transaction-category-id" class="form-label">
							{{ translate('domaincontrol', 'Category') }}
						</label>
						<select
							id="transaction-category-id"
							v-model="formData.categoryId"
							class="form-control"
						>
							<option value="">{{ translate('domaincontrol', 'Select Category') }}</option>
							<option
								v-for="category in filteredCategories"
								:key="category.id"
								:value="category.id"
							>
								{{ category.name }}
							</option>
						</select>
					</div>
				</div>

				<div class="form-row">
					<div class="form-group">
						<label for="transaction-amount" class="form-label">
							{{ translate('domaincontrol', 'Amount') }} *
						</label>
						<input
							id="transaction-amount"
							v-model="formData.amount"
							type="number"
							step="0.01"
							class="form-control"
							placeholder="0.00"
							required
						/>
					</div>
				</div>

				<div class="form-row">
					<div class="form-group">
						<label for="transaction-date" class="form-label">
							{{ translate('domaincontrol', 'Transaction Date') }} *
						</label>
						<input
							id="transaction-date"
							v-model="formData.transactionDate"
							type="date"
							class="form-control"
							required
						/>
					</div>
					<div class="form-group">
						<label for="transaction-payment-method" class="form-label">
							{{ translate('domaincontrol', 'Payment Method') }}
						</label>
						<select
							id="transaction-payment-method"
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
				</div>

				<div class="form-group">
					<label for="transaction-description" class="form-label">
						{{ translate('domaincontrol', 'Description') }}
					</label>
					<textarea
						id="transaction-description"
						v-model="formData.description"
						class="form-control"
						rows="3"
						:placeholder="translate('domaincontrol', 'Transaction description...')"
					></textarea>
				</div>

				<div class="form-row">
					<div class="form-group">
						<label for="transaction-client-id" class="form-label">
							{{ translate('domaincontrol', 'Client (Optional)') }}
						</label>
						<select
							id="transaction-client-id"
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
					<div class="form-group">
						<label for="transaction-project-id" class="form-label">
							{{ translate('domaincontrol', 'Project (Optional)') }}
						</label>
						<select
							id="transaction-project-id"
							v-model="formData.projectId"
							class="form-control"
						>
							<option value="">{{ translate('domaincontrol', 'Select Project') }}</option>
							<option
								v-for="project in projects"
								:key="project.id"
								:value="project.id"
							>
								{{ project.name }}
							</option>
						</select>
					</div>
				</div>

				<div class="form-group">
					<label for="transaction-reference" class="form-label">
						{{ translate('domaincontrol', 'Reference / Invoice Number') }}
					</label>
					<input
						id="transaction-reference"
						v-model="formData.reference"
						type="text"
						class="form-control"
						:placeholder="translate('domaincontrol', 'Invoice number, transaction reference...')"
					/>
				</div>

				<div class="form-group">
					<label for="transaction-notes" class="form-label">
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
	name: 'TransactionModal',
	components: {
		MaterialIcon,
		RichTextEditor,
	},
	props: {
		open: {
			type: Boolean,
			default: false,
		},
		transaction: {
			type: Object,
			default: null,
		},
		clients: {
			type: Array,
			default: () => [],
		},
		projects: {
			type: Array,
			default: () => [],
		},
		categories: {
			type: Array,
			default: () => [],
		},
	},
	emits: ['close', 'saved'],
	data() {
		return {
			saving: false,
			defaultCurrency: 'USD',
			formData: {
				type: '',
				categoryId: '',
				amount: '',
				transactionDate: '',
				paymentMethod: '',
				description: '',
				clientId: '',
				projectId: '',
				reference: '',
				notes: '',
			},
		}
	},
	computed: {
		isOpen() {
			return this.open
		},
		filteredCategories() {
			if (!this.formData.type) return []
			return this.categories.filter(c => c.type === this.formData.type)
		},
	},
	mounted() {
		this.loadSettings()
	},
	watch: {
		open(newVal) {
			if (newVal) {
				this.resetForm()
				if (this.transaction && this.transaction.id) {
					this.loadTransactionData()
				} else {
					// Set default date to today
					this.formData.transactionDate = new Date().toISOString().split('T')[0]
				}
			}
		},
	},
	methods: {
		async loadSettings() {
			try {
				const response = await api.settings.get()
				const settings = response.data || {}
				this.defaultCurrency = settings.default_currency || 'USD'
			} catch (error) {
				console.error('Error loading settings:', error)
				this.defaultCurrency = 'USD'
			}
		},
		resetForm() {
			this.formData = {
				type: '',
				categoryId: '',
				amount: '',
				transactionDate: '',
				paymentMethod: '',
				description: '',
				clientId: '',
				projectId: '',
				reference: '',
				notes: '',
			}
		},
		loadTransactionData() {
			if (!this.transaction || !this.transaction.id) return

			this.formData = {
				type: this.transaction.type || '',
				categoryId: this.transaction.categoryId || '',
				amount: this.transaction.amount || '',
				transactionDate: this.transaction.transactionDate ? this.transaction.transactionDate.split(' ')[0] : '',
				paymentMethod: this.transaction.paymentMethod || '',
				description: this.transaction.description || '',
				clientId: this.transaction.clientId || '',
				projectId: this.transaction.projectId || '',
				reference: this.transaction.reference || '',
				notes: this.transaction.notes || '',
			}
		},
		onTypeChange() {
			// Clear category when type changes
			this.formData.categoryId = ''
		},
		async saveTransaction() {
			this.saving = true
			try {
				const data = {
					type: this.formData.type || '',
					categoryId: this.formData.categoryId || '',
					amount: this.formData.amount || '',
					currency: this.defaultCurrency || 'USD',
					transactionDate: this.formData.transactionDate || '',
					paymentMethod: this.formData.paymentMethod || '',
					description: this.formData.description || '',
					clientId: this.formData.clientId || '',
					projectId: this.formData.projectId || '',
					reference: this.formData.reference || '',
					notes: this.formData.notes || '',
				}

				// Convert empty strings to null for optional fields
				if (!data.categoryId) data.categoryId = null
				if (!data.clientId) data.clientId = null
				if (!data.projectId) data.projectId = null

				if (this.transaction && this.transaction.id) {
					await api.transactions.update(this.transaction.id, data)
				} else {
					await api.transactions.create(data)
				}

				this.$emit('saved')
				this.closeModal()
			} catch (error) {
				console.error('Error saving transaction:', error)
				alert(this.translate('domaincontrol', 'Error saving transaction'))
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
				'Add Transaction': 'İşlem Ekle',
				'Edit Transaction': 'İşlem Düzenle',
				'Type': 'Tür',
				'Select': 'Seçin',
				'Income': 'Gelir',
				'Expense': 'Gider',
				'Category': 'Kategori',
				'Select Category': 'Kategori Seçin',
				'Amount': 'Tutar',
				'Currency': 'Para Birimi',
				'Transaction Date': 'İşlem Tarihi',
				'Payment Method': 'Ödeme Yöntemi',
				'Cash': 'Nakit',
				'Bank Transfer': 'Banka Transferi',
				'Credit Card': 'Kredi Kartı',
				'Debit Card': 'Banka Kartı',
				'Online Payment': 'Online Ödeme',
				'Other': 'Diğer',
				'Description': 'Açıklama',
				'Transaction description...': 'İşlem açıklaması...',
				'Client (Optional)': 'Müşteri (Opsiyonel)',
				'Select Client': 'Müşteri Seçin',
				'Project (Optional)': 'Proje (Opsiyonel)',
				'Select Project': 'Proje Seçin',
				'Reference / Invoice Number': 'Referans / Fatura No',
				'Invoice number, transaction reference...': 'Fatura numarası, işlem referansı...',
				'Notes': 'Notlar',
				'Additional notes...': 'Ek notlar...',
				'Cancel': 'İptal',
				'Saving...': 'Kaydediliyor...',
				'Save': 'Kaydet',
				'Error saving transaction': 'İşlem kaydedilirken hata oluştu',
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

