<?php $hooks = array($page, $segment); ?>
<div class="tab-area">
  <div class="tab-button-area">
    <?php Weapon::fire('tab_button_before', $hooks); ?>
    <a class="tab-button active" href="#tab-content-1"><?php echo Jot::icon('pencil', 'fw') . ' ' . $speak->new; ?></a>
    <a class="tab-button" href="#tab-content-2"><?php echo Jot::icon('clock-o', 'fw') . ' ' . $speak->widget->recent_snippets; ?></a>
    <?php Weapon::fire('tab_button_after', $hooks); ?>
  </div>
  <?php echo $messages; ?>
  <div class="tab-content-area">
    <?php Weapon::fire('tab_content_before', $hooks); ?>
    <div class="tab-content" id="tab-content-1">
      <form class="form-plugin" action="<?php echo $config->url_current; ?>/ignite" method="post">
        <?php echo Form::hidden('token', $token); ?>
        <p><?php echo Form::textarea('content', Guardian::wayback('content'), $speak->manager->placeholder_content, array('class' => array('textarea-block', 'textarea-expand', 'code'))); ?></p>
        <p><?php echo Form::text('name', Guardian::wayback('name'), $speak->manager->placeholder_file_name); ?> <?php echo Jot::button('construct', $speak->create); ?></p>
        <p><?php echo Form::checkbox('redirect', 1, Request::method('get') ? false : Guardian::wayback('redirect', false), Config::speak('manager.description_redirect_to_', $speak->file)); ?></p>
      </form>
    </div>
    <div class="tab-content hidden" id="tab-content-2">
      <?php

      $_files = array();
      foreach(File::open(CACHE . DS . 'snippets.recent.cache')->unserialize() as $_file) {
          $e = File::E($_file);
          if( ! file_exists(ASSET . DS . '__snippet' . DS . $e . DS . $_file)) continue;
          $_files[] = File::url($_file);
      }

      ?>
      <?php if( ! empty($_files)): ?>
      <table class="table-bordered table-full-width">
        <tbody>
          <?php foreach($_files as $_file): ?>
          <?php $e = File::E($_file); ?>
          <tr>
            <td><a href="javascript:show_snippet_shortcode('<?php echo str_replace('.' . $e, "", $_file); ?>', '<?php echo $e === 'php' ? 'include' : 'print'; ?>');" title="<?php echo $speak->shortcode; ?>"><?php echo Jot::icon($e === 'php' ? 'file-code-o' : 'file-o', 'fw') . ' ' . $_file; ?></a></td>
            <td class="td-icon"><?php echo Jot::a('construct', $config->manager->slug . '/asset/repair/file:__snippet/' . $e . '/' . $_file, Jot::icon('pencil'), array('title' => $speak->edit)); ?></td>
            <td class="td-icon"><?php echo Jot::a('destruct', $config->manager->slug . '/asset/kill/file:__snippet/' . $e . '/' . $_file, Jot::icon('times'), array('title' => $speak->delete)); ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <?php endif; ?>
      <p><?php echo Jot::btn('action:database', $speak->snippets, $config->manager->slug . '/asset?path=__snippet'); ?></p>
    </div>
    <?php Weapon::fire('tab_content_after', $hooks); ?>
  </div>
</div>
<script>
function show_snippet_shortcode(id, method) {
    var text = '<?php echo $speak->shortcode; ?>',
        code = '{{' + method + ':' + id + '}}';
    if (typeof DASHBOARD.editor.prompt === "function") {
        DASHBOARD.editor.prompt(text, code);
    } else {
        window.prompt(text, code);
    }
}
</script>