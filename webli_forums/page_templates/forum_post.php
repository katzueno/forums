<?php 
defined('C5_EXECUTE') or die("Access Denied.");
//If you move file to theme directory uncomment
// $this->inc('elements/header.php');
$date = Core::make('helper/date');
if($display['add_this'] && $display['add_this_script']) echo $display['add_this_script'];
?>

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
				<div class="forumPost main col-sm-8"> <!--Begin Main Column-->
					
					<?php if($display['enable_breadcrumb']): ?>
						<div class="forumBreadcrumb">
							<?php 	
							$bt = BlockType::getByHandle('autonav');
							$bt->controller->displayPages = 'top'; // 'top', 'above', 'below', 'second_level', 'third_level', 'custom', 'current'
							$bt->controller->orderBy = 'display_asc';  // 'chrono_desc', 'chrono_asc', 'alpha_asc', 'alpha_desc', 'display_desc','display_asc'             
							$bt->controller->displaySubPages = 'relevant_breadcrumb';  //none', 'all, 'relevant_breadcrumb', 'relevant'          
							$bt->controller->displaySubPageLevels = 'enough'; // 'enough', 'enough_plus1', 'all', 'custom'
							$bt->render('templates/forum_breadcrumb'); // for template 'templates/template_name';
							?>
						</div>
					<?php endif; ?>


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
								<h1><?php echo $c->getCollectionName(); ?></h1>
							<?php endif; ?>
							
							<h5>
							<?php if($display['display_date']):
									echo $date->getSystemDateTime($c->getCollectionDatePublic(), $mask = $display['date_format']) ?> by <?php echo $author;
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
							endif; ?>
							<?php echo $c->getCollectionAttributeValue('forum_post') ?>
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
						<?php endif; ?>
		
		
						<?php if($display['share_this']): ?>
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