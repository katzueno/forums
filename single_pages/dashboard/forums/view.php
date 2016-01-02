<?php defined('C5_EXECUTE') or die("Access Denied.");
$al = Loader::helper('concrete/asset_library');
$txt = Loader::helper('text');
$fp = FilePermissions::getGlobal();
$tp = new TaskPermission();
$u = new User();
?>
		
<script type="text/javascript">
	$(document).ready(function() {

		$('.redactor-edit').redactor({
			minHeight: 200,
			'concrete5': {
				filemanager: <?php echo $fp->canAccessFileManager()?>,
				sitemap: <?php echo $tp->canAccessSitemap()?>,
				lightbox: true
			}
		});
			
	});

	function confirmDelete()
	{
		var agree=confirm("<?php echo t('Are you sure you want to DELETE THIS PAGE?  This will remove the page from the website. THIS ACTION CANNOT BE UNDONE.') ?>");
		if (agree)
		return true ;
		else
		return false ;
	}

	function confirmReplyDelete()
	{
		var agree=confirm("<?php echo t('Are you sure you want to DELETE THIS REPLY? You can undelete replies in the Conversations Messages Dashboard Page') ?>");
		if (agree)
		return true ;
		else
		return false ;
	}	
	
</script>



<p>
	<?php

	switch ($activeTab){
		case 'settings':
			$settingsActive = true;
			break;
		case 'newPost':
			$newPostActive = true;
			break;
		case 'unapproved':
			$unapprovedActive = true;
			break;
		case 'pinned':
			$pinnedActive = true;
			break;
		case 'forumPosts':
			$forumPostsActive = true;
			break;
		case 'replies':
			$repliesActive = true;
			break;
		case 'unApprovedReplies':
			$unApprovedRepliesActive = true;
			break;
		case '':
			$settingsActive = true;
			break;
	}
		
	print Loader::helper('concrete/ui')->tabs(array(
	array('settings', t('Settings'), $settingsActive),
	array('newPost', t('New Post'), $newPostActive),
	array('forumPosts', t('Posts'), $forumPostsActive),
	array('unapproved', t('UnApproved Posts'), $unapprovedActive),
	array('pinned', t('Pinned Posts'), $pinnedActive),
	array('replies', t('Replies'), $repliesActive),
	array('unApprovedReplies', t('UnApproved Replies'), $unApprovedRepliesActive),
	array('help', t('Help'))
));?>
</p>

