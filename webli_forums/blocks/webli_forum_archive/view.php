<?php
defined('C5_EXECUTE') or die("Access Denied.");
$rssUrl = $showRss ? $controller->getRssUrl($b) : '';
$th = Loader::helper('text');
$dh = Core::make('helper/date');
$c = Page::getCurrentPage();
$parentPage = Page::getByID($c->getCollectionParentID());
//$ih = Loader::helper('image'); //<--uncomment this line if displaying image attributes (see below)
//Note that $nh (navigation helper) is already loaded for us by the controller (for legacy reasons)

if( !Page::getCurrentPage()->getCollectionAttributeValue('forum_category') && !Page::getByID(Page::getCurrentPage()->getCollectionParentID())->getCollectionAttributeValue('forum_category')
    || Page::getCurrentPage()->getCollectionAttributeValue('forum_category') && $display['forum_archive_block']
    || Page::getByID( Page::getCurrentPage()->getCollectionParentID() )->getCollectionAttributeValue('forum_category') && $display['forum_archive_block'] ): ?>

<div class="forumArchiveWrapper <?php echo $class ?>">
	<div class="forumArchive">
		<div id="forumArchiveAccordion<?php echo $bID ?>">
			<h3><?php echo $title ?></h3>
			
			<ul>
		
			<?php
		
			$currentMonth = null;
			$currentYear = null;
			
			foreach ($pages as $page):
				
				$title = $th->entities($page->getCollectionName());
				$url = $nh->getLinkToCollection($page);
				$target = ($page->getCollectionPointerExternalLink() != '' && $page->openCollectionPointerExternalLinkInNewWindow()) ? '_blank' : $page->getAttribute('nav_target');
				$target = empty($target) ? '_self' : $target;
				
				
						
				$year =  $dh->getSystemDateTime($page->getCollectionDatePublic(), $mask = 'Y');
				$month = $dh->getSystemDateTime($page->getCollectionDatePublic(), $mask = 'F');
				
		
				if($cParentID != $c->getCollectionID() && $year == $dh->getSystemDateTime($c->getCollectionDatePublic(), $mask = 'Y')){
					$yActive = 'class="active"';
				}
				
				if($parentPage->getCollectionAttributeValue('forum_category') && $month == $dh->getSystemDateTime($c->getCollectionDatePublic(), $mask = 'F')){
					$mActive = 'class="active"';
				} elseif(!$parentPage->getCollectionAttributeValue('forum_category') && $month == $dh->getSystemDateTime('now', $mask = 'F')){
					$mActive = 'class="active"';
				}
				
				if($c->getCollectionID() == $page->getCollectionID()){
					$uActive = 'class="active"';
				}
								
				
				if($currentYear == null && $currentMonth == null){
					if ($cParentID == $c->getCollectionID()){
						echo '<li class="active"><a class="forumArchiveDropdown" href="#">'. $year . '&nbsp;<i class="fa fa-caret-right"></i></a><ul>';
					} else {
						echo '<li ' . $yActive . '><a class="forumArchiveDropdown" href="#">'. $year . '&nbsp;<i class="fa fa-caret-right"></i></a><ul>';
					}
					echo '<li ' . $mActive . '><a class="forumArchiveDropdown" href="#">' . $month . '&nbsp;<i class="fa fa-caret-right"></i></a><ul>';	
				}
		
				if($year == $currentYear && $month != $currentMonth){
					echo '</ul><li ' . $mActive . '><a class="forumArchiveDropdown" href="#">' . $month . '&nbsp;<i class="fa fa-caret-right"></i></a><ul>';
				}
				
				if($year != $currentYear && $currentYear != null){
					echo '</ul></ul></li><li ' . $yActive . '><a class="forumArchiveDropdown" href="#">' . $year . '&nbsp;<i class="fa fa-caret-right"></i></a><ul>';
					echo '<li ' . $mActive . '><a class="forumArchiveDropdown" href="#">' . $month . '&nbsp;<i class="fa fa-caret-right"></i></a><ul>';
				}
							
				echo '<li class="url"><a ' . $uActive . ' href="' . $url . '" target="' . $target . '">' . $title . '</a></li>';
				
				$currentYear = $year;
				$currentMonth = $month;
				unset($yActive);
				unset($mActive);
				unset($uActive);
			endforeach; ?>
		 
			</ul></ul></li></ul>
		</div><!-- end .ccm-page-list -->
	</div>
</div>
<?php
elseif(Page::getCurrentPage()->isEditMode()):?>
    <div class="forumSearchDisabled" style="color:red; background-color:#ccc; text-align:center; padding: 10px; margin: 5px 0">
        <?php echo t('The Forum Archive Block is disabled in Forum Dashboard Settings.');?>
    </div>
<?php

endif; ?>    
<script type="text/javascript">
/*jQuery time*/
$(document).ready(function(){
	$('.forumArchiveDropdown').click(function(e) {
    e.preventDefault();
    //do other stuff when a click happens
});
	
	$("#forumArchiveAccordion<?php echo $bID ?> a").click(function(){
		var link = $(this);
		var closest_ul = link.closest("ul");
		var parallel_active_links = closest_ul.find(".active")
		var closest_li = link.closest("li");
		var link_status = closest_li.hasClass("active");
		var count = 0;

		closest_ul.find("ul").slideUp(function(){
			if(++count == closest_ul.find("ul").length)
				parallel_active_links.removeClass("active");
		});

		if(!link_status)
		{
			closest_li.children("ul").slideDown('fast');
			closest_li.addClass("active");
		}
	})
})		
</script>
