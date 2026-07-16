<?php
declare( strict_types = 1 );

namespace Automattic\WooCommerce\Tests\Admin\API\Reports;

use Automattic\WooCommerce\Admin\API\Reports\Cache;
use WC_Unit_Test_Case;

/**
 * Tests for the Reports Cache version bucketing and TTL.
 */
class CacheTest extends WC_Unit_Test_Case {

	/**
	 * Cache key used by the tests.
	 *
	 * @var string
	 */
	private $cache_key = 'wc_report_cache_test';

	/**
	 * Set up a clean version transient.
	 *
	 * Earlier tests in the suite may leave a raw version behind, and the
	 * monotonicity clamp in Cache::get_version() would preserve it.
	 */
	public function setUp(): void {
		parent::setUp();
		delete_transient( $this->cache_key );
		delete_transient( Cache::VERSION_OPTION . '-transient-version' );
	}

	/**
	 * Tear down test fixtures.
	 */
	public function tearDown(): void {
		parent::tearDown();
		remove_all_filters( 'woocommerce_analytics_cache_version_bucket_size' );
		remove_all_filters( 'woocommerce_analytics_cache_ttl' );
		delete_transient( $this->cache_key );
		delete_transient( Cache::VERSION_OPTION . '-transient-version' );
	}

	/**
	 * @testdox The default cache version is rounded down to a 10 minute bucket.
	 */
	public function test_default_version_is_bucketed_to_ten_minutes(): void {
		Cache::invalidate();
		$version = Cache::get_version();

		$this->assertSame( 0, (int) $version % ( 10 * MINUTE_IN_SECONDS ) );
	}

	/**
	 * @testdox A cached value survives an invalidation that lands in the same bucket.
	 */
	public function test_cached_value_survives_invalidation_within_bucket(): void {
		// A large bucket keeps both invalidations below inside one bucket.
		add_filter(
			'woocommerce_analytics_cache_version_bucket_size',
			function () {
				return HOUR_IN_SECONDS;
			}
		);

		$bucket_before = intdiv( time(), HOUR_IN_SECONDS );

		Cache::invalidate();
		Cache::set( $this->cache_key, 'expensive-report-data' );

		// Ensure time() has moved on, so unbucketed versions would differ.
		sleep( 1 );

		Cache::invalidate();
		$cached = Cache::get( $this->cache_key );

		if ( intdiv( time(), HOUR_IN_SECONDS ) !== $bucket_before ) {
			$this->markTestSkipped( 'A bucket boundary fell inside the test window.' );
		}

		$this->assertSame( 'expensive-report-data', $cached );
	}

	/**
	 * @testdox An invalidation never rolls the version back behind an externally written raw version.
	 */
	public function test_version_is_monotonic_after_external_raw_refresh(): void {
		// A large bucket keeps every step below inside one bucket.
		add_filter(
			'woocommerce_analytics_cache_version_bucket_size',
			function () {
				return HOUR_IN_SECONDS;
			}
		);

		$bucket_before = intdiv( time(), HOUR_IN_SECONDS );

		Cache::invalidate();
		Cache::set( $this->cache_key, 'pre-bust-report-data' );

		// An external caller writes a raw, unbucketed version, the way the
		// coupons lookup repair tool does through
		// WC_Cache_Helper::get_transient_version( 'woocommerce_reports', true ),
		// which stores (string) time(). Written directly with a mid-bucket
		// timestamp so the test does not depend on where in the bucket it runs.
		$raw_version = (string) ( (int) Cache::get_version() + 5 * MINUTE_IN_SECONDS );
		set_transient( Cache::VERSION_OPTION . '-transient-version', $raw_version );

		$this->assertFalse( Cache::get( $this->cache_key ), 'The raw version write must bust the earlier cache entry.' );

		// An entity change lands in the same bucket.
		Cache::invalidate();
		$version_after = Cache::get_version();

		if ( intdiv( time(), HOUR_IN_SECONDS ) !== $bucket_before ) {
			$this->markTestSkipped( 'A bucket boundary fell inside the test window.' );
		}

		$this->assertSame( $raw_version, $version_after, 'invalidate() must not roll the version back to the bucket start.' );
		$this->assertFalse( Cache::get( $this->cache_key ), 'The pre-bust cache entry must stay busted after a same-bucket invalidation.' );
	}

	/**
	 * @testdox A bucket size of 0 restores immediate invalidation on every change.
	 */
	public function test_zero_bucket_size_invalidates_immediately(): void {
		add_filter( 'woocommerce_analytics_cache_version_bucket_size', '__return_zero' );

		Cache::invalidate();
		$version_1 = Cache::get_version();

		sleep( 1 );

		Cache::invalidate();
		$version_2 = Cache::get_version();

		$this->assertNotSame( $version_1, $version_2 );
	}

	/**
	 * @testdox Cached values expire after one hour by default.
	 */
	public function test_default_ttl_is_one_hour(): void {
		if ( wp_using_ext_object_cache() ) {
			$this->markTestSkipped( 'Transient timeouts are not stored as options with an external object cache.' );
		}

		Cache::set( $this->cache_key, 'expensive-report-data' );
		$timeout = (int) get_option( '_transient_timeout_' . $this->cache_key );

		$this->assertLessThanOrEqual( time() + HOUR_IN_SECONDS, $timeout );
		$this->assertGreaterThan( time() + HOUR_IN_SECONDS - MINUTE_IN_SECONDS, $timeout );
	}

	/**
	 * @testdox The TTL filter controls how long cached values live.
	 */
	public function test_ttl_filter_is_respected(): void {
		if ( wp_using_ext_object_cache() ) {
			$this->markTestSkipped( 'Transient timeouts are not stored as options with an external object cache.' );
		}

		add_filter(
			'woocommerce_analytics_cache_ttl',
			function () {
				return 123;
			}
		);

		Cache::set( $this->cache_key, 'expensive-report-data' );
		$timeout = (int) get_option( '_transient_timeout_' . $this->cache_key );

		$this->assertLessThanOrEqual( time() + 123, $timeout );
		$this->assertGreaterThan( time() + 100, $timeout );
	}
}
