<?php

function notice_admin_menu()
{
	$query = array(
		'_iframe' => 'wordpress'
	);

	$project = array_key_exists('project', $_GET) ? $_GET['project'] : null;
	if (!is_null($project)) {
		$query['project'] = $project;
	}

	$workspace = array_key_exists('workspace', $_GET) ? $_GET['workspace'] : null;
	if (!is_null($workspace)) {
		$query['workspace'] = $workspace;
	}

	$iframeURL = 'https://app.notice.studio/editor?' . http_build_query($query);
	$iframeStyle = 'width: calc(100% - 20px); height: calc(100svh - 121px); border: none; margin-top: 20px; box-shadow: rgba(0, 0, 0, 0.24) 0px 3px 8px;';

?>
	<iframe id="notice-app" src="<?php echo $iframeURL ?>" style="<?php echo $iframeStyle ?>">
	</iframe>
<?php
}
