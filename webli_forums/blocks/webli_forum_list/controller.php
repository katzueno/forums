<?php
namespace Concrete\Package\WebliForums\Block\WebliForumList;
use BlockType;
use CollectionAttributeKey;
use Concrete\Core\Block\BlockController;
use Concrete\Core\Page\Feed;
use Page;
use Core;
use Database;
use PageList;
use Concrete\Core\Attribute\Key\CollectionKey;
use \Concrete\Core\Tree\Node\Type\Topic;
use User;
use Group;
use Concrete\Core\Conversation\Conversation;
use Concrete\Core\Conversation\Message\MessageList;


class Controller extends BlockController
{

    protected $btTable = 'btWebliForumList';
    protected $btInterfaceWidth = "800";
    protected $btInterfaceHeight = "350";
    protected $btExportPageColumns = array('cParentID');
    protected $btExportPageTypeColumns = array('ptID');
    protected $btExportPageFeedColumns = array('pfID');
    protected $btCacheBlockRecord = true;
    protected $list;
	protected $btDefaultSet = 'Forums';

    /**
     * Used for localization. If we want to localize the name/description we have to include this
     */
    public function getBlockTypeDescription()
    {
        return t("Create Forum Post Lists.");
    }

    public function getBlockTypeName()
    {
        return t("Forum List");
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

        switch ($this->orderBy) {
            case 'display_asc':
                $this->list->sortByDisplayOrder();
                break;
            case 'display_desc':
                $this->list->sortByDisplayOrderDescending();
                break;
            case 'chrono_asc':
                $this->list->sortByPublicDate();
                break;
            case 'random':
                $this->list->sortBy('RAND()');
                break;
            case 'alpha_asc':
                $this->list->sortByName();
                break;
            case 'alpha_desc':
                $this->list->sortByNameDescending();
                break;
            default:
                $this->list->sortByPublicDateDescending();
                break;
        }

        $c = Page::getCurrentPage();
        if (is_object($c)) {
            $this->cID = $c->getCollectionID();
        }

        if ($this->displayFeaturedOnly == 1) {
            $cak = CollectionAttributeKey::getByHandle('is_featured');
            if (is_object($cak)) {
                $this->list->filterByIsFeatured(1);
            }
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

        $db = Database::connection();
        if (function_exists(CollectionAttributeKey::getDefaultIndexedSearchTable()))
        {
            $columns = $db->MetaColumnNames(CollectionAttributeKey::getDefaultIndexedSearchTable());
        } else
        {
            $columns = $db->MetaColumnNames(CollectionAttributeKey::getIndexedSearchTable());
        }
        if (in_array('ak_exclude_page_list', $columns)) {
            $this->list->filter(false, '(ak_exclude_page_list = 0 or ak_exclude_page_list is null)');
        }

        if (intval($this->cParentID) != 0) {
            $cParentID = ($this->cThis) ? $this->cID : $this->cParentID;
            if ($this->includeAllDescendents) {
                $this->list->filterByPath(Page::getByID($cParentID)->getCollectionPath());
            } else {
                $this->list->filterByParentID($cParentID);
            }
        }
		
    }
	

    public function view()
    {
		
		$this->set('forumAdmin', $this->forumAdmin());
		$this->set('pinned', $this->forum_pin);
		$this->set('approved', Page::getCurrentPage()->getCollectionAttributeValue('forum_post_approved'));
		
        $list = $this->list;
        $nh = Core::make('helper/navigation');
        $this->set('nh', $nh);

        if ($this->pfID) {
            $this->requireAsset('css', 'font-awesome');
            $feed = Feed::getByID($this->pfID);
            if (is_object($feed)) {
                $this->set('rssUrl', $feed->getFeedURL());
                $link = new \HtmlObject\Element('link');
                $link->href($feed->getFeedURL());
                $link->rel('alternate');
                $link->type('application/rss+xml');
                $link->title($feed->getTitle());
                $this->addHeaderItem($link);
            }
        }
		
        //Pagination...
        $showPagination = false;
        if ($this->num > 0) {
            $list->setItemsPerPage($this->num);
            $pagination = $list->getPagination();
            $pages = $pagination->getCurrentPageResults();
            if ($pagination->getTotalPages() > 1 && $this->paginate) {
                $showPagination = true;
                $pagination = $pagination->renderDefaultView();
                $this->set('pagination', $pagination);
            }
        } else {
            $pages = $list->getResults();
        }

        if ($showPagination) {
            $this->requireAsset('css', 'core/frontend/pagination');
        }
        $this->set('pages', $pages);
        $this->set('list', $list);
        $this->set('showPagination', $showPagination);
	
		// Thumbnail Attribute
		if($this->image_attribute == 0) {
		    $this->set('thumbnail_attribute', 'thumbnail');
		} else {
			$this->set('thumbnail_attribute', CollectionAttributeKey::getByID($this->image_attribute)->getAttributeKeyHandle());	
		}
		
		$this->set('displaySettings', $this->get_display_options());
    }

    public function add()
    {
        $c = Page::getCurrentPage();
        $uh = Core::make('helper/concrete/urls');
        $this->set('c', $c);
        $this->set('uh', $uh);
        $this->set('includeDescription', true);
        $this->set('includeName', true);
		$this->set('includeDate', true);
		$this->set('display_author)', true);
        $this->set('bt', BlockType::getByHandle('webli_forum_list'));
        $this->set('featuredAttribute', CollectionAttributeKey::getByHandle('is_featured'));
        $this->set('thumbnailAttribute', CollectionAttributeKey::getByHandle('thumbnail'));
		$this->set('forum_pin', 1);
		$this->set('use_content', 1);
		$this->set('truncateSummaries', 1);
		$this->set('truncateChars', 150);
		$this->set('orderBy', 'chrono_desc');
        $this->loadKeys();
		$this->set(forumReplies, 3);
		$this->set(thumb_height, 150);
		$this->set(thumb_width, 190);
		$this->set(date_format, 'l F j, Y g:ia');
		$this->set('orderBy', 'chrono_desc');
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
        $uh = Core::make('helper/concrete/urls');
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
        } elseif (Core::make('helper/validation/numbers')->integer($parameters[0])) {
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

	
	public function forumAdmin()
	{
		$u = new user();
		$admin = Group::getByName('Administrators');
		$mod = Group::getByName('Forum Moderators');
	 
		if ($u->inGroup($admin)|| $u->inGroup($mod) || $u->isSuperUser()) {
			$forumAdmin = true;
		}
	 
		return $forumAdmin;
	}
	  
	public function getPostList($cID)
	{
		$posts = new PageList();
		$posts->filterByParentID($cID);
		$posts->sortByPublicDateDescending();
		
		return $posts->get();	
	}

	public function getConversations()
	{
		$ml = new MessageList();
		$ml->filterByNotDeleted();
		$ml->filterByApproved();
		$ml->sortByDateDescending();
		$messages = $ml->get();
		
		return $messages;	
	}

	public function getLandingPageConversations($cID)
	{	
		$db = Database::connection();
		$res = $db->GetRow("select cnvID from Conversations where cID = ?", $cID);
		
		if($res){
			$cnv = Conversation::getByID($res['cnvID']);
		
			$ml = new MessageList();
			$ml->filterByConversation($cnv);
			$ml->filterByNotDeleted();
			$ml->filterByApproved();
			$ml->sortByDateDescending();
			$messages = $ml->get();
		}
		
		return $messages;
		
	}
	
// I think we will replace this with function below		
	public function category_defaults($cID)
		{
		// get display options as defined in dashboard page
		$db = Database::connection();
		$display = $db->GetRow("select * from btWebliForums where cID = ?", $cID);
		
		if(!$display) $display = $db->GetRow("select * from btWebliForums where cID = ?", 0);
		
		return $display;
	}

	
		public function get_display_options()
		{
		// get display options as defined in dashboard page
		$db = Database::connection();
		$results = $db->GetAll("select * from btWebliForums" );
		
		foreach($results as $r){
			$display[$r['cID']] = $r;
		}
		return $display;
	}
	
	
    public function save($args)
    {
        // If we've gotten to the process() function for this class, we assume that we're in
        // the clear, as far as permissions are concerned (since we check permissions at several
        // points within the dispatcher)
        $db = Database::connection();

        $bID = $this->bID;
        $c = $this->getCollectionObject();
        if (is_object($c)) {
            $this->cID = $c->getCollectionID();
        }

        $args['num'] = ($args['num'] > 0) ? $args['num'] : 0;
        $args['cThis'] = ($args['cParentID'] == $this->cID) ? '1' : '0';
        $args['cParentID'] = ($args['cParentID'] == 'OTHER') ? $args['cParentIDValue'] : $args['cParentID'];
        if (!$args['cParentID']) {
            $args['cParentID'] = 0;
        }
        $args['enableExternalFiltering'] = ($args['enableExternalFiltering']) ? '1' : '0';
        $args['includeAllDescendents'] = ($args['includeAllDescendents']) ? '1' : '0';
        $args['includeDate'] = ($args['includeDate']) ? '1' : '0';
        $args['truncateSummaries'] = ($args['truncateSummaries']) ? '1' : '0';
		$args['use_content'] = ($args['use_content']) ? '1' : '0';
        $args['displayFeaturedOnly'] = ($args['displayFeaturedOnly']) ? '1' : '0';
        $args['filterByRelated'] = ($args['filterByRelated']) ? '1' : '0';
        $args['displayThumbnail'] = ($args['displayThumbnail']) ? '1' : '0';
        $args['crop'] = ($args['crop']) ? '1' : '0';
		$args['displayAliases'] = ($args['displayAliases']) ? '1' : '0';
		$args['forum_pin'] = ($args['forum_pin']) ? '1' : '0';
        $args['truncateChars'] = intval($args['truncateChars']);
        $args['paginate'] = intval($args['paginate']);
        $args['rss'] = intval($args['rss']);
        $args['ptID'] = intval($args['ptID']);
		$args['thumb_height'] = intval($args['thumb_height']);
		$args['thumb_height'] = intval($args['thumb_height']);
		$args['forumReplies'] = intval($args['forumReplies']);

        if ($args['rss']) {
            if ($this->pfID) {
                $pf = Feed::getByID($this->pfID);
            }

            if (!is_object($pf)) {
                $pf = new \Concrete\Core\Page\Feed();
                $pf->setTitle($args['rssTitle']);
                $pf->setDescription($args['rssDescription']);
                $pf->setHandle($args['rssHandle']);
            }

            $pf->setParentID($args['cParentID']);
            $pf->setPageTypeID($args['ptID']);
            $pf->setIncludeAllDescendents($args['includeAllDescendents']);
            $pf->setDisplayAliases($args['displayAliases']);
            $pf->setDisplayFeaturedOnly($args['displayFeaturedOnly']);
            $pf->setDisplayAliases($args['displayAliases']);
            $pf->displayShortDescriptionContent();
            $pf->save();
            $args['pfID'] = $pf->getID();
        } elseif ($this->pfID && !$args['rss']) {
            // let's make sure this isn't in use elsewhere.
            $cnt = $db->GetOne('select count(pfID) from btPageList where pfID = ?', array($this->pfID));
            if ($cnt == 1) { // this is the last one, so we delete
                $pf = Feed::getByID($this->pfID);
                if (is_object($pf)) {
                    $pf->delete();
                }
            }
            $args['pfID'] = 0;
        }

        $args['pfID'] = intval($args['pfID']);
        parent::save($args);

    }

    public function isBlockEmpty()
    {
        $pages = $this->get('pages');
        if ($this->pageListTitle) {
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
