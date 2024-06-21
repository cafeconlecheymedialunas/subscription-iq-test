<?php 
$iq_test_manager = IQTestResultManager::getInstance();
$result = $iq_test_manager->viewResults(898);

?>
<pre><?php print_r($result["results"]); ?></pre>
<div class="subscription-iq-test-show" style="background-image: url(http://booking-woocommerce-valijas.test:83/wp-content/uploads/2024/05/home-2.jpg);">
  <?php if (!is_wp_error($result)): ?>
    <h1>Iq Test</h1>
    <p><?php echo $result["results"]["user"]; ?></p>
    <p>Correct Answers: <?php echo $result["results"]["total_correct"]; ?>/<?php echo count($result["test"]["preguntas"]); ?> </p>
    <h2>Your Iq: <?php echo $result["results"]["iq"]; ?></h2>
    <div class="respuestas">
      <?php foreach($result["test"]["questions"] as $index => $pregunta):
        $i = $index + 1;
        $respuesta_usuario = $result["results"]["user_responses"][$i]['response'];
        $respuesta_correcta = $result["test"]["correct_responses"][$i]; 
      ?>
        <div class="pregunta">
          <h3><?php echo $pregunta["question_title"]; ?>:</h3>
          <img class="image-question" src="<?php echo wp_get_attachment_image_url($pregunta["question_image"]); ?>"/>
          <div class="opciones">
            <?php foreach($pregunta["question_options"] as $subIndex => $opcion):

              $y = $subIndex + 1;
          
              $opcion_id = ($i) . '_' . ($y);
              $is_correct = ($respuesta_correcta == $y);
              $is_user_answer = ($respuesta_usuario == $opcion_id); 
            ?>
              <div class="opcion <?php echo $is_user_answer ? ($is_correct ? 'user-correct' : 'user-incorrect') : ''; ?>">
                <img src="<?php echo wp_get_attachment_image_url($opcion["image"]); ?>" alt="">
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
    <p><?php echo $result->get_error_message(); ?></p>
  <?php endif; ?>
</div>
