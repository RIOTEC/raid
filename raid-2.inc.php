<?php
// Load status
$status = get_status();

// Nothing gets posted here. The drive should have been mounted
// in the previous step.
?>

<h1>Verificar</h1>
<h3>Segundo: Elija la imagen de backup</h3>
<p>Seleccione el archivo para verificar integridad:</p>

<form id="riotec_form" class="form-horizontal">

  <div class="form-group">
    <label class="col-sm-2 control-label">Archivo <a data-toggle="tooltip" title="De tipo .riotec"><i class="fas fa-info-circle text-info"></i></a></label>
    <div class="col-sm-10">
      <div class="input-group">
        <input class="form-control" id="file" name="file" placeholder="mi-backup.riotec" type="text" value="<?php if (property_exists($status, 'file')) print $status->file; ?>">
        <span class="input-group-btn">
          <button class="btn btn-info" type="button" onClick="chooseFile();"><i class="fas fa-folder-open"></i> Seleccionar</button>
        </span>
      </div>
    </div>
  </div>

  <div class="form-group">
    <div class="col-sm-12 text-right">
      <button type="reset" class="btn btn-default" onClick="$('#content').load('procesar.php?page=verificar-1');">&lt; Regresar</button>
      <button type="submit" class="btn btn-warning">Continuar &gt;</button>
    </div>
  </div>

</form>

<script>

function chooseFile() {
	bootbox.dialog({
		message: '<div class="text-center"><i class="fas fa-spin fa-circle-notch text-primary"></i><br>Esperando por seleccion de archivo...</div>',
		closeButton: true
	});
	$.post("/ajax/open-dialog.php", { type: "file", file: $('#file').val() })
		.done(function(data) {
			bootbox.hideAll();
			r = $.parseJSON(data);
			$('#file').val(r['file']);
			if ((typeof r['error'] !== "undefined") && (r['error'] !== null)) {
				bootbox.alert('<h3>Backup no valido</h3><p>'+r['error']+'. Elija una image de backup valida.</p>');
			}
		});
}

$("#riotec_form").submit(function(event) {
	event.preventDefault();
	var file = $('#file').val();
	$.ajax({
		'url': '/ajax/save-id.php',
       		'type': 'POST',
		data: { type: 'verify', file: file },
	})
	.done(function(data) {
		r = $.parseJSON(data);
		if (r['status']) {
			// Success: Proceed to next page
			$('#content').load('procesar.php?page=verificar-3');
		} else {
			// Failure: Notify user of error
			bootbox.alert('<h3>Backup no valido</h3><div class="alert alert-danger"><p><i class="fas fa-exclamation-triangle"></i> <b>Error: ' + r['error'] + '</b></p></div><p>Revisar configuracion e intentar nuevamente.</p>');
		}
	});
});

</script>
