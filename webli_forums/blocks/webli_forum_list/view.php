<?php
defined('C5_EXECUTE') or die("Access Denied.");
$th = Loader::helper('text');
$c = Page::getCurrentPage();
$dh = Core::make('helper/date'); /* @var $dh \Concrete\Core\Localization\Service\Date */
$imageHelper = Core::make('helper/image');
$replies = $controller->getConversations() ?>

<style>
.u-avatar {
	float: left;
	margin: 5px 10px 0 0;
}

</style>

<?php if ( $c->isEditMode() && $controller->isBlockEmpty()) { ?>
    <div class="ccm-edit-mode-disabled-item"><?php echo t('Empty Forum List Block.')?></div>
<?php } else { ?>

<div class="forumListWrapper">

   <?php if ($pageListTitle): ?>
	   <div class="ForumListHeader">
		   <h1><?php echo t($pageListTitle)?></h1>
	   </div>
   <?php endif; ?>

   <?php if ($rssUrl): ?>
	   <a href="<?php echo $rssUrl ?>" target="_blank" class="forumListRssFeed"><i class="fa fa-rss"></i></a>
   <?php endif; ?>



	<?php	
	if($c->getCollectionAttributeValue('forum_category')) {
		// Display list of forum posts on Forum category pages
		foreach ($pages as $page):
			// Prepare data for each page being listed...
			$title = $th->entities($page->getCollectionName());
			$url = $nh->getLinkToCollection($page);
			$target = ($page->getCollectionPointerExternalLink() != '' && $page->openCollectionPointerExternalLinkInNewWindow()) ? '_blank' : $page->getAttribute('nav_target');
			$target = empty($target) ? '_self' : $target;
			unset($display);
			$display = $displaySettings[$page->getCollectionParentID()];
			$userInfo = UserInfo::getByID($page->getCollectionUserID());
			
			//Replace Description with page content
			if($use_content && !$page->getCollectionAttributeValue('use_forum_description')) {
				$description = $page->getCollectionAttributeValue('forum_post');
			} else {
				$description = $page->getCollectionDescription();
			}
			
			if($controller->truncateSummaries) {
				$th->sanitize($description);
				$description = $controller->truncateSummaries ? $th->wordSafeShortText($description, $controller->truncateChars) : $description;
				$description = $th->entities($description);
			}
			
			$thumbnail = false;
			if ($displayThumbnail) {
				$thumbnail = $page->getAttribute($thumbnail_attribute);
			}
	  
			$date = $dh->getSystemDateTime($page->getCollectionDatePublic(), $mask = $date_format);
	  
			if($page->getCollectionAttributeValue('forum_name')):
				// Name saved when post from user not logged in
			   $author = $page->getCollectionAttributeValue('forum_name');
			else: 
			   $author = $userInfo->getUserName();
			endif;
			
			// Forum List displayed on Blog Category Pages
			if($page->getCollectionAttributeValue('forum_post_approved') || $forumAdmin):?>
				
				<div class="forumListItem<?php if(!$page->getCollectionAttributeValue('forum_post_approved')) echo ' forumUnapproved'?> <?php if($page->getCollectionAttributeValue('forum_pin')) echo ' forumPinned'?>">
					
					<?php if (is_object($thumbnail)): ?>
						<div class="forumListThumbnail">
							<?php
							if($crop) $crop = '$crop="true"';
							$img = $imageHelper->getThumbnail($thumbnail, $thumb_width, $thumb_height, $crop); ?>
							<img class="img-responsive" src="<?php echo $img->src; ?>" alt="" />
						</div>
					<?php endif; ?>
			 
			 
					<?php if ($includeName): ?>
						<div class="forumListTitle">
							<h3><a href="<?php echo $url ?>" target="<?php echo $target ?>"><?php echo $title ?></a></h3>
						</div>
					<?php endif; ?>
	
	
					<?php if ($includeDate || $display_author): ?>
						<h5>
							<div class="forumListDateAuthor">
							<?php if($includeDate) echo '<span class="forumListDate">' . $date . '</span>'; ?>
							<?php if($display_author) echo ' by <span class="forumListAuthor">' . $author . '</span>'; ?>
							<?php if($forumAdmin && $page->getCollectionAttributeValue('forum_email') ) echo ' <span class="forumListEmail"> (' . $page->getCollectionAttributeValue("forum_email") . ')</span>'; ?>
						   </div>
						</h5>
					<?php endif;
					
					// Displays forum pin and unapproved message
					if($forumAdmin && !$page->getCollectionAttributeValue('forum_post_approved')) echo '<div class="forumUnApprovedText">' . t("UnApproved") . '</div>';
					if($forumAdmin && $page->getCollectionAttributeValue('forum_pin')) echo '<div class="forumPinnedText">' . t("Pinned") . '</div>';
						
	
					if ($includeDescription): ?>
					   <div class="forumListDescription">
							
							<?php if($display['display_avatars']):
								print Loader::helper('concrete/avatar')->outputUserAvatar($userInfo);
							endif;
							
							echo $description;
	
							if ($useButtonForLink): ?>
								<div class="forumListReadMore">
								<a href="<?php echo $url?>" class="<?php echo $buttonClasses?>"><?php echo $buttonLinkText?></a>
								</div>
							<?php elseif($truncateSummaries): ?>
								<span class="forumListReadMore"><a href="<?php echo $url ?>" target="<?php echo $target ?>"> <?php echo t('Read More') ?></a></span>
							<?php endif; ?>
							<div style="clear:both"></div>
						</div>
					<?php endif; ?>
					
					
					<?php
					if($forumReplies > 0):
						unset($replies);
						$replies = $controller->getLandingPageConversations($page->getCollectionID());
						
						$display = $controller->category_defaults($c->getCollectionID());

						if($replies && $display['enable_comments']):
							echo '<div class="latestReplies">';
							echo '<h5>Latest Replies</h5>';
							$i = 1;
							foreach($replies as $reply){	
								$cnv = $reply->getConversationObject();
								echo $reply->cnvMessageAuthorName . ' on ' . $dh->formatDateTime(strtotime($reply->getConversationMessageDateTime())) . '<br/>';
								echo '<div class="latestReply">"' . $th->wordSafeShortText($reply->getConversationMessageBodyOutput(),150) . '"</div>';
								$i++;
								if($i > $forumReplies) break;
								}
							echo '</div>';
						endif;
					endif; ?>
			</div>

				<?php
			endif;
		endforeach;
	} else {
		// Forum List Displayed on Forum Landing Page (Parent above Forum Categories). 
		foreach ($pages as $page):
			echo '<div class="forumListCategories">';
			// Prepare data for each page being listed...
			$title = $th->entities($page->getCollectionName());
			$url = $nh->getLinkToCollection($page);
			$target = ($page->getCollectionPointerExternalLink() != '' && $page->openCollectionPointerExternalLinkInNewWindow()) ? '_blank' : $page->getAttribute('nav_target');
			$target = empty($target) ? '_self' : $target;
			
			$description = $page->getCollectionDescription();
			
			if($controller->truncateSummaries) {
				$th->sanitize($description);
				$description = $controller->truncateSummaries ? $th->wordSafeShortText($description, $controller->truncateChars) : $description;
				$description = $th->entities($description);
			}
			
			$thumbnail = false;
			if ($displayThumbnail) {
				$thumbnail = $page->getAttribute($thumbnail_attribute);
			}
			
			$includeEntryText = false;
			if ($includeName || $includeDescription || $useButtonForLink) {
				$includeEntryText = true;
			}
	  
			$date = $dh->getSystemDateTime($page->getCollectionDatePublic(), $mask = $date_format);
					
			if (is_object($thumbnail)): ?>
				<div class="forumListThumbnail">
					<?php
					if($crop) $crop = '$crop="true"';
					$img = $imageHelper->getThumbnail($thumbnail, $thumb_width, $thumb_height, $crop); ?>
					<img class="img-responsive" src="<?php echo $img->src; ?>" alt="" />
				</div>
			<?php endif; ?>
	
			<div class="forumListText">
				
				<div class="forumListTitle">
					<h3><a href="<?php echo $url ?>" target="<?php echo $target ?>"><?php echo $title ?></a></h3>
				</div>
				
   
				<?php if ($includeDescription): ?>
					<div class="forumListDescription">
						<?php echo $description; ?>
					</div>
				<?php
				endif; ?>
					
				<div class="forumListTotalDiscussions">	
					<?php
					$postList = $controller->getPostList($page->getCollectionID());
					echo 'Total Discussions: <a href="' . $url . '" target="_self">' . count($postList) . '</a><br/>';
					?>
				</div>
				
				<?php
				foreach($postList as $p){
					echo '<div class="latestDiscussionWrap">';
					if($p->getCollectionAttributeValue('forum_pin') ){
						echo '<h5>';	
					} else {
						echo '<h5>Latest Discussion: ';
					}
					echo '<a href="' . $nh->getLinkToCollection($p) . '">' . $p->getCollectionName() . '</a></h5>';
					echo 'by: ' . $page->getVersionObject()->getVersionAuthorUserName() . ' on ' . $date = $dh->getSystemDateTime($p->getCollectionDatePublic(), $mask = $date_format);
					echo '<div class="latestDiscussion">' . $th->wordSafeShortText($p->getCollectionAttributeValue('forum_post'), 280) . '<a href="' . $nh->getLinkToCollection($p) . '" target="' . $target . '"> Read More</a></div>';				
					
					$hasReplies = false;
					foreach($replies as $r){
						$cv = $r->getConversationObject();
						if ($page->getCollectionID() == Page::getByID($cv->cID)->getCollectionParentID()){
							$hasReplies = true;
						}
					}
					
					if($hasReplies) $display = $controller->category_defaults($page->getCollectionID());

					if($hasReplies && $display['enable_comments'] && $forumReplies > 0){
						echo '<div class="latestReplies">';
						echo '<h5>Latest Replies</h5>';
						$i = 1;
						foreach($replies as $reply){
							$cnv = $reply->getConversationObject();
							$replyPage = Page::getByID($cnv->cID);
							$replyParent = $replyPage->getCollectionParentID();
							if($page->getCollectionID() == Page::getByID($cnv->cID)->getCollectionParentID()){
								echo $reply->cnvMessageAuthorName . ' replied to <a href="' . $nh->getLinkToCollection($replyPage) . '">' . $replyPage->getCollectionName() . ' </a> on ' . $dh->formatDateTime(strtotime($reply->getConversationMessageDateTime())) . '<br/>';
								echo '<div class="latestReply">"' . $th->wordSafeShortText($reply->getConversationMessageBodyOutput(),150) . '"</div>';
								$i++;
								if($i > $forumReplies) break;
							}					
						}
						echo '</div>'; //latestReplies
					}		
					echo '</div>'; //latestDiscussionWrap
				break;	
				}
				
			echo '</div>'; //forumListText
			
		echo '</div>'; //forumListCategories

		endforeach;	   
	}
	
	if (count($pages) == 0): ?>
	   <div class="forumListNoPages"><?php echo h($noResultsMessage)?></div>
	<?php endif;?>

	<?php if ($showPagination): ?>
	   <?php echo $pagination;?>
	<?php endif; ?>

</div> <!--forumListWrapper-->
<?php
} ?>