/**
 * Devsroom Google Reviews — OAuth Connect Admin JS.
 *
 * Handles: mode toggle, Connect popup, Sync Now, Disconnect.
 */
(function () {
	'use strict';

	// Mode toggle — show/hide the correct section.
	function initModeToggle() {
		var radios = document.querySelectorAll('input[name="devsroom_greviews_connection_mode"]');
		if (!radios.length) return;

		radios.forEach(function (radio) {
			radio.addEventListener('change', function () {
				toggleSections(this.value);
			});
		});

		// Set initial state.
		var checked = document.querySelector('input[name="devsroom_greviews_connection_mode"]:checked');
		if (checked) {
			toggleSections(checked.value);
		}
	}

	function toggleSections(mode) {
		var apiSection = document.getElementById('devsroom-greviews-mode-api-key');
		var oauthSection = document.getElementById('devsroom-greviews-mode-oauth');
		var actionsApi = document.getElementById('devsroom-greviews-actions-api-key');
		var actionsOauth = document.getElementById('devsroom-greviews-actions-oauth');

		if (apiSection) apiSection.style.display = (mode === 'api_key') ? '' : 'none';
		if (oauthSection) oauthSection.style.display = (mode === 'oauth') ? '' : 'none';
		if (actionsApi) actionsApi.style.display = (mode === 'api_key') ? '' : 'none';
		if (actionsOauth) actionsOauth.style.display = (mode === 'oauth') ? '' : 'none';
	}

	// Connect Google Account — opens popup.
	function initConnectButton() {
		var btn = document.getElementById('devsroom-greviews-oauth-connect');
		if (!btn) return;

		btn.addEventListener('click', function (e) {
			e.preventDefault();

			var nonce = btn.getAttribute('data-nonce');
			var url = ajaxurl + '?action=devsroom_greviews_oauth_start&nonce=' + encodeURIComponent(nonce);

			var popup = window.open(url, 'devsroom_greviews_oauth', 'width=600,height=700,left=100,top=100');
			if (!popup || popup.closed) {
				alert('Popup blocked. Please allow popups for this site.');
			}
		});
	}

	// Called by the popup after successful connection.
	window.devsroom_greviews_oauth_complete = function (success) {
		if (success) {
			window.location.reload();
		}
	};

	// Sync Now button.
	function initSyncButton() {
		var btn = document.getElementById('devsroom-greviews-sync-now');
		if (!btn) return;

		btn.addEventListener('click', function (e) {
			e.preventDefault();
			var resultEl = document.getElementById('devsroom-greviews-sync-result');

			btn.disabled = true;
			btn.textContent = 'Syncing...';

			jQuery.post(ajaxurl, {
				action: 'devsroom_greviews_sync_now',
				nonce: devsroom_greviews_admin.sync_nonce
			}, function (response) {
				btn.disabled = false;
				btn.textContent = 'Sync Now';
				if (response.success) {
					resultEl.innerHTML = '<span style="color:green;">' + response.data.message + '</span>';
				} else {
					resultEl.innerHTML = '<span style="color:red;">' + response.data.message + '</span>';
				}
			}).fail(function () {
				btn.disabled = false;
				btn.textContent = 'Sync Now';
				resultEl.innerHTML = '<span style="color:red;">Request failed.</span>';
			});
		});
	}

	// Disconnect button.
	function initDisconnectButton() {
		var btn = document.getElementById('devsroom-greviews-oauth-disconnect');
		if (!btn) return;

		btn.addEventListener('click', function (e) {
			e.preventDefault();
			if (!confirm('Are you sure you want to disconnect your Google account? Existing reviews will be kept.')) {
				return;
			}

			var resultEl = document.getElementById('devsroom-greviews-disconnect-result');

			btn.disabled = true;
			btn.textContent = 'Disconnecting...';

			jQuery.post(ajaxurl, {
				action: 'devsroom_greviews_oauth_disconnect',
				nonce: devsroom_greviews_admin.disconnect_nonce
			}, function (response) {
				btn.disabled = false;
				btn.textContent = 'Disconnect';
				if (response.success) {
					window.location.reload();
				} else {
					resultEl.innerHTML = '<span style="color:red;">' + response.data.message + '</span>';
				}
			}).fail(function () {
				btn.disabled = false;
				btn.textContent = 'Disconnect';
				resultEl.innerHTML = '<span style="color:red;">Request failed.</span>';
			});
		});
	}

	// Fetch Locations button.
	function initFetchLocationsButton() {
		var btn = document.getElementById('devsroom-greviews-fetch-locations');
		if (!btn) return;

		btn.addEventListener('click', function (e) {
			e.preventDefault();
			var resultEl = document.getElementById('devsroom-greviews-locations-result');

			btn.disabled = true;
			btn.textContent = 'Fetching...';

			jQuery.post(ajaxurl, {
				action: 'devsroom_greviews_fetch_locations',
				nonce: devsroom_greviews_admin.locations_nonce
			}, function (response) {
				btn.disabled = false;
				btn.textContent = 'Refresh Locations';
				if (response.success && response.data.locations) {
					var select = document.getElementById('devsroom_greviews_oauth_location_name');
					if (select) {
						select.innerHTML = '';
						response.data.locations.forEach(function (loc) {
							var opt = document.createElement('option');
							opt.value = loc.name;
							opt.textContent = loc.title;
							select.appendChild(opt);
						});
					}
					resultEl.innerHTML = '<span style="color:green;">' + response.data.message + '</span>';
				} else {
					resultEl.innerHTML = '<span style="color:red;">' + (response.data ? response.data.message : 'Failed') + '</span>';
				}
			}).fail(function () {
				btn.disabled = false;
				btn.textContent = 'Refresh Locations';
				resultEl.innerHTML = '<span style="color:red;">Request failed.</span>';
			});
		});
	}

	// Initialize on DOM ready.
	function init() {
		initModeToggle();
		initConnectButton();
		initSyncButton();
		initDisconnectButton();
		initFetchLocationsButton();
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
})();
