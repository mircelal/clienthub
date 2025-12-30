<template>
    <div class="inventory-navigation">
        <!-- Header Section (Sticky) -->
        <div class="inventory-header">
            <!-- Search Bar Area -->
            <div class="header-top-row">
                <div class="search-wrapper">
                    <NcTextField
                        :value="searchQuery"
                        :placeholder="translate('domaincontrol', 'Search inventory...')"
                        :show-trailing-button="!!searchQuery"
                        class="full-width-search"
                        @update:value="$emit('update:searchQuery', $event)"
                        @trailing-button-click="$emit('update:searchQuery', '')"
                    >
                        <template #leading-icon>
                            <Magnify :size="20" />
                        </template>
                        <template #trailing-icon v-if="searchQuery">
                            <Close :size="20" />
                        </template>
                    </NcTextField>
                </div>
            </div>
            
            <!-- Management Tools & Filters -->
            <div class="header-controls">
                <!-- Action Buttons -->
                <div class="action-grid primary-actions">
                    <NcButton 
                        type="primary" 
                        class="flex-grow-btn"
                        @click="$emit('add')">
                        <template #icon>
                            <Plus :size="20" />
                        </template>
                        {{ translate('domaincontrol', 'Add Item') }}
                    </NcButton>
                    <NcButton 
                        type="tertiary"
                        @click="$emit('open-pos')"
                    >
                        <template #icon>
                            <CashRegister :size="20" />
                        </template>
                        POS
                    </NcButton>
                </div>

                <div class="action-grid secondary-actions">
                    <NcButton 
                        type="secondary" 
                        class="flex-grow-btn"
                        @click="$emit('manage-categories')">
                        <template #icon>
                            <Folder :size="20" />
                        </template>
                        {{ translate('domaincontrol', 'Categories') }}
                    </NcButton>
                    <NcButton 
                        type="secondary" 
                        class="flex-grow-btn"
                        @click="$emit('manage-warehouses')">
                        <template #icon>
                            <Warehouse :size="20" />
                        </template>
                        {{ translate('domaincontrol', 'Warehouses') }}
                    </NcButton>
                </div>

                <!-- Filters (Tab Style) -->
                <div class="filter-bar">
                    <button 
                        class="filter-tab" 
                        :class="{ active: currentFilter === 'all' }"
                        @click="$emit('filter', 'all')"
                    >
                        {{ translate('domaincontrol', 'All') }}
                    </button>
                    <button 
                        class="filter-tab" 
                        :class="{ active: currentFilter === 'available' }"
                        @click="$emit('filter', 'available')"
                    >
                        {{ translate('domaincontrol', 'Available') }}
                    </button>
                    <button 
                        class="filter-tab" 
                        :class="{ active: currentFilter === 'rented' }"
                        @click="$emit('filter', 'rented')"
                    >
                        {{ translate('domaincontrol', 'Rented') }}
                    </button>
                    <button 
                        class="filter-tab" 
                        :class="{ active: currentFilter === 'maintenance' }"
                        @click="$emit('filter', 'maintenance')"
                    >
                        {{ translate('domaincontrol', 'Maintenance') }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Scrollable List Area -->
        <div class="inventory-list-wrapper">
            <!-- Loading State -->
            <div v-if="loading" class="state-container">
                <NcLoadingIcon :size="48" name="Loading inventory..." />
            </div>
            
            <!-- Empty State -->
            <NcEmptyContent
                v-else-if="items.length === 0"
                name="No items found"
                description="Try adjusting your search or filters."
            >
                <template #icon>
                    <PackageVariant :size="48" />
                </template>
            </NcEmptyContent>

            <!-- List -->
            <ul v-else class="inventory-list">
                <li 
                    v-for="item in items" 
                    :key="item.id" 
                    class="inventory-item" 
                    :class="{ 'selected': selectedItem && selectedItem.id === item.id }" 
                    @click="$emit('select', item)"
                >
                    <!-- Visual / Avatar -->
                    <div class="item-visual-col">
                        <div class="item-visual-wrapper">
                            <div v-if="item.imagePath" class="item-thumb">
                                <img :src="item.imagePath" alt="" /> 
                            </div>
                            <div v-else class="item-avatar" :style="{ backgroundColor: getItemColor(item) }">
                                <span class="avatar-char">{{ item.name.charAt(0).toUpperCase() }}</span>
                            </div>
                            <!-- Status Indicator -->
                            <div class="status-indicator" :class="getStatusClass(item.status)"></div>
                        </div>
                    </div>

                    <!-- Main Content -->
                    <div class="item-info-col">
                        <div class="info-top-row">
                            <span class="item-name" :title="item.name">{{ item.name }}</span>
                            <span class="item-price" v-if="item.salePrice">{{ formatCurrency(item.salePrice) }}</span>
                        </div>
                        
                        <div class="info-bottom-row">
                            <div class="meta-left">
                                <span class="meta-sku" v-if="item.sku">
                                    {{ item.sku }}
                                </span>
                                <!-- Separator removed, using gap/margin instead for cleaner look -->
                                <span class="meta-stock" :class="getStockClass(item)">
                                    <span v-if="isOutOfStock(item)" class="stock-label error">
                                        {{ translate('domaincontrol', 'Out of Stock') }}
                                    </span>
                                    <span v-else>
                                        {{ item.quantity ?? 0 }} {{ translate('domaincontrol', 'in stock') }}
                                    </span>
                                </span>
                            </div>
                        </div>
                    </div>
                </li>
            </ul>
        </div>
    </div>
</template>

<script>
import { NcButton, NcEmptyContent, NcLoadingIcon, NcTextField } from '@nextcloud/vue'
import PackageVariant from 'vue-material-design-icons/PackageVariant.vue'
import Plus from 'vue-material-design-icons/Plus.vue'
import Magnify from 'vue-material-design-icons/Magnify.vue'
import Folder from 'vue-material-design-icons/Folder.vue'
import Warehouse from 'vue-material-design-icons/Warehouse.vue'
import CashRegister from 'vue-material-design-icons/CashRegister.vue'
import Close from 'vue-material-design-icons/Close.vue'

export default {
    name: 'InventoryList',
    components: {
        NcButton,
        NcEmptyContent,
        NcLoadingIcon,
        NcTextField,
        PackageVariant,
        Plus,
        Magnify,
        Folder,
        Warehouse,
        CashRegister,
        Close
    },
    props: {
        items: { type: Array, default: () => [] },
        loading: { type: Boolean, default: false },
        currentFilter: { type: String, default: 'all' },
        searchQuery: { type: String, default: '' },
        selectedItem: { type: Object, default: null },
    },
    emits: ['add', 'filter', 'update:searchQuery', 'select', 'manage-categories', 'manage-warehouses', 'open-pos'],
    methods: {
        translate(appId, text, vars) {
            try {
                if (typeof window.OC !== 'undefined' && window.OC.L10n) {
                    return window.OC.L10n.translate(appId, text, vars || {})
                }
            } catch (e) { /* ignore */ }
            return text
        },
        getItemColor(item) {
            if (!item.name) return 'var(--color-primary-element)'
            const colors = ['#0082c9', '#46ba61', '#f0ad4e', '#e3322d', '#5bc0de', '#9b59b6']
            let hash = 0
            for (let i = 0; i < item.name.length; i++) hash = item.name.charCodeAt(i) + ((hash << 5) - hash)
            return colors[Math.abs(hash) % colors.length]
        },
        getStatusClass(status) {
            const map = {
                available: 'bg-success',
                rented: 'bg-warning',
                sold: 'bg-neutral',
                maintenance: 'bg-error',
                broken: 'bg-error',
            }
            return map[status] || 'bg-neutral'
        },
        formatCurrency(value) {
            return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD', maximumFractionDigits: 0 }).format(value)
        },
        isLowStock(item) {
            const quantity = item.quantity ?? 0
            const minQuantity = item.minQuantity ?? 0
            return minQuantity > 0 && quantity > 0 && quantity <= minQuantity
        },
        isOutOfStock(item) {
            return (item.quantity ?? 0) === 0
        },
        getStockClass(item) {
            if (this.isOutOfStock(item)) return 'text-error'
            if (this.isLowStock(item)) return 'text-warning'
            return 'text-success'
        },
    },
}
</script>

<style scoped>
.inventory-navigation {
    height: 100%;
    display: flex;
    flex-direction: column;
    background-color: var(--color-main-background);
    border-right: 1px solid var(--color-border);
    overflow: hidden;
}

/* --- HEADER --- */
.inventory-header {
    background: var(--color-main-background);
    border-bottom: 1px solid var(--color-border);
    z-index: 10;
    flex-shrink: 0;
    display: flex;
    flex-direction: column;
}

.header-top-row {
    padding: 12px 12px 4px 12px;
}

.header-controls {
    padding: 0 12px;
}

.search-wrapper {
    width: 100%;
}
.full-width-search {
    width: 100%;
}

.action-grid {
    display: flex;
    gap: 8px;
    margin-bottom: 8px;
}
.primary-actions { margin-top: 4px; }

.flex-grow-btn {
    flex: 1;
}

/* Filter Bar - Native Tab Look */
.filter-bar {
    display: flex;
    gap: 4px;
    overflow-x: auto;
    scrollbar-width: none;
    padding-bottom: 0; /* Tabs sit on the bottom border */
    border-bottom: 1px solid transparent; /* Placeholder */
}
.filter-bar::-webkit-scrollbar { display: none; }

.filter-tab {
    background: none;
    border: none;
    border-bottom: 2px solid transparent;
    padding: 8px 12px;
    font-size: 13px;
    font-weight: 600;
    color: var(--color-text-maxcontrast);
    cursor: pointer;
    white-space: nowrap;
    transition: all 0.2s ease;
    opacity: 0.7;
}

.filter-tab:hover {
    color: var(--color-main-text);
    background-color: var(--color-background-hover);
    border-radius: var(--border-radius) var(--border-radius) 0 0;
    opacity: 1;
}

.filter-tab.active {
    color: var(--color-primary-element);
    border-bottom-color: var(--color-primary-element);
    opacity: 1;
}

/* --- LIST --- */
.inventory-list-wrapper {
    flex: 1;
    overflow-y: auto;
    background-color: var(--color-main-background);
}

.inventory-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.inventory-item {
    display: flex;
    padding: 12px 16px; /* slightly more breathing room */
    border-bottom: 1px solid var(--color-border);
    cursor: pointer;
    transition: background-color 0.1s ease;
    height: 72px; /* Fixed height for consistency */
    box-sizing: border-box;
}

.inventory-item:hover {
    background-color: var(--color-background-hover);
}

.inventory-item.selected {
    background-color: var(--color-primary-element-light);
    border-left: 3px solid var(--color-primary-element);
    padding-left: 13px; /* Compensate for border */
}

/* Visual Column */
.item-visual-col {
    margin-right: 14px;
    display: flex;
    align-items: center;
}

.item-visual-wrapper {
    position: relative;
    width: 44px;
    height: 44px;
}

.item-thumb, .item-avatar {
    width: 100%;
    height: 100%;
    border-radius: var(--border-radius-large);
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
    background-color: var(--color-background-dark);
    box-shadow: inset 0 0 0 1px rgba(0,0,0,0.05); /* Subtle border */
}

.item-thumb img { width: 100%; height: 100%; object-fit: cover; }
.avatar-char { color: white; font-weight: 700; font-size: 18px; }

.status-indicator {
    position: absolute;
    bottom: -2px;
    right: -2px;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    border: 2px solid var(--color-main-background);
    box-sizing: border-box;
}
.bg-success { background-color: var(--color-success); }
.bg-warning { background-color: var(--color-warning); }
.bg-error { background-color: var(--color-error); }
.bg-neutral { background-color: var(--color-text-maxcontrast); }

/* Info Column */
.item-info-col {
    flex: 1;
    min-width: 0;
    display: flex;
    flex-direction: column;
    justify-content: center;
    gap: 4px;
}

.info-top-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    line-height: 1.2;
}

