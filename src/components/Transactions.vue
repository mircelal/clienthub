<template>
    <div class="nc-app-content">
        <!-- Transaction Modal -->
        <TransactionModal
            :open="modalOpen"
            :transaction="editingTransaction"
            :clients="clients"
            :projects="projects"
            :categories="categories"
            @close="closeModal"
            @saved="handleTransactionSaved"
        />

        <!-- Category Management Modal -->
        <TransactionCategoryManagementModal
            :open="categoryManagementOpen"
            :categories="categories"
            @close="closeCategoryManagement"
            @add="showAddCategory"
            @edit="editCategory"
            @delete="deleteCategory"
        />

        <!-- Category Add/Edit Modal -->
        <TransactionCategoryModal
            :open="categoryModalOpen"
            :category="editingCategory"
            @close="closeCategoryModal"
            @saved="handleCategorySaved"
        />

        <!-- LIST COLUMN (Sol Taraf) -->
        <div class="nc-app-content-list" :class="{ 'mobile-hidden': selectedTransaction }">
            <!-- Header -->
            <div class="app-content-list__header">
                <div class="header-top">
                    <h2 class="header-title">{{ translate('domaincontrol', 'Transactions') }}</h2>
                    <div class="header-actions">
                        <NcButton type="tertiary-no-background" @click="showCategoryManagement" :title="translate('domaincontrol', 'Manage Categories')">
                            <template #icon>
                                <Tag :size="20" />
                            </template>
                        </NcButton>
                        <NcButton type="primary" @click="showAddModal">
                            <template #icon>
                                <Plus :size="20" />
                            </template>
                            {{ translate('domaincontrol', 'Add') }}
                        </NcButton>
                    </div>
                </div>
                
                <div class="search-filter-container">
                    <div class="search-box-wrapper">
                         <MaterialIcon name="magnify" :size="16" class="search-icon" />
                        <input
                            type="text"
                            v-model="searchQuery"
                            class="search-input"
                            :placeholder="translate('domaincontrol', 'Search...')"
                        />
                    </div>

                    <div class="filter-pills">
                        <button class="filter-pill" :class="{ 'active': currentFilter === 'all' }" @click="setFilter('all')">
                            {{ translate('domaincontrol', 'All') }}
                        </button>
                        <button class="filter-pill" :class="{ 'active': currentFilter === 'income' }" @click="setFilter('income')">
                            {{ translate('domaincontrol', 'Income') }}
                        </button>
                        <button class="filter-pill" :class="{ 'active': currentFilter === 'expense' }" @click="setFilter('expense')">
                            {{ translate('domaincontrol', 'Expense') }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- List Content -->
            <div class="app-content-list__items">
                <!-- Loading -->
                <div v-if="loading" class="state-container">
                    <MaterialIcon name="loading" :size="32" class="spin" />
                </div>

                <!-- Empty State -->
                <div v-else-if="filteredTransactions.length === 0" class="state-container">
                    <div class="empty-icon-bg">
                        <MaterialIcon name="file-document-outline" :size="32" />
                    </div>
                    <p class="empty-text">{{ searchQuery ? translate('domaincontrol', 'No transactions found') : translate('domaincontrol', 'No transactions yet') }}</p>
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
                            <MaterialIcon :name="transaction.type === 'income' ? 'arrow-up' : 'arrow-down'" :size="18" />
                        </div>
                        <div class="nc-list-item__content">
                            <div class="nc-list-item__row">
                                <span class="item-title">{{ transaction.description || translate('domaincontrol', 'Transaction') }}</span>
                                <span class="item-amount" :class="`amount-${transaction.type}`">
                                    {{ transaction.type === 'income' ? '+' : '-' }}{{ formatCurrency(transaction.amount) }}
                                </span>
                            </div>
                            <div class="nc-list-item__row secondary">
                                <span class="item-date">{{ formatDate(transaction.transactionDate) }}</span>
                                <span class="item-category" v-if="getCategoryName(transaction.categoryId)">
                                    {{ getCategoryName(transaction.categoryId) }}
                                </span>
                            </div>
                        </div>
                        
                        <!-- Hover Action (More Menu) -->
                        <div class="item-actions" @click.stop>
                            <button class="icon-btn" @click="togglePopover(transaction.id)">
                                <MaterialIcon name="dots-vertical" :size="18" />
                            </button>
                            <!-- Context Menu -->
                             <div v-if="openPopover === transaction.id" class="nc-popover-menu" v-click-outside="closePopover">
                                <button class="nc-popover-item" @click="editTransaction(transaction); closePopover()">
                                    <MaterialIcon name="pencil" :size="16" /> {{ translate('domaincontrol', 'Edit') }}
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

        <!-- DETAILS COLUMN (Sağ Taraf) -->
        <div class="nc-app-content-details" v-if="selectedTransaction">
            <header class="details-header sticky-header">
                <div class="details-header-left">
                    <button class="icon-btn mobile-only" @click="backToList">
                        <MaterialIcon name="arrow-left" :size="20" />
                    </button>
                    <h2 class="details-header-title">{{ translate('domaincontrol', 'Details') }} <span class="id-badge">#{{ selectedTransaction.id }}</span></h2>
                </div>
                <div class="header-actions">
                    <NcButton type="secondary" @click="editTransaction(selectedTransaction)">
                        <template #icon><MaterialIcon name="pencil" :size="16" /></template>
                        {{ translate('domaincontrol', 'Edit') }}
                    </NcButton>
                    <button class="icon-btn danger-hover" @click="confirmDelete(selectedTransaction)" :title="translate('domaincontrol', 'Delete')">
                        <MaterialIcon name="delete" :size="18" />
                    </button>
                </div>
            </header>

            <div class="details-body">
                <div class="details-container">
                    <!-- Compact Hero Amount -->
                    <div class="detail-group highlight-group">
                        <div class="detail-label">{{ getTransactionTypeText(selectedTransaction.type) }}</div>
                        <div class="detail-amount" :class="`text-${selectedTransaction.type}`">
                            {{ formatCurrency(selectedTransaction.amount) }}
                        </div>
                    </div>

                    <!-- Info List -->
                    <div class="detail-list">
                        <div class="detail-row">
                            <span class="label">{{ translate('domaincontrol', 'Description') }}</span>
                            <span class="value strong">{{ selectedTransaction.description || '-' }}</span>
                        </div>
                        <div class="detail-row">
                            <span class="label">{{ translate('domaincontrol', 'Date') }}</span>
                            <span class="value">{{ formatDate(selectedTransaction.transactionDate) }}</span>
                        </div>
                        <div class="detail-row">
                            <span class="label">{{ translate('domaincontrol', 'Category') }}</span>
                            <span class="value">
                                <span class="text-pill" v-if="getCategoryName(selectedTransaction.categoryId)">
                                    {{ getCategoryName(selectedTransaction.categoryId) }}
                                </span>
                                <span v-else>-</span>
                            </span>
                        </div>
                        <div class="detail-row">
                            <span class="label">{{ translate('domaincontrol', 'Client') }}</span>
                            <span class="value">
                                <a v-if="selectedTransaction.clientId" href="#" @click.prevent="navigateToClient(selectedTransaction.clientId)" class="link-styled">
                                    {{ getClientName(selectedTransaction.clientId) }} <MaterialIcon name="open-in-new" :size="12" />
                                </a>
                                <span v-else>-</span>
                            </span>
                        </div>
                        <div class="detail-row">
                            <span class="label">{{ translate('domaincontrol', 'Project') }}</span>
                            <span class="value">
                                 <a v-if="selectedTransaction.projectId" href="#" @click.prevent="navigateToProject(selectedTransaction.projectId)" class="link-styled">
                                    {{ getProjectName(selectedTransaction.projectId) }} <MaterialIcon name="open-in-new" :size="12" />
                                </a>
                                <span v-else>-</span>
                            </span>
                        </div>
                        <div class="detail-row">
                            <span class="label">{{ translate('domaincontrol', 'Payment Method') }}</span>
                            <span class="value">{{ getPaymentMethodText(selectedTransaction.paymentMethod) }}</span>
                        </div>
                        <div class="detail-row" v-if="selectedTransaction.reference">
                            <span class="label">{{ translate('domaincontrol', 'Reference') }}</span>
                            <span class="value">{{ selectedTransaction.reference }}</span>
                        </div>
                    </div>

                    <!-- Invoice Link -->
                    <div class="detail-block-link" v-if="getInvoiceIdFromTransaction(selectedTransaction)">
                        <MaterialIcon name="file-document-outline" :size="20" />
                        <div class="block-content">
                             <a href="#" @click.prevent="navigateToInvoice(getInvoiceIdFromTransaction(selectedTransaction))">
                                {{ translate('domaincontrol', 'View Linked Invoice') }} #{{ getInvoiceIdFromTransaction(selectedTransaction) }}
                            </a>
                        </div>
                    </div>

                    <!-- Notes -->
                    <div class="detail-block" v-if="selectedTransaction.notes && getCleanNotes(selectedTransaction.notes)">
                        <h4>{{ translate('domaincontrol', 'Notes') }}</h4>
                        <div class="notes-text" v-html="getCleanNotes(selectedTransaction.notes)"></div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Statistics View (Dashboard) -->
        <div class="nc-app-content-details statistics-view" v-else>
            <div class="dashboard-container">
                <header class="dashboard-header">
                    <div class="dashboard-title-area">
                        <h1>{{ translate('domaincontrol', 'Financial Overview') }}</h1>
                    </div>
                    
                    <div class="dashboard-controls">
                         <!-- Date Range Filter -->
                         <div class="control-group-wrapper">
                            <div class="select-wrapper">
                                <select v-model="dateRange" class="nc-select">
                                    <option value="all">{{ translate('domaincontrol', 'All Time') }}</option>
                                    <option value="thisMonth">{{ translate('domaincontrol', 'This Month') }}</option>
                                    <option value="lastMonth">{{ translate('domaincontrol', 'Last Month') }}</option>
                                    <option value="last3Days">{{ translate('domaincontrol', 'Last 3 Days') }}</option>
                                    <option value="custom">{{ translate('domaincontrol', 'Custom Range...') }}</option>
                                </select>
                                <MaterialIcon name="calendar-range" :size="16" class="select-icon" />
                            </div>
                            
                            <!-- Custom Date Inputs -->
                            <div v-if="dateRange === 'custom'" class="custom-date-inputs">
                                <input type="date" v-model="customStartDate" class="nc-input-date" :title="translate('domaincontrol', 'Start Date')">
                                <span class="separator">-</span>
                                <input type="date" v-model="customEndDate" class="nc-input-date" :title="translate('domaincontrol', 'End Date')">
                            </div>
                        </div>

                        <!-- Category Filter -->
                        <div class="select-wrapper">
                            <select v-model="dashboardCategoryFilter" class="nc-select">
                                <option value="all">{{ translate('domaincontrol', 'All Categories') }}</option>
                                <option v-for="cat in categories" :key="cat.id" :value="cat.id">
                                    {{ cat.name }}
                                </option>
                                <option value="uncategorized">{{ translate('domaincontrol', 'Uncategorized') }}</option>
                            </select>
                            <MaterialIcon name="tag" :size="16" class="select-icon" />
                        </div>

                        <!-- Type Toggle -->
                        <div class="toggle-group">
                            <button :class="{ 'active': statsFilter === 'all' }" @click="statsFilter = 'all'">
                                {{ translate('domaincontrol', 'All') }}
                            </button>
                            <button :class="{ 'active': statsFilter === 'income' }" @click="statsFilter = 'income'">
                                {{ translate('domaincontrol', 'Income') }}
                            </button>
                            <button :class="{ 'active': statsFilter === 'expense' }" @click="statsFilter = 'expense'">
                                {{ translate('domaincontrol', 'Expense') }}
                            </button>
                        </div>
                    </div>
                </header>
                
                <!-- KPI Cards (Compact) -->
                <div class="kpi-grid">
                    <div class="kpi-card">
                        <div class="kpi-label">{{ translate('domaincontrol', 'Income') }}</div>
                        <div class="kpi-value text-success">{{ formatCurrency(kpiStats.income) }}</div>
                    </div>
                    <div class="kpi-card">
                        <div class="kpi-label">{{ translate('domaincontrol', 'Expense') }}</div>
                        <div class="kpi-value text-error">{{ formatCurrency(kpiStats.expense) }}</div>
                    </div>
                    <div class="kpi-card">
                        <div class="kpi-label">{{ translate('domaincontrol', 'Net Profit') }}</div>
                        <div class="kpi-value" :class="kpiStats.net >= 0 ? 'text-success' : 'text-error'">
                            {{ formatCurrency(kpiStats.net) }}
                        </div>
                    </div>
                </div>

                <!-- Category Breakdown -->
                <div class="dashboard-section">
                    <h3 class="section-header">
                        {{ dashboardCategoryFilter !== 'all' 
                            ? translate('domaincontrol', 'Category Details') 
                            : translate('domaincontrol', 'Breakdown') 
                        }}
                    </h3>
                    
                    <div v-if="!filteredCategoryStats || filteredCategoryStats.length === 0" class="empty-state-simple">
                         <p>{{ translate('domaincontrol', 'No data available for this period') }}</p>
                    </div>
                    
                    <div v-else class="category-table">
                        <div
                            v-for="stat in filteredCategoryStats"
                            :key="stat.categoryId"
                            class="category-row"
                        >
                            <div class="row-main">
                                <div class="cat-name">
                                    <span class="dot" :class="stat.type"></span>
                                    {{ stat.categoryName || translate('domaincontrol', 'Uncategorized') }}
                                </div>
                                <div class="cat-bar-container">
                                    <div class="cat-bar" :class="stat.type" :style="{ width: getCategoryPercentage(stat.total, stat.type) + '%' }"></div>
                                </div>
                            </div>
                            <div class="row-meta">
                                <span class="cat-count">{{ stat.count }}</span>
                                <span class="cat-amount">{{ formatCurrency(stat.total) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import api from '../services/api'
import MaterialIcon from './MaterialIcon.vue'
import TransactionModal from './TransactionModal.vue'
import TransactionCategoryModal from './TransactionCategoryModal.vue'
import TransactionCategoryManagementModal from './TransactionCategoryManagementModal.vue'
import { NcButton } from '@nextcloud/vue'
import Tag from 'vue-material-design-icons/Tag.vue'
import Plus from 'vue-material-design-icons/Plus.vue'
import ArrowUp from 'vue-material-design-icons/ArrowUp.vue'
import ArrowDown from 'vue-material-design-icons/ArrowDown.vue'
import ChartBar from 'vue-material-design-icons/ChartBar.vue'

// Custom directive for clicking outside popover
const clickOutside = {
  beforeMount: (el, binding) => {
    el.clickOutsideEvent = event => {
      if (!(el == event.target || el.contains(event.target))) {
        binding.value(event);
      }
    };
    document.addEventListener("click", el.clickOutsideEvent);
  },
  unmounted: el => {
    document.removeEventListener("click", el.clickOutsideEvent);
  },
};

export default {
    name: 'Transactions',
    directives: { clickOutside },
    components: {
        MaterialIcon,
        TransactionModal,
        TransactionCategoryModal,
        TransactionCategoryManagementModal,
        NcButton,
        Tag,
        Plus,
        ArrowUp,
        ArrowDown,
        ChartBar,
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
            defaultCurrency: 'USD',
            currencies: [],
            categoryModalOpen: false,
            editingCategory: null,
            categoryManagementOpen: false,
            statsFilter: 'all',
            dashboardCategoryFilter: 'all', 
            dateRange: 'thisMonth', // Default date range
            customStartDate: '',
            customEndDate: '',
        }
    },
    computed: {
        filteredTransactions() {
            let filtered = this.transactions

            if (this.currentFilter !== 'all') {
                filtered = filtered.filter(t => t.type === this.currentFilter)
            }

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
            
            return filtered.sort((a, b) => new Date(b.transactionDate) - new Date(a.transactionDate))
        },
        // Dashboard transactions filtered by Category AND Date
        dashboardFilteredTransactions() {
            let filtered = this.transactions;

            // 1. Filter by Category
            if (this.dashboardCategoryFilter !== 'all') {
                if (this.dashboardCategoryFilter === 'uncategorized') {
                     filtered = filtered.filter(t => !t.categoryId);
                } else {
                     filtered = filtered.filter(t => t.categoryId === this.dashboardCategoryFilter);
                }
            }

            // 2. Filter by Date
            if (this.dateRange !== 'all') {
                const now = new Date();
                let start = null;
                let end = null;

                if (this.dateRange === 'thisMonth') {
                    start = new Date(now.getFullYear(), now.getMonth(), 1);
                    end = new Date(now.getFullYear(), now.getMonth() + 1, 0); // End of current month
                } else if (this.dateRange === 'lastMonth') {
                    start = new Date(now.getFullYear(), now.getMonth() - 1, 1);
                    end = new Date(now.getFullYear(), now.getMonth(), 0); // End of last month
                } else if (this.dateRange === 'last3Days') {
                    start = new Date();
                    start.setDate(now.getDate() - 3);
                    end = now;
                } else if (this.dateRange === 'custom') {
                    if (this.customStartDate) start = new Date(this.customStartDate);
                    if (this.customEndDate) end = new Date(this.customEndDate);
                    
                    // Set time to end of day for the end date to include transactions on that day
                    if (end) end.setHours(23, 59, 59, 999);
                }

                if (start) {
                    filtered = filtered.filter(t => new Date(t.transactionDate) >= start);
                }
                if (end) {
                    filtered = filtered.filter(t => new Date(t.transactionDate) <= end);
                }
            }

            return filtered;
        },
        // KPI Stats (Calculated from dashboardFilteredTransactions)
        kpiStats() {
            const income = this.dashboardFilteredTransactions
                .filter(t => t.type === 'income')
                .reduce((sum, t) => sum + (parseFloat(t.amount) || 0), 0);
            
            const expense = this.dashboardFilteredTransactions
                .filter(t => t.type === 'expense')
                .reduce((sum, t) => sum + (parseFloat(t.amount) || 0), 0);
                
            return {
                income,
                expense,
                net: income - expense
            }
        },
        // Category Statistics for Breakdown List
        categoryStatistics() {
            const stats = {}
            
            // Use dashboardFilteredTransactions to respect filters
            this.dashboardFilteredTransactions.forEach(transaction => {
                const categoryId = transaction.categoryId || 'uncategorized'
                const categoryName = this.getCategoryName(transaction.categoryId) || this.translate('domaincontrol', 'Uncategorized')
                const type = transaction.type
                const amount = parseFloat(transaction.amount) || 0
                
                if (!stats[categoryId]) {
                    stats[categoryId] = {
                        categoryId,
                        categoryName,
                        type,
                        count: 0,
                        total: 0,
                    }
                }
                
                stats[categoryId].count++
                stats[categoryId].total += amount
            })
            
            return Object.values(stats).sort((a, b) => b.total - a.total)
        },
        filteredCategoryStats() {
            if (this.statsFilter === 'all') {
                return this.categoryStatistics
            }
            return this.categoryStatistics.filter(stat => stat.type === this.statsFilter)
        },
        // Legacy getters
        totalIncome() { return this.kpiStats.income },
        totalExpense() { return this.kpiStats.expense },
        netProfit() { return this.kpiStats.net },
    },
    mounted() {
        this.loadSettings()
        this.loadData()
        
        // Initialize custom dates with today if needed
        const today = new Date().toISOString().split('T')[0];
        this.customStartDate = today;
        this.customEndDate = today;
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
                this.clients = []
            }
        },
        async loadProjects() {
            try {
                const response = await api.projects.getAll()
                this.projects = response.data || []
            } catch (error) {
                this.projects = []
            }
        },
        async loadCategories() {
            try {
                const response = await api.transactionCategories.getAll()
                this.categories = response.data || []
            } catch (error) {
                this.categories = []
            }
        },
        setFilter(filter) {
            this.currentFilter = filter
        },
        selectTransaction(transaction) {
            this.selectedTransaction = transaction
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
                const updated = this.transactions.find(t => t.id === this.selectedTransaction.id)
                if (updated) this.selectedTransaction = updated
            }
            this.closeModal()
        },
        showCategoryManagement() {
            this.categoryManagementOpen = true
        },
        closeCategoryManagement() {
            this.categoryManagementOpen = false
        },
        showAddCategory() {
            this.editingCategory = null
            this.categoryManagementOpen = false
            this.categoryModalOpen = true
        },
        editCategory(category) {
            this.editingCategory = category
            this.categoryManagementOpen = false
            this.categoryModalOpen = true
        },
        closeCategoryModal() {
            this.categoryModalOpen = false
            this.editingCategory = null
        },
        async handleCategorySaved() {
            await this.loadCategories()
            this.closeCategoryModal()
            this.categoryManagementOpen = true
        },
        async deleteCategory(category) {
            try {
                await api.transactionCategories.delete(category.id)
                await this.loadCategories()
            } catch (error) {
                console.error('Error deleting category:', error)
            }
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
            if (typeof window.DomainControl !== 'undefined' && typeof window.DomainControl.selectInvoice === 'function') {
                window.DomainControl.selectInvoice(invoiceId)
            } else {
                const event = new CustomEvent('navigate-to-invoice', { detail: invoiceId })
                window.dispatchEvent(event)
            }
        },
        getInvoiceIdFromTransaction(transaction) {
            if (!transaction || !transaction.notes) return null
            const match = transaction.notes.match(/\[INVOICE_ID:(\d+)\]/)
            return match ? parseInt(match[1]) : null
        },
        getCleanNotes(notes) {
            if (!notes) return ''
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
        formatDate(date) {
            if (!date) return ''
            const d = new Date(date)
            return d.toLocaleDateString('tr-TR', { day: 'numeric', month: 'short', year: 'numeric' })
        },
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
                'USD': '$', 'EUR': '€', 'TRY': '₺', 'AZN': '₼', 'GBP': '£', 'RUB': '₽',
            }
            return defaultSymbols[currencyCode] || currencyCode
        },
        formatCurrency(amount) {
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
        getCategoryPercentage(amount, type) {
            // Calculate percentage based on the currently filtered totals
            const total = type === 'income' ? this.kpiStats.income : this.kpiStats.expense
            if (!total || total === 0) return 0
            return Math.round((amount / total) * 100)
        },
        translate(appId, text, vars) {
             // Mock translation or window.t implementation
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
            } catch (e) {}
            return text
        },
    },
}
</script>

