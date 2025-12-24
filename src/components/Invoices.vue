<template>
    <div class="invoices-view-container">

        <!-- ========================================== -->
        <!-- MODALS                                     -->
        <!-- ========================================== -->
        <!-- Create/Edit Invoice Modal (Placeholder) -->
        <InvoiceModal
            :open="modalOpen"
            :invoice="editingInvoice"
            :clients="clients"
            @close="closeModal"
            @saved="handleInvoiceSaved"
        />

        <!-- Add Invoice Item Modal -->
        <InvoiceItemModal
            :open="itemModalOpen"
            :invoice-id="selectedInvoice?.id"
            @close="closeItemModal"
            @saved="handleItemSaved"
        />

        <!-- Add Payment Modal -->
        <InvoicePaymentModal
            :open="paymentModalOpen"
            :invoice="selectedInvoice"
            :clients="clients"
            @close="closePaymentModal"
            @paid="handlePaymentSaved"
        />

        <!-- ========================================== -->
        <!-- HEADER & KPI SUMMARY                       -->
        <!-- ========================================== -->
        <div v-if="!selectedInvoice" class="view-header">
            <div class="header-content">
                <h2 class="nc-app-title">
                    <FileDocumentMultiple :size="24" class="header-icon" />
                    {{ translate('domaincontrol', 'Invoices') }}
                </h2>
                <div class="header-actions">
                    <NcButton type="secondary" @click="exportReport">
                        <template #icon>
                            <Download :size="20" />
                        </template>
                        {{ translate('domaincontrol', 'Export Report') }}
                    </NcButton>
                    <NcButton type="primary" @click="showAddModal">
                        <template #icon>
                            <Plus :size="20" />
                        </template>
                        {{ translate('domaincontrol', 'New Invoice') }}
                    </NcButton>
                </div>
            </div>

            <!-- KPI Cards -->
            <div class="kpi-grid">
                <div class="kpi-card">
                    <div class="kpi-icon primary-bg">
                        <ChartLineVariant :size="24" />
                    </div>
                    <div class="kpi-data">
                        <span class="kpi-label">{{ translate('domaincontrol', 'Total Revenue') }}</span>
                        <span class="kpi-value">{{ formatCurrency(stats.totalRevenue) }}</span>
                    </div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-icon warning-bg">
                        <TimerSand :size="24" />
                    </div>
                    <div class="kpi-data">
                        <span class="kpi-label">{{ translate('domaincontrol', 'Outstanding') }}</span>
                        <span class="kpi-value text-warning">{{ formatCurrency(stats.totalOutstanding) }}</span>
                    </div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-icon error-bg">
                        <AlertCircle :size="24" />
                    </div>
                    <div class="kpi-data">
                        <span class="kpi-label">{{ translate('domaincontrol', 'Overdue') }}</span>
                        <span class="kpi-value text-error">{{ formatCurrency(stats.totalOverdue) }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- FILTER BAR                                 -->
        <!-- ========================================== -->
        <div v-if="!selectedInvoice" class="filter-bar">
            <div class="search-wrapper">
                <Magnify :size="20" class="search-icon" />
                <input 
                    type="text" 
                    v-model="searchQuery" 
                    :placeholder="translate('domaincontrol', 'Search invoice #, client or amount...')" 
                    class="nc-input search-input"
                >
            </div>
            
            <div class="filter-actions">
                <div class="filter-pills">
                    <button 
                        v-for="status in ['all', 'paid', 'unpaid', 'overdue', 'draft']" 
                        :key="status"
                        class="filter-pill"
                        :class="{ active: currentFilter === status }"
                        @click="currentFilter = status"
                    >
                        {{ translate('domaincontrol', capitalize(status)) }}
                    </button>
                </div>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- INVOICE DETAIL VIEW                        -->
        <!-- ========================================== -->
        <div v-if="selectedInvoice" class="invoice-detail-view">
            <div class="detail-header">
                <NcButton type="tertiary-no-background" @click="backToList">
                    <template #icon>
                        <ArrowLeft :size="20" />
                    </template>
                    {{ translate('domaincontrol', 'Back') }}
                </NcButton>
                <h2 class="detail-title">
                    {{ selectedInvoice.invoiceNumber }}
                    <span class="detail-subtitle">{{ selectedInvoice.title || translate('domaincontrol', 'General Invoice') }}</span>
                </h2>
                <div class="detail-actions">
                    <NcButton type="secondary" @click="downloadPdf(selectedInvoice)">
                        <template #icon>
                            <FilePdfBox :size="18" />
                        </template>
                        {{ translate('domaincontrol', 'Download PDF') }}
                    </NcButton>
                    <NcButton type="secondary" @click="sendEmail(selectedInvoice)">
                        <template #icon>
                            <Email :size="18" />
                        </template>
                        {{ translate('domaincontrol', 'Send Email') }}
                    </NcButton>
                    <NcActions :primary="true">
                        <NcActionButton @click="editInvoice(selectedInvoice)">
                            <template #icon>
                                <Pencil :size="20" />
                            </template>
                            {{ translate('domaincontrol', 'Edit') }}
                        </NcActionButton>
                        <NcActionButton @click="duplicateInvoice(selectedInvoice)">
                            <template #icon>
                                <ContentCopy :size="20" />
                            </template>
                            {{ translate('domaincontrol', 'Duplicate') }}
                        </NcActionButton>
                        <NcActionSeparator />
                        <NcActionButton @click="deleteInvoice(selectedInvoice)">
                            <template #icon>
                                <Delete :size="20" />
                            </template>
                            {{ translate('domaincontrol', 'Delete') }}
                        </NcActionButton>
                    </NcActions>
                </div>
            </div>

            <div class="detail-content">
                <!-- Invoice Info Cards -->
                <div class="info-cards-grid">
                    <div class="info-card">
                        <div class="info-label">{{ translate('domaincontrol', 'Client') }}</div>
                        <div class="info-value">{{ getClientName(selectedInvoice.clientId) }}</div>
                    </div>
                    <div class="info-card">
                        <div class="info-label">{{ translate('domaincontrol', 'Issue Date') }}</div>
                        <div class="info-value">{{ formatDate(selectedInvoice.issueDate) }}</div>
                    </div>
                    <div class="info-card">
                        <div class="info-label">{{ translate('domaincontrol', 'Due Date') }}</div>
                        <div class="info-value" :class="{ 'text-error': isOverdue(selectedInvoice) }">
                            {{ formatDate(selectedInvoice.dueDate) }}
                        </div>
                    </div>
                    <div class="info-card">
                        <div class="info-label">{{ translate('domaincontrol', 'Status') }}</div>
                        <div class="info-value">
                            <span class="nc-status-badge" :class="getStatusClass(selectedInvoice.status)">
                                {{ getStatusText(selectedInvoice.status) }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Financial Summary -->
                <div class="financial-summary">
                    <div class="summary-row">
                        <span class="summary-label">{{ translate('domaincontrol', 'Total Amount') }}</span>
                        <span class="summary-value font-mono">{{ formatCurrency(selectedInvoice.total || selectedInvoice.totalAmount, selectedInvoice.currency) }}</span>
                    </div>
                    <div class="summary-row">
                        <span class="summary-label">{{ translate('domaincontrol', 'Paid Amount') }}</span>
                        <span class="summary-value font-mono text-success">{{ formatCurrency(selectedInvoice.paidAmount || 0, selectedInvoice.currency) }}</span>
                    </div>
                    <div class="summary-row summary-total">
                        <span class="summary-label">{{ translate('domaincontrol', 'Balance Due') }}</span>
                        <span class="summary-value font-mono" :class="getBalanceClass(selectedInvoice)">
                            {{ formatCurrency(selectedInvoice.balance || (selectedInvoice.totalAmount - selectedInvoice.paidAmount), selectedInvoice.currency) }}
                        </span>
                    </div>
                </div>

                <!-- Invoice Items Section -->
                <div class="items-section">
                    <div class="section-header">
                        <h3 class="section-title">
                            {{ translate('domaincontrol', 'Line Items') }}
                        </h3>
                        <NcButton type="secondary" @click="showAddItemModal">
                            <template #icon>
                                <Plus :size="18" />
                            </template>
                            {{ translate('domaincontrol', 'Add Item') }}
                        </NcButton>
                    </div>
                    
                    <div v-if="loadingInvoiceDetails[selectedInvoice.id]" class="nc-loading-state">
                        <Refresh :size="24" class="spin-animation" />
                        <span>{{ translate('domaincontrol', 'Loading items...') }}</span>
                    </div>
                    
                    <table v-else-if="invoiceDetails[selectedInvoice.id]?.items?.length > 0" class="nc-table">
                        <thead>
                            <tr>
                                <th>{{ translate('domaincontrol', 'Description') }}</th>
                                <th class="text-right">{{ translate('domaincontrol', 'Qty') }}</th>
                                <th class="text-right">{{ translate('domaincontrol', 'Price') }}</th>
                                <th class="text-right">{{ translate('domaincontrol', 'Total') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(item, idx) in invoiceDetails[selectedInvoice.id].items" :key="idx">
                                <td>
                                    <div class="item-desc">{{ item.description || '-' }}</div>
                                    <div v-if="item.periodStart" class="item-meta">
                                        {{ formatDate(item.periodStart) }} - {{ formatDate(item.periodEnd) }}
                                    </div>
                                </td>
                                <td class="text-right">{{ item.quantity || 1 }}</td>
                                <td class="text-right font-mono">{{ formatCurrency(item.unitPrice || 0, selectedInvoice.currency) }}</td>
                                <td class="text-right font-mono font-bold">{{ formatCurrency(item.totalPrice || 0, selectedInvoice.currency) }}</td>
                            </tr>
                        </tbody>
                    </table>
                    <div v-else class="nc-empty-text">
                        {{ translate('domaincontrol', 'No items found') }}
                    </div>
                </div>

                <!-- Payments Section -->
                <div class="payments-section">
                    <div class="section-header">
                        <h3 class="section-title">
                            {{ translate('domaincontrol', 'Payments') }}
                        </h3>
                        <NcButton type="secondary" @click="showAddPaymentModal">
                            <template #icon>
                                <Plus :size="18" />
                            </template>
                            {{ translate('domaincontrol', 'Add Payment') }}
                        </NcButton>
                    </div>
                    
                    <div v-if="loadingInvoiceDetails[selectedInvoice.id]" class="nc-loading-state">
                        <Refresh :size="24" class="spin-animation" />
                        <span>{{ translate('domaincontrol', 'Loading payments...') }}</span>
                    </div>
                    
                    <div v-else-if="invoiceDetails[selectedInvoice.id]?.payments?.length > 0" class="nc-payments-list">
                        <div 
                            v-for="(payment, idx) in invoiceDetails[selectedInvoice.id].payments" 
                            :key="idx" 
                            class="payment-row"
                        >
                            <div class="payment-date">{{ formatDate(payment.paymentDate) }}</div>
                            <div class="payment-method">{{ payment.paymentMethod || '-' }}</div>
                            <div class="payment-note">{{ payment.notes || '-' }}</div>
                            <div class="payment-amount font-mono text-success">
                                +{{ formatCurrency(payment.amount || 0, selectedInvoice.currency) }}
                            </div>
                        </div>
                    </div>
                    <div v-else class="nc-empty-text">
                        {{ translate('domaincontrol', 'No payments recorded') }}
                    </div>
                </div>

                <!-- Notes -->
                <div v-if="selectedInvoice.notes" class="notes-section">
                    <h3 class="section-title">{{ translate('domaincontrol', 'Notes') }}</h3>
                    <div class="notes-content">{{ selectedInvoice.notes }}</div>
                </div>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- INVOICE LIST TABLE                         -->
        <!-- ========================================== -->
        <div v-else class="nc-content-wrapper">
            
            <div v-if="loading" class="nc-loading-state">
                <Refresh :size="48" class="spin-animation nc-state-icon" />
                <p>{{ translate('domaincontrol', 'Loading invoices...') }}</p>
            </div>

            <div v-else-if="filteredInvoices.length === 0" class="nc-empty-state">
                <FileDocumentOutline :size="64" class="nc-state-icon" />
                <h3>{{ translate('domaincontrol', 'No invoices found') }}</h3>
                <p>{{ translate('domaincontrol', 'Create a new invoice to get paid.') }}</p>
                <NcButton type="primary" @click="showAddModal" class="mt-4">
                    {{ translate('domaincontrol', 'Create Invoice') }}
                </NcButton>
            </div>

            <div v-else class="nc-table-container">
                <table class="nc-table">
                    <thead>
                        <tr>
                            <th class="w-icon"></th>
                            <th @click="sortBy('number')" class="sortable">
                                {{ translate('domaincontrol', 'Invoice #') }}
                            </th>
                            <th @click="sortBy('client')" class="sortable">
                                {{ translate('domaincontrol', 'Client') }}
                            </th>
                            <th @click="sortBy('date')" class="sortable">
                                {{ translate('domaincontrol', 'Date / Due') }}
                            </th>
                            <th class="text-right">{{ translate('domaincontrol', 'Amount') }}</th>
                            <th class="text-right">{{ translate('domaincontrol', 'Balance') }}</th>
                            <th class="text-center">{{ translate('domaincontrol', 'Status') }}</th>
                            <th class="w-actions"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr 
                            v-for="invoice in filteredInvoices" 
                            :key="invoice.id"
                            class="nc-table-row"
                            @click="viewInvoice(invoice)"
                        >
                            <!-- Icon -->
                            <td class="w-icon">
                                <div class="row-icon-bg">
                                    <FileDocument :size="20" />
                                </div>
                            </td>

                            <!-- Number & Title -->
                            <td>
                                <div class="cell-primary font-mono">
                                    {{ invoice.invoiceNumber }}
                                </div>
                                <div class="cell-secondary">
                                    {{ invoice.title || translate('domaincontrol', 'General Invoice') }}
                                </div>
                            </td>

                            <!-- Client -->
                            <td>
                                <div class="client-cell">
                                    <Account :size="16" class="sub-icon" />
                                    <span>{{ getClientName(invoice.clientId) }}</span>
                                </div>
                            </td>

                            <!-- Dates -->
                            <td>
                                <div class="cell-primary">{{ formatDate(invoice.issueDate) }}</div>
                                <div class="cell-secondary" :class="{ 'text-error': isOverdue(invoice) }">
                                    {{ translate('domaincontrol', 'Due') }}: {{ formatDate(invoice.dueDate) }}
                                </div>
                            </td>

                            <!-- Amount -->
                            <td class="text-right">
                                <div class="cell-primary font-mono">
                                    {{ formatCurrency(invoice.total, invoice.currency) }}
                                </div>
                            </td>

                            <!-- Balance Due -->
                            <td class="text-right">
                                <div class="cell-primary font-mono" :class="getBalanceClass(invoice)">
                                    {{ formatCurrency(invoice.balance, invoice.currency) }}
                                </div>
                            </td>

                            <!-- Status -->
                            <td class="text-center">
                                <span class="nc-status-badge" :class="getStatusClass(invoice.status)">
                                    {{ getStatusText(invoice.status) }}
                                </span>
                            </td>

                            <!-- Actions -->
                            <td class="w-actions">
                                <div class="row-actions">
                                    <button 
                                        class="action-btn" 
                                        @click.stop="downloadPdf(invoice)" 
                                        :title="translate('domaincontrol', 'Download PDF')"
                                    >
                                        <FilePdfBox :size="18" />
                                    </button>
                                    <button 
                                        class="action-btn" 
                                        @click.stop="sendEmail(invoice)" 
                                        :title="translate('domaincontrol', 'Send Email')"
                                    >
                                        <Email :size="18" />
                                    </button>
                                    
                                    <!-- Context Menu Trigger (Mockup for dropdown) -->
                                    <div class="dropdown-wrapper">
                                        <button class="action-btn" @click.stop="toggleMenu(invoice.id)">
                                            <DotsHorizontal :size="18" />
                                        </button>
                                        <!-- Simple dropdown for demo -->
                                        <div v-if="activeMenu === invoice.id" class="dropdown-menu">
                                            <div class="menu-item" @click.stop="editInvoice(invoice)">
                                                <Pencil :size="16" /> {{ translate('domaincontrol', 'Edit') }}
                                            </div>
                                            <div class="menu-item" @click.stop="duplicateInvoice(invoice)">
                                                <ContentCopy :size="16" /> {{ translate('domaincontrol', 'Duplicate') }}
                                            </div>
                                            <div class="menu-divider"></div>
                                            <div class="menu-item text-error" @click.stop="deleteInvoice(invoice)">
                                                <Delete :size="16" /> {{ translate('domaincontrol', 'Delete') }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination (Mockup) -->
            <div v-if="filteredInvoices.length > 0" class="pagination-bar">
                <span class="pagination-info">
                    {{ translate('domaincontrol', 'Showing {count} invoices', { count: filteredInvoices.length }) }}
                </span>
                <div class="pagination-controls">
                    <NcButton type="tertiary" disabled>
                        <ChevronLeft :size="20" />
                    </NcButton>
                    <NcButton type="tertiary" disabled>
                        <ChevronRight :size="20" />
                    </NcButton>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
// Components
import { NcButton, NcActions, NcActionButton, NcActionSeparator } from '@nextcloud/vue'
import InvoiceModal from './InvoiceModal.vue' // Placeholder for Create/Edit
import InvoiceItemModal from './InvoiceItemModal.vue'
import InvoicePaymentModal from './InvoicePaymentModal.vue'

// Icons
import Plus from 'vue-material-design-icons/Plus.vue'
import FileDocumentMultiple from 'vue-material-design-icons/FileDocumentMultiple.vue'
import FileDocument from 'vue-material-design-icons/FileDocument.vue'
import FileDocumentOutline from 'vue-material-design-icons/FileDocumentOutline.vue'
import Magnify from 'vue-material-design-icons/Magnify.vue'
import Refresh from 'vue-material-design-icons/Refresh.vue'
import ChartLineVariant from 'vue-material-design-icons/ChartLineVariant.vue'
import TimerSand from 'vue-material-design-icons/TimerSand.vue'
import AlertCircle from 'vue-material-design-icons/AlertCircle.vue'
import Account from 'vue-material-design-icons/Account.vue'
import Download from 'vue-material-design-icons/Download.vue'
import FilePdfBox from 'vue-material-design-icons/FilePdfBox.vue'
import Email from 'vue-material-design-icons/Email.vue'
import DotsHorizontal from 'vue-material-design-icons/DotsHorizontal.vue'
import Pencil from 'vue-material-design-icons/Pencil.vue'
import Delete from 'vue-material-design-icons/Delete.vue'
import ContentCopy from 'vue-material-design-icons/ContentCopy.vue'
import ChevronLeft from 'vue-material-design-icons/ChevronLeft.vue'
import ChevronRight from 'vue-material-design-icons/ChevronRight.vue'
import ArrowLeft from 'vue-material-design-icons/ArrowLeft.vue'

import api from '../services/api'

export default {
    name: 'Invoices',
    components: {
        NcButton,
        NcActions,
        NcActionButton,
        NcActionSeparator,
        InvoiceModal,
        InvoiceItemModal,
        InvoicePaymentModal,
        // Icons
        Plus,
        FileDocumentMultiple,
        FileDocument,
        FileDocumentOutline,
        Magnify,
        Refresh,
        ChartLineVariant,
        TimerSand,
        AlertCircle,
        Account,
        Download,
        FilePdfBox,
        Email,
        DotsHorizontal,
        Pencil,
        Delete,
        ContentCopy,
        ChevronLeft,
        ChevronRight,
        ArrowLeft
    },
    data() {
        return {
            invoices: [],
            clients: [],
            loading: false,
            searchQuery: '',
            currentFilter: 'all', // all, paid, unpaid, overdue, draft
            modalOpen: false,
            editingInvoice: null,
            activeMenu: null,
            selectedInvoice: null, // For detail view
            invoiceDetails: {}, // { invoiceId: { items: [], payments: [] } }
            loadingInvoiceDetails: {}, // { invoiceId: boolean }
            itemModalOpen: false, // For add invoice item modal
            paymentModalOpen: false, // For add payment modal
            // Mock Stats
            stats: {
                totalRevenue: 0,
                totalOutstanding: 0,
                totalOverdue: 0
            }
        }
    },
    computed: {
        filteredInvoices() {
            let result = this.invoices

            // Text Filter
            if (this.searchQuery) {
                const query = this.searchQuery.toLowerCase()
                result = result.filter(inv => 
                    inv.invoiceNumber.toLowerCase().includes(query) ||
                    this.getClientName(inv.clientId).toLowerCase().includes(query)
                )
            }

            // Status Filter
            if (this.currentFilter !== 'all') {
                const now = new Date()
                result = result.filter(inv => {
                    if (this.currentFilter === 'paid') return inv.status === 'paid'
                    if (this.currentFilter === 'draft') return inv.status === 'draft'
                    if (this.currentFilter === 'unpaid') return inv.status === 'sent' && inv.balance > 0
                    if (this.currentFilter === 'overdue') {
                        return inv.status !== 'paid' && new Date(inv.dueDate) < now
                    }
                    return true
                })
            }

            return result
        }
    },
    mounted() {
        this.loadData()
        document.addEventListener('click', this.closeMenus)
    },
    beforeUnmount() {
        document.removeEventListener('click', this.closeMenus)
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
        async loadData() {
            this.loading = true
            try {
                // Mock API call simulation
                const [invoicesRes, clientsRes] = await Promise.all([
                    api.invoices.getAll().catch(() => ({ data: [] })),
                    api.clients.getAll().catch(() => ({ data: [] }))
                ])
                
                this.invoices = invoicesRes.data || []
                this.clients = clientsRes.data || []
                
                this.calculateStats()
            } catch (error) {
                console.error('Error loading data:', error)
            } finally {
                this.loading = false
            }
        },
        calculateStats() {
            const now = new Date()
            this.stats.totalRevenue = this.invoices
                .filter(i => i.status === 'paid')
                .reduce((acc, curr) => acc + parseFloat(curr.total), 0)
            
            this.stats.totalOutstanding = this.invoices
                .filter(i => i.status !== 'paid' && i.status !== 'draft' && i.status !== 'cancelled')
                .reduce((acc, curr) => acc + parseFloat(curr.balance), 0)

            this.stats.totalOverdue = this.invoices
                .filter(i => i.status !== 'paid' && i.status !== 'draft' && i.status !== 'cancelled' && new Date(i.dueDate) < now)
                .reduce((acc, curr) => acc + parseFloat(curr.balance), 0)
        },
        formatCurrency(amount, currency = 'USD') {
            if (amount === undefined || amount === null) return '-'
            return new Intl.NumberFormat('tr-TR', { style: 'currency', currency }).format(amount)
        },
        formatDate(date) {
            if (!date) return '-'
            return new Date(date).toLocaleDateString('tr-TR')
        },
        getClientName(id) {
            const client = this.clients.find(c => c.id === id)
            return client ? client.name : 'Unknown Client'
        },
        isOverdue(invoice) {
            if (invoice.status === 'paid' || invoice.status === 'cancelled') return false
            return new Date(invoice.dueDate) < new Date()
        },
        getStatusClass(status) {
            const map = {
                paid: 'status-success',
                sent: 'status-info',
                draft: 'status-neutral',
                overdue: 'status-error',
                cancelled: 'status-neutral'
            }
            return map[status] || 'status-neutral'
        },
        getStatusText(status) {
            const texts = {
                paid: 'Ödendi',
                sent: 'Gönderildi',
                draft: 'Taslak',
                overdue: 'Gecikmiş',
                cancelled: 'İptal'
            }
            return texts[status] || status
        },
        getBalanceClass(invoice) {
            if (invoice.balance <= 0) return 'text-success'
            return ''
        },
        capitalize(s) {
            if (!s) return ''
            return s.charAt(0).toUpperCase() + s.slice(1)
        },
        
        // Actions
        showAddModal() {
            this.editingInvoice = null
            this.modalOpen = true
        },
        editInvoice(invoice) {
            this.editingInvoice = { ...invoice }
            this.modalOpen = true
            this.activeMenu = null
        },
        viewInvoice(invoice) {
            this.selectedInvoice = invoice
            if (invoice && invoice.id) {
                this.loadInvoiceDetails(invoice.id)
            }
        },
        backToList() {
            this.selectedInvoice = null
            this.invoiceDetails = {}
            this.loadingInvoiceDetails = {}
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
        showAddItemModal() {
            if (!this.selectedInvoice || !this.selectedInvoice.id) return
            this.itemModalOpen = true
        },
        closeItemModal() {
            this.itemModalOpen = false
        },
        async handleItemSaved() {
            if (this.selectedInvoice && this.selectedInvoice.id) {
                await this.loadInvoiceDetails(this.selectedInvoice.id)
                // Reload invoice to get updated total
                await this.loadData()
            }
        },
        showAddPaymentModal() {
            if (!this.selectedInvoice || !this.selectedInvoice.id) return
            this.paymentModalOpen = true
        },
        closePaymentModal() {
            this.paymentModalOpen = false
        },
        async handlePaymentSaved() {
            if (this.selectedInvoice && this.selectedInvoice.id) {
                await this.loadInvoiceDetails(this.selectedInvoice.id)
                // Reload invoice to get updated paid amount and balance
                await this.loadData()
            }
        },
        closeModal() {
            this.modalOpen = false
            this.editingInvoice = null
        },
        handleInvoiceSaved() {
            this.loadData()
            this.closeModal()
        },
        async downloadPdf(invoice) {
            try {
                const response = await api.invoices.downloadPdf(invoice.id)
                // Check if response is a blob (PDF file)
                if (response.data instanceof Blob) {
                    const url = window.URL.createObjectURL(response.data)
                    const link = document.createElement('a')
                    link.href = url
                    link.download = `invoice-${invoice.invoiceNumber || invoice.id}.pdf`
                    document.body.appendChild(link)
                    link.click()
                    document.body.removeChild(link)
                    window.URL.revokeObjectURL(url)
                } else {
                    // Backend returns JSON (not implemented yet)
                    console.log('PDF data:', response.data)
                    alert(this.translate('domaincontrol', 'PDF download feature coming soon'))
                }
            } catch (error) {
                console.error('Error downloading PDF:', error)
                alert(this.translate('domaincontrol', 'Error downloading PDF') + ': ' + (error.response?.data?.error || error.message))
            }
        },
        async sendEmail(invoice) {
            try {
                const client = this.clients.find(c => c.id === invoice.clientId)
                if (!client || !client.email) {
                    alert(this.translate('domaincontrol', 'Client email address is not available. Please add email address to the client.'))
                    return
                }
                
                const response = await api.invoices.sendEmail(invoice.id, { email: client.email })
                if (response.data && response.data.success) {
                    alert(this.translate('domaincontrol', 'Email sent successfully to {email}', { email: client.email }))
                } else {
                    alert(this.translate('domaincontrol', 'Email sending feature coming soon'))
                }
            } catch (error) {
                console.error('Error sending email:', error)
                alert(this.translate('domaincontrol', 'Error sending email') + ': ' + (error.response?.data?.error || error.message))
            }
        },
        async duplicateInvoice(invoice) {
            this.activeMenu = null
            try {
                const response = await api.invoices.duplicate(invoice.id)
                if (response.data) {
                    this.loadData()
                    this.translate('domaincontrol', 'Invoice duplicated successfully')
                }
            } catch (error) {
                console.error('Error duplicating invoice:', error)
                alert(this.translate('domaincontrol', 'Error duplicating invoice'))
            }
        },
        async deleteInvoice(invoice) {
            if(confirm(this.translate('domaincontrol', 'Are you sure you want to delete this invoice?'))) {
                try {
                    await api.invoices.delete(invoice.id)
                    this.loadData()
                    if (this.selectedInvoice && this.selectedInvoice.id === invoice.id) {
                        this.selectedInvoice = null
                    }
                } catch (error) {
                    console.error('Error deleting invoice:', error)
                    alert(this.translate('domaincontrol', 'Error deleting invoice'))
                }
                this.activeMenu = null
            }
        },
        async exportReport() {
            try {
                const response = await api.invoices.exportReport()
                // TODO: Handle CSV/Excel download when implemented
                console.log('Export data:', response.data)
                alert(this.translate('domaincontrol', 'Export feature coming soon'))
            } catch (error) {
                console.error('Error exporting report:', error)
                alert(this.translate('domaincontrol', 'Error exporting report'))
            }
        },
        
        // UI Helpers
        toggleMenu(id) {
            this.activeMenu = this.activeMenu === id ? null : id
        },
        closeMenus() {
            this.activeMenu = null
        },
        sortBy(column) {
            // Simple sort logic placeholder
            console.log('Sorting by', column)
        }
    }
}
</script>

<style scoped>
/* MAIN LAYOUT */
.invoices-view-container {
    padding: 24px;
    height: 100%;
    overflow-y: auto;
    background-color: var(--color-main-background);
    color: var(--color-main-text);
    font-family: var(--font-face, sans-serif);
}

/* --- Header & KPI --- */
.view-header {
    margin-bottom: 24px;
}

.header-content {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin: 0 0 24px 30px;
}

.nc-app-title {
    margin: 0;
    font-size: 24px;
    font-weight: bold;
    display: flex;
    align-items: center;
    gap: 12px;
}
.header-icon { opacity: 0.8; }

.header-actions {
    display: flex;
    gap: 8px;
}

/* KPI Cards */
.kpi-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
    gap: 16px;
}

.kpi-card {
    background: var(--color-main-background);
    border: 1px solid var(--color-border);
    border-radius: var(--border-radius-large);
    padding: 16px;
    display: flex;
    align-items: center;
    gap: 16px;
    transition: transform 0.2s, box-shadow 0.2s;
}
.kpi-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    border-color: var(--color-primary-element-element-element);
}

.kpi-icon {
    width: 48px; height: 48px;
    border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    color: var(--color-text-maxcontrast);
}

.primary-bg { background: var(--color-info); color: var(--color-info-text); }
.warning-bg { background: var(--color-element-warning); color: var(--color-element-warning-text); }
.error-bg { background: var(--color-element-error); color: var(--color-element-error-text); }

.kpi-data { display: flex; flex-direction: column; }
.kpi-label { font-size: 13px; color: var(--color-text-maxcontrast); text-transform: uppercase; letter-spacing: 0.5px; }
.kpi-value { font-size: 20px; font-weight: bold; margin-top: 4px; }

/* --- Filter Bar --- */
.filter-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    flex-wrap: wrap;
    gap: 16px;
}

.search-wrapper {
    position: relative;
    width: 300px;
}
.search-icon {
    position: absolute;
    left: 10px;
    top: 50%;
    transform: translateY(-50%);
    color: var(--color-text-maxcontrast);
    opacity: 0.7;
}
.search-input {
    width: 100%;
    padding: 8px 12px 8px 36px;
    border: 1px solid var(--color-border);
    border-radius: var(--border-radius);
    background: var(--color-main-background);
    color: var(--color-main-text);
}
.search-input:focus {
    border-color: var(--color-primary-element-element);
    outline: none;
}

.filter-pills {
    display: flex;
    gap: 8px;
    background: var(--color-background-hover);
    padding: 4px;
    border-radius: var(--border-radius);
}

.filter-pill {
    background: none;
    border: none;
    padding: 6px 16px;
    border-radius: var(--border-radius);
    font-size: 13px;
    font-weight: 500;
    color: var(--color-text-maxcontrast);
    cursor: pointer;
    transition: all 0.2s;
}
.filter-pill.active {
    background: var(--color-main-background);
    color: var(--color-main-text);
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    font-weight: 600;
}
.filter-pill:hover:not(.active) {
    color: var(--color-main-text);
}

/* --- Table --- */
.nc-table-container {
    background: var(--color-main-background);
    border: 1px solid var(--color-border);
    border-radius: var(--border-radius-large);
    overflow: hidden; /* For rounded corners */
}

.nc-table {
    width: 100%;
    border-collapse: collapse;
}

.nc-table th {
    background: var(--color-background-hover);
    padding: 12px 16px;
    text-align: left;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
    color: var(--color-text-maxcontrast);
    border-bottom: 1px solid var(--color-border);
    white-space: nowrap;
}
.sortable { cursor: pointer; user-select: none; }
.sortable:hover { color: var(--color-main-text); }

.nc-table-row {
    border-bottom: 1px solid var(--color-border);
    cursor: pointer;
    transition: background 0.1s;
}
.nc-table-row:last-child { border-bottom: none; }
.nc-table-row:hover { background: var(--color-background-hover); }

.nc-table td {
    padding: 12px 16px;
    vertical-align: middle;
    font-size: 14px;
    color: var(--color-main-text);
}

.nc-table td.text-right {
    text-align: right !important;
}

.nc-table th.text-right {
    text-align: right !important;
}

/* Cell Styles */
.w-icon { width: 48px; text-align: center; }
.w-actions { width: 120px; }
.row-icon-bg {
    width: 36px; height: 36px; border-radius: 8px;
    background: var(--color-background-dark);
    display: flex; align-items: center; justify-content: center;
    color: var(--color-text-maxcontrast);
}

.cell-primary { font-weight: 600; font-size: 14px; }
.cell-secondary { font-size: 12px; color: var(--color-text-maxcontrast); margin-top: 2px; }

.client-cell { display: flex; align-items: center; gap: 8px; font-weight: 500; }
.sub-icon { color: var(--color-text-maxcontrast); opacity: 0.7; }

.font-mono { font-family: monospace; }
.text-right { text-align: right; }
.text-center { text-align: center; }
.text-error { color: var(--color-element-error); }
.text-warning { color: var(--color-element-warning); }
.mt-4 { margin-top: 16px; }

/* Status Badges */
.nc-status-badge {
    padding: 4px 10px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    display: inline-block;
}
.status-success { background: var(--color-element-success); color: var(--color-element-success-text); }
.status-error { background: var(--color-element-error); color: var(--color-element-error-text); }
.status-info { background: var(--color-info); color: var(--color-info-text); }
.status-neutral { background: var(--color-background-dark); color: var(--color-text-maxcontrast); border: 1px solid var(--color-border); }

/* Row Actions */
.row-actions {
    display: flex;
    justify-content: flex-end;
    gap: 4px;
    opacity: 0.6;
    transition: opacity 0.2s;
}
.nc-table-row:hover .row-actions { opacity: 1; }

.action-btn {
    background: none;
    border: none;
    padding: 6px;
    border-radius: 4px;
    color: var(--color-text-maxcontrast);
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
}
.action-btn:hover {
    background: var(--color-background-dark);
    color: var(--color-main-text);
}

/* Context Menu */
.dropdown-wrapper { position: relative; }
.dropdown-menu {
    position: absolute;
    right: 0;
    top: 100%;
    background: var(--color-main-background);
    border: 1px solid var(--color-border);
    border-radius: var(--border-radius);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    z-index: 100;
    min-width: 140px;
    padding: 4px;
    margin-top: 4px;
}
.menu-item {
    padding: 8px 12px;
    font-size: 13px;
    display: flex;
    align-items: center;
    gap: 8px;
    cursor: pointer;
    border-radius: var(--border-radius);
    color: var(--color-main-text);
}
.menu-item:hover { background: var(--color-background-hover); }
.menu-divider { height: 1px; background: var(--color-border); margin: 4px 0; }

/* Empty & Loading */
.nc-empty-state, .nc-loading-state {
    padding: 60px;
    text-align: center;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 16px;
    color: var(--color-text-maxcontrast);
}
.nc-state-icon { opacity: 0.5; }
.spin-animation { animation: spin 1s linear infinite; }

/* Pagination */
.pagination-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 16px;
    border-top: 1px solid var(--color-border);
    background: var(--color-main-background);
}
.pagination-info { font-size: 13px; color: var(--color-text-maxcontrast); }
.pagination-controls { display: flex; gap: 8px; }

@keyframes spin { 100% { transform: rotate(360deg); } }

/* Invoice Detail View */
.invoice-detail-view {
    padding: 24px;
    color: var(--color-main-text);
}

.detail-header {
    display: flex;
    align-items: center;
    gap: 16px;
    margin-bottom: 32px;
    margin-left: 30px;
}

.detail-title {
    flex: 1;
    margin: 0;
    font-size: 24px;
    font-weight: bold;
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.detail-subtitle {
    font-size: 14px;
    font-weight: normal;
    color: var(--color-text-maxcontrast);
}

.detail-actions {
    display: flex;
    gap: 8px;
    align-items: center;
}

.detail-content {
    margin-left: 30px;
}

.info-cards-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 16px;
    margin-bottom: 32px;
}

.info-card {
    background: var(--color-main-background);
    border: 1px solid var(--color-border);
    border-radius: var(--border-radius-large);
    padding: 16px;
}

.info-label {
    font-size: 12px;
    color: var(--color-text-maxcontrast);
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 8px;
}

.info-value {
    font-size: 16px;
    font-weight: 600;
    color: var(--color-main-text);
}

.financial-summary {
    background: var(--color-main-background);
    border: 1px solid var(--color-border);
    border-radius: var(--border-radius-large);
    padding: 24px;
    margin-bottom: 32px;
}

.summary-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 0;
    border-bottom: 1px solid var(--color-border);
}

.summary-row:last-child {
    border-bottom: none;
}

.summary-row.summary-total {
    padding-top: 16px;
    margin-top: 8px;
    border-top: 2px solid var(--color-border);
    font-size: 18px;
    font-weight: bold;
}

.summary-label {
    font-size: 14px;
    color: var(--color-text-maxcontrast);
}

.summary-value {
    font-size: 16px;
    font-weight: 600;
    color: var(--color-main-text);
}

.text-success {
    color: var(--color-element-success);
}

.notes-section {
    background: var(--color-main-background);
    border: 1px solid var(--color-border);
    border-radius: var(--border-radius-large);
    padding: 24px;
}

.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 16px;
}

