<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class TitanFrameworkOptionRepeatable extends TitanFrameworkOption
{


    /**
     * The defaults of the settings specific to this option.
     *
     * @var array
     */
    public $defaultSecondarySettings = array(
        'fields' => array(),
        'template' => array(),
    );


    /**
     * Holds the options of this group.
     *
     * @var array
     */
    public $options = array();

    private static $firstLoad = true;

    /**
     * Override the constructor to include the creation of the options within
     * the group.
     *
     * @param array $settings The settings of the option.
     * @param TitanFrameworkAdminPage $owner The owner of the option.
     */
    function __construct($settings, $owner)
    {
        parent::__construct($settings, $owner);
        add_action('admin_head', array(__CLASS__, 'createRepeatableScript'));
        $this->init_group_options();
    }


    /**
     * Creates the options contained in the group. Mimics how Admin pages
     * create options.
     *
     * @return void
     */
    public function init_group_options()
    {
        if (!empty($this->settings['fields'])) {
            if (is_array($this->settings['fields'])) {
                $values = $this->getValue();
                if (!is_array($values)) {
                    $values = maybe_unserialize($values);
                }
                if (empty($values)) $values = array('template');

                $count = 0;
                foreach ($values as $val) {
                    foreach ($this->settings['fields'] as $settings) {

                        $settings['id'] = $this->settings['id'] . "[$count][" . $settings['id'] . ']';
                        if (!apply_filters('tf_create_option_continue_' . $this->getOptionNamespace(), true, $settings)) {
                            continue;
                        }

                        $obj = TitanFrameworkOption::factory($settings, $this->owner);
                        $this->options[] = $obj;

                        do_action('tf_create_option_' . $this->getOptionNamespace(), $obj);
                    }
                    $count++;
                }
            }
        }
    }

    public static function createRepeatableScript()
    {
        if (!self::$firstLoad) {
            return;
        }
        self::$firstLoad = false;

        ?>
        <script>
            jQuery(document).ready(function ($) {
                "use strict";
                $('.tf-repeatable-add').click(function () {
                    var type = $(this).data("repeatable");
                    alert(type);
                });
            });
        </script>
        <?php
    }

    /**
     * Display for options and meta
     */
    public function display()
    {

        $this->echoOptionHeader();

        echo "<div class='tf-repeatable-wrap'><ul>";
        if (!empty($this->options)) {
            $values = $this->getValue();
            if (!is_array($values)) {
                $values = maybe_unserialize($values);
            }
            if (empty($values)) $values = array('template');
            $count = 0;
            foreach ($values as $val) {
                echo "<li class='tf-repeatable-item'>";
                foreach ($this->options as $option) {
                    // Display the name of the option.
                    $name = $option->getName();
                    if (!empty($name) && !$option->getHidden()) {
                        echo '<span class="tf-group-name">' . esc_html($name) . '</span> ';
                    }

                    // Disable wrapper printing.
                    $option->echo_wrapper = false;

                    // Display the option field.
                    echo '<span class="tf-group-option">';
                    $option->display();
                    echo '</span>';
                }
                echo "</li>";
                $count++;
            }
        }
        echo "</ul>";
        echo "<i class='dashicons dashicons-plus tf-repeatable-add' data-repeatable='{$this->getID()}'></i>";
        echo "</div>";

        $this->echoOptionFooter();
    }
}