.item-name {
    font-weight: 600;
    font-size: 15px; /* Slightly larger */
    color: var(--color-main-text);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

/* Financial Standard: Right aligned price */
.item-price {
    font-size: 14px;
    font-weight: 700;
    color: var(--color-text-maxcontrast);
    font-feature-settings: "tnum"; /* Tabular numbers */
}

.info-bottom-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 13px;
    color: var(--color-text-maxcontrast);
}

.meta-left {
    display: flex;
    align-items: center;
    gap: 8px; /* Use gap instead of separators for cleaner badge look */
    overflow: hidden;
}

.meta-sku {
    font-family: monospace;
    opacity: 0.8;
    letter-spacing: -0.5px;
}

/* STOCK BADGE SYSTEM - OUTLINE STYLE for Contrast Safety */
.meta-stock {
    font-weight: 700;
    padding: 2px 8px;
    border-radius: 12px;
    font-size: 11px;
    display: inline-flex;
    align-items: center;
    line-height: 1.4;
    white-space: nowrap;
    border: 1px solid transparent; /* Default placeholder */
}

/* Success Badge (Green) */
.text-success { 
    background-color: var(--color-main-background);
    border-color: var(--color-success);
    color: var(--color-success); 
}

/* Warning Badge (Yellow/Orange) */
.text-warning { 
    background-color: var(--color-main-background);
    border-color: var(--color-warning);
    color: var(--color-warning); 
}

/* Error Badge (Red) */
.text-error { 
    background-color: var(--color-main-background);
    border-color: var(--color-error);
    color: var(--color-error); 
}

.stock-label.error {
    color: inherit; /* Inherit from badge color */
    font-weight: 800;
    font-size: 11px;
    text-transform: uppercase;
}

/* States */
.state-container {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    height: 100%;
    min-height: 200px;
    padding: 20px;
}
</style>