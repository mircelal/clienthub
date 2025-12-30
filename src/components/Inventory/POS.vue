<template>
    <div class="pos-layout">
        <!-- HEADER -->
        <header class="pos-top-bar">
            <div class="top-bar-left">
                <button class="nav-back-btn" @click="$emit('close')" :title="t('domaincontrol', 'Back to Inventory')">
                    <ArrowLeft :size="24" />
                </button>
                <h1 class="app-title">{{ t('domaincontrol', 'POS') }}</h1>
                
                <div class="view-switcher desktop-only">
                    <button 
                        class="switch-btn" 
                        :class="{ active: activeView === 'catalog' }"
                        @click="activeView = 'catalog'"
                    >
                        {{ t('domaincontrol', 'Catalog') }}
                    </button>
                    <button 
                        class="switch-btn" 
                        :class="{ active: activeView === 'sales' }"
                        @click="activeView = 'sales'"
                    >
                        {{ t('domaincontrol', 'History') }}
                    </button>
                </div>
            </div>

            <div class="top-bar-right">
                <!-- Advanced Transaction Toggle -->
                <div class="segmented-control">
                    <div class="segment-backdrop" :class="transactionType"></div>
                    <button 
                        class="segment-btn" 
                        :class="{ active: transactionType === 'sale' }"
                        @click="transactionType = 'sale'"
                    >
                        {{ t('domaincontrol', 'Sale') }}
                    </button>
                    <button 
                        class="segment-btn" 
                        :class="{ active: transactionType === 'rental' }"
                        @click="transactionType = 'rental'"
                    >
                        {{ t('domaincontrol', 'Rental') }}
                    </button>
                </div>
            </div>
        </header>

        <!-- MAIN GRID -->
        <main class="pos-grid">
            
            <!-- LEFT PANEL: Catalog/History -->
            <section class="pos-left-panel">
                
                <!-- CATALOG VIEW -->
                <div v-if="activeView === 'catalog'" class="panel-content">
                    <div class="search-header">
                        <div class="search-input-wrapper">
                            <Magnify :size="20" class="search-icon" />
                            <input
                                type="text"
                                v-model="productSearch"
                                :placeholder="t('domaincontrol', 'Search product name or SKU...')"
                                class="pos-search-input"
                                autofocus
                            />
                            <button v-if="productSearch" @click="productSearch = ''" class="clear-icon">
                                <Close :size="16" />
                            </button>
                        </div>
                    </div>

                    <!-- NEW: Product List View -->
                    <div class="product-list-container">
                        <div class="product-list-header">
                            <span class="col-name">{{ t('domaincontrol', 'Product') }}</span>
                            <span class="col-stock">{{ t('domaincontrol', 'Stock') }}</span>
                            <span class="col-price text-right">{{ t('domaincontrol', 'Price') }}</span>
                            <span class="col-action"></span>
                        </div>
                        
                        <div class="product-list-body">
                            <div
                                v-for="item in filteredProducts"
                                :key="item.id"
                                class="product-row"
                                :class="{ 
                                    'disabled': item.status !== 'available' || (item.quantity ?? 0) <= 0,
                                    'low-stock': (item.quantity ?? 0) > 0 && (item.quantity ?? 0) < 5
                                }"
                                @click="addToCart(item)"
                            >
                                <div class="col-name">
                                    <div class="p-name">{{ item.name }}</div>
                                    <div class="p-sku">{{ item.sku || '-' }}</div>
                                </div>
                                <div class="col-stock">
                                    <span class="stock-pill" :class="getStockClass(item)">
                                        {{ item.quantity ?? 0 }}
                                    </span>
                                </div>
                                <div class="col-price text-right">
                                    <div class="price-val">
                                        {{ formatCurrency(transactionType === 'sale' ? item.salePrice : item.rentalPrice) }}
                                    </div>
                                    <div v-if="transactionType === 'rental'" class="price-unit">/{{ t('domaincontrol', 'day') }}</div>
                                </div>
                                <div class="col-action">
                                    <button class="add-icon-btn">
                                        <Plus :size="18" />
                                    </button>
                                </div>
                            </div>
                            
                            <div v-if="filteredProducts.length === 0" class="no-results">
                                {{ t('domaincontrol', 'No products found.') }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- HISTORY VIEW -->
                <div v-else class="panel-content history-mode">
                    <div class="history-filters">
                        <button class="filter-chip" :class="{ active: activeHistoryTab === 'sales' }" @click="activeHistoryTab = 'sales'">{{ t('domaincontrol', 'Sales') }}</button>
                        <button class="filter-chip" :class="{ active: activeHistoryTab === 'rentals' }" @click="activeHistoryTab = 'rentals'">{{ t('domaincontrol', 'Rentals') }}</button>
                        <button class="filter-chip" :class="{ active: activeHistoryTab === 'returns' }" @click="activeHistoryTab = 'returns'">{{ t('domaincontrol', 'Returns') }}</button>
                    </div>

                    <div class="history-list-wrapper">
                        <table class="nc-pos-table">
                            <thead>
                                <tr>
                                    <th>{{ t('domaincontrol', 'ID') }}</th>
                                    <th>{{ t('domaincontrol', 'Product') }}</th>
                                    <th>{{ t('domaincontrol', 'Customer') }}</th>
                                    <th class="hide-mobile">{{ t('domaincontrol', 'Date') }}</th>
                                    <th class="text-right">{{ t('domaincontrol', 'Status') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="item in currentHistoryList" :key="item.id" @click="showTransactionDetail(item)">
                                    <td class="text-mono">#{{ item.id }}</td>
                                    <td class="fw-bold">
                                        {{ item.itemName }}
                                        <div class="mobile-only-date">{{ formatDate(item.dateOut) }}</div>
                                    </td>
                                    <td>{{ item.customerName }}</td>
                                    <td class="hide-mobile">
                                        {{ formatDate(item.dateOut) }}
                                    </td>
                                    <td class="text-right">
                                        <span v-if="item.dateReturned" class="status-badge returned">R</span>
                                        <span v-else-if="isOverdue(item)" class="status-badge overdue">!</span>
                                        <span v-else class="status-badge active">OK</span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <!-- RIGHT PANEL: Cart & Checkout -->
            <!-- Mobile: Shown as overlay if showMobileCart is true -->
            <aside class="pos-right-panel" :class="{ 'mobile-open': showMobileCart }">
                <div class="mobile-cart-header">
                    <h2>{{ t('domaincontrol', 'Cart') }}</h2>
                    <button class="icon-btn" @click="showMobileCart = false">
                        <Close :size="24" />
                    </button>
                </div>

                <!-- Customer Selection -->
                <div class="customer-section">
                    <div v-if="!selectedCustomer" class="customer-search-box">
                        <div class="c-search-input-wrapper">
                            <Magnify :size="18" />
                            <input 
                                type="text" 
                                v-model="customerSearch" 
                                :placeholder="t('domaincontrol', 'Select Customer...')" 
                            />
                        </div>
                        <div v-if="customerSearch && filteredClients.length > 0" class="c-dropdown">
                            <div 
                                v-for="client in filteredClients" 
                                :key="client.id" 
                                class="c-item"
                                @click="selectCustomer(client)"
                            >
                                <div class="c-avatar-mini" :style="{ backgroundColor: getAvatarColor(client.name) }">{{ getInitials(client.name) }}</div>
                                <div class="c-info">
                                    <div class="c-name">{{ client.name }}</div>
                                    <div class="c-sub">{{ client.company }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div v-else class="selected-customer-display">
                        <div class="c-avatar" :style="{ backgroundColor: getAvatarColor(selectedCustomer.name) }">
                            {{ getInitials(selectedCustomer.name) }}
                        </div>
                        <div class="c-info-display">
                            <div class="c-name">{{ selectedCustomer.name }}</div>
                            <div class="c-sub">{{ selectedCustomer.email || selectedCustomer.company || 'Customer' }}</div>
                        </div>
                        <button class="c-remove-btn" @click="selectedCustomer = null">
                            <Close :size="18" />
                        </button>
                    </div>
                </div>

                <!-- Cart List -->
                <div class="cart-container">
                    <div v-if="cart.length === 0" class="empty-cart">
                        <CartOutline :size="48" class="empty-icon" />
                        <p>{{ t('domaincontrol', 'Cart is empty') }}</p>
                    </div>
                    <div v-else class="cart-items-list">
                        <div v-for="(item, index) in cart" :key="index" class="cart-row">
                            <div class="cart-row-left">
                                <div class="cart-item-name">{{ item.name }}</div>
                                <div class="cart-item-meta">
                                    <span class="type-badge" :class="item.type">{{ item.type === 'sale' ? 'S' : 'R' }}</span>
                                    <span v-if="item.rentalDays">{{ item.rentalDays }}d</span>
                                </div>
                            </div>
                            <div class="cart-row-right">
                                <div class="qty-stepper">{{ item.quantity }}x</div>
                                <div class="item-price">{{ formatCurrency(item.price) }}</div>
                                <button class="delete-btn" @click="removeFromCart(index)">
                                    <Delete :size="18" />
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="checkout-footer">
                    <div v-if="transactionType === 'rental'" class="setting-row">
                        <label>{{ t('domaincontrol', 'Duration') }}</label>
                        <div class="duration-stepper">
                            <button class="stepper-btn" @click="updateDuration(-1)" :disabled="defaultRentalDays <= 1">
                                <Minus :size="16" />
                            </button>
                            <div class="stepper-value">
                                <span class="val">{{ defaultRentalDays }}</span>
                                <span class="unit">{{ t('domaincontrol', 'Days') }}</span>
                            </div>
                            <button class="stepper-btn" @click="updateDuration(1)">
                                <Plus :size="16" />
                            </button>
                        </div>
                    </div>
                    <div class="totals-area">
                        <div class="total-row grand-total">
                            <span class="label">{{ t('domaincontrol', 'Total') }}</span>
                            <span class="value">{{ formatCurrency(cartSubtotal) }}</span>
                        </div>
                    </div>
                    <div class="action-buttons">
                        <button class="btn-clear" @click="clearCart" :disabled="cart.length === 0">
                            <Delete :size="18" />
                        </button>
                        <button 
                            class="btn-checkout" 
                            :class="{ 'rental-action': transactionType === 'rental' }"
                            :disabled="cart.length === 0 || !selectedCustomer || loading"
                            @click="processTransaction"
                        >
                            <span v-if="loading" class="spinner"></span>
                            <span v-else>
                                {{ transactionType === 'sale' ? t('domaincontrol', 'Checkout') : t('domaincontrol', 'Rent') }}
                            </span>
                        </button>
                    </div>
                </div>
            </aside>
        </main>

        <!-- MOBILE BOTTOM BAR (Triggers Cart) -->
        <div class="mobile-bottom-bar" v-if="!showMobileCart">
            <div class="mobile-bar-info" @click="showMobileCart = true">
                <div class="mb-label">{{ t('domaincontrol', 'Cart') }} ({{ cart.length }})</div>
                <div class="mb-total">{{ formatCurrency(cartSubtotal) }}</div>
            </div>
            <button class="mobile-view-btn" @click="showMobileCart = true">
                <ChevronUp :size="24" />
            </button>
        </div>

        <!-- Transaction Detail Modal -->
        <div v-if="selectedTransaction" class="modal-backdrop" @click="selectedTransaction = null">
            <div class="pos-modal" @click.stop>
                <div class="modal-header">
                    <h3>#{{ selectedTransaction.id }}</h3>
                    <button class="icon-btn" @click="selectedTransaction = null"><Close :size="24" /></button>
                </div>
                <div class="modal-body">
                    <div class="info-row">
                        <label>{{ t('domaincontrol', 'Product') }}</label>
                        <div class="val">{{ selectedTransaction.itemName }}</div>
                    </div>
                    <div class="info-row">
                        <label>{{ t('domaincontrol', 'Customer') }}</label>
                        <div class="val">{{ selectedTransaction.customerName }}</div>
                    </div>
                    <div class="info-row">
                        <label>{{ t('domaincontrol', 'Date Out') }}</label>
                        <div class="val">{{ formatDate(selectedTransaction.dateOut) }}</div>
                    </div>
                    <div class="info-row">
                        <label>{{ t('domaincontrol', 'Price') }}</label>
                        <div class="val highlight">{{ formatCurrency(selectedTransaction.price) }}</div>
                    </div>
                </div>
                <div class="modal-footer">
                     <NcButton type="secondary" @click="selectedTransaction = null">{{ t('domaincontrol', 'Close') }}</NcButton>
                     <NcButton v-if="!selectedTransaction.dateReturned" type="primary" @click="handleReturnFromDetail">{{ t('domaincontrol', 'Return') }}</NcButton>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { NcButton } from '@nextcloud/vue'
import api from '../../services/api'
import ArrowLeft from 'vue-material-design-icons/ArrowLeft.vue'
import Magnify from 'vue-material-design-icons/Magnify.vue'
import Delete from 'vue-material-design-icons/Delete.vue'
import Close from 'vue-material-design-icons/Close.vue'
import CartOutline from 'vue-material-design-icons/CartOutline.vue'
import Plus from 'vue-material-design-icons/Plus.vue'
import Minus from 'vue-material-design-icons/Minus.vue'
import ChevronUp from 'vue-material-design-icons/ChevronUp.vue'

export default {
    name: 'POS',
    components: {
        NcButton, ArrowLeft, Magnify, Delete, Close, CartOutline, Plus, Minus, ChevronUp
    },
    props: {
        items: { type: Array, default: () => [] },
        clients: { type: Array, default: () => [] },
    },
    emits: ['close'],
    data() {
        return {
            selectedCustomer: null,
            customerSearch: '',
            productSearch: '',
            cart: [],
            transactionType: 'sale',
            defaultRentalDays: 7,
            activeView: 'catalog',
            activeHistoryTab: 'sales',
            recentSales: [],
            activeRentals: [],
            returns: [],
            loading: false,
            selectedTransaction: null,
            showMobileCart: false, // Control mobile cart overlay
        }
    },
    computed: {
        filteredClients() {
            if (!this.customerSearch) return []
            const search = this.customerSearch.toLowerCase()
            return this.clients.filter(client =>
                client.name?.toLowerCase().includes(search) ||
                client.company?.toLowerCase().includes(search)
            ).slice(0, 8) 
        },
        filteredProducts() {
            let products = this.items.filter(item => item.status === 'available')
            if (this.productSearch) {
                const search = this.productSearch.toLowerCase()
                products = products.filter(item =>
                    item.name?.toLowerCase().includes(search) ||
                    item.sku?.toLowerCase().includes(search)
                )
            }
            return products
        },
        cartSubtotal() {
            return this.cart.reduce((sum, item) => sum + (item.price || 0), 0)
        },
        currentHistoryList() {
            if (this.activeHistoryTab === 'sales') return this.recentSales
            if (this.activeHistoryTab === 'rentals') return this.activeRentals
            if (this.activeHistoryTab === 'returns') return this.returns
            return []
        }
    },
    watch: {
        activeView(val) {
            if(val === 'sales') this.loadTransactions()
        },
        activeHistoryTab() {
            this.loadTransactions()
        }
    },
    methods: {
        t(appId, text, vars) {
            try {
                if (typeof window.OC !== 'undefined' && window.OC.L10n) {
                    return window.OC.L10n.translate(appId, text, vars || {})
                }
            } catch (e) { /* ignore */ }
            return text
        },
        getAvatarColor(name) {
            if (!name) return 'var(--color-primary-element)'
            const colors = ['#0082c9', '#46ba61', '#f0ad4e', '#e3322d', '#5bc0de', '#9b59b6']
            let hash = 0
            for (let i = 0; i < name.length; i++) hash = name.charCodeAt(i) + ((hash << 5) - hash)
            return colors[Math.abs(hash) % colors.length]
        },
        getInitials(name) {
            if (!name) return '?'
            return name.substring(0, 2).toUpperCase()
        },
        selectCustomer(client) {
            this.selectedCustomer = client
            this.customerSearch = '' 
        },
        addToCart(item) {
            if (item.status !== 'available' || (item.quantity ?? 0) <= 0) return

            const price = this.transactionType === 'sale' ? (item.salePrice || 0) : (item.rentalPrice || 0)
            const rentalDays = this.transactionType === 'rental' ? this.defaultRentalDays : null
            const unitPrice = this.transactionType === 'rental' ? price * (rentalDays || 1) : price

            const existingIndex = this.cart.findIndex(
                c => c.inventoryId === item.id && c.type === this.transactionType
            )

            if (existingIndex !== -1) {
                this.cart[existingIndex].quantity++
                this.cart[existingIndex].price += unitPrice 
            } else {
                this.cart.push({
                    inventoryId: item.id,
                    name: item.name,
                    sku: item.sku,
                    type: this.transactionType,
                    price: unitPrice,
                    quantity: 1,
                    rentalDays: rentalDays,
                })
            }
        },
        removeFromCart(index) {
            this.cart.splice(index, 1)
        },
        clearCart() {
            this.cart = []
        },
        updateDuration(delta) {
            const newVal = this.defaultRentalDays + delta
            if (newVal >= 1) {
                this.defaultRentalDays = newVal
            }
        },
        formatCurrency(value) {
            return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(value || 0)
        },
        formatDate(dateString) {
            if (!dateString) return ''
            return new Date(dateString).toLocaleDateString()
        },
        isOverdue(transaction) {
            if (!transaction.dateDue || transaction.dateReturned) return false
            return new Date(transaction.dateDue) < new Date()
        },
        getStockClass(item) {
            const qty = item.quantity ?? 0
            if (qty === 0) return 'status-error'
            if (qty < 5) return 'status-warning'
            return 'status-ok'
        },
        showTransactionDetail(item) {
            this.selectedTransaction = item
        },
        async processTransaction() {
            if (this.cart.length === 0 || !this.selectedCustomer) return
            this.loading = true
            try {
                const items = []
                for (const cItem of this.cart) {
                    const singlePrice = cItem.price / cItem.quantity
                    for (let i = 0; i < cItem.quantity; i++) {
                        items.push({
                            inventoryId: cItem.inventoryId,
                            price: singlePrice,
                            rentalDays: cItem.rentalDays,
                            notes: '',
                            quantity: 1
                        })
                    }
                }
                await api.pos.createOrder({
                    clientId: this.selectedCustomer.id,
                    type: this.transactionType,
                    items: JSON.stringify(items),
                    notes: '',
                })
                this.clearCart()
                this.showMobileCart = false // Close on mobile
                if(this.activeView === 'sales') this.loadTransactions()
                alert(this.t('domaincontrol', 'Transaction successful'))
            } catch (e) {
                console.error(e)
                alert(this.t('domaincontrol', 'Error'))
            } finally {
                this.loading = false
            }
        },
        async returnRental(rental) {
            if (!confirm(this.t('domaincontrol', 'Confirm return?'))) return
            this.loading = true
            try {
                await api.pos.returnRental(rental.id)
                await this.loadTransactions()
                this.selectedTransaction = null
            } catch (e) { console.error(e) } finally { this.loading = false }
        },
        async returnSale(sale) {
            if (!confirm(this.t('domaincontrol', 'Confirm return?'))) return
            this.loading = true
            try {
                await api.pos.returnSale(sale.id)
                await this.loadTransactions()
                this.selectedTransaction = null
            } catch (e) { console.error(e) } finally { this.loading = false }
        },
        handleReturnFromDetail() {
            if (!this.selectedTransaction) return
            if (this.selectedTransaction.type === 'rent') this.returnRental(this.selectedTransaction)
            else this.returnSale(this.selectedTransaction)
        },
        async loadTransactions() {
            this.loading = true
            try {
                const [sales, rentals, rets] = await Promise.all([
                    api.pos.getRecentSales().catch(() => ({ data: [] })),
                    api.pos.getActiveRentals().catch(() => ({ data: [] })),
                    api.pos.getReturns().catch(() => ({ data: [] })),
                ])
                this.recentSales = sales.data || []
                this.activeRentals = rentals.data || []
                this.returns = rets.data || []
            } catch (e) { console.error(e) } finally { this.loading = false }
        }
    }
}
</script>

<style scoped>
/* BASE LAYOUT */
.pos-layout {
    display: flex;
    flex-direction: column;
    width: 100%;
    height: 100%;
    background-color: var(--color-main-background);
    color: var(--color-main-text);
    font-family: var(--font-face, sans-serif);
    overflow: hidden;
    position: relative;
}

/* TOP BAR */
.pos-top-bar {
    height: 60px;
    background: var(--color-main-background);
    border-bottom: 1px solid var(--color-border);
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0 16px;
    flex-shrink: 0;
}
.top-bar-left { display: flex; align-items: center; gap: 12px; }
.nav-back-btn { background: none; border: none; cursor: pointer; color: var(--color-text-maxcontrast); padding: 4px; border-radius: 50%; }
.nav-back-btn:hover { background: var(--color-background-hover); }
.app-title { font-size: 18px; font-weight: 700; margin: 0; }

.view-switcher {
    background: var(--color-background-dark);
    padding: 3px;
    border-radius: 8px;
    display: flex;
    margin-left: 20px;
}
.switch-btn {
    border: none;
    background: transparent;
    padding: 6px 12px;
    border-radius: 6px;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    color: var(--color-text-maxcontrast);
    transition: all 0.2s;
}
.switch-btn.active { background: var(--color-main-background); color: var(--color-main-text); box-shadow: 0 1px 2px rgba(0,0,0,0.1); }

/* SEGMENTED CONTROL (NEW) */
.segmented-control {
    position: relative;
    display: flex;
    background: var(--color-background-dark);
    padding: 3px;
    border-radius: 20px;
    box-shadow: inset 0 1px 3px rgba(0,0,0,0.05);
}
.segment-backdrop {
    position: absolute;
    top: 3px; left: 3px; bottom: 3px;
    width: calc(50% - 3px);
    background: var(--color-main-background);
    border-radius: 18px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    transition: transform 0.25s cubic-bezier(0.2, 0.0, 0.2, 1);
    pointer-events: none;
}
.segment-backdrop.rental { transform: translateX(100%); }
.segment-btn {
    position: relative;
    z-index: 1;
    flex: 1;
    border: none;
    background: transparent;
    padding: 6px 24px;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    color: var(--color-text-maxcontrast);
    min-width: 80px;
    transition: color 0.2s;
}
.segment-btn.active { color: var(--color-primary-element); }

/* MAIN GRID */
.pos-grid {
    display: grid;
    grid-template-columns: 1fr 400px; /* Default desktop split */
    flex: 1;
    min-height: 0;
    overflow: hidden;
}

/* LEFT PANEL (Catalog) */
.pos-left-panel {
    background: var(--color-background-hover);
    border-right: 1px solid var(--color-border);
    display: flex;
    flex-direction: column;
    overflow: hidden;
}
.panel-content { padding: 16px; display: flex; flex-direction: column; height: 100%; }

.search-header { margin-bottom: 16px; }
.search-input-wrapper {
    background: var(--color-main-background);
    border: 1px solid var(--color-border);
    border-radius: 10px;
    display: flex;
    align-items: center;
    padding: 0 12px;
    height: 44px;
}
.pos-search-input { border: none; background: transparent; font-size: 15px; flex: 1; margin-left: 8px; }
.pos-search-input:focus { outline: none; }

/* NEW PRODUCT LIST VIEW */
.product-list-container { flex: 1; display: flex; flex-direction: column; background: var(--color-main-background); border-radius: 8px; border: 1px solid var(--color-border); overflow: hidden; }
.product-list-header {
    display: flex;
    padding: 10px 16px;
    background: var(--color-background-dark);
    border-bottom: 1px solid var(--color-border);
    font-size: 12px;
    font-weight: 700;
    color: var(--color-text-maxcontrast);
    text-transform: uppercase;
}
.product-list-body { flex: 1; overflow-y: auto; }
.product-row {
    display: flex;
    align-items: center;
    padding: 12px 16px;
    border-bottom: 1px solid var(--color-border);
    cursor: pointer;
    transition: background 0.1s;
}
.product-row:hover { background: var(--color-background-hover); }
.product-row.disabled { opacity: 0.5; pointer-events: none; }

/* Columns */
.col-name { flex: 1; min-width: 0; }
.col-stock { width: 80px; text-align: center; }
.col-price { width: 100px; }
.col-action { width: 40px; display: flex; justify-content: flex-end; }

.p-name { font-weight: 600; font-size: 14px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.p-sku { font-size: 11px; color: var(--color-text-maxcontrast); }
.stock-pill { padding: 2px 8px; border-radius: 10px; font-size: 11px; font-weight: 600; background: var(--color-background-dark); }
.stock-pill.status-ok { color: var(--color-success); background: var(--color-success-light); }
.stock-pill.status-warning { color: var(--color-warning); background: var(--color-warning-light); }
.price-val { font-weight: 700; font-size: 14px; }
.price-unit { font-size: 10px; color: var(--color-text-maxcontrast); }
.add-icon-btn { border: none; background: var(--color-background-dark); width: 28px; height: 28px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: var(--color-primary-element); cursor: pointer; }
.product-row:hover .add-icon-btn { background: var(--color-primary-element); color: white; }

/* HISTORY VIEW */
.history-filters { display: flex; gap: 8px; margin-bottom: 16px; overflow-x: auto; padding-bottom: 4px; }
.filter-chip { border: 1px solid var(--color-border); background: var(--color-main-background); padding: 6px 12px; border-radius: 16px; cursor: pointer; font-size: 13px; font-weight: 600; white-space: nowrap; transition: all 0.2s; }
.filter-chip:hover { background: var(--color-background-hover); }
.filter-chip.active { background: var(--color-text-light); color: var(--color-main-background); border-color: var(--color-text-light); }

.history-list-wrapper { flex: 1; overflow-y: auto; background: var(--color-main-background); border: 1px solid var(--color-border); border-radius: 8px; }
.nc-pos-table { width: 100%; border-collapse: collapse; }
.nc-pos-table th { position: sticky; top: 0; background: var(--color-background-dark); z-index: 10; text-align: left; padding: 12px 16px; font-size: 12px; font-weight: 700; color: var(--color-text-maxcontrast); border-bottom: 1px solid var(--color-border); }
.nc-pos-table td { padding: 12px 16px; border-bottom: 1px solid var(--color-border); font-size: 14px; color: var(--color-main-text); }
.nc-pos-table tr { cursor: pointer; transition: background 0.1s; }
.nc-pos-table tr:hover { background: var(--color-background-hover); }

.text-mono { font-family: monospace; color: var(--color-text-maxcontrast); font-size: 12px; }
.fw-bold { font-weight: 600; }
.text-right { text-align: right; }

.status-badge { display: inline-flex; align-items: center; justify-content: center; min-width: 30px; height: 24px; padding: 0 8px; border-radius: 4px; font-size: 11px; font-weight: 700; }
.status-badge.active { background: var(--color-success-light); color: var(--color-success); }
.status-badge.returned { background: var(--color-background-dark); color: var(--color-text-maxcontrast); }
.status-badge.overdue { background: var(--color-error-light); color: var(--color-error); }

/* RIGHT PANEL (Cart) */
.pos-right-panel {
    background: var(--color-main-background);
    display: flex;
    flex-direction: column;
    z-index: 50;
    transition: transform 0.3s ease;
}
.mobile-cart-header { display: none; padding: 16px; border-bottom: 1px solid var(--color-border); justify-content: space-between; align-items: center; }
.customer-section { padding: 16px; border-bottom: 1px solid var(--color-border); background: var(--color-background-dark); }
.c-search-input-wrapper { background: var(--color-main-background); border: 1px solid var(--color-border); border-radius: 6px; display: flex; align-items: center; padding: 6px 10px; }
.c-search-input-wrapper input { border: none; background: transparent; width: 100%; margin-left: 8px; font-size: 13px; }
.c-dropdown { position: absolute; width: 360px; background: var(--color-main-background); border: 1px solid var(--color-border); box-shadow: 0 4px 12px rgba(0,0,0,0.15); border-radius: 6px; margin-top: 4px; z-index: 100; max-height: 200px; overflow-y: auto; }
.c-item { display: flex; align-items: center; padding: 8px; cursor: pointer; border-bottom: 1px solid var(--color-border); }
.c-avatar-mini { width: 24px; height: 24px; border-radius: 50%; color: white; display: flex; align-items: center; justify-content: center; font-size: 10px; margin-right: 8px; }

.selected-customer-display { display: flex; align-items: center; gap: 10px; }
.c-avatar { width: 32px; height: 32px; border-radius: 50%; color: white; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 12px; }
.c-info-display { flex: 1; }
.c-name { font-weight: 700; font-size: 13px; }
.c-sub { font-size: 11px; color: var(--color-text-maxcontrast); }

.cart-container { flex: 1; overflow-y: auto; }
.empty-cart { height: 100%; display: flex; flex-direction: column; align-items: center; justify-content: center; color: var(--color-text-maxcontrast); opacity: 0.5; }
.cart-row { display: flex; justify-content: space-between; padding: 12px 16px; border-bottom: 1px solid var(--color-border); }
.cart-item-name { font-weight: 600; font-size: 13px; }
.cart-row-right { display: flex; align-items: center; gap: 10px; }
.qty-stepper { font-size: 13px; font-weight: bold; color: var(--color-text-maxcontrast); }
.item-price { font-weight: 700; font-size: 14px; width: 60px; text-align: right; }

.checkout-footer { padding: 16px; background: var(--color-main-background); border-top: 1px solid var(--color-border); }
.totals-area { margin-bottom: 12px; }
.total-row.grand-total { display: flex; justify-content: space-between; font-size: 18px; font-weight: 800; }
.action-buttons { display: flex; gap: 8px; height: 44px; }
.btn-clear { width: 50px; border: 1px solid var(--color-border); background: white; color: var(--color-error); border-radius: 6px; cursor: pointer; display: flex; align-items: center; justify-content: center;}
.btn-checkout { flex: 1; border: none; background: var(--color-success); color: white; border-radius: 6px; font-weight: 700; cursor: pointer; }
.btn-checkout.rental-action { background: var(--color-primary-element); }
.btn-checkout:disabled { opacity: 0.5; }

/* DURATION STEPPER */
.setting-row { display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; font-size: 14px; }
.duration-stepper { display: flex; align-items: center; background: var(--color-background-dark); border-radius: 8px; padding: 4px; }
.stepper-btn { background: var(--color-main-background); border: 1px solid var(--color-border); border-radius: 6px; width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; cursor: pointer; color: var(--color-text-maxcontrast); transition: all 0.2s; }
.stepper-btn:hover:not(:disabled) { background: var(--color-background-hover); color: var(--color-main-text); }
.stepper-btn:disabled { opacity: 0.5; cursor: not-allowed; }
.stepper-value { display: flex; align-items: baseline; gap: 4px; padding: 0 12px; min-width: 60px; justify-content: center; }
.stepper-value .val { font-weight: 700; font-size: 16px; }
.stepper-value .unit { font-size: 12px; color: var(--color-text-maxcontrast); }

/* MOBILE BOTTOM BAR */
.mobile-bottom-bar { display: none; height: 60px; background: var(--color-main-background); border-top: 1px solid var(--color-border); padding: 0 16px; align-items: center; justify-content: space-between; position: absolute; bottom: 0; left: 0; width: 100%; z-index: 40; box-shadow: 0 -2px 10px rgba(0,0,0,0.05); }
.mobile-bar-info { display: flex; flex-direction: column; cursor: pointer; }
.mb-label { font-size: 12px; color: var(--color-text-maxcontrast); }
.mb-total { font-weight: 800; font-size: 16px; color: var(--color-primary-element); }
.mobile-view-btn { background: none; border: none; color: var(--color-text-maxcontrast); }

/* MODAL STYLES */
.modal-backdrop { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.6); z-index: 2000; display: flex; align-items: center; justify-content: center; backdrop-filter: blur(2px); }
.pos-modal { background: var(--color-main-background); width: 400px; border-radius: 16px; box-shadow: 0 20px 50px rgba(0,0,0,0.3); overflow: hidden; display: flex; flex-direction: column; max-height: 90vh; }
.modal-header { padding: 20px; border-bottom: 1px solid var(--color-border); display: flex; justify-content: space-between; align-items: center; }
.modal-header h3 { margin: 0; font-size: 18px; }
.icon-btn { background: none; border: none; cursor: pointer; color: var(--color-text-maxcontrast); padding: 4px; border-radius: 50%; display: flex; align-items: center; justify-content: center; }
.icon-btn:hover { background: var(--color-background-hover); }
.modal-body { padding: 20px; overflow-y: auto; }
.info-row { display: flex; justify-content: space-between; margin-bottom: 12px; font-size: 14px; }
.info-row label { color: var(--color-text-maxcontrast); }
.info-row .val { font-weight: 600; }
.info-row .val.highlight { color: var(--color-primary-element); font-size: 18px; }
.modal-footer { padding: 20px; background: var(--color-background-dark); display: flex; justify-content: flex-end; gap: 10px; border-top: 1px solid var(--color-border); }

/* RESPONSIVE */
@media (max-width: 900px) {
    .pos-grid { grid-template-columns: 1fr; padding-bottom: 60px; /* Space for bottom bar */ }
    
    .pos-right-panel {
        position: fixed;
        top: 0; left: 0; width: 100%; height: 100%;
        transform: translateY(100%); /* Hidden by default */
        z-index: 2000;
    }
    .pos-right-panel.mobile-open { transform: translateY(0); }
    .mobile-cart-header { display: flex; }
    .mobile-bottom-bar { display: flex; }
    .hide-mobile { display: none; }
    .desktop-only { display: none; }
    .mobile-only-date { display: block; font-size: 10px; color: var(--color-text-maxcontrast); font-weight: normal; }
    .c-dropdown { width: 300px; }
}
</style>