<div class="ccm-tab-content" id="ccm-tab-content-settings">
	<?php
	if($forumCategories):
		if(count($forumCategories) > 1 ){ ?>
		<div class="forumCategory" style="margin-bottom:20px;">
			<form method="post" id="select_forum_category" action="<?php echo $this->action('select_forum_category')?>">
				<input type="hidden" name="activeTab" value="settings">
				<label style="margin-right: 4px;" for="category"><?php echo t('Category') ?></label>
				<select name="activeCategory" onchange="this.form.submit()">
					<?php
					foreach($forumCategories as $cat){
						unset($selected);
						if($activeCategory == $cat->getCollectionID()) $selected = 'selected="selected"';
						echo '<option value="' . $cat->getCollectionID() . '" ' . $selected . '>' . $cat->getCollectionName() . '</option>';
						$selected = null;
						} ?>
		
				</select>
			</form>
		</div>
		<?php
		} ?>	
		<form method="post" id="save_settings" action="<?php echo $this->action('save_settings')?>">
			<input type="hidden" name="cID" value="<?php echo $cID ?>">
			<input type="hidden" name="activeTab" value="settings">
			<table class="table-bordered table">
				
				<tr>
					<td width="25%">
						<input type="checkbox" name="enable_comments" value="1" <?php if($settings['enable_comments']) echo 'checked="checked"'?>> <?php echo t('Enable Comments') ?>
						<br/>
						<input type="checkbox" name="enable_breadcrumb" value="1" <?php if($settings['enable_breadcrumb']) echo 'checked="checked"'?>> <?php echo t('Enable Breadcrumb Menu') ?>
						<br/>
						<input type="checkbox" name="anonymous_posts" value="1" <?php if($settings['anonymous_posts']) echo 'checked="checked"'?>> <?php echo t('Enable Anonymous Posts') ?>
						<br/>
						<input type="checkbox" name="mod_approval" value="1" <?php if($settings['mod_approval']) echo 'checked="checked"'?>> <?php echo t('Moderator Approval Required') ?>
					</td>
					
					<td width="25%">
						<input type="checkbox" name="forum_search_block" value="1" <?php if($settings['forum_search_block']) echo 'checked="checked"'?>> <?php echo t('Display Search Block') ?>
						<br/>
						<input type="checkbox" name="forum_archive_block" value="1" <?php if($settings['forum_archive_block']) echo 'checked="checked"'?>> <?php echo t('Display Archive Block') ?>
						<br/>
						<input type="checkbox" name="forum_tags_block" value="1" <?php if($settings['forum_tags_block']) echo 'checked="checked"'?>> <?php echo t('Display Tags Block') ?>
						<br/>	
					</td>
	
					<td width="25%">
						<input type="checkbox" name="display_title" value="1" <?php if($settings['display_title']) echo 'checked="checked"'?>> <?php echo t('Display Title') ?>
						<br/>
						<input type="checkbox" name="display_author" value="1" <?php if($settings['display_author']) echo 'checked="checked"'?>> <?php echo t('Display Author') ?>
						<br/>
						<input type="checkbox" name="display_tags" value="1" <?php if($settings['display_tags']) echo 'checked="checked"'?>> <?php echo t('Display Forum Tags') ?>
						<br/>
						<input type="checkbox" name="display_date" value="1" <?php if($settings['display_date']) echo 'checked="checked"'?>> <?php echo t('Display Date') ?>
						<br/>
						<?php echo t('Date Format') ?>
						<br/>
						<input type="text" maxlength="50" name="date_format" value="<?php echo $settings['date_format'] ?>">
					</td>
	
					<td width="25%">
						<input type="checkbox" name="display_avatars" value="1" <?php if($settings['display_avatars']) echo 'checked="checked"'?>> <?php echo t('Display Avatars') ?>
						<br/>
						<input type="checkbox" name="display_image" value="1" <?php if($settings['display_image']) echo 'checked="checked"'?>> <?php echo t('Display Forum Image') ?>
						<br/>
						<input type="checkbox" name="crop_image" value="1" <?php if($settings['crop_image']) echo 'checked="checked"'?>> <?php echo t('Crop Images') ?>	
						<br/>
						<?php echo t('Image Dimensions') ?>
						<br/>
						<div style="display: inline-block">	
							<?php echo t('Width') ?>
							<br/>
							<input maxlength="4" style="width:70px; margin-right:15px;" type="text" name="image_width" value="<?php echo $settings['image_width'] ?>">
						</div>
						<div style="display: inline-block">	
							<?php echo t('Height') ?>
							<br/>
							<input maxlength="4" style="width:70px;" type="text" name="image_height" value="<?php echo $settings['image_height'] ?>">							
						</div>
					</td>
				</tr>

				<tr>
					<td colspan="1">
						<label>Page Type</label>
						<br/>
						<select name="page_type">
						<?php 
						foreach($pageTypes as $ptype){ ?>
							<option value="<?php echo $ptype->getPageTypeID() ?>" <?php if($settings['page_type'] == $ptype->getPageTypeID()) echo 'selected="selected"'?>><?php echo $ptype->getPageTypeDisplayName() ?></option>
						<?php	
						}
						?>
						</select>
						<br/>
						<label>Page Template</label>
						<br/>
						<select name="page_template">
						<?php
						foreach($pageTemplates as $pt){ ?>
							<option value="<?php echo $pt->getPageTemplateID() ?>" <?php if($settings['page_template'] == $pt->getPageTemplateID()) echo 'selected="selected"'?>><?php echo $pt->getPageTemplateName() ?></option>
						<?php	
						}
						?>
						</select>
					</td>
	
					<td colspan="3">
						<label>Optional Attributes</label>
						<br/>
						<?php
						if($forumAttributes) {
							$optionalAttributes = unserialize($settings['optional_attributes']);
							
							foreach($forumAttributes as $key => $val){ ?>
								<div style="width: 24%;display: inline-block;">
								<input type="checkbox" name="optional_attributes[]" value="<?php echo $key ?>" <?php if($optionalAttributes && in_array($key, $optionalAttributes)) echo 'checked="checked"'?>> <?php echo $val ?>
								</div>
							<?php
							}
						}
						?>
					</td>
				</tr>




				
				<tr>
					<td colspan="4">
						<input id="notification" type="checkbox" name="notification" value="1" <?php if($settings['notification']) echo 'checked="checked"'?>> <?php echo t('E-Mail Notification of New Forum Post') ?>
						<div id="emailNotification" style="display: none">
							<label><?php echo t('Enter recipient E-Mail Addresses seperated by commas') ?></label>
							<br/>
							<input type="text" maxlength="255" name="email_addresses" value="<?php echo $settings['email_addresses'] ?>" style="width: 100%"/>
						</div>

							
							

					</td>
					

				
				</tr>
				<tr>
					<td colspan="2">
						<div id="addThis"class="addThis">
							<input id="add_this" type="checkbox" name="add_this" value="1" <?php if($settings['add_this']) echo 'checked="checked"'?>> <?php echo t('Add This') ?>
							<div id="addThisBox" style="display: none;">
								<?php echo t('Paste Add This Share Code Here: (<a href="http://www.addthis.com" target="_blank">www.addthis.com</a>)') ?><br/>
								<label><?php echo t('Copy Javascript Script Tags Here') ?></label>
								<textarea name="add_this_script"><?php echo $settings['add_this_script'] ?></textarea>
								<label><?php echo t('Copy Share HTML Tags Here') ?></label>
								<textarea name="add_this_share_html"><?php echo $settings['add_this_share_html'] ?>
								</textarea><label><?php echo t('Copy Follow HTML Tags Here') ?></label>
								<textarea name="add_this_follow_html"><?php echo $settings['add_this_follow_html'] ?></textarea>
								</textarea><label><?php echo t('Copy Recommend HTML Tags Here') ?></label>
								<textarea name="add_this_recommend_html"><?php echo $settings['add_this_recommend_html'] ?></textarea>
								
							</div>
						</div>
					</td>
	
					<td colspan="2">
						<div id="shareThis"class="shareThis">
							<input id="share_this" type="checkbox" name="share_this" value="1" <?php if($settings['share_this']) echo 'checked="checked"'?>> <?php echo t('Share This') ?>
							<div id="shareThisBox" style="display: none;">
								<?php echo t('Paste Share This Code Here: (<a href="http://www.sharethis.com" target="_blank">www.sharethis.com</a>)') ?><br/>
								<label><?php echo t('Copy Javascript Script Tags Here') ?></label>
								<textarea name="share_this_script"><?php echo $settings['share_this_script'] ?></textarea>
								<label><?php echo t('Copy HTML Tags Here') ?></label>
								<textarea name="share_this_html"><?php echo $settings['share_this_html'] ?></textarea>
								
							</div>
						</div>
					</td>
				</tr>
		
				<tr>
					<td colspan="4">
						<div style="text-align: center; margin: 10px;">	
							<?php echo $form->submit('Save Settings', 'Save Settings', array('class' => 'btn success' ));?>
						</div>
					</td>
				</tr>
			</table>
		</form>
	<?php
	else:
		echo t('Add a Forum Category page by setting a "forum_category" Page Attribute');
	endif; ?>
