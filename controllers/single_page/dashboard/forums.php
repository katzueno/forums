<?php 
namespace Concrete\Package\WebliForums\Controller\SinglePage\Dashboard;
use \Concrete\Core\Page\Controller\DashboardPageController;
use Loader;
use Core;
use User;
use UserInfo;
use Group;
use Page;
use PageList;
use Permissions;
use PageTemplate;
use PageType;
use Asset;
use AssetList;
use Concrete\Core\Conversation\Conversation;
use Concrete\Core\Conversation\Message\MessageList;
use \Concrete\Core\Conversation\Message\Message as ConversationMessage;
use \Concrete\Core\Attribute\Key\CollectionKey as CollectionAttributeKey;
use \Concrete\Core\Attribute\Set as AttributeSet;
use \Concrete\Core\Page\Type\Composer\Control\Type\Type as ComposerControlType;
use Concrete\Core\Page\Type\Composer\FormLayoutSet as FormLayoutSet;

class Forums extends DashboardPageController {

	public function on_start() 
	{
		$al = AssetList::getInstance();
		
		$al->register('css', 'forumsDashboard', 'css/forumsDashboard.css', array(), 'webli_forums');
		$al->register('javascript', 'forumsDashboard', 'js/forumsDashboard.js', array(), 'webli_forums');

		$this->requireAsset('javascript', 'forumsDashboard');
		$this->requireAsset('css', 'forumsDashboard');
	
		$this->requireAsset('redactor');
		$this->requireAsset('core/lightbox');		
	}

	
	function view()
	{		
		// Send some values to the view
		
		$active = $this->get_active();
		$settings = $this->get_saved_settings($active['active_category']);
		$forumCategories = $this->get_forum_categories();
		
		if($settings['cID'] == 0){
			$this->set('cID', $forumCategories[0]->getCollectionID());
		} else {
			$this->set('cID', $settings['cID']);
		}
	
		$this->set('activeCategory', $active['active_category']);	
		$this->set('settings', $settings);
		$this->set('forumCategories', $forumCategories);

		$newPostCategory = $active['new_post_category'];
		if(!$newPostCategory) $newPostCategory = $forumCategories[0]->getCollectionID();
			
		$this->set('newPostCategory', $newPostCategory);
		$this->set('newPostSettings', $this->get_saved_settings($active['new_post_category']));
		
		$this->set('activeTab', $active['active_tab']);
		$this->set('sortCategory', $active['sort_category']);
		$this->set('unApprovedPages', $this->get_unapproved_pages());		
		$this->set('forumPosts', $this->get_forum_posts());
		$this->set('pinnedPages', $this->get_pinned_pages());
		$this->set('replies', $this->get_approved_conversations());
		$this->set('unApprovedReplies', $this->get_unApproved_conversations());
		$this->set('forumAttributes', $this->get_forum_attributes());
		$this->set('pageTemplates', PageTemplate::getList());
		$this->set('pageTypes', PageType::getList());
	}


	function get_saved_settings($cID)
	{
		// get array of values in db
		$db = Loader::db();
		
		$res = $db->GetRow("select * from btWebliForums where cID = ?", $cID);
		
		if(!$res) $res = $db->GetRow("select * from btWebliForums where cID = ?", 0);
		
		return $res;
	}

	
	function get_forum_attributes()
	{
	
		$atSet = AttributeSet::getByHandle('forums');
		$atKeys = $atSet->getAttributeKeys();
		
		$banAtt = array('Forum Post', 'Forum Category', 'Pin Forum Post', 'Forum Image', 'Forum Email Address', 'Forum Name', 'Forum Post Approved'); 
		
		foreach($atKeys as $ak) {
			if(!in_array($ak->getAttributeKeyName(), $banAtt)) {
				$forumAttributes[$ak->getAttributeKeyID()] = $ak->getAttributeKeyName();
			}
		}
		
		return $forumAttributes;
	}
	
	
	function get_forum_categories()
	{
		$pl = new PageList();
		$pl->sortByName();
		$pl->filterByAttribute('forum_category', true);
		
		return $pl->get();
	}


