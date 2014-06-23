<?php
/************************************************************************
 Keosu is an open source CMS for mobile app
Copyright (C) 2014  Vincent Le Borgne, Pockeit

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU Affero General Public License as
published by the Free Software Foundation, either version 3 of the
License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU Affero General Public License for more details.

You should have received a copy of the GNU Affero General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
************************************************************************/
namespace Keosu\CoreBundle\Service;

use Keosu\CoreBundle\KeosuExtension;

use Keosu\CoreBundle\Util\ZipUtil;
use Keosu\CoreBundle\Util\ThemeUtil;
use Keosu\CoreBundle\Util\FilesUtil;
use Keosu\CoreBundle\Util\ExporterUtil;
use Keosu\CoreBundle\Util\StringUtil;
use Keosu\CoreBundle\Util\TemplateUtil;

use Keosu\CoreBundle\Delegate\ExporterDelegate;
use Keosu\CoreBundle\Model\ZoneModel;
use Keosu\CoreBundle\Entity\Page;

use Symfony\Component\DomCrawler\Crawler;

class Exporter {

	private $doctrine;

	private $container;

	public function __construct($doctrine,$container) {
		$this->doctrine = $doctrine;
		$this->container = $container;
	}

	public function exportApp() {
		
		$em = $this->doctrine->getManager();
		$baseurl = $this->container->getParameter('url_base');
		$param = $this->container->getParameter('url_param');
		$appId = $this->container->get('keosu_core.curapp')->getCurApp();
		
		
		$pages = $em->getRepository('KeosuCoreBundle:Page')->findByAppId($appId);
		$isIndexPageImported = false;

		$clean=$this->cleanDir();

		//Export theme
		$app = $em->getRepository('KeosuCoreBundle:App')->find($appId);
		$json = json_encode(array('name' => $app->getName()));
		
		FilesUtil::copyContent($json, ExporterUtil::getAbsolutePath() . '/simulator/www/data/appName.json');
		
		FilesUtil::copyFolder(ThemeUtil::getAbsolutePath() . $app->getTheme().'/style',
				ExporterUtil::getAbsolutePath() . '/simulator/www/theme');
		
		FilesUtil::copyFolder(ThemeUtil::getAbsolutePath() . $app->getTheme().'/res',
			ExporterUtil::getAbsolutePath() . '/simulator/www/res');
		
		//Copy all web/templates/export/js dir to web/export/www/js
		FilesUtil::copyFolder(TemplateUtil::getAbsolutePath() . '/main-header/js',
				ExporterUtil::getAbsolutePath() . '/simulator/www/js');
		
		//cordova_plugins.json
		copy(TemplateUtil::getAbsolutePath() . '/main-header/cordova_plugins.js',
			ExporterUtil::getAbsolutePath() . '/simulator/www/cordova_plugins.js');
		

		
		//Copy all theme/header/js dir to web/export/www/js
		FilesUtil::copyFolder(ThemeUtil::getAbsolutePath() . $app->getTheme().'/header/js',
		ExporterUtil::getAbsolutePath() . '/simulator/www/js');

		// list of imported gadgets
		$importedGadget = array();
		// list of permissions requiered for the application
		$permission = array();

		foreach ($pages as $page) {
			if ($page->getIsMain()) {
				if ($isIndexPageImported) {
					//TODO create custom exception
					throw new \Exception("Duplicate Index: Only one page can have the isMain attribute");
				}
				$isIndexPageImported = true;
			}
			$tmp = $this->exportPage($app->getTheme(), $page);
			$permission = array_merge($permission,$tmp[0]);
			$importedGadget = array_merge($importedGadget,$tmp[1]);
		}
		
		// import folder if they exist
		$importedGadget = array_unique($importedGadget);
		foreach($importedGadget as $gadget) {
		
			$path = TemplateUtil::getAbsolutePath().DIRECTORY_SEPARATOR.'gadget'.DIRECTORY_SEPARATOR.$gadget;
			$dirs = scandir($path);
			foreach($dirs as $dir) {
			
				if($dir != '.' && $dir != '..') {
			
					if(is_dir($path. DIRECTORY_SEPARATOR .$dir)) {
						FilesUtil::copyFolder($path. DIRECTORY_SEPARATOR .$dir,
							ExporterUtil::getAbsolutePath() . DIRECTORY_SEPARATOR .'simulator'. DIRECTORY_SEPARATOR .'www'. DIRECTORY_SEPARATOR .$dir);
					}
				}
			}
		}
		

				//config.xml
		//TODO generate the file
		copy(TemplateUtil::getAbsolutePath() . '/main-header/config.xml',
		ExporterUtil::getAbsolutePath() . '/simulator/www/config.xml');
		
		
		
		// $permission part
		$permission = array_unique($permission);


		


		//die();
		


		/**
		 * Duplicate Export for ios, android and phonegapbuild
		 */
		//For ios
		FilesUtil::copyFolder(ExporterUtil::getAbsolutePath() . '/simulator/www',
			ExporterUtil::getAbsolutePath() . '/ios/www');
		
		copy(TemplateUtil::getAbsolutePath() . '/main-header/ios/cordova.js',
			ExporterUtil::getAbsolutePath() . '/ios/www/cordova.js');
		
		//For Android
		
		FilesUtil::copyFolder(ExporterUtil::getAbsolutePath() . '/simulator/www',
			ExporterUtil::getAbsolutePath() . '/android/www');
		
		copy(TemplateUtil::getAbsolutePath() . '/main-header/android/cordova.js',
		ExporterUtil::getAbsolutePath() . '/android/www/cordova.js');
		
		//For phonegapbuild
		FilesUtil::copyFolder(ExporterUtil::getAbsolutePath() . '/simulator/www',
			ExporterUtil::getAbsolutePath() . '/phonegapbuild/www');
		//No cordova.js in phonegapbuild
		
		copy(TemplateUtil::getAbsolutePath() . '/main-header/ios/cordova.js',
		ExporterUtil::getAbsolutePath() . '/simulator/www/cordova.js');
		
		
		//Generate ZIP files for all

		//ios
		ZipUtil::ZipFolder(ExporterUtil::getAbsolutePath() . '/ios/www',
			ExporterUtil::getAbsolutePath() . '/ios/export.zip');
		//android
		ZipUtil::ZipFolder(ExporterUtil::getAbsolutePath() . '/android/www',
			ExporterUtil::getAbsolutePath() . '/android/export.zip');
		//Phonegapbuild
		ZipUtil::ZipFolder(ExporterUtil::getAbsolutePath() . '/phonegapbuild/www',
			ExporterUtil::getAbsolutePath() . '/phonegapbuild/export.zip');


	}

