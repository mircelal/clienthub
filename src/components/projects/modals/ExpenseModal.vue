<template>
    <div v-if="open" class="modal-overlay" @click.self="$emit('close')">
        <div class="modal-content modal-content--medium">
            <div class="modal-header">
                <h2 class="modal-title">{{ translate('domaincontrol', 'Add Expense') }}</h2>
                <button class="modal-close" @click="$emit('close')">
                    <Close :size="24" />
                </button>
            </div>
            <div class="modal-body">
                <form @submit.prevent="saveExpense">
                    <div class="form-group">
                        <label class="form-label">{{ translate('domaincontrol', 'Description') }} *</label>
                        <NcTextField
                            v-model="formData.description"
                            :placeholder="translate('domaincontrol', 'Expense description')"
                            required
                        />
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">{{ translate('domaincontrol', 'Amount') }} *</label>
                            <NcTextField
                                v-model.number="formData.amount"
                                type="number"
                                :placeholder="translate('domaincontrol', 'Amount')"
                                min="0"
                                step="0.01"
                                required
                            />
                        </div>
                        <div class="form-group">
                            <label class="form-label">{{ translate('domaincontrol', 'Currency') }}</label>
                            <NcSelect
                                v-model="formData.currency"
                                :options="currencyOptions"
                            />
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">{{ translate('domaincontrol', 'Date') }} *</label>
                            <input
                                type="date"
                                v-model="formData.transactionDate"
                                class="form-control"
                                required
                            />
                        </div>
                        <div class="form-group">
                            <label class="form-label">{{ translate('domaincontrol', 'Category') }}</label>
                            <NcSelect
                                v-model="formData.categoryId"
                                :options="categoryOptions"
                                :placeholder="translate('domaincontrol', 'Select category')"
                            />
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">{{ translate('domaincontrol', 'Payment Method') }}</label>
                            <NcTextField
                                v-model="formData.paymentMethod"
                                :placeholder="translate('domaincontrol', 'Payment method')"
                            />
                        </div>
                        <div class="form-group">
                            <label class="form-label">{{ translate('domaincontrol', 'Reference') }}</label>
                            <NcTextField
                                v-model="formData.reference"
                                :placeholder="translate('domaincontrol', 'Reference')"
                            />
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">{{ translate('domaincontrol', 'Notes') }}</label>
                        <textarea
                            v-model="formData.notes"
                            class="form-control"
                            rows="3"
                            :placeholder="translate('domaincontrol', 'Additional notes')"
                        ></textarea>
                    </div>

                    <div class="modal-footer">
                        <NcButton type="secondary" @click="$emit('close')">
                            {{ translate('domaincontrol', 'Cancel') }}
                        </NcButton>
                        <NcButton type="primary" @click="saveExpense" :disabled="saving">
                            <span v-if="saving">{{ translate('domaincontrol', 'Saving...') }}</span>
                            <span v-else>{{ translate('domaincontrol', 'Save Expense') }}</span>
                        </NcButton>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script>
import { NcButton, NcTextField, NcSelect } from '@nextcloud/vue'
import Close from 'vue-material-design-icons/Close.vue'
import api from '../../../services/api'

export default {
    name: 'ExpenseModal',
    components: {
        NcButton,
        NcTextField,
        NcSelect,
        Close,
    },
    props: {
        open: {
            type: Boolean,
            default: false,
        },
        projectId: {
            type: Number,
            default: null,
        },
        currency: {
            type: String,
            default: 'USD',
        },
    },
    emits: ['close', 'saved'],
    data() {
        return {
            saving: false,
            formData: {
                description: '',
                amount: 0,
                currency: this.currency || 'USD',
                transactionDate: new Date().toISOString().split('T')[0],
                categoryId: null,
                paymentMethod: '',
                reference: '',
                notes: '',
            },
            categories: [],
            currencyOptions: [
                { value: 'USD', label: 'USD' },
                { value: 'EUR', label: 'EUR' },
                { value: 'TRY', label: 'TRY' },
                { value: 'GBP', label: 'GBP' },
            ],
        }
    },
    computed: {
        categoryOptions() {
            return [
                { value: null, label: this.translate('domaincontrol', 'No category') },
                ...this.categories
                    .filter(cat => cat.type === 'expense')
                    .map(cat => ({
                        value: cat.id,
                        label: cat.name,
                    }))
            ]
        },
    },
    watch: {
        open(newVal) {
            if (newVal) {
                this.resetForm()
                this.loadCategories()
            }
        },
        currency(newVal) {
            if (newVal) {
                this.formData.currency = newVal
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
            } catch (e) {
                console.warn('Translation error:', e)
            }
            return text
        },
        resetForm() {
            this.formData = {
                description: '',
                amount: 0,
                currency: this.currency || 'USD',
                transactionDate: new Date().toISOString().split('T')[0],
                categoryId: null,
                paymentMethod: '',
                reference: '',
                notes: '',
            }
        },
        async loadCategories() {
            try {
                const response = await api.transactionCategories.getAll()
                this.categories = response.data || []
            } catch (error) {
                console.error('Error loading categories:', error)
                this.categories = []
            }
        },
        async saveExpense() {
            if (!this.formData.description || !this.formData.amount || this.formData.amount <= 0) {
                alert(this.translate('domaincontrol', 'Please fill in all required fields'))
                return
            }

            this.saving = true
            try {
                const expenseData = {
                    type: 'expense',
                    description: this.formData.description,
                    amount: parseFloat(this.formData.amount),
                    currency: this.formData.currency,
                    transactionDate: this.formData.transactionDate,
                    categoryId: this.formData.categoryId || null,
                    paymentMethod: this.formData.paymentMethod || '',
                    reference: this.formData.reference || '',
                    notes: this.formData.notes || '',
                    projectId: this.projectId || null,
                }

                const response = await api.transactions.create(expenseData)
                if (response.data && response.data.id) {
                    this.$emit('saved', response.data)
                    this.$emit('close')
                } else {
                    throw new Error('Invalid response from server')
                }
            } catch (error) {
                console.error('Error saving expense:', error)
                alert(this.translate('domaincontrol', 'Error saving expense') + ': ' + (error.response?.data?.error || error.message))
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
}

.modal-content {
    background: var(--color-main-background);
    border-radius: var(--border-radius-large);
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
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
    cursor: pointer;
    padding: 4px;
    color: var(--color-text-maxcontrast);
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: var(--border-radius);
    transition: background-color 0.2s;
}

.modal-close:hover {
    background-color: var(--color-background-hover);
}

.modal-body {
    padding: 24px;
}

.form-group {
    margin-bottom: 16px;
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
    font-size: 14px;
    background: var(--color-main-background);
    color: var(--color-main-text);
    transition: border-color 0.2s;
}

.form-control:focus {
    outline: none;
    border-color: var(--color-primary-element-element-element);
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
}

.modal-footer {
    display: flex;
    justify-content: flex-end;
    gap: 12px;
    margin-top: 24px;
    padding-top: 16px;
    border-top: 1px solid var(--color-border);
}

textarea.form-control {
    resize: vertical;
    min-height: 80px;
}
</style>