	function select_forum_category()
	{
		// value from category selector on setting page
		$this->save_active_category($_POST['activeCategory']);
		$this->save_active_tab($_POST['activeTab']);
		
		$this->redirect(Page::getCurrentPage()->getCollectionPath());
	}
	
		
	function get_active()
	{
		// get array of values in db
		$db = Loader::db();
		
		$res = $db->GetRow("select * from btWebliForums where cID = ?", 0);
		
		return $res;
	}	

	
	function save_active_category($cat)
	{
		$db = Loader::db();
		$db->query('update btWebliForums set active_category = ? where cID = ? ',array($cat, 0));	
	}

	
	function set_new_post_category()
	{
		$cat = $_POST['newPostCategory'];
		
		$db = Loader::db();
		$db->query('update btWebliForums set new_post_category = ? where cID = ? ',array($cat, 0));
		
		$this->save_active_tab($_POST['activeTab']);
		$this->redirect(Page::getCurrentPage()->getCollectionPath());
	}


	function set_sort_category()
	{
		$cat = $_POST['sortCategory'];
		
		$db = Loader::db();
		$db->query('update btWebliForums set sort_category = ? where cID = ? ',array($cat, 0));
		
		$this->save_active_tab($_POST['activeTab']);
		$this->redirect(Page::getCurrentPage()->getCollectionPath());
	}
	
	
	function save_active_tab($tab)
	{
		$db = Loader::db();
		$db->query('update btWebliForums set active_tab = ? where cID = ? ',array($tab, 0));	
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


	public function get_approved_conversations()
	{
		$ml = new MessageList();
		$ml->filterByNotDeleted();
		$ml->filterByApproved();
		$ml->sortByDateDescending();
		$messages = $ml->get();
		
		foreach($messages as $msg){
			$cnv = $msg->getConversationObject();
			$page = $cnv->getConversationPageObject();
			
			if($page instanceof Page && $page->getCollectionTypeHandle() == 'forum_post') {
				$sort = $this->get_active();
				if($sort['sort_category'] > 0){
					if($page->getCollectionParentID() == $sort){
						$replies[] = $msg;
					}
				} else {
					$replies[] = $msg;			 
				}
			}	
		}
		
		return $replies;	
	}


	public function get_unApproved_conversations()
	{
		$ml = new MessageList();
		$ml->filterByNotDeleted();
		$ml->filterByUnapproved();
		$ml->sortByDateDescending();
		$messages = $ml->get();
		
		foreach($messages as $msg){
			$cnv = $msg->getConversationObject();
			$page = $cnv->getConversationPageObject();
			
			if($page instanceof Page && $page->getCollectionTypeHandle() == 'forum_post') {
				$sort = $this->get_active();
				if($sort['sort_category'] > 0){
					if($page->getCollectionParentID() == $sort){
						$replies[] = $msg;
					}
				} else {
					$replies[] = $msg;			 
				}
			}	
		}
		
		return $replies;	
	}

	
	function edit_reply()
	{
		$vf = Loader::helper('validation/form');
        $vf->setData($_POST['ccm_token']);
        $vf->addRequiredToken('edit_reply');
        
		if ($vf->test()) {
			$msg = ConversationMessage::getByID($_POST['mID']);
			$msg->setMessageBody($_POST['replyBody']);	
		} else {
			die("Access Denied.");
		}
		
		$this->save_active_tab($_POST['activeTab']);
		$this->redirect(Page::getCurrentPage()->getCollectionPath());
	}
	
	
	function delete_reply()
	{
		$vf = Loader::helper('validation/form');
        $vf->setData($_POST['ccm_token']);
        $vf->addRequiredToken('delete_reply');
        
		if ($vf->test()) {
			$msg = ConversationMessage::getByID($_POST['mID']);
			
			$msg->delete();	
		} else {
			die("Access Denied.");
		}
		
		$this->save_active_tab($_POST['activeTab']);
		$this->redirect(Page::getCurrentPage()->getCollectionPath());
	}
	

	function approve_reply()
	{
		$vf = Loader::helper('validation/form');
        $vf->setData($_POST['ccm_token']);
        $vf->addRequiredToken('approve_reply');
        
		if ($vf->test()) {
			$msg = ConversationMessage::getByID($_POST['mID']);
			
			$msg->approve();	
		} else {
			die("Access Denied.");
		}
		
		$this->save_active_tab($_POST['activeTab']);
		$this->redirect(Page::getCurrentPage()->getCollectionPath());
	}
	
		
	function unapprove_reply()
	{
		$vf = Loader::helper('validation/form');
        $vf->setData($_POST['ccm_token']);
        $vf->addRequiredToken('unapprove_reply');
        
		if ($vf->test()) {
			$msg = ConversationMessage::getByID($_POST['mID']);
			
			$msg->unapprove();	
		} else {
			die("Access Denied.");
		}
		
		$this->save_active_tab($_POST['activeTab']);
		$this->redirect(Page::getCurrentPage()->getCollectionPath());
	}
	
	
	function get_forum_posts()
	{
		$fpl = new PageList();
		$fpl->filterByCollectionTypeHandle('forum_post');
		$fpl->filterByAttribute('forum_post_approved', true);
		$fpl->sortByPublicDateDescending();
		
		$sort = $this->get_active();
		if($sort['sort_category'] > 0){
			 $fpl->filterByParentID($sort); 
		}
		
		$forumPosts = $fpl->get();
		
		return $forumPosts;
	}

	
	function get_unapproved_pages()
	{
		$fpl = new PageList();
		$fpl->filterByCollectionTypeHandle('forum_post');
		$fpl->sortByPublicDateDescending();
		$fpl->filterByAttribute('forum_post_approved', false);
		
		$sort = $this->get_active();
		if($sort['sort_category'] > 0){
			 $fpl->filterByParentID($sort); 
		}
		
		$unapprovedPages = $fpl->get();
		
		return $unapprovedPages;
	}
	
	
	function get_pinned_pages()
	{
		$fpl = new PageList();
		$fpl->sortByPublicDateDescending();
		$fpl->filterByAttribute('forum_pin', true);

		$sort = $this->get_active();
		if($sort['sort_category'] > 0){
			 $fpl->filterByParentID($sort); 
		}
		
		$pinnedPages = $fpl->get();
		
		return $pinnedPages;
	}
	

	function pin_page()
	{
		$vf = Loader::helper('validation/form');
        $vf->setData($_POST['ccm_token']);
        $vf->addRequiredToken('pin_page');
        
		if ($vf->test()) {
		$page = Page::getByID($_POST['cID']);
		$page->setAttribute('forum_pin', 1);
		} else {
			die("Access Denied.");
		}
		
		$this->save_active_tab($_POST['activeTab']);
		$this->redirect(Page::getCurrentPage()->getCollectionPath());
	}
	
	
	function un_pin_page()
	{
		$vf = Loader::helper('validation/form');
        $vf->setData($_POST['ccm_token']);
        $vf->addRequiredToken('un_pin_page');
        
		if ($vf->test()) {
		$page = Page::getByID($_POST['cID']);
		$page->setAttribute('forum_pin', 0);
		} else {
			die("Access Denied.");
		}
		
		$this->save_active_tab($_POST['activeTab']);
		$this->redirect(Page::getCurrentPage()->getCollectionPath());
	}
	
	
	function delete_page()
	{
		$vf = Loader::helper('validation/form');
        $vf->setData($_POST['ccm_token']);
        $vf->addRequiredToken('delete_page');
        
		if ($vf->test()) {
			$page = Page::getByID($_POST['cID']);
			$cp = new Permissions($page);
		
			if($cp->canDeletePage()) {
				
				$page->delete();
			}
		} else {
			die("Access Denied.");
		}
		
		$this->save_active_tab($_POST['activeTab']);
		$this->redirect(Page::getCurrentPage()->getCollectionPath());
	}

	
	function approve_page()
	{
		$vf = Loader::helper('validation/form');
        $vf->setData($_POST['ccm_token']);
        $vf->addRequiredToken('approve_page');
        
		if ($vf->test()) {
		$page = Page::getByID($_POST['cID']);
		$page->setAttribute('forum_post_approved', 1);
		} else {
			die("Access Denied.");
		}
		
		$this->save_active_tab($_POST['activeTab']);
		$this->redirect(Page::getCurrentPage()->getCollectionPath());
	}

	
	function unapprove_page()
	{
		$vf = Loader::helper('validation/form');
        $vf->setData($_POST['ccm_token']);
        $vf->addRequiredToken('unapprove_page');
        
		if ($vf->test()) {
		$page = Page::getByID($_POST['cID']);
		$page->setAttribute('forum_post_approved', 0);
		} else {
			die("Access Denied.");
		}
		
		$this->save_active_tab($_POST['activeTab']);
		$this->redirect(Page::getCurrentPage()->getCollectionPath());	
	}

	
		function new_forum_post()
	{
		$vf = Loader::helper('validation/form');
        $vf->setData($_POST['ccm_token']);
        $vf->addRequiredToken('new_forum_post');
        
		if ($vf->test()) {
			
			$th = Core::make('helper/text');
			
			$parentPage = Page::getByID($_POST['category']);
			$pageType = PageType::getByHandle('forum_post');
			$template = PageTemplate::getByHandle('forum_post');
			
			$u = new User();
			
			// if user not loggged in make admin page owner
			if($u->isLoggedIn()) {
				$owner = $u->getUserID();
			} else {
				$owner = 1;	
			}
			
			// Add new page to site
			$newPage = $parentPage->add($pageType, array(
				'cName' => $_POST['title'],
				'cDescription' => $th->makenice($_POST['description']),
				'cHandle ' => $th->sanitizeFileSystem($_POST['title'], $leaveSlashes=false),
				'uID' => $_POST['user'],
				'cDatePublic' => $_POST['public_date']
				), $template);
			
			if($_POST['forumPost']) $newPage->setAttribute('forum_post', $_POST['forumPost'] );
			if($_POST['forumImage']) $newPage->setAttribute('forum_image', $_POST['forumImage'] );
			if($_POST['pin']) $newPage->setAttribute('forum_pin', $_POST['pin'] );
			
			$newPage->setAttribute('forum_post_approved', 1);
	
			// save tags
			$ak = CollectionAttributeKey::getByHandle('tags');
			$ak->saveAttributeForm($newPage);
			
			$newPage->reindex();
		
		} else {
			die("Access Denied.");
		}
		
		$this->redirect($newPage->getCollectionPath());
	}

//	function edit_post()
//	{
//		$vf = Loader::helper('validation/form');
//        $vf->setData($_POST['ccm_token']);
//        $vf->addRequiredToken('edit_post');
//        
//		if ($vf->test()) {
//			
//			// get page to be edited
//			$editPage = Page::getbyID($_POST['cID']);
//
//			// check if we need to move the page
//			if($_POST['forumSelect'] && $_POST['forumSelect'] != $editPage->getCollectionParentID()){	
//				$category = \Page::getByID($_POST['forumSelect']);
//				$editPage->move($category);
//			}
//			
//			// Save any changes
//			if($_POST['title']) $editPage->update(array('cName' => $_POST['title']));
//			if($_POST['forumPost']) $editPage->setAttribute('forum_post', $_POST['forumPost'] );
//			if($_POST['forumTags']) $editPage->setAttribute('forum_tags', $_POST['forumTags'] );
//
//			// save tags
//			$ak = CollectionAttributeKey::getByHandle('tags');
//			$ak->saveAttributeForm($editPage);
//			
//			$editPage->reindex();
//			
//		} else {
//			die("Access Denied.");
//		}
//		
//		$this->save_active_tab($_POST['activeTab']);
//		$this->redirect(Page::getCurrentPage()->getCollectionPath());
//	}
//	

	function save_settings()
	{
		
		if($_POST['optional_attributes']) {
			$forumAttributes = serialize($_POST['optional_attributes']);
			
			/* Get blog_post page template */
			$pageType = \PageType::getByID($_POST['page_type']);
			$ctTemplate = $pageType->getPageTypeDefaultPageTemplateObject();
			$blogPostTemplate = $pageType->getPageTypePageTemplateDefaultPageObject($ctTemplate);
			
			// Drop the Optional Attribute Composer Layout
			$db = Loader::db();
			$db->Execute('delete from PageTypeComposerFormLayoutSets where ptComposerFormLayoutSetName = ?', array('Optional Attributes'));
			
			/* Add Composer Layouts */
			$post = $pageType->addPageTypeComposerFormLayoutSet('Optional Attributes', 'Optional Attributes');
			
			/* Add Optional Attributes */
			$cct = ComposerControlType::getByHandle('collection_attribute');
		
			foreach($_POST['optional_attributes'] as $oa) {	
				$control = $cct->getPageTypeComposerControlByIdentifier($oa);
				$control->addToPageTypeComposerFormLayoutSet($post);
			}
			
		}
		
		$db = Loader::db();
		// Drop database row
		$db->Execute('DELETE FROM btWebliForums WHERE cID= ?',$_POST['cID']);
		
		$db->Execute('insert into btWebliForums (
			cID,
			display_title,
			display_date,
			date_format,
			display_author,
			display_tags,
			enable_comments,
			enable_breadcrumb,
			mod_approval,
			display_image,
			crop_image,
			image_height,
			image_width,
			add_this,
			add_this_script,
			add_this_share_html,
			add_this_follow_html,
			add_this_recommend_html,
			share_this,
			share_this_script,
			share_this_html,		
			twitter_post,
			twitter_api_key,
			twitter_api_secret,
			twitter_token,
			twitter_token_secret,
			twitter_select,
			twitter_image,
			anonymous_posts,
			captcha,
			forum_search_block,
			forum_archive_block,
			forum_tags_block,
			display_avatars,
			notification,
			email_addresses,
			page_template,
			page_type,
			optional_attributes) values (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)',
			array( $_POST['cID'],
				$_POST['display_title'],
				$_POST['display_date'],
				$_POST['date_format'],
				$_POST['display_author'],
				$_POST['display_tags'],
				$_POST['enable_comments'],
				$_POST['enable_breadcrumb'],
				$_POST['mod_approval'],
				$_POST['display_image'],
				$_POST['crop_image'],
				$_POST['image_height'],
				$_POST['image_width'],
				$_POST['add_this'],
				($_POST['add_this']) ? $_POST['add_this_script'] : null,
				($_POST['add_this']) ? $_POST['add_this_share_html'] : null,
				($_POST['add_this']) ? $_POST['add_this_follow_html'] : null,
				($_POST['add_this']) ? $_POST['add_this_recommend_html'] : null,
				$_POST['share_this'],
				($_POST['share_this']) ? $_POST['share_this_script'] : null,
				($_POST['share_this']) ? $_POST['share_this_html'] : null,
				$_POST['twitter_post'],
				$_POST['twitter_api_key'],
				$_POST['twitter_api_secret'],
				$_POST['twitter_token'],
				$_POST['twitter_token_secret'],
				$_POST['twitter_select'],
				$_POST['twitter_image'],
				$_POST['anonymous_posts'],
				$_POST['captcha'],
				$_POST['forum_search_block'],
				$_POST['forum_archive_block'],
				$_POST['forum_tags_block'],
				$_POST['display_avatars'],
				$_POST['notification'],
				$_POST['email_addresses'],
				$_POST['page_template'],
				$_POST['page_type'],
				$forumAttributes));
				
		$this->save_active_category($_POST['cID']);
		$this->save_active_tab($_POST['activeTab']);
		$this->redirect(Page::getCurrentPage()->getCollectionPath());
	}	

}