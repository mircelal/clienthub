<template>
	<NcModal v-if="open" @close="closeModal" :title="isEdit ? translate('domaincontrol', 'Edit Warehouse') : translate('domaincontrol', 'Add New Warehouse')">
		<div class="modal-content">
			<form @submit.prevent="saveWarehouse">
				<div class="form-group">
					<label>{{ translate('domaincontrol', 'Warehouse Name') }} *</label>
					<input type="text" v-model="form.name" required class="full-width" :placeholder="translate('domaincontrol', 'e.g. Main Warehouse')" />
				</div>

				<div class="form-group">
					<label>{{ translate('domaincontrol', 'Location') }}</label>
					<input type="text" v-model="form.location" class="full-width" :placeholder="translate('domaincontrol', 'e.g. Istanbul, Turkey')" />
				</div>

				<div class="form-group">
					<label>{{ translate('domaincontrol', 'Description') }}</label>
					<textarea v-model="form.description" class="full-width" rows="3" :placeholder="translate('domaincontrol', 'Optional description')"></textarea>
				</div>

				<div class="form-actions">
					<NcButton @click="closeModal" type="tertiary">{{ translate('domaincontrol', 'Cancel') }}</NcButton>
					<NcButton native-type="submit" type="primary" :disabled="saving">
						{{ saving ? translate('domaincontrol', 'Saving...') : translate('domaincontrol', 'Save Warehouse') }}
					</NcButton>
				</div>
			</form>
		</div>
	</NcModal>
</template>

<script>
import { NcModal, NcButton } from '@nextcloud/vue'

export default {
	name: 'WarehouseModal',
	components: {
		NcModal,
		NcButton,
	},
	props: {
		open: {
			type: Boolean,
			default: false,
		},
		warehouse: {
			type: Object,
			default: null,
		},
	},
	emits: ['close', 'save'],
	data() {
		return {
			saving: false,
			form: {
				name: '',
				location: '',
				description: '',
			},
		}
	},
	computed: {
		isEdit() {
			return !!this.warehouse
		},
	},
	watch: {
		open(val) {
			if (val) {
				this.initForm()
			}
		},
	},
	methods: {
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
			} catch (e) { console.warn(e) }
			return text
		},
		initForm() {
			if (this.warehouse) {
				this.form = {
					name: this.warehouse.name || '',
					location: this.warehouse.location || '',
					description: this.warehouse.description || '',
				}
			} else {
				this.form = {
					name: '',
					location: '',
					description: '',
				}
			}
		},
		closeModal() {
			this.$emit('close')
		},
		async saveWarehouse() {
			this.saving = true
			try {
				await this.$emit('save', this.form)
			} finally {
				this.saving = false
			}
		},
	},
}
</script>

<style scoped>
.modal-content {
	padding: 20px;
}

.form-group {
	margin-bottom: 15px;
}

.form-group label {
	display: block;
	margin-bottom: 5px;
	font-weight: bold;
	color: var(--color-text-maxcontrast);
}

.full-width {
	width: 100%;
	padding: 8px;
	border: 1px solid var(--color-border);
	border-radius: var(--border-radius);
	box-sizing: border-box;
	background-color: var(--color-main-background);
	color: var(--color-main-text);
}

.form-actions {
	display: flex;
	justify-content: flex-end;
	gap: 10px;
	margin-top: 20px;
}
</style>


