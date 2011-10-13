<?php
    global $settings, $page, $location, $showCommercials, $mosConfig_live_site;
?>
<?php
    $page->doBeforeHtmlHead();

    global $location;
    //Refresh newswire page at 3min interval
    if (($location['zone']=="newswire") && ($location['task']==null ||
$location['task']=="newWindow")){
        echo "<meta http-equiv=\"refresh\" content=\"180\">";
    }
    ?>    

    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title><?=$page->getTitle()?></title>
    <meta lang="fr" name="description" content="<?=$page->getDesc()?>" />
    <meta lang="fr" name="keywords" content="<?=$page->getKeys()?>" />
    <link rel="shortcut icon"
href="<?=$settings['prj']['tmpl']['url']?>/images/favicon.ico"
type="image/x-icon" />
    <link rel="alternate" type="application/rss+xml" title="RTL Info - A la
Une" href="http://feeds.feedburner.com/Rtlinfos-ALaUne" />
    <link rel="alternate" type="application/rss+xml" title="RTL Info -
Belgique" href="http://feeds.feedburner.com/rtlinfo/belgique" />
    <link rel="alternate" type="application/rss+xml" title="RTL Info - Monde"
href="http://feeds.feedburner.com/RTLInternational" />
    <link rel="alternate" type="application/rss+xml" title="RTL Info -
Economie" href="http://feeds.feedburner.com/RTLEconomie" />
    <link rel="alternate" type="application/rss+xml" title="RTL Info - Sport"
href="http://feeds.feedburner.com/RTLSports" />
    <link rel="stylesheet" href="<?php echo
$settings['prj_redesign']['tmpl']['css']?>/reset.css?v=<?php echo
$settings['version']['resources']['css'];?>" type="text/css" media="all"
charset="utf-8" />
    <link rel="stylesheet" href="<?php echo
$settings['prj_redesign']['tmpl']['css']?>/structure.css?v=<?php echo
$settings['version']['resources']['css'];?>" type="text/css" media="all"
charset="utf-8" />
    <link rel="stylesheet" href="<?php echo
$settings['prj_redesign']['tmpl']['css']?>/style.css?v=<?php echo
$settings['version']['resources']['css'];?>" type="text/css" media="all"
charset="utf-8" />
    <link rel="stylesheet" href="<?php echo
$settings['prj_redesign']['tmpl']['css']?>/colors.css?v=<?php echo
$settings['version']['resources']['css'];?>" type="text/css" media="all"
charset="utf-8" />
    <link rel="stylesheet" href="<?php echo
$settings['prj_redesign']['tmpl']['css']?>/topmenu.css?v=<?php echo
$settings['version']['resources']['css'];?>" type="text/css" media="all"
charset="utf-8" />
    <link rel="stylesheet" href="<?php echo
$settings['prj_redesign']['tmpl']['css']?>/footer.css?v=<?php echo
$settings['version']['resources']['css'];?>" type="text/css" media="all"
charset="utf-8" />
<link rel="stylesheet" href="<?php echo
$settings['prj_redesign']['tmpl']['css']?>/development.css?v=<?php echo
$settings['version']['resources']['css'];?>" type="text/css" media="all"
charset="utf-8" /><link rel="stylesheet" href="<?php echo
$settings['prj_redesign']['tmpl']['css']?>/rtl_print.css?v=<?php echo
$settings['version']['resources']['css'];?>" type="text/css" media="print"
charset="utf-8" /><link rel="stylesheet" href="<?php echo
$settings['prj_redesign']['tmpl']['css']?>/rtl_print.css?v=<?php echo
$settings['version']['resources']['css'];?>" type="text/css" media="print"
charset="utf-8" />
    <style type="text/css" media="screen">
        <!--
        @import
