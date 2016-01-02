<?php      
namespace Concrete\Package\WebliForums;
use Package;
use BlockType;
use BlockTypeSet;
use Loader;
use PageTemplate;
use PageType;
use Page;
use SinglePage;
use Group;
use Core;
use Events;
use URL;
use Permissions;
use Application\Service\UserInterface\Menu;
use Config;

use \Concrete\Core\Page\Type\Type as CollectionType;

// for adding Composer Layouts
use \Concrete\Core\Page\Type\Composer\FormLayoutSet as ComposerSet;
use \Concrete\Core\Page\Type\Composer\FormLayoutSetControl as ComposerSetControl;
use \Concrete\Core\Page\Type\Composer\OutputControl as ComposerOutputControl;
use \Concrete\Core\Page\Type\Composer\Control\Type\Type as ComposerControlType;

// for adding attribute sets
use \Concrete\Core\Attribute\Key\Category as AttributeKeyCategory;

// for adding attributes
use Concrete\Core\Attribute\Key\CollectionKey as CollectionAttributeKey;
use \Concrete\Core\Attribute\Type as AttributeType;
use Concrete\Attribute\Select as SelectAttributeTypeOption;


class Controller extends Package
{

	protected $pkgHandle = 'webli_forums';
	protected $appVersionRequired = '5.7.4';
    protected $pkgVersion = '0.5';

     public function getPackageDescription()
	 {
          return t("Forums");
     }

