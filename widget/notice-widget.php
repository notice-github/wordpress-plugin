<?php
class Elementor_Notice_Widget extends \Elementor\Widget_Base
{
	public function get_name()
	{
		return 'noticefaq';
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

	//------------------------//
	// Cards render functions //
	//------------------------//

	private function empty_card()
	{ ?>
		<div class="card">
			<div class="card__label"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" aria-hidden="true" focusable="false">
					<path d="M19 8h-1V6h-5v2h-2V6H6v2H5c-1.1 0-2 .9-2 2v8c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2v-8c0-1.1-.9-2-2-2zm.5 10c0 .3-.2.5-.5.5H5c-.3 0-.5-.2-.5-.5v-8c0-.3.2-.5.5-.5h14c.3 0 .5.2.5.5v8z"></path>
				</svg><?php echo __('Notice block', 'noticefaq') ?></div>
			<div class="card__instructions"><?php echo __('You need to edit the project ID of this block to see it appear on your site.', 'noticefaq') ?></div>
		</div>
	<?php
	}

	private function error_card($projectId)
	{ ?>
		<div class="card">
			<div class="card__label"><svg xmlns="http://www.w3.org/2000/svg" viewBox="-2 -2 24 24" width="24" height="24" aria-hidden="true" focusable="false">
					<path d="M10 2c4.42 0 8 3.58 8 8s-3.58 8-8 8-8-3.58-8-8 3.58-8 8-8zm1.13 9.38l.35-6.46H8.52l.35 6.46h2.26zm-.09 3.36c.24-.23.37-.55.37-.96 0-.42-.12-.74-.36-.97s-.59-.35-1.06-.35-.82.12-1.07.35-.37.55-.37.97c0 .41.13.73.38.96.26.23.61.34 1.06.34s.8-.11 1.05-.34z"></path>
				</svg><?php echo __('Could not find target "', 'noticefaq') . esc_html($projectId) . '"' ?></div>
		</div>
	<?php
	}

	private function block_card($projectId, $name)
	{ ?>
		<div class="card">
			<div class="card__label"><svg xmlns="http://www.w3.org/2000/svg" viewBox="-2 -2 24 24" width="24" height="24" aria-hidden="true" focusable="false">
					<svg xmlns="http://www.w3.org/2000/svg" height="20" width="15" viewBox="0 0 384 512">
						<path d="M272 384c9.6-31.9 29.5-59.1 49.2-86.2l0 0c5.2-7.1 10.4-14.2 15.4-21.4c19.8-28.5 31.4-63 31.4-100.3C368 78.8 289.2 0 192 0S16 78.8 16 176c0 37.3 11.6 71.9 31.4 100.3c5 7.2 10.2 14.3 15.4 21.4l0 0c19.8 27.1 39.7 54.4 49.2 86.2H272zM192 512c44.2 0 80-35.8 80-80V416H112v16c0 44.2 35.8 80 80 80zM112 176c0 8.8-7.2 16-16 16s-16-7.2-16-16c0-61.9 50.1-112 112-112c8.8 0 16 7.2 16 16s-7.2 16-16 16c-44.2 0-80 35.8-80 80z" />
					</svg>
				</svg><?php echo esc_html($name) ?></div>
			<div class="card__instructions">
				<i class="eicon-check-circle" style="font-size: 16px; margin-right: 2px; color: green"></i>
				<?php echo __('Your Notice project is loaded and will be visible on the public and preview page.', 'noticefaq') ?>
			</div>
			<div class="card__fieldset">
				<p class="hint">
					<i class="eicon-info-circle-o" style="font-size: 13px; margin-right: 4px; color: cornflowerblue"></i>
					<?php echo 'Project ID = "' . $projectId . '"' ?>
				</p>
			</div>
		</div>
<?php
	}

	//----------------//
	// Block Fetchers //
	//----------------//

	private function get_project_block($projectId)
	{
		$target = urldecode($projectId);

		$response = wp_remote_get("https://bdn.notice.studio/blocks/$target");

		$status = wp_remote_retrieve_response_code($response);
		if ($status !== 200) throw new Exception('Project not found');

		$body = wp_remote_retrieve_body($response);
		if (empty($body)) throw new Exception('Project not found');

		return json_decode($body)->data;
	}

	private function get_project_body($projectId)
	{
		$target = urldecode($projectId);

		if (array_key_exists('article', $_GET) && !empty($_GET['article'])) $target = $_GET['article'];

		$response = wp_remote_get("https://bdn.notice.studio/body/$target?integration=wordpress-plugin&navigationType=query");

		$status = wp_remote_retrieve_response_code($response);
		if ($status !== 200) throw new Exception('Project not found');

		$body = wp_remote_retrieve_body($response);
		if (empty($body)) throw new Exception('Project not found');

		return $body;
	}

	//----------------------//
	// Main render function //
	//----------------------//

	protected function render()
	{
		$projectId = $this->get_settings_for_display('project_id');

		if (\Elementor\Plugin::$instance->editor->is_edit_mode()) {
			echo '<style>';
			echo file_get_contents(__DIR__ . '/style.css');
			echo '</style>';

			if (empty($projectId)) {
				echo $this->empty_card();
				return;
			}

			try {
				$block = $this->get_project_block($projectId);
			} catch (Exception $e) {
				echo $this->error_card($projectId);
				return;
			}

			echo $this->block_card($projectId, $block->data->text);
		} else {
			if (empty($projectId)) return;

			try {
				$body = $this->get_project_body($projectId);
			} catch (Exception $e) {
				return;
			}

			echo $body;
		}
	}
}
