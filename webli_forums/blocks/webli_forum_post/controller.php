<?php
namespace Concrete\Package\WebliForums\Block\WebliForumPost;
use Loader;
use Core;
use Database;
use Page;
use Concrete\Core\Page\PageList;
use PageTemplate;
use PageType;
use BlockType;
use User;
use Group;
use \Concrete\Core\Attribute\Key\CollectionKey as CollectionAttributeKey;

use \Concrete\Core\Attribute\Set as AttributeSet;
use Permissions;

use Concrete\Core\Block\BlockController;
class Controller extends BlockController
{
	protected $btTable = 'btWebliForumPost';
	protected $btInterfaceWidth = "800";
	protected $btInterfaceHeight = "350";
	protected $btDefaultSet = 'Forums';
	
	public function getBlockTypeDescription()
	{
		return t("Create forum posts");
	}

	public function getBlockTypeName()
	{
		return t("Forum Post");
	}

	public function add()
	{
		$this->set('includeDescription', true);
        $this->set('includeName', true);
		$this->set('includeDate', true);
		$this->set('display_author)', true);
        $this->set('bt', BlockType::getByHandle('webli_forum_post'));
		$this->set(date_format, 'l F j, Y g:ia');
	}
	
    public function view()
	{
		$this->requireAsset('redactor');
		$this->requireAsset('core/lightbox');
		
		$this->set('forumPages', $this->get_forum_pages());
		$this->set('forumAdmin', $this->forumAdmin());
		$this->set('approved', Page::getCurrentPage()->getCollectionAttributeValue('forum_post_approved'));
		$this->set('pinned', Page::getCurrentPage()->getCollectionAttributeValue('forum_pin'));
		$this->set('publicForums', $this->get_public_forums());
		$this->set('settings', $this->get_saved_settings());
		
		$cp = new Permissions(Page::getCurrentPage());
		if($cp->canDeletePage()) $this->set('canDeletePage', true);
		
		$u = new User();
		if ($u->isLoggedIn()) {
			$this->set('loggedIn', true);
		}

	}

 
 	function get_public_forums()
	{
		$publicForums = array();
		
		// get array of values in db
		$db = Database::connection();
		
		$res = $db->GetAll("select * from btWebliForums where anonymous_posts = ?", 1);
	
		if($res) {
			foreach($res as $r){	
					$publicForums[] = $r['cID'];	
			}
		}
		
		return $publicForums;
	}

	//
	//function get_forum_attributes()
	//{
	//
	//	$atSet = AttributeSet::getByHandle('forums');
	//	$atKeys = $atSet->getAttributeKeys();
	//	
	//	$banAtt = array('Forum Post', 'Forum Category', 'Pin Forum Post', 'Forum Image', 'Forum Email Address', 'Forum Name', 'Forum Post Approved'); 
	//	
	//	foreach($atKeys as $ak) {
	//		if(!in_array($ak->getAttributeKeyName(), $banAtt)) {
	//			$forumAttributes[$ak->getAttributeKeyID()] = $ak->getAttributeKeyName();
	//		}
	//	}
	//	
	//	return $forumAttributes;
	//}
	//

	function get_saved_settings()
	{
		// get array of values in db
		$db = Database::connection();
		
		$res = $db->GetRow("select * from btWebliForums where cID = ?", Page::getCurrentPage()->getCollectionParentID());
		
		if(!$res) $res = $db->GetRow("select * from btWebliForums where cID = ?", 0);
		
		return $res;
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
		
	
	function get_forum_pages()
	{
		$fpl = new PageList();
		$fpl->sortByDisplayOrder();
		$fpl->filterByAttribute('forum_category', true);
		$forumPages = $fpl->get();
		
		return $forumPages;
	}

	
	function action_new_forum_post()
	{
		$vf = Core::make('helper/validation/form');
        $vf->setData($_POST['ccm_token']);
        $vf->addRequiredToken('new_forum_post');
        
		if ($vf->test()) {
			
			$th = Core::make('helper/text');
			
			$parentPage = Page::getByID($_POST['forumSelect']);
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
				'cName' => $_POST['forumTitle'],
				'cDescription' => $th->makenice($_POST['ForumPost']),
				'cHandle ' => $th->sanitizeFileSystem($_POST['forumTitle'], $leaveSlashes=false),
				'uID' => $owner
				), $template);
			
			
			// set attributes for Anonymous users
			if($_POST['forumEmail']) $newPage->setAttribute('forum_email', $_POST['forumEmail'] );
			if($_POST['forumName']) $newPage->setAttribute('forum_name', $_POST['forumName'] );
			
			if($_POST['forumPost']) $newPage->setAttribute('forum_post', $_POST['forumPost'] );
			if($_POST['forumTags']) $newPage->setAttribute('forum_tags', $_POST['forumTags'] );
			
			// get moderator approval attribute for blog category
			$db = Database::connection();
			$res = $db->GetRow("select * from btWebliForums where cID = ?", $_POST['forumSelect']);
			
			if($res['mod_approval']){
				$newPage->setAttribute('forum_post_approved', 0 );
			} else {
				$newPage->setAttribute('forum_post_approved', 1 );
			}
		
			// save tags
			$ak = CollectionAttributeKey::getByHandle('tags');
			$ak->saveAttributeForm($newPage);

			// Save optional Attributes
			$settings = $this->get_saved_settings();
			
			if($settings['optional_attributes']) {
				$optAtts = unserialize($settings['optional_attributes']);
				
				foreach($optAtts as $ot){	
					$ak = CollectionAttributeKey::getByID($ot);
					$ak->saveAttributeForm($newPage);
				}	
			}
			
			$newPage->reindex();
		
		} else {
			die("Access Denied.");
		}
		
