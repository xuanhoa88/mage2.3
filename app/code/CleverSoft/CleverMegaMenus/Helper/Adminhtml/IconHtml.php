<?php
/**
 * @category    CleverSoft
 * @package     CleverMegaMenus
 * @copyright   Copyright © 2017 CleverSoft., JSC. All Rights Reserved.
 * @author 		ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */
namespace CleverSoft\CleverMegaMenus\Helper\Adminhtml;

class IconHtml {
    protected $_general;
    protected $_accessibility;
    protected $_hand;
    protected $_brand;
    protected $_fileType;
    protected $_textEditor;
    protected $_directional;
    protected $_videoPlayer;
    protected $_formControl;
    protected $_chart;
    protected $_currency;
    protected $_gender;
    protected $_spinner;
    protected $_transportation;
    protected $_medical;
    protected $_icons;
    public function __construct(){
        $this->_general = ['adjust','american-sign-language-interpreting','anchor','archive','area-chart','arrows','arrows-h','arrows-v','asl-interpreting','assistive-listening-systems','asterisk','at','audio-description','automobile','balance-scale','ban','bank','bar-chart','bar-chart-o','barcode','bars','battery-0','battery-1','battery-2','battery-3','battery-4','battery-empty','battery-full','battery-half','battery-quarter','battery-three-quarters','bed','beer','bell','bell-o','bell-slash','bell-slash-o','bicycle','binoculars','birthday-cake','blind','bluetooth','bluetooth-b','bolt','bomb','book','bookmark','bookmark-o','braille','briefcase','bug','building','building-o','bullhorn','bullseye','bus','cab','calculator','calendar','calendar-check-o','calendar-minus-o','calendar-o','calendar-plus-o','calendar-times-o','camera','camera-retro','car','caret-square-o-down','caret-square-o-left','caret-square-o-right','caret-square-o-up','cart-arrow-down','cart-plus','cc','certificate','check','check-circle','check-circle-o','check-square','check-square-o','child','circle','circle-o','circle-o-notch','circle-thin','clock-o','clone','close','cloud','cloud-download','cloud-upload','code','code-fork','coffee','cog','cogs','comment','comment-o','commenting','commenting-o','comments','comments-o','compass','copyright','creative-commons','credit-card','credit-card-alt','crop','crosshairs','cube','cubes','cutlery','dashboard','database','deaf','deafness','desktop','diamond','dot-circle-o','download','edit','ellipsis-h','ellipsis-v','envelope','envelope-o','envelope-square','eraser','exchange','exclamation','exclamation-circle','exclamation-triangle','external-link','external-link-square','eye','eye-slash','eyedropper','fax','feed','female','fighter-jet','file-archive-o','file-audio-o','file-code-o','file-excel-o','file-image-o','file-movie-o','file-pdf-o','file-photo-o','file-picture-o','file-powerpoint-o','file-sound-o','file-video-o','file-word-o','file-zip-o','film','filter','fire','fire-extinguisher','flag','flag-checkered','flag-o','flash','flask','folder','folder-o','folder-open','folder-open-o','frown-o','futbol-o','gamepad','gavel','gear','gears','gift','glass','globe','graduation-cap','group','hand-grab-o','hand-lizard-o','hand-paper-o','hand-peace-o','hand-pointer-o','hand-rock-o','hand-scissors-o','hand-spock-o','hand-stop-o','hard-of-hearing','hashtag','hdd-o','headphones','heart','heart-o','heartbeat','history','home','hotel','hourglass','hourglass-1','hourglass-2','hourglass-3','hourglass-end','hourglass-half','hourglass-o','hourglass-start','i-cursor','image','inbox','industry','info','info-circle','institution','key','keyboard-o','language','laptop','leaf','legal','lemon-o','level-down','level-up','life-bouy','life-buoy','life-ring','life-saver','lightbulb-o','line-chart','location-arrow','lock','low-vision','magic','magnet','mail-forward','mail-reply','mail-reply-all','male','map','map-marker','map-o','map-pin','map-signs','meh-o','microphone','microphone-slash','minus','minus-circle','minus-square','minus-square-o','mobile','mobile-phone','money','moon-o','mortar-board','motorcycle','mouse-pointer','music','navicon','newspaper-o','object-group','object-ungroup','paint-brush','paper-plane','paper-plane-o','paw','pencil','pencil-square','pencil-square-o','percent','phone','phone-square','photo','picture-o','pie-chart','plane','plug','plus','plus-circle','plus-square','plus-square-o','power-off','print','puzzle-piece','qrcode','question','question-circle','question-circle-o','quote-left','quote-right','random','recycle','refresh','registered','remove','reorder','reply','reply-all','retweet','road','rocket','rss','rss-square','search','search-minus','search-plus','send','send-o','server','share','share-alt','share-alt-square','share-square','share-square-o','shield','ship','shopping-bag','shopping-basket','shopping-cart','sign-in','sign-language','sign-out','signal','signing','sitemap','sliders','smile-o','soccer-ball-o','sort','sort-alpha-asc','sort-alpha-desc','sort-amount-asc','sort-amount-desc','sort-asc','sort-desc','sort-down','sort-numeric-asc','sort-numeric-desc','sort-up','space-shuttle','spinner','spoon','square','square-o','star','star-half','star-half-empty','star-half-full','star-half-o','star-o','sticky-note','sticky-note-o','street-view','suitcase','sun-o','support','tablet','tachometer','tag','tags','tasks','taxi','television','terminal','thumb-tack','thumbs-down','thumbs-o-down','thumbs-o-up','thumbs-up','ticket','times','times-circle','times-circle-o','tint','toggle-down','toggle-left','toggle-off','toggle-on','toggle-right','toggle-up','trademark','trash','trash-o','tree','trophy','truck','tty','tv','umbrella','universal-access','university','unlock','unlock-alt','unsorted','upload','user','user-plus','user-secret','user-times','users','video-camera','volume-control-phone','volume-down','volume-off','volume-up','warning','wheelchair','wheelchair-alt','wifi','wrench'];
        $this->_accessibility = ['american-sign-language-interpreting','asl-interpreting','assistive-listening-systems','audio-description','blind','braille','cc','deaf','deafness','hard-of-','low-vision','question-circle-o','sign-language','signing','tty','universal-access','volume-control-phone','wheelchair','wheelchair-al'];
        $this->_hand = ['hand-grab-o','hand-lizard-o','hand-o-down','hand-o-left','hand-o-right','hand-o-up','hand-paper-o','hand-peace-o','hand-pointer-o','hand-rock-o','hand-scissors-o','hand-spock-o','hand-stop-o','thumbs-down','thumbs-o-down','thumbs-o-up','thumbs-up'];
        $this->_brand = ['500px','adn','amazon','android','angellist','apple','behance','behance-square','bitbucket','bitbucket-square','bitcoin','black-tie','bluetooth','bluetooth-b','btc','buysellads','cc-amex','cc-diners-club','cc-discover','cc-jcb','cc-mastercard','cc-paypal','cc-stripe','cc-visa','chrome','codepen','codiepie','connectdevelop','contao','css3','dashcube','delicious','deviantart','digg','dribbble','dropbox','drupal','edge','empire','envira','expeditedssl','fa','facebook','facebook-f','facebook-official','facebook-square','firefox','first-order','flickr','font-awesome','fonticons','fort-awesome','forumbee','foursquare','ge','get-pocket','gg','gg-circle','git','git-square','github','github-alt','github-square','gitlab','gittip','glide','glide-g','google','google-plus','google-plus-circle','google-plus-official','google-plus-square','google-wallet','gratipay','hacker-news','houzz','html5','instagram','internet-explorer','ioxhost','joomla','jsfiddle','lastfm','lastfm-square','leanpub','linkedin','linkedin-square','linux','maxcdn','meanpath','medium','mixcloud','modx','odnoklassniki','odnoklassniki-square','opencart','openid','opera','optin-monster','pagelines','paypal','pied-piper','pied-piper-alt','pied-piper-pp','pinterest','pinterest-p','pinterest-square','product-hunt','qq','ra','rebel','reddit','reddit-alien','reddit-square','renren','resistance','safari','scribd','sellsy','share-alt','share-alt-square','shirtsinbulk','simplybuilt','skyatlas','skype','slack','slideshare','snapchat','snapchat-ghost','snapchat-square','soundcloud','spotify','stack-exchange','stack-overflow','steam','steam-square','stumbleupon','stumbleupon-circle','tencent-weibo','themeisle','trello','tripadvisor','tumblr','tumblr-square','twitch','twitter','twitter-square','usb','viacoin','viadeo','viadeo-square','vimeo','vimeo-square','vine','vk','wechat','weibo','weixin','whatsapp','wikipedia-w','windows','wordpress','wpbeginner','wpforms','xing','xing-square','y-combinator','y-combinator-square','yahoo','yc','yc-square','yelp','yoast','youtube','youtube-play','youtube-square'];
        $this->_fileType = ['file-archive-o','file-audio-o','file-code-o','file-excel-o','file-image-o','file-movie-o','file-o','file-pdf-o','file-photo-o','file-picture-o','file-powerpoint-o','file-sound-o','file-text','file-text-o','file-video-o','file-word-o','file-zip-o'];
        $this->_textEditor = ['align-center','align-justify','align-left','align-right','bold','chain,','chain-broken','clipboard','columns','copy,','cut,','dedent,','eraser','file','file-o','file-text','file-text-o','files-o','floppy-o','font','header','indent','italic','link','list','list-alt','list-ol','list-ul','outdent','paperclip','paragraph','paste,','repeat','rotate-left,','rotate-right,','save,','scissors','strikethrough','subscript','superscript','table','text-height','text-width','th','th-large','th-list','underline','undo'];
        $this->_directional = ['angle-double-down','angle-double-left','angle-double-right','angle-double-up','angle-down','angle-left','angle-right','angle-up','arrow-circle-down','arrow-circle-left','arrow-circle-o-down','arrow-circle-o-left','arrow-circle-o-right','arrow-circle-o-up','arrow-circle-right','arrow-circle-up','arrow-down','arrow-left','arrow-right','arrow-up','arrows','arrows-alt','arrows-h','arrows-v','caret-down','caret-left','caret-right','caret-square-o-down','caret-square-o-left','caret-square-o-right','caret-square-o-up','caret-up','chevron-circle-down','chevron-circle-left','chevron-circle-right','chevron-circle-up','chevron-down','chevron-left','chevron-right','chevron-up','exchange','hand-o-down','hand-o-left','hand-o-right','hand-o-up','long-arrow-down','long-arrow-left','long-arrow-right','long-arrow-up','toggle-down','toggle-left','toggle-right','toggle-up'];
        $this->_videoPlayer = ['arrows-alt','backward','compress','eject','expand','fast-backward','fast-forward','forward','pause','pause-circle','pause-circle-o','play','play-circle','play-circle-o','random','step-backward','step-forward','stop','stop-circle','stop-circle-o','youtube-play'];
        $this->_formControl = ['check-square','check-square-o','circle','circle-o','dot-circle-o','minus-square','minus-square-o','plus-square','plus-square-o','square','square-o'];
        $this->_chart = ['area-chart','bar-chart','bar-chart-o','line-chart','pie-chart'];
        $this->_currency = ['bitcoin','btc','cny','dollar','eur','euro','gbp','gg','gg-circle','ils','inr','jpy','krw','money','rmb','rouble','rub','ruble','rupee','shekel','sheqel','try','turkish-lira','usd','won','yen'];
        $this->_gender = ['genderless','intersex','mars','mars-double','mars-stroke','mars-stroke-h','mars-stroke-v','mercury','neuter','transgender','transgender-alt','venus','venus-double','venus-mars'];
        $this->_spinner = ['circle-o-notch','cog','gear','refresh','spinner'];
        $this->_transportation = ['ambulance','automobile','bicycle','bus','cab','car','fighter-jet','motorcycle','plane','rocket','ship','space-shuttle','subway','taxi','train','truck','wheelchair'];
        $this->_medical = ['ambulance','h-square','heart','heart-o','heartbeat','hospital-o','medkit','plus-square','stethoscope','user-md','wheelchair'];
        $this->_icons = [
            ['name' => __('Web Application Icons'), 'icons' => $this->_general],
            ['name' => __('Accessibility Icons'), 'icons' => $this->_accessibility],
            ['name' => __('Hand Icons'), 'icons' => $this->_hand],
            ['name' => __('Brand Icons'), 'icons' => $this->_brand],
            ['name' => __('File Type Icons'), 'icons' => $this->_fileType],
            ['name' => __('Text Editor Icons'), 'icons' => $this->_textEditor],
            ['name' => __('Directional Icons'), 'icons' => $this->_directional],
            ['name' => __('Video Player Icons'), 'icons' => $this->_videoPlayer],
            ['name' => __('Form Control Icons'), 'icons' => $this->_formControl],
            ['name' => __('Chart Icons'), 'icons' => $this->_chart],
            ['name' => __('Currency Icons'), 'icons' => $this->_currency],
            ['name' => __('Gender Icons'), 'icons' => $this->_gender],
            ['name' => __('Spinner Icons'), 'icons' => $this->_spinner],
            ['name' => __('Transportation Icons'), 'icons' => $this->_transportation],
            ['name' => __('Medical Icons'), 'icons' => $this->_medical],
        ];
    }

    /*
     * get template
     */
    public function iconTemplateHelper(){
        $iconH = '';
        foreach ($this->_icons as $key=>$ics) {
            $innerHtml = '';
            foreach ($ics['icons'] as $i=>$ic) {
                $innerHtml .='
                    <div class="col-xs-2"><a href="javascript:void(0)" onclick="Icons.insertCleverIcon(\''.$ic.'\',\'fa fa-\');return false;"><i class="fa fa-'.$ic.'"></i> '.$ic.'</a></div>
                ';
            }
            $iconH .= '
                <div class="menu-icons-wrapper">
                <p class="icon-label">'.$ics['name'].'</p>
                <div class="row">
                '.$innerHtml.'
                </div>
            </div>
            ';
        }
        return $iconH;
    }
}