</div>

<div class="ccm-tab-content" id="ccm-tab-content-newPost">
	<div class="newPost">
		<?php
		if(count($forumCategories) > 1 ){ ?>
			<div class="forumCategory" style="margin-bottom:20px;">
				<form method="post" id="new_post_category" action="<?php echo $this->action('set_new_post_category')?>">
					<input type="hidden" name="activeTab" value="newPost">
					<label style="margin-right: 4px;" for="category"><?php echo t('Category') ?></label>
					<select name="newPostCategory" onchange="this.form.submit()">
						<option value="0"><?php echo t('Select a Category') ?></option>
						<?php
						foreach($forumCategories as $cat){
							unset($selected);
							if($newPostCategory == $cat->getCollectionID()) $selected = 'selected="selected"';
							echo '<option value="' . $cat->getCollectionID() . '" ' . $selected . '>' . $cat->getCollectionName() . '</option>';
							$selected = null;
							}	
						?>	
					</select>
				</form>
			</div>
		<?php
		}
		
		if($newPostCategory > 0):?>
			<form id="newForumPost" method="POST" action="<?php echo $this->action('new_forum_post') ?>">
				<input type="hidden" name="category" value="<?php echo $newPostCategory ?>"/>
				<input type="hidden" name="activeTab" value="newPosts">
				<?php Loader::helper('validation/token')->output('new_forum_post') ?>
				
				<div class="form-group title">
					<label for="title"><?php echo t('Title')?></label>
					<br/>
					<input maxlength="255" class="form-control" !important" id="title" type="text" name="title" maxlength="255" required>
				</div>
				
				<div class="form-group description">
				<label for="description"><?php echo t('Description')?></label>
					<br/>
					<textarea class="form-control" id="description" name="description" required> </textarea>
				</div>
				
				<table width="100%">
					<tr>
						<td width="50%">
							<div class="form-group user">
								<label for="user"><?php echo t('User')?></label>
								<?php
								echo Loader::helper('form/user_selector')->selectUser('user', $u->getUserID())
								?>
							</div>
						</td>
						
						<td width="50%">
							<div class="form-group date">
								<label for="date"><?php echo t('Date')?></label>
								<br/>
								<?php
								$fdh = Core::make('helper/form/date_time');
								echo $fdh->datetime('public_date');
								?>
							</div>
						</td>
					</tr>
				</table>
	
				<div class="form-group forumPost">
					<label for="forumPost"><?php echo t('Forum Post') ?></label>
					<textarea class="redactor-edit" name="forumPost"> </textarea>
				</div>
				
				<?php
				if($newPostSettings['display_image']) { ?>
					<div class="form-group forumImage">
						<label for="forumImage"><?php echo t('Forum Image') ?></label>
						<?php
						echo $al->file('newForumImage', 'forumImage', t('Choose Image'));?>
					</div>
				<?php
				}
		
				if($optionalAttributes) {
					foreach($optionalAttributes as $optAtt){
						$ak = CollectionAttributeKey::getByID($optAtt);
						if(is_object($ak)){
							if($ak->atHandle != 'boolean') {
								?>
								<label><?php echo $ak->getAttributeKeyName() ?></label>
							<?php
							} 
							echo $ak->render('form');
							echo '<br/>';
						}
					}		
				}
				
				if($newPostSettings['display_tags']) { ?>
					<div class="form-group tags">
					   <div class="tagInput">
						  <label for="tags"><?php echo t('Tags') ?></label>
						  <?php
						  $ak = CollectionAttributeKey::getByHandle('tags');
						  echo $ak->render('form');
						  ?>
					   </div>
					</div>
				<?php
				} ?>
				<input type="checkbox" name="pin"> <?php echo t('Pin')?>
				<br/>
				<br/>
				<button type="submit" <button class="btn btn-default"><?php echo t('Save') ?></button>
			</form>
		<?php
		endif; ?>
		
	</div>
	
</div>

