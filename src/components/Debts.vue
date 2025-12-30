<template>
    <div class="nc-app-content">
        <!-- Modals -->
        <DebtModal
            :open="modalOpen"
            :debt="editingDebt"
            :clients="clients"
            @close="closeModal"
            @saved="handleDebtSaved"
        />

        <DebtPaymentModal
            :open="paymentModalOpen"
            :debt="payingDebt"
            @close="closePaymentModal"
            @paid="handlePaymentAdded"
        />

        <!-- LIST COLUMN (Sol Taraf) -->
        <div class="nc-app-content-list" :class="{ 'mobile-hidden': selectedDebt }">
            <!-- Header: Actions & Search & Filter -->
            <div class="app-content-list__header">
                <div class="header-main-actions">
                    <h2 class="header-title">{{ translate('domaincontrol', 'Debts & Credits') }}</h2>
                    <button class="nc-button nc-button--primary" @click="showAddModal" :title="translate('domaincontrol', 'Add Debt/Credit')">
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
                        @input="filterDebts"
                    />
                </div>

                <!-- Scrollable Filter Tabs -->
                <div class="filter-tabs-wrapper">
                    <div class="filter-tabs">
                        <button class="filter-tab" :class="{ 'active': currentFilter === 'all' }" @click="setFilter('all')">
                            {{ translate('domaincontrol', 'All') }}
                        </button>
                        <button class="filter-tab" :class="{ 'active': currentFilter === 'debt' }" @click="setFilter('debt')">
                            {{ translate('domaincontrol', 'Debts') }}
                        </button>
                        <button class="filter-tab" :class="{ 'active': currentFilter === 'credit' }" @click="setFilter('credit')">
                            {{ translate('domaincontrol', 'Credits') }}
                        </button>
                        <button class="filter-tab" :class="{ 'active': currentFilter === 'upcoming' }" @click="setFilter('upcoming')">
                            {{ translate('domaincontrol', 'Upcoming') }}
                        </button>
                        <button class="filter-tab" :class="{ 'active': currentFilter === 'overdue' }" @click="setFilter('overdue')">
                            {{ translate('domaincontrol', 'Overdue') }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- List Content -->
            <div class="app-content-list__items">
                <!-- Loading -->
                <div v-if="loading" class="loading-state">
                    <MaterialIcon name="loading" :size="32" class="spin" />
                </div>

                <!-- Empty State -->
                <div v-else-if="filteredDebts.length === 0" class="empty-state">
                    <MaterialIcon name="accounting" :size="64" class="empty-icon" />
                    <p>{{ searchQuery ? translate('domaincontrol', 'No debts found') : translate('domaincontrol', 'No debts yet') }}</p>
                </div>

                <!-- Debts List -->
                <div v-else class="debt-list-wrapper">
                    <div
                        v-for="debt in filteredDebts"
                        :key="debt.id"
                        class="nc-list-item"
                        :class="{
                            'active': selectedDebt && selectedDebt.id === debt.id,
                            'item-overdue': isOverdue(debt)
                        }"
                        @click="selectDebt(debt)"
                    >
                        <div class="nc-list-item__icon" :class="`type-${debt.type}`">
                            <MaterialIcon :name="debt.type === 'debt' ? 'arrow-down-circle' : 'arrow-up-circle'" :size="20" />
                        </div>
                        <div class="nc-list-item__content">
                            <div class="nc-list-item__primary">
                                {{ debt.creditorDebtorName || debt.description || translate('domaincontrol', 'Debt/Credit') }}
                            </div>
                            <div class="nc-list-item__secondary">
                                <span v-if="debt.nextPaymentDate" :class="{'text-error': isOverdue(debt)}">
                                    {{ formatDate(debt.nextPaymentDate) }}
                                </span>
                                <span v-else>{{ translate('domaincontrol', 'No date') }}</span>
                                <span v-if="getClientName(debt.clientId)" class="separator">•</span>
                                <span v-if="getClientName(debt.clientId)">{{ getClientName(debt.clientId) }}</span>
                            </div>
                        </div>
                        <div class="nc-list-item__end">
                            <div class="amount-column">
                                <div class="amount-total">{{ formatCurrency(debt.totalAmount, debt.currency) }}</div>
                                <div class="amount-remaining" :class="getRemainingClass(debt)">
                                    {{ translate('domaincontrol', 'Rem') }}: {{ formatCurrency(getRemaining(debt), debt.currency) }}
                                </div>
                            </div>
                            <div class="action-menu-wrapper" @click.stop>
                                <button class="icon-action" @click.stop="togglePopover(debt.id)">
                                    <MaterialIcon name="more-vertical" :size="20" />
                                </button>
                                <div v-if="openPopover === debt.id" class="nc-popover-menu">
                                    <button class="nc-popover-item" @click="editDebt(debt); closePopover()">
                                        <MaterialIcon name="edit" :size="16" /> {{ translate('domaincontrol', 'Edit') }}
                                    </button>
                                    <button class="nc-popover-item danger" @click="confirmDelete(debt); closePopover()">
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
        <div class="nc-app-content-details" v-if="selectedDebt">
            <header class="details-header">
                <button class="icon-action mobile-only" @click="backToList">
                    <MaterialIcon name="arrow-left" :size="20" />
                </button>
                <h2 class="details-title">{{ translate('domaincontrol', 'Details') }}</h2>
                <div class="header-actions">
                     <button
                        v-if="selectedDebt.status === 'active' && getRemaining(selectedDebt) > 0"
                        class="nc-button nc-button--primary small"
                        @click="showPaymentModal"
                    >
                        <MaterialIcon name="add" :size="16" /> {{ translate('domaincontrol', 'Pay') }}
                    </button>
                    <button class="icon-action" @click="editDebt(selectedDebt)" :title="translate('domaincontrol', 'Edit')">
                        <MaterialIcon name="edit" :size="20" />
                    </button>
                    <button class="icon-action" @click="confirmDelete(selectedDebt)" :title="translate('domaincontrol', 'Delete')">
                        <MaterialIcon name="delete" :size="20" />
                    </button>
                </div>
            </header>

            <div class="details-body">
                <!-- Hero Section: Progress & Amount -->
                <div class="hero-section">
                    <div class="hero-top">
                        <div class="hero-icon" :class="`type-${selectedDebt.type}`">
                            <MaterialIcon :name="selectedDebt.type === 'debt' ? 'delete' : 'add'" :size="32" />
                        </div>
                        <div class="hero-info">
                            <div class="hero-label">{{ selectedDebt.creditorDebtorName }}</div>
                            <div class="hero-amount" :class="`type-${selectedDebt.type}-text`">
                                {{ formatCurrency(selectedDebt.totalAmount, selectedDebt.currency) }}
                            </div>
                        </div>
                    </div>
                    
                    <div class="payment-progress-container">
                        <div class="progress-labels">
                            <span>{{ translate('domaincontrol', 'Paid') }}: {{ formatCurrency(selectedDebt.paidAmount, selectedDebt.currency) }}</span>
                            <span class="text-bold">{{ getProgressPercentage(selectedDebt).toFixed(0) }}%</span>
                        </div>
                        <div class="nc-progress-bar">
                            <div class="nc-progress-fill" :style="{ width: getProgressPercentage(selectedDebt) + '%' }"></div>
                        </div>
                        <div class="progress-remaining" :class="getRemainingClass(selectedDebt)">
                            {{ translate('domaincontrol', 'Remaining') }}: {{ formatCurrency(getRemaining(selectedDebt), selectedDebt.currency) }}
                        </div>
                    </div>
                </div>

                <!-- Info Grid -->
                <div class="details-section-grid">
                    <div class="detail-card">
                        <h3 class="card-title">{{ translate('domaincontrol', 'Information') }}</h3>
                         <div class="detail-row">
                            <span class="label">{{ translate('domaincontrol', 'Type') }}</span>
                            <span class="value">
                                <span class="nc-badge" :class="`badge-${selectedDebt.type}`">{{ getDebtTypeText(selectedDebt.type) }}</span>
                            </span>
                        </div>
                        <div class="detail-row">
                            <span class="label">{{ translate('domaincontrol', 'Status') }}</span>
                            <span class="value">
                                <span class="nc-badge" :class="getDebtStatusClass(selectedDebt)">{{ getDebtStatusText(selectedDebt.status) }}</span>
                            </span>
                        </div>
                         <div class="detail-row">
                            <span class="label">{{ translate('domaincontrol', 'Category') }}</span>
                            <span class="value">{{ getDebtCategoryText(selectedDebt.debtType) }}</span>
                        </div>
                         <div class="detail-row">
                            <span class="label">{{ translate('domaincontrol', 'Client') }}</span>
                            <span class="value">
                                <a v-if="selectedDebt.clientId" href="#" @click.prevent="navigateToClient(selectedDebt.clientId)" class="link-primary">
                                    {{ getClientName(selectedDebt.clientId) }}
                                </a>
                                <span v-else>-</span>
                            </span>
                        </div>
                    </div>

                    <div class="detail-card">
                        <h3 class="card-title">{{ translate('domaincontrol', 'Schedule') }}</h3>
                        <div class="detail-row">
                            <span class="label">{{ translate('domaincontrol', 'Start Date') }}</span>
                            <span class="value">{{ formatDate(selectedDebt.startDate) || '-' }}</span>
                        </div>
                         <div class="detail-row">
                            <span class="label">{{ translate('domaincontrol', 'Due Date') }}</span>
                            <span class="value" :class="getDueDateClass(selectedDebt)">{{ formatDate(selectedDebt.dueDate) || '-' }}</span>
                        </div>
                         <div class="detail-row">
                            <span class="label">{{ translate('domaincontrol', 'Next Payment') }}</span>
                            <span class="value" :class="getNextPaymentClass(selectedDebt)">{{ formatDate(selectedDebt.nextPaymentDate) || '-' }}</span>
                        </div>
                         <div class="detail-row">
                            <span class="label">{{ translate('domaincontrol', 'Frequency') }}</span>
                            <span class="value">{{ getPaymentFrequencyText(selectedDebt.paymentFrequency) }}</span>
                        </div>
                    </div>
                </div>

                <div class="details-section" v-if="selectedDebt.description || selectedDebt.notes">
                    <h3 class="section-title">{{ translate('domaincontrol', 'Notes') }}</h3>
                    <div class="notes-content">
                        <div v-if="selectedDebt.description" class="mb-2"><strong>{{ translate('domaincontrol', 'Desc') }}:</strong> {{ selectedDebt.description }}</div>
                        <div v-if="selectedDebt.notes" v-html="selectedDebt.notes"></div>
                    </div>
                </div>

                <!-- Payment History -->
                <div class="details-section">
                    <h3 class="section-title">{{ translate('domaincontrol', 'Payment History') }}</h3>
                    <div v-if="payments.length === 0" class="empty-small">
                        {{ translate('domaincontrol', 'No payments recorded') }}
                    </div>
                    <div v-else class="payment-history-list">
                        <div v-for="payment in payments" :key="payment.id" class="payment-history-item">
                            <div class="payment-icon">
                                <MaterialIcon name="history" :size="18" />
                            </div>
                            <div class="payment-info">
                                <div class="payment-amount">{{ formatCurrency(payment.amount, selectedDebt.currency) }}</div>
                                <div class="payment-date">{{ formatDate(payment.paymentDate) }} &bull; {{ getPaymentMethodText(payment.paymentMethod) }}</div>
                                <div v-if="payment.notes" class="payment-note">{{ payment.notes }}</div>
                            </div>
                            <button class="icon-action danger-hover" @click="confirmDeletePayment(payment)" :title="translate('domaincontrol', 'Delete Payment')">
                                <MaterialIcon name="delete" :size="16" />
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Empty Details State -->
        <div class="nc-app-content-details empty-details" v-else>
            <MaterialIcon name="information-outline" :size="48" color="var(--color-text-maxcontrast)" />
            <p>{{ translate('domaincontrol', 'Select a debt/credit to view details') }}</p>
        </div>
    </div>
