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
    if(strpos($content, '{{print:') !== false) {
        $content = preg_replace_callback('#(?<!`)\{\{print\:(.*?)\}\}(?!`)#', function($matches) {
            $content = $matches[0];
            $e = File::E($matches[1], false);
            if($e !== 'txt' && $e !== 'php') $e = 'txt';
            if($snippet = File::exist(ASSET . DS . '__snippet' . DS . $e . DS . $matches[1] . '.' . $e)) {
                return File::open($snippet)->read();
            }
            return $content;
        }, $content);
    }
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
    if(strpos($content, '{{include:') !== false) {
        $content = preg_replace_callback('#(?<!`)\{\{include\:(.*?)\}\}(?!`)#', function($matches) {
            $content = $matches[0];
            $e = File::E($matches[1], false);
            if($e !== 'php') $e = 'php';
            if($snippet = File::exist(ASSET . DS . '__snippet' . DS . $e . DS . $matches[1] . '.' . $e)) {
                $content = include $snippet;
            }
            return $content;
        }, $content);
    }
    return $content;
}

Filter::add(array('shortcode', 'shortcode') /* check for nested snippet loader */, 'do_load_snippet', 1.1);