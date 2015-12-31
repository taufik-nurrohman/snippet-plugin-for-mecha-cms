<?php

Config::merge('manager_menu', array(
    $speak->snippet => array(
        'icon' => 'puzzle-piece',
        'url' => $config->manager->slug . '/snippet',
        'stack' => 9.021
    )
));

function do_load_snippet($content) {
    global $config, $speak;
    if(strpos($content, '{{') === false) return $content;
    // Plain text => `{{print:foo}}`
    if(strpos($content, '{{print:') !== false || strpos($content, '{{print=') !== false) {
        $content = preg_replace_callback('#(?<!`)\{\{print[:=](.*?)\}\}(?!`)#', function($matches) {
            $content = $matches[0];
            $e = File::E($matches[1], false);
            if($e !== 'txt' && $e !== 'php') $e = 'txt';
            if($snippet = File::exist(ASSET . DS . '__snippet' . DS . $e . DS . $matches[1] . '.' . $e)) {
                return File::open($snippet)->read();
            }
            return $content;
        }, $content);
    }
    // Plain text with wildcard(s) => `{{print path="foo" lot="bar,baz,qux"}}`
    if(strpos($content, '{{print ') !== false) {
        $content = preg_replace_callback('#(?<!`)\{\{print\s+(.*?)\}\}(?!`)#', function($matches) {
            $content = $matches[0];
            $data = Converter::attr($content, array('{{', '}}', ' '), array('"', '"', '='));
            if( ! isset($data['attributes']['path'])) {
                return $matches[0];
            }
            $e = File::E($data['attributes']['path'], false);
            if($e !== 'txt' && $e !== 'php') $e = 'txt';
            if( ! $snippet = File::exist(ASSET . DS . '__snippet' . DS . $e . DS . $data['attributes']['path'] . '.' . $e)) {
                return $matches[0];
            }
            $content = File::open($snippet)->read();
            if(isset($data['attributes']['lot'])) {
                $attr = Mecha::walk(explode(',', $data['attributes']['lot']), function($v) {
                    return str_replace('&#44;', ',', $v);
                });
                $content = vsprintf($content, $attr);
            }
            return $content;
        }, $content);
    }
    // Executable code => `{{include:foo}}`
    if(strpos($content, '{{include:') !== false || strpos($content, '{{include=') !== false) {
        $content = preg_replace_callback('#(?<!`)\{\{include[:=](.*?)\}\}(?!`)#', function($matches) {
            $content = $matches[0];
            $e = File::E($matches[1], false);
            if($e !== 'php') $e = 'php';
            if($snippet = File::exist(ASSET . DS . '__snippet' . DS . $e . DS . $matches[1] . '.' . $e)) {
                $content = include $snippet;
            }
            return $content;
        }, $content);
    }
    // Executable code with wildcard(s) => `{{include path="foo" lot="bar,baz,qux"}}`
    if(strpos($content, '{{include ') !== false) {
        $content = preg_replace_callback('#(?<!`)\{\{include\s+(.*?)\}\}(?!`)#', function($matches) {
            $content = $matches[0];
            $data = Converter::attr($content, array('{{', '}}', ' '), array('"', '"', '='));
            if( ! isset($data['attributes']['path'])) {
                return $matches[0];
            }
            $e = File::E($data['attributes']['path'], false);
            if($e !== 'php') $e = 'php';
            if( ! $snippet = File::exist(ASSET . DS . '__snippet' . DS . $e . DS . $data['attributes']['path'] . '.' . $e)) {
                return $matches[0];
            }
            $content = include $snippet;
            if(isset($data['attributes']['lot'])) {
                $attr = Mecha::walk(explode(',', $data['attributes']['lot']), function($v) {
                    return str_replace('&#44;', ',', $v);
                });
                $content = vsprintf($content, $attr);
            }
            return $content;
        }, $content);
    }
    return $content;
}

// Allow nested snippet(s) three time(s)
Filter::add(array('shortcode', 'shortcode', 'shortcode'), 'do_load_snippet', 1.1);