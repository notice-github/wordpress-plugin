<?php
class Elementor_Notice_Widget extends \Elementor\Widget_Base
{
	public function get_name()
	{
		return 'notice';
	}

	public function get_title()
	{
		return __('Notice', 'noticefaq');
	}

	public function get_icon()
	{
		return 'eicon-elementor-circle';
	}

	public function get_categories()
	{
		return ['general'];
	}

	public function get_keywords()
	{
		return ['notice', 'widget'];
	}

	protected function register_controls()
	{
		$this->start_controls_section('settings', [
			'label' => esc_html__('Project Settings', 'noticefaq'),
			'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
		]);

		$this->add_control(
			'project_id',
			[
				'label' => esc_html__('Project ID', 'noticefaq'),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => '',
				'placeholder' => esc_html__('Enter your Project ID', 'noticefaq'),
			]
		);

		$this->end_controls_section();
	}

	private function render_styles()
	{
?>
		<style>
			.placeholder {
				background-color: #fff;
				border-radius: 2px;
				box-shadow: inset 0 0 0 1px #1e1e1e;
				box-sizing: border-box;
				color: #1e1e1e;
				margin: 0;
				outline: 1px solid transparent;
				padding: 1em;
				position: relative;
				text-align: left;
				width: 100%;
			}

			.placeholder__label {
				font-size: 18pt;
				font-weight: 400;
				user-select: text;
				align-items: center;
				display: flex;
				margin-bottom: 16px;
			}

			.placeholder__label svg {
				margin-right: 12px;
			}

			.placeholder__instructions {
				user-select: text;
				margin-bottom: 1em;
				font-size: 13px;
			}

			.placeholder__fieldset {
				display: flex;
				flex-direction: row;
				flex-wrap: wrap;
				width: 100%;
			}

			.placeholder .button {
				background: #006ba1;
				color: #fff;
				outline: 1px solid transparent;
				text-decoration: none;
				text-shadow: none;
				white-space: nowrap;
				align-items: center;
				border: 0;
				border-radius: 2px;
				box-sizing: border-box;
				cursor: pointer;
				display: inline-flex;
				font-family: inherit;
				font-size: 13px;
				font-weight: 400;
				height: 36px;
				margin: 0;
				padding: 6px 12px;
				text-decoration: none;
			}
		</style>
	<?php
	}

	private function empty_placeholder()
	{
		$this->render_styles();
	?>
		<div class="placeholder">
			<div class="placeholder__label"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" aria-hidden="true" focusable="false">
					<path d="M19 8h-1V6h-5v2h-2V6H6v2H5c-1.1 0-2 .9-2 2v8c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2v-8c0-1.1-.9-2-2-2zm.5 10c0 .3-.2.5-.5.5H5c-.3 0-.5-.2-.5-.5v-8c0-.3.2-.5.5-.5h14c.3 0 .5.2.5.5v8z"></path>
				</svg><?php echo __('Notice block', 'noticefaq') ?></div>
			<div class="placeholder__instructions"><?php echo __('You need to edit the project ID of this block to see it appear on your site.', 'noticefaq') ?></div>
			<div class="placeholder__fieldset"><button type="button" class="button is-primary"><?php echo __('Edit Project ID', 'noticefaq') ?></button></div>
		</div>
	<?php
	}

	private function error_placeholder($projectId)
	{
		$this->render_styles();
	?>
		<div class="placeholder">
			<div class="placeholder__label"><svg xmlns="http://www.w3.org/2000/svg" viewBox="-2 -2 24 24" width="24" height="24" aria-hidden="true" focusable="false">
					<path d="M10 2c4.42 0 8 3.58 8 8s-3.58 8-8 8-8-3.58-8-8 3.58-8 8-8zm1.13 9.38l.35-6.46H8.52l.35 6.46h2.26zm-.09 3.36c.24-.23.37-.55.37-.96 0-.42-.12-.74-.36-.97s-.59-.35-1.06-.35-.82.12-1.07.35-.37.55-.37.97c0 .41.13.73.38.96.26.23.61.34 1.06.34s.8-.11 1.05-.34z"></path>
				</svg><?php echo __('Could not find target "', 'noticefaq') . esc_html($projectId) . '"' ?></div>
			<div class="placeholder__fieldset"><button type="button" class="button is-primary"><?php echo __('Edit Project ID', 'noticefaq') ?></button></div>
		</div>
<?php
	}

	private function render_head($data, $includeScript = true)
	{
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
		if ($includeScript) echo "<script id=\"NTC_script-{$data->id}\">{$data->script}</script>";
	}

	private function get_project_data($projectId)
	{
		$target = urldecode($projectId);
		$data = null;

		if (array_key_exists('article', $_GET) && !empty($_GET['article'])) $target = $_GET['article'];

		$response = wp_remote_get("https://bdn.notice.studio/document/$target?format=fragmented&integration=wordpress-plugin&navigationType=query");
		$data = wp_remote_retrieve_body($response);
		if (empty($data)) throw new Exception('Project not found');

		$data = json_decode($data);

		if (property_exists($data, 'success') && $data->success === false) {
			throw new Exception('Project not found');
		}

		return $data;
	}

	protected function render()
	{
		$projectId = $this->get_settings_for_display('project_id');
		$isEditing = false;

		if (\Elementor\Plugin::$instance->editor->is_edit_mode()) {
			$isEditing = true;
		}

		if (empty($projectId)) {
			if ($isEditing) echo $this->empty_placeholder();
			return;
		}

		$data = null;
		try {
			$data = $this->get_project_data($projectId);
		} catch (Exception $e) {
			if ($isEditing) echo $this->error_placeholder($projectId);
			return;
		}

		if ($isEditing) {
			echo '<head>' . $this->render_head($data, !$isEditing) . '</head>';
		} else {
			add_action('wp_head', function () use ($data) {
				echo $this->render_head($data);
			});
		}

		echo $data->body;
	}
}