</template>

<script>
import api from '../services/api'
import MaterialIcon from './MaterialIcon.vue'
import DebtModal from './DebtModal.vue'
import DebtPaymentModal from './DebtPaymentModal.vue'

export default {
    name: 'Debts',
    components: {
        MaterialIcon,
        DebtModal,
        DebtPaymentModal,
    },
    data() {
        return {
            debts: [],
            clients: [],
            selectedDebt: null,
            payments: [],
            loading: false,
            modalOpen: false,
            editingDebt: null,
            paymentModalOpen: false,
            payingDebt: null,
            currentFilter: 'all',
            searchQuery: '',
            openPopover: null,
        }
    },
    computed: {
        filteredDebts() {
            let filtered = this.debts

            // Apply type filter
            if (this.currentFilter === 'debt' || this.currentFilter === 'credit') {
                filtered = filtered.filter(d => d.type === this.currentFilter)
            } else if (this.currentFilter === 'upcoming') {
                const today = new Date()
                const thirtyDaysLater = new Date()
                thirtyDaysLater.setDate(today.getDate() + 30)
                filtered = filtered.filter(d => {
                    if (!d.nextPaymentDate) return false
                    const nextDate = new Date(d.nextPaymentDate)
                    return nextDate >= today && nextDate <= thirtyDaysLater
                })
            } else if (this.currentFilter === 'overdue') {
                filtered = filtered.filter(d => this.isOverdue(d))
            }

            // Apply search query
            if (this.searchQuery) {
                const query = this.searchQuery.toLowerCase()
                filtered = filtered.filter(debt => {
                    const name = (debt.creditorDebtorName || '').toLowerCase()
                    const description = (debt.description || '').toLowerCase()
                    const clientName = this.getClientName(debt.clientId) || ''
                    return (
                        name.includes(query) ||
                        description.includes(query) ||
                        clientName.toLowerCase().includes(query)
                    )
                })
            }

            return filtered
        },
    },
    watch: {
        selectedDebt(newVal) {
            if (newVal && newVal.id) {
                this.loadPayments(newVal.id)
            } else {
                this.payments = []
            }
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
                    this.loadDebts(),
                    this.loadClients(),
                ])
            } catch (error) {
                console.error('Error loading data:', error)
            } finally {
                this.loading = false
            }
        },
        async loadDebts() {
            try {
                const response = await api.debts.getAll()
                this.debts = response.data || []
            } catch (error) {
                console.error('Error loading debts:', error)
                this.debts = []
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
        async loadPayments(debtId) {
            try {
                const response = await api.debts.get(debtId)
                if (response.data && response.data.payments) {
                    this.payments = response.data.payments || []
                } else {
                    this.payments = []
                }
            } catch (error) {
                console.error('Error loading payments:', error)
                this.payments = []
            }
        },
        filterDebts() {
            // Computed property handles filtering
        },
        setFilter(filter) {
            this.currentFilter = filter
        },
        selectDebt(debt) {
            this.selectedDebt = debt
        },
        backToList() {
            this.selectedDebt = null
        },
        showAddModal() {
            this.editingDebt = null
            this.modalOpen = true
        },
        editDebt(debt) {
            this.editingDebt = debt
            this.modalOpen = true
        },
        closeModal() {
            this.modalOpen = false
            this.editingDebt = null
        },
        async handleDebtSaved() {
            await this.loadDebts()
            if (this.selectedDebt) {
                const response = await api.debts.get(this.selectedDebt.id)
                this.selectedDebt = response.data
            }
            this.closeModal()
        },
        showPaymentModal() {
            this.payingDebt = this.selectedDebt
            this.paymentModalOpen = true
        },
        closePaymentModal() {
            this.paymentModalOpen = false
            this.payingDebt = null
        },
        async handlePaymentAdded() {
            if (this.selectedDebt) {
                const response = await api.debts.get(this.selectedDebt.id)
                this.selectedDebt = response.data
                if (response.data && response.data.payments) {
                    this.payments = response.data.payments || []
                }
            }
            this.closePaymentModal()
        },
        async confirmDelete(debt) {
            if (!confirm(this.translate('domaincontrol', 'Are you sure you want to delete this debt/credit?'))) {
                return
            }
            try {
                await api.debts.delete(debt.id)
                await this.loadDebts()
                if (this.selectedDebt && this.selectedDebt.id === debt.id) {
                    this.backToList()
                }
            } catch (error) {
                console.error('Error deleting debt:', error)
                alert(this.translate('domaincontrol', 'Error deleting debt/credit'))
            }
        },
        async confirmDeletePayment(payment) {
            if (!confirm(this.translate('domaincontrol', 'Are you sure you want to delete this payment?'))) {
                return
            }
            try {
                await api.debtPayments.delete(payment.id)
                await this.loadPayments(this.selectedDebt.id)
                const response = await api.debts.get(this.selectedDebt.id)
                this.selectedDebt = response.data
            } catch (error) {
                console.error('Error deleting payment:', error)
                alert(this.translate('domaincontrol', 'Error deleting payment'))
            }
        },
        getClientName(clientId) {
            if (!clientId) return ''
            const client = this.clients.find(c => c.id === clientId)
            return client ? client.name : ''
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
        getRemaining(debt) {
            return (debt.totalAmount || 0) - (debt.paidAmount || 0)
        },
        getRemainingClass(debt) {
            const remaining = this.getRemaining(debt)
            if (remaining <= 0) return 'text-success'
            if (this.isOverdue(debt)) return 'text-error'
            return ''
        },
        getProgressPercentage(debt) {
            if (!debt.totalAmount || debt.totalAmount === 0) return 0
            return Math.min(100, ((debt.paidAmount || 0) / debt.totalAmount) * 100)
        },
        isOverdue(debt) {
            if (!debt.nextPaymentDate || debt.status === 'paid') return false
            const nextDate = new Date(debt.nextPaymentDate)
            const today = new Date()
            today.setHours(0, 0, 0, 0)
            return nextDate < today
        },
        getDebtTypeText(type) {
            const types = {
                debt: this.translate('domaincontrol', 'Debt'),
                credit: this.translate('domaincontrol', 'Credit'),
            }
            return types[type] || type
        },
        getDebtCategoryText(debtType) {
            const types = {
                credit_card: this.translate('domaincontrol', 'Credit Card'),
                loan: this.translate('domaincontrol', 'Loan'),
                physical: this.translate('domaincontrol', 'Physical Debt'),
                other: this.translate('domaincontrol', 'Other'),
            }
            return types[debtType] || debtType
        },
        getDebtStatusClass(debt) {
             // Returning badge classes for Nextcloud style
            if (debt.status === 'paid') return 'badge-success'
            if (debt.status === 'overdue' || this.isOverdue(debt)) return 'badge-error'
            if (debt.status === 'cancelled') return 'badge-neutral'
            return 'badge-primary'
        },
        getDebtStatusText(status) {
             const statusTexts = {
                active: this.translate('domaincontrol', 'Active'),
                paid: this.translate('domaincontrol', 'Paid'),
                overdue: this.translate('domaincontrol', 'Overdue'),
                cancelled: this.translate('domaincontrol', 'Cancelled'),
            }
            return statusTexts[status] || status
        },
        getPaymentFrequencyText(frequency) {
            const frequencies = {
                daily: this.translate('domaincontrol', 'Daily'),
                weekly: this.translate('domaincontrol', 'Weekly'),
                monthly: this.translate('domaincontrol', 'Monthly'),
            }
            return frequencies[frequency] || frequency
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
        getDueDateClass(debt) {
            if (!debt.dueDate) return ''
            const dueDate = new Date(debt.dueDate)
            const today = new Date()
            today.setHours(0, 0, 0, 0)
            if (dueDate < today && debt.status !== 'paid') return 'text-error'
            return ''
        },
        getNextPaymentClass(debt) {
            if (!debt.nextPaymentDate) return ''
            const nextDate = new Date(debt.nextPaymentDate)
            const today = new Date()
            today.setHours(0, 0, 0, 0)
            if (nextDate < today) return 'text-error'
            const diffTime = nextDate - today
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24))
            if (diffDays <= 7) return 'text-warning'
            return ''
        },
        togglePopover(debtId) {
            this.openPopover = this.openPopover === debtId ? null : debtId
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
            
            // Fallback
             const translations = {
                'Debts & Credits': 'Borç & Alacak',
                'Add': 'Ekle',
                'Search...': 'Ara...',
                'All': 'Tümü',
                'Debts': 'Borçlar',
                'Credits': 'Alacaklar',
                'Upcoming': 'Yaklaşan',
                'Overdue': 'Gecikmiş',
                'No debts found': 'Kayıt bulunamadı',
                'Details': 'Detaylar',
                'Pay': 'Öde',
                'Information': 'Bilgiler',
                'Schedule': 'Takvim',
                'Remaining': 'Kalan',
                'Paid': 'Ödenen',
                'Rem': 'Kal',
                'Debt': 'Borç',
                'Credit': 'Alacak',
                'No payments recorded': 'Ödeme kaydı yok',
                 // ... (diğerleri aynı kalabilir)
            }
            return translations[text] || text
        },
    },
}
</script>