	/**
	 * Generate html for a page
	 * @param $themeValue theme used in this page
	 * @param $page doctrine object page
	 * @return array[0] list of requiered permission for this page
	 * @return array[1] list of imported gadget
	 */
	private function exportPage($themeValue, $page) {
	
		$baseurl = $this->container->getParameter('url_base');
		$param = $this->container->getParameter('url_param');
		$appId = $this->container->get('keosu_core.curapp')->getCurApp();
	
		$em = $this->doctrine->getManager();
		$gadgetRepo = $em->getRepository('KeosuCoreBundle:Gadget');

		//All page content will be put in $document
		$document = new \DOMDocument();
		//Template of page content as String
		$templateHtml = file_get_contents(
				TemplateUtil::getPageTemplateAbsolutePath()
						. $page->getTemplateId());
		$templateHtml = utf8_encode($templateHtml);
		//Disallow errors to allow HTML5 parsing
		libxml_use_internal_errors(true);
		$document->loadHtml($templateHtml);
		libxml_clear_errors();
		$finder = new \DOMXPath($document);

		//Add HTMl header and js file import (web/templates/main-header.html + web/themes/xxx/header/header.html)
		$document = $this->addHeader($document, $themeValue);
		//Add the main app init in javascript
		$document = $this->addJsInitApp($document);

		//Html head element in document
		$headEl = $document->getElementsByTagName("head")->item(0);
		//Html body element in document
		$bodyEl = $document->getElementsByTagName("body")->item(0);
		//History of gadget init file that have already been imported
		$importedInitFile = array();

		//For all zones in page template
		$classname = "zone";//Find all the zone div in page template
		$zones = $finder
				->query(
						"//*[contains(concat(' ', normalize-space(@class), ' '), ' $classname ')]");
		
		$ret = array();
		$ret[0] = array();
		$ret[1] = array();
		foreach ($zones as $zone) {
			$zoneId = $zone->getAttribute('id');
			//Look if there is a shared gadget in this zone
			$gadget=$gadgetRepo->findSharedByZoneAndApp($zoneId,$appId);
			//If there is no share gadget we try to find the specific one
			if($gadget==null){
				//Find the gadget associated with page and zone			
				$gadget = $gadgetRepo
					->findOneBy(array('zone' => $zoneId, 'page' => $page->getId()));
			}

			if ($gadget != null) {
				//Add gadget html template
				$gadgetTemplateHtml = file_get_contents(
						TemplateUtil::getGadgetAbsolutePath() . '/'
								. $gadget->getGadgetName() . '/'
								. $gadget->getGadgetTemplate());
				$gadgetTemplateHtml = utf8_encode($gadgetTemplateHtml);
				$zone->nodeValue = $gadgetTemplateHtml;
				//Add the angularJS directive to zone
				$zone->setAttribute("ng-controller", $gadget->getGadgetName()."Controller");
				$zone->setAttribute("ng-init","init('".$baseurl."','".$param."','".$page->getFileName()."','".$gadget->getId() ."','".$zoneId."')");
				//Saving node
				$zone->ownerDocument->saveXML($zone);

				//Check if init file is not already imported
				if (!array_key_exists($gadget->getGadgetName(),$importedInitFile)) {
					//Add gadget javascript init file
					$mainIniEl = $document->createElement('script');
					$mainIniEl->setAttribute('src','gadget/' . $gadget->getGadgetName(). 'Controller.js');
					$headEl->appendChild($mainIniEl);
					$importedInitFile[$gadget->getGadgetName()] = 1;
					//Copy js init file to www
					copy(TemplateUtil::getGadgetAbsolutePath(). $gadget->getGadgetName() . '/'. $gadget->getGadgetName() . 'Controller.js',
							ExporterUtil::getAbsolutePath() . '/simulator/www/gadget/'. $gadget->getGadgetName() . 'Controller.js');
				}
			
				//Gadget name without suffix
				$shortGadgetName = str_replace("_gadget", "", $gadget->getGadgetName());

				//If gadget isStatic we copy static data in a local file
				if($gadget->isStatic()){
					//CURL the gadget service
					$serviceurl=$baseurl.$param."service/gadget/".$shortGadgetName."/".$gadget->getId()."/json";
					$curl = curl_init($serviceurl);
					curl_setopt($curl, CURLOPT_HTTPHEADER,
						array('Content-Type: text/xml',
						'User-Agent: Keosu-UA'));
					curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
					$feedstring = utf8_decode(curl_exec($curl));
					$this->writeFile($feedstring, $gadget->getId().".json","/simulator/www/data/");
					
				}

				// permission part
				$class = KeosuExtension::$gadgetList[$gadget->getGadgetName()];
				$instance = new $class();
				$instance->convertAsExistingCommonGadget($gadget);
				$ret[0] = array_merge($ret[0],$instance->getRequieredPermissions());
				
				// import folder part
				$ret[1][] = $gadget->getGadgetName();
				
			}
		}

		//Get all the html from document
		$html = $document->saveHTML();
		$html = StringUtil::decodeString($html);

		$this->writeFile($html, $page->getFileName(),"/simulator/www/");

		$ret[0] = array_unique($ret[0]);
		return $ret;
	}

