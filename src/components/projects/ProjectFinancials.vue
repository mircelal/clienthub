<template>
    <div class="financials-view">
        
        <!-- ========================================== -->
        <!-- LEVEL 1: HERO METRIC - NET RESULT         -->
        <!-- ========================================== -->
        <div class="hero-metric">
            <div class="hero-label">{{ translate('domaincontrol', 'Net Result') }}</div>
            <div class="hero-value" :class="getNetResultClass()">
                {{ formatCurrency(getNetResult()) }}
            </div>
            <div class="hero-subtitle">
                {{ getNetResultSubtitle() }}
            </div>
        </div>

        <!-- ========================================== -->
        <!-- LEVEL 2: CASH FLOW SUMMARY                 -->
        <!-- ========================================== -->
        <div class="cash-flow-summary">
            <div class="flow-metric">
                <div class="flow-label">{{ translate('domaincontrol', 'Total Collected') }}</div>
                <div class="flow-value positive">{{ formatCurrency(totalPaid) }}</div>
            </div>
            <div class="flow-separator">−</div>
            <div class="flow-metric">
                <div class="flow-label">{{ translate('domaincontrol', 'Total Expenses') }}</div>
                <div class="flow-value negative">{{ formatCurrency(totalExpenses) }}</div>
            </div>
            <div class="flow-separator">=</div>
            <div class="flow-metric">
                <div class="flow-label">{{ translate('domaincontrol', 'Expected Income') }}</div>
                <div class="flow-value pending">{{ formatCurrency(totalPending) }}</div>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- LEVEL 3: INVOICES (PRIMARY)                -->
        <!-- ========================================== -->
        <div class="section-container">
            <div class="section-header">
                <h3>{{ translate('domaincontrol', 'Invoices') }}</h3>
                <div class="section-actions">
                    <span class="count-badge">{{ invoices.length }}</span>
                    <NcButton type="primary" @click="$emit('create-invoice')">
                        <template #icon>
                            <Plus :size="20" />
                        </template>
                        {{ translate('domaincontrol', 'Create Invoice') }}
                    </NcButton>
                </div>
            </div>

            <div v-if="invoices.length > 0" class="data-table">
                <table>
                    <thead>
                        <tr>
                            <th @click="sortInvoices('invoiceNumber')" class="sortable">
                                {{ translate('domaincontrol', 'Invoice') }}
                                <ChevronDown v-if="sortBy === 'invoiceNumber'" :size="16" />
                            </th>
                            <th @click="sortInvoices('issueDate')" class="sortable">
                                {{ translate('domaincontrol', 'Date Range') }}
                                <ChevronDown v-if="sortBy === 'issueDate'" :size="16" />
                            </th>
                            <th @click="sortInvoices('totalAmount')" class="sortable text-right">
                                {{ translate('domaincontrol', 'Amount') }}
                                <ChevronDown v-if="sortBy === 'totalAmount'" :size="16" />
                            </th>
                            <th @click="sortInvoices('status')" class="sortable">
                                {{ translate('domaincontrol', 'Status') }}
                                <ChevronDown v-if="sortBy === 'status'" :size="16" />
                            </th>
                            <th class="text-right">{{ translate('domaincontrol', 'Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="invoice in sortedInvoices" :key="invoice.id" class="table-row">
                            <td class="invoice-number">{{ invoice.invoiceNumber || '#' + invoice.id }}</td>
                            <td class="date-range">
                                <span>{{ formatDate(invoice.issueDate) }}</span>
                                <span class="date-separator">→</span>
                                <span :class="{ 'overdue': isOverdue(invoice.dueDate) }">
                                    {{ formatDate(invoice.dueDate) }}
                                </span>
                            </td>
                            <td class="text-right amount">{{ formatCurrency(invoice.totalAmount || invoice.total || 0) }}</td>
                            <td>
                                <span class="status-text" :class="getStatusClass(invoice)">
                                    {{ getInvoiceStatusText(invoice.status) }}
                                </span>
                            </td>
                            <td class="text-right actions">
                                <NcButton 
                                    type="tertiary" 
                                    @click="$emit('navigate-invoice', invoice.id)"
                                    :aria-label="translate('domaincontrol', 'View Invoice')"
                                >
                                    <template #icon>
                                        <OpenInNew :size="18" />
                                    </template>
                                </NcButton>
                                <NcButton 
                                    v-if="invoice.status !== 'paid'"
                                    type="tertiary" 
                                    @click="markAsPaid(invoice.id)"
                                    :aria-label="translate('domaincontrol', 'Mark as Paid')"
                                >
                                    <template #icon>
                                        <CheckCircle :size="18" />
                                    </template>
                                </NcButton>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div v-else class="empty-state">
                <FileDocumentOutline :size="48" />
                <p>{{ translate('domaincontrol', 'No invoices created yet') }}</p>
                <NcButton type="primary" @click="$emit('create-invoice')">
                    {{ translate('domaincontrol', 'Create First Invoice') }}
                </NcButton>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- LEVEL 4: EXPENSES (SECONDARY)              -->
        <!-- ========================================== -->
        <div class="section-container">
            <div class="section-header">
                <h3>{{ translate('domaincontrol', 'Expenses') }}</h3>
                <div class="section-actions">
                    <span class="count-badge">{{ expenses.length }}</span>
                    <NcButton type="secondary" @click="showAddExpenseModal">
                        <template #icon>
                            <Plus :size="18" />
                        </template>
                        {{ translate('domaincontrol', 'Add Expense') }}
                    </NcButton>
                </div>
            </div>

            <div v-if="expenses.length > 0" class="expense-list">
                <div 
                    v-for="expense in sortedExpenses" 
                    :key="expense.id" 
                    class="expense-item"
                >
                    <div class="expense-main">
                        <div class="expense-info">
                            <div class="expense-description">{{ expense.description || translate('domaincontrol', 'Unnamed Expense') }}</div>
                            <div class="expense-meta">
                                <span>{{ formatDate(expense.transactionDate) }}</span>
                                <span v-if="expense.categoryName" class="meta-separator">•</span>
                                <span v-if="expense.categoryName">{{ expense.categoryName }}</span>
                                <span v-if="expense.paymentMethod" class="meta-separator">•</span>
                                <span v-if="expense.paymentMethod">{{ expense.paymentMethod }}</span>
                            </div>
                        </div>
                        <div class="expense-amount">{{ formatCurrency(expense.amount || 0) }}</div>
                        <div class="expense-actions">
                            <NcButton 
                                type="tertiary" 
                                @click="toggleExpenseDetails(expense.id)"
                                :aria-label="translate('domaincontrol', 'Toggle Details')"
                            >
                                <template #icon>
                                    <ChevronDown v-if="!expandedExpenses[expense.id]" :size="20" />
                                    <ChevronUp v-else :size="20" />
                                </template>
                            </NcButton>
                        </div>
                    </div>

                    <!-- Expanded Details -->
                    <div v-if="expandedExpenses[expense.id]" class="expense-details">
                        <div class="detail-grid">
                            <div v-if="expense.reference" class="detail-item">
                                <span class="detail-label">{{ translate('domaincontrol', 'Reference') }}</span>
                                <span class="detail-value">{{ expense.reference }}</span>
                            </div>
                            <div v-if="expense.notes" class="detail-item full-width">
                                <span class="detail-label">{{ translate('domaincontrol', 'Notes') }}</span>
                                <span class="detail-value">{{ expense.notes }}</span>
                            </div>
                        </div>
                        <div class="detail-actions">
                            <NcButton type="error" @click="deleteExpense(expense.id)">
                                <template #icon>
                                    <Delete :size="18" />
                                </template>
                                {{ translate('domaincontrol', 'Delete') }}
                            </NcButton>
                        </div>
                    </div>
                </div>
            </div>

            <div v-else class="empty-state">
                <CashMinus :size="48" />
                <p>{{ translate('domaincontrol', 'No expenses recorded') }}</p>
                <NcButton type="primary" @click="showAddExpenseModal">
                    {{ translate('domaincontrol', 'Add Expense') }}
                </NcButton>
            </div>
        </div>
    </div>
</template>

<script>
// Libraries
import { NcButton } from '@nextcloud/vue'
import api from '../../services/api'

// Icons
import Plus from 'vue-material-design-icons/Plus.vue'
import FileDocumentOutline from 'vue-material-design-icons/FileDocumentOutline.vue'
import CheckCircle from 'vue-material-design-icons/CheckCircle.vue'
import ChevronDown from 'vue-material-design-icons/ChevronDown.vue'
import ChevronUp from 'vue-material-design-icons/ChevronUp.vue'
import OpenInNew from 'vue-material-design-icons/OpenInNew.vue'
import CashMinus from 'vue-material-design-icons/CashMinus.vue'
import Delete from 'vue-material-design-icons/Delete.vue'

export default {
    name: 'ProjectFinancials',
    components: {
        NcButton,
        Plus,
        FileDocumentOutline,
        CheckCircle,
        ChevronDown,
        ChevronUp,
        OpenInNew,
        CashMinus,
        Delete,
    },
    props: {
        project: {
            type: Object,
            required: true,
        },
        invoices: {
            type: Array,
            default: () => [],
        },
        totalInvoiced: {
            type: Number,
            default: 0,
        },
        totalPaid: {
            type: Number,
            default: 0,
        },
        totalPending: {
            type: Number,
            default: 0,
        },
        totalExpenses: {
            type: Number,
            default: 0,
        },
        expenses: {
            type: Array,
            default: () => [],
        },
    },
    emits: ['create-invoice', 'navigate-invoice', 'add-expense', 'expense-deleted', 'invoice-updated'],
    data() {
        return {
            expandedExpenses: {},
            defaultCurrency: 'USD',
            currencies: [],
            sortBy: 'issueDate',
            sortDirection: 'desc',
        }
    },
    computed: {
        sortedInvoices() {
            const invoices = [...this.invoices]
            return invoices.sort((a, b) => {
                let aVal = a[this.sortBy]
                let bVal = b[this.sortBy]

                // Handle different data types
                if (this.sortBy === 'totalAmount') {
                    aVal = parseFloat(a.totalAmount || a.total || 0)
                    bVal = parseFloat(b.totalAmount || b.total || 0)
                } else if (this.sortBy === 'issueDate' || this.sortBy === 'dueDate') {
                    aVal = new Date(aVal).getTime()
                    bVal = new Date(bVal).getTime()
                }

                if (this.sortDirection === 'asc') {
                    return aVal > bVal ? 1 : -1
                } else {
                    return aVal < bVal ? 1 : -1
                }
            })
        },
        sortedExpenses() {
            const expenses = [...this.expenses]
            return expenses.sort((a, b) => {
                const aDate = new Date(a.transactionDate).getTime()
                const bDate = new Date(b.transactionDate).getTime()
                return bDate - aDate // Most recent first
            })
        },
    },
    mounted() {
        this.loadSettings()
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
            } catch (e) { console.warn('Translation error:', e) }
            return text
        },

        // ==========================================
        // FINANCIAL CALCULATIONS (CENTRALIZED)
        // ==========================================
        
        /**
         * Net Result = Total Collected - Total Expenses
         * This is the ACTUAL profit/loss based on cash flow
         */
        getNetResult() {
            return this.totalPaid - this.totalExpenses
        },

        getNetResultClass() {
            const result = this.getNetResult()
            if (result > 0) return 'profit'
            if (result < 0) return 'loss'
            return 'neutral'
        },

        getNetResultSubtitle() {
            const result = this.getNetResult()
            if (result > 0) {
                return this.translate('domaincontrol', 'Project is profitable')
            } else if (result < 0) {
                return this.translate('domaincontrol', 'Project is operating at a loss')
            } else {
                return this.translate('domaincontrol', 'Break-even')
            }
        },

        // ==========================================
        // SETTINGS & FORMATTING
        // ==========================================

        async loadSettings() {
            try {
                const response = await api.settings.get()
                const settings = response.data || {}
                
                this.defaultCurrency = settings.default_currency || 'USD'
                
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

        getCurrencySymbol(currencyCode) {
            if (!currencyCode) currencyCode = this.defaultCurrency || 'USD'
            
            if (this.currencies && this.currencies.length > 0) {
                const currency = this.currencies.find(c => c.code === currencyCode)
                if (currency && currency.symbol) {
                    return currency.symbol
                }
            }
            
            const defaultSymbols = {
                'USD': '$',
                'EUR': '€',
                'TRY': '₺',
                'AZN': '₼',
                'GBP': '£',
                'RUB': '₽',
            }
            return defaultSymbols[currencyCode] || currencyCode
        },

        formatCurrency(amount, currency = null) {
            if (amount === null || amount === undefined) return '-'
            const currencyCode = this.defaultCurrency || 'USD'
            const symbol = this.getCurrencySymbol(currencyCode)
            
            const val = parseFloat(amount)
            if (isNaN(val)) return '-'
            
            return new Intl.NumberFormat('tr-TR', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2,
            }).format(val) + ' ' + symbol
        },

        formatDate(dateString) {
            if (!dateString) return '-'
            try {
                return new Date(dateString).toLocaleDateString('tr-TR', { 
                    year: 'numeric', 
                    month: 'short', 
                    day: 'numeric' 
                })
            } catch (e) { 
                return dateString 
            }
        },

        // ==========================================
        // INVOICE METHODS
        // ==========================================

        sortInvoices(field) {
            if (this.sortBy === field) {
                this.sortDirection = this.sortDirection === 'asc' ? 'desc' : 'asc'
            } else {
                this.sortBy = field
                this.sortDirection = 'desc'
            }
        },

        getInvoiceStatusText(status) {
            const statusTexts = {
                draft: this.translate('domaincontrol', 'Draft'),
                sent: this.translate('domaincontrol', 'Sent'),
                paid: this.translate('domaincontrol', 'Paid'),
                overdue: this.translate('domaincontrol', 'Overdue'),
                cancelled: this.translate('domaincontrol', 'Cancelled'),
            }
            return statusTexts[status] || status
        },

        getStatusClass(invoice) {
            if (invoice.status === 'paid') return 'status-paid'
            if (invoice.status === 'cancelled') return 'status-cancelled'
            if (this.isOverdue(invoice.dueDate)) return 'status-overdue'
            if (invoice.status === 'sent') return 'status-pending'
            return 'status-draft'
        },

        isOverdue(dueDate) {
            if (!dueDate) return false
            try {
                const due = new Date(dueDate)
                const today = new Date()
                today.setHours(0, 0, 0, 0)
                due.setHours(0, 0, 0, 0)
                return due < today
            } catch (e) { 
                return false 
            }
        },

        async markAsPaid(invoiceId) {
            try {
                await api.invoices.updateStatus(invoiceId, 'paid')
                this.$emit('invoice-updated')
            } catch (error) {
                console.error('Error marking invoice as paid:', error)
                alert(this.translate('domaincontrol', 'Error updating invoice status'))
            }
        },

        // ==========================================
        // EXPENSE METHODS
        // ==========================================

        showAddExpenseModal() {
            this.$emit('add-expense')
        },

        toggleExpenseDetails(expenseId) {
            this.expandedExpenses = {
                ...this.expandedExpenses,
                [expenseId]: !this.expandedExpenses[expenseId]
            }
        },

        async deleteExpense(expenseId) {
            if (!confirm(this.translate('domaincontrol', 'Are you sure you want to delete this expense?'))) {
                return
            }
            try {
                await api.transactions.delete(expenseId)
                if (this.expandedExpenses[expenseId]) {
                    this.expandedExpenses = { ...this.expandedExpenses, [expenseId]: false }
                }
                this.$emit('expense-deleted')
            } catch (error) {
                console.error('Error deleting expense:', error)
                alert(this.translate('domaincontrol', 'Error deleting expense'))
            }
        },
    },
}
</script>

<style scoped>
/* ========================================== */
/* BASE STYLES                                */
/* ========================================== */

.financials-view {
    padding: 24px;
    max-width: 1400px;
    margin: 0 auto;
    color: var(--color-main-text);
    font-family: var(--font-face, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif);
}

/* ========================================== */
/* LEVEL 1: HERO METRIC                       */
/* ========================================== */

.hero-metric {
    text-align: center;
    padding: 48px 24px;
    margin-bottom: 40px;
    background: var(--color-main-background);
    border-radius: var(--border-radius-large);
    border: 2px solid var(--color-border-dark);
}

.hero-label {
    font-size: 14px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 1.5px;
    color: var(--color-text-lighter);
    margin-bottom: 16px;
}

.hero-value {
    font-size: 56px;
    font-weight: 700;
    font-family: var(--font-face, monospace);
    line-height: 1.2;
    margin-bottom: 12px;
}

.hero-value.profit {
    color: #46a546;
}

.hero-value.loss {
    color: #e9322d;
}

.hero-value.neutral {
    color: var(--color-main-text);
}

.hero-subtitle {
    font-size: 16px;
    color: var(--color-text-lighter);
    font-weight: 600;
}

/* ========================================== */
/* LEVEL 2: CASH FLOW SUMMARY                 */
/* ========================================== */

.cash-flow-summary {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 32px;
    margin-bottom: 48px;
    padding: 28px 24px;
    background: var(--color-background-dark);
    border-radius: var(--border-radius-large);
    border: 1px solid var(--color-border);
}

.flow-metric {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 10px;
}

.flow-label {
    font-size: 12px;
    font-weight: 700;
    text-transform: uppercase;
    color: var(--color-text-lighter);
    letter-spacing: 1px;
}

.flow-value {
    font-size: 28px;
    font-weight: 700;
    font-family: var(--font-face, monospace);
}

.flow-value.positive {
    color: #46a546;
}

.flow-value.negative {
    color: #e9322d;
}

.flow-value.pending {
    color: #f90;
}

.flow-separator {
    font-size: 32px;
    font-weight: 300;
    color: var(--color-text-lighter);
    opacity: 0.7;
}

/* ========================================== */
/* SECTION CONTAINER                          */
/* ========================================== */

.section-container {
    margin-bottom: 48px;
}

.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 16px;
    padding-bottom: 16px;
    border-bottom: 2px solid var(--color-border-dark);
}

.section-header h3 {
    margin: 0;
    font-size: 20px;
    font-weight: 700;
    color: var(--color-main-text);
}

.section-actions {
    display: flex;
    align-items: center;
    gap: 12px;
}

.count-badge {
    background: var(--color-primary-element-light);
    color: var(--color-primary-element-text);
    padding: 6px 14px;
    border-radius: 16px;
    font-size: 13px;
    font-weight: 700;
}

/* ========================================== */
/* DATA TABLE (INVOICES)                      */
/* ========================================== */

.data-table {
    background: var(--color-main-background);
    border: 1px solid var(--color-border);
    border-radius: var(--border-radius-large);
    overflow: hidden;
}

.data-table table {
    width: 100%;
    border-collapse: collapse;
}

.data-table thead {
    background: var(--color-background-dark);
}

.data-table th {
    padding: 14px 16px;
    text-align: left;
    font-size: 12px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 1px;
    color: var(--color-text-lighter);
    border-bottom: 2px solid var(--color-border);
}

.data-table th.sortable {
    cursor: pointer;
    user-select: none;
    transition: background-color 0.2s;
}

.data-table th.sortable:hover {
    background: var(--color-background-hover);
}

.data-table tbody tr {
    border-bottom: 1px solid var(--color-border);
    transition: background-color 0.15s;
}

.data-table tbody tr:last-child {
    border-bottom: none;
}

.data-table tbody tr:hover {
    background: var(--color-background-hover);
}

.data-table td {
    padding: 16px;
    font-size: 14px;
    color: var(--color-main-text);
}

.invoice-number {
    font-weight: 700;
    font-family: var(--font-face, monospace);
    color: var(--color-main-text);
}

.date-range {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 13px;
    color: var(--color-text-lighter);
}

.date-separator {
    opacity: 0.6;
}

.date-range .overdue {
    color: #e9322d;
    font-weight: 700;
}

.amount {
    font-family: var(--font-face, monospace);
    font-weight: 700;
    color: var(--color-main-text);
}

.status-text {
    display: inline-block;
    padding: 6px 12px;
    border-radius: var(--border-radius);
    font-size: 12px;
    font-weight: 700;
    text-transform: capitalize;
}

.status-paid {
    background: #d4edda;
    color: #155724;
}

.status-pending {
    background: #fff3cd;
    color: #856404;
}

.status-overdue {
    background: #f8d7da;
    color: #721c24;
}

.status-draft {
    background: var(--color-background-dark);
    color: var(--color-text-lighter);
}

.status-cancelled {
    background: var(--color-background-dark);
    color: var(--color-text-maxcontrast);
    text-decoration: line-through;
}

.actions {
    display: flex;
    gap: 4px;
    justify-content: flex-end;
}

.text-right {
    text-align: right;
}

/* ========================================== */
/* EXPENSE LIST                               */
/* ========================================== */

.expense-list {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.expense-item {
    background: var(--color-main-background);
    border: 1px solid var(--color-border);
    border-radius: var(--border-radius-large);
    overflow: hidden;
    transition: border-color 0.2s, box-shadow 0.2s;
}

.expense-item:hover {
    border-color: var(--color-primary-element);
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.expense-main {
    display: flex;
    align-items: center;
    padding: 16px;
    gap: 16px;
}

.expense-info {
    flex: 1;
    min-width: 0;
}

.expense-description {
    font-size: 15px;
    font-weight: 700;
    color: var(--color-main-text);
    margin-bottom: 6px;
}

.expense-meta {
    font-size: 13px;
    color: var(--color-text-lighter);
    display: flex;
    align-items: center;
    gap: 8px;
}

.meta-separator {
    opacity: 0.6;
}

.expense-amount {
    font-size: 18px;
    font-weight: 700;
    font-family: var(--font-face, monospace);
    color: #e9322d;
}

.expense-actions {
    display: flex;
    gap: 4px;
}

.expense-details {
    padding: 16px;
    background: var(--color-background-dark);
    border-top: 1px solid var(--color-border);
}

.detail-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 16px;
    margin-bottom: 16px;
}

.detail-item {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.detail-item.full-width {
    grid-column: 1 / -1;
}

.detail-label {
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 1px;
    color: var(--color-text-lighter);
}

.detail-value {
    font-size: 14px;
    color: var(--color-main-text);
    font-weight: 500;
}

.detail-actions {
    display: flex;
    justify-content: flex-end;
    padding-top: 12px;
    border-top: 1px solid var(--color-border);
}

/* ========================================== */
/* EMPTY STATE                                */
/* ========================================== */

.empty-state {
    padding: 64px 24px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 16px;
    color: var(--color-text-lighter);
    text-align: center;
}

.empty-state svg {
    opacity: 0.4;
    color: var(--color-text-maxcontrast);
}

.empty-state p {
    margin: 0;
    font-size: 15px;
    font-weight: 500;
}

/* ========================================== */
/* RESPONSIVE                                 */
/* ========================================== */

@media (max-width: 768px) {
    .financials-view {
        padding: 16px;
    }

    .hero-value {
        font-size: 40px;
    }

    .cash-flow-summary {
        flex-direction: column;
        gap: 20px;
    }

    .flow-separator {
        transform: rotate(90deg);
    }

    .data-table {
        overflow-x: auto;
    }

    .data-table table {
        min-width: 600px;
    }

    .expense-main {
        flex-wrap: wrap;
    }

    .expense-amount {
        width: 100%;
        text-align: right;
    }
}

@media (max-width: 480px) {
    .section-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 12px;
    }

    .section-actions {
        width: 100%;
        justify-content: space-between;
    }
}
</style>
