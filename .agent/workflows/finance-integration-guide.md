# Finance Section Redesign - Implementation Guide

## Overview

This document provides step-by-step instructions for integrating the redesigned `ProjectFinancials.vue` component into the parent `Projects.vue` component.

---

## Changes Made to ProjectFinancials.vue

### 1. **New Event Emitted**
```javascript
emits: ['create-invoice', 'navigate-invoice', 'add-expense', 'expense-deleted', 'invoice-updated']
```

**New event:** `invoice-updated`
- **When:** User clicks "Mark as Paid" button on an invoice
- **Purpose:** Notify parent to refresh financial data after invoice status change

### 2. **Removed Features**
- Expandable invoice cards with detailed items/payments view
- Individual invoice details loading
- "Remaining Receivable" metric (duplicate of "Pending")
- Budget-based net profit calculation

### 3. **New Features**
- Hero metric showing Net Result (Collected - Expenses)
- Sortable invoice table
- "Mark as Paid" quick action
- Simplified expense list with expand/collapse

---

## Required Parent Component Updates

### Step 1: Add Event Handler in Template

In `Projects.vue`, line 107-120, add the new event handler:

```vue
<ProjectFinancials
    v-if="activeTab === 'financials' && selectedProject"
    :project="selectedProject"
    :invoices="projectInvoices"
    :total-invoiced="totalInvoiced"
    :total-paid="totalPaid"
    :total-pending="totalPending"
    :total-expenses="totalExpenses"
    :expenses="projectExpenses"
    @create-invoice="createInvoiceFromProject"
    @navigate-invoice="navigateToInvoice"
    @add-expense="showAddExpenseModal"
    @expense-deleted="handleExpenseDeleted"
    @invoice-updated="handleInvoiceUpdated"
/>
```

**Change:** Add `@invoice-updated="handleInvoiceUpdated"`

---

### Step 2: Implement Missing Methods

Add these methods to the `methods` section of `Projects.vue`:

```javascript
// ==========================================
// FINANCIAL DATA LOADING
// ==========================================

async loadProjectFinancials(projectId) {
    try {
        // Load invoices
        const invoicesResponse = await api.invoices.getByProject(projectId)
        this.projectInvoices = invoicesResponse.data || []
        
        // Load expenses
        const expensesResponse = await api.transactions.getByProject(projectId)
        this.projectExpenses = expensesResponse.data || []
        
        // Calculate totals
        this.calculateFinancialTotals()
    } catch (error) {
        console.error('Error loading project financials:', error)
        this.projectInvoices = []
        this.projectExpenses = []
        this.totalInvoiced = 0
        this.totalPaid = 0
        this.totalPending = 0
        this.totalExpenses = 0
    }
},

calculateFinancialTotals() {
    // Calculate invoice totals
    this.totalInvoiced = this.projectInvoices.reduce((sum, inv) => {
        return sum + parseFloat(inv.totalAmount || inv.total || 0)
    }, 0)
    
    this.totalPaid = this.projectInvoices.reduce((sum, inv) => {
        if (inv.status === 'paid') {
            return sum + parseFloat(inv.paidAmount || inv.totalAmount || inv.total || 0)
        }
        return sum
    }, 0)
    
    this.totalPending = this.projectInvoices.reduce((sum, inv) => {
        if (inv.status !== 'paid' && inv.status !== 'cancelled') {
            const total = parseFloat(inv.totalAmount || inv.total || 0)
            const paid = parseFloat(inv.paidAmount || 0)
            return sum + (total - paid)
        }
        return sum
    }, 0)
    
    // Calculate expense total
    this.totalExpenses = this.projectExpenses.reduce((sum, exp) => {
        return sum + parseFloat(exp.amount || 0)
    }, 0)
},

// ==========================================
// INVOICE HANDLERS
// ==========================================

createInvoiceFromProject() {
    if (!this.selectedProject) return
    
    // Navigate to invoices tab and trigger create
    if (typeof window.DomainControl !== 'undefined' && window.DomainControl.switchTab) {
        window.DomainControl.switchTab('invoices')
        setTimeout(() => {
            const event = new CustomEvent('create-invoice', {
                detail: {
                    projectId: this.selectedProject.id,
                    clientId: this.selectedProject.clientId || this.selectedProject.client_id
                }
            })
            window.dispatchEvent(event)
        }, 100)
    }
},

navigateToInvoice(invoiceId) {
    if (typeof window.DomainControl !== 'undefined' && window.DomainControl.switchTab) {
        window.DomainControl.switchTab('invoices')
        setTimeout(() => {
            const event = new CustomEvent('select-invoice', {
                detail: { invoiceId }
            })
            window.dispatchEvent(event)
        }, 100)
    }
},

async handleInvoiceUpdated() {
    // Refresh financial data after invoice status change
    if (this.selectedProject) {
        await this.loadProjectFinancials(this.selectedProject.id)
    }
},

// ==========================================
// EXPENSE HANDLERS
// ==========================================

showAddExpenseModal() {
    this.expenseModalOpen = true
},

closeExpenseModal() {
    this.expenseModalOpen = false
},

async handleExpenseSaved() {
    this.closeExpenseModal()
    if (this.selectedProject) {
        await this.loadProjectFinancials(this.selectedProject.id)
    }
},

async handleExpenseDeleted() {
    if (this.selectedProject) {
        await this.loadProjectFinancials(this.selectedProject.id)
    }
},
```

