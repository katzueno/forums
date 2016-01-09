<?php 
defined('C5_EXECUTE') or die("Access Denied.");
//If you move file to theme directory uncomment
// $this->inc('elements/header.php');

$date = Core::make('helper/date');
if($display['add_this'] && $display['add_this_script']) echo $display['add_this_script'];
?>

<!-- If you move file to theme directory you can delete these
	 styles and do all the styling in your CSS Files -->
<style>
	.forumUnApprovedPost{
		text-align: center;
		padding: 25px;
		font-weight: bold;
	}
	
	/*this style aligns things up input elemental*/
	.forumPost.main{
		padding-left: 0;
		padding-right: 0;
	}
	
	.u-avatar {
		float: left;
		margin: 10px 10px 0 0;
	}
</style>

<!-- If you move file to theme directory uncomment	
	<main>
		<div class="center container"> 
			<div class="row"> 
-->			
				<div class="forumPost main col-sm-8"> <!-- Begin Main Column -->
					
					<!-- Move complete sections wrapped in if statements to
						 rearrange the content of your forum post page -->
					<?php if($display['enable_breadcrumb']): ?>
						<div class="forumBreadcrumb">
							<?php 	
							$bt = BlockType::getByHandle('autonav');
							$bt->controller->displayPages = 'top'; 
							$bt->controller->orderBy = 'display_asc';     
							$bt->controller->displaySubPages = 'relevant_breadcrumb';       
							$bt->controller->displaySubPageLevels = 'enough';
							$bt->render('templates/forum_breadcrumb'); 
							?>
						</div>
					<?php endif; ?>

					<!-- Area containing post block at top of pages -->
					<div class="forumPostBlock">
						<?php
						$a = new Area('Forum Post');
						$a->display($c);			
						?>
					</div>
								
					<?php
					if($forumAdmin || $c->getCollectionAttributeValue('forum_post_approved')):?>

						<div class="forumPostTitle">
							<?php if($display['display_title']): ?>
								<h2><?php echo $c->getCollectionName(); ?></h2>
							<?php endif; ?>
							
							<h5>
							<?php if($display['display_date']):
									echo $date->getSystemDateTime($c->getCollectionDatePublic(), $mask = $display['date_format']);
							endif;
							
							if($display['display_author']):
								if($c->getCollectionAttributeValue('forum_name')) {
									$author = $c->getCollectionAttributeValue('forum_name');
								 } else { 
									$author = $c->getVersionObject()->getVersionAuthorUserName();
								 } ?>	
								by <?php echo $author;
							endif; ?>
							</h5>
							
							<?php if($forumAdmin && $c->getCollectionAttributeValue('forum_email') ) echo ' <span class="forumListEmail"> (' . $c->getCollectionAttributeValue("forum_email") . ')</span>'; ?>
						</div>

						<?php
						if($forumAdmin &&  !$c->getCollectionAttributeValue('forum_post_approved')) echo '<div class="forumUnApprovedText">' . t("UnApproved") . '</div>';
						if($forumAdmin && $c->getCollectionAttributeValue('forum_pin')) echo '<div class="forumPinnedText">' . t("Pinned") . '</div>';
						?>							
						
		
						
						<div class="forumPost">
							<?php if($display['display_avatars']):
								$u = new user();
								$usr = UserInfo::getByID($c->getCollectionUserID());
								print Loader::helper('concrete/avatar')->outputUserAvatar($usr);
							endif;
    						if($display['rich_text'])
    						{
    						    echo $c->getCollectionAttributeValue('forum_post');
                            }
                            else
                            {
                                echo nl2br(h($c->getCollectionAttributeValue('forum_post'));
                            }
                            ?>
							<div style="clear:both"></div>
						</div>
						
							
						<?php if($display['display_tags'] && $c->getCollectionAttributeValue('tags')):
							foreach($c->getCollectionAttributeValue('tags') as $t){	
								$tag[] = $t;
							}
							if($tag){ ?>
								<div class="forumTags">
									Tags: <?php echo implode(',  ', $tag) ?>
								</div>
							<?php
							} ?>
						<?php endif; 

						if($display['optional_attributes']) { ?>
							<?php
							$optionalAttributes = unserialize($display['optional_attributes']);
							
							foreach($optionalAttributes as $optAtt){
								$ak = CollectionAttributeKey::getByID($optAtt);
								if($ak->atHandle != 'boolean' && $c->getCollectionAttributeValue($ak->akHandle)) {
									?>
									<br/>
									<label><?php echo $ak->getAttributeKeyName() ?></label>
									<br/>
								<?php
								
								}
								echo $c->getCollectionAttributeValue($ak->akHandle);
								echo '<br/>';
							}		
						}
					
		
						if($display['share_this']): ?>
							<div class="blogShareThis">
								<?php echo $display['share_this_html'] ?>
							</div>
						<?php endif; ?>

						
						<?php if($display['add_this'] && $display['add_this_follow_html']): ?>
							<div class="blogAddThisFollow">
								<?php echo $display['add_this_follow_html'] ?>
							</div>
						<?php endif;
						
						
						if($display['add_this']  && $display['add_this_share_html']): ?>
							<div class="blogAddThisShare">
								<?php echo $display['add_this_share_html'] ?>
							</div>
						<?php endif;

						
						if($display['add_this'] && $display['add_this_recommend_html']): ?>
							<div class="blogAddThisRecommend">
								<?php echo $display['add_this_recommend_html'] ?>
							</div>
						<?php endif;
						
						
						if($display['enable_comments']): ?>				
							<div class="forumReplies">
								<h3><?php echo t('Comments') ?></h3>
								<?php
								$a = new Area('Forum Replies');
								$a->display($c);			
								?>
							</div>
						<?php endif; ?>
						
					
					<?php else: ?>
						<div class="forumUnApprovedPost"><?php echo t('This Post is Waiting Approval') ?></div>
					<?php
					endif?>
				</div>
					
				<div class="rightSidebar col-sm-3 col-sm-offset-1"> <!--Begin Sidebar Column-->
					<?php
					$a = new Area('Sidebar');
					$a->display($c);
					?>
				</div> <!--End Column-->

<!-- If you move file to theme directory uncomment					
			</div>
		 </div> 
	</main>
-->

<?php
//If you move file to theme directory uncomment
// $this->inc('elements/footer.php');
?>