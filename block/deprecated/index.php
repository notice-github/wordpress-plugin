<?php

function notice_old_block()
{
	$dir        = dirname(__FILE__);
	$block_js   = 'index.js';
	$editor_css = 'editor.css';

	wp_enqueue_script(
		'notice-blocksr',
		plugins_url($block_js, __FILE__),
		array(
			'wp-blocks',
			'wp-i18n',
			'wp-element',
		),
		filemtime("$dir/$block_js")
	);

	wp_enqueue_style(
		'notice-blocksy',
		plugins_url($editor_css, __FILE__),
		array(
			'wp-blocks',
		),
		filemtime("$dir/$editor_css")
	);
}
