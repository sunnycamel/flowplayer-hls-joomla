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
	  //$this->p = new JParameter($params['params']);
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
		
	  $document->addScript(_URL_FPP_ .'javascripts/'. $this->params->get( 'js' ) );
	  if($this->params->get('ipad')) $document->addScript(_URL_FPP_ .'swfplugins/flowplayer.ipad-3.2.2.min.js');
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
		    //if(!($this->params->get('noscript')==1))	JResponse::appendBody($this->buildScript());
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
	  $p 		= $this->parseTagParameters($matches[1]);
	  $filename 	= strip_tags($matches[2]);
		
	  $width	= (isset($p['width']))?$p['width']:$this->params->get( 'width' );
	  $height	= (isset($p['height']))?$p['height']:$this->params->get( 'height' );
	  $center	= (isset($p['center']))?(($p['center'])?true:false):$this->params->get( 'center' );
	  $img_tag	= (isset($p['splashtype']))?$p['splashtype']:$this->params->get('splashtype',0);
		
	  $elem		= $this->params->get( 'element', 'div' );
	  $dlink        = (isset($p['dlink']))?$p['dlink']:$this->params->get( 'dlink', 0 );	
	  $popup 	= (isset($p['popup']))?$p['popup']:$this->params->get( 'popup', 0 );		
		
	  if(strtolower(substr($filename, -4))!=".rss")  $href = 'href="'.$filename.'"';
	  else $href='';
	  $id = crc32($filename);
		
	  if(!$p['img']&&$this->params->get('globalimg','')) $p['img'] = $this->params->get('globalimg','');
		
	  if(!$popup) {			
	       //play icon (if necessary)
	       $mid = 	($p['img']&&$img_tag)?'<div style="width: 100%; height: 100%; background: url('._URL_FPP_.'assets/play_large.png) center center no-repeat;"></div>':'';
	       //img code that will be used either in CSS of JSO
	       $img =  ($img_tag)?" background-image: url('".$p['img']."');":"backgroundImage: '".$p['img']."'";
	       //force autoplay when using images, not flash
	       if ($p['img']&&$img_tag) $p['autoplay'] = 1;
					
	       $video = '<'.$elem.' class="flowplayer'.$id.'" style="width: '
		    .$width.'px; height: '.$height.'px;	display: block;'.(($img_tag)?$img:'').'" '
		    .$href.'>'.$mid.'</'.$elem.'>';
	       if(!($this->params->get('noscript')==1))	
		    $video .= ($href)?$this->buildScript($p, $id, (($img&&!$img_tag)?$img:false)):$this->buildScript($p, $id, (($img&&!$img_tag)?$img:false), $filename);
	       if($dlink && $href) $video .= '<br /><a '.$href.' class="flowplayer_dlink">'.$this->params->get( 'dlinktext', "Download" ).'</a>';			
	  }
	  else
	  {
	       $p['autoplay'] = 1;
	       $pwidth	      =	(isset($p['pwidth']))?$p['pwidth']:$this->params->get( 'pwidth' );
	       $pheight	      =	(isset($p['pheight']))?$p['pheight']:$this->params->get( 'pheight' );
			
	       //build video block
	       $video = '<'.$elem.' class="flowplayer'.$id.'" style="width: '
		    .$pwidth.'px; height: '.$pheight.'px;	display: block;"></'.$elem.'>';
	       if(!($this->params->get('noscript')==1))	
		    $video .= $this->buildScript($p, $id, "backgroundImage: '".$p['img']."'", $filename);
			
	       //build wrapper
	       $video = '<a class="fprrpopup'.$id.'" href="#fprrpopup'.$id.'" style="display: block; width: '.$width.'px;
						 height: '.$height.'px; background-image: url('.$p['img'].')"
						 rel="{size:{x:'.$pwidth.',y:'.$pheight.'}}">
						 	<div style="width: 100%; height: 100%; background: url('._URL_FPP_.'assets/play_large.png) center center no-repeat;"></div>
						 </a><div style="display: none;"><div id="fprrpopup'.$id.'">'.$video.'</div></div>
						 <style>#sbox-window{background: none;padding:4px;-moz-border-radius:3px;-webkit-border-radius:3px;} #sbox-content{overflow:visible;}</style>';
			
	       if($dlink && $href) $video .= '<br /><a '.$href.' class="flowplayer_dlink">'.$this->params->get( 'dlinktext', "Download" ).'</a>';			
	  }
		
	  if($center) $video = '<div style="display: block; width: 100%;">'.$video.'</div><style>.flowplayer'.$id.',.fprrpopup'.$id.'{margin: 0px auto;}</style>';
	  //var_dump($p);
	  return $video;
     }

     protected function buildScript($p, $id = '', $img = '', $filename = '')
     {
	  //receiving parameters
	  $fpp_autoPlay	  =	(isset($p['autoplay']))?(($p['autoplay'])?true:false):$this->params->get( 'autoPlay' );
	  $fpp_viral      =	(isset($p['viral']))?(($p['viral'])?true:false):$this->params->get( 'viral', false );
			
	  $fpp_scaling 	  =	$this->params->get( 'scaling' );
	  $fpp_bufferL	  =	$this->params->get( 'bufferL' );
	  $fpp_key	  =	$this->params->get( 'key' );
	  $fpp_canvas	  = 	$this->params->get( 'canvas' );
	  $fpp_clip	  = 	$this->params->get( 'clip' );
	  $fpp_screen	  = 	$this->params->get( 'screen' );
	  $fpp_play	  =	$this->params->get( 'play' );
	  $fpp_plugins	  =	$this->params->get( 'plugins' );
	  $fpp_jssuffix	  =	$this->params->get( 'jssuffix','' );
	  $fpp_config	  =	$this->params->get( 'additionalconfig','');
	  $fpp_viral_conf =	$this->params->get( 'viral_conf','');
	  $fpp_wmode	  =	$this->params->get( 'wmode', 'opaque');
	  $elem 	  = $this->params->get( 'element', 'a' );
			
			
	  //formatting & adding necessary elements
	  $fpp_key 	  = ($fpp_key) ? 'key: "'.$fpp_key.'",' : '';
	  $fpp_config 	  = ($fpp_config)?",\n$fpp_conf":'';
	  $fpp_clip 	  = ($fpp_clip)?", $fpp_clip":'';
	  $fpp_viral_conf = ($fpp_viral_conf)?",\n$fpp_viral_conf":'';
	  $fpp_jssuffix   = ($fpp_jssuffix)?".$fpp_jssuffix":'';
	  //Convert boolean param
	  ($fpp_autoPlay == 0)? $fpp_autoPlay = 'false' : $fpp_autoPlay = 'true';
			
	  if($filename){
	       if(strtolower(substr($filename, -4))==".rss") $playlist=$filename;
	       else $fpp_clip = "$fpp_clip,
								  url: '$filename'";
	  }
			
	  if ($img && $fpp_canvas) $fpp_canvas .= ",\n";			
	  if($playlist) {
	       $playlist = "playlist: '$playlist', ";
	  }
			
	  if($this->params->get('ipad')) $fpp_jssuffix = ".each(function(){this.ipad(".
					      (($this->params->get('ipad')==2)?'{ simulateiDevice: true}':'').")})".$fpp_jssuffix;
			
			
	  //viral videos plugin
	  $fpp_viral		= ($fpp_viral)?"viral: {url: '"._URL_FPP_ ."swfplugins/flowplayer.viralvideos-3.2.5.swf'$fpp_viral_conf}":'';
	  if ($fpp_viral && ( $fpp_plugins || $this->params->get('subtitles') ||$p['subtitles'])) $fpp_viral .= ",\n";

	  //subtitles
	  if($p['subtitles']!=="0"){
	       if($this->params->get('subtitles')||isset($p['subtitles'])){
		    if($fpp_plugins) $fpp_plugins.=",";
		    $fpp_plugins.="captions: {
					url: '"._URL_FPP_ ."swfplugins/flowplayer.captions-3.2.3.swf',	captionTarget: 'content' },
					content: {
					url:'"._URL_FPP_ ."swfplugins/flowplayer.content-3.2.0.swf',bottom: 5,
					height:40, backgroundColor: 'transparent',	backgroundGradient: 'none',
					border: 0, textDecoration: 'outline', style: { body: { fontSize: 14, 
							fontFamily: 'Arial',textAlign: 'center',color: '#ffffff'} } 
					}";
		    if($p['cuepointmultiplier']) 
			 $fpp_clip .=",cuepointMultiplier: ".$p['cuepointmultiplier'];
	       }
	       if(isset($p['subtitles'])&&$p['subtitles']!="1") 
		    $fpp_clip.=",captionUrl: '".$p['subtitles']."'";					
	  }
	  if($p['loop'])
	       $fpp_clip .=",\nonBeforeFinish: function() {this.play(0);return false; }";
			

			
	  //Build javascript finally			
	  $script = '<script type="text/javascript">
			$f("'.$elem.'.flowplayer'.$id.'", {src: "' . _URL_FPP_ .'players/'. $this->params->get( 'swf' ) . '", wmode: "'.$fpp_wmode.'"}, {
				'.$fpp_key.'
				clip: {
						autoPlay: '.$fpp_autoPlay.',
						scaling: "'. $fpp_scaling . '", 
						bufferLength: ' . $fpp_bufferL . '
						'.$fpp_clip.'
					} ,		 
				'.$playlist.'
				canvas: {
						' . $img . $fpp_canvas	. '
					} ,
				screen: {
						' . $fpp_screen	 . '
					} ,	
				play: {
						' . $fpp_play . '
					},
				plugins: {
					' . $fpp_viral . $fpp_plugins . '
				}'.$fpp_config.'
			})'.$fpp_jssuffix.';
			</script>';
			
	  return $script;
     }
     protected function parseTagParameters($string)
     {
	  preg_match_all("/\s+([a-zA-Z]+)=([^\s]+)/i", $string, $matches, PREG_SET_ORDER);
	  foreach($matches as $i) 
	       $p[strtolower($i[1])] = $i[2];
	  return $p;
     }
}

