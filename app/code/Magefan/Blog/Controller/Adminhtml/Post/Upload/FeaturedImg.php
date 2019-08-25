<?php
/**
 * Copyright © 2016 Ihor Vansach (ihor@magefan.com). All rights reserved.
 * See LICENSE.txt for license details (http://opensource.org/licenses/osl-3.0.php).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace Magefan\Blog\Controller\Adminhtml\Post\Upload;

use Magefan\Blog\Controller\Adminhtml\Upload\Image\Action;

/**
 * Blog featured image upload controller
 */
class FeaturedImg extends Action
{
    /**
     * File key
     *
     * @var string
     */
    protected $_fileKey = 'featured_img';

    /**
     * Check admin permissions for this controller
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magefan_Blog::post');
    }
}
