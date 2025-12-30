<template>
    <div v-if="open" class="modal-overlay" @click.self="$emit('close')">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title">
                    <CashMinus :size="24" class="title-icon" />
                    {{ translate('domaincontrol', 'Add Expense') }}
                </h2>
                <button class="modal-close" @click="$emit('close')">
                    <Close :size="20" />
                </button>
            </div>
            
            <div class="modal-body">
                <form @submit.prevent="saveExpense">
                    <!-- Essential Fields -->
                    <div class="essential-fields">
                        <div class="form-group">
                            <label class="form-label">
                                {{ translate('domaincontrol', 'What did you spend on?') }}
                                <span class="required">*</span>
                            </label>
                            <NcTextField
                                v-model="formData.description"
                                :placeholder="translate('domaincontrol', 'e.g., Server hosting, Office supplies')"
                                required
                                class="large-input"
                            />
                        </div>

                        <div class="amount-date-row">
                            <div class="form-group amount-group">
                                <label class="form-label">
                                    {{ translate('domaincontrol', 'Amount') }}
                                    <span class="required">*</span>
                                </label>
                                <div class="amount-input-wrapper">
                                    <input
                                        v-model.number="formData.amount"
                                        type="number"
                                        class="amount-input"
                                        :placeholder="'0.00'"
                                        min="0"
                                        step="0.01"
                                        required
                                    />
                                    <span class="currency-symbol">{{ currencySymbol }}</span>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="form-label">
                                    {{ translate('domaincontrol', 'Date') }}
                                    <span class="required">*</span>
                                </label>
                                <input
                                    type="date"
                                    v-model="formData.transactionDate"
                                    class="date-input"
                                    required
                                />
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">{{ translate('domaincontrol', 'Category') }}</label>
                            <NcSelect
                                v-model="formData.categoryId"
                                :options="categoryOptions"
                                :placeholder="translate('domaincontrol', 'Select a category (optional)')"
                            />
                        </div>
                    </div>

                    <!-- Optional Details (Collapsible) -->
                    <div class="optional-section">
                        <button 
                            type="button" 
                            class="toggle-details" 
                            @click="showOptionalFields = !showOptionalFields"
                        >
                            <ChevronDown v-if="!showOptionalFields" :size="20" />
                            <ChevronUp v-else :size="20" />
                            {{ translate('domaincontrol', 'Additional Details') }}
                            <span class="optional-badge">{{ translate('domaincontrol', 'Optional') }}</span>
                        </button>

                        <div v-if="showOptionalFields" class="optional-fields">
                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label">{{ translate('domaincontrol', 'Payment Method') }}</label>
                                    <NcTextField
                                        v-model="formData.paymentMethod"
                                        :placeholder="translate('domaincontrol', 'e.g., Credit Card, Cash')"
                                    />
                                </div>
                                <div class="form-group">
                                    <label class="form-label">{{ translate('domaincontrol', 'Reference') }}</label>
                                    <NcTextField
                                        v-model="formData.reference"
                                        :placeholder="translate('domaincontrol', 'Invoice #, Receipt #')"
                                    />
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="form-label">{{ translate('domaincontrol', 'Notes') }}</label>
                                <textarea
                                    v-model="formData.notes"
                                    class="notes-textarea"
                                    rows="3"
                                    :placeholder="translate('domaincontrol', 'Any additional information...')"
                                ></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <NcButton type="secondary" @click="$emit('close')">
                            {{ translate('domaincontrol', 'Cancel') }}
                        </NcButton>
                        <NcButton type="primary" native-type="submit" :disabled="saving || !isFormValid">
                            <template #icon>
                                <Loading v-if="saving" :size="20" />
                                <Check v-else :size="20" />
                            </template>
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
import CashMinus from 'vue-material-design-icons/CashMinus.vue'
import ChevronDown from 'vue-material-design-icons/ChevronDown.vue'
import ChevronUp from 'vue-material-design-icons/ChevronUp.vue'
import Check from 'vue-material-design-icons/Check.vue'
import Loading from 'vue-material-design-icons/Loading.vue'
import api from '../../../services/api'