	private function cleanDir() {
		//Clean existing export dir
		FilesUtil::deleteDir(ExporterUtil::getAbsolutePath() .'/simulator/www');
		FilesUtil::deleteDir(ExporterUtil::getAbsolutePath() .'/ios/www');
		FilesUtil::deleteDir(ExporterUtil::getAbsolutePath() .'/android/www');
		FilesUtil::deleteDir(ExporterUtil::getAbsolutePath() .'/phonegapbuild/www');
		
		//Creating dir www and js
		mkdir(ExporterUtil::getAbsolutePath() . '/simulator/www/gadget', 0777, true);
		mkdir(ExporterUtil::getAbsolutePath() . '/simulator/www/theme', 0777, true);
		mkdir(ExporterUtil::getAbsolutePath() . '/simulator/www/js', 0777, true);
		mkdir(ExporterUtil::getAbsolutePath() . '/simulator/www/data', 0777, true);
		mkdir(ExporterUtil::getAbsolutePath() . '/simulator/www/res', 0777, true);
		
		mkdir(ExporterUtil::getAbsolutePath() . '/ios/www', 0777, true);
		mkdir(ExporterUtil::getAbsolutePath() . '/android/www', 0777, true);
		mkdir(ExporterUtil::getAbsolutePath() . '/phonegapbuild/www', 0777, true);
	}

	//Add HTMl header and js file import
	private function addHeader($document, $themeValue) {

		//Add the Main header (cordova and javascript import common to all pages)
		$headerHtmlMain = file_get_contents(
				TemplateUtil::getAbsolutePath() . '/main-header/header.html');
		$headerHtmlMain = utf8_encode($headerHtmlMain);
		//Add the theme header
		$headerHtml = file_get_contents(
				ThemeUtil::getAbsolutePath() . $themeValue . '/header/header.html');
		$headerHtml = utf8_encode($headerHtml);
		
		
		$headerEl = new \DOMElement('head', $headerHtmlMain.$headerHtml);
		//Insert "head" section before body
		$document->getElementsByTagName("html")->item(0)
				->insertBefore($headerEl,
						$document->getElementsByTagName("body")->item(0));
		return $document;
	}

	//Add JS init app
	private function addJsInitApp($document) {
		//$mainInitHtml = file_get_contents(
				//TemplateUtil::getAbsolutePath() . '/export/main-init.html');
		//$mainInitHtml = utf8_encode($mainInitHtml);
		//$mainIniEl = new \DOMElement('script', $mainInitHtml);
		//$document->getElementsByTagName("body")->item(0)
			//	->appendChild($mainIniEl);
		return $document;
	}

	//write a file
	private function writeFile($html, $fileName, $path) {
		//Writting the html content in file
		$destiPath = ExporterUtil::getAbsolutePath() . $path;
	
		$fileName = $destiPath . $fileName;
	
		if (file_exists($fileName)) {
			unlink($fileName);
		}
		$file = fopen($fileName, "x+");
		fwrite($file, $html);
		fclose($file);
	}
}
?>
