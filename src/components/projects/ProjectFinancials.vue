<template>
	<div class="tab-content">
		<div class="detail-section">
			<div class="section-header">
				<h3 class="section-title">
					<MaterialIcon name="accounting" :size="24" />
					{{ translate('domaincontrol', 'Financials') }}
				</h3>
				<button class="button-vue button-vue--primary" @click="$emit('create-invoice')">
					<MaterialIcon name="add" :size="18" />
					{{ translate('domaincontrol', 'Create Invoice') }}
				</button>
			</div>
			<div class="section-content">
				<div class="financial-summary">
					<div class="financial-item">
						<span class="financial-label">{{ translate('domaincontrol', 'Budget') }}:</span>
						<span class="financial-value">{{ formatCurrency(project.budget, project.currency) }}</span>
					</div>
					<div v-if="totalInvoiced > 0" class="financial-item">
						<span class="financial-label">{{ translate('domaincontrol', 'Total Invoiced') }}:</span>
						<span class="financial-value">{{ formatCurrency(totalInvoiced, project.currency) }}</span>
					</div>
					<div v-if="totalPaid > 0" class="financial-item">
						<span class="financial-label">{{ translate('domaincontrol', 'Paid') }}:</span>
						<span class="financial-value financial-value--success">{{ formatCurrency(totalPaid, project.currency) }}</span>
					</div>
					<div v-if="totalPending > 0" class="financial-item">
						<span class="financial-label">{{ translate('domaincontrol', 'Pending') }}:</span>
						<span class="financial-value financial-value--warning">{{ formatCurrency(totalPending, project.currency) }}</span>
					</div>
				</div>

				<!-- Project Invoices -->
				<div v-if="invoices.length > 0" class="project-invoices-list">
					<div class="invoices-header">
						<strong>{{ translate('domaincontrol', 'Project Invoices') }}</strong>
						<span>{{ invoices.length }} {{ translate('domaincontrol', 'invoices') }}</span>
					</div>
					<div class="invoices-items">
						<div
							v-for="invoice in invoices"
							:key="invoice.id"
							class="invoice-item"
							@click="$emit('navigate-invoice', invoice.id)"
						>
							<div class="invoice-info">
								<div class="invoice-number">{{ invoice.invoiceNumber || 'Invoice #' + invoice.id }}</div>
								<div class="invoice-date">{{ invoice.issueDate || '-' }}</div>
							</div>
							<span class="status-badge" :class="'status-' + invoice.status">
								{{ getInvoiceStatusText(invoice.status) }}
							</span>
							<div class="invoice-amounts">
								<div class="invoice-total">
									{{ translate('domaincontrol', 'Total') }}: <strong>{{ formatCurrency(invoice.totalAmount, invoice.currency) }}</strong>
								</div>
								<div class="invoice-paid">
									{{ translate('domaincontrol', 'Paid') }}: <strong>{{ formatCurrency(invoice.paidAmount, invoice.currency) }}</strong>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</template>

<script>
import MaterialIcon from '../MaterialIcon.vue'

export default {
	name: 'ProjectFinancials',
	components: {
		MaterialIcon,
	},
	props: {
		project: {
			type: Object,
			required: true,
		},
		invoices: {
			type: Array,
			default: () => [],
		},
		totalInvoiced: {
			type: Number,
			default: 0,
		},
		totalPaid: {
			type: Number,
			default: 0,
		},
		totalPending: {
			type: Number,
			default: 0,
		},
	},
	emits: ['create-invoice', 'navigate-invoice'],
	methods: {
		translate(appId, text, vars) {
			try {
				if (typeof window !== 'undefined') {
					if (typeof OC !== 'undefined' && OC.L10n && typeof OC.L10n.translate === 'function') {
						const translated = OC.L10n.translate(appId, text, vars || {})
						if (translated && translated !== text) {
							return translated
						}
					}
					if (typeof window.t === 'function') {
						const translated = window.t(appId, text, vars || {})
						if (translated && translated !== text) {
							return translated
						}
					}
				}
			} catch (e) {
				console.warn('Translation error:', e)
			}
			return text
		},
		formatCurrency(amount, currency = 'USD') {
			if (amount === null || amount === undefined || amount === 0) return '-'
			const formatter = new Intl.NumberFormat('tr-TR', {
				style: 'currency',
				currency: currency || 'USD',
			})
			return formatter.format(amount)
		},
		getInvoiceStatusText(status) {
			const statusTexts = {
				draft: this.translate('domaincontrol', 'Draft'),
				sent: this.translate('domaincontrol', 'Sent'),
				paid: this.translate('domaincontrol', 'Paid'),
				overdue: this.translate('domaincontrol', 'Overdue'),
				cancelled: this.translate('domaincontrol', 'Cancelled'),
			}
			return statusTexts[status] || status
		},
	},
}
</script>

<style scoped>
.detail-section {
	margin-bottom: 24px;
}

.section-header {
	display: flex;
	align-items: center;
	justify-content: space-between;
	margin-bottom: 16px;
}

.section-title {
	display: flex;
	align-items: center;
	gap: 8px;
	margin: 0;
	font-size: 18px;
	font-weight: 600;
	color: var(--color-main-text);
}

.financial-summary {
	display: flex;
	flex-direction: column;
	gap: 12px;
	margin-bottom: 24px;
	padding: 16px;
	background-color: var(--color-main-background);
	border: 1px solid var(--color-border);
	border-radius: var(--border-radius-element);
}

.financial-item {
	display: flex;
	justify-content: space-between;
	align-items: center;
}

.financial-label {
	font-size: 14px;
	color: var(--color-text-maxcontrast);
}

.financial-value {
	font-size: 16px;
	font-weight: 600;
	color: var(--color-main-text);
}

.financial-value--success {
	color: var(--color-success);
}

.financial-value--warning {
	color: var(--color-warning);
}

.project-invoices-list {
	margin-top: 24px;
}

.invoices-header {
	display: flex;
	justify-content: space-between;
	align-items: center;
	margin-bottom: 12px;
	font-size: 14px;
	color: var(--color-main-text);
}

.invoices-items {
	display: flex;
	flex-direction: column;
	gap: 8px;
}

.invoice-item {
	padding: 12px;
	background-color: var(--color-main-background);
	border: 1px solid var(--color-border);
	border-radius: var(--border-radius-element);
	cursor: pointer;
	transition: background-color 0.2s;
}

.invoice-item:hover {
	background-color: var(--color-background-hover);
}

.invoice-info {
	margin-bottom: 8px;
}

.invoice-number {
	font-weight: 600;
	font-size: 13px;
	color: var(--color-main-text);
	margin-bottom: 4px;
}

.invoice-date {
	font-size: 11px;
	color: var(--color-text-maxcontrast);
}

.invoice-amounts {
	display: flex;
	justify-content: space-between;
	align-items: center;
	margin-top: 8px;
	font-size: 12px;
}

.invoice-total {
	color: var(--color-text-maxcontrast);
}

.invoice-paid {
	color: var(--color-success);
}

.status-badge {
	padding: 4px 8px;
	border-radius: 12px;
	font-size: 11px;
	font-weight: 500;
}
</style>