<div class="ccm-tab-content" id="ccm-tab-content-forumPosts">
	<?php if(count($forumCategories) > 1 ){ ?>
		<div class="forumCategory" style="margin-bottom:15px;">
			<form method="post" id="sort_category" action="<?php echo $this->action('set_sort_category')?>">
				<input type="hidden" name="activeTab" value="forumPosts">
				<label style="margin-right: 4px;" for="category"><?php echo t('Filter by Category') ?></label>
				<select name="sortCategory" onchange="this.form.submit()">
					<option value="0"><?php echo t('All Categories') ?></option>
					<?php
					foreach($forumCategories as $cat){
						unset($selected);
						if($sortCategory == $cat->getCollectionID()) $selected = 'selected="selected"';
						echo '<option value="' . $cat->getCollectionID() . '" ' . $selected . '>' . $cat->getCollectionName() . '</option>';
						$selected = null;
						}	
					?>	
				</select>
			</form>
		</div>
	<?php
	}
	if($forumPosts){ ?>
		<table id="forumPosts" class="table table-striped">
			<thead>
				<tr>
					<th><?php echo t('Date') ?></th>
					<th><?php echo t('Title') ?></th>
					<th><?php echo t('Category') ?></th>
					<th><?php echo t('Author') ?></th>
					<th></th>
					<th></th>
					<th></th>
					<th></th>
				</tr>
			</thead>
			
			<tbody>
			
				<?php
				foreach($forumPosts as $p){
					if($p->getCollectionAttributeValue('forum_name')) {
						$author = $p->getCollectionAttributeValue('forum_name');
					 } else { 
						$author = UserInfo::getByID($p->getCollectionUserID())->getUserName();
					 } ?>
						<tr>
							<td>
								<?php echo $p->getCollectionDatePublic() ?>
							</td>
							
							<td>
								<a href="<?php echo $p->getCollectionPath() ?>"><?php echo $p->getCollectionName() ?></a>
							</td>
							
							<td>
								<a href="<?php echo Page::getByID($p->getCollectionParentID())->getCollectionPath() ?>"><?php echo Page::getByID($p->getCollectionParentID())->getCollectionName() ?>
							</td>
							
							<td>
								<?php echo $author;
								if($p->getCollectionAttributeValue('forum_email') ) echo '<br/>' . $p->getCollectionAttributeValue("forum_email") ?>
							</td>
							
							<td width=75>
								<form method="POST" action="<?php echo $this->action('delete_page') ?>">
									<input type="hidden" name="cID" value="<?php echo $p->getCollectionID() ?>"/>
									<input type="hidden" name="activeTab" value="forumPosts">
									<?php echo Loader::helper('validation/token')->output('delete_page') ?>
									<button onClick="return confirmDelete()" type="submit" class="forumDeleteButton btn btn-default"><?php echo t('Delete') ?></button>
								</form>
							</td>
							
							<td width=95>
								<form method="POST" action="<?php echo $this->action('unapprove_page') ?>">
									<input type="hidden" name="cID" value="<?php echo $p->getCollectionID() ?>"/>
									<input type="hidden" name="activeTab" value="forumPosts">
									<?php echo Loader::helper('validation/token')->output('unapprove_page') ?>
									<button type="submit" class="forumBanButton btn btn-default"><?php echo t('unApprove') ?></button>
								</form>
							</td>
					
							<td width=75>
								<?php
								if($p->getCollectionAttributeValue('forum_pin')): ?>
									<form method="POST" action="<?php echo $this->action('un_pin_page') ?>">
										<input type="hidden" name="cID" value="<?php echo $p->getCollectionID() ?>"/>
										<input type="hidden" name="activeTab" value="forumPosts">
										<?php echo Loader::helper('validation/token')->output('un_pin_page') ?>
										<button type="submit" class="btn btn-default"><?php echo t('UnPin') ?></button>
									</form>
								<?php
								else: ?>
									<form method="POST" action="<?php echo $this->action('pin_page') ?>">
										<input type="hidden" name="cID" value="<?php echo $p->getCollectionID() ?>"/>
										<input type="hidden" name="activeTab" value="forumPosts">
										<?php echo Loader::helper('validation/token')->output('pin_page') ?>
										<button type="submit" class="btn btn-default"><?php echo t('Pin') ?></button>
									</form>
								<?php
								endif; ?>
							</td>

							<td style="padding: 14px 0 8px 0; width:32px;">
								<a href="/index.php?cID=<?php echo $p->getCollectionID()?>&ctask=check-out-first"><i class="fa fa-edit" style="font-size:25px;"></i></a>				
							</td>
						</tr>
						
						<tr>
							<td colspan="8">
								<div class="forumPostPreview"> 
									<?php echo $p->getCollectionAttributeValue('forum_post')?>
								</div>
							</td>
						</tr>
				<?php
				}
				?>
		</tbody>
	</table>
	<?php
	} else { ?>
		<div class="forumsDashboardMessage"><?php echo t('There are no Posts') ?></div>
	<?php
	} ?>
</div>


<div class="ccm-tab-content" id="ccm-tab-content-unapproved">
	<?php if(count($forumCategories) > 1 ){ ?>
		<div class="forumCategory" style="margin-bottom:15px;">
			<form method="post" id="sort_category" action="<?php echo $this->action('set_sort_category')?>">
				<input type="hidden" name="activeTab" value="unapproved">
				<label style="margin-right: 4px;" for="category"><?php echo t('Filter by Category') ?></label>
				<select name="sortCategory" onchange="this.form.submit()">
					<option value="0"><?php echo t('All Categories') ?></option>
					<?php
					foreach($forumCategories as $cat){
						unset($selected);
						if($sortCategory == $cat->getCollectionID()) $selected = 'selected="selected"';
						echo '<option value="' . $cat->getCollectionID() . '" ' . $selected . '>' . $cat->getCollectionName() . '</option>';
						$selected = null;
						}	
					?>	
				</select>
			</form>
		</div>
	<?php
	}
	if($unApprovedPages){ ?>
		
		<table id="unapprovedPosts" class="table table-striped">
			<thead>
				<tr>
					<th><?php echo t('Date') ?></th>
					<th><?php echo t('Title') ?></th>
					<th><?php echo t('Category') ?></th>
					<th><?php echo t('Author') ?></th>
					<th></th>
					<th></th>
					<th></th>
				</tr>
			</thead>
			
			<tbody>
			
				<?php
				foreach($unApprovedPages as $up){
					if($up->getCollectionAttributeValue('forum_name')) {
						$author = $up->getCollectionAttributeValue('forum_name');
					 } else { 
						$author = UserInfo::getByID($up->getCollectionUserID())->getUserName();
					 } ?>
					<tr>
						<td>
							<?php echo $up->getCollectionDatePublic() ?>
						</td>
						
						<td>
							<a href="<?php echo $up->getCollectionPath() ?>"><?php echo $up->getCollectionName() ?></a>
						</td>
						
						<td>
							<a href="<?php echo Page::getByID($up->getCollectionParentID())->getCollectionPath() ?>"><?php echo Page::getByID($up->getCollectionParentID())->getCollectionName() ?>
						</td>
						
						<td>
							<?php echo $author;
							if($up->getCollectionAttributeValue('forum_email') ) echo '<br/>' . $up->getCollectionAttributeValue("forum_email") ?>
						</td>
						
						<td width=75>
							<form method="POST" action="<?php echo $this->action('delete_page') ?>">
								<input type="hidden" name="cID" value="<?php echo $up->getCollectionID() ?>"/>
								<input type="hidden" name="activeTab" value="unapproved">
								<?php echo Loader::helper('validation/token')->output('delete_page') ?>
								<button onClick="return confirmDelete()" type="submit" class="forumDeleteButton btn btn-default"><?php echo t('Delete') ?></button>
							</form>
						</td>
						
						<td width=95>
							<form method="POST" action="<?php echo $this->action('approve_page') ?>">
								<input type="hidden" name="cID" value="<?php echo $up->getCollectionID() ?>"/>
								<input type="hidden" name="activeTab" value="unapproved">
								<?php echo Loader::helper('validation/token')->output('approve_page') ?>
								<button type="submit" class="forumBanButton btn btn-default"><?php echo t('Approve') ?></button>
							</form>
						</td>
						
						<td style="padding: 14px 0 8px 0; width:32px;">
							<a href="/index.php?cID=<?php echo $up->getCollectionID()?>&ctask=check-out-first"><i class="fa fa-edit" style="font-size:25px;"></i></a>	
						</td>
					</tr>
						
					<tr>
					<td colspan="7">
						<div class="forumPostPreview" >
							<?php echo $up->getCollectionAttributeValue('forum_post')?>
						</div>
					</td>
				</tr>
				<?php
				}
				?>
		</tbody>
	</table>
	<?php
	} else { ?>
		<div class="forumsDashboardMessage"><?php echo t('There are no unApproved Posts') ?></div>
	<?php
	} ?>
