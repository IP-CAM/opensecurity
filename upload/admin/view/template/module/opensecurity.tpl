<?php echo $header; ?><?php echo $column_left; ?>
<div id="content">
  <div class="page-header">
    <div class="container-fluid">
      <div class="pull-right">
        <button type="submit" form="form-html" data-toggle="tooltip" title="<?php echo $button_save; ?>" class="btn btn-primary"><i class="fa fa-save"></i></button>
        <a href="<?php echo $cancel; ?>" data-toggle="tooltip" title="<?php echo $button_cancel; ?>" class="btn btn-default"><i class="fa fa-reply"></i></a></div>
      <h1><?php echo $heading_title; ?></h1>
      <ul class="breadcrumb">
        <?php foreach ($breadcrumbs as $breadcrumb) { ?>
        <li><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a></li>
        <?php } ?>
      </ul>
    </div>
  </div>
  <div class="container-fluid">
    <!-- panel -->
    <div class="panel panel-default">
      <div class="panel-heading">
        <h3 class="panel-title"><i class="fa fa-pencil"></i><?php echo $text_module_settings ?></h3>
      </div>
      <div class="panel-body">
	<form action="<?php echo $action ?>" method="post" enctype="multipart/form-data" id="form-opsec" class="form-horizontal">
          <div class="row">
	    <!-- 1 col -->
	    <div class="col-md-6">
	      <div class="form-group">
		<div class="col-sm-4"><label class="control-label" for="opensec-status"><?php echo $module_status ?></label></div>
		<div class="col-sm-8">
		  <select class="form-control" id="opensec-status" name="opensec-status">
		    <option value="1"><?php echo $text_enabled ?></option>
		    <option value="0"><?php echo $text_disabled ?></option>
		  </select>
		</div>
	      </div>
	      <div class="form-group">
		<div class="col-sm-4"><label class="control-label" for="opensec-alertemail"><?php echo $alert_email ?></label></div>
		<div class="col-sm-8"><input type="email" class="form-control" name="opensec-alertemail" name="opensec-alertemail" value="<?php echo $settings['opensec-alertemail'] ?>"></div>
	      </div>
	      <div class="form-group">
		<div class="col-sm-4"><label class="control-label" for="opensec-sendalerts"><?php echo $text_send_emails ?></label></div>
		<div class="col-sm-8">
		  <select class="form-control" id="opensec-sendalerts" name="opensec-sendalerts">
		    <option value="1"><?php echo $text_enabled ?></option>
		    <option value="0"><?php echo $text_disabled ?></option>
		  </select>
		</div>
	      </div>
	      <div class="form-group">
		<div class="col-sm-4"><label class="control-label"><?php echo $text_version ?></label></div>
		<div class="col-sm-2"><span class="form-control"><?php echo $settings['opensec-version'] ?></span></div>
	      </div>
	    </div>
	    <!-- 1 col -->
	    <!-- 2 col -->
	    <div class="col-md-6">
	      <div class="form-group">
		<div class="col-sm-4"><label class="control-label" for="opensec-captchastatus"><?php echo $captcha_status ?></label></div>
		<div class="col-sm-8">
		  <select class="form-control" id="opensec-captchastatus" name="opensec-captchastatus">
		    <option value="1"><?php echo $text_enabled ?></option>
		    <option value="0"><?php echo $text_disabled ?></option>
		  </select>
		</div>
	      
	      </div>
	      <div class="form-group">
		<div class="col-sm-4"><label class="control-label" for="opensec-wlogcount"><?php echo $captcha_logins_count ?></label></div>
		<div class="col-sm-2"><input type="text" class="form-control" name="opensec-wlogcount" name="opensec-wlogcount" value="<?php echo $settings['opensec-wlogcount'] ?>"></div>
	      </div>
	      <div class="form-group">
		<div class="col-sm-4"><label class="control-label" for="opensec-autoblockip"><?php echo $text_auto_block_ip ?></label></div>
		<div class="col-sm-8">
		  <select class="form-control" id="opensec-autoblockip" name="opensec-autoblockip">
		    <option value="1"><?php echo $text_enabled ?></option>
		    <option value="0"><?php echo $text_disabled ?></option>
		  </select>
		</div>
	      </div>
	      <div class="form-group">
		<div class="col-sm-4"><label class="control-label" for="opensec-autoblockipcount"><?php echo $captcha_logins_count ?></label></div>
		<div class="col-sm-2"><input type="text" class="form-control" name="opensec-autoblockipcount" name="opensec-autoblockipcount" value="<?php echo $settings['opensec-autoblockipcount'] ?>"></div>
	      </div>
	    </div>
	    <!-- 2 col -->  
          </div>
          <div class="row">
	    <div class="col-md-12">
	      <h4><?php echo $text_notify_me ?></h4>
	    </div>
          </div>
	</form>
      </div>
    
    </div>

    <!-- panel -->
      <?php if($messages) { ?>
      <div class="panel panel-default">
	<div class="panel-heading">
	  <h3 class="panel-title"><?php echo $text_last_messages ?></h3>
	</div>
	<div class="panel-body">
	<table class="table table-striped">
	  <tr>
	    <th style="width:150px;"><?php echo $text_Date_Time ?></th>
	    <th><?php echo $text_Message ?></th>
	  </tr>
	<?php foreach($messages as $msg) { ?>
	  <tr>
	    <td><?php echo date('d.m.Y h:i', $msg['adate']) ?></td>
	    <td><?php echo $msg['alerttext'] ?></td>
	  </tr>
	<?php } ?>
	</table>
      </div>
      </div>
      <?php } ?>

  </div>
</div>
<script>
$(document).ready(function(){
  $("#opensec-captchastatus").val('<?php echo $settings['opensec-captchastatus'] ?>');
  $("#opensec-status").val('<?php echo $settings['opensec-status'] ?>');
  $("#opensec-autoblockip").val('<?php echo $settings['opensec-autoblockip'] ?>');
  $("#opensec-sendalerts").val('<?php echo $settings['opensec-sendalerts'] ?>');

});
</script>
<?php echo $footer; ?>