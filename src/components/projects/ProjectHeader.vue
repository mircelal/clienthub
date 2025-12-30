<template>
    <div class="project-header-wrapper">
        <!-- Ana Header Alanı -->
        <div class="project-top-bar">
            <!-- Sol: Geri Dön & Başlık -->
            <div class="header-left">
                <button
                    class="icon-button back-button"
                    :class="{ 'mobile-visible': isMobile }"
                    :aria-label="translate('domaincontrol', 'Back')"
                    @click="$emit('back')"
                >
                    <ArrowLeft :size="24" />
                </button>

                <div class="title-container">
                    <div class="title-row">
                        <h2 class="project-name" :title="project?.name">
                            {{ project?.name || translate('domaincontrol', 'Loading...') }}
                        </h2>
                        
                        <!-- Modern Status Badge - Desktop Only -->
                        <div v-if="project && !isMobile" :class="['status-badge', `status-${project.status}`]">
                            <span class="status-dot"></span>
                            <span class="status-text">{{ getProjectStatusText(project.status) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sağ: Timer & Aksiyonlar -->
            <div class="header-right">
                <!-- Desktop: Timer Buttons -->
                <template v-if="!isMobile">
                    <!-- Start Timer Button -->
                    <NcButton
                        v-if="canStartTimer && !currentRunningEntry"
                        type="primary"
                        class="action-button timer-button"
                        @click="$emit('start-timer')"
                    >
                        <template #icon>
                            <PlayCircle :size="20" />
                        </template>
                        {{ translate('domaincontrol', 'Start Timer') }}
                    </NcButton>

                    <!-- Stop Timer Button -->
                    <NcButton
                        v-if="currentRunningEntry"
                        type="primary"
                        class="action-button timer-button stop-timer-button"
                        @click="$emit('stop-timer')"
                    >
                        <template #icon>
                            <StopCircle :size="20" />
                        </template>
                        {{ translate('domaincontrol', 'Stop Timer') }}
                    </NcButton>

                    <div v-if="canStartTimer || currentRunningEntry" class="separator"></div>
                </template>

                <!-- Actions Menu -->
                <NcActions :primary="true" class="project-header-actions">
                    <!-- Mobile: Timer Actions First -->
                    <template v-if="isMobile">
                        <NcActionButton 
                            v-if="canStartTimer && !currentRunningEntry"
                            @click="$emit('start-timer')"
                        >
                            <template #icon>
                                <PlayCircle :size="20" />
                            </template>
                            {{ translate('domaincontrol', 'Start Timer') }}
                        </NcActionButton>
                        <NcActionButton 
                            v-if="currentRunningEntry"
                            @click="$emit('stop-timer')"
                        >
                            <template #icon>
                                <StopCircle :size="20" />
                            </template>
                            {{ translate('domaincontrol', 'Stop Timer') }}
                        </NcActionButton>
                        <NcActionSeparator v-if="canStartTimer || currentRunningEntry" />
                    </template>

                    <!-- Edit Action -->
                    <NcActionButton @click="$emit('edit')">
                        <template #icon>
                            <IconPencilOutline :size="20" />
                        </template>
                        {{ translate('domaincontrol', 'Edit') }}
                    </NcActionButton>
                    
                    <NcActionSeparator />
                    
                    <!-- Status Change Actions -->
                    <NcActionButton 
                        v-if="project && project.status !== 'active'"
                        @click="$emit('change-status', 'active')"
                    >
                        <template #icon>
                            <IconPlayCircle :size="20" />
                        </template>
                        {{ translate('domaincontrol', 'Set as Active') }}
                    </NcActionButton>
                    <NcActionButton 
                        v-if="project && project.status !== 'on_hold'"
                        @click="$emit('change-status', 'on_hold')"
                    >
                        <template #icon>
                            <IconPauseCircle :size="20" />
                        </template>
                        {{ translate('domaincontrol', 'Set as On Hold') }}
                    </NcActionButton>
                    <NcActionButton 
                        v-if="project && project.status !== 'completed'"
                        @click="$emit('change-status', 'completed')"
                    >
                        <template #icon>
                            <IconCheckCircle :size="20" />
                        </template>
                        {{ translate('domaincontrol', 'Set as Completed') }}
                    </NcActionButton>
                    <NcActionButton 
                        v-if="project && project.status !== 'cancelled'"
                        @click="$emit('change-status', 'cancelled')"
                    >
                        <template #icon>
                            <IconCloseCircle :size="20" />
                        </template>
                        {{ translate('domaincontrol', 'Set as Cancelled') }}
                    </NcActionButton>
                    
                    <NcActionSeparator />
                    
                    <!-- Delete Action -->
                    <NcActionButton @click="$emit('delete')">
                        <template #icon>
                            <IconTrashCanOutline :size="20" />
                        </template>
                        {{ translate('domaincontrol', 'Delete') }}
                    </NcActionButton>
                </NcActions>
            </div>
        </div>

        <!-- Modern Tab Navigasyonu -->
        <div class="project-tabs-nav">
            <nav class="tab-list" role="tablist">
                <button
                    v-for="tab in tabs"
                    :key="tab.id"
                    role="tab"
                    :aria-selected="activeTab === tab.id"
                    :class="['tab-item', { 'active': activeTab === tab.id }]"
                    @click="$emit('tab-change', tab.id)"
                >
                    <span class="tab-item-content">
                        <!-- Dinamik Bileşen Kullanımı -->
                        <component 
                            :is="tab.icon" 
                            :size="isMobile ? 20 : 18" 
                            class="tab-icon" 
                        />
                        <span v-if="!isMobile" class="tab-label">{{ translate('domaincontrol', tab.label) }}</span>
                    </span>
                    <span class="active-indicator" v-if="activeTab === tab.id"></span>
                </button>
            </nav>
        </div>
    </div>
</template>

<script>
// Nextcloud Vue Components
import { NcButton, NcActions, NcActionButton, NcActionSeparator } from '@nextcloud/vue'

// Material Design Icons (Standart Nextcloud Yöntemi)
import ArrowLeft from 'vue-material-design-icons/ArrowLeft.vue'
import PlayCircle from 'vue-material-design-icons/PlayCircle.vue'
import StopCircle from 'vue-material-design-icons/StopCircle.vue'
import IconPencilOutline from 'vue-material-design-icons/PencilOutline.vue'
import IconTrashCanOutline from 'vue-material-design-icons/TrashCanOutline.vue'
import IconPlayCircle from 'vue-material-design-icons/PlayCircle.vue'
import IconPauseCircle from 'vue-material-design-icons/PauseCircle.vue'
import IconCheckCircle from 'vue-material-design-icons/CheckCircle.vue'
import IconCloseCircle from 'vue-material-design-icons/CloseCircle.vue'
import ViewDashboard from 'vue-material-design-icons/ViewDashboard.vue'
import CheckboxMarkedCircleOutline from 'vue-material-design-icons/CheckboxMarkedCircleOutline.vue'
import FolderOutline from 'vue-material-design-icons/FolderOutline.vue'
import CurrencyUsd from 'vue-material-design-icons/CurrencyUsd.vue'
import Link from 'vue-material-design-icons/Link.vue'
import IconHistory from 'vue-material-design-icons/History.vue'

export default {
    name: 'ProjectHeader',
    components: {
        NcButton,
        NcActions,
        NcActionButton,
        NcActionSeparator,
        // İkonları register ediyoruz
        ArrowLeft,
        PlayCircle,
        StopCircle,
        IconPencilOutline,
        IconTrashCanOutline,
        IconPlayCircle,
        IconPauseCircle,
        IconCheckCircle,
        IconCloseCircle,
        ViewDashboard,
        CheckboxMarkedCircleOutline,
        FolderOutline,
        CurrencyUsd,
        Link,
        IconHistory,
    },
    props: {
        project: {
            type: Object,
            default: null,
        },
        activeTab: {
            type: String,
            default: 'overview',
        },
        canStartTimer: {
            type: Boolean,
            default: false,
        },
        currentRunningEntry: {
            type: Object,
            default: null,
        },
        isMobile: {
            type: Boolean,
            default: false,
        },
    },
    emits: ['back', 'edit', 'delete', 'start-timer', 'stop-timer', 'tab-change', 'change-status'],
    data() {
        return {
            // Tab ID'leri Projects.vue ile eşleşmeli
            tabs: [
                { id: 'overview', label: 'Overview', icon: 'ViewDashboard' },
                { id: 'tasks-time', label: 'Tasks & Time', icon: 'CheckboxMarkedCircleOutline' },
                { id: 'documents', label: 'Documents', icon: 'FolderOutline' },
                { id: 'financials', label: 'Financials', icon: 'CurrencyUsd' },
                { id: 'linked-sharing', label: 'Linked & Sharing', icon: 'Link' },
                { id: 'activity', label: 'Activity', icon: 'IconHistory' },
            ],
        }
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
        getProjectStatusText(status) {
            const map = {
                active: this.translate('domaincontrol', 'Active'),
                on_hold: this.translate('domaincontrol', 'On Hold'),
                completed: this.translate('domaincontrol', 'Completed'),
                cancelled: this.translate('domaincontrol', 'Cancelled'),
            }
            return map[status] || status;
        }
    }
}
</script>

<style scoped>
/* Wrapper */
.project-header-wrapper {
    position: relative;
    z-index: 100;
    background-color: var(--color-main-background);
    border-bottom: 1px solid var(--color-border);
    display: flex;
    flex-direction: column;
    width: 100%;
}

/* Üst Kısım */
.project-top-bar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 16px 24px;
    gap: 16px;
    min-height: 70px;
    box-sizing: border-box;
    width: 100%;
}

.header-left {
    display: flex;
    align-items: center;
    gap: 12px;
    flex: 1;
    min-width: 0;
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
    flex-shrink: 0;
}

.icon-button:hover {
    background-color: var(--color-background-hover);
    color: var(--color-main-text);
}

.back-button {
    margin-right: 8px;
    display: flex; /* Varsayılan olarak görünür (Mobile First) */
    align-items: center;
    justify-content: center;
    color: var(--color-text-maxcontrast);
}

/* Sadece Masaüstünde Gizle */
@media (min-width: 769px) {
    .back-button {
        display: none;
    }
}

.title-container {
    display: flex;
    flex-direction: column;
    justify-content: center;
    min-width: 0;
    flex: 1;
}

.title-row {
    display: flex;
    align-items: center;
    gap: 12px;
    min-width: 0;
}

.project-name {
    margin: 0;
    font-size: 22px;
    font-weight: 700;
    color: var(--color-main-text);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    line-height: 1.2;
}

.header-right {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-shrink: 0;
}

.header-right .separator {
    width: 1px;
    height: 24px;
    background-color: var(--color-border);
    margin: 0 8px;
}

.action-button.timer-button {
    font-weight: 600;
}

/* Custom styling for NcActionButton components - only affects this component */
.project-header-actions :deep(.action-button),
.project-header-actions :deep(button),
.project-header-actions :deep(li) {
    display: flex;
    align-items: center;
    width: 100%;
    height: auto;
    margin: 0;
    padding: 0;
    padding-inline-end: calc((var(--default-clickable-area) - 16px) / 2);
    box-sizing: border-box;
    cursor: pointer;
    white-space: nowrap;
    color: var(--color-main-text);
    border: 0;
    border-radius: 0;
    background-color: transparent;
    box-shadow: none;
    font-weight: normal;
    font-size: var(--default-font-size);
    line-height: var(--default-clickable-area);
    justify-content: flex-start;
}

/* Modern Status Badge Tasarımı */
.status-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 4px 10px;
    border-radius: 99px;
    font-size: 13px;
    font-weight: 600;
    line-height: 1;
    flex-shrink: 0;
}

