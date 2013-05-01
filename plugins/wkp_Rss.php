<?php # coding: utf-8

/* Based on ModuleRss.php from TigerWiki
 ******
 * Creation of a RSS feed with $RECENT_NUMBER last modified pages
 * each time a page is modified.
 * Created file: rss_XX.xml in the wiki root. XX=$LANG
 * Add {RSS} between <head></head> in template.html
 * This will allow the feed to be discovered.
 * The number of entries is the same as for 'recent' ($RECENT_NUMBER)
 */

class Rss
{
   function __toString()
   {
      return tr('Create RSS feed of last changes');
   }

   const template = '<rss version="0.91">
<channel>
<title>{WIKI_TITLE}</title>
<link>{ADR_ACCUEIL}</link>
<description>{WIKI_DESCRIPTION}</description>
<language>{LANG}</language>
{CONTENT_RSS}
</channel>
</rss>';

   function writedPage ($file)
   {
      global $WIKI_TITLE,$PAGES_DIR,$LANG,$TIME_FORMAT,$RECENT_NUMBER;
      $CONTENT_RSS = "";
   
      // TODO: bug if https
      $ADR_ACCUEIL = "http://".$_SERVER["SERVER_NAME"].':'.$_SERVER["SERVER_PORT"].$_SERVER["SCRIPT_NAME"];
      
      $rss = str_replace('{WIKI_TITLE}', $WIKI_TITLE, self::template);
      $rss = str_replace('{ADR_ACCUEIL}', $ADR_ACCUEIL , $rss);
      $rss = str_replace('{LANG}', $LANG, $rss);
      $rss = str_replace('{WIKI_DESCRIPTION}', "Flux RSS de ".$WIKI_TITLE, $rss);

      $dir = opendir(getcwd() . "/$PAGES_DIR");
      while ($file = readdir($dir))
         if (preg_match("/\.txt$/", $file))
            $filetime[$file] = filemtime($PAGES_DIR . $file);
      arsort($filetime);
      $filetime = array_slice($filetime, 0, $RECENT_NUMBER);
      foreach ($filetime as $filename => $timestamp)
      {
         $filename = substr($filename, 0, strlen($filename) - 4);
         //RSS content
         $CONTENT_RSS .= "<item>
            <title>$filename</title>
            <pubDate>". date("r", $timestamp)."</pubDate>
            <link>$ADR_ACCUEIL?page=".urlencode("$filename")."&amp;lang=$LANG</link>
            <description>$filename " . strftime("$TIME_FORMAT", $timestamp) . "</description>
            </item>";
      }
      $rss = str_replace("{CONTENT_RSS}", $CONTENT_RSS, $rss); 
      //Write RSS             
      if (! $file = fopen("rss_$LANG.xml", "w"))
         die (tr('Cannot create RSS feed'));
      fputs($file, $rss);
      fclose($file);    
   }
   
   function template()
   {
      global $html,$LANG;
      $html = str_replace('{RSS}','<link rel="alternate" type="application/rss+xml" title="RSS" href="rss_'.$LANG.'.xml" />',$html);
      return FALSE;
   }
}

?>
