<template>
    <div class="app-content-wrapper">
        <!-- ========================================== -->
        <!-- MODALS                                     -->
        <!-- ========================================== -->
		<WebsiteModal
			:open="modalOpen"
			:website="editingWebsite"
			:clients="clients"
			:domains="domains"
			:hostings="hostings"
			@close="closeModal"
			@saved="handleWebsiteSaved"
		/>

        <!-- ========================================== -->
        <!-- LEFT PANE: LIST                            -->
        <!-- ========================================== -->
        <div class="app-content-list" :class="{ 'mobile-hidden': isMobile && selectedWebsite }">
            <div class="app-content-list-header">
                <div class="search-wrapper">
                    <div class="search-wrapper-inner">
                        <Magnify :size="20" class="search-icon" />
                        <input type="text" v-model="searchQuery" :placeholder="translate('domaincontrol', 'Website ara...')" class="search-input" />
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
                                {{ translate('domaincontrol', 'Yeni Website') }}
                            </NcButton>
			</div>
                    </header>
                </div>
			</div>

            <div class="app-content-list-wrapper">
                <div v-if="loading" class="loading-container">
                    <Refresh :size="32" class="spin-animation" />
                </div>
                <div v-else-if="filteredWebsites.length === 0" class="empty-list">
                    <div class="empty-text">{{ translate('domaincontrol', 'Website bulunamadı') }}</div>
                </div>
                <ul v-else class="app-navigation-list">
                    <li 
					v-for="website in filteredWebsites"
					:key="website.id"
                        class="app-navigation-entry" 
                        :class="{ 'active': selectedWebsite && selectedWebsite.id === website.id }" 
					@click="selectWebsite(website)"
				>
                        <div class="app-navigation-entry-icon">
                            <div class="avatar-circle website-avatar" :style="{ backgroundColor: getWebsiteColor(website.name || website.software) }">
                                <Web :size="20" />
					</div>
						</div>
                        <div class="app-navigation-entry-content">
                            <div class="app-navigation-entry-name">{{ website.name || website.software || translate('domaincontrol', 'Website') }}</div>
                            <div class="app-navigation-entry-details">
                                <span v-if="getClientName(website.clientId)">{{ getClientName(website.clientId) }}</span>
                                <span v-else-if="getDomainName(website.domainId)">{{ getDomainName(website.domainId) }}</span>
                                <span v-else-if="website.software">{{ website.software }}</span>
                                <span v-else>{{ translate('domaincontrol', 'Website') }}</span>
					</div>
							</div>
                        <div class="app-navigation-entry-status">
                            <span class="status-dot-small" :class="getWebsiteStatusDotClass(website)"></span>
						</div>
                    </li>
                </ul>
							</div>
						</div>

        <!-- ========================================== -->
        <!-- RIGHT PANE: DETAIL VIEW                    -->
        <!-- ========================================== -->
        <div class="app-content-details" :class="{ 'mobile-hidden': isMobile && !selectedWebsite }">

            <!-- Empty State -->
            <div v-if="!selectedWebsite" class="empty-content">
                <div class="empty-content-icon"><Web :size="64" /></div>
                <h2 class="empty-content-title">{{ translate('domaincontrol', 'Bir website seçin') }}</h2>
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
                            <div class="avatar-xl website-avatar-xl" :style="{ backgroundColor: getWebsiteColor(selectedWebsite.name || selectedWebsite.software) }">
                                <Web :size="36" />
							</div>
                            <div class="crm-profile-text">
                                <h1 class="crm-client-name">{{ selectedWebsite.name || selectedWebsite.software || translate('domaincontrol', 'Website') }}</h1>
                                <div class="crm-client-meta">
                                    <span v-if="getClientName(selectedWebsite.clientId)" class="meta-item">
                                        <Account :size="14" />
                                        <span>{{ getClientName(selectedWebsite.clientId) }}</span>
                                    </span>
                                    <span v-if="selectedWebsite.software" class="meta-item">
                                        <span>{{ selectedWebsite.software }}</span>
                                    </span>
                                    <span class="meta-item">
                                        <span class="status-badge" :class="getWebsiteStatusClass(selectedWebsite)">
                                            {{ getWebsiteStatusText(selectedWebsite.status) }}
                                        </span>
                                    </span>
						</div>
					</div>
				</div>
                        <div class="crm-header-actions">
                            <NcButton @click="editWebsite(selectedWebsite)">
                                {{ translate('domaincontrol', 'Düzenle') }}
                            </NcButton>
                            <button class="icon-button danger" @click="confirmDelete(selectedWebsite)">
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
                            <button class="crm-tab" :class="{ active: activeTab === 'info' }" @click="activeTab = 'info'">
                                {{ translate('domaincontrol', 'Bilgiler') }}
						</button>
                            <button class="crm-tab" :class="{ active: activeTab === 'notes' }" @click="activeTab = 'notes'">
                                {{ translate('domaincontrol', 'Notlar') }}
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
                                    <CodeTags :size="24" style="color: var(--color-primary-element);" />
						</div>
                                <div class="stat-content">
                                    <div class="stat-label">{{ translate('domaincontrol', 'Yazılım') }}</div>
                                    <div class="stat-value">{{ selectedWebsite.software || '-' }}</div>
						</div>
					</div>
                            
					<div class="stat-card">
                                <div class="stat-icon" style="background-color: rgba(0, 130, 201, 0.1);">
                                    <Tag :size="24" style="color: var(--color-primary-element);" />
						</div>
                                <div class="stat-content">
                                    <div class="stat-label">{{ translate('domaincontrol', 'Versiyon') }}</div>
                                    <div class="stat-value">{{ selectedWebsite.version || '-' }}</div>
						</div>
					</div>

					<div class="stat-card">
                                <div class="stat-icon" :style="{ backgroundColor: getStatusIconBg(selectedWebsite.status) }">
                                    <CheckCircle :size="24" :style="{ color: getStatusIconColor(selectedWebsite.status) }" />
						</div>
                                <div class="stat-content">
                                    <div class="stat-label">{{ translate('domaincontrol', 'Durum') }}</div>
                                    <div class="stat-value">
								<span class="status-badge" :class="getWebsiteStatusClass(selectedWebsite)">
									{{ getWebsiteStatusText(selectedWebsite.status) }}
								</span>
							</div>
						</div>
					</div>

					<div class="stat-card">
                                <div class="stat-icon" style="background-color: rgba(0, 130, 201, 0.1);">
                                    <CalendarClock :size="24" style="color: var(--color-primary-element);" />
						</div>
                                <div class="stat-content">
                                    <div class="stat-label">{{ translate('domaincontrol', 'Kurulum Tarihi') }}</div>
                                    <div class="stat-value">{{ formatDate(selectedWebsite.installationDate || selectedWebsite.installation_date) }}</div>
						</div>
					</div>
				</div>

                        <!-- Quick Links -->
                        <div class="content-box" v-if="selectedWebsite.url || selectedWebsite.adminUrl">
                            <div class="box-header">
                                <h3>{{ translate('domaincontrol', 'Hızlı Bağlantılar') }}</h3>
                            </div>
                            <div class="box-body">
                                <div class="info-grid">
                                    <div class="info-group" v-if="selectedWebsite.url">
                                        <div class="info-label">{{ translate('domaincontrol', 'Website URL') }}</div>
                                        <div class="info-val">
                                            <a :href="selectedWebsite.url" target="_blank" rel="noopener noreferrer" class="info-link">
                                                <Web :size="14" />
                                                {{ selectedWebsite.url }}
                                            </a>
                                        </div>
                                    </div>
                                    <div class="info-group" v-if="selectedWebsite.adminUrl || selectedWebsite.admin_url">
                                        <div class="info-label">{{ translate('domaincontrol', 'Admin Panel URL') }}</div>
                                        <div class="info-val">
                                            <a :href="selectedWebsite.adminUrl || selectedWebsite.admin_url" target="_blank" rel="noopener noreferrer" class="info-link">
                                                <Lock :size="14" />
                                                {{ selectedWebsite.adminUrl || selectedWebsite.admin_url }}
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 2. BİLGİLER -->
                    <div v-if="activeTab === 'info'" class="tab-pane">
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
                                            <a v-if="selectedWebsite.clientId && getClientName(selectedWebsite.clientId)" href="#" class="info-link" @click.prevent="navigateToClient(selectedWebsite.clientId)">
                                                <Account :size="14" />
											{{ getClientName(selectedWebsite.clientId) }}
										</a>
										<span v-else>-</span>
                                        </div>
                                    </div>
                                    <div class="info-group">
                                        <div class="info-label">{{ translate('domaincontrol', 'Domain') }}</div>
                                        <div class="info-val">
                                            <a v-if="selectedWebsite.domainId && getDomainName(selectedWebsite.domainId)" href="#" class="info-link" @click.prevent="navigateToDomain(selectedWebsite.domainId)">
                                                <Web :size="14" />
											{{ getDomainName(selectedWebsite.domainId) }}
										</a>
										<span v-else>-</span>
                                        </div>
                                    </div>
                                    <div class="info-group">
                                        <div class="info-label">{{ translate('domaincontrol', 'Hosting') }}</div>
                                        <div class="info-val">
                                            <a v-if="selectedWebsite.hostingId && getHostingName(selectedWebsite.hostingId)" href="#" class="info-link" @click.prevent="navigateToHosting(selectedWebsite.hostingId)">
                                                <ServerNetwork :size="14" />
											{{ getHostingName(selectedWebsite.hostingId) }}
										</a>
										<span v-else>-</span>
                                        </div>
                                    </div>
                                    <div class="info-group" v-if="selectedWebsite.software">
                                        <div class="info-label">{{ translate('domaincontrol', 'Yazılım') }}</div>
                                        <div class="info-val">{{ selectedWebsite.software }}</div>
                                    </div>
                                    <div class="info-group" v-if="selectedWebsite.version">
                                        <div class="info-label">{{ translate('domaincontrol', 'Versiyon') }}</div>
                                        <div class="info-val">{{ selectedWebsite.version }}</div>
                                    </div>
                                    <div class="info-group" v-if="selectedWebsite.installationDate || selectedWebsite.installation_date">
                                        <div class="info-label">{{ translate('domaincontrol', 'Kurulum Tarihi') }}</div>
                                        <div class="info-val">{{ formatDate(selectedWebsite.installationDate || selectedWebsite.installation_date) }}</div>
                                    </div>
                                </div>
                            </div>
					</div>

                        <!-- Admin Panel Information -->
                        <div class="content-box" v-if="selectedWebsite.adminUrl || selectedWebsite.admin_url || selectedWebsite.adminNotes || selectedWebsite.admin_notes">
                            <div class="box-header">
                                <h3>
                                    <Lock :size="18" class="inline-icon" />
                                    {{ translate('domaincontrol', 'Admin Panel Bilgileri') }}
                                </h3>
                            </div>
                            <div class="box-body">
                                <div v-if="selectedWebsite.adminUrl || selectedWebsite.admin_url" class="info-group" style="margin-bottom: 12px;">
                                    <div class="info-label">URL</div>
                                    <div class="info-val">
                                        <a :href="selectedWebsite.adminUrl || selectedWebsite.admin_url" target="_blank" rel="noopener noreferrer" class="info-link">
                                            {{ selectedWebsite.adminUrl || selectedWebsite.admin_url }}
							</a>
                                    </div>
                                </div>
                                <pre class="code-block">{{ selectedWebsite.adminNotes || selectedWebsite.admin_notes || translate('domaincontrol', 'Admin panel bilgisi yok') }}</pre>
                            </div>
					</div>
				</div>

                    <!-- 3. NOTLAR -->
                    <div v-if="activeTab === 'notes'" class="tab-pane">
                        <div v-if="!selectedWebsite.notes" class="empty-box">
                            {{ translate('domaincontrol', 'Henüz not eklenmemiş') }}
				</div>
                        <div v-else class="rich-text-content" v-html="selectedWebsite.notes"></div>
                    </div>

                </div>
			</div>
		</div>
	</div>
