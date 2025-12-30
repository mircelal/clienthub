<template>
    <transition name="fade">
        <div v-if="open" class="nc-modal-overlay" @click.self="closeModal" @keydown.esc="closeModal" tabindex="0">
            <div class="nc-modal-container" :class="{ 'modal-loading': loading }">
                
                <!-- HEADER -->
                <div class="nc-modal-header">
                    <h2 class="nc-modal-title">
                        {{ view === 'form' 
                            ? (client ? translate('domaincontrol', 'Müşteriyi Düzenle') : translate('domaincontrol', 'Yeni Müşteri Ekle')) 
                            : translate('domaincontrol', 'Kişilerden İçe Aktar') 
                        }}
                    </h2>
                    <button class="nc-modal-close-btn" @click="closeModal" :aria-label="translate('domaincontrol', 'Kapat')">
                        <Close :size="20" />
                    </button>
                </div>

                <!-- BODY -->
                <div class="nc-modal-body">
                    
                    <!-- VIEW: FORM -->
                    <div v-if="view === 'form'" class="form-view">
                        
                        <!-- Import Action -->
                        <div class="import-section" v-if="!client">
                            <button type="button" class="nc-button secondary full-width" @click="view = 'contacts'">
                                <AccountBoxOutline :size="20" class="btn-icon" />
                                {{ translate('domaincontrol', 'Nextcloud Kişilerinden Seç') }}
                            </button>
                            <div class="divider">
                                <span>{{ translate('domaincontrol', 'veya manuel girin') }}</span>
                            </div>
                        </div>

                        <form @submit.prevent="saveClient" id="clientForm">
                            <div class="form-grid">
                                <!-- Avatar Preview (Visual only) -->
                                <div class="avatar-section">
                                    <div class="avatar-preview" :style="{ backgroundColor: getAvatarColor(formData.name) }">
                                        {{ getInitials(formData.name) }}
                                    </div>
                                </div>

                                <div class="inputs-section">
                                    <div class="form-group">
                                        <label for="client-name">{{ translate('domaincontrol', 'Ad Soyad / Firma Adı') }} *</label>
                                        <div class="input-wrapper">
                                            <input type="text" id="client-name" v-model="formData.name" required class="nc-input" :placeholder="translate('domaincontrol', 'Örn: Ahmet Yılmaz veya ABC Ltd. Şti.')" ref="nameInput" />
                                        </div>
                                    </div>

                                    <div class="form-row">
                                        <div class="form-group half">
                                            <label for="client-email">{{ translate('domaincontrol', 'E-posta') }}</label>
                                            <div class="input-wrapper">
                                                <Email :size="16" class="input-icon" />
                                                <input type="email" id="client-email" v-model="formData.email" class="nc-input with-icon" placeholder="mail@example.com" />
                                            </div>
                                        </div>
                                        <div class="form-group half">
                                            <label for="client-phone">{{ translate('domaincontrol', 'Telefon') }}</label>
                                            <div class="input-wrapper">
                                                <Phone :size="16" class="input-icon" />
                                                <input type="tel" id="client-phone" v-model="formData.phone" class="nc-input with-icon" placeholder="+90 555 ..." />
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label for="client-company">{{ translate('domaincontrol', 'Şirket / Ünvan') }}</label>
                                        <div class="input-wrapper">
                                            <Domain :size="16" class="input-icon" />
                                            <input type="text" id="client-company" v-model="formData.company" class="nc-input with-icon" :placeholder="translate('domaincontrol', 'Firma adı (Opsiyonel)')" />
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label for="client-address">{{ translate('domaincontrol', 'Adres') }}</label>
                                        <textarea id="client-address" v-model="formData.address" class="nc-input textarea" rows="2"></textarea>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- VIEW: CONTACTS SELECTION -->
                    <div v-else class="contacts-view">
                        <div class="contacts-search-bar">
                            <Magnify :size="20" class="search-icon" />
                            <input 
                                type="text" 
                                v-model="contactsSearchQuery" 
                                class="nc-input search-input" 
                                :placeholder="translate('domaincontrol', 'Kişi ara...')"
                                ref="contactSearchInput"
                                @input="filterContacts"
                            />
                        </div>

                        <div class="contacts-list-container">
                            <div v-if="contactsLoading" class="loading-state">
                                <Refresh :size="32" class="spin-animation" />
                                <p>{{ translate('domaincontrol', 'Kişiler yükleniyor...') }}</p>
                            </div>
                            
                            <div v-else-if="filteredContacts.length === 0" class="empty-state">
                                <p>{{ translate('domaincontrol', 'Kişi bulunamadı.') }}</p>
                            </div>

                            <ul v-else class="contacts-list">
                                <li v-for="contact in filteredContacts" :key="contact.id" class="contact-item" @click="selectContact(contact)">
                                    <div class="contact-avatar" :style="{ backgroundColor: getAvatarColor(contact.name) }">
                                        {{ getInitials(contact.name) }}
                                    </div>
                                    <div class="contact-info">
                                        <div class="contact-name">{{ contact.name }}</div>
                                        <div class="contact-meta">
                                            <span v-if="contact.email">{{ contact.email }}</span>
                                            <span v-if="contact.email && contact.phone" class="separator">•</span>
                                            <span v-if="contact.phone">{{ contact.phone }}</span>
                                        </div>
                                    </div>
                                    <div class="contact-action">
                                        <Plus :size="20" />
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>

                </div>

                <!-- FOOTER -->
                <div class="nc-modal-footer">
                    <template v-if="view === 'form'">
                        <button type="button" class="nc-button secondary" @click="closeModal">
                            {{ translate('domaincontrol', 'İptal') }}
                        </button>
                        <button type="submit" form="clientForm" class="nc-button primary" :disabled="saving || !formData.name">
                            <span v-if="saving" class="spin-loader"></span>
                            {{ saving ? translate('domaincontrol', 'Kaydediliyor...') : translate('domaincontrol', 'Kaydet') }}
                        </button>
                    </template>
                    <template v-else>
                        <button type="button" class="nc-button secondary" @click="view = 'form'">
                            {{ translate('domaincontrol', 'Geri Dön') }}
                        </button>
                    </template>
                </div>

            </div>
        </div>
    </transition>
