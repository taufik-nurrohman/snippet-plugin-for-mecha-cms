<?php

Config::merge('manager_menu', array(
    $speak->snippet => array(
        'icon' => 'puzzle-piece',
        'url' => $config->manager->slug . '/snippet',
        'stack' => 9.021
    )
));

function do_snippet($content) {
    global $config, $speak;
    if(strpos($content, '{{') === false) return $content;
    // plain text: `{{print:foo}}`
    if(strpos($content, '{{print:') !== false || strpos($content, '{{print=') !== false) {
        $content = preg_replace_callback('#(?<!`)\{\{print[:=](.*?)\}\}(?!`)#', function($matches) {
            $content = $matches[0];
            $e = File::E($matches[1], false);
            if($e !== 'txt' && $e !== 'php') {
                $e = 'txt';
                $matches[1] .= '.txt';
            }
            if($snippet = File::exist(ASSET . DS . '__snippet' . DS . $e . DS . $matches[1])) {
                return File::open($snippet)->read();
            }
            return $content;
        }, $content);
    }
    // plain text with wildcard(s): `{{print path="foo" lot="bar,baz,qux"}}`
    if(strpos($content, '{{print ') !== false) {
        $content = preg_replace_callback('#(?<!`)\{\{print\s+(.*?)\}\}(?!`)#', function($matches) {
            $content = $matches[0];
            $data = Converter::attr($content, array('{{', '}}', ' '), array('"', '"', '='));
            $attr = (array) $data['attributes'];
            if( ! isset($attr['path'])) {
                return $matches[0];
            }
            $e = File::E($attr['path'], false);
            if($e !== 'txt' && $e !== 'php') {
                $e = 'txt';
                $attr['path'] .= '.txt';
            }
            if( ! $snippet = File::exist(ASSET . DS . '__snippet' . DS . $e . DS . $attr['path'])) {
                return $matches[0];
            }
            $content = File::open($snippet)->read();
            if(isset($attr['lot']) && strpos($content, '%') !== false) {
                // `http://stackoverflow.com/a/2053931`
                if(preg_match_all('#%(?:(\d+)[$])?[-+]?(?:[ 0]|[\'].)?(?:[-]?\d+)?(?:[.]\d+)?[%bcdeEfFgGosuxX]#', $content, $matches)) {
                    $lot = Mecha::walk(explode(',', $attr['lot']), function($v) {
                        return str_replace('&#44;', ',', $v);
                    });
                    if(count($lot) >= count(array_unique($matches[1]))) {
                        $content = vsprintf($content, $lot);
                    }
                }
            }
            return $content;
        }, $content);
    }
    // executable code: `{{include:foo}}`
    if(strpos($content, '{{include:') !== false || strpos($content, '{{include=') !== false) {
        $content = preg_replace_callback('#(?<!`)\{\{include[:=](.*?)\}\}(?!`)#', function($matches) {
            $content = $matches[0];
            $e = File::E($matches[1], false);
            if($e !== 'php') {
                $e = 'php';
                $matches[1] .= '.php';
            }
            if($snippet = File::exist(ASSET . DS . '__snippet' . DS . $e . DS . $matches[1])) {
                ob_start();
                include $snippet;
                $content = ob_get_clean();
            }
            return $content;
        }, $content);
    }
    // executable code with variable(s): `{{include path="foo" lot="bar,baz,qux" another_var="1"}}`
    if(strpos($content, '{{include ') !== false) {
        $content = preg_replace_callback('#(?<!`)\{\{include\s+(.*?)\}\}(?!`)#', function($matches) {
            $content = $matches[0];
            $data = Converter::attr($content, array('{{', '}}', ' '), array('"', '"', '='));
            $attr = (array) $data['attributes'];
            if( ! isset($attr['path'])) {
                return $matches[0];
            }
            $e = File::E($attr['path'], false);
            if($e !== 'php') {
                $e = 'php';
                $attr['path'] .= '.php';
            }
            if($snippet = File::exist(ASSET . DS . '__snippet' . DS . $e . DS . $attr['path'])) {
                ob_start();
                if(isset($attr['lot'])) {
                    $lot = Mecha::walk(explode(',', $attr['lot']), function($v) {
                        return Converter::strEval(str_replace('&#44;', ',', $v));
                    });
                } else {
                    $lot = array();
                }
                unset($attr['path'], $attr['lot']);
                extract($attr);
                include $snippet;
                $content = ob_get_clean();
            }
            return $content;
        }, $content);
    }
    return $content;
}

// Apply `do_snippet` filter and allow nested snippet(s) three time(s)
Filter::add(array('shortcode', 'shortcode', 'shortcode'), 'do_snippet', 1.1);