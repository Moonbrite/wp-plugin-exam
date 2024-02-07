<?php
/*
Plugin Name: Ticket Master
Description: Le plugin numéro 1 pour la resevation de ticket
Version: 1.0
Author: Jose.inc
*/


if (!defined('ABSPATH')) {
    exit;
}




$active_plugins = get_option('active_plugins');
if (in_array('woocommerce/woocommerce.php', $active_plugins)) {

    add_action('acf/init', 'ticket_master_fields');
    add_action("save_post","ticket_master_save_form");
    add_action('init', 'ticket_master_shortcode');
    add_action('wp_enqueue_scripts', 'ticket_master_display_enqueue_styles');


    function ticket_master_fields() {

        // Vérifie si ACF est actif
        if( function_exists('acf_add_local_field_group') ) {

            // Définition d'un tableau pour stocker les paramètres du groupe de champs
            $fields = array(
                'key' => 'group_601f3bb9fbd0e',
                'title' => 'Mon Groupe de Champs',
                'fields' => array(
                    array(
                        'key' => 'field_601f3bc9fbd0f',
                        'label' => 'Date de l\'événement',
                        'name' => 'date_of_concerts',
                        'type' => 'date_picker',
                        'instructions' => 'Saisissez votre date ici.',
                        'required' => 1,
                    ),
                    array(
                        'key' => 'field_601f3bc9fgggg',
                        'label' => 'Heure',
                        'name' => 'heure',
                        'type' => 'time_picker',
                        'instructions' => 'Saisissez votre heure ici.',
                        'required' => 1,
                    ),
                    array(
                        'key' => 'field_601f3b525',
                        'label' => 'Description suplémentaire',
                        'name' => 'descrition_sup',
                        'type' => 'text',
                        'instructions' => 'Saisissez votre texte ici.',
                        'required' => 0,
                    ),
                    array(
                        'key' => 'field_601f3bc9fggg',
                        'label' => 'informations privées',
                        'name' => 'prived_information',
                        'type' => 'text',
                        'instructions' => 'Saisissez votre texte ici.',
                        'required' => 1,
                    ),
                ),
                'location' => array(
                    array(
                        array(
                            'param' => 'post_type',
                            'operator' => '==',
                            'value' => 'product',
                        ),
                    ),
                ),
            );

            // Enregistrement du groupe de champs
            acf_add_local_field_group( $fields );
        }
    }
    function ticket_master_save_form($post_id){

        if(isset($_POST['date_of_concerts'])){
            update_post_meta(
                $post_id,
                'date_of_concerts',
                $_POST['date_of_concerts']
            );
        }

        if(isset($_POST['heure'])){
            update_post_meta(
                $post_id,
                'heure',
                $_POST['heure']
            );
        }

        if(isset($_POST['descrition_sup'])){
            update_post_meta(
                $post_id,
                'descrition_sup',
                $_POST['descrition_sup']
            );
        }

        if(isset($_POST['prived_information'])){
            update_post_meta(
                $post_id,
                'prived_information',
                $_POST['prived_information']
            );
        }

    }
    function ticket_master_shortcode(){
        add_shortcode('info', 'ticket_master_do_shortcode');
        add_shortcode('informations_privees', 'ticket_master_display_prived_info');
    }
    function ticket_master_do_shortcode(){


        $id = get_the_ID();

        $date_of_concerts = get_post_meta(
            $id,
            'date_of_concerts',
            true
        );

        $date = date( 'Y-m-d', strtotime( $date_of_concerts ) );

        $heure = get_post_meta(
            $id,
            'heure',
            true
        );

        $heure_clean = date( 'H:i', strtotime( $heure ) );

        $descrition_sup = get_post_meta(
            $id,
            'descrition_sup',
            true
        );

        $allInfos =
            "Date du concert : ".$date."<br>".
            "Heure du concert : ".$heure_clean." h<br>".
            "Description suplémentaire : ".$descrition_sup."<br>";



        $product_id = get_the_ID();

// Récupérer la date de l'événement à partir des méta-données du produit
        $event_date = get_post_meta($product_id, 'date_of_concerts', true);

// Récupérer l'heure de l'événement à partir des méta-données du produit
        $event_hour = get_post_meta($product_id, 'heure', true);


// Concaténer la date et l'heure pour former un timestamp complet de l'événement
        $event_datetime = strtotime($event_date . ' ' . $event_hour);

        $event_datetime = strtotime('-1 hour', $event_datetime);

// Vérifier si la date et l'heure de l'événement sont valides
        if (!empty($event_date) && !empty($event_hour)) {
            ?>

            <div id="countdown"></div>

            <script>
                function updateCountdown(eventTimestamp) {
                    // Date et heure actuelles
                    var currentTimestamp = Math.floor(Date.now() / 1000);

                    // Calcul du temps restant jusqu'à l'événement
                    var timeLeft = eventTimestamp - currentTimestamp ;

                    console.log(eventTimestamp)
                    // Si le temps restant est négatif, l'événement est passé
                    if (timeLeft < 0) {
                        document.getElementById('countdown').innerHTML = 'L\'événement est terminé.';
                        return;
                    }

                    // Convertir le temps restant en jours, heures, minutes et secondes
                    var daysLeft = Math.floor(timeLeft / (60 * 60 * 24));
                    var hoursLeft = Math.floor((timeLeft % (60 * 60 * 24)) / (60 * 60));
                    var minutesLeft = Math.floor((timeLeft % (60 * 60)) / 60);
                    var secondsLeft = timeLeft % 60;

                    if (timeLeft < 86400){
                        var countdownHTML = '<h2>L\'événement démarre dans :</h2>';
                        countdownHTML += '<div id="countdown-clock">';
                        countdownHTML += '<div><span>' + daysLeft + '</span> Jours</div>';
                        countdownHTML += '<div><span>' + hoursLeft + '</span> Heures</div>';
                        countdownHTML += '<div><span>' + minutesLeft + '</span> Minutes</div>';
                        countdownHTML += '<div><span>' + secondsLeft + '</span> Secondes</div>';
                        countdownHTML += '</div>';
                        document.getElementById('countdown').innerHTML = countdownHTML;
                    }
                }

                // Date et heure de l'événement (à partir des méta-données du produit)
                var eventTimestamp = <?php echo $event_datetime; ?>;

                // Mettre à jour le compteur toutes les secondes
                setInterval(function() {
                    updateCountdown(eventTimestamp);
                }, 1000);

                // Appeler la fonction une première fois pour initialiser le compteur
                updateCountdown(eventTimestamp);
            </script>

            <?php
        } else {
            // La date ou l'heure de l'événement n'est pas disponible
        }

        return "<p>$allInfos</p>";

    }
    function ticket_master_display_prived_info(){

        if (is_user_logged_in()) {

            $user_id = get_current_user_id();
            $product_id = get_the_ID();


            if (wc_customer_bought_product($user_id, $user_id, $product_id)) {

                $id = get_the_ID();

                $prived_information = get_post_meta(
                    $id,
                    'prived_information',
                    true
                );

                return "<p>Info privé : ".$prived_information."<br></p>";
            }
        }

    }
    function ticket_master_display_enqueue_styles()
    {
        wp_enqueue_style('style', plugins_url('css/style.css', __FILE__));
    }

}
else{
    echo("<h1 style='text-align: center;color:red'>Veuiller activé le plugin woocommerce</h1>");
}


