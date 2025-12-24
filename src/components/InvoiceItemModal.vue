<template>
    <div v-if="open" class="modal-overlay" @click.self="$emit('close')">
        <div class="modal-content modal-content--medium">
            <div class="modal-header">
                <h2 class="modal-title">{{ translate('domaincontrol', 'Add Invoice Item') }}</h2>
                <button class="modal-close" @click="$emit('close')">
                    <Close :size="24" />
                </button>
            </div>
            <div class="modal-body">
                <form @submit.prevent="saveItem">
                    <div class="form-group">
                        <label class="form-label">{{ translate('domaincontrol', 'Description') }} *</label>
                        <input
                            type="text"
                            v-model="formData.description"
                            class="form-control"
                            :placeholder="translate('domaincontrol', 'Item description')"
                            required
                        />
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">{{ translate('domaincontrol', 'Quantity') }}</label>
                            <input
                                type="number"
                                v-model.number="formData.quantity"
                                class="form-control"
                                min="1"
                                step="1"
                                @input="calculateTotal"
                            />
                        </div>
                        <div class="form-group">
                            <label class="form-label">{{ translate('domaincontrol', 'Unit Price') }}</label>
                            <input
                                type="number"
                                v-model.number="formData.unitPrice"
                                class="form-control"
                                min="0"
                                step="0.01"
                                @input="calculateTotal"
                            />
                        </div>
                        <div class="form-group">
                            <label class="form-label">{{ translate('domaincontrol', 'Total Price') }}</label>
                            <input
                                type="number"
                                v-model.number="formData.totalPrice"
                                class="form-control"
                                min="0"
                                step="0.01"
                                readonly
                            />
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">{{ translate('domaincontrol', 'Period Start') }}</label>
                            <input
                                type="date"
                                v-model="formData.periodStart"
                                class="form-control"
                            />
                        </div>
                        <div class="form-group">
                            <label class="form-label">{{ translate('domaincontrol', 'Period End') }}</label>
                            <input
                                type="date"
                                v-model="formData.periodEnd"
                                class="form-control"
                            />
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">{{ translate('domaincontrol', 'Item Type') }}</label>
                        <select v-model="formData.itemType" class="form-control">
                            <option value="service">{{ translate('domaincontrol', 'Service') }}</option>
                            <option value="domain">{{ translate('domaincontrol', 'Domain') }}</option>
                            <option value="hosting">{{ translate('domaincontrol', 'Hosting') }}</option>
                            <option value="website">{{ translate('domaincontrol', 'Website') }}</option>
                            <option value="other">{{ translate('domaincontrol', 'Other') }}</option>
                        </select>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" @click="$emit('close')">
                            {{ translate('domaincontrol', 'Cancel') }}
                        </button>
                        <button type="submit" class="btn btn-primary" :disabled="saving">
                            <span v-if="saving">{{ translate('domaincontrol', 'Saving...') }}</span>
                            <span v-else>{{ translate('domaincontrol', 'Add Item') }}</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script>
import Close from 'vue-material-design-icons/Close.vue'
import api from '../services/api'

export default {
    name: 'InvoiceItemModal',
    components: {
        Close
    },
    props: {
        open: {
            type: Boolean,
            default: false
        },
        invoiceId: {
            type: Number,
            required: true
        }
    },
    emits: ['close', 'saved'],
    data() {
        return {
            saving: false,
            formData: {
                description: '',
                quantity: 1,
                unitPrice: 0,
                totalPrice: 0,
                periodStart: '',
                periodEnd: '',
                itemType: 'service',
                itemId: 0
            }
        }
    },
    watch: {
        open(newVal) {
            if (newVal) {
                this.resetForm()
            }
        }
    },
    methods: {
        translate(appId, text, vars) {
            try {
                if (typeof window !== 'undefined' && typeof window.t === 'function') {
                    return window.t(appId, text, vars || {})
                }
            } catch (e) { /* ignore */ }
            return text
        },
        resetForm() {
            this.formData = {
                description: '',
                quantity: 1,
                unitPrice: 0,
                totalPrice: 0,
                periodStart: '',
                periodEnd: '',
                itemType: 'service',
                itemId: 0
            }
        },
        calculateTotal() {
            this.formData.totalPrice = (this.formData.quantity || 1) * (this.formData.unitPrice || 0)
        },
        async saveItem() {
            this.saving = true
            try {
                await api.invoices.addItem(this.invoiceId, this.formData)
                this.$emit('saved')
                this.$emit('close')
            } catch (error) {
                console.error('Error saving invoice item:', error)
                alert(this.translate('domaincontrol', 'Error saving invoice item'))
            } finally {
                this.saving = false
            }
        }
    }
}
</script>

<style scoped>
.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
}

.modal-content {
    background: var(--color-main-background);
    border-radius: var(--border-radius-large);
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
    max-width: 600px;
    width: 90%;
    max-height: 90vh;
    overflow-y: auto;
}

.modal-content--medium {
    max-width: 600px;
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px 24px;
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
    padding: 4px;
    cursor: pointer;
    color: var(--color-text-maxcontrast);
    display: flex;
    align-items: center;
    justify-content: center;
}

.modal-close:hover {
    color: var(--color-main-text);
}

.modal-body {
    padding: 24px;
}

.form-group {
    margin-bottom: 20px;
}

.form-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 16px;
    margin-bottom: 20px;
}

.form-label {
    display: block;
    margin-bottom: 8px;
    font-size: 14px;
    font-weight: 500;
    color: var(--color-main-text);
}

.form-control {
    width: 100%;
    padding: 8px 12px;
    border: 1px solid var(--color-border);
    border-radius: var(--border-radius);
    background: var(--color-main-background);
    color: var(--color-main-text);
    font-size: 14px;
}

.form-control:focus {
    outline: none;
    border-color: var(--color-primary-element-element);
}

.form-control[readonly] {
    background: var(--color-background-hover);
    cursor: not-allowed;
}

.modal-footer {
    display: flex;
    justify-content: flex-end;
    gap: 12px;
    margin-top: 24px;
    padding-top: 20px;
    border-top: 1px solid var(--color-border);
}

.btn {
    padding: 10px 20px;
    border: none;
    border-radius: var(--border-radius);
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-primary {
    background: var(--color-primary-element-element);
    color: var(--color-primary-element-element-text);
}

.btn-primary:hover:not(:disabled) {
    background: var(--color-primary-element-element-hover);
}

.btn-secondary {
    background: var(--color-background-hover);
    color: var(--color-main-text);
}

.btn-secondary:hover {
    background: var(--color-background-dark);
}

.btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}
</style>
