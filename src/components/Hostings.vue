<template>
    <div class="app-content-wrapper">
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
        <!-- PACKAGES VIEW (Split-Pane)                 -->
        <!-- ========================================== -->
        <template v-if="showPackagesView">
            <!-- LEFT PANE: PACKAGE LIST -->
            <div class="app-content-list" :class="{ 'mobile-hidden': isMobile && selectedPackage }">
                <div class="app-content-list-header">
                    <div class="search-wrapper">
                        <div class="search-wrapper-inner">
                            <Magnify :size="20" class="search-icon" />
                            <input type="text" v-model="packageSearchQuery" :placeholder="translate('domaincontrol', 'Paket ara...')" class="search-input" />
                        </div>
                    </div>
                    <div class="app-navigation__search">
                        <header class="header">
                            <div class="import-and-new-contact-buttons">
                                <NcButton 
                                    type="secondary" 
                                    :wide="true"
                                    @click="showPackagesView = false">
                                    <template #icon>
                                        <ArrowLeft :size="20" />
                                    </template>
                                    {{ translate('domaincontrol', 'Geri') }}
                                </NcButton>
                                <NcButton 
                                    type="secondary" 
                                    :wide="true"
                                    @click="showPackageModal()">
                                    <template #icon>
                                        <Plus :size="20" />
                                    </template>
                                    {{ translate('domaincontrol', 'Yeni Paket') }}
                                </NcButton>
                            </div>
                        </header>
                    </div>
                </div>

                <div class="app-content-list-wrapper">
                    <div v-if="packagesLoading" class="loading-container">
                        <Refresh :size="32" class="spin-animation" />
                    </div>
                    <div v-else-if="filteredPackages.length === 0" class="empty-list">
                        <div class="empty-text">{{ translate('domaincontrol', 'Paket bulunamadı') }}</div>
                    </div>
                    <ul v-else class="app-navigation-list">
                        <li 
                            v-for="pkg in filteredPackages" 
                            :key="pkg.id" 
                            class="app-navigation-entry" 
                            :class="{ 'active': selectedPackage && selectedPackage.id === pkg.id }" 
                            @click="selectPackage(pkg)"
                        >
                            <div class="app-navigation-entry-icon">
                                <div class="avatar-circle package-avatar" :style="{ backgroundColor: getPackageColor(pkg.name) }">
                                    <PackageVariant :size="20" />
                                </div>
                            </div>
                            <div class="app-navigation-entry-content">
                                <div class="app-navigation-entry-name">{{ pkg.name || '-' }}</div>
                                <div class="app-navigation-entry-details">
                                    <span v-if="pkg.provider">{{ pkg.provider }}</span>
                                    <span v-else-if="pkg.priceMonthly">{{ formatCurrency(pkg.priceMonthly, pkg.currency) }}/{{ translate('domaincontrol', 'ay') }}</span>
                                    <span v-else>{{ translate('domaincontrol', 'Paket') }}</span>
                                </div>
                            </div>
                            <div class="app-navigation-entry-status">
                                <span class="status-dot-small" :class="pkg.isActive ? 'status-ok' : 'status-expired'"></span>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- RIGHT PANE: PACKAGE DETAIL -->
            <div class="app-content-details" :class="{ 'mobile-hidden': isMobile && !selectedPackage }">

                <!-- Empty State -->
                <div v-if="!selectedPackage" class="empty-content">
                    <div class="empty-content-icon"><PackageVariant :size="64" /></div>
                    <h2 class="empty-content-title">{{ translate('domaincontrol', 'Bir paket seçin') }}</h2>
                </div>

                <!-- Detail Content -->
                <div v-else class="crm-detail-container">
                    
                    <!-- HEADER -->
                    <div class="crm-header">
                        <div class="crm-header-top">
                            <button v-if="isMobile" class="icon-button back-button" @click="backToPackageList">
                                <ArrowLeft :size="24" />
                            </button>
                            <div class="crm-profile-info">
                                <div class="avatar-xl package-avatar-xl" :style="{ backgroundColor: getPackageColor(selectedPackage.name) }">
                                    <PackageVariant :size="36" />
                                </div>
                                <div class="crm-profile-text">
                                    <h1 class="crm-client-name">{{ selectedPackage.name || '-' }}</h1>
                                    <div class="crm-client-meta">
                                        <span v-if="selectedPackage.provider" class="meta-item">
                                            <span>{{ selectedPackage.provider }}</span>
                                        </span>
                                        <span class="meta-item">
                                            <span class="status-badge" :class="selectedPackage.isActive ? 'badge-success' : 'badge-neutral'">
                                                {{ selectedPackage.isActive ? translate('domaincontrol', 'Aktif') : translate('domaincontrol', 'Pasif') }}
                                            </span>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="crm-header-actions">
                                <NcButton @click="editPackage(selectedPackage)">
                                    {{ translate('domaincontrol', 'Düzenle') }}
                                </NcButton>
                                <button class="icon-button danger" @click="confirmDeletePackage(selectedPackage)">
                                    <Delete :size="20" />
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- CONTENT SCROLL AREA -->
                    <div class="crm-content-scroll">
                        
                        <!-- Stats Grid -->
                        <div class="stats-grid">
                            <div class="stat-card">
                                <div class="stat-icon" style="background-color: rgba(70, 186, 97, 0.1);">
                                    <CurrencyUsd :size="24" style="color: var(--color-element-success);" />
                                </div>
                                <div class="stat-content">
                                    <div class="stat-label">{{ translate('domaincontrol', 'Aylık Fiyat') }}</div>
                                    <div class="stat-value">
                                        {{ selectedPackage.priceMonthly ? formatCurrency(selectedPackage.priceMonthly, selectedPackage.currency) : '-' }}
                                    </div>
                                </div>
                            </div>
                            
                            <div class="stat-card">
                                <div class="stat-icon" style="background-color: rgba(0, 130, 201, 0.1);">
                                    <ServerNetwork :size="24" style="color: var(--color-primary-element);" />
                                </div>
                                <div class="stat-content">
                                    <div class="stat-label">{{ translate('domaincontrol', 'Kullanılan Hostingler') }}</div>
                                    <div class="stat-value">{{ getPackagesHostings(selectedPackage.id).length }}</div>
                                </div>
                            </div>
                        </div>

                        <!-- Package Information -->
                        <div class="content-box">
                            <div class="box-header">
                                <h3>{{ translate('domaincontrol', 'Paket Bilgileri') }}</h3>
                            </div>
                            <div class="box-body">
                                <div class="info-grid">
                                    <div class="info-group">
                                        <div class="info-label">{{ translate('domaincontrol', 'Paket Adı') }}</div>
                                        <div class="info-val">{{ selectedPackage.name || '-' }}</div>
                                    </div>
                                    <div class="info-group" v-if="selectedPackage.provider">
                                        <div class="info-label">{{ translate('domaincontrol', 'Sağlayıcı') }}</div>
                                        <div class="info-val">{{ selectedPackage.provider }}</div>
                                    </div>
                                    <div class="info-group" v-if="selectedPackage.priceMonthly">
                                        <div class="info-label">{{ translate('domaincontrol', 'Aylık Fiyat') }}</div>
                                        <div class="info-val">{{ formatCurrency(selectedPackage.priceMonthly, selectedPackage.currency) }}</div>
                                    </div>
                                    <div class="info-group" v-if="selectedPackage.priceYearly">
                                        <div class="info-label">{{ translate('domaincontrol', 'Yıllık Fiyat') }}</div>
                                        <div class="info-val">{{ formatCurrency(selectedPackage.priceYearly, selectedPackage.currency) }}</div>
                                    </div>
                                    <div class="info-group">
                                        <div class="info-label">{{ translate('domaincontrol', 'Durum') }}</div>
                                        <div class="info-val">
                                            <span class="status-badge" :class="selectedPackage.isActive ? 'badge-success' : 'badge-neutral'">
                                                {{ selectedPackage.isActive ? translate('domaincontrol', 'Aktif') : translate('domaincontrol', 'Pasif') }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Used By Hostings -->
                        <div class="content-box">
                            <div class="box-header">
                                <h3>
                                    {{ translate('domaincontrol', 'Bu Paketi Kullanan Hostingler') }}
                                    <span class="tab-badge" v-if="getPackagesHostings(selectedPackage.id).length">{{ getPackagesHostings(selectedPackage.id).length }}</span>
                                </h3>
                            </div>
                            <div class="box-body">
                                <div v-if="getPackagesHostings(selectedPackage.id).length === 0" class="empty-box">
                                    {{ translate('domaincontrol', 'Bu paketi kullanan hosting yok') }}
                                </div>
                                <div v-else class="linked-items-list">
                                    <div 
                                        v-for="hosting in getPackagesHostings(selectedPackage.id)" 
                                        :key="hosting.id" 
                                        class="linked-item"
                                        @click="navigateToHosting(hosting.id)"
                                    >
                                        <div class="linked-item-icon">
                                            <ServerNetwork :size="24" />
                                        </div>
                                        <div class="linked-item-info">
                                            <div class="linked-item-name">{{ hosting.provider || '-' }}</div>
                                            <div class="linked-item-meta">
                                                <span v-if="getClientName(hosting.clientId) && getClientName(hosting.clientId) !== 'Unassigned'">
                                                    {{ getClientName(hosting.clientId) }}
                                                </span>
                                                <span v-else-if="hosting.serverIp">{{ hosting.serverIp }}</span>
                                                <span v-else>-</span>
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

        <!-- ========================================== -->
        <!-- HOSTING LIST & DETAIL VIEW (Split-Pane)   -->
        <!-- ========================================== -->
        <template v-else>
            <!-- LEFT PANE: LIST -->
            <div class="app-content-list" :class="{ 'mobile-hidden': isMobile && selectedHosting }">
                <div class="app-content-list-header">
                    <div class="search-wrapper">
                        <div class="search-wrapper-inner">
                            <Magnify :size="20" class="search-icon" />
                            <input type="text" v-model="searchQuery" :placeholder="translate('domaincontrol', 'Hosting ara...')" class="search-input" />
                        </div>
                    </div>
                    <div class="app-navigation__search">
                        <header class="header">
                            <div class="import-and-new-contact-buttons">
                                <NcButton 
                                    type="secondary" 
                                    :wide="true"
                                    @click="showPackagesView = true">
                                    <template #icon>
                                        <PackageVariant :size="20" />
                                    </template>
                                    {{ translate('domaincontrol', 'Paketler') }}
                                </NcButton>
                                <NcButton 
                                    type="secondary" 
                                    :wide="true"
                                    @click="showAddModal">
                                    <template #icon>
                                        <Plus :size="20" />
                                    </template>
                                    {{ translate('domaincontrol', 'Yeni Hosting') }}
                                </NcButton>
                            </div>
                        </header>
                    </div>
                </div>

                <div class="app-content-list-wrapper">
                    <div v-if="loading" class="loading-container">
                        <Refresh :size="32" class="spin-animation" />
                    </div>
                    <div v-else-if="filteredHostings.length === 0" class="empty-list">
                        <div class="empty-text">{{ translate('domaincontrol', 'Hosting bulunamadı') }}</div>
                    </div>
                    <ul v-else class="app-navigation-list">
                        <li 
                            v-for="hosting in filteredHostings" 
                            :key="hosting.id" 
                            class="app-navigation-entry" 
                            :class="{ 'active': selectedHosting && selectedHosting.id === hosting.id }" 
                            @click="selectHosting(hosting)"
                        >
                            <div class="app-navigation-entry-icon">
                                <div class="avatar-circle hosting-avatar" :style="{ backgroundColor: getAvatarColor(hosting.provider) }">
                                    {{ (hosting.provider || 'H').substring(0, 2).toUpperCase() }}
                                </div>
                            </div>
                            <div class="app-navigation-entry-content">
                                <div class="app-navigation-entry-name">
                                    {{ hosting.provider || '-' }}
                                    <span v-if="hosting.plan" class="plan-tag-small">{{ hosting.plan }}</span>
                                </div>
                                <div class="app-navigation-entry-details">
                                    <span v-if="getClientName(hosting.clientId) && getClientName(hosting.clientId) !== 'Unassigned'">
                                        {{ getClientName(hosting.clientId) }}
                                    </span>
                                    <span v-else-if="hosting.serverIp">
                                        {{ hosting.serverIp }}
                                    </span>
                                    <span v-else>{{ translate('domaincontrol', 'Hosting') }}</span>
                                </div>
                            </div>
                            <div class="app-navigation-entry-status">
                                <span class="status-dot-small" :class="getHostingStatusClass(hosting)"></span>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- RIGHT PANE: DETAIL VIEW -->
            <div class="app-content-details" :class="{ 'mobile-hidden': isMobile && !selectedHosting }">

                <!-- Empty State -->
                <div v-if="!selectedHosting" class="empty-content">
                    <div class="empty-content-icon"><ServerNetwork :size="64" /></div>
                    <h2 class="empty-content-title">{{ translate('domaincontrol', 'Bir hosting seçin') }}</h2>
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
                                <div class="avatar-xl hosting-avatar-xl" :style="{ backgroundColor: getAvatarColor(selectedHosting.provider) }">
                                    {{ (selectedHosting.provider || 'H').substring(0, 1).toUpperCase() }}
                                </div>
                                <div class="crm-profile-text">
                                    <h1 class="crm-client-name">{{ selectedHosting.provider || '-' }}</h1>
                                    <div class="crm-client-meta">
                                        <span v-if="selectedHosting.plan" class="meta-item">
                                            <span>{{ selectedHosting.plan }}</span>
                                        </span>
                                        <span v-if="getClientName(selectedHosting.clientId) && getClientName(selectedHosting.clientId) !== 'Unassigned'" class="meta-item">
                                            <Account :size="14" />
                                            <span>{{ getClientName(selectedHosting.clientId) }}</span>
                                        </span>
                                        <span class="meta-item" :class="getHostingStatusBadgeClass(selectedHosting)">
                                            <span>{{ getHostingStatusText(selectedHosting) }}</span>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="crm-header-actions">
                                <NcButton type="success" @click="showPaymentModal(selectedHosting)">
                                    <template #icon>
                                        <CurrencyUsd :size="18" />
                                    </template>
                                    {{ translate('domaincontrol', 'Ödeme Ekle') }}
                                </NcButton>
                                <NcButton @click="editHosting(selectedHosting)">
                                    {{ translate('domaincontrol', 'Düzenle') }}
                                </NcButton>
                                <button class="icon-button danger" @click="confirmDelete(selectedHosting)">
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
                                <button class="crm-tab" :class="{ active: activeTab === 'domains' }" @click="activeTab = 'domains'">
                                    {{ translate('domaincontrol', 'Domainler') }} 
                                    <span class="tab-badge" v-if="getLinkedDomains(selectedHosting.id).length">{{ getLinkedDomains(selectedHosting.id).length }}</span>
                                </button>
                                <button class="crm-tab" :class="{ active: activeTab === 'websites' }" @click="activeTab = 'websites'">
                                    {{ translate('domaincontrol', 'Web Siteleri') }} 
                                    <span class="tab-badge" v-if="getLinkedWebsites(selectedHosting.id).length">{{ getLinkedWebsites(selectedHosting.id).length }}</span>
                                </button>
                                <button class="crm-tab" :class="{ active: activeTab === 'payments' }" @click="activeTab = 'payments'">
                                    {{ translate('domaincontrol', 'Ödeme Geçmişi') }}
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
                                        <div class="stat-label">{{ translate('domaincontrol', 'Sonraki Ödeme') }}</div>
                                        <div class="stat-value">{{ formatDate(selectedHosting.expirationDate || selectedHosting.expiration_date) }}</div>
                                    </div>
                                </div>
                                
                                <div class="stat-card">
                                    <div class="stat-icon" :style="{ backgroundColor: getDaysUntilExpiry(selectedHosting.expirationDate || selectedHosting.expiration_date) <= 30 ? 'rgba(233, 50, 45, 0.1)' : 'rgba(70, 186, 97, 0.1)' }">
                                        <Timelapse :size="24" :style="{ color: getDaysUntilExpiry(selectedHosting.expirationDate || selectedHosting.expiration_date) <= 30 ? 'var(--color-element-error)' : 'var(--color-element-success)' }" />
                                    </div>
                                    <div class="stat-content">
                                        <div class="stat-label">{{ translate('domaincontrol', 'Kalan Gün') }}</div>
                                        <div class="stat-value" :class="getDaysUntilExpiry(selectedHosting.expirationDate || selectedHosting.expiration_date) <= 30 ? 'text-error' : 'text-success'">
                                            {{ getDaysUntilExpiry(selectedHosting.expirationDate || selectedHosting.expiration_date) }}
                                        </div>
                                    </div>
                                </div>

                                <div class="stat-card">
                                    <div class="stat-icon" style="background-color: rgba(70, 186, 97, 0.1);">
                                        <CurrencyUsd :size="24" style="color: var(--color-element-success);" />
                                    </div>
                                    <div class="stat-content">
                                        <div class="stat-label">{{ translate('domaincontrol', 'Maliyet') }}</div>
                                        <div class="stat-value">
                                            {{ formatCurrency(selectedHosting.price, selectedHosting.currency) || '-' }}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- General Information -->
                            <div class="content-box">
                                <div class="box-header">
                                    <h3>{{ translate('domaincontrol', 'Hosting Detayları') }}</h3>
                                </div>
                                <div class="box-body">
                                    <div class="info-grid">
                                        <div class="info-group">
                                            <div class="info-label">{{ translate('domaincontrol', 'Müşteri') }}</div>
                                            <div class="info-val">
                                                <a v-if="selectedHosting.clientId && getClientName(selectedHosting.clientId) !== 'Unassigned'" href="#" class="info-link" @click.prevent="navigateToClient(selectedHosting.clientId)">
                                                    <Account :size="14" />
                                                    {{ getClientName(selectedHosting.clientId) }}
                                                </a>
                                                <span v-else>-</span>
                                            </div>
                                        </div>
                                        <div class="info-group" v-if="selectedHosting.serverIp">
                                            <div class="info-label">{{ translate('domaincontrol', 'Sunucu IP') }}</div>
                                            <div class="info-val font-mono">{{ selectedHosting.serverIp }}</div>
                                        </div>
                                        <div class="info-group" v-if="selectedHosting.serverType">
                                            <div class="info-label">{{ translate('domaincontrol', 'Tip') }}</div>
                                            <div class="info-val">
                                                {{ selectedHosting.serverType === 'own' ? translate('domaincontrol', 'Kendi Sunucusu') : translate('domaincontrol', 'Harici Sunucu') }}
                                            </div>
                                        </div>
                                        <div class="info-group" v-if="selectedHosting.renewalInterval">
                                            <div class="info-label">{{ translate('domaincontrol', 'Döngü') }}</div>
                                            <div class="info-val">{{ formatRenewalInterval(selectedHosting.renewalInterval) }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Panel Access -->
                            <div class="content-box" v-if="selectedHosting.panelUrl || selectedHosting.panelNotes || selectedHosting.panel_url || selectedHosting.panel_notes">
                                <div class="box-header">
                                    <h3>
                                        <Login :size="18" class="inline-icon" />
                                        {{ translate('domaincontrol', 'Panel Erişimi') }}
                                    </h3>
                                </div>
                                <div class="box-body">
                                    <div v-if="selectedHosting.panelUrl || selectedHosting.panel_url" class="info-group" style="margin-bottom: 12px;">
                                        <div class="info-label">URL</div>
                                        <div class="info-val">
                                            <a :href="selectedHosting.panelUrl || selectedHosting.panel_url" target="_blank" class="info-link">
                                                {{ selectedHosting.panelUrl || selectedHosting.panel_url }}
                                            </a>
                                        </div>
                                    </div>
                                    <pre class="code-block">{{ selectedHosting.panelNotes || selectedHosting.panel_notes || translate('domaincontrol', 'Giriş bilgisi yok') }}</pre>
                                </div>
                            </div>
                        </div>

                        <!-- 2. DOMAINLER -->
                        <div v-if="activeTab === 'domains'" class="tab-pane">
                            <div v-if="getLinkedDomains(selectedHosting.id).length === 0" class="empty-box">
                                {{ translate('domaincontrol', 'Bu hosting\'e bağlı domain yok') }}
                            </div>
                            <div v-else class="linked-items-list">
                                <div 
                                    v-for="domain in getLinkedDomains(selectedHosting.id)" 
                                    :key="domain.id" 
                                    class="linked-item"
                                    @click="navigateToDomain(domain.id)"
                                >
                                    <div class="linked-item-icon">
                                        <Web :size="24" />
                                    </div>
                                    <div class="linked-item-info">
                                        <div class="linked-item-name">{{ domain.domainName || domain.domain_name || '-' }}</div>
                                        <div class="linked-item-meta">{{ formatDate(domain.expirationDate || domain.expiration_date) }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 3. WEB SİTELERİ -->
                        <div v-if="activeTab === 'websites'" class="tab-pane">
                            <div v-if="getLinkedWebsites(selectedHosting.id).length === 0" class="empty-box">
                                {{ translate('domaincontrol', 'Bu hosting\'e bağlı web sitesi yok') }}
                            </div>
                            <div v-else class="linked-items-list">
                                <div 
                                    v-for="website in getLinkedWebsites(selectedHosting.id)" 
                                    :key="website.id" 
                                    class="linked-item"
                                    @click="navigateToWebsite(website.id)"
                                >
                                    <div class="linked-item-icon">
                                        <WebBox :size="24" />
                                    </div>
                                    <div class="linked-item-info">
                                        <div class="linked-item-name">{{ website.name || website.software || translate('domaincontrol', 'Web Sitesi') }}</div>
                                        <div class="linked-item-meta">{{ website.url || '-' }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 4. ÖDEME GEÇMİŞİ -->
                        <div v-if="activeTab === 'payments'" class="tab-pane">
                            <div class="pane-actions">
                                <NcButton type="primary" @click="showPaymentModal(selectedHosting)">
                                    <template #icon><Plus :size="20" /></template>
                                    {{ translate('domaincontrol', 'Ödeme Ekle') }}
                                </NcButton>
                            </div>
                            <div v-if="getPaymentHistory(selectedHosting).length === 0" class="empty-box">
                                {{ translate('domaincontrol', 'Ödeme geçmişi yok') }}
                            </div>
                            <div v-else class="payment-history">
                                <div
                                    v-for="(entry, index) in getPaymentHistory(selectedHosting)"
                                    :key="index"
                                    class="history-entry"
                                >
                                    <div class="history-date">
                                        <Calendar :size="14" />
                                        {{ formatDate(entry.date) }}
                                    </div>
                                    <div class="history-content">
                                        <div class="history-main text-success">
                                            + {{ formatCurrency(entry.amount, entry.currency) }}
                                        </div>
                                        <div class="history-sub">
                                            {{ entry.period }} {{ translate('domaincontrol', 'ay') }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </template>
    </div>
</template>

<script>
import { NcButton } from '@nextcloud/vue'
import api from '../services/api'
import HostingModal from './HostingModal.vue'
import HostingPaymentModal from './HostingPaymentModal.vue'
import HostingPackageModal from './HostingPackageModal.vue'

// Icons
import ServerNetwork from 'vue-material-design-icons/ServerNetwork.vue'
import Magnify from 'vue-material-design-icons/Magnify.vue'
import Plus from 'vue-material-design-icons/Plus.vue'
import Refresh from 'vue-material-design-icons/Refresh.vue'
import ArrowLeft from 'vue-material-design-icons/ArrowLeft.vue'
import Pencil from 'vue-material-design-icons/Pencil.vue'
import Delete from 'vue-material-design-icons/Delete.vue'
import PackageVariant from 'vue-material-design-icons/PackageVariant.vue'
import Account from 'vue-material-design-icons/Account.vue'
import CalendarClock from 'vue-material-design-icons/CalendarClock.vue'
import Timelapse from 'vue-material-design-icons/Timelapse.vue'
import CurrencyUsd from 'vue-material-design-icons/CurrencyUsd.vue'
import Login from 'vue-material-design-icons/Login.vue'
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
        ServerNetwork, Magnify, Plus, Refresh, ArrowLeft, Pencil, Delete, 
        PackageVariant, Account, CalendarClock, Timelapse, 
        CurrencyUsd, Login, Calendar, Web, WebBox
    },
    data() {
        return {
            hostings: [],
            hostingPackages: [],
            clients: [],
            domains: [],
            websites: [],
            selectedHosting: null,
            selectedPackage: null,
            searchQuery: '',
            packageSearchQuery: '',
            loading: false,
            isMobile: window.innerWidth < 768,
            activeTab: 'overview',
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
        filteredPackages() {
            if (!this.packageSearchQuery) return this.hostingPackages
            const query = this.packageSearchQuery.toLowerCase()
            return this.hostingPackages.filter(pkg => {
                return (
                    pkg.name?.toLowerCase().includes(query) ||
                    pkg.provider?.toLowerCase().includes(query)
                )
            })
        },
    },
    mounted() {
        this.loadHostings()
        this.loadRelatedData()
        window.addEventListener('resize', this.handleResize)
    },
    beforeUnmount() {
        window.removeEventListener('resize', this.handleResize)
    },
    watch: {
        showPackagesView(newVal) { 
            if (newVal) {
                this.loadHostingPackages()
                this.selectedPackage = null
            } else {
                this.selectedPackage = null
            }
        },
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
            this.activeTab = 'overview'
        },
        backToList() {
            this.selectedHosting = null
        },
        selectPackage(pkg) {
            this.selectedPackage = pkg
        },
        backToPackageList() {
            this.selectedPackage = null
        },
        navigateToHosting(hostingId) {
            this.showPackagesView = false
            this.$nextTick(() => {
                const hosting = this.hostings.find(h => h.id === hostingId)
                if (hosting) {
                    this.selectHosting(hosting)
                }
            })
        },
        getPackagesHostings(packageId) {
            // Find hostings that use this package
            // Assuming package is linked via packageId or package_id field
            return this.hostings.filter(h => {
                return (h.packageId == packageId || h.package_id == packageId) ||
                       (h.plan && this.hostingPackages.find(p => p.id == packageId && p.name === h.plan))
            })
        },
        getPackageColor(name) {
            if (!name) return '#0082c9'
            const colors = ['#0082c9', '#46ba61', '#f0ad4e', '#e3322d', '#5bc0de', '#9b59b6', '#e67e22', '#3498db']
            let hash = 0
            for (let i = 0; i < name.length; i++) {
                hash = name.charCodeAt(i) + ((hash << 5) - hash)
            }
            return colors[Math.abs(hash) % colors.length]
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
            if (this.selectedHosting) {
                const updated = this.hostings.find(h => h.id === this.selectedHosting.id)
                if (updated) this.selectedHosting = updated
            }
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
            if (this.selectedHosting) {
                const updated = this.hostings.find(h => h.id === this.selectedHosting.id)
                if (updated) this.selectedHosting = updated
            }
        },
        showPackageModal(pkg = null) {
            this.editingPackage = pkg
            this.packageModalOpen = true
        },
        editPackage(pkg) {
            if (pkg) {
                this.selectedPackage = pkg
            }
            this.showPackageModal(pkg || this.selectedPackage)
        },
        closePackageModal() {
            this.packageModalOpen = false
            this.editingPackage = null
        },
        handlePackageSaved() {
            this.closePackageModal()
            this.loadHostingPackages()
            if (this.selectedPackage) {
                const updated = this.hostingPackages.find(p => p.id === this.selectedPackage.id)
                if (updated) this.selectedPackage = updated
            }
        },
        async confirmDelete(hosting) {
            if (confirm(this.translate('domaincontrol', 'Bu hosting\'i silmek istediğinizden emin misiniz?'))) {
                try {
                    await api.hostings.delete(hosting.id)
                    this.backToList()
                    this.loadHostings()
                    this.loadRelatedData()
                } catch (error) {
                    alert(this.translate('domaincontrol', 'Hosting silinemedi'))
                }
            }
        },
        async confirmDeletePackage(pkg) {
            if (confirm(this.translate('domaincontrol', `Paket ${pkg.name} silmek istediğinizden emin misiniz?`))) {
                try {
                    await api.hostingPackages.delete(pkg.id)
                    this.loadHostingPackages()
                } catch (error) {
                    alert(this.translate('domaincontrol', 'Paket silinemedi'))
                }
            }
        },
        formatDate(dateString) {
            if (!dateString) return '-'
            try {
                return new Date(dateString).toLocaleDateString('tr-TR', { year: 'numeric', month: 'short', day: 'numeric' })
            } catch (e) { 
                return dateString.split(' ')[0] 
            }
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
            const days = this.getDaysUntilExpiry(hosting.expirationDate || hosting.expiration_date)
            if (days <= 0) return 'status-critical'
            if (days <= 30) return 'status-warning'
            return 'status-ok'
        },
        getHostingStatusText(hosting) {
            const days = this.getDaysUntilExpiry(hosting.expirationDate || hosting.expiration_date)
            if (days <= 0) return this.translate('domaincontrol', 'Süresi Doldu')
            if (days <= 7) return this.translate('domaincontrol', 'Kritik')
            if (days <= 30) return this.translate('domaincontrol', 'Yenileme Gerekli')
            return this.translate('domaincontrol', 'Aktif')
        },
        getHostingStatusBadgeClass(hosting) {
            const status = this.getHostingStatusClass(hosting)
            if(status === 'status-critical') return 'text-error'
            if(status === 'status-warning') return 'text-warning'
            return 'text-success'
        },
        getClientName(clientId) {
            if (!clientId) return this.translate('domaincontrol', 'Unassigned')
            const client = this.clients.find(c => c.id == clientId)
            return client ? client.name : this.translate('domaincontrol', 'Unassigned')
        },
        getLinkedDomains(hostingId) { 
            return this.domains.filter(d => (d.hostingId == hostingId || d.hosting_id == hostingId)) 
        },
        getLinkedWebsites(hostingId) { 
            return this.websites.filter(w => (w.hostingId == hostingId || w.hosting_id == hostingId)) 
        },
        getPaymentHistory(hosting) {
            try { 
                const history = hosting.paymentHistory || hosting.payment_history
                return history ? JSON.parse(history) : [] 
            } 
            catch (e) { 
                return [] 
            }
        },
        formatCurrency(amount, currency) {
            if (amount == null) return ''
            const symbol = { USD: '$', EUR: '€', TRY: '₺' }[currency] || ''
            return `${symbol}${parseFloat(amount).toFixed(2)}`
        },
        formatRenewalInterval(interval) { 
            return interval 
        },
        navigateToClient(id) { 
            if(window.DomainControl?.selectClient) {
                window.DomainControl.selectClient(id)
            }
        },
        navigateToDomain(id) { 
            if(window.DomainControl?.switchTab) {
                window.DomainControl.switchTab('domains')
                setTimeout(() => {
                    if (window.DomainControl.selectDomain) {
                        window.DomainControl.selectDomain(id)
                    }
                }, 100)
            }
        },
        navigateToWebsite(id) { 
            if(window.DomainControl?.switchTab) {
                window.DomainControl.switchTab('websites')
                if (id) {
                    setTimeout(() => {
                        if (window.DomainControl.showWebsiteDetail) {
                            window.DomainControl.showWebsiteDetail(id)
                        }
                    }, 100)
                }
            }
        },
        getAvatarColor(name) {
            if (!name) return '#999'
            let hash = 0
            for (let i = 0; i < name.length; i++) {
                hash = name.charCodeAt(i) + ((hash << 5) - hash)
            }
            return `hsl(${hash % 360}, 65%, 45%)`
        }
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

/* Package Avatar Styles */
.package-avatar {
    background-color: var(--color-primary-element) !important;
    color: white;
}

.package-avatar-xl {
    background-color: var(--color-primary-element) !important;
    color: white;
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
    flex-direction: column;
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

.hosting-avatar {
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
    display: flex;
    align-items: center;
    gap: 6px;
}

.plan-tag-small {
    font-size: 11px;
    background: var(--color-background-dark);
    padding: 2px 6px;
    border-radius: 4px;
    color: var(--color-text-maxcontrast);
    font-weight: normal;
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
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 28px;
    font-weight: bold;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    padding: 10px 18px;
}


.hosting-avatar-xl {
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
    min-width: min-content;
}

.crm-tab {
    padding: 12px 20px;
    background: none;
    border: none;
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
    padding: 25px;
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

.font-mono {
    font-family: monospace;
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

/* Linked Items List */
.linked-items-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.linked-item {
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

.linked-item:hover {
    background: var(--color-background-hover);
    border-color: var(--color-primary-element);
}

.linked-item-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    background: var(--color-background-dark);
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--color-primary-element);
}

.linked-item-info {
    flex: 1;
    min-width: 0;
}

.linked-item-name {
    font-weight: 600;
    font-size: 15px;
    color: var(--color-main-text);
    margin-bottom: 4px;
}

.linked-item-meta {
    font-size: 13px;
    color: var(--color-text-maxcontrast);
}

/* Payment History */
.payment-history {
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
    display: flex;
    align-items: center;
    gap: 6px;
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

.badge-success {
    background-color: rgba(70, 186, 97, 0.15);
    color: var(--color-element-success);
}

.badge-neutral {
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

.inline-icon {
    margin-right: 6px;
    vertical-align: middle;
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
