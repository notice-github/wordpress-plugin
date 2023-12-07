<?php

function notice_admin_menu()
{
?>
	<iframe id="notice-app" src="https://app.notice.studio/editor?_iframe=wordpress" style="width: calc(100% - 20px); height: calc(100svh - 121px); border: none; margin-top: 20px; box-shadow: rgba(0, 0, 0, 0.24) 0px 3px 8px;"></iframe>

	<!-- <script>
		const iframe = document.getElementById('notice-app')

		iframe.addEventListener('load', (e) => {
			console.log('Loaded!')
		})
	</script> -->
<?php
}