.section-title {
    margin: 0;
    font-size: 18px;
    font-weight: 600;
    color: var(--color-main-text);
}

.notes-content {
    color: var(--color-text-maxcontrast);
    line-height: 1.6;
    white-space: pre-wrap;
}

/* Items and Payments Sections */
.items-section,
.payments-section {
    background: var(--color-main-background);
    border: 1px solid var(--color-border);
    border-radius: var(--border-radius-large);
    padding: 24px;
    margin-bottom: 24px;
}

.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 16px;
}

.item-desc {
    font-weight: 500;
    color: var(--color-main-text);
}

.item-meta {
    font-size: 12px;
    color: var(--color-text-maxcontrast);
    margin-top: 4px;
}

.nc-payments-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.payment-row {
    display: grid;
    grid-template-columns: 120px 1fr 1fr auto;
    gap: 16px;
    align-items: center;
    padding: 12px;
    background: var(--color-background-hover);
    border-radius: var(--border-radius);
}

.payment-date {
    font-weight: 500;
    color: var(--color-main-text);
}

.payment-method {
    color: var(--color-text-maxcontrast);
    font-size: 13px;
}

.payment-note {
    color: var(--color-text-maxcontrast);
    font-size: 13px;
}

.payment-amount {
    font-weight: 600;
    font-size: 16px;
}

.nc-empty-text {
    text-align: center;
    padding: 32px;
    color: var(--color-text-maxcontrast);
    font-size: 14px;
}

.font-bold {
    font-weight: 700;
}

/* Responsive */
@media (max-width: 768px) {
    .kpi-grid { grid-template-columns: 1fr; }
    .filter-bar { flex-direction: column; align-items: stretch; }
    .search-wrapper { width: 100%; }
    .filter-pills { overflow-x: auto; }
    
    .nc-table th:nth-child(4), .nc-table td:nth-child(4), /* Date */
    .nc-table th:nth-child(6), .nc-table td:nth-child(6) /* Balance */
    { display: none; }
}
</style>