---

### Step 3: Update loadProjectDetails Method

Ensure `loadProjectFinancials` is called when project is selected.

Find the `loadProjectDetails` method (around line 552) and verify it includes:

```javascript
async loadProjectDetails(projectId) {
    console.log('Loading project details for projectId:', projectId)
    try {
        await Promise.all([
            this.loadProjectTasks(projectId),
            this.loadTimeTracking(projectId),
            this.loadProjectFinancials(projectId),  // ← Ensure this is here
            this.loadProjectItems(projectId),
            this.loadProjectShares(projectId),
            this.loadProjectActivities(projectId),
        ])
        // ... rest of method
    } catch (error) {
        console.error('Error loading project details:', error)
    }
},
```

---

### Step 4: Update loadTabData Method

Ensure financial data is reloaded when switching to financials tab.

Find the `loadTabData` method (around line 576) and verify it includes:

```javascript
async loadTabData(tab, projectId) {
    if (!projectId) return
    
    switch (tab) {
        case 'tasks-time':
            await Promise.all([
                this.loadProjectTasks(projectId),
                this.loadTimeTracking(projectId)
            ])
            break
        case 'financials':
            await this.loadProjectFinancials(projectId)  // ← Ensure this is here
            break
        // ... other cases
    }
},
```

---

## API Endpoints Required

The implementation assumes these API endpoints exist:

### Invoices
```javascript
// Get all invoices for a project
api.invoices.getByProject(projectId)
// Returns: { data: [{ id, invoiceNumber, issueDate, dueDate, totalAmount, paidAmount, status }] }

// Update invoice status
api.invoices.updateStatus(invoiceId, status)
// Returns: { data: { id, status, ... } }
```

### Transactions (Expenses)
```javascript
// Get all expenses for a project
api.transactions.getByProject(projectId)
// Returns: { data: [{ id, description, amount, transactionDate, categoryName, paymentMethod, reference, notes }] }

// Delete expense
api.transactions.delete(expenseId)
// Returns: { success: true }
```

---

## API Service Implementation

If these methods don't exist in `src/services/api.js`, add them:

```javascript
// In api.js

export default {
    // ... existing methods
    
    invoices: {
        getByProject(projectId) {
            return axios.get(`/apps/domaincontrol/api/projects/${projectId}/invoices`)
        },
        updateStatus(invoiceId, status) {
            return axios.put(`/apps/domaincontrol/api/invoices/${invoiceId}/status`, { status })
        },
        // ... other invoice methods
    },
    
    transactions: {
        getByProject(projectId) {
            return axios.get(`/apps/domaincontrol/api/projects/${projectId}/transactions`)
        },
        delete(transactionId) {
            return axios.delete(`/apps/domaincontrol/api/transactions/${transactionId}`)
        },
        // ... other transaction methods
    },
}
```

---

## Backend API Endpoints Required

### PHP Controller Methods

