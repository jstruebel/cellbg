<?php
/**
 * cellbg Plugin: Allows user-defined colored cells in tables
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     dr4Ke <dr4ke@dr4ke.net>
 * @link       https://github.com/dr4Ke/cellbg
 * @version    1.0
 *
 * Derived from the highlight plugin from : http://www.dokuwiki.org/plugin:highlight
 * and : http://www.staddle.net/wiki/plugins/highlight
 */

if(!defined('DOKU_INC')) define('DOKU_INC',realpath(dirname(__FILE__).'/../../').'/');
if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
require_once(DOKU_PLUGIN.'syntax.php');

/**
 * All DokuWiki plugins to extend the parser/rendering mechanism
 * need to inherit from this class
 */
class syntax_plugin_cellbg extends DokuWiki_Syntax_Plugin {

    function getInfo(){  // return some info
        return array(
            'author' => 'dr4Ke',
            'email'  => 'dr4ke@dr4ke.net',
            'date'   => '2013-10-09',
            'name'   => 'Cells background color plugin',
            'desc'   => 'Sets background of a cell with a specific color',
            'url'    => 'http://www.dokuwiki.org/plugin:cellbg',
        );
    }

     // What kind of syntax are we?
    function getType(){ return 'formatting'; }
 
    // What kind of syntax do we allow (optional)
    function getAllowedTypes() {
        return array('formatting', /*'substition', 'disabled'*/);
    }

    // What about paragraphs? (optional)
    function getPType(){ return 'normal'; }

    // Where to sort in?
    function getSort(){ return 200; }

    // Connect pattern to lexer
    function connectTo($mode) {
      if ($mode == "table")
      {
        $this->Lexer->addSpecialPattern('^@-?#?[0-9a-zA-Z(),.%]*:', $mode, 'plugin_cellbg');
      }
    }
    function postConnect() {
      //$this->Lexer->addExitPattern(':','plugin_cellbg');
    }

    // Handle the match
    function handle($match, $state, $pos, Doku_Handler $handler){
        switch ($state) {
          case DOKU_LEXER_ENTER :
            break;
          case DOKU_LEXER_MATCHED :
            break;
          case DOKU_LEXER_UNMATCHED :
            //return array($state, $match);
            break;
          case DOKU_LEXER_EXIT :
            break;
          case DOKU_LEXER_SPECIAL :
            preg_match("/@(-?)([^:]*)/", $match, $color); // get the color
            if ( $this->_isValid($color[2]) ) return array($state, $color[2], $color[1], $match);
            break;
        }
        return array($state, "transparent", "", $match);
    }

    // Create output
    function render($mode, Doku_Renderer $renderer, $data) {
        if($mode == 'xhtml'){
          list($state, $color, $row, $text) = $data;
          switch ($state) {
            case DOKU_LEXER_ENTER :
              break;
            case DOKU_LEXER_MATCHED :
              break;
            case DOKU_LEXER_UNMATCHED :
              //$renderer->doc .= $renderer->_xmlEntities($color);
              break;
            case DOKU_LEXER_EXIT :
              break;
            case DOKU_LEXER_SPECIAL :
              if ($row == "-") {
                if (preg_match('/(<tr[^<>]*)>([[:space:]]*<t[hd][^<>]*>[[:space:]]*)$/', $renderer->doc) != 0) {
                  $renderer->doc = preg_replace('/(<tr[^>]*)>([[:space:]]*<t[hd][^>]*>[[:space:]]*)$/', '\1 style="background-color:'.$color.'">\2', $renderer->doc);
                } else {
                  $renderer->doc .= $text;
                }
              } else {
                if (preg_match('/(<t[hd][^<>]*)>[[:space:]]*$/', $renderer->doc) != 0) {
                  $renderer->doc = preg_replace('/(<t[hd][^>]*)>[[:space:]]*$/', '\1 style="background-color:'.$color.'"> ', $renderer->doc);
                } else {
                  $renderer->doc .= $text;
                }
              }
              break;
          }
          return true;
        }
        return false;
    }

    // validate color value $c
    // this is cut price validation - only to ensure the basic format is
    // correct and there is nothing harmful
    // three basic formats  "colorname", "#fff[fff]", "rgb(255[%],255[%],255[%])"
    function _isValid($c) {

        $c = trim($c);

        $pattern = "/
            (^[a-zA-Z]+$)|                                #colorname - not verified
            (^\#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$)|        #colorvalue
            (^rgba?\(([0-9]{1,3}%?,){2}[0-9]{1,3}%?(,[0-9.]+%?)?\)$)|    #rgb[a] triplet
            (^hsla?\([0-9]{1,3}(,[0-9]{1,3}%)(,[0-9.]+%?)?\)$)           #hsl[a] triplet
            /x";

        return (preg_match($pattern, $c));

    }
}

//Setup VIM: ex: et ts=4 sw=4 enc=utf-8 :
