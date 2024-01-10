<?php

/**
 * Plugin Name:       Notice
 * Plugin URI:        https://wordpress.org/plugins/notice-faq/
 * Description:       The official Notice Wordpress Plugin. Notice allows you to create, translate, and deploy content quickly and easily everywhere on the web.
 * Requires at least: 5.0
 * Requires PHP:      6.0
 * Version:           2.2.1
 * Author:            Notice
 * Author URI:        https://notice.studio
 * License:           GPL-3.0
 * License URI:       https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain:       noticefaq
 */

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly.
}

add_action('init', function () {
	//--------------//
	// Notice Block //
	//--------------//

	// @deprecated (for compatibility)
	require_once __DIR__ . '/block/deprecated/index.php';
	add_action('enqueue_block_editor_assets', function () {
		notice_old_block();
	}, 10, 0);

	register_block_type(__DIR__ . '/block/build');

	//---------------//
	// Notice Widget //
	//---------------//

	add_action('elementor/widgets/register', function ($widgets_manager) {
		require_once(__DIR__ . '/widget/notice-widget.php');

		$widgets_manager->register(new \Elementor_Notice_Widget());
	}, 10, 1);

	//------------------//
	// Notice shortcode //
	//------------------//

	// @deprecated (for compatibility)
	add_shortcode('noticefaq', function ($atts) {
		extract(
			shortcode_atts(
				array(
					'projectid' => ''
				),
				$atts
			)
		);

		$output = '<script defer="defer" charset="UTF-8" src="https://bundle.notice.studio/index.js"></script>';
		$output .= '<div class="notice-target-container" project-id="' . $projectid . '" notice-integration="wordpress-plugin"></div>';

		return $output;
	});

	add_shortcode('notice-block', function ($atts) {
		extract(
			shortcode_atts(
				array(
					'projectid' => ''
				),
				$atts
			)
		);

		$output = '<script defer="defer" charset="UTF-8" src="https://bundle.notice.studio/index.js"></script>';
		$output .= '<div class="notice-target-container" project-id="' . $projectid . '" notice-integration="wordpress-plugin"></div>';

		return $output;
	});

	//-------------//
	// Notice Head //
	//-------------//

	require_once __DIR__ . '/includes/head.php';
	add_action('wp_head', function () {
		try {
			notice_head();
		} catch (Exception $e) {
			return;
		}
	}, 10, 0);

	//------------//
	// Admin Menu //
	//------------//

	require_once __DIR__ . '/includes/admin-menu.php';
	add_action('admin_menu', function () {
		add_menu_page(
			'Notice',
			'Notice',
			'manage_options',
			'noticefaq',
			'notice_admin_menu',
			'dashicons-lightbulb',
			'58.5'
		);
	}, 10, 0);
}, 10, 0);
