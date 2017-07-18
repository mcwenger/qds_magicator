<div class="box publish">

  <h1><?=lang('heading')?></h1>

  <?php if ($is_freeform_installed): ?>
    <?=form_open($action, ' class="settings"')?>
      <?php /* if (lang('subheading')): ?>
        <h2><?=lang('subheading')?></h2>
      <?php endif; */ ?>


        <div class="md-wrap">
        	<?php if (lang('intro')): ?>
          <p><?=lang('intro')?></p>
          <?php endif; ?>
        </div>

        <?php $this->embed('ee:_shared/table', $table); ?>

        <fieldset class="col-group last">
        	<div class="setting-txt col w-8">
            <h3><?=lang('label_template_var_name')?></h3>
            <em>The field name to be used in notifications</em>
        	</div>
        	<div class="setting-field col w-8 last">
            <input type="text" name="template_var_name" value="<?= $current['template_var_name'] ? $current['template_var_name'] : $composer_var_name_fallback; ?>">
    			</div>
        </fieldset>

        <fieldset class="form-ctrls">
      		<input class="btn" type="submit" value="Save Settings" data-submit-text="Save Settings" data-work-text="Saving...">
      	</fieldset>

    <?= form_close(); ?>

  <?php else: ?>
    <div class="md-wrap">
      <p><?=lang('ff_not_installed')?></p>
    </div>
  <?php endif; ?>
</div>
