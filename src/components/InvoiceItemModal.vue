<template>
	<div v-if="isOpen" class="modal-overlay" @click.self="closeModal">
		<div class="modal-content">
			<div class="modal-header">
				<h3 class="modal-title">
					{{ editingItem ? translate('domaincontrol', 'Edit Item') : translate('domaincontrol', 'Add Item') }}
				</h3>
				<button class="modal-close" @click="closeModal">
					<MaterialIcon name="close" :size="24" />
				</button>
			</div>
			<div class="modal-body">
				<form @submit.prevent="saveItem">
					<input type="hidden" v-model="formData.invoiceId" />
					<div class="form-group">
						<label for="item-description">{{ translate('domaincontrol', 'Description') }} *</label>
						<input
							type="text"
							id="item-description"
							v-model="formData.description"
							required
							class="form-control"
							:placeholder="translate('domaincontrol', 'e.g., Annual domain renewal')"
						/>
					</div>
					<div class="form-row">
						<div class="form-group">
							<label for="item-quantity">{{ translate('domaincontrol', 'Quantity') }}</label>
							<input
								type="number"
								id="item-quantity"
								v-model="formData.quantity"
								min="1"
								class="form-control"
								@input="calculateTotal"
							/>
						</div>
						<div class="form-group">
							<label for="item-unit-price">{{ translate('domaincontrol', 'Unit Price') }}</label>
							<input
								type="number"
								id="item-unit-price"
								v-model="formData.unitPrice"
								step="0.01"
								class="form-control"
								placeholder="0.00"
								@input="calculateTotal"
							/>
						</div>
						<div class="form-group">
							<label for="item-total-price">{{ translate('domaincontrol', 'Total Price') }}</label>
							<input
								type="number"
								id="item-total-price"
								v-model="formData.totalPrice"
								step="0.01"
								class="form-control"
								readonly
							/>
						</div>
					</div>
					<div class="form-row">
						<div class="form-group">
							<label for="item-period-start">{{ translate('domaincontrol', 'Period Start') }}</label>
							<input
								type="date"
								id="item-period-start"
								v-model="formData.periodStart"
								class="form-control"
							/>
						</div>
						<div class="form-group">
							<label for="item-period-end">{{ translate('domaincontrol', 'Period End') }}</label>
							<input
								type="date"
								id="item-period-end"
								v-model="formData.periodEnd"
								class="form-control"
							/>
						</div>
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

export default {
	name: 'InvoiceItemModal',
	components: {
		MaterialIcon,
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
		item: {
			type: Object,
			default: null,
		},
	},
	emits: ['close', 'saved'],
	data() {
		return {
			isOpen: false,
			saving: false,
			formData: {
				invoiceId: null,
				description: '',
				quantity: 1,
				unitPrice: '',
				totalPrice: '',
				periodStart: '',
				periodEnd: '',
			},
		}
	},
	watch: {
		open(newVal) {
			this.isOpen = newVal
			if (newVal) {
				this.resetForm()
				if (this.invoice) {
					this.formData.invoiceId = this.invoice.id
				}
				if (this.item) {
					this.loadItem()
				}
			}
		},
	},
	methods: {
		resetForm() {
			this.formData = {
				invoiceId: null,
				description: '',
				quantity: 1,
				unitPrice: '',
				totalPrice: '',
				periodStart: '',
				periodEnd: '',
			}
		},
		loadItem() {
			if (!this.item) return
			this.formData = {
				invoiceId: this.item.invoiceId || this.invoice?.id || null,
				description: this.item.description || '',
				quantity: this.item.quantity || 1,
				unitPrice: this.item.unitPrice || '',
				totalPrice: this.item.totalPrice || '',
				periodStart: this.item.periodStart || '',
				periodEnd: this.item.periodEnd || '',
			}
		},
		calculateTotal() {
			const quantity = parseFloat(this.formData.quantity) || 0
			const unitPrice = parseFloat(this.formData.unitPrice) || 0
			this.formData.totalPrice = (quantity * unitPrice).toFixed(2)
		},
		async saveItem() {
			this.saving = true
			try {
				const data = {
					...this.formData,
					invoiceId: this.formData.invoiceId || '',
					description: this.formData.description || '',
					quantity: this.formData.quantity || 1,
					unitPrice: this.formData.unitPrice || '0',
					totalPrice: this.formData.totalPrice || '0',
					periodStart: this.formData.periodStart || '',
					periodEnd: this.formData.periodEnd || '',
				}

				if (this.item && this.item.id) {
					// Update existing item - would need update endpoint
					// For now, we'll delete and recreate
					await api.invoices.removeItem(this.formData.invoiceId, this.item.id)
				}
				await api.invoices.addItem(this.formData.invoiceId, data)
				this.$emit('saved')
				this.closeModal()
			} catch (error) {
				console.error('Error saving item:', error)
				alert(this.translate('domaincontrol', 'Error saving item'))
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
				'Edit Item': 'Kalem Düzenle',
				'Add Item': 'Kalem Ekle',
				'Description': 'Açıklama',
				'e.g., Annual domain renewal': 'Örn: Yıllık domain yenileme',
				'Quantity': 'Miktar',
				'Unit Price': 'Birim Fiyat',
				'Total Price': 'Toplam Fiyat',
				'Period Start': 'Dönem Başlangıç',
				'Period End': 'Dönem Bitiş',
				'Cancel': 'İptal',
				'Saving...': 'Kaydediliyor...',
				'Save': 'Kaydet',
				'Error saving item': 'Kalem kaydedilirken hata oluştu',
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
	z-index: 10001;
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
	grid-template-columns: repeat(3, 1fr);
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
	border-color: var(--color-primary);
}

.form-control:read-only {
	background-color: var(--color-background-hover);
	cursor: not-allowed;
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
	background-color: var(--color-primary);
	color: var(--color-primary-text);
}

.button-vue--primary:hover:not(:disabled) {
	background-color: var(--color-primary-hover);
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

