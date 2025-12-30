<template>
    <div class="app-content-wrapper">
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
        <!-- LEFT PANE: LIST                            -->
        <!-- ========================================== -->
        <div class="app-content-list" :class="{ 'mobile-hidden': isMobile && selectedDomain }">
            <div class="app-content-list-header">
                <div class="search-wrapper">
                    <div class="search-wrapper-inner">
                        <Magnify :size="20" class="search-icon" />
                        <input type="text" v-model="searchQuery" :placeholder="translate('domaincontrol', 'Domain ara...')" class="search-input" />
                    </div>
                </div>
                <div class="app-navigation__search">
                    <header class="header">
                        <div class="import-and-new-contact-buttons">
                            <NcButton 
                                type="secondary" 
                                :wide="true"
                                @click="showAddModal">
                                <template #icon>
                                    <Plus :size="20" />
                                </template>
                                {{ translate('domaincontrol', 'Yeni Domain') }}
                            </NcButton>
                        </div>
                    </header>
                </div>
            </div>

            <div class="app-content-list-wrapper">
                <div v-if="loading" class="loading-container">
                    <Refresh :size="32" class="spin-animation" />
                </div>
                <div v-else-if="filteredDomains.length === 0" class="empty-list">
                    <div class="empty-text">{{ translate('domaincontrol', 'Domain bulunamadı') }}</div>
                </div>
                <ul v-else class="app-navigation-list">
                    <li 
                        v-for="domain in filteredDomains" 
                        :key="domain.id" 
                        class="app-navigation-entry" 
                        :class="{ 'active': selectedDomain && selectedDomain.id === domain.id }" 
                        @click="selectDomain(domain)"
                    >
                        <div class="app-navigation-entry-icon">
                            <div class="avatar-circle domain-avatar" :style="{ backgroundColor: getDomainColor(domain.domainName) }">
                                <Web :size="20" />
                            </div>
                        </div>
                        <div class="app-navigation-entry-content">
                            <div class="app-navigation-entry-name">{{ domain.domainName || domain.domain_name || '-' }}</div>
                            <div class="app-navigation-entry-details">
                                <span v-if="getClientName(domain.clientId)">{{ getClientName(domain.clientId) }}</span>
                                <span v-else-if="domain.registrar">{{ domain.registrar }}</span>
                                <span v-else>{{ translate('domaincontrol', 'Domain') }}</span>
                            </div>
                        </div>
                        <div class="app-navigation-entry-status">
                            <span class="status-dot-small" :class="getDomainStatusClass(domain)"></span>
                        </div>
                    </li>
                </ul>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- RIGHT PANE: DETAIL VIEW                    -->
        <!-- ========================================== -->
        <div class="app-content-details" :class="{ 'mobile-hidden': isMobile && !selectedDomain }">

            <!-- Empty State -->
            <div v-if="!selectedDomain" class="empty-content">
                <div class="empty-content-icon"><Web :size="64" /></div>
                <h2 class="empty-content-title">{{ translate('domaincontrol', 'Bir domain seçin') }}</h2>
            </div>

            <!-- Detail Content -->
            <div v-else class="crm-detail-container">
                
                <!-- HEADER -->
                <div class="crm-header">
                    <div class="crm-header-top">
                        <button v-if="isMobile" class="icon-button back-button" @click="backToList">
                            <ArrowLeft :size="24" />
                        </button>
                        <div class="crm-profile-info">
                            <div class="avatar-xl domain-avatar-xl" :style="{ backgroundColor: getDomainColor(selectedDomain.domainName) }">
                                <Web :size="36" />
                            </div>
                            <div class="crm-profile-text">
                                <h1 class="crm-client-name">{{ selectedDomain.domainName || selectedDomain.domain_name || '-' }}</h1>
                                <div class="crm-client-meta">
                                    <span v-if="getClientName(selectedDomain.clientId)" class="meta-item">
                                        <Account :size="14" />
                                        <span>{{ getClientName(selectedDomain.clientId) }}</span>
                                    </span>
                                    <span v-if="selectedDomain.registrar" class="meta-item">
                                        <span>{{ selectedDomain.registrar }}</span>
                                    </span>
                                    <span class="meta-item" :class="getTextStatusClass(selectedDomain)">
                                        <span>{{ getDomainStatusText(selectedDomain) }}</span>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="crm-header-actions">
                            <NcButton type="success" @click="showExtendModal(selectedDomain)">
                                <template #icon>
                                    <Update :size="18" />
                                </template>
                                {{ translate('domaincontrol', 'Uzat') }}
                            </NcButton>
                            <NcButton @click="editDomain(selectedDomain.id)">
                                {{ translate('domaincontrol', 'Düzenle') }}
                            </NcButton>
                            <button class="icon-button danger" @click="confirmDelete(selectedDomain)">
                                <Delete :size="20" />
                            </button>
                        </div>
                    </div>

                    <!-- TABS -->
                    <div class="crm-tabs-scroll">
                        <div class="crm-tabs">
                            <button class="crm-tab" :class="{ active: activeTab === 'overview' }" @click="activeTab = 'overview'">
                                {{ translate('domaincontrol', 'Genel Bakış') }}
                            </button>
                            <button class="crm-tab" :class="{ active: activeTab === 'websites' }" @click="activeTab = 'websites'">
                                {{ translate('domaincontrol', 'Web Siteleri') }} 
                                <span class="tab-badge" v-if="getDomainWebsites(selectedDomain.id).length">{{ getDomainWebsites(selectedDomain.id).length }}</span>
                            </button>
                            <button class="crm-tab" :class="{ active: activeTab === 'history' }" @click="activeTab = 'history'">
                                {{ translate('domaincontrol', 'Yenileme Geçmişi') }}
                            </button>
                        </div>
                    </div>
                </div>

                <!-- CONTENT SCROLL AREA -->
                <div class="crm-content-scroll">
                    
                    <!-- 1. GENEL BAKIŞ -->
                    <div v-if="activeTab === 'overview'" class="tab-pane">
                        <!-- Stats Grid -->
                        <div class="stats-grid">
                            <div class="stat-card">
                                <div class="stat-icon" style="background-color: rgba(0, 130, 201, 0.1);">
                                    <CalendarClock :size="24" style="color: var(--color-primary-element);" />
                                </div>
                                <div class="stat-content">
                                    <div class="stat-label">{{ translate('domaincontrol', 'Bitiş Tarihi') }}</div>
                                    <div class="stat-value">{{ formatDate(selectedDomain.expirationDate || selectedDomain.expiration_date) }}</div>
                                </div>
                            </div>
                            
                            <div class="stat-card">
                                <div class="stat-icon" :style="{ backgroundColor: getDaysUntilExpiryNumber(selectedDomain.expirationDate || selectedDomain.expiration_date) <= 30 ? 'rgba(233, 50, 45, 0.1)' : 'rgba(70, 186, 97, 0.1)' }">
                                    <TimerSand :size="24" :style="{ color: getDaysUntilExpiryNumber(selectedDomain.expirationDate || selectedDomain.expiration_date) <= 30 ? 'var(--color-element-error)' : 'var(--color-element-success)' }" />
                                </div>
                                <div class="stat-content">
                                    <div class="stat-label">{{ translate('domaincontrol', 'Kalan Gün') }}</div>
                                    <div class="stat-value" :class="getTextStatusClass(selectedDomain)">
                                        {{ getDaysUntilExpiry(selectedDomain.expirationDate || selectedDomain.expiration_date) }}
                                    </div>
                                </div>
                            </div>

                            <div class="stat-card">
                                <div class="stat-icon" style="background-color: rgba(0, 130, 201, 0.1);">
                                    <CheckCircle :size="24" style="color: var(--color-primary-element);" />
                                </div>
                                <div class="stat-content">
                                    <div class="stat-label">{{ translate('domaincontrol', 'Durum') }}</div>
                                    <div class="stat-value">
                                        <span class="status-badge" :class="getDomainStatusClass(selectedDomain)">
                                            {{ getDomainStatusText(selectedDomain) }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div class="stat-card">
                                <div class="stat-icon" style="background-color: rgba(70, 186, 97, 0.1);">
                                    <Cash :size="24" style="color: var(--color-element-success);" />
                                </div>
                                <div class="stat-content">
                                    <div class="stat-label">{{ translate('domaincontrol', 'Fiyat') }}</div>
                                    <div class="stat-value">
                                        {{ formatCurrency(selectedDomain.price, selectedDomain.currency) }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- General Information -->
                        <div class="content-box">
                            <div class="box-header">
                                <h3>{{ translate('domaincontrol', 'Genel Bilgiler') }}</h3>
                            </div>
                            <div class="box-body">
                                <div class="info-grid">
                                    <div class="info-group">
                                        <div class="info-label">{{ translate('domaincontrol', 'Müşteri') }}</div>
                                        <div class="info-val">
                                            <a v-if="selectedDomain.clientId" href="#" class="info-link" @click.prevent="navigateToClient(selectedDomain.clientId)">
                                                <Account :size="14" />
                                                {{ getClientName(selectedDomain.clientId) }}
                                            </a>
                                            <span v-else>-</span>
                                        </div>
                                    </div>
                                    <div class="info-group">
                                        <div class="info-label">{{ translate('domaincontrol', 'Kayıt Firması') }}</div>
                                        <div class="info-val">{{ selectedDomain.registrar || '-' }}</div>
                                    </div>
                                    <div class="info-group">
                                        <div class="info-label">{{ translate('domaincontrol', 'Kayıt Tarihi') }}</div>
                                        <div class="info-val">{{ formatDate(selectedDomain.registrationDate || selectedDomain.registration_date) }}</div>
                                    </div>
                                    <div class="info-group">
                                        <div class="info-label">{{ translate('domaincontrol', 'Yenileme Süresi') }}</div>
                                        <div class="info-val">{{ (selectedDomain.renewalInterval || selectedDomain.renewal_interval || 1) }} {{ translate('domaincontrol', 'Yıl') }}</div>
                                    </div>
                                    <div class="info-group">
                                        <div class="info-label">{{ translate('domaincontrol', 'Hosting') }}</div>
                                        <div class="info-val">{{ getHostingName(selectedDomain.hostingId || selectedDomain.hosting_id) || '-' }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Panel Access -->
                        <div class="content-box" v-if="selectedDomain.panelNotes || selectedDomain.panel_notes">
                            <div class="box-header">
                                <h3>
                                    <Lock :size="18" class="inline-icon" />
                                    {{ translate('domaincontrol', 'Panel Erişimi') }}
                                </h3>
                            </div>
                            <div class="box-body">
                                <pre class="code-block">{{ selectedDomain.panelNotes || selectedDomain.panel_notes || translate('domaincontrol', 'Panel bilgisi yok.') }}</pre>
                            </div>
                        </div>
                    </div>

                    <!-- 2. WEB SİTELERİ -->
                    <div v-if="activeTab === 'websites'" class="tab-pane">
                        <div class="pane-actions">
                            <NcButton type="primary" @click="navigateToWebsite(null)">
                                <template #icon><Plus :size="20" /></template>
                                {{ translate('domaincontrol', 'Web Sitesi Ekle') }}
                            </NcButton>
                        </div>
                        <div v-if="getDomainWebsites(selectedDomain.id).length === 0" class="empty-box">
                            {{ translate('domaincontrol', 'Bu domain\'e bağlı web sitesi yok') }}
                        </div>
                        <div v-else class="websites-list">
                            <div 
                                v-for="website in getDomainWebsites(selectedDomain.id)" 
                                :key="website.id" 
                                class="website-item"
                                @click="navigateToWebsite(website.id)"
                            >
                                <div class="website-icon">
                                    <Web :size="24" />
                                </div>
                                <div class="website-info">
                                    <div class="website-name">{{ website.name || website.software || translate('domaincontrol', 'Web Sitesi') }}</div>
                                    <div class="website-meta">
                                        <span v-if="website.software">{{ website.software }}</span>
                                        <span v-if="website.url">{{ website.url }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 3. YENİLEME GEÇMİŞİ -->
                    <div v-if="activeTab === 'history'" class="tab-pane">
                        <div v-if="getRenewalHistory(selectedDomain).length === 0" class="empty-box">
                            {{ translate('domaincontrol', 'Henüz yenileme yapılmadı') }}
                        </div>
                        <div v-else class="renewal-history">
                            <div
                                v-for="(entry, index) in getRenewalHistory(selectedDomain)"
                                :key="index"
                                class="history-entry"
                            >
                                <div class="history-date">{{ formatDate(entry.date) }}</div>
                                <div class="history-content">
                                    <div class="history-main">
                                        <strong>+{{ entry.years }} {{ translate('domaincontrol', 'Yıl') }}</strong>
                                    </div>
                                    <div class="history-sub">
                                        {{ translate('domaincontrol', 'Yeni bitiş tarihi') }}: {{ formatDate(entry.newExpiry) }}
                                    </div>
                                    <div v-if="entry.price" class="history-sub">
                                        {{ translate('domaincontrol', 'Fiyat') }}: {{ formatCurrency(entry.price, selectedDomain.currency) }}
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
import Magnify from 'vue-material-design-icons/Magnify.vue'

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
        Magnify,
    },
    data() {
        return {
            domains: [],
            clients: [],
            hostings: [],
            websites: [],
            selectedDomain: null,
            loading: false,
            isMobile: window.innerWidth < 768,
            activeTab: 'overview',
            modalOpen: false,
            extendModalOpen: false,
            editingDomain: null,
            extendingDomain: null,
            searchQuery: '',
        }
    },
    computed: {
        filteredDomains() {
            if (!this.searchQuery) {
                return this.domains
            }
            
            const query = this.searchQuery.toLowerCase()
            return this.domains.filter(domain => {
                const domainName = domain.domainName || domain.domain_name || ''
                const registrar = domain.registrar || ''
                if (domainName.toLowerCase().includes(query) || registrar.toLowerCase().includes(query)) {
                    return true
                }
                if (domain.clientId || domain.client_id) {
                    const client = this.clients.find(c => c.id == (domain.clientId || domain.client_id))
                    if (client && client.name && client.name.toLowerCase().includes(query)) {
                        return true
                    }
                }
                return false
            })
        },
    },
    mounted() {
        this.loadDomains()
        this.loadRelatedData()
        window.addEventListener('resize', this.handleResize)
    },
    beforeUnmount() {
        window.removeEventListener('resize', this.handleResize)
    },
    methods: {
        handleResize() {
            this.isMobile = window.innerWidth < 768
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
            this.activeTab = 'overview'
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
            if (confirm(this.translate('domaincontrol', 'Bu domain\'i silmek istediğinizden emin misiniz?'))) {
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
                alert(this.translate('domaincontrol', 'Domain silinemedi'))
            }
        },
        getClientName(clientId) {
            if (!clientId) return null
            const client = this.clients.find(c => c.id == clientId)
            return client ? client.name : null
        },
        getHostingName(hostingId) {
            if (!hostingId) return null
            const hosting = this.hostings.find(h => h.id == hostingId)
            return hosting ? `${hosting.provider || ''}${hosting.plan ? ' - ' + hosting.plan : ''}`.trim() : null
        },
        getDomainWebsites(domainId) {
            return (this.websites || []).filter(w => (w.domainId == domainId || w.domain_id == domainId))
        },
        getRenewalHistory(domain) {
            if (!domain.renewalHistory && !domain.renewal_history) return []
            try {
                const history = JSON.parse(domain.renewalHistory || domain.renewal_history)
                return Array.isArray(history) ? history.slice().reverse() : []
            } catch (e) { 
                return [] 
            }
        },
        getDaysUntilExpiry(expirationDate) {
            if (!expirationDate) return '-'
            const today = new Date()
            today.setHours(0, 0, 0, 0)
            const expiry = new Date(expirationDate)
            expiry.setHours(0, 0, 0, 0)
            const diff = Math.ceil((expiry - today) / (1000 * 60 * 60 * 24))
            if (diff < 0) return this.translate('domaincontrol', 'Süresi Doldu')
            if (diff === 0) return this.translate('domaincontrol', 'Bugün')
            return `${diff} ${this.translate('domaincontrol', 'gün')}`
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
            const daysLeft = this.getDaysUntilExpiryNumber(domain.expirationDate || domain.expiration_date)
            if (daysLeft <= 0) return 'status-expired'
            if (daysLeft <= 7) return 'status-critical'
            if (daysLeft <= 30) return 'status-warning'
            return 'status-ok'
        },
        getTextStatusClass(domain) {
            const daysLeft = this.getDaysUntilExpiryNumber(domain.expirationDate || domain.expiration_date)
            if (daysLeft <= 0) return 'text-error'
            if (daysLeft <= 7) return 'text-error'
            if (daysLeft <= 30) return 'text-warning'
            return 'text-success'
        },
        getDomainStatusText(domain) {
            const daysLeft = this.getDaysUntilExpiryNumber(domain.expirationDate || domain.expiration_date)
            if (daysLeft <= 0) return this.translate('domaincontrol', 'Süresi Doldu')
            if (daysLeft <= 7) return this.translate('domaincontrol', 'Kritik')
            if (daysLeft <= 30) return this.translate('domaincontrol', 'Yakında Yenile')
            return this.translate('domaincontrol', 'Aktif')
        },
        formatDate(date) {
            if (!date) return '-'
            try {
                return new Date(date).toLocaleDateString('tr-TR', { year: 'numeric', month: 'short', day: 'numeric' })
            } catch (e) { 
                return date 
            }
        },
        formatCurrency(amount, currency = 'USD') {
            if (!amount) return '-'
            return new Intl.NumberFormat('tr-TR', { style: 'currency', currency: currency || 'USD' }).format(amount)
        },
        navigateToClient(clientId) {
            if (typeof window.DomainControl !== 'undefined' && window.DomainControl.selectClient) {
                window.DomainControl.selectClient(clientId)
            }
        },
        navigateToWebsite(websiteId) {
            if (typeof window.DomainControl !== 'undefined' && window.DomainControl.switchTab) {
                window.DomainControl.switchTab('websites')
                if (websiteId) {
                    setTimeout(() => {
                        if (window.DomainControl.showWebsiteDetail) {
                            window.DomainControl.showWebsiteDetail(websiteId)
                        }
                    }, 100)
                }
            }
        },
        getDomainColor(domainName) {
            if (!domainName) return '#0082c9'
            const colors = ['#0082c9', '#46ba61', '#f0ad4e', '#e3322d', '#5bc0de', '#9b59b6', '#e67e22', '#3498db']
            let hash = 0
            for (let i = 0; i < domainName.length; i++) {
                hash = domainName.charCodeAt(i) + ((hash << 5) - hash)
            }
            return colors[Math.abs(hash) % colors.length]
        },
        testEmail() {
            alert(this.translate('domaincontrol', 'Test email functionality coming soon'))
        },
    },
}
</script>

<style scoped>
/* Clients.vue'daki aynı CSS yapısını kullan */
.app-content-wrapper {
    display: flex;
    height: 100%;
    width: 100%;
    background-color: var(--color-main-background);
    overflow: hidden;
    color: var(--color-main-text);
}

/* ==========================================
   LEFT PANE: LIST
   ========================================== */
.app-content-list {
    width: 300px;
    min-width: 300px;
    display: flex;
    flex-direction: column;
    border-right: 1px solid var(--color-border);
    background-color: var(--color-main-background);
    z-index: 50;
}

.app-content-list-header {
    padding: 0;
    display: flex;
    flex-direction: column;
    border-bottom: 1px solid var(--color-border);
}

.app-navigation__search {
    padding: 0;
}

.app-navigation__search .header {
    padding: 12px 16px;
}

.import-and-new-contact-buttons {
    display: flex;
    gap: 8px;
}

.search-wrapper {
    position: relative;
    padding: 12px 16px;
    border-bottom: 1px solid var(--color-border);
}

.search-wrapper-inner {
    margin-left: 24px;
}

.search-icon {
    position: absolute;
    left: 45px;
    top: 50%;
    transform: translateY(-50%);
    opacity: 0.5;
    pointer-events: none;
    color: var(--color-text-maxcontrast);
}

.search-input {
    width: 100%;
    padding: 8px 12px 8px 34px !important;
    border: 1px solid transparent !important;
    border-radius: 8px !important;
    background-color: var(--color-background-dark) !important;
    color: var(--color-main-text);
    box-sizing: border-box;
    transition: all 0.2s ease;
}

.search-input:focus {
    background-color: var(--color-main-background) !important;
    border-color: var(--color-primary-element) !important;
    outline: none;
    box-shadow: 0 0 0 2px var(--color-primary-element-light);
}

.app-content-list-wrapper {
    flex: 1;
    overflow-y: auto;
}

.app-navigation-list { 
    list-style: none; 
    padding: 0; 
    margin: 0; 
}

.app-navigation-entry {
    display: flex;
    align-items: center;
    padding: 12px 15px;
    cursor: pointer;
    border-bottom: 1px solid transparent;
    transition: background-color 0.15s ease;
}

.app-navigation-entry:hover { 
    background-color: var(--color-background-hover); 
}

.app-navigation-entry.active { 
    background-color: var(--color-primary-element-light); 
    border-left: 3px solid var(--color-primary-element); 
}

.avatar-circle {
    width: 36px; 
    height: 36px;
    border-radius: 50%;
    color: white;
    display: flex; 
    align-items: center; 
    justify-content: center;
    font-size: 14px; 
    font-weight: 600;
}

.domain-avatar {
    background-color: var(--color-primary-element) !important;
}

.app-navigation-entry-content { 
    margin-left: 12px; 
    flex: 1; 
    min-width: 0; 
}

.app-navigation-entry-name { 
    font-weight: 600; 
    white-space: nowrap; 
    overflow: hidden; 
    text-overflow: ellipsis; 
    color: var(--color-main-text); 
}

.app-navigation-entry-details { 
    font-size: 12px; 
    color: var(--color-text-maxcontrast); 
    opacity: 0.7; 
    white-space: nowrap; 
    overflow: hidden; 
    text-overflow: ellipsis; 
}

.app-navigation-entry-status {
    margin-left: 8px;
    flex-shrink: 0;
}

.status-dot-small {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    display: inline-block;
}

.status-dot-small.status-ok {
    background-color: var(--color-element-success);
}

.status-dot-small.status-warning {
    background-color: var(--color-element-warning);
}

.status-dot-small.status-critical {
    background-color: var(--color-element-error);
}

.status-dot-small.status-expired {
    background-color: var(--color-text-maxcontrast);
}

/* ==========================================
   RIGHT PANE: DETAIL VIEW
   ========================================== */
.app-content-details {
    flex: 1;
    background-color: var(--color-background-hover);
    display: flex; 
    flex-direction: column; 
    min-width: 0;
}

.empty-content {
    flex: 1; 
    display: flex; 
    flex-direction: column;
    align-items: center; 
    justify-content: center;
    color: var(--color-text-maxcontrast); 
    opacity: 0.6;
}

.empty-content-icon {
    margin-bottom: 16px;
    opacity: 0.5;
}

.empty-content-title {
    font-size: 18px;
    font-weight: 600;
    margin: 0;
}

.crm-detail-container { 
    display: flex; 
    flex-direction: column; 
    height: 100%; 
}

/* HEADER */
.crm-header {
    background-color: var(--color-main-background);
    padding: 25px 25px 0 25px;
    border-bottom: 1px solid var(--color-border);
    flex-shrink: 0;
}

.crm-header-top { 
    display: flex; 
    justify-content: space-between; 
    align-items: center; 
    margin-bottom: 25px; 
}

.crm-profile-info { 
    display: flex; 
    align-items: center; 
    gap: 20px; 
}

.avatar-xl {
    width: 72px; 
    height: 72px; 
    border-radius: 50%;
    display: flex; 
    align-items: center; 
    justify-content: center;
    color: white; 
    font-size: 28px; 
    font-weight: bold;
    box-shadow: 0 4px 10px rgba(0,0,0,0.1);
}

.domain-avatar-xl {
    background-color: var(--color-primary-element) !important;
}

.crm-profile-text { 
    display: flex; 
    flex-direction: column; 
}

.crm-client-name { 
    margin: 0; 
    font-size: 24px; 
    font-weight: bold; 
    line-height: 1.2; 
    color: var(--color-main-text); 
}

.crm-client-meta {
    display: flex; 
    align-items: center; 
    gap: 16px;
    font-size: 14px; 
    color: var(--color-text-maxcontrast); 
    margin-top: 8px;
    flex-wrap: wrap;
}

.meta-item {
    display: inline-flex;
    align-items: center;
    gap: 6px;
}

.crm-header-actions {
    display: flex;
    gap: 8px;
    align-items: center;
}

.icon-button {
    background: none;
    border: none;
    padding: 8px;
    cursor: pointer;
    border-radius: var(--border-radius);
    color: var(--color-text-maxcontrast);
    transition: all 0.15s ease;
    display: flex;
    align-items: center;
    justify-content: center;
}

.icon-button:hover {
    background-color: var(--color-background-hover);
    color: var(--color-main-text);
}

.icon-button.danger:hover {
    background-color: rgba(233, 50, 45, 0.1);
    color: var(--color-element-error);
}

.icon-button.back-button {
    margin-right: 12px;
}

/* TABS */
.crm-tabs-scroll {
    overflow-x: auto;
    overflow-y: hidden;
    -webkit-overflow-scrolling: touch;
}

.crm-tabs {
    display: flex;
    gap: 0;
    border-bottom: 2px solid var(--color-border);
    min-width: min-content;
}

.crm-tab {
    padding: 12px 20px;
    background: none;
    border: none;
    border-bottom: 2px solid transparent;
    color: var(--color-text-maxcontrast);
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s ease;
    white-space: nowrap;
    display: flex;
    align-items: center;
    gap: 6px;
    margin-bottom: -2px;
}

.crm-tab:hover {
    color: var(--color-main-text);
    background-color: var(--color-background-hover);
}

.crm-tab.active {
    color: var(--color-primary-element);
    border-bottom-color: var(--color-primary-element);
    font-weight: 600;
}

.tab-badge {
    background-color: var(--color-background-dark);
    padding: 2px 6px;
    border-radius: 10px;
    font-size: 11px;
    font-weight: 600;
}

/* CONTENT SCROLL */
.crm-content-scroll {
    flex: 1;
    overflow-y: auto;
    padding: 24px;
}

.tab-pane {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

/* Stats Grid */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 16px;
}

.stat-card {
    background: var(--color-main-background);
    border: 1px solid var(--color-border);
    border-radius: var(--border-radius-large);
    padding: 16px;
    display: flex;
    align-items: center;
    gap: 16px;
}

.stat-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.stat-content {
    display: flex;
    flex-direction: column;
}

.stat-label {
    font-size: 13px;
    color: var(--color-text-maxcontrast);
    margin-bottom: 4px;
}

.stat-value {
    font-size: 18px;
    font-weight: bold;
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
    padding: 16px 20px;
    background: var(--color-background-hover);
    border-bottom: 1px solid var(--color-border);
}

.box-header h3 {
    margin: 0;
    font-size: 16px;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 8px;
}

.box-body {
    padding: 20px;
}

/* Info Grid */
.info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
}

.info-group {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.info-label {
    font-size: 12px;
    font-weight: 600;
    color: var(--color-text-maxcontrast);
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.info-val {
    font-size: 14px;
    color: var(--color-main-text);
    display: flex;
    align-items: center;
    gap: 6px;
}

.info-link {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    color: var(--color-primary-element);
    text-decoration: none;
    transition: opacity 0.15s;
}

.info-link:hover {
    opacity: 0.8;
}

/* Code Block */
.code-block {
    background: var(--color-background-dark);
    padding: 12px;
    border-radius: var(--border-radius);
    font-family: monospace;
    font-size: 13px;
    white-space: pre-wrap;
    margin: 0;
    color: var(--color-main-text);
}

/* Pane Actions */
.pane-actions {
    display: flex;
    gap: 12px;
    margin-bottom: 20px;
}

/* Empty Box */
.empty-box {
    padding: 40px 20px;
    text-align: center;
    color: var(--color-text-maxcontrast);
    font-style: italic;
    background: var(--color-main-background);
    border: 1px solid var(--color-border);
    border-radius: var(--border-radius-large);
}

/* Websites List */
.websites-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.website-item {
    background: var(--color-main-background);
    border: 1px solid var(--color-border);
    border-radius: var(--border-radius);
    padding: 16px;
    display: flex;
    align-items: center;
    gap: 16px;
    cursor: pointer;
    transition: all 0.15s ease;
}

.website-item:hover {
    background: var(--color-background-hover);
    border-color: var(--color-primary-element);
}

.website-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    background: var(--color-background-dark);
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--color-primary-element);
}

.website-info {
    flex: 1;
    min-width: 0;
}

.website-name {
    font-weight: 600;
    font-size: 15px;
    color: var(--color-main-text);
    margin-bottom: 4px;
}

.website-meta {
    font-size: 12px;
    color: var(--color-text-maxcontrast);
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
}

/* Renewal History */
.renewal-history {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.history-entry {
    background: var(--color-main-background);
    border: 1px solid var(--color-border);
    border-radius: var(--border-radius);
    padding: 16px;
    display: flex;
    gap: 16px;
}

.history-date {
    font-size: 12px;
    color: var(--color-text-maxcontrast);
    min-width: 120px;
    flex-shrink: 0;
}

.history-content {
    flex: 1;
}

.history-main {
    font-size: 15px;
    font-weight: 600;
    color: var(--color-main-text);
    margin-bottom: 4px;
}

.history-sub {
    font-size: 13px;
    color: var(--color-text-maxcontrast);
    margin-top: 2px;
}

/* Status Badges */
.status-badge {
    padding: 4px 10px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    display: inline-block;
}

.status-badge.status-ok {
    background-color: rgba(70, 186, 97, 0.15);
    color: var(--color-element-success);
}

.status-badge.status-warning {
    background-color: rgba(240, 173, 78, 0.15);
    color: var(--color-element-warning);
}

.status-badge.status-critical {
    background-color: rgba(233, 50, 45, 0.15);
    color: var(--color-element-error);
}

.status-badge.status-expired {
    background-color: var(--color-background-dark);
    color: var(--color-text-maxcontrast);
}

/* Text Colors */
.text-error {
    color: var(--color-element-error);
}

.text-warning {
    color: var(--color-element-warning);
}

.text-success {
    color: var(--color-element-success);
}

/* Loading */
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

/* Empty List */
.empty-list {
    padding: 40px 20px;
    text-align: center;
}

.empty-text {
    color: var(--color-text-maxcontrast);
    font-style: italic;
}

/* Mobile */
@media (max-width: 768px) {
    .app-content-list {
        width: 100%;
        border: none;
    }
    
    .mobile-hidden {
        display: none !important;
    }
    
    .stats-grid {
        grid-template-columns: 1fr;
    }
    
    .info-grid {
        grid-template-columns: 1fr;
    }
}
</style>
