<?php  defined('C5_EXECUTE') or die("Access Denied.");
$al = Core::make('helper/concrete/asset_library');
$fp = FilePermissions::getGlobal();
$tp = new TaskPermission();
$u = new User();
$form = Core::make('helper/form');
$nav = Core::make('helper/navigation');
$th = Core::make('helper/text');
if(!$forumAdmin) { ?>

   <style>
	  .fa.fa-code.re-icon.re-html,
	  .fa.fa-image.re-icon.re-image,
	  .fa.fa-link.re-icon.re-link {
		 display: none;
	  }
   </style>
		 
<?php
}
?>


<div>

	<div class="forumPost">

	  <?php
	  if(Page::getCurrentPage()->getCollectionAttributeValue('forum_post') && $forumAdmin) { ?>
		 <a href="#edit-post-popup" class="open-popup-link"><button class="forumEditButton btn btn-default">Edit</button></a>
		 
		 <form method="POST" action="<?php echo $this->action('delete_page'); ?>">
			<?php Loader::helper('validation/token')->output('delete_page') ?>
			<button onClick="return confirmDelete()" type="submit" class="forumDeleteButton btn btn-default"><?php echo t('Delete') ?></button>
		 </form>
		 
		 <?php
		 if($pinned){ ?>
			<form method="POST" action="<?php echo $this->action('set_unpin'); ?>">
			   <?php Loader::helper('validation/token')->output('set_unpin') ?>
			   <button type="submit" class="forumUnPinButton btn btn-default"><?php echo t('UnPin') ?></button>
			</form>
		 <?php
		 } else { ?>
			<form method="POST" action="<?php echo $this->action('set_pin'); ?>">
			   <?php Loader::helper('validation/token')->output('set_pin') ?>
			   <button type="submit" class="forumPinButton btn btn-default"><?php echo t('Pin') ?></button>
			</form>
		 <?php
		 }

		 if($approved){ ?>
			<form method="POST" action="<?php echo $this->action('unapprove_page'); ?>">
			   <?php Loader::helper('validation/token')->output('unapprove_page') ?>
			   <button type="submit" class="forumUnApproveButton btn btn-default"><?php echo t('UnApprove') ?></button>
			</form>
		 <?php
		 } else {?>
			<form method="POST" action="<?php echo $this->action('approve_page'); ?>">
			   <?php Loader::helper('validation/token')->output('approve_page') ?>
			   <button type="submit" class="forumApproveButton btn btn-default"><?php echo t('Approve') ?></button>
			</form>
		 <?php
		 } 
	  }
	  
	  if($forumAdmin || $publicForums || $loggedIn){ ?>
		 <a href="#new-post-popup" class="open-popup-link"><button style="float:right;" class="forumPostButton btn btn-default"><?php echo t('Add a Forum Post') ?></button></a>
	  <?php
	  } else { ?>
		<a href="/login"><button style="float:right;" class="forumPostButton btn btn-default"><?php echo t('Login or Register to Add New Post') ?></button></a> 
	  <?php
	  } ?>
	  <div style="clear: both"></div>
	</div>
   
</div>

<script>
   $(document).ready(function() {
		$('.open-popup-link').magnificPopup({
	  type:'inline',
	  midClick: true // Allow opening popup on middle mouse click. Always set it to true if you don't provide alternative source in href.
	});

   $('#redactor-post').redactor({
	   minHeight: 200,
	   'concrete5': {
		   filemanager: <?php echo $fp->canAccessFileManager()?>,
		   sitemap: <?php echo $tp->canAccessSitemap()?>,
		   lightbox: true
	   }
   });
   
   $('#redactor-edit').redactor({
	   minHeight: 200,
	   'concrete5': {
		   filemanager: <?php echo $fp->canAccessFileManager()?>,
		   sitemap: <?php echo $tp->canAccessSitemap()?>,
		   lightbox: true
	   }
   });

});
</script>

<!--Stops form submit with enter, but also stops add tag on enter - revisit-->
<!--<script type="text/javascript">

function stopRKey(evt) {
  var evt = (evt) ? evt : ((event) ? event : null);
  var node = (evt.target) ? evt.target : ((evt.srcElement) ? evt.srcElement : null);
  if ((evt.keyCode == 13) && (node.type=="text"))  {return false;}
}

document.onkeypress = stopRKey;

