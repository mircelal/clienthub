/**
 * Vue.js Test Component
 * Simple test to verify Vue.js works in Nextcloud
 */

// Wait for DOM to be ready
document.addEventListener('DOMContentLoaded', function() {
	console.log('Vue.js Test: DOM ready, initializing...');
	
	// Check if Vue is available (from CDN or Nextcloud)
	if (typeof Vue === 'undefined') {
		console.error('Vue.js Test: Vue is not loaded!');
		return;
	}
	
	console.log('Vue.js Test: Vue found, version:', Vue.version);
	
	// Simple test component
	const { createApp } = Vue;
	
	const TestComponent = {
		data() {
			return {
				message: 'Vue.js çalışıyor! ✅',
				counter: 0,
				clickCount: 0
			}
		},
		methods: {
			increment() {
				this.counter++;
				this.clickCount++;
			},
			reset() {
				this.counter = 0;
			}
		},
		template: `
			<div class="vue-test-container" style="padding: 20px; border: 2px solid #00679e; border-radius: 8px; background: var(--color-background-dark); margin: 20px 0;">
				<h3 style="color: var(--color-main-text); margin-bottom: 15px;">🧪 Vue.js Test</h3>
				<p style="color: var(--color-text-maxcontrast); margin-bottom: 10px;">{{ message }}</p>
				<div style="margin: 15px 0;">
					<button 
						@click="increment" 
						style="padding: 8px 16px; background: #00679e; color: white; border: none; border-radius: 4px; cursor: pointer; margin-right: 10px;"
					>
						Artır ({{ counter }})
					</button>
					<button 
						@click="reset" 
						style="padding: 8px 16px; background: #666; color: white; border: none; border-radius: 4px; cursor: pointer;"
					>
						Sıfırla
					</button>
				</div>
				<p style="color: var(--color-text-maxcontrast); font-size: 0.9em;">
					Toplam tıklama: {{ clickCount }}
				</p>
			</div>
		`
	};
	
	// Mount to test container
	const testContainer = document.getElementById('vue-test-container');
	if (testContainer) {
		console.log('Vue.js Test: Mount point found, creating app...');
		try {
			const app = createApp(TestComponent);
			app.mount(testContainer);
			console.log('Vue.js Test: App mounted successfully!');
		} catch (error) {
			console.error('Vue.js Test: Error mounting app:', error);
		}
	} else {
		console.warn('Vue.js Test: Mount point #vue-test-container not found');
	}
});

