<?php
declare( strict_types = 1 );

/**
 * `checkout/payment-method.php` test.
 *
 * @covers `checkout/payment-method.php` template
 */
class WC_Payment_Method_Template_Test extends \WC_Unit_Test_Case {

	/**
	 * Render the template for the given gateway and return the wpautop()ed result,
	 * mirroring themes and page builders that re-filter the rendered page content,
	 * which is how the order-pay page grew empty paragraphs in woocommerce/woocommerce#53551.
	 *
	 * @param WC_Payment_Gateway $gateway Gateway to render.
	 * @return string
	 */
	private function render_with_wpautop( WC_Payment_Gateway $gateway ): string {
		return wpautop( wc_get_template_html( 'checkout/payment-method.php', array( 'gateway' => $gateway ) ) );
	}

	/**
	 * @testdox wpautop() adds no paragraphs or line breaks for a gateway without fields or description.
	 */
	public function test_wpautop_adds_no_markup_for_gateway_without_payment_box() {
		$gateway         = new WC_Mock_Payment_Gateway();
		$gateway->title  = 'Mock Gateway';
		$gateway->chosen = false;

		$content = $this->render_with_wpautop( $gateway );

		$this->assertStringContainsString( '<label for="payment_method_mock">Mock Gateway', $content );
		$this->assertDoesNotMatchRegularExpression( '#<p[^>]*>|</p>|<br\s*/?>#', $content );
	}

	/**
	 * @testdox wpautop() does not separate the radio input from its label for a gateway with a description.
	 */
	public function test_wpautop_keeps_radio_input_and_label_together_for_gateway_with_payment_box() {
		$gateway              = new WC_Mock_Payment_Gateway();
		$gateway->title       = 'Mock Gateway';
		$gateway->chosen      = false;
		$gateway->description = 'Mock description.';

		$content = $this->render_with_wpautop( $gateway );

		// The radio input must be followed directly by its label, with no paragraph break or <br /> in between.
		$this->assertMatchesRegularExpression( '#/> <label for="payment_method_mock">#', $content );
		$this->assertDoesNotMatchRegularExpression( '#<p[^>]*>\s*</p>#', $content );
		$this->assertDoesNotMatchRegularExpression( '#<br\s*/?>#', $content );
	}
}
