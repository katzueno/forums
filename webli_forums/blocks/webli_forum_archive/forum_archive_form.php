<?php defined('C5_EXECUTE') or die("Access Denied.");
$c = Page::getCurrentPage();
$pageselect = Loader::helper('form/page_selector');
?>

<div class="row pagelist-form">
    <div class="col-xs-12">

        <input type="hidden" name="pageListToolsDir" value="<?php echo Loader::helper('concrete/urls')->getBlockTypeToolsURL($bt) ?>/"/>

        <fieldset>
        <legend><?php echo t('Settings') ?></legend>
        
        <div class="form-group">
            <label class="control-label"><?php echo t('Page Type') ?></label>
            <?php
            $ctArray = PageType::getList();

            if (is_array($ctArray)) {
                ?>
                <select class="form-control" name="ptID" id="selectPTID">
                    <option value="0">** <?php echo t('All') ?> **</option>
                    <?php
                    foreach ($ctArray as $ct) {
                        ?>
                        <option
                            value="<?php echo $ct->getPageTypeID() ?>" <?php if ($ptID == $ct->getPageTypeID()) { ?> selected <?php } ?>>
                            <?php echo $ct->getPageTypeDisplayName() ?>
                        </option>
                    <?php
                    }
                    ?>
                </select>
            <?php
            }
            ?>
        </div>
	</fieldset>
		
		<fieldset>
        <legend><?php echo t('Location') ?></legend>
        <div class="radio">
            <label>
                <input type="radio" name="cParentID" id="cEverywhereField"
                       value="0" <?php if ($cParentID == 0) { ?> checked<?php } ?> />
                <?php echo t('Everywhere') ?>
            </label>
        </div>
        <div class="radio">
            <label>
                <input type="radio" name="cParentID" id="cThisPageField"
                       value="<?php echo $c->getCollectionID() ?>" <?php if ($cParentID == $c->getCollectionID() || $cThis) { ?> checked<?php } ?>>
                <?php echo t('Beneath this page') ?>
            </label>
         </div>
        <div class="radio">
            <label>
                <input type="radio" name="cParentID" id="cOtherField"
                       value="OTHER" <?php if ($isOtherPage) { ?> checked<?php } ?>>
                <?php echo t('Beneath another page') ?>
            </label>
        </div>

        <div class="radio">
            <label>
                <input type="radio" name="cParentID" id="parent"
                       value="PARENT" <?php if ($parent) { ?> checked<?php } ?>>
                <?php echo t('Beneath parent page') ?>
            </label>
        </div>
		
        <div class="ccm-page-list-page-other" <?php if (!$isOtherPage) { ?> style="display: none" <?php } ?>>

            <div class="form-group">
                <?php echo $pageselect->selectPage('cParentIDValue', $isOtherPage ? $cParentID : false); ?>
            </div>
        </div>

	</fieldset>

	<fieldset>
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
	</fieldset>		 
		
	<fieldset>
        <div class="form-group">
            <label class="control-label"><?php echo t('Block Class') ?></label>
			<span class="help-block"><?php echo
                    t('(<strong>Note</strong>: Wraps the Forum List block in a div with this class name.)'); ?></span>
            <input type="text" maxwidth="255" class="form-control" name="class" value="<?php echo $class?>" />
        </div>
		
		<div class="form-group">
            <label class="control-label"><?php echo t('Title of Archive') ?></label>
            <input type="text" maxwidth="255" class="form-control" name="title" value="<?php echo $title?>" />
        </div>
	</fieldset>

        <div class="loader">
            <i class="fa fa-cog fa-spin"></i>
        </div>
    </div>

</div>

<style type="text/css">
    div.pagelist-form div.loader {
        position: absolute;
        line-height: 34px;
    }

    div.pagelist-form div.cover {
        width: 100%;
        height: 100%;
        position: absolute;
        top: 0;
        left: 0;
    }

    div.pagelist-form div.render .ccm-page-list-title {
        font-size: 12px;
        font-weight: normal;
    }

    div.pagelist-form label.checkbox,
    div.pagelist-form label.radio {
        font-weight: 300;
    }

</style>
<script type="application/javascript">
    Concrete.event.publish('pagelist.edit.open');
    $(function() {
        $('input[name=filterByRelated]').on('change', function() {
            if ($(this).is(':checked')) {
                $('div[data-row=related-topic]').show();
            } else {
                $('div[data-row=related-topic]').hide();
            }
        }).trigger('change');
    });

</script>

