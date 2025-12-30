<template>
	<div v-if="isOpen" class="modal-overlay" @click.self="closeModal">
		<div class="modal-content">
			<div class="modal-header">
				<h3 class="modal-title">
					{{ category && category.id ? translate('domaincontrol', 'Edit Category') : translate('domaincontrol', 'Add Category') }}
				</h3>
				<button class="modal-close" @click="closeModal">
					<MaterialIcon name="close" :size="24" />
				</button>
			</div>

			<div class="modal-body">
				<div class="form-row">
					<div class="form-group">
						<label for="category-type" class="form-label">
							{{ translate('domaincontrol', 'Type') }} *
						</label>
						<select
							id="category-type"
							v-model="formData.type"
							class="form-control"
							required
						>
							<option value="">{{ translate('domaincontrol', 'Select') }}</option>
							<option value="income">{{ translate('domaincontrol', 'Income') }}</option>
							<option value="expense">{{ translate('domaincontrol', 'Expense') }}</option>
						</select>
					</div>
					<div class="form-group">
						<label for="category-name" class="form-label">
							{{ translate('domaincontrol', 'Name') }} *
						</label>
						<input
							id="category-name"
							v-model="formData.name"
							type="text"
							class="form-control"
							required
							:placeholder="translate('domaincontrol', 'Category name')"
						/>
					</div>
				</div>

				<div class="form-actions">
					<NcButton type="tertiary" @click="closeModal">
						{{ translate('domaincontrol', 'Cancel') }}
					</NcButton>
					<NcButton type="primary" @click="saveCategory" :disabled="saving">
						<MaterialIcon v-if="saving" name="loading" :size="18" class="loading-icon" />
						{{ saving ? translate('domaincontrol', 'Saving...') : translate('domaincontrol', 'Save') }}
					</NcButton>
				</div>
			</div>
		</div>
	</div>
</template>

<script>
import api from '../services/api'
import MaterialIcon from './MaterialIcon.vue'
import { NcButton } from '@nextcloud/vue'

export default {
	name: 'TransactionCategoryModal',
	components: {
		MaterialIcon,
		NcButton,
	},
	props: {
		open: {
			type: Boolean,
			default: false,
		},
		category: {
			type: Object,
			default: null,
		},
	},
	emits: ['close', 'saved'],
	data() {
		return {
			saving: false,
			formData: {
				type: '',
				name: '',
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
				if (this.category && this.category.id) {
					this.loadCategoryData()
				}
			}
		},
	},
	methods: {
		resetForm() {
			this.formData = {
				type: '',
				name: '',
			}
		},
		loadCategoryData() {
			if (!this.category || !this.category.id) return

			this.formData = {
				type: this.category.type || '',
				name: this.category.name || '',
			}
		},
		async saveCategory() {
			this.saving = true
			try {
				const data = {
					type: this.formData.type || '',
					name: this.formData.name || '',
				}

				if (this.category && this.category.id) {
					await api.transactionCategories.update(this.category.id, data)
				} else {
					await api.transactionCategories.create(data)
				}

				this.$emit('saved')
				this.closeModal()
			} catch (error) {
				console.error('Error saving category:', error)
				alert(this.translate('domaincontrol', 'Error saving category'))
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
						if (translated && translated !== text) return translated
					}
					if (typeof window.t === 'function') {
						const translated = window.t(appId, text, vars || {})
						if (translated && translated !== text) return translated
					}
				}
			} catch (e) {
				console.warn('Translation error:', e)
			}
			return text
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
	border-color: var(--color-primary-element-element);
}

.form-control::placeholder {
	color: var(--color-text-maxcontrast);
}

.form-actions {
	display: flex;
	justify-content: flex-end;
	gap: 12px;
	margin-top: 8px;
}

.button-vue {
	padding: 10px 20px;
	border: none;
	border-radius: var(--border-radius-element);
	font-size: 14px;
	font-weight: 500;
	cursor: pointer;
	display: flex;
	align-items: center;
	gap: 8px;
	transition: background-color 0.2s;
}

.button-vue--primary {
	background-color: var(--color-primary-element-element);
	color: var(--color-primary-element-text);
}

.button-vue--primary:hover:not(:disabled) {
	background-color: var(--color-primary-element-element-dark);
}

.button-vue--secondary {
	background-color: var(--color-background-hover);
	color: var(--color-main-text);
}

.button-vue--secondary:hover {
	background-color: var(--color-background-dark);
}

.button-vue:disabled {
	opacity: 0.5;
	cursor: not-allowed;
}

.loading-icon {
	animation: spin 1s linear infinite;
}

@keyframes spin {
	from { transform: rotate(0deg); }
	to { transform: rotate(360deg); }
}
</style>

