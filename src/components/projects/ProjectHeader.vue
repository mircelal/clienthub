<template>
    <div class="project-header-wrapper">
        <!-- Ana Header Alanı -->
        <div class="project-top-bar">
            <!-- Sol: Geri Dön & Başlık -->
            <div class="header-left">
                <NcButton
                    type="tertiary-no-background"
                    :aria-label="t('domaincontrol', 'Back')"
                    class="back-button"
                    @click="$emit('back')"
                >
                    <template #icon>
                        <ArrowLeft :size="24" />
                    </template>
                </NcButton>

                <div class="title-container">
                    <div class="title-row">
                        <h2 class="project-name" :title="project?.name">
                            {{ project?.name || t('domaincontrol', 'Loading...') }}
                        </h2>
                        
                        <!-- Modern Status Badge -->
                        <div v-if="project" :class="['status-badge', `status-${project.status}`]">
                            <span class="status-dot"></span>
                            <span class="status-text">{{ getProjectStatusText(project.status) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sağ: Timer & Aksiyonlar -->
            <div class="header-right">
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
                    {{ t('domaincontrol', 'Start Timer') }}
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
                    {{ t('domaincontrol', 'Stop Timer') }}
                </NcButton>

                <div v-if="canStartTimer || currentRunningEntry" class="separator"></div>

                <!-- Actions Menu -->
                <NcActions :primary="true">
                    <NcActionButton @click="$emit('edit')">
                        <template #icon>
                            <IconPencilOutline :size="20" />
                        </template>
                        {{ t('domaincontrol', 'Edit') }}
                    </NcActionButton>
                    <NcActionButton @click="$emit('delete')">
                        <template #icon>
                            <IconTrashCanOutline :size="20" />
                        </template>
                        {{ t('domaincontrol', 'Delete') }}
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
                    <span class="tab-content">
                        <!-- Dinamik Bileşen Kullanımı -->
                        <component 
                            :is="tab.icon" 
                            :size="18" 
                            class="tab-icon" 
                        />
                        <span class="tab-label">{{ t('domaincontrol', tab.label) }}</span>
                    </span>
                    <span class="active-indicator" v-if="activeTab === tab.id"></span>
                </button>
            </nav>
        </div>
    </div>
</template>

<script>
// Nextcloud Vue Components
import { NcButton, NcActions, NcActionButton } from '@nextcloud/vue'

// Material Design Icons (Standart Nextcloud Yöntemi)
import ArrowLeft from 'vue-material-design-icons/ArrowLeft.vue'
import PlayCircle from 'vue-material-design-icons/PlayCircle.vue'
import StopCircle from 'vue-material-design-icons/StopCircle.vue'
import IconPencilOutline from 'vue-material-design-icons/PencilOutline.vue'
import IconTrashCanOutline from 'vue-material-design-icons/TrashCanOutline.vue'
import ViewDashboard from 'vue-material-design-icons/ViewDashboard.vue'
import CheckboxMarkedCircleOutline from 'vue-material-design-icons/CheckboxMarkedCircleOutline.vue'
import FolderOutline from 'vue-material-design-icons/FolderOutline.vue'
import CurrencyUsd from 'vue-material-design-icons/CurrencyUsd.vue'
import Link from 'vue-material-design-icons/Link.vue'

export default {
    name: 'ProjectHeader',
    components: {
        NcButton,
        NcActions,
        NcActionButton,
        // İkonları register ediyoruz
        ArrowLeft,
        PlayCircle,
        StopCircle,
        IconPencilOutline,
        IconTrashCanOutline,
        ViewDashboard,
        CheckboxMarkedCircleOutline,
        FolderOutline,
        CurrencyUsd,
        Link,
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
    },
    emits: ['back', 'edit', 'delete', 'start-timer', 'stop-timer', 'tab-change'],
    data() {
        return {
            // Tab ID'leri Projects.vue ile eşleşmeli
            tabs: [
                { id: 'overview', label: 'Overview', icon: 'ViewDashboard' },
                { id: 'tasks-time', label: 'Tasks & Time Tracking', icon: 'CheckboxMarkedCircleOutline' },
                { id: 'documents', label: 'Documents & Notes', icon: 'FolderOutline' },
                { id: 'financials', label: 'Financials', icon: 'CurrencyUsd' },
                { id: 'linked-sharing', label: 'Linked Items & Sharing', icon: 'Link' },
            ],
        }
    },
    methods: {
        t(app, text) {
            try {
                return OC?.L10n?.translate(app, text) || text;
            } catch (e) {
                return text;
            }
        },
        getProjectStatusText(status) {
            const map = {
                active: this.t('domaincontrol', 'Active'),
                on_hold: this.t('domaincontrol', 'On Hold'),
                completed: this.t('domaincontrol', 'Completed'),
                cancelled: this.t('domaincontrol', 'Cancelled'),
            }
            return map[status] || status;
        }
    }
}
</script>

<style scoped>
/* Wrapper */
.project-header-wrapper {
    position: sticky;
    top: 0;
    z-index: 1000;
    background-color: var(--color-main-background);
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
    border-bottom: 1px solid var(--color-border);
    display: flex;
    flex-direction: column;
}

/* İKON HİZALAMA DÜZELTMESİ (ÖNEMLİ) */
/* vue-material-design-icons bir span içinde render olur, bunu flex yapmazsak hizalama bozulur */
:deep(.material-design-icon) {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    vertical-align: middle;
}

/* İkon renginin ebeveynden gelmesini sağlar */
:deep(.material-design-icon > .material-design-icon__svg) {
    fill: currentColor;
    display: block; /* SVG altındaki boşlukları siler */
}

/* Üst Kısım */
.project-top-bar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 16px 24px;
    margin-left: 30px;
    gap: 16px;
    min-height: 70px;
    box-sizing: border-box;

    .header-left {
        display: flex;
        align-items: center;
        gap: 16px;
        flex: 1;
        min-width: 0;

        .back-button {
            margin-right: -8px;
            color: var(--color-text-maxcontrast);
            
            &:hover {
                color: var(--color-main-text);
            }
        }

        .title-container {
            display: flex;
            flex-direction: column;
            justify-content: center;

            .title-row {
                display: flex;
                align-items: center;
                gap: 12px;

                .project-name {
                    margin: 0;
                    font-size: 22px;
                    font-weight: 700;
                    color: var(--color-main-text);
                    white-space: nowrap;
                    overflow: hidden;
                    text-overflow: ellipsis;
                    line-height: 1.2;
                    letter-spacing: -0.01em;
                }
            }
        }
    }

    .header-right {
        display: flex;
        align-items: center;
        gap: 8px;

        .separator {
            width: 1px;
            height: 24px;
            background-color: var(--color-border);
            margin: 0 8px;
        }

        .action-button {
            display: flex;
            align-items: center;
            justify-content: center;
            
            &.icon-only {
                width: 40px;
                height: 40px;
                padding: 0;
                border-radius: 50%;
                color: var(--color-text-maxcontrast);
                
                &:hover, &:focus {
                    background-color: var(--color-background-hover);
                    color: var(--color-main-text);
                }
            }

            &.delete-button:hover {
                color: var(--color-error);
                background-color: var(--color-error-hover);
            }
            
            &.timer-button {
                font-weight: 600;
                padding-left: 16px;
                padding-right: 16px;
                box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            }

            &.stop-timer-button {
                background-color: var(--color-error);
                color: var(--color-error-text);
                
                &:hover {
                    background-color: var(--color-error-hover);
                }
            }
        }
    }
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
    transition: all 0.2s ease;

    .status-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background-color: currentColor;
        box-shadow: 0 0 0 1px rgba(255, 255, 255, 0.2);
    }

    &.status-active {
        background-color: rgba(var(--color-success-rgb, 70, 186, 97), 0.15);
        color: var(--color-success);
    }
    &.status-on_hold {
        background-color: rgba(var(--color-warning-rgb, 234, 163, 11), 0.15);
        color: var(--color-warning);
    }
    &.status-completed {
        background-color: rgba(var(--color-primary-rgb, 0, 130, 201), 0.15);
        color: var(--color-primary);
    }
    &.status-cancelled {
        background-color: rgba(var(--color-error-rgb, 226, 50, 55), 0.15);
        color: var(--color-error);
    }
}

