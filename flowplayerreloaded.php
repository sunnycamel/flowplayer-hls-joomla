<?php
/**
 * @version             $Id: flowplayerreloaded.php revision date tushev $
 * @package             Joomla
 * @subpackage  System
 * @copyright   Copyright (C) S.A. Tushev, 2011. All rights reserved.
 * @license     GNU GPL v2.0
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */
 

defined( '_JEXEC' ) or die( 'Restricted access' );
jimport('joomla.plugin.plugin');

define( '_URL_FPP_', JURI::base().'plugins/system/flowplayerreloaded/flowplayer_r/' );

class plgSystemFlowplayerReloaded extends JPlugin{

     function plgSystemFlowplayerReloaded( &$subject, $params )
     {
	  parent::__construct( $subject, $params );
     }

     function onAfterRoute() {
	  $document = &JFactory::getDocument();
	  $document->setGenerator($document->getGenerator().'; FlowPlayerReloaded 3.0 by tushev.org');
		
	  $juri = &JFactory::getURI();
	  if(stripos($juri->getPath(),'/administrator/')!==false) return;
		
	  JHtml::_('behavior.framework');
	  JHTML::_('script','system/modal.js', false, true);
	  JHTML::_('stylesheet','system/modal.css', array(), true);
	  $document->addScriptDeclaration(	"window.addEvent('domready', function(){
        		SqueezeBox.initialize();
        		SqueezeBox.assign($$('a[href^=#fprrpopup]'),{parse: 'rel'});
		});"		);
		
	  $document->addScript(_URL_FPP_ . 'player/jquery-1.11.2.min.js');
	  $document->addScript(_URL_FPP_ . 'player/flowplayer.min.js');
	  $document->addScript(_URL_FPP_ . 'player/hls.js');
	  $document->addScript(_URL_FPP_ . 'player/flowplayer.hlsjs.js');
	  $document->addStyleSheet(_URL_FPP_ . 'player/skin/functional.css');
     }

     function onAfterRender()
     {					
	  $juri = &JFactory::getURI();
	  if(stripos($juri->getPath(),'/administrator/')!==false) return;
		
	  $text = JResponse::getBody();
	  if ( stripos( $text, 'flowplayer' ) !== false ) 
	  {
	       $result = $this->processText($text);
	       if($result!==false)
	       {
		    JResponse::setBody($result);
	       }
	  }

     }

     function processText($text)
     {
	  $regex = '/{\s*flowplayer(\s+.+)?\s*}\s*([^\s]+.*[^\s]+)\s*{\s*\/\s*flowplayer\s*}/i';
	  //$regex = '/{\s*flowplayer(\s+size\=([0-9]+)x([0-9]+))?(\s+img=([\/\:@\?#%\.\,\(\)\w-=]+))?(\s+(autoplay|noautoplay))?\s*}\s*([^\s]*)\s*{\s*\/\s*flowplayer\s*}/i';
	  //'/{\s?flowplayer(\ssize\=([0-9]+)x([0-9]+))?\s?}\s*(.*?){\s?\/\s?flowplayer\s?}/i';		//{\s?flowplayer(\ssize\=([0-9]+)x([0-9]+))?(\s?img=[/:@\?#-_=\d])?\s?}\s*([^\s]*)\s*{\s?\/\s?flowplayer\s?}
	  preg_match_all( $regex, $text, $matches);
	  $count = count( $matches[0] );
		
	  //there are matches
	  if ( $count ) 
	  {
	       //do replacement
	       $text = preg_replace_callback( $regex, array($this, 'buildHtml4Instance'), $text );
	       return $text;			
	  }
	  else return false;
		
     }
	
     /**
      * Get the url of video and build the div
      * @param array $matches A array with regex content.
      */
     protected function buildHtml4Instance(&$matches)
     {
	  /* $p 		= $this->parseTagParameters($matches[1]); */
	  $filename 	= strip_tags($matches[2]);
		
	  /* $width	= (isset($p['width']))?$p['width']:$this->params->get( 'width' ); */
	  /* $height	= (isset($p['height']))?$p['height']:$this->params->get( 'height' ); */
	  /* $center	= (isset($p['center']))?(($p['center'])?true:false):$this->params->get( 'center' ); */

	  $width	= $this->params->get( 'width' );
	  $height	= $this->params->get( 'height' );
	  $center	= $this->params->get( 'center' );
		
	  $video = '<div class="flowplayer" data-swf="flowplayer.swf" data-ratio="0.4167" style="width: '
		    . $width. 'px; height: ' . $height . 'px;	display: block;" >' .
		    '<video>' .
		    '<source type="application/x-mpegurl" ' .
		    'src="'. $filename . '">' .
		    '</video>' .
		    '</div>';
          
	  return $video;
     }

     /* protected function parseTagParameters($string) */
     /* { */
     /* 	  preg_match_all("/\s+([a-zA-Z]+)=([^\s]+)/i", $string, $matches, PREG_SET_ORDER); */
     /* 	  foreach($matches as $i)  */
     /* 	       $p[strtolower($i[1])] = $i[2]; */
     /* 	  if($p) { */
     /* 	       return $p;} */
     /* } */
}

