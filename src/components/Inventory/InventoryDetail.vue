<template>
    <div class="inventory-detail">
        <!-- 1. Header Section: Identity & Primary Actions -->
        <div class="detail-header">
            <div class="header-main">
                <div class="header-left">
                    <button
                        class="icon-button back-button"
                        :aria-label="translate('domaincontrol', 'Back')"
                        @click="$emit('back')"
                    >
                        <ArrowLeft :size="24" />
                    </button>

                    <div class="title-row">
                        <h2 class="item-name">{{ item.name }}</h2>
                        <span class="status-badge" :class="getStatusClass(item.status)">
                            {{ getStatusText(item.status) }}
                        </span>
                    </div>

                    <div class="breadcrumbs">
                        <span>{{ getWarehouseName(item.warehouseId) }}</span>
                        <span class="sep">/</span>
                        <span>{{ getCategoryName(item.categoryId) }}</span>
                    </div>
                </div>
            </div>

            <!-- Action Toolbar -->
            <div class="header-toolbar">
                <div class="action-group primary">
                    <NcButton type="primary" @click="$emit('action', 'rent')">
                        <template #icon><Export :size="20" /></template>
                        {{ translate('domaincontrol', 'Rent Out') }}
                    </NcButton>
                </div>

                <div class="action-group operations">
                    <NcButton @click="openTransferModal">
                        <template #icon><SwapHorizontal :size="20" /></template>
                        {{ translate('domaincontrol', 'Transfer') }}
                    </NcButton>
                    <NcButton @click="openRepairModal">
                        <template #icon><Wrench :size="20" /></template>
                        {{ translate('domaincontrol', 'Repair') }}
                    </NcButton>
                </div>
                
                <div class="action-group admin">
                    <NcActions>
                        <NcActionButton @click="$emit('edit', item)">
                            <template #icon><Pencil :size="20" /></template>
                            {{ translate('domaincontrol', 'Edit Details') }}
                        </NcActionButton>
                        <NcActionButton @click="$emit('action', 'clone')">
                            <template #icon><ContentCopy :size="20" /></template>
                            {{ translate('domaincontrol', 'Duplicate Item') }}
                        </NcActionButton>
                        <NcActionSeparator />
                        <NcActionButton @click="$emit('delete', item)">
                            <template #icon><TrashCan :size="20" /></template>
                            {{ translate('domaincontrol', 'Delete Item') }}
                        </NcActionButton>
                    </NcActions>
                </div>
            </div>
        </div>

        <!-- 2. Quick Stats Ribbon (Financial & Stock Overview) -->
        <div class="stats-ribbon">
            <div class="stat-item">
                <span class="stat-label">{{ translate('domaincontrol', 'Total Value') }}</span>
                <span class="stat-value">{{ formatCurrency((item.purchasePrice || 0) * (item.quantity || 0)) }}</span>
            </div>
            <div class="stat-divider"></div>
            <div class="stat-item">
                <span class="stat-label">{{ translate('domaincontrol', 'Stock Level') }}</span>
                <span class="stat-value" :class="getStockValueClass()">
                    {{ item.quantity ?? 0 }} <small>{{ translate('domaincontrol', 'units') }}</small>
                </span>
            </div>
            <div class="stat-divider"></div>
            <div class="stat-item">
                <span class="stat-label">{{ translate('domaincontrol', 'Utilization') }}</span>
                <!-- Mock utilization calculation -->
                <span class="stat-value success">85%</span>
            </div>
        </div>

        <!-- 3. Main Content Grid -->
        <div class="detail-content">
            <div class="detail-grid">
                
                <!-- Left Column: Media & Core Info -->
                <div class="grid-column left-col">
                    <!-- Images -->
                    <div class="card images-card">
                        <div class="card-header">
                            <h3>{{ translate('domaincontrol', 'Product Images') }}</h3>
                            <button v-if="images.length > 0" class="btn-link" @click="triggerUpload">
                                {{ translate('domaincontrol', '+ Add Image') }}
                            </button>
                        </div>
                        
                        <!-- Hidden File Input for Upload (Updated with 'multiple') -->
                        <input 
                            type="file" 
                            ref="fileInput" 
                            style="display: none" 
                            accept="image/*" 
                            multiple
                            @change="handleFileUpload" 
                        />

                        <div v-if="images.length === 0" class="no-image-placeholder">
                            <div class="placeholder-icon">
                                <Camera :size="32" />
                            </div>
                            <span>{{ translate('domaincontrol', 'No images uploaded') }}</span>
                            <NcButton 
                                type="tertiary" 
                                size="small" 
                                class="mt-2" 
                                @click="triggerUpload"
                                :disabled="uploading"
                            >
                                <template v-if="uploading">
                                    <NcLoadingIcon :size="16" />
                                </template>
                                <template v-else>
                                    {{ translate('domaincontrol', 'Upload Image') }}
                                </template>
                            </NcButton>
                        </div>
                        <div v-else>
                            <div class="main-image-preview">
                                <img :src="getImageUrl(images[selectedImageIndex || 0].filePath, images[selectedImageIndex || 0].fileId)" alt="Preview" />
                                <div class="image-controls" v-if="images.length > 1">
                                    <button class="nav-btn prev" @click="previousImage"><ChevronLeft :size="20" /></button>
                                    <button class="nav-btn next" @click="nextImage"><ChevronRight :size="20" /></button>
                                </div>
                            </div>
                            <div class="images-thumbs" v-if="images.length > 1">
                                <div 
                                    v-for="(img, index) in images" 
                                    :key="img.id" 
                                    class="thumb-item"
                                    :class="{ active: index === (selectedImageIndex || 0) }"
                                    @click="selectImage(index)"
                                >
                                    <img :src="getImageUrl(img.filePath, img.fileId)" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Description -->
                    <div class="card description-card">
                        <div class="card-header">
                            <h3>{{ translate('domaincontrol', 'Description') }}</h3>
                        </div>
                        <div class="card-body">
                            <p v-if="item.description" class="text-description">{{ item.description }}</p>
                            <p v-else class="text-muted italic">{{ translate('domaincontrol', 'No description provided.') }}</p>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Details, Financials, History -->
                <div class="grid-column right-col">
                    
                    <!-- Key Details -->
                    <div class="card info-card">
                        <div class="card-header">
                            <h3>{{ translate('domaincontrol', 'Item Details') }}</h3>
                        </div>
                        <div class="info-list">
                            <div class="info-row">
                                <span class="label">{{ translate('domaincontrol', 'SKU') }}</span>
                                <span class="value mono">{{ item.sku || '-' }}</span>
                            </div>
                            <div class="info-row">
                                <span class="label">{{ translate('domaincontrol', 'Serial Number') }}</span>
                                <span class="value mono">{{ item.serialNumber || '-' }}</span>
                            </div>
                            <div class="info-row">
                                <span class="label">{{ translate('domaincontrol', 'Category') }}</span>
                                <span class="value link">{{ getCategoryName(item.categoryId) }}</span>
                            </div>
                            <div class="info-row">
                                <span class="label">{{ translate('domaincontrol', 'Location') }}</span>
                                <div class="value-badge">
                                    <MapMarker :size="14" /> {{ getWarehouseName(item.warehouseId) }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Pricing & Finance -->
                    <div class="card financial-card">
                        <div class="card-header">
                            <h3>{{ translate('domaincontrol', 'Pricing & Costs') }}</h3>
                        </div>
                        <div class="info-list">
                            <div class="info-row">
                                <span class="label">{{ translate('domaincontrol', 'Purchase Cost') }}</span>
                                <span class="value">{{ formatCurrency(item.purchasePrice) }}</span>
                            </div>
                            <div class="info-row highlight">
                                <span class="label">{{ translate('domaincontrol', 'Sale Price') }}</span>
                                <span class="value primary">{{ formatCurrency(item.salePrice) }}</span>
                            </div>
                            <div class="info-row">
                                <span class="label">{{ translate('domaincontrol', 'Rental (Day)') }}</span>
                                <span class="value">{{ formatCurrency(item.rentalPrice) }}</span>
                            </div>
                            <div class="info-row">
                                <span class="label">{{ translate('domaincontrol', 'Acquisition Date') }}</span>
                                <span class="value">{{ formatDate(item.purchasedAt) }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Movements / History -->
                    <div class="card history-card">
                        <div class="card-header">
                            <h3>{{ translate('domaincontrol', 'Recent Activity') }}</h3>
                            <button class="btn-link">{{ translate('domaincontrol', 'View All') }}</button>
                        </div>
                        
                        <div v-if="loadingMovements" class="loading-state">
                            <NcLoadingIcon :size="24" />
                        </div>
                        <div v-else-if="movements.length === 0" class="empty-state-small">
                            <span>{{ translate('domaincontrol', 'No activity yet.') }}</span>
                        </div>
                        <div v-else class="timeline-list">
                            <div 
                                v-for="movement in movements.slice(0, 5)" 
                                :key="movement.id" 
                                class="timeline-item"
                            >
                                <div class="timeline-icon" :class="getMovementTypeClass(movement.type)">
                                    <component :is="getMovementIcon(movement.type)" :size="14" />
                                </div>
                                <div class="timeline-content">
                                    <div class="timeline-title">
                                        <span class="type">{{ getMovementTypeText(movement.type) }}</span>
                                        <span class="date">{{ formatDate(movement.dateOut) }}</span>
                                    </div>
                                    <div class="timeline-desc">
                                        <span v-if="movement.customerName">{{ movement.customerName }}</span>
                                        <span v-else-if="movement.notes">{{ movement.notes }}</span>
                                        <span v-else>-</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 4. Modals (Custom Implementation using NC Styles) -->
        
        <!-- Transfer Modal -->
        <div v-if="showTransferModal" class="modal-backdrop">
            <div class="nc-modal">
                <div class="modal-header">
                    <h3>{{ translate('domaincontrol', 'Transfer Stock') }}</h3>
                    <button class="close-btn" @click="showTransferModal = false"><Close :size="20" /></button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>{{ translate('domaincontrol', 'Current Location') }}</label>
                        <input type="text" disabled :value="getWarehouseName(item.warehouseId)" class="nc-input disabled" />
                    </div>
                    <div class="form-group">
                        <label>{{ translate('domaincontrol', 'Destination Warehouse') }}</label>
                        <select v-model="transferData.targetWarehouseId" class="nc-select">
                            <option value="" disabled>{{ translate('domaincontrol', 'Select Destination') }}</option>
                            <option v-for="wh in warehouses" :key="wh.id" :value="wh.id" :disabled="wh.id === item.warehouseId">
                                {{ wh.name }}
                            </option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>{{ translate('domaincontrol', 'Quantity') }}</label>
                        <input type="number" v-model="transferData.quantity" class="nc-input" min="1" :max="item.quantity" />
                    </div>
                </div>
                <div class="modal-footer">
                    <NcButton type="secondary" @click="showTransferModal = false">{{ translate('domaincontrol', 'Cancel') }}</NcButton>
                    <NcButton type="primary" @click="submitTransfer">{{ translate('domaincontrol', 'Confirm Transfer') }}</NcButton>
                </div>
            </div>
        </div>

        <!-- Repair Modal -->
        <div v-if="showRepairModal" class="modal-backdrop">
            <div class="nc-modal">
                <div class="modal-header">
                    <h3>{{ translate('domaincontrol', 'Send to Repair') }}</h3>
                    <button class="close-btn" @click="showRepairModal = false"><Close :size="20" /></button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>{{ translate('domaincontrol', 'Issue Description') }}</label>
                        <textarea v-model="repairData.notes" class="nc-textarea" rows="3" :placeholder="translate('domaincontrol', 'Describe the damage or reason...')"></textarea>
                    </div>
                    <div class="form-group">
                        <label>{{ translate('domaincontrol', 'Service Provider (Optional)') }}</label>
                        <input type="text" v-model="repairData.vendor" class="nc-input" />
                    </div>
                </div>
                <div class="modal-footer">
                    <NcButton type="secondary" @click="showRepairModal = false">{{ translate('domaincontrol', 'Cancel') }}</NcButton>
                    <NcButton type="primary" @click="submitRepair">{{ translate('domaincontrol', 'Create Repair Ticket') }}</NcButton>
                </div>
            </div>
        </div>

    </div>
</template>

<script>
import { NcButton, NcActions, NcActionButton, NcActionSeparator, NcLoadingIcon } from '@nextcloud/vue'
import Pencil from 'vue-material-design-icons/Pencil.vue'
import TrashCan from 'vue-material-design-icons/TrashCan.vue'
import Export from 'vue-material-design-icons/Export.vue'
import SwapHorizontal from 'vue-material-design-icons/SwapHorizontal.vue'
import Wrench from 'vue-material-design-icons/Wrench.vue'
import ContentCopy from 'vue-material-design-icons/ContentCopy.vue'
import Camera from 'vue-material-design-icons/Camera.vue'
import ChevronLeft from 'vue-material-design-icons/ChevronLeft.vue'
import ChevronRight from 'vue-material-design-icons/ChevronRight.vue'
import MapMarker from 'vue-material-design-icons/MapMarker.vue'
import Close from 'vue-material-design-icons/Close.vue'
import History from 'vue-material-design-icons/History.vue'
import Import from 'vue-material-design-icons/Import.vue'
import CashCheck from 'vue-material-design-icons/CashCheck.vue'
import ArrowLeft from 'vue-material-design-icons/ArrowLeft.vue'
import api from '../../services/api'
import { generateUrl } from '@nextcloud/router'

export default {
    name: 'InventoryDetail',
    components: {
        NcButton, NcActions, NcActionButton, NcActionSeparator, NcLoadingIcon,
        Pencil, TrashCan, Export, SwapHorizontal, Wrench, ContentCopy,
        Camera, ChevronLeft, ChevronRight, MapMarker, Close,
        History, Import, CashCheck
        , ArrowLeft
    },
    props: {
        item: { type: Object, required: true },
        categories: { type: Array, default: () => [] },
        warehouses: { type: Array, default: () => [] },
    },
    emits: ['edit', 'delete', 'action', 'update-item'],
    data() {
        return {
            images: [],
            selectedImageIndex: 0,
            movements: [],
            loadingMovements: false,
            uploading: false,
            
            // Modal States
            showTransferModal: false,
            transferData: { targetWarehouseId: '', quantity: 1 },
            
            showRepairModal: false,
            repairData: { notes: '', vendor: '' }
        }
    },
    watch: {
        item: {
            handler() {
                this.loadImages()
                this.loadMovements()
                this.selectedImageIndex = 0
            },
            immediate: true,
        },
    },
    methods: {
        translate(appId, text, vars) {
             try {
                if (typeof window.OC !== 'undefined' && window.OC.L10n) return window.OC.L10n.translate(appId, text, vars || {})
            } catch (e) { /* ignore */ }
            return text
        },
        // --- Helper Methods ---
        getStatusClass(status) {
            const map = { available: 'bg-success', rented: 'bg-warning', maintenance: 'bg-error' }
            return map[status] || 'bg-neutral'
        },
        getStatusText(status) { return status ? status.charAt(0).toUpperCase() + status.slice(1) : '' },
        getCategoryName(id) { return this.categories.find(c => c.id === id)?.name || '-' },
        getWarehouseName(id) { return this.warehouses.find(w => w.id === id)?.name || '-' },
        formatCurrency(val) { return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(val || 0) },
        formatDate(date) { return date ? new Date(date).toLocaleDateString() : '-' },
        getStockValueClass() { return (this.item.quantity || 0) <= (this.item.minQuantity || 0) ? 'text-error' : '' },
        
        // --- Image Logic ---
        triggerUpload() {
            this.$refs.fileInput.click()
        },
        async handleFileUpload(event) {
            const files = Array.from(event.target.files)
            if (files.length === 0) return
            
            this.uploading = true
            try {
                for (const file of files) {
                    // Basic type check
                    if (!file.type.startsWith('image/')) {
                        continue // Skip non-images
                    }

                    // Use the API signature from the modal example (passing file directly)
                    const response = await api.inventoryImages.upload(this.item.id, file)
                    
                    // Push to local array instead of full reload for smoother UX
                    if (response.data) {
                        this.images.push(response.data)
                    }
                }
                
                // If it was empty before, select the first new image
                if (this.images.length > 0 && this.selectedImageIndex === null) {
                    this.selectedImageIndex = 0
                }

                // Reset file input
                event.target.value = ''
            } catch (e) {
                console.error('Upload failed', e)
                alert(this.translate('domaincontrol', 'Failed to upload image. Please try again.'))
            } finally {
                this.uploading = false
            }
        },
        async loadImages() {
             if(this.item.id) {
                try {
                    const res = await api.inventoryImages.getAll(this.item.id)
                    this.images = res.data || []
                } catch(e) { this.images = [] }
             }
        },
        getImageUrl(filePath, fileId) {
            if (fileId) return generateUrl(`/core/preview?fileId=${fileId}&x=800&y=800&a=1`)
            return ''
        },
        selectImage(index) { this.selectedImageIndex = index },
        nextImage() { if (this.selectedImageIndex < this.images.length - 1) this.selectedImageIndex++ },
        previousImage() { if (this.selectedImageIndex > 0) this.selectedImageIndex-- },

        // --- Movement Logic ---
        async loadMovements() {
            this.loadingMovements = true
            try {
                const res = await api.pos.getMovementsByInventory(this.item.id)
                this.movements = res.data || []
            } catch(e) { this.movements = [] } 
            finally { this.loadingMovements = false }
        },
        getMovementTypeClass(type) {
             const map = { sale: 'text-success', rent: 'text-warning', repair: 'text-error' }
             return map[type] || 'text-neutral'
        },
        getMovementIcon(type) {
            if (type === 'sale') return CashCheck
            if (type === 'rent') return Export
            if (type === 'repair') return Wrench
            return History
        },
        getMovementTypeText(type) { return type.charAt(0).toUpperCase() + type.slice(1) },

        // --- Action Handlers ---
        openTransferModal() {
            this.transferData = { targetWarehouseId: '', quantity: 1 }
            this.showTransferModal = true
        },
        async submitTransfer() {
            // Emit event to parent to handle API call, or call API directly here
            this.$emit('action', { type: 'transfer', data: this.transferData })
            this.showTransferModal = false
            alert(this.translate('domaincontrol', 'Transfer initiated'))
        },
        openRepairModal() {
            this.repairData = { notes: '', vendor: '' }
            this.showRepairModal = true
        },
        async submitRepair() {
            this.$emit('action', { type: 'repair', data: this.repairData })
            this.showRepairModal = false
            alert(this.translate('domaincontrol', 'Repair ticket created'))
        }
    }
}
</script>

<style scoped>
.inventory-detail {
    height: 100%;
    overflow-y: auto;
    background-color: var(--color-main-background);
    padding-bottom: 40px;
}

/* 1. Header */
.detail-header {
    background: var(--color-main-background);
    padding: 20px 24px;
    border-bottom: 1px solid var(--color-border);
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    flex-wrap: wrap;
    gap: 16px;
}

.header-main {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.title-row {
    display: flex;
    align-items: center;
    gap: 12px;
}

.item-name {
    margin: 0;
    font-size: 22px;
    font-weight: 700;
    color: var(--color-main-text);
}

.status-badge {
    font-size: 11px;
    padding: 2px 8px;
    border-radius: 12px;
    font-weight: 700;
    text-transform: uppercase;
    border: 1px solid transparent;
}
.bg-success { color: var(--color-success); border-color: var(--color-success); }
.bg-warning { color: var(--color-warning); border-color: var(--color-warning); }
.bg-error { color: var(--color-error); border-color: var(--color-error); }
.bg-neutral { color: var(--color-text-maxcontrast); border-color: var(--color-text-maxcontrast); }

.breadcrumbs {
    display: flex;
    align-items: center;
    font-size: 13px;
    color: var(--color-text-maxcontrast);
}
.breadcrumbs .sep { margin: 0 6px; opacity: 0.5; }

.header-toolbar {
    display: flex;
    align-items: center;
    gap: 12px;
}
.action-group { display: flex; gap: 8px; }
.action-group.operations { padding-right: 12px; border-right: 1px solid var(--color-border); margin-right: 4px; }

/* 2. Stats Ribbon */
.stats-ribbon {
    display: flex;
    align-items: center;
    padding: 12px 24px;
    background: var(--color-background-hover);
    border-bottom: 1px solid var(--color-border);
    margin-bottom: 24px;
}

.stat-item {
    display: flex;
    flex-direction: column;
    padding: 0 16px;
}
.stat-item:first-child { padding-left: 0; }

.stat-divider {
    width: 1px;
    height: 24px;
    background: var(--color-border);
}

.stat-label {
    font-size: 11px;
    color: var(--color-text-maxcontrast);
    text-transform: uppercase;
    font-weight: 700;
}
.stat-value {
    font-size: 16px;
    font-weight: 600;
    color: var(--color-main-text);
}
.stat-value small { font-size: 12px; font-weight: normal; color: var(--color-text-maxcontrast); }
.text-error { color: var(--color-error); }

/* 3. Grid Layout */
.detail-content {
    padding: 0 24px;
}

.detail-grid {
    display: grid;
    grid-template-columns: 350px 1fr;
    gap: 24px;
}

/* Cards Generic */
.card {
    background: var(--color-main-background);
    border: 1px solid var(--color-border);
    border-radius: var(--border-radius-large);
    margin-bottom: 24px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.02);
    overflow: hidden;
}

.card-header {
    padding: 12px 16px;
    border-bottom: 1px solid var(--color-border);
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: var(--color-background-hover);
}
.card-header h3 { margin: 0; font-size: 14px; font-weight: 700; color: var(--color-text-maxcontrast); text-transform: uppercase; }

/* Images */
.main-image-preview {
    width: 100%;
    height: 350px; /* Increased height for better visibility of portrait images */
    background: var(--color-background-dark); /* Changed to dark theme color */
    border: 1px solid var(--color-border);
    border-radius: var(--border-radius);
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
}
.main-image-preview img { max-width: 100%; max-height: 100%; object-fit: contain; display: block; }
.image-controls { position: absolute; width: 100%; display: flex; justify-content: space-between; padding: 0 10px; }
.nav-btn { background: rgba(0,0,0,0.5); color: white; border: none; border-radius: 50%; padding: 6px; cursor: pointer; display: flex; }

.images-thumbs {
    display: flex;
    gap: 8px;
    padding: 8px 0;
    overflow-x: auto;
}
.thumb-item {
    width: 60px;
    height: 60px;
    border-radius: 4px;
    overflow: hidden;
    opacity: 0.6;
    cursor: pointer;
    border: 2px solid transparent;
    background: var(--color-background-dark);
    display: flex;
    align-items: center;
    justify-content: center;
}
.thumb-item.active { opacity: 1; border-color: var(--color-primary-element); }
.thumb-item img { width: 100%; height: 100%; object-fit: contain; } /* Changed to contain to prevent cropping */

.no-image-placeholder {
    padding: 30px;
    text-align: center;
    color: var(--color-text-maxcontrast);
    display: flex;
    flex-direction: column;
    align-items: center;
}

/* Description */
.card-body { padding: 16px; }
.text-description { line-height: 1.6; color: var(--color-main-text); margin: 0; }
.text-muted { color: var(--color-text-maxcontrast); }

/* Info Lists */
.info-list { padding: 0; }
.info-row {
    display: flex;
    justify-content: space-between;
    padding: 10px 16px;
    border-bottom: 1px solid var(--color-border);
    font-size: 13px;
}
.info-row:last-child { border-bottom: none; }
.info-row.highlight { background: rgba(var(--color-primary-element), 0.05); }

.info-row .label { color: var(--color-text-maxcontrast); }
.info-row .value { font-weight: 500; color: var(--color-main-text); text-align: right; }
.info-row .value.mono { font-family: monospace; }
.info-row .value.link { color: var(--color-primary-element); cursor: pointer; }
.info-row .value.primary { color: var(--color-primary-element); font-weight: 700; font-size: 14px; }

.value-badge {
    background: var(--color-background-dark);
    padding: 2px 8px;
    border-radius: 12px;
    display: inline-flex;
    align-items: center;
    gap: 4px;
    font-size: 12px;
}

/* Timeline */
.timeline-list { padding: 12px 16px; }
.timeline-item {
    display: flex;
    gap: 12px;
    margin-bottom: 16px;
    position: relative;
}
.timeline-item::after {
    content: '';
    position: absolute;
    left: 10px;
    top: 24px;
    bottom: -16px;
    width: 1px;
    background: var(--color-border);
}
.timeline-item:last-child { margin-bottom: 0; }
.timeline-item:last-child::after { display: none; }

.timeline-icon {
    width: 22px;
    height: 22px;
    border-radius: 50%;
    background: var(--color-background-dark);
    border: 1px solid var(--color-border);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1;
    color: var(--color-text-maxcontrast);
}
.text-success { color: var(--color-success); border-color: var(--color-success); }
.text-warning { color: var(--color-warning); border-color: var(--color-warning); }
.text-error { color: var(--color-error); border-color: var(--color-error); }

.timeline-content { flex: 1; margin-top: 2px; }
.timeline-title { display: flex; justify-content: space-between; font-size: 13px; font-weight: 600; }
.timeline-title .date { font-weight: normal; color: var(--color-text-maxcontrast); font-size: 11px; }
.timeline-desc { font-size: 12px; color: var(--color-text-maxcontrast); margin-top: 2px; }
.btn-link { background: none; border: none; color: var(--color-primary-element); cursor: pointer; font-size: 12px; }

/* 4. Modals */
.modal-backdrop {
    position: fixed;
    top: 0; left: 0;
    width: 100%; height: 100%;
    background: rgba(0, 0, 0, 0.4);
    z-index: 10000;
    display: flex;
    align-items: center;
    justify-content: center;
    backdrop-filter: blur(2px);
}

.nc-modal {
    background: var(--color-main-background);
    width: 450px;
    border-radius: var(--border-radius-large);
    box-shadow: 0 10px 40px rgba(0,0,0,0.2);
    display: flex;
    flex-direction: column;
}

.modal-header {
    padding: 16px 20px;
    border-bottom: 1px solid var(--color-border);
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.modal-header h3 { margin: 0; font-size: 16px; }
.close-btn { background: none; border: none; cursor: pointer; color: var(--color-text-maxcontrast); padding: 4px; }

.modal-body { padding: 20px; }

.form-group { margin-bottom: 16px; }
.form-group label { display: block; font-size: 13px; font-weight: 600; margin-bottom: 6px; color: var(--color-text-maxcontrast); }

.nc-input, .nc-select, .nc-textarea {
    width: 100%;
    padding: 8px 12px;
    border: 1px solid var(--color-border);
    border-radius: var(--border-radius);
    background: var(--color-main-background);
    color: var(--color-main-text);
    font-size: 14px;
    box-sizing: border-box;
}
.nc-input.disabled { background: var(--color-background-dark); color: var(--color-text-maxcontrast); }
.nc-input:focus, .nc-select:focus, .nc-textarea:focus {
    border-color: var(--color-primary-element);
    outline: none;
    box-shadow: 0 0 0 2px var(--color-primary-element-light);
}

.modal-footer {
    padding: 16px 20px;
    border-top: 1px solid var(--color-border);
    display: flex;
    justify-content: flex-end;
    gap: 12px;
    background: var(--color-background-hover);
    border-radius: 0 0 var(--border-radius-large) var(--border-radius-large);
}

@media (max-width: 900px) {
    .detail-grid { grid-template-columns: 1fr; }
    .header-toolbar { width: 100%; overflow-x: auto; padding-bottom: 5px; }
    .action-group.operations { border-right: none; margin-right: 0; }
}
</style>