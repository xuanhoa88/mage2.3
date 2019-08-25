<?php
/**
 * @category    CleverSoft
 * @package     CleverPageBuilder
 * @copyright   Copyright © 2017 CleverSoft., JSC. All Rights Reserved.
 * @author      ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */
namespace CleverSoft\CleverBuilder\Helper;

use CleverSoft\CleverBuilder\Helper\cbColumn as CbColumn;
use CleverSoft\CleverBuilder\Helper\cbRow as CbRow;

class DoShortCode extends \CleverSoft\CleverBuilder\Helper\Data {
    /**
     * @param $content
     *
     * @since 4.4
     */
    function getPageShortcodesByContent( $content ) {
        $content = $this->shortcodeUnautop( trim( $content ) );
        $not_shortcodes = preg_split( '/' . $this->shortcodesRegexp() . '/', $content );

        foreach ( $not_shortcodes as $string ) {
            $temp = str_replace( array(
                '<p>',
                '</p>',
            ), '', $string ); // just to avoid autop @todo maybe do it better like vc_wpnop in js.
            if ( strlen( trim( $temp ) ) > 0 ) {
                $content = preg_replace( '/(' . preg_quote( $string, '/' ) . '(?!\[\/))/', '[cb-row][cb-column width="12/12"][cb-column-text]$1<input type="hidden" name="cbblocktype" value="pagetext">[/cb-column-text][/cb-column][/cb-row]', $content );
            }
        }

        return $content;
    }
    /**
     * Returns the regexp for common whitespace characters.
     *
     * By default, spaces include new lines, tabs, nbsp entities, and the UTF-8 nbsp.
     * This is designed to replace the PCRE \s sequence.  In ticket #22692, that
     * sequence was found to be unreliable due to random inclusion of the A0 byte.
     *
     * @since 4.0.0
     *
     * @staticvar string $spaces
     *
     * @return string The spaces regexp.
     */
    function spacesRegexp() {
        return '[\r\n\t ]|\xC2\xA0|&nbsp;';
    }

    /**
     * Don't auto-p wrap shortcodes that stand alone
     *
     * Ensures that shortcodes are not wrapped in `<p>...</p>`.
     *
     * @since 2.9.0
     *
     * @global array $shortcode_tags
     *
     * @param string $pee The content.
     * @return string The filtered content.
     */
    function shortcodeUnautop( $pee ) {

        $tagregexp = join( '|', array_map( 'preg_quote', array_keys( $this->_shortCodes ) ) );
        $spaces = $this->spacesRegexp();

        $pattern =
            '/'
            . '<p>'                              // Opening paragraph
            . '(?:' . $spaces . ')*+'            // Optional leading whitespace
            . '('                                // 1: The shortcode
            .     '\\['                          // Opening bracket
            .     "($tagregexp)"                 // 2: Shortcode name
            .     '(?![\\w-])'                   // Not followed by word character or hyphen
            // Unroll the loop: Inside the opening shortcode tag
            .     '[^\\]\\/]*'                   // Not a closing bracket or forward slash
            .     '(?:'
            .         '\\/(?!\\])'               // A forward slash not followed by a closing bracket
            .         '[^\\]\\/]*'               // Not a closing bracket or forward slash
            .     ')*?'
            .     '(?:'
            .         '\\/\\]'                   // Self closing tag and closing bracket
            .     '|'
            .         '\\]'                      // Closing bracket
            .         '(?:'                      // Unroll the loop: Optionally, anything between the opening and closing shortcode tags
            .             '[^\\[]*+'             // Not an opening bracket
            .             '(?:'
            .                 '\\[(?!\\/\\2\\])' // An opening bracket not followed by the closing shortcode tag
            .                 '[^\\[]*+'         // Not an opening bracket
            .             ')*+'
            .             '\\[\\/\\2\\]'         // Closing shortcode tag
            .         ')?'
            .     ')'
            . ')'
            . '(?:' . $spaces . ')*+'            // optional trailing whitespace
            . '<\\/p>'                           // closing paragraph
            . '/';

        return preg_replace( $pattern, '$1', $pee );
    }

