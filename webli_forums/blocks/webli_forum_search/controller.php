<?php
namespace Concrete\Package\WebliForums\Block\WebliForumSearch;

use Loader;
use CollectionAttributeKey;
use \Concrete\Core\Page\PageList;
use \Concrete\Core\Block\BlockController;
use Page;
use Core;

class Controller extends BlockController
{
    protected $btTable = 'btWebliForumSearch';
    protected $btInterfaceWidth = "400";
    protected $btInterfaceHeight = "420";
    protected $btWrapperClass = 'ccm-ui';
    protected $btExportPageColumns = array('postTo_cID');
	protected $btDefaultSet = 'forums'; 

    public $title = "";
    public $buttonText = ">";
    public $baseSearchPath = "";
    public $resultsURL = "";
    public $postTo_cID = "";

    protected $hColor = '#EFE795';

    public function highlightedMarkup($fulltext, $highlight)
    {
        if (!$highlight) {
            return $fulltext;
        }

        $this->hText = $fulltext;
        $this->hHighlight  = $highlight;
        $this->hText = @preg_replace('#' . preg_quote($this->hHighlight, '#') . '#ui', '<span style="background-color:'. $this->hColor .';">$0</span>', $this->hText );

        return $this->hText;
    }

    public function highlightedExtendedMarkup($fulltext, $highlight)
    {
        $text = @preg_replace("#\n|\r#", ' ', $fulltext);

        $matches = array();
        $highlight = str_replace(array('"',"'","&quot;"),'',$highlight); // strip the quotes as they mess the regex

        if (!$highlight) {
            $text = Loader::helper('text')->shorten($fulltext, 180);
            if (strlen($fulltext) > 180) {
                $text .= '&hellip;<wbr>';
            }

            return $text;
        }

        $regex = '([[:alnum:]|\'|\.|_|\s]{0,45})'. preg_quote($highlight, '#') .'([[:alnum:]|\.|_|\s]{0,45})';
        preg_match_all("#$regex#ui", $text, $matches);

        if (!empty($matches[0])) {
            $body_length = 0;
            $body_string = array();
            foreach ($matches[0] as $line) {
                $body_length += strlen($line);

                $r = $this->highlightedMarkup($line, $highlight);
                if ($r) {
                    $body_string[] = $r;
                }
                if($body_length > 150)
                    break;
            }
            if(!empty($body_string))

                return @implode("&hellip;<wbr>", $body_string);
        }
    }

    public function setHighlightColor($color)
    {
        $this->hColor = $color;
    }

    /**
	 * Used for localization. If we want to localize the name/description we have to include this
	 */
    public function getBlockTypeDescription()
    {
        return t("Add a forum search box.");
    }

    public function getBlockTypeName()
    {
        return t("Forum Search");
    }

    public function __construct($obj = null)
    {
        parent::__construct($obj);
    }

    public function indexExists()
    {
        $db = Loader::db();
        $numRows = $db->GetOne('select count(cID) from PageSearchIndex');

        return ($numRows > 0);
    }

