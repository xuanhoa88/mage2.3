<?php
/**
 * @category    CleverSoft
 * @package     CleverLayeredNavigation
 * @copyright   Copyright © 2017 CleverSoft., JSC. All Rights Reserved.
 * @author 		ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */

namespace CleverSoft\CleverLayeredNavigation\Helper;

use CleverSoft\CleverLayeredNavigation\Helper\Dataseo;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Module\Manager;

class UrlParser extends AbstractHelper
{
    const ALIAS_DELIMITER = '-';

    /** @var  Data */
    protected $seoHelper;

    public function __construct(
        Context $context,
        Dataseo $seoHelper
    )
    {
        parent::__construct($context);
        $this->seoHelper = $seoHelper;
    }

    public function parseSeoPart($seoPart)
    {
        $aliases = explode(static::ALIAS_DELIMITER, $seoPart);
        $params = $this->parseAliasesRecursively($aliases);
        return $params;
    }

    /**
     * @param array $aliases
     * @return array|false
     */
    protected function parseAliasesRecursively($aliases)
    {
        $optionsData = $this->seoHelper->getOptionsSeoData();
        $unparsedAliases = [];
        while ($aliases) {
            $currentAlias = implode(static::ALIAS_DELIMITER, $aliases);
            foreach ($optionsData as $optionId => $option) {
                if ($option['alias'] === $currentAlias) {
                    // Continue DFS
                    $params = $unparsedAliases ? $this->parseAliasesRecursively($unparsedAliases) : [];

                    if ($params !== false) {
                        // Local solution found
                        $params = $this->addParsedOptionToParams($optionId, $option['attribute_code'], $params);
                        return $params;
                    }
                }
            }

            array_unshift($unparsedAliases, array_pop($aliases));
        }

        return false;
    }

    protected function addParsedOptionToParams($value, $paramName, $params)
    {
        if (array_key_exists($paramName, $params)) {
            $params[$paramName] .= ',' . $value;
        } else {
            $params[$paramName] = '' . $value;
        }

        return $params;
    }
}
