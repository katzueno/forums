<?php
namespace Concrete\Package\WebliForums\MenuItem\Forums;

use Concrete\Core\Application\UserInterface\Menu\Item\Controller as MenuItemController;
use Concrete\Core\Controller\AbstractController;
use HtmlObject\Element;
use HtmlObject\Link;
use Config;

class Controller extends MenuItemController {
      
   public function getMenuItemLinkElement()
   {
      $a = new Link();
      $a->setValue('');
      if ($this->menuItem->getIcon()) {
         $icon = new Element('i');
         $icon->addClass('fa fa-' . $this->menuItem->getIcon());
         $a->appendChild($icon);
      }
    
      if ($this->menuItem->getLink()) {
         $a->href($this->menuItem->getLink());
      }
    
      foreach($this->menuItem->getLinkAttributes() as $key => $value) {
         $a->setAttribute($key, $value);
      }
    
      // Set styling for accessiblity options
      if( Config::get('concrete.accessibility.toolbar_large_font')){
         $spacing = 'padding-top: 15px';
         $height = 'line-height:17px;';      
      } else {
         $spacing = 'padding: 16px 5px;';
         $height = 'line-height:14px;';
      }
      
      $wbTitle = new Element('div');
      $wbTitle->style($height . $spacing);
      $wbTitle->addClass('wb-fourms')->setValue($this->menuItem->getLabel());
      $a->appendChild($wbTitle);
      
      return $a;
   }
}
?>