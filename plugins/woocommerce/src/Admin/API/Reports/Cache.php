<?php
/**
 * REST API Reports Cache.
 *
 * Handles report data object caching.
 */

namespace Automattic\WooCommerce\Admin\API\Reports;

defined( 'ABSPATH' ) || exit;

/**
 * REST API Reports Cache class.
 */
class Cache {
	/**
	 * Cache version. Used to invalidate all cached values.
	 */
	const VERSION_OPTION = 'woocommerce_reports';

	/**
	 * Invalidate cache.
	 */
	public static function invalidate() {
		self::get_version( true );
	}

	/**
	 * Get cache version number.
	 *
	 * This is based on WC_Cache_Helper::get_transient_version(), but rounds the
	 * Unix timestamp down to a time bucket, so that bursts of entity changes
	 * cost at most one cache invalidation per bucket instead of one per change.
	 *
	 * @param bool $refresh True to regenerate the version.
	 * @return string
	 */
	public static function get_version( $refresh = false ) {
		$transient_name  = self::VERSION_OPTION . '-transient-version';
		$transient_value = get_transient( $transient_name );

		if ( false === $transient_value || true === $refresh ) {
			/**
			 * Filters the size, in seconds, of the analytics cache invalidation bucket.
			 *
			 * Invalidations within the same bucket produce the same cache version,
			 * so cached report data survives bursts of entity changes. Return 0 to
			 * invalidate immediately on every change.
			 *
			 * @since 11.1.0
			 * @param int $bucket_size Bucket size in seconds. Default 600 (10 minutes).
			 */
			$bucket_size = (int) apply_filters( 'woocommerce_analytics_cache_version_bucket_size', 10 * MINUTE_IN_SECONDS );

			$timestamp       = time();
			$transient_value = (string) ( $bucket_size > 0 ? $timestamp - ( $timestamp % $bucket_size ) : $timestamp );

			set_transient( $transient_name, $transient_value );
		}

		return $transient_value;
	}

	/**
	 * Get cached value.
	 *
	 * @param string $key Cache key.
	 * @return mixed
	 */
	public static function get( $key ) {
		$transient_version = self::get_version();
		$transient_value   = get_transient( $key );

		if (
			isset( $transient_value['value'], $transient_value['version'] ) &&
			$transient_value['version'] === $transient_version
		) {
			return $transient_value['value'];
		}

		return false;
	}

	/**
	 * Update cached value.
	 *
	 * @param string $key   Cache key.
	 * @param mixed  $value New value.
	 * @return bool
	 */
	public static function set( $key, $value ) {
		$transient_version = self::get_version();
		$transient_value   = array(
			'version' => $transient_version,
			'value'   => $value,
		);

		/**
		 * Filters the TTL, in seconds, for cached analytics report data.
		 *
		 * With bucketed invalidation, entity changes made late in a bucket can
		 * leave cached data stale until the next invalidation, so the TTL is
		 * the upper bound on how long stale data can be served.
		 *
		 * @since 11.1.0
		 * @param int $ttl TTL in seconds. Default HOUR_IN_SECONDS.
		 */
		$ttl = (int) apply_filters( 'woocommerce_analytics_cache_ttl', HOUR_IN_SECONDS );

		$result = set_transient( $key, $transient_value, $ttl );

		return $result;
	}
}