/* Modern Tab Tasarımı */
.project-tabs-nav {
    padding: 0 24px 0 calc(24px + 30px);
    border-bottom: 1px solid transparent;

    .tab-list {
        display: flex;
        gap: 32px;
        overflow-x: auto;
        
        .tab-item {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 12px 0;
            background: transparent;
            border: none;
            color: var(--color-text-maxcontrast);
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: color 0.2s ease;

            .tab-content {
                display: flex;
                align-items: center;
                gap: 8px;
                padding: 4px 8px;
                border-radius: 6px;
                transition: background-color 0.2s ease;
            }

            .tab-icon {
                opacity: 0.7;
            }

            &:hover {
                color: var(--color-main-text);
                
                .tab-content {
                    background-color: var(--color-background-hover);
                }
            }

            &.active {
                color: var(--color-primary);
                font-weight: 600;

                .tab-icon {
                    opacity: 1;
                    color: var(--color-primary);
                }

                .active-indicator {
                    position: absolute;
                    bottom: -1px;
                    left: 0;
                    width: 100%;
                    height: 3px;
                    background-color: var(--color-primary);
                    border-radius: 3px 3px 0 0;
                    box-shadow: 0 -2px 6px rgba(var(--color-primary-rgb), 0.3);
                }
            }
        }
    }
}

@media (max-width: 768px) {
    .project-top-bar {
        padding: 12px 16px;
        margin-left: 0;
        
        .project-name {
            font-size: 18px !important;
        }

        .header-right {
            .timer-button {
                padding: 0;
                width: 36px;
                height: 36px;
                border-radius: 50%;
                font-size: 0;
            }
        }
    }

    .project-tabs-nav {
        padding: 0 16px;
        .tab-list {
            gap: 16px;
        }
    }
}
</style>