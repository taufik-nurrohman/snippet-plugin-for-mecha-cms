<?php

Route::accept($config->manager->slug . '/snippet', function() use($config, $speak) {
    // Add `.htaccess` file to prevent direct access
    $s = ASSET . DS . '__snippet' . DS . '.htaccess';
    if( ! file_exists($s)) {
        File::write('deny from all')->saveTo($s, 0600);
    }
    Config::set(array(
        'page_title' => Config::speak('manager.title_new_', $speak->snippet) . $config->title_separator . $config->manager->title,
        'cargo' => __DIR__ . DS . 'workers' . DS . 'cargo.snippet.php'
    ));
    Shield::attach('manager');
});

Route::accept($config->manager->slug . '/snippet/ignite', function() use($config, $speak) {
    if($request = Request::post()) {
		Guardian::checkToken($request['token']);
		if(trim($request['name']) === "") {
			$request['name'] = 'snippet.' . time() . '.txt'; // empty file name
		}
        $path = Text::parse($request['name'], '->safe_path_name');
		$path_ = File::path($path);
        $e = File::E($path, false);
        if($e !== 'txt' && $e !== 'php') $e = 'txt';
		$file = ASSET . DS . '__snippet' . DS . $e . DS . $path_;
		if(file_exists($file)) { // file already exists
			Notify::error(Config::speak('notify_file_exist', '<code>' . $path_ . '</code>'));
		}
		if(trim($request['content']) === "") { // empty file content
			Notify::error($speak->notify_error_content_empty);
		}
		if( ! Notify::errors()) {
			$url = $config->manager->slug . '/asset/repair/file:__snippet/' . $e . '/' . File::url($path);
			File::write($request['content'])->saveTo($file, 0600);
			Notify::success(Config::speak('notify_file_created', '<code>' . $path_ . '</code> <a class="pull-right" href="' . $config->url . '/' . $url . '" target="_blank">' . Jot::icon('pencil') . ' ' . $speak->edit . '</a>'));
			Guardian::kick(isset($request['redirect']) ? $url : File::D($config->url_current));
		}
		Guardian::kick(File::D($config->url_current));
    }
});