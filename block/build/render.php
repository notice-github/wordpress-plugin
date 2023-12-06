<?php

get_block_wrapper_attributes();

if (!array_key_exists('projectId', $attributes) || empty($attributes['projectId'])) {
	return;
}

try {
	$target = urldecode($attributes['projectId']);

	if (array_key_exists('article', $_GET) && !empty($_GET['article'])) $target = $_GET['article'];

	$response = wp_remote_get("https://bdn.notice.studio/body/$target?integration=wordpress-plugin&navigationType=query");

	$status = wp_remote_retrieve_response_code($response);
	if ($status !== 200) return;

	$body = wp_remote_retrieve_body($response);
	if (empty($body)) return;

	echo $body;
} catch (Exception $e) {
	return;
}