<style scoped>
/* --- BASE LAYOUT (Master-Detail) --- */
.nc-app-content {
    display: flex;
    height: 100%;
    width: 100%;
    background-color: var(--color-main-background);
    overflow: hidden;
}

/* --- LIST VIEW --- */
.nc-app-content-list {
    flex: 0 0 350px;
    min-width: 300px;
    max-width: 450px;
    border-right: 1px solid var(--color-border);
    display: flex;
    flex-direction: column;
    height: 100%;
    background-color: var(--color-main-background);
    z-index: 50;
}

.app-content-list__header {
    padding: 16px;
    border-bottom: 1px solid var(--color-border);
    background-color: var(--color-main-background);
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
    background-color: var(--color-primary);
    color: var(--color-primary-text);
}

.nc-button.small {
    padding: 6px 12px;
    font-size: 13px;
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

.search-icon {
    position: absolute;
    left: 10px;
    top: 50%;
    transform: translateY(-50%);
    opacity: 0.5;
    pointer-events: none;
}

/* Filter Tabs */
.filter-tabs-wrapper {
    overflow-x: auto;
    /* Hide scrollbar */
    scrollbar-width: none;
    -ms-overflow-style: none;
}
.filter-tabs-wrapper::-webkit-scrollbar { display: none; }

.filter-tabs {
    display: flex;
    gap: 8px;
    padding-bottom: 4px; /* for focus ring space */
}

.filter-tab {
    white-space: nowrap;
    border: none;
    background: var(--color-background-dark);
    padding: 6px 12px;
    border-radius: var(--border-radius-pill, 16px);
    color: var(--color-text-maxcontrast);
    cursor: pointer;
    font-size: 13px;
    transition: all 0.2s;
}

.filter-tab.active {
    background-color: var(--color-primary);
    color: var(--color-primary-text);
}

/* List Items */
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
    min-height: 72px; /* Slightly taller for debts info */
}

