<template>
	<div class="stat-card stat-card--gradient-primary">
		<div class="stat-card__icon">
			<span :class="icon"></span>
		</div>
		<div class="stat-card__info">
			<div class="stat-card__label">{{ title }}</div>
			<div class="stat-card__value">{{ value }}</div>
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
			type: Number,
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
	}
}
</script>

<style scoped>
.stat-card {
	display: flex;
	align-items: center;
	padding: 20px;
	border-radius: 8px;
	background: var(--color-background-dark);
	box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.stat-card__icon {
	font-size: 2.5em;
	margin-right: 15px;
	color: var(--color-primary);
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

