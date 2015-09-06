<?php

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Papi Property Divider class.
 *
 * @package Papi
 */
class Papi_Property_Divider extends Papi_Property {

	/**
	 * Display property html.
	 */
	public function html() {
		$options = $this->get_options();

		papi_render_html_tag( 'div', [
			'class'          => 'papi-property-divider',
			'data-papi-rule' => $this->html_name(),
			sprintf( '<h3><span>%s</span></h3>', $options->title )
		] );

		if ( ! papi_is_empty( $options->description ) ) {
			echo sprintf( '<p>%s</p>', $options->description );
		}
	}

	/**
	 * Render the final html that is displayed in the table.
	 */
	public function render_row_html() {
		if ( $this->get_option( 'raw' ) ) {
			parent::render_row_html();
		} else {
			papi_render_html_tag( 'tr', [
				'class' => $this->display ? '' : 'papi-hide',
				papi_html_tag( 'td', [
					'colspan' => 2,
					$this->render_property_html()
				] )
			] );
		}
	}
}
