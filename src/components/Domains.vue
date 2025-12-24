<template>
    <div class="domains-view-container">
        
        <!-- ========================================== -->
        <!-- MODALS                                     -->
        <!-- ========================================== -->
        <DomainModal
            :open="modalOpen"
            :domain="editingDomain"
            :clients="clients"
            @close="closeModal"
            @saved="handleDomainSaved"
        />

        <DomainExtendModal
            :open="extendModalOpen"
            :domain="extendingDomain"
            @close="closeExtendModal"
            @extended="handleDomainExtended"
        />

        <!-- ========================================== -->
        <!-- LIST VIEW                                  -->
        <!-- ========================================== -->
        <div v-if="!selectedDomain" class="nc-content-wrapper">
            <!-- Header Actions -->
            <div class="nc-section-header">
                <div class="header-left">
                    <h2 class="nc-app-title">{{ translate('domaincontrol', 'Domains') }}</h2>
                    <span class="nc-counter-badge" v-if="filteredDomains.length > 0">
                        {{ filteredDomains.length }}
                    </span>
                </div>
                <div class="header-actions">
                    <NcButton type="secondary" @click="testEmail">
                        <template #icon>
                            <Email :size="20" />
                        </template>
                        {{ translate('domaincontrol', 'Test Email') }}
                    </NcButton>
                    <NcButton type="primary" @click="showAddModal">
                        <template #icon>
                            <Plus :size="20" />
                        </template>
                        {{ translate('domaincontrol', 'Add Domain') }}
                    </NcButton>
                </div>
            </div>

            <!-- Loading State -->
            <div v-if="loading" class="nc-empty-state">
                <Refresh :size="48" class="spin-animation nc-state-icon" />
                <p>{{ translate('domaincontrol', 'Loading domains...') }}</p>
            </div>

            <!-- Empty State -->
            <div v-else-if="filteredDomains.length === 0" class="nc-empty-state">
                <Web :size="64" class="nc-state-icon" />
                <h2>{{ translate('domaincontrol', 'No domains yet') }}</h2>
                <p>{{ translate('domaincontrol', 'Add your first domain to start tracking renewals and details.') }}</p>
                <br>
                <NcButton type="primary" @click="showAddModal">
                    {{ translate('domaincontrol', 'Add First Domain') }}
                </NcButton>
            </div>

            <!-- Domain List Table -->
            <div v-else class="nc-domain-list">
                <div class="nc-list-header">
                    <div class="col-icon"></div>
                    <div class="col-name">{{ translate('domaincontrol', 'Domain Name') }}</div>
                    <div class="col-client">{{ translate('domaincontrol', 'Client') }}</div>
                    <div class="col-expiry">{{ translate('domaincontrol', 'Expiry') }}</div>
                    <div class="col-status">{{ translate('domaincontrol', 'Status') }}</div>
                    <div class="col-actions"></div>
                </div>

                <div
                    v-for="domain in filteredDomains"
                    :key="domain.id"
                    class="nc-list-item"
                    @click="selectDomain(domain)"
                >
                    <div class="col-icon">
                        <div class="item-icon-bg">
                            <Web :size="24" />
                        </div>
                    </div>
                    
                    <div class="col-name">
                        <span class="text-bold">{{ domain.domainName }}</span>
                        <span class="text-subtle small-text">{{ domain.registrar }}</span>
                    </div>

                    <div class="col-client">
                        <div v-if="domain.clientId" class="client-tag">
                            <Account :size="14" />
                            {{ getClientName(domain.clientId) }}
                        </div>
                        <span v-else class="text-subtle">{{ translate('domaincontrol', 'Not specified') }}</span>
                    </div>

                    <div class="col-expiry">
                        <span>{{ formatDate(domain.expirationDate) }}</span>
                        <span class="text-subtle small-text">{{ getDaysUntilExpiry(domain.expirationDate) }}</span>
                    </div>

                    <div class="col-status">
                         <span class="nc-status-badge" :class="getDomainStatusClass(domain)">
                            {{ getDomainStatusText(domain) }}
                        </span>
                    </div>

                    <div class="col-actions">
                         <button
                            class="nc-action-icon primary-hover"
                            @click.stop="showExtendModal(domain)"
                            :title="translate('domaincontrol', 'Extend')"
                        >
                            <span class="extend-text">+{{ domain.renewalInterval || 1 }}{{ translate('domaincontrol', 'Year') }}</span>
                        </button>
                        <button
                            class="nc-action-icon"
                            @click.stop="editDomain(domain.id)"
                            :title="translate('domaincontrol', 'Edit')"
                        >
                            <Pencil :size="20" />
                        </button>
                        <button
                            class="nc-action-icon delete-hover"
                            @click.stop="confirmDelete(domain)"
                            :title="translate('domaincontrol', 'Delete')"
                        >
                            <Delete :size="20" />
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- DETAIL VIEW                                -->
        <!-- ========================================== -->
        <div v-else class="nc-detail-wrapper">
            <!-- Breadcrumb / Header -->
            <div class="nc-detail-header">
                <div class="header-left">
                    <NcButton type="tertiary" @click="backToList">
                        <template #icon>
                            <ArrowLeft :size="20" />
                        </template>
                        {{ translate('domaincontrol', 'Back') }}
                    </NcButton>
                    <h2 class="detail-title">{{ selectedDomain.domainName }}</h2>
                </div>
                <div class="header-actions">
                    <NcButton type="success" @click="showExtendModal(selectedDomain)">
                         <template #icon>
                            <Update :size="20" />
                        </template>
                        {{ translate('domaincontrol', 'Extend') }}
                    </NcButton>
                    <NcButton type="secondary" @click="editDomain(selectedDomain.id)">
                        <template #icon>
                            <Pencil :size="20" />
                        </template>
                        {{ translate('domaincontrol', 'Edit') }}
                    </NcButton>
                    <NcButton type="error" @click="confirmDelete(selectedDomain)">
                        <template #icon>
                            <Delete :size="20" />
                        </template>
                        {{ translate('domaincontrol', 'Delete') }}
                    </NcButton>
                </div>
            </div>

            <!-- Detail Grid -->
            <div class="nc-detail-grid">
                
                <!-- KPI Widgets -->
                <div class="nc-widget-row">
                    <div class="nc-widget">
                        <div class="widget-icon primary-bg">
                            <CalendarClock :size="24" />
                        </div>
                        <div class="widget-content">
                            <span class="widget-label">{{ translate('domaincontrol', 'Expiry Date') }}</span>
                            <span class="widget-value">{{ formatDate(selectedDomain.expirationDate) }}</span>
                        </div>
                    </div>
                    
                    <div class="nc-widget">
                        <div class="widget-icon" :class="getDomainStatusClass(selectedDomain)">
                            <TimerSand :size="24" />
                        </div>
                        <div class="widget-content">
                            <span class="widget-label">{{ translate('domaincontrol', 'Days Left') }}</span>
                            <span class="widget-value" :class="getTextStatusClass(selectedDomain)">
                                {{ getDaysUntilExpiry(selectedDomain.expirationDate) }}
                            </span>
                        </div>
                    </div>

                    <div class="nc-widget">
                         <div class="widget-icon info-bg">
                            <CheckCircle :size="24" />
                        </div>
                        <div class="widget-content">
                            <span class="widget-label">{{ translate('domaincontrol', 'Status') }}</span>
                            <span class="widget-value">
                                 {{ getDomainStatusText(selectedDomain) }}
                            </span>
                        </div>
                    </div>

                    <div class="nc-widget">
                        <div class="widget-icon success-bg">
                            <Cash :size="24" />
                        </div>
                        <div class="widget-content">
                            <span class="widget-label">{{ translate('domaincontrol', 'Price') }}</span>
                            <span class="widget-value font-mono">
                                {{ formatCurrency(selectedDomain.price, selectedDomain.currency) }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Info Panels Layout -->
                <div class="nc-panels-layout">
                    <!-- Left Column -->
                    <div class="layout-col main">
                        <!-- General Info -->
                        <div class="nc-panel">
                            <div class="nc-panel-header">
                                <h3>{{ translate('domaincontrol', 'General Information') }}</h3>
                            </div>
                            <div class="nc-key-value-list">
                                <div class="kv-item">
                                    <span class="key">{{ translate('domaincontrol', 'Client') }}</span>
                                    <span class="value">
                                        <a v-if="selectedDomain.clientId" href="#" class="nc-link">
                                            {{ getClientName(selectedDomain.clientId) }}
                                        </a>
                                        <span v-else>{{ translate('domaincontrol', 'Not specified') }}</span>
                                    </span>
                                </div>
                                <div class="kv-item">
                                    <span class="key">{{ translate('domaincontrol', 'Registrar') }}</span>
                                    <span class="value">{{ selectedDomain.registrar || translate('domaincontrol', 'Not specified') }}</span>
                                </div>
                                <div class="kv-item">
                                    <span class="key">{{ translate('domaincontrol', 'Registration Date') }}</span>
                                    <span class="value">{{ formatDate(selectedDomain.registrationDate) || translate('domaincontrol', 'Not specified') }}</span>
                                </div>
                                <div class="kv-item">
                                    <span class="key">{{ translate('domaincontrol', 'Renewal Interval') }}</span>
                                    <span class="value">{{ (selectedDomain.renewalInterval || 1) }} {{ translate('domaincontrol', 'Year') }}</span>
                                </div>
                                <div class="kv-item">
                                    <span class="key">{{ translate('domaincontrol', 'Hosting') }}</span>
                                    <span class="value">
                                         {{ getHostingName(selectedDomain.hostingId) || translate('domaincontrol', 'Not specified') }}
                                    </span>
                                </div>
                            </div>
                        </div>

                         <!-- Panel Access -->
                        <div class="nc-panel">
                            <div class="nc-panel-header">
                                <h3>
                                    <Lock :size="18" class="icon-inline" />
                                    {{ translate('domaincontrol', 'Panel Access') }}
                                </h3>
                            </div>
                            <div class="nc-panel-body">
                                <pre class="nc-code-block">{{ selectedDomain.panelNotes || translate('domaincontrol', 'No panel information available.') }}</pre>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column -->
                    <div class="layout-col side">
                        <!-- Linked Websites -->
                        <div class="nc-panel">
                            <div class="nc-panel-header">
                                <h3>{{ translate('domaincontrol', 'Linked Websites') }}</h3>
                            </div>
                            <div class="nc-simple-list">
                                <div v-if="getDomainWebsites(selectedDomain.id).length === 0" class="nc-empty-text small">
                                    {{ translate('domaincontrol', 'No websites linked') }}
                                </div>
                                <div
                                    v-for="website in getDomainWebsites(selectedDomain.id)"
                                    :key="website.id"
                                    class="nc-simple-list-item clickable"
                                    @click="navigateToWebsite(website.id)"
                                >
                                    <Web :size="18" class="item-icon" />
                                    <span>{{ website.name || website.software || translate('domaincontrol', 'Website') }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Renewal History -->
                        <div class="nc-panel">
                            <div class="nc-panel-header">
                                <h3>{{ translate('domaincontrol', 'Renewal History') }}</h3>
                            </div>
                            <div class="nc-history-stream">
                                <div v-if="getRenewalHistory(selectedDomain).length === 0" class="nc-empty-text small">
                                    {{ translate('domaincontrol', 'No renewals yet') }}
                                </div>
                                <div
                                    v-for="(entry, index) in getRenewalHistory(selectedDomain)"
                                    :key="index"
                                    class="history-entry"
                                >
                                    <div class="history-date">{{ formatDate(entry.date) }}</div>
                                    <div class="history-info">
                                        <div class="history-main">
                                            <strong>+{{ entry.years }} {{ translate('domaincontrol', 'Year(s)') }}</strong>
                                        </div>
                                        <div class="history-sub">
                                            {{ translate('domaincontrol', 'New expiry') }}: {{ formatDate(entry.newExpiry) }}
                                        </div>
                                        <div v-if="entry.price" class="history-sub">
                                            {{ translate('domaincontrol', 'Price') }}: {{ entry.price }}
                                        </div>
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
import DomainModal from './DomainModal.vue'
import DomainExtendModal from './DomainExtendModal.vue'
import { NcButton } from '@nextcloud/vue'
// vue-material-design-icons
import Email from 'vue-material-design-icons/Email.vue'
import Plus from 'vue-material-design-icons/Plus.vue'
import Refresh from 'vue-material-design-icons/Refresh.vue'
import Web from 'vue-material-design-icons/Web.vue'
import Account from 'vue-material-design-icons/Account.vue'
import Pencil from 'vue-material-design-icons/Pencil.vue'
import Delete from 'vue-material-design-icons/Delete.vue'
import ArrowLeft from 'vue-material-design-icons/ArrowLeft.vue'
import Update from 'vue-material-design-icons/Update.vue'
import CalendarClock from 'vue-material-design-icons/CalendarClock.vue'
import TimerSand from 'vue-material-design-icons/TimerSand.vue'
import CheckCircle from 'vue-material-design-icons/CheckCircle.vue'
import Cash from 'vue-material-design-icons/Cash.vue'
import Lock from 'vue-material-design-icons/Lock.vue'

export default {
    name: 'Domains',
    components: {
        DomainModal,
        DomainExtendModal,
        NcButton,
        Email,
        Plus,
        Refresh,
        Web,
        Account,
        Pencil,
        Delete,
        ArrowLeft,
        Update,
        CalendarClock,
        TimerSand,
        CheckCircle,
        Cash,
        Lock,
    },
    data() {
        return {
            domains: [],
            clients: [],
            hostings: [],
            websites: [],
            selectedDomain: null,
            loading: false,
            modalOpen: false,
            extendModalOpen: false,
            editingDomain: null,
            extendingDomain: null,
        }
    },
    computed: {
        filteredDomains() {
            return this.domains
        },
    },
    mounted() {
        this.loadDomains()
        this.loadRelatedData()
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
        async loadDomains() {
            this.loading = true
            try {
                const response = await api.domains.getAll()
                this.domains = response.data || []
            } catch (error) {
                this.domains = []
                console.error('Error loading domains:', error)
            } finally {
                this.loading = false
            }
        },
        async loadRelatedData() {
            try {
                const [clientsRes, hostingsRes, websitesRes] = await Promise.all([
                    api.clients.getAll().catch(() => ({ data: [] })),
                    api.hostings.getAll().catch(() => ({ data: [] })),
                    api.websites.getAll().catch(() => ({ data: [] })),
                ])
                this.clients = clientsRes.data || []
                this.hostings = hostingsRes.data || []
                this.websites = websitesRes.data || []
            } catch (error) {
                console.error('Error loading related data:', error)
            }
        },
        selectDomain(domain) {
            this.selectedDomain = domain
        },
        backToList() {
            this.selectedDomain = null
        },
        showAddModal() {
            this.editingDomain = null
            this.modalOpen = true
        },
        editDomain(id) {
            const domain = this.domains.find(d => d.id === id)
            if (domain) {
                this.editingDomain = domain
                this.modalOpen = true
            }
        },
        showExtendModal(domain) {
            this.extendingDomain = domain
            this.extendModalOpen = true
        },
        closeModal() {
            this.modalOpen = false
            this.editingDomain = null
        },
        closeExtendModal() {
            this.extendModalOpen = false
            this.extendingDomain = null
        },
        async handleDomainSaved() {
            await this.loadDomains()
            this.closeModal()
            if (this.selectedDomain) {
                const updated = this.domains.find(d => d.id === this.selectedDomain.id)
                if (updated) this.selectedDomain = updated
            }
        },
        async handleDomainExtended() {
            await this.loadDomains()
            this.closeExtendModal()
            if (this.selectedDomain) {
                const updated = this.domains.find(d => d.id === this.selectedDomain.id)
                if (updated) this.selectedDomain = updated
            }
        },
        confirmDelete(domain) {
            if (confirm(this.translate('domaincontrol', 'Are you sure you want to delete {name}?', { name: domain.domainName }))) {
                this.deleteDomain(domain.id)
            }
        },
        async deleteDomain(id) {
            try {
                await api.domains.delete(id)
                await this.loadDomains()
                if (this.selectedDomain && this.selectedDomain.id === id) {
                    this.selectedDomain = null
                }
            } catch (error) {
                alert(this.translate('domaincontrol', 'Error deleting domain'))
            }
        },
        getClientName(clientId) {
            const client = this.clients.find(c => c.id === clientId)
            return client ? client.name : null
        },
        getHostingName(hostingId) {
            const hosting = this.hostings.find(h => h.id === hostingId)
            return hosting ? `${hosting.provider}${hosting.plan ? ' - ' + hosting.plan : ''}` : null
        },
        getDomainWebsites(domainId) {
            return (this.websites || []).filter(w => w.domainId === domainId)
        },
        getRenewalHistory(domain) {
            if (!domain.renewalHistory) return []
            try {
                const history = JSON.parse(domain.renewalHistory)
                return Array.isArray(history) ? history.slice().reverse() : []
            } catch (e) { return [] }
        },
        getDaysUntilExpiry(expirationDate) {
            if (!expirationDate) return '-'
            const today = new Date()
            today.setHours(0, 0, 0, 0)
            const expiry = new Date(expirationDate)
            expiry.setHours(0, 0, 0, 0)
            const diff = Math.ceil((expiry - today) / (1000 * 60 * 60 * 24))
            if (diff < 0) return this.translate('domaincontrol', 'Expired')
            if (diff === 0) return this.translate('domaincontrol', 'Today')
            return `${diff} ${this.translate('domaincontrol', 'days')}`
        },
        getDaysUntilExpiryNumber(expirationDate) {
             if (!expirationDate) return 999
            const today = new Date()
            today.setHours(0, 0, 0, 0)
            const expiry = new Date(expirationDate)
            expiry.setHours(0, 0, 0, 0)
            return Math.ceil((expiry - today) / (1000 * 60 * 60 * 24))
        },
        getDomainStatusClass(domain) {
            const daysLeft = this.getDaysUntilExpiryNumber(domain.expirationDate)
            if (daysLeft <= 0) return 'status-expired'
            if (daysLeft <= 7) return 'status-critical'
            if (daysLeft <= 30) return 'status-warning'
            return 'status-ok'
        },
        getTextStatusClass(domain) {
            const daysLeft = this.getDaysUntilExpiryNumber(domain.expirationDate)
            if (daysLeft <= 0) return 'text-error'
            if (daysLeft <= 7) return 'text-error'
            if (daysLeft <= 30) return 'text-warning'
            return 'text-success'
        },
        getDomainStatusText(domain) {
            const daysLeft = this.getDaysUntilExpiryNumber(domain.expirationDate)
            if (daysLeft <= 0) return this.translate('domaincontrol', 'Expired')
            if (daysLeft <= 7) return this.translate('domaincontrol', 'Critical')
            if (daysLeft <= 30) return this.translate('domaincontrol', 'Renew Soon')
            return this.translate('domaincontrol', 'Active')
        },
        formatDate(date) {
            if (!date) return '-'
            try {
                return new Date(date).toLocaleDateString('tr-TR', { year: 'numeric', month: 'short', day: 'numeric' })
            } catch (e) { return date }
        },
        formatCurrency(amount, currency = 'USD') {
            if (!amount) return '-'
            return new Intl.NumberFormat('tr-TR', { style: 'currency', currency: currency || 'USD' }).format(amount)
        },
        navigateToWebsite(websiteId) {
            if (typeof window.DomainControl !== 'undefined' && window.DomainControl.switchTab) {
                window.DomainControl.switchTab('websites')
                // Add short delay to allow tab switch
                setTimeout(() => {
                    if (window.DomainControl.showWebsiteDetail) window.DomainControl.showWebsiteDetail(websiteId)
                }, 100)
            }
        },
        testEmail() {
            alert(this.translate('domaincontrol', 'Test email functionality coming soon'))
        },
    },
}
</script>

<style scoped>
/* GLOBAL STYLES */
.domains-view-container {
    padding: 20px;
    height: 100%;
    overflow-y: auto;
    color: var(--color-main-text);
    font-family: var(--font-face, sans-serif);
}

.nc-content-wrapper {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

/* --- Header --- */
.nc-section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin: 0 0 0 31px;
}

.header-left { display: flex; align-items: center; gap: 12px; }
.nc-app-title { margin: 0; font-size: 24px; font-weight: bold; color: var(--color-main-text); }

.nc-counter-badge {
    background-color: var(--color-background-hover);
    padding: 2px 8px;
    border-radius: 10px;
    font-size: 13px;
    font-weight: bold;
    color: var(--color-text-maxcontrast);
}

.header-actions { display: flex; gap: 8px; }

/* --- Empty States --- */
.nc-empty-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 60px 20px;
    text-align: center;
    background: var(--color-main-background);
    border-radius: var(--border-radius-large);
    color: var(--color-text-maxcontrast);
}

.nc-state-icon { margin-bottom: 16px; opacity: 0.5; color: var(--color-text-maxcontrast); }
.spin-animation { animation: spin 1s linear infinite; }
@keyframes spin { 100% { transform: rotate(360deg); } }

/* --- Domain List (Table-like) --- */
.nc-domain-list {
    display: flex;
    flex-direction: column;
    background: var(--color-main-background);
    border-radius: var(--border-radius-large);
    border: 1px solid var(--color-border);
}

.nc-list-header {
    display: flex;
    padding: 12px 16px;
    background-color: var(--color-background-hover);
    border-bottom: 1px solid var(--color-border);
    font-size: 13px;
    font-weight: 600;
    color: var(--color-text-maxcontrast);
    text-transform: uppercase;
    border-top-left-radius: var(--border-radius-large);
    border-top-right-radius: var(--border-radius-large);
}

.nc-list-item {
    display: flex;
    align-items: center;
    padding: 12px 16px;
    border-bottom: 1px solid var(--color-border);
    transition: background 0.1s;
    cursor: pointer;
}
.nc-list-item:last-child { border-bottom: none; }
.nc-list-item:hover { background-color: var(--color-background-hover); }

/* Columns */
.col-icon { width: 48px; flex-shrink: 0; }
.col-name { flex: 2; min-width: 150px; display: flex; flex-direction: column; }
.col-client { flex: 1.5; min-width: 120px; color: var(--color-text-maxcontrast); font-size: 14px; }
.col-expiry { flex: 1; min-width: 100px; display: flex; flex-direction: column; font-size: 14px; }
.col-status { width: 120px; flex-shrink: 0; }
.col-actions { width: 120px; display: flex; justify-content: flex-end; align-items: center; gap: 4px; }

/* Column Details */
.item-icon-bg {
    width: 36px; height: 36px;
    background-color: var(--color-background-dark);
    border-radius: 8px;
    display: flex; align-items: center; justify-content: center;
    color: var(--color-text-maxcontrast);
}

.text-bold { font-weight: 600; color: var(--color-main-text); font-size: 15px; }
.text-subtle { color: var(--color-text-maxcontrast); }
.small-text { font-size: 12px; }

.client-tag { display: flex; align-items: center; gap: 6px; }

/* Status Badges - Nextcloud durum renkleri değişkenleri */
.nc-status-badge {
    padding: 6px 12px;
    border-radius: 16px;
    font-size: 12px;
    font-weight: 600;
    display: inline-block;
    text-align: center;
    min-width: 90px;
    letter-spacing: 0.3px;
}
/* Active - Success */
.status-ok {
    background-color: var(--color-element-success);
    color: var(--color-element-success-text);
}
/* Warning */
.status-warning {
    background-color: var(--color-element-warning);
    color: var(--color-element-warning-text);
}
/* Critical - Error */
.status-critical {
    background-color: var(--color-element-error);
    color: var(--color-element-error-text);
}
/* Expired - Neutral */
.status-expired {
    background-color: var(--color-background-dark);
    color: var(--color-text-maxcontrast);
}

/* Action Buttons */
.nc-action-icon {
    background: none; border: none; padding: 6px; cursor: pointer; color: var(--color-text-maxcontrast); border-radius: 4px; transition: all 0.2s; opacity: 0;
}
.nc-list-item:hover .nc-action-icon { opacity: 1; }
.nc-action-icon:hover { background-color: var(--color-background-dark); color: var(--color-main-text); }
.primary-hover:hover { color: var(--color-primary-element-element); background-color: rgba(0, 130, 201, 0.1); }
.delete-hover:hover { color: var(--color-element-error); background-color: rgba(233, 50, 45, 0.1); }
.extend-text { font-size: 11px; font-weight: bold; }

/* ========================================== */
/* DETAIL VIEW STYLES                         */
/* ========================================== */
.nc-detail-wrapper { display: flex; flex-direction: column; gap: 24px; }
.nc-detail-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-bottom: 16px;
    border-bottom: 1px solid var(--color-border);
    margin: 0 0 0 31px;
}
.detail-title { margin: 0 0 0 16px; font-size: 24px; font-weight: bold; }