</div>


<div class="ccm-tab-content" id="ccm-tab-content-pinned">
	<?php if(count($forumCategories) > 1 ){ ?>
		<div class="forumCategory" style="margin-bottom:15px;">
			<form method="post" id="sort_category" action="<?php echo $this->action('set_sort_category')?>">
				<input type="hidden" name="activeTab" value="pinned">
				<label style="margin-right: 4px;" for="category"><?php echo t('Filter by Category') ?></label>
				<select name="sortCategory" onchange="this.form.submit()">
					<option value="0"><?php echo t('All Categories') ?></option>
					<?php
					foreach($forumCategories as $cat){
						unset($selected);
						if($sortCategory == $cat->getCollectionID()) $selected = 'selected="selected"';
						echo '<option value="' . $cat->getCollectionID() . '" ' . $selected . '>' . $cat->getCollectionName() . '</option>';
						$selected = null;
						}	
					?>	
				</select>
			</form>
		</div>
	<?php
	}
	if($pinnedPages) { ?>
		<table id="pinnedPosts" class="table table-striped">
			<thead>
				<tr>
					<th><?php echo t('Date') ?></th>
					<th><?php echo t('Title') ?></th>
					<th><?php echo t('Category') ?></th>
					<th><?php echo t('Author') ?></th>
					<th></th>
					<th></th>
					<th></th>					
				</tr>
			</thead>
			
			<tbody>	
				<?php
				foreach($pinnedPages as $pin){
					if($pin->getCollectionAttributeValue('forum_name')) {
						$author = $pin->getCollectionAttributeValue('forum_name');
					 } else { 
						$author = UserInfo::getByID($pin->getCollectionUserID())->getUserName();
					 } ?>
						<tr>
							<td>
								<?php echo $pin->getCollectionDatePublic() ?>
							</td>
							
							<td>
								<a href="<?php echo $pin->getCollectionPath() ?>"><?php echo $pin->getCollectionName() ?></a>
							</td>
							
							<td>
								<a href="<?php echo Page::getByID($pin->getCollectionParentID())->getCollectionPath() ?>"><?php echo Page::getByID($pin->getCollectionParentID())->getCollectionName() ?>
							</td>
							
							<td>
								<?php echo $author;
								if($pin->getCollectionAttributeValue('forum_email') ) echo '<br/>' . $pin->getCollectionAttributeValue("forum_email") ?>
							</td>
							
							<td width=75>
								<form method="POST" action="<?php echo $this->action('delete_page') ?>">
									<input type="hidden" name="cID" value="<?php echo $pin->getCollectionID() ?>"/>
									<input type="hidden" name="activeTab" value="pinned">
									<?php echo Loader::helper('validation/token')->output('delete_page') ?>
									<button onClick="return confirmDelete()" type="submit" class="forumDeleteButton btn btn-default"><?php echo t('Delete') ?></button>
								</form>
							</td>
							
							<td width=95>
								<form method="POST" action="<?php echo $this->action('un_pin_page') ?>">
									<input type="hidden" name="cID" value="<?php echo $pin->getCollectionID() ?>"/>
									<input type="hidden" name="activeTab" value="pinned">
									<?php echo Loader::helper('validation/token')->output('un_pin_page') ?>
									<button type="submit" class="forumBanButton btn btn-default"><?php echo t('UnPin') ?></button>
								</form>
							</td>
							
							<td style="padding: 14px 0 8px 0; width:32px;">
								<a href="/index.php?cID=<?php echo $pin->getCollectionID()?>&ctask=check-out-first"><i class="fa fa-edit" style="font-size:25px;"></i></a>		
							</td>
						</tr>
						<tr>
							<td colspan="7">
								<div class="forumPostPreview" >
									<?php echo $pin->getCollectionAttributeValue('forum_post')?>
								</div>
							</td>
						</tr>
				<?php
				}
				?>
		</tbody>
	</table>
	<?php
	if ($pinnedPagination):
		echo $pinnedPagination;
	endif;
	
	} else { ?>
		<div class="forumDashboardMessage"><?php echo t('There are no Pinned Posts') ?></div>
	<?php
	} ?>
