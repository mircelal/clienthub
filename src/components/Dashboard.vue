<template>
    <div class="dashboard-container">
        <!-- Welcome Header -->
        <div class="nc-header">
            <div class="nc-header-left">
                <h2 class="nc-title">
                    {{ translate('domaincontrol', 'Welcome, {name}', { name: currentUserName }) }}
                </h2>
                <p class="nc-subtitle">
                    {{ translate('domaincontrol', "Here's what's happening in your business today.") }}
                </p>
            </div>
            <div class="nc-header-right">
                <div class="nc-date-badge">
                    <Calendar :size="16" />
                    {{ currentDate }}
                </div>
            </div>
        </div>

        <!-- Main Stats Grid (Native Widget Style) -->
        <div class="stats-grid-main">
            <!-- Clients Widget -->
            <div v-if="isModuleActive('clients')" class="nc-stat-widget">
                <div class="widget-icon primary-bg">
                    <AccountMultiple :size="24" />
                </div>
                <div class="widget-content">
                    <span class="widget-value">{{ stats.clients }}</span>
                    <span class="widget-label">{{ translate('domaincontrol', 'Total Clients') }}</span>
                </div>
            </div>

            <!-- Projects Widget -->
            <div v-if="isModuleActive('projects')" class="nc-stat-widget">
                <div class="widget-icon success-bg">
                    <Folder :size="24" />
                </div>
                <div class="widget-content">
                    <span class="widget-value">{{ stats.projects }}</span>
                    <span class="widget-label">{{ translate('domaincontrol', 'Active Projects') }}</span>
                </div>
            </div>

            <!-- Tasks Widget -->
            <div v-if="isModuleActive('tasks')" class="nc-stat-widget">
                <div class="widget-icon warning-bg">
                    <ClipboardCheck :size="24" />
                </div>
                <div class="widget-content">
                    <span class="widget-value">{{ stats.tasks }}</span>
                    <span class="widget-label">{{ translate('domaincontrol', 'Pending Tasks') }}</span>
                </div>
            </div>

            <!-- Invoices Widget -->
            <div v-if="isModuleActive('invoices')" class="nc-stat-widget">
                <div class="widget-icon error-bg">
                    <FileDocumentOutline :size="24" />
                </div>
                <div class="widget-content">
                    <span class="widget-value">{{ stats.unpaidInvoices }}</span>
                    <span class="widget-label">{{ translate('domaincontrol', 'Unpaid Invoices') }}</span>
                </div>
            </div>
        </div>

        <div class="dashboard-layout">
            <!-- LEFT COLUMN -->
            <div class="layout-column main-col">
                
                <!-- Overview Panel -->
                <div class="nc-panel">
                    <div class="nc-panel-header">
                        <h3>{{ translate('domaincontrol', 'Overview') }}</h3>
                    </div>
                    <div class="mini-stats-grid">
                        <div v-if="isModuleActive('domains')" class="mini-stat-item">
                            <span class="mini-label">
                                <Web :size="14" class="icon-inline" />
                                {{ translate('domaincontrol', 'Domains') }}
                            </span>
                            <span class="mini-value">{{ stats.domains }}</span>
                        </div>
                        <div v-if="isModuleActive('hostings')" class="mini-stat-item">
                            <span class="mini-label">
                                <Server :size="14" class="icon-inline" />
                                {{ translate('domaincontrol', 'Hosting') }}
                            </span>
                            <span class="mini-value">{{ stats.hostings }}</span>
                        </div>
                        <div v-if="isModuleActive('websites')" class="mini-stat-item">
                            <span class="mini-label">
                                <Laptop :size="14" class="icon-inline" />
                                {{ translate('domaincontrol', 'Websites') }}
                            </span>
                            <span class="mini-value">{{ stats.websites }}</span>
                        </div>
                        
                        <!-- Financial Stats -->
                        <div v-if="isModuleActive('transactions')" class="mini-stat-item">
                            <span class="mini-label">{{ translate('domaincontrol', 'Income (This Month)') }}</span>
                            <span class="mini-value text-success">{{ formatCurrency(stats.monthlyIncome) }}</span>
                        </div>
                        <div v-if="isModuleActive('transactions')" class="mini-stat-item">
                            <span class="mini-label">{{ translate('domaincontrol', 'Expense (This Month)') }}</span>
                            <span class="mini-value text-subtle">{{ formatCurrency(stats.monthlyExpense) }}</span>
                        </div>
                        <div v-if="isModuleActive('transactions')" class="mini-stat-item">
                            <span class="mini-label">{{ translate('domaincontrol', 'Net Profit/Loss') }}</span>
                            <span class="mini-value" :class="stats.netProfit >= 0 ? 'text-success' : 'text-error'">
                                {{ formatCurrency(stats.netProfit) }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="nc-panel">
                    <div class="nc-panel-header">
                        <h3>{{ translate('domaincontrol', 'Quick Actions') }}</h3>
                    </div>
                    <div class="quick-actions-grid">
                        <NcButton v-if="isModuleActive('clients')" @click="quickAdd('client')" class="action-btn">
                            <template #icon><AccountPlus :size="20" /></template>
                            {{ translate('domaincontrol', 'New Client') }}
                        </NcButton>
                        <NcButton v-if="isModuleActive('projects')" @click="quickAdd('project')" class="action-btn">
                            <template #icon><FolderPlus :size="20" /></template>
                            {{ translate('domaincontrol', 'New Project') }}
                        </NcButton>
                        <NcButton v-if="isModuleActive('tasks')" @click="quickAdd('task')" class="action-btn">
                            <template #icon><ClipboardPlus :size="20" /></template>
                            {{ translate('domaincontrol', 'New Task') }}
                        </NcButton>
                        <NcButton v-if="isModuleActive('invoices')" @click="quickAdd('invoice')" class="action-btn">
                            <template #icon><FilePlus :size="20" /></template>
                            {{ translate('domaincontrol', 'Create Invoice') }}
                        </NcButton>
                    </div>
                </div>

                <!-- Recent Clients List -->
                <div v-if="isModuleActive('clients')" class="nc-panel">
                    <div class="nc-panel-header">
                        <h3>{{ translate('domaincontrol', 'Recent Clients') }}</h3>
                        <NcButton type="tertiary" @click="switchToTab('clients')" size="small">
                            {{ translate('domaincontrol', 'View All') }}
                        </NcButton>
                    </div>
                    
                    <div v-if="recentClients.length === 0" class="nc-empty-placeholder">
                        <AccountMultiple :size="32" />
                        <p>{{ translate('domaincontrol', 'No clients yet') }}</p>
                    </div>
                    
                    <div v-else class="nc-list">
                        <div v-for="client in recentClients" :key="client.id" class="nc-list-item">
                            <div class="nc-avatar" :style="{ backgroundColor: getAvatarColor(client.name) }">
                                {{ getInitials(client.name) }}
                            </div>
                            <div class="nc-list-content">
                                <div class="nc-item-title">{{ client.name }}</div>
                                <div class="nc-item-subtitle">{{ client.email }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- RIGHT COLUMN (ALERTS) -->
            <div class="layout-column side-col">
                
                <!-- Overdue Payments -->
                <div v-if="isModuleActive('invoices')" class="nc-panel alert-panel error-border">
                    <div class="nc-panel-header">
                        <h3 class="text-error">
                            <AlertCircle :size="16" class="icon-inline" />
                            {{ translate('domaincontrol', 'Overdue Payments') }}
                        </h3>
                        <span v-if="alerts.overdue.length > 0" class="nc-counter-badge bg-error">
                            {{ alerts.overdue.length }}
                        </span>
                    </div>
                    
                    <div v-if="alerts.overdue.length === 0" class="nc-empty-placeholder small">
                        <p>{{ translate('domaincontrol', 'No overdue payments') }}</p>
                    </div>
                    
                    <div v-else class="nc-list">
                        <div v-for="item in alerts.overdue" :key="item.id" class="nc-list-item alert-item">
                            <div class="nc-list-content">
                                <div class="nc-item-title">{{ item.title }}</div>
                                <div class="nc-item-subtitle text-error">{{ formatDate(item.date) }}</div>
                            </div>
                            <div class="nc-item-end font-bold">{{ formatCurrency(item.amount) }}</div>
                        </div>
                    </div>
                </div>

                <!-- Upcoming Payments -->
                <div v-if="isModuleActive('invoices')" class="nc-panel alert-panel warning-border">
                    <div class="nc-panel-header">
                        <h3 class="text-warning">
                            <ClockAlert :size="16" class="icon-inline" />
                            {{ translate('domaincontrol', 'Upcoming Payments') }}
                        </h3>
                        <span v-if="alerts.upcoming.length > 0" class="nc-counter-badge bg-warning">
                            {{ alerts.upcoming.length }}
                        </span>
                    </div>
                    
                    <div v-if="alerts.upcoming.length === 0" class="nc-empty-placeholder small">
                        <p>{{ translate('domaincontrol', 'No upcoming payments') }}</p>
                    </div>
                    
                    <div v-else class="nc-list">
                        <div v-for="item in alerts.upcoming" :key="item.id" class="nc-list-item alert-item">
                            <div class="nc-list-content">
                                <div class="nc-item-title">{{ item.title }}</div>
                                <div class="nc-item-subtitle">{{ formatDate(item.date) }}</div>
                            </div>
                            <div class="nc-item-end font-bold">{{ formatCurrency(item.amount) }}</div>
                        </div>
                    </div>
                </div>

                <!-- Upcoming Tasks -->
                <div v-if="isModuleActive('tasks')" class="nc-panel alert-panel info-border">
                    <div class="nc-panel-header">
                        <h3 class="text-primary">
                            <CalendarClock :size="16" class="icon-inline" />
                            {{ translate('domaincontrol', 'Upcoming Tasks') }}
                        </h3>
                        <span v-if="alerts.tasks.length > 0" class="nc-counter-badge bg-primary">
                            {{ alerts.tasks.length }}
                        </span>
                    </div>
                    
                    <div v-if="alerts.tasks.length === 0" class="nc-empty-placeholder small">
                        <p>{{ translate('domaincontrol', 'No upcoming tasks') }}</p>
                    </div>
                    
                    <div v-else class="nc-list">
                        <div v-for="item in alerts.tasks" :key="item.id" class="nc-list-item alert-item">
                            <div class="nc-list-content">
                                <div class="nc-item-title">{{ item.title }}</div>
                                <div class="nc-item-subtitle">{{ formatDate(item.date) }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                 <!-- Upcoming Debts -->
                <div v-if="isModuleActive('debts')" class="nc-panel alert-panel warning-border">
                    <div class="nc-panel-header">
                        <h3 class="text-warning">
                            <Wallet :size="16" class="icon-inline" />
                            {{ translate('domaincontrol', 'Upcoming Debt Payments') }}
                        </h3>
                        <span v-if="alerts.debts.length > 0" class="nc-counter-badge bg-warning">
                            {{ alerts.debts.length }}
                        </span>
                    </div>
                    
                    <div v-if="alerts.debts.length === 0" class="nc-empty-placeholder small">
                        <p>{{ translate('domaincontrol', 'No upcoming debt payments') }}</p>
                    </div>
                    
                    <div v-else class="nc-list">
                        <div v-for="item in alerts.debts" :key="item.id" class="nc-list-item alert-item">
                            <div class="nc-list-content">
                                <div class="nc-item-title">{{ item.title }}</div>
                                <div class="nc-item-subtitle">{{ formatDate(item.date) }}</div>
                            </div>
                             <div class="nc-item-end font-bold">{{ formatCurrency(item.amount) }}</div>
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
// vue-material-design-icons
import Calendar from 'vue-material-design-icons/Calendar.vue'
import AccountMultiple from 'vue-material-design-icons/AccountMultiple.vue'
import Folder from 'vue-material-design-icons/Folder.vue'
import ClipboardCheck from 'vue-material-design-icons/ClipboardCheck.vue'
import FileDocumentOutline from 'vue-material-design-icons/FileDocumentOutline.vue'
import Web from 'vue-material-design-icons/Web.vue'
import Server from 'vue-material-design-icons/Server.vue'
import Laptop from 'vue-material-design-icons/Laptop.vue'
import AccountPlus from 'vue-material-design-icons/AccountPlus.vue'
import FolderPlus from 'vue-material-design-icons/FolderPlus.vue'
import ClipboardPlus from 'vue-material-design-icons/ClipboardPlus.vue'
import FilePlus from 'vue-material-design-icons/FilePlus.vue'
import AlertCircle from 'vue-material-design-icons/AlertCircle.vue'
import ClockAlert from 'vue-material-design-icons/ClockAlert.vue'
import CalendarClock from 'vue-material-design-icons/CalendarClock.vue'
import Wallet from 'vue-material-design-icons/Wallet.vue'

export default {
    name: 'Dashboard',
    components: {
        NcButton,
        Calendar,
        AccountMultiple,
        Folder,
        ClipboardCheck,
        FileDocumentOutline,
        Web,
        Server,
        Laptop,
        AccountPlus,
        FolderPlus,
        ClipboardPlus,
        FilePlus,
        AlertCircle,
        ClockAlert,
        CalendarClock,
        Wallet,
    },
    data() {
        return {
            currentUserName: OC.getCurrentUser().uid || 'User',
            currentDate: '',
            activeModules: [],
            stats: {
                clients: 0,
                domains: 0,
                hostings: 0,
                websites: 0,
                projects: 0,
                tasks: 0,
                unpaidInvoices: 0,
                monthlyIncome: 0,
                monthlyExpense: 0,
                netProfit: 0,
            },
            recentClients: [],
            alerts: {
                overdue: [],
                upcoming: [],
                tasks: [],
                debts: [],
            },
            allData: {
                clients: [],
                domains: [],
                hostings: [],
                websites: [],
                projects: [],
                tasks: [],
                invoices: [],
                payments: [],
                transactions: [],
                debts: [],
            },
        }
    },
    mounted() {
        this.updateDate()
        this.loadActiveModules()
        this.loadDashboardData()
        window.addEventListener('settings-updated', this.handleSettingsUpdate)
    },
    beforeUnmount() {
        window.removeEventListener('settings-updated', this.handleSettingsUpdate)
    },
    methods: {
        translate(appId, text, vars) {
            try {
                if (typeof window !== 'undefined') {
                    // Try OC.L10n.translate first (Nextcloud's standard method)
                    if (typeof OC !== 'undefined' && OC.L10n && typeof OC.L10n.translate === 'function') {
                        const translated = OC.L10n.translate(appId, text, vars || {})
                        if (translated && translated !== text) {
                            let result = translated
                            if (vars && typeof vars === 'object') {
                                for (const key in vars) {
                                    result = result.replace(new RegExp(`\\{${key}\\}`, 'g'), vars[key])
                                }
                            }
                            return result
                        }
                    }
                    // Fallback to window.t
                    if (typeof window.t === 'function') {
                        const translated = window.t(appId, text, vars || {})
                        if (translated && translated !== text) {
                            let result = translated
                            if (vars && typeof vars === 'object') {
                                for (const key in vars) {
                                    result = result.replace(new RegExp(`\\{${key}\\}`, 'g'), vars[key])
                                }
                            }
                            return result
                        }
                    }
                }
            } catch (e) {
                console.warn('Translation error:', e)
            }
            // If translation not found, return original text with variable replacement
            let result = text
            if (vars && typeof vars === 'object') {
                for (const key in vars) {
                    result = result.replace(new RegExp(`\\{${key}\\}`, 'g'), vars[key])
                }
            }
            return result
        },
        async loadActiveModules() {
            try {
                const response = await api.settings.get()
                const settings = response.data || {}
                
                if (settings.active_modules) {
                    try {
                        this.activeModules = JSON.parse(settings.active_modules)
                    } catch (e) {
                        this.activeModules = settings.active_modules.split(',').filter(Boolean)
                    }
                } else {
                    this.activeModules = ['dashboard', 'clients', 'domains', 'hostings', 'websites', 'services', 'invoices', 'projects', 'tasks', 'transactions', 'debts', 'reports']
                }
            } catch (error) {
                this.activeModules = ['dashboard', 'clients', 'domains', 'hostings', 'websites', 'services', 'invoices', 'projects', 'tasks', 'transactions', 'debts', 'reports']
            }
        },
        handleSettingsUpdate(event) {
            if (event.detail && event.detail.activeModules) {
                this.activeModules = event.detail.activeModules
            }
        },
        isModuleActive(moduleId) {
            return this.activeModules.length === 0 || this.activeModules.includes(moduleId)
        },
        updateDate() {
            const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }
            this.currentDate = new Date().toLocaleDateString('tr-TR', options)
        },
        async loadDashboardData() {
            try {
                const [clients, domains, hostings, websites, projects, tasks, invoices, payments, transactions, debts] = await Promise.all([
                    api.clients.getAll().catch(() => ({ data: [] })),
                    api.domains.getAll().catch(() => ({ data: [] })),
                    api.hostings.getAll().catch(() => ({ data: [] })),
                    api.websites.getAll().catch(() => ({ data: [] })),
                    api.projects.getActive().catch(() => ({ data: [] })),
                    api.tasks.getAll().catch(() => ({ data: [] })),
                    api.invoices.getAll().catch(() => ({ data: [] })),
                    api.payments.getAll().catch(() => ({ data: [] })),
                    api.transactions.getAll().catch(() => ({ data: [] })),
                    api.debts.getAll().catch(() => ({ data: [] })),
                ])

                this.allData = {
                    clients: clients.data || clients || [],
                    domains: domains.data || domains || [],
                    hostings: hostings.data || hostings || [],
                    websites: websites.data || websites || [],
                    projects: projects.data || projects || [],
                    tasks: tasks.data || tasks || [],
                    invoices: invoices.data || invoices || [],
                    payments: payments.data || payments || [],
                    transactions: transactions.data || transactions || [],
                    debts: debts.data || debts || [],
                }

                this.calculateStats()
                this.updateRecentClients()
                this.updateAlerts()
            } catch (error) {
                console.error('Error loading dashboard data:', error)
            }
        },
        calculateStats() {
            this.stats.clients = this.allData.clients.length
            this.stats.domains = this.allData.domains.length
            this.stats.hostings = this.allData.hostings.length
            this.stats.websites = this.allData.websites.length
            this.stats.projects = this.allData.projects.length
            this.stats.tasks = this.allData.tasks.length
            this.stats.unpaidInvoices = (this.allData.invoices || []).filter(i => ['draft', 'sent', 'overdue'].includes(i.status)).length

            const now = new Date()
            const currentMonth = now.getMonth()
            const currentYear = now.getFullYear()

            this.stats.monthlyIncome = (this.allData.payments || []).reduce((sum, p) => {
                if (!p.paymentDate) return sum
                const date = new Date(p.paymentDate)
                if (date.getMonth() === currentMonth && date.getFullYear() === currentYear) {
                    return sum + (parseFloat(p.amount) || 0)
                }
                return sum
            }, 0)

            this.stats.monthlyExpense = (this.allData.transactions || []).reduce((sum, t) => {
                if (t.type !== 'expense') return sum
                if (!t.date) return sum
                const date = new Date(t.date)
                if (date.getMonth() === currentMonth && date.getFullYear() === currentYear) {
                    return sum + (parseFloat(t.amount) || 0)
                }
                return sum
            }, 0)

            this.stats.netProfit = this.stats.monthlyIncome - this.stats.monthlyExpense
        },
        updateRecentClients() {
            this.recentClients = this.allData.clients.slice(0, 5)
        },
        updateAlerts() {
            const today = new Date()
            today.setHours(0, 0, 0, 0)
            const nextWeek = new Date()
            nextWeek.setDate(nextWeek.getDate() + 7)
            nextWeek.setHours(23, 59, 59, 999)

            this.alerts.overdue = (this.allData.invoices || [])
                .filter(i => {
                    if (!i) return false
                    if (i.status === 'overdue') return true
                    if (i.dueDate) {
                        const dueDate = new Date(i.dueDate)
                        dueDate.setHours(0, 0, 0, 0)
                        if (dueDate < today && !['paid', 'cancelled'].includes(i.status)) return true
                    }
                    return false
                })
                .slice(0, 5)
                .map(i => ({
                    id: i.id,
                    title: `Fatura #${i.invoiceNumber || i.id}`,
                    date: i.dueDate,
                    amount: parseFloat(i.totalAmount || 0) - parseFloat(i.paidAmount || 0),
                }))

            this.alerts.upcoming = (this.allData.invoices || [])
                .filter(i => {
                    if (!i || !i.dueDate) return false
                    if (['paid', 'cancelled', 'overdue'].includes(i.status)) return false
                    try {
                        const dueDate = new Date(i.dueDate)
                        dueDate.setHours(0, 0, 0, 0)
                        return dueDate >= today && dueDate <= nextWeek
                    } catch (e) { return false }
                })
                .slice(0, 5)
                .map(i => ({
                    id: i.id,
                    title: `Fatura #${i.invoiceNumber || i.id}`,
                    date: i.dueDate,
                    amount: parseFloat(i.totalAmount || 0) - parseFloat(i.paidAmount || 0),
                }))

            this.alerts.tasks = (this.allData.tasks || [])
                .filter(t => {
                    if (!t || !t.dueDate || t.status === 'done' || t.status === 'completed') return false
                    try {
                        const dueDate = new Date(t.dueDate)
                        dueDate.setHours(0, 0, 0, 0)
                        return dueDate >= today
                    } catch (e) { return false }
                })
                .sort((a, b) => new Date(a.dueDate) - new Date(b.dueDate))
                .slice(0, 5)
                .map(t => ({ id: t.id, title: t.title || 'Görev', date: t.dueDate }))

            this.alerts.debts = (this.allData.debts || [])
                .filter(d => {
                    if (!d || !d.nextPaymentDate) return false
                    try {
                        const pd = new Date(d.nextPaymentDate)
                        pd.setHours(0, 0, 0, 0)
                        return pd >= today
                    } catch (e) { return false }
                })
                .sort((a, b) => new Date(a.nextPaymentDate) - new Date(b.nextPaymentDate))
                .slice(0, 5)
                .map(d => ({
                    id: d.id,
                    title: d.description || d.title || `Borç #${d.id}`,
                    date: d.nextPaymentDate,
                    amount: parseFloat(d.amount || d.monthlyPayment || 0),
                }))
        },
        formatCurrency(amount) {
            if (typeof amount !== 'number') return '0 ₼'
            return new Intl.NumberFormat('tr-TR', { style: 'currency', currency: 'AZN' }).format(amount)
        },
        formatDate(dateString) {
            if (!dateString) return '-'
            try {
                return new Intl.DateTimeFormat('tr-TR', { day: 'numeric', month: 'short' }).format(new Date(dateString))
            } catch (e) { return dateString }
        },
        quickAdd(type) {
            if (typeof window.DomainControl !== 'undefined') {
                window.DomainControl.switchTab(type + 's')
                if (type === 'client') window.DomainControl.showClientModal()
                else if (type === 'project') window.DomainControl.showProjectModal()
                else if (type === 'task') window.DomainControl.showTaskModal()
                else if (type === 'invoice') window.DomainControl.showInvoiceModal()
            }
        },
        switchToTab(tab) {
            if (typeof window.DomainControl !== 'undefined') {
                window.DomainControl.switchTab(tab)
            }
        },
        getInitials(name) {
            if (!name) return '?'
            const parts = name.trim().split(' ')
            return parts.length >= 2 ? (parts[0][0] + parts[parts.length - 1][0]).toUpperCase() : name.substring(0, 2).toUpperCase()
        },
        getAvatarColor(name) {
            if (!name) return '#999'
            let hash = 0
            for (let i = 0; i < name.length; i++) hash = name.charCodeAt(i) + ((hash << 5) - hash)
            return `hsl(${hash % 360}, 60%, 45%)`
        },
    },
}
</script>

<style scoped>
/* NEXTCLOUD NATIVE STYLES */
.dashboard-container {
    padding: 20px;
    max-width: 1400px;
    margin: 0 auto;
    font-family: var(--font-face, sans-serif);
    color: var(--color-main-text);
}

/* Header */
.nc-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 24px;
    padding: 0 0 0 29px;
}

.nc-title {
    font-size: 24px;
    font-weight: bold;
    margin: 0 0 4px 0;
    color: var(--color-main-text);
}

.nc-subtitle {
    font-size: 15px;
    color: var(--color-text-maxcontrast);
    margin: 0;
}

.nc-date-badge {
    display: flex;
    align-items: center;
    gap: 8px;
    background-color: var(--color-background-hover);
    padding: 6px 12px;
    border-radius: var(--border-radius-pill);
    font-size: 13px;
    font-weight: 500;
    color: var(--color-text-maxcontrast);
}

/* Main Stats Grid (Widgets) */
.stats-grid-main {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 16px;
    margin-bottom: 32px;
}

.nc-stat-widget {
    background-color: var(--color-main-background);
    border: 1px solid var(--color-border);
    border-radius: var(--border-radius-large);
    padding: 20px;
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
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.primary-bg { background-color: rgba(0, 130, 201, 0.1); color: var(--color-primary-element-element); }
.success-bg { background-color: rgba(70, 186, 97, 0.1); color: var(--color-element-success); }
.warning-bg { background-color: rgba(233, 144, 2, 0.1); color: var(--color-element-warning); }
.error-bg { background-color: rgba(233, 50, 45, 0.1); color: var(--color-element-error); }

.widget-content {
    display: flex;
    flex-direction: column;
}

.widget-value { font-size: 24px; font-weight: bold; line-height: 1.2; }
.widget-label { font-size: 13px; color: var(--color-text-maxcontrast); }

/* Layout Columns */
.dashboard-layout {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 24px;
}

.layout-column {
    display: flex;
    flex-direction: column;
    gap: 24px;
}

/* Panels (Cards) */
.nc-panel {
    background-color: var(--color-main-background);
    border: 1px solid var(--color-border);
    border-radius: var(--border-radius-large);
    padding: 20px;
}

.nc-panel-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 16px;
}

.nc-panel-header h3 {
    margin: 0;
    font-size: 16px;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 8px;
}

/* Mini Stats Grid */
.mini-stats-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 12px;
}