/* Widgets */
.nc-widget-row { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; }
.nc-widget {
    background: var(--color-main-background);
    border: 1px solid var(--color-border);
    border-radius: var(--border-radius-large);
    padding: 16px;
    display: flex; align-items: center; gap: 16px;
}
.widget-icon {
    width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center;
    color: var(--color-text-maxcontrast); background-color: var(--color-background-hover);
}
.widget-content { display: flex; flex-direction: column; }
.widget-label { font-size: 13px; color: var(--color-text-maxcontrast); margin-bottom: 4px; }
.widget-value { font-size: 18px; font-weight: bold; color: var(--color-main-text); }

/* Colors for widgets */
.primary-bg { background-color: rgba(0, 130, 201, 0.1); color: var(--color-primary-element-element); }
.success-bg { background-color: rgba(70, 186, 97, 0.1); color: var(--color-element-success); }
.info-bg { background-color: rgba(0, 130, 201, 0.05); color: var(--color-text-maxcontrast); }
.text-error { color: var(--color-element-error); }
.text-warning { color: var(--color-element-warning); }
.text-success { color: var(--color-element-success); }
.font-mono { font-family: monospace; }

/* Panels Layout */
.nc-panels-layout { display: grid; grid-template-columns: 2fr 1fr; gap: 24px; }
.layout-col { display: flex; flex-direction: column; gap: 24px; }

