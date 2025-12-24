<template>
    <div class="hostings-view-container">
        <!-- ========================================== -->
        <!-- MODALS                                     -->
        <!-- ========================================== -->
        <HostingModal
            :open="modalOpen"
            :hosting="editingHosting"
            :clients="clients"
            :packages="hostingPackages"
            @close="closeModal"
            @saved="handleHostingSaved"
        />

        <HostingPaymentModal
            :open="paymentModalOpen"
            :hosting="payingHosting"
            @close="closePaymentModal"
            @paid="handleHostingPaid"
        />

        <HostingPackageModal
            :open="packageModalOpen"
            :package="editingPackage"
            @close="closePackageModal"
            @saved="handlePackageSaved"
        />

        <!-- ========================================== -->
        <!-- PACKAGES VIEW                              -->
        <!-- ========================================== -->
        <div v-if="showPackagesView" class="nc-sub-view">
            <!-- Header -->
            <div class="nc-section-header">
                <div class="header-left">
                    <NcButton type="tertiary" @click="showPackagesView = false">
                        <template #icon><ArrowLeft :size="20" /></template>
                        {{ translate('domaincontrol', 'Back') }}
                    </NcButton>
                    <h2 class="nc-app-title">{{ translate('domaincontrol', 'Hosting Packages') }}</h2>
                </div>
                <div class="header-actions">
                    <NcButton type="primary" @click="showPackageModal()">
                        <template #icon><Plus :size="20" /></template>
                        {{ translate('domaincontrol', 'Add Package') }}
                    </NcButton>
                </div>
            </div>

            <!-- Content -->
            <div v-if="hostingPackages.length === 0 && !packagesLoading" class="nc-empty-state">
                <PackageVariant :size="64" class="nc-state-icon" />
                <h3>{{ translate('domaincontrol', 'No packages yet') }}</h3>
                <NcButton type="primary" @click="showPackageModal()" class="mt-4">
                    {{ translate('domaincontrol', 'Add First Package') }}
                </NcButton>
            </div>

            <div v-else-if="packagesLoading" class="nc-loading-state">
                <Refresh :size="48" class="spin-animation nc-state-icon" />
                <p>{{ translate('domaincontrol', 'Loading packages...') }}</p>
            </div>

            <div v-else class="nc-list-container">
                <div v-for="pkg in hostingPackages" :key="pkg.id" class="nc-list-item" @click="editPackage(pkg)">
                    <div class="item-icon-wrapper">
                        <PackageVariant :size="24" />
                    </div>
                    <div class="item-content">
                        <div class="item-title">{{ pkg.name }}</div>
                        <div class="item-subtitle">
                            {{ pkg.provider }} 
                            <span v-if="pkg.priceMonthly" class="bullet-sep">•</span>
                            <span v-if="pkg.priceMonthly">{{ formatCurrency(pkg.priceMonthly, pkg.currency) }}/{{ translate('domaincontrol', 'mo') }}</span>
                        </div>
                    </div>
                    <div class="item-status">
                        <span class="nc-badge" :class="pkg.isActive ? 'badge-success' : 'badge-neutral'">
                            {{ pkg.isActive ? translate('domaincontrol', 'Active') : translate('domaincontrol', 'Inactive') }}
                        </span>
                    </div>
                    <div class="item-actions">
                        <button class="action-btn" @click.stop="editPackage(pkg)" :title="translate('domaincontrol', 'Edit')">
                            <Pencil :size="18" />
                        </button>
                        <button class="action-btn delete-hover" @click.stop="confirmDeletePackage(pkg)" :title="translate('domaincontrol', 'Delete')">
                            <Delete :size="18" />
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- HOSTING LIST VIEW                          -->
        <!-- ========================================== -->
        <div v-else-if="!selectedHosting" class="nc-main-view">
            <!-- Header -->
            <div class="nc-section-header">
                <div class="header-left">
                    <h2 class="nc-app-title">
                        <ServerNetwork :size="24" class="header-icon" />
                        {{ translate('domaincontrol', 'Hostings') }}
                    </h2>
                </div>
                <div class="header-actions">
                    <div class="search-wrapper">
                        <Magnify :size="20" class="search-icon" />
                        <input
                            type="text"
                            v-model="searchQuery"
                            class="nc-input search-input"
                            :placeholder="translate('domaincontrol', 'Search hostings...')"
                        />
                    </div>
                    <NcButton type="secondary" @click="showPackagesView = true">
                        <template #icon><PackageVariant :size="20" /></template>
                        {{ translate('domaincontrol', 'Packages') }}
                    </NcButton>
                    <NcButton type="primary" @click="showAddModal">
                        <template #icon><Plus :size="20" /></template>
                        {{ translate('domaincontrol', 'Add Hosting') }}
                    </NcButton>
                </div>
            </div>

            <!-- Empty / Loading -->
            <div v-if="loading" class="nc-loading-state">
                <Refresh :size="48" class="spin-animation nc-state-icon" />
                <p>{{ translate('domaincontrol', 'Loading hostings...') }}</p>
            </div>

            <div v-else-if="filteredHostings.length === 0" class="nc-empty-state">
                <ServerNetwork :size="64" class="nc-state-icon" />
                <h3>{{ searchQuery ? translate('domaincontrol', 'No hostings found') : translate('domaincontrol', 'No hostings yet') }}</h3>
                <NcButton v-if="!searchQuery" type="primary" @click="showAddModal" class="mt-4">
                    {{ translate('domaincontrol', 'Add First Hosting') }}
                </NcButton>
            </div>

            <!-- List -->
            <div v-else class="nc-list-container">
                <div 
                    v-for="hosting in filteredHostings" 
                    :key="hosting.id" 
                    class="nc-list-item hosting-row"
                    :class="getHostingStatusClass(hosting)"
                    @click="selectHosting(hosting)"
                >
                    <div class="item-avatar">
                        <div class="avatar-circle" :style="{ backgroundColor: getAvatarColor(hosting.provider) }">
                            {{ hosting.provider.substring(0,2).toUpperCase() }}
                        </div>
                    </div>
                    
                    <div class="item-content">
                        <div class="item-title">
                            {{ hosting.provider }} 
                            <span v-if="hosting.plan" class="plan-tag">{{ hosting.plan }}</span>
                        </div>
                        <div class="item-subtitle">
                            <span v-if="getClientName(hosting.clientId) !== 'Unassigned'">
                                <Account :size="12" class="inline-icon"/> {{ getClientName(hosting.clientId) }}
                            </span>
                            <span v-if="hosting.serverIp" class="ml-2">
                                <IpNetwork :size="12" class="inline-icon"/> {{ hosting.serverIp }}
                            </span>
                        </div>
                    </div>

                    <div class="item-meta desktop-only">
                        <div class="meta-block">
                            <span class="meta-label">{{ translate('domaincontrol', 'Next Payment') }}</span>
                            <span class="meta-value">{{ formatDate(hosting.expirationDate) }}</span>
                        </div>
                        <div class="meta-block">
                            <span class="meta-label">{{ translate('domaincontrol', 'Status') }}</span>
                            <span class="nc-badge" :class="getHostingStatusBadgeClass(hosting)">
                                {{ getHostingStatusText(hosting) }}
                            </span>
                        </div>
                    </div>

                    <div class="item-actions">
                        <button class="action-btn" @click.stop="editHosting(hosting)" :title="translate('domaincontrol', 'Edit')">
                            <Pencil :size="18" />
                        </button>
                        <button class="action-btn delete-hover" @click.stop="confirmDelete(hosting)" :title="translate('domaincontrol', 'Delete')">
                            <Delete :size="18" />
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- HOSTING DETAIL VIEW                        -->
        <!-- ========================================== -->
        <div v-else class="nc-detail-view">
            <!-- Header -->
            <div class="nc-detail-header">
                <div class="header-left">
                    <NcButton type="tertiary" @click="backToList">
                        <template #icon><ArrowLeft :size="20" /></template>
                        {{ translate('domaincontrol', 'Back') }}
                    </NcButton>
                    <div class="detail-avatar" :style="{ backgroundColor: getAvatarColor(selectedHosting.provider) }">
                        {{ selectedHosting.provider.substring(0,1).toUpperCase() }}
                    </div>
                    <div class="detail-title-group">
                        <h2 class="detail-title">{{ selectedHosting.provider }}</h2>
                        <span v-if="selectedHosting.plan" class="detail-subtitle">{{ selectedHosting.plan }}</span>
                    </div>
                </div>
                <div class="header-actions">
                    <NcButton type="success" @click="showPaymentModal(selectedHosting)">
                        <template #icon><CurrencyUsd :size="20" /></template>
                        {{ translate('domaincontrol', 'Add Payment') }}
                    </NcButton>
                    <NcButton type="secondary" @click="editHosting(selectedHosting)">
                        <template #icon><Pencil :size="20" /></template>
                        {{ translate('domaincontrol', 'Edit') }}
                    </NcButton>
                    <NcButton type="error" @click="confirmDelete(selectedHosting)">
                        <template #icon><Delete :size="20" /></template>
                        {{ translate('domaincontrol', 'Delete') }}
                    </NcButton>
                </div>
            </div>

            <!-- Content Grid -->
            <div class="nc-detail-content">
                
                <!-- Left Column (Main) -->
                <div class="detail-column main">
                    
                    <!-- KPI Cards -->
                    <div class="stats-grid">
                        <div class="stat-widget">
                            <div class="widget-icon">
                                <CalendarClock :size="24" />
                            </div>
                            <div class="widget-info">
                                <span class="label">{{ translate('domaincontrol', 'Next Payment') }}</span>
                                <span class="value">{{ formatDate(selectedHosting.expirationDate) || '-' }}</span>
                            </div>
                        </div>
                        <div class="stat-widget" :class="getHostingStatusClass(selectedHosting) + '-bg'">
                            <div class="widget-icon">
                                <Timelapse :size="24" />
                            </div>
                            <div class="widget-info">
                                <span class="label">{{ translate('domaincontrol', 'Days Left') }}</span>
                                <span class="value">{{ getDaysUntilExpiry(selectedHosting.expirationDate) }}</span>
                            </div>
                        </div>
                        <div class="stat-widget">
                            <div class="widget-icon">
                                <CurrencyUsd :size="24" />
                            </div>
                            <div class="widget-info">
                                <span class="label">{{ translate('domaincontrol', 'Cost') }}</span>
                                <span class="value">{{ formatCurrency(selectedHosting.price, selectedHosting.currency) || '-' }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Information Panel -->
                    <div class="nc-panel">
                        <div class="panel-header">
                            <h3>{{ translate('domaincontrol', 'Hosting Details') }}</h3>
                        </div>
                        <div class="panel-body">
                            <div class="info-row">
                                <span class="row-label">{{ translate('domaincontrol', 'Client') }}</span>
                                <span class="row-value link" @click="navigateToClient(selectedHosting.clientId)">
                                    {{ getClientName(selectedHosting.clientId) }}
                                </span>
                            </div>
                            <div class="info-row" v-if="selectedHosting.serverIp">
                                <span class="row-label">{{ translate('domaincontrol', 'Server IP') }}</span>
                                <span class="row-value font-mono">{{ selectedHosting.serverIp }}</span>
                            </div>
                            <div class="info-row" v-if="selectedHosting.serverType">
                                <span class="row-label">{{ translate('domaincontrol', 'Type') }}</span>
                                <span class="row-value">
                                    {{ selectedHosting.serverType === 'own' ? translate('domaincontrol', 'Own Server') : translate('domaincontrol', 'External Server') }}
                                </span>
                            </div>
                            <div class="info-row" v-if="selectedHosting.renewalInterval">
                                <span class="row-label">{{ translate('domaincontrol', 'Cycle') }}</span>
                                <span class="row-value">{{ formatRenewalInterval(selectedHosting.renewalInterval) }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Panel Login Info -->
                    <div class="nc-panel">
                        <div class="panel-header">
                            <h3>
                                <Login :size="18" class="inline-icon" />
                                {{ translate('domaincontrol', 'Panel Access') }}
                            </h3>
                        </div>
                        <div class="panel-body">
                             <div v-if="selectedHosting.panelUrl" class="info-row">
                                <span class="row-label">URL</span>
                                <a :href="selectedHosting.panelUrl" target="_blank" class="row-value link">{{ selectedHosting.panelUrl }}</a>
                            </div>
                            <div class="notes-box font-mono">
                                {{ selectedHosting.panelNotes || translate('domaincontrol', 'No login info available') }}
                            </div>
                        </div>
                    </div>

                    <!-- Payment History -->
                    <div class="nc-panel">
                        <div class="panel-header">
                            <h3>
                                <History :size="18" class="inline-icon" />
                                {{ translate('domaincontrol', 'Payment History') }}
                            </h3>
                        </div>
                         <div v-if="getPaymentHistory(selectedHosting).length === 0" class="empty-mini">
                            {{ translate('domaincontrol', 'No payment history') }}
                        </div>
                        <div v-else class="history-list">
                             <div v-for="(entry, index) in getPaymentHistory(selectedHosting)" :key="index" class="history-item">
                                <div class="hist-date"><Calendar :size="14" /> {{ formatDate(entry.date) }}</div>
                                <div class="hist-amount text-success">+ {{ formatCurrency(entry.amount, entry.currency) }}</div>
                                <div class="hist-period">{{ entry.period }} {{ translate('domaincontrol', 'months') }}</div>
                             </div>
                        </div>
                    </div>

                </div>

                <!-- Right Column (Sidebar) -->
                <div class="detail-column sidebar">
                    
                    <!-- Linked Domains -->
                    <div class="nc-panel">
                        <div class="panel-header">
                            <h3>
                                <Web :size="18" class="inline-icon" />
                                {{ translate('domaincontrol', 'Domains') }}
                            </h3>
                            <span class="count-badge">{{ getLinkedDomains(selectedHosting.id).length }}</span>
                        </div>
                        <div class="mini-list">
                            <div v-if="getLinkedDomains(selectedHosting.id).length === 0" class="empty-mini">
                                {{ translate('domaincontrol', 'No linked domains') }}
                            </div>
                            <div v-for="domain in getLinkedDomains(selectedHosting.id)" :key="domain.id" class="mini-item" @click="navigateToDomain(domain.id)">
                                <span class="mini-title">{{ domain.domainName }}</span>
                                <span class="mini-sub">{{ formatDate(domain.expirationDate) }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Linked Websites -->
                    <div class="nc-panel">
                        <div class="panel-header">
                            <h3>
                                <WebBox :size="18" class="inline-icon" />
                                {{ translate('domaincontrol', 'Websites') }}
                            </h3>
                            <span class="count-badge">{{ getLinkedWebsites(selectedHosting.id).length }}</span>
                        </div>
                         <div class="mini-list">
                            <div v-if="getLinkedWebsites(selectedHosting.id).length === 0" class="empty-mini">
                                {{ translate('domaincontrol', 'No linked websites') }}
                            </div>
                            <div v-for="website in getLinkedWebsites(selectedHosting.id)" :key="website.id" class="mini-item" @click="navigateToWebsite(website.id)">
                                <span class="mini-title">{{ website.name || website.software }}</span>
                                <span class="mini-sub">{{ website.url || '-' }}</span>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { NcButton } from '@nextcloud/vue'
import api from '../services/api'
import HostingModal from './HostingModal.vue'
import HostingPaymentModal from './HostingPaymentModal.vue'
import HostingPackageModal from './HostingPackageModal.vue'

// Icons (Standard Nextcloud/Material Design Icons)
import ServerNetwork from 'vue-material-design-icons/ServerNetwork.vue'
import Magnify from 'vue-material-design-icons/Magnify.vue'
import Plus from 'vue-material-design-icons/Plus.vue'
import Refresh from 'vue-material-design-icons/Refresh.vue'
import ArrowLeft from 'vue-material-design-icons/ArrowLeft.vue'
import Pencil from 'vue-material-design-icons/Pencil.vue'
import Delete from 'vue-material-design-icons/Delete.vue'
import PackageVariant from 'vue-material-design-icons/PackageVariant.vue'
import Account from 'vue-material-design-icons/Account.vue'
import IpNetwork from 'vue-material-design-icons/IpNetwork.vue'
import CalendarClock from 'vue-material-design-icons/CalendarClock.vue'
import Timelapse from 'vue-material-design-icons/Timelapse.vue'
import CurrencyUsd from 'vue-material-design-icons/CurrencyUsd.vue'
import Login from 'vue-material-design-icons/Login.vue'
import History from 'vue-material-design-icons/History.vue'
import Calendar from 'vue-material-design-icons/Calendar.vue'
import Web from 'vue-material-design-icons/Web.vue'
import WebBox from 'vue-material-design-icons/WebBox.vue'

export default {
    name: 'Hostings',
    components: {
        NcButton,
        HostingModal,
        HostingPaymentModal,
        HostingPackageModal,
        // Icons
        ServerNetwork, Magnify, Plus, Refresh, ArrowLeft, Pencil, Delete, 
        PackageVariant, Account, IpNetwork, CalendarClock, Timelapse, 
        CurrencyUsd, Login, History, Calendar, Web, WebBox
    },
    data() {
        return {
            hostings: [],
            hostingPackages: [],
            clients: [],
            domains: [],
            websites: [],
            selectedHosting: null,
            searchQuery: '',
            loading: false,
            packagesLoading: false,
            modalOpen: false,
            editingHosting: null,
            paymentModalOpen: false,
            payingHosting: null,
            showPackagesView: false,
            packageModalOpen: false,
            editingPackage: null,
        }
    },
    computed: {
        filteredHostings() {
            if (!this.searchQuery) return this.hostings
            const query = this.searchQuery.toLowerCase()
            return this.hostings.filter(hosting => {
                return (
                    hosting.provider?.toLowerCase().includes(query) ||
                    hosting.plan?.toLowerCase().includes(query) ||
                    this.getClientName(hosting.clientId)?.toLowerCase().includes(query) ||
                    hosting.serverIp?.toLowerCase().includes(query)
                )
            })
        },
    },
    mounted() {
        this.loadHostings()
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
            // English Fallback
            const dict = {
                'mo': 'mo'
            }
            return dict[text] || text
        },
        async loadHostings() {
            this.loading = true
            try {
                const response = await api.hostings.getAll()
                this.hostings = response.data || []
            } catch (error) {
                console.error(error)
                this.hostings = []
            } finally {
                this.loading = false
            }
        },
        async loadHostingPackages() {
            this.packagesLoading = true
            try {
                const response = await api.hostingPackages.getAll()
                this.hostingPackages = response.data || []
            } catch (error) {
                console.error(error)
                this.hostingPackages = []
            } finally {
                this.packagesLoading = false
            }
        },
        async loadRelatedData() {
            try {
                const [clientsRes, domainsRes, websitesRes] = await Promise.all([
                    api.clients.getAll().catch(() => ({ data: [] })),
                    api.domains.getAll().catch(() => ({ data: [] })),
                    api.websites.getAll().catch(() => ({ data: [] })),
                ])
                this.clients = clientsRes.data || []
                this.domains = domainsRes.data || []
                this.websites = websitesRes.data || []
            } catch (error) {
                console.error(error)
            }
        },
        selectHosting(hosting) {
            this.selectedHosting = hosting
        },
        backToList() {
            this.selectedHosting = null
        },
        showAddModal() {
            this.editingHosting = null
            this.modalOpen = true
        },
        editHosting(hosting) {
            this.editingHosting = hosting
            this.modalOpen = true
        },
        closeModal() {
            this.modalOpen = false
            this.editingHosting = null
        },
        handleHostingSaved() {
            this.closeModal()
            this.loadHostings()
            this.loadRelatedData()
        },
        showPaymentModal(hosting) {
            this.payingHosting = hosting
            this.paymentModalOpen = true
        },
        closePaymentModal() {
            this.paymentModalOpen = false
            this.payingHosting = null
        },
        handleHostingPaid() {
            this.closePaymentModal()
            this.loadHostings()
        },
        showPackageModal(pkg = null) {
            this.editingPackage = pkg
            this.packageModalOpen = true
        },
        editPackage(pkg) {
            this.showPackageModal(pkg)
        },
        closePackageModal() {
            this.packageModalOpen = false
            this.editingPackage = null
        },
        handlePackageSaved() {
            this.closePackageModal()
            this.loadHostingPackages()
        },
        async confirmDelete(hosting) {
            if (confirm(this.translate('domaincontrol', `Are you sure you want to delete this hosting?`))) {
                try {
                    await api.hostings.delete(hosting.id)
                    this.backToList()
                    this.loadHostings()
                    this.loadRelatedData()
                } catch (error) {
                    alert('Error deleting hosting')
                }
            }
        },
        async confirmDeletePackage(pkg) {
            if (confirm(this.translate('domaincontrol', `Are you sure you want to delete package ${pkg.name}?`))) {
                try {
                    await api.hostingPackages.delete(pkg.id)
                    this.loadHostingPackages()
                } catch (error) {
                     alert('Error deleting package')
                }
            }
        },
        formatDate(dateString) {
            if (!dateString) return '-'
            try {
                return new Date(dateString).toLocaleDateString(undefined, { year: 'numeric', month: 'short', day: 'numeric' })
            } catch (e) { return dateString.split(' ')[0] }
        },
        getDaysUntilExpiry(expirationDate) {
            if (!expirationDate) return 0
            const today = new Date()
            today.setHours(0, 0, 0, 0)
            const expiry = new Date(expirationDate)
            expiry.setHours(0, 0, 0, 0)
            return Math.ceil((expiry - today) / (1000 * 60 * 60 * 24))
        },
        getHostingStatusClass(hosting) {
            const days = this.getDaysUntilExpiry(hosting.expirationDate)
            if (days <= 0) return 'status-critical'
            if (days <= 30) return 'status-warning'
            return 'status-ok'
        },
        getHostingStatusText(hosting) {
            const days = this.getDaysUntilExpiry(hosting.expirationDate)
            if (days <= 0) return this.translate('domaincontrol', 'Expired')
            if (days <= 7) return this.translate('domaincontrol', 'Critical')
            if (days <= 30) return this.translate('domaincontrol', 'Renewal Due')
            return this.translate('domaincontrol', 'Active')
        },
        getHostingStatusBadgeClass(hosting) {
            const status = this.getHostingStatusClass(hosting)
            if(status === 'status-critical') return 'badge-error'
            if(status === 'status-warning') return 'badge-warning'
            return 'badge-success'
        },
        getClientName(clientId) {
            const client = this.clients.find(c => c.id == clientId)
            return client ? client.name : this.translate('domaincontrol', 'Unassigned')
        },
        getLinkedDomains(hostingId) { return this.domains.filter(d => d.hostingId == hostingId) },
        getLinkedWebsites(hostingId) { return this.websites.filter(w => w.hostingId == hostingId) },
        getPaymentHistory(hosting) {
            try { return hosting.paymentHistory ? JSON.parse(hosting.paymentHistory) : [] } 
            catch (e) { return [] }
        },
        formatCurrency(amount, currency) {
            if (amount == null) return ''
            const symbol = { USD: '$', EUR: '€', TRY: '₺' }[currency] || ''
            return `${symbol}${parseFloat(amount).toFixed(2)}`
        },
        formatRenewalInterval(interval) { return interval }, // Simplification
        navigateToClient(id) { if(window.DomainControl?.switchTab) window.DomainControl.switchTab('clients') },
        navigateToDomain(id) { if(window.DomainControl?.switchTab) window.DomainControl.switchTab('domains') },
        navigateToWebsite(id) { if(window.DomainControl?.switchTab) window.DomainControl.switchTab('websites') },
        
        getAvatarColor(name) {
             if (!name) return '#999'
            let hash = 0
            for (let i = 0; i < name.length; i++) {
                hash = name.charCodeAt(i) + ((hash << 5) - hash)
            }
            return `hsl(${hash % 360}, 65%, 45%)`
        }
    },
    watch: {
        showPackagesView(newVal) { if (newVal) this.loadHostingPackages() },
    },
}
</script>

<style scoped>
/* GLOBAL CONTAINER */
.hostings-view-container {
    padding: 20px;
    height: 100%;
    color: var(--color-main-text);
    font-family: var(--font-face, sans-serif);
}

.nc-main-view, .nc-sub-view {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

/* --- Header --- */
.nc-section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 24px;
    flex-wrap: wrap;
    gap: 16px;
}
.header-left { display: flex; align-items: center; gap: 12px; }
.nc-app-title { margin: 0; font-size: 24px; font-weight: bold; display: flex; align-items: center; gap: 12px; }
.header-icon { opacity: 0.8; color: var(--color-text-maxcontrast); }
.header-actions { display: flex; align-items: center; gap: 12px; }

/* Search Bar */
.search-wrapper { position: relative; width: 250px; }
.search-icon { position: absolute; left: 10px; top: 50%; transform: translateY(-50%); color: var(--color-text-maxcontrast); opacity: 0.7; }
.search-input { width: 100%; padding: 8px 12px 8px 36px; border-radius: var(--border-radius-pill); border: 1px solid var(--color-border); background: var(--color-main-background); color: var(--color-main-text); }
.search-input:focus { border-color: var(--color-primary); outline: none; }

/* --- Lists --- */
.nc-list-container {
    display: flex; flex-direction: column; background: var(--color-main-background);
    border: 1px solid var(--color-border); border-radius: var(--border-radius-large); overflow: hidden;
}

.nc-list-item {
    display: flex; align-items: center; padding: 12px 16px; border-bottom: 1px solid var(--color-border);
    cursor: pointer; transition: background 0.1s ease;
}
.nc-list-item:last-child { border-bottom: none; }
.nc-list-item:hover { background-color: var(--color-background-hover); }

/* Status Indicators Border */
.status-critical { border-left: 4px solid var(--color-error-element); }
.status-warning { border-left: 4px solid var(--color-warning-element); }
.status-ok { border-left: 4px solid transparent; } /* Default alignment */

/* Hosting Row Items */
.item-avatar { margin-right: 16px; }
.avatar-circle {
    width: 42px; height: 42px; border-radius: 50%; color: #fff; display: flex; align-items: center; justify-content: center;
    font-weight: bold; font-size: 16px; text-shadow: 0 1px 2px rgba(0,0,0,0.2);
}

.item-content { flex: 1; min-width: 0; display: flex; flex-direction: column; gap: 2px; }
.item-title { font-weight: 600; font-size: 16px; color: var(--color-main-text); display: flex; align-items: center; gap: 8px; }
.plan-tag { font-size: 12px; background: var(--color-background-dark); padding: 1px 6px; border-radius: 4px; color: var(--color-text-maxcontrast); font-weight: normal; }
.item-subtitle { font-size: 13px; color: var(--color-text-maxcontrast); display: flex; align-items: center; }
.inline-icon { opacity: 0.7; margin-right: 4px; }
.ml-2 { margin-left: 12px; }
.bullet-sep { margin: 0 6px; opacity: 0.5; }

.item-meta { display: flex; gap: 24px; margin-right: 20px; align-items: center; }
.meta-block { display: flex; flex-direction: column; align-items: flex-end; }
.meta-label { font-size: 11px; color: var(--color-text-maxcontrast); text-transform: uppercase; margin-bottom: 2px; }
.meta-value { font-size: 14px; font-weight: 500; }

.nc-badge { font-size: 11px; padding: 2px 8px; border-radius: 10px; font-weight: 600; text-transform: uppercase; }
.badge-success { background: rgba(70, 186, 97, 0.15); color: var(--color-success-element); }
.badge-warning { background: rgba(233, 144, 2, 0.15); color: var(--color-warning-element); }
.badge-error { background: rgba(233, 50, 45, 0.15); color: var(--color-error-element); }
.badge-neutral { background: var(--color-background-dark); color: var(--color-text-maxcontrast); }

.item-actions { display: flex; gap: 4px; opacity: 0.6; transition: opacity 0.2s; }
.nc-list-item:hover .item-actions { opacity: 1; }
.action-btn {
    background: none; border: none; padding: 6px; color: var(--color-text-maxcontrast); cursor: pointer; border-radius: 4px;
}
.action-btn:hover { background: var(--color-background-dark); color: var(--color-main-text); }
.delete-hover:hover { color: var(--color-error-element); background: rgba(233, 50, 45, 0.1); }

/* --- Detail View --- */
.nc-detail-header {
    display: flex; justify-content: space-between; align-items: center; padding-bottom: 20px; border-bottom: 1px solid var(--color-border);
}
.detail-avatar {
    width: 48px; height: 48px; border-radius: 50%; color: #fff; display: flex; align-items: center; justify-content: center;
    font-weight: bold; font-size: 20px; margin-left: 16px; margin-right: 16px;
}
.detail-title-group { display: flex; flex-direction: column; }
.detail-title { margin: 0; font-size: 24px; font-weight: bold; }
.detail-subtitle { font-size: 14px; color: var(--color-text-maxcontrast); }

.nc-detail-content { display: grid; grid-template-columns: 2fr 1fr; gap: 24px; }
.detail-column { display: flex; flex-direction: column; gap: 24px; }

/* Stats Grid */
.stats-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; }
.stat-widget {
    background: var(--color-main-background); border: 1px solid var(--color-border); border-radius: var(--border-radius-large);
    padding: 16px; display: flex; align-items: center; gap: 12px;
}
.widget-icon {
    width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center;
    color: var(--color-text-maxcontrast); background: var(--color-background-hover);
}
.status-critical-bg .widget-icon { color: var(--color-error-element); background: rgba(233, 50, 45, 0.1); }
.status-warning-bg .widget-icon { color: var(--color-warning-element); background: rgba(233, 144, 2, 0.1); }
.widget-info { display: flex; flex-direction: column; }
.widget-info .label { font-size: 12px; color: var(--color-text-maxcontrast); }
.widget-info .value { font-size: 18px; font-weight: bold; color: var(--color-main-text); }

/* Panels */
.nc-panel { background: var(--color-main-background); border: 1px solid var(--color-border); border-radius: var(--border-radius-large); overflow: hidden; }
.panel-header {
    padding: 12px 16px; background: var(--color-background-hover); border-bottom: 1px solid var(--color-border);
    display: flex; justify-content: space-between; align-items: center;
}
.panel-header h3 { margin: 0; font-size: 15px; font-weight: 600; display: flex; align-items: center; gap: 8px; }
.panel-body { padding: 0; }
.info-row { display: flex; align-items: center; padding: 12px 16px; border-bottom: 1px solid var(--color-border); font-size: 14px; }
.info-row:last-child { border-bottom: none; }
.row-label { width: 120px; color: var(--color-text-maxcontrast); }
.row-value { color: var(--color-main-text); font-weight: 500; }
.row-value.link { color: var(--color-primary); cursor: pointer; text-decoration: none; }
.row-value.link:hover { text-decoration: underline; }
.font-mono { font-family: monospace; }
.notes-box { padding: 16px; background: var(--color-background-dark); font-size: 13px; color: var(--color-text-maxcontrast); white-space: pre-wrap; word-break: break-all; }

/* History & Mini List */
.history-list, .mini-list { display: flex; flex-direction: column; }
.history-item, .mini-item {
    display: flex; align-items: center; padding: 10px 16px; border-bottom: 1px solid var(--color-border);
    font-size: 13px; gap: 12px;
}
.history-item:last-child, .mini-item:last-child { border-bottom: none; }
.mini-item { justify-content: space-between; cursor: pointer; transition: background 0.1s; }
.mini-item:hover { background: var(--color-background-hover); }
.mini-title { font-weight: 500; }
.mini-sub { color: var(--color-text-maxcontrast); }

.hist-date { flex: 1; display: flex; align-items: center; gap: 6px; color: var(--color-text-maxcontrast); }
.hist-amount { font-weight: 600; width: 100px; text-align: right; }
.hist-period { width: 80px; text-align: right; color: var(--color-text-maxcontrast); }
.text-success { color: var(--color-success-element); }

/* Package Item */
.item-icon-wrapper { 
    width: 40px; height: 40px; border-radius: 8px; background: var(--color-background-hover); 
    display: flex; align-items: center; justify-content: center; color: var(--color-text-maxcontrast); margin-right: 16px; 
}

/* Empty & Loading */
.nc-empty-state, .nc-loading-state {
    padding: 60px; text-align: center; display: flex; flex-direction: column; align-items: center;
    color: var(--color-text-maxcontrast);
}
.nc-state-icon { opacity: 0.5; margin-bottom: 16px; }
.spin-animation { animation: spin 1s linear infinite; }
.count-badge { background: var(--color-primary); color: #fff; padding: 1px 8px; border-radius: 10px; font-size: 11px; font-weight: bold; }
.mt-4 { margin-top: 16px; }

/* Responsive */
@media (max-width: 900px) {
    .nc-detail-content { grid-template-columns: 1fr; }
    .desktop-only { display: none; }
    .stats-grid { grid-template-columns: 1fr; }
}
</style>