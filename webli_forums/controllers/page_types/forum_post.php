<?php
namespace Concrete\Package\WebliForums\Controller\PageType;
use Concrete\Core\Page\Controller\PageTypeController;
use Page;
use Loader;
use User;
use UserInfo;
use Group;
 
class forumPost extends PageTypeController
{

	public function on_start()
	{
		$category = Page::getByID(Page::getCurrentPage()->getCollectionParentID())->getCollectionID();
		
		$db = Loader::db();
		$display = $db->GetRow("select * from btWebliForums where cID = ?", $category);
		
		if(!$display) $display = $db->GetRow("select * from btWebliForums where cID = ?", 0);
		
		$this->set('display', $display);
		
		if($display['share_this']) {
			$al = \Concrete\Core\Asset\AssetList::getInstance();
			$al->register('javascript', 'share-this', $display['share_this_script']);
		
			$this->requireAsset('javascript', 'share-this');
		}
	}

	
    public function view()
    {
        $this->set('forumAdmin', $this->forumAdmin());
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
 
}
