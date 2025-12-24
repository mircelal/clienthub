<template>
    <div v-if="isOpen" class="nc-modal-overlay" @click.self="closeModal">
        <div class="nc-modal-content nc-modal-large">
            
            <!-- Header -->
            <div class="nc-modal-header">
                <div class="header-title-container">
                    <Pencil v-if="editingDomain" :size="20" class="header-icon" />
                    <Plus v-else :size="20" class="header-icon" />
                    <h3 class="nc-modal-title">
                        {{ editingDomain ? translate('domaincontrol', 'Edit Domain') : translate('domaincontrol', 'Add Domain') }}
                    </h3>
                </div>
                <button class="nc-modal-close" @click="closeModal" :title="translate('domaincontrol', 'Close')">
                    <Close :size="24" />
                </button>
            </div>

            <!-- Body -->
            <div class="nc-modal-body">
                <form @submit.prevent="saveDomain" id="domain-form">
                    <input type="hidden" v-model="formData.id" />
                    
                    <div class="nc-form-grid">
                        <!-- Client & Domain Name -->
                        <div class="nc-form-group">
                            <label for="domain-client-id">
                                {{ translate('domaincontrol', 'Client') }} <span class="required">*</span>
                            </label>
                            <select
                                id="domain-client-id"
                                v-model="formData.clientId"
                                required
                                class="nc-select"
                            >
                                <option value="">{{ translate('domaincontrol', 'Select Client') }}</option>
                                <option
                                    v-for="client in clients"
                                    :key="client.id"
                                    :value="client.id"
                                >
                                    {{ client.name }}
                                </option>
                            </select>
                        </div>

                        <div class="nc-form-group">
                            <label for="domain-name">
                                {{ translate('domaincontrol', 'Domain Name') }} <span class="required">*</span>
                            </label>
                            <input
                                type="text"
                                id="domain-name"
                                v-model="formData.domainName"
                                required
                                class="nc-input"
                                placeholder="example.com"
                            />
                        </div>

                        <!-- Registrar & Interval -->
                        <div class="nc-form-group">
                            <label for="domain-registrar">{{ translate('domaincontrol', 'Registrar') }}</label>
                            <input
                                type="text"
                                id="domain-registrar"
                                v-model="formData.registrar"
                                class="nc-input"
                                placeholder="GoDaddy, Namecheap..."
                            />
                        </div>

                        <div class="nc-form-group">
                            <label for="domain-renewal-interval">
                                {{ translate('domaincontrol', 'Renewal Interval') }}
                            </label>
                            <select
                                id="domain-renewal-interval"
                                v-model="formData.renewalInterval"
                                class="nc-select"
                            >
                                <option value="1">1 {{ translate('domaincontrol', 'Year') }}</option>
                                <option value="2">2 {{ translate('domaincontrol', 'Year') }}</option>
                                <option value="3">3 {{ translate('domaincontrol', 'Year') }}</option>
                                <option value="5">5 {{ translate('domaincontrol', 'Year') }}</option>
                                <option value="10">10 {{ translate('domaincontrol', 'Year') }}</option>
                            </select>
                        </div>

                        <!-- Dates -->
                        <div class="nc-form-group">
                            <label for="domain-registration-date">{{ translate('domaincontrol', 'Registration Date') }}</label>
                            <input
                                type="date"
                                id="domain-registration-date"
                                v-model="formData.registrationDate"
                                class="nc-input"
                            />
                        </div>

                        <div class="nc-form-group">
                            <label for="domain-expiration-date">{{ translate('domaincontrol', 'Expiration Date') }}</label>
                            <input
                                type="date"
                                id="domain-expiration-date"
                                v-model="formData.expirationDate"
                                class="nc-input"
                            />
                        </div>

                        <!-- Price & Currency -->
                        <div class="nc-form-group">
                            <label for="domain-price">{{ translate('domaincontrol', 'Price') }}</label>
                            <input
                                type="number"
                                id="domain-price"
                                v-model="formData.price"
                                step="0.01"
                                class="nc-input font-mono"
                                placeholder="0.00"
                            />
                        </div>

                        <div class="nc-form-group">
                            <label for="domain-currency">{{ translate('domaincontrol', 'Currency') }}</label>
                            <select
                                id="domain-currency"
                                v-model="formData.currency"
                                class="nc-select"
                            >
                                <option value="USD">$ USD</option>
                                <option value="EUR">€ EUR</option>
                                <option value="TRY">₺ TRY</option>
                                <option value="AZN">₼ AZN</option>
                                <option value="GBP">£ GBP</option>
                                <option value="RUB">₽ RUB</option>
                            </select>
                        </div>
                    </div>

                    <!-- Full Width Fields -->
                    <div class="nc-form-group mt-16">
                        <label for="domain-panel-notes">{{ translate('domaincontrol', 'Panel Access Information') }}</label>
                        <textarea
                            id="domain-panel-notes"
                            v-model="formData.panelNotes"
                            class="nc-textarea font-mono"
                            rows="3"
                            :placeholder="translate('domaincontrol', 'Domain panel URL, username, password notes...')"
                        ></textarea>
                    </div>

                    <div class="nc-form-group mt-16">
                        <label>{{ translate('domaincontrol', 'General Notes') }}</label>
                        <div class="editor-wrapper">
                            <RichTextEditor
                                v-model="formData.notes"
                                :placeholder="translate('domaincontrol', 'Other notes...')"
                            />
                        </div>
                    </div>
                </form>
            </div>

            <!-- Footer -->
            <div class="nc-modal-footer">
                <NcButton type="tertiary" @click="closeModal" :disabled="saving">
                    {{ translate('domaincontrol', 'Cancel') }}
                </NcButton>
                <NcButton type="primary" @click="saveDomain" :disabled="saving">
                    <template #icon>
                        <ContentSave v-if="!saving" :size="20" />
                        <Refresh v-else :size="20" class="spin-animation" />
                    </template>
                    {{ saving ? translate('domaincontrol', 'Saving...') : translate('domaincontrol', 'Save') }}
                </NcButton>
            </div>
        </div>
    </div>