.nc-panel {
    background: var(--color-main-background);
    border: 1px solid var(--color-border);
    border-radius: var(--border-radius-large);
    overflow: hidden;
}
.nc-panel-header { padding: 16px; border-bottom: 1px solid var(--color-border); background: var(--color-background-hover); }
.nc-panel-header h3 { margin: 0; font-size: 16px; font-weight: 600; display: flex; align-items: center; gap: 8px; }
.nc-panel-body { padding: 16px; }

/* Key-Value List */
.nc-key-value-list { display: flex; flex-direction: column; }
.kv-item {
    display: flex; justify-content: space-between; padding: 12px 16px; border-bottom: 1px solid var(--color-border);
}
.kv-item:last-child { border-bottom: none; }
.kv-item .key { color: var(--color-text-maxcontrast); font-size: 14px; }
.kv-item .value { font-weight: 500; text-align: right; }

/* Code Block */
.nc-code-block {
    background: var(--color-background-dark); padding: 12px; border-radius: var(--border-radius); font-family: monospace; font-size: 13px; white-space: pre-wrap; margin: 0;
}

/* Simple List (Linked Websites) */
.nc-simple-list { display: flex; flex-direction: column; }
.nc-simple-list-item {
    display: flex; align-items: center; gap: 12px; padding: 12px 16px; border-bottom: 1px solid var(--color-border);
}
.nc-simple-list-item.clickable { cursor: pointer; transition: background 0.1s; }
.nc-simple-list-item.clickable:hover { background-color: var(--color-background-hover); }
.nc-empty-text { padding: 16px; text-align: center; color: var(--color-text-maxcontrast); font-style: italic; }

/* History Stream */
.nc-history-stream { padding: 0 16px; }
.history-entry {
    padding: 12px 0; border-bottom: 1px solid var(--color-border); display: flex; flex-direction: column; gap: 4px;
}
.history-entry:last-child { border-bottom: none; }
.history-date { font-size: 12px; color: var(--color-text-maxcontrast); }
.history-main { font-size: 14px; color: var(--color-main-text); }
.history-sub { font-size: 12px; color: var(--color-text-maxcontrast); }

/* Icon Styles */
.icon-inline {
    margin-right: 6px;
    vertical-align: middle;
}
.item-icon {
    margin-right: 8px;
    flex-shrink: 0;
}

/* Mobile */
@media (max-width: 900px) {
    .nc-list-header { display: none; }
    .nc-list-item { flex-wrap: wrap; gap: 8px; }
    .col-name { width: 100%; margin-bottom: 4px; }
    .col-client, .col-expiry, .col-status { width: auto; font-size: 13px; }
    .col-actions { margin-left: auto; width: auto; }
    .nc-action-icon { opacity: 1; }
    
    .nc-panels-layout { grid-template-columns: 1fr; }
    .nc-widget-row { grid-template-columns: 1fr 1fr; }
}
</style>