.nc-list-item:hover, .nc-list-item.active {
    background-color: var(--color-background-hover);
}

.nc-list-item.active {
    border-left: 4px solid var(--color-primary);
    padding-left: 12px;
}

/* Overdue Marking on List Item */
.nc-list-item.item-overdue {
    background-color: var(--color-text-error, rgba(233, 50, 45, 0.1));
}

/* Icon Styles using Elements */
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

.type-debt {
    background-color: var(--color-text-error, #ffebee);
    color: var(--color-text-error, #d93025);
}

.type-credit {
    background-color: var(--color-element-success-element, #e6f4ea);
    color: var(--color-element-success-element, #188038);
}

.nc-list-item__content {
    flex: 1;
    min-width: 0;
    display: flex;
    flex-direction: column;
}

.nc-list-item__primary {
    font-weight: 600;
    color: var(--color-main-text);
    font-size: 15px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.nc-list-item__secondary {
    font-size: 13px;
    color: var(--color-text-maxcontrast);
    margin-top: 2px;
}

.separator { margin: 0 4px; }

/* Right Side of List Item */
.nc-list-item__end {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-left: 8px;
    text-align: right;
}

.amount-column {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
}

.amount-total {
    font-weight: bold;
    font-size: 14px;
    color: var(--color-main-text);
}

.amount-remaining {
    font-size: 11px;
    color: var(--color-text-maxcontrast);
}

/* Action Menu */
.action-menu-wrapper { position: relative; }

.icon-action {
    background: transparent;
    border: none;
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    cursor: pointer;
    color: var(--color-text-maxcontrast);
}

.icon-action:hover { background-color: var(--color-background-dark); }
.icon-action.danger-hover:hover { color: var(--color-element-error); background-color: var(--color-text-error); }

.nc-popover-menu {
    position: absolute;
    right: 0;
    top: 100%;
    background: var(--color-main-background);
    border: 1px solid var(--color-border);
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    z-index: 100;
    min-width: 140px;
    padding: 4px;
}

.nc-popover-item {
    display: flex;
    align-items: center;
    gap: 8px;
    width: 100%;
    padding: 8px;
    border: none;
    background: transparent;
    text-align: left;
    cursor: pointer;
    border-radius: 4px;
    color: var(--color-main-text);
}

.nc-popover-item:hover { background-color: var(--color-background-hover); }
.nc-popover-item.danger { color: var(--color-element-error); }
.nc-popover-item.danger:hover { background-color: var(--color-text-error); }


/* --- DETAILS VIEW --- */
.nc-app-content-details {
    flex: 1;
    display: flex;
    flex-direction: column;
    overflow-y: auto;
    background-color: var(--color-main-background);
}

.details-header {
    display: flex;
    align-items: center;
    padding: 16px 24px;
    border-bottom: 1px solid var(--color-border);
    height: 64px;
}

.details-title {
    flex: 1;
    margin: 0;
    font-size: 18px;
    font-weight: bold;
    color: var(--color-main-text);
}

.header-actions { display: flex; gap: 8px; align-items: center; }

.details-body {
    padding: 24px;
    max-width: 900px;
    margin: 0 auto;
    width: 100%;
    box-sizing: border-box;
}

/* Hero Section */
.hero-section {
    background-color: var(--color-background-dark);
    border-radius: 12px;
    padding: 24px;
    margin-bottom: 24px;
}

.hero-top {
    display: flex;
    align-items: center;
    gap: 16px;
    margin-bottom: 20px;
}

.hero-icon {
    width: 64px;
    height: 64px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}
/* Reusing type classes defined above for bg/color */
.type-debt { background-color: var(--color-text-error); color: var(--color-element-error); }
.type-credit { background-color: var(--color-element-success-element); color: var(--color-element-success-element); }

.hero-info { flex: 1; }
.hero-label { font-size: 14px; color: var(--color-text-maxcontrast); margin-bottom: 4px; }
.hero-amount { font-size: 32px; font-weight: 800; }
.type-debt-text { color: var(--color-element-error); }
.type-credit-text { color: var(--color-element-success-element); }

/* Progress Bar */
.progress-labels {
    display: flex;
    justify-content: space-between;
    font-size: 13px;
    margin-bottom: 6px;
    color: var(--color-main-text);
}

.nc-progress-bar {
    height: 12px;
    background-color: var(--color-border);
    border-radius: 6px;
    overflow: hidden;
    margin-bottom: 8px;
}

.nc-progress-fill {
    height: 100%;
    background-color: var(--color-element-success-element);
    transition: width 0.3s ease;
}

.progress-remaining {
    font-size: 13px;
    text-align: right;
}

/* Info Grid */
.details-section-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 24px;
    margin-bottom: 24px;
}

.detail-card {
    background-color: var(--color-background-dark);
    padding: 20px;
    border-radius: 12px;
}

.card-title, .section-title {
    margin: 0 0 16px 0;
    font-size: 16px;
    font-weight: 600;
    color: var(--color-text-maxcontrast);
    border-bottom: 1px solid var(--color-border);
    padding-bottom: 8px;
}

.detail-row {
    display: flex;
    justify-content: space-between;
    padding: 8px 0;
    font-size: 14px;
}

.label { color: var(--color-text-maxcontrast); }
.value { color: var(--color-main-text); font-weight: 500; }

/* Badges using Element Colors */
.nc-badge {
    padding: 2px 8px;
    border-radius: 12px;
    font-size: 12px;
}
.badge-success { background-color: var(--color-element-success-element); color: var(--color-element-success-element); }
.badge-error { background-color: var(--color-text-error); color: var(--color-element-error); }
.badge-warning { background-color: var(--color-warning-element); color: var(--color-warning); }
.badge-primary { background-color: var(--color-primary-element); color: var(--color-primary); }
.badge-neutral { background-color: var(--color-border); color: var(--color-text-maxcontrast); }

.badge-debt { background-color: var(--color-text-error); color: var(--color-element-error); }
.badge-credit { background-color: var(--color-element-success-element); color: var(--color-element-success-element); }

/* Payment History List */
.payment-history-item {
    display: flex;
    align-items: center;
    padding: 12px;
    border-bottom: 1px solid var(--color-border);
}
.payment-history-item:last-child { border-bottom: none; }

.payment-icon {
    margin-right: 12px;
    color: var(--color-text-maxcontrast);
}

.payment-info { flex: 1; }
.payment-amount { font-weight: 600; color: var(--color-main-text); }
.payment-date { font-size: 12px; color: var(--color-text-maxcontrast); }
.payment-note { font-size: 12px; font-style: italic; color: var(--color-text-maxcontrast); }

/* Text Utilities using CSS Vars */
.text-error { color: var(--color-element-error); }
.text-success { color: var(--color-element-success-element); }
.text-warning { color: var(--color-warning); }
.text-bold { font-weight: bold; }

.link-primary { color: var(--color-primary); text-decoration: none; }
.link-primary:hover { text-decoration: underline; }

.empty-details {
    align-items: center;
    justify-content: center;
    color: var(--color-text-maxcontrast);
}

.spin { animation: spin 1s linear infinite; }
@keyframes spin { 100% { transform: rotate(360deg); } }

/* Mobile */
.mobile-only { display: none; }

@media (max-width: 768px) {
    .nc-app-content-list { flex: 1; width: 100%; max-width: none; }
    .nc-app-content-details {
        position: absolute; top: 0; left: 0; width: 100%; height: 100%;
        z-index: 100;
    }
    .mobile-hidden { display: none; }
    .mobile-only { display: flex; margin-right: 12px; }
}
</style>