</template>

<script>
import api from '../services/api'
import WebsiteModal from './WebsiteModal.vue'
import { NcButton } from '@nextcloud/vue'
import MaterialIcon from './MaterialIcon.vue'
// Icons
import Web from 'vue-material-design-icons/Web.vue'
import Magnify from 'vue-material-design-icons/Magnify.vue'
import Plus from 'vue-material-design-icons/Plus.vue'
import Refresh from 'vue-material-design-icons/Refresh.vue'
import ArrowLeft from 'vue-material-design-icons/ArrowLeft.vue'
import Delete from 'vue-material-design-icons/Delete.vue'
import Account from 'vue-material-design-icons/Account.vue'
import CodeTags from 'vue-material-design-icons/CodeTags.vue'
import Tag from 'vue-material-design-icons/Tag.vue'
import CheckCircle from 'vue-material-design-icons/CheckCircle.vue'
import CalendarClock from 'vue-material-design-icons/CalendarClock.vue'
import Lock from 'vue-material-design-icons/Lock.vue'
import ServerNetwork from 'vue-material-design-icons/ServerNetwork.vue'

export default {
	name: 'Websites',
	components: {
		WebsiteModal,
        NcButton,
        MaterialIcon,
        Web,
        Magnify,
        Plus,
        Refresh,
        ArrowLeft,
        Delete,
        Account,
        CodeTags,
        Tag,
        CheckCircle,
        CalendarClock,
        Lock,
        ServerNetwork,
	},
	data() {
		return {
			websites: [],
			clients: [],
			domains: [],
			hostings: [],
			selectedWebsite: null,
			loading: false,
            isMobile: window.innerWidth < 768,
            activeTab: 'overview',
			modalOpen: false,
			editingWebsite: null,
			searchQuery: '',
		}
	},
	computed: {
		filteredWebsites() {
			if (!this.searchQuery) return this.websites

			const query = this.searchQuery.toLowerCase()
			return this.websites.filter(website => {
				const name = (website.name || website.software || '').toLowerCase()
				const clientName = this.getClientName(website.clientId) || ''
				const domainName = this.getDomainName(website.domainId) || ''
				const software = (website.software || '').toLowerCase()
				return (
					name.includes(query) ||
					clientName.toLowerCase().includes(query) ||
					domainName.toLowerCase().includes(query) ||
					software.includes(query)
				)
			})
		},
	},
	mounted() {
		this.loadData()
        window.addEventListener('resize', this.handleResize)
	},
	beforeUnmount() {
        window.removeEventListener('resize', this.handleResize)
	},
	methods: {
        handleResize() {
            this.isMobile = window.innerWidth < 768
        },
		async loadData() {
			this.loading = true
			try {
				await Promise.all([
					this.loadWebsites(),
					this.loadClients(),
					this.loadDomains(),
					this.loadHostings(),
				])
			} catch (error) {
				console.error('Error loading data:', error)
			} finally {
				this.loading = false
			}
		},
		async loadWebsites() {
			try {
				const response = await api.websites.getAll()
				this.websites = response.data || []
			} catch (error) {
				console.error('Error loading websites:', error)
				this.websites = []
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
		async loadDomains() {
			try {
				const response = await api.domains.getAll()
				this.domains = response.data || []
			} catch (error) {
				console.error('Error loading domains:', error)
				this.domains = []
			}
		},
		async loadHostings() {
			try {
				const response = await api.hostings.getAll()
				this.hostings = response.data || []
			} catch (error) {
				console.error('Error loading hostings:', error)
				this.hostings = []
			}
		},
		selectWebsite(website) {
			this.selectedWebsite = website
            this.activeTab = 'overview'
		},
		backToList() {
			this.selectedWebsite = null
		},
		showAddModal() {
			this.editingWebsite = null
			this.modalOpen = true
		},
		editWebsite(website) {
			this.editingWebsite = website
			this.modalOpen = true
		},
		closeModal() {
			this.modalOpen = false
			this.editingWebsite = null
		},
		async handleWebsiteSaved() {
			await this.loadWebsites()
			if (this.selectedWebsite) {
                const updated = this.websites.find(w => w.id === this.selectedWebsite.id)
                if (updated) this.selectedWebsite = updated
			}
			this.closeModal()
		},
		async confirmDelete(website) {
            if (!confirm(this.translate('domaincontrol', 'Bu websiteyi silmek istediğinizden emin misiniz?'))) {
				return
			}
			try {
				await api.websites.delete(website.id)
				await this.loadWebsites()
				if (this.selectedWebsite && this.selectedWebsite.id === website.id) {
					this.backToList()
				}
			} catch (error) {
				console.error('Error deleting website:', error)
                alert(this.translate('domaincontrol', 'Website silinemedi'))
			}
		},
		getClientName(clientId) {
            if (!clientId) return null
            const client = this.clients.find(c => c.id == clientId)
            return client ? client.name : null
		},
		getDomainName(domainId) {
            if (!domainId) return null
            const domain = this.domains.find(d => d.id == domainId)
            return domain ? (domain.domainName || domain.domain_name) : null
		},
		getHostingName(hostingId) {
            if (!hostingId) return null
            const hosting = this.hostings.find(h => h.id == hostingId)
            return hosting ? `${hosting.provider || ''}${hosting.plan ? ' - ' + hosting.plan : ''}`.trim() : null
		},
		navigateToClient(clientId) {
            if (typeof window.DomainControl !== 'undefined' && window.DomainControl.selectClient) {
                window.DomainControl.selectClient(clientId)
			}
		},
		navigateToDomain(domainId) {
			if (typeof window.DomainControl !== 'undefined' && window.DomainControl.switchTab) {
				window.DomainControl.switchTab('domains')
				setTimeout(() => {
                    if (window.DomainControl.selectDomain) {
                        window.DomainControl.selectDomain(domainId)
                    }
				}, 100)
			}
		},
		navigateToHosting(hostingId) {
			if (typeof window.DomainControl !== 'undefined' && window.DomainControl.switchTab) {
				window.DomainControl.switchTab('hostings')
				setTimeout(() => {
                    const hosting = this.hostings.find(h => h.id === hostingId)
                    if (hosting && window.DomainControl.selectHosting) {
                        window.DomainControl.selectHosting(hostingId)
                    }
				}, 100)
			}
		},
		getWebsiteStatusClass(website) {
            const status = website.status || 'active'
            if (status === 'active') return 'status-ok'
            if (status === 'maintenance') return 'status-warning'
            if (status === 'development') return 'status-info'
            if (status === 'inactive') return 'status-expired'
            return 'status-ok'
        },
        getWebsiteStatusDotClass(website) {
            const status = website.status || 'active'
            if (status === 'active') return 'status-ok'
            if (status === 'maintenance') return 'status-warning'
            if (status === 'development') return 'status-info'
            if (status === 'inactive') return 'status-expired'
            return 'status-ok'
		},
		getWebsiteStatusText(status) {
			const statusTexts = {
                active: this.translate('domaincontrol', 'Aktif'),
                maintenance: this.translate('domaincontrol', 'Bakımda'),
                development: this.translate('domaincontrol', 'Geliştirmede'),
                inactive: this.translate('domaincontrol', 'Pasif'),
			}
			return statusTexts[status] || status
		},
        getStatusIconBg(status) {
            const statusMap = {
                active: 'rgba(70, 186, 97, 0.1)',
                maintenance: 'rgba(240, 173, 78, 0.1)',
                development: 'rgba(0, 130, 201, 0.1)',
                inactive: 'rgba(128, 128, 128, 0.1)',
            }
            return statusMap[status] || 'rgba(0, 130, 201, 0.1)'
        },
        getStatusIconColor(status) {
            const statusMap = {
                active: 'var(--color-element-success)',
                maintenance: 'var(--color-element-warning)',
                development: 'var(--color-primary-element)',
                inactive: 'var(--color-text-maxcontrast)',
            }
            return statusMap[status] || 'var(--color-primary-element)'
        },
        getWebsiteColor(name) {
            if (!name) return '#0082c9'
            const colors = ['#0082c9', '#46ba61', '#f0ad4e', '#e3322d', '#5bc0de', '#9b59b6', '#e67e22', '#3498db']
            let hash = 0
            for (let i = 0; i < name.length; i++) {
                hash = name.charCodeAt(i) + ((hash << 5) - hash)
            }
            return colors[Math.abs(hash) % colors.length]
		},
		formatDate(date) {
            if (!date) return '-'
            try {
                return new Date(date).toLocaleDateString('tr-TR', { year: 'numeric', month: 'short', day: 'numeric' })
            } catch (e) { 
                return date 
            }
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

.website-avatar {
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

.status-dot-small.status-info {
    background-color: var(--color-primary-element);
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

.website-avatar-xl {
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

/* Rich Text Content */
.rich-text-content {
	font-size: 14px;
	color: var(--color-main-text);
	line-height: 1.6;
    background: var(--color-main-background);
    padding: 20px;
    border: 1px solid var(--color-border);
    border-radius: var(--border-radius-large);
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

.status-badge.status-info {
    background-color: rgba(0, 130, 201, 0.15);
    color: var(--color-primary-element);
}

.status-badge.status-expired {
    background-color: var(--color-background-dark);
    color: var(--color-text-maxcontrast);
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