    /**
     * Search content for shortcodes and filter shortcodes through their hooks.
     *
     * If there are no shortcode tags defined, then the content will be returned
     * without any filtering. This might cause issues when plugins are disabled but
     * the shortcode will still show up in the post or content.
     *
     * @since 2.5.0
     *
     * @global array $shortcode_tags List of shortcode tags and their callback hooks.
     *
     * @param string $content Content to search for shortcodes.
     * @param bool $ignore_html When true, shortcodes inside HTML elements will be skipped.
     * @return string Content with shortcodes filtered out.
     */
    function doShortcode( $content, $ignore_html = false ) {
        $shortcode_tags = $this->_shortCodes;
        if ( false === strpos( $content, '[' ) ) {
            return $content;
        }

        if (empty($shortcode_tags) || !is_array($shortcode_tags))
            return $content;
        // Find all registered tag names in $content.
        preg_match_all( '@\[([^<>&/\[\]\x00-\x20=]++)@', $content, $matches );
        $tagnames = array_intersect( array_keys( $shortcode_tags ), $matches[1] );

        if ( empty( $tagnames ) ) {
            return $content;
        }

//        $content = $this->doShortcodesInHtmlTags( $content, $ignore_html, $tagnames );

        $pattern = $this->shortcodesRegexp();
        $content = preg_replace_callback( "/$pattern/", array($this, 'doShortcodeTag'), $content );

        // Always restore square braces so we don't break things like <!--[if IE ]>
        $content = $this->unescapeInvalidShortcodes( $content );
        $this->doShortcode($content);
        return $content;
    }
    /**
     * Remove placeholders added by do_shortcodes_in_html_tags().
     *
     * @since 4.2.3
     *
     * @param string $content Content to search for placeholders.
     * @return string Content with placeholders removed.
     */
    function unescapeInvalidShortcodes( $content ) {
        // Clean up entire string, avoids re-parsing HTML.
        $trans = array( '&#91;' => '[', '&#93;' => ']' );
        $content = strtr( $content, $trans );

        return $content;
    }
    /*
     *
     */

    public function doShortcodeTag($m){
        if ( $m[1] == '[' && $m[6] == ']' ) {
            return substr($m[0], 1, -1);
        }

        $tag = $m[2];
        $attr = $this->shortcodeParseAtts( $m[3] );

        $content = isset( $m[5] ) ? $m[5] : null;

        $output = $m[1] . call_user_func( array($this,'doShortcodeTagFunction'), $attr, $content, $tag ) . $m[6];

        return $this->callbackShortcodes($output);
    }

    /*
     *read template from cbRow.php or cbColumn.php , $content is not contain the current shortcode.
     */

    public function doShortcodeTagFunction($attr,$content,$tag) {
        if(!$tag) return $content;
        $tagHelper = '\CleverSoft\CleverBuilder\Helper\cb'.ucfirst($this->_shortCodes[$tag]);
        $helper = \Magento\Framework\App\ObjectManager::getInstance()->get($tagHelper);
        $funcStart = 'cb'.ucfirst($this->_shortCodes[$tag]).'TemplateStart';
        $funcClose = 'cb'.ucfirst($this->_shortCodes[$tag]).'TemplateClose';
        return $helper->$funcStart($attr).$content.$helper->$funcClose();
    }

    /**
     * @param $content
     * @param bool $autop
     *
     * @since 4.2
     * @return string
     */
    function callbackShortcodes( $content) {
        return $this->doShortcode( $this->shortcodeUnautop( $content ) );
    }


    /*
     *Retrieve all attributes from the shortcodes tag.
     */
    public function shortcodeParseAtts($text){
        $atts = array();
        $pattern = $this->getShortcodeAttsRegex();
        $text = preg_replace("/[\x{00a0}\x{200b}]+/u", " ", $text);
        if ( preg_match_all($pattern, $text, $match, PREG_SET_ORDER) ) {
            foreach ($match as $m) {
                if (!empty($m[1]))
                    $atts[strtolower($m[1])] = stripcslashes($m[2]);
                elseif (!empty($m[3]))
                    $atts[strtolower($m[3])] = stripcslashes($m[4]);
                elseif (!empty($m[5]))
                    $atts[strtolower($m[5])] = stripcslashes($m[6]);
                elseif (isset($m[7]) && strlen($m[7]))
                    $atts[] = stripcslashes($m[7]);
                elseif (isset($m[8]))
                    $atts[] = stripcslashes($m[8]);
            }

            // Reject any unclosed HTML elements
            foreach( $atts as &$value ) {
                if ( false !== strpos( $value, '<' ) ) {
                    if ( 1 !== preg_match( '/^[^<]*+(?:<[^>]*+>[^<]*+)*+$/', $value ) ) {
                        $value = '';
                    }
                }
            }
        } else {
            $atts = ltrim($text);
        }
        return $atts;
    }

    /**
     * Retrieve the shortcode attributes regex.
     *
     * @since 4.4.0
     *
     * @return string The shortcode attribute regular expression
     */
    function getShortcodeAttsRegex() {
        return '/([\w-]+)\s*=\s*"([^"]*)"(?:\s|$)|([\w-]+)\s*=\s*\'([^\']*)\'(?:\s|$)|([\w-]+)\s*=\s*([^\s\'"]+)(?:\s|$)|"([^"]*)"(?:\s|$)|(\S+)(?:\s|$)/';
    }
}