</template>

<script>
import api from '../services/api'
// Icons
import Close from 'vue-material-design-icons/Close.vue'
import AccountBoxOutline from 'vue-material-design-icons/AccountBoxOutline.vue'
import Email from 'vue-material-design-icons/Email.vue'
import Phone from 'vue-material-design-icons/Phone.vue'
import Domain from 'vue-material-design-icons/Domain.vue'
import Magnify from 'vue-material-design-icons/Magnify.vue'
import Refresh from 'vue-material-design-icons/Refresh.vue'
import Plus from 'vue-material-design-icons/Plus.vue'

export default {
    name: 'ClientModal',
    components: {
        Close, AccountBoxOutline, Email, Phone, Domain, Magnify, Refresh, Plus
    },
    props: {
        open: { type: Boolean, default: false },
        client: { type: Object, default: null }, // If null, adding new client
    },
    data() {
        return {
            view: 'form', // 'form' or 'contacts'
            formData: {
                name: '',
                email: '',
                phone: '',
                company: '',
                address: '',
                notes: ''
            },
            saving: false,
            // Contacts Logic
            contacts: [],
            contactsSearchQuery: '',
            contactsLoading: false,
            contactsLoaded: false // Cache flag
        }
    },
    computed: {
        filteredContacts() {
            if (!this.contactsSearchQuery) return this.contacts
            const q = this.contactsSearchQuery.toLowerCase()
            return this.contacts.filter(c => 
                c.name?.toLowerCase().includes(q) || 
                c.email?.toLowerCase().includes(q)
            )
        }
    },
    watch: {
        open(val) {
            if (val) {
                this.resetForm()
                this.view = 'form'
                if (this.client) {
                    this.formData = { ...this.client }
                }
                // Focus name input on open
                this.$nextTick(() => {
                    if (this.$refs.nameInput) this.$refs.nameInput.focus()
                })
            }
        },
        view(val) {
            if (val === 'contacts' && !this.contactsLoaded) {
                this.loadContacts()
                this.$nextTick(() => {
                    if (this.$refs.contactSearchInput) this.$refs.contactSearchInput.focus()
                })
            }
        }
    },
    methods: {
        translate(app, text) { return (window.t && window.t(app, text)) || text },
        
        resetForm() {
            this.formData = { name: '', email: '', phone: '', company: '', address: '', notes: '' }
            this.saving = false
        },

        closeModal() {
            this.$emit('close')
        },

        getInitials(name) {
            if (!name) return '?'
            return name.substring(0, 2).toUpperCase()
        },
        getAvatarColor(name) {
            if (!name) return '#ccc'
            let hash = 0
            for (let i = 0; i < name.length; i++) hash = name.charCodeAt(i) + ((hash << 5) - hash)
            return `hsl(${hash % 360}, 60%, 45%)`
        },

        async saveClient() {
            this.saving = true
            try {
                if (this.client) {
                    await api.clients.update(this.client.id, this.formData)
                } else {
                    await api.clients.create(this.formData)
                }
                this.$emit('saved')
                this.closeModal()
            } catch (error) {
                console.error(error)
                alert(this.translate('domaincontrol', 'Hata oluştu: ') + (error.message || 'Unknown error'))
            } finally {
                this.saving = false
            }
        },

        // --- Contacts Logic ---
        async loadContacts() {
            this.contactsLoading = true
            try {
                // 1. Try App API if available
                // const res = await api.contacts.getAll() 
                // this.contacts = res.data

                // 2. Fallback: DAV (Simplified for this component)
                // In a real Nextcloud app, you usually use @nextcloud/cdav-library or fetch from internal API
                await this.mockLoadContactsFromDAV() 
                
                this.contactsLoaded = true
            } catch (e) {
                console.error("Contacts load error", e)
            } finally {
                this.contactsLoading = false
            }
        },

        async mockLoadContactsFromDAV() {
            // Simulating DAV fetch for demonstration. 
            // In production, replace this with actual DAV PROPFIND request to /remote.php/dav/addressbooks/users/USER/contacts/
            return new Promise(resolve => {
                setTimeout(() => {
                    this.contacts = [
                        { id: '1', name: 'Ali Veli', email: 'ali@example.com', phone: '05551112233', company: 'Teknoloji A.Ş.' },
                        { id: '2', name: 'Ayşe Yılmaz', email: 'ayse@test.com', phone: '05324445566', company: '' },
                        { id: '3', name: 'Mehmet Öz', email: 'mehmet@domain.com', phone: '', company: 'İnşaat Ltd.' },
                        { id: '4', name: 'Zeynep Kaya', email: 'zeynep@kargo.com', phone: '02123334455', company: 'Kargo' },
                    ]
                    resolve()
                }, 800)
            })
        },

        selectContact(contact) {
            this.formData.name = contact.name || ''
            this.formData.email = contact.email || ''
            this.formData.phone = contact.phone || ''
            this.formData.company = contact.company || ''
            this.view = 'form'
        }
    }
}
</script>

