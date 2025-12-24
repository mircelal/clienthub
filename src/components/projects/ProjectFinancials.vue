<template>
    <div class="financials-view">
        
        <!-- ========================================== -->
        <!-- HEADER & ACTIONS                           -->
        <!-- ========================================== -->
        <div class="nc-section-header">
            <div class="header-left">
                <h2 class="nc-app-title">
                    <CashMultiple :size="24" class="header-icon" />
                    {{ translate('domaincontrol', 'Financials') }}
                </h2>
            </div>
            <div class="header-actions">
                <NcButton type="primary" @click="$emit('create-invoice')">
                    <template #icon>
                        <Plus :size="20" />
                    </template>
                    {{ translate('domaincontrol', 'Create Invoice') }}
                </NcButton>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- KPI WIDGETS (SUMMARY)                      -->
        <!-- ========================================== -->
        <div class="nc-stats-grid">
            <!-- Budget -->
            <div class="nc-stat-widget">
                <div class="widget-icon primary-bg">
                    <Wallet :size="24" />
                </div>
                <div class="widget-content">
                    <span class="widget-label">{{ translate('domaincontrol', 'Total Budget') }}</span>
                    <span class="widget-value font-mono">
                        {{ formatCurrency(project.budget, project.currency) }}
                    </span>
                </div>
            </div>

            <!-- Invoiced -->
            <div class="nc-stat-widget">
                <div class="widget-icon info-bg">
                    <FileDocumentOutline :size="24" />
                </div>
                <div class="widget-content">
                    <span class="widget-label">{{ translate('domaincontrol', 'Total Invoiced') }}</span>
                    <span class="widget-value font-mono">
                        {{ formatCurrency(totalInvoiced, project.currency) }}
                    </span>
                </div>
            </div>

            <!-- Paid -->
            <div class="nc-stat-widget">
                <div class="widget-icon success-bg">
                    <CheckCircle :size="24" />
                </div>
                <div class="widget-content">
                    <span class="widget-label">{{ translate('domaincontrol', 'Collected') }}</span>
                    <span class="widget-value font-mono text-success">
                        {{ formatCurrency(totalPaid, project.currency) }}
                    </span>
                </div>
            </div>

            <!-- Pending -->
            <div class="nc-stat-widget">
                <div class="widget-icon warning-bg">
                    <TimerSand :size="24" />
                </div>
                <div class="widget-content">
                    <span class="widget-label">{{ translate('domaincontrol', 'Pending') }}</span>
                    <span class="widget-value font-mono text-warning">
                        {{ formatCurrency(totalPending, project.currency) }}
                    </span>
                </div>
            </div>

            <!-- Expenses -->
            <div class="nc-stat-widget">
                <div class="widget-icon error-bg">
                    <CashMinus :size="24" />
                </div>
                <div class="widget-content">
                    <span class="widget-label">{{ translate('domaincontrol', 'Total Expenses') }}</span>
                    <span class="widget-value font-mono text-error">
                        {{ formatCurrency(totalExpenses, project.currency) }}
                    </span>
                </div>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- EXPENSES LIST                              -->
        <!-- ========================================== -->
        <div class="nc-section-content" style="margin-bottom: 32px;">
            <div class="nc-list-header-title">
                <h3>{{ translate('domaincontrol', 'Expenses') }}</h3>
                <div style="display: flex; align-items: center; gap: 12px;">
                    <span class="nc-counter-badge">{{ expenses.length }}</span>
                    <NcButton type="secondary" @click="showAddExpenseModal">
                        <template #icon>
                            <Plus :size="18" />
                        </template>
                        {{ translate('domaincontrol', 'Add Expense') }}
                    </NcButton>
                </div>
            </div>

            <div v-if="expenses.length > 0" class="nc-invoice-list">
                <div
                    v-for="expense in expenses"
                    :key="expense.id"
                    class="nc-invoice-card"
                    :class="{ 'is-expanded': expandedExpenses[expense.id] }"
                >
                    <!-- Expense Summary Row -->
                    <div class="invoice-summary" @click="toggleExpenseDetails(expense.id)">
                        <!-- Status Icon -->
                        <div class="col-icon">
                            <div class="status-icon status-error">
                                <CashMinus :size="20" />
                            </div>
                        </div>

                        <!-- Info -->
                        <div class="col-info">
                            <div class="invoice-title">
                                <span class="invoice-number">{{ expense.description || '-' }}</span>
                                <span v-if="expense.categoryName" class="nc-status-badge status-neutral">
                                    {{ expense.categoryName }}
                                </span>
                            </div>
                            <div class="invoice-dates">
                                <span>{{ formatDate(expense.transactionDate) }}</span>
                                <span v-if="expense.paymentMethod" class="arrow-separator"> • </span>
                                <span v-if="expense.paymentMethod">{{ expense.paymentMethod }}</span>
                                <span v-if="expense.reference" class="arrow-separator"> • </span>
                                <span v-if="expense.reference">{{ translate('domaincontrol', 'Ref') }}: {{ expense.reference }}</span>
                            </div>
                        </div>

                        <!-- Amounts -->
                        <div class="col-amounts">
                            <div class="amount-group">
                                <span class="amount-label">{{ translate('domaincontrol', 'Amount') }}</span>
                                <span class="amount-value font-mono text-error">
                                    -{{ formatCurrency(expense.amount || 0, expense.currency || project.currency) }}
                                </span>
                            </div>
                        </div>

                        <!-- Toggle Icon -->
                        <div class="col-toggle">
                            <ChevronUp v-if="expandedExpenses[expense.id]" :size="24" />
                            <ChevronDown v-else :size="24" />
                        </div>
                    </div>

                    <!-- Expense Details (Expanded) -->
                    <div v-if="expandedExpenses[expense.id]" class="invoice-details-panel">
                        <div class="details-content">
                            <!-- Expense Details Section -->
                            <div class="details-section">
                                <h4 class="details-title">
                                    <CashMinus :size="18" />
                                    {{ translate('domaincontrol', 'Expense Details') }}
                                </h4>
                                
                                <div class="details-grid">
                                    <div class="detail-item">
                                        <span class="detail-label">{{ translate('domaincontrol', 'Description') }}</span>
                                        <span class="detail-value">{{ expense.description || '-' }}</span>
                                    </div>
                                    <div class="detail-item">
                                        <span class="detail-label">{{ translate('domaincontrol', 'Amount') }}</span>
                                        <span class="detail-value font-mono text-error">
                                            -{{ formatCurrency(expense.amount || 0, expense.currency || project.currency) }}
                                        </span>
                                    </div>
                                    <div class="detail-item">
                                        <span class="detail-label">{{ translate('domaincontrol', 'Date') }}</span>
                                        <span class="detail-value">{{ formatDate(expense.transactionDate) }}</span>
                                    </div>
                                    <div class="detail-item" v-if="expense.categoryName">
                                        <span class="detail-label">{{ translate('domaincontrol', 'Category') }}</span>
                                        <span class="detail-value">{{ expense.categoryName }}</span>
                                    </div>
                                    <div class="detail-item" v-if="expense.paymentMethod">
                                        <span class="detail-label">{{ translate('domaincontrol', 'Payment Method') }}</span>
                                        <span class="detail-value">{{ expense.paymentMethod }}</span>
                                    </div>
                                    <div class="detail-item" v-if="expense.reference">
                                        <span class="detail-label">{{ translate('domaincontrol', 'Reference') }}</span>
                                        <span class="detail-value">{{ expense.reference }}</span>
                                    </div>
                                    <div class="detail-item" v-if="expense.notes" style="grid-column: 1 / -1;">
                                        <span class="detail-label">{{ translate('domaincontrol', 'Notes') }}</span>
                                        <span class="detail-value">{{ expense.notes }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Footer Actions -->
                            <div class="details-footer">
                                <NcButton type="error" @click.stop="deleteExpense(expense.id)">
                                    <template #icon>
                                        <Delete :size="20" />
                                    </template>
                                    {{ translate('domaincontrol', 'Delete Expense') }}
                                </NcButton>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-else class="nc-empty-state">
                <CashMinus :size="48" class="nc-state-icon" />
                <p>{{ translate('domaincontrol', 'No expenses recorded') }}</p>
                <NcButton type="primary" @click="showAddExpenseModal">
                    {{ translate('domaincontrol', 'Add Expense') }}
                </NcButton>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- INVOICES LIST                              -->
        <!-- ========================================== -->
        <div class="nc-section-content">
            <div class="nc-list-header-title">
                <h3>{{ translate('domaincontrol', 'Invoices') }}</h3>
                <span class="nc-counter-badge">{{ invoices.length }}</span>
            </div>

            <div v-if="invoices.length > 0" class="nc-invoice-list">
                
                <div
                    v-for="invoice in invoices"
                    :key="invoice.id"
                    class="nc-invoice-card"
                    :class="{ 'is-expanded': expandedInvoices[invoice.id] }"
                >
                    <!-- Invoice Summary Row -->
                    <div class="invoice-summary" @click="toggleInvoiceDetails(invoice.id)">
                        <!-- Status Icon -->
                        <div class="col-icon">
                            <div class="status-icon" :class="getInvoiceStatusClass(invoice)">
                                <FileDocument :size="20" />
                            </div>
                        </div>

                        <!-- Info -->
                        <div class="col-info">
                            <div class="invoice-title">
                                <span class="invoice-number">{{ invoice.invoiceNumber || 'Invoice #' + invoice.id }}</span>
                                <span class="nc-status-badge" :class="getInvoiceStatusClass(invoice)">
                                    {{ getInvoiceStatusText(invoice.status) }}
                                </span>
                            </div>
                            <div class="invoice-dates">
                                <span>{{ formatDate(invoice.issueDate) }}</span>
                                <ArrowRight :size="12" class="arrow-separator" />
                                <span :class="{ 'text-error': isOverdue(invoice.dueDate) }">
                                    {{ formatDate(invoice.dueDate) }}
                                </span>
                            </div>
                        </div>

                        <!-- Amounts -->
                        <div class="col-amounts">
                            <div class="amount-group">
                                <span class="amount-label">{{ translate('domaincontrol', 'Total') }}</span>
                                <span class="amount-value font-mono">{{ formatCurrency(invoice.totalAmount || invoice.total || 0, invoice.currency) }}</span>
                            </div>
                            <div class="amount-group desktop-only">
                                <span class="amount-label">{{ translate('domaincontrol', 'Balance') }}</span>
                                <span class="amount-value font-mono" :class="getRemainingAmountClass(invoice)">
                                    {{ formatCurrency(getRemainingAmount(invoice), invoice.currency) }}
                                </span>
                            </div>
                        </div>

                        <!-- Toggle Icon -->
                        <div class="col-toggle">
                            <ChevronUp v-if="expandedInvoices[invoice.id]" :size="24" />
                            <ChevronDown v-else :size="24" />
                        </div>
                    </div>

                    <!-- Invoice Details (Expanded) -->
                    <div v-if="expandedInvoices[invoice.id]" class="invoice-details-panel">
                        
                        <div v-if="loadingInvoiceDetails[invoice.id]" class="nc-loading-state">
                            <Refresh :size="24" class="spin-animation" />
                            <span>{{ translate('domaincontrol', 'Loading details...') }}</span>
                        </div>

                        <div v-else-if="invoiceDetails[invoice.id]" class="details-content">
                            
                            <!-- Items Section -->
                            <div class="details-section">
                                <h4 class="details-title">
                                    <FormatListBulleted :size="18" />
                                    {{ translate('domaincontrol', 'Line Items') }}
                                </h4>
                                
                                <table v-if="invoiceDetails[invoice.id].items?.length > 0" class="nc-table">
                                    <thead>
                                        <tr>
                                            <th>{{ translate('domaincontrol', 'Description') }}</th>
                                            <th class="text-right">{{ translate('domaincontrol', 'Qty') }}</th>
                                            <th class="text-right">{{ translate('domaincontrol', 'Price') }}</th>
                                            <th class="text-right">{{ translate('domaincontrol', 'Total') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="(item, idx) in invoiceDetails[invoice.id].items" :key="idx">
                                            <td>
                                                <div class="item-desc">{{ item.description || '-' }}</div>
                                                <div v-if="item.periodStart" class="item-meta">
                                                    {{ formatDate(item.periodStart) }} - {{ formatDate(item.periodEnd) }}
                                                </div>
                                            </td>
                                            <td class="text-right">{{ item.quantity || 1 }}</td>
                                            <td class="text-right font-mono">{{ formatCurrency(item.unitPrice || 0, invoice.currency) }}</td>
                                            <td class="text-right font-mono font-bold">{{ formatCurrency(item.totalPrice || 0, invoice.currency) }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                                <div v-else class="nc-empty-text">
                                    {{ translate('domaincontrol', 'No items found') }}
                                </div>
                            </div>

                            <!-- Payments Section -->
                            <div class="details-section">
                                <h4 class="details-title">
                                    <CreditCard :size="18" />
                                    {{ translate('domaincontrol', 'Payments') }}
                                </h4>
                                
                                <div v-if="invoiceDetails[invoice.id].payments?.length > 0" class="nc-payments-list">
                                    <div 
                                        v-for="(payment, idx) in invoiceDetails[invoice.id].payments" 
                                        :key="idx" 
                                        class="payment-row"
                                    >
                                        <div class="payment-date">{{ formatDate(payment.paymentDate) }}</div>
                                        <div class="payment-method">{{ payment.paymentMethod || '-' }}</div>
                                        <div class="payment-note">{{ payment.notes }}</div>
                                        <div class="payment-amount font-mono text-success">
                                            +{{ formatCurrency(payment.amount || 0, invoice.currency) }}
                                        </div>
                                    </div>
                                </div>
                                <div v-else class="nc-empty-text">
                                    {{ translate('domaincontrol', 'No payments recorded') }}
                                </div>
                            </div>

                            <!-- Footer Actions -->
                            <div class="details-footer">
                                <NcButton type="secondary" @click.stop="$emit('navigate-invoice', invoice.id)">
                                    <template #icon>
                                        <OpenInNew :size="20" />
                                    </template>
                                    {{ translate('domaincontrol', 'Open Full Invoice') }}
                                </NcButton>
                            </div>

                        </div>
                    </div>
                </div>

            </div>

            <!-- Empty State -->
            <div v-else class="nc-empty-state">
                <FileDocumentOutline :size="48" class="nc-state-icon" />
                <p>{{ translate('domaincontrol', 'No invoices created yet') }}</p>
                <NcButton type="primary" @click="$emit('create-invoice')">
                    {{ translate('domaincontrol', 'Create First Invoice') }}
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
import Refresh from 'vue-material-design-icons/Refresh.vue'
import CashMultiple from 'vue-material-design-icons/CashMultiple.vue' // For Header
import Wallet from 'vue-material-design-icons/Wallet.vue' // For Budget
import FileDocument from 'vue-material-design-icons/FileDocument.vue'
import FileDocumentOutline from 'vue-material-design-icons/FileDocumentOutline.vue'
import CheckCircle from 'vue-material-design-icons/CheckCircle.vue'
import TimerSand from 'vue-material-design-icons/TimerSand.vue'
import ChevronDown from 'vue-material-design-icons/ChevronDown.vue'
import ChevronUp from 'vue-material-design-icons/ChevronUp.vue'
import FormatListBulleted from 'vue-material-design-icons/FormatListBulleted.vue'
import CreditCard from 'vue-material-design-icons/CreditCard.vue'
import ArrowRight from 'vue-material-design-icons/ArrowRight.vue'
import OpenInNew from 'vue-material-design-icons/OpenInNew.vue'
import CashMinus from 'vue-material-design-icons/CashMinus.vue'
import Delete from 'vue-material-design-icons/Delete.vue'

export default {
    name: 'ProjectFinancials',
    components: {
        NcButton,
        // Register Icons
        Plus,
        Refresh,
        CashMultiple,
        Wallet,
        FileDocument,
        FileDocumentOutline,
        CheckCircle,
        TimerSand,
        ChevronDown,
        ChevronUp,
        FormatListBulleted,
        CreditCard,
        ArrowRight,
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
    emits: ['create-invoice', 'navigate-invoice', 'add-expense'],
    data() {
        return {
            expandedInvoices: {},
            invoiceDetails: {},
            loadingInvoiceDetails: {},
            expandedExpenses: {},
        }
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
        formatCurrency(amount, currency = 'USD') {
            if (amount === null || amount === undefined) return '-'
            const val = parseFloat(amount)
            if (isNaN(val)) return '-'
            return new Intl.NumberFormat('tr-TR', {
                style: 'currency',
                currency: currency || 'USD',
            }).format(val)
        },
        formatDate(dateString) {
            if (!dateString) return '-'
            try {
                return new Date(dateString).toLocaleDateString('tr-TR', { year: 'numeric', month: 'short', day: 'numeric' })
            } catch (e) { return dateString }
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
        getInvoiceStatusClass(invoice) {
            if (invoice.status === 'paid') return 'status-success'
            if (invoice.status === 'cancelled') return 'status-neutral'
            if (this.isOverdue(invoice.dueDate)) return 'status-error'
            if (invoice.status === 'sent') return 'status-info'
            return 'status-neutral'
        },
        isOverdue(dueDate) {
            if (!dueDate) return false
            try {
                const due = new Date(dueDate)
                const today = new Date()
                today.setHours(0, 0, 0, 0)
                due.setHours(0, 0, 0, 0)
                return due < today
            } catch (e) { return false }
        },
        getRemainingAmount(invoice) {
            const total = parseFloat(invoice.totalAmount || invoice.total || 0)
            const paid = parseFloat(invoice.paidAmount || 0)
            return Math.max(0, total - paid)
        },
        getRemainingAmountClass(invoice) {
            const remaining = this.getRemainingAmount(invoice)
            if (remaining <= 0) return 'text-success'
            if (this.isOverdue(invoice.dueDate)) return 'text-error'
            return 'text-warning'
        },
        async toggleInvoiceDetails(invoiceId) {
            if (this.expandedInvoices[invoiceId]) {
                this.expandedInvoices = { ...this.expandedInvoices, [invoiceId]: false }
            } else {
                this.expandedInvoices = { ...this.expandedInvoices, [invoiceId]: true }
                if (!this.invoiceDetails[invoiceId]) {
                    await this.loadInvoiceDetails(invoiceId)
                }
            }
        },
        async loadInvoiceDetails(invoiceId) {
            this.loadingInvoiceDetails = { ...this.loadingInvoiceDetails, [invoiceId]: true }
            try {
                const [itemsResponse, paymentsResponse] = await Promise.all([
                    api.invoices.getItems(invoiceId),
                    api.invoices.getPayments(invoiceId),
                ])
                this.invoiceDetails = {
                    ...this.invoiceDetails,
                    [invoiceId]: {
                        items: itemsResponse.data || [],
                        payments: paymentsResponse.data || [],
                    },
                }
            } catch (error) {
                console.error('Error loading invoice details:', error)
                this.invoiceDetails = { ...this.invoiceDetails, [invoiceId]: { items: [], payments: [] } }
            } finally {
                this.loadingInvoiceDetails = { ...this.loadingInvoiceDetails, [invoiceId]: false }
            }
        },
        showAddExpenseModal() {
            this.$emit('add-expense')
        },
        async deleteExpense(expenseId) {
            if (!confirm(this.translate('domaincontrol', 'Are you sure you want to delete this expense?'))) {
                return
            }
            try {
                await api.transactions.delete(expenseId)
                // Close expanded state if open
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
.financials-view {
    padding: 20px;
    color: var(--color-main-text);
    font-family: var(--font-face, sans-serif);
}

/* --- Header --- */
.nc-section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 24px;
}
.header-left { display: flex; align-items: center; }
.nc-app-title { 
    margin: 0; font-size: 24px; font-weight: bold; color: var(--color-main-text);
    display: flex; align-items: center; gap: 12px;
}
.header-icon { color: var(--color-text-maxcontrast); opacity: 0.8; }

/* --- Stats Grid (Widgets) --- */
.nc-stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 16px;
    margin-bottom: 32px;
}

.nc-stat-widget {
    background-color: var(--color-main-background);
    border: 1px solid var(--color-border);
    border-radius: var(--border-radius-large);
    padding: 16px;
    display: flex;
    align-items: center;
    gap: 16px;
    transition: transform 0.2s, border-color 0.2s;
}
.nc-stat-widget:hover {
    border-color: var(--color-primary-element-element-element);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.05);
}

.widget-icon {
    width: 48px; height: 48px; border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    color: var(--color-text-maxcontrast);
    background-color: var(--color-background-hover);
}

/* Widget Colors */
.primary-bg { background-color: var(--color-info); color: var(--color-info-text); }
.success-bg { background-color: var(--color-element-success); color: var(--color-element-success-text); }
.warning-bg { background-color: var(--color-element-warning); color: var(--color-element-warning-text); }
.info-bg { background-color: var(--color-info); color: var(--color-info-text); }
.error-bg { background-color: var(--color-element-error); color: var(--color-element-error-text); }

.widget-content { display: flex; flex-direction: column; min-width: 0; }
.widget-label { font-size: 13px; color: var(--color-text-maxcontrast); margin-bottom: 4px; }
.widget-value { font-size: 18px; font-weight: bold; color: var(--color-main-text); }

/* --- Invoices List --- */
.nc-list-header-title {
    display: flex; align-items: center; gap: 12px; margin-bottom: 16px;
}
.nc-list-header-title h3 { margin: 0; font-size: 18px; font-weight: 600; }
.nc-counter-badge {
    background: var(--color-background-dark); padding: 2px 8px; border-radius: 10px; font-size: 12px; font-weight: bold;
}

.nc-invoice-list {
    display: flex; flex-direction: column; gap: 12px;
}

.nc-invoice-card {
    background: var(--color-main-background);
    border: 1px solid var(--color-border);
    border-radius: var(--border-radius-large);
    overflow: hidden;
    transition: all 0.2s ease;
}
.nc-invoice-card:hover { border-color: var(--color-primary-element-element-element); }
.nc-invoice-card.is-expanded { border-color: var(--color-primary-element-element-element); box-shadow: 0 4px 12px rgba(0,0,0,0.05); }

/* Summary Row */
.invoice-summary {
    display: flex; align-items: center; padding: 16px; cursor: pointer; gap: 16px;
}

/* Columns */
.col-icon { width: 40px; flex-shrink: 0; }
.status-icon {
    width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center;
    background: var(--color-background-hover); color: var(--color-text-maxcontrast);
}
.status-success { background: var(--color-element-success); color: var(--color-element-success-text); }
.status-error { background: var(--color-element-error); color: var(--color-element-error-text); }
.status-info { background: var(--color-info); color: var(--color-info-text); }
.status-neutral { background: var(--color-background-dark); color: var(--color-text-maxcontrast); }

.col-info { flex: 2; display: flex; flex-direction: column; gap: 4px; }
.invoice-title { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
.invoice-number { font-weight: 600; font-size: 16px; color: var(--color-main-text); }
.nc-status-badge {
    padding: 2px 8px; border-radius: 4px; font-size: 11px; font-weight: bold; text-transform: uppercase;
}
.invoice-dates { display: flex; align-items: center; gap: 6px; font-size: 13px; color: var(--color-text-maxcontrast); }
.arrow-separator { opacity: 0.5; }

.col-amounts { flex: 1.5; display: flex; gap: 24px; justify-content: flex-end; align-items: center; text-align: right; }
.amount-group { display: flex; flex-direction: column; }
.amount-label { font-size: 11px; color: var(--color-text-maxcontrast); text-transform: uppercase; }
.amount-value { font-size: 15px; font-weight: 600; color: var(--color-main-text); }

.col-toggle { width: 32px; display: flex; justify-content: center; color: var(--color-text-maxcontrast); }

/* --- Details Panel --- */
.invoice-details-panel {
    background: var(--color-background-hover);
    border-top: 1px solid var(--color-border);
}

.details-content { padding: 24px; display: flex; flex-direction: column; gap: 24px; }
.details-section { display: flex; flex-direction: column; gap: 12px; }
.details-title { margin: 0; font-size: 14px; font-weight: 600; display: flex; align-items: center; gap: 8px; color: var(--color-text-maxcontrast); }

/* Table */
.nc-table { width: 100%; border-collapse: collapse; font-size: 13px; }
.nc-table th { text-align: left; color: var(--color-text-maxcontrast); padding: 8px; border-bottom: 1px solid var(--color-border); font-weight: 600; }
.nc-table td { padding: 12px 8px; border-bottom: 1px solid var(--color-border); vertical-align: top; color: var(--color-main-text); }
.nc-table tr:last-child td { border-bottom: none; }
.text-right { text-align: right; }

.item-desc { font-weight: 500; }
.item-meta { font-size: 11px; color: var(--color-text-maxcontrast); margin-top: 2px; }

/* Payments List */
.nc-payments-list { display: flex; flex-direction: column; gap: 8px; }
.payment-row {
    display: flex; align-items: center; padding: 12px; background: var(--color-main-background);
    border-radius: var(--border-radius); border: 1px solid var(--color-border); font-size: 13px;
}
.payment-date { width: 120px; color: var(--color-text-maxcontrast); }
.payment-method { width: 100px; font-weight: 500; }
.payment-note { flex: 1; color: var(--color-text-maxcontrast); font-style: italic; }
.payment-amount { font-weight: bold; }

.details-footer { display: flex; justify-content: flex-end; padding-top: 12px; border-top: 1px solid var(--color-border); }

/* Loading & Empty */
.nc-loading-state, .nc-empty-state {
    padding: 32px; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 12px; color: var(--color-text-maxcontrast);
}
.nc-empty-text { font-style: italic; color: var(--color-text-maxcontrast); padding: 8px; }
.nc-state-icon { opacity: 0.5; margin-bottom: 8px; }

.spin-animation { animation: spin 1s linear infinite; }
@keyframes spin { 100% { transform: rotate(360deg); } }

/* Utils */
.font-mono { font-family: monospace; }
.font-bold { font-weight: bold; }
.text-success { color: var(--color-element-success) !important; }
.text-warning { color: var(--color-element-warning) !important; }
.text-error { color: var(--color-element-error) !important; }

/* Details Grid */
.details-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 16px;
    margin-top: 12px;
}

.detail-item {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.detail-label {
    font-size: 12px;
    font-weight: 600;
    color: var(--color-text-maxcontrast);
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.detail-value {
    font-size: 14px;
    color: var(--color-main-text);
    word-break: break-word;
}

@media (max-width: 768px) {
    .invoice-summary { flex-wrap: wrap; }
    .col-info { width: 100%; order: 1; flex: none; }
    .col-icon { order: 0; }
    .col-toggle { order: 2; margin-left: auto; }
    .col-amounts { width: 100%; order: 3; justify-content: space-between; margin-top: 8px; padding-top: 8px; border-top: 1px solid var(--color-border); }
    .desktop-only { display: none; }
    .details-grid { grid-template-columns: 1fr; }
}
</style>