#### InvoiceController.php
```php
/**
 * Get all invoices for a project
 * @NoAdminRequired
 */
public function getByProject(int $projectId): JSONResponse {
    try {
        $invoices = $this->invoiceMapper->findByProject($projectId);
        return new JSONResponse($invoices);
    } catch (Exception $e) {
        return new JSONResponse(['error' => $e->getMessage()], Http::STATUS_INTERNAL_SERVER_ERROR);
    }
}

/**
 * Update invoice status
 * @NoAdminRequired
 */
public function updateStatus(int $id, string $status): JSONResponse {
    try {
        $invoice = $this->invoiceMapper->find($id);
        $invoice->setStatus($status);
        
        // If marking as paid, set paidAmount = totalAmount
        if ($status === 'paid') {
            $invoice->setPaidAmount($invoice->getTotalAmount());
        }
        
        $this->invoiceMapper->update($invoice);
        return new JSONResponse($invoice);
    } catch (Exception $e) {
        return new JSONResponse(['error' => $e->getMessage()], Http::STATUS_INTERNAL_SERVER_ERROR);
    }
}
```

#### TransactionController.php
```php
/**
 * Get all transactions (expenses) for a project
 * @NoAdminRequired
 */
public function getByProject(int $projectId): JSONResponse {
    try {
        $transactions = $this->transactionMapper->findByProject($projectId);
        return new JSONResponse($transactions);
    } catch (Exception $e) {
        return new JSONResponse(['error' => $e->getMessage()], Http::STATUS_INTERNAL_SERVER_ERROR);
    }
}

/**
 * Delete a transaction
 * @NoAdminRequired
 */
public function delete(int $id): JSONResponse {
    try {
        $transaction = $this->transactionMapper->find($id);
        $this->transactionMapper->delete($transaction);
        return new JSONResponse(['success' => true]);
    } catch (Exception $e) {
        return new JSONResponse(['error' => $e->getMessage()], Http::STATUS_INTERNAL_SERVER_ERROR);
    }
}
```

---

## Testing Checklist

After implementing the changes:

### Functional Tests
- [ ] Financial data loads when project is selected
- [ ] Financial data loads when switching to Financials tab
- [ ] "Create Invoice" button navigates to invoices tab
- [ ] "View Invoice" button navigates to specific invoice
- [ ] "Mark as Paid" button updates invoice status
- [ ] Invoice status change refreshes financial totals
- [ ] "Add Expense" button opens expense modal
- [ ] Expense deletion refreshes financial totals
- [ ] Net Result calculates correctly (Collected - Expenses)
- [ ] All financial totals calculate correctly

### UI/UX Tests
- [ ] Hero metric displays prominently
- [ ] Cash flow summary shows correct values
- [ ] Invoice table sorts correctly
- [ ] Expense list expands/collapses correctly
- [ ] Empty states display when no data
- [ ] Loading states work correctly

### Integration Tests
- [ ] Navigation between tabs works
- [ ] Navigation to invoices tab works
- [ ] Navigation to specific invoice works
- [ ] Expense modal integration works
- [ ] Data refreshes after changes

---

## Migration Notes

### Breaking Changes
None - the component is backward compatible with existing props.

### Deprecated Props
- `totalInvoiced` - still accepted but not displayed (kept for compatibility)

### New Requirements
- Parent must implement `handleInvoiceUpdated` event handler
- Parent must implement financial data loading methods
- API must support `updateStatus` endpoint for invoices

---

## Rollback Plan

If issues arise, you can temporarily revert to the old component:

1. Restore the old `ProjectFinancials.vue` from git history
2. Remove the `@invoice-updated` event handler from parent
3. Keep the new financial loading methods (they're still useful)

---

## Next Steps

1. **Implement parent component methods** (Step 2 above)
2. **Verify API endpoints exist** (check `src/services/api.js`)
3. **Test in development environment**
4. **Gather user feedback** on new UX
5. **Monitor performance** with large datasets

---

## Support

If you encounter issues:

1. Check browser console for errors
2. Verify API responses in Network tab
3. Ensure all props are passed correctly
4. Confirm event handlers are implemented
5. Test with sample data first

---

**Last Updated:** 2025-12-29
**Component Version:** 2.0.0 (UX Redesign)
**Compatibility:** Nextcloud 25+
