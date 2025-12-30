<template>
    <div class="client-projects-tab">
        <div class="pane-actions">
            <NcButton type="primary" @click="$emit('add-project')">
                <template #icon><Plus :size="20" /></template>
                {{ translate('domaincontrol', 'Proje Ekle') }}
            </NcButton>
        </div>
        <div v-if="projects.length === 0" class="empty-box full-width">
            {{ translate('domaincontrol', 'Henüz proje yok') }}
        </div>
        <div v-else class="nc-list-container">
            <div 
                v-for="project in projects" 
                :key="project.id" 
                class="nc-list-item nc-list-item--clickable" 
                @click="$emit('navigate', project.id)"
            >
                <div class="nc-list-item__icon">
                    <BriefcaseOutline :size="20" />
                </div>
                <div class="nc-list-item__content">
                    <div class="nc-list-item__header">
                        <div class="nc-list-item__title-row">
                            <span class="nc-list-item__title">{{ project.name }}</span>
                            <span class="project-status-badge" :class="project.status?.toLowerCase()">{{ getProjectStatusLabel(project.status) }}</span>
                        </div>
                    </div>
                    <div class="nc-list-item__body">
                        <div class="project-progress-section">
                            <div class="progress-header">
                                <span class="progress-label">{{ translate('domaincontrol', 'İlerleme') }}</span>
                                <span class="progress-percentage">{{ getProjectProgress(project) }}%</span>
                            </div>
                            <NcProgressBar :value="getProjectProgress(project)" :max="100" class="project-progress-bar" />
                        </div>
                    </div>
                    <div class="nc-list-item__footer">
                        <div class="project-meta-row">
                            <span v-if="project.deadline" class="project-meta-item">
                                <Calendar :size="16" />
                                <span class="meta-text">{{ formatDate(project.deadline) }}</span>
                            </span>
                            <span v-if="project.budget" class="project-meta-item">
                                <CashCheck :size="16" />
                                <span class="meta-text">{{ formatCurrency(project.budget, project.currency) }}</span>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { NcButton, NcProgressBar } from '@nextcloud/vue'
import BriefcaseOutline from 'vue-material-design-icons/BriefcaseOutline.vue'
import Calendar from 'vue-material-design-icons/Calendar.vue'
import CashCheck from 'vue-material-design-icons/CashCheck.vue'
import Plus from 'vue-material-design-icons/Plus.vue'

export default {
    name: 'ClientProjects',
    components: {
        NcButton,
        NcProgressBar,
        BriefcaseOutline,
        Calendar,
        CashCheck,
        Plus
    },
    props: {
        projects: {
            type: Array,
            default: () => []
        }
    },
    emits: ['add-project', 'navigate'],
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
        formatCurrency(val, currency = 'USD') {
            if (!val) return '-'
            const currencyCode = currency || 'USD'
            return new Intl.NumberFormat('tr-TR', { 
                style: 'currency', 
                currency: currencyCode,
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }).format(val)
        },
        formatDate(d) {
            if (!d) return '-'
            return new Date(d).toLocaleDateString('tr-TR')
        },
        getProjectProgress(project) {
            return project.progress || project.progressPercentage || 0
        },
        getProjectStatusLabel(status) {
            const labels = {
                'active': this.translate('domaincontrol', 'Aktif'),
                'completed': this.translate('domaincontrol', 'Tamamlandı'),
                'on-hold': this.translate('domaincontrol', 'Beklemede'),
                'cancelled': this.translate('domaincontrol', 'İptal Edildi'),
                'pending': this.translate('domaincontrol', 'Bekliyor')
            }
            return labels[status?.toLowerCase()] || status || this.translate('domaincontrol', 'Aktif')
        }
    }
}
</script>

<style scoped>
.client-projects-tab {
    padding: 0;
}

.pane-actions {
    margin-bottom: 20px;
}

.empty-box {
    padding: 40px 20px;
    text-align: center;
    color: var(--color-text-maxcontrast);
    font-style: italic;
}

.full-width {
    width: 100%;
}

/* Nextcloud List Container */
.nc-list-container {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.nc-list-item {
    display: flex;
    align-items: flex-start;
    gap: 16px;
    padding: 16px 20px;
    background-color: var(--color-main-background);
    border: 1px solid var(--color-border);
    border-radius: var(--border-radius);
    transition: all 0.15s ease;
    min-height: 100px;
}

.nc-list-item:hover {
    background-color: var(--color-background-hover);
    border-color: var(--color-primary-element);
}

.nc-list-item--clickable {
    cursor: pointer;
}

.nc-list-item__icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 44px;
    height: 44px;
    flex-shrink: 0;
    color: var(--color-primary-element);
    background-color: var(--color-primary-element-light);
    border-radius: var(--border-radius);
    margin-top: 2px;
}

.nc-list-item__content {
    flex: 1;
    min-width: 0;
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.nc-list-item__header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    width: 100%;
}

.nc-list-item__title-row {
    display: flex;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
    flex: 1;
}

.nc-list-item__title {
    font-size: 16px;
    font-weight: 600;
    color: var(--color-main-text);
    line-height: 1.4;
    word-break: break-word;
}

.project-status-badge {
    font-size: 11px;
    padding: 4px 10px;
    border-radius: 12px;
    font-weight: 600;
    text-transform: uppercase;
    background-color: var(--color-background-dark);
    color: var(--color-text-maxcontrast);
    white-space: nowrap;
}

.project-status-badge.active {
    background-color: var(--color-element-success);
    color: white;
}

.project-status-badge.completed {
    background-color: var(--color-primary-element);
    color: white;
}

.project-status-badge.on-hold,
.project-status-badge.pending {
    background-color: var(--color-warning-element);
    color: white;
}

.project-status-badge.cancelled {
    background-color: var(--color-element-error);
    color: white;
}

.nc-list-item__body {
    width: 100%;
}

.project-progress-section {
    width: 100%;
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.progress-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    width: 100%;
}

.progress-label {
    font-size: 13px;
    color: var(--color-text-maxcontrast);
    font-weight: 500;
}

.progress-percentage {
    font-size: 13px;
    font-weight: 600;
    color: var(--color-main-text);
}

.project-progress-bar {
    width: 100%;
    height: 8px;
}

.nc-list-item__footer {
    width: 100%;
    padding-top: 4px;
    border-top: 1px solid var(--color-border);
}

.project-meta-row {
    display: flex;
    align-items: center;
    gap: 24px;
    flex-wrap: wrap;
    margin-top: 8px;
}

.project-meta-item {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 13px;
    color: var(--color-text-maxcontrast);
}

.project-meta-item svg {
    flex-shrink: 0;
    opacity: 0.7;
}

.meta-text {
    font-weight: 500;
}
</style>

