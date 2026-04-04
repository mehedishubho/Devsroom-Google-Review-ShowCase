<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Devsroom_GReviews_Google_API {

	/**
	 * @var Devsroom_GReviews_OAuth
	 */
	private $oauth;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->oauth = new Devsroom_GReviews_OAuth();
	}

	/**
	 * Make an authenticated API request to a Google service.
	 *
	 * @param string $url    Full request URL.
	 * @param string $method HTTP method (GET/POST).
	 * @param array  $body   Optional body for POST requests.
	 * @return array|WP_Error Parsed response body or WP_Error.
	 */
	private function api_request( $url, $method = 'GET', $body = null ) {
		$access_token = $this->oauth->get_access_token();

		if ( is_wp_error( $access_token ) ) {
			return $access_token;
		}

		$args = array(
			'timeout' => 30,
			'headers' => array(
				'Authorization' => 'Bearer ' . $access_token,
				'Content-Type'  => 'application/json',
			),
		);

		if ( 'POST' === $method && $body ) {
			$args['body']    = wp_json_encode( $body );
			$args['method']  = 'POST';
			$response = wp_remote_post( $url, $args );
		} else {
			$response = wp_remote_get( $url, $args );
		}

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$http_code = wp_remote_retrieve_response_code( $response );
		$data      = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( 200 !== $http_code ) {
			$error_msg = __( 'API request failed.', 'devsroom-google-review-showcase' );
			if ( is_array( $data ) && isset( $data['error']['message'] ) ) {
				$error_msg = $data['error']['message'];
			} elseif ( is_array( $data ) && isset( $data['error'] ) && is_string( $data['error'] ) ) {
				$error_msg = $data['error'];
			}
			return new WP_Error( 'api_error', $error_msg );
		}

		return $data;
	}

	/**
	 * Get the first account from the user's Google Business Profile.
	 *
	 * @return string|WP_Error Account name (e.g. "accounts/123456") or WP_Error.
	 */
	public function get_accounts() {
		$data = $this->api_request(
			'https://mybusinessaccountmanagement.googleapis.com/v1/accounts'
		);

		if ( is_wp_error( $data ) ) {
			return $data;
		}

		if ( empty( $data['accounts'] ) || ! is_array( $data['accounts'] ) ) {
			return new WP_Error( 'no_accounts', __( 'No Google Business accounts found.', 'devsroom-google-review-showcase' ) );
		}

		return $data['accounts'];
	}

	/**
	 * Get the first account name for API calls.
	 *
	 * @return string|WP_Error Account resource name or WP_Error.
	 */
	public function get_first_account_name() {
		// Check for cached account name.
		$cached = get_option( 'devsroom_greviews_oauth_account_name', '' );
		if ( ! empty( $cached ) ) {
			return $cached;
		}

		$accounts = $this->get_accounts();

		if ( is_wp_error( $accounts ) ) {
			return $accounts;
		}

		$account_name = $accounts[0]['name'];
		update_option( 'devsroom_greviews_oauth_account_name', $account_name, false );

		return $account_name;
	}

	/**
	 * Get business locations for the given account.
	 *
	 * @param string $account_name Account resource name (e.g. "accounts/123").
	 * @return array|WP_Error Array of locations or WP_Error.
	 */
	public function get_locations( $account_name ) {
		$url = add_query_arg(
			'readMask',
			'title,name',
			'https://mybusinessbusinessinformation.googleapis.com/v1/' . $account_name . '/locations'
		);

		$data = $this->api_request( $url );

		if ( is_wp_error( $data ) ) {
			return $data;
		}

		if ( empty( $data['locations'] ) || ! is_array( $data['locations'] ) ) {
			return new WP_Error( 'no_locations', __( 'No business locations found.', 'devsroom-google-review-showcase' ) );
		}

		return $data['locations'];
	}

	/**
	 * Fetch all reviews for a location, paginating through all results.
	 *
	 * @param string $account_name  Account resource name.
	 * @param string $location_name Location resource name.
	 * @return array|WP_Error Array of normalized reviews or WP_Error.
	 */
	public function fetch_all_reviews( $account_name, $location_name ) {
		$all_reviews = array();
		$page_token  = '';
		$page        = 0;

		do {
			$url = add_query_arg(
				array(
					'pageSize'  => 100,
					'readMask'  => 'reviewId,reviewer,starRating,comment,createTime',
				),
				'https://mybusiness.googleapis.com/v4/' . $location_name . '/reviews'
			);

			if ( ! empty( $page_token ) ) {
				$url = add_query_arg( 'pageToken', $page_token, $url );
			}

			$data = $this->api_request( $url );

			if ( is_wp_error( $data ) ) {
				// Return what we have so far if a later page fails.
				if ( ! empty( $all_reviews ) ) {
					return $all_reviews;
				}
				return $data;
			}

			$reviews = isset( $data['reviews'] ) ? $data['reviews'] : array();

			foreach ( $reviews as $raw ) {
				$all_reviews[] = $this->normalize_review( $raw );
			}

			$page_token = isset( $data['nextPageToken'] ) ? $data['nextPageToken'] : '';
			$page++;

		} while ( ! empty( $page_token ) && $page < 50 ); // Safety limit: 50 pages × 100 = 5000 reviews.

		return $all_reviews;
	}

	/**
	 * Normalize a single review from the Google Business Profile API.
	 *
	 * @param array $raw Raw review from Google API.
	 * @return array Normalized review.
	 */
	private function normalize_review( $raw ) {
		$star_map = array(
			'ONE'   => 1,
			'TWO'   => 2,
			'THREE' => 3,
			'FOUR'  => 4,
			'FIVE'  => 5,
		);

		$star_rating = isset( $raw['starRating'] ) ? $raw['starRating'] : 'FIVE';
		$rating      = isset( $star_map[ $star_rating ] ) ? $star_map[ $star_rating ] : 5;

		// Convert RFC 3339 timestamp to Unix.
		$time = 0;
		if ( ! empty( $raw['createTime'] ) ) {
			$dt = date_create( $raw['createTime'] );
			if ( $dt ) {
				$time = $dt->getTimestamp();
			}
		}

		$reviewer        = isset( $raw['reviewer'] ) ? $raw['reviewer'] : array();
		$display_name    = isset( $reviewer['displayName'] ) ? sanitize_text_field( $reviewer['displayName'] ) : '';
		$profile_photo   = isset( $reviewer['profilePhotoUrl'] ) ? esc_url_raw( $reviewer['profilePhotoUrl'] ) : '';
		$review_id       = isset( $raw['reviewId'] ) ? sanitize_text_field( $raw['reviewId'] ) : '';

		return array(
			'author_name'               => $display_name,
			'author_photo'              => $profile_photo,
			'rating'                    => $rating,
			'text'                      => isset( $raw['comment'] ) ? wp_kses_post( $raw['comment'] ) : '',
			'time'                      => $time,
			'relative_time_description' => '',
			'source'                    => 'oauth',
			'google_review_id'          => $review_id,
		);
	}

	/**
	 * Sync reviews from Google — fetch all and upsert into stored reviews.
	 *
	 * @return array|WP_Error Result with count info or WP_Error.
	 */
	public function sync_reviews() {
		$account_name  = $this->get_first_account_name();

		if ( is_wp_error( $account_name ) ) {
			return $account_name;
		}

		$location_name = get_option( 'devsroom_greviews_oauth_location_name', '' );

		if ( empty( $location_name ) ) {
			// Try to auto-select the first location.
			$locations = $this->get_locations( $account_name );
			if ( is_wp_error( $locations ) ) {
				return $locations;
			}
			$location_name = $locations[0]['name'];
			update_option( 'devsroom_greviews_oauth_location_name', $location_name, false );

			$business_name = isset( $locations[0]['title'] ) ? $locations[0]['title'] : '';
			update_option( 'devsroom_greviews_oauth_business_name', $business_name, false );
		}

		$fetched_reviews = $this->fetch_all_reviews( $account_name, $location_name );

		if ( is_wp_error( $fetched_reviews ) ) {
			return $fetched_reviews;
		}

		// Load existing stored reviews and index by google_review_id.
		$existing       = get_option( 'devsroom_greviews_oauth_reviews', array() );
		$existing_index = array();

		if ( is_array( $existing ) ) {
			foreach ( $existing as $review ) {
				if ( ! empty( $review['google_review_id'] ) ) {
					$existing_index[ $review['google_review_id'] ] = $review;
				}
			}
		}

		// Upsert: update existing or add new.
		$added   = 0;
		$updated = 0;

		foreach ( $fetched_reviews as $review ) {
			$rid = $review['google_review_id'];
			if ( isset( $existing_index[ $rid ] ) ) {
				$existing_index[ $rid ] = $review;
				$updated++;
			} else {
				$existing_index[ $rid ] = $review;
				$added++;
			}
		}

		// Re-index to a plain array and save.
		$merged = array_values( $existing_index );
		update_option( 'devsroom_greviews_oauth_reviews', $merged, false );
		update_option( 'devsroom_greviews_oauth_last_sync', current_time( 'mysql' ), false );

		return array(
			'total'   => count( $merged ),
			'added'   => $added,
			'updated' => $updated,
		);
	}

	/**
	 * Get stored OAuth reviews.
	 *
	 * @return array Array of normalized reviews.
	 */
	public function get_stored_reviews() {
		$reviews = get_option( 'devsroom_greviews_oauth_reviews', array() );
		return is_array( $reviews ) ? $reviews : array();
	}
}
