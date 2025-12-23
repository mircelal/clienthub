<template>
	<div v-if="open" class="modal-overlay" @click.self="$emit('close')">
		<div class="modal-content modal-content--medium">
			<div class="modal-header">
				<h2 class="modal-title">{{ translate('domaincontrol', 'Link Item') }}</h2>
				<button class="modal-close" @click="$emit('close')">
					<MaterialIcon name="close" :size="24" />
				</button>
			</div>
			<div class="modal-body">
				<div class="form-group">
					<label class="form-label">{{ translate('domaincontrol', 'Item Type') }}</label>
					<select v-model="itemType" class="form-control" @change="itemId = null">
						<option value="">{{ translate('domaincontrol', 'Select Type') }}</option>
						<option value="domain">{{ translate('domaincontrol', 'Domain') }}</option>
						<option value="hosting">{{ translate('domaincontrol', 'Hosting') }}</option>
						<option value="website">{{ translate('domaincontrol', 'Website') }}</option>
						<option value="service">{{ translate('domaincontrol', 'Service') }}</option>
					</select>
				</div>
				<div class="form-group" v-if="itemType">
					<label class="form-label">{{ translate('domaincontrol', 'Select Item') }}</label>
					<select v-model="itemId" class="form-control">
						<option value="">{{ translate('domaincontrol', 'Select Item') }}</option>
						<option
							v-for="domain in domains"
							:key="domain.id"
							:value="domain.id"
							v-if="itemType === 'domain'"
						>
							{{ domain.domainName }}
						</option>
						<option
							v-for="hosting in hostings"
							:key="hosting.id"
							:value="hosting.id"
							v-if="itemType === 'hosting'"
						>
							{{ hosting.provider }} - {{ hosting.plan }}
						</option>
						<option
							v-for="website in websites"
							:key="website.id"
							:value="website.id"
							v-if="itemType === 'website'"
						>
							{{ website.name }}
						</option>
						<option
							v-for="service in services"
							:key="service.id"
							:value="service.id"
							v-if="itemType === 'service'"
						>
							{{ service.name }}
						</option>
					</select>
				</div>
			</div>
			<div class="modal-footer">
				<button class="button-vue button-vue--secondary" @click="$emit('close')">
					{{ translate('domaincontrol', 'Cancel') }}
				</button>
				<button class="button-vue button-vue--primary" @click="handleLink" :disabled="!itemType || !itemId">
					{{ translate('domaincontrol', 'Link') }}
				</button>
			</div>
		</div>
	</div>
</template>

<script>
import MaterialIcon from '../../MaterialIcon.vue'

export default {
	name: 'LinkItemModal',
	components: {
		MaterialIcon,
	},
	props: {
		open: {
			type: Boolean,
			default: false,
		},
		domains: {
			type: Array,
			default: () => [],
		},
		hostings: {
			type: Array,
			default: () => [],
		},
		websites: {
			type: Array,
			default: () => [],
		},
		services: {
			type: Array,
			default: () => [],
		},
	},
	emits: ['close', 'link'],
	data() {
		return {
			itemType: '',
			itemId: null,
		}
	},
	watch: {
		open(newVal) {
			if (!newVal) {
				this.itemType = ''
				this.itemId = null
			}
		},
	},
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
		handleLink() {
			if (!this.itemType || !this.itemId) {
				alert(this.translate('domaincontrol', 'Please select item type and item'))
				return
			}
			this.$emit('link', {
				itemType: this.itemType,
				itemId: this.itemId,
			})
			this.itemType = ''
			this.itemId = null
		},
	},
}
</script>

<style scoped>
.modal-overlay {
	position: fixed;
	top: 0;
	left: 0;
	right: 0;
	bottom: 0;
	background-color: rgba(0, 0, 0, 0.5);
	display: flex;
	align-items: center;
	justify-content: center;
	z-index: 10000;
	overflow-y: auto;
	padding: 20px;
}

.modal-content {
	background-color: var(--color-main-background);
	border-radius: var(--border-radius-large);
	box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
	max-width: 500px;
	width: 90%;
	max-height: 90vh;
	overflow-y: auto;
	margin: auto;
}

.modal-content--medium {
	max-width: 500px;
}

.modal-header {
	display: flex;
	align-items: center;
	justify-content: space-between;
	padding: 20px;
	border-bottom: 1px solid var(--color-border);
}

.modal-title {
	margin: 0;
	font-size: 20px;
	font-weight: 600;
	color: var(--color-main-text);
}

.modal-close {
	background: transparent;
	border: none;
	cursor: pointer;
	color: var(--color-text-maxcontrast);
	padding: 4px;
	display: flex;
	align-items: center;
	justify-content: center;
}

.modal-close:hover {
	color: var(--color-main-text);
}

.modal-body {
	padding: 20px;
}

.modal-footer {
	display: flex;
	justify-content: flex-end;
	gap: 8px;
	padding: 20px;
	border-top: 1px solid var(--color-border);
}

.form-group {
	margin-bottom: 16px;
}

.form-label {
	display: block;
	margin-bottom: 8px;
	font-size: 14px;
	font-weight: 500;
	color: var(--color-main-text);
}

.form-control {
	width: 100%;
	padding: 8px 12px;
	border: 1px solid var(--color-border);
	border-radius: var(--border-radius-element);
	background-color: var(--color-main-background);
	color: var(--color-main-text);
	font-size: 14px;
}
</style>