		$this->redirect($newPage->getCollectionPath());
	}

	
		function action_edit_forum_post()
	{
		$vf = Core::make('helper/validation/form');
        $vf->setData($_POST['ccm_token']);
        $vf->addRequiredToken('edit_forum_post');
        
		if ($vf->test()) {
			$th = Core::make('helper/text');
		
			$editPage = Page::getCurrentPage();
			
			$settings = $this->get_saved_settings();
			
			
			$u = new User();
			
			// if user not loggged in make admin page owner
			if($u->isLoggedIn()) {
				$owner = $u->getUserID();
			} else {
				$owner = 1;	
			}
			
			$editPage->update(array('cName' => $_POST['forumTitle']));

			// set Page Attribute for Anonymous users
			if($_POST['forumEmail']) $editPage->setAttribute('forum_email', $_POST['forumEmail'] );
			if($_POST['forumName']) $editPage->setAttribute('forum_name', $_POST['forumName'] );
			
			// Save forum post
			if($settings['rich_text']){
				if($_POST['forumPost']) $editPage->setAttribute('forum_post', $_POST['forumPost'] );
			} else { 
				if($_POST['forumPost']) $editPage->setAttribute('forum_post', $th->sanitize($_POST['forumPost']) );
			}

			// save tags
			$ak = CollectionAttributeKey::getByHandle('tags');
			$ak->saveAttributeForm($editPage);			

			// Save optional Attributes
			if($settings['optional_attributes']) {
				$optAtts = unserialize($settings['optional_attributes']);
				
				foreach($optAtts as $ot){	
					$ak = CollectionAttributeKey::getByID($ot);
					$ak->saveAttributeForm($editPage);
				}	
			}
			
			// check if we need to move the page
			if($_POST['forumSelect'] && $_POST['forumSelect'] != $editPage->getCollectionParentID()){	
				$category = \Page::getByID($_POST['forumSelect']);
				$editPage->move($category);
			}
				
				$editPage->reindex();
				
		} else {
			die("Access Denied.");
		}
		
		$this->redirect($editPage->getCollectionPath());
	}
	

	function action_set_pin()
	{		
		$vf = Core::make('helper/validation/form');
        $vf->setData($_POST['ccm_token']);
        $vf->addRequiredToken('set_pin');
        
		if ($vf->test()) {
			Page::getCurrentPage()->setAttribute('forum_pin', 1 );
		} else {
			die("Access Denied.");
		}
		
		$this->redirect(Page::getCurrentPage()->getCollectionPath());	
	}

	
	function action_set_unpin()
	{	
		$vf = Core::make('helper/validation/form');
        $vf->setData($_POST['ccm_token']);
        $vf->addRequiredToken('set_unpin');
        
		if ($vf->test()) {
			Page::getCurrentPage()->setAttribute('forum_pin', 0 );
		} else {
			die("Access Denied.");
		}		
		$this->redirect(Page::getCurrentPage()->getCollectionPath());	
	}	
 
 
 	function action_delete_page()
	{
		$cp = new Permissions(Page::getCurrentPage());

		$vf = Core::make('helper/validation/form');
        $vf->setData($_POST['ccm_token']);
        $vf->addRequiredToken('delete_page');
        
		if ($vf->test()) {		
			if($cp->canDeletePage()) {
				Page::getCurrentPage()->delete();
			}
		} else {
			die("Access Denied.");
		}
		
		$parent = Page::getCurrentPage()->getCollectionParentID();
		$this->redirect(Page::getCollectionPathFromID($parent));	
	}

	
	
	function action_approve_page()
	{
		$vf = Core::make('helper/validation/form');
        $vf->setData($_POST['ccm_token']);
        $vf->addRequiredToken('approve_page');
        
		if ($vf->test()) {
			Page::getCurrentPage()->setAttribute('forum_post_approved', 1);
		} else {
			die("Access Denied.");
		}
		
		$this->redirect(Page::getCurrentPage()->getCollectionPath());	
	}


	function action_unapprove_page()
	{
		$vf = Core::make('helper/validation/form');
        $vf->setData($_POST['ccm_token']);
        $vf->addRequiredToken('unapprove_page');
        
		if ($vf->test()) {
			Page::getCurrentPage()->setAttribute('forum_post_approved', 0);
		} else {
			die("Access Denied.");
		}
		
		$this->redirect(Page::getCurrentPage()->getCollectionPath());
		
	}
	
	
	function save($args)
	{
		
		parent::save($args);
	}
	
}
?>