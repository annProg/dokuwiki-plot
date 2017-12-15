<?php
/**
 * graphviz-Plugin: Parses graphviz-blocks
 *
 * @license    MIT
 * @author     Ann He <me@annhe.net>
 */


if(!defined('DOKU_INC')) define('DOKU_INC',realpath(dirname(__FILE__).'/../../').'/');
if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
require_once(DOKU_PLUGIN.'syntax.php');

class syntax_plugin_graphviz2 extends DokuWiki_Syntax_Plugin {
	private $input = "";

    /**
     * What about paragraphs?
     */
    function getPType(){
        return 'normal';
    }

    /**
     * What kind of syntax are we?
     */
    function getType(){
        return 'substition';
    }

    /**
     * Where to sort in?
     */
    function getSort(){
        return 100;
    }

    /**
     * Connect pattern to lexer
     */
    function connectTo($mode) {
        $this->Lexer->addSpecialPattern('<graphviz.*?>\n.*?\n</graphviz>',$mode,'plugin_graphviz2');
    }

    /**
     * Handle the match
     */
    function handle($match, $state, $pos, Doku_Handler $handler) {
        $info = $this->getInfo();

        // prepare default data
        $return = array(
                        'layout'    => 'dot',
                        'align'     => '',
						'chof' => 'png',
                       );

        // prepare input
        $lines = explode("\n",$match);
        $conf = array_shift($lines);
        array_pop($lines);

        // match config options
        if(preg_match('/\b(left|center|right)\b/i',$conf,$match)) $return['align'] = $match[1];
        if(preg_match('/\b(dot|neato|twopi|circo|fdp|sfdp)\b/i',$conf,$match)){
            $return['layout'] = strtolower($match[1]);
        }

        $this->input = join("\n",$lines);
        $return['md5'] = md5($this->input); // we only pass a hash around

        // store input for later use
        return $return;
    }

    /**
     * Create output
     */
    function render($format, Doku_Renderer $R, $data) {
        if($format == 'xhtml'){
            $img = $this->_remote($data);;
            $R->doc .= '<img src="'.$img.'" class="media'.$data['align'].'" alt=""';
            if($data['width'])  $R->doc .= ' width="'.$data['width'].'"';
            if($data['height']) $R->doc .= ' height="'.$data['height'].'"';
            if($data['align'] == 'right') $R->doc .= ' align="right"';
            if($data['align'] == 'left')  $R->doc .= ' align="left"';
            $R->doc .= '/>';
            return true;
        }
        return false;
    }

    /**
     * Render the output remotely at graphviz API
     */
    function _remote($data){
		$api = $this->getConf('api');	
		$img = $api . "?cht=" . 'gv:' . $data['layout'] . "&chl=" . urlencode($this->input) . "&chof=" . $data['chof'];
        return $img;
    }
}
