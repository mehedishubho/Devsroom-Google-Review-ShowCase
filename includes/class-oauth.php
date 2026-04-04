<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Devsroom_GReviews_OAuth {

	/**
	 * Get the encryption key derived from WordPress salt.
	 *
	 * @return string 32-byte key for AES-256.
	 */
	private function get_encryption_key() {
		return hash( 'sha256', wp_salt( 'secure_auth' ), true );
	}

	/**
	 * Encrypt a token string using AES-256-CBC.
	 *
	 * @param string $token Plain-text token.
	 * @return string|false Base64-encoded ciphertext or false on failure.
	 */
	public function encrypt_token( $token ) {
		if ( empty( $token ) ) {
			return false;
		}

		$key    = $this->get_encryption_key();
		$iv     = openssl_random_pseudo_bytes( 16 );
		$encrypted = openssl_encrypt( $token, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv );

		if ( false === $encrypted ) {
			return false;
		}

		return base64_encode( $iv . $encrypted );
	}

	/**
	 * Decrypt a token string.
	 *
	 * @param string $encrypted Base64-encoded ciphertext.
	 * @return string|false Decrypted plain-text or false on failure.
	 */
	public function decrypt_token( $encrypted ) {
		if ( empty( $encrypted ) ) {
			return false;
		}

		$data = base64_decode( $encrypted, true );
		if ( false === $data || strlen( $data ) < 32 ) {
			return false;
		}

		$key        = $this->get_encryption_key();
		$iv         = substr( $data, 0, 16 );
		$ciphertext = substr( $data, 16 );

		return openssl_decrypt( $ciphertext, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv );
	}

	/**
	 * Exchange an authorization code for access and refresh tokens.
	 *
	 * @param string $code Authorization code from Google.
	 * @return array|WP_Error Token data or WP_Error.
	 */
	public function exchange_code( $code ) {
		$client_id     = get_option( 'devsroom_greviews_oauth_client_id', '' );
		$client_secret = get_option( 'devsroom_greviews_oauth_client_secret', '' );

		if ( empty( $client_id ) || empty( $client_secret ) ) {
			return new WP_Error( 'missing_credentials', __( 'Client ID or Client Secret is not configured.', 'devsroom-google-review-showcase' ) );
		}

		$redirect_uri = admin_url( 'admin-ajax.php?action=devsroom_greviews_oauth_callback' );

		$response = wp_remote_post( 'https://oauth2.googleapis.com/token', array(
			'timeout' => 30,
			'body'    => array(
				'code'          => $code,
				'client_id'     => $client_id,
				'client_secret' => $client_secret,
				'redirect_uri'  => $redirect_uri,
				'grant_type'    => 'authorization_code',
			),
		) );

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );
		$http_code = wp_remote_retrieve_response_code( $response );

		if ( 200 !== $http_code || empty( $body['access_token'] ) ) {
			$error = isset( $body['error_description'] ) ? $body['error_description'] : __( 'Failed to exchange authorization code.', 'devsroom-google-review-showcase' );
			return new WP_Error( 'token_exchange_failed', $error );
		}

		// Encrypt and store tokens.
		$encrypted_access  = $this->encrypt_token( $body['access_token'] );
		$encrypted_refresh = $this->encrypt_token( $body['refresh_token'] );

		if ( ! $encrypted_access || ! $encrypted_refresh ) {
			return new WP_Error( 'encryption_failed', __( 'Failed to encrypt tokens.', 'devsroom-google-review-showcase' ) );
		}

		$expires_in = ! empty( $body['expires_in'] ) ? absint( $body['expires_in'] ) : 3600;

		update_option( 'devsroom_greviews_oauth_access_token', $encrypted_access, false );
		update_option( 'devsroom_greviews_oauth_refresh_token', $encrypted_refresh, false );
		update_option( 'devsroom_greviews_oauth_token_expiry', time() + $expires_in - 60, false );

		return $body;
	}

	/**
	 * Refresh the access token using the stored refresh token.
	 *
	 * @return true|WP_Error True on success or WP_Error.
	 */
	public function refresh_access_token() {
		$encrypted_refresh = get_option( 'devsroom_greviews_oauth_refresh_token', '' );
		$refresh_token     = $this->decrypt_token( $encrypted_refresh );

		if ( ! $refresh_token ) {
			return new WP_Error( 'no_refresh_token', __( 'No refresh token available. Please reconnect your Google account.', 'devsroom-google-review-showcase' ) );
		}

		$client_id     = get_option( 'devsroom_greviews_oauth_client_id', '' );
		$client_secret = get_option( 'devsroom_greviews_oauth_client_secret', '' );

		if ( empty( $client_id ) || empty( $client_secret ) ) {
			return new WP_Error( 'missing_credentials', __( 'Client ID or Client Secret is not configured.', 'devsroom-google-review-showcase' ) );
		}

		$response = wp_remote_post( 'https://oauth2.googleapis.com/token', array(
			'timeout' => 30,
			'body'    => array(
				'refresh_token' => $refresh_token,
				'client_id'     => $client_id,
				'client_secret' => $client_secret,
				'grant_type'    => 'refresh_token',
			),
		) );

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );
		$http_code = wp_remote_retrieve_response_code( $response );

		if ( 200 !== $http_code || empty( $body['access_token'] ) ) {
			// Refresh token may have been revoked.
			$this->clear_tokens();
			$error = isset( $body['error_description'] ) ? $body['error_description'] : __( 'Failed to refresh access token.', 'devsroom-google-review-showcase' );
			return new WP_Error( 'refresh_failed', $error );
		}

		$encrypted_access = $this->encrypt_token( $body['access_token'] );

		if ( ! $encrypted_access ) {
			return new WP_Error( 'encryption_failed', __( 'Failed to encrypt access token.', 'devsroom-google-review-showcase' ) );
		}

		$expires_in = ! empty( $body['expires_in'] ) ? absint( $body['expires_in'] ) : 3600;

		update_option( 'devsroom_greviews_oauth_access_token', $encrypted_access, false );
		update_option( 'devsroom_greviews_oauth_token_expiry', time() + $expires_in - 60, false );

		return true;
	}

	/**
	 * Get a valid access token, refreshing if necessary.
	 *
	 * @return string|WP_Error Valid access token or WP_Error.
	 */
	public function get_access_token() {
		$expiry = get_option( 'devsroom_greviews_oauth_token_expiry', 0 );

		// Refresh if expired or about to expire (5 minute buffer).
		if ( time() >= $expiry ) {
			$result = $this->refresh_access_token();
			if ( is_wp_error( $result ) ) {
				return $result;
			}
		}

		$encrypted = get_option( 'devsroom_greviews_oauth_access_token', '' );
		return $this->decrypt_token( $encrypted );
	}

	/**
	 * Check if the Google account is connected.
	 *
	 * @return bool
	 */
	public function is_connected() {
		$access_token  = get_option( 'devsroom_greviews_oauth_access_token', '' );
		$refresh_token = get_option( 'devsroom_greviews_oauth_refresh_token', '' );

		return ! empty( $access_token ) && ! empty( $refresh_token );
	}

	/**
	 * Fetch and store the connected user's email and profile info.
	 *
	 * @return array|WP_Error User info array or WP_Error.
	 */
	public function fetch_user_info() {
		$access_token = $this->get_access_token();

		if ( is_wp_error( $access_token ) ) {
			return $access_token;
		}

		$response = wp_remote_get( 'https://www.googleapis.com/oauth2/v2/userinfo', array(
			'timeout' => 15,
			'headers' => array(
				'Authorization' => 'Bearer ' . $access_token,
			),
		) );

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );
		$http_code = wp_remote_retrieve_response_code( $response );

		if ( 200 !== $http_code ) {
			return new WP_Error( 'userinfo_failed', __( 'Failed to fetch user info.', 'devsroom-google-review-showcase' ) );
		}

		$email = isset( $body['email'] ) ? sanitize_email( $body['email'] ) : '';
		$name  = isset( $body['name'] ) ? sanitize_text_field( $body['name'] ) : '';

		update_option( 'devsroom_greviews_oauth_user_email', $email, false );
		update_option( 'devsroom_greviews_oauth_user_name', $name, false );

		return array(
			'email' => $email,
			'name'  => $name,
		);
	}

	/**
	 * Disconnect the Google account — revoke tokens and clear stored data.
	 *
	 * @return true|WP_Error
	 */
	public function disconnect() {
		// Attempt to revoke the token at Google.
		$encrypted_access = get_option( 'devsroom_greviews_oauth_access_token', '' );
		$access_token     = $this->decrypt_token( $encrypted_access );

		if ( $access_token ) {
			wp_remote_get( add_query_arg(
				'token',
				$access_token,
				'https://oauth2.googleapis.com/revoke'
			), array( 'timeout' => 10 ) );
		}

		$this->clear_tokens();

		return true;
	}

	/**
	 * Clear all stored OAuth tokens and account data.
	 */
	public function clear_tokens() {
		delete_option( 'devsroom_greviews_oauth_access_token' );
		delete_option( 'devsroom_greviews_oauth_refresh_token' );
		delete_option( 'devsroom_greviews_oauth_token_expiry' );
		delete_option( 'devsroom_greviews_oauth_user_email' );
		delete_option( 'devsroom_greviews_oauth_user_name' );
		delete_option( 'devsroom_greviews_oauth_account_name' );
		delete_option( 'devsroom_greviews_oauth_location_name' );
		delete_option( 'devsroom_greviews_oauth_business_name' );
	}
}
