<?php $iq_test_manager = IQ_Test_Result_Manager::get_instance();

$resultados = $iq_test_manager->ver_resultados(898);
?>
<div class="subscription-iq-test-show" style="background-image: url(http://booking-woocommerce-valijas.test:83/wp-content/uploads/2024/05/home-2.jpg);">
   <h1>Iq Test</h1>
   <p>User name</p>
   <p>Correct Answers: <?php echo $resultados["results"]["total_correctas"]; ?>/<?php echo count($resultados["test"]["preguntas"]); ?> </p>
   <h2>Your Iq:</h2>
   <p>Iq: 122</p>
   <div class="respuestas">
    <?php for($i = 0;$i < count($resultados["test"]["preguntas"]);$i++):
        
    $pregunta = $resultados["test"]["preguntas"][$i];?>
    <div class="pregunta">
      <h3><?php echo $pregunta["pregunta_titulo"];?>:</h3>
      <img class="image-question" src="<?php echo wp_get_attachment_image_url($pregunta["pregunta_imagen"]);?>"/>
      <div class="opciones">
        <?php for($y = 0;$y < count($pregunta["pregunta_opciones"]);$y++):
        $opcion = $pregunta["pregunta_opciones"][$y];
        ?>
        <div class="opcion">
            <img src="<?php echo wp_get_attachment_image_url($opcion["opcion"]);?>" alt="">
        </div>
        <?php endfor;?>
      </div>
      </div>
      <?php endfor;?>

   </div>
</div>
</div>