export default {
    name: 'ExpenseModal',
    components: {
        NcButton,
        NcTextField,
        NcSelect,
        Close,
        CashMinus,
        ChevronDown,
        ChevronUp,
        Check,
        Loading,
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
        clientId: {
            type: [Number, String],
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
            showOptionalFields: false,
            formData: {
                description: '',
                amount: null,
                transactionDate: new Date().toISOString().split('T')[0],
                categoryId: null,
                paymentMethod: '',
                reference: '',
                notes: '',
            },
            categories: [],
            currencies: [],
            defaultCurrency: 'USD', // Will be loaded from settings
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
        currencySymbol() {
            // Use default currency from settings, not prop
            const currencyCode = this.defaultCurrency || 'USD'
            
            // Get currency symbol from settings
            if (this.currencies && this.currencies.length > 0) {
                const curr = this.currencies.find(c => c.code === currencyCode)
                if (curr && curr.symbol) {
                    return curr.symbol
                }
            }
            
            // Fallback symbols
            const symbols = {
                'USD': '$',
                'EUR': '€',
                'TRY': '₺',
                'AZN': '₼',
                'GBP': '£',
                'RUB': '₽',
            }
            return symbols[currencyCode] || currencyCode
        },
        isFormValid() {
            return this.formData.description && 
                   this.formData.amount && 
                   this.formData.amount > 0 &&
                   this.formData.transactionDate
        },
    },
    watch: {
        open(newVal) {
            if (newVal) {
                this.resetForm()
                this.loadCategories()
                this.loadSettings()
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
                amount: null,
                transactionDate: new Date().toISOString().split('T')[0],
                categoryId: null,
                paymentMethod: '',
                reference: '',
                notes: '',
            }
            this.showOptionalFields = false
        },
        async loadSettings() {
            try {
                const response = await api.settings.get()
                const settings = response.data || {}
                
                // Load default currency
                this.defaultCurrency = settings.default_currency || 'USD'
                
                // Load currencies list
                if (settings.currencies) {
                    try {
                        this.currencies = JSON.parse(settings.currencies)
                    } catch (e) {
                        this.currencies = []
                    }
                }
            } catch (error) {
                console.error('Error loading settings:', error)
                this.defaultCurrency = 'USD'
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
            if (!this.isFormValid) {
                alert(this.translate('domaincontrol', 'Please fill in all required fields'))
                return
            }

            this.saving = true
            try {
                const expenseData = {
                    type: 'expense',
                    description: this.formData.description,
                    amount: parseFloat(this.formData.amount),
                    currency: this.defaultCurrency, // Use default currency from settings
                    transactionDate: this.formData.transactionDate,
                    categoryId: this.formData.categoryId || null,
                    clientId: this.clientId || null,
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
    background-color: rgba(0, 0, 0, 0.6);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 10000;
    backdrop-filter: blur(2px);
}

.modal-content {
    background: var(--color-main-background);
    border-radius: var(--border-radius-large);
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
    max-width: 560px;
    width: 90%;
    max-height: 90vh;
    overflow: hidden;
    display: flex;
    flex-direction: column;
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 24px 28px;
    border-bottom: 2px solid var(--color-border-dark);
    background: var(--color-background-dark);
}

.modal-title {
    margin: 0;
    font-size: 20px;
    font-weight: 700;
    color: var(--color-main-text);
    display: flex;
    align-items: center;
    gap: 12px;
}

.title-icon {
    color: #e9322d;
}

.modal-close {
    background: none;
    border: none;
    cursor: pointer;
    padding: 8px;
    color: var(--color-text-lighter);
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: var(--border-radius);
    transition: all 0.2s;
}

.modal-close:hover {
    background-color: var(--color-background-hover);
    color: var(--color-main-text);
}

.modal-body {
    padding: 28px;
    overflow-y: auto;
    flex: 1;
}

/* Essential Fields */
.essential-fields {
    margin-bottom: 24px;
}

.form-group {
    margin-bottom: 20px;
}

.form-label {
    display: block;
    margin-bottom: 8px;
    font-size: 14px;
    font-weight: 600;
    color: var(--color-main-text);
}

.required {
    color: #e9322d;
    margin-left: 4px;
}

.large-input {
    font-size: 15px;
}

/* Amount and Date Row */
.amount-date-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
    margin-bottom: 20px;
}

.amount-group {
    margin-bottom: 0;
}

.amount-input-wrapper {
    position: relative;
    display: flex;
    align-items: center;
}

.amount-input {
    width: 100%;
    padding: 12px 50px 12px 16px;
    border: 2px solid var(--color-border);
    border-radius: var(--border-radius-large);
    font-size: 18px;
    font-weight: 700;
    font-family: var(--font-face, monospace);
    background: var(--color-main-background);
    color: var(--color-main-text);
    transition: all 0.2s;
}

.amount-input:focus {
    outline: none;
    border-color: var(--color-primary-element);
    box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.1);
}

.currency-symbol {
    position: absolute;
    right: 16px;
    font-size: 18px;
    font-weight: 700;
    color: var(--color-text-lighter);
    pointer-events: none;
}

.date-input {
    width: 100%;
    padding: 12px 16px;
    border: 2px solid var(--color-border);
    border-radius: var(--border-radius-large);
    font-size: 14px;
    background: var(--color-main-background);
    color: var(--color-main-text);
    transition: all 0.2s;
}

.date-input:focus {
    outline: none;
    border-color: var(--color-primary-element);
    box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.1);
}

/* Optional Section */
.optional-section {
    margin-top: 24px;
    padding-top: 24px;
    border-top: 1px solid var(--color-border);
}

.toggle-details {
    width: 100%;
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 12px 16px;
    background: var(--color-background-dark);
    border: 1px solid var(--color-border);
    border-radius: var(--border-radius-large);
    font-size: 14px;
    font-weight: 600;
    color: var(--color-main-text);
    cursor: pointer;
    transition: all 0.2s;
}

.toggle-details:hover {
    background: var(--color-background-hover);
    border-color: var(--color-primary-element);
}

.optional-badge {
    margin-left: auto;
    padding: 2px 8px;
    background: var(--color-background-hover);
    border-radius: 10px;
    font-size: 11px;
    font-weight: 600;
    color: var(--color-text-lighter);
}

.optional-fields {
    margin-top: 16px;
    padding: 16px;
    background: var(--color-background-hover);
    border-radius: var(--border-radius-large);
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
}

.notes-textarea {
    width: 100%;
    padding: 12px 16px;
    border: 2px solid var(--color-border);
    border-radius: var(--border-radius-large);
    font-size: 14px;
    font-family: var(--font-face, sans-serif);
    background: var(--color-main-background);
    color: var(--color-main-text);
    resize: vertical;
    min-height: 80px;
    transition: all 0.2s;
}

.notes-textarea:focus {
    outline: none;
    border-color: var(--color-primary-element);
    box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.1);
}

/* Modal Footer */
.modal-footer {
    display: flex;
    justify-content: flex-end;
    gap: 12px;
    margin-top: 28px;
    padding-top: 20px;
    border-top: 2px solid var(--color-border-dark);
}

/* Responsive */
@media (max-width: 600px) {
    .modal-content {
        width: 95%;
        max-height: 95vh;
    }

    .modal-header {
        padding: 20px;
    }

    .modal-body {
        padding: 20px;
    }

    .amount-date-row,
    .form-row {
        grid-template-columns: 1fr;
    }
}
</style>
