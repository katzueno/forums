<?php
namespace Concrete\Package\WebliForums\Block\WebliForumTags;
use BlockType;
use CollectionAttributeKey;
use Concrete\Core\Block\BlockController;
use Loader;
use Page;
use Core;
use PageList;
use Concrete\Core\Attribute\Key\CollectionKey;
use \Concrete\Core\Tree\Node\Type\Topic;


class Controller extends BlockController
{

    protected $btTable = 'btWebliForumTags';
    protected $btInterfaceWidth = "400";
    protected $btInterfaceHeight = "420";
    protected $btExportPageColumns = array('cParentID');
    protected $btExportPageTypeColumns = array('ptID');
    protected $btCacheBlockRecord = true;
    protected $list;
	protected $btDefaultSet = 'forums';

    /**
     * Used for localization. If we want to localize the name/description we have to include this
     */
    public function getBlockTypeDescription()
    {
        return t("Create Forum Tag List.");
    }

    public function getBlockTypeName()
    {
        return t("Forum Tags");
    }

    public function getJavaScriptStrings()
    {
        return array(
            'feed-name' => t('Please give your RSS Feed a name.')
        );
    }

    public function on_start()
    {
		
        $this->list = new PageList();
        $this->list->disableAutomaticSorting();
        //$pl->setNameSpace('b' . $this->bID);

        $cArray = array();
		

        $c = Page::getCurrentPage();
        if (is_object($c)) {
            $this->cID = $c->getCollectionID();
        }

        if ($this->displayAliases) {
            $this->list->includeAliases();
        }
        $this->list->filter('cvName', '', '!=');

        if ($this->ptID) {
            $this->list->filterByPageTypeID($this->ptID);
        }

        if ($this->filterByRelated) {
            $ak = CollectionKey::getByHandle($this->relatedTopicAttributeKeyHandle);
            if (is_object($ak)) {
                $topics = $c->getAttribute($ak->getAttributeKeyHandle());
                if (count($topics) > 0 && is_array($topics)) {
                    $topic = $topics[array_rand($topics)];
                    $this->list->filter('p.cID', $c->getCollectionID(), '<>');
                    $this->list->filterByTopic($topic);
                }
            }
        }

        $db = Loader::db();
        $columns = $db->MetaColumnNames(CollectionAttributeKey::getIndexedSearchTable());
        if (in_array('ak_exclude_page_list', $columns)) {
            $this->list->filter(false, '(ak_exclude_page_list = 0 or ak_exclude_page_list is null)');
        }

        if (intval($this->cParentID) != 0) {
            $cParentID = ($this->cThis) ? $this->cID : $this->cParentID;
            $this->list->filterByPath(Page::getByID($cParentID)->getCollectionPath());
        }
		
		if($this->parent){
				$parentPage=Page::getByID($c->getCollectionParentID());
				$this->list->filterByParentID($parentPage->getCollectionID());
		}


		$this->list->filterByAttribute('forum_post_approved', 1, '=');		 
		
        return $this->list;
    }


    public function view()
    {	
		
		$this->set('tags', $this->get_tags());
		$this->set('display', $this->get_display_options());
		
	}


