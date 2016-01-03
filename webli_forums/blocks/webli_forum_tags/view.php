<?php
defined('C5_EXECUTE') or die("Access Denied.");

if( !Page::getCurrentPage()->getCollectionAttributeValue('forum_category') && !Page::getByID(Page::getCurrentPage()->getCollectionParentID())->getCollectionAttributeValue('forum_category')
    || Page::getCurrentPage()->getCollectionAttributeValue('forum_category') && $display['forum_tags_block']
    || Page::getByID( Page::getCurrentPage()->getCollectionParentID() )->getCollectionAttributeValue('forum_category') && $display['forum_tags_block'] ):
?>
	 
	 <div class="forumTagsWrapper <?php echo $class?>">
		  <h3><?php echo $title ?></h3>
		  <div class="forumTagsList">
			<?php echo $tags; ?>
		  </div>
	 </div>
<?php
elseif(Page::getCurrentPage()->isEditMode()):?>
    <div class="forumTagsDisabled" style="color:red; background-color:#ccc; text-align:center; padding: 10px; margin: 5px 0">
        <?php echo t('The Forum Tags Block is disabled in Forum Dashboard Settings.');
    </div>
<?php
endif; ?>