<template>
    <transition name="fade">
        <div v-if="open" class="nc-modal-overlay" @click.self="closeModal" @keydown.esc="closeModal" tabindex="0">
            <div class="nc-modal-container" :class="{ 'modal-loading': saving }">
                
                <!-- HEADER -->
                <div class="nc-modal-header">
                    <h2 class="nc-modal-title">
                        {{ note ? translate('domaincontrol', 'Notu Düzenle') : translate('domaincontrol', 'Yeni Not Ekle') }}
                    </h2>
                    <button class="nc-modal-close-btn" @click="closeModal" :aria-label="translate('domaincontrol', 'Kapat')">
                        <Close :size="20" />
                    </button>
                </div>

                <!-- BODY -->
                <div class="nc-modal-body">
                    <form @submit.prevent="saveNote" id="noteForm">
                        <div class="form-group">
                            <label for="note-title">{{ translate('domaincontrol', 'Başlık') }}</label>
                            <input 
                                type="text" 
                                id="note-title" 
                                v-model="formData.title" 
                                class="nc-input" 
                                :placeholder="translate('domaincontrol', 'Not başlığı (Opsiyonel)')"
                                ref="titleInput"
                            />
                        </div>

                        <div class="form-group">
                            <label for="note-content">{{ translate('domaincontrol', 'Not Metni') }} *</label>
                            <RichTextEditor
                                v-model="formData.content"
                                :placeholder="translate('domaincontrol', 'Notunuzu buraya yazın...')"
                            />
                        </div>
                    </form>
                </div>

                <!-- FOOTER -->
                <div class="nc-modal-footer">
                    <button type="button" class="nc-button secondary" @click="closeModal">
                        {{ translate('domaincontrol', 'İptal') }}
                    </button>
                    <button type="submit" form="noteForm" class="nc-button primary" :disabled="saving || !formData.content">
                        <span v-if="saving" class="spin-loader"></span>
                        {{ saving ? translate('domaincontrol', 'Kaydediliyor...') : translate('domaincontrol', 'Kaydet') }}
                    </button>
                </div>

            </div>
        </div>
    </transition>
</template>

<script>
import api from '../services/api'
import RichTextEditor from './RichTextEditor.vue'
import Close from 'vue-material-design-icons/Close.vue'

export default {
    name: 'ClientNoteModal',
    components: {
        RichTextEditor,
        Close
    },
    props: {
        open: { type: Boolean, default: false },
        note: { type: Object, default: null },
        clientId: { type: Number, default: null }
    },
    data() {
        return {
            formData: {
                title: '',
                content: ''
            },
            saving: false
        }
    },
    watch: {
        open(val) {
            if (val) {
                this.resetForm()
                if (this.note) {
                    this.formData = {
                        title: this.note.title || '',
                        content: this.note.content || ''
                    }
                }
                this.$nextTick(() => {
                    if (this.$refs.titleInput) this.$refs.titleInput.focus()
                })
            }
        }
    },
    methods: {
        translate(app, text) { 
            return (window.t && window.t(app, text)) || text 
        },
        
        resetForm() {
            this.formData = { title: '', content: '' }
            this.saving = false
        },

        closeModal() {
            this.$emit('close')
        },

        async saveNote() {
            if (!this.clientId) return
            
            this.saving = true
            try {
                if (this.note) {
                    await api.clients.byClient.notes.update(this.clientId, this.note.id, this.formData)
                } else {
                    await api.clients.byClient.notes.create(this.clientId, this.formData)
                }
                this.$emit('saved')
                this.closeModal()
            } catch (error) {
                console.error('Error saving note:', error)
                alert(this.translate('domaincontrol', 'Hata oluştu: ') + (error.message || 'Unknown error'))
            } finally {
                this.saving = false
            }
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
    max-width: 700px;
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

/* FORM STYLES */
.form-group { margin-bottom: 20px; }
.form-group label {
    display: block; margin-bottom: 8px;
    font-weight: 600; font-size: 13px;
    color: var(--color-text-maxcontrast);
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

.nc-input:focus {
    border-color: var(--color-primary-element);
    box-shadow: 0 0 0 2px var(--color-primary-element-light);
    outline: none;
}

/* BUTTONS */
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

.spin-loader {
    width: 14px; height: 14px; border: 2px solid rgba(255,255,255,0.3);
    border-top-color: white; border-radius: 50%; animation: spin 1s linear infinite;
}

@keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
@keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
@keyframes slideUp { from { opacity: 0; transform: translateY(20px) scale(0.95); } to { opacity: 1; transform: translateY(0) scale(1); } }
</style>

