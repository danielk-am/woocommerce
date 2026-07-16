<?php
declare( strict_types=1 );

namespace Automattic\WooCommerce\Tests\Internal\Admin;

use Automattic\Jetpack\Constants;
use Automattic\WooCommerce\Internal\Admin\Analytics;
use WC_Unit_Test_Case;

/**
 * Analytics feature test.
 *
 * @covers \Automattic\WooCommerce\Internal\Admin\Analytics
 */
class AnalyticsTest extends WC_Unit_Test_Case {

	/**
	 * The REQUEST_URI value before the test ran.
	 *
	 * @var string|null
	 */
	private $original_request_uri;

	/**
	 * Set up: intercept redirect attempts so wp_safe_redirect() never reaches exit().
	 */
	public function setUp(): void {
		parent::setUp();

		$this->original_request_uri = isset( $_SERVER['REQUEST_URI'] ) ? $_SERVER['REQUEST_URI'] : null;

		add_filter( 'wp_redirect', array( $this, 'intercept_redirect' ) );
	}

	/**
	 * Tear down: restore the request URI, the REST_REQUEST constant, and the reload flag.
	 */
	public function tearDown(): void {
		remove_filter( 'wp_redirect', array( $this, 'intercept_redirect' ) );
		Constants::clear_single_constant( 'REST_REQUEST' );

		$is_updated = new \ReflectionProperty( Analytics::class, 'is_updated' );
		$is_updated->setAccessible( true );
		$is_updated->setValue( null, false );

		if ( null === $this->original_request_uri ) {
			unset( $_SERVER['REQUEST_URI'] );
		} else {
			$_SERVER['REQUEST_URI'] = $this->original_request_uri;
		}

		parent::tearDown();
	}

	/**
	 * Turns a redirect attempt into an exception, before the exit() that follows it.
	 *
	 * @param string $location Redirect destination.
	 * @throws \Exception Always, to signal the attempted redirect.
	 */
	public function intercept_redirect( $location ) {
		throw new \Exception( 'redirect attempted: ' . $location );
	}

	/**
	 * @testdox maybe_reload_page does not redirect when the option is toggled during a REST API request.
	 */
	public function test_maybe_reload_page_does_not_redirect_during_rest_request() {
		Constants::set_constant( 'REST_REQUEST', true );
		$_SERVER['REQUEST_URI'] = '/wp-json/wc/v3/settings/advanced/' . Analytics::TOGGLE_OPTION_NAME;

		Analytics::reload_page_on_toggle( 'no', 'yes' );
		Analytics::maybe_reload_page();

		// Reaching this point means no redirect was attempted: intercept_redirect() throws on any attempt.
		$this->assertTrue( true );
	}

	/**
	 * @testdox maybe_reload_page still redirects when the option is toggled on an admin settings save.
	 */
	public function test_maybe_reload_page_redirects_on_admin_settings_save() {
		$_SERVER['REQUEST_URI'] = '/wp-admin/admin.php?page=wc-settings&tab=advanced&section=features';

		Analytics::reload_page_on_toggle( 'no', 'yes' );

		$this->expectExceptionMessage( 'redirect attempted: /wp-admin/admin.php?page=wc-settings' );
		Analytics::maybe_reload_page();
	}
}
