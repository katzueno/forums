<?php 
defined('C5_EXECUTE') or die("Access Denied.");
$c = Page::getCurrentPage();
?>

	<div class="form-group">
		<label><?php   echo t('Block Class (Optional)')?></label>
		<?php   echo $form->text('block_class',$block_class) ?>
	 </div>
		