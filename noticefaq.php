<?php

/**
 * Plugin Name:       Notice
 * Plugin URI:        https://wordpress.org/plugins/notice-faq/
 * Description:       The official Notice Wordpress Plugin. Notice allows you to create, translate, and deploy content quickly and easily everywhere on the web.
 * Requires at least: 5.0
 * Requires PHP:      6.0
 * Version:           2.0.0
 * Author:            Notice
 * Author URI:        https://notice.studio
 * License:           GPL-3.0
 * License URI:       https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain:       noticefaq
 */

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly.
}

function notice_init()
{
	//--------------//
	// Notice Block //
	//--------------//

	register_block_type(__DIR__ . '/block/build');

	//---------------//
	// Notice Widget //
	//---------------//

	add_action('elementor/widgets/register', function ($widgets_manager) {
		require_once(__DIR__ . '/widget/notice-widget.php');

		$widgets_manager->register(new \Elementor_Notice_Widget());
	});

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
			10
		);
	});
}

add_action('init', 'notice_init');
