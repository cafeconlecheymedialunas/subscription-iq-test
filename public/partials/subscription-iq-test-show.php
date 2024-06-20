<?php 
$iq_test_manager = IQ_Test_Result_Manager::get_instance();
$resultados = $iq_test_manager->ver_resultados(898);
?>
<pre><?php print_r($resultados["results"]); ?></pre>
<div class="subscription-iq-test-show" style="background-image: url(http://booking-woocommerce-valijas.test:83/wp-content/uploads/2024/05/home-2.jpg);">
  <?php if (!is_wp_error($resultados)): ?>
    <h1>Iq Test</h1>
    <p><?php echo $resultados["results"]["user"]; ?></p>
    <p>Correct Answers: <?php echo $resultados["results"]["total_correctas"]; ?>/<?php echo count($resultados["test"]["preguntas"]); ?> </p>
    <h2>Your Iq: <?php echo $resultados["results"]["iq"]; ?></h2>
    <div class="respuestas">
      <?php foreach($resultados["test"]["preguntas"] as $index => $pregunta):
        $i = $index + 1;
        $respuesta_usuario = $resultados["results"]["respuestas_usuario"][$i]['respuesta'];
        $respuesta_correcta = $resultados["test"]["respuestas_correctas"][$i]; 
      ?>
        <div class="pregunta">
          <h3><?php echo $pregunta["pregunta_titulo"]; ?>:</h3>
          <img class="image-question" src="<?php echo wp_get_attachment_image_url($pregunta["pregunta_imagen"]); ?>"/>
          <div class="opciones">
            <?php foreach($pregunta["pregunta_opciones"] as $subIndex => $opcion):

              $y = $subIndex + 1;
          
              $opcion_id = ($i) . '_' . ($y);
              $is_correct = ($respuesta_correcta == $y);
              $is_user_answer = ($respuesta_usuario == $opcion_id); 
            ?>
              <div class="opcion <?php echo $is_user_answer ? ($is_correct ? 'user-correct' : 'user-incorrect') : ''; ?>">
                <img src="<?php echo wp_get_attachment_image_url($opcion["opcion"]); ?>" alt="">
                <?php if ($is_user_answer): ?>
                  <?php if ($is_correct): ?>
                    <span class="icon correct-icon">
                      <i class="fas fa-check-circle"></i> <!-- Icono de check verde -->
                    </span>
                  <?php else: ?>
                    <span class="icon incorrect-icon">
                      <i class="fas fa-times-circle"></i> <!-- Icono de cruz roja -->
                    </span>
                  <?php endif; ?>
                <?php endif; ?>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php else: ?>
    <p><?php echo $resultados->get_error_message(); ?></p>
  <?php endif; ?>
</div>