    public function view()
    {
        $c = Page::getCurrentPage();
        $this->set('title', $this->title);
        $this->set('buttonText', $this->buttonText);	
		
        $this->set('baseSearchPath', $this->baseSearchPath);
        $this->set('postTo_cID', $this->postTo_cID);
		
		$this->set('selectedCategories', unserialize($this->forum_categories));
		$this->set('display', $this->get_display_options());

        $resultsURL = $c->getCollectionPath();

        if ($this->resultsURL != '') {
            $resultsURL = $this->resultsURL;
        } elseif ($this->postTo_cID != '') {
            $resultsPage = Page::getById($this->postTo_cID);
            $resultsURL = $resultsPage->cPath;
        }

        $resultsURL = Loader::helper('text')->encodePath($resultsURL);

        $this->set('resultTargetURL', $resultsURL);

        //run query if display results elsewhere not set, or the cID of this page is set
        if ($this->postTo_cID == '') {
            if ( !empty($_REQUEST['query']) || isset($_REQUEST['akID']) || isset($_REQUEST['month']) || isset($_REQUEST['tag'])) {
                $this->do_search();
            }
        }
    }

	
	function add()
	{
		
	}
	
	
	function get_forum_categories()
	{
		$pl = new PageList();
		$pl->sortByName();
		$pl->filterByAttribute('forum_category', true);
		
		return $pl->get();
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
		
	
    public function save($data)
    {
        $args['title'] = isset($data['title']) ? $data['title'] : '';
        $args['buttonText'] = isset($data['buttonText']) ? $data['buttonText'] : '';
        $args['baseSearchPath'] = isset($data['baseSearchPath']) ? $data['baseSearchPath'] : '';
        if ( $args['baseSearchPath']=='OTHER' && intval($data['searchUnderCID'])>0 ) {
            $customPathC = Page::getByID( intval($data['searchUnderCID']) );
            if( !$customPathC )    $args['baseSearchPath']='';
            else $args['baseSearchPath'] = $customPathC->getCollectionPath();
        }
        if( trim($args['baseSearchPath'])=='/' || strlen(trim($args['baseSearchPath']))==0 )
            $args['baseSearchPath']='';

        if ( intval($data['postTo_cID'])>0 ) {
            $args['postTo_cID'] = intval($data['postTo_cID']);
        } else {
            $args['postTo_cID'] = '';
        }

        $args['resultsURL'] = ( $data['externalTarget']==1 && strlen($data['resultsURL'])>0 ) ? trim($data['resultsURL']) : '';
		$args['select_all'] = ($data['select_all']) ? '1' : '0';
		
		$args['forum_categories'] = '';
		if($data['forum_categories']){
			$args['forum_categories'] = serialize($data['forum_categories']);
		}else {
			$args['forum_categories'] = '';
		}
		
		parent::save($args);
    }

    public $reservedParams=array('page=','query=','search_paths[]=','submit=','search_paths%5B%5D=' );

    public function do_search()
    {

        $q = $_REQUEST['query'];
        // i have NO idea why we added this in rev 2000. I think I was being stupid. - andrew
        // $_q = trim(preg_replace('/[^A-Za-z0-9\s\']/i', ' ', $_REQUEST['query']));
        $_q = $q;

        $ipl = new PageList();
		

		
        $aksearch = false;
        if (is_array($_REQUEST['akID'])) {
            foreach ($_REQUEST['akID'] as $akID => $req) {
                $fak = CollectionAttributeKey::getByID($akID);
                if (is_object($fak)) {
                    $type = $fak->getAttributeType();
                    $cnt = $type->getController();
                    $cnt->setAttributeKey($fak);
                    $cnt->searchForm($ipl);
                    $aksearch = true;
                }
            }
        }

        if (isset($_REQUEST['month']) && isset($_REQUEST['year'])) {
            $year = @intval($_REQUEST['year']);
            $month = abs(@intval($_REQUEST['month']));
            if (strlen(abs($year)) < 4) {
                $year = (($year < 0) ? '-' : '') . str_pad($year, 4, '0', STR_PAD_LEFT);
            }
            if ($month < 12) {
                $month = str_pad($month, 2, '0', STR_PAD_LEFT);
            }
            $daysInMonth = date('t', strtotime("$year-$month-01"));
            $dh = Core::make('helper/date');
            /* @var $dh \Concrete\Core\Localization\Service\Date */
            $start = $dh->toDB("$year-$month-01 00:00:00", 'user');
            $end = $dh->toDB("$year-$month-$daysInMonth 23:59:59", 'user');
            $ipl->filterByPublicDate($start, '>=');
            $ipl->filterByPublicDate($end, '<=');
            $aksearch = true;
        }

        if (empty($_REQUEST['query']) && $aksearch == false && empty($_REQUEST['tag'])) {
            return false;
        }

        if (isset($_REQUEST['query'])) {
            $ipl->filterByKeywords($_q);
        }

	
        if ( is_array($_REQUEST['search_paths']) ) {
            foreach ($_REQUEST['search_paths'] as $path) {
                if(!strlen($path)) continue;
                $ipl->filterByPath($path);
            }
        } elseif ($this->baseSearchPath != '') {
            $ipl->filterByPath($this->baseSearchPath);
        }

		
		//Filter by selected forum categories
		if (isset($_REQUEST['cat'])) {
			foreach($_REQUEST['cat'] as $val){
				$select[] =  $val;
			}
				$ipl->filterByParentID($select);
		}
	
	
		//Filter by tag Attribute
		if ($_REQUEST['tag']){
			$ipl->filterByTags($_REQUEST['tag']);
		}
		

        // TODO fix this
        //$ipl->filter(false, '(ak_exclude_search_index = 0 or ak_exclude_search_index is null)');

        $pagination = $ipl->getPagination();
        $results = $pagination->getCurrentPageResults();

        $this->set('query', $q);
        $this->set('results', $results);
        $this->set('do_search', true);
        $this->set('searchList', $ipl);
        $this->set('pagination', $pagination);
    }

}