</div>


<div class="ccm-tab-content" id="ccm-tab-content-replies">
	<?php if(count($forumCategories) > 1 ){ ?>
		<div class="forumCategory" style="margin-bottom:15px;">
			<form method="post" id="sort_category" action="<?php echo $this->action('set_sort_category')?>">
				<input type="hidden" name="activeTab" value="replies">
				<label style="margin-right: 4px;" for="category"><?php echo t('Filter by Category') ?></label>
				<select name="sortCategory" onchange="this.form.submit()">
					<option value="0"><?php echo t('All Categories') ?></option>
					<?php
					foreach($forumCategories as $cat){
						unset($selected);
						if($sortCategory == $cat->getCollectionID()) $selected = 'selected="selected"';
						echo '<option value="' . $cat->getCollectionID() . '" ' . $selected . '>' . $cat->getCollectionName() . '</option>';
						$selected = null;
						}	
					?>	
				</select>
			</form>
		</div>
	<?php
	}
	if($replies) { ?>

		<table id="replies" class="table table-striped">
			<thead>
				<tr>
					<th><?php echo t('Date') ?></th>
					<th><?php echo t('Reply To') ?></th>
					<th><?php echo t('Category') ?></th>
					<th><?php echo t('Author') ?></th>
					<th></th>
					<th></th>
					<th></th>
									
				</tr>
			</thead>
			
			<tbody>

				<?php
				foreach($replies as $reply){
					$cnv = $reply->getConversationObject();
					$replyPage = Page::getByID($cnv->cID);
					$author = $reply->getConversationMessageAuthorObject();
					$formatter = $author->getFormatter();
					$category = Page::getByID($replyPage->getCollectionParentID());
					$replyDate = $reply->getConversationMessageDateTime();
					?>
					<tr>
						<td>
							<?php echo $replyDate ?>
						</td>
					
						<td>
							<a href="<?php echo $replyPage->getCollectionPath() ?>"><?php echo $replyPage->getCollectionName(); ?></a>
						</td>
						
						<td>
							<a href="<?php echo $category->getCollectionPath() ?>"><?php echo $category->getCollectionName(); ?></a>
						</td>
						
						<td>
							<?php echo $formatter->getLinkedAdministrativeDisplayName(); ?>
						</td>
						
						<td width=75>
							<form method="POST" action="<?php echo $this->action('delete_reply') ?>">
								<input type="hidden" name="mID" value="<?php echo $reply->getConversationMessageID() ?>"/>
								<input type="hidden" name="activeTab" value="replies">
								<?php echo Loader::helper('validation/token')->output('delete_reply') ?>
								<button onClick="return confirmReplyDelete()" type="submit" class="forumDeleteButton btn btn-default"><?php echo t('Delete') ?></button>
							</form>
						</td>
						
						<td width=95>
							<form method="POST" action="<?php echo $this->action('unapprove_reply') ?>">
								<input type="hidden" name="mID" value="<?php echo $reply->getConversationMessageID() ?>"/>
								<input type="hidden" name="activeTab" value="replies">
								<?php echo Loader::helper('validation/token')->output('unapprove_reply') ?>
								<button style="float: right; margin-right: 5px;" type="submit" class="btn btn-default"><?php echo t('unApprove') ?></button>
							</form>
						</td>
						
						<td style="padding: 14px 0 8px 0; width:32px;">
							<a href="#" id="editReply<?php echo $reply->getConversationMessageID()?>"><i class="fa fa-edit" style="font-size:25px;"></i></a>
							<script>
								$('#editReply<?php echo $reply->getConversationMessageID()?>').click(function() {
									$('.toggle<?php echo $reply->getConversationMessageID()?>').toggle();
									return false;
								});
							</script>								
						</td>
					</tr>
					
					<tr>
						<td colspan="7">
							<div class="forumPostPreview toggle<?php echo $reply->getConversationMessageID()?>" >
								<?php echo $reply->getConversationMessageBodyOutput(true) ?>
							</div>
							
							<div class="forumPostEdit toggle<?php echo $reply->getConversationMessageID()?>">
								<?php echo t('Reply To: ') ?> <?php echo $replyPage->getCollectionName(); ?> on <?php echo $replyDate ?>
								<br/>
								<br/>
								<form method="POST" action="<?php echo $this->action('edit_reply') ?>">
									<?php Loader::helper('validation/token')->output('edit_reply') ?>
									<input type="hidden" name="mID" value="<?php echo $reply->getConversationMessageID() ?>"/>
									<input type="hidden" name="activeTab" value="replies">
									<textarea name="replyBody" style="height: 200px" required><?php echo $reply->getConversationMessageBodyOutput(true) ?></textarea>
									<br/>
									<br/>
									<button style="float: left" type="submit" <button class="btn btn-default"><?php echo t('Save') ?></button>
								</form>
								<button style="float: left; margin-left:5px;" onclick="$.magnificPopup.close();"class="btn btn-default">Cancel</button>
									 
								<form method="POST" action="<?php echo $this->action('delete_reply') ?>">
									<input type="hidden" name="mID" value="<?php echo $reply->getConversationMessageID() ?>"/>
									<input type="hidden" name="activeTab" value="replies">
									<?php echo Loader::helper('validation/token')->output('delete_reply') ?>
									<button style="float: right" onClick="return confirmReplyDelete()" class="btn btn-default"><?php echo t('Delete') ?></button>
								</form>
									
								<form method="POST" action="<?php echo $this->action('unapprove_reply') ?>">
									<input type="hidden" name="mID" value="<?php echo $reply->getConversationMessageID() ?>"/>
									<input type="hidden" name="activeTab" value="replies">
									<?php echo Loader::helper('validation/token')->output('unapprove_reply') ?>
									<button style="float: right; margin-right: 5px;" type="submit" class="btn btn-default"><?php echo t('unApprove') ?></button>
								</form>

								<div style="clear:both"></div>
							</div>
						</td>
					</tr>

						
				<?php
				} ?>
			</tbody>
		</table>
	<?php
	} else { ?>
		<div class="forumDashboardMessage"><?php echo t('There are no Replies') ?></div>
	<?php
	} ?>
	
