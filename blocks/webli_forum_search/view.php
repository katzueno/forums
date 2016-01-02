<?php defined('C5_EXECUTE') or die('Access Denied.');

if (isset($error)) {
    ?><?php echo $error?><br/><br/><?php
}

if (!isset($query) || !is_string($query)) {
    $query = '';
}

if( !Page::getCurrentPage()->getCollectionAttributeValue('forum_category') && !Page::getByID(Page::getCurrentPage()->getCollectionParentID())->getCollectionAttributeValue('forum_category')
    || Page::getCurrentPage()->getCollectionAttributeValue('forum_category') && $display['forum_search_block']
    || Page::getByID( Page::getCurrentPage()->getCollectionParentID() )->getCollectionAttributeValue('forum_category') && $display['forum_search_block'] ):
?>

    <div class="forumSearchWrapper <?php echo $class ?>">
        <form action="<?php echo $view->url($resultTargetURL)?>" method="get" class="forumSearchForm"><?php
            if (isset($title) && ($title !== '')) {
                ?><h3><?php echo h($title)?></h3><?php
            }
            if ($query === '') {
                if($baseSearchPath == 'PARENT'){
                    $parentPage =  Page::getByID(Page::getCurrentPage()->getCollectionParentID());
                    $baseSearchPath = $parentPage->getCollectionPath();
                } ?>
                <input name="search_paths[]" type="hidden" value="<?php echo htmlentities($baseSearchPath, ENT_COMPAT, APP_CHARSET) ?>" /><?php
            } elseif (isset($_REQUEST['search_paths']) && is_array($_REQUEST['search_paths'])) {
                foreach ($_REQUEST['search_paths'] as $search_path) {
                    ?><input name="search_paths[]" type="hidden" value="<?php echo htmlentities($search_path, ENT_COMPAT, APP_CHARSET) ?>" /><?php
                }
            }
                
            if(is_array($selectedCategories)){
                foreach($selectedCategories as $cat){ ?>
                   <input name="cat[]" type="hidden" value="<?php echo $cat?>" />
                <?php
                }
            }
            ?>
                
            <input name="query" type="text" value="<?php echo htmlentities($query, ENT_COMPAT, APP_CHARSET)?>" class="ccm-search-block-text" />
            
            <?php
            if (isset($buttonText) && ($buttonText !== '')) {
                ?> <input name="submit" type="submit" value="<?php echo h($buttonText)?>" class="btn btn-default ccm-search-block-submit" /><?php
            }
        
            if (isset($do_search) && $do_search) {
                if (count($results) == 0) {
                    ?><h4 style="margin-top:32px"><?php echo t('There were no results found. Please try another keyword or phrase.')?></h4><?php
                } else {
                    $tt = Core::make('helper/text');
                    ?><div id="searchResults">
                    <?php
                    if($_REQUEST['tag']) echo '<br/>Searched Tag: ' . $_REQUEST['tag'];
                        foreach ($results as $r) {
                            $currentPageBody = $this->controller->highlightedExtendedMarkup($r->getCollectionAttributeValue('forum_post'), $query);
                            ?><div class="searchResult">
                                <h3><a href="<?php echo $r->getCollectionLink()?>"><?php echo $r->getCollectionName()?></a></h3>
                                <p><?php
                                    if ($r->getCollectionDescription()) {
                                        echo $this->controller->highlightedMarkup($tt->shortText($r->getCollectionDescription()), $query);
                                        ?><br/><?php
        
                                    }
                                    echo $currentPageBody;
                                    ?> <a href="<?php echo $r->getCollectionLink()?>" class="pageLink"><?php echo $this->controller->highlightedMarkup($r->getCollectionLink(), $query)?></a>
                                </p>
                            </div><?php
                        }
                    ?></div><?php
                    $pages = $pagination->getCurrentPageResults();
                    if ($pagination->getTotalPages() > 1 && $pagination->haveToPaginate()) {
                        $showPagination = true;
                        echo $pagination->renderDefaultView();
                    }
                }
            }
        ?>
        </form>
    </div>
<?php
elseif(Page::getCurrentPage()->isEditMode()):?>
    <div class="forumSearchDisabled" style="color:red; background-color:#ccc; text-align:center; padding: 10px; margin: 5px 0">
        <?php echo t('The Forum Search Block is disabled in Forum Dashboard Settings.');?>
    </div>
<?php
endif; ?>    
