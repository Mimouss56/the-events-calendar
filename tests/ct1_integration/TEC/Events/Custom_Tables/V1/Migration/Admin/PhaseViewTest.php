<?php

namespace TEC\Events\Custom_Tables\V1\Migration\Admin;

use TEC\Events\Custom_Tables\V1\Migration\State;

class PhaseViewTest extends \Codeception\TestCase\WPTestCase {

	/**
	 * Should find and structure the templates with their metadata.
	 *
	 * @test
	 */
	public function should_compile_view() {
		// Setup with some known templates.
		$renderer = new Phase_View_Renderer( State::PHASE_PREVIEW_IN_PROGRESS, '/phase/preview-in-progress.php' );
		$renderer->register_node( 'progress-bar',
			'.tribe-update-bar-container',
			'/partials/progress-bar.php'
		);

		$output = $renderer->compile();

		// Check for expected compiled values.
		$this->assertNotEmpty( $output );
		$this->assertEquals( State::PHASE_PREVIEW_IN_PROGRESS, $output['key'] );
		$this->assertNotEmpty( $output['html'] );
		$this->assertIsString( $output['html'] );
		$this->assertIsArray( $output['nodes'] );
		foreach ( $output['nodes'] as $node ) {
			$this->assertNotEmpty( $node['html'] );
			$this->assertIsString( $node['html'] );
			$this->assertNotEmpty( $node['hash'] );
			$this->assertIsString( $node['hash'] );
			$this->assertNotEmpty( $node['key'] );
			$this->assertIsString( $node['key'] );
			$this->assertNotEmpty( $node['target'] );
			$this->assertIsString( $node['target'] );
		}
	}

	/**
	 * Should render HTML from Preview In Progress templates.
	 *
	 * @test
	 */
	public function should_render_preview_in_progress_ok() {
		// Preview In Progress templates.
		$renderer = new Phase_View_Renderer( State::PHASE_PREVIEW_IN_PROGRESS, '/upgrade-box-contents.php' );
		$renderer->register_node( 'progress-bar',
			'.tribe-update-bar-container',
			'/partials/progress-bar.php'
		);

		$output = $renderer->compile();
		$node   = array_pop( $output['nodes'] );

		// Check for expected compiled values.
		$this->assertNotEmpty( $output );
		$this->assertContains( 'tec-ct1-upgrade--' . State::PHASE_PREVIEW_IN_PROGRESS, $output['html'] );
		$this->assertContains( 'tribe-update-bar-container', $output['html'] );
		$this->assertContains( 'tribe-update-bar__summary-progress-text', $node['html'] );
	}
}