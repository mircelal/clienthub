<template>
    <div class="nc-app-content">
        <!-- Transaction Modal (Değişmedi) -->
        <TransactionModal
            :open="modalOpen"
            :transaction="editingTransaction"
            :clients="clients"
            :projects="projects"
            :categories="categories"
            @close="closeModal"
            @saved="handleTransactionSaved"
        />

        <!-- LIST COLUMN (Sol Taraf) -->
        <div class="nc-app-content-list" :class="{ 'mobile-hidden': selectedTransaction }">
            <!-- Header: Search & Filters & Add Button -->
            <div class="app-content-list__header">
                <div class="header-main-actions">
                    <h2 class="header-title">{{ translate('domaincontrol', 'Transactions') }}</h2>
                    <button class="nc-button nc-button--primary" @click="showAddModal" :title="translate('domaincontrol', 'Add Transaction')">
                        <MaterialIcon name="add" :size="20" />
                        <span class="button-text">{{ translate('domaincontrol', 'Add') }}</span>
                    </button>
                </div>
                
                <div class="search-box-wrapper">
                     <MaterialIcon name="search" :size="20" class="search-icon" />
                    <input
                        type="text"
                        v-model="searchQuery"
                        class="search-input"
                        :placeholder="translate('domaincontrol', 'Search...')"
                        @input="filterTransactions"
                    />
                </div>

                <div class="filter-tabs">
                    <button
                        class="filter-tab"
                        :class="{ 'active': currentFilter === 'all' }"
                        @click="setFilter('all')"
                    >
                        {{ translate('domaincontrol', 'All') }}
                    </button>
                    <button
                        class="filter-tab"
                        :class="{ 'active': currentFilter === 'income' }"
                        @click="setFilter('income')"
                    >
                        {{ translate('domaincontrol', 'Income') }}
                    </button>
                    <button
                        class="filter-tab"
                        :class="{ 'active': currentFilter === 'expense' }"
                        @click="setFilter('expense')"
                    >
                        {{ translate('domaincontrol', 'Expense') }}
                    </button>
                </div>
            </div>

            <!-- List Content -->
            <div class="app-content-list__items">
                <!-- Loading -->
                <div v-if="loading" class="loading-state">
                    <MaterialIcon name="loading" :size="32" class="spin" />
                </div>

                <!-- Empty State -->
                <div v-else-if="filteredTransactions.length === 0" class="empty-state">
                    <MaterialIcon name="accounting" :size="64" class="empty-icon" />
                    <p>{{ searchQuery ? translate('domaincontrol', 'No transactions found') : translate('domaincontrol', 'No transactions yet') }}</p>
                </div>

                <!-- Transactions List -->
                <div v-else class="transaction-list-wrapper">
                    <div
                        v-for="transaction in filteredTransactions"
                        :key="transaction.id"
                        class="nc-list-item"
                        :class="{ 'active': selectedTransaction && selectedTransaction.id === transaction.id }"
                        @click="selectTransaction(transaction)"
                    >
                        <div class="nc-list-item__icon" :class="`type-${transaction.type}`">
                            <MaterialIcon :name="transaction.type === 'income' ? 'arrow-up' : 'arrow-down'" :size="20" />
                        </div>
                        <div class="nc-list-item__content">
                            <div class="nc-list-item__primary">
                                {{ transaction.description || translate('domaincontrol', 'Transaction') }}
                            </div>
                            <div class="nc-list-item__secondary">
                                <span class="date">{{ formatDate(transaction.transactionDate) }}</span>
                                <span v-if="getClientName(transaction.clientId)" class="separator">•</span>
                                <span v-if="getClientName(transaction.clientId)">{{ getClientName(transaction.clientId) }}</span>
                            </div>
                        </div>
                        <div class="nc-list-item__end">
                            <div class="amount" :class="`amount-${transaction.type}`">
                                {{ transaction.type === 'income' ? '+' : '-' }}{{ formatCurrency(transaction.amount, transaction.currency) }}
                            </div>
                            <div class="action-menu-wrapper" @click.stop>
                                <button class="icon-action" @click.stop="togglePopover(transaction.id)">
                                    <MaterialIcon name="more-vertical" :size="20" />
                                </button>
                                <!-- Inline Popover -->
                                <div v-if="openPopover === transaction.id" class="nc-popover-menu">
                                    <button class="nc-popover-item" @click="editTransaction(transaction); closePopover()">
                                        <MaterialIcon name="edit" :size="16" /> {{ translate('domaincontrol', 'Edit') }}
                                    </button>
                                    <button class="nc-popover-item danger" @click="confirmDelete(transaction); closePopover()">
                                        <MaterialIcon name="delete" :size="16" /> {{ translate('domaincontrol', 'Delete') }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- DETAILS COLUMN (Sağ Taraf) -->
        <div class="nc-app-content-details" v-if="selectedTransaction">
            <header class="details-header">
                <button class="icon-action mobile-only" @click="backToList">
                    <MaterialIcon name="arrow-left" :size="20" />
                </button>
                <h2 class="details-title">{{ translate('domaincontrol', 'Details') }}</h2>
                <div class="header-actions">
                    <button class="icon-action" @click="editTransaction(selectedTransaction)" :title="translate('domaincontrol', 'Edit')">
                        <MaterialIcon name="edit" :size="20" />
                    </button>
                    <button class="icon-action" @click="confirmDelete(selectedTransaction)" :title="translate('domaincontrol', 'Delete')">
                        <MaterialIcon name="delete" :size="20" />
                    </button>
                </div>
            </header>

            <div class="details-body">
                <!-- Big Amount Display -->
                <div class="hero-amount" :class="`amount-${selectedTransaction.type}`">
                    <div class="hero-icon" :class="`type-${selectedTransaction.type}`">
                        <MaterialIcon :name="selectedTransaction.type === 'income' ? 'add' : 'minus'" :size="32" />
                    </div>
                    <div class="amount-value">
                        {{ formatCurrency(selectedTransaction.amount, selectedTransaction.currency) }}
                    </div>
                    <div class="amount-label">
                        {{ getTransactionTypeText(selectedTransaction.type) }}
                    </div>
                </div>

                <!-- Info List -->
                <div class="details-section">
                    <div class="detail-row">
                        <div class="detail-label">{{ translate('domaincontrol', 'Description') }}</div>
                        <div class="detail-value text-bold">{{ selectedTransaction.description || '-' }}</div>
                    </div>

                    <div class="detail-row">
                        <div class="detail-label">{{ translate('domaincontrol', 'Date') }}</div>
                        <div class="detail-value">{{ formatDate(selectedTransaction.transactionDate) }}</div>
                    </div>

                    <div class="detail-row">
                        <div class="detail-label">{{ translate('domaincontrol', 'Category') }}</div>
                        <div class="detail-value">
                            <span class="tag-badge" v-if="getCategoryName(selectedTransaction.categoryId)">
                                {{ getCategoryName(selectedTransaction.categoryId) }}
                            </span>
                            <span v-else>-</span>
                        </div>
                    </div>

                    <div class="detail-row">
                        <div class="detail-label">{{ translate('domaincontrol', 'Client') }}</div>
                        <div class="detail-value">
                            <a v-if="selectedTransaction.clientId" href="#" @click.prevent="navigateToClient(selectedTransaction.clientId)">
                                {{ getClientName(selectedTransaction.clientId) }}
                            </a>
                            <span v-else>-</span>
                        </div>
                    </div>

                    <div class="detail-row">
                        <div class="detail-label">{{ translate('domaincontrol', 'Project') }}</div>
                        <div class="detail-value">
                             <a v-if="selectedTransaction.projectId" href="#" @click.prevent="navigateToProject(selectedTransaction.projectId)">
                                {{ getProjectName(selectedTransaction.projectId) }}
                            </a>
                            <span v-else>-</span>
                        </div>
                    </div>

                    <div class="detail-row">
                        <div class="detail-label">{{ translate('domaincontrol', 'Payment Method') }}</div>
                        <div class="detail-value">{{ getPaymentMethodText(selectedTransaction.paymentMethod) }}</div>
                    </div>

                    <div class="detail-row" v-if="selectedTransaction.reference">
                        <div class="detail-label">{{ translate('domaincontrol', 'Reference') }}</div>
                        <div class="detail-value">{{ selectedTransaction.reference }}</div>
                    </div>

                    <div class="detail-row" v-if="getInvoiceIdFromTransaction(selectedTransaction)">
                        <div class="detail-label">{{ translate('domaincontrol', 'Invoice') }}</div>
                        <div class="detail-value">
                            <a href="#" @click.prevent="navigateToInvoice(getInvoiceIdFromTransaction(selectedTransaction))" class="link-primary">
                                {{ translate('domaincontrol', 'View Invoice') }} #{{ getInvoiceIdFromTransaction(selectedTransaction) }}
                            </a>
                        </div>
                    </div>
                </div>

                <div class="details-section" v-if="selectedTransaction.notes && getCleanNotes(selectedTransaction.notes)">
                    <h3 class="section-title">{{ translate('domaincontrol', 'Notes') }}</h3>
                    <div class="notes-content" v-html="getCleanNotes(selectedTransaction.notes)"></div>
                </div>
            </div>
        </div>
        
        <!-- Empty Details State (Masaüstü için) -->
        <div class="nc-app-content-details empty-details" v-else>
            <MaterialIcon name="information-outline" :size="48" color="var(--color-text-maxcontrast)" />
            <p>{{ translate('domaincontrol', 'Select a transaction to view details') }}</p>
        </div>
    </div>
</template>

<script>
import api from '../services/api'
import MaterialIcon from './MaterialIcon.vue'
import TransactionModal from './TransactionModal.vue'

export default {
    name: 'Transactions',
    components: {
        MaterialIcon,
        TransactionModal,
    },
    data() {
        return {
            transactions: [],
            clients: [],
            projects: [],
            categories: [],
            selectedTransaction: null,
            loading: false,
            modalOpen: false,
            editingTransaction: null,
            currentFilter: 'all',
            searchQuery: '',
            openPopover: null,
        }
    },
    computed: {
        filteredTransactions() {
            let filtered = this.transactions

            // Apply type filter
            if (this.currentFilter !== 'all') {
                filtered = filtered.filter(t => t.type === this.currentFilter)
            }

            // Apply search query
            if (this.searchQuery) {
                const query = this.searchQuery.toLowerCase()
                filtered = filtered.filter(transaction => {
                    const description = (transaction.description || '').toLowerCase()
                    const categoryName = this.getCategoryName(transaction.categoryId) || ''
                    const clientName = this.getClientName(transaction.clientId) || ''
                    return (
                        description.includes(query) ||
                        categoryName.toLowerCase().includes(query) ||
                        clientName.toLowerCase().includes(query)
                    )
                })
            }
            
            // Sort by date desc (Genellikle en yeni en üstte istenir)
            return filtered.sort((a, b) => new Date(b.transactionDate) - new Date(a.transactionDate))
        },
    },
    mounted() {
        this.loadData()
        document.addEventListener('click', this.handleClickOutside)
    },
    beforeUnmount() {
        document.removeEventListener('click', this.handleClickOutside)
    },
    methods: {
        async loadData() {
            this.loading = true
            try {
                await Promise.all([
                    this.loadTransactions(),
                    this.loadClients(),
                    this.loadProjects(),
                    this.loadCategories(),
                ])
            } catch (error) {
                console.error('Error loading data:', error)
            } finally {
                this.loading = false
            }
        },
        async loadTransactions() {
            try {
                const response = await api.transactions.getAll()
                this.transactions = response.data || []
            } catch (error) {
                console.error('Error loading transactions:', error)
                this.transactions = []
            }
        },
        async loadClients() {
            try {
                const response = await api.clients.getAll()
                this.clients = response.data || []
            } catch (error) {
                console.error('Error loading clients:', error)
                this.clients = []
            }
        },
        async loadProjects() {
            try {
                const response = await api.projects.getAll()
                this.projects = response.data || []
            } catch (error) {
                console.error('Error loading projects:', error)
                this.projects = []
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
        filterTransactions() {
            // Computed property handles filtering
        },
        setFilter(filter) {
            this.currentFilter = filter
        },
        selectTransaction(transaction) {
            this.selectedTransaction = transaction
            // Mobile navigation logic can be handled here if needed
        },
        backToList() {
            this.selectedTransaction = null
        },
        showAddModal() {
            this.editingTransaction = null
            this.modalOpen = true
        },
        editTransaction(transaction) {
            this.editingTransaction = transaction
            this.modalOpen = true
        },
        closeModal() {
            this.modalOpen = false
            this.editingTransaction = null
        },
        async handleTransactionSaved() {
            await this.loadTransactions()
            if (this.selectedTransaction) {
                // Refresh selected item data
                const updated = this.transactions.find(t => t.id === this.selectedTransaction.id)
                if (updated) this.selectedTransaction = updated
            }
            this.closeModal()
        },
        async confirmDelete(transaction) {
            if (!confirm(this.translate('domaincontrol', 'Are you sure you want to delete this transaction?'))) {
                return
            }
            try {
                await api.transactions.delete(transaction.id)
                await this.loadTransactions()
                if (this.selectedTransaction && this.selectedTransaction.id === transaction.id) {
                    this.backToList()
                }
            } catch (error) {
                console.error('Error deleting transaction:', error)
                alert(this.translate('domaincontrol', 'Error deleting transaction'))
            }
        },
        getClientName(clientId) {
            if (!clientId) return ''
            const client = this.clients.find(c => c.id === clientId)
            return client ? client.name : ''
        },
        getProjectName(projectId) {
            if (!projectId) return ''
            const project = this.projects.find(p => p.id === projectId)
            return project ? project.name : ''
        },
        getCategoryName(categoryId) {
            if (!categoryId) return ''
            const category = this.categories.find(c => c.id === categoryId)
            return category ? category.name : ''
        },
        navigateToClient(clientId) {
            if (typeof window.DomainControl !== 'undefined' && window.DomainControl.switchTab) {
                window.DomainControl.switchTab('clients')
                setTimeout(() => {
                    const event = new CustomEvent('select-client', { detail: { clientId } })
                    window.dispatchEvent(event)
                }, 100)
            }
        },
        navigateToProject(projectId) {
            if (typeof window.DomainControl !== 'undefined' && window.DomainControl.switchTab) {
                window.DomainControl.switchTab('projects')
                setTimeout(() => {
                    const event = new CustomEvent('select-project', { detail: { projectId } })
                    window.dispatchEvent(event)
                }, 100)
            }
        },
        navigateToInvoice(invoiceId) {
            // Use window.DomainControl.selectInvoice if available
            if (typeof window.DomainControl !== 'undefined' && typeof window.DomainControl.selectInvoice === 'function') {
                window.DomainControl.selectInvoice(invoiceId)
            } else {
                // Fallback: Dispatch event to App.vue to switch tab and select invoice
                const event = new CustomEvent('navigate-to-invoice', { detail: invoiceId })
                window.dispatchEvent(event)
            }
        },
        getInvoiceIdFromTransaction(transaction) {
            if (!transaction || !transaction.notes) return null
            // Look for [INVOICE_ID:xxx] pattern in notes
            const match = transaction.notes.match(/\[INVOICE_ID:(\d+)\]/)
            return match ? parseInt(match[1]) : null
        },
        getCleanNotes(notes) {
            if (!notes) return ''
            // Remove [INVOICE_ID:xxx] pattern from notes for display
            return notes.replace(/\s*\[INVOICE_ID:\d+\]\s*/g, '').trim()
        },
        getTransactionTypeText(type) {
            const types = {
                income: this.translate('domaincontrol', 'Income'),
                expense: this.translate('domaincontrol', 'Expense'),
            }
            return types[type] || type
        },
        getPaymentMethodText(method) {
            const methods = {
                cash: this.translate('domaincontrol', 'Cash'),
                bank: this.translate('domaincontrol', 'Bank Transfer'),
                credit_card: this.translate('domaincontrol', 'Credit Card'),
                debit_card: this.translate('domaincontrol', 'Debit Card'),
                online: this.translate('domaincontrol', 'Online Payment'),
                other: this.translate('domaincontrol', 'Other'),
            }
            return methods[method] || method
        },
        togglePopover(transactionId) {
            this.openPopover = this.openPopover === transactionId ? null : transactionId
        },
        closePopover() {
            this.openPopover = null
        },
        handleClickOutside(event) {
            if (this.openPopover && !event.target.closest('.action-menu-wrapper')) {
                this.closePopover()
            }
        },
        formatDate(date) {
            if (!date) return ''
            const d = new Date(date)
            return d.toLocaleDateString('tr-TR', { day: 'numeric', month: 'long', year: 'numeric' })
        },
        formatCurrency(amount, currency = 'USD') {
            if (amount === null || amount === undefined) return '-'
            const formatter = new Intl.NumberFormat('tr-TR', {
                style: 'currency',
                currency: currency || 'USD',
            })
            return formatter.format(amount)
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
            
            // Fallback translations
             const translations = {
                'Add Transaction': 'İşlem Ekle',
                'Search...': 'Ara...',
                'No transactions found': 'İşlem bulunamadı',
                'No transactions yet': 'Henüz işlem yok',
                'Add': 'Ekle',
                'Transactions': 'İşlemler',
                'All': 'Tümü',
                'Income': 'Gelir',
                'Expense': 'Gider',
                'Details': 'Detaylar',
                'Edit': 'Düzenle',
                'Delete': 'Sil',
                'Back': 'Geri',
                'Transaction': 'İşlem',
                'Category': 'Kategori',
                'Date': 'Tarih',
                'Client': 'Müşteri',
                'Description': 'Açıklama',
                'Project': 'Proje',
                'Notes': 'Notlar',
                'Reference': 'Referans',
                'Select a transaction to view details': 'Detayları görüntülemek için bir işlem seçin',
            }
            return translations[text] || text
        },
    },
}
</script>

<style scoped>
/* Base Layout - Nextcloud AppContent Structure */
.nc-app-content {
    display: flex;
    height: 100%;
    width: 100%;
    background-color: var(--color-main-background);
    overflow: hidden;
}

/* LIST VIEW */
.nc-app-content-list {
    flex: 0 0 350px; /* Fixed width for list on desktop */
    min-width: 300px;
    max-width: 450px;
    border-right: 1px solid var(--color-border);
    display: flex;
    flex-direction: column;
    height: 100%;
    background-color: var(--color-main-background);
    transition: transform 0.3s ease;
    z-index: 50;
}

.app-content-list__header {
    padding: 16px;
    border-bottom: 1px solid var(--color-border);
    background-color: var(--color-main-background);
    z-index: 10;
}

.header-main-actions {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 12px;
}

.header-title {
    margin: 0;
    font-size: 20px;
    font-weight: bold;
    color: var(--color-main-text);
}

/* Nextcloud Style Button */
.nc-button {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 16px;
    border-radius: var(--border-radius-pill, 20px);
    border: none;
    font-weight: 500;
    cursor: pointer;
    transition: opacity 0.2s;
}

.nc-button--primary {
    background-color: var(--color-primary-element);
    color: var(--color-primary-text);
}

.nc-button:hover {
    opacity: 0.9;
}

/* Search Box */
.search-box-wrapper {
    position: relative;
    margin-bottom: 12px;
}

.search-input {
    width: 100%;
    padding: 8px 12px 8px 36px;
    border: 1px solid var(--color-border);
    border-radius: var(--border-radius-large, 10px);
    background-color: var(--color-background-dark);
    color: var(--color-main-text);
    box-sizing: border-box;
}

.search-input:focus {
    border-color: var(--color-primary-element);
    outline: none;
}

.search-icon {
    position: absolute;
    left: 10px;
    top: 50%;
    transform: translateY(-50%);
    opacity: 0.5;
    pointer-events: none;
}

/* Filter Tabs */
.filter-tabs {
    display: flex;
    background-color: var(--color-background-dark);
    padding: 4px;
    border-radius: var(--border-radius-large, 10px);
}

.filter-tab {
    flex: 1;
    border: none;
    background: transparent;
    padding: 6px;
    border-radius: var(--border-radius-large, 8px);
    color: var(--color-text-maxcontrast);
    cursor: pointer;
    font-size: 13px;
    font-weight: 500;
    transition: all 0.2s;
}

.filter-tab.active {
    background-color: var(--color-main-background);
    color: var(--color-main-text);
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

/* List Items Area */
.app-content-list__items {
    flex: 1;
    overflow-y: auto;
    padding: 0;
}

.nc-list-item {
    display: flex;
    align-items: center;
    padding: 12px 16px;
    cursor: pointer;
    border-bottom: 1px solid var(--color-border);
    transition: background-color 0.2s;
    height: 64px; /* Standard NC list item height */
    box-sizing: border-box;
}

.nc-list-item:hover, .nc-list-item.active {
    background-color: var(--color-background-hover);
}

.nc-list-item.active {
    border-left: 4px solid var(--color-primary-element);
    padding-left: 12px; /* Compensate for border */
}

.nc-list-item__icon {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 12px;
    flex-shrink: 0;
}

.type-income {
    background-color: rgba(76, 175, 80, 0.1); /* Light green */
    color: var(--color-success, #4caf50);
}

.type-expense {
    background-color: rgba(244, 67, 54, 0.1); /* Light red */
    color: var(--color-error, #f44336);
}

.nc-list-item__content {
    flex: 1;
    min-width: 0;
    display: flex;
    flex-direction: column;
    justify-content: center;
}

.nc-list-item__primary {
    font-weight: 600;
    color: var(--color-main-text);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    font-size: 15px;
}

.nc-list-item__secondary {
    font-size: 13px;
    color: var(--color-text-maxcontrast);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    margin-top: 2px;
}

.separator {
    margin: 0 4px;
}

.nc-list-item__end {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-left: 8px;
}

.amount {
    font-weight: bold;
    font-size: 14px;
    white-space: nowrap;
}

.amount-income { color: var(--color-success, #4caf50); }
.amount-expense { color: var(--color-error, #f44336); }

/* Action Menu */
.icon-action {
    background: transparent;
    border: none;
    border-radius: 50%;
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    color: var(--color-text-maxcontrast);
    opacity: 0.7;
}

.icon-action:hover {
    background-color: var(--color-background-dark);
    opacity: 1;
}

.action-menu-wrapper {
    position: relative;
}

.nc-popover-menu {
    position: absolute;
    right: 0;
    top: 100%;
    background-color: var(--color-main-background);
    border: 1px solid var(--color-border);
    border-radius: var(--border-radius-large, 8px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.2);
    min-width: 150px;
    z-index: 100;
    padding: 4px;
    margin-top: 4px;
}

.nc-popover-item {
    display: flex;
    align-items: center;
    gap: 10px;
    width: 100%;
    padding: 10px;
    border: none;
    background: transparent;
    color: var(--color-main-text);
    text-align: left;
    cursor: pointer;
    border-radius: var(--border-radius-large, 4px);
}

.nc-popover-item:hover {
    background-color: var(--color-background-hover);
}

.nc-popover-item.danger {
    color: var(--color-error, #f44336);
}

/* DETAILS VIEW */
.nc-app-content-details {
    flex: 1;
    display: flex;
    flex-direction: column;
    overflow-y: auto;
    background-color: var(--color-main-background);
}

.empty-details {
    align-items: center;
    justify-content: center;
    color: var(--color-text-maxcontrast);
}

.details-header {
    display: flex;
    align-items: center;
    padding: 16px 24px;
    border-bottom: 1px solid var(--color-border);
    height: 64px;
    box-sizing: border-box;
}

.details-title {
    flex: 1;
    margin: 0;
    font-size: 18px;
    font-weight: bold;
    color: var(--color-main-text);
}

.header-actions {
    display: flex;
    gap: 8px;
}

.details-body {
    padding: 24px;
    max-width: 800px;
    margin: 0 auto;
    width: 100%;
    box-sizing: border-box;
}

.hero-amount {
    display: flex;
    flex-direction: column;
    align-items: center;
    margin-bottom: 32px;
    padding: 24px;
    background-color: var(--color-background-dark);
    border-radius: var(--border-radius-large, 12px);
}

.hero-icon {
    width: 64px;
    height: 64px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 16px;
    color: #fff;
}

.hero-icon.type-income { background-color: var(--color-success, #4caf50); }
.hero-icon.type-expense { background-color: var(--color-error, #f44336); }

.amount-value {
    font-size: 32px;
    font-weight: 800;
}

.amount-label {
    margin-top: 4px;
    color: var(--color-text-maxcontrast);
    text-transform: uppercase;
    font-size: 12px;
    letter-spacing: 1px;
}

/* Detail Rows */
.details-section {
    margin-bottom: 32px;
}

.detail-row {
    display: flex;
    padding: 16px 0;
    border-bottom: 1px solid var(--color-border);
}

.detail-row:last-child {
    border-bottom: none;
}

.detail-label {
    flex: 0 0 150px;
    color: var(--color-text-maxcontrast);
    font-size: 14px;
}

.detail-value {
    flex: 1;
    color: var(--color-main-text);
    font-size: 15px;
}

.text-bold {
    font-weight: 600;
}

.tag-badge {
    background-color: var(--color-primary-light, rgba(0, 130, 201, 0.1));
    color: var(--color-primary-element);
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 13px;
}

.section-title {
    font-size: 16px;
    font-weight: 600;
    margin-bottom: 12px;
    color: var(--color-text-maxcontrast);
}

.notes-content {
    background-color: var(--color-background-dark);
    padding: 16px;
    border-radius: var(--border-radius-large, 8px);
    line-height: 1.6;
    color: var(--color-main-text);
}

.link-primary {
    color: var(--color-primary-element);
    text-decoration: none;
}
.link-primary:hover { text-decoration: underline; }

/* Spin Animation */
.spin {
    animation: spin 1s linear infinite;
}
@keyframes spin { 100% { transform: rotate(360deg); } }

.loading-state {
    padding: 20px;
    text-align: center;
    color: var(--color-text-maxcontrast);
}

/* Mobile Responsiveness */
.mobile-only { display: none; }

@media (max-width: 768px) {
    .nc-app-content-list {
        flex: 1;
        width: 100%;
        max-width: none;
    }
    
    .nc-app-content-details {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: 100;
        background-color: var(--color-main-background);
    }
    
    .mobile-hidden {
        display: none;
    }
    
    .mobile-only {
        display: flex;
        margin-right: 12px;
    }

    .detail-row {
        flex-direction: column;
        gap: 4px;
    }
    
    .detail-label {
        flex: none;
        font-size: 13px;
    }
}
</style>