<?php
defined('C5_EXECUTE') or die("Access Denied.");
$form = Loader::helper('form');
$searchWithinOther=($searchObj->baseSearchPath!=Page::getCurrentPage()->getCollectionPath() && $searchObj->baseSearchPath!='' && strlen($searchObj->baseSearchPath)>0)?true:false;
if($baseSearchPath == 'PARENT') $searchWithinOther = false;
/**
 * Post to another page, get page object.
 */
$basePostPage = Null;
if (isset($searchObj->postTo_cID) && intval($searchObj->postTo_cID) > 0) {
    $basePostPage = Page::getById($searchObj->postTo_cID);
} else if ($searchObj->pagePath != Page::getCurrentPage()->getCollectionPath() && strlen($searchObj->pagePath)) {
    $basePostPage = Page::getByPath($searchObj->pagePath);
}
/**
 * Verify object.
 */
if (is_object($basePostPage) && $basePostPage->isError()) {
    $basePostPage = NULL;
}
?>

<?php if (!$controller->indexExists()) { ?>
    <div class="ccm-error"><?php echo t('The search index does not appear to exist. This block will not function until the reindex job has been run at least once in the dashboard.')?></div>
<?php } ?>

<fieldset>
	<legend><?php echo t('Settings') ?></legend>

    <div class='form-group'>
        <label for='title' style="margin-bottom: 0px;"><?php echo t('Search for Pages')?>:</label>
        <div class="radio">
            <label for="baseSearchPathEverywhere">
                <input type="radio" name="baseSearchPath" id="baseSearchPathEverywhere" value="" <?php echo ($searchObj->baseSearchPath=='' || !$searchObj->baseSearchPath)?'checked':''?> onchange="searchBlock.pathSelector(this)" />
                <?php echo t('Everywhere')?>
            </label>
        </div>
		<div class="radio">
            <label for="baseSearchPathParent">
                <input type="radio" name="baseSearchPath" id="baseSearchPathParent" value="PARENT" onchange="searchBlock.pathSelector(this)" <?php echo ($baseSearchPath)?'checked':''?>>
                <?php echo t('Beneath Parent')?>
            </label>
        </div>
		<div class="radio">
            <label for="baseSearchPathThis">
                <input type="radio" name="baseSearchPath" id="baseSearchPathThis" value="<?php echo Page::getCurrentPage()->getCollectionPath()?>" <?php echo ( $searchObj->baseSearchPath != '' && $searchObj->baseSearchPath==Page::getCurrentPage()->getCollectionPath() )?'checked':''?> onchange="searchBlock.pathSelector(this)" >
                <?php echo t('Beneath this Page')?>
            </label>
        </div>
        <div class="radio">
            <label for="baseSearchPathOther">
                <input type="radio" name="baseSearchPath" id="baseSearchPathOther" value="OTHER" onchange="searchBlock.pathSelector(this)" <?php echo ($searchWithinOther)?'checked':''?>>
                <?php echo t('Beneath Another Page')?>
                <div id="basePathSelector" style="display:<?php echo ($searchWithinOther)?'block':'none'?>" >

                    <?php $select_page = Loader::helper('form/page_selector');
                    if ($searchWithinOther) {
                        $cpo = Page::getByPath($baseSearchPath);
                        if (is_object($cpo)) {
                            print $select_page->selectPage('searchUnderCID', $cpo->getCollectionID());
                        } else {
                            print $select_page->selectPage('searchUnderCID');
                        }
                    } else {
                        print $select_page->selectPage('searchUnderCID');
                    }
                    ?>
                </div>
            </label>
        </div>
    </div>
	
	     <div class='form-group'>
        <label for='categories' style="margin-bottom: 0px;"><?php echo t('Forum Categories')?>:</label>
        <br/>
		<?php echo t('optional - limit to selected categories') ?>
		<br/>
        <div style="padding: 10px 0; width: 100%">
        <?php

		$selected = unserialize($forum_categories);

		foreach($controller->get_forum_categories() as $cats): 
            unset($checked);
			if($selected){
			  if(in_array($cats->getCollectionID(), $selected)){
				$checked = 'checked="checked"';
			  }
			}
			?>
			<div style="display:inline-block; width: 48%">
                <input class="categories" type="checkbox" name="forum_categories[]" value="<?php echo $cats->getCollectionID() ?>"  <?php echo $checked ?>> <?php echo $cats->getCollectionName() ?>
            </div>
        <?php
        endforeach;
        ?>
        </div>
     </div>
		 
		 
    <div class='form-group'>
        <label for='title' style="margin-bottom: 0px;"><?php echo t('Results Page')?>:</label>
        <div class="checkbox">
            <label for="ccm-searchBlock-externalTarget">
                <input id="ccm-searchBlock-externalTarget" name="externalTarget" type="checkbox" value="1" <?php echo (strlen($searchObj->resultsURL) || $basePostPage !== NULL)?'checked':''?> />
                <?php echo t('Post Results to a Different Page')?>
            </label>
        </div>
        <div id="ccm-searchBlock-resultsURL-wrap" class="input" style=" <?php echo (strlen($searchObj->resultsURL) || $basePostPage !== NULL)?'':'display:none'?>" >
            <?php
            if ($basePostPage !== NULL) {
                print $select_page->selectPage('postTo_cID', $basePostPage->getCollectionID());
            } else {
                print $select_page->selectPage('postTo_cID');
            }
            ?>
            <?php echo t('OR Path')?>:
            <?php echo $form->text('resultsURL',$searchObj->resultsURL, array('maxlength' => 255));?>
        </div>
    </div>

</fieldset>

<fieldset>
	<div class='form-group'>
        <label for='buttonText'><?php echo t('Button Text')?>:</label>
        <?php echo $form->text('buttonText',$searchObj->buttonText, array('maxlength' => 128));?>
    </div>

	<div class="form-group">
		<label class="control-label"><?php echo t('Block Class') ?></label>
		<span class="help-block"><?php echo
				t('(<strong>Note</strong>: Wraps the Forum Search block in a div with this class name.)'); ?></span>
		<input type="text" maxlength="255" class="form-control" name="class" value="<?php echo $class?>" />
	</div>
		
    <div class='form-group'>
        <label for='title'><?php echo t('Title')?>:</label>
        <?php echo $form->text('title',$searchObj->title, array('maxlength' => 255));?>
    </div>




</fieldset>

