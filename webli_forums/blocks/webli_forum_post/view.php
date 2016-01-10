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

<?php
$forumview = $this;
Loader::element('webli_forums/forum_post',
    array(
        'fp' => $fp,
        'tp' => $tp,
        'forumview' => $forumview,
        'forumPages' => $forumPages,
        'publicForums' => $publicForums,
        'u' => $u
    ), 'webli_forums');
?>

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