<style scoped>
/* NEXTCLOUD MODAL STYLES */
.nc-modal-overlay {
    position: fixed;
    top: 0; left: 0; right: 0; bottom: 0;
    background-color: rgba(0, 0, 0, 0.4);
    backdrop-filter: blur(2px);
    z-index: 10000;
    display: flex;
    align-items: center;
    justify-content: center;
    animation: fadeIn 0.2s ease-out;
}

.nc-modal-container {
    background-color: var(--color-main-background);
    color: var(--color-main-text);
    width: 100%;
    max-width: 550px;
    max-height: 90vh;
    border-radius: var(--border-radius-large);
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
    display: flex;
    flex-direction: column;
    overflow: hidden;
    animation: slideUp 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    position: relative;
}

/* HEADER */
.nc-modal-header {
    padding: 18px 24px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid var(--color-border);
    flex-shrink: 0;
}
.nc-modal-title {
    margin: 0;
    font-size: 18px;
    font-weight: 700;
    color: var(--color-main-text);
}
.nc-modal-close-btn {
    background: none;
    border: none;
    color: var(--color-text-maxcontrast);
    cursor: pointer;
    border-radius: 50%;
    width: 36px; height: 36px;
    display: flex; align-items: center; justify-content: center;
    transition: background 0.15s;
}
.nc-modal-close-btn:hover { background-color: var(--color-background-hover); }

/* BODY */
.nc-modal-body {
    padding: 24px;
    overflow-y: auto;
    flex: 1;
}

/* FOOTER */
.nc-modal-footer {
    padding: 16px 24px;
    border-top: 1px solid var(--color-border);
    display: flex;
    justify-content: flex-end;
    gap: 12px;
    background-color: var(--color-background-dark);
    flex-shrink: 0;
}

