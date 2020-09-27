<?php
// Load template and begin making timed AJAX calls

// Load status
$status = get_status();
?>

<h1>Verificar</h1>
<h3>Verificando imagen de backup</h3>
<p>Chequeando la integridad de la imagen de backup elegida.</p>

<div class="row">
  <div class="col-sm-12">
    <div class="progress progress-striped active">
      <div id="overall_bar" class="progress-bar" style="width: 0%">
        <span id="overall_pct">Cargando...</span>
      </div>
    </div>
  </div>
</div>

<table id="progress-details" class="table table-striped">
  <thead>
    <tr>
      <th>Parte</th>
      <th>Realizado</th>
      <th>Tama&ntilde;o/Usado</th>
      <th>Transcurrido</th>
      <th>Restante</th>
      <th>Velocidad</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td><span id="part_num"><span class="text-muted">0 de 0</span></span></td>
      <td><span id="part_pct"><span class="text-muted">0.00%</span></span></td>
      <td>
        <span id="part_size"><span class="text-muted">0GB</span></span>
        <span id="part_used"><span class="text-muted">/ 0GB</span></span>
        <span id="part_mode"><span class="text-muted">raw</span></span>
      </td>
      <td><span id="time_elapsed"><span class="text-muted">00:00:00</span></span></td>
      <td><span id="time_remaining"><span class="text-muted">00:00:00</span></span></td>
      <td><span id="speed"><span class="text-muted">0GB/min</span></span></td>
    </tr>
  </tbody>
</table>

<!-- Detailed log box -->
<section id="log">
  <div class="row">
    <div class="col-sm-10 col-sm-offset-1 text-center">
      <p id="details" class="text-muted">Espere...</p>
    </div>
    <div class="col-sm-1 text-right">
      <span data-toggle="tooltip" title="Mostrar detalles">
        <i id="log-toggle" class="fas fa-ellipsis-h icon-button text-primary" data-toggle="collapse" data-target="#log-area"></i>
      </span>
    </div>
  </div>
  <p id="log-area" class="collapse">
    <textarea class="form-control" rows="4" id="log-box" readonly></textarea>
    <button class="btn btn-default btn-block btn-sm" onClick="copyLog();"><i class="fas fa-clipboard-check"></i> Copiar al portapapeles</button>
  </p>
</section>
<!-- End of log box -->

<div class="row">
  <div class="col-sm-12 text-center">
    <button id="cancel" type="reset" class="btn btn-danger" onClick="cancel();"><i class="fas fa-times-circle"></i> Cancelar</button>
    <button style="display: none;" id="again" type="button" class="btn btn-default" onClick="again();"><i class="fas fa-redo"></i> Iniciar nuevamente</button>
    <button style="display: none;" id="exit" type="button" class="btn btn-primary" onClick="exit();"><i class="fas fa-check-circle"></i> Salir</button>
  </div>
</div>

<script>

function exit() {
	$('#content').load('/ajax/salir.php');
}

function again() {
	location.replace('/');
}

function cancel() {
	bootbox.confirm({ 
		message: '<h3>Cancelar?</h3><p>Esto aborta el chequeo!</p>',
		buttons: {
			confirm: {
				label: 'Si, cancelar!',
				className: 'btn-danger'
			},
			cancel: {
				label: 'No',
				className: 'btn-default'
			}
		},
		callback: function(result){
			if (result) $('#content').load('/ajax/salir.php');
		}
	});
}

function copyLog() {
	$('#log-box').select();
  	document.execCommand("copy");
}

function start() {
	// Begin timed interval updates
	var updater = setInterval(function() {
		$.ajax({
			'url': '/ajax/verificarte.php',
	       		'type': 'GET',
		})
		.done(function(data) {
			r = $.parseJSON(data);
			if (r['status']) {
				// Success: Insert content on page
				if (r['overall_pct'] != null) {
					$('#overall_pct').html(r['overall_pct']+'%');
					$('#overall_bar').width(r['overall_pct']+'%');
				}
				if (r['part_pct'] != null) $('#part_pct').html(r['part_pct']);
				if (r['part_num'] != null) $('#part_num').html(r['part_num']);
				if (r['part_size'] != null) $('#part_size').html(r['part_size']);
				if (r['part_used'] != null) $('#part_used').html(' / '+r['part_used']);
				if (r['part_mode'] != null) $('#part_mode').html(' '+r['part_mode']);
				if (r['time_elapsed'] != null) $('#time_elapsed').html(r['time_elapsed']);
				if (r['time_remaining'] != null) $('#time_remaining').html(r['time_remaining']);
				if (r['speed'] != null) $('#speed').html(r['speed']);
				if (r['target'] != null) $('#target').html(r['target']);
				if (r['details'] != null) $('#details').html(r['details']);
				if (r['log_msg'] != null) {
					$('#log-box').scrollTop($('#log-box')[0].scrollHeight).append(r['log_msg']+"\n");
				}
				if (r['done'] != null) {
					// Operation complete
					clearInterval(updater);
					$('#overall_pct').html('100%');
					$('#overall_bar').width('100%').parent().removeClass('active');
					$('#cancel').hide();
					$('#again').show();
					$('#exit').show();
					bootbox.alert("<h3><i class='fas fa-check-circle text-success'></i> Verificacion completa</h3><p>Todo perfecto! "+r['done']+"</p>");
				}
			} else {
				// Failure: An error occurred
				clearInterval(updater);
				$('#overall_bar').addClass('progress-bar-danger').parent().removeClass('active');
				bootbox.alert("<h3><i class='fas fa-times-circle text-danger'></i> Error</h3><p>Fallo el proceso. Error dado por:</p><p><code>"+r['log_msg']+"</code></p>");
			}
		});
	}, 1 * 1000);
}

$(document).ready(function() {
	start();
});

</script>
