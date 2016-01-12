<?php
/**
 * @version             $Id: joomlahls.php revision 1.0
 * @package             Joomla
 * @subpackage  System
 * @copyright   Copyright (C) Sun Peng, 2020. All rights reserved.
 * @license     GNU GPL v2.0
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */
 

defined( '_JEXEC' ) or die( 'Restricted access' );
jimport('joomla.plugin.plugin');

define( '_URL_FPP_', JURI::base().'plugins/system/joomlahls/' );

class plgSystemJoomlaHLS extends JPlugin{

     function plgSystemJoomlaHLS( &$subject, $params )
     {
	  parent::__construct( $subject, $params );
     }

     function onAfterRoute() {
	  $document = &JFactory::getDocument();
	  $document->setGenerator($document->getGenerator().'; Joomla HLS plugin by Sun Peng');
		
	  $juri = &JFactory::getURI();
	  if(stripos($juri->getPath(),'/administrator/')!==false) return;
		
	  JHtml::_('behavior.framework');
	  JHTML::_('script','system/modal.js', false, true);
	  JHTML::_('stylesheet','system/modal.css', array(), true);
	  $document->addScriptDeclaration(	"window.addEvent('domready', function(){
                  var players = document.getElementsByClassName('video-js');
                  for(var i=0; i<players.length; i++){
                    //alert(players[i].getAttribute('id'));
                    videojs(players[i]);
                  }
		});"		);


	  $document->addScript(_URL_FPP_ . 'player/video.js');
	  $document->addScript(_URL_FPP_ . 'player/videojs-ie8.min.js');
	  $document->addScript(_URL_FPP_ . 'player/hls.js');
	  $document->addScript(_URL_FPP_ . 'player/videojs-hlsjs.js');
	  $document->addStyleSheet(_URL_FPP_ . 'player/video-js.css');
     }

     function onAfterRender()
     {					
	  $juri = &JFactory::getURI();
	  if(stripos($juri->getPath(),'/administrator/')!==false) return;
		
	  $text = JResponse::getBody();
	  if ( stripos( $text, 'hls' ) !== false ) 
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
	  $regex = '/{\s*hls(\s+.+)?\s*}\s*([^\s]+.*[^\s]+)\s*{\s*\/\s*hls\s*}/i';
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
	  $filename 	= strip_tags($matches[2]);

	  $width	= $this->params->get( 'width' );
	  $height	= $this->params->get( 'height' );
	  $center	= $this->params->get( 'center' );
	  $mp4fallback  = $this-params->get('mp4fallback');

		
	  $video = '<video id="video' . rand(0, 100) . '" class="video-js vjs-default-skin" controls preload="auto" width="320" height="240" data-setup=\'{"techOrder":["Hlsjs", "html5"]}\' >' .
	       '<source src="' . $filename . '" type="application/vnd.apple.mpegurl">';
	  
	  if($mp4fallback) {
	       $parts = explode('/', $filename);
	       array_pop($parts);
	       $mp4_filename = implode('/', $parts);
	       $mp4_filename = str_replace("movie","mp4", $mp4_filename);
	       $video = $video . 
	       '<source src="' . $mp4_filename . '" type="video/mp4">';
	  }
	  
	  $video = $video . '</video>';
          
	  return $video;
     }

}

