<?php
// Load status
$status = get_status();

// Set operation type
$status->op = 'raid';

// Save status
unset($status->file);
set_status($status);

// Force refresh the list of disks
$disks = get_disks(TRUE);

// Get partition options
$options = get_part_options($disks, array(), '/iso9660|fat.*|ext\d|ntfs/');

?>

<h1>RAID</h1>
<h3>SIN CARGAR: Elija unidad origen</h3>
<p>Seleccione la unidad origen que contiene la imagen de backup guardada:</p>

<form id="riotec_form" class="form-horizontal">

  <ul id="riotec_tabs" class="nav nav-tabs" style="margin-bottom: 1em;">
    <li class="active"><a href="#local" data-toggle="tab">Este equipo</a></li>
    <li><a href="#cifs" data-toggle="tab">Unidad de red</a></li>
  </ul>
  <div id="myTabContent" class="tab-content">

    <div class="tab-panel fade active in" id="local">
      <div class="form-group">
        <label class="col-sm-2 control-label">Disco local <a data-toggle="tooltip" title="El backup se encuentra en este equipo"><i class="fas fa-info-circle text-info"></i></a></label>
        <div class="col-sm-10">
<?php if (sizeof($options)>0) { ?>
	  <select class="form-control" name="local_part" id="local_part">
	<?php
	foreach ($options as $ov=>$od) print "<option value='$ov'>$od</option>";
	?>
	  </select>
<?php } else { ?>
          <p class="form-control-static text-muted"><i>No hay particiones locales de las que leer.</i></p>
<?php } ?>
	</div>
      </div>
    </div>

  <div class="form-group">
    <div class="col-sm-12 text-right">
      <button type="reset" class="btn btn-default" onClick="$('#content').load('procesar.php?page=inicio');">&lt; Regresar</button>
      <button type="submit" class="btn btn-warning">Continuar &gt;</button>
    </div>
  </div>

</form>

<script>

function togglePassword($e, $b) {
	var newtype = $e.prop('type')=='password'?'text':'password';
	$e.prop('type', newtype);
	$("i", $b).toggleClass("fa-eye fa-eye-slash");
}

function shareSearch() {
	bootbox.dialog({
		message: '<div class="text-center"><i class="fas fa-spin fa-circle-notch text-primary"></i><br>Escaneando recursos compartidos de red...</div>',
		closeButton: false
	});
	$.post("/ajax/nas-search.php", { type: "cifs" })
		.done(function(data) {
			bootbox.hideAll();
			bootbox.alert(data);
		});
}

$("#riotec_form").submit(function(event) {
	event.preventDefault();
	bootbox.dialog({
		message: '<div class="text-center"><i class="fas fa-spin fa-circle-notch text-primary"></i><br>Chequeando unidad de origen...</div>',
		closeButton: true
	});
	var vars = $('#riotec_form').serialize();
	var type = $('ul#riotec_tabs li.active a').attr('href');
	$.ajax({
		'url': '/ajax/mount-drive.php',
       		'type': 'POST',
		data: { type: type, vars: vars },
	})
	.done(function(data) {
		bootbox.hideAll();
		r = $.parseJSON(data);
		if (r['status']) {
			// Success: Proceed to next page
			$('#content').load('procesar.php?page=verificar-2');
		} else {
			// Failure: Notify user of error
			bootbox.alert('<h3>Fallo al acceder a la unidad</h3><div class="alert alert-danger"><p><i class="fas fa-exclamation-triangle"></i> <b>Error: ' + r['error'] + '</b></p></div><p>Revisar configuracion e intentar nuevamente.</p>');
		}
	});
});

</script>