/* --- FORM STYLES --- */
.import-section {
    margin-bottom: 24px;
}
.divider {
    display: flex;
    align-items: center;
    text-align: center;
    margin-top: 15px;
    color: var(--color-text-maxcontrast);
    font-size: 12px;
}
.divider::before, .divider::after {
    content: ''; flex: 1; border-bottom: 1px solid var(--color-border);
}
.divider span { padding: 0 10px; opacity: 0.7; }

.form-grid {
    display: flex;
    gap: 20px;
}
.avatar-section { flex-shrink: 0; }
.avatar-preview {
    width: 64px; height: 64px; border-radius: 50%;
    color: white; font-size: 24px; font-weight: bold;
    display: flex; align-items: center; justify-content: center;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}
.inputs-section { flex: 1; }

.form-group { margin-bottom: 16px; }
.form-group label {
    display: block; margin-bottom: 6px;
    font-weight: 600; font-size: 13px;
    color: var(--color-text-maxcontrast);
}
.form-row { display: flex; gap: 15px; }
.half { flex: 1; }

.input-wrapper { position: relative; }
.input-icon {
    position: absolute; left: 10px; top: 50%;
    transform: translateY(-50%);
    color: var(--color-text-maxcontrast); opacity: 0.7;
    pointer-events: none;
}

.nc-input {
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
.nc-input.with-icon { padding-left: 36px; }
.nc-input.textarea { resize: vertical; min-height: 80px; }

.nc-input:focus {
    border-color: var(--color-primary-element);
    box-shadow: 0 0 0 2px var(--color-primary-element-light);
    outline: none;
}

/* --- BUTTONS --- */
.nc-button {
    padding: 10px 20px;
    border-radius: var(--border-radius-pill);
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    border: none;
    display: flex; align-items: center; justify-content: center; gap: 8px;
    transition: all 0.2s;
}
.nc-button.full-width { width: 100%; }
.nc-button.primary {
    background-color: var(--color-primary-element);
    color: var(--color-primary-element-text);
}
.nc-button.primary:hover:not(:disabled) { opacity: 0.9; }
.nc-button.primary:disabled { opacity: 0.6; cursor: not-allowed; }

.nc-button.secondary {
    background-color: transparent;
    border: 1px solid var(--color-border);
    color: var(--color-main-text);
}
.nc-button.secondary:hover { background-color: var(--color-background-hover); }

/* --- CONTACTS VIEW --- */
.contacts-view {
    display: flex; flex-direction: column; height: 400px;
}
.contacts-search-bar {
    position: relative; margin-bottom: 15px; flex-shrink: 0;
}
.search-icon {
    position: absolute; left: 10px; top: 50%; transform: translateY(-50%);
    color: var(--color-text-maxcontrast); opacity: 0.5;
}
.search-input { padding-left: 36px; }

.contacts-list-container {
    flex: 1; overflow-y: auto;
    border: 1px solid var(--color-border);
    border-radius: var(--border-radius);
}

.contacts-list { list-style: none; padding: 0; margin: 0; }
.contact-item {
    display: flex; align-items: center; gap: 12px;
    padding: 12px 15px;
    border-bottom: 1px solid var(--color-border);
    cursor: pointer;
    transition: background 0.15s;
}
.contact-item:hover { background-color: var(--color-background-hover); }
.contact-avatar {
    width: 36px; height: 36px; border-radius: 50%;
    color: white; font-size: 14px; display: flex; align-items: center; justify-content: center;
}
.contact-info { flex: 1; }
.contact-name { font-weight: 600; font-size: 14px; }
.contact-meta { font-size: 12px; color: var(--color-text-maxcontrast); display: flex; gap: 5px; }
.separator { opacity: 0.5; }
.contact-action {
    color: var(--color-primary-element); opacity: 0; transform: translateX(10px); transition: all 0.2s;
}
.contact-item:hover .contact-action { opacity: 1; transform: translateX(0); }

.loading-state, .empty-state {
    display: flex; flex-direction: column; align-items: center; justify-content: center;
    height: 100%; color: var(--color-text-maxcontrast); gap: 10px;
}
.spin-animation { animation: spin 1s linear infinite; }
.spin-loader {
    width: 14px; height: 14px; border: 2px solid rgba(255,255,255,0.3);
    border-top-color: white; border-radius: 50%; animation: spin 1s linear infinite;
}

@keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
@keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
@keyframes slideUp { from { opacity: 0; transform: translateY(20px) scale(0.95); } to { opacity: 1; transform: translateY(0) scale(1); } }
</style>