</template>

<script>
import api from '../services/api'
import RichTextEditor from './RichTextEditor.vue'
import { NcButton } from '@nextcloud/vue'

// Icons
import Close from 'vue-material-design-icons/Close.vue'
import Plus from 'vue-material-design-icons/Plus.vue'
import Pencil from 'vue-material-design-icons/Pencil.vue'
import ContentSave from 'vue-material-design-icons/ContentSave.vue'
import Refresh from 'vue-material-design-icons/Refresh.vue'

export default {
    name: 'DomainModal',
    components: {
        RichTextEditor,
        NcButton,
        Close,
        Plus,
        Pencil,
        ContentSave,
        Refresh
    },
    props: {
        open: {
            type: Boolean,
            default: false,
        },
        domain: {
            type: Object,
            default: null,
        },
        clients: {
            type: Array,
            default: () => [],
        },
    },
    data() {
        return {
            formData: {
                id: null,
                clientId: '',
                domainName: '',
                registrar: '',
                registrationDate: '',
                expirationDate: '',
                price: '',
                currency: 'USD',
                renewalInterval: '1',
                panelNotes: '',
                notes: '',
            },
            saving: false,
        }
    },
    computed: {
        isOpen() {
            return this.open
        },
        editingDomain() {
            return this.domain
        },
    },
    watch: {
        domain: {
            handler(newDomain) {
                if (newDomain) {
                    this.formData = {
                        id: newDomain.id || null,
                        clientId: newDomain.clientId || '',
                        domainName: newDomain.domainName || '',
                        registrar: newDomain.registrar || '',
                        registrationDate: newDomain.registrationDate ? newDomain.registrationDate.split(' ')[0] : '',
                        expirationDate: newDomain.expirationDate ? newDomain.expirationDate.split(' ')[0] : '',
                        price: newDomain.price || '',
                        currency: newDomain.currency || 'USD',
                        renewalInterval: newDomain.renewalInterval || '1',
                        panelNotes: newDomain.panelNotes || '',
                        notes: newDomain.notes || '',
                    }
                } else {
                    this.resetForm()
                }
            },
            immediate: true,
        },
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
        resetForm() {
            this.formData = {
                id: null,
                clientId: '',
                domainName: '',
                registrar: '',
                registrationDate: '',
                expirationDate: '',
                price: '',
                currency: 'USD',
                renewalInterval: '1',
                panelNotes: '',
                notes: '',
            }
        },
        closeModal() {
            this.$emit('close')
            this.resetForm()
        },
        async saveDomain() {
            if (!this.formData.domainName || !this.formData.clientId) {
                alert(this.translate('domaincontrol', 'Domain name and client are required'))
                return
            }

            this.saving = true
            try {
                const data = {
                    clientId: this.formData.clientId,
                    domainName: this.formData.domainName,
                    registrar: this.formData.registrar || '',
                    registrationDate: this.formData.registrationDate || '',
                    expirationDate: this.formData.expirationDate || '',
                    price: this.formData.price || '',
                    currency: this.formData.currency,
                    renewalInterval: this.formData.renewalInterval,
                    panelNotes: this.formData.panelNotes || '',
                    notes: this.formData.notes || '',
                }

                if (this.formData.id) {
                    await api.domains.update(this.formData.id, data)
                } else {
                    await api.domains.create(data)
                }

                this.$emit('saved')
            } catch (error) {
                console.error('Error saving domain:', error)
                alert(this.translate('domaincontrol', 'Error saving domain'))
            } finally {
                this.saving = false
            }
        },
    },
}
</script>