</script>
-->
<style>
.white-popup {
   border-radius: 25px;
   background: #FFF;
   padding: 20px;
   width: 70%;
   margin: 20px auto;
}
</style>

<div id="new-post-popup" class="white-popup mfp-hide">

	<form method="POST" action="<?php echo $this->action('new_forum_post'); ?>">
	  <?php
	  Loader::helper('validation/token')->output('new_forum_post');
	  
	  if(count($forumPages) > 1) { ?>
		 <div class="form-group forumSelect">
			<label for="forumSelect"><?php echo t('Select Forum') ?></label>
			<select name="forumSelect">
			<?php 
			foreach($forumPages as $fp) {
			   if($forumAdmin || $loggedIn|| in_array($fp->getCollectionID(), $publicForums)){ ?>
				  <option value="<?php echo $fp->getCollectionID() ?>"><?php echo $fp->getCollectionName() ?></option>
			   <?php
			   }
			} ?>
			</select>
		 </div>
	  <?php
	  } elseif(count($forumPages) == 1) { ?>
		<input type="hidden" name="forumSelect" value="<?php echo $forumPages[0]->getCollectionID() ?>"/>
	  <?php 
	  } ?>
	  
	  <div class="form-group forumTitle">
		  <label for="forumTitle"><?php echo t('Title') ?></label>
		  <input class="form-control" name="forumTitle" type="text" maxlength="255" required />
		  <input type="hidden" name="parentID" value="<?php echo Page::getCurrentPage()->getCollectionID() ?>">
	  </div>

	   <?php
	   if(!$u->isLoggedIn()) { ?>
		  <div class="form-group forumName">
			  <label for="forumName"><?php echo t('Name') ?></label>
			  <input class="form-control" id="forumName" name="forumName" type="text" maxlength="255" required />
		  </div>
				  
		  <div class="form-group forumEmail">
			  <label for="forumEmail"><?php echo t('Email Address (not displayed)') ?></label>
			  <input class="form-control" id="forumEmail" name="forumEmail" type="text" maxlength="255" required />
		  </div>			
	   <?php
	   } ?>
  
	  <div class="form-group forumPost">
		  <label for="forumPost"><?php echo t('Post') ?></label>
		  <?php
			if($settings['rich_text']): ?>
			   <textarea style="min-height:200px;width:100%;" id="redactor-post" class="form-control" name="forumPost"></textarea>
			<?php
			else: ?>
			   <textarea style="min-height:200px;width:100%;" id="forumPost" class="form-control" name="forumPost"></textarea>
			<?php
			endif ?>
	  </div>
	
	  <div class="form-group tags">
		  <style>
			.existingAttrValue,
			.newAttrValue{
			   display: inline-block;
			   margin-right: 5px;
			 }
			 .tagInput .ccm-input-text{
			   width: 300px;
			 }
			 .tagInput .input-group-btn{
			   display: unset;
			   margin-left: 4px;
			 }
			 .well{
			   padding: 0 15px 15px;
			 }
		 </style>
		 <div class="tagInput">
			<label for="tags"><?php echo t('Tags') ?></label>
			<?php
			$ak = CollectionAttributeKey::getByHandle('tags');
			echo $ak->render('form');
			?>
		 </div>
	  </div>

	 <?php
	  if($settings['optional_attributes']) { ?>
		 <div class="form-group attributes">
			 <div class="attributeInput">
			   <?php
			   $optAtts = unserialize($settings['optional_attributes']);
			   foreach($optAtts as $ot){
				   $ak = CollectionAttributeKey::getByID($ot);
				   if(is_object($ak)){
					   if($ak->atHandle != 'boolean') {
						   ?>
						   <label><?php echo $ak->getAttributeKeyName() ?></label>
					   <?php
					   }
					   echo $ak->render('form');
					   echo '<br/>';
				   }
			   } ?>					
			</div>
		  </div>
		 <?php
		 } ?>
	 


	  <button style="float: right" type="submit" class="btn btn-default"><?php echo t('Submit') ?></button>
   </form>
   <button style="float: left" onclick="$.magnificPopup.close();"class="cancelButton btn btn-default"><?php echo t('Cancel') ?></button>
   <div style="clear: both"></div>
		
</div>

