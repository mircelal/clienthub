<template>
    <div class="app-content-wrapper">
        <!-- ========================================== -->
        <!-- MODALS (Global scope)                      -->
        <!-- ========================================== -->
        <ClientModal :open="modalOpen" :client="editingClient" @close="closeModal" @saved="handleClientSaved" />
        <ProjectModal :open="projectModalOpen" :project="editingProject" :clients="clients" :presetClientId="selectedClient?.id" @close="closeProjectModal" @saved="handleProjectSaved" />
        <TaskModal :open="taskModalOpen" :task="editingTask" :clients="clients" :projects="projects" :tasks="tasks" :presetClientId="selectedClient?.id" @close="closeTaskModal" @saved="handleTaskSaved" />
        <InvoiceModal :open="invoiceModalOpen" :invoice="editingInvoice" :clients="clients" :presetClientId="selectedClient?.id" @close="closeInvoiceModal" @saved="handleInvoiceSaved" />
        <DomainModal :open="domainModalOpen" :domain="editingDomain" :clients="clients" :presetClientId="selectedClient?.id" @close="closeDomainModal" @saved="handleDomainSaved" />
        <HostingModal :open="hostingModalOpen" :hosting="editingHosting" :clients="clients" :packages="hostingPackages" :presetClientId="selectedClient?.id" @close="closeHostingModal" @saved="handleHostingSaved" />
        <ClientNoteModal :open="noteModalOpen" :note="editingNote" :clientId="selectedClient?.id" @close="closeNoteModal" @saved="handleNoteSaved" />
        <TransactionModal 
            :open="transactionModalOpen" 
            :transaction="editingTransaction" 
            :clients="clients" 
            :projects="projects" 
            :categories="transactionCategories"
            :presetClientId="selectedClient?.id"
            :presetType="transactionPresetType"
            @close="closeTransactionModal" 
            @saved="handleTransactionSaved" 
        />

        <!-- ========================================== -->
        <!-- LEFT PANE: LIST                            -->
        <!-- ========================================== -->
        <div class="app-content-list" :class="{ 'mobile-hidden': isMobile && selectedClient }">
            <div class="app-content-list-header">
                    <div class="search-wrapper">
                    <div class="search-wrapper-inner">
                        <Magnify :size="20" class="search-icon" />
                        <input type="text" v-model="searchQuery" :placeholder="translate('domaincontrol', 'Müşteri ara...')" class="search-input" />
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
                                {{ translate('domaincontrol', 'New Client') }}
                    </NcButton>
                </div>
                    </header>
            </div>
        </div>

            <div class="app-content-list-wrapper">
                <div v-if="loading" class="loading-container">
                    <Refresh :size="32" class="spin-animation" />
                </div>
                <div v-else-if="filteredClients.length === 0" class="empty-list">
                    <div class="empty-text">{{ translate('domaincontrol', 'Müşteri bulunamadı') }}</div>
                    </div>
                <ul v-else class="app-navigation-list">
                    <li v-for="client in filteredClients" :key="client.id" class="app-navigation-entry" :class="{ 'active': selectedClient && selectedClient.id === client.id }" @click="selectClient(client)">
                        <div class="app-navigation-entry-icon">
                        <div class="avatar-circle" :style="{ backgroundColor: getAvatarColor(client.name) }">
                            {{ getInitials(client.name) }}
                </div>
                </div>
                        <div class="app-navigation-entry-content">
                            <div class="app-navigation-entry-name">{{ client.name }}</div>
                            <div class="app-navigation-entry-details">{{ client.company || client.email || '' }}</div>
                        </div>
                    </li>
                </ul>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- RIGHT PANE: CRM DETAIL VIEW                -->
        <!-- ========================================== -->
        <div class="app-content-details" :class="{ 'mobile-hidden': isMobile && !selectedClient }">

            <!-- Empty State -->
            <div v-if="!selectedClient" class="empty-content">
                <div class="empty-content-icon"><AccountGroup :size="64" /></div>
                <h2 class="empty-content-title">{{ translate('domaincontrol', 'Bir müşteri seçin') }}</h2>
            </div>

            <!-- Detail Content -->
            <div v-else class="crm-detail-container">
                
                <!-- HEADER -->
                <div class="crm-header">
                    <div class="crm-header-top">
                        <button v-if="isMobile" class="icon-button back-button" @click="backToList"><ArrowLeft :size="24" /></button>
                        <div class="crm-profile-info">
                            <div class="avatar-xl" :style="{ backgroundColor: getAvatarColor(selectedClient.name) }">
                        {{ getInitials(selectedClient.name) }}
                        </div>
                            <div class="crm-profile-text">
                                <h1 class="crm-client-name">{{ selectedClient.name }}</h1>
                                <div class="crm-client-meta">
                                    <span v-if="selectedClient.company" class="meta-item">
                                        <Domain :size="14" />
                                        <span>{{ selectedClient.company }}</span>
                                    </span>
                                    <span v-if="selectedClient.email" class="meta-item">
                                        <Email :size="14" />
                                        <span>{{ selectedClient.email }}</span>
                                    </span>
                                    <span v-if="selectedClient.phone" class="meta-item">
                                        <Phone :size="14" />
                                        <span>{{ selectedClient.phone }}</span>
                                    </span>
                        </div>
                    </div>
                        </div>
                        <div class="crm-header-actions">
                            <!-- Quick Actions Dropdown Logic could go here, sticking to buttons for now -->
                            <NcButton @click="editClient(selectedClient.id)">{{ translate('domaincontrol', 'Düzenle') }}</NcButton>
                            <button class="icon-button danger" @click="confirmDelete(selectedClient)"><Delete :size="20" /></button>
                        </div>
                    </div>

                    <!-- TABS -->
                    <div class="crm-tabs-scroll">
                        <div class="crm-tabs">
                            <button class="crm-tab" :class="{ active: activeTab === 'overview' }" @click="activeTab = 'overview'">{{ translate('domaincontrol', 'Genel Bakış') }}</button>
                            <button class="crm-tab" :class="{ active: activeTab === 'finance' }" @click="activeTab = 'finance'">{{ translate('domaincontrol', 'Faturalar & Ödemeler') }}</button>
                            <button class="crm-tab" :class="{ active: activeTab === 'services' }" @click="activeTab = 'services'">{{ translate('domaincontrol', 'Hizmetler') }}</button>
                            <button class="crm-tab" :class="{ active: activeTab === 'projects' }" @click="activeTab = 'projects'">
                                {{ translate('domaincontrol', 'Projeler') }} <span class="tab-badge" v-if="clientProjects.length">{{ clientProjects.length }}</span>
                        </button>
                            <button class="crm-tab" :class="{ active: activeTab === 'tasks' }" @click="activeTab = 'tasks'">
                                {{ translate('domaincontrol', 'Görevler') }} <span class="tab-badge" v-if="clientTasks.length">{{ clientTasks.length }}</span>
                        </button>
                            <button class="crm-tab" :class="{ active: activeTab === 'notes' }" @click="activeTab = 'notes'">{{ translate('domaincontrol', 'Notlar') }}</button>
                            <button class="crm-tab" :class="{ active: activeTab === 'files' }" @click="activeTab = 'files'">
                                {{ translate('domaincontrol', 'Müşteri Dosyaları') }} <span class="tab-badge" v-if="clientFiles.length">{{ clientFiles.length }}</span>
                            </button>
                </div>
            </div>
        </div>

                <!-- CONTENT SCROLL AREA -->
                <div class="crm-content-scroll">
                    
                    <!-- 1. GENEL BAKIŞ (DASHBOARD) -->
                    <div v-if="activeTab === 'overview'" class="tab-pane">
                        <!-- Stats Grid -->
                        <div class="stats-grid">
                            <div class="stat-card">
                                <div class="stat-content">
                                    <div class="stat-label">{{ translate('domaincontrol', 'Toplam Ciro') }}</div>
                                    <div class="stat-value success-text">{{ formatCurrency(financials.totalPaid) }}</div>
                    </div>
                                <div class="stat-icon success-bg"><CashCheck :size="24" /></div>
                </div>
                            <div class="stat-card">
                                <div class="stat-content">
                                    <div class="stat-label">{{ translate('domaincontrol', 'Bekleyen Ödeme') }}</div>
                                    <div class="stat-value error-text">{{ formatCurrency(financials.totalUpcoming + financials.totalOverdue) }}</div>
                </div>
                                <div class="stat-icon error-bg"><AlertCircleOutline :size="24" /></div>
            </div>
                            <div class="stat-card">
                                <div class="stat-content">
                                    <div class="stat-label">{{ translate('domaincontrol', 'Aktif Projeler') }}</div>
                                    <div class="stat-value">{{ clientProjects.length }}</div>
                        </div>
                                <div class="stat-icon info-bg"><BriefcaseOutline :size="24" /></div>
                                </div>
                            <div class="stat-card">
                                <div class="stat-content">
                                    <div class="stat-label">{{ translate('domaincontrol', 'Hizmetler') }}</div>
                                    <div class="stat-value">{{ clientServicesCount }}</div>
                                </div>
                                <div class="stat-icon warning-bg"><ServerNetwork :size="24" /></div>
                                </div>
                            </div>

                        <!-- Chart & Activity Row -->
                        <div class="dashboard-columns">
                            <!-- Left: Income Chart -->
                            <div class="dashboard-col main-col">
                                <div class="content-box">
                                    <div class="box-header">
                                        <h3>{{ translate('domaincontrol', 'Yıllık Ödeme Geçmişi') }}</h3>
                                </div>
                                    <div class="box-body chart-container">
                                        <div v-if="monthlyPaymentData.length === 0" class="chart-empty">
                                            {{ translate('domaincontrol', 'Henüz ödeme verisi yok') }}
                                        </div>
                                        <div v-else>
                                            <!-- SVG Chart with Real Data -->
                                            <svg viewBox="0 0 500 150" class="crm-chart">
                                                <!-- Grid lines -->
                                                <line x1="20" y1="130" x2="480" y2="130" stroke="var(--color-border)" stroke-width="1" />
                                                <line x1="20" y1="100" x2="480" y2="100" stroke="var(--color-border)" stroke-width="1" stroke-dasharray="4" />
                                                <line x1="20" y1="70" x2="480" y2="70" stroke="var(--color-border)" stroke-width="1" stroke-dasharray="4" />
                                                <line x1="20" y1="40" x2="480" y2="40" stroke="var(--color-border)" stroke-width="1" stroke-dasharray="4" />
                                                
                                                <!-- Y-axis labels -->
                                                <text x="10" y="135" font-size="10" fill="var(--color-text-maxcontrast)" text-anchor="end">0</text>
                                                <text x="10" y="105" font-size="10" fill="var(--color-text-maxcontrast)" text-anchor="end">{{ formatCurrencyShort(chartMaxValue * 0.5) }}</text>
                                                <text x="10" y="75" font-size="10" fill="var(--color-text-maxcontrast)" text-anchor="end">{{ formatCurrencyShort(chartMaxValue * 0.75) }}</text>
                                                <text x="10" y="45" font-size="10" fill="var(--color-text-maxcontrast)" text-anchor="end">{{ formatCurrencyShort(chartMaxValue) }}</text>
                                                
                                                <!-- Area fill -->
                                                <path 
                                                    :d="chartAreaPath" 
                                                    fill="var(--color-primary-element)" 
                                                    fill-opacity="0.1" 
                                                />
                                                
                                                <!-- Line path -->
                                                <path 
                                                    :d="chartPath" 
                                                    fill="none" 
                                                    stroke="var(--color-primary-element)" 
                                                    stroke-width="3" 
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                />
                                                
                                                <!-- Data points -->
                                                <g v-for="(item, index) in monthlyPaymentData" :key="index">
                                                    <circle 
                                                        :cx="monthlyPaymentData.length > 1 ? 20 + (index * (460 / (monthlyPaymentData.length - 1))) : 250"
                                                        :cy="item.total > 0 ? 130 - ((item.total / chartMaxValue) * 90) : 130"
                                                        r="4" 
                                                        fill="var(--color-primary-element)"
                                                        stroke="var(--color-main-background)"
                                                        stroke-width="2"
                                                    />
                                                </g>
                                            </svg>
                                            <div class="chart-labels">
                                                <span v-for="(item, index) in monthlyPaymentData" :key="index">
                                                    {{ item.monthName }}
                                                </span>
                                </div>
                                    </div>
                                </div>
                            </div>

                                <div class="content-box mt-4">
                                    <div class="box-header">
                                        <h3>{{ translate('domaincontrol', 'İletişim & Detaylar') }}</h3>
                                </div>
                                    <div class="box-body grid-2">
                                        <div class="info-group">
                                            <div class="info-label">{{ translate('domaincontrol', 'E-posta') }}</div>
                                            <div class="info-val">
                                                <a v-if="selectedClient.email" :href="`mailto:${selectedClient.email}`" class="info-link">
                                                    <Email :size="14" />
                                                    {{ selectedClient.email }}
                                                </a>
                                                <span v-else>-</span>
                                </div>
                                        </div>
                                        <div class="info-group">
                                            <div class="info-label">{{ translate('domaincontrol', 'Telefon') }}</div>
                                            <div class="info-val">
                                                <a v-if="selectedClient.phone" :href="`tel:${selectedClient.phone}`" class="info-link">
                                                    <Phone :size="14" />
                                                    {{ selectedClient.phone }}
                                                </a>
                                                <span v-else>-</span>
                                        </div>
                                        </div>
                                        <div v-if="selectedClient.company" class="info-group">
                                            <div class="info-label">{{ translate('domaincontrol', 'Şirket / Ünvan') }}</div>
                                            <div class="info-val">
                                                <Domain :size="14" />
                                                {{ selectedClient.company }}
                                    </div>
                                </div>
                                        <div v-if="selectedClient.address" class="info-group full-w">
                                            <div class="info-label">{{ translate('domaincontrol', 'Adres') }}</div>
                                            <div class="info-val">
                                                <MapMarker :size="14" />
                                                {{ selectedClient.address }}
                                </div>
                            </div>
                                        </div>
                        </div>
                    </div>

                            <!-- Right: Activity Timeline -->
                            <div class="dashboard-col side-col">
                                <div class="content-box">
                                    <div class="box-header">
                                        <h3>{{ translate('domaincontrol', 'Son Aktiviteler') }}</h3>
                        </div>
                                    <div class="box-body no-padding">
                                        <div v-if="clientActivities.length === 0" class="empty-timeline">
                                            <p>{{ translate('domaincontrol', 'Henüz aktivite yok') }}</p>
                                        </div>
                                        <div v-else class="timeline">
                                            <div 
                                                v-for="activity in clientActivities" 
                                                :key="activity.id" 
                                                class="timeline-item"
                                            >
                                                <div class="timeline-dot" :class="activity.dotClass"></div>
                                                <div class="timeline-content">
                                                    <div class="timeline-title">{{ activity.title }}</div>
                                                    <div class="timeline-date">{{ activity.dateText }}</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                        </div>
                            </div>
                                    </div>
                                        </div>

                    <!-- 2. FATURALAR & FİNANS -->
                    <div v-if="activeTab === 'finance'" class="tab-pane">
                         <div class="pane-actions finance-actions">
                            <NcButton type="primary" @click="showAddInvoiceModal">
                                <template #icon><Plus :size="18" /></template>
                                {{ translate('domaincontrol', 'Fatura Oluştur') }}
                            </NcButton>
                            <NcButton type="secondary" @click="showAddPaymentModal">
                                <template #icon><Plus :size="18" /></template>
                                {{ translate('domaincontrol', 'Ödeme Ekle') }}
                            </NcButton>
                            <NcButton type="secondary" @click="showAddExpenseModal">
                                <template #icon><Plus :size="18" /></template>
                                {{ translate('domaincontrol', 'Gider/Masraf Ekle') }}
                            </NcButton>
                        </div>
                        
                        <!-- Faturalar -->
                        <div class="content-box mb-4">
                            <div class="box-header">
                                <h3>{{ translate('domaincontrol', 'Faturalar') }}</h3>
                                </div>
                            <table class="crm-table">
                                <thead>
                                    <tr>
                                        <th>{{ translate('domaincontrol', 'Fatura #') }}</th>
                                        <th>{{ translate('domaincontrol', 'Durum') }}</th>
                                        <th>{{ translate('domaincontrol', 'Tarih') }}</th>
                                        <th>{{ translate('domaincontrol', 'Vade') }}</th>
                                        <th class="text-right">{{ translate('domaincontrol', 'Tutar') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-if="clientInvoices.length === 0">
                                        <td colspan="5" class="text-center empty-table">{{ translate('domaincontrol', 'Fatura bulunamadı') }}</td>
                                    </tr>
                                    <tr v-for="inv in clientInvoices" :key="inv.id" @click="navigateToInvoice(inv.id)" class="table-row-clickable">
                                        <td class="font-bold"><a href="#" @click.stop="navigateToInvoice(inv.id)">{{ inv.number }}</a></td>
                                        <td><span class="status-pill" :class="inv.status">{{ inv.statusLabel }}</span></td>
                                        <td>{{ inv.date }}</td>
                                        <td>{{ inv.dueDate }}</td>
                                        <td class="text-right font-bold">{{ formatCurrency(inv.amount, inv.currency) }}</td>
                                    </tr>
                                </tbody>
                            </table>
                    </div>

                        <!-- Ödemeler ve Giderler -->
                        <div class="content-box">
                            <div class="box-header">
                                <h3>{{ translate('domaincontrol', 'Gelirler ve Giderler') }}</h3>
                        </div>
                            <table class="crm-table">
                                <thead>
                                    <tr>
                                        <th>{{ translate('domaincontrol', 'Tarih') }}</th>
                                        <th>{{ translate('domaincontrol', 'Tip') }}</th>
                                        <th>{{ translate('domaincontrol', 'Açıklama') }}</th>
                                        <th>{{ translate('domaincontrol', 'Ödeme Yöntemi') }}</th>
                                        <th class="text-right">{{ translate('domaincontrol', 'Tutar') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-if="clientPayments.length === 0">
                                        <td colspan="5" class="text-center empty-table">{{ translate('domaincontrol', 'Ödeme bulunamadı') }}</td>
                                    </tr>
                                    <tr v-for="payment in clientPayments" :key="payment.id" class="table-row-clickable">
                                        <td>{{ formatDate(payment.date) }}</td>
                                        <td>
                                            <span class="payment-type-badge" :class="payment.type">
                                                {{ payment.typeLabel }}
                                            </span>
                                        </td>
                                        <td>{{ payment.description }}</td>
                                        <td>{{ payment.method || '-' }}</td>
                                        <td class="text-right font-bold" :class="payment.type === 'expense' ? 'error-text' : 'success-text'">
                                            {{ payment.type === 'expense' ? '-' : '+' }}{{ formatCurrency(payment.amount, payment.currency) }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            </div>
                                    </div>

                    <!-- 3. HİZMETLER (Hosting, Domain, etc.) -->
                    <div v-if="activeTab === 'services'" class="tab-pane">
                        
                        <!-- Domains Section -->
                        <div class="content-box mb-4">
                            <div class="box-header flex-header">
                                <h3><Web :size="18"/> {{ translate('domaincontrol', 'Domainler') }}</h3>
                                <NcButton size="small" @click="showAddDomainModal">{{ translate('domaincontrol', 'Ekle') }}</NcButton>
                                    </div>
                            <table class="crm-table">
                                <thead>
                                    <tr>
                                        <th>{{ translate('domaincontrol', 'Domain') }}</th>
                                        <th>{{ translate('domaincontrol', 'Kayıtçı') }}</th>
                                        <th>{{ translate('domaincontrol', 'Bitiş') }}</th>
                                        <th>{{ translate('domaincontrol', 'Durum') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-if="clientServices.domains.length === 0">
                                        <td colspan="4" class="text-center empty-table">{{ translate('domaincontrol', 'Domain bulunamadı') }}</td>
                                    </tr>
                                    <tr v-for="dom in clientServices.domains" :key="dom.id" @click="navigateToDomain(dom.id)" class="table-row-clickable">
                                        <td class="font-bold">{{ dom.name }}</td>
                                        <td>{{ dom.registrar }}</td>
                                        <td :class="{'text-error': isUrgent(dom.expiryDate)}">
                                            <div>{{ dom.expiry }}</div>
                                            <div v-if="dom.daysLeft !== null" class="days-left-text" :class="getDaysLeftClass(dom.daysLeft)">
                                                {{ getDaysLeftText(dom.daysLeft) }}
                                    </div>
                                        </td>
                                        <td><span class="status-dot active"></span> Aktif</td>
                                    </tr>
                                </tbody>
                            </table>
                                </div>

                        <!-- Hosting Section -->
                        <div class="content-box">
                            <div class="box-header flex-header">
                                <h3><ServerNetwork :size="18"/> {{ translate('domaincontrol', 'Hosting & Sunucu') }}</h3>
                                <NcButton size="small" @click="showAddHostingModal">{{ translate('domaincontrol', 'Ekle') }}</NcButton>
                            </div>
                            <table class="crm-table">
                                <thead>
                                    <tr>
                                        <th>{{ translate('domaincontrol', 'Hizmet') }}</th>
                                        <th>{{ translate('domaincontrol', 'IP Adresi') }}</th>
                                        <th>{{ translate('domaincontrol', 'Fiyat') }}</th>
                                        <th>{{ translate('domaincontrol', 'Yenileme') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-if="clientServices.hosting.length === 0">
                                        <td colspan="4" class="text-center empty-table">{{ translate('domaincontrol', 'Hosting bulunamadı') }}</td>
                                    </tr>
                                    <tr v-for="host in clientServices.hosting" :key="host.id" @click="editHosting(clientHostings.find(h => h.id === host.id))" class="table-row-clickable">
                                        <td class="font-bold">{{ host.name }} <span class="sub-text">({{ host.type }})</span></td>
                                        <td>{{ host.ip }}</td>
                                        <td>{{ formatCurrency(host.price, host.currency) }} / {{ host.period }}</td>
                                        <td>{{ host.renewal }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- 4. PROJELER -->
                    <div v-if="activeTab === 'projects'" class="tab-pane">
                        <ClientProjects 
                            :projects="clientProjects"
                            @add-project="showAddProjectModal"
                            @navigate="navigateToProject"
                        />
                </div>

                     <!-- 5. GÖREVLER -->
                    <div v-if="activeTab === 'tasks'" class="tab-pane">
                         <div class="pane-actions">
                            <NcButton type="primary" @click="showAddTaskModal">
                                <template #icon><Plus :size="20" /></template>
                                {{ translate('domaincontrol', 'Görev Ekle') }}
                            </NcButton>
                        </div>
                        
                        <!-- Aktif Görevler -->
                        <div v-if="activeClientTasks.length > 0" class="task-section">
                            <h3 class="task-section-title">{{ translate('domaincontrol', 'Aktif Görevler') }}</h3>
                            <div class="task-list-container">
                                <div v-for="task in activeClientTasks" :key="task.id" class="crm-task-item" :class="{ 'has-subtasks': getSubtasksCount(task) > 0 }" @click="selectTask(task)">
                                    <div class="task-checkbox-area" @click.stop>
                                        <div 
                                            class="custom-checkbox" 
                                            :class="{ 
                                                'checked': task.status === 'done',
                                                'checkbox-blocked': getIncompleteSubtasksCount(task) > 0 && task.status !== 'done'
                                            }" 
                                            @click.stop="toggleTaskStatus(task)"
                                            :title="getIncompleteSubtasksCount(task) > 0 ? translate('domaincontrol', 'Alt görevler tamamlanmadan üst görev tamamlanamaz') : ''"
                                        >
                                            <MaterialIcon v-if="getIncompleteSubtasksCount(task) > 0 && task.status !== 'done'" name="lock" :size="12" class="lock-icon" />
                                        </div>
                                    </div>
                                    <div class="task-content" @click.stop="selectTask(task)">
                                        <div class="task-title" :class="{ 'is-completed': task.status === 'done' }">{{ task.title }}</div>
                                        <div class="task-meta">
                                            <span v-if="task.dueDate" :class="{'text-error': isOverdue(task.dueDate) && task.status !== 'done'}">
                                                {{ translate('domaincontrol', 'Son Tarih') }} {{ formatDate(task.dueDate) }}
                                            </span>
                                            <span v-if="getSubtasksCount(task) > 0" class="subtasks-indicator">
                                                <MaterialIcon name="format-list-bulleted" :size="12" />
                                                {{ getSubtasksCount(task) }} {{ translate('domaincontrol', 'alt görev') }}
                                                <span v-if="getIncompleteSubtasksCount(task) > 0" class="incomplete-badge">
                                                    ({{ getIncompleteSubtasksCount(task) }} {{ translate('domaincontrol', 'bekliyor') }})
                                                </span>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="task-priority" :class="task.priority">{{ getPriorityLabel(task.priority) }}</div>
                                    
                                    <!-- Alt Görevler -->
                                    <div v-if="getSubtasksCount(task) > 0" class="subtasks-container">
                                        <div class="subtasks-header">
                                            <MaterialIcon name="format-list-bulleted" :size="14" />
                                            <span class="subtasks-header-text">{{ getSubtasksCount(task) }} {{ translate('domaincontrol', 'alt görev') }}</span>
                                        </div>
                                        <div class="subtasks-list">
                                            <div 
                                                v-for="subtask in getSubtasks(task)" 
                                                :key="subtask.id" 
                                                class="subtask-item"
                                                :class="{ 'is-completed': subtask.status === 'done' }"
                                            >
                                                <div class="subtask-connector"></div>
                                                <div class="subtask-content-wrapper">
                                                    <div class="task-checkbox-area subtask-checkbox" @click.stop>
                                                        <div 
                                                            class="custom-checkbox small" 
                                                            :class="{ 'checked': subtask.status === 'done' }" 
                                                            @click="toggleTaskStatus(subtask)"
                                                        ></div>
                                                    </div>
                                                    <div class="task-content subtask-content" @click.stop="selectTask(subtask)">
                                                        <div class="subtask-header-row">
                                                            <div class="task-title subtask-title" :class="{ 'is-completed': subtask.status === 'done' }">
                                                                {{ subtask.title }}
                                                            </div>
                                                            <div v-if="subtask.priority === 'high'" class="subtask-priority-badge high">
                                                                {{ translate('domaincontrol', 'Yüksek') }}
                                                            </div>
                                                        </div>
                                                        <div class="task-meta subtask-meta">
                                                            <span v-if="subtask.dueDate" class="subtask-date" :class="{'text-error': isOverdue(subtask.dueDate) && subtask.status !== 'done', 'date-overdue': isOverdue(subtask.dueDate) && subtask.status !== 'done'}">
                                                                <MaterialIcon name="calendar" :size="12" />
                                                                {{ formatDate(subtask.dueDate) }}
                                                            </span>
                                                            <span v-if="subtask.description" class="subtask-description">
                                                                {{ subtask.description.substring(0, 60) }}{{ subtask.description.length > 60 ? '...' : '' }}
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Tamamlanmış Görevler -->
                        <div v-if="completedClientTasks.length > 0" class="task-section completed-section">
                            <h3 class="task-section-title">{{ translate('domaincontrol', 'Tamamlanmış Görevler') }}</h3>
                            <div class="task-list-container">
                                <div v-for="task in completedClientTasks" :key="task.id" class="crm-task-item completed-task" @click="selectTask(task)">
                                    <div class="task-checkbox-area" @click.stop>
                                        <div 
                                            class="custom-checkbox checked" 
                                            @click.stop="toggleTaskStatus(task)"
                                        ></div>
                                    </div>
                                    <div class="task-content" @click.stop="selectTask(task)">
                                        <div class="task-title is-completed">{{ task.title }}</div>
                                        <div class="task-meta">
                                            <span v-if="task.dueDate">
                                                {{ translate('domaincontrol', 'Son Tarih') }} {{ formatDate(task.dueDate) }}
                                            </span>
                                            <span v-if="task.completedAt">
                                                • {{ translate('domaincontrol', 'Tamamlandı') }} {{ formatDate(task.completedAt) }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="task-priority" :class="task.priority">{{ getPriorityLabel(task.priority) }}</div>
                                </div>
                            </div>
                        </div>
                        
                        <div v-if="activeClientTasks.length === 0 && completedClientTasks.length === 0" class="empty-box">
                            {{ translate('domaincontrol', 'Görev bulunamadı') }}
                        </div>
                    </div>
                    <!-- 6. NOTLAR -->
                    <div v-if="activeTab === 'notes'" class="tab-pane">
                        <!-- Not Yazma Formu -->
                        <div class="content-box mb-4">
                            <div class="box-header">
                                <h3>{{ translate('domaincontrol', 'Yeni Not Ekle') }}</h3>
                        </div>
                            <div class="box-body">
                                <form @submit.prevent="saveNewNote" class="note-form">
                                    <div class="form-group">
                                        <label for="new-note-title">{{ translate('domaincontrol', 'Başlık') }}</label>
                                        <input 
                                            type="text" 
                                            id="new-note-title" 
                                            v-model="newNoteForm.title" 
                                            class="nc-input" 
                                            :placeholder="translate('domaincontrol', 'Not başlığı (Opsiyonel)')"
                                        />
                            </div>
                                    <div class="form-group">
                                        <label for="new-note-content">{{ translate('domaincontrol', 'Not Metni') }} *</label>
                                        <RichTextEditor
                                            v-model="newNoteForm.content"
                                            :placeholder="translate('domaincontrol', 'Notunuzu buraya yazın...')"
                                        />
                            </div>
                                    <div class="form-actions">
                                        <NcButton type="primary" @click="saveNewNote" :disabled="savingNote || !newNoteForm.content">
                                            <template #icon><Plus :size="20" /></template>
                                            {{ savingNote ? translate('domaincontrol', 'Kaydediliyor...') : translate('domaincontrol', 'Not Ekle') }}
                                        </NcButton>
                                    </div>
                                </form>
                        </div>
                    </div>

                        <!-- Notlar Listesi -->
                        <div class="content-box">
                            <div class="box-header">
                                <h3>{{ translate('domaincontrol', 'Notlar') }}</h3>
                        </div>
                            <div class="box-body">
                                <div v-if="clientNotes.length === 0" class="empty-box full-width">
                                    {{ translate('domaincontrol', 'Henüz not yok.') }}
                            </div>
                                <div v-else class="notes-list">
                                    <div v-for="note in clientNotes" :key="note.id" class="crm-note-card" @click="openNoteModal(note)">
                                        <div class="note-header">
                                            <h4 class="note-title">{{ note.title || translate('domaincontrol', 'Başlıksız Not') }}</h4>
                                            <div class="note-actions">
                                                <button class="icon-button-small" @click.stop="deleteNote(note)" :title="translate('domaincontrol', 'Sil')">
                                                    <Delete :size="16" />
                                                </button>
                            </div>
                        </div>
                                        <div class="note-body" v-html="note.content || ''"></div>
                                        <div class="note-footer">
                                            <span class="note-date">{{ formatDate(note.date || note.createdAt) }}</span>
                    </div>
                        </div>
                            </div>
                            </div>
                        </div>
                    </div>

                    <!-- 7. MÜŞTERİ DOSYALARI -->
                    <div v-if="activeTab === 'files'" class="tab-pane">
                        <div class="pane-actions">
                            <input
                                ref="fileInputRef"
                                type="file"
                                style="display: none"
                                @change="handleFileSelect"
                                multiple
                            />
                            <NcButton type="primary" @click="triggerFileInput" :disabled="uploadingFile">
                                <template #icon>
                                    <Plus :size="20" />
                                </template>
                                {{ uploadingFile ? translate('domaincontrol', 'Yükleniyor...') : translate('domaincontrol', 'Dosya Yükle') }}
                            </NcButton>
                        </div>
                        <div v-if="loading" class="loading-container">
                            <Refresh :size="32" class="spin-animation" />
                        </div>
                        <div v-else-if="clientFiles.length === 0" class="empty-list">
                            <div class="empty-text">{{ translate('domaincontrol', 'Henüz dosya yok') }}</div>
                        </div>
                        <div v-else class="files-list">
                            <div v-for="file in clientFiles" :key="file.id" class="file-item">
                                <div class="file-icon">
                                    <MaterialIcon :name="getFileIcon(file.mimeType)" :size="24" />
                                </div>
                                <div class="file-info">
                                    <div class="file-name">{{ file.name }}</div>
                                    <div class="file-meta">
                                        <span>{{ formatFileSize(file.size) }}</span>
                                        <span>•</span>
                                        <span>{{ formatDate(file.mtime) }}</span>
                                    </div>
                                </div>
                                <div class="file-actions">
                                    <button class="icon-button-small" @click="downloadFile(file)" :title="translate('domaincontrol', 'İndir')">
                                        <MaterialIcon name="download" :size="16" />
                                    </button>
                                    <button class="icon-button-small danger" @click="deleteFile(file)" :title="translate('domaincontrol', 'Sil')">
                                        <Delete :size="16" />
                                    </button>
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
import { NcButton, NcProgressBar } from '@nextcloud/vue'
import api from '../services/api'
import ClientModal from './ClientModal.vue'
import ProjectModal from './ProjectModal.vue'
import TaskModal from './TaskModal.vue'
import InvoiceModal from './InvoiceModal.vue'
import DomainModal from './DomainModal.vue'
import HostingModal from './HostingModal.vue'
import RichTextEditor from './RichTextEditor.vue'
import ClientProjects from './clients/ClientProjects.vue'
import MaterialIcon from './MaterialIcon.vue'
import TransactionModal from './TransactionModal.vue'

// Icons
import AccountGroup from 'vue-material-design-icons/AccountGroup.vue'
import Magnify from 'vue-material-design-icons/Magnify.vue'
import Plus from 'vue-material-design-icons/Plus.vue'
import Refresh from 'vue-material-design-icons/Refresh.vue'
import Phone from 'vue-material-design-icons/Phone.vue'
import Domain from 'vue-material-design-icons/Domain.vue'
import Pencil from 'vue-material-design-icons/Pencil.vue'
import Delete from 'vue-material-design-icons/Delete.vue'
import ArrowLeft from 'vue-material-design-icons/ArrowLeft.vue'
import Email from 'vue-material-design-icons/Email.vue'
import Calendar from 'vue-material-design-icons/Calendar.vue'
import Close from 'vue-material-design-icons/Close.vue'
import MapMarker from 'vue-material-design-icons/MapMarker.vue'
import CashCheck from 'vue-material-design-icons/CashCheck.vue'
import AlertCircleOutline from 'vue-material-design-icons/AlertCircleOutline.vue'
import InvoiceText from 'vue-material-design-icons/InvoiceText.vue'
import BriefcaseOutline from 'vue-material-design-icons/BriefcaseOutline.vue'
import ServerNetwork from 'vue-material-design-icons/ServerNetwork.vue'
import Web from 'vue-material-design-icons/Web.vue'

export default {
    name: 'Clients',
    components: {
        ClientModal, ProjectModal, TaskModal, InvoiceModal, DomainModal, HostingModal, RichTextEditor, 
        ClientProjects, NcButton, NcProgressBar, MaterialIcon, TransactionModal,
        AccountGroup, Magnify, Plus, Refresh, Phone, Domain, Pencil, Delete, 
        ArrowLeft, Email, Calendar, Close, MapMarker, CashCheck, AlertCircleOutline, 
        InvoiceText, BriefcaseOutline, ServerNetwork, Web
    },
    data() {
        return {
            clients: [],
            projects: [],
            tasks: [],
            notes: [],
            clientFiles: [],
            invoices: [],
            transactions: [],
            domains: [],
            hostings: [],
            hostingPackages: [],
            selectedClient: null,
            searchQuery: '',
            loading: false,
            isMobile: window.innerWidth < 768,
            activeTab: 'overview',

            // Modals
            modalOpen: false,
            editingClient: null,
            projectModalOpen: false,
            editingProject: null,
            taskModalOpen: false,
            editingTask: null,
            noteModalOpen: false,
            editingNote: null,
            invoiceModalOpen: false,
            editingInvoice: null,
            domainModalOpen: false,
            editingDomain: null,
            hostingModalOpen: false,
            editingHosting: null,
            transactionModalOpen: false,
            editingTransaction: null,
            transactionPresetType: null,
            transactionCategories: [],
            newNoteForm: {
                title: '',
                content: ''
            },
            savingNote: false,
            defaultCurrency: 'USD',
            currencies: [],
            uploadingFile: false,
            fileInputRef: null,
        }
    },
    computed: {
        filteredClients() {
            if (!this.searchQuery) return this.clients
            const query = this.searchQuery.toLowerCase()
            return this.clients.filter(c => 
                c.name?.toLowerCase().includes(query) || 
                c.email?.toLowerCase().includes(query) ||
                c.company?.toLowerCase().includes(query)
            )
        },
        financials() {
            if (!this.selectedClient) return { totalPaid: 0, totalOverdue: 0, totalUpcoming: 0 }
            
            const clientId = this.selectedClient.id
            const clientPayments = this.clientPayments
            const now = new Date()

            // Toplam Ciro: Tüm ödemeler (payments + income transactions)
            let totalPaid = 0
            clientPayments.forEach(payment => {
                totalPaid += payment.amount || 0
            })

            // Bekleyen ve Gecikmiş Ödemeler: Ham fatura verilerinden hesapla
            let totalOverdue = 0
            let totalUpcoming = 0

            // Ham fatura verilerini kullan (clientInvoices formatlanmış veri döndürüyor)
            const rawInvoices = this.invoices.filter(inv => 
                (inv.clientId == clientId || inv.client_id == clientId)
            )

            rawInvoices.forEach(inv => {
                // API'den gelen balance alanını kullan, yoksa manuel hesapla
                let remainingAmount = 0
                if (inv.balance !== undefined && inv.balance !== null) {
                    remainingAmount = parseFloat(inv.balance) || 0
                } else {
                    const totalAmount = parseFloat(inv.totalAmount || inv.total_amount || 0)
                    const paidAmount = parseFloat(inv.paidAmount || inv.paid_amount || 0)
                    remainingAmount = Math.max(0, totalAmount - paidAmount)
                }
                
                // Eğer tamamen ödendiyse, atla
                if (remainingAmount <= 0) return
                
                const status = (inv.status || 'draft').toLowerCase()
                
                // Sadece ödenmemiş faturaları kontrol et (paid ve cancelled hariç)
                // Status: draft, sent, overdue gibi durumlar ödenmemiş sayılır
                if (status !== 'paid' && status !== 'cancelled') {
                    const dueDateStr = inv.dueDate || inv.due_date
                    
                    if (dueDateStr) {
                        try {
                            const dueDate = new Date(dueDateStr)
                            // Tarih geçerli mi kontrol et
                            if (!isNaN(dueDate.getTime())) {
                                // Sadece tarih kısmını karşılaştır (saat bilgisi olmadan)
                                const dueDateOnly = new Date(dueDate.getFullYear(), dueDate.getMonth(), dueDate.getDate())
                                const nowOnly = new Date(now.getFullYear(), now.getMonth(), now.getDate())
                                
                                if (dueDateOnly < nowOnly) {
                                    // Vade tarihi geçmiş, gecikmiş ödeme
                                    totalOverdue += remainingAmount
                    } else {
                                    // Vade tarihi henüz gelmemiş, bekleyen ödeme
                                    totalUpcoming += remainingAmount
                                }
                            } else {
                                // Geçersiz tarih, bekleyen olarak say
                                totalUpcoming += remainingAmount
                            }
                        } catch (e) {
                            // Tarih parse edilemedi, bekleyen olarak say
                            totalUpcoming += remainingAmount
                        }
                    } else {
                        // Vade tarihi yok, bekleyen olarak say
                        totalUpcoming += remainingAmount
                    }
                }
            })

            // Bekleyen Ödemeler = Tüm ödenmemiş faturaların toplamı (hem gecikmiş hem bekleyen)
            // Ama UI'da ayrı gösterildiği için totalUpcoming'i toplam bekleyen olarak kullanıyoruz
            // Eğer sadece "Bekleyen Ödemeler" kartında toplam gösterilecekse, totalUpcoming + totalOverdue olmalı
            return { totalPaid, totalOverdue, totalUpcoming }
        },
        // Client Specific Getters
        clientProjects() { 
            return this.selectedClient ? this.projects.filter(p => (p.clientId == this.selectedClient.id || p.client_id == this.selectedClient.id)) : [] 
        },
        clientTasks() { 
            return this.selectedClient ? this.tasks.filter(t => (t.clientId == this.selectedClient.id || t.client_id == this.selectedClient.id)) : [] 
        },
        activeClientTasks() {
            if (!this.selectedClient) return []
            const clientId = this.selectedClient.id
            // Sadece üst görevleri al (parentId yok)
            return this.tasks.filter(t => 
                (t.clientId == clientId || t.client_id == clientId) && 
                !t.parentId && 
                t.status !== 'done' && 
                t.status !== 'cancelled'
            )
        },
        completedClientTasks() {
            if (!this.selectedClient) return []
            const clientId = this.selectedClient.id
            // Tamamlanmış üst görevler
            return this.tasks.filter(t => 
                (t.clientId == clientId || t.client_id == clientId) && 
                !t.parentId && 
                (t.status === 'done' || t.status === 'cancelled')
            )
        },
        clientNotes() { 
            return this.selectedClient ? this.notes.filter(n => (n.clientId == this.selectedClient.id || n.client_id == this.selectedClient.id)) : [] 
        },
        clientInvoices() { 
            if (!this.selectedClient) return []
            return this.invoices.filter(inv => (inv.clientId == this.selectedClient.id || inv.client_id == this.selectedClient.id))
                .map(inv => ({
                    id: inv.id,
                    number: inv.invoiceNumber || inv.invoice_number || `INV-${inv.id}`,
                    status: inv.status || 'draft',
                    statusLabel: this.getInvoiceStatusLabel(inv.status),
                    date: this.formatDate(inv.invoiceDate || inv.invoice_date || inv.createdAt),
                    dueDate: this.formatDate(inv.dueDate || inv.due_date),
                    amount: parseFloat(inv.totalAmount || inv.total_amount || 0),
                    currency: inv.currency || this.defaultCurrency
                }))
        },
        clientDomains() { 
            return this.selectedClient ? this.domains.filter(d => (d.clientId == this.selectedClient.id || d.client_id == this.selectedClient.id)) : [] 
        },
        clientHostings() { 
            return this.selectedClient ? this.hostings.filter(h => (h.clientId == this.selectedClient.id || h.client_id == this.selectedClient.id)) : [] 
        },
        clientServices() { 
            return {
                domains: this.clientDomains.map(d => {
                    const expiryDate = d.expirationDate || d.expiration_date
                    const daysLeft = expiryDate ? this.getDaysUntilExpiry(expiryDate) : null
                    return {
                        id: d.id,
                        name: d.domainName || d.domain_name,
                        registrar: d.registrar || '-',
                        expiry: this.formatDate(expiryDate),
                        expiryDate: expiryDate,
                        daysLeft: daysLeft
                    }
                }),
                hosting: this.clientHostings.map(h => ({
                    id: h.id,
                    name: h.provider || h.packageName || '-',
                    type: h.serverType || h.server_type || '-',
                    ip: h.serverIp || h.server_ip || '-',
                    price: parseFloat(h.price || 0),
                    period: h.period || 'Yıl',
                    renewal: this.formatDate(h.expirationDate || h.expiration_date)
                }))
            }
        },
        clientServicesCount() {
            return this.clientDomains.length + this.clientHostings.length
        },
        // Tüm ödemeler ve giderler (income + expense transactions)
        clientPayments() {
            if (!this.selectedClient) return []
            
            const clientId = this.selectedClient.id
            const allPayments = []
            
            // Tüm transactions (income ve expense)
            this.transactions.forEach(transaction => {
                const transClientId = transaction.clientId || transaction.client_id
                if (transClientId == clientId && (transaction.type === 'income' || transaction.type === 'expense')) {
                    const transDate = transaction.date || transaction.transactionDate || transaction.createdAt
                    
                    // Fatura ödemesi mi kontrol et (sadece income için)
                    const isInvoicePayment = transaction.type === 'income' && (transaction.invoiceId || (transaction.notes && transaction.notes.includes('[INVOICE_ID:')))
                    
                    let typeLabel = ''
                    if (transaction.type === 'expense') {
                        typeLabel = this.translate('domaincontrol', 'Gider')
                    } else if (isInvoicePayment) {
                        typeLabel = this.translate('domaincontrol', 'Fatura Ödemesi')
                    } else {
                        typeLabel = this.translate('domaincontrol', 'Gelir')
                    }
                    
                    allPayments.push({
                        id: `transaction-${transaction.id}`,
                        date: transDate,
                        type: transaction.type === 'expense' ? 'expense' : (isInvoicePayment ? 'invoice' : 'income'),
                        typeLabel: typeLabel,
                        description: transaction.description || transaction.category || (transaction.type === 'expense' ? this.translate('domaincontrol', 'Gider işlemi') : this.translate('domaincontrol', 'Gelir işlemi')),
                        method: transaction.paymentMethod || transaction.payment_method || '-',
                        amount: parseFloat(transaction.amount) || 0,
                        currency: transaction.currency || this.defaultCurrency
                    })
                }
            })
            
            // Tarihe göre sırala (en yeni önce)
            return allPayments.sort((a, b) => {
                const dateA = new Date(a.date || 0)
                const dateB = new Date(b.date || 0)
                return dateB - dateA
            })
        },
        // Aylık ödeme verileri (son 12 ay)
        monthlyPaymentData() {
            if (!this.selectedClient) return []
            
            const clientId = this.selectedClient.id
            const now = new Date()
            const months = []
            
            // Son 12 ayı oluştur
            for (let i = 11; i >= 0; i--) {
                const date = new Date(now.getFullYear(), now.getMonth() - i, 1)
                months.push({
                    year: date.getFullYear(),
                    month: date.getMonth(),
                    monthName: date.toLocaleDateString('tr-TR', { month: 'short' }),
                    total: 0
                })
            }
            
            // Transactions verilerini işle (income transactions - fatura ödemeleri dahil)
            this.transactions.forEach(transaction => {
                const transClientId = transaction.clientId || transaction.client_id
                if (transClientId == clientId && transaction.type === 'income' && transaction.amount) {
                    const transDate = transaction.date || transaction.transactionDate || transaction.createdAt
                    if (transDate) {
                        try {
                            const date = new Date(transDate)
                            const monthIndex = months.findIndex(m => 
                                m.year === date.getFullYear() && m.month === date.getMonth()
                            )
                            if (monthIndex !== -1) {
                                months[monthIndex].total += parseFloat(transaction.amount) || 0
                            }
                        } catch (e) {
                            console.warn('Invalid transaction date:', transDate)
                        }
                    }
                }
            })
            
            return months
        },
        clientActivities() {
            if (!this.selectedClient) return []
            
            const clientId = this.selectedClient.id
            const activities = []
            
            // 1. Ödemeler (Income transactions)
            this.transactions.forEach(transaction => {
                const transClientId = transaction.clientId || transaction.client_id
                if (transClientId == clientId && transaction.type === 'income') {
                    const date = transaction.date || transaction.transactionDate || transaction.createdAt
                    const amount = parseFloat(transaction.amount) || 0
                    const currency = transaction.currency || this.defaultCurrency
                    const invoiceId = transaction.invoiceId
                    
                    let title = ''
                    if (invoiceId) {
                        const invoice = this.invoices.find(inv => inv.id == invoiceId)
                        const invoiceNumber = invoice ? (invoice.invoiceNumber || invoice.invoice_number || `#${invoiceId}`) : `#${invoiceId}`
                        title = this.translate('domaincontrol', 'Fatura Ödendi') + ` (${invoiceNumber}) - ${this.formatCurrency(amount, currency)}`
                    } else {
                        title = this.translate('domaincontrol', 'Ödeme Yapıldı') + ` - ${this.formatCurrency(amount, currency)}`
                    }
                    
                    activities.push({
                        id: `payment-${transaction.id}`,
                        date: date,
                        title: title,
                        dotClass: 'success',
                        type: 'payment'
                    })
                }
            })
            
            // 2. Giderler (Expense transactions)
            this.transactions.forEach(transaction => {
                const transClientId = transaction.clientId || transaction.client_id
                if (transClientId == clientId && transaction.type === 'expense') {
                    const date = transaction.date || transaction.transactionDate || transaction.createdAt
                    const amount = parseFloat(transaction.amount) || 0
                    const currency = transaction.currency || this.defaultCurrency
                    const description = transaction.description || transaction.category || this.translate('domaincontrol', 'Gider')
                    
                    activities.push({
                        id: `expense-${transaction.id}`,
                        date: date,
                        title: `${description} - ${this.formatCurrency(amount, currency)}`,
                        dotClass: 'warning',
                        type: 'expense'
                    })
                }
            })
            
            // 3. Projeler
            this.projects.forEach(project => {
                const projClientId = project.clientId || project.client_id
                if (projClientId == clientId) {
                    // Proje oluşturuldu
                    if (project.createdAt) {
                        activities.push({
                            id: `project-created-${project.id}`,
                            date: project.createdAt,
                            title: this.translate('domaincontrol', 'Proje Oluşturuldu') + `: ${project.name || this.translate('domaincontrol', 'İsimsiz Proje')}`,
                            dotClass: 'primary',
                            type: 'project_created'
                        })
                    }
                    
                    // Proje başladı
                    if (project.startDate) {
                        activities.push({
                            id: `project-started-${project.id}`,
                            date: project.startDate,
                            title: this.translate('domaincontrol', 'Proje Başladı') + `: ${project.name || this.translate('domaincontrol', 'İsimsiz Proje')}`,
                            dotClass: 'primary',
                            type: 'project_started'
                        })
                    }
                    
                    // Proje bitti
                    if (project.endDate || (project.status && (project.status === 'completed' || project.status === 'done'))) {
                        const endDate = project.endDate || project.updatedAt
                        activities.push({
                            id: `project-completed-${project.id}`,
                            date: endDate,
                            title: this.translate('domaincontrol', 'Proje Tamamlandı') + `: ${project.name || this.translate('domaincontrol', 'İsimsiz Proje')}`,
                            dotClass: 'success',
                            type: 'project_completed'
                        })
                    }
                }
            })
            
            // 4. Domainler
            this.domains.forEach(domain => {
                const domainClientId = domain.clientId || domain.client_id
                if (domainClientId == clientId) {
                    // Domain eklendi
                    if (domain.createdAt) {
                        activities.push({
                            id: `domain-created-${domain.id}`,
                            date: domain.createdAt,
                            title: this.translate('domaincontrol', 'Domain Eklendi') + `: ${domain.domainName || domain.domain_name || '-'}`,
                            dotClass: 'info',
                            type: 'domain_created'
                        })
                    }
                    
                    // Domain süresi uzatıldı (renewal date güncellendi)
                    if (domain.renewalDate || domain.renewal_date) {
                        const renewalDate = domain.renewalDate || domain.renewal_date
                        activities.push({
                            id: `domain-renewed-${domain.id}`,
                            date: domain.updatedAt || renewalDate,
                            title: this.translate('domaincontrol', 'Domain Süresi Uzatıldı') + `: ${domain.domainName || domain.domain_name || '-'}`,
                            dotClass: 'info',
                            type: 'domain_renewed'
                        })
                    }
                }
            })
            
            // 5. Hostingler
            this.hostings.forEach(hosting => {
                const hostingClientId = hosting.clientId || hosting.client_id
                if (hostingClientId == clientId && hosting.createdAt) {
                    activities.push({
                        id: `hosting-created-${hosting.id}`,
                        date: hosting.createdAt,
                        title: this.translate('domaincontrol', 'Hosting Eklendi') + `: ${hosting.name || hosting.provider || '-'}`,
                        dotClass: 'info',
                        type: 'hosting_created'
                    })
                }
            })
            
            // 6. Faturalar
            this.invoices.forEach(invoice => {
                const invClientId = invoice.clientId || invoice.client_id
                if (invClientId == clientId) {
                    // Fatura oluşturuldu
                    if (invoice.createdAt || invoice.invoiceDate || invoice.invoice_date) {
                        const invoiceDate = invoice.createdAt || invoice.invoiceDate || invoice.invoice_date
                        const invoiceNumber = invoice.invoiceNumber || invoice.invoice_number || `#${invoice.id}`
                        const amount = parseFloat(invoice.totalAmount || invoice.total_amount || 0)
                        const currency = invoice.currency || this.defaultCurrency
                        
                        activities.push({
                            id: `invoice-created-${invoice.id}`,
                            date: invoiceDate,
                            title: this.translate('domaincontrol', 'Fatura Oluşturuldu') + ` (${invoiceNumber}) - ${this.formatCurrency(amount, currency)}`,
                            dotClass: 'primary',
                            type: 'invoice_created'
                        })
                    }
                }
            })
            
            // Tarihe göre sırala (en yeni önce) ve son 20 aktiviteyi al
            return activities
                .sort((a, b) => {
                    const dateA = new Date(a.date || 0)
                    const dateB = new Date(b.date || 0)
                    return dateB - dateA
                })
                .slice(0, 20)
                .map(activity => {
                    const date = new Date(activity.date)
                    const now = new Date()
                    const diffMs = now - date
                    const diffDays = Math.floor(diffMs / (1000 * 60 * 60 * 24))
                    const diffHours = Math.floor(diffMs / (1000 * 60 * 60))
                    const diffMinutes = Math.floor(diffMs / (1000 * 60))
                    
                    let dateText = ''
                    if (diffMinutes < 60) {
                        dateText = diffMinutes <= 1 ? this.translate('domaincontrol', 'Az önce') : `${diffMinutes} ${this.translate('domaincontrol', 'dakika önce')}`
                    } else if (diffHours < 24) {
                        dateText = diffHours === 1 ? this.translate('domaincontrol', '1 saat önce') : `${diffHours} ${this.translate('domaincontrol', 'saat önce')}`
                    } else if (diffDays === 0) {
                        dateText = this.translate('domaincontrol', 'Bugün') + ', ' + date.toLocaleTimeString('tr-TR', { hour: '2-digit', minute: '2-digit' })
                    } else if (diffDays === 1) {
                        dateText = this.translate('domaincontrol', 'Dün') + ', ' + date.toLocaleTimeString('tr-TR', { hour: '2-digit', minute: '2-digit' })
                    } else if (diffDays < 7) {
                        dateText = `${diffDays} ${this.translate('domaincontrol', 'gün önce')}`
                    } else if (diffDays < 30) {
                        const weeks = Math.floor(diffDays / 7)
                        dateText = weeks === 1 ? this.translate('domaincontrol', '1 hafta önce') : `${weeks} ${this.translate('domaincontrol', 'hafta önce')}`
                    } else {
                        dateText = this.formatDate(activity.date)
                    }
                    
                    return {
                        ...activity,
                        dateText: dateText
                    }
                })
        },
        // Grafik için SVG path oluştur
        chartPath() {
            const data = this.monthlyPaymentData
            if (!data || data.length === 0) return 'M 20,130'
            
            const maxValue = Math.max(...data.map(d => d.total), 1)
            const width = 500
            const height = 150
            const padding = 20
            const chartWidth = width - (padding * 2)
            const chartHeight = 90
            const stepX = data.length > 1 ? chartWidth / (data.length - 1) : 0
            const baseY = height - padding
            
            let path = `M ${padding},${baseY} `
            
            data.forEach((item, index) => {
                const x = padding + (index * stepX)
                const y = item.total > 0 ? baseY - ((item.total / maxValue) * chartHeight) : baseY
                path += `L ${x},${y} `
            })
            
            return path
        },
        // Grafik için area fill path
        chartAreaPath() {
            const data = this.monthlyPaymentData
            if (!data || data.length === 0) return ''
            
            const maxValue = Math.max(...data.map(d => d.total), 1)
            const width = 500
            const height = 150
            const padding = 20
            const chartWidth = width - (padding * 2)
            const chartHeight = 90
            const stepX = data.length > 1 ? chartWidth / (data.length - 1) : 0
            const baseY = height - padding
            
            let path = `M ${padding},${baseY} `
            
            data.forEach((item, index) => {
                const x = padding + (index * stepX)
                const y = item.total > 0 ? baseY - ((item.total / maxValue) * chartHeight) : baseY
                path += `L ${x},${y} `
            })
            
            path += `L ${padding + (chartWidth)},${baseY} Z`
            
            return path
        },
        // Maksimum değer (Y ekseni için)
        chartMaxValue() {
            const data = this.monthlyPaymentData
            if (!data || data.length === 0) return 0
            const max = Math.max(...data.map(d => d.total), 0)
            return max > 0 ? max : 1
        }
    },
    created() {
        window.addEventListener('resize', this.handleResize)
    },
    destroyed() {
        window.removeEventListener('resize', this.handleResize)
    },
    mounted() {
        this.loadSettings()
        this.loadClients()
        this.loadRelatedData()
    },
    methods: {
        handleResize() { this.isMobile = window.innerWidth < 768 },
        translate(app, text) { return (window.t && window.t(app, text)) || text },
        async loadClients() {
            this.loading = true
            try {
                const res = await api.clients.getAll()
                this.clients = res.data || []
            } catch (e) { console.error(e) }
                this.loading = false
        },
        async loadRelatedData() {
            try {
                const [proj, tsk, inv, trans, dom, host, packages, categories] = await Promise.all([
                    api.projects.getAll().catch(()=>({data:[]})),
                    api.tasks.getAll().catch(()=>({data:[]})),
                    api.invoices.getAll().catch(()=>({data:[]})),
                    api.transactions.getAll().catch(()=>({data:[]})),
                    api.domains.getAll().catch(()=>({data:[]})),
                    api.hostings.getAll().catch(()=>({data:[]})),
                    api.hostingPackages.getAll().catch(()=>({data:[]})),
                    api.transactionCategories.getAll().catch(()=>({data:[]}))
                ])
                this.projects = proj.data || []
                this.tasks = tsk.data || []
                this.invoices = inv.data || []
                this.transactions = trans.data || []
                this.domains = dom.data || []
                this.hostings = host.data || []
                this.hostingPackages = packages.data || []
                this.transactionCategories = categories.data || []
            } catch (e) { console.error(e) }
        },
        async loadSettings() {
            try {
                const response = await api.settings.get()
                const settings = response.data || {}
                
                this.defaultCurrency = settings.default_currency || 'USD'
                
                // Load currencies to get symbol
                if (settings.currencies) {
                    try {
                        this.currencies = JSON.parse(settings.currencies)
                } catch (e) {
                        this.currencies = []
                    }
                }
            } catch (error) {
                console.error('Error loading settings:', error)
                this.defaultCurrency = 'USD'
            }
        },
        getCurrencySymbol(currencyCode) {
            if (!currencyCode) currencyCode = this.defaultCurrency || 'USD'
            
            // First try to find in loaded currencies
            if (this.currencies && this.currencies.length > 0) {
                const currency = this.currencies.find(c => c.code === currencyCode)
                if (currency && currency.symbol) {
                    return currency.symbol
                }
            }
            
            // Fallback to default symbols if not found in settings
            const defaultSymbols = {
                'USD': '$',
                'EUR': '€',
                'TRY': '₺',
                'AZN': '₼',
                'GBP': '£',
                'RUB': '₽',
            }
            return defaultSymbols[currencyCode] || currencyCode
        },
        async loadClientNotes(id) {
            try {
                const res = await api.clients.byClient.notes.getAll(id)
                this.notes = res.data || []
            } catch (e) { this.notes = [] }
        },
        async loadClientFiles(id) {
            try {
                const res = await api.clients.byClient.files.getAll(id)
                this.clientFiles = res.data || []
            } catch (e) { 
                console.error('Error loading client files:', e)
                this.clientFiles = [] 
            }
        },
        triggerFileInput() {
            this.$refs.fileInputRef?.click()
        },
        async handleFileSelect(event) {
            const files = event.target.files
            if (!files || files.length === 0) return
            
            if (!this.selectedClient) return
            
            this.uploadingFile = true
            try {
                for (let file of files) {
                    await api.clients.byClient.files.upload(this.selectedClient.id, file)
                }
                await this.loadClientFiles(this.selectedClient.id)
                // Reset file input
                if (this.$refs.fileInputRef) {
                    this.$refs.fileInputRef.value = ''
                }
            } catch (error) {
                console.error('Error uploading file:', error)
                alert(this.translate('domaincontrol', 'Dosya yükleme hatası: ') + (error.response?.data?.error || error.message))
            } finally {
                this.uploadingFile = false
            }
        },
        async downloadFile(file) {
            try {
                const response = await api.clients.byClient.files.download(this.selectedClient.id, file.name)
                const url = window.URL.createObjectURL(new Blob([response.data]))
                const link = document.createElement('a')
                link.href = url
                link.setAttribute('download', file.name)
                document.body.appendChild(link)
                link.click()
                link.remove()
                window.URL.revokeObjectURL(url)
            } catch (error) {
                console.error('Error downloading file:', error)
                alert(this.translate('domaincontrol', 'Dosya indirme hatası'))
            }
        },
        async deleteFile(file) {
            if (!confirm(this.translate('domaincontrol', 'Bu dosyayı silmek istediğinize emin misiniz?'))) {
                return
            }
            try {
                await api.clients.byClient.files.delete(this.selectedClient.id, file.name)
                await this.loadClientFiles(this.selectedClient.id)
            } catch (error) {
                console.error('Error deleting file:', error)
                alert(this.translate('domaincontrol', 'Dosya silme hatası'))
            }
        },
        getFileIcon(mimeType) {
            if (!mimeType) return 'file-document-outline'
            if (mimeType.startsWith('image/')) return 'image'
            if (mimeType.startsWith('video/')) return 'video'
            if (mimeType.startsWith('audio/')) return 'music'
            if (mimeType.includes('pdf')) return 'file-pdf-box'
            if (mimeType.includes('word')) return 'file-word-box'
            if (mimeType.includes('excel') || mimeType.includes('spreadsheet')) return 'file-excel-box'
            if (mimeType.includes('zip') || mimeType.includes('archive')) return 'folder-zip'
            return 'file-document-outline'
        },
        formatFileSize(bytes) {
            if (!bytes) return '0 B'
            const k = 1024
            const sizes = ['B', 'KB', 'MB', 'GB']
            const i = Math.floor(Math.log(bytes) / Math.log(k))
            return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i]
        },
        async selectClient(client) {
            this.selectedClient = client
            this.activeTab = 'overview'
            if (client) {
                await this.loadClientNotes(client.id)
                await this.loadClientFiles(client.id)
                // Reload related data to ensure fresh data
                await this.loadRelatedData()
            }
        },
        backToList() { this.selectedClient = null },
        
        // Modals
        showAddModal() { this.editingClient = null; this.modalOpen = true },
        editClient(id) { 
            const c = this.clients.find(x => x.id === id)
            if (c) { this.editingClient = c; this.modalOpen = true }
        },
        closeModal() { this.modalOpen = false },
        async handleClientSaved() { await this.loadClients(); this.closeModal() },
        
        showAddProjectModal() { this.editingProject = null; this.projectModalOpen = true },
        closeProjectModal() { this.projectModalOpen = false },
        async handleProjectSaved() { await this.loadRelatedData(); this.closeProjectModal() },

        showAddTaskModal() { this.editingTask = null; this.taskModalOpen = true },
        closeTaskModal() { this.taskModalOpen = false },
        async handleTaskSaved() { await this.loadRelatedData(); this.closeTaskModal() },

        openNoteModal(note) { 
            this.editingNote = note || null
            this.noteModalOpen = true 
        },
        closeNoteModal() { 
            this.noteModalOpen = false
            this.editingNote = null
        },
        async handleNoteSaved() {
            if (this.selectedClient) {
                await this.loadClientNotes(this.selectedClient.id)
            }
        },
        async saveNewNote() {
            if (!this.selectedClient || !this.newNoteForm.content) return
            
            this.savingNote = true
            try {
                await api.clients.byClient.notes.create(this.selectedClient.id, {
                    title: this.newNoteForm.title || null,
                    content: this.newNoteForm.content
                })
                this.newNoteForm = { title: '', content: '' }
                await this.loadClientNotes(this.selectedClient.id)
            } catch (e) {
                console.error('Error saving note:', e)
                alert(this.translate('domaincontrol', 'Not kaydedilemedi'))
            } finally {
                this.savingNote = false
            }
        },
        async deleteNote(note) {
            if (!this.selectedClient || !note || !note.id) return
            if (!confirm(this.translate('domaincontrol', 'Bu notu silmek istediğinizden emin misiniz?'))) return
            
            try {
                await api.clients.byClient.notes.delete(this.selectedClient.id, note.id)
                await this.loadClientNotes(this.selectedClient.id)
            } catch (e) {
                console.error('Error deleting note:', e)
                alert(this.translate('domaincontrol', 'Not silinemedi'))
            }
        },
        
        // Invoice Modals
        showAddInvoiceModal() {
            this.editingInvoice = null
            this.invoiceModalOpen = true
        },
        editInvoice(invoice) {
            this.editingInvoice = invoice
            this.invoiceModalOpen = true
        },
        closeInvoiceModal() {
            this.invoiceModalOpen = false
            this.editingInvoice = null
        },
        async handleInvoiceSaved() {
            await this.loadRelatedData()
            this.closeInvoiceModal()
        },
        
        // Domain Modals
        showAddDomainModal() {
            this.editingDomain = null
            this.domainModalOpen = true
        },
        editDomain(domain) {
            this.editingDomain = domain
            this.domainModalOpen = true
        },
        closeDomainModal() {
            this.domainModalOpen = false
            this.editingDomain = null
        },
        async handleDomainSaved() {
            await this.loadRelatedData()
            this.closeDomainModal()
        },
        
        // Hosting Modals
        showAddHostingModal() {
            this.editingHosting = null
            this.hostingModalOpen = true
        },
        editHosting(hosting) {
            this.editingHosting = hosting
            this.hostingModalOpen = true
        },
        closeHostingModal() {
            this.hostingModalOpen = false
            this.editingHosting = null
        },
        async handleHostingSaved() {
            await this.loadRelatedData()
            this.closeHostingModal()
        },
        
        // Transaction Modals
        showAddPaymentModal() {
            this.editingTransaction = null
            this.transactionPresetType = 'income'
            this.transactionModalOpen = true
        },
        showAddExpenseModal() {
            this.editingTransaction = null
            this.transactionPresetType = 'expense'
            this.transactionModalOpen = true
        },
        closeTransactionModal() {
            this.transactionModalOpen = false
            this.editingTransaction = null
            this.transactionPresetType = null
        },
        async handleTransactionSaved() {
            await this.loadRelatedData()
            this.closeTransactionModal()
        },
        
        // Task Helpers
        getSubtasksCount(task) {
            if (!task || !task.id) return 0
            return this.tasks.filter(t => (t.parentId == task.id || t.parent_id == task.id)).length
        },
        getSubtasks(task) {
            if (!task || !task.id) return []
            return this.tasks.filter(t => (t.parentId == task.id || t.parent_id == task.id))
                .sort((a, b) => {
                    // Tamamlanmamış önce
                    if (a.status === 'done' && b.status !== 'done') return 1
                    if (a.status !== 'done' && b.status === 'done') return -1
                    return 0
                })
        },
        getIncompleteSubtasksCount(task) {
            if (!task || !task.id) return 0
            return this.tasks.filter(t => 
                (t.parentId == task.id || t.parent_id == task.id) && 
                t.status !== 'done' && 
                t.status !== 'cancelled'
            ).length
        },
        async toggleTaskStatus(task) {
            const incompleteSubtasks = this.getIncompleteSubtasksCount(task)
            if (task.status !== 'done' && incompleteSubtasks > 0) {
                alert(this.translate('domaincontrol', `Alt görevler tamamlanmadan üst görev tamamlanamaz. ${incompleteSubtasks} alt görev bekliyor.`))
                return
            }
            
            const oldStatus = task.status
            const newStatus = oldStatus === 'done' ? 'todo' : 'done'
            task.status = newStatus
            
            try {
                const response = await api.tasks.toggleStatus(task.id)
                Object.assign(task, response.data)
                await this.loadRelatedData()
            } catch (error) {
                task.status = oldStatus
                console.error('Error toggling task status:', error)
                alert(this.translate('domaincontrol', 'Görev durumu güncellenemedi'))
            }
        },
        selectTask(task) {
            if (!task || !task.id) return
            // Navigate to Tasks tab and open task detail
            if (typeof window.DomainControl !== 'undefined' && window.DomainControl.selectTask) {
                window.DomainControl.selectTask(task.id)
            }
        },
        editTask(task) {
            this.editingTask = task
            this.taskModalOpen = true
        },
        getProjectName(projectId) {
            if (!projectId) return null
            const project = this.projects.find(p => (p.id == projectId || p.project_id == projectId))
            return project ? project.name : null
        },
        getTaskStatusText(status) {
            const labels = {
                'todo': this.translate('domaincontrol', 'Yapılacak'),
                'in_progress': this.translate('domaincontrol', 'Devam Ediyor'),
                'done': this.translate('domaincontrol', 'Tamamlandı'),
                'cancelled': this.translate('domaincontrol', 'İptal Edildi')
            }
            return labels[status] || status
        },
        getTaskStatusClass(status) {
            const classes = {
                'todo': 'status-todo',
                'in_progress': 'status-in-progress',
                'done': 'status-done',
                'cancelled': 'status-cancelled'
            }
            return classes[status] || ''
        },
        getPriorityLabel(priority) {
            const labels = {
                'low': this.translate('domaincontrol', 'Düşük'),
                'medium': this.translate('domaincontrol', 'Orta'),
                'high': this.translate('domaincontrol', 'Yüksek')
            }
            return labels[priority] || priority
        },
        isOverdue(date) {
            if (!date) return false
            const dueDate = new Date(date)
            const today = new Date()
            today.setHours(0, 0, 0, 0)
            dueDate.setHours(0, 0, 0, 0)
            return dueDate < today
        },
        
        // Helpers
        getInvoiceStatusLabel(status) {
            const labels = {
                'paid': this.translate('domaincontrol', 'Ödendi'),
                'unpaid': this.translate('domaincontrol', 'Ödenmedi'),
                'partial': this.translate('domaincontrol', 'Kısmi'),
                'overdue': this.translate('domaincontrol', 'Vadesi Geçti'),
                'draft': this.translate('domaincontrol', 'Taslak'),
                'sent': this.translate('domaincontrol', 'Gönderildi')
            }
            return labels[status] || status
        },

        confirmDelete(client) {
            if(confirm(this.translate('domaincontrol', 'Delete ' + client.name + '?'))) {
                this.deleteClient(client.id)
            }
        },
        async deleteClient(id) {
                await api.clients.delete(id)
                this.clients = this.clients.filter(c => c.id !== id)
                    this.selectedClient = null
        },

        // Helpers
        getAvatarColor(name) {
            if (!name) return '#ccc'
            let hash = 0
            for (let i = 0; i < name.length; i++) hash = name.charCodeAt(i) + ((hash << 5) - hash)
            return `hsl(${hash % 360}, 60%, 45%)`
        },
        getInitials(name) { return name ? name.substring(0,2).toUpperCase() : '?' },
        formatCurrency(val, currencyCode = null) {
            const currency = currencyCode || this.defaultCurrency || 'USD'
            const symbol = this.getCurrencySymbol(currency)
            
            if (symbol) {
                // Use symbol instead of currency name
                const valNum = parseFloat(val) || 0
                if (isNaN(valNum)) return '-'
                return new Intl.NumberFormat('tr-TR', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2,
                }).format(valNum) + ' ' + symbol
            }
            
            // Fallback to standard currency format
            return new Intl.NumberFormat('tr-TR', { 
                style: 'currency', 
                currency: currency 
            }).format(val || 0)
        },
        formatCurrencyShort(val, currencyCode = null) {
            const currency = currencyCode || this.defaultCurrency || 'USD'
            const symbol = this.getCurrencySymbol(currency)
            
            if (!val || val === 0) return `${symbol}0`
            const valNum = parseFloat(val) || 0
            if (valNum >= 1000000) return `${symbol}${(valNum / 1000000).toFixed(1)}M`
            if (valNum >= 1000) return `${symbol}${(valNum / 1000).toFixed(1)}K`
            return `${symbol}${Math.round(valNum)}`
        },
        formatDate(d) { 
            if (!d) return '-'
            // Handle timestamp (seconds or milliseconds)
            const date = typeof d === 'number' ? new Date(d * 1000) : new Date(d)
            return date.toLocaleDateString('tr-TR')
        },
        stripHtml(html) {
            const tmp = document.createElement("DIV");
            tmp.innerHTML = html;
            return tmp.textContent || tmp.innerText || "";
        },
        isOverdue(d) { 
            if (!d) return false
            return new Date(d) < new Date() 
        },
        isUrgent(d) { 
            if (!d) return false
            const daysLeft = Math.ceil((new Date(d) - new Date()) / (1000 * 60 * 60 * 24))
            return daysLeft <= 30 && daysLeft > 0
        },
        
        navigateToProject(id) { 
            if (typeof window.DomainControl !== 'undefined' && window.DomainControl.switchTab) {
                this.backToList()
                window.DomainControl.switchTab('projects')
                setTimeout(() => {
                    if (window.DomainControl.selectProject) {
                        window.DomainControl.selectProject(id)
                    }
                }, 100)
            }
        },
        navigateToDomain(id) {
            if (typeof window.DomainControl !== 'undefined' && window.DomainControl.switchTab) {
                this.backToList()
                window.DomainControl.switchTab('domains')
                setTimeout(() => {
                    if (window.DomainControl.selectDomain) {
                        window.DomainControl.selectDomain(id)
                    }
                }, 100)
            }
        },
        navigateToInvoice(id) {
            if (typeof window.DomainControl !== 'undefined' && window.DomainControl.switchTab) {
                this.backToList()
                window.DomainControl.switchTab('invoices')
                setTimeout(() => {
                    if (window.DomainControl.selectInvoice) {
                        window.DomainControl.selectInvoice(id)
                    }
                }, 100)
            }
        },
        getDaysUntilExpiry(expiryDate) {
            if (!expiryDate) return null
            try {
                const expiry = new Date(expiryDate)
                const now = new Date()
                const diffTime = expiry - now
                const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24))
                return diffDays
            } catch (e) {
                return null
            }
        },
        getDaysLeftText(daysLeft) {
            if (daysLeft === null) return ''
            if (daysLeft < 0) {
                return `${Math.abs(daysLeft)} ${this.translate('domaincontrol', 'gün önce bitti')}`
            } else if (daysLeft === 0) {
                return this.translate('domaincontrol', 'Bugün bitiyor')
            } else if (daysLeft === 1) {
                return this.translate('domaincontrol', '1 gün kaldı')
            } else {
                return `${daysLeft} ${this.translate('domaincontrol', 'gün kaldı')}`
            }
        },
        getDaysLeftClass(daysLeft) {
            if (daysLeft === null) return ''
            if (daysLeft < 0) return 'days-left-expired'
            if (daysLeft <= 7) return 'days-left-urgent'
            if (daysLeft <= 30) return 'days-left-warning'
            return 'days-left-normal'
        }
    }
}
</script>

<style scoped>
/* ==========================================
   LAYOUT STRUCTURE
   ========================================== */
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
    margin-left: 30px;
}
.search-icon {
    position: absolute;
    left: 55px;
    top: 50%;
    transform: translateY(-50%);
    opacity: 0.5;
    pointer-events: none;
    color: var(--color-text-maxcontrast);
}
.search-input {
    width: 100%;
    padding: 8px 10px 8px 34px;
    border-radius: 8px;
    border: 1px solid transparent;
    background-color: var(--color-background-dark);
    color: var(--color-main-text);
    box-sizing: border-box;
    transition: all 0.2s ease;
}
.search-input:focus {
    background-color: var(--color-main-background);
    border-color: var(--color-primary-element);
    outline: none;
    box-shadow: 0 0 0 2px var(--color-primary-element-light);
}


.app-content-list-wrapper {
    flex: 1;
    overflow-y: auto;
}
.app-navigation-list { list-style: none; padding: 0; margin: 0; }

.app-navigation-entry {
    display: flex;
    align-items: center;
    padding: 12px 15px;
    cursor: pointer;
    border-bottom: 1px solid transparent;
    transition: background-color 0.15s ease;
}
.app-navigation-entry:hover { background-color: var(--color-background-hover); }
.app-navigation-entry.active { 
    background-color: var(--color-primary-element-light); 
    border-left: 3px solid var(--color-primary-element); 
}

.avatar-circle {
    width: 36px; height: 36px;
    border-radius: 50%;
    color: white;
    display: flex; align-items: center; justify-content: center;
    font-size: 14px; font-weight: 600;
}
.app-navigation-entry-content { margin-left: 12px; flex: 1; min-width: 0; }
.app-navigation-entry-name { font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; color: var(--color-main-text); }
.app-navigation-entry-details { font-size: 12px; color: var(--color-text-maxcontrast); opacity: 0.7; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

/* ==========================================
   RIGHT PANE: CRM DETAIL (Rise CRM Style)
   ========================================== */
.app-content-details {
    flex: 1;
    background-color: var(--color-background-hover);
    display: flex; flex-direction: column; min-width: 0;
}

.empty-content {
    flex: 1; display: flex; flex-direction: column;
    align-items: center; justify-content: center;
    color: var(--color-text-maxcontrast); opacity: 0.6;
}

.crm-detail-container { display: flex; flex-direction: column; height: 100%; }

/* HEADER */
.crm-header {
    background-color: var(--color-main-background);
    padding: 25px 25px 0 25px;
    border-bottom: 1px solid var(--color-border);
    flex-shrink: 0;
}

.crm-header-top { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; }

.crm-profile-info { display: flex; align-items: center; gap: 20px; }
.avatar-xl {
    width: 72px; height: 72px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    color: white; font-size: 28px; font-weight: bold;
    box-shadow: 0 4px 10px rgba(0,0,0,0.1);
}
.crm-profile-text { display: flex; flex-direction: column; }
.crm-client-name { margin: 0; font-size: 24px; font-weight: bold; line-height: 1.2; color: var(--color-main-text); }
.crm-client-meta {
    display: flex; align-items: center; gap: 16px;
    font-size: 14px; color: var(--color-text-maxcontrast); margin-top: 8px;
    flex-wrap: wrap;
}
.meta-item {
    display: flex; align-items: center; gap: 6px;
    color: var(--color-text-maxcontrast);
}
.meta-item svg {
    flex-shrink: 0;
    opacity: 0.7;
}
.meta-item span {
    white-space: nowrap;
}
.meta-dot { font-size: 8px; opacity: 0.5; margin: 0 5px; }

.crm-header-actions { display: flex; gap: 10px; align-items: center; }

/* TABS */
.crm-tabs-scroll { overflow-x: auto; }
.crm-tabs { display: flex; gap: 30px; }
.crm-tab {
    background: none; border: none;
    padding: 12px 0;
    font-size: 14px; font-weight: 600;
    color: var(--color-text-maxcontrast);
    cursor: pointer;
    border-bottom: 3px solid transparent;
    transition: all 0.2s; white-space: nowrap;
}
.crm-tab:hover { color: var(--color-main-text); }
.crm-tab.active { color: var(--color-primary-element); }
.tab-badge {
    background-color: var(--color-background-dark);
    padding: 1px 6px; border-radius: 10px;
    font-size: 10px; margin-left: 5px; vertical-align: middle;
    color: var(--color-main-text);
}

/* CONTENT AREA */
.crm-content-scroll { flex: 1; overflow-y: auto; padding: 25px; }

/* STATS CARDS */
.stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; margin-bottom: 25px; }
.stat-card {
    background: var(--color-main-background);
    padding: 20px; border-radius: var(--border-radius-large);
    box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    display: flex; align-items: center; justify-content: space-between;
    border: 1px solid var(--color-border);
}
.stat-content { display: flex; flex-direction: column; }
.stat-label { font-size: 13px; color: var(--color-text-maxcontrast); margin-bottom: 5px; }
.stat-value { font-size: 22px; font-weight: bold; color: var(--color-main-text); }
.stat-icon {
    width: 48px; height: 48px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    color: var(--color-primary-element-text);
}
.success-bg { background-color: var(--color-element-success); }
.error-bg { background-color: var(--color-element-error); }
.info-bg { background-color: var(--color-primary-element); }
.warning-bg { background-color: #f0ad4e; color: white; }
.success-text { color: var(--color-element-success); }
.error-text { color: var(--color-element-error); }

/* DASHBOARD COLUMNS */
.dashboard-columns { display: grid; grid-template-columns: 2fr 1fr; gap: 25px; }
@media (max-width: 1100px) { .dashboard-columns { grid-template-columns: 1fr; } }

.content-box {
    background: var(--color-main-background);
    border-radius: var(--border-radius-large);
    border: 1px solid var(--color-border);
    box-shadow: 0 1px 2px rgba(0,0,0,0.05);
    overflow: hidden;
}
.box-header {
    padding: 15px 20px; border-bottom: 1px solid var(--color-border);
    background-color: var(--color-background-dark);
}
.flex-header { display: flex; justify-content: space-between; align-items: center; }
.box-header h3 { margin: 0; font-size: 15px; font-weight: 600; color: var(--color-main-text); display: flex; align-items: center; gap: 8px; }
.box-body { padding: 20px; }
.box-body.no-padding { padding: 0; }
.box-body.grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
.full-w { grid-column: 1 / -1; }
.mb-4 { margin-bottom: 20px; }
.mt-4 { margin-top: 20px; }

/* Info Groups */
.info-group { display: flex; flex-direction: column; }
.info-label { font-size: 11px; text-transform: uppercase; color: var(--color-text-maxcontrast); margin-bottom: 4px; }
.info-val { 
    font-size: 14px; font-weight: 500;
    display: flex; align-items: center; gap: 6px;
    color: var(--color-main-text);
}
.info-val svg { flex-shrink: 0; opacity: 0.7; }
.info-link {
    display: inline-flex; align-items: center; gap: 6px;
    color: var(--color-primary-element);
    text-decoration: none;
    transition: opacity 0.15s;
}
.info-link:hover { opacity: 0.8; }
.info-link svg { flex-shrink: 0; }
.info-val a { color: var(--color-primary-element); text-decoration: none; }

/* Chart */
.chart-container { position: relative; padding: 20px; min-height: 200px; }
.crm-chart { width: 100%; height: 150px; overflow: visible; }
.chart-labels { 
    display: flex; 
    justify-content: space-between; 
    font-size: 10px; 
    color: var(--color-text-maxcontrast);
    margin-top: 10px; 
    padding: 0 20px; 
}
.chart-empty {
    display: flex;
    align-items: center;
    justify-content: center;
    height: 150px;
    color: var(--color-text-maxcontrast);
    font-style: italic;
}

/* Timeline */
.timeline { position: relative; padding: 20px; }
.timeline::before {
    content: ''; position: absolute; left: 29px; top: 20px; bottom: 20px;
    width: 2px; background-color: var(--color-border);
}
.timeline-item { display: flex; gap: 15px; margin-bottom: 20px; position: relative; }
.timeline-item:last-child { margin-bottom: 0; }
.timeline-dot {
    width: 12px; height: 12px; border-radius: 50%  !important; border: 2px solid var(--color-main-background);
    background-color: rgb(245, 78, 76) !important; z-index: 2; margin-top: 4px; flex-shrink: 0; margin-left: 4px;
}
.timeline-dot.success { background-color: var(--color-element-success) !important; }
.timeline-dot.primary { background-color: var(--color-primary-element) !important; }
.timeline-dot.warning { background-color: #f0ad4e }
.timeline-dot.info { background-color: #5bc0de !important; }
.timeline-content { display: flex; flex-direction: column; }
.timeline-title { font-size: 13px; font-weight: 600; }
.timeline-date { font-size: 11px; color: var(--color-text-maxcontrast); }

.empty-timeline {
    padding: 40px 20px;
    text-align: center;
    color: var(--color-text-maxcontrast);
    font-style: italic;
}

.empty-timeline {
    padding: 40px 20px;
    text-align: center;
    color: var(--color-text-maxcontrast);
    font-style: italic;
}

/* TABLES */
.crm-table { width: 100%; border-collapse: collapse; }
.crm-table th { text-align: left; padding: 12px 15px; font-size: 12px; font-weight: 600; color: var(--color-text-maxcontrast); background: var(--color-background-dark); border-bottom: 1px solid var(--color-border); }
.crm-table td { padding: 12px 15px; border-bottom: 1px solid var(--color-border); font-size: 13px; color: var(--color-main-text); }
.crm-table tr:last-child td { border-bottom: none; }
.table-row-clickable { cursor: pointer; transition: background-color 0.15s ease; }
.table-row-clickable:hover { background-color: var(--color-background-hover); }
.empty-table { 
    padding: 40px 20px !important; 
    text-align: center; 
    color: var(--color-text-maxcontrast);
    font-style: italic;
}
.text-right { text-align: right; }
.text-center { text-align: center; }
.font-bold { font-weight: 600; }
.sub-text { opacity: 0.6; font-weight: normal; font-size: 12px; }
.status-pill { padding: 4px 10px; border-radius: 12px; font-size: 11px; font-weight: 600; text-transform: uppercase; }
.status-pill.paid { background-color: #dff0d8; color: #3c763d; }
.status-pill.unpaid { background-color: #f2dede; color: #a94442; }
.status-pill.overdue { background-color: #f2dede; color: #a94442; }
.status-pill.draft { background-color: #e6e6e6; color: #666; }
.status-pill.sent { background-color: #d9edf7; color: #31708f; }
.status-dot { display: inline-block; width: 8px; height: 8px; border-radius: 50%; background: #ccc; margin-right: 5px; }
.status-dot.active { background: var(--color-element-success); }
.text-error { color: var(--color-element-error); }

.payment-type-badge {
    padding: 4px 10px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    display: inline-block;
}
.payment-type-badge.invoice {
    background-color: #d9edf7;
    color: #31708f;
}
.payment-type-badge.income {
    background-color: #dff0d8;
    color: #3c763d;
}
.payment-type-badge.expense {
    background-color: #f2dede;
    color: #a94442;
}

.days-left-text {
    font-size: 11px;
    margin-top: 4px;
    font-weight: 500;
}

.days-left-expired {
    color: var(--color-element-error);
}

.days-left-urgent {
    color: var(--color-element-error);
}

.days-left-warning {
    color: var(--color-warning-element);
}

.days-left-normal {
    color: var(--color-text-maxcontrast);
}

/* PROJECTS GRID */
.projects-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 20px; }
.project-card {
    background: var(--color-main-background); border: 1px solid var(--color-border);
    border-radius: var(--border-radius-large); padding: 15px; cursor: pointer;
    transition: transform 0.2s, box-shadow 0.2s;
}
.project-card:hover { transform: translateY(-3px); box-shadow: 0 5px 15px rgba(0,0,0,0.05); border-color: var(--color-primary-element); }
.project-header { display: flex; justify-content: space-between; margin-bottom: 15px; }
.project-title { font-weight: bold; font-size: 15px; }
.project-status { font-size: 11px; padding: 2px 8px; background: var(--color-background-dark); border-radius: 4px; }
.progress-wrapper { margin-bottom: 15px; }
.progress-label { display: flex; justify-content: space-between; font-size: 11px; margin-bottom: 5px; opacity: 0.8; }
.project-dates { font-size: 12px; opacity: 0.7; display: flex; align-items: center; gap: 5px; }

/* TASKS & NOTES */
.task-section {
    margin-bottom: 32px;
}

.task-section-title {
    font-size: 16px;
    font-weight: 600;
    color: var(--color-main-text);
    margin-bottom: 16px;
    padding-bottom: 8px;
    border-bottom: 2px solid var(--color-border);
}

.task-section.completed-section {
    margin-top: 32px;
    padding-top: 24px;
    border-top: 1px solid var(--color-border);
}

.task-section.completed-section .task-section-title {
    color: var(--color-text-maxcontrast);
    font-size: 14px;
}

.task-list-container { 
    display: flex; 
    flex-direction: column; 
    gap: 12px; 
}

.crm-task-item { 
    background: var(--color-main-background); 
    padding: 16px; 
    border: 1px solid var(--color-border); 
    border-radius: var(--border-radius); 
    display: flex; 
    align-items: flex-start; 
    gap: 12px;
    transition: all 0.15s ease;
}

.crm-task-item:hover {
    background: var(--color-background-hover);
    border-color: var(--color-primary-element);
}

.crm-task-item.has-subtasks {
    flex-direction: column;
}

.crm-task-item.completed-task {
    opacity: 0.7;
}

.task-checkbox-area {
    flex-shrink: 0;
    margin-top: 2px;
}

.custom-checkbox {
    width: 20px;
    height: 20px;
    border: 2px solid var(--color-border);
    border-radius: 4px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.15s ease;
    background: var(--color-main-background);
    position: relative;
}

.custom-checkbox:hover:not(.checkbox-blocked) {
    border-color: var(--color-primary-element);
    background: var(--color-primary-light);
}

.custom-checkbox.checked {
    background: var(--color-primary-element);
    border-color: var(--color-primary-element);
}

.custom-checkbox.checked::after {
    content: '✓';
    color: white;
    font-size: 14px;
    font-weight: bold;
}

.custom-checkbox.checkbox-blocked {
    cursor: not-allowed;
    opacity: 0.6;
    background: var(--color-background-dark);
}

.custom-checkbox.small {
    width: 16px;
    height: 16px;
}

.custom-checkbox.small.checked::after {
    font-size: 12px;
}

.lock-icon {
    color: var(--color-text-maxcontrast);
    position: absolute;
}

.task-content {
    flex: 1;
    min-width: 0;
    cursor: pointer;
}

.task-title { 
    font-weight: 600; 
    font-size: 14px;
    color: var(--color-main-text);
    margin-bottom: 4px;
}

.task-title.is-completed {
    text-decoration: line-through;
    opacity: 0.6;
}

.subtask-title {
    font-size: 13px;
    font-weight: 500;
}

.task-meta {
    display: flex;
    align-items: center;
    gap: 12px;
    font-size: 12px;
    color: var(--color-text-maxcontrast);
    flex-wrap: wrap;
}

.subtasks-indicator {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    color: var(--color-text-maxcontrast);
}

.incomplete-badge {
    color: var(--color-element-error);
    font-weight: 600;
}

.subtasks-container {
    margin-top: 16px;
    padding-top: 16px;
    border-top: 1px solid var(--color-border);
    width: 95%;
}

.subtasks-header {
    display: flex;
    align-items: center;
    gap: 6px;
    margin-bottom: 12px;
    padding: 0 4px;
    font-size: 12px;
    font-weight: 600;
    color: var(--color-text-maxcontrast);
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.subtasks-header-text {
    font-size: 11px;
}

.subtasks-list {
    display: flex;
    flex-direction: column;
    gap: 6px;
    position: relative;
    padding-left: 20px;
}

.subtasks-list::before {
    content: '';
    position: absolute;
    left: 8px;
    top: 0;
    bottom: 0;
    width: 1px;
    background: linear-gradient(
        to bottom,
        var(--color-border) 0%,
        var(--color-border) 50%,
        transparent 100%
    );
}

.subtask-item {
    position: relative;
    display: flex;
    align-items: flex-start;
    gap: 0;
    padding: 10px 12px;
    background: var(--color-background-dark);
    border: 1px solid var(--color-border);
    border-radius: var(--border-radius);
    transition: all 0.2s ease;
    cursor: pointer;
}

.subtask-item:hover {
    background: var(--color-background-hover);
    border-color: var(--color-primary-element);
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.08);
    transform: translateX(2px);
}

.subtask-item.is-completed {
    opacity: 0.7;
    background: var(--color-background-dark);
}

.subtask-connector {
    position: absolute;
    left: -12px;
    top: 20px;
    width: 8px;
    height: 1px;
    background: var(--color-border);
}

.subtask-content-wrapper {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    flex: 1;
    min-width: 0;
}

.subtask-checkbox {
    flex-shrink: 0;
    margin-top: 2px;
}

.subtask-content {
    flex: 1;
    min-width: 0;
}

.subtask-header-row {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 4px;
    flex-wrap: wrap;
}

.subtask-title {
    font-size: 13px;
    font-weight: 500;
    color: var(--color-main-text);
    line-height: 1.4;
}

.subtask-priority-badge {
    padding: 2px 6px;
    border-radius: 10px;
    font-size: 10px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.3px;
}

.subtask-priority-badge.high {
    background-color: rgba(169, 68, 66, 0.15);
    color: #a94442;
}

.subtask-meta {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
    margin-top: 4px;
}

.subtask-date {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    font-size: 11px;
    color: var(--color-text-maxcontrast);
    padding: 2px 6px;
    background: var(--color-main-background);
    border-radius: 4px;
}

.subtask-date.date-overdue {
    background: rgba(169, 68, 66, 0.1);
    color: var(--color-element-error);
    font-weight: 500;
}

.subtask-description {
    font-size: 11px;
    color: var(--color-text-maxcontrast);
    font-style: italic;
    line-height: 1.3;
    opacity: 0.8;
}

/* Task Detail View */
.task-detail-view {
    display: flex;
    flex-direction: column;
    gap: 20px;
    padding: 20px;
}

.task-detail-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-bottom: 16px;
    border-bottom: 1px solid var(--color-border);
}

.task-detail-header .header-left {
    display: flex;
    align-items: center;
    gap: 16px;
    flex: 1;
}

.detail-title-wrapper {
    display: flex;
    align-items: center;
    gap: 12px;
    flex: 1;
}

.custom-checkbox.large {
    width: 24px;
    height: 24px;
    flex-shrink: 0;
}

.custom-checkbox.large.checked::after {
    font-size: 16px;
}

.detail-title {
    font-size: 20px;
    font-weight: 600;
    color: var(--color-main-text);
    margin: 0;
}

.detail-title.text-strike {
    text-decoration: line-through;
    opacity: 0.6;
}

.task-detail-header .header-actions {
    display: flex;
    gap: 8px;
}

.task-detail-content {
    display: grid;
    grid-template-columns: 1fr 350px;
    gap: 20px;
}

.task-detail-main {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.task-stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 16px;
}

.task-stat-widget {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 16px;
    background: var(--color-main-background);
    border: 1px solid var(--color-border);
    border-radius: var(--border-radius);
}

.widget-icon {
    flex-shrink: 0;
    color: var(--color-primary-element);
}

.widget-info {
    display: flex;
    flex-direction: column;
    gap: 4px;
    flex: 1;
    min-width: 0;
}

.widget-info .label {
    font-size: 11px;
    color: var(--color-text-maxcontrast);
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.widget-info .value {
    font-size: 14px;
    font-weight: 600;
    color: var(--color-main-text);
}

.widget-info .value.link {
    color: var(--color-primary-element);
    cursor: pointer;
}

.widget-info .value.link:hover {
    text-decoration: underline;
}

.status-badge, .priority-badge {
    padding: 4px 10px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    display: inline-block;
}

.status-badge.status-todo {
    background-color: #e6e6e6;
    color: #666;
}

.status-badge.status-in-progress {
    background-color: #d9edf7;
    color: #31708f;
}

.status-badge.status-done {
    background-color: #dff0d8;
    color: #3c763d;
}

.status-badge.status-cancelled {
    background-color: #f2dede;
    color: #a94442;
}

.priority-badge.priority-low {
    background-color: #dff0d8;
    color: #3c763d;
}

.priority-badge.priority-medium {
    background-color: #d9edf7;
    color: #31708f;
}

.priority-badge.priority-high {
    background-color: #f2dede;
    color: #a94442;
}

.task-detail-panel {
    background: var(--color-main-background);
    border: 1px solid var(--color-border);
    border-radius: var(--border-radius-large);
    overflow: hidden;
}

.task-detail-panel .panel-header {
    padding: 12px 16px;
    background: var(--color-background-hover);
    border-bottom: 1px solid var(--color-border);
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.task-detail-panel .panel-header h3 {
    margin: 0;
    font-size: 14px;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 8px;
    color: var(--color-main-text);
}

.task-detail-panel .panel-body {
    padding: 16px;
    font-size: 14px;
    line-height: 1.5;
    color: var(--color-main-text);
}

.text-content {
    white-space: pre-wrap;
}

.task-detail-sidebar {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.subtasks-detail-list {
    display: flex;
    flex-direction: column;
}

.subtask-detail-item {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    padding: 10px 16px;
    border-bottom: 1px solid var(--color-border);
    cursor: pointer;
    transition: background 0.15s;
}

.subtask-detail-item:last-child {
    border-bottom: none;
}

.subtask-detail-item:hover {
    background: var(--color-background-hover);
}

.subtask-detail-item.is-completed .subtask-detail-text {
    text-decoration: line-through;
    opacity: 0.6;
}

.subtask-detail-content {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.subtask-detail-text {
    font-size: 14px;
    color: var(--color-main-text);
}

.subtask-detail-desc {
    font-size: 12px;
    color: var(--color-text-maxcontrast);
}

@media (max-width: 1024px) {
    .task-detail-content {
        grid-template-columns: 1fr;
    }
}

/* Subtask responsive adjustments */
@media (max-width: 768px) {
    .subtasks-list {
        padding-left: 12px;
    }
    
    .subtask-item {
        padding: 8px 10px;
    }
    
    .subtask-title {
        font-size: 12px;
    }
    
    .subtask-meta {
        font-size: 10px;
    }
}

.task-priority {
    flex-shrink: 0;
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
}

.task-priority.low {
    background-color: #dff0d8;
    color: #3c763d;
}

.task-priority.medium {
    background-color: #d9edf7;
    color: #31708f;
}

.task-priority.high {
    background-color: #f2dede;
    color: #a94442;
}

.notes-list {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.crm-note-card {
    background: var(--color-main-background);
    border: 1px solid var(--color-border);
    border-radius: var(--border-radius);
    padding: 16px;
    cursor: pointer;
    transition: all 0.15s ease;
}

.crm-note-card:hover {
    background-color: var(--color-background-hover);
    border-color: var(--color-primary-element);
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.note-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 12px;
    gap: 12px;
}

.note-title {
    margin: 0;
    font-size: 16px;
    font-weight: 600;
    color: var(--color-main-text);
    flex: 1;
    word-break: break-word;
}

.note-actions {
    display: flex;
    gap: 4px;
    opacity: 0;
    transition: opacity 0.15s;
}

.crm-note-card:hover .note-actions {
    opacity: 1;
}

.icon-button-small {
    background: none;
    border: none;
    color: var(--color-text-maxcontrast);
    cursor: pointer;
    padding: 4px;
    border-radius: var(--border-radius);
    display: flex;
    align-items: center;
    justify-content: center;
    transition: background 0.15s;
}

.icon-button-small:hover {
    background-color: var(--color-background-hover);
    color: var(--color-error);
}

.note-body {
    font-size: 14px;
    color: var(--color-main-text);
    line-height: 1.6;
    margin-bottom: 12px;
    word-break: break-word;
    max-height: 200px;
    overflow: hidden;
}

.note-body :deep(p) {
    margin: 0 0 8px 0;
}

.note-body :deep(p:last-child) {
    margin-bottom: 0;
}

.note-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-top: 12px;
    border-top: 1px solid var(--color-border);
}

.note-date {
    font-size: 12px;
    color: var(--color-text-maxcontrast);
}

/* Note Form */
.note-form {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.note-form .form-group {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.note-form label {
    font-size: 13px;
    font-weight: 600;
    color: var(--color-text-maxcontrast);
}

.note-form .nc-input {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid var(--color-border);
    border-radius: var(--border-radius);
    background-color: var(--color-main-background);
    color: var(--color-main-text);
    font-size: 14px;
    box-sizing: border-box;
    transition: border-color 0.15s, box-shadow 0.15s;
}

.note-form .nc-input:focus {
    border-color: var(--color-primary-element);
    box-shadow: 0 0 0 2px var(--color-primary-element-light);
    outline: none;
}

.form-actions {
    display: flex;
    justify-content: flex-end;
    gap: 12px;
    margin-top: 8px;
}

/* Pane Actions */
.pane-actions {
    display: flex;
    gap: 12px;
    margin-bottom: 24px;
    flex-wrap: wrap;
    align-items: center;
}

.pane-actions.finance-actions {
    padding: 16px 20px;
    background: var(--color-main-background);
    border: 1px solid var(--color-border);
    border-radius: var(--border-radius-large);
    margin-bottom: 24px;
    box-shadow: 0 1px 2px rgba(0,0,0,0.05);
}

.pane-actions :deep(.button-vue) {
    min-width: auto;
    white-space: nowrap;
    flex-shrink: 0;
}

/* Responsive */
@media (max-width: 768px) {
    .mobile-hidden { display: none; }
    .app-content-list { width: 100%; border: none; }
    .crm-header { padding: 15px 15px 0; }
    .crm-profile-info { flex-direction: column; align-items: flex-start; gap: 10px; }
}

/* FILES */
.files-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
    margin-top: 20px;
}

.file-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px;
    background: var(--color-main-background);
    border: 1px solid var(--color-border);
    border-radius: var(--border-radius);
    transition: all 0.15s ease;
}

.file-item:hover {
    background: var(--color-background-hover);
    border-color: var(--color-primary-element);
}

.file-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
    background: var(--color-background-dark);
    border-radius: var(--border-radius);
    color: var(--color-text-maxcontrast);
    flex-shrink: 0;
}

.file-info {
    flex: 1;
    min-width: 0;
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.file-name {
    font-weight: 500;
    font-size: 14px;
    color: var(--color-main-text);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.file-meta {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 12px;
    color: var(--color-text-maxcontrast);
}

.file-actions {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-shrink: 0;
}

.icon-button-small {
    background: none;
    border: none;
    cursor: pointer;
    padding: 6px;
    border-radius: var(--border-radius);
    color: var(--color-text-maxcontrast);
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.15s ease;
}

.icon-button-small:hover {
    background: var(--color-background-hover);
    color: var(--color-main-text);
}

.icon-button-small.danger:hover {
    background: var(--color-element-error);
    color: white;
}
</style>