.status-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background-color: currentColor;
}

.status-active {
    background-color: var(--color-element-success);
    color: var(--color-element-success-text);
}
.status-on_hold {
    background-color: var(--color-element-warning);
    color: var(--color-element-warning-text);
}
.status-completed {
    background-color: var(--color-info);
    color: var(--color-info-text);
}
.status-cancelled {
    background-color: var(--color-element-error);
    color: var(--color-element-error-text);
}

/* Modern Tab Tasarımı - NEXTCLOUD STİLİ */
.project-tabs-nav {
    padding: 0 24px;
    background-color: var(--color-main-background);
    width: 100%;
    box-sizing: border-box;
    overflow: hidden;
}

.tab-list {
    display: flex;
    gap: 24px;
    overflow-x: auto;
    scrollbar-width: none;
    -ms-overflow-style: none;
    padding: 0;
    margin: 0;
}

.tab-list::-webkit-scrollbar {
    display: none;
}

.tab-item {
    position: relative;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 0;
    background: transparent;
    border: none;
    color: var(--color-main-text);
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    transition: color 0.2s ease;
    white-space: nowrap;
    min-height: 44px;
}

.tab-item-content {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 10px 4px;
}

.tab-icon {
    opacity: 0.7;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.tab-item:hover {
    color: var(--color-primary-element);
}

.tab-item.active {
    color: var(--color-primary-element);
    font-weight: 600;
}

.tab-item.active .tab-icon {
    opacity: 1;
}

.active-indicator {
    position: absolute;
    bottom: 0;
    left: 0;
    width: 100%;
    height: 3px;
    background-color: var(--color-primary-element);
    border-radius: 3px 3px 0 0;
}

/* Mobile Responsive - Nextcloud Style */
@media (max-width: 768px) {
    .project-top-bar {
        padding: 10px 12px;
        min-height: 56px;
        gap: 8px;
    }
    
    .header-left {
        gap: 8px;
        margin: 0 0 0 40px; /* Prevent overlap with Nextcloud menu button */
    }
    
    /* Make back button visible on mobile */
    .back-button {
        display: flex !important;
        padding: 6px;
        margin-right: 0;
    }
    
    .project-name {
        font-size: 16px !important;
    }
    
    .header-right {
        gap: 4px;
    }
    
    .project-tabs-nav {
        padding: 0 12px;
    }
    
    .tab-list {
        gap: 8px;
        justify-content: space-around;
        width: 100%;
    }
    
    .tab-item {
        min-height: 48px;
        flex: 1;
        min-width: 0; /* Allow shrinking */
    }
    
    .tab-item-content {
        padding: 10px 4px;
        gap: 0;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        width: 100%;
    }
    
    .tab-icon {
        margin-bottom: 2px;
    }
}

/* İKON HİZALAMA */
:deep(.material-design-icon) {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    vertical-align: middle;
}

:deep(.material-design-icon > .material-design-icon__svg) {
    fill: currentColor;
}
</style>