<div id="edit-post-popup" class="white-popup mfp-hide">
	<form method="POST" action="<?php echo $this->action('edit_forum_post'); ?>">
	  <?php
	  Loader::helper('validation/token')->output('edit_forum_post');
	  
	  if(count($forumPages) > 1) { ?>
		 <div class="form-group forumSelect">
			   <label for="forumSelect"><?php echo t('Select Forum') ?></label>
			   <select name="forumSelect">
			   <?php
			   foreach($forumPages as $fp) { ?>
				  <option value="<?php echo $fp->getCollectionID() ?>" <?php if($fp->getCollectionID() == Page::getCurrentPage()->getCollectionParentID()) echo 'selected="selected"' ?>><?php echo $fp->getCollectionName() ?></option>
			   <?php
			   } ?>
			   </select>
		 </div>
			 <?php
	  } elseif(count($forumPages) == 1) { ?>
		<input type="hidden" name="forumSelect" value="<?php echo $forumPages[0]->getCollectionID() ?>"/>
	  <?php 
	  } ?>
		<div class="form-group forumTitle">
			<label for="forumTitle"><?php echo t('Title') ?></label>
			<input class="form-control" name="forumTitle" type="text" value="<?php echo Page::getCurrentPage()->getCollectionName() ?>"/>
			<input type="hidden" name="parentID" value="<?php echo Page::getCurrentPage()->getCollectionID() ?>">
		</div>

		 <?php
		 if(Page::getCurrentPage()->getCollectionAttributeValue('forum_name')) { ?>
		 	<div class="form-group forumName">
				<label for="forumEmail"><?php echo t('Name') ?></label>
				<input class="form-control" id="forumName" name="forumName" type="text" value="<?php echo Page::getCurrentPage()->getCollectionAttributeValue('forum_name') ?>"/>
				<br/>
				Email: <?php echo Page::getCurrentPage()->getCollectionAttributeValue('forum_email') ?>		
			</div>		
		 <?php
		 } ?>
	
		<div class="form-group forumPost">
			<label for="forumPost"><?php echo t('Post') ?></label>
			<?php
			if($settings['rich_text']): ?>
			   <textarea style="min-height:200px;width:100%;" id="redactor-edit" class="form-control" name="forumPost"><?php echo Page::getCurrentPage()->getCollectionAttributeValue('forum_post') ?></textarea>
			<?php
			else: ?>
			   <textarea style="min-height:200px;width:100%;" id="forumEdit" class="form-control" name="forumPost"><?php echo strip_tags(Page::getCurrentPage()->getCollectionAttributeValue('forum_post')) ?></textarea>
			<?php
			endif ?>
		 </div>


	  <div class="form-group tags">
		 <div class="tagInput">
			<label for="tags"><?php echo t('Tags') ?></label>
			<?php
			$ak = CollectionAttributeKey::getByHandle('tags');
			echo $ak->render('form', Page::getCurrentPage()->getAttributeValueObject($ak), true);
			?>
		 </div>
	  </div>
	 
	 <?php
	  if($settings['optional_attributes']) { ?>
		 <div class="form-group attributes">
			 <div class="attributeInput">
			   <?php
			   $page = Page::getCurrentPage();
			   $optAtts = unserialize($settings['optional_attributes']);
			   foreach($optAtts as $ot){
				   $ak = CollectionAttributeKey::getByID($ot);
				   if(is_object($ak)){
					   if($ak->atHandle != 'boolean') {
						   ?>
						   <label><?php echo $ak->getAttributeKeyName() ?></label>
					   <?php
					   }
					   $atValue = $page->getAttributeValueObject($ak);
					   echo $ak->render('form', $atValue);
					   echo '<br/>';
				   }
			   } ?>					
			</div>
		  </div>
		 <?php
		 } ?>
	  
	  <button style="float: right" type="submit" class="btn btn-default"><?php echo t('Submit') ?></button>
   </form>
   <button style="float: left" onclick="$.magnificPopup.close();"class="cancelButton btn btn-default"><?php echo t('Cancel') ?></button>
   <div style="clear: both"></div>
</div>


<script type="text/javascript">
	function confirmDelete()
	{
		var agree=confirm("<?php echo t('Are you sure you want to DELETE THIS PAGE?  This will remove the page from the website. THIS ACTION CANNOT BE UNDONE.') ?>");
		if (agree)
		return true ;
		else
		return false ;
	}
</script>