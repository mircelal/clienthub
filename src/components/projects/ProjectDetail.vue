<template>
	<div class="project-detail-view">
		<ProjectHeader
			:project="project"
			:active-tab="activeTab"
			:popover-open="popoverOpen"
			:can-start-timer="canStartTimer"
			:current-running-entry="currentRunningEntry"
			@back="$emit('back')"
			@edit="$emit('edit')"
			@delete="$emit('delete')"
			@toggle-popover="$emit('toggle-popover')"
			@close-popover="$emit('close-popover')"
			@start-timer="$emit('start-timer')"
			@stop-timer="$emit('stop-timer')"
			@tab-change="$emit('tab-change', $event)"
		/>

		<NcAppContent>
			<div class="detail-content">
				<slot />
			</div>
		</NcAppContent>
	</div>
</template>

<script>
import { NcAppContent } from '@nextcloud/vue'
import ProjectHeader from './ProjectHeader.vue'

export default {
	name: 'ProjectDetail',
	components: {
		NcAppContent,
		ProjectHeader,
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
		popoverOpen: {
			type: Boolean,
			default: false,
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
	emits: ['back', 'edit', 'delete', 'toggle-popover', 'close-popover', 'start-timer', 'stop-timer', 'tab-change'],
}
</script>

<style scoped>
/* Nextcloud Files app content structure */
/* Following Nextcloud Files app pattern */
/* NcAppContent handles all padding and layout automatically */
.project-detail-view {
	width: 100%;
	height: 100%;
	display: flex;
	flex-direction: column;
}

.detail-content {
	width: 100%;
	min-height: 100%;
}
</style>

