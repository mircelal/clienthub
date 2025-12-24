<template>
    <div class="clients-view-container">
        <!-- ========================================== -->
        <!-- CLIENT MODAL                               -->
        <!-- ========================================== -->
        <ClientModal
            :open="modalOpen"
            :client="editingClient"
            @close="closeModal"
            @saved="handleClientSaved"
        />

        <!-- ========================================== -->
        <!-- PROJECT MODAL                               -->
        <!-- ========================================== -->
        <ProjectModal
            :open="projectModalOpen"
            :project="editingProject"
            :clients="clients"
            :presetClientId="selectedClient?.id"
            @close="closeProjectModal"
            @saved="handleProjectSaved"
        />

        <!-- ========================================== -->
        <!-- TASK MODAL                                  -->
        <!-- ========================================== -->
        <TaskModal
            :open="taskModalOpen"
            :task="editingTask"
            :clients="clients"
            :projects="projects"
            :presetClientId="selectedClient?.id"
            @close="closeTaskModal"
            @saved="handleTaskSaved"
        />

        <!-- ========================================== -->
        <!-- NOTE DETAIL MODAL                          -->
        <!-- ========================================== -->
        <div v-if="noteModalOpen" class="nc-modal-overlay" @click.self="closeNoteModal">
            <div class="nc-modal-content note-modal">
                <div class="nc-modal-header">
                    <h3 class="nc-modal-title">
                        {{ editingNote.id ? translate('domaincontrol', 'Edit Note') : translate('domaincontrol', 'New Note') }}
                    </h3>
                    <button class="nc-modal-close" @click="closeNoteModal">
                        <Close :size="20" />
                    </button>
                </div>
                <div class="nc-modal-body">
                    <div class="form-group">
                        <label>{{ translate('domaincontrol', 'Note Content') }}</label>
                        <!-- Rich Text Editor Placeholder -->
                        <RichTextEditor
                            v-model="editingNote.content"
                            :placeholder="translate('domaincontrol', 'Write your note here...')"
                        />
                    </div>
                </div>
                <div class="nc-modal-footer">
                    <NcButton type="tertiary" @click="closeNoteModal">{{ translate('domaincontrol', 'Cancel') }}</NcButton>
                    <NcButton type="primary" @click="saveNote">{{ translate('domaincontrol', 'Save Note') }}</NcButton>
                </div>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- LIST VIEW                                  -->
        <!-- ========================================== -->
        <div v-if="!selectedClient" class="nc-content-wrapper">
            <!-- Header & Actions -->
            <div class="nc-section-header">
                <div class="header-left">
                    <h2 class="nc-app-title">
                        <AccountGroup :size="24" class="header-icon" />
                        {{ translate('domaincontrol', 'Clients') }}
                    </h2>
                </div>
                
                <div class="header-actions">
                    <div class="search-wrapper">
                        <Magnify :size="20" class="search-icon" />
                        <input
                            type="text"
                            v-model="searchQuery"
                            class="nc-input search-input"
                            :placeholder="translate('domaincontrol', 'Search clients...')"
                            @input="filterClients"
                        />
                    </div>
                    <NcButton type="primary" @click="showAddModal">
                        <template #icon>
                            <Plus :size="20" />
                        </template>
                        {{ translate('domaincontrol', 'Add Client') }}
                    </NcButton>
                </div>
            </div>

            <!-- Loading State -->
            <div v-if="loading" class="nc-loading-state">
                <Refresh :size="48" class="spin-animation nc-state-icon" />
                <p>{{ translate('domaincontrol', 'Loading clients...') }}</p>
            </div>

            <!-- Empty State -->
            <div v-else-if="filteredClients.length === 0" class="nc-empty-state">
                <AccountBoxOutline :size="64" class="nc-state-icon" />
                <h3>{{ searchQuery ? translate('domaincontrol', 'No clients found') : translate('domaincontrol', 'No clients yet') }}</h3>
                <p v-if="!searchQuery">{{ translate('domaincontrol', 'Manage your customers and their services here.') }}</p>
                <NcButton v-if="!searchQuery" type="primary" @click="showAddModal" class="mt-4">
                    {{ translate('domaincontrol', 'Add First Client') }}
                </NcButton>
            </div>

            <!-- Clients List -->
            <div v-else class="nc-client-list">
                <div 
                    v-for="client in filteredClients" 
                    :key="client.id" 
                    class="nc-list-item"
                    @click="selectClient(client)"
                >
                    <!-- Avatar -->
                    <div class="item-avatar">
                        <div class="avatar-circle" :style="{ backgroundColor: getAvatarColor(client.name) }">
                            {{ getInitials(client.name) }}
                        </div>
                    </div>

                    <!-- Content -->
                    <div class="item-content">
                        <div class="item-main">
                            <span class="client-name">{{ client.name }}</span>
                            <span v-if="client.email" class="client-sub">{{ client.email }}</span>
                        </div>
                        <div class="item-meta" v-if="client.phone">
                            <Phone :size="14" class="meta-icon" />
                            <span>{{ client.phone }}</span>
                        </div>
                    </div>

                    <!-- Stats Badges -->
                    <div class="item-stats desktop-only">
                        <div class="stat-badge" :title="translate('domaincontrol', 'Domains')">
                            <Web :size="14" />
                            <span>{{ getClientDomainCount(client.id) }}</span>
                        </div>
                        <div class="stat-badge" :title="translate('domaincontrol', 'Projects')">
                            <Briefcase :size="14" />
                            <span>{{ getClientProjectCount(client.id) }}</span>
                        </div>
                        <div class="stat-badge" :title="translate('domaincontrol', 'Invoices')">
                            <FileDocumentOutline :size="14" />
                            <span>{{ getClientInvoiceCount(client.id) }}</span>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="item-actions">
                        <button class="action-btn" @click.stop="editClient(client.id)" :title="translate('domaincontrol', 'Edit')">
                            <Pencil :size="18" />
                        </button>
                        <button class="action-btn delete-hover" @click.stop="confirmDelete(client)" :title="translate('domaincontrol', 'Delete')">
                            <Delete :size="18" />
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- DETAIL VIEW                                -->
        <!-- ========================================== -->
        <div v-else class="nc-detail-view">
            
            <!-- Header -->
            <div class="nc-detail-header">
                <div class="header-left">
                    <NcButton type="tertiary" @click="backToList">
                        <template #icon>
                            <ArrowLeft :size="20" />
                        </template>
                        {{ translate('domaincontrol', 'Back') }}
                    </NcButton>
                    <div class="detail-avatar" :style="{ backgroundColor: getAvatarColor(selectedClient.name) }">
                        {{ getInitials(selectedClient.name) }}
                    </div>
                    <h2 class="detail-title">{{ selectedClient.name }}</h2>
                </div>
                <div class="header-actions">
                    <NcButton type="secondary" @click="editClient(selectedClient.id)">
                        <template #icon>
                            <Pencil :size="20" />
                        </template>
                        {{ translate('domaincontrol', 'Edit') }}
                    </NcButton>
                    <NcButton type="error" @click="confirmDelete(selectedClient)">
                        <template #icon>
                            <Delete :size="20" />
                        </template>
                        {{ translate('domaincontrol', 'Delete') }}
                    </NcButton>
                </div>
            </div>

            <div class="nc-detail-content">
                
                <!-- MAIN COLUMN -->
                <div class="detail-column main">
                    
                    <!-- Financial Overview Section -->
                    <div class="nc-panel financial-panel">
                        <div class="panel-header">
                            <h3>
                                <ChartBar :size="18" class="inline-icon" />
                                {{ translate('domaincontrol', 'Financial Overview') }}
                            </h3>
                        </div>
                        <div class="financial-body">
                            <!-- Summary Cards -->
                            <div class="financial-cards">
                                <div class="fin-card">
                                    <span class="fin-label">{{ translate('domaincontrol', 'Total Paid') }}</span>
                                    <span class="fin-value text-success">{{ formatCurrency(financials.totalPaid) }}</span>
                                </div>
                                <div class="fin-card">
                                    <span class="fin-label">{{ translate('domaincontrol', 'Overdue') }}</span>
                                    <span class="fin-value text-error">{{ formatCurrency(financials.totalOverdue) }}</span>
                                </div>
                                <div class="fin-card">
                                    <span class="fin-label">{{ translate('domaincontrol', 'Upcoming') }}</span>
                                    <span class="fin-value text-primary">{{ formatCurrency(financials.totalUpcoming) }}</span>
                                </div>
                            </div>

                            <!-- CSS Bar Chart -->
                            <div class="income-chart">
                                <div class="chart-header">
                                    <span>{{ translate('domaincontrol', 'Income (Last 6 Months)') }}</span>
                                </div>
                                <div class="chart-bars">
                                    <div v-for="(month, index) in monthlyIncome" :key="index" class="chart-col">
                                        <div class="bar-container">
                                            <div class="bar-fill" :style="{ height: month.percentage + '%' }" :title="formatCurrency(month.amount)"></div>
                                        </div>
                                        <span class="bar-label">{{ month.label }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Payment History List -->
                            <div class="payment-history-section">
                                <div class="chart-header mt-4">
                                    <History :size="16" class="inline-icon" />
                                    <span>{{ translate('domaincontrol', 'Recent Payments') }}</span>
                                </div>
                                <div v-if="getClientPayments(selectedClient.id).length === 0" class="empty-mini">
                                    {{ translate('domaincontrol', 'No payments found') }}
                                </div>
                                <div v-else class="payment-list">
                                    <div v-for="payment in visiblePayments" :key="payment.id" class="payment-row">
                                        <div class="payment-date">
                                            {{ formatDate(payment.paymentDate) }}
                                        </div>
                                        <div class="payment-method">
                                            {{ payment.method || 'Bank Transfer' }}
                                        </div>
                                        <div class="payment-amount text-success">
                                            + {{ formatCurrency(payment.amount) }} {{ payment.currency }}
                                        </div>
                                    </div>
                                </div>
                                <div v-if="getClientPayments(selectedClient.id).length > 3" class="show-more-container">
                                    <button class="show-more-btn" @click="showAllPayments = !showAllPayments">
                                        {{ showAllPayments ? translate('domaincontrol', 'Show Less') : translate('domaincontrol', 'Show More') }}
                                        <ChevronUp v-if="showAllPayments" :size="16" />
                                        <ChevronDown v-else :size="16" />
                                    </button>
                                </div>
                            </div>

                        </div>
                    </div>

                    <!-- Projects Section -->
                    <div class="nc-panel">
                        <div class="panel-header">
                            <h3>
                                <Briefcase :size="18" class="inline-icon" />
                                {{ translate('domaincontrol', 'Projects') }}
                            </h3>
                            <NcButton type="tertiary" size="small" @click="showAddProjectModal">
                                <template #icon><Plus :size="16" /></template>
                                {{ translate('domaincontrol', 'New Project') }}
                            </NcButton>
                        </div>
                        <div class="panel-body no-padding">
                            <div v-if="getClientProjects(selectedClient.id).length === 0" class="empty-mini-large">
                                {{ translate('domaincontrol', 'No active projects') }}
                            </div>
                            <div v-else class="project-list">
                                <div v-for="project in getClientProjects(selectedClient.id)" :key="project.id" class="project-row" @click="navigateToProject(project.id)">
                                    <div class="project-info">
                                        <div class="project-name">{{ project.name }}</div>
                                        <div class="project-deadline" :class="{'text-error': isOverdue(project.deadline)}">
                                            {{ translate('domaincontrol', 'Deadline') }}: {{ formatDate(project.deadline) }}
                                        </div>
                                    </div>
                                    <div class="project-progress">
                                        <div class="progress-header">
                                            <span class="progress-label">{{ translate('domaincontrol', 'Progress') }}</span>
                                            <span class="progress-percentage">{{ project.progress || 0 }}%</span>
                                        </div>
                                        <NcProgressBar :value="project.progress || 0" />
                                    </div>
                                    <div class="project-status">
                                        <span class="nc-badge" :class="getProjectStatusClass(project.status)">
                                            {{ project.status }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tasks Section -->
                    <div class="nc-panel">
                        <div class="panel-header">
                            <h3>
                                <CheckboxMarkedCircleOutline :size="18" class="inline-icon" />
                                {{ translate('domaincontrol', 'Pending Tasks') }}
                            </h3>
                            <NcButton type="tertiary" size="small" @click="showAddTaskModal">
                                <template #icon><Plus :size="16" /></template>
                                {{ translate('domaincontrol', 'Add Task') }}
                            </NcButton>
                        </div>
                        <div class="panel-body no-padding">
                             <div v-if="getClientTasks(selectedClient.id).length === 0" class="empty-mini-large">
                                {{ translate('domaincontrol', 'No pending tasks') }}
                            </div>
                            <div v-else class="task-list">
                                <div v-for="task in getClientTasks(selectedClient.id)" :key="task.id" class="task-row">
                                    <div class="task-check">
                                        <div class="check-circle"></div>
                                    </div>
                                    <div class="task-content">
                                        <div class="task-title">{{ task.title }}</div>
                                        <div class="task-meta">
                                            <span class="task-due" :class="{'text-error': isOverdue(task.dueDate)}">
                                                <CalendarClock :size="12" /> {{ formatDate(task.dueDate) }}
                                            </span>
                                            <span class="task-priority" :class="'priority-' + task.priority">
                                                {{ task.priority }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                     <!-- Notes (Re-designed) -->
                    <div class="nc-panel">
                        <div class="panel-header">
                            <h3>
                                <StickerTextOutline :size="18" class="inline-icon" />
                                {{ translate('domaincontrol', 'Notes') }}
                            </h3>
                            <NcButton type="tertiary" size="small" @click="openNoteModal()">
                                <template #icon><Plus :size="16" /></template>
                                {{ translate('domaincontrol', 'Add Note') }}
                            </NcButton>
                        </div>
                        <div class="panel-body no-padding">
                             <div v-if="getClientNotes(selectedClient.id).length === 0" class="empty-mini-large">
                                {{ translate('domaincontrol', 'No notes added yet.') }}
                            </div>
                            <div v-else class="note-list">
                                <div v-for="note in getClientNotes(selectedClient.id)" :key="note.id" class="note-row" @click="openNoteModal(note)">
                                    <div class="note-icon">
                                        <StickerTextOutline :size="20" />
                                    </div>
                                    <div class="note-content">
                                        <div class="note-text" v-html="stripHtml(note.content)"></div>
                                        <div class="note-date">{{ formatDate(note.date) }}</div>
                                    </div>
                                    <div class="note-arrow">
                                        <ChevronDown :size="16" style="transform: rotate(-90deg);" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- SIDEBAR COLUMN -->
                <div class="detail-column sidebar">
                    
                    <!-- Contact Info -->
                    <div class="nc-panel">
                        <div class="panel-header">
                            <h3>{{ translate('domaincontrol', 'Contact Info') }}</h3>
                        </div>
                        <div class="panel-body">
                            <div class="info-row" v-if="selectedClient.email">
                                <Email :size="18" class="row-icon" />
                                <a :href="`mailto:${selectedClient.email}`" class="row-value link">{{ selectedClient.email }}</a>
                            </div>
                            <div class="info-row" v-if="selectedClient.phone">
                                <Phone :size="18" class="row-icon" />
                                <a :href="`tel:${selectedClient.phone}`" class="row-value link">{{ selectedClient.phone }}</a>
                            </div>
                            <div class="info-row" v-if="selectedClient.createdAt">
                                <Calendar :size="18" class="row-icon" />
                                <span class="row-value">{{ formatDate(selectedClient.createdAt) }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Domains -->
                    <div class="nc-panel">
                        <div class="panel-header">
                            <h3>
                                <Web :size="18" class="inline-icon" /> 
                                {{ translate('domaincontrol', 'Domains') }}
                            </h3>
                            <span class="count-badge">{{ getClientDomainCount(selectedClient.id) }}</span>
                        </div>
                        <div class="mini-list">
                            <div v-if="getClientDomains(selectedClient.id).length === 0" class="empty-mini">
                                {{ translate('domaincontrol', 'No domains') }}
                            </div>
                            <div v-for="domain in getClientDomains(selectedClient.id)" 
                                :key="domain.id" 
                                class="mini-item"
                                :class="getDomainStatusClass(domain)"
                                @click="navigateToDomain(domain.id)">
                                <span class="mini-title">{{ domain.domainName }}</span>
                                <span class="mini-sub">{{ formatDate(domain.expirationDate) }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Invoices -->
                    <div class="nc-panel">
                         <div class="panel-header">
                            <h3>
                                <FileDocumentOutline :size="18" class="inline-icon" />
                                {{ translate('domaincontrol', 'Invoices') }}
                            </h3>
                            <span class="count-badge">{{ getClientInvoiceCount(selectedClient.id) }}</span>
                        </div>
                        <div class="mini-list">
                             <div v-if="getClientInvoices(selectedClient.id).length === 0" class="empty-mini">
                                {{ translate('domaincontrol', 'No invoices') }}
                            </div>
                            <div v-for="invoice in getClientInvoices(selectedClient.id).slice(0, 5)" 
                                :key="invoice.id" 
                                class="mini-item"
                                @click="navigateToInvoice(invoice.id)">
                                <span class="mini-title">{{ invoice.invoiceNumber || `#${invoice.id}` }}</span>
                                <span class="mini-sub" :class="invoice.status === 'overdue' ? 'text-error' : ''">
                                    {{ formatCurrency(invoice.totalAmount) }}
                                </span>
                            </div>
                        </div>
                    </div>

                     <!-- Hostings & Services -->
                    <div class="nc-panel">
                        <div class="panel-header">
                            <h3>
                                <ServerNetwork :size="18" class="inline-icon" />
                                {{ translate('domaincontrol', 'Services') }}
                            </h3>
                            <span class="count-badge">{{ getClientHostingCount(selectedClient.id) + getClientServiceCount(selectedClient.id) }}</span>
                        </div>
                        <div class="mini-list">
                             <div v-if="getClientHostings(selectedClient.id).length === 0 && getClientServices(selectedClient.id).length === 0" class="empty-mini">
                                {{ translate('domaincontrol', 'No services') }}
                            </div>
                            <!-- Hostings -->
                            <div v-for="hosting in getClientHostings(selectedClient.id)" 
                                :key="'h'+hosting.id" 
                                class="mini-item"
                                @click="navigateToHosting(hosting.id)">
                                <span class="mini-title">{{ hosting.provider }}</span>
                                <span class="mini-sub">Hosting</span>
                            </div>
                             <!-- Other Services -->
                            <div v-for="service in getClientServices(selectedClient.id)" 
                                :key="'s'+service.id" 
                                class="mini-item"
                                @click="navigateToService(service.id)">
                                <span class="mini-title">{{ service.name }}</span>
                                <span class="mini-sub">{{ formatCurrency(service.price) }}</span>
                            </div>
                        </div>
                    </div>

                </div>

            </div>
        </div>
    </div>
</template>

<script>
// Libraries
import { NcButton, NcProgressBar } from '@nextcloud/vue'
import api from '../services/api'
import ClientModal from './ClientModal.vue'
import ProjectModal from './ProjectModal.vue'
import TaskModal from './TaskModal.vue'
import RichTextEditor from './RichTextEditor.vue'

// Icons
import AccountGroup from 'vue-material-design-icons/AccountGroup.vue'
import Magnify from 'vue-material-design-icons/Magnify.vue'
import Plus from 'vue-material-design-icons/Plus.vue'
import Refresh from 'vue-material-design-icons/Refresh.vue'
import AccountBoxOutline from 'vue-material-design-icons/AccountBoxOutline.vue'
import Phone from 'vue-material-design-icons/Phone.vue'
import Web from 'vue-material-design-icons/Web.vue'
import ServerNetwork from 'vue-material-design-icons/ServerNetwork.vue'
import FileDocumentOutline from 'vue-material-design-icons/FileDocumentOutline.vue'
import Pencil from 'vue-material-design-icons/Pencil.vue'
import Delete from 'vue-material-design-icons/Delete.vue'
import ArrowLeft from 'vue-material-design-icons/ArrowLeft.vue'
import Email from 'vue-material-design-icons/Email.vue'
import Calendar from 'vue-material-design-icons/Calendar.vue'
import Briefcase from 'vue-material-design-icons/Briefcase.vue'
import CheckboxMarkedCircleOutline from 'vue-material-design-icons/CheckboxMarkedCircleOutline.vue'
import ChartBar from 'vue-material-design-icons/ChartBar.vue'
import CalendarClock from 'vue-material-design-icons/CalendarClock.vue'
import StickerTextOutline from 'vue-material-design-icons/StickerTextOutline.vue'
import Close from 'vue-material-design-icons/Close.vue'
import History from 'vue-material-design-icons/History.vue'
import ChevronDown from 'vue-material-design-icons/ChevronDown.vue'
import ChevronUp from 'vue-material-design-icons/ChevronUp.vue'

export default {
    name: 'Clients',
    components: {
        ClientModal,
        ProjectModal,
        TaskModal,
        RichTextEditor,
        NcButton,
        NcProgressBar,
        // Icons
        AccountGroup,
        Magnify,
        Plus,
        Refresh,
        AccountBoxOutline,
        Phone,
        Web,
        ServerNetwork,
        FileDocumentOutline,
        Pencil,
        Delete,
        ArrowLeft,
        Email,
        Calendar,
        Briefcase,
        CheckboxMarkedCircleOutline,
        ChartBar,
        CalendarClock,
        StickerTextOutline,
        Close,
        History,
        ChevronDown,
        ChevronUp
    },
    data() {
        return {
            clients: [],
            domains: [],
            hostings: [],
            websites: [],
            services: [],
            invoices: [],
            payments: [],
            projects: [], // Mock data
            tasks: [], // Mock data
            notes: [], // Mock data
            selectedClient: null,
            searchQuery: '',
            loading: false,
            modalOpen: false,
            editingClient: null,
            
            // Notes Modal
            noteModalOpen: false,
            editingNote: { id: null, content: '' },

            // Project Modal
            projectModalOpen: false,
            editingProject: null,

            // Task Modal
            taskModalOpen: false,
            editingTask: null,

            // Payments
            showAllPayments: false
        }
    },
    computed: {
        filteredClients() {
            if (!this.searchQuery) {
                return this.clients
            }
            const query = this.searchQuery.toLowerCase()
            return this.clients.filter(client => {
                return (
                    client.name?.toLowerCase().includes(query) ||
                    client.email?.toLowerCase().includes(query) ||
                    client.phone?.toLowerCase().includes(query)
                )
            })
        },
        financials() {
            if (!this.selectedClient) return { totalPaid: 0, totalOverdue: 0, totalUpcoming: 0 }
            
            const clientInvoices = this.getClientInvoices(this.selectedClient.id)
            const now = new Date()

            let totalPaid = 0
            let totalOverdue = 0
            let totalUpcoming = 0

            clientInvoices.forEach(inv => {
                if (inv.status === 'paid') {
                    totalPaid += parseFloat(inv.totalAmount || 0)
                } else {
                    const amount = parseFloat(inv.totalAmount || 0)
                    const dueDate = new Date(inv.dueDate)
                    if (dueDate < now) {
                        totalOverdue += amount
                    } else {
                        totalUpcoming += amount
                    }
                }
            })

            return { totalPaid, totalOverdue, totalUpcoming }
        },
        monthlyIncome() {
            if (!this.selectedClient) return []
            // Calculate income for last 6 months based on payments
            const clientPayments = this.getClientPayments(this.selectedClient.id)
            const months = []
            const now = new Date()
            
            for (let i = 5; i >= 0; i--) {
                const d = new Date()
                d.setMonth(d.getMonth() - i)
                d.setDate(1)
                d.setHours(0, 0, 0, 0)
                
                const nextMonth = new Date(d)
                nextMonth.setMonth(nextMonth.getMonth() + 1)
                
                // Sum payments for this month
                let amount = 0
                clientPayments.forEach(payment => {
                    const paymentDate = new Date(payment.paymentDate || payment.payment_date)
                    if (paymentDate >= d && paymentDate < nextMonth) {
                        amount += parseFloat(payment.amount || 0)
                    }
                })
                
                months.push({
                    date: d,
                    label: new Intl.DateTimeFormat('tr-TR', { month: 'short' }).format(d),
                    amount: amount
                })
            }
            
            // Calculate percentages for bar heights
            const maxAmount = Math.max(...months.map(m => m.amount), 1)
            months.forEach(month => {
                month.percentage = maxAmount > 0 ? Math.round((month.amount / maxAmount) * 100) : 0
            })
            
            return months
        },
        visiblePayments() {
            if (!this.selectedClient) return []
            const clientPayments = this.getClientPayments(this.selectedClient.id)
            const sorted = [...clientPayments].sort((a, b) => new Date(b.paymentDate) - new Date(a.paymentDate))
            if (this.showAllPayments) {
                return sorted
            }
            return sorted.slice(0, 3)
        }
    },
    mounted() {
        this.loadClients()
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
            } catch (e) { 
                console.warn('Translation error:', e) 
            }
            return text
        },
        async loadClients() {
            this.loading = true
            try {
                const response = await api.clients.getAll()
                this.clients = response.data || []
            } catch (error) {
                this.clients = []
            } finally {
                this.loading = false
            }
        },
        async loadRelatedData() {
            try {
                const [domainsRes, hostingsRes, websitesRes, servicesRes, invoicesRes, paymentsRes] = await Promise.all([
                    api.domains.getAll().catch(() => ({ data: [] })),
                    api.hostings.getAll().catch(() => ({ data: [] })),
                    api.websites.getAll().catch(() => ({ data: [] })),
                    api.services.getAll().catch(() => ({ data: [] })),
                    api.invoices.getAll().catch(() => ({ data: [] })),
                    api.payments.getAll().catch(() => ({ data: [] })),
                ])
                this.domains = domainsRes.data || []
                this.hostings = hostingsRes.data || []
                this.websites = websitesRes.data || []
                this.services = servicesRes.data || []
                this.invoices = invoicesRes.data || []
                this.payments = paymentsRes.data || []
                
                // Load all projects and tasks
                try {
                    const projectsRes = await api.projects.getAll().catch(() => ({ data: [] }))
                    this.projects = (projectsRes.data || []).map(p => ({
                        ...p,
                        clientId: p.clientId || p.client_id,
                        progress: p.progress || 0,
                        deadline: p.deadline || p.deadline_date
                    }))
                } catch (e) {
                    this.projects = []
                }
                
                try {
                    const tasksRes = await api.tasks.getAll().catch(() => ({ data: [] }))
                    this.tasks = (tasksRes.data || []).map(t => ({
                        ...t,
                        clientId: t.clientId || t.client_id,
                        projectId: t.projectId || t.project_id,
                        dueDate: t.dueDate || t.due_date,
                        completed: t.status === 'done' || t.completed
                    }))
                } catch (e) {
                    this.tasks = []
                }
                
                // Notes will be loaded per client when selected
                this.notes = []

            } catch (error) {
                console.error('Error loading related data:', error)
            }
        },
        async loadClientNotes(clientId) {
            try {
                const response = await api.clients.byClient.notes.getAll(clientId)
                this.notes = (response.data || []).map(n => ({
                    ...n,
                    clientId: n.clientId || n.client_id,
                    date: n.date || n.createdAt || n.created_at
                }))
            } catch (error) {
                console.error('Error loading client notes:', error)
                this.notes = []
            }
        },
        filterClients() {
            // Handled by computed
        },
        async selectClient(client) {
            // If client is an ID, find the client object
            if (typeof client === 'number' || typeof client === 'string') {
                const foundClient = this.clients.find(c => c.id == client)
                if (foundClient) {
                    client = foundClient
                } else {
                    // If client not found in list, try to load it
                    await this.loadClientById(client)
                    return
                }
            }
            this.selectedClient = client
            this.showAllPayments = false
            // Load client-specific notes
            if (client && client.id) {
                await this.loadClientNotes(client.id)
            }
        },
        async loadClientById(clientId) {
            try {
                const response = await api.clients.get(clientId)
                if (response.data) {
                    await this.selectClient(response.data)
                }
            } catch (error) {
                console.error('Error loading client:', error)
            }
        },
        backToList() {
            this.selectedClient = null
        },
        getInitials(name) {
            if (!name) return '?'
            const parts = name.trim().split(' ')
            if (parts.length >= 2) {
                return (parts[0][0] + parts[parts.length - 1][0]).toUpperCase()
            }
            return name.substring(0, 2).toUpperCase()
        },
        getAvatarColor(name) {
            if (!name) return '#999'
            let hash = 0
            for (let i = 0; i < name.length; i++) {
                hash = name.charCodeAt(i) + ((hash << 5) - hash)
            }
            const hue = hash % 360
            return `hsl(${hue}, 65%, 45%)`
        },
        // Count Helpers
        getClientDomainCount(clientId) { return (this.domains || []).filter(d => d.clientId == clientId).length },
        getClientHostingCount(clientId) { return (this.hostings || []).filter(h => h.clientId == clientId).length },
        getClientWebsiteCount(clientId) { return (this.websites || []).filter(w => w.clientId == clientId).length },
        getClientServiceCount(clientId) { return (this.services || []).filter(s => s.clientId == clientId).length },
        getClientInvoiceCount(clientId) { return (this.invoices || []).filter(i => i.clientId == clientId).length },
        getClientPaymentCount(clientId) { return (this.payments || []).filter(p => p.clientId == clientId).length },
        getClientProjectCount(clientId) { return (this.projects || []).filter(p => p.clientId == clientId).length },
        
        // List Helpers
        getClientDomains(clientId) { return (this.domains || []).filter(d => d.clientId == clientId) },
        getClientHostings(clientId) { return (this.hostings || []).filter(h => h.clientId == clientId) },
        getClientWebsites(clientId) { return (this.websites || []).filter(w => w.clientId == clientId) },
        getClientServices(clientId) { return (this.services || []).filter(s => s.clientId == clientId) },
        getClientInvoices(clientId) { return (this.invoices || []).filter(i => i.clientId == clientId) },
        getClientPayments(clientId) { return (this.payments || []).filter(p => p.clientId == clientId) },
        getClientProjects(clientId) { 
            const projects = (this.projects || []).filter(p => p.clientId == clientId)
            // Calculate progress for each project based on tasks
            return projects.map(project => {
                const projectTasks = (this.tasks || []).filter(t => t.projectId == project.id || t.project_id == project.id)
                const activeTasks = projectTasks.filter(t => t.status !== 'cancelled' && t.status !== 'done').length
                const completedTasks = projectTasks.filter(t => t.status === 'done').length
                const totalActive = activeTasks + completedTasks
                const progress = totalActive > 0 ? Math.round((completedTasks / totalActive) * 100) : (project.progress || 0)
                return { ...project, progress }
            })
        },
        getClientTasks(clientId) { return (this.tasks || []).filter(t => t.clientId == clientId && !t.completed) },
        getClientNotes(clientId) { return (this.notes || []).filter(n => n.clientId == clientId).sort((a,b) => new Date(b.date) - new Date(a.date)) },

        getDomainStatusClass(domain) {
            if (!domain.expirationDate) return 'status-ok'
            const daysLeft = this.getDaysUntilExpiry(domain.expirationDate)
            if (daysLeft <= 7) return 'status-critical'
            if (daysLeft <= 30) return 'status-warning'
            return 'status-ok'
        },
        getDaysUntilExpiry(dateString) {
            if (!dateString) return Infinity
            try {
                const expiryDate = new Date(dateString)
                const today = new Date()
                today.setHours(0, 0, 0, 0)
                expiryDate.setHours(0, 0, 0, 0)
                return Math.ceil((expiryDate - today) / (1000 * 60 * 60 * 24))
            } catch (e) { return Infinity }
        },
        isOverdue(dateString) {
            return this.getDaysUntilExpiry(dateString) < 0
        },
        formatDate(dateString) {
            if (!dateString) return '-'
            try {
                const date = new Date(dateString)
                return new Intl.DateTimeFormat('tr-TR', { day: 'numeric', month: 'short', year: 'numeric' }).format(date)
            } catch (e) { return dateString }
        },
        formatCurrency(amount) {
            if (typeof amount !== 'number') return '0'
            return new Intl.NumberFormat('tr-TR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(amount)
        },
        // Navigation (Mocked)
        navigateToDomain(id) { this.navigate('domains', 'showDomainDetail', id) },
        navigateToHosting(id) { this.navigate('hostings', 'showHostingDetail', id) },
        navigateToWebsite(id) { this.navigate('websites', 'showWebsiteDetail', id) },
        navigateToService(id) { this.navigate('services', 'showServiceDetail', id) },
        navigateToInvoice(id) { this.navigate('invoices', 'showInvoiceDetail', id) },
        navigateToProject(id) { this.navigate('projects', 'selectProject', id) },
        
        navigate(tab, method, id) {
            if (typeof window.DomainControl !== 'undefined' && window.DomainControl.switchTab) {
                this.backToList()
                window.DomainControl.switchTab(tab)
                setTimeout(() => {
                    if (window.DomainControl[method]) window.DomainControl[method](id)
                }, 100)
            }
        },
        showAddModal() {
            this.editingClient = null
            this.modalOpen = true
        },
        editClient(id) {
            const client = this.clients.find(c => c.id === id)
            if (client) {
                this.editingClient = client
                this.modalOpen = true
            }
        },
        closeModal() {
            this.modalOpen = false
            this.editingClient = null
        },
        async handleClientSaved() {
            await this.loadClients()
            await this.loadRelatedData()
            if (typeof window.DomainControl !== 'undefined' && window.DomainControl.loadClients) {
                window.DomainControl.loadClients()
            }
        },
        confirmDelete(client) {
            const clientObj = typeof client === 'object' ? client : this.clients.find(c => c.id === client)
            if (!clientObj) return
            if (confirm(this.translate('domaincontrol', 'Are you sure you want to delete {name}?', { name: clientObj.name }))) {
                this.deleteClient(clientObj.id)
            }
        },
        async deleteClient(id) {
            try {
                await api.clients.delete(id)
                this.clients = this.clients.filter(c => c.id !== id)
                if (this.selectedClient && this.selectedClient.id === id) {
                    this.selectedClient = null
                }
                this.loadRelatedData()
            } catch (error) {
                alert(this.translate('domaincontrol', 'Error deleting client'))
            }
        },
        getProjectStatusClass(status) {
            if (status === 'Completed') return 'badge-success'
            if (status === 'In Progress') return 'badge-primary'
            return 'badge-neutral'
        },
        
        // --- Note Methods ---
        openNoteModal(note = null) {
            if (note) {
                this.editingNote = { ...note }
            } else {
                this.editingNote = { id: null, content: '', clientId: this.selectedClient.id }
            }
            this.noteModalOpen = true
        },
        closeNoteModal() {
            this.noteModalOpen = false
            this.editingNote = { id: null, content: '' }
        },
        async saveNote() {
            if (!this.editingNote.content || !this.selectedClient) return
            
            try {
                if (this.editingNote.id) {
                    // Update
                    await api.clients.byClient.notes.update(
                        this.selectedClient.id,
                        this.editingNote.id,
                        { content: this.editingNote.content }
                    )
                } else {
                    // Create
                    await api.clients.byClient.notes.create(
                        this.selectedClient.id,
                        { content: this.editingNote.content }
                    )
                }
                // Reload notes
                await this.loadClientNotes(this.selectedClient.id)
                this.closeNoteModal()
            } catch (error) {
                console.error('Error saving note:', error)
                alert(this.translate('domaincontrol', 'Error saving note'))
            }
        },
        stripHtml(html) {
            const tmp = document.createElement("DIV");
            tmp.innerHTML = html;
            let text = tmp.textContent || tmp.innerText || "";
            return text.length > 60 ? text.substring(0, 60) + '...' : text;
        },
        
        // --- Project Modal Methods ---
        showAddProjectModal() {
            if (!this.selectedClient) return
            this.editingProject = null
            this.projectModalOpen = true
        },
        closeProjectModal() {
            this.projectModalOpen = false
            this.editingProject = null
        },
        async handleProjectSaved() {
            await this.loadRelatedData()
            // Reload projects for the current client
            if (this.selectedClient) {
                try {
                    const response = await api.projects.byClient(this.selectedClient.id)
                    this.projects = (response.data || []).map(p => ({
                        ...p,
                        clientId: p.clientId || p.client_id,
                        progress: p.progress || 0,
                        deadline: p.deadline || p.deadline_date
                    }))
                } catch (error) {
                    console.error('Error loading client projects:', error)
                }
            }
        },
        
        // --- Task Modal Methods ---
        showAddTaskModal() {
            if (!this.selectedClient) return
            this.editingTask = null
            this.taskModalOpen = true
        },
        closeTaskModal() {
            this.taskModalOpen = false
            this.editingTask = null
        },
        async handleTaskSaved() {
            await this.loadRelatedData()
            // Reload tasks for the current client
            if (this.selectedClient) {
                try {
                    const response = await api.tasks.byClient(this.selectedClient.id)
                    this.tasks = (response.data || []).map(t => ({
                        ...t,
                        clientId: t.clientId || t.client_id,
                        dueDate: t.dueDate || t.due_date,
                        completed: t.status === 'done' || t.completed
                    }))
                } catch (error) {
                    console.error('Error loading client tasks:', error)
                }
            }
        }
    },
}
</script>

<style scoped>
/* GLOBAL LAYOUT */
.clients-view-container {
    padding: 20px;
    height: 100%;
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
    margin-bottom: 24px;
    flex-wrap: wrap;
    gap: 16px;
}
.header-left { display: flex; align-items: center; }
.nc-app-title { margin: 0; font-size: 24px; font-weight: bold; display: flex; align-items: center; gap: 12px; }
.header-icon { opacity: 0.8; color: var(--color-text-maxcontrast); }

.header-actions { display: flex; align-items: center; gap: 12px; }

/* Search Bar */
.search-wrapper { position: relative; width: 250px; }
.search-icon { position: absolute; left: 10px; top: 50%; transform: translateY(-50%); color: var(--color-text-maxcontrast); opacity: 0.7; }
.search-input { width: 100%; padding: 8px 12px 8px 36px; border-radius: var(--border-radius-pill); border: 1px solid var(--color-border); background: var(--color-main-background); color: var(--color-main-text); }
.search-input:focus { border-color: var(--color-primary-element-element); outline: none; }

/* --- Client List --- */
.nc-client-list {
    display: flex; flex-direction: column; background: var(--color-main-background);
    border: 1px solid var(--color-border); border-radius: var(--border-radius-large); overflow: hidden;
}

.nc-list-item {
    display: flex; align-items: center; padding: 12px 16px; border-bottom: 1px solid var(--color-border);
    cursor: pointer; transition: background 0.1s ease;
}
.nc-list-item:last-child { border-bottom: none; }
.nc-list-item:hover { background-color: var(--color-background-hover); }

/* Item Columns */
.item-avatar { margin-right: 16px; }
.avatar-circle {
    width: 40px; height: 40px; border-radius: 50%; color: #fff; display: flex; align-items: center; justify-content: center;
    font-weight: bold; font-size: 16px; text-shadow: 0 1px 2px rgba(0,0,0,0.2);
}

.item-content { flex: 1; min-width: 0; display: flex; flex-direction: column; gap: 2px; }
.item-main { display: flex; align-items: baseline; gap: 8px; flex-wrap: wrap; }
.client-name { font-weight: 600; font-size: 16px; color: var(--color-main-text); }
.client-sub { font-size: 13px; color: var(--color-text-maxcontrast); }
.item-meta { display: flex; align-items: center; gap: 6px; font-size: 12px; color: var(--color-text-maxcontrast); margin-top: 2px; }

.item-stats { display: flex; gap: 12px; margin-right: 16px; align-items: center; }
.stat-badge {
    display: flex; align-items: center; gap: 4px; padding: 2px 8px; background: var(--color-background-hover);
    border-radius: 12px; font-size: 12px; color: var(--color-text-maxcontrast);
}

.item-actions { display: flex; gap: 4px; opacity: 0.6; transition: opacity 0.2s; }
.nc-list-item:hover .item-actions { opacity: 1; }
.action-btn {
    background: none; border: none; padding: 6px; color: var(--color-text-maxcontrast); cursor: pointer; border-radius: 4px;
}
.action-btn:hover { background: var(--color-background-dark); color: var(--color-main-text); }
.delete-hover:hover { color: var(--color-element-error); background: rgba(233, 50, 45, 0.1); }

/* --- Detail View --- */
.nc-detail-view { display: flex; flex-direction: column; gap: 24px; }
.nc-detail-header {
    display: flex; justify-content: space-between; align-items: center; padding-bottom: 20px; border-bottom: 1px solid var(--color-border);
}
.detail-avatar {
    width: 48px; height: 48px; border-radius: 50%; color: #fff; display: flex; align-items: center; justify-content: center;
    font-weight: bold; font-size: 20px; margin: 0 16px;
}
.detail-title { margin: 0; font-size: 24px; font-weight: bold; }

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
    color: var(--color-text-maxcontrast);
}
.primary-bg { background: rgba(0, 130, 201, 0.1); color: var(--color-primary-element-element); }
.success-bg { background: rgba(70, 186, 97, 0.1); color: var(--color-element-success); }
.warning-bg { background: rgba(233, 144, 2, 0.1); color: var(--color-element-warning); }
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
.inline-icon { opacity: 0.7; }

/* Financial Panel Specifics */
.financial-body { padding: 16px; }
.financial-cards { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; margin-bottom: 20px; }
.fin-card {
    background: var(--color-background-hover); border-radius: var(--border-radius-large); padding: 12px;
    display: flex; flex-direction: column; align-items: center; justify-content: center; text-align: center;
}
.fin-label { font-size: 12px; color: var(--color-text-maxcontrast); text-transform: uppercase; margin-bottom: 4px; }
.fin-value { font-size: 16px; font-weight: bold; }
.text-success { color: var(--color-element-success); }
.text-error { color: var(--color-element-error); }
.text-primary { color: var(--color-primary-element-element); }

/* CSS Bar Chart */
.income-chart { background: var(--color-background-dark); border-radius: var(--border-radius-large); padding: 16px; }
.chart-header { margin-bottom: 12px; font-size: 13px; font-weight: 600; color: var(--color-text-maxcontrast); display: flex; align-items: center; gap: 8px; }
.chart-bars { display: flex; justify-content: space-between; align-items: flex-end; height: 100px; gap: 8px; }
.chart-col { flex: 1; display: flex; flex-direction: column; align-items: center; height: 100%; justify-content: flex-end; }
.bar-container { width: 100%; height: 80px; background: rgba(0,0,0,0.05); border-radius: 4px; position: relative; display: flex; align-items: flex-end; }
.bar-fill {
    width: 100%; background: var(--color-primary-element-element); border-radius: 4px 4px 0 0; opacity: 0.8; transition: height 0.3s;
}
.bar-fill:hover { opacity: 1; }
.bar-label { font-size: 11px; color: var(--color-text-maxcontrast); margin-top: 6px; }

/* Payment History */
.mt-4 { margin-top: 16px; }
.payment-list { display: flex; flex-direction: column; }
.payment-row { display: flex; justify-content: space-between; align-items: center; padding: 10px 0; border-bottom: 1px solid var(--color-border); font-size: 13px; }
.payment-row:last-child { border-bottom: none; }
.payment-date { color: var(--color-text-maxcontrast); }
.payment-amount { font-weight: 600; }
.show-more-container { text-align: center; margin-top: 12px; }
.show-more-btn { background: none; border: none; color: var(--color-primary-element-element); cursor: pointer; font-size: 12px; display: flex; align-items: center; justify-content: center; gap: 4px; margin: 0 auto; }
.show-more-btn:hover { text-decoration: underline; }

/* Projects List */
.no-padding { padding: 0; }
.project-list { display: flex; flex-direction: column; }
.project-row { padding: 12px 16px; border-bottom: 1px solid var(--color-border); display: grid; grid-template-columns: 2fr 2fr 1fr; align-items: center; gap: 16px; }
.project-row:last-child { border-bottom: none; }
.project-name { font-weight: 600; font-size: 14px; margin-bottom: 2px; }
.project-deadline { font-size: 12px; color: var(--color-text-maxcontrast); }
.project-progress { display: flex; flex-direction: column; gap: 8px; min-width: 0; }
.progress-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px; }
.progress-label { font-size: 12px; color: var(--color-text-maxcontrast); font-weight: 500; }
.progress-percentage { font-size: 12px; font-weight: 600; color: var(--color-main-text); }
.nc-badge { font-size: 11px; padding: 2px 8px; border-radius: 10px; font-weight: 600; }
.badge-success { background: rgba(70, 186, 97, 0.1); color: var(--color-element-success); }
.badge-primary { background: rgba(0, 130, 201, 0.1); color: var(--color-primary-element-element); }
.badge-neutral { background: var(--color-background-dark); color: var(--color-text-maxcontrast); }

/* Tasks List */
.task-list { display: flex; flex-direction: column; }
.task-row { padding: 12px 16px; border-bottom: 1px solid var(--color-border); display: flex; gap: 12px; align-items: center; }
.task-row:last-child { border-bottom: none; }
.task-check { display: flex; align-items: center; }
.check-circle { width: 18px; height: 18px; border: 2px solid var(--color-text-maxcontrast); border-radius: 50%; opacity: 0.5; cursor: pointer; }
.check-circle:hover { border-color: var(--color-primary-element-element); opacity: 1; }
.task-title { font-size: 14px; font-weight: 500; margin-bottom: 2px; }
.task-meta { display: flex; gap: 12px; font-size: 12px; align-items: center; }
.task-due { display: flex; align-items: center; gap: 4px; color: var(--color-text-maxcontrast); }
.task-priority { text-transform: uppercase; font-size: 10px; font-weight: 700; }
.priority-High { color: var(--color-element-error); }
.priority-Medium { color: var(--color-element-warning); }

/* Notes List */
.note-list { display: flex; flex-direction: column; }
.note-row { padding: 12px 16px; border-bottom: 1px solid var(--color-border); display: flex; gap: 12px; cursor: pointer; transition: background 0.1s; }
.note-row:hover { background: var(--color-background-hover); }
.note-icon { color: var(--color-text-maxcontrast); opacity: 0.7; padding-top: 2px; }
.note-content { flex: 1; min-width: 0; }
.note-text { font-size: 14px; margin-bottom: 4px; }
.note-date { font-size: 11px; color: var(--color-text-maxcontrast); }
.note-arrow { display: flex; align-items: center; color: var(--color-text-maxcontrast); opacity: 0.5; }

/* Info Rows */
.info-row { display: flex; align-items: center; padding: 12px 16px; border-bottom: 1px solid var(--color-border); }
.info-row:last-child { border-bottom: none; }
.row-icon { color: var(--color-text-maxcontrast); margin-right: 12px; }
.row-label { width: 100px; color: var(--color-text-maxcontrast); font-size: 13px; }
.row-value { color: var(--color-main-text); font-weight: 500; font-size: 14px; }
.row-value.link { color: var(--color-primary-element-element); text-decoration: none; }
.row-value.link:hover { text-decoration: underline; }

/* Mini List */
.mini-list { display: flex; flex-direction: column; }
.mini-item {
    display: flex; justify-content: space-between; align-items: center; padding: 10px 16px;
    border-bottom: 1px solid var(--color-border); cursor: pointer; transition: background 0.1s;
}
.mini-item:last-child { border-bottom: none; }
.mini-item:hover { background: var(--color-background-hover); }
.mini-title { font-weight: 500; font-size: 13px; }
.mini-sub { font-size: 12px; color: var(--color-text-maxcontrast); }
.empty-mini { padding: 12px 16px; font-style: italic; color: var(--color-text-maxcontrast); font-size: 13px; text-align: center; }
.empty-mini-large { padding: 24px; text-align: center; font-style: italic; color: var(--color-text-maxcontrast); }

.count-badge {
    background: var(--color-primary-element-element); color: #fff; padding: 1px 8px; border-radius: 10px; font-size: 11px; font-weight: bold;
}
.status-critical { border-left: 3px solid var(--color-element-error); }
.status-warning { border-left: 3px solid var(--color-element-warning); }

/* Empty & Loading */
.nc-empty-state, .nc-loading-state {
    padding: 60px; text-align: center; display: flex; flex-direction: column; align-items: center;
    color: var(--color-text-maxcontrast);
}
.nc-state-icon { opacity: 0.5; margin-bottom: 16px; }
.spin-animation { animation: spin 1s linear infinite; }

/* Modal Simple Overlay */
.nc-modal-overlay { position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.4); display: flex; align-items: center; justify-content: center; z-index: 2000; backdrop-filter: blur(2px); }
.nc-modal-content { background: var(--color-main-background); border-radius: var(--border-radius-large); box-shadow: 0 10px 30px rgba(0,0,0,0.3); width: 90%; max-width: 500px; display: flex; flex-direction: column; }
.nc-modal-header { padding: 16px; border-bottom: 1px solid var(--color-border); display: flex; justify-content: space-between; align-items: center; }
.nc-modal-title { margin: 0; font-size: 18px; font-weight: bold; }
.nc-modal-close { background: none; border: none; cursor: pointer; color: var(--color-text-maxcontrast); }
.nc-modal-body { padding: 20px; }
.nc-modal-footer { padding: 16px; border-top: 1px solid var(--color-border); display: flex; justify-content: flex-end; gap: 12px; background: var(--color-background-hover); border-radius: 0 0 var(--border-radius-large) var(--border-radius-large); }
.form-group label { display: block; margin-bottom: 8px; font-weight: 600; font-size: 13px; }

/* Responsive */
@media (max-width: 900px) {
    .nc-detail-content { grid-template-columns: 1fr; }
    .desktop-only { display: none; }
    .stats-grid { grid-template-columns: 1fr; }
    .financial-cards { grid-template-columns: 1fr; }
    .project-row { grid-template-columns: 1fr; gap: 8px; }
}
</style>