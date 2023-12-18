<?

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

function add_block_head($target)
{
	try {
		if (array_key_exists('article', $_GET) && !empty($_GET['article'])) $target = $_GET['article'];

		$target = urlencode($target);

		$response = wp_remote_get("https://bdn.notice.studio/document/$target?format=fragmented&integration=wordpress-plugin&navigationType=query");

		$status = wp_remote_retrieve_response_code($response);
		if ($status !== 200) return;

		$body = wp_remote_retrieve_body($response);
		if (empty($body)) return;

		$data = json_decode($body);

		if (property_exists($data, 'head') && !empty($data->head)) {
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

		if (property_exists($data, 'meta') && !empty($data->meta)) {
			foreach ($data->meta as $elem) {
				$attrs = get_object_vars($elem->attributes);
				echo "<{$elem->tagName} " . attributes_to_string($attrs) . "></{$elem->tagName}>";
			}
		}

		echo '<style id="NTC_style-' . $data->id . '">' . $data->style . '</style>';
		echo '<script id="NTC_script-' . $data->id . '">' . $data->script . '</script>';
	} catch (Exception $e) {
		return;
	}
}

function flatten_blocks(array $blocks, array $keys, array $flattened = [])
{
	foreach ($blocks as $block) {
		if (array_key_exists($keys['detectField'], $block) && $block[$keys['detectField']] === $keys['detectValue']) {
			$flattened[] = $block;
		}

		if (array_key_exists($keys['recursionField'], $block) && !empty($block[$keys['recursionField']])) {
			$flattened = flatten_blocks($block[$keys['recursionField']], $keys, $flattened);
		}
	}

	return $flattened;
}

function notice_head()
{
	if (is_front_page()) {
		global $_wp_current_template_content;

		$post_content = $_wp_current_template_content;
		$is_elementor = false;
	} else {
		$post = get_post();
		if (is_null($post) || empty($post)) return;

		$post_content = $post->post_content;
		$post_id = $post->ID;
		$is_elementor = (bool)get_post_meta($post_id, '_elementor_edit_mode', true);
	}

	if ($is_elementor) {
		$document = Elementor\Plugin::$instance->documents->get($post_id);

		$blocks = $document->get_elements_raw_data();
		if (empty($blocks)) return;

		$blocks = flatten_blocks($blocks, array(
			'detectField' => 'widgetType',
			'detectValue' => 'noticefaq',
			'recursionField' => 'elements'
		));

		foreach ($blocks as $block) {
			if (!array_key_exists('project_id', $block['settings'])) continue;

			$projectId = $block['settings']['project_id'];
			if (empty($projectId)) continue;

			add_block_head($projectId);
		}
	} else {
		$blocks = parse_blocks($post_content);
		$script_injected = false;

		$blocks = flatten_blocks($blocks, array(
			'detectField' => 'blockName',
			'detectValue' => 'noticefaq/block',
			'recursionField' => 'innerBlocks'
		));

		foreach ($blocks as $block) {
			// @deprecated (for compatibility)
			if ($block['blockName'] === 'noticefaq-block/noticefaq' && !$script_injected) {
				echo '<script defer="defer" charset="UTF-8" src="https://bundle.notice.studio/index.js"></script>';
				$script_injected = true;
				continue;
			}

			if ($block['blockName'] === 'noticefaq/block') {
				if (!array_key_exists('projectId', $block['attrs'])) continue;

				$projectId = $block['attrs']['projectId'];
				if (empty($projectId)) continue;

				add_block_head($projectId);
			}
		}
	}
}