	function get_display_options()
	{
		$page = Page::getCurrentPage();
		$parentPage = Page::getByID($page->getCollectionParentID());
		
		$db = Loader::db();
		if($page->getCollectionAttributeValue('forum_category')){
			$display = $db->GetRow("select * from btWebliForums where cID = ?", $page->getCollectionID());
		} elseif($parentPage->getCollectionAttributeValue('forum_category')){
			$display = $db->GetRow("select * from btWebliForums where cID = ?", $parentPage->getCollectionID());		
		}
		
		if(!$display) $display = $db->GetRow("select * from btWebliForums where cID = ?", 0);
		
		return $display;
	}
	
	
	function get_tags(){
		$pages = $this->list->get();
		 
		$tagList = array();
		foreach ($pages as $page) {				  
			if($page->getAttribute('tags')){
			   $selectedOptions = $page->getAttribute('tags');
			   if($selectedOptions->count() > 0) {
					foreach($selectedOptions as $opt) {						  
						 $tagCount[] = $opt->value;
						 $tags = array_count_values($tagCount);
					}
			   }
			}
		}
		  
		
		if(is_array($tags)){
		  
			ksort($tags);
			
			if($this->max_tags > 0){
			  $tags = array_slice($tags, 0, $this->max_tags);
			}
			
			$max_size = $this->max_height; // max font size in pixels
			$min_size = $this->min_height; // min font size in pixels
			
			// largest and smallest array values
			$max_qty = max(array_values($tags));
			$min_qty = min(array_values($tags));
			
			// find the range of values
			$spread = $max_qty - $min_qty;
			if ($spread == 0) { // we don't want to divide by zero
					$spread = 1;
			}
			
			// set the font-size increment
			$step = ($max_size - $min_size) / ($spread);
			
			// Get results page
			$nh = Loader::helper('navigation');
			$resultsPage = Page::getById($this->postTo_cID);
			$resultsURL = $nh->getCollectionURL($resultsPage);
			
			// Get Search Path
			$searchPath = Page::getByID($this->cParentID)->getCollectionPath();
			
			// loop through the tag array
			foreach ($tags as $key => $value) {
					// calculate font-size
					// find the $value in excess of $min_qty
					// multiply by the font-size increment ($size)
					// and add the $min_size set above
					$size = round($min_size + (($value - $min_qty) * $step));
			
					$tagDisplay .= '<a href="' . $resultsURL . '?search_paths[]=' . $searchPath . '&tag=' . $key . '" style="font-size: ' . $size . 'px" title="' . $value . ' things tagged with ' . $key . '">' . $key . '</a> ';
			}
		
		return $tagDisplay;	
		}
		
	}
	
	
	function get_forum_categories()
	{
		$pl = new PageList();
		$pl->sortByName();
		$pl->filterByAttribute('forum_category', true);
		
		return $pl->get();
	}
	
    public function add()
    {

        $c = Page::getCurrentPage();
        $uh = Loader::helper('concrete/urls');
        $this->set('c', $c);
        $this->set('uh', $uh);
        $this->set('includeDescription', true);
        $this->set('includeName', true);
        $this->set('bt', BlockType::getByHandle('page_list'));
        $this->set('featuredAttribute', CollectionAttributeKey::getByHandle('is_featured'));
        $this->set('thumbnailAttribute', CollectionAttributeKey::getByHandle('thumbnail'));
        $this->loadKeys();
		$this->set(max_height, 30);
		$this->set(min_height, 15);
		$this->set(event_location, true);
		$this->set(orderBy, 'chrono_asc');
    }

    public function edit()
    {
        $b = $this->getBlockObject();
        $bCID = $b->getBlockCollectionID();
        $bID = $b->getBlockID();
        $this->set('bID', $bID);
        $c = Page::getCurrentPage();
        if ($c->getCollectionID() != $this->cParentID && (!$this->cThis) && ($this->cParentID != 0)) {
            $isOtherPage = true;
            $this->set('isOtherPage', true);
        }
        if ($this->pfID) {
            $feed = Feed::getByID($this->pfID);
            if (is_object($feed)) {
                $this->set('rssFeed', $feed);
            }
        }
        $uh = Loader::helper('concrete/urls');
        $this->set('uh', $uh);
        $this->set('bt', BlockType::getByHandle('page_list'));
        $this->set('featuredAttribute', CollectionAttributeKey::getByHandle('is_featured'));
        $this->set('thumbnailAttribute', CollectionAttributeKey::getByHandle('thumbnail'));
        $this->loadKeys();
    }

    protected function loadKeys()
    {
        $attributeKeys = array();
        $keys = CollectionKey::getList(array('atHandle' => 'topics'));
        foreach ($keys as $ak) {
            if ($ak->getAttributeTypeHandle() == 'topics') {
                $attributeKeys[] = $ak;
            }
        }
        $this->set('attributeKeys', $attributeKeys);
    }

    public function action_filter_by_topic($treeNodeID = false, $topic = false)
    {
        if ($treeNodeID) {
            $this->list->filterByTopic(intval($treeNodeID));
            $topicObj = Topic::getByID(intval($treeNodeID));
            if (is_object($topicObj)) {
                $seo = Core::make('helper/seo');
                $seo->addTitleSegment($topicObj->getTreeNodeDisplayName());
            }
        }
        $this->view();
    }

    public function action_filter_by_tag($tag = false)
    {
        $seo = Core::make('helper/seo');
        $seo->addTitleSegment($tag);
        $this->list->filterByTags(h($tag));
        $this->view();
    }