</div>



<div class="ccm-tab-content" id="ccm-tab-content-unApprovedReplies">
	<?php if(count($forumCategories) > 1 ){ ?>
		<div class="forumCategory" style="margin-bottom:15px;">
			<form method="post" id="sort_category" action="<?php echo $this->action('set_sort_category')?>">
				<input type="hidden" name="activeTab" value="unApprovedReplies">
				<label style="margin-right: 4px;" for="category"><?php echo t('Filter by Category') ?></label>
				<select name="sortCategory" onchange="this.form.submit()">
					<option value="0"><?php echo t('All Categories') ?></option>
					<?php
					foreach($forumCategories as $cat){
						unset($selected);
						if($sortCategory == $cat->getCollectionID()) $selected = 'selected="selected"';
						echo '<option value="' . $cat->getCollectionID() . '" ' . $selected . '>' . $cat->getCollectionName() . '</option>';
						$selected = null;
						}	
					?>	
				</select>
			</form>
		</div>
	<?php
	}
	if($unApprovedReplies) { ?>

		<table id="unApprovedReplies" class="table table-striped">
			<thead>
				<tr>
					<th><?php echo t('Date') ?></th>
					<th><?php echo t('Reply To') ?></th>
					<th><?php echo t('Category') ?></th>
					<th><?php echo t('Author') ?></th>
					<th></th>
					<th></th>
					<th></th>
									
				</tr>
			</thead>
			
			<tbody>

				<?php
				foreach($unApprovedReplies as $reply){
					$cnv = $reply->getConversationObject();
					$replyPage = Page::getByID($cnv->cID);
					$author = $reply->getConversationMessageAuthorObject();
					$formatter = $author->getFormatter();
					$category = Page::getByID($replyPage->getCollectionParentID());
					$replyDate = $reply->getConversationMessageDateTime();
					?>
					<tr>
						<td>
							<?php echo $replyDate ?>
						</td>
					
						<td>
							<a href="<?php echo $replyPage->getCollectionPath() ?>"><?php echo $replyPage->getCollectionName(); ?></a>
						</td>
						
						<td>
							<a href="<?php echo $category->getCollectionPath() ?>"><?php echo $category->getCollectionName(); ?></a>
						</td>
						
						<td>
							<?php echo $formatter->getLinkedAdministrativeDisplayName(); ?>
						</td>
						
						<td width=75>
							<form method="POST" action="<?php echo $this->action('delete_reply') ?>">
								<input type="hidden" name="mID" value="<?php echo $reply->getConversationMessageID() ?>"/>
								<input type="hidden" name="activeTab" value="unApprovedReplies">
								<?php echo Loader::helper('validation/token')->output('delete_reply') ?>
								<button onClick="return confirmReplyDelete()" type="submit" class="forumDeleteButton btn btn-default"><?php echo t('Delete') ?></button>
							</form>
						</td>
						
						<td width=95>
							<form method="POST" action="<?php echo $this->action('approve_reply') ?>">
								<input type="hidden" name="mID" value="<?php echo $reply->getConversationMessageID() ?>"/>
								<input type="hidden" name="activeTab" value="unApprovedReplies">
								<?php echo Loader::helper('validation/token')->output('approve_reply') ?>
								<button style="float: right; margin-right: 5px;" type="submit" class="btn btn-default"><?php echo t('Approve') ?></button>
							</form>
						</td>
						
						<td style="padding: 14px 0 8px 0; width:32px;">
							<a href="#" id="editUnapprovedReply<?php echo $reply->getConversationMessageID()?>"><i class="fa fa-edit" style="font-size:25px;"></i></a>
							<script>
								$('#editUnapprovedReply<?php echo $reply->getConversationMessageID()?>').click(function() {
									$('.toggle<?php echo $reply->getConversationMessageID()?>').toggle();
									return false;
								});
							</script>								
						</td>
					</tr>
					
					<tr class>
						<td colspan="7">
							<div class="forumPostPreview toggle<?php echo $reply->getConversationMessageID()?>" >
								<?php echo $reply->getConversationMessageBodyOutput(true) ?>
							</div>
							
							<div class="forumPostEdit toggle<?php echo $reply->getConversationMessageID()?>">
								Reply To: <?php echo $replyPage->getCollectionName(); ?> on <?php echo $replyDate ?>
								<br/>
								<br/>
								<form method="POST" action="<?php echo $this->action('edit_reply') ?>">
									<?php Loader::helper('validation/token')->output('edit_reply') ?>
									<input type="hidden" name="mID" value="<?php echo $reply->getConversationMessageID() ?>"/>
									<input type="hidden" name="activeTab" value="unApprovedReplies">
									<textarea name="replyBody" style="height: 200px" required><?php echo $reply->getConversationMessageBodyOutput(true) ?></textarea>
									<br/>
									<br/>
									<button style="float: left" type="submit" <button class="btn btn-default"><?php echo t('Save') ?></button>
								</form>
								<button style="float: left; margin-left:5px;" onclick="$.magnificPopup.close();"class="btn btn-default">Cancel</button>
									 
								<form method="POST" action="<?php echo $this->action('delete_reply') ?>">
									<input type="hidden" name="mID" value="<?php echo $reply->getConversationMessageID() ?>"/>
									<input type="hidden" name="activeTab" value="unApprovedReplies">
									<?php echo Loader::helper('validation/token')->output('delete_reply') ?>
									<button style="float: right" onClick="return confirmReplyDelete()" class="btn btn-default"><?php echo t('Delete') ?></button>
								</form>
									
								<form method="POST" action="<?php echo $this->action('approve_reply') ?>">
									<input type="hidden" name="mID" value="<?php echo $reply->getConversationMessageID() ?>"/>
									<input type="hidden" name="activeTab" value="unApprovedReplies">
									<?php echo Loader::helper('validation/token')->output('approve_reply') ?>
									<button style="float: right; margin-right: 5px;" type="submit" class="btn btn-default"><?php echo t('Approve') ?></button>
								</form>

								<div style="clear:both"></div>
							</div>
						</td>
					</tr>

						
				<?php
				} ?>
			</tbody>
		</table>
	<?php
	} else { ?>
		<div class="forumDashboardMessage"><?php echo t('There are no unApproved Replies') ?></div>
	<?php
	} ?>
	
