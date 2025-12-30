<template>
    <div class="app-content-wrapper">
        <!-- ========================================== -->
        <!-- MODALS                                     -->
        <!-- ========================================== -->
        <TransactionModal
            :open="modalOpen"
            :transaction="editingTransaction"
            :clients="clients"
            :projects="projects"
            :categories="categories"
            @close="closeModal"
            @saved="handleTransactionSaved"
        />

        <TransactionCategoryManagementModal
            :open="categoryManagementOpen"
            :categories="categories"
            @close="closeCategoryManagement"
            @add="showAddCategory"
            @edit="editCategory"
            @delete="deleteCategory"
        />

        <TransactionCategoryModal
            :open="categoryModalOpen"
            :category="editingCategory"
            @close="closeCategoryModal"
            @saved="handleCategorySaved"
        />

        <!-- ========================================== -->
        <!-- LEFT PANE: TRANSACTION LIST                -->
        <!-- ========================================== -->
        <div class="app-content-list" :class="{ 'mobile-hidden': isMobile && selectedTransaction }">
            <div class="app-content-list-header">
                <div class="search-wrapper">
                    <div class="search-wrapper-inner">
                        <Magnify :size="20" class="search-icon" />
                        <input 
                            type="text" 
                            v-model="searchQuery" 
                            :placeholder="translate('domaincontrol', 'İşlem ara...')" 
                            class="search-input" 
                        />
                    </div>
                </div>
                <div class="app-navigation__search">
                    <header class="header">
                        <div class="header-action-buttons">
                            <NcButton 
                                type="secondary" 
                                :wide="true"
                                @click="showCategoryManagement"
                                :title="translate('domaincontrol', 'Kategorileri Yönet')">
                                <template #icon>
                                    <Tag :size="20" />
                                </template>
                                {{ translate('domaincontrol', 'Kategoriler') }}
                            </NcButton>
                            <NcButton 
                                type="secondary" 
                                :wide="true"
                                @click="showAddModal"
                                :title="translate('domaincontrol', 'Yeni İşlem Ekle')">
                                <template #icon>
                                    <Plus :size="20" />
                                </template>
                                {{ translate('domaincontrol', 'Yeni') }}
                            </NcButton>
                        </div>
                        
                        <div class="filter-pills-container">
                            <div class="filter-pills">
                                <button class="filter-pill" :class="{ 'active': currentFilter === 'all' }" @click="setFilter('all')">
                                    {{ translate('domaincontrol', 'Tümü') }}
                                </button>
                                <button class="filter-pill" :class="{ 'active': currentFilter === 'income' }" @click="setFilter('income')">
                                    {{ translate('domaincontrol', 'Gelir') }}
                                </button>
                                <button class="filter-pill" :class="{ 'active': currentFilter === 'expense' }" @click="setFilter('expense')">
                                    {{ translate('domaincontrol', 'Gider') }}
                                </button>
                            </div>
                        </div>
                    </header>
                </div>
            </div>

            <div class="app-content-list-wrapper">
                <div v-if="loading" class="loading-container">
                    <Refresh :size="32" class="spin-animation" />
                </div>
                <div v-else-if="filteredTransactions.length === 0" class="empty-list">
                    <div class="empty-icon-bg">
                         <FileDocumentOutline :size="32" />
                    </div>
                    <div class="empty-text">
                        {{ searchQuery ? translate('domaincontrol', 'İşlem bulunamadı') : translate('domaincontrol', 'Henüz işlem yok') }}
                    </div>
                </div>
                <ul v-else class="app-navigation-list">
                    <li 
                        v-for="transaction in filteredTransactions" 
                        :key="transaction.id" 
                        class="app-navigation-entry" 
                        :class="{ 'active': selectedTransaction && selectedTransaction.id === transaction.id }" 
                        @click="selectTransaction(transaction)"
                    >
                        <div class="app-navigation-entry-icon">
                            <div class="avatar-circle transaction-avatar" :class="`type-${transaction.type}`">
                                <MaterialIcon :name="transaction.type === 'income' ? 'arrow-up' : 'arrow-down'" :size="18" />
                            </div>
                        </div>
                        <div class="app-navigation-entry-content">
                            <div class="app-navigation-entry-name">
                                {{ transaction.description || translate('domaincontrol', 'İşlem') }}
                            </div>
                            <div class="app-navigation-entry-details">
                                <span>{{ formatDate(transaction.transactionDate) }}</span>
                                <span v-if="getCategoryName(transaction.categoryId)" class="dot-separator">•</span>
                                <span v-if="getCategoryName(transaction.categoryId)">{{ getCategoryName(transaction.categoryId) }}</span>
                            </div>
                        </div>
                        <div class="app-navigation-entry-status">
                            <span class="item-amount" :class="`amount-${transaction.type}`">
                                {{ transaction.type === 'income' ? '+' : '-' }}{{ formatCurrency(transaction.amount) }}
                            </span>
                        </div>
                    </li>
                </ul>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- RIGHT PANE: DETAIL OR DASHBOARD            -->
        <!-- ========================================== -->
        <div class="app-content-details" :class="{ 'mobile-hidden': isMobile && !selectedTransaction }">
            
            <!-- 1. TRANSACTION DETAIL VIEW -->
            <div v-if="selectedTransaction" class="crm-detail-container">
                <!-- HEADER -->
                <div class="crm-header">
                    <div class="crm-header-top">
                        <button v-if="isMobile" class="icon-button back-button" @click="backToList">
                            <ArrowLeft :size="24" />
                        </button>
                        <div class="crm-profile-info">
                            <div class="avatar-xl transaction-avatar-xl" :class="`type-${selectedTransaction.type}`">
                                <MaterialIcon :name="selectedTransaction.type === 'income' ? 'arrow-up' : 'arrow-down'" :size="36" />
                            </div>
                            <div class="crm-profile-text">
                                <h1 class="crm-client-name">{{ selectedTransaction.description || translate('domaincontrol', 'İşlem') }}</h1>
                                <div class="crm-client-meta">
                                    <span class="meta-item">
                                        <span class="status-badge" :class="selectedTransaction.type === 'income' ? 'badge-success' : 'badge-error'">
                                            {{ getTransactionTypeText(selectedTransaction.type) }}
                                        </span>
                                    </span>
                                    <span class="meta-item">
                                        <Calendar :size="14" />
                                        <span>{{ formatDate(selectedTransaction.transactionDate) }}</span>
                                    </span>
                                    <span v-if="selectedTransaction.id" class="meta-item id-badge-item">
                                        <span>#{{ selectedTransaction.id }}</span>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="crm-header-actions">
                            <NcButton @click="editTransaction(selectedTransaction)">
                                <template #icon><Pencil :size="18" /></template>
                                {{ translate('domaincontrol', 'Düzenle') }}
                            </NcButton>
                            <button class="icon-button danger" @click="confirmDelete(selectedTransaction)" :title="translate('domaincontrol', 'Sil')">
                                <Delete :size="20" />
                            </button>
                        </div>
                    </div>

                    <!-- TABS (if needed, currently using simple layout) -->
                    <div class="crm-tabs-scroll">
                        <div class="crm-tabs">
                            <button class="crm-tab" :class="{ active: activeTab === 'details' }" @click="activeTab = 'details'">
                                {{ translate('domaincontrol', 'Genel Bilgiler') }}
                            </button>
                            <button v-if="selectedTransaction.notes && getCleanNotes(selectedTransaction.notes)" class="crm-tab" :class="{ active: activeTab === 'notes' }" @click="activeTab = 'notes'">
                                {{ translate('domaincontrol', 'Notlar') }}
                            </button>
                        </div>
                    </div>
                </div>

                <!-- CONTENT SCROLL AREA -->
                <div class="crm-content-scroll">
                    
                    <div v-if="activeTab === 'details'" class="tab-pane">
                        <!-- Stats Grid -->
                        <div class="stats-grid">
                            <div class="stat-card">
                                <div class="stat-icon" :class="`bg-${selectedTransaction.type}`">
                                    <CurrencyUsd :size="24" :class="`text-${selectedTransaction.type}`" />
                                </div>
                                <div class="stat-content">
                                    <div class="stat-label">{{ translate('domaincontrol', 'Tutar') }}</div>
                                    <div class="stat-value" :class="`text-${selectedTransaction.type}`">
                                        {{ formatCurrency(selectedTransaction.amount) }}
                                    </div>
                                </div>
                            </div>
                            
                            <div class="stat-card">
                                <div class="stat-icon" style="background-color: rgba(0, 130, 201, 0.1);">
                                    <Tag :size="24" style="color: var(--color-primary-element);" />
                                </div>
                                <div class="stat-content">
                                    <div class="stat-label">{{ translate('domaincontrol', 'Kategori') }}</div>
                                    <div class="stat-value">{{ getCategoryName(selectedTransaction.categoryId) || '-' }}</div>
                                </div>
                            </div>

                            <div class="stat-card">
                                <div class="stat-icon" style="background-color: rgba(155, 89, 182, 0.1);">
                                    <Calendar :size="24" style="color: #9b59b6;" />
                                </div>
                                <div class="stat-content">
                                    <div class="stat-label">{{ translate('domaincontrol', 'Ödeme Yöntemi') }}</div>
                                    <div class="stat-value">{{ getPaymentMethodText(selectedTransaction.paymentMethod) }}</div>
                                </div>
                            </div>
                        </div>

                        <!-- Information Boxes -->
                        <div class="content-box">
                            <div class="box-header">
                                <h3>{{ translate('domaincontrol', 'İşlem Detayları') }}</h3>
                            </div>
                            <div class="box-body">
                                <div class="info-grid">
                                    <div class="info-group">
                                        <div class="info-label">{{ translate('domaincontrol', 'Açıklama') }}</div>
                                        <div class="info-val strong">{{ selectedTransaction.description || '-' }}</div>
                                    </div>
                                    <div class="info-group">
                                        <div class="info-label">{{ translate('domaincontrol', 'Tarih') }}</div>
                                        <div class="info-val">{{ formatDate(selectedTransaction.transactionDate) }}</div>
                                    </div>
                                    <div class="info-group">
                                        <div class="info-label">{{ translate('domaincontrol', 'Müşteri') }}</div>
                                        <div class="info-val">
                                            <a v-if="selectedTransaction.clientId" href="#" @click.prevent="navigateToClient(selectedTransaction.clientId)" class="info-link">
                                                <Account :size="14" />
                                                {{ getClientName(selectedTransaction.clientId) }}
                                            </a>
                                            <span v-else>-</span>
                                        </div>
                                    </div>
                                    <div class="info-group">
                                        <div class="info-label">{{ translate('domaincontrol', 'Proje') }}</div>
                                        <div class="info-val">
                                             <a v-if="selectedTransaction.projectId" href="#" @click.prevent="navigateToProject(selectedTransaction.projectId)" class="info-link">
                                                <ChartBar :size="14" />
                                                {{ getProjectName(selectedTransaction.projectId) }}
                                            </a>
                                            <span v-else>-</span>
                                        </div>
                                    </div>
                                    <div class="info-group" v-if="selectedTransaction.reference">
                                        <div class="info-label">{{ translate('domaincontrol', 'Referans') }}</div>
                                        <div class="info-val">{{ selectedTransaction.reference }}</div>
                                    </div>
                                    <div class="info-group" v-if="getInvoiceId(selectedTransaction)">
                                        <div class="info-label">{{ translate('domaincontrol', 'İlişkili Fatura') }}</div>
                                        <div class="info-val">
                                            <a href="#" @click.prevent="navigateToInvoice(getInvoiceId(selectedTransaction))" class="info-link">
                                                <FileDocumentOutline :size="14" />
                                                #{{ getInvoiceNumber(getInvoiceId(selectedTransaction)) }}
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div v-if="activeTab === 'notes'" class="tab-pane">
                         <div class="content-box">
                            <div class="box-header">
                                <h3>{{ translate('domaincontrol', 'Notlar') }}</h3>
                            </div>
                            <div class="box-body">
                                <div class="notes-text" v-html="getCleanNotes(selectedTransaction.notes)"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 2. DASHBOARD / FINANCIAL OVERVIEW -->
            <div v-else class="crm-detail-container">
                <!-- HEADER -->
                <div class="crm-header">
                    <div class="crm-header-top">
                        <div class="crm-profile-info">
                            <div class="avatar-xl dashboard-avatar-xl">
                                <ChartPie :size="36" />
                            </div>
                            <div class="crm-profile-text">
                                <h1 class="crm-client-name">{{ translate('domaincontrol', 'Finansal Genel Bakış') }}</h1>
                                <div class="crm-client-meta">
                                    <span class="meta-item">
                                        <CalendarRange :size="14" />
                                        <span>{{ translate('domaincontrol', 'Dashboard') }}</span>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="crm-header-actions dashboard-actions">
                             <!-- Date Range Filter -->
                            <div class="select-wrapper interactive-select">
                                <select v-model="dateRange" class="nc-select">
                                    <option value="all">{{ translate('domaincontrol', 'Tüm Zamanlar') }}</option>
                                    <option value="thisMonth">{{ translate('domaincontrol', 'Bu Ay') }}</option>
                                    <option value="lastMonth">{{ translate('domaincontrol', 'Geçen Ay') }}</option>
                                    <option value="last3Days">{{ translate('domaincontrol', 'Son 3 Gün') }}</option>
                                    <option value="custom">{{ translate('domaincontrol', 'Özel Aralık...') }}</option>
                                </select>
                                <CalendarRange :size="16" class="select-icon" />
                            </div>

                             <!-- Category Filter -->
                            <div class="select-wrapper interactive-select">
                                <select v-model="dashboardCategoryFilter" class="nc-select">
                                    <option value="all">{{ translate('domaincontrol', 'Tüm Kategoriler') }}</option>
                                    <option v-for="cat in categories" :key="cat.id" :value="cat.id">
                                        {{ cat.name }}
                                    </option>
                                    <option value="uncategorized">{{ translate('domaincontrol', 'Kategorisiz') }}</option>
                                </select>
                                <Tag :size="16" class="select-icon" />
                            </div>
                        </div>
                    </div>

                    <div class="dashboard-secondary-controls" v-if="dateRange === 'custom'">
                         <div class="custom-date-inputs">
                            <input type="date" v-model="customStartDate" class="nc-input-date" :title="translate('domaincontrol', 'Başlangıç Tarihi')">
                            <span class="separator">-</span>
                            <input type="date" v-model="customEndDate" class="nc-input-date" :title="translate('domaincontrol', 'Bitiş Tarihi')">
                        </div>
                    </div>

                    <div class="crm-tabs-scroll">
                        <div class="crm-tabs">
                            <button class="crm-tab" :class="{ active: statsFilter === 'all' }" @click="statsFilter = 'all'">
                                {{ translate('domaincontrol', 'Tümü') }}
                            </button>
                            <button class="crm-tab" :class="{ active: statsFilter === 'income' }" @click="statsFilter = 'income'">
                                {{ translate('domaincontrol', 'Gelir') }}
                            </button>
                            <button class="crm-tab" :class="{ active: statsFilter === 'expense' }" @click="statsFilter = 'expense'">
                                {{ translate('domaincontrol', 'Gider') }}
                            </button>
                        </div>
                    </div>
                </div>

                <!-- CONTENT SCROLL AREA -->
                <div class="crm-content-scroll">
                    <!-- KPI Cards -->
                    <div class="stats-grid">
                        <div class="stat-card">
                            <div class="stat-icon bg-income">
                                <TrendingUp :size="24" class="text-income" />
                            </div>
                            <div class="stat-content">
                                <div class="stat-label">{{ translate('domaincontrol', 'Toplam Gelir') }}</div>
                                <div class="stat-value text-income">{{ formatCurrency(kpiStats.income) }}</div>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon bg-expense">
                                <TrendingDown :size="24" class="text-error" />
                            </div>
                            <div class="stat-content">
                                <div class="stat-label">{{ translate('domaincontrol', 'Toplam Gider') }}</div>
                                <div class="stat-value text-error">{{ formatCurrency(kpiStats.expense) }}</div>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon" :style="{ backgroundColor: kpiStats.net >= 0 ? 'rgba(70, 186, 97, 0.1)' : 'rgba(233, 50, 45, 0.1)' }">
                                <CurrencyUsd :size="24" :style="{ color: kpiStats.net >= 0 ? 'var(--color-element-success)' : 'var(--color-element-error)' }" />
                            </div>
                            <div class="stat-content">
                                <div class="stat-label">{{ translate('domaincontrol', 'Net Kar/Zarar') }}</div>
                                <div class="stat-value" :class="kpiStats.net >= 0 ? 'text-success' : 'text-error'">
                                    {{ formatCurrency(kpiStats.net) }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Breakdown Section -->
                    <div class="content-box">
                        <div class="box-header">
                            <h3>
                                <ChartBar :size="18" class="inline-icon" />
                                {{ dashboardCategoryFilter !== 'all' 
                                    ? translate('domaincontrol', 'Kategori Detayları') 
                                    : translate('domaincontrol', 'Kategori Dağılımı') 
                                }}
                            </h3>
                        </div>
                        <div class="box-body no-padding">
                            <div v-if="!filteredCategoryStats || filteredCategoryStats.length === 0" class="empty-state-simple">
                                <p>{{ translate('domaincontrol', 'Bu dönem için veri bulunamadı') }}</p>
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
                                            {{ stat.categoryName || translate('domaincontrol', 'Kategorisiz') }}
                                        </div>
                                        <div class="cat-bar-container">
                                            <div class="cat-bar" :class="stat.type" :style="{ width: getCategoryPercentage(stat.total, stat.type) + '%' }"></div>
                                        </div>
                                    </div>
                                    <div class="row-meta">
                                        <span class="cat-count">{{ stat.count }} {{ translate('domaincontrol', 'işlem') }}</span>
                                        <span class="cat-amount">{{ formatCurrency(stat.total) }}</span>
                                    </div>
                                </div>
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
import Magnify from 'vue-material-design-icons/Magnify.vue'
import Refresh from 'vue-material-design-icons/Refresh.vue'
import ArrowLeft from 'vue-material-design-icons/ArrowLeft.vue'
import Pencil from 'vue-material-design-icons/Pencil.vue'
import Delete from 'vue-material-design-icons/Delete.vue'
import Account from 'vue-material-design-icons/Account.vue'
import CurrencyUsd from 'vue-material-design-icons/CurrencyUsd.vue'
import Calendar from 'vue-material-design-icons/Calendar.vue'
import TrendingUp from 'vue-material-design-icons/TrendingUp.vue'
import TrendingDown from 'vue-material-design-icons/TrendingDown.vue'
import ChevronRight from 'vue-material-design-icons/ChevronRight.vue'
import CalendarRange from 'vue-material-design-icons/CalendarRange.vue'
import FilterVariant from 'vue-material-design-icons/FilterVariant.vue'
import FileDocumentOutline from 'vue-material-design-icons/FileDocumentOutline.vue'
import ChartPie from 'vue-material-design-icons/ChartPie.vue'

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
        Magnify,
        Refresh,
        ArrowLeft,
        Pencil,
        Delete,
        Account,
        CurrencyUsd,
        Calendar,
        TrendingUp,
        TrendingDown,
        ChevronRight,
        CalendarRange,
        FilterVariant,
        FileDocumentOutline,
        ChartPie,
    },
    data() {
        return {
            transactions: [],
            clients: [],
            projects: [],
            categories: [],
            invoices: [],
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
            isMobile: window.innerWidth < 768,
            activeTab: 'details', // 'details' or 'notes'
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
        
        window.addEventListener('resize', this.handleResize);
    },
    beforeUnmount() {
        window.removeEventListener('resize', this.handleResize);
    },
    methods: {
        handleResize() {
            this.isMobile = window.innerWidth < 768;
        },
        async loadData() {
            this.loading = true
            try {
                await Promise.all([
                    this.loadTransactions(),
                    this.loadClients(),
                    this.loadProjects(),
                    this.loadCategories(),
                    this.loadInvoices(),
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
        async loadInvoices() {
            try {
                const response = await api.invoices.getAll()
                this.invoices = response.data || []
            } catch (error) {
                this.invoices = []
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
        getInvoiceId(transaction) {
            if (!transaction) return null
            // First check invoiceId field (new way)
            if (transaction.invoiceId) {
                return transaction.invoiceId
            }
            // Fallback to parsing notes (old way, for backward compatibility)
            if (transaction.notes) {
                const match = transaction.notes.match(/\[INVOICE_ID:(\d+)\]/)
                return match ? parseInt(match[1]) : null
            }
            return null
        },
        getInvoiceIdFromTransaction(transaction) {
            // Keep for backward compatibility
            return this.getInvoiceId(transaction)
        },
        getInvoiceNumber(invoiceId) {
            if (!invoiceId) return ''
            const invoice = this.invoices.find(inv => inv.id === invoiceId)
            return invoice ? (invoice.invoiceNumber || invoice.id) : invoiceId
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
/* App Layout */
.app-content-wrapper {
    display: flex;
    height: 100%;
    width: 100%;
    overflow: hidden;
    position: relative;
}

/* Left Pane: List */
.app-content-list {
    flex: 0 0 320px;
    width: 320px;
    border-right: 1px solid var(--color-border);
    display: flex;
    flex-direction: column;
    height: 100%;
    background-color: var(--color-main-background);
    z-index: 50;
    transition: transform 0.3s ease;
}

.app-content-list-header {
    padding: 0;
    border-bottom: 1px solid var(--color-border);
    background-color: var(--color-main-background);
    flex-shrink: 0;
}

.search-wrapper {
    padding: 12px 12px 6px 12px;
}

.search-wrapper-inner {
    position: relative;
    display: flex;
    align-items: center;
    margin-left: 30px;

}

.search-icon {
    position: absolute;
    left: 10px;
    color: var(--color-text-maxcontrast);
    opacity: 0.5;
    pointer-events: none;
}

.search-input {
    width: 100%;
    height: 38px;
    padding: 0 12px 0 36px !important;
    border: 1px solid var(--color-border) !important;
    border-radius: var(--border-radius-large) !important;
    background-color: var(--color-background-hover) !important;
    font-size: 14px;
}

.search-input:focus {
    background-color: var(--color-main-background) !important;
    border-color: var(--color-primary-element) !important;
}

.app-navigation__search {
    padding: 4px 12px 12px 12px;
}

.header-action-buttons {
    display: flex;
    gap: 8px;
    margin-bottom: 12px;
}

.filter-pills-container {
    padding: 0;
}

.filter-pills {
    display: flex;
    background-color: var(--color-background-dark);
    padding: 2px;
    border-radius: var(--border-radius-large);
    gap: 2px;
}

.filter-pill {
    flex: 1;
    border: none;
    background: transparent;
    padding: 6px 4px;
    border-radius: var(--border-radius);
    color: var(--color-text-maxcontrast);
    cursor: pointer;
    font-size: 12px;
    font-weight: 600;
    transition: all 0.2s;
}

.filter-pill:hover {
    color: var(--color-main-text);
}

.filter-pill.active {
    background-color: var(--color-main-background);
    color: var(--color-primary-element);
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.app-content-list-wrapper {
    flex: 1;
    overflow-y: auto;
}

/* Navigation List */
.app-navigation-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.app-navigation-entry {
    display: flex;
    align-items: center;
    padding: 12px 16px;
    cursor: pointer;
    transition: background-color 0.15s ease;
    border-bottom: 1px solid var(--color-border-lighter);
}

.app-navigation-entry:hover {
    background-color: var(--color-background-hover);
}

.app-navigation-entry.active {
    background-color: var(--color-primary-light);
}

.app-navigation-entry-icon {
    margin-right: 12px;
    flex-shrink: 0;
}

.transaction-avatar {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.transaction-avatar.type-income {
    background-color: rgba(70, 186, 97, 0.15);
    color: var(--color-element-success);
}

.transaction-avatar.type-expense {
    background-color: rgba(233, 50, 45, 0.15);
    color: var(--color-element-error);
}

.app-navigation-entry-content {
    flex: 1;
    min-width: 0;
}

.app-navigation-entry-name {
    font-weight: 600;
    font-size: 14px;
    color: var(--color-main-text);
    margin-bottom: 2px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.app-navigation-entry-details {
    display: flex;
    align-items: center;
    font-size: 11px;
    color: var(--color-text-maxcontrast);
    gap: 4px;
}

.dot-separator {
    opacity: 0.5;
}

.app-navigation-entry-status {
    flex-shrink: 0;
    margin-left: 8px;
}

.item-amount {
    font-weight: 700;
    font-size: 13px;
}

.amount-income { color: var(--color-element-success); }
.amount-expense { color: var(--color-element-error); }

/* Right Pane: Details */
.app-content-details {
    flex: 1;
    height: 100%;
    background-color: var(--color-main-background);
    display: flex;
    flex-direction: column;
    overflow: hidden;
    position: relative;
    z-index: 40;
}

.crm-detail-container {
    display: flex;
    flex-direction: column;
    height: 100%;
}

.crm-header {
    background-color: var(--color-main-background);
    border-bottom: 1px solid var(--color-border);
    flex-shrink: 0;
}

.crm-header-top {
    padding: 20px 24px;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.crm-profile-info {
    display: flex;
    align-items: center;
    gap: 16px;
}

.avatar-xl {
    width: 64px;
    height: 64px;
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.transaction-avatar-xl.type-income {
    background-color: rgba(70, 186, 97, 0.1);
    color: var(--color-element-success);
}

.transaction-avatar-xl.type-expense {
    background-color: rgba(233, 50, 45, 0.1);
    color: var(--color-element-error);
}

.dashboard-avatar-xl {
    background-color: var(--color-primary-light);
    color: var(--color-primary-element);
}

.crm-profile-text {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.crm-client-name {
    margin: 0;
    font-size: 20px;
    font-weight: 700;
    color: var(--color-main-text);
}

.crm-client-meta {
    display: flex;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
}

.meta-item {
    display: flex;
    align-items: center;
    gap: 4px;
    font-size: 12px;
    color: var(--color-text-maxcontrast);
}

.status-badge {
    padding: 2px 8px;
    border-radius: 10px;
    font-weight: 600;
}

.badge-success { background-color: rgba(70, 186, 97, 0.15); color: var(--color-element-success); }
.badge-error { background-color: rgba(233, 50, 45, 0.15); color: var(--color-element-error); }

.id-badge-item {
    background-color: var(--color-background-dark);
    padding: 2px 8px;
    border-radius: 10px;
}

.crm-header-actions {
    display: flex;
    gap: 8px;
}

.dashboard-actions {
    flex-direction: row;
    align-items: center;
}

.interactive-select {
    width: 160px !important;
}

.dashboard-secondary-controls {
    padding: 0 24px 12px 24px;
}

.custom-date-inputs {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 4px 12px;
    background-color: var(--color-background-hover);
    border-radius: var(--border-radius-large);
    font-size: 12px;
}

.nc-input-date {
    border: none;
    background: transparent;
    color: var(--color-main-text);
    width: 100px;
}

/* Tabs */
.crm-tabs-scroll {
    padding: 0 24px;
    overflow-x: auto;
}

.crm-tabs {
    display: flex;
}

.crm-tab {
    padding: 10px 16px;
    background: none;
    border: none;
    border-bottom: 2px solid transparent;
    cursor: pointer;
    font-size: 13px;
    font-weight: 600;
    color: var(--color-text-maxcontrast);
    transition: all 0.2s;
    white-space: nowrap;
}

.crm-tab:hover {
    color: var(--color-main-text);
}

.crm-tab.active {
    color: var(--color-primary-element);
    border-bottom-color: var(--color-primary-element);
}

/* Content Area */
.crm-content-scroll {
    flex: 1;
    overflow-y: auto;
    padding: 24px;
}

.tab-pane {
    display: flex;
    flex-direction: column;
    gap: 24px;
}

/* Stats */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 16px;
}

.stat-card {
    background: var(--color-main-background);
    border: 1px solid var(--color-border);
    border-radius: var(--border-radius-large);
    padding: 16px;
    display: flex;
    align-items: center;
    gap: 12px;
}

.stat-icon {
    width: 40px;
    height: 40px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.bg-income { background-color: rgba(70, 186, 97, 0.1); }
.bg-expense { background-color: rgba(233, 50, 45, 0.1); }

.stat-content {
    display: flex;
    flex-direction: column;
}

.stat-label {
    font-size: 12px;
    color: var(--color-text-maxcontrast);
    margin-bottom: 2px;
}

.stat-value {
    font-size: 16px;
    font-weight: 700;
    color: var(--color-main-text);
}

/* Content Box */
.content-box {
    background: var(--color-main-background);
    border: 1px solid var(--color-border);
    border-radius: var(--border-radius-large);
    overflow: hidden;
}

.box-header {
    padding: 12px 16px;
    background: var(--color-background-hover);
    border-bottom: 1px solid var(--color-border);
}

.box-header h3 {
    margin: 0;
    font-size: 14px;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 6px;
}

.box-body {
    padding: 16px;
}

.box-body.no-padding {
    padding: 0;
}

/* Info Grid */
.info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 20px;
}

.info-group {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.info-label {
    font-size: 11px;
    font-weight: 600;
    color: var(--color-text-maxcontrast);
    text-transform: uppercase;
}

.info-val {
    font-size: 13px;
    color: var(--color-main-text);
}

.info-val.strong {
    font-weight: 600;
}

.info-link {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    color: var(--color-primary-element);
    text-decoration: none;
    font-weight: 500;
}

/* Category Table */
.category-table {
    display: flex;
    flex-direction: column;
}

.category-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 16px;
    border-bottom: 1px solid var(--color-border-lighter);
}

.category-row:last-child {
    border-bottom: none;
}

.row-main {
    flex: 1;
    display: flex;
    align-items: center;
    gap: 16px;
}

.cat-name {
    width: 140px;
    font-weight: 600;
    font-size: 13px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.dot { width: 8px; height: 8px; border-radius: 50%; }
.dot.income { background-color: var(--color-element-success); }
.dot.expense { background-color: var(--color-element-error); }

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
}

.cat-bar.income { background-color: var(--color-element-success); }
.cat-bar.expense { background-color: var(--color-element-error); }

.row-meta {
    display: flex;
    align-items: center;
    gap: 20px;
    width: 150px;
    justify-content: flex-end;
}

.cat-count {
    font-size: 11px;
    color: var(--color-text-maxcontrast);
}

.cat-amount {
    font-weight: 600;
    font-size: 13px;
}

/* Helpers */
.text-income { color: var(--color-element-success) !important; }
.text-error { color: var(--color-element-error) !important; }

.loading-container {
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 40px;
}

.spin-animation {
    animation: spin 1s linear infinite;
}

@keyframes spin {
    100% { transform: rotate(360deg); }
}

.empty-list {
    padding: 40px 20px;
    text-align: center;
}

.empty-icon-bg {
    width: 48px;
    height: 48px;
    background-color: var(--color-background-hover);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 12px;
    color: var(--color-text-maxcontrast);
    opacity: 0.5;
}

.empty-text {
    color: var(--color-text-maxcontrast);
    font-style: italic;
    font-size: 13px;
}

.empty-state-simple {
    padding: 20px;
    text-align: center;
    color: var(--color-text-maxcontrast);
    font-size: 13px;
}

/* Buttons and Selects */
.nc-select {
    width: 100%;
    appearance: none;
    background-color: var(--color-background-hover);
    border: 1px solid var(--color-border);
    border-radius: var(--border-radius-large);
    padding: 8px 12px 8px 36px;
    font-size: 13px;
    color: var(--color-main-text);
    cursor: pointer;
    height: 38px;
}

.select-wrapper {
    position: relative;
}

.select-icon {
    position: absolute;
    left: 10px;
    top: 50%;
    transform: translateY(-50%);
    pointer-events: none;
    color: var(--color-text-maxcontrast);
    opacity: 0.7;
}

.icon-button {
    background: none;
    border: none;
    padding: 6px;
    border-radius: 50%;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--color-text-maxcontrast);
    transition: all 0.2s;
}

.icon-button:hover {
    background-color: var(--color-background-hover);
    color: var(--color-main-text);
}

.icon-button.danger:hover {
    background-color: rgba(233, 50, 45, 0.1);
    color: var(--color-element-error);
}

.notes-text {
    font-size: 13px;
    line-height: 1.6;
    color: var(--color-main-text);
    white-space: pre-wrap;
}

/* Mobile */
@media (max-width: 768px) {
    .app-content-list {
        width: 100%;
        flex: 1;
    }
    
    .mobile-hidden {
        display: none !important;
    }
    
    .crm-header-top {
        padding: 16px;
        flex-direction: column;
        align-items: flex-start;
        gap: 16px;
    }
    
    .crm-header-actions {
        width: 100%;
        justify-content: space-between;
    }

    .dashboard-actions {
        width: 100%;
        flex-wrap: wrap;
        gap: 8px;
    }

    .interactive-select {
        width: calc(50% - 4px) !important;
    }

    .stats-grid {
        grid-template-columns: 1fr;
    }

    .info-grid {
        grid-template-columns: 1fr;
    }
}
</style>