    public function action_filter_by_date($year = false, $month = false, $timezone = 'user')
    {
        if (is_numeric($year)) {
            $year = (($year < 0) ? '-' : '') . str_pad(abs($year), 4, '0', STR_PAD_LEFT);
            if ($month) {
                $month = str_pad($month, 2, '0', STR_PAD_LEFT);
                $lastDayInMonth = date('t', strtotime("$year-$month-01"));
                $start = "$year-$month-01 00:00:00";
                $end = "$year-$month-$lastDayInMonth 23:59:59";
            } else {
                $start = "$year-01-01 00:00:00";
                $end = "$year-12-31 23:59:59";
            }
            if ($timezone !== 'system') {
                $dh = Core::make('helper/date');
                /* @var $dh \Concrete\Core\Localization\Service\Date */
                $start = $dh->toDB($start, $timezone);
                $end = $dh->toDB($end, $timezone);
            }
            $this->list->filterByPublicDate($start, '>=');
            $this->list->filterByPublicDate($end, '<=');
            
            $seo = Core::make('helper/seo');
            $srv = Core::make('helper/date');
            $seo->addTitleSegment($srv->date('F Y',$start));
        }
        $this->view();
    }

    public function validate($args)
    {
        $e = Core::make('helper/validation/error');
        $vs = Core::make('helper/validation/strings');
        $pf = false;
        if ($this->pfID) {
            $pf = Feed::getByID($this->pfID);
        }
        if ($args['rss'] && !is_object($pf)) {
            if (!$vs->handle($args['rssHandle'])) {
                $e->add(t('Your RSS feed must have a valid URL, containing only letters, numbers or underscores'));
            }
            if (!$vs->notempty($args['rssTitle'])) {
                $e->add(t('Your RSS feed must have a valid title.'));
            }
            if (!$vs->notempty($args['rssDescription'])) {
                $e->add(t('Your RSS feed must have a valid description.'));
            }
        }

        return $e;
    }

    public function getPassThruActionAndParameters($parameters)
    {
        if ($parameters[0] == 'topic') {
            $method = 'action_filter_by_topic';
            $parameters = array_slice($parameters, 1);
        } elseif ($parameters[0] == 'tag') {
            $method = 'action_filter_by_tag';
            $parameters = array_slice($parameters, 1);
        } elseif (Loader::helper("validation/numbers")->integer($parameters[0])) {
            // then we're going to treat this as a year.
            $method = 'action_filter_by_date';
            $parameters[0] = intval($parameters[0]);
            if (isset($parameters[1])) {
                $parameters[1] = intval($parameters[1]);
            }
        }

        return array($method, $parameters);
    }

    public function isValidControllerTask($method, $parameters = array())
    {
        if (!$this->enableExternalFiltering) {
            return false;
        }

        return parent::isValidControllerTask($method, $parameters);
    }

    public function save($args)
    {
        // If we've gotten to the process() function for this class, we assume that we're in
        // the clear, as far as permissions are concerned (since we check permissions at several
        // points within the dispatcher)
        $db = Loader::db();

        $bID = $this->bID;
        $c = $this->getCollectionObject();
        if (is_object($c)) {
            $this->cID = $c->getCollectionID();
        }

        $args['cThis'] = ($args['cParentID'] == $this->cID) ? '1' : '0';
 		if($args['cParentID'] == 'PARENT'){
			$args['parent'] = 1;
			$args['cParentIDValue'] = 0;
		} else {
			$args['parent'] = 0;
		}
		$args['cParentID'] = ($args['cParentID'] == 'OTHER' || $args['cParentID'] == 'PARENT') ? $args['cParentIDValue'] : $args['cParentID'];
        if (!$args['cParentID']) {
            $args['cParentID'] = 0;
        }
        $args['enableExternalFiltering'] = ($args['enableExternalFiltering']) ? '1' : '0';
        $args['filterByRelated'] = ($args['filterByRelated']) ? '1' : '0';
        $args['ptID'] = intval($args['ptID']);

		
		if($data['forum_categories']){
			$args['forum_categories'] = serialize($data['forum_categories']);
		}else {
			$args['forum_categories'] = '';
		}
		
		$args['max_width'] = intval($args['max_width']);
		$args['max_height'] = intval($args['max_height']);
		$args['max_tags'] = intval($args['max_tags']);
		
        parent::save($args);

    }

    public function isBlockEmpty()
    {
        $pages = $this->get('pages');
        if ($this->title) {
            return false;
        }
        if (count($pages) == 0) {
            if ($this->noResultsMessage) {
                return false;
            } else {
                return true;
            }
        } else {
            if ($this->includeName || $this->includeDate || $this->displayThumbnail
                || $this->includeDescription || $this->useButtonForLink
            ) {
                return false;
            } else {
                return true;
            }
        }
    }

}