</div>



<div class="ccm-tab-content" id="ccm-tab-content-help">
	<?php
	echo t('<center><h3>Forums Help</h3></center>
		   <h4>Single Category Forums</h4>
		   <p>
		   In a single forum you will have one page that is the Forum Category Page, it will have the "forum_category" Page Attribute checkbox set to true (checked), this will be the
		   Parent Page or Category Page for all your Forum Posts.  Forum posts beneath this page use the "forum_post" Page Type.
		   </p>
		   
		   <p>
		   Forum Page (any page type with forum_category attribute checked and Forum List Block)
			 <br/>&nbsp;&nbsp;&nbsp; |- Forum Post One (child pages using the forum_post page type)
			 <br/>&nbsp;&nbsp;&nbsp; |- Forum Post Two
			 <br/>&nbsp;&nbsp;&nbsp; |- ect...
		    </p>
		   
		   <p>
		   Category Pages use a Forums List Block that will display a Page List of all the Forum Posts with links to each post.
		   </p>
		   
		   <p>
		   All Forum Pages have a Forums Post Block, this block adds the "Add a New Post" button to the page that allows visitors to post from the front end of the website. On Forum Post Pages
		   the Forum Post Block adds buttons for Admins or Forum Moderators to Edit. Delete and UnApprove or Approve Pages. The Forums Post Block can be placed on any page on your website
		   where you want to give visitors a link to add forum posts.
		   </p>
			
		   <p>
		   All Forum Post Pages have an embeded Autonav block using the Forums Breadcrumb Template and a Forum Post block.  The rest of the data on the page is generated from Forum
		   Page Attributes on the forum_post.php Page Template.
		   
		   <h4>Multiple Category Forums</h4>
		   <p>
		   Multiple Category Forums are created with more than one page with the forums_category page attribute set.

		   <p>
		   Top Level Forum Page (any page type with a Forum List Bock)
		    <br/>&nbsp;&nbsp;&nbsp;|- Forum Category Page (any page type with forum_category attribute checked and Forum List Block)
			<br/>&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;|- Forum Post One (child pages using the forum_post page type)
			<br/>&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;|- Forum Post Two
			<br/>&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;|- ect...
			<br/>&nbsp;&nbsp;&nbsp;|- Second Forum Category Page (any page type with forum_category attribute checked and Forum List Block)
			<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;|- Forum Post One (child pages using the forum_post page type)
			<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;|- Forum Post Two
			<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;|- ect...	
		  </p>
			
		  <p>
		  <br/>
		  <h4>Settings</h4>
		  On the Settings page you can select the defaults for each Forum Category.  The top row of checkboxes allows you to select the options you
		  want displayed on your Forum pages.
		  <br/>
		  <br/>
		  <label>Page Types / Page Template</label>
		  <br/>
		  For advanced users, you can select a different Page Type or Page Template than the default /"Forum Post/".
		  </br/>
		  To make a new Page Template copy packages/webli_forums/page_templates/forum_post.php to your theme or page_templates directory
		  and make any changes you want to the file.
		  <br/>
		  <br/>
		  To make a new Page Type, Create the page Type in your Concrete5 dashboard and make a new Page Type Controller by copying and renaming
		  packages/webli_forums/controllers/page_types/forum_post.php and change the class at the beginning of the file.  The controller is necessary
		  for the options in Forum Settings to work properly.
		  <br/>
		  <br/>
		  <label>Optional Attributes</label>
		  <br/>
		  You can add additional attributes to the Forums Attribute Set in the Concrete5 dashboard and they will become available for your forum.  Check the checkbox
		  for attributes you want to use for a forum Category.  Using Optional Attributes may require some styling or modifications to the forum page template.
		  <br/>
		  <br/>
		  <label>Add This / Share This</label>
		  Visit <a target="blank" href="http://www.addthis.com">www.addthis.com</a> for the code to enable social icons.
		  <br/>
		  <br/>
		  <label>Pinned Posts</label>
		  <br/>
		  This function is not available yet. Coming soon.
		  </p>
		  
		  <p>
		  To be continued..
		  </p>
		   
		   
		'); ?>
</div>