     public function getPackageName()
	 {
          return t("Forums");
     }

	 
	public function on_start()
	{
		
		$ihm = Loader::helper('concrete/ui/menu');
				
		$p = new Permissions(Page::getByPath('/dashboard/forums/'));
		if($p->canRead()) {
			$ihm->addPageHeaderMenuItem('forums', 'webli_forums', array('icon' => '', 'label' => t('Forums'), 'position' => 'right', 'href' => URL::to('/dashboard/forums'), 'linkAttributes' => array('style' => 'padding:0 5px;width:auto;')));
		}
				
		Events::addListener('on_page_add', function($e){
			$page = $e->getPageObject();
			
			if($page->getCollectionTypeHandle() == 'forum_post') {
				
				$settings =	$this->get_saved_settings($page->getCollectionParentID());
				
				if($settings['notification']){

                    if (Config::get('concrete.email.webli_forum.address') && strstr(Config::get('concrete.email.webli_forum.address'), '@')) {
                        $formFormEmailAddress = Config::get('concrete.email.webli_forum.address');
                    } else if (Config::get('concrete.email.default.address') && strstr(Config::get('concrete.email.default.address'), '@')) {
                        $formFormEmailAddress = Config::get('concrete.email.default.address');
                    } else {
                        $adminUserInfo = UserInfo::getByID(USER_SUPER_ID);
                        $formFormEmailAddress = $adminUserInfo->getUserEmail();
                    }

					$mh = Core::make('helper/mail');
					$mh->to($settings['email_addresses']);
					$mh->from($formFormEmailAddress);
					
					$parentPage = Page::getByID($page->getCollectionParentID());
									
					$mh->addParameter('forumName', $parentPage->getCollectionName());
					$mh->addParameter('forumPath', BASE_URL . DIR_REL . $parentPage->getCollectionPath());

					$mh->load('forum_notification', 'webli_forums');
					$mh->setSubject(t('New Forum Post to %s', $parentPage->getCollectionName()));
					@$mh->sendMail();
				}
		
			}	
		});
	}

	
	function get_saved_settings($cID)
	{
		// get array of values in db
		$db = Loader::db();
		
		$res = $db->GetRow("select * from btWebliForums where cID = ?", $cID);
		
		if(!$res) $res = $db->GetRow("select * from btWebliForums where cID = ?", 0);
		
		return $res;
	}	

	
    public function upgrade()
	{
        $pkg = $this;
        parent::upgrade();

// This is all for beta upgrades, remove from finished version		
		if(!BlockType::getByHandle('webli_forum_search')){
			BlockType::installBlockTypeFromPackage('webli_forum_search', $pkg);
		}
		
		if(!BlockType::getByHandle('webli_forum_archive')){
			BlockType::installBlockTypeFromPackage('webli_forum_archive', $pkg);
		}
		
		if(!BlockType::getByHandle('webli_forum_tags')){
			BlockType::installBlockTypeFromPackage('webli_forum_tags', $pkg);
		}

		$attribute = CollectionAttributeKey::getByHandle('tags');
		if ( !is_object($attribute)) {
			$att = AttributeType::getByHandle('select');
			// add to attribute set
			CollectionAttributeKey::add($att, array('akHandle' => 'tags', 'akName' => t('Tags'), 'akIsSearchableIndexed' => true),$pkg)->setAttributeSet('forums'); 
		}

		
		$attribute = CollectionAttributeKey::getByHandle('forum_image');
		if ( !is_object($attribute)) {
			$att = AttributeType::getByHandle('image_file');
			// add to attribute set
			CollectionAttributeKey::add($att, array('akHandle' => 'forum_image', 'akName' => t('Forum Image')),$pkg)->setAttributeSet('forums'); 
			$addAttribute = CollectionAttributeKey::getByHandle('forum_image');
		}

		
		$attribute = CollectionAttributeKey::getByHandle('forum_expiration_date');
		if ( is_object($attribute)) {
			$attribute->delete();
		}

		$db = Loader::db();
	
		$db->Execute('update btWebliForums set
			forum_search_block = ?,
			forum_archive_block = ?,
			forum_tags_block = ?',
			array(1,1,1));		
    }
				
				
	public function install()
	{
         
		$pkg = parent::install();

		// Dashboard Page
		$sp = SinglePage::add('/dashboard/forums', $pkg);
		if (is_object($sp)) {
			$sp->update(array('cName'=>t('Forums'), 'cDescription'=>t('Forums Dashboard')));
		}
		
		// Add Sidebar block set
		BlockTypeSet::add('forums', 'Forums', $pkg);

		// Add forum moderator user group
		$forumGroup = Group::getByName('Forum Moderators');
		if (!is_object($authorGroup)){
			Group::add('Forum Moderators', 'Forum Moderators, delete, edit, approve');
		}
		
		// install blocks	
		BlockType::installBlockTypeFromPackage('webli_forum_post', $pkg);
		BlockType::installBlockTypeFromPackage('webli_forum_list', $pkg);
		BlockType::installBlockTypeFromPackage('webli_forum_search', $pkg);
		BlockType::installBlockTypeFromPackage('webli_forum_archive', $pkg);
		BlockType::installBlockTypeFromPackage('webli_forum_tags', $pkg);

		
		// Add Collection Attribute Set
		$akCat = AttributeKeyCategory::getByHandle('collection');
		$akCat->setAllowAttributeSets(AttributeKeyCategory::ASET_ALLOW_SINGLE);
		$akCatSet = $akCat->addSet('forums', t('Forums'),$pkg);		

		
		// Add Collection Attributes
		$attribute = CollectionAttributeKey::getByHandle('forum_post');
		if ( !is_object($attribute)) {
			$att = AttributeType::getByHandle('textarea');
			// add to attribute set
			CollectionAttributeKey::add($att, array('akHandle' => 'forum_post', 'akName' => t('Forum Post'), 'akIsSearchableIndexed' => true, 'akTextareaDisplayMode' => 'rich_text'),$pkg)->setAttributeSet($akCatSet); 
		}
		
		$attribute = CollectionAttributeKey::getByHandle('forum_category');
		if ( !is_object($attribute)) {
			$att = AttributeType::getByHandle('boolean');
			// add to attribute set
			CollectionAttributeKey::add($att, array('akHandle' => 'forum_category', 'akName' => t('Forum Category')),$pkg)->setAttributeSet($akCatSet); 
		}
		
		$attribute = CollectionAttributeKey::getByHandle('forum_email');
		if ( !is_object($attribute)) {
			$att = AttributeType::getByHandle('text');
			// add to attribute set
			CollectionAttributeKey::add($att, array('akHandle' => 'forum_email', 'akName' => t('Forum Email Address'), 'akIsSearchableIndexed' => true),$pkg)->setAttributeSet($akCatSet); 
		}
	
		$attribute = CollectionAttributeKey::getByHandle('forum_name');
		if ( !is_object($attribute)) {
			$att = AttributeType::getByHandle('text');
			// add to attribute set
			CollectionAttributeKey::add($att, array('akHandle' => 'forum_name', 'akName' => t('Forum Name'), 'akIsSearchableIndexed' => true),$pkg)->setAttributeSet($akCatSet); 
		}

		$attribute = CollectionAttributeKey::getByHandle('forum_pin');
		if ( !is_object($attribute)) {
			$att = AttributeType::getByHandle('boolean');
			// add to attribute set
			CollectionAttributeKey::add($att, array('akHandle' => 'forum_pin', 'akName' => t('Pin Forum Post')),$pkg)->setAttributeSet($akCatSet); 
		}

		$attribute = CollectionAttributeKey::getByHandle('forum_post_approved');
		if ( !is_object($attribute)) {
			$att = AttributeType::getByHandle('boolean');
			// add to attribute set
			CollectionAttributeKey::add($att, array('akHandle' => 'forum_post_approved', 'akName' => t('Forum Post Approved')),$pkg)->setAttributeSet($akCatSet); 
		}

		$attribute = CollectionAttributeKey::getByHandle('tags');
		if ( !is_object($attribute)) {
			$att = AttributeType::getByHandle('select');
			// add to attribute set
			CollectionAttributeKey::add($att, array('akHandle' => 'tags', 'akName' => t('Tags'), 'akIsSearchableIndexed' => true),$pkg)->setAttributeSet($akCatSet); 
		}

		$attribute = CollectionAttributeKey::getByHandle('forum_image');
		if ( !is_object($attribute)) {
			$att = AttributeType::getByHandle('image_file');
			// add to attribute set
			CollectionAttributeKey::add($att, array('akHandle' => 'forum_image', 'akName' => t('Forum Image')),$pkg)->setAttributeSet($akCatSet); 
			$addAttribute = CollectionAttributeKey::getByHandle('forum_image');
		}

		// Add top level Forums Page
		$forumPage = \Page::getByPath('/forums');
        if (!is_object($forumPage) || $forumPage->cID == null) {
			$parentPage = \Page::getByID(1);
			$pageType = \PageType::getByHandle('right_sidebar');
			$template = \PageTemplate::getByHandle('right_sidebar');
			$forumsPage = $parentPage->add($pageType, array(
				'cName' => 'Forums',
				'cDescription' => 'Top Level Forums Page',
				'cHandle ' => 'forums'
			), $template);
			
			//Add forum_category page attribute
			$forumsPage->setAttribute('forum_category', 1 );
		}

		
		// Add top Forum Search Results Page
 		$forumSearchPage = \Page::getByPath('/forum-search');
        if (!is_object($forumSearchPage) || $forumSearch->cID == null) {
			$parentPage = \Page::getByID(1);
			$pageType = \PageType::getByHandle('right_sidebar');
			$template = \PageTemplate::getByHandle('right_sidebar');
			$forumSearchPage = $parentPage->add($pageType, array(
				'cName' => 'Forum Search',
				'cDescription' => 'Forum Search Page',
				'cHandle ' => 'forum-search'
			), $template);
	
		$forumSearchPage->setAttribute('exclude_nav', 1 );
	
        }

	
		// Add Forum Post Page Template
		if (!is_object(PageTemplate::getByHandle('forum_post'))){
			$pageTemplate = PageTemplate::add('forum_post', 'Forum Post', 'landing.png', $pkg);	
		}

		// Add Forum Post Page Type
		if (!is_object(PageType::getByHandle('forum_post'))){
			$data = array(
				'handle' => 'forum_post',
				'name' => 'Forum Post',
				'ptLaunchInComposer' => true,
                'ptIsFrequentlyAdded' => true,
				'defaultTemplate' => PageTemplate::getByHandle('forum_post'),
				'allowedTemplates' => 'C',
				'templates' => array(PageTemplate::getByHandle('forum_post')
				 ),	
			);
			
			$pt = PageType::add($data, $pkg);
		}
		
		/* Get blog_post page template */
		$pageType = \PageType::getByHandle('forum_post');
		$ctTemplate = $pageType->getPageTypeDefaultPageTemplateObject();
		$forumPostTemplate = $pageType->getPageTypePageTemplateDefaultPageObject($ctTemplate);
	
		/* Add Composer Layouts */
		$basics = $pageType->addPageTypeComposerFormLayoutSet('Basics', 'Basic Info');
		$post = $pageType->addPageTypeComposerFormLayoutSet('Forum Post', 'Forum Post');			
		
		/* Add Built in Properties */
		$cct = ComposerControlType::getByHandle('core_page_property');
		
		/* Post Title */
		$control = $cct->getPageTypeComposerControlByIdentifier('name');
		$control->addToPageTypeComposerFormLayoutSet( $basics );

		/* Post Slug */
		$control = $cct->getPageTypeComposerControlByIdentifier('url_slug');
		$control->addToPageTypeComposerFormLayoutSet( $basics );
		
		/* Post Publish Location */
		$control = $cct->getPageTypeComposerControlByIdentifier('publish_target');
		$control->addToPageTypeComposerFormLayoutSet( $basics );

		/* Post Date */
		$control = $cct->getPageTypeComposerControlByIdentifier('date_time');
		$control->addToPageTypeComposerFormLayoutSet( $basics);

		/* Post Author */
		$control = $cct->getPageTypeComposerControlByIdentifier('user');
		$control->addToPageTypeComposerFormLayoutSet( $basics);
		
		/* Add Attributes */
		$cct = ComposerControlType::getByHandle('collection_attribute');

		/* Forum Pin */
		$attributeId = CollectionAttributeKey::getByHandle('forum_pin')->getAttributeKeyID();
		$control = $cct->getPageTypeComposerControlByIdentifier($attributeId);
		$control->addToPageTypeComposerFormLayoutSet($post);	

		/* Forum Post Approved */
		$attributeId = CollectionAttributeKey::getByHandle('forum_post_approved')->getAttributeKeyID();
		$control = $cct->getPageTypeComposerControlByIdentifier($attributeId);
		$control->addToPageTypeComposerFormLayoutSet($post);	
			
		/* Forum Post */
		$attributeId = CollectionAttributeKey::getByHandle('forum_post')->getAttributeKeyID();
		$control = $cct->getPageTypeComposerControlByIdentifier($attributeId);
		$control->addToPageTypeComposerFormLayoutSet($post);

		/* Forum Image */
		$attributeId = CollectionAttributeKey::getByHandle('forum_image')->getAttributeKeyID();
		$control = $cct->getPageTypeComposerControlByIdentifier($attributeId);
		$control->addToPageTypeComposerFormLayoutSet($post);

		/* Forum Tags */
		$attributeId = CollectionAttributeKey::getByHandle('tags')->getAttributeKeyID();
		$control = $cct->getPageTypeComposerControlByIdentifier($attributeId);
		$control->addToPageTypeComposerFormLayoutSet($post);
		

		/* Add default Blocks to page template */
		$ctTemplate = $pageType->getPageTypeDefaultPageTemplateObject();
		$forumPostTemplate = $pageType->getPageTypePageTemplateDefaultPageObject($ctTemplate);
				
		//Add exclude_nav page attributeto Forum Post Template
		$forumPostTemplate->setAttribute('exclude_nav', 1 );

			
		// Get Forum Category Page
		$forumCategoryPage = Page::getByPath('/forums');
	
	
		//Add forum_category page attribute
		$forumCategoryPage->setAttribute('forum_category', 1 );

	
		// Get Forum Search Page
		$forumSearchPage = Page::getByPath('/forum-search');				

		
		//Add exclude_nav page attribute
		$forumSearchPage->setAttribute('exclude_nav', 1 );			
		
			
		// Install Blocks
		
		//install Forum Post Block
		$forumPost = BlockType::getByHandle('webli_forum_post');
		$forumPostData = array();
		
		$forumCategoryPage->addBlock($forumPost, 'Main', $forumPostData);
		
		//install forum post block to forum_post template
		$forumPostTemplate->addBlock($forumPost, 'Forum Post', $forumPostData);
		
		
		//install Forum List Block on Forums top level page
		$forumList = BlockType::getByHandle('webli_forum_list');
		$forumListData = array();
		$forumListData['num'] = 25;
		$forumListData['paginate'] = 1;
		$forumListData['cParentID'] = $forumCategoryPage->getCollectionID();
		$forumListData['orderBy'] = 'chrono_desc';
		$forumListData['use_content'] = 1;
		$forumListData['truncateSummaries'] = 1;
		$forumListData['truncateChars'] = 200;
		$forumListData['display_author'] = 1;
		$forumListData['includeDate'] = 1;
		$forumListData['includeName'] = 1;
		$forumListData['includeDescription'] = 1;
		$forumListData['date_format'] = 'l F j, Y g:ia';
		$forumListData['forum_pin'] = 1;
		$forumListData['forumReplies'] = 3;
		$forumListData['thumb_width'] = 250;
		$forumListData['thumb_height'] = 150;
		$forumListData['crop'] = 1;
		$forumListData['noResultsMessage'] = 'No Forum Posts available to view.';
		
		$forumCategoryPage->addBlock($forumList, 'Main', $forumListData);
		
		// Install Forum Search Block on forum_post Page template
		$forumSearch = BlockType::getByHandle('webli_forum_search');
		$forumSearchData = array();
		$forumSearchData['title'] = 'Forum Search';
		$forumSearchData['postTo_cID'] = $forumSearchPage->getCollectionID();
		$forumSearchData['baseSearchPath'] = 'PARENT';
		
		$forumPostTemplate->addBlock($forumSearch, 'Sidebar', $forumSearchData);
		
		//install Forum Search Block Forums forum_category Page
		$forumSearchData = array();
		$forumSearchData['title'] = 'Forum Search';
		$forumSearchData['postTo_cID'] = $forumSearchPage->getCollectionID();
		$forumSearchData['baseSearchPath'] = $forumCategoryPage->getCollectionPath();
		
		$forumCategoryPage->addBlock($forumSearch, 'Sidebar', $forumSearchData);

		//install Forum Search Block on Forum Search Page
		$forumSearchData = array();
		$forumSearchData['title'] = 'Forum Search';
		$forumSearchData['buttonText'] = 'Search';
		$forumSearchData['baseSearchPath'] = $forumCategoryPage->getCollectionPath();
		
		$forumSearchPage->addBlock($forumSearch, 'Main', $forumSearchData);		
		
		
		//install Forum Archive Block on forum_post template
		$forumArchive = BlockType::getByHandle('webli_forum_archive');
		$forumArchiveData = array();
		$forumArchiveData['title'] = 'Forum Archive';
		$forumArchiveData['cParentID'] = 'PARENT';
		
		$forumPostTemplate->addBlock($forumArchive, 'Sidebar', $forumArchiveData);
		
		//install Forum Archive Block 
		$forumArchiveData = array();
		$forumArchiveData['title'] = 'Forum Archive';
		$forumArchiveData['cParentID'] = $forumCategoryPage->getCollectionID();
	
		$forumCategoryPage->addBlock($forumArchive, 'Sidebar', $forumArchiveData);		
		$forumSearchPage->addBlock($forumArchive, 'Sidebar', $forumArchiveData);
		

		//install Forum Tags Block on forum_post template
		$forumTags = BlockType::getByHandle('webli_forum_tags');
		$forumTagsData = array();
		$forumTagsData['title'] = 'Forum Tags';
		$forumTagsData['cParentID'] = 'PARENT';
		$forumTagsData['postTo_cID'] = $forumSearchPage->getCollectionID();
		$forumTagsData['baseSearchPath'] = $forumCategoryPage->getCollectionPath();		
		$forumTagsData['min_height'] = 15;
		$forumTagsData['max_height'] = 30;

		$forumPostTemplate->addBlock($forumTags, 'Sidebar', $forumTagsData);
		
		
		//install Forum Tags Block 
		$forumTagsData = array();
		$forumTagsData['title'] = 'Forum Tags';
		$forumTagsData['cParentID'] = $forumCategoryPage->getCollectionID();
		$forumTagsData['postTo_cID'] = $forumSearchPage->getCollectionID();
		$forumTagsData['baseSearchPath'] = $forumCategoryPage->getCollectionPath();		
		$forumTagsData['min_height'] = 15;
		$forumTagsData['max_height'] = 30;
		
		$forumCategoryPage->addBlock($forumTags, 'Sidebar', $forumTagsData);		
		$forumSearchPage->addBlock($forumTags, 'Sidebar', $forumTagsData);
				
			
		//install Conversations block to forum_post template
		$conversations = BlockType::getByHandle('core_conversation');
		$conversationsData = array();
		$conversationsData['attachmentsEnabled'] = 0;
		$conversationsData['addMessageLabel'] = t('Add a Comment');
		$conversationsData['itemsPerPage'] = 25;
		$conversationsData['enablePosting'] = 1;
		$conversationsData['enableCommentRating'] = 1;
		$conversationsData['paginate'] = 1;
		$conversationsData['displayMode'] = 'threaded';
		$conversationsData['displayPostingForm'] = 'bottom';
		
		$forumPostTemplate->addBlock($conversations, 'Forum Replies', $conversationsData);
	

		$db = Loader::db();
		
		// insert default page category values
			$db->Execute('insert into btWebliForums (
				cID,
				display_title,
				display_author,
				display_date,
				date_format,
				display_tags,
				enable_comments,
				enable_breadcrumb,
				crop_image,
				display_image,
				image_height,
				image_width,
				display_avatars,
				forum_search_block,
				forum_archive_block,
				forum_tags_block,
				page_template,
				page_type
				) values (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)',
				array(0,1,1,1,'l F j, Y g:ia',1,1,1,0,1,350,250,1,1,1,1,$pageTemplate->getPageTemplateID(),$pageType->getPageTypeID()));			


