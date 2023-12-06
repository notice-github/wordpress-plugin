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

function notice_head()
{
	try {
		$post = get_post();
		$is_elementor = (bool)get_post_meta($post->ID, '_elementor_edit_mode', true);

		if ($is_elementor) {
			$document = Elementor\Plugin::$instance->documents->get($post->ID);

			$data = $document->get_elements_raw_data();
			if (empty($data)) return;

			$elements = $data[0]['elements'];

			foreach ($elements as $element) {
				if (!array_key_exists('widgetType', $element) || $element['widgetType'] !== 'noticefaq') continue;

				$target = urldecode($element['settings']['project_id']);

				add_block_head($target);
			}
		} else {
			$blocks = parse_blocks($post->post_content);

			foreach ($blocks as $block) {
				if ($block['blockName'] === 'noticefaq/block') {
					$target = urldecode($block['attrs']['projectId']);

					add_block_head($target);
				}
			}
		}
	} catch (Exception $e) {
		return;
	}
}