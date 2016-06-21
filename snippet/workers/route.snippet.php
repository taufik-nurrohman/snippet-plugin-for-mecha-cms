<?php

Route::accept($config->manager->slug . '/snippet', function() use($config, $speak) {
    // Add `.htaccess` file to prevent direct access
    $htaccess = ASSET . DS . '__snippet' . DS . '.htaccess';
    if( ! File::exist($htaccess)) {
        File::write('deny from all')->saveTo($htaccess, 0600);
    }
    Config::set(array(
        'page_title' => $speak->snippets . $config->title_separator . $config->manager->title,
        'cargo' => __DIR__ . DS . 'cargo.snippet.php'
    ));
    Shield::lot(array('segment' => 'snippet'))->attach('manager');
});

Route::post($config->manager->slug . '/snippet/ignite', function() use($config, $speak) {
    $request = Request::post();
    $id = time();
    Guardian::checkToken($request['token']);
    if(trim($request['name']) === "") {
        $request['name'] = $id . '.txt'; // empty file name
    }
    $_path = Text::parse(sprintf($request['name'], $id), '->safe_path_name');
    $e = File::E($_path, false);
    if($e !== 'txt' && $e !== 'php') {
        $e = 'txt';
        $_path .= '.txt';
    }
    $_path_ = File::path($_path);
    $file = ASSET . DS . '__snippet' . DS . $e . DS . $_path;
    if(File::exist($file)) { // file already exists
        Notify::error(Config::speak('notify_file_exist', '<code>' . $_path_ . '</code>'));
    }
    if(trim($request['content']) === "") { // empty file content
        Notify::error($speak->notify_error_content_empty);
    }
    if( ! Notify::errors()) {
        $recent = array_slice(File::open(CACHE . DS . 'plugin.snippet.cache')->unserialize(), 0, $config->per_page);
        File::serialize(array_merge(array($_path), $recent))->saveTo(CACHE . DS . 'plugin.snippet.cache', 0600);
        $url = $config->manager->slug . '/asset/repair/file:__snippet/' . $e . '/' . File::url($_path) . '?path=' . urlencode(rtrim('__snippet/' . $e . '/' . File::D(File::url($_path)), '/'));
        File::write($request['content'])->saveTo($file, 0600);
        Notify::success(Config::speak('notify_file_created', '<code>' . $_path_ . '</code>' . ( ! isset($request['redirect']) ? ' <a class="pull-right" href="' . $config->url . '/' . $url . '" target="_blank">' . Jot::icon('pencil') . ' ' . $speak->edit . '</a>' : "")));
        Notify::info('<strong>' . $speak->shortcode . ':</strong> <code>{{' . ($e === 'php' ? 'include' : 'print') . ':' . str_replace('.' . $e . X, "", File::url($_path) . X) . '}}</code>');
        Guardian::kick(isset($request['redirect']) ? $url : File::D($config->url_current));
    }
    Guardian::kick(File::D($config->url_current));
});