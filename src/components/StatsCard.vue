<template>
	<div class="stat-card" :class="`stat-card--${color}`">
		<div class="stat-card__icon">
			<span :class="icon" />
		</div>
		<div class="stat-card__info">
			<div class="stat-card__label">{{ title }}</div>
			<div class="stat-card__value">{{ formattedValue }}</div>
		</div>
	</div>
</template>

<script>
export default {
	name: 'StatsCard',
	props: {
		title: {
			type: String,
			required: true
		},
		value: {
			type: [Number, String],
			default: 0
		},
		icon: {
			type: String,
			default: 'icon-home'
		},
		color: {
			type: String,
			default: 'primary'
		}
	},
	computed: {
		formattedValue() {
			if (typeof this.value === 'number') {
				return this.value.toLocaleString('tr-TR')
			}
			return this.value
		}
	}
}
</script>

<style scoped>
.stat-card {
	display: flex;
	align-items: center;
	padding: 20px;
	border-radius: var(--border-radius-element);
	background: var(--color-background-dark);
	box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
	transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.stat-card:hover {
	transform: translateY(-2px);
	box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
}

.stat-card--primary {
	background: var(--color-primary-element-element-light);
}

.stat-card--success {
	background: var(--color-element-success);
}

.stat-card--warning {
	background: var(--color-element-warning);
}

.stat-card--danger {
	background: var(--color-element-error);
}

.stat-card__icon {
	font-size: 2.5em;
	margin-right: 15px;
	color: var(--color-primary-element-element);
	display: flex;
	align-items: center;
	justify-content: center;
	width: 50px;
	height: 50px;
}

.stat-card--primary .stat-card__icon {
	color: var(--color-primary-element-element-text);
}

.stat-card--success .stat-card__icon {
	color: var(--color-element-success-text);
}

.stat-card--warning .stat-card__icon {
	color: var(--color-element-warning-text);
}

.stat-card--danger .stat-card__icon {
	color: var(--color-element-error-text);
}

.stat-card__info {
	flex-grow: 1;
}

.stat-card__label {
	font-size: 0.9em;
	color: var(--color-text-maxcontrast);
	margin-bottom: 5px;
}

.stat-card__value {
	font-size: 1.8em;
	font-weight: bold;
	color: var(--color-main-text);
}
</style>
