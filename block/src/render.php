<?php

get_block_wrapper_attributes();

if (!array_key_exists('projectId', $attributes) || empty($attributes['projectId'])) {
	return;
}

$target = urldecode($attributes['projectId']);
$data = null;

try {
	if (array_key_exists('article', $_GET) && !empty($_GET['article'])) $target = $_GET['article'];

	$response = wp_remote_get("https://bdn.notice.studio/document/$target?format=fragmented&integration=wordpress-plugin&navigationType=query");
	$data = wp_remote_retrieve_body($response);
	if (empty($data)) return;

	$data = json_decode($data);
} catch (Exception $e) {
	return;
}

add_action('wp_head', function () use ($data) {
	function attributes_to_string(array $attr): string
	{
		return implode(' ', array_map(function ($key) use ($attr) {
			$value = is_object($attr[$key]) ? (string) $attr[$key] : $attr[$key];
			$value = is_array($value) ? implode(' ', array_map(function ($item) {
				return (string) $item; // cast all second level array value to string.
			}, $value)) : (string) $value;
			return sprintf('%s="%s"', htmlspecialchars($key), htmlspecialchars($value));
		}, array_keys($attr)));
	}

	if (property_exists($data, 'head')) {
		foreach ($data->head as $elem) {
			if (property_exists($elem, 'attributes')) {
				$attrs = get_object_vars($elem->attributes);
			} else {
				$attrs = [];
			}

			echo "<{$elem->tagName} " . attributes_to_string($attrs) . ">";
			if (property_exists($elem, 'innerHTML')) echo $elem->innerHTML;
			else if (property_exists($elem, 'innerText')) echo $elem->innerText;
			echo "</{$elem->tagName}>";
		}
	}

	if (property_exists($data, 'meta')) {
		foreach ($data->meta as $elem) {
			$attrs = get_object_vars($elem->attributes);
			echo "<{$elem->tagName} " . attributes_to_string($attrs) . "></{$elem->tagName}>";
		}
	}

	echo "<style id=\"NTC_style-{$data->id}\">{$data->style}</style>";
	echo "<script id=\"NTC_script-{$data->id}\">{$data->script}</script>";
});

echo $data->body;
