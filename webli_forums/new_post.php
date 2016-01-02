<?php 
defined('C5_EXECUTE') or die("Access Denied.");
?>

<div id="new-post-popup" class="white-popup mfp-hide">
	<form method="POST" action="<?php echo $this->action('new_forum_post'); ?>">
	  <?php 
	  if(count($forumPages) > 1) { ?>
		 <div class="form-group forumSelect">
			<label for="forumSelect"><?php echo t('Select Forum') ?></label>
			<select name="forumSelect">
			<?php
			foreach($forumPages as $fp) { ?>
			   <option value="<?php echo $fp->getCollectionID() ?>"><?php echo $fp->getCollectionName() ?></option>
			<?php
			} ?>
			</select>
		 </div>
	  <?php
	  } else { ?>
		<input type="hidden" name="forumSelect" value="<?php echo $forumPages[0]->getCollectionID() ?>"/>
	  <?php 
	  } ?>
	  
	  <div class="form-group forumTitle">
		  <label for="forumTitle"><?php echo t('Title') ?></label>
		  <input class="form-control" name="forumTitle" type="text">
		  <input type="hidden" name="parentID" value="<?php echo Page::getCurrentPage()->getCollectionID() ?>">
	  </div>

	   <?php
	   if(!$u->isLoggedIn()) { ?>
		  <div class="form-group forumName">
			  <label for="forumEmail"><?php echo t('Name') ?></label>
			  <input class="form-control" id="forumName" name="forumName" type="text"/>
		  </div>
				  
		  <div class="form-group forumEmail">
			  <label for="forumEmail"><?php echo t('Email Address (not displayed)') ?></label>
			  <input class="form-control" id="forumEmail" name="forumEmail" type="text"/>
		  </div>			
	   <?php
	   } ?>
  
	  <div class="form-group forumPost">
		  <label for="forumPost"><?php echo t('Post') ?></label>
		  <textarea style="min-height:200px" id="redactor-post" class="form-control" name="forumPost"></textarea>
	  </div>
	  
	  <div class="form-group forumTags">
		  <label for="forumTags"><?php echo t('Tags') ?></label>
		  <input class="form-control" id="forumTags" name="forumTags" type="text"/>
	  </div>
		 
	  <button style="float: left" type="submit" class="btn btn-default"><?php echo t('Submit') ?></button>
   </form>
   <button style="float: right" onclick="$.magnificPopup.close();"class="cancelButton btn btn-default">Cancel</button>
   <div style="clear: both"></div>
		
</div>