url("<?=$settings['prj']['tmpl']['url']?>/css/thickbox.css?v=<?php echo
$settings['version']['resources']['css'];?>");
        @import
url("<?=$settings['prj']['tmpl']['url']?>/css/thickbox_ifr.css?v=<?php echo
$settings['version']['resources']['css'];?>");
        @import
url("<?=$settings['prj']['tmpl']['scripts']?>/calendar/calendar-win2k-cold-1.css?v=<?php
echo $settings['version']['resources']['css'];?>");
        -->
    </style>
    <script type="text/javascript"
src="<?=$mosConfig_live_site?>/metriweb/mwTag.js"></script>
    <script type="text/javascript"
src="<?=$settings['prj']['tmpl']['scripts'];?>/ajax.js?v=<?php echo
$settings['version']['resources']['scripts'];?>"></script>
    <script type="text/javascript"
src="<?=$settings['prj']['tmpl']['scripts'];?>/messages.js?v=<?php echo
$settings['version']['resources']['scripts'];?>"></script>
    <script type="text/javascript"
src="<?=$settings['prj']['tmpl']['scripts'];?>/jquery.js?v=<?php echo
$settings['version']['resources']['scripts'];?>"></script>
    <script type="text/javascript"
src="<?=$settings['prj']['tmpl']['scripts'];?>/thickbox.js?v=<?php echo
$settings['version']['resources']['scripts'];?>"></script>
    <script type="text/javascript"
src="<?=$settings['prj']['tmpl']['scripts'];?>/default.js?v=<?php echo
$settings['version']['resources']['scripts'];?>"></script>
    <script type="text/javascript"
src="<?=$settings['prj']['tmpl']['scripts'];?>/jquery_ifr.js?v=<?php echo
$settings['version']['resources']['scripts'];?>"></script>
    <script type="text/javascript"
src="<?=$settings['prj']['tmpl']['scripts'];?>/thickbox_ifr.js?v=<?php echo
$settings['version']['resources']['scripts'];?>"></script>
    <script type="text/javascript"
src="<?=$settings['prj']['tmpl']['scripts'];?>/common.js?v=<?php echo
$settings['version']['resources']['scripts'];?>"></script>
    <script type="text/javascript"
src="<?=$settings['prj']['tmpl']['scripts'];?>/_ajax.js?v=<?php echo
$settings['version']['resources']['scripts'];?>"></script>

    <script type="text/javascript"
src="<?=$settings['prj']['tmpl']['scripts'];?>/calendar/calendar.js?v=<?php
echo $settings['version']['resources']['scripts'];?>"></script>
    <script type="text/javascript"
src="<?=$settings['prj']['tmpl']['scripts'];?>/calendar/lang/calendar-fr.js?v=<?php
echo $settings['version']['resources']['scripts'];?>"></script>
    <script type="text/javascript"
src="<?=$settings['prj']['tmpl']['scripts'];?>/calendar/calendar-setup.js?v=<?php
echo $settings['version']['resources']['scripts'];?>"></script>
    <script type="text/javascript"
src="<?=$settings['prj']['tmpl']['scripts']?>/audio-player.js?v=<?php echo
$settings['version']['resources']['scripts'];?>"></script>
    <script type="text/javascript"
src="<?=$settings['prj']['tmpl']['scripts']?>/cookies/rtl_Cookies.js?v=<?php
echo $settings['version']['resources']['scripts'];?>"></script>
    <script type="text/javascript" src="<?php echo
$settings['prj']['tmpl']['scripts']?>/swfobject_new.js?v=<?php echo
$settings['version']['resources']['scripts'];?>"></script>
<!--[if lte IE 6]>
    <script type="text/javascript" src="<?php echo
$settings['prj_redesign']['tmpl']['scripts']; ?>/supersleight-min.js?v=<?php
echo $settings['version']['resources']['scripts'];?>"></script>
<![endif]-->
    <script type="text/javascript">
          var LiveSite = '<?php print addslashes($mosConfig_live_site); ?>';

          var functionsToCallAfterBodyLoad = []; // including images
          var functionsToCallAfterDomLoad = []; // NOT including images


          function doOnLoad(){
            var sendLinkAnchor = document.getElementById("thickbox_a");
            if(typeof(sendLinkAnchor) != 'undefined' && sendLinkAnchor != null)
{
//                sendLinkAnchor.href =
"<?=$mosConfig_live_site.'/'.SITE;?>/sendlink/?KeepThis=true&TB_iframe=true&height=320&width=480";
                sendLinkAnchor.href =
"<?=setURl('sendlink',null,null,null,null,"KeepThis=true&TB_iframe=true&height=320&width=480",null,true,null,null,true);?>";
            }

                TB_init_ifr();
                if (typeof (playInThickboxURL) != 'undefined' &&
playInThickboxURL && playInThickboxURL.length > 0) {
                //    alert('playInThickboxURL: '+playInThickboxURL);
                removeLoader();
                removeLoaderX2();
                    TB_init_ifr_playList(playInThickboxURL);            
                }
                else if (typeof (playInAudioPopupURL) != 'undefined' &&
playInAudioPopupURL && playInAudioPopupURL.length > 0) {
                //    alert('playInThickboxURL: '+playInThickboxURL);
                removeLoader();
                removeLoaderX2();
                    openAudioPopup(playInAudioPopupURL, 'Rtlinfo_audio',
AUDIO_POPUP_WIDTH, AUDIO_POPUP_HEIGHT);            
                }

                pageLoaded = true;

                /* call the functions "registered" to be called after the body
was loaded(including images) */
                for(var i=0; i < functionsToCallAfterBodyLoad.length; i++) {
                    window[functionsToCallAfterBodyLoad[i]]();
                }

        }

        $(document).ready(function() {
           // do stuff when DOM is ready
//           setInterval('showNextHeadlineX( )', 20000);

                /* call the functions "registered" to be called after the dom
was loaded( NOT including images) */
                for(var i=0; i < functionsToCallAfterDomLoad.length; i++) {
                    window[functionsToCallAfterDomLoad[i]]();
                }

             }
         );        



    </script>