<div class="subscription-iq-test-show">
    <?php if (!is_wp_error($result)): ?>
        <h1><?php echo $result["test"]["title"]; ?></h1>
        <p>User: <?php echo $result['results']['user']; ?></p>
        <p>Correct Answers: <?php echo $result['results']['total_correct_responses']; ?>/<?php echo count($result['test']['questions']); ?> </p>
        <h2 class="your-iq">
            <span>Your IQ:</span> 
            <span class="iq-number"><?php echo $result['results']['iq']; ?></span>
        </h2>
        <div class="respuestas" style="background-image: url(http://booking-woocommerce-valijas.test:83/wp-content/uploads/2024/05/home-2.jpg);">
            <?php foreach ($result['test']['questions'] as $index => $pregunta): ?>
                <?php $i = $index + 1;?>
                <div class="pregunta">
                    <h3><?php echo $pregunta['question_title']; ?>:</h3>
                    <img class="image-question" src="<?php echo wp_get_attachment_image_url($pregunta['question_image']); ?>"/>
                    <div class="opciones" >
                        <?php foreach ($pregunta['question_options'] as $subIndex => $opcion): ?>
                            <?php $y = $subIndex + 1;?>
                            <?php
                                $opcion_id = $i . '_' . $y;
                                $respuesta_usuario = isset($result['results']['user_responses'][$i]['response_value']) ? $result['results']['user_responses'][$i]['response_value'] : '';
                                $respuesta_correcta = $result['test']['correct_responses'][$index];
                                $is_correct = ($respuesta_correcta == $y);
                                $is_user_answer = ($respuesta_usuario == $y);
                            ?>
                            <div class="opcion <?php echo $is_user_answer ? ($is_correct ? 'user-correct' : 'user-incorrect') : ''; ?>">
                                <img src="<?php echo wp_get_attachment_image_url($opcion['option_image']); ?>" alt="">
                                <?php if ($is_user_answer): ?>
                                    <?php if ($is_correct): ?>
                                        <span class="icon correct-icon">
                                            <i class="fas fa-check-circle"></i> <!-- Green check icon -->
                                        </span>
                                    <?php else: ?>
                                        <span class="icon incorrect-icon">
                                            <i class="fas fa-times-circle"></i> <!-- Red cross icon -->
                                        </span>
                                    <?php endif;?>
                                <?php endif;?>
                            </div>
                        <?php endforeach;?>
                    </div>
                </div>
            <?php endforeach;?>
        </div>
    <?php else: ?>
        <p><?php echo $result->get_error_message(); ?></p>
    <?php endif;?>
</div>