.mini-stat-item {
    background-color: var(--color-background-hover);
    padding: 12px;
    border-radius: var(--border-radius);
    display: flex;
    flex-direction: column;
}

.mini-label {
    font-size: 12px;
    color: var(--color-text-maxcontrast);
    margin-bottom: 4px;
    display: flex;
    align-items: center;
    gap: 6px;
}

.mini-value { font-size: 16px; font-weight: 600; }
.text-success { color: var(--color-element-success); }
.text-error { color: var(--color-element-error); }
.text-warning { color: var(--color-element-warning); }
.text-subtle { color: var(--color-text-maxcontrast); }
.text-primary { color: var(--color-primary-element-element); }
.icon-inline { opacity: 0.7; }

/* Quick Actions */
.quick-actions-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 12px;
}

.action-btn { width: 100%; justify-content: flex-start; }

/* Lists (Clients, Alerts) */
.nc-list {
    display: flex;
    flex-direction: column;
}

.nc-list-item {
    display: flex;
    align-items: center;
    padding: 10px 0;
    border-bottom: 1px solid var(--color-border);
}
.nc-list-item:last-child { border-bottom: none; }

.nc-avatar {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: 600;
    font-size: 13px;
    margin-right: 12px;
}

.nc-list-content { flex: 1; min-width: 0; }
.nc-item-title { font-weight: 500; color: var(--color-main-text); margin-bottom: 2px; }
.nc-item-subtitle { font-size: 12px; color: var(--color-text-maxcontrast); }
.nc-item-end { font-size: 14px; }
.font-bold { font-weight: 600; }

/* Alert Panels */
.alert-panel { border-left: 4px solid transparent; }
.error-border { border-left-color: var(--color-element-error); }
.warning-border { border-left-color: var(--color-element-warning); }
.info-border { border-left-color: var(--color-primary-element-element); }

.nc-counter-badge {
    padding: 2px 8px;
    border-radius: 10px;
    font-size: 11px;
    font-weight: bold;
    color: white;
}
.bg-error { background-color: var(--color-element-error); }
.bg-warning { background-color: var(--color-element-warning); }
.bg-primary { background-color: var(--color-primary-element-element); }

.nc-empty-placeholder {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 30px;
    text-align: center;
    color: var(--color-text-maxcontrast);
    opacity: 0.7;
}
.nc-empty-placeholder.small { padding: 15px; font-size: 13px; }

/* Responsive */
@media (max-width: 1000px) {
    .dashboard-layout { grid-template-columns: 1fr; }
    .mini-stats-grid { grid-template-columns: repeat(2, 1fr); }
}

@media (max-width: 600px) {
    .nc-header { flex-direction: column; align-items: flex-start; gap: 12px; }
    .quick-actions-grid { grid-template-columns: 1fr; }
}
</style>