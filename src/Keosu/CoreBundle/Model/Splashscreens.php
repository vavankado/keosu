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
namespace Keosu\CoreBundle\Model;

use Symfony\Component\HttpFoundation\File\File;
use Keosu\CoreBundle\Util\StringUtil;

/**
 * Splashscreens
 *
 */
class Splashscreens{

    // Android
    private $splashscreenA320x436;
    private $splashscreenA320x470;
    private $splashscreenA640x480;
    private $splashscreenA960x720;


    // IOS
    private $splashscreenI320x480;
    private $splashscreenI640x960;
    private $splashscreenI640x1136;
    private $splashscreenI1024x748;
    private $splashscreenI768x1004;
    private $splashscreenI2048x1496;
    private $splashscreenI1536x2008;


    public function getAbsolutePath($os) {
        return $this->getUploadRootDir($os) . '/';
    }

    public function getWebPath() {
        return '/' . $this->getUploadDir() . '/';
    }

    public function getUploadRootDir($os) {
        // the absolute directory path where uploaded
        // documents should be saved
        return __DIR__ .'/../../../../web/' . $this->getUploadDir($os);
    }

    protected function getUploadDir($os) {
        // get rid of the __DIR__ so it doesn't screw up
        // when displaying uploaded doc/image in the view.
        return 'keosu/res/tmp/splashscreens/'.$os;
    }

    /**
     * Get splashscreensA320x436
     *
     * @return File
     */
    public function getSplashscreenA320x436(){
        return $this->splashscreenA320x436;
    }


    public function setSplashscreenA320x436(File $splashscreen){
        if (StringUtil::endsWith($splashscreen->getClientOriginalName(), '.9.png'))
            $splashscreen->move($this->getUploadRootDir("android"), "splashscreenA320x436.9.png");
        else
            $splashscreen->move($this->getUploadRootDir("android"), "splashscreenA320x436.png");
    }

    /**
     * Get splashscreensA320x470
     *
     * @return File
     */
    public function getSplashscreenA320x470(){
        return $this->splashscreenA320x470;
    }


    public function setSplashscreenA320x470(File $splashscreen){
        if (StringUtil::endsWith($splashscreen->getClientOriginalName(), '.9.png'))
            $splashscreen->move($this->getUploadRootDir("android"), "splashscreenA320x470.9.png");
        else
            $splashscreen->move($this->getUploadRootDir("android"), "splashscreenA320x470.png");
    }

    /**
     * Get splashscreensA640x480
     *
     * @return File
     */
    public function getSplashscreenA640x480(){
        return $this->splashscreenA640x480;
    }


    public function setSplashscreenA640x480(File $splashscreen){
        if (StringUtil::endsWith($splashscreen->getClientOriginalName(), '.9.png')) {
            $splashscreen->move($this->getUploadRootDir("android"), "splashscreenA640x480.9.png"); }
        else {
            $splashscreen->move($this->getUploadRootDir("android"), "splashscreenA640x480.png"); }
    }

    /**
     * Get splashscreensA960x720
     *
     * @return File
     */
    public function getSplashscreenA960x720(){
        return $this->splashscreenA960x720;
    }


    public function setSplashscreenA960x720(File $splashscreen){
        if (StringUtil::endsWith($splashscreen->getClientOriginalName(), '.9.png'))
            $splashscreen->move($this->getUploadRootDir("android"), "splashscreenA960x720.9.png");
        else
            $splashscreen->move($this->getUploadRootDir("android"), "splashscreenA960x720.png");
    }


    /**
     * Get splashscreensI320x480
     *
     * @return File
     */
    public function getSplashscreenI320x480(){
        return $this->splashscreenI320x480;
    }


    public function setSplashscreenI320x480(File $splashscreen){
        $splashscreen->move($this->getUploadRootDir("ios"), "splashscreenI320x480.png");
    }

    /**
     * Get splashscreensI640x960
     *
     * @return File
     */
    public function getSplashscreenI640x960(){
        return $this->splashscreenI640x960;
    }


    public function setSplashscreenI640x960(File $splashscreen){
        $splashscreen->move($this->getUploadRootDir("ios"), "splashscreenI640x960.png");
    }

    /**
     * Get splashscreensI640x1136
     *
     * @return File
     */
    public function getSplashscreenI640x1136(){
        return $this->splashscreenI640x1136;
    }


    public function setSplashscreenI640x1136(File $splashscreen){
        $splashscreen->move($this->getUploadRootDir("ios"), "splashscreenI640x1136.png");
    }

    /**
     * Get splashscreensI1024x748
     *
     * @return File
     */
    public function getSplashscreenI1024x748(){
        return $this->splashscreenI1024x748;
    }


    public function setSplashscreenI1024x748(File $splashscreen){
        $splashscreen->move($this->getUploadRootDir("ios"), "splashscreenI1024x748.png");
    }

    /**
     * Get splashscreensI768x1004
     *
     * @return File
     */
    public function getSplashscreenI768x1004(){
        return $this->splashscreenI768x1004;
    }


    public function setSplashscreenI768x1004(File $splashscreen){
        $splashscreen->move($this->getUploadRootDir("ios"), "splashscreenI768x1004.png");
    }

    /**
     * Get splashscreensI2048x1496
     *
     * @return File
     */
    public function getSplashscreenI2048x1496(){
        return $this->splashscreenI2048x1496;
    }


    public function setSplashscreenI2048x1496(File $splashscreen){
        $splashscreen->move($this->getUploadRootDir("ios"), "splashscreenI2048x1496.png");
    }

    /**
     * Get splashscreensI1536x2008
     *
     * @return File
     */
    public function getSplashscreenI1536x2008(){
        return $this->splashscreenI1536x2008;
    }


    public function setSplashscreenI1536x2008(File $splashscreen){
        $splashscreen->move($this->getUploadRootDir("ios"), "splashscreenI1536x2008.png");
    }
    
}