<style scoped>
/* --- Layout Basics --- */
.nc-app-content {
    display: flex;
    height: 100%;
    width: 100%;
    background-color: var(--color-main-background);
    color: var(--color-main-text);
    overflow: hidden;
}

/* --- Left Column: List --- */
.nc-app-content-list {
    flex: 0 0 300px; /* Reduced width */
    width: 300px;
    border-right: 1px solid var(--color-border);
    display: flex;
    flex-direction: column;
    height: 100%;
    background-color: var(--color-main-background);
    z-index: 50;
}

/* List Header */
.app-content-list__header {
    padding: 12px;
    border-bottom: 1px solid var(--color-border);
    background-color: var(--color-main-background);
    position: sticky;
    top: 0;
    z-index: 10;
}

.header-top {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 12px;
}

.header-title {
    margin: 0;
    font-size: 18px; /* Slightly smaller */
    font-weight: bold;
}

.header-actions {
    display: flex;
    gap: 4px;
}

/* Search Box Styled */
.search-box-wrapper {
    position: relative;
    margin-bottom: 8px;
}

.search-input {
    width: 100%;
    height: 34px; /* Standard NC input height */
    padding: 6px 12px 6px 32px;
    border: 1px solid var(--color-border);
    border-radius: var(--border-radius);
    background-color: var(--color-main-background);
    color: var(--color-main-text);
    box-sizing: border-box;
    transition: all 0.2s;
    font-size: 13px;
}