<style scoped>
/* Modal Overlay Standard */
.nc-modal-overlay {
    position: fixed;
    top: 0; left: 0; right: 0; bottom: 0;
    background: rgba(0, 0, 0, 0.4);
    backdrop-filter: blur(4px);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 10000;
}

/* Modal Content Container */
.nc-modal-content {
    background: var(--color-main-background);
    border-radius: var(--border-radius-large);
    width: 90%;
    max-width: 600px;
    max-height: 85vh;
    overflow: hidden;
    box-shadow: 0 20px 50px rgba(0, 0, 0, 0.3);
    display: flex;
    flex-direction: column;
}

.nc-modal-large { /* max-width: 800px; */ }

/* Header */
.nc-modal-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 24px;
    border-bottom: 1px solid var(--color-border);
}

.header-title-container {
    display: flex;
    align-items: center;
    gap: 12px;
    color: var(--color-main-text);
}

.header-icon { color: var(--color-text-maxcontrast); }

.nc-modal-title {
    margin: 0;
    font-size: 20px;
    font-weight: 700;
}

.nc-modal-close {
    background: transparent;
    border: none;
    color: var(--color-text-maxcontrast);
    cursor: pointer;
    padding: 8px;
    border-radius: 50%;
    display: flex;
    transition: background 0.2s;
}
.nc-modal-close:hover { background: var(--color-background-dark); color: var(--color-main-text); }

/* Body */
.nc-modal-body {
    padding: 24px;
    overflow-y: auto;
}

/* Form Layout */
.nc-form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
}

.nc-form-group {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.nc-form-group label {
    font-size: 13px;
    font-weight: 600;
    color: var(--color-main-text);
}

.required { color: var(--color-element-error); margin-left: 2px; }

/* Input Elements */
.nc-input, .nc-select, .nc-textarea {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid var(--color-border);
    border-radius: var(--border-radius);
    background: var(--color-main-background);
    color: var(--color-main-text);
    font-size: 14px;
    transition: border-color 0.2s;
    box-sizing: border-box;
}

.nc-input:focus, .nc-select:focus, .nc-textarea:focus {
    border-color: var(--color-primary-element-element);
    outline: none;
    box-shadow: 0 0 0 2px rgba(0, 130, 201, 0.1);
}

.nc-textarea {
    min-height: 80px;
    resize: vertical;
}

.editor-wrapper {
    border: 1px solid var(--color-border);
    border-radius: var(--border-radius);
    overflow: hidden;
}

/* Footer */
.nc-modal-footer {
    padding: 20px 24px;
    background: var(--color-background-hover);
    border-top: 1px solid var(--color-border);
    display: flex;
    justify-content: flex-end;
    gap: 12px;
}

/* Utils */
.mt-16 { margin-top: 16px; }
.font-mono { font-family: monospace; }
.spin-animation { animation: spin 1s linear infinite; }
@keyframes spin { 100% { transform: rotate(360deg); } }

/* Responsive */
@media (max-width: 650px) {
    .nc-form-grid { grid-template-columns: 1fr; }
    .nc-modal-content { height: 95vh; max-height: none; }
}
</style>