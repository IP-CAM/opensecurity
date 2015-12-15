<div class="row">
  <div class="col-sm-6">
    <h2><?php echo $text_new_customer; ?></h2>
    <p><?php echo $text_checkout; ?></p>
    <div class="radio">
      <label>
        <?php if ($account == 'register') { ?>
        <input type="radio" name="account" value="register" checked="checked" />
        <?php } else { ?>
        <input type="radio" name="account" value="register" />
        <?php } ?>
        <?php echo $text_register; ?></label>
    </div>
    <?php if ($checkout_guest) { ?>
    <div class="radio">
      <label>
        <?php if ($account == 'guest') { ?>
        <input type="radio" name="account" value="guest" checked="checked" />
        <?php } else { ?>
        <input type="radio" name="account" value="guest" />
        <?php } ?>
        <?php echo $text_guest; ?></label>
    </div>
    <?php } ?>
    <p><?php echo $text_register_account; ?></p>
    <input type="button" value="<?php echo $button_continue; ?>" id="button-account" data-loading-text="<?php echo $text_loading; ?>" class="btn btn-primary" />
  </div>
  <div class="col-sm-6">
    <h2><?php echo $text_returning_customer; ?></h2>
    <p><?php echo $text_i_am_returning_customer; ?></p>
    <div class="form-group">
      <label class="control-label" for="input-email"><?php echo $entry_email; ?></label>
      <input type="text" name="email" value="" placeholder="<?php echo $entry_email; ?>" id="input-email" class="form-control" />
    </div>
    <div class="form-group">
      <label class="control-label" for="input-password"><?php echo $entry_password; ?></label>
      <input type="password" name="password" value="" placeholder="<?php echo $entry_password; ?>" id="input-password" class="form-control" />
      
    </div>
 <div class="form-group">
    <div class="row">
        <div class="col-md-4">
            <input type="button" value="<?php echo $button_login; ?>" id="button-login" data-loading-text="<?php echo $text_loading; ?>" class="btn btn-primary" />
        </div>
<?php if($show_captcha) { ?>
	<div class="col-md-4">
	  <img src="<?php echo $base ?>/index.php?route=captcha/basic_captcha/captcha" alt="" />
	</div>
	
	<div class="col-md-4">
	  <input type="text" name="input-captcha" id="input-captcha" class="form-control" placeholder="<?php echo $text_captcha ?>" />
	</div>
    </div>
 </div>
<?php } ?>
 <div class="form-group">
    <div class="row">
        <div class="col-md-12" style="text-align:right;">
            <a href="<?php echo $forgotten; ?>"><?php echo $text_forgotten; ?></a>
        </div>
    </div>
 </div>
  </div>
</div>