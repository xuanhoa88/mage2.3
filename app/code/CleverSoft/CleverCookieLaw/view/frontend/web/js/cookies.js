/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE_AFL.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magento.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magento.com for more information.
 *
 * @category    Mage
 * @package     js
 * @copyright   Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
// old school cookie functions grabbed off the web
define([
  'jquery',
  'mage/cookies'
], function($){
  if (!window.mage) var mage = {};

  $.mage.cookies = {};
  $.mage.cookies.expires  = null;
  $.mage.cookies.path     = '/';
  $.mage.cookies.domain   = null;
  $.mage.cookies.secure   = false;
  $.mage.cookies.set = function(name, value){
       var argv = arguments;
       var argc = arguments.length;
       var expires = (argc > 2) ? argv[2] : $.mage.cookies.expires;
       var path = (argc > 3) ? argv[3] : $.mage.cookies.path;
       var domain = (argc > 4) ? argv[4] : $.mage.cookies.domain;
       var secure = (argc > 5) ? argv[5] : $.mage.cookies.secure;
       document.cookie = name + "=" + value +
         ((expires == null) ? "" : ("; expires=" + expires.toGMTString())) +
         ((path == null) ? "" : ("; path=" + path)) +
         ((domain == null) ? "" : ("; domain=" + domain)) +
         ((secure == true) ? "; secure" : "");
  };

  $.mage.cookies.get = function(name){
      var arg = name + "=";
      var alen = arg.length;
      var clen = document.cookie.length;
      var i = 0;
      var j = 0;
      while(i < clen){
          j = i + alen;
          if (document.cookie.substring(i, j) == arg)
              return $.mage.cookies.getCookieVal(j);
          i = document.cookie.indexOf(" ", i) + 1;
          if(i == 0)
              break;
      }
      return null;
  };

  $.mage.cookies.clear = function(name) {
    if($.mage.cookies.get(name)){
      document.cookie = name + "=" +
      "; expires=Thu, 01-Jan-70 00:00:01 GMT";
    }
  };

  $.mage.cookies.getCookieVal = function(offset){
     var endstr = document.cookie.indexOf(";", offset);
     if(endstr == -1){
         endstr = document.cookie.length;
     }
     return unescape(document.cookie.substring(offset, endstr));
  };
});