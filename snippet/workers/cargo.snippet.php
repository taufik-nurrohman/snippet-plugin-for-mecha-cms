<?php echo $messages; ?>
<form class="form-plugin" action="<?php echo $config->url_current; ?>/ignite" method="post">
  <?php echo Form::hidden('token', $token); ?>
  <p><?php echo Form::textarea('content', Guardian::wayback('content'), $speak->manager->placeholder_content, array('class' => array('textarea-block', 'textarea-expand', 'code'))); ?></p>
  <p><?php echo Form::text('name', Guardian::wayback('name', 'snippet.' . time() . '.txt'), $speak->manager->placeholder_file_name); ?> <?php echo Jot::button('construct', $speak->create); ?></p>
  <p><?php echo Form::checkbox('redirect', 1, Request::method('get') ? false : Guardian::wayback('redirect', false), Config::speak('manager.description_redirect_to_', $speak->file)); ?></p>
</form>