.search-input:focus {
    border-color: var(--color-primary-element);
    box-shadow: 0 0 0 2px rgba(0, 130, 201, 0.2);
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

/* Filter Pills */
.filter-pills {
    display: flex;
    background-color: var(--color-background-hover);
    padding: 2px;
    border-radius: var(--border-radius);
    gap: 2px;
}

.filter-pill {
    flex: 1;
    border: none;
    background: transparent;
    padding: 4px 8px;
    border-radius: 4px;
    color: var(--color-text-maxcontrast);
    cursor: pointer;
    font-size: 12px;
    font-weight: 500;
    transition: all 0.2s;
}

.filter-pill:hover {
    color: var(--color-main-text);
}

.filter-pill.active {
    background-color: var(--color-main-background);
    color: var(--color-main-text);
    box-shadow: 0 1px 2px rgba(0,0,0,0.08);
    font-weight: 600;
}

/* List Items */
.app-content-list__items {
    flex: 1;
    overflow-y: auto;
}

.nc-list-item {
    display: flex;
    padding: 10px 12px;
    cursor: pointer;
    border-bottom: 1px solid var(--color-border);
    transition: background-color 0.1s;
    height: auto;
    position: relative;
}

.nc-list-item:hover {
    background-color: var(--color-background-hover);
}

.nc-list-item.active {
    background-color: var(--color-primary-light);
}

.nc-list-item.active::before {
    content: '';
    position: absolute;
    left: 0;
    top: 0;
    bottom: 0;
    width: 3px;
    background-color: var(--color-primary-element);
}

.nc-list-item__icon {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 10px;
    flex-shrink: 0;
}

.type-income { background-color: rgba(76, 175, 80, 0.1); color: #43a047; }
.type-expense { background-color: rgba(244, 67, 54, 0.1); color: #e53935; }

.nc-list-item__content {
    flex: 1;
    min-width: 0;
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.nc-list-item__row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    line-height: 1.2;
}

.item-title {
    font-weight: 600;
    color: var(--color-main-text);
    font-size: 13px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    padding-right: 8px;
}

.item-amount {
    font-weight: 600;
    font-size: 13px;
    white-space: nowrap;
}

.amount-income { color: #43a047; }
.amount-expense { color: #e53935; }

.secondary {
    font-size: 11px;
    color: var(--color-text-maxcontrast);
}

.item-actions {
    display: flex;
    align-items: center;
    margin-left: 4px;
    opacity: 0;
    transition: opacity 0.2s;
}

.nc-list-item:hover .item-actions,
.openPopover .item-actions {
    opacity: 1;
}

.icon-btn {
    background: none;
    border: none;
    cursor: pointer;
    padding: 4px;
    border-radius: 50%;
    color: var(--color-text-maxcontrast);
    display: flex;
    align-items: center;
    justify-content: center;
}

.icon-btn:hover {
    background-color: rgba(0,0,0,0.05);
    color: var(--color-main-text);
}
.icon-btn.danger-hover:hover { color: #e53935; background-color: rgba(229, 57, 53, 0.1); }

/* --- Popover Menu --- */
.nc-popover-menu {
    position: absolute;
    right: 8px;
    top: 30px;
    background-color: var(--color-main-background);
    border: 1px solid var(--color-border);
    border-radius: var(--border-radius);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    min-width: 140px;
    z-index: 100;
    padding: 4px;
}

.nc-popover-item {
    display: flex;
    align-items: center;
    gap: 8px;
    width: 100%;
    padding: 8px 12px;
    border: none;
    background: transparent;
    color: var(--color-main-text);
    text-align: left;
    cursor: pointer;
    border-radius: 3px;
    font-size: 13px;
}

.nc-popover-item:hover { background-color: var(--color-background-hover); }
.nc-popover-item.danger { color: #e53935; }
.nc-popover-item.danger:hover { background-color: rgba(229, 57, 53, 0.1); }


/* --- Right Column: Details --- */
.nc-app-content-details {
    flex: 1;
    display: flex;
    flex-direction: column;
    overflow-y: auto;
    background-color: var(--color-main-background);
}

/* Detail Header */
.details-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 20px;
    height: 50px;
    background-color: var(--color-main-background);
    border-bottom: 1px solid var(--color-border);
    flex-shrink: 0;
}

.details-header-left {
    display: flex;
    align-items: center;
    gap: 8px;
}

.details-header-title {
    margin: 0;
    font-size: 16px;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 8px;
}

.id-badge {
    font-size: 11px;
    color: var(--color-text-maxcontrast);
    background: var(--color-background-dark);
    padding: 1px 6px;
    border-radius: 10px;
    font-weight: normal;
}

/* Details Body */
.details-body {
    padding: 20px;
    display: flex;
    justify-content: center;
    align-items: flex-start;
}

.details-container {
    width: 100%;
    max-width: 600px;
}

/* Detail Groups */
.detail-group {
    margin-bottom: 24px;
}

.highlight-group {
    padding-bottom: 16px;
    border-bottom: 1px solid var(--color-border);
}

.detail-label {
    font-size: 12px;
    color: var(--color-text-maxcontrast);
    text-transform: uppercase;
    margin-bottom: 4px;
}

.detail-amount {
    font-size: 24px;
    font-weight: bold;
}

.text-income { color: #43a047; }
.text-expense { color: #e53935; }

/* Detail List (Flat) */
.detail-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
    margin-bottom: 24px;
}

.detail-row {
    display: flex;
    justify-content: space-between;
    align-items: baseline;
    font-size: 14px;
}

.detail-row .label {
    color: var(--color-text-maxcontrast);
    flex-shrink: 0;
    width: 120px;
}

.detail-row .value {
    color: var(--color-main-text);
    text-align: right;
    flex: 1;
}

.detail-row .value.strong {
    font-weight: 600;
}

.text-pill {
    background-color: var(--color-background-dark);
    padding: 2px 8px;
    border-radius: 10px;
    font-size: 12px;
}

.link-styled {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    color: var(--color-primary-element);
    text-decoration: none;
}
.link-styled:hover { text-decoration: underline; }

/* Blocks */
.detail-block-link {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px;
    border: 1px solid var(--color-border);
    border-radius: var(--border-radius);
    margin-bottom: 20px;
    background-color: var(--color-background-hover);
}

.detail-block h4 {
    font-size: 14px;
    font-weight: 600;
    margin: 0 0 8px 0;
    color: var(--color-text-maxcontrast);
}

.notes-text {
    font-size: 14px;
    line-height: 1.5;
    color: var(--color-main-text);
    white-space: pre-wrap;
}

/* --- Dashboard / Statistics --- */
.dashboard-container {
    max-width: 900px;
    margin: 0 auto;
    padding: 20px;
    width: 100%;
    box-sizing: border-box;
}

.dashboard-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 24px;
    border-bottom: 1px solid var(--color-border);
    padding-bottom: 16px;
}

.dashboard-title-area h1 {
    font-size: 22px;
    margin: 0;
    font-weight: bold;
    color: var(--color-main-text);
}

.dashboard-controls {
    display: flex;
    gap: 12px;
    align-items: center;
    flex-wrap: wrap; /* Allow wrapping on small screens */
}

.control-group-wrapper {
    display: flex;
    gap: 8px;
    align-items: center;
}

.custom-date-inputs {
    display: flex;
    align-items: center;
    gap: 6px;
    background-color: var(--color-main-background);
    border: 1px solid var(--color-border);
    border-radius: var(--border-radius);
    padding: 0 8px;
    height: 34px;
}

.nc-input-date {
    border: none;
    background: transparent;
    font-size: 12px;
    color: var(--color-main-text);
    padding: 0;
    width: 95px;
    font-family: inherit;
}

.nc-input-date:focus {
    outline: none;
}

.separator { color: var(--color-text-maxcontrast); }

/* Custom Select */
.select-wrapper {
    position: relative;
    width: 180px;
}

.nc-select {
    width: 100%;
    appearance: none;
    background-color: var(--color-main-background);
    border: 1px solid var(--color-border);
    border-radius: var(--border-radius);
    padding: 6px 28px 6px 28px; /* Added left padding for icon */
    font-size: 13px;
    color: var(--color-main-text);
    cursor: pointer;
    height: 34px;
}

.nc-select:focus {
    border-color: var(--color-primary-element);
    outline: none;
}

.select-icon {
    position: absolute;
    right: 8px;
    top: 50%;
    transform: translateY(-50%);
    pointer-events: none;
    color: var(--color-text-maxcontrast);
}

/* Custom Toggle Buttons */
.toggle-group {
    display: flex;
    background-color: var(--color-background-dark);
    padding: 2px;
    border-radius: var(--border-radius);
    height: 34px;
    box-sizing: border-box;
}

.toggle-group button {
    border: none;
    background: transparent;
    padding: 0 12px;
    border-radius: 4px;
    font-size: 13px;
    font-weight: 500;
    color: var(--color-text-maxcontrast);
    cursor: pointer;
    transition: all 0.2s;
    height: 100%;
}

.toggle-group button.active {
    background-color: var(--color-main-background);
    color: var(--color-main-text);
    box-shadow: 0 1px 2px rgba(0,0,0,0.1);
    font-weight: 600;
}

/* KPI Cards (Compact) */
.kpi-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 16px;
    margin-bottom: 32px;
}

.kpi-card {
    background: var(--color-main-background);
    padding: 16px;
    border-radius: var(--border-radius-large);
    border: 1px solid var(--color-border);
    display: flex;
    flex-direction: column;
}

.kpi-label {
    font-size: 12px;
    color: var(--color-text-maxcontrast);
    margin-bottom: 4px;
    font-weight: 600;
    text-transform: uppercase;
}

.kpi-value {
    font-size: 20px;
    font-weight: bold;
    color: var(--color-main-text);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.text-success { color: #43a047; }
.text-error { color: #e53935; }

/* Dashboard Sections */
.dashboard-section {
    background: var(--color-main-background);
}

.section-header {
    margin: 0 0 16px 0;
    font-size: 16px;
    font-weight: 600;
    color: var(--color-main-text);
}

/* Compact Table Layout for Categories */
.category-table {
    display: flex;
    flex-direction: column;
    gap: 0;
    border: 1px solid var(--color-border);
    border-radius: var(--border-radius);
    overflow: hidden;
}

.category-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 16px;
    border-bottom: 1px solid var(--color-border);
}

.category-row:last-child { border-bottom: none; }

.row-main {
    flex: 1;
    display: flex;
    align-items: center;
    gap: 16px;
}

.cat-name {
    width: 150px;
    font-size: 13px;
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 8px;
}

.dot { width: 8px; height: 8px; border-radius: 50%; }
.dot.income { background-color: #43a047; }
.dot.expense { background-color: #e53935; }

.cat-bar-container {
    flex: 1;
    height: 6px;
    background-color: var(--color-background-dark);
    border-radius: 3px;
    overflow: hidden;
    max-width: 200px;
}

.cat-bar {
    height: 100%;
    border-radius: 3px;
}
.cat-bar.income { background-color: #43a047; }
.cat-bar.expense { background-color: #e53935; }

.row-meta {
    display: flex;
    align-items: center;
    gap: 20px;
    width: 150px;
    justify-content: flex-end;
}

.cat-count {
    font-size: 12px;
    color: var(--color-text-maxcontrast);
    width: 40px;
    text-align: right;
}

.cat-amount {
    font-size: 13px;
    font-weight: 600;
    width: 90px;
    text-align: right;
}

.empty-state-simple {
    padding: 20px;
    text-align: center;
    color: var(--color-text-maxcontrast);
    font-size: 13px;
    border: 1px dashed var(--color-border);
    border-radius: var(--border-radius);
}

/* State Helpers */
.state-container {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 40px 20px;
    color: var(--color-text-maxcontrast);
    text-align: center;
}
.empty-icon-bg {
    color: var(--color-text-maxcontrast);
    opacity: 0.5;
    margin-bottom: 8px;
}
.spin { animation: spin 1s linear infinite; }
@keyframes spin { 100% { transform: rotate(360deg); } }

/* Responsive */
.mobile-only { display: none; }

@media (max-width: 768px) {
    .nc-app-content-list {
        width: 100%;
        flex: 1;
        border-right: none;
    }
    
    .nc-app-content-details {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: 200;
    }
    
    .mobile-hidden { display: none; }
    .mobile-only { display: flex; }
    
    .dashboard-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 16px;
    }
    
    .dashboard-controls {
        width: 100%;
        flex-direction: column;
        align-items: flex-start;
        gap: 12px;
    }
    
    .select-wrapper { width: 100%; }
    
    .kpi-grid { grid-template-columns: 1fr; }
    
    .category-row {
        flex-direction: column;
        align-items: flex-start;
        gap: 8px;
    }
    .row-main { width: 100%; justify-content: space-between; }
    .row-meta { width: 100%; justify-content: space-between; }
    .cat-bar-container { max-width: none; width: 100px; }
}
</style>