		// Add Sample Forum Post
 		$forumsPage = \Page::getByPath('/forums');
        if (is_object($forumsPage) || $forumsPage->cID != null) {
			$pageType = \PageType::getByHandle('forum_post');
			$template = \PageTemplate::getByHandle('forum_post');
			$samplePost = $forumsPage->add($pageType, array(
				'cName' => 'My First Forum Post',
				'cDescription' => 'First Forum Post',
				'cHandle ' => 'first-forum-post'
			), $template);
	
			$samplePost->setAttribute('forum_post', '
				<p>Hey, Congratulations, you have installed Forums for Concrete5.  Forums will give visitors to your site frontend
				editing capabilities to add Forum Messages and reply to existing messages.</p>
				<p>Administrators have access to the Forums Dashboard Page to customize and manage your forums.</p>
				<p>So get your forum started and if you have any comments or questions visit <a href="http://forums.webli.us" target="_blank">forums.webli.us</a></p>');
		
			$samplePost->setAttribute('forum_post_approved', 1);
			$samplePost->setAttribute('tags', array('Forums', 'Frist Message'));	
		}

		
		$cms = Core::make('app');
        $cms->clearCaches();
		
	}
	
	     
     public function uninstall()
	 {
	    
	    // cleanup package on uninstall
	    $pkg = parent::uninstall();
	    
		// drop database table
		$db = Loader::db();
		$db->Execute('drop table if exists btWebliForums');
		$db->Execute('drop table if exists btWebliForumPost');
		$db->Execute('drop table if exists btWebliForumList');
		$db->Execute('drop table if exists btWebliForumArchive');
		$db->Execute('drop table if exists btWebliForumSearch');
		$db->Execute('drop table if exists btWebliForumTags');
		
		$group = Group::getByName('Forum Moderators');
		$group->delete();
		
		Page::getByPath('/forums')->delete();
		Page::getByPath('/forum-search')->delete();
		
		$cms = Core::make('app');
        $cms->clearCaches();		

	}  
}