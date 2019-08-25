<?php
/**
 * Copyright © 2015 Ihor Vansach (ihor@magefan.com). All rights reserved.
 * See LICENSE.txt for license details (http://opensource.org/licenses/osl-3.0.php).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace Magefan\Blog\Block\Post\PostList;
/**
 * Post list item
 */
class Item extends \Magefan\Blog\Block\Post\AbstractPost
{
    public function getConfigImage($config_path)
    {
        return (int) $this->_scopeConfig->getValue(
            $config_path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
}