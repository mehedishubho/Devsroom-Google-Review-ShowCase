/**
 * Devsroom Google Reviews — OAuth Connect Admin JS.
 *
 * Handles: mode toggle, Connect popup, Sync Now, Disconnect, Fetch Locations.
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

		if (apiSection) apiSection.style.display = (mode === 'api_key') ? '' : 'none';
		if (oauthSection) oauthSection.style.display = (mode === 'oauth') ? '' : 'none';
		if (actionsApi) actionsApi.style.display = (mode === 'api_key') ? '' : 'none';
	}

	// Connect Google Account — opens popup.
	function initConnectButton() {
		var btn = document.getElementById('devsroom-greviews-oauth-connect');
		if (!btn) return;

		btn.addEventListener('click', function (e) {
			e.preventDefault();

			// Check if Client ID is configured (in advanced settings).
			var clientIdField = document.getElementById('devsroom_greviews_oauth_client_id');
			if (clientIdField && !clientIdField.value.trim()) {
				// Open the advanced settings section.
				var details = btn.closest('#devsroom-greviews-mode-oauth').querySelector('details');
				if (details) details.open = true;
				alert('Please enter your OAuth Client ID in Advanced Settings first.');
				return;
			}

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

	// Fetch Locations button — populates the <select> dropdown.
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
				btn.textContent = 'Refresh';
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
				btn.textContent = 'Refresh';
				resultEl.innerHTML = '<span style="color:red;">Request failed.</span>';
			});
		});
	}

	// Place Search — Google Places Autocomplete dropdown.
	function initPlaceSearch() {
		var searchInput = document.getElementById('devsroom-greviews-place-search');
		var resultsEl = document.getElementById('devsroom-greviews-place-search-results');
		if (!searchInput || !resultsEl) return;

		var pendingReq = null;
		var debounceTimer = null;
		var pinSvg = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>';

		function openDropdown() {
			resultsEl.classList.add('is-open');
		}

		function closeDropdown() {
			resultsEl.classList.remove('is-open');
		}

		function doSearch() {
			var query = searchInput.value.trim();
			if (query.length < 2) {
				closeDropdown();
				resultsEl.innerHTML = '';
				return;
			}

			if (pendingReq) {
				pendingReq.abort();
			}

			resultsEl.innerHTML = '<div style="padding:12px 14px;color:#6b7280;font-size:13px;">Searching...</div>';
			openDropdown();

			pendingReq = jQuery.post(ajaxurl, {
				action: 'devsroom_greviews_search_places',
				nonce: devsroom_greviews_admin.search_places_nonce,
				query: query
			}, function (response) {
				pendingReq = null;

				if (response.success && response.data.predictions && response.data.predictions.length) {
					var html = '';
					response.data.predictions.forEach(function (pred) {
						html += '<div class="devsroom-greviews-place-result" data-place-id="' + escHtml(pred.place_id) + '" data-name="' + escHtml(pred.main_text) + '">';
						html += '<span class="devsroom-greviews-place-result-pin">' + pinSvg + '</span>';
						html += '<div class="devsroom-greviews-place-result-info">';
						html += '<strong>' + escHtml(pred.main_text) + '</strong>';
						html += '<span class="devsroom-greviews-place-result-address">' + escHtml(pred.secondary_text) + '</span>';
						html += '</div>';
						html += '</div>';
					});
					html += '<div class="devsroom-greviews-place-dropdown-footer">powered by Google</div>';
					resultsEl.innerHTML = html;
					openDropdown();
				} else if (response.success && (!response.data.predictions || !response.data.predictions.length)) {
					resultsEl.innerHTML = '<div style="padding:12px 14px;color:#6b7280;font-size:13px;">No results found. Try a different search.</div>';
					openDropdown();
				} else {
					resultsEl.innerHTML = '<div style="padding:12px 14px;color:#dc2626;font-size:13px;">' + escHtml(response.data.message || 'Search failed.') + '</div>';
					openDropdown();
				}
			}).fail(function (jqXHR, status) {
				pendingReq = null;
				if (status !== 'abort') {
					resultsEl.innerHTML = '<div style="padding:12px 14px;color:#dc2626;font-size:13px;">Request failed.</div>';
					openDropdown();
				}
			});
		}

		// Debounced input — suggestions as you type.
		searchInput.addEventListener('input', function () {
			clearTimeout(debounceTimer);
			debounceTimer = setTimeout(doSearch, 400);
		});

		searchInput.addEventListener('keydown', function (e) {
			if (e.key === 'Enter') {
				e.preventDefault();
				clearTimeout(debounceTimer);
				doSearch();
			}
			if (e.key === 'Escape') {
				closeDropdown();
			}
		});

		searchInput.addEventListener('focus', function () {
			if (resultsEl.innerHTML.trim()) {
				openDropdown();
			}
		});

		// Click on suggestion — event delegation.
		resultsEl.addEventListener('click', function (e) {
			var row = e.target.closest('.devsroom-greviews-place-result');
			if (!row) return;

			var placeId = row.getAttribute('data-place-id');
			var placeName = row.getAttribute('data-name');
			var placeIdField = document.getElementById('devsroom_greviews_place_id');
			if (placeIdField && placeId) {
				placeIdField.value = placeId;
				searchInput.value = placeName;
				closeDropdown();
				resultsEl.innerHTML = '';
			}
		});

		// Close dropdown when clicking outside.
		document.addEventListener('click', function (e) {
			if (!e.target.closest('.devsroom-greviews-place-search-wrap')) {
				closeDropdown();
			}
		});
	}

	function escHtml(str) {
		var div = document.createElement('div');
		div.appendChild(document.createTextNode(str));
		return div.innerHTML;
	}

	// Initialize on DOM ready.
	function init() {
		initModeToggle();
		initConnectButton();
		initSyncButton();
		initDisconnectButton();
		initFetchLocationsButton();
		initPlaceSearch();
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
})();
