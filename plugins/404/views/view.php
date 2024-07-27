<link rel="stylesheet" type="text/css" href="<?=pluginHttpPath('/assets/css/style.css')?>">

<div class="col-md-10 mx-auto p-4">
	<center>
		<h1>Page not found</h1>
		<div><i>Sorry, we couldnt find what you're looking for</i></div>

		<form class="input-group my-3 mx-auto" style="max-width: 500px;">
			<input type="text" name="find" class="form-control" value="<?= oldValue('find', '', 'get') ?>" autofocus="true">
			<button class="input-group-text bg-primary text-white" id="basic-addon1">
				Search
			</button>
		</form>

		<?php if(empty($_GET['find'])):?>
			<img src="<?= pluginHttpPath('/assets/images/404.jpg') ?>" style="width: 100%;max-width: 500px">
		<?php else:?>

			<?php if(!empty($results)):?>
				<div>
					<?php doAction(pluginId() . '_display_search_results', $results)?>
				</div>
			<?php else:?>
				<div>No results found !</div>
			<?php endif?>
		<?php endif?>
	</center>
</div>

<script src="<?= pluginHttpPath('/assets/js/plugin.js') ?>"></script>