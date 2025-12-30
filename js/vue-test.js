/**
 * Vue.js Test - Nextcloud Native Integration
 * Using Nextcloud's Vue.js system without external dependencies
 * 
 * This test uses Nextcloud's built-in Vue.js if available,
 * or falls back to a simple vanilla JS test
 */

(function() {
	'use strict';
	
	console.log('Vue.js Test: Starting Nextcloud-compatible test...');
	
	// Wait for DOM and Nextcloud to be ready
	document.addEventListener('DOMContentLoaded', function() {
		// Check if Nextcloud Vue is available
		if (typeof window.OC !== 'undefined' && window.OC.getCurrentUser) {
			console.log('Vue.js Test: Nextcloud environment detected');
			
			// Try to use Nextcloud's Vue if available
			// Nextcloud apps typically use @nextcloud/vue which requires webpack
			// For a simple test, we'll create a vanilla JS component that mimics Vue behavior
			initVueLikeTest();
		} else {
			console.warn('Vue.js Test: Nextcloud not detected, using fallback');
			initVueLikeTest();
		}
	});
	
	function initVueLikeTest() {
		const container = document.getElementById('vue-test-container');
		if (!container) {
			console.warn('Vue.js Test: Container not found');
			return;
		}
		
		// Simple reactive-like component using vanilla JS
		let state = {
			message: 'Nextcloud Vue.js Test Çalışıyor! ✅',
			counter: 0,
			clickCount: 0
		};
		
		function render() {
			container.innerHTML = `
				<div class="vue-test-container" style="padding: 20px; border: 2px solid var(--color-primary-element, #00679e); border-radius: 8px; background: var(--color-background-dark, #292929); margin: 20px 0;">
					<h3 style="color: var(--color-main-text, #EBEBEB); margin-bottom: 15px;">🧪 Nextcloud Vue.js Test</h3>
					<p style="color: var(--color-text-maxcontrast, #999999); margin-bottom: 10px;">${state.message}</p>
					<div style="margin: 15px 0;">
						<button 
							id="vue-test-increment"
							style="padding: 8px 16px; background: var(--color-primary-element, #00679e); color: white; border: none; border-radius: 4px; cursor: pointer; margin-right: 10px;"
						>
							Artır (${state.counter})
						</button>
						<button 
							id="vue-test-reset"
							style="padding: 8px 16px; background: #666; color: white; border: none; border-radius: 4px; cursor: pointer;"
						>
							Sıfırla
						</button>
					</div>
					<p style="color: var(--color-text-maxcontrast, #999999); font-size: 0.9em;">
						Toplam tıklama: ${state.clickCount}
					</p>
					<p style="color: var(--color-text-maxcontrast, #999999); font-size: 0.8em; margin-top: 10px;">
						ℹ️ Bu basit bir test. Gerçek Vue.js entegrasyonu için @nextcloud/vue ve webpack gerekir.
					</p>
				</div>
			`;
			
			// Attach event listeners
			const incrementBtn = document.getElementById('vue-test-increment');
			const resetBtn = document.getElementById('vue-test-reset');
			
			if (incrementBtn) {
				incrementBtn.onclick = function() {
					state.counter++;
					state.clickCount++;
					render();
				};
			}
			
			if (resetBtn) {
				resetBtn.onclick = function() {
					state.counter = 0;
					render();
				};
			}
		}
		
		render();
		console.log('Vue.js Test: Component initialized successfully');
	}
})();

