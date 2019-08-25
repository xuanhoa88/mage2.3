<?php
/**
 * @category    CleverSoft
 * @package     CleverTheme
 * @copyright   Copyright © 2017 CleverSoft., JSC. All Rights Reserved.
 * @author 		ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */

namespace CleverSoft\CleverTheme\Helper;


class Grid extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * Values: number of columns / grid item width
     *
     * @var array
     */
    protected $_itemWidth = array(
        "1" => 100,
        "2" => 50,
        "3" => 33.33,
        "4" => 25,
        "5" => 20,
        "6" => 16.667,
        "7" => 14.2,
        "8" => 12.5,
    );

    /**
     * Get CSS for grid item based on number of columns
     *
     * @param int $columnCount
     * @return string
     */
    public function getCssGridItem($columnCount)
    {
        $out = "\n";
        $out .= '.itemgrid.zoo-itemgrid-adaptive .product-item { width:' . $this->_itemWidth[$columnCount] . '%; clear:none !important; }' . "\n";

        if ($columnCount > 1)
        {
            $out .= '.itemgrid.zoo-itemgrid-adaptive .product-item:nth-child(' . $columnCount . 'n+1) { clear:left !important; }' . "\n